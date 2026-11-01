<?php

declare( strict_types=1 );

use WebberZone\Better_Search\Util\Cache;

class EmptyResultCacheTest extends WP_UnitTestCase {

	private function search( array $args = array() ): Better_Search_Query {
		return new Better_Search_Query(
			array_merge(
				array(
					's'                 => 'emptycacheuniqueterm',
					'post_type'         => 'post',
					'post_status'       => 'publish',
					'posts_per_page'    => 10,
					'use_custom_tables' => false,
					'use_fulltext'      => false,
					'cache'             => true,
					'cache_results'     => false,
				),
				$args
			)
		);
	}

	public function test_empty_results_are_reused_without_returning_unrelated_posts(): void {
		self::factory()->post->create( array( 'post_title' => 'Unrelated article' ) );
		$cold = $this->search();
		$this->assertSame( array(), $cold->posts );
		wp_cache_flush();
		$executed = array();
		$record = static function ( $sql ) use ( &$executed ) {
			$executed[] = $sql;
			return $sql;
		};
		add_filter( 'query', $record );
		try {
			$warm = $this->search();
		} finally {
			remove_filter( 'query', $record );
		}
		$this->assertSame( array(), $warm->posts );
		$this->assertSame( 0, (int) $warm->found_posts );
		$this->assertSame( 0, (int) $warm->max_num_pages );
		$this->assertSame( 0.0, (float) $warm->topscore );
		$this->assertSame( array(), array_values( preg_grep( '/SELECT[\s\S]*FROM[\s\S]*posts/i', $executed ) ) );
	}

	public function test_publishing_a_matching_post_invalidates_empty_results(): void {
		$id = self::factory()->post->create( array( 'post_title' => 'emptycacheuniqueterm', 'post_status' => 'draft' ) );
		$this->assertSame( array(), $this->search()->posts );
		$this->assertSame( array(), $this->search()->posts );
		wp_update_post( array( 'ID' => $id, 'post_status' => 'publish' ) );
		$this->assertSame( array( $id ), wp_list_pluck( $this->search()->posts, 'ID' ) );
	}
}
