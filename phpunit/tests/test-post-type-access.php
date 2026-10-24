<?php

declare( strict_types=1 );

use WebberZone\Better_Search\Util\Cache;

/**
 * Front-end search must never expose revisions, internal post types or
 * attachments of non-public parents, whatever post_type the request asks for.
 */
class PostTypeAccessTest extends WP_UnitTestCase {

	/**
	 * Fixture IDs keyed by label.
	 *
	 * @var int[]
	 */
	private array $ids = array();

	public function set_up(): void {
		parent::set_up();

		$this->set_settings( array( 'post_types' => 'post,page' ) );

		register_post_type( 'bs_private_cpt', array( 'public' => false ) );
		register_post_type( 'bs_hidden_cpt', array( 'public' => true, 'exclude_from_search' => true ) );
		register_post_type( 'bs_internal_cpt', array( 'public' => true, 'publicly_queryable' => false, 'exclude_from_search' => true ) );
		register_post_type( 'bs_public_cpt', array( 'public' => true ) );

		$post = function ( string $label, array $args ) {
			$this->ids[ $label ] = self::factory()->post->create(
				array_merge(
					array(
						'post_title'   => "zebraword {$label}",
						'post_content' => "zebraword content {$label}",
						'post_status'  => 'publish',
					),
					$args
				)
			);
			return $this->ids[ $label ];
		};

		$post( 'public', array() );
		$post( 'public_page', array( 'post_type' => 'page' ) );
		$draft     = $post( 'draft', array( 'post_status' => 'draft' ) );
		$private   = $post( 'private', array( 'post_status' => 'private' ) );
		$pending   = $post( 'pending', array( 'post_status' => 'pending' ) );
		$future    = $post( 'future', array( 'post_status' => 'future', 'post_date' => gmdate( 'Y-m-d H:i:s', time() + YEAR_IN_SECONDS ) ) );
		$protected = $post( 'protected', array( 'post_password' => 's3cretPW' ) );
		$post( 'trash', array( 'post_status' => 'trash' ) );

		foreach ( array( 'draft' => $draft, 'private' => $private, 'pending' => $pending, 'protected' => $protected, 'public' => $this->ids['public'] ) as $label => $parent ) {
			$post(
				"revision_{$label}",
				array(
					'post_type'   => 'revision',
					'post_status' => 'inherit',
					'post_parent' => $parent,
					'post_name'   => "{$parent}-revision-v1",
				)
			);
		}

		// A real revision made by WordPress itself.
		wp_update_post( array( 'ID' => $private, 'post_content' => 'zebraword content private edited' ) );
		$this->ids['revision_real'] = (int) current( array_keys( wp_get_post_revisions( $private ) ) );

		$post( 'attachment_unattached', array( 'post_type' => 'attachment', 'post_status' => 'inherit', 'post_mime_type' => 'image/jpeg' ) );
		foreach ( array( 'public' => $this->ids['public'], 'draft' => $draft, 'private' => $private, 'pending' => $pending, 'future' => $future, 'protected' => $protected ) as $label => $parent ) {
			$post(
				"attachment_{$label}",
				array(
					'post_type'      => 'attachment',
					'post_status'    => 'inherit',
					'post_parent'    => $parent,
					'post_mime_type' => 'image/jpeg',
				)
			);
		}

		$post( 'nav_menu_item', array( 'post_type' => 'nav_menu_item' ) );
		$post( 'wp_block', array( 'post_type' => 'wp_block' ) );
		$post( 'customize_changeset', array( 'post_type' => 'customize_changeset' ) );
		$post( 'bs_private_cpt', array( 'post_type' => 'bs_private_cpt' ) );

		// Published parent, but of a post type visitors cannot view.
		$post( 'attachment_hidden_type', array( 'post_type' => 'attachment', 'post_status' => 'inherit', 'post_parent' => $this->ids['bs_private_cpt'], 'post_mime_type' => 'image/jpeg' ) );
		$post( 'bs_hidden_cpt', array( 'post_type' => 'bs_hidden_cpt' ) );
		$post( 'bs_internal_cpt', array( 'post_type' => 'bs_internal_cpt' ) );
		$post( 'bs_public_cpt', array( 'post_type' => 'bs_public_cpt' ) );
	}

