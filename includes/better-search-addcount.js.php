<?php
/**
 * Increments the count on the search terms
 *
 * @package Better_Search
 */

// This makes the browser treat this file as a javascript
Header( 'content-type: application/x-javascript' );

// Force a short-init since we just need core WP, not the entire framework stack
// define( 'SHORTINIT', true );
// Build the wp-load.php path from a plugin/theme
$wp_load_path = dirname( dirname( dirname( dirname( __FILE__ ) ) ) );

// Require the wp-load.php file (which loads wp-config.php and bootstraps WordPress)
$wp_load_filename = '/wp-load.php';

// Check if the file exists in the root or one level up
if ( ! file_exists( $wp_load_path . $wp_load_filename ) ) {
	// Just in case the user may have placed wp-config.php one more level up from the root
	$wp_load_filename = dirname( $wp_load_path ) . $wp_load_filename;
}

// Require the wp-config.php file
require( $wp_load_filename );

// Include the now instantiated global $wpdb Class for use
global $wpdb;


/**
 * Increment the counter using Ajax.
 */
function bsearch_inc_count() {
	global $wpdb;
	$table_name = $wpdb->prefix . 'bsearch';
	$table_name_daily = $wpdb->prefix . 'bsearch_daily';
	$str = '';

	$search_query = wp_kses( $_GET['bsearch_id'], array() );

	if ( '' != $search_query ) {
		$results = $wpdb->get_results( $wpdb->prepare( "SELECT searchvar, cntaccess FROM $table_name WHERE searchvar = '%s' LIMIT 1", $search_query ) );
		$test = 0;
		if ( $results ) {
			foreach ( $results as $result ) {
				$tt = $wpdb->query( $wpdb->prepare( "UPDATE $table_name SET cntaccess = cntaccess + 1 WHERE searchvar = '%s'", $result->searchvar ) );
				$str .= ( $tt === false ) ? 'e_' : 's_' . $tt;
				$test = 1;
			}
		}
		if ( 0 == $test ) {
			$tt = $wpdb->query( $wpdb->prepare( "INSERT INTO $table_name (searchvar, cntaccess) VALUES('%s', '1')", $search_query ) );
			$str .= ( $tt === false ) ? 'e_' : 's_' . $tt;
		}

		// Now update daily count
		$current_date = gmdate( 'Y-m-d', ( time() + ( get_option( 'gmt_offset' ) * 3600 ) ) );

		$results = $wpdb->get_results( $wpdb->prepare( "SELECT searchvar, cntaccess, dp_date FROM $table_name_daily WHERE searchvar = '%s' AND dp_date = '%s' ", $search_query, $current_date ) );
		$test = 0;
		if ( $results ) {
			foreach ( $results as $result ) {
				$ttd = $wpdb->query( $wpdb->prepare( "UPDATE $table_name_daily SET cntaccess = cntaccess + 1 WHERE searchvar = '%s' AND dp_date = '%s' ", $result->searchvar, $current_date ) );
				$str .= ( $ttd === false ) ? '_e' : '_s' . $ttd;
				$test = 1;
			}
		}
		if ( 0 == $test ) {
			$ttd = $wpdb->query( $wpdb->prepare( "INSERT INTO $table_name_daily (searchvar, cntaccess, dp_date) VALUES('%s', '1', '%s' )", $search_query, $current_date ) );
			$str .= ( $ttd === false ) ? '_e' : '_s' . $ttd;
		}
	}
	echo '<!-- ' . $str . ' -->';
}
bsearch_inc_count();


