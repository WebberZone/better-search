<?php
/**
 * Activation functions
 *
 * @package Better_Search
 */


/**
 * Create tables to store pageviews.
 *
 * @since	2.0.0
 */
function bsearch_single_activate() {
	global $wpdb, $bsearch_db_version;

	$bsearch_settings = bsearch_read_options();

	// Create full text index
	$wpdb->hide_errors();
	$wpdb->query( 'ALTER TABLE ' . $wpdb->posts . ' ENGINE = MYISAM;' );
	$wpdb->query( 'ALTER TABLE ' . $wpdb->posts . ' ADD FULLTEXT bsearch (post_title, post_content);' );
	$wpdb->query( 'ALTER TABLE ' . $wpdb->posts . ' ADD FULLTEXT bsearch_title (post_title);' );
	$wpdb->query( 'ALTER TABLE ' . $wpdb->posts . ' ADD FULLTEXT bsearch_content (post_content);' );
	$wpdb->show_errors();

	// Create the tables
	$table_name = $wpdb->prefix . 'bsearch';
	$table_name_daily = $wpdb->prefix . 'bsearch_daily';

	if ( $wpdb->get_var( "show tables like '$table_name'" ) != $table_name ) {

		$sql = 'CREATE TABLE ' . $table_name . ' (
            accessedid int NOT NULL AUTO_INCREMENT,
            searchvar VARCHAR(100) NOT NULL,
            cntaccess int NOT NULL,
            PRIMARY KEY  (accessedid)
        );';

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );

		$wpdb->hide_errors();
		$wpdb->query( 'CREATE INDEX IDX_searhvar ON ' . $table_name . ' (searchvar)' );
		$wpdb->show_errors();

		add_option( 'bsearch_db_version', $bsearch_db_version );
	}

	if ( $wpdb->get_var( "show tables like '$table_name_daily'" ) != $table_name_daily ) {

		$sql = 'CREATE TABLE ' . $table_name_daily . ' (
            accessedid int NOT NULL AUTO_INCREMENT,
            searchvar VARCHAR(100) NOT NULL,
            cntaccess int NOT NULL,
            dp_date date NOT NULL,
            PRIMARY KEY  (accessedid)
        );';

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );

		$wpdb->hide_errors();
		$wpdb->query( 'CREATE INDEX IDX_searhvar ON ' . $table_name_daily . ' (searchvar)' );
		$wpdb->show_errors();

		add_option( 'bsearch_db_version', $bsearch_db_version );
	}

	// Upgrade table code
	$installed_ver = get_option( 'bsearch_db_version' );

	if ( $installed_ver != $bsearch_db_version ) {

		$sql = 'CREATE TABLE ' . $table_name . ' (
            accessedid int NOT NULL AUTO_INCREMENT,
            searchvar VARCHAR(100) NOT NULL,
            cntaccess int NOT NULL,
            PRIMARY KEY  (accessedid)
        );';

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );

		$wpdb->hide_errors();
		$wpdb->query( 'ALTER '.$table_name.' DROP INDEX IDX_searhvar ' );
		$wpdb->query( 'CREATE INDEX IDX_searhvar ON '.$table_name.' (searchvar)' );
		$wpdb->show_errors();

		$sql = "DROP TABLE $table_name_daily";
		$wpdb->query( $sql );

		$sql = 'CREATE TABLE ' . $table_name_daily . ' (
            accessedid int NOT NULL AUTO_INCREMENT,
            searchvar VARCHAR(100) NOT NULL,
            cntaccess int NOT NULL,
            dp_date date NOT NULL,
            PRIMARY KEY  (accessedid)
        );';

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );

		$wpdb->hide_errors();
		$wpdb->query( 'ALTER ' . $table_name_daily . ' DROP INDEX IDX_searhvar ' );
		$wpdb->query( 'CREATE INDEX IDX_searhvar ON ' . $table_name_daily . ' (searchvar)' );
		$wpdb->show_errors();

		update_option( 'bsearch_db_version', $bsearch_db_version );
	}

}


/**
 * Fired when a new site is activated with a WPMU environment.
 *
 * @since	2.0.0
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
 * @since	2.0.0
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


