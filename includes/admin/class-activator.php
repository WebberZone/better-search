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
		add_filter( 'wpmu_drop_tables', array( __CLASS__, 'on_delete_blog' ) );
		add_action( 'plugins_loaded', array( __CLASS__, 'update_db_check' ) );
		add_action( 'wp_initialize_site', array( __CLASS__, 'activate_new_site' ) );
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
		global $wpdb, $bsearch_db_version;

		$charset_collate = $wpdb->get_charset_collate();
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		// Create FULLTEXT indexes.
		$wpdb->hide_errors();
		$wpdb->query( 'START TRANSACTION' ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->query( 'ALTER TABLE ' . $wpdb->posts . ' ADD FULLTEXT bsearch (post_title, post_content);' ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.SchemaChange
		$wpdb->query( 'ALTER TABLE ' . $wpdb->posts . ' ADD FULLTEXT bsearch_title (post_title);' ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.SchemaChange
		$wpdb->query( 'ALTER TABLE ' . $wpdb->posts . ' ADD FULLTEXT bsearch_content (post_content);' ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.SchemaChange
		$wpdb->query( 'COMMIT' ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->show_errors();

		$table_name       = $wpdb->base_prefix . 'bsearch';
		$table_name_daily = $wpdb->base_prefix . 'bsearch_daily';

		if ( $wpdb->get_var( "show tables like '$table_name'" ) != $table_name ) { // phpcs:ignore Universal.Operators.StrictComparisons.LooseNotEqual, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery

			$sql = 'CREATE TABLE ' . $table_name . // phpcs:ignore WordPress.DB.DirectDatabaseQuery.SchemaChange
			" (
				accessedid int NOT NULL AUTO_INCREMENT,
				searchvar VARCHAR(100) NOT NULL,
				cntaccess int NOT NULL,
				PRIMARY KEY  (accessedid)
			) $charset_collate;";

			dbDelta( $sql );

			$wpdb->hide_errors();
			$wpdb->query( 'CREATE INDEX IDX_searhvar ON ' . $table_name . ' (searchvar)' ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery.SchemaChange
			$wpdb->show_errors();

			update_option( 'bsearch_db_version', $bsearch_db_version );
		}

		if ( $wpdb->get_var( "show tables like '$table_name_daily'" ) != $table_name_daily ) { // phpcs:ignore Universal.Operators.StrictComparisons.LooseNotEqual, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery

			$sql = 'CREATE TABLE ' . $table_name_daily . // phpcs:ignore WordPress.DB.DirectDatabaseQuery.SchemaChange
			" (
				accessedid int NOT NULL AUTO_INCREMENT,
				searchvar VARCHAR(100) NOT NULL,
				cntaccess int NOT NULL,
				dp_date date NOT NULL,
				PRIMARY KEY  (accessedid)
			) $charset_collate;";

			dbDelta( $sql );

			$wpdb->hide_errors();
			$wpdb->query( 'CREATE INDEX IDX_searhvar ON ' . $table_name_daily . ' (searchvar)' );  // phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery.SchemaChange
			$wpdb->show_errors();

			update_option( 'bsearch_db_version', $bsearch_db_version );
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
	 * @since   1.9
	 */
	public static function update_db_check() {
		global $bsearch_db_version, $network_wide;

		if ( get_site_option( 'bsearch_db_version' ) != $bsearch_db_version ) { // phpcs:ignore Universal.Operators.StrictComparisons.LooseNotEqual
			self::activation_hook( $network_wide );
		}
	}
}
