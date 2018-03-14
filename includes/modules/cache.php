<?php
/**
 * Cache functions used by Better Search
 *
 * @package Better_Search
 */

/**
 * Delete the Better Search cache.
 */
function bsearch_cache_delete() {
	global $wpdb;

	$wpdb->query( 'DELETE FROM ' . $wpdb->options . " WHERE option_name LIKE '_transient_bs_%'" );
	$wpdb->query( 'DELETE FROM ' . $wpdb->options . " WHERE option_name LIKE '_transient_timeout_bs_%'" );

}


/**
 * Function to clear the Better Search Cache with Ajax.
 *
 * @since   2.2.0
 */
function bsearch_ajax_clearcache() {

	bsearch_cache_delete();

	exit(
		wp_json_encode(
			array(
				'success' => 1,
				'message' => __( 'Better Search cache has been cleared', 'better-search' ),
			)
		)
	);
}
add_action( 'wp_ajax_bsearch_clear_cache', 'bsearch_ajax_clearcache' );

