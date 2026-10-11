<?php

use WebberZone\Better_Search\Main;
use WebberZone\Better_Search\Util\Cache;

class AbilitiesTest extends WP_UnitTestCase {

	private $test_filters = array();

	public function set_up() {
		parent::set_up();

		if ( ! function_exists( 'wp_get_ability' ) ) {
			$this->markTestSkipped( 'Requires the WordPress Abilities API.' );
		}

		update_option( 'bsearch_settings', bsearch_settings_defaults() );
		bsearch_update_option( 'cache', 0 );
		Cache::delete();
	}

	public function tear_down() {
		foreach ( $this->test_filters as $filter ) {
			remove_filter( $filter[0], $filter[1], $filter[2] );
		}

		$this->test_filters = array();
		Cache::delete();
		wp_set_current_user( 0 );

		parent::tear_down();
	}

	private function add_test_filter( $hook, $callback, $priority = 10, $accepted_args = 1 ) {
		add_filter( $hook, $callback, $priority, $accepted_args );
		$this->test_filters[] = array( $hook, $callback, $priority );
	}

	public function test_search_input_schema_rejects_invalid_values() {
		$schema = wp_get_ability( 'better-search/search' )->get_input_schema();

		$this->assertWPError( rest_validate_value_from_schema( array(), $schema ) );
		$this->assertWPError(
			rest_validate_value_from_schema(
				array(
					'query' => 'hello',
					'limit' => 101,
				),
				$schema
			)
		);
		$this->assertWPError(
			rest_validate_value_from_schema(
				array(
					'query'  => 'hello',
					'offset' => -1,
				),
				$schema
			)
		);
		$this->assertWPError( rest_validate_value_from_schema( array( 'query' => '' ), $schema ) );
		$this->assertNotWPError(
			rest_validate_value_from_schema(
				array(
					'query'      => 'hello',
					'post_types' => array( 'post', 'page' ),
					'limit'      => 5,
					'offset'     => 10,
				),
				$schema
			)
		);
	}

	public function test_search_returns_scored_results_and_filters_unreadable_posts() {
		$visible_id = self::factory()->post->create(
			array(
				'post_title'   => 'Findable widget guide',
				'post_content' => 'A guide about findable widgets.',
				'post_excerpt' => 'A visible excerpt.',
			)
		);
		$private_id = self::factory()->post->create(
			array(
				'post_title'  => 'Private widget guide',
				'post_status' => 'private',
			)
		);

		$this->add_test_filter(
			'posts_pre_query',
			static function ( $posts, $query ) use ( $visible_id, $private_id ) {
				if ( ! $query->get( 'better_search_query' ) ) {
					return $posts;
				}

				$visible        = get_post( $visible_id );
				$visible->score = 12.5;
				$private        = get_post( $private_id );
				$private->score = 9.5;

				return array( $visible, $private );
			},
			99,
			2
		);

		wp_set_current_user( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );

		$response = wp_get_ability( 'better-search/search' )->execute( array( 'query' => 'widget' ) );

		$this->assertNotWPError( $response );
		$this->assertArrayHasKey( 'results', $response );
		$this->assertArrayHasKey( 'total', $response );
		$this->assertIsInt( $response['total'] );

		$results = $response['results'];
		$this->assertCount( 1, $results );
		$this->assertSame( $visible_id, $results[0]['id'] );
		$this->assertSame( get_the_title( $visible_id ), $results[0]['title'] );
		$this->assertSame( get_permalink( $visible_id ), $results[0]['url'] );
		$this->assertIsFloat( $results[0]['relevance'] );
		$this->assertSame( 12.5, $results[0]['relevance'] );
	}

	public function test_search_returns_plain_text_titles_and_excerpts() {
		$post_id = self::factory()->post->create(
			array(
				'post_title'   => 'Widgets &#8211; the "best" ones',
				'post_excerpt' => 'A <strong>bold</strong> take on widgets &#8212; read on&#8230;',
			)
		);

		$this->add_test_filter(
			'posts_pre_query',
			static function ( $posts, $query ) use ( $post_id ) {
				if ( ! $query->get( 'better_search_query' ) ) {
					return $posts;
				}

				return array( get_post( $post_id ) );
			},
			99,
			2
		);
		$this->add_test_filter(
			'excerpt_more',
			static function () {
				return ' <a class="read-more" href="">Read more</a>';
			},
			99
		);

		wp_set_current_user( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );

		$response = wp_get_ability( 'better-search/search' )->execute( array( 'query' => 'widgets' ) );

		$this->assertNotWPError( $response );
		$results = $response['results'];
		$this->assertCount( 1, $results );

		foreach ( array( 'title', 'excerpt' ) as $field ) {
			$this->assertStringNotContainsString( '<', $results[0][ $field ] );
			$this->assertStringNotContainsString( '&#', $results[0][ $field ] );
			$this->assertStringNotContainsString( '&amp;', $results[0][ $field ] );
		}

		$this->assertSame(
			html_entity_decode( get_the_title( $post_id ), ENT_QUOTES | ENT_HTML5, 'UTF-8' ),
			$results[0]['title']
		);
	}

