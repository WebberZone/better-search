<?php

declare( strict_types=1 );

/**
 * Tests for the heatmap functions — verifies DB data is surfaced correctly
 * via get_bsearch_heatmap_counts() and get_bsearch_heatmap().
 */
class HeatmapTest extends WP_UnitTestCase {

	/** @var string */
	private string $table;

	/** @var string */
	private string $table_daily;

	public function set_up(): void {
		parent::set_up();

		global $wpdb;
		$this->table       = $wpdb->prefix . 'bsearch';
		$this->table_daily = $wpdb->prefix . 'bsearch_daily';
	}

	public function tear_down(): void {
		global $wpdb;
		$wpdb->query( "DELETE FROM {$this->table} WHERE searchvar LIKE 'phpunit_%'" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->query( "DELETE FROM {$this->table_daily} WHERE searchvar LIKE 'phpunit_%'" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching

		parent::tear_down();
	}

	// -------------------------------------------------------------------------
	// Helpers
	// -------------------------------------------------------------------------

	/** Insert rows directly into the overall bsearch table. */
	private function insert_overall( string $term, int $count ): void {
		global $wpdb;
		$wpdb->query( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->prepare(
				"INSERT INTO {$this->table} (searchvar, cntaccess) VALUES (%s, %d) ON DUPLICATE KEY UPDATE cntaccess = %d", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$term,
				$count,
				$count
			)
		);
	}

	/** Insert rows directly into the bsearch_daily table. */
	private function insert_daily( string $term, int $count, string $date ): void {
		global $wpdb;
		$wpdb->query( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->prepare(
				"INSERT INTO {$this->table_daily} (searchvar, cntaccess, dp_date) VALUES (%s, %d, %s) ON DUPLICATE KEY UPDATE cntaccess = %d", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$term,
				$count,
				$date,
				$count
			)
		);
	}

	// -------------------------------------------------------------------------
	// Tests
	// -------------------------------------------------------------------------

	/** get_bsearch_heatmap_counts() returns the inserted terms. */
	public function test_counts_returns_inserted_terms(): void {
		$this->insert_overall( 'phpunit_alpha', 10 );
		$this->insert_overall( 'phpunit_beta', 5 );

		$results = get_bsearch_heatmap_counts(
			array(
				'daily'  => false,
				'number' => 10,
			)
		);

		$names = wp_list_pluck( $results, 'name' );

		$this->assertContains( 'phpunit_alpha', $names );
		$this->assertContains( 'phpunit_beta', $names );
	}

	/** Daily mode returns terms from within the date range, not older ones. */
	public function test_daily_mode_respects_date_range(): void {
		$today     = gmdate( 'Y-m-d' );
		$old_date  = gmdate( 'Y-m-d', strtotime( '-10 days' ) );

		$this->insert_daily( 'phpunit_recent', 5, $today );
		$this->insert_daily( 'phpunit_old', 5, $old_date );

		$results = get_bsearch_heatmap_counts(
			array(
				'daily'       => true,
				'daily_range' => 3,
				'number'      => 20,
			)
		);

		$names = wp_list_pluck( $results, 'name' );

		$this->assertContains( 'phpunit_recent', $names );
		$this->assertNotContains( 'phpunit_old', $names );
	}
}
