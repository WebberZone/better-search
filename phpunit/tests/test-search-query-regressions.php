<?php

declare( strict_types=1 );

class SearchQueryRegressionsTest extends WP_UnitTestCase {

	public function test_like_taxonomy_search_matches_term_descriptions(): void {
		$id   = self::factory()->post->create( array( 'post_title' => 'anchor', 'post_status' => 'publish' ) );
		$term = wp_insert_term( 'label', 'category', array( 'description' => 'needle description' ) );
		$this->assertIsArray( $term );
		wp_set_post_terms( $id, array( $term['term_id'] ), 'category' );

		$query = new Better_Search_Query(
			array(
				's'                  => 'needle',
				'post_type'          => 'post',
				'post_status'        => 'publish',
				'posts_per_page'     => 10,
				'use_custom_tables'   => false,
				'use_fulltext'       => false,
				'search_taxonomies'   => true,
				'search_excerpt'      => false,
				'search_meta'        => false,
				'search_authors'     => false,
				'search_comments'    => false,
				'fuzzy_search_level' => 'off',
			)
		);

		$this->assertSame( array( $id ), wp_list_pluck( $query->posts, 'ID' ) );
	}

	public function test_native_query_field_weights_boost_meta_matches(): void {
		$boosted = self::factory()->post->create( array( 'post_title' => 'anchor one', 'post_status' => 'publish' ) );
		$plain   = self::factory()->post->create( array( 'post_title' => 'anchor two', 'post_status' => 'publish' ) );
		add_post_meta( $boosted, 'allowed', 'anchor' );

		$args = array(
			's'                  => 'anchor',
			'post_type'          => 'post',
			'post_status'        => 'publish',
			'posts_per_page'     => 10,
			'use_custom_tables'  => false,
			'use_fulltext'       => false,
			'search_taxonomies'  => false,
			'search_excerpt'     => false,
			'search_meta'        => true,
			'search_authors'     => false,
			'search_comments'    => false,
			'fuzzy_search_level' => 'off',
			'weight_meta'        => 500,
		);

		$query = new Better_Search_Query( $args );
		$this->assertSame( $boosted, (int) $query->posts[0]->ID );

		$args['weight_meta'] = 0;
		$unweighted          = new Better_Search_Query( $args );
		$this->assertContains( $plain, wp_list_pluck( $unweighted->posts, 'ID' ) );
	}

	public function test_topscore_callback_ignores_another_query_instance(): void {
		$core = new Better_Search_Core_Query( array( 'is_better_search_loaded' => true ) );
		$owner = ( new ReflectionClass( WP_Query::class ) )->newInstanceWithoutConstructor();
		$other = ( new ReflectionClass( WP_Query::class ) )->newInstanceWithoutConstructor();
		$core->set_owner( $owner );
		$other->topscore = 23;

		$this->assertSame( 'SELECT 1', $core->set_topscore( 'SELECT 1', $other, $core ) );
		$this->assertSame( 23, $other->topscore );
	}

	public function test_posts_from_different_sites_with_the_same_id_are_kept(): void {
		$here  = get_post( self::factory()->post->create() );
		$other = clone $here;

		$other->blog_id = get_current_blog_id() + 1;

		$this->assertCount( 2, WebberZone\Better_Search\Frontend\Language_Handler::translate_ids( array( $here, $other ) ) );
		$this->assertCount( 1, WebberZone\Better_Search\Frontend\Language_Handler::translate_ids( array( $here, $here ) ) );
	}
}
