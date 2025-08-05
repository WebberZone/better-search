<?php
/**
 * Database operations for Better Search.
 *
 * @package Better_Search
 */

namespace WebberZone\Better_Search;

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Database class
 *
 * @since 4.2.0
 */
class Db {

	/**
	 * Name of the main table.
	 *
	 * @since 4.2.0
	 * @var string
	 */
	public static $table_name = 'bsearch';

	/**
	 * Name of the daily table.
	 *
	 * @since 4.2.0
	 * @var string
	 */
	public static $table_name_daily = 'bsearch_daily';

	/**
	 * Create fulltext indexes on the posts table.
	 *
	 * @since 3.3.0
	 */
	public static function create_fulltext_indexes() {
		// Get the list of fulltext indexes.
		$indexes = self::get_fulltext_indexes();

		// Loop through the indexes and create them if not exist.
		foreach ( $indexes as $index => $columns ) {
			if ( ! self::is_index_installed( $index ) ) {
				self::install_fulltext_index( $index, $columns );
			}
		}
	}

	/**
	 * Delete the FULLTEXT index.
	 *
	 * @since 4.2.0
	 */
	public static function delete_fulltext_indexes() {
		global $wpdb;

		$indexes = array_merge( self::get_fulltext_indexes(), self::get_old_fulltext_indexes() );

		foreach ( $indexes as $index => $columns ) {
			if ( self::is_index_installed( $index ) ) {
				$index = esc_sql( $index );
				$wpdb->query( "ALTER TABLE {$wpdb->posts} DROP INDEX $index" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.SchemaChange,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			}
		}
	}

	/**
	 * Check if a fulltext index already exists on the posts table.
	 *
	 * @since 4.0.0
	 *
	 * @param string $index Index name.
	 * @return bool True if the index exists, false otherwise.
	 */
	public static function is_index_installed( $index ) {
		global $wpdb;

		$new_indexes = self::get_fulltext_indexes();
		$old_indexes = self::get_old_fulltext_indexes();

		// Find the corresponding old index name if the given index is a new one.
		$old_index_name = '';
		if ( in_array( $index, array_keys( $new_indexes ), true ) ) {
			$key            = array_search( $index, array_keys( $new_indexes ), true );
			$old_index_keys = array_keys( $old_indexes );
			if ( isset( $old_index_keys[ $key ] ) ) {
				$old_index_name = $old_index_keys[ $key ];
			}
		}

		$index_exists = $wpdb->get_var( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->prepare(
				"SHOW INDEX FROM {$wpdb->posts} WHERE Key_name = %s OR Key_name = %s",
				$index,
				$old_index_name
			)
		);

		return (bool) $index_exists;
	}

	/**
	 * Install a fulltext index on the posts table.
	 *
	 * @since 4.0.0
	 *
	 * @param string $index   Index name.
	 * @param string $columns Columns to be indexed.
	 * @return void
	 */
	public static function install_fulltext_index( $index, $columns ) {
		global $wpdb;

		// Install the fulltext index if it doesn't exist.
		$wpdb->query( "ALTER TABLE {$wpdb->posts} ADD FULLTEXT {$index} {$columns};" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.SchemaChange,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	}

	/**
	 * Get the list of fulltext indexes to be created on the posts table.
	 *
	 * @since 4.0.0
	 *
	 * @return array Array of fulltext indexes with their respective columns.
	 */
	public static function get_fulltext_indexes() {
		$indexes = array(
			'wz_title_content' => '(post_title, post_content)',
			'wz_title'         => '(post_title)',
			'wz_content'       => '(post_content)',
		);

		/**
		 * Filter the fulltext indexes.
		 *
		 * @since 4.0.0
		 *
		 * @param array $indexes Array of fulltext indexes.
		 */
		return apply_filters( 'bsearch_fulltext_indexes', $indexes );
	}

	/**
	 * Get the list of old fulltext indexes.
	 *
	 * @since 4.2.0
	 *
	 * @return array Array of fulltext indexes with their respective columns.
	 */
	public static function get_old_fulltext_indexes() {
		return array(
			'bsearch'         => '(post_title, post_content)',
			'bsearch_title'   => '(post_title)',
			'bsearch_content' => '(post_content)',
		);
	}

	/**
	 * Check the status of all fulltext indexes.
	 *
	 * @since 4.0.0
	 *
	 * @return array Array of index statuses indicating whether they are installed.
	 */
	public static function check_fulltext_indexes() {
		// Get the list of fulltext indexes.
		$indexes  = self::get_fulltext_indexes();
		$statuses = array();

		// Check if each index is installed and add to the report.
		foreach ( $indexes as $index => $columns ) {
			$is_installed = self::is_index_installed( $index );

			$statuses[ $index ] = array(
				'columns' => $columns,
				'status'  => $is_installed
					? '<span style="color: #006400;">' . __( 'Installed', 'better-search' ) . '</span>'
					: '<span style="color: #8B0000;">' . __( 'Not Installed', 'better-search' ) . '</span>',
			);
		}

		/**
		 * Filter the index statuses report.
		 *
		 * @since 4.0.0
		 *
		 * @param array $statuses Array of index statuses.
		 */
		return apply_filters( 'bsearch_fulltext_index_statuses', $statuses );
	}

	/**
	 * Check if all fulltext indexes are installed.
	 *
	 * @since 4.0.0
	 *
	 * @return bool True if all fulltext indexes are installed, false if any are missing.
	 */
	public static function is_fulltext_index_installed() {
		$indexes = self::get_fulltext_indexes();

		foreach ( $indexes as $index => $columns ) {
			if ( ! self::is_index_installed( $index ) ) {
				return false; // Return false if any index is missing.
			}
		}

		return true; // Return true if all indexes are installed.
	}

	/**
	 * Check if the Better Search table is installed.
	 *
	 * @since 4.0.2
	 *
	 * @param string $table_name Table name.
	 * @return bool True if the table exists, false otherwise.
	 */
	public static function is_table_installed( $table_name ) {
		global $wpdb;

		if ( $wpdb->get_var( "SHOW TABLES LIKE '{$table_name}'" ) === $table_name ) { // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery
			return true;
		}

		return false;
	}

	/**
	 * Create table if not exists.
	 *
	 * @since 4.2.0
	 *
	 * @param string $table_name Table name.
	 * @param string $sql        SQL to create the table.
	 */
	public static function maybe_create_table( $table_name, $sql ) {
		global $wpdb;
		if ( ! self::is_table_installed( $table_name ) ) {
			require_once ABSPATH . 'wp-admin/includes/upgrade.php';

			$wpdb->hide_errors();
			dbDelta( $sql );
			$wpdb->show_errors();
		}
	}

	/**
	 * Create tables.
	 *
	 * @since 4.2.0
	 */
	public static function create_tables() {
		self::maybe_create_table( self::$table_name, self::create_full_table_sql() );
		self::maybe_create_table( self::$table_name_daily, self::create_daily_table_sql() );
	}

	/**
	 * Create full table sql.
	 *
	 * @since 4.2.0
	 *
	 * @return string SQL to create the full table.
	 */
	public static function create_full_table_sql() {
		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();
		$table_name      = $wpdb->prefix . self::$table_name;

		$sql = "CREATE TABLE {$table_name}" . // phpcs:ignore WordPress.DB.DirectDatabaseQuery.SchemaChange
		" (
			searchvar VARCHAR(100) NOT NULL,
			cntaccess int NOT NULL,
			PRIMARY KEY  (searchvar)
		) $charset_collate;";

		return $sql;
	}

	/**
	 * Create full daily table sql.
	 *
	 * @since 4.2.0
	 *
	 * @return string SQL to create the daily table.
	 */
	public static function create_daily_table_sql() {
		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();
		$table_name      = $wpdb->prefix . self::$table_name_daily;

		$sql = "CREATE TABLE {$table_name}" . // phpcs:ignore WordPress.DB.DirectDatabaseQuery.SchemaChange
		" (
			searchvar VARCHAR(100) NOT NULL,
			cntaccess int NOT NULL,
			dp_date date NOT NULL,
			PRIMARY KEY  (searchvar, dp_date)
		) $charset_collate;";

		return $sql;
	}

