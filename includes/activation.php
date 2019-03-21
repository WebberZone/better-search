<?php
/**
 * Activation functions
 *
 * @package Better_Search
 */

// If this file is called directly, then abort execution.
if ( ! defined( 'WPINC' ) ) {
	die( "Aren't you supposed to come here via WP-Admin?" );
}

/**
 * Fired for each blog when the plugin is activated.
 *
 * @since   1.0
 *
 * @param    boolean $network_wide    True if WPMU superadmin uses
 *                                    "Network Activate" action, false if
 *                                    WPMU is disabled or plugin is
 *                                    activated on an individual blog.
 */
function bsearch_install( $network_wide ) {
	global $wpdb;

	if ( is_multisite() && $network_wide ) {

		// Get all blogs in the network and activate plugin on each one.
		$blog_ids = $wpdb->get_col( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
			"
        	SELECT blog_id FROM $wpdb->blogs
			WHERE archived = '0' AND spam = '0' AND deleted = '0'
		"
		);
		foreach ( $blog_ids as $blog_id ) {
			switch_to_blog( $blog_id );
			bsearch_single_activate();
		}

		// Switch back to the current blog.
		restore_current_blog();

	} else {
		bsearch_single_activate();
	}
}
register_activation_hook( BETTER_SEARCH_PLUGIN_FILE, 'bsearch_install' );


/**
 * Create tables to store pageviews.
 *
 * @since   2.0.0
 */
function bsearch_single_activate() {
	global $wpdb, $bsearch_db_version;

	$bsearch_settings = bsearch_read_options();

	// Create full text index.
	$wpdb->hide_errors();
	$wpdb->query( 'ALTER TABLE ' . $wpdb->posts . ' ADD FULLTEXT bsearch (post_title, post_content);' ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery.SchemaChange
	$wpdb->query( 'ALTER TABLE ' . $wpdb->posts . ' ADD FULLTEXT bsearch_title (post_title);' ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery.SchemaChange
	$wpdb->query( 'ALTER TABLE ' . $wpdb->posts . ' ADD FULLTEXT bsearch_content (post_content);' );// phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery.SchemaChange
	$wpdb->show_errors();

	// Create the tables.
	$table_name       = $wpdb->prefix . 'bsearch';
	$table_name_daily = $wpdb->prefix . 'bsearch_daily';

	if ( $wpdb->get_var( "show tables like '$table_name'" ) !== $table_name ) { // phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.PreparedSQL.NotPrepared

		$sql = 'CREATE TABLE ' . $table_name . ' (
            accessedid int NOT NULL AUTO_INCREMENT,
            searchvar VARCHAR(100) NOT NULL,
            cntaccess int NOT NULL,
            PRIMARY KEY  (accessedid)
        );';

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );

		$wpdb->hide_errors();
		$wpdb->query( 'CREATE INDEX IDX_searhvar ON ' . $table_name . ' (searchvar)' ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery.SchemaChange
		$wpdb->show_errors();

		add_option( 'bsearch_db_version', $bsearch_db_version );
	}

	if ( $wpdb->get_var( "show tables like '$table_name_daily'" ) !== $table_name_daily ) {  // phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.PreparedSQL.NotPrepared

		$sql = 'CREATE TABLE ' . $table_name_daily . ' (
            accessedid int NOT NULL AUTO_INCREMENT,
            searchvar VARCHAR(100) NOT NULL,
            cntaccess int NOT NULL,
            dp_date date NOT NULL,
            PRIMARY KEY  (accessedid)
        );';

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );

		$wpdb->hide_errors();
		$wpdb->query( 'CREATE INDEX IDX_searhvar ON ' . $table_name_daily . ' (searchvar)' );  // phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery.SchemaChange
		$wpdb->show_errors();

		add_option( 'bsearch_db_version', $bsearch_db_version );
	}

	// Upgrade table code.
	$installed_ver = get_option( 'bsearch_db_version' );

	if ( $installed_ver != $bsearch_db_version ) { //phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison

		$sql = 'CREATE TABLE ' . $table_name . ' (
            accessedid int NOT NULL AUTO_INCREMENT,
            searchvar VARCHAR(100) NOT NULL,
            cntaccess int NOT NULL,
            PRIMARY KEY  (accessedid)
        );';

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );

		$wpdb->hide_errors();
		$wpdb->query( 'ALTER ' . $table_name . ' DROP INDEX IDX_searhvar ' ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery.SchemaChange
		$wpdb->query( 'CREATE INDEX IDX_searhvar ON ' . $table_name . ' (searchvar)' ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery.SchemaChange
		$wpdb->show_errors();

		$sql = "DROP TABLE $table_name_daily";
		$wpdb->query( $sql ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery.SchemaChange

		$sql = 'CREATE TABLE ' . $table_name_daily . ' (
            accessedid int NOT NULL AUTO_INCREMENT,
            searchvar VARCHAR(100) NOT NULL,
            cntaccess int NOT NULL,
            dp_date date NOT NULL,
            PRIMARY KEY  (accessedid)
        );';

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );

		$wpdb->hide_errors();
		$wpdb->query( 'ALTER ' . $table_name_daily . ' DROP INDEX IDX_searhvar ' ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery.SchemaChange
		$wpdb->query( 'CREATE INDEX IDX_searhvar ON ' . $table_name_daily . ' (searchvar)' ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery.SchemaChange
		$wpdb->show_errors();

		update_option( 'bsearch_db_version', $bsearch_db_version );
	}

}


/**
 * Fired when a new site is activated with a WPMU environment.
 *
 * @since   2.0.0
 *
 * @param    int $blog_id    ID of the new blog.
 */
function bsearch_activate_new_site( $blog_id ) {

	if ( 1 !== did_action( 'wpmu_new_blog' ) ) {
		return;
	}

	switch_to_blog( $blog_id );
	bsearch_single_activate();
	restore_current_blog();

}
add_action( 'wpmu_new_blog', 'bsearch_activate_new_site' );


/**
 * Fired when a site is deleted in a WPMU environment.
 *
 * @since   2.0.0
 *
 * @param    array $tables    Tables in the blog.
 */
function bsearch_on_delete_blog( $tables ) {
	global $wpdb;

	$tables[] = $wpdb->prefix . 'bsearch';
	$tables[] = $wpdb->prefix . 'bsearch_daily';

	return $tables;
}
add_filter( 'wpmu_drop_tables', 'bsearch_on_delete_blog' );


