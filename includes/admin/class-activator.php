<?php
/**
 * Functions run on activation / deactivation.
 *
 * @package Better_Search
 */

namespace WebberZone\Better_Search\Admin;

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Admin Columns Class.
 *
 * @since 3.3.0
 */
class Activator {

	/**
	 * Constructor class.
	 *
	 * @since 3.3.0
	 */
	public function __construct() {
		add_filter( 'wpmu_drop_tables', array( $this, 'on_delete_blog' ) );
		add_action( 'admin_init', array( $this, 'update_db_check' ) );
		add_action( 'wp_initialize_site', array( $this, 'activate_new_site' ) );
	}

	/**
	 * Fired when the plugin is Network Activated.
	 *
	 * @since 1.9.10.1
	 *
	 * @param    boolean $network_wide    True if WPMU superadmin uses
	 *                                    "Network Activate" action, false if
	 *                                    WPMU is disabled or plugin is
	 *                                    activated on an individual blog.
	 */
	public static function activation_hook( $network_wide ) {

		if ( is_multisite() && $network_wide ) {
			$sites = get_sites(
				array(
					'archived' => 0,
					'spam'     => 0,
					'deleted'  => 0,
				)
			);

			foreach ( $sites as $site ) {
				switch_to_blog( (int) $site->blog_id );
				self::single_activate();
			}

			// Switch back to the current blog.
			restore_current_blog();

		} else {
			self::single_activate();
		}
	}

	/**
	 * Fired for each blog when the plugin is activated.
	 *
	 * @since 2.0.0
	 */
	public static function single_activate() {
		global $wpdb;

		$table_name       = $wpdb->prefix . 'bsearch';
		$table_name_daily = $wpdb->prefix . 'bsearch_daily';

		// Create FULLTEXT indexes.
		$wpdb->hide_errors();
		self::create_fulltext_indexes();
		$wpdb->show_errors();

		// Create tables if not exists.
		self::maybe_create_table( $table_name, self::create_full_table_sql() );
		self::maybe_create_table( $table_name_daily, self::create_full_daily_table_sql() );

		// Upgrade table code for 2.0.0.
		$current_db_version = get_option( 'bsearch_db_version' );

		if ( version_compare( $current_db_version, BETTER_SEARCH_DB_VERSION, '<' ) ) {
			self::recreate_overall_table();
			self::recreate_daily_table();
			update_option( 'bsearch_db_version', BETTER_SEARCH_DB_VERSION );
		}
	}

