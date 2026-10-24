<?php

declare( strict_types=1 );

use WebberZone\Better_Search\Util\Cache;

/**
 * Front-end search must not expose revisions, internal post types or attachments of hidden parents.
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

		update_option( 'bsearch_settings', array_merge( (array) get_option( 'bsearch_settings', array() ), array( 'post_types' => 'post,page', 'use_fulltext' => 0, 'cache' => 0 ) ) );
		register_post_type( 'bs_hidden', array( 'public' => false ) );
		register_post_type( 'bs_docs', array( 'public' => true, 'exclude_from_search' => true ) );

		$public    = $this->post( 'public' );
		$draft     = $this->post( 'draft', array( 'post_status' => 'draft' ) );
		$private   = $this->post( 'private', array( 'post_status' => 'private' ) );
		$protected = $this->post( 'protected', array( 'post_password' => 'pw' ) );
		$hidden    = $this->post( 'hidden', array( 'post_type' => 'bs_hidden' ) );
		$this->post( 'docs', array( 'post_type' => 'bs_docs' ) );
		$this->post( 'menu_item', array( 'post_type' => 'nav_menu_item' ) );
		$this->post( 'revision', array( 'post_type' => 'revision', 'post_status' => 'inherit', 'post_parent' => $draft, 'post_name' => "{$draft}-revision-v1" ) );
		$this->post( 'attachment', array( 'post_type' => 'attachment', 'post_status' => 'inherit' ) );

		foreach ( compact( 'public', 'draft', 'private', 'protected', 'hidden' ) as $label => $parent ) {
			$this->post( "attachment_{$label}", array( 'post_type' => 'attachment', 'post_status' => 'inherit', 'post_parent' => $parent ) );
		}
	}

	public function tear_down(): void {
		unregister_post_type( 'bs_hidden' );
		unregister_post_type( 'bs_docs' );
		parent::tear_down();
	}

	private function post( string $label, array $args = array() ): int {
		$this->ids[ $label ] = self::factory()->post->create( array_merge( array( 'post_title' => "zebraword {$label}", 'post_status' => 'publish' ), $args ) );
		return $this->ids[ $label ];
	}

	private function labels( array $posts ): array {
		$labels = array_map( fn( $post ) => array_search( (int) ( $post->ID ?? $post ), $this->ids, true ), $posts );
		sort( $labels );
		return $labels;
	}

	private function search( array $args = array() ): array {
		return $this->labels( ( new Better_Search_Query( array_merge( array( 's' => 'zebraword', 'posts_per_page' => 100, 'exclude_protected_posts' => true ), $args ) ) )->posts );
	}

	public function test_request_cannot_reach_revisions_or_internal_post_types(): void {
		foreach ( array( 'revision', 'REVISION', array( 'revision', 'post' ), 'nav_menu_item', 'bs_hidden' ) as $post_type ) {
			$this->assertSame( array( 'public' ), $this->search( array( 'post_type' => $post_type ) ), wp_json_encode( $post_type ) );
		}
		$this->assertSame( array( 'public' ), $this->search( array( 'post_types' => 'revision' ) ) );

		// The seamless main query reads post_type from the URL.
		update_option( 'bsearch_settings', array_merge( get_option( 'bsearch_settings' ), array( 'seamless' => 1 ) ) );
		$this->go_to( '/?s=zebraword&post_type=revision' );
		$this->assertSame( array( 'public' ), $this->labels( $GLOBALS['wp_query']->posts ) );
	}

	public function test_allowed_post_types_match_core_rules(): void {
		// A post type hidden from general search can still have its own search form, as WP::parse_request() allows.
		$this->assertSame( array( 'public' ), $this->search() );
		$this->assertSame( array( 'docs' ), $this->search( array( 'post_type' => 'bs_docs' ) ) );

		// On multisite, configured post types can belong to another site and are not registered here.
		update_option( 'bsearch_settings', array_merge( get_option( 'bsearch_settings' ), array( 'post_types' => 'post,another_sites_type,revision' ) ) );
		$this->assertSame( array( 'another_sites_type', 'post' ), Better_Search_Core_Query::get_allowed_post_types( array( 'another_sites_type', 'post', 'revision' ) ) );
	}

	public function test_attachments_need_a_visible_parent(): void {
		$this->assertSame( array( 'attachment', 'attachment_public' ), $this->search( array( 'post_type' => 'attachment' ) ) );
		$this->assertSame( array( 'attachment', 'attachment_protected', 'attachment_public' ), $this->search( array( 'post_type' => 'attachment', 'exclude_protected_posts' => false ) ) );
	}

	public function test_cached_results_are_rechecked(): void {
		$args = array( 'post_type' => array( 'post', 'attachment' ), 'cache' => true );

		// A cache hit used to drop every attachment.
		Cache::delete_all();
		$expected = array( 'attachment', 'attachment_public', 'public' );
		$this->assertSame( $expected, $this->search( $args ) );
		$this->assertSame( $expected, $this->search( $args ) );

		// IDs cached before the fix, including hidden content, are filtered on the next hit.
		foreach ( Cache::get_keys() as $key ) {
			$value = Cache::get( $key );
			if ( isset( $value['items'] ) ) {
				$value['items'] = array_map( fn( $id ) => array( 'ID' => $id, 'score' => 1 ), array_values( $this->ids ) );
				Cache::set( $key, $value );
			}
		}
		$this->assertSame( $expected, $this->search( $args ) );
		Cache::delete_all();
	}
}