	public function tear_down(): void {
		unregister_post_type( 'bs_private_cpt' );
		unregister_post_type( 'bs_hidden_cpt' );
		unregister_post_type( 'bs_internal_cpt' );
		unregister_post_type( 'bs_public_cpt' );
		parent::tear_down();
	}

	private function set_settings( array $settings ): void {
		update_option( 'bsearch_settings', array_merge( (array) get_option( 'bsearch_settings', array() ), $settings ) );
	}

	private function search( array $args = array() ): array {
		$query = new Better_Search_Query(
			array_merge(
				array(
					's'                       => 'zebraword',
					'posts_per_page'          => 100,
					'use_custom_tables'       => false,
					'use_fulltext'            => false,
					'search_taxonomies'       => false,
					'search_excerpt'          => false,
					'search_meta'             => false,
					'search_authors'          => false,
					'search_comments'         => false,
					'fuzzy_search_level'      => 'off',
					'exclude_protected_posts' => true,
					'cache'                   => false,
				),
				$args
			)
		);

		return $this->labels( wp_list_pluck( $query->posts, 'ID' ) );
	}

	private function sorted( array $values ): array {
		sort( $values );
		return $values;
	}

	private function labels( array $ids ): array {
		$labels = array_map(
			function ( $id ) {
				$label = array_search( (int) $id, $this->ids, true );
				return false === $label ? "unknown:{$id}" : $label;
			},
			$ids
		);
		sort( $labels );
		return $labels;
	}

	private function assert_only_public_content( array $labels, string $context ): void {
		$allowed = array( 'public', 'public_page', 'bs_public_cpt', 'bs_hidden_cpt', 'attachment_unattached', 'attachment_public' );
		$this->assertSame( array(), array_values( array_diff( $labels, $allowed ) ), "Leaked via {$context}" );
	}

	public function attack_post_types(): array {
		return array(
			'revision'              => array( 'revision' ),
			'uppercase revision'    => array( 'REVISION' ),
			'padded revision'       => array( ' revision ' ),
			'array with revision'   => array( array( 'revision', 'post' ) ),
			'only revision array'   => array( array( 'revision' ) ),
			'nav_menu_item'         => array( 'nav_menu_item' ),
			'wp_block'              => array( 'wp_block' ),
			'customize_changeset'   => array( 'customize_changeset' ),
			'non-public cpt'        => array( 'bs_private_cpt' ),
			'not publicly queryable' => array( 'bs_internal_cpt' ),
			'unregistered'          => array( 'does_not_exist' ),
			'any'                   => array( 'any' ),
			'sql-ish'               => array( "revision') OR 1=1 -- " ),
			'empty'                 => array( '' ),
		);
	}

	/**
	 * @dataProvider attack_post_types
	 *
	 * @param mixed $post_type Requested post type.
	 */
	public function test_post_type_arg_cannot_reach_hidden_content( $post_type ): void {
		$labels = $this->search( array( 'post_type' => $post_type ) );
		$this->assert_only_public_content( $labels, 'post_type=' . wp_json_encode( $post_type ) );
		$this->assertNotEmpty( $labels, 'Fallback should still return public results.' );
	}

	/**
	 * @dataProvider attack_post_types
	 *
	 * @param mixed $post_type Requested post type.
	 */
	public function test_post_types_arg_cannot_reach_hidden_content( $post_type ): void {
		$value  = is_array( $post_type ) ? implode( ',', $post_type ) : $post_type;
		$labels = $this->search( array( 'post_types' => $value ) );
		$this->assert_only_public_content( $labels, 'post_types=' . $value );
	}

	public function test_post_types_query_string_format_cannot_reach_revisions(): void {
		$labels = $this->search( array( 'post_types' => 'post=post&revision=revision' ) );
		$this->assert_only_public_content( $labels, 'post_types query string' );
		$this->assertContains( 'public', $labels );
	}

