<?php
/**
 * Cache functions used by Better Search
 *
 * @package Better_Search
 */

// If this file is called directly, then abort execution.
if ( ! defined( 'WPINC' ) ) {
	die( "Aren't you supposed to come here via WP-Admin?" );
}

/**
 * Delete the Better Search cache.
 *
 * @since 2.2.0
 * @since 3.0.0 Added transient argument to delete
 *
 * @param array $transients Array of transients to delete.
 * @return int Number of transients deleted.
 */
function bsearch_cache_delete( $transients = array() ) {
	$loop = 0;

	$transients = bsearch_cache_get_keys();

	foreach ( $transients as $transient ) {
		$del = delete_transient( $transient );
		if ( $del ) {
			++$loop;
		}
	}
	return $loop;
}


/**
 * Function to clear the Better Search Cache with Ajax.
 *
 * @since 2.2.0
 */
function bsearch_ajax_clearcache() {

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 0 );
	}
	check_ajax_referer( 'bsearch-admin', 'security' );

	$count = bsearch_cache_delete();

	exit(
		wp_json_encode(
			array(
				'success' => 1,
				/* translators: 1: Number of entries. */
				'message' => sprintf( _n( '%s entry cleared', '%s entries cleared', $count, 'text-domain' ), number_format_i18n( $count ) ),
			)
		)
	);
}
add_action( 'wp_ajax_bsearch_clear_cache', 'bsearch_ajax_clearcache' );


/**
 * Get the meta key based on a list of parameters.
 *
 * @since 3.0.0
 *
 * @param mixed  $attr    Array of attributes typically.
 * @param string $context Context of the cache key to be set.
 * @return string Cache meta key
 */
function bsearch_cache_get_key( $attr, $context = 'query' ) {

	$key = sprintf( 'bs_cache_%1$s_%2$s', md5( wp_json_encode( $attr ) ), $context );

	return $key;
}


/**
 * Get the transient names for Better Search.
 *
 * @since 3.0.0
 *
 * @return array Better Search Cache keys.
 */
function bsearch_cache_get_keys() {
	global $wpdb;

	$keys = array();

	$sql = "
		SELECT option_name
		FROM {$wpdb->options}
		WHERE `option_name` LIKE '_transient_bs_%'
	";

	$results = $wpdb->get_results( $sql ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared

	if ( is_array( $results ) ) {
		foreach ( $results as $result ) {
			$keys[] = str_replace( '_transient_', '', $result->option_name );
		}
	}

	return apply_filters( 'bsearch_cache_get_keys', $keys );
}