	/** Create fulltext indexes on the posts table.
	 *
	 * @since 3.3.0
	 */
	public static function create_fulltext_indexes() {
		global $wpdb;

		$indexes = array(
			'bsearch'         => '(post_title, post_content)',
			'bsearch_title'   => '(post_title)',
			'bsearch_content' => '(post_content)',
		);

		foreach ( $indexes as $index => $columns ) {
			$index_exists = $wpdb->get_var( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
				$wpdb->prepare(
					"SHOW INDEX FROM {$wpdb->posts} WHERE Key_name = %s",
					$index
				)
			);

			if ( ! $index_exists ) {
				$wpdb->query( 'ALTER TABLE ' . $wpdb->posts . ' ADD FULLTEXT ' . $index . ' ' . $columns . ';' ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.SchemaChange,WordPress.DB.PreparedSQL.NotPrepared
			}
		}
	}

	/**
	 * Create table if not exists.
	 *
	 * @since 3.3.0
	 *
	 * @param string $table_name Table name.
	 * @param string $sql        SQL to create the table.
	 */
	public static function maybe_create_table( $table_name, $sql ) {
		global $wpdb;
		if ( $wpdb->get_var( "SHOW TABLES LIKE '{$table_name}'" ) !== $table_name ) { // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery
			require_once ABSPATH . 'wp-admin/includes/upgrade.php';

			$wpdb->hide_errors();
			dbDelta( $sql );
			$wpdb->show_errors();

			update_option( 'bsearch_db_version', BETTER_SEARCH_DB_VERSION );
		}
	}

	/**
	 * Create full table sql.
	 *
	 * @since 3.3.0
	 *
	 * @return string SQL to create the full table.
	 */
	public static function create_full_table_sql() {
		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE {$wpdb->prefix}bsearch" . // phpcs:ignore WordPress.DB.DirectDatabaseQuery.SchemaChange
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
	 * @since 3.3.0
	 *
	 * @return string SQL to create the daily table.
	 */
	public static function create_full_daily_table_sql() {
		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE {$wpdb->prefix}bsearch_daily" . // phpcs:ignore WordPress.DB.DirectDatabaseQuery.SchemaChange
		" (
			searchvar VARCHAR(100) NOT NULL,
			cntaccess int NOT NULL,
			dp_date date NOT NULL,
			PRIMARY KEY  (searchvar, dp_date)
		) $charset_collate;";

		return $sql;
	}

	/**
	 * Recreate overall table.
	 *
	 * @since 3.3.0
	 *
	 * @param bool $backup Whether to backup the table or not.
	 */
	public static function recreate_overall_table( $backup = true ) {
		global $wpdb;

		$table_name        = $wpdb->prefix . 'bsearch';
		$backup_table_name = $table_name . '_backup';

		if ( $backup ) {
			$success = $wpdb->query( "CREATE TABLE $backup_table_name LIKE $table_name" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.SchemaChange,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			if ( false !== $success ) {
				$wpdb->query( "INSERT INTO $backup_table_name SELECT * FROM $table_name" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			}
		} else {
			// Create a temporary table and store the data.
			$wpdb->query(  // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
				"
			CREATE TEMPORARY TABLE $backup_table_name AS " . // phpcs:ignore WordPress.DB.DirectDatabaseQuery.SchemaChange,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				"SELECT searchvar, SUM(cntaccess) as cntaccess
			FROM $table_name " . // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				'GROUP BY searchvar
			'
			);
		}

		$wpdb->query( "DROP TABLE IF EXISTS $table_name" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.SchemaChange,WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		self::maybe_create_table( $table_name, self::create_full_table_sql() );

		// Insert the data back into the table.
		$wpdb->query( "INSERT INTO $table_name (searchvar, cntaccess ) SELECT bs.searchvar, bs.cntaccess FROM $backup_table_name AS bs ON DUPLICATE KEY UPDATE $table_name.cntaccess = $table_name.cntaccess + VALUES(cntaccess);" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		if ( ! $backup ) {
			$wpdb->query( "DROP TABLE $backup_table_name" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.SchemaChange,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		}
	}

	/**
	 * Recreate daily table.
	 *
	 * @since 3.3.0
	 *
	 * @param bool $backup Whether to backup the table or not.
	 */
	public static function recreate_daily_table( $backup = true ) {
		global $wpdb;

		$table_name        = $wpdb->prefix . 'bsearch_daily';
		$backup_table_name = $table_name . '_backup';

		if ( $backup ) {
			$success = $wpdb->query( "CREATE TABLE $backup_table_name LIKE $table_name" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.SchemaChange,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			if ( false !== $success ) {
				$wpdb->query( "INSERT INTO $backup_table_name SELECT * FROM $table_name" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			}
		} else {
			// Create a temporary table and store the data.
			$wpdb->query(  // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
				"
				CREATE TEMPORARY TABLE $backup_table_name AS " . // phpcs:ignore WordPress.DB.DirectDatabaseQuery.SchemaChange,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				"SELECT searchvar, dp_date, SUM(cntaccess) as cntaccess
				FROM $table_name " . // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				'GROUP BY searchvar, dp_date
			'
			);
		}

		$wpdb->query( "DROP TABLE IF EXISTS $table_name" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.SchemaChange,WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		self::maybe_create_table( $table_name, self::create_full_daily_table_sql() );

		// Insert the data back into the table.
		$wpdb->query( "INSERT INTO $table_name (searchvar, dp_date, cntaccess ) SELECT bs.searchvar, bs.dp_date, bs.cntaccess FROM $backup_table_name AS bs ON DUPLICATE KEY UPDATE $table_name.cntaccess = $table_name.cntaccess + VALUES(cntaccess);" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		if ( ! $backup ) {
			$wpdb->query( "DROP TABLE $backup_table_name" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.SchemaChange,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		}
	}

	/**
	 * Fired when a new site is activated with a WPMU environment.
	 *
	 * @since 2.0.0
	 *
	 * @param  int|\WP_Site $blog WordPress 5.1 passes a WP_Site object.
	 */
	public static function activate_new_site( $blog ) {

		if ( ! is_plugin_active_for_network( plugin_basename( BETTER_SEARCH_PLUGIN_FILE ) ) ) {
			return;
		}

		if ( ! is_int( $blog ) ) {
			$blog = $blog->id;
		}

		switch_to_blog( $blog );
		self::single_activate();
		restore_current_blog();
	}

	/**
	 * Fired when a site is deleted in a WPMU environment.
	 *
	 * @since 2.0.0
	 *
	 * @param    array $tables    Tables in the blog.
	 */
	public static function on_delete_blog( $tables ) {
		global $wpdb;

		$tables[] = $wpdb->prefix . 'bsearch';
		$tables[] = $wpdb->prefix . 'bsearch_daily';

		return $tables;
	}

	/**
	 * Function to call install function if needed.
	 *
	 * @since 3.3.0
	 */
	public static function update_db_check() {
		global $network_wide;

		$current_db_version = get_option( 'bsearch_db_version' );

		if ( version_compare( $current_db_version, BETTER_SEARCH_DB_VERSION, '<' ) ) {
			self::activation_hook( $network_wide );
		}
	}
}
