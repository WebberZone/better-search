<?php
/**
 * Fired when the plugin is uninstalled
 *
 * @package Better_Search
 */

use WebberZone\Better_Search\Db;

defined( 'ABSPATH' ) || exit;

if ( ! ( defined( 'WP_UNINSTALL_PLUGIN' ) || defined( 'WP_FS__UNINSTALL_MODE' ) ) ) {
	exit;
}

if ( is_multisite() ) {

	$sites = get_sites(
		array(
			'archived' => 0,
			'spam'     => 0,
			'deleted'  => 0,
		)
	);

	foreach ( $sites as $site ) {
		switch_to_blog( (int) $site->blog_id );
		bsearch_delete_data();
		restore_current_blog();
	}
} else {
	bsearch_delete_data();
}


/**
 * Delete plugin data.
 *
 * @since 2.5.0
 */
function bsearch_delete_data() {
	global $wpdb;

	if ( is_plugin_active( 'better-search-pro/better-search.php' ) ) {
		return;
	}

	$settings = get_option( 'bsearch_settings' );

	if ( defined( 'BETTER_SEARCH_DELETE_DATA' ) && BETTER_SEARCH_DELETE_DATA ) {
		$wpdb->query( 'DROP TABLE ' . $wpdb->prefix . 'bsearch' ); //phpcs:ignore WordPress.DB.DirectDatabaseQuery.SchemaChange,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.DirectQuery
		$wpdb->query( 'DROP TABLE ' . $wpdb->prefix . 'bsearch_daily' ); //phpcs:ignore WordPress.DB.DirectDatabaseQuery.SchemaChange,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.DirectQuery
	}

	// Delete main plugin options.
	delete_option( 'ald_bsearch_settings' );
	delete_option( 'bsearch_settings' );
	delete_option( 'bsearch_db_version' );
	delete_site_option( 'better_search_selected_sites' );

	// Delete wizard-related options.
	delete_option( 'bsearch_wizard_completed' );
	delete_option( 'bsearch_wizard_completed_date' );
	delete_option( 'bsearch_wizard_current_step' );
	delete_option( 'bsearch_show_wizard' );

	// Delete custom tables options.
	delete_option( 'wz_posts_custom_tables_ready' );

	// Drop custom tables if they exist and uninstall is enabled.
	if ( ! empty( $settings['uninstall_tables'] ) && class_exists( 'WebberZone\\Better_Search\\Pro\\Custom_Tables\\Table_Manager' ) ) {
		$table_manager = new \WebberZone\Better_Search\Pro\Custom_Tables\Table_Manager();
		$table_manager->drop_tables();
		delete_option( \WebberZone\Better_Search\Pro\Custom_Tables\Table_Manager::$db_version_option );
	}

	// Delete fulltext indexes.
	Db::delete_fulltext_indexes();

	// Drop fuzzy functions.
	$wpdb->query( 'DROP FUNCTION IF EXISTS wz_levenshtein' ); //phpcs:ignore WordPress.DB.DirectDatabaseQuery.SchemaChange,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.DirectQuery
	$wpdb->query( 'DROP FUNCTION IF EXISTS wz_phrase_similarity_levenshtein' ); //phpcs:ignore WordPress.DB.DirectDatabaseQuery.SchemaChange,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.DirectQuery
	$wpdb->query( 'DROP FUNCTION IF EXISTS wz_phrase_similarity_soundex' ); //phpcs:ignore WordPress.DB.DirectDatabaseQuery.SchemaChange,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.DirectQuery

	// Delete all plugin transients.
	bsearch_delete_transients();

	do_action( 'bsearch_delete_data' );
}

/**
 * Delete all plugin transients.
 *
 * @since 4.2.0
 */
function bsearch_delete_transients() {
	global $wpdb;

	// Delete specific known transients.
	delete_transient( 'bsearch_reindex_state' );
	delete_transient( 'bsearch_show_wizard_activation_redirect' );
	delete_transient( 'bsearch_deactivated_notice_id' );
	delete_transient( 'bsearch_reindex_scheduled' );

	// Delete all transients with bs_ prefix (cache transients).
	$sql = "
		SELECT option_name
		FROM {$wpdb->options}
		WHERE `option_name` LIKE '_transient_bs_%'
		OR `option_name` LIKE '_transient_timeout_bs_%'
	";

	$results = $wpdb->get_results( $sql ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared

	if ( is_array( $results ) ) {
		foreach ( $results as $result ) {
			if ( strpos( $result->option_name, '_transient_timeout_' ) === 0 ) {
				// Skip timeout options, they'll be deleted with the transient.
				continue;
			}
			$transient = str_replace( '_transient_', '', $result->option_name );
			delete_transient( $transient );
		}
	}

	// Delete all transients with bsearch_ prefix.
	$sql = "
		SELECT option_name
		FROM {$wpdb->options}
		WHERE `option_name` LIKE '_transient_bsearch_%'
		OR `option_name` LIKE '_transient_timeout_bsearch_%'
	";

	$results = $wpdb->get_results( $sql ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared

	if ( is_array( $results ) ) {
		foreach ( $results as $result ) {
			if ( strpos( $result->option_name, '_transient_timeout_' ) === 0 ) {
				// Skip timeout options, they'll be deleted with the transient.
				continue;
			}
			$transient = str_replace( '_transient_', '', $result->option_name );
			delete_transient( $transient );
		}
	}

	// Delete site transients with bsearch_ prefix (for multisite).
	if ( is_multisite() ) {
		$sql = "
			SELECT meta_key
			FROM {$wpdb->sitemeta}
			WHERE `meta_key` LIKE '_site_transient_bsearch_%'
			OR `meta_key` LIKE '_site_transient_timeout_bsearch_%'
			OR `meta_key` LIKE '_site_transient_bs_%'
			OR `meta_key` LIKE '_site_transient_timeout_bs_%'
		";

		$results = $wpdb->get_results( $sql ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared

		if ( is_array( $results ) ) {
			foreach ( $results as $result ) {
				if ( strpos( $result->meta_key, '_site_transient_timeout_' ) === 0 ) {
					// Skip timeout options, they'll be deleted with the transient.
					continue;
				}
				$transient = str_replace( '_site_transient_', '', $result->meta_key );
				delete_site_transient( $transient );
			}
		}
	}
}