	/**
	 * Recreate a table.
	 *
	 * This method recreates a table by creating a backup, dropping the original table,
	 * and then creating a new table with the original name and inserting the data from the backup.
	 *
	 * @since 4.2.0
	 *
	 * @param string $table_name        The name of the table to recreate.
	 * @param string $create_table_sql  The SQL statement to create the new table.
	 * @param bool   $backup            Whether to backup the table or not.
	 * @param array  $fields            The fields to include in the temporary table and on duplicate key code.
	 * @param array  $group_by_fields   The fields to group by in the temporary table.
	 *
	 * @return bool|\WP_Error True if recreated, error message if failed.
	 */
	public static function recreate_table( $table_name, $create_table_sql, $backup = true, $fields = array( 'searchvar', 'cntaccess' ), $group_by_fields = array( 'searchvar' ) ) {
		global $wpdb;

		$backup_table_name = $backup ? $table_name . '_backup' : $table_name . '_temp';
		$success           = false;

		$fields_sql          = implode( ', ', $fields );
		$fields_sql_with_sum = str_replace( 'cntaccess', 'SUM(cntaccess) as cntaccess', $fields_sql );
		$group_by_sql        = implode( ', ', $group_by_fields );

		if ( $backup ) {
			$success = $wpdb->query( "CREATE TABLE $backup_table_name LIKE $table_name" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.SchemaChange,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			if ( false !== $success ) {
				$success = $wpdb->query( "INSERT INTO $backup_table_name SELECT * FROM $table_name" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			} else {
				/* translators: 1: Site number, 2: Error message */
				return new \WP_Error( 'bsearch_database_backup_failed', sprintf( esc_html__( 'Database backup failed on site %1$s. Error message: %2$s', 'better-search' ), get_site_url(), $wpdb->last_error ) );
			}
		} else {
			$success = $wpdb->query( "CREATE TEMPORARY TABLE $backup_table_name AS SELECT $fields_sql_with_sum FROM $table_name GROUP BY $group_by_sql" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.SchemaChange,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		}

		if ( false !== $success ) {
			$wpdb->query( "DROP TABLE IF EXISTS $table_name" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.SchemaChange,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			self::maybe_create_table( $table_name, $create_table_sql );
			$insert_fields_sql = 'bs.' . implode( ', bs.', $fields );

			$success = $wpdb->query( "INSERT INTO $table_name ($fields_sql) SELECT $insert_fields_sql FROM $backup_table_name AS bs ON DUPLICATE KEY UPDATE $table_name.cntaccess = $table_name.cntaccess + VALUES(cntaccess)" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.InterpolatedNotPrepared

			if ( false === $success ) {
				/* translators: 1: Site number, 2: Error message */
				return new \WP_Error( 'bsearch_database_insert_failed', sprintf( esc_html__( 'Database insert failed on site %1$s. Error message: %2$s', 'better-search' ), get_site_url(), $wpdb->last_error ) );
			}
		}

		if ( ! $backup ) {
			$wpdb->query( "DROP TABLE $backup_table_name" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.SchemaChange,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		}

		return $success;
	}

	/**
	 * Recreate overall table.
	 *
	 * @since 4.2.0
	 *
	 * @param bool $backup Whether to backup the table or not.
	 *
	 * @return bool|\WP_Error True if recreated, error message if failed.
	 */
	public static function recreate_overall_table( $backup = true ) {
		global $wpdb;
		return self::recreate_table(
			$wpdb->prefix . self::$table_name,
			self::create_full_table_sql(),
			$backup
		);
	}

	/**
	 * Recreate daily table.
	 *
	 * @since 4.2.0
	 *
	 * @param bool $backup Whether to backup the table or not.
	 *
	 * @return bool|\WP_Error True if recreated, error message if failed.
	 */
	public static function recreate_daily_table( $backup = true ) {
		global $wpdb;
		return self::recreate_table(
			$wpdb->prefix . self::$table_name_daily,
			self::create_daily_table_sql(),
			$backup,
			array( 'searchvar', 'cntaccess', 'dp_date' ),
			array( 'searchvar', 'dp_date' )
		);
	}
}
