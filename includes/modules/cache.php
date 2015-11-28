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


