<?php

declare( strict_types=1 );

use WebberZone\Better_Search\Pro\Custom_Tables\Table_Manager;
use WebberZone\Better_Search\Pro\Custom_Tables\Sync_Manager;

/**
 * Tests for Sync_Manager — verifies the wz_posts custom table stays in sync
 * when posts are created, updated, or deleted.
 *
 * set_up_before_class() runs before the WP test-suite installs its
 * CREATE TABLE → CREATE TEMPORARY TABLE filter, so the wz_posts table is
 * created as a real InnoDB table (which supports FULLTEXT indexes).
 * Per-test data changes are rolled back by the WP transaction wrapper.
 */
class CustomTablesSyncTest extends WP_UnitTestCase {

	/** @var Table_Manager */
	private Table_Manager $table_manager;

	/** @var Sync_Manager */
	private Sync_Manager $sync_manager;

	public static function set_up_before_class(): void {
		parent::set_up_before_class();

		// Creates the real wz_posts table once for the whole test class.
		// The temp-table filter is not yet active at this point, so the
		// table is a real InnoDB table that supports FULLTEXT indexes.
		global $wpdb;
		$wpdb->hide_errors();
		$table_manager = new Table_Manager();
		$table_manager->create_tables();
		$wpdb->show_errors();
	}

	public static function tear_down_after_class(): void {
		// Drop the real table created for this test class.
		$table_manager = new Table_Manager();
		$table_manager->drop_tables();

		parent::tear_down_after_class();
	}

	public function set_up(): void {
		parent::set_up();

		// WP 6.8+ CI installations may be missing script-loader-react-refresh-entry.php.
		// This file is included (without @) from wp_default_scripts(), which is triggered
		// when do_blocks() runs inside sync_post() during the save_post hook chain.
		// The E_WARNING from the failed include is converted to a PHPUnit exception.
		// Suppress only that specific warning so it doesn't count as a test failure.
		set_error_handler(
			static function ( int $errno, string $errstr ): bool {
				return E_WARNING === $errno && false !== strpos( $errstr, 'script-loader' );
			},
			E_WARNING
		);

		$this->table_manager = new Table_Manager();

		if ( ! $this->table_manager->tables_exist() ) {
			$this->markTestSkipped( 'Custom table could not be created (FULLTEXT not supported in this environment).' );
		}

		$this->sync_manager = new Sync_Manager( $this->table_manager );
	}

	public function tear_down(): void {
		restore_error_handler();
		// Explicit cleanup in case the WP transaction rollback doesn't cover
		// rows inserted outside of the WP transaction (e.g. sync_post calls).
		global $wpdb;
		$blog_id = get_current_blog_id();
		$wpdb->query( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->prepare(
				"DELETE FROM {$this->table_manager->content_table} WHERE blog_id = %d", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$blog_id
			)
		);

		parent::tear_down();
	}

	// -------------------------------------------------------------------------
	// Helpers
	// -------------------------------------------------------------------------

	/**
	 * Return the custom-table row for a post, or null if it doesn't exist.
	 */
	private function get_row( int $post_id ): ?object {
		global $wpdb;
		return $wpdb->get_row( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->prepare(
				"SELECT * FROM {$this->table_manager->content_table} WHERE ID = %d AND blog_id = %d", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$post_id,
				get_current_blog_id()
			)
		);
	}

	// -------------------------------------------------------------------------
	// Tests
	// -------------------------------------------------------------------------

	/** Publishing a post inserts a row in the custom table. */
	public function test_publish_post_inserts_row(): void {
		$post_id = self::factory()->post->create(
			array(
				'post_title'   => 'Custom Tables Sync Test Post',
				'post_content' => 'This content should be indexed in the custom table.',
				'post_status'  => 'publish',
			)
		);

		$post = get_post( $post_id );
		$this->sync_manager->sync_post( $post_id, $post );

		$row = $this->get_row( $post_id );

		$this->assertNotNull( $row, 'Expected a row to be inserted.' );
		$this->assertSame( 'Custom Tables Sync Test Post', $row->title );
		$this->assertStringContainsString( 'indexed in the custom table', $row->content );
	}

	/** Updating a published post updates the existing row (no duplicate). */
	public function test_update_post_updates_row(): void {
		$post_id = self::factory()->post->create(
			array(
				'post_title'  => 'Original Title',
				'post_status' => 'publish',
			)
		);

		$post = get_post( $post_id );
		$this->sync_manager->sync_post( $post_id, $post );

		wp_update_post(
			array(
				'ID'         => $post_id,
				'post_title' => 'Updated Title',
			)
		);
		$post = get_post( $post_id );
		$this->sync_manager->sync_post( $post_id, $post );

		$row = $this->get_row( $post_id );

		$this->assertNotNull( $row );
		$this->assertSame( 'Updated Title', $row->title );

		// Confirm there is exactly one row (no duplicate).
		global $wpdb;
		$count = (int) $wpdb->get_var( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$this->table_manager->content_table} WHERE ID = %d AND blog_id = %d", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$post_id,
				get_current_blog_id()
			)
		);
		$this->assertSame( 1, $count );
	}

	/** Unpublishing a post removes its row from the custom table. */
	public function test_unpublish_post_removes_row(): void {
		$post_id = self::factory()->post->create(
			array(
				'post_title'  => 'Post To Unpublish',
				'post_status' => 'publish',
			)
		);

		$post = get_post( $post_id );
		$this->sync_manager->sync_post( $post_id, $post );

		$this->assertNotNull( $this->get_row( $post_id ), 'Row should exist before unpublish.' );

		// Set to draft — sync_post should delete the row.
		wp_update_post(
			array(
				'ID'          => $post_id,
				'post_status' => 'draft',
			)
		);
		$post = get_post( $post_id );
		$this->sync_manager->sync_post( $post_id, $post );

		$this->assertNull( $this->get_row( $post_id ), 'Row should be removed after unpublish.' );
	}

	/** Deleting a post removes its row from the custom table. */
	public function test_delete_post_removes_row(): void {
		$post_id = self::factory()->post->create(
			array(
				'post_title'  => 'Post To Delete',
				'post_status' => 'publish',
			)
		);

		$post = get_post( $post_id );
		$this->sync_manager->sync_post( $post_id, $post );

		$this->assertNotNull( $this->get_row( $post_id ), 'Row should exist before deletion.' );

		$this->sync_manager->delete_post( $post_id );

		$this->assertNull( $this->get_row( $post_id ), 'Row should be removed after deletion.' );
	}

	/** Revisions and autosaves must not be synced. */
	public function test_revision_is_skipped(): void {
		$post_id     = self::factory()->post->create( array( 'post_status' => 'publish' ) );
		$revision_id = wp_save_post_revision( $post_id );

		if ( ! $revision_id ) {
			$this->markTestSkipped( 'Post revisions are disabled in this environment.' );
		}

		$revision = get_post( $revision_id );
		$this->sync_manager->sync_post( $revision_id, $revision );

		$this->assertNull( $this->get_row( $revision_id ), 'Revision must not create a custom table row.' );
	}
}
