<?php

declare( strict_types=1 );

use WebberZone\Better_Search\Tracker;

/**
 * Tests for Tracker::update_count() — verifies search terms are recorded
 * in the bsearch / bsearch_daily tables and that counts increment correctly.
 */
class TrackerTest extends WP_UnitTestCase {

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
		// Clean up tracking rows inserted during tests.
		$wpdb->query( "DELETE FROM {$this->table} WHERE searchvar LIKE 'phpunit_%'" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->query( "DELETE FROM {$this->table_daily} WHERE searchvar LIKE 'phpunit_%'" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching

		parent::tear_down();
	}

	// -------------------------------------------------------------------------
	// Helpers
	// -------------------------------------------------------------------------

	/**
	 * Fetch the overall count for a search term.
	 */
	private function get_count( string $term ): int {
		global $wpdb;
		return (int) $wpdb->get_var( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->prepare(
				"SELECT cntaccess FROM {$this->table} WHERE searchvar = %s", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$term
			)
		);
	}

	// -------------------------------------------------------------------------
	// Tests
	// -------------------------------------------------------------------------

	/** Recording a new term creates a row with count = 1. */
	public function test_first_record_creates_row(): void {
		$term = 'phpunit_first_record';

		Tracker::update_count( $term );

		$this->assertSame( 1, $this->get_count( $term ) );
	}
}