	public function test_url_post_type_is_filtered_when_args_omit_it(): void {
		$this->go_to( '/?s=zebraword&post_type=revision' );
		$labels = $this->search();
		$this->assert_only_public_content( $labels, 'URL post_type with no args' );
	}

	public function test_seamless_main_query_cannot_reach_revisions(): void {
		$this->set_settings( array( 'seamless' => true, 'use_fulltext' => false ) );
		$this->assertTrue( (bool) bsearch_get_option( 'seamless' ) );

		foreach ( array( 'revision', 'nav_menu_item', 'bs_private_cpt' ) as $post_type ) {
			$this->go_to( "/?s=zebraword&post_type={$post_type}" );
			$labels = $this->labels( wp_list_pluck( $GLOBALS['wp_query']->posts, 'ID' ) );
			$this->assert_only_public_content( $labels, "seamless main query post_type={$post_type}" );
			$this->assertNotContains( 'revision', (array) $GLOBALS['wp_query']->get( 'post_type' ) );
		}
	}

	public function test_admin_user_still_gets_no_revisions(): void {
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'administrator' ) ) );
		$labels = $this->search( array( 'post_type' => 'revision' ) );
		$this->assert_only_public_content( $labels, 'administrator post_type=revision' );
	}

	public function test_default_search_returns_public_post_and_page_only(): void {
		$this->assertSame( array( 'public', 'public_page' ), $this->search() );
	}

	public function test_inherit_status_only_added_for_attachments(): void {
		$query = new Better_Search_Query( array( 's' => 'zebraword', 'post_type' => 'post', 'use_fulltext' => false, 'cache' => false ) );
		$this->assertSame( array( 'publish' ), (array) $query->get( 'post_status' ) );

		$query = new Better_Search_Query( array( 's' => 'zebraword', 'post_type' => 'attachment', 'use_fulltext' => false, 'cache' => false ) );
		$this->assertSame( array( 'publish', 'inherit' ), (array) $query->get( 'post_status' ) );
	}

	public function test_attachments_require_public_parent_when_protected_posts_excluded(): void {
		$labels = $this->search( array( 'post_type' => 'attachment', 'exclude_protected_posts' => true ) );
		$this->assertSame( array( 'attachment_public', 'attachment_unattached' ), $labels );
	}

	public function test_attachments_of_protected_parent_shown_when_protected_posts_allowed(): void {
		$labels = $this->search( array( 'post_type' => 'attachment', 'exclude_protected_posts' => false ) );
		$this->assertSame( array( 'attachment_protected', 'attachment_public', 'attachment_unattached' ), $labels );
	}

	public function test_mixed_post_and_attachment_search(): void {
		$labels = $this->search( array( 'post_type' => array( 'post', 'attachment', 'revision' ) ) );
		$this->assertSame( array( 'attachment_public', 'attachment_unattached', 'public' ), $labels );
	}

	public function test_excluded_but_publicly_queryable_cpt_can_be_requested(): void {
		// Matches WP::parse_request(): a dedicated search form for a post type hidden from general search works.
		$this->assertSame( array( 'bs_hidden_cpt' ), $this->search( array( 'post_type' => 'bs_hidden_cpt' ) ) );
		$this->assertSame( array( 'public', 'public_page' ), $this->search(), 'It stays out of general searches.' );
	}

	public function test_configured_post_types_need_not_be_registered(): void {
		// On multisite another site's post types are not registered while it is searched.
		$this->set_settings( array( 'post_types' => 'post,another_sites_type' ) );

		$this->assertSame( array( 'another_sites_type', 'post' ), $this->sorted( Better_Search_Core_Query::get_allowed_post_types( array( 'post', 'another_sites_type', 'revision' ) ) ) );
		$this->assertNotContains( 'another_unconfigured_type', Better_Search_Core_Query::get_searchable_post_types() );
	}

	public function test_configured_excluded_cpt_is_allowed(): void {
		$this->set_settings( array( 'post_types' => 'post,bs_hidden_cpt,revision' ) );

		$this->assertSame( array( 'bs_hidden_cpt' ), $this->search( array( 'post_type' => 'bs_hidden_cpt' ) ) );
		$this->assertSame( array( 'bs_hidden_cpt', 'public' ), $this->search(), 'Configured revision must be dropped.' );
		$this->assertSame( array( 'bs_hidden_cpt', 'public' ), $this->search( array( 'post_type' => 'revision' ) ) );
		$this->assertSame( array( 'bs_hidden_cpt', 'public' ), $this->search( array( 'post_type' => 'bs_internal_cpt' ) ), 'Not publicly queryable falls back.' );
	}

	public function test_public_cpt_can_be_requested(): void {
		$this->assertSame( array( 'bs_public_cpt' ), $this->search( array( 'post_type' => 'bs_public_cpt' ) ) );
	}

	public function test_explicit_post_status_from_code_is_respected(): void {
		$labels = $this->search( array( 'post_type' => 'post', 'post_status' => array( 'publish', 'private' ) ) );
		$this->assertContains( 'private', $labels );
	}

	public function test_get_allowed_post_types(): void {
		$this->assertSame( array( 'post' ), Better_Search_Core_Query::get_allowed_post_types( array( 'post', 'revision', 'nav_menu_item' ) ) );
		$this->assertSame( array( 'post', 'page' ), Better_Search_Core_Query::get_allowed_post_types( array( 'revision' ) ) );
		$this->assertSame( array( 'post', 'page' ), Better_Search_Core_Query::get_allowed_post_types( array() ) );
		$this->assertSame( array( 'post', 'page' ), Better_Search_Core_Query::get_allowed_post_types( 'revision' ) );
	}

	/**
	 * Run a cached search once and return the key of the stored result set.
	 */
	private function warm_cache( array $args ): string {
		Cache::delete_all();
		$this->search( array_merge( array( 'cache' => true ), $args ) );

		foreach ( Cache::get_keys() as $key ) {
			$value = Cache::get( $key );
			if ( is_array( $value ) && isset( $value['items'] ) ) {
				return $key;
			}
		}

		$this->fail( 'No cached result set was stored.' );
	}

	/**
	 * Cached items as Better Search 4.5.2 could have stored them for this search.
	 */
	private function poisoned_items(): array {
		$items = array();
		foreach ( array( 'revision_draft', 'revision_private', 'revision_protected', 'revision_real', 'draft', 'private', 'attachment_draft', 'attachment_private', 'attachment_protected', 'attachment_hidden_type', 'attachment_public', 'attachment_unattached', 'public', 'nav_menu_item' ) as $label ) {
			$items[] = array( 'ID' => $this->ids[ $label ], 'blog_id' => get_current_blog_id(), 'score' => 1 );
		}
		return $items;
	}

	public function test_cached_attachment_search_matches_uncached(): void {
		$args = array( 'post_type' => 'attachment' );
		$cold = $this->search( array_merge( array( 'cache' => true ), $args ) );
		$warm = $this->search( array_merge( array( 'cache' => true ), $args ) );

		$this->assertSame( array( 'attachment_public', 'attachment_unattached' ), $cold );
		$this->assertSame( $cold, $warm );
		Cache::delete_all();
	}

	public function test_cached_mixed_search_matches_uncached(): void {
		$args = array( 'post_type' => array( 'post', 'page', 'attachment' ) );
		$cold = $this->search( array_merge( array( 'cache' => true ), $args ) );
		$warm = $this->search( array_merge( array( 'cache' => true ), $args ) );

		$this->assertSame( $cold, $warm );
		$this->assertContains( 'attachment_public', $warm );
		Cache::delete_all();
	}

	public function poisoned_cache_cases(): array {
		return array(
			'revision request'   => array( array( 'post_type' => 'revision' ), array( 'public' ) ),
			'attachment request' => array( array( 'post_type' => 'attachment' ), array( 'attachment_public', 'attachment_unattached' ) ),
			'mixed request'      => array( array( 'post_type' => array( 'post', 'attachment' ) ), array( 'attachment_public', 'attachment_unattached', 'public' ) ),
			'protected allowed'  => array( array( 'post_type' => 'attachment', 'exclude_protected_posts' => false ), array( 'attachment_protected', 'attachment_public', 'attachment_unattached' ) ),
		);
	}

	/**
	 * @dataProvider poisoned_cache_cases
	 */
	public function test_stale_cache_entries_are_rechecked( array $args, array $expected ): void {
		$key   = $this->warm_cache( $args );
		$value = Cache::get( $key );

		$value['items'] = $this->poisoned_items();
		Cache::set( $key, $value );

		$this->assertSame( $expected, $this->search( array_merge( array( 'cache' => true ), $args ) ) );
		Cache::delete_all();
	}

	/**
	 * @dataProvider poisoned_cache_cases
	 */
	public function test_stale_legacy_cache_entries_are_rechecked( array $args, array $expected ): void {
		$key    = $this->warm_cache( $args );
		$legacy = array();
		foreach ( $this->poisoned_items() as $item ) {
			$legacy[ $item['ID'] ] = 1;
		}
		Cache::set( $key, $legacy );

		$this->assertSame( $expected, $this->search( array_merge( array( 'cache' => true ), $args ) ) );
		Cache::delete_all();
	}

	public function test_stale_cache_is_rechecked_for_id_fields(): void {
		$core = new Better_Search_Core_Query( array( 'is_better_search_loaded' => true ) );
		$core->query_args['exclude_protected_posts'] = true;

		$query = new WP_Query();
		$query->set( 'fields', 'ids' );
		$query->set( 'post_type', array( 'post', 'attachment' ) );
		$query->set( 'post_status', array( 'publish', 'inherit' ) );

		$restore = new ReflectionMethod( $core, 'restore_cached_posts' );
		$restore->setAccessible( true );

		$this->assertSame( array( 'attachment_public', 'attachment_unattached', 'public' ), $this->labels( $restore->invoke( $core, $this->poisoned_items(), $query ) ) );
	}

	public function test_filter_visible_post_ids(): void {
		$ids    = array_values( $this->ids );
		$orphan = self::factory()->post->create( array( 'post_type' => 'attachment', 'post_status' => 'inherit', 'post_parent' => PHP_INT_MAX ) );
		$ids[]  = $orphan;

		$visible = Better_Search_Core_Query::filter_visible_post_ids( $ids, array( 'post', 'attachment' ), array( 'publish', 'inherit' ), true );
		$this->assertSame( array( 'attachment_public', 'attachment_unattached', 'public' ), $this->labels( $visible ) );

		$visible = Better_Search_Core_Query::filter_visible_post_ids( $ids, array( 'post', 'attachment' ), array( 'publish', 'inherit' ), false );
		$this->assertSame( array( 'attachment_protected', 'attachment_public', 'attachment_unattached', 'protected', 'public' ), $this->labels( $visible ) );

		$this->assertSame( array( 'public' ), $this->labels( Better_Search_Core_Query::filter_visible_post_ids( $ids, 'post', '', true ) ), 'Empty status means publish.' );
		$this->assertSame( array(), Better_Search_Core_Query::filter_visible_post_ids( $ids, array( 'revision' ), array( 'inherit' ), true ), 'Revisions never pass, even with a published parent.' );
		$this->assertSame( array(), Better_Search_Core_Query::filter_visible_post_ids( array(), 'post', 'publish', true ) );

		$ordered = array( $this->ids['public_page'], $this->ids['public'] );
		$this->assertSame( $ordered, Better_Search_Core_Query::filter_visible_post_ids( $ordered, 'any', 'publish', true ), 'Order and any are preserved.' );
	}

	public function test_default_post_status(): void {
		$this->assertSame( array( 'publish' ), Better_Search_Core_Query::get_default_post_status( array( 'post', 'page' ) ) );
		$this->assertSame( array( 'publish', 'inherit' ), Better_Search_Core_Query::get_default_post_status( 'attachment' ) );
	}
}