	public function test_search_passes_bounded_arguments_to_the_query() {
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );
		$received = array();

		$this->add_test_filter(
			'better_search_query_args_before',
			static function ( $args ) use ( &$received ) {
				$received = $args;
				return $args;
			},
			99
		);

		wp_get_ability( 'better-search/search' )->execute(
			array(
				'query'      => 'widget',
				'limit'      => 3,
				'offset'     => 5,
				'post_types' => array( 'post' ),
			)
		);

		$this->assertSame( 'widget', $received['s'] );
		$this->assertSame( 3, $received['limit'] );
		$this->assertSame( 5, $received['offset'] );
		$this->assertSame( array( 'post' ), $received['post_types'] );
	}

	public function test_search_rejects_post_types_that_are_not_public_and_searchable() {
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );
		register_post_type( 'bsearch_hidden', array( 'public' => false ) );

		$result = wp_get_ability( 'better-search/search' )->execute(
			array(
				'query'      => 'widget',
				'post_types' => array( 'bsearch_hidden' ),
			)
		);

		$this->assertWPError( $result );
		$this->assertSame( 'bsearch_invalid_post_types', $result->get_error_code() );

		unregister_post_type( 'bsearch_hidden' );
	}

	public function test_search_ability_requires_a_signed_in_user() {
		wp_set_current_user( 0 );

		$this->assertFalse( Main::get_instance()->abilities->can_search() );
		$this->assertWPError( wp_get_ability( 'better-search/search' )->execute( array( 'query' => 'widget' ) ) );

		$request = new WP_REST_Request( 'GET', '/wp-abilities/v1/abilities/better-search/search/run' );
		$request->set_query_params( array( 'input' => array( 'query' => 'widget' ) ) );
		$this->assertSame( 401, rest_do_request( $request )->get_status() );

		wp_set_current_user( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );
		$this->assertTrue( Main::get_instance()->abilities->can_search() );
		$this->assertNotWPError( wp_get_ability( 'better-search/search' )->execute( array( 'query' => 'widget' ) ) );
	}

	public function test_search_output_matches_the_wrapped_schema() {
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );

		$ability  = wp_get_ability( 'better-search/search' );
		$response = $ability->execute( array( 'query' => 'widget' ) );

		$this->assertNotWPError( $response );
		$this->assertNotWPError( rest_validate_value_from_schema( $response, $ability->get_output_schema() ) );
	}

	public function test_zero_result_prose_queries_report_that_the_phrase_was_too_long() {
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );

		$this->add_test_filter(
			'posts_pre_query',
			static function ( $posts, $query ) {
				if ( ! $query->get( 'better_search_query' ) ) {
					return $posts;
				}

				$query->found_posts = 0;

				return array();
			},
			99,
			2
		);

		$ability = wp_get_ability( 'better-search/search' );

		$prose = $ability->execute(
			array( 'query' => 'Where can I find information about tiger facts and conservation' )
		);
		$this->assertWPError( $prose );
		$this->assertSame( 'bsearch_search_query_too_long', $prose->get_error_code() );

		// A short phrase that genuinely matches nothing must stay a clean empty result set,
		// so an agent can tell "absent" apart from "unmatchable".
		$short = $ability->execute( array( 'query' => 'zzzqqq' ) );
		$this->assertNotWPError( $short );
		$this->assertSame(
			array(
				'results' => array(),
				'total'   => 0,
			),
			$short
		);
	}

	public function test_prose_query_thresholds_are_filterable() {
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );

		$this->add_test_filter(
			'posts_pre_query',
			static function ( $posts, $query ) {
				if ( ! $query->get( 'better_search_query' ) ) {
					return $posts;
				}

				$query->found_posts = 0;

				return array();
			},
			99,
			2
		);
		$this->add_test_filter( 'bsearch_abilities_max_search_terms', static fn() => 99, 99 );
		$this->add_test_filter( 'bsearch_abilities_max_search_words', static fn() => 99, 99 );

		$this->assertNotWPError(
			wp_get_ability( 'better-search/search' )->execute(
				array( 'query' => 'Where can I find information about tiger facts and conservation' )
			)
		);
	}
}
