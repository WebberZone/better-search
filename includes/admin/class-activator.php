<?php
/**
 * Functions run on activation / deactivation.
 *
 * @package Better_Search
 */

namespace WebberZone\Better_Search\Admin;

use WebberZone\Better_Search\Util\Hook_Registry;
use WebberZone\Better_Search\Db;

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Activator class
 *
 * @since 3.3.0
 */
class Activator {

	/**
	 * Name of the main table.
	 *
	 * @since 4.0.0
	 * @var string
	 */
	public static $table_name = 'bsearch';

	/**
	 * Name of the daily table.
	 *
	 * @since 4.0.0
	 * @var string
	 */
	public static $table_name_daily = 'bsearch_daily';

	/**
	 * Constructor class.
	 *
	 * @since 3.3.0
	 */
	public function __construct() {
		Hook_Registry::add_filter( 'wpmu_drop_tables', array( $this, 'on_delete_blog' ) );
		Hook_Registry::add_action( 'wp_initialize_site', array( $this, 'activate_new_site' ) );
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

		$table_name       = $wpdb->prefix . self::$table_name;
		$table_name_daily = $wpdb->prefix . self::$table_name_daily;

		// Create FULLTEXT indexes.
		$wpdb->hide_errors();
		Db::create_fulltext_indexes();
		$wpdb->show_errors();

		// Create tables if not exists.
		Db::maybe_create_table( $table_name, Db::create_full_table_sql() );
		Db::maybe_create_table( $table_name_daily, Db::create_daily_table_sql() );

		// Upgrade table code for 2.0.0.
		$current_db_version = get_option( 'bsearch_db_version' );

		if ( version_compare( $current_db_version, BETTER_SEARCH_DB_VERSION, '<' ) ) {
			Db::recreate_overall_table();
			Db::recreate_daily_table();
			update_option( 'bsearch_db_version', BETTER_SEARCH_DB_VERSION );
		}

		/**
		 * Fires after the plugin has been activated.
		 *
		 * @since 4.0.0
		 */
		do_action( 'bsearch_activate' );
	}

	/**
	 * Fired for each blog when the plugin is deactivated.
	 *
	 * @since 4.2.0
	 */
	public static function single_deactivate() {
		$settings = get_option( 'bsearch_settings' );

		if ( ! empty( $settings['uninstall_indices_deactivate'] ) ) {
			Db::delete_fulltext_indexes();
			delete_option( 'bsearch_db_version' );
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

		$tables[] = $wpdb->prefix . self::$table_name;
		$tables[] = $wpdb->prefix . self::$table_name_daily;

		return $tables;
	}
}
