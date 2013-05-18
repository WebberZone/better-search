<?php
//"better-search-addcount.js.php" Add count to database
Header("content-type: application/x-javascript");

// Force a short-init since we just need core WP, not the entire framework stack
//define( 'SHORTINIT', true );

// Build the wp-load.php path from a plugin/theme
$wp_load_path = dirname( dirname( dirname( __FILE__ ) ) );
// Require the wp-load.php file (which loads wp-config.php and bootstraps WordPress)
$wp_load_filename = '/wp-load.php';

// Check if the file exists in the root or one level up
if( !file_exists( $wp_load_path . $wp_load_filename ) ) {
    // Just in case the user may have placed wp-config.php one more level up from the root
    $wp_load_filename = dirname( $wp_load_path ) . $wp_load_filename;
}
// Require the wp-config.php file
require( $wp_load_filename );

// Include the now instantiated global $wpdb Class for use
global $wpdb;


// Ajax Increment Counter
bsearch_inc_count();
function bsearch_inc_count() {
	global $wpdb;
	$table_name = $wpdb->prefix . "bsearch";
	$table_name_daily = $wpdb->prefix . "bsearch_daily";
	
	$s = wp_kses($_GET['bsearch_id'],array());

	if($s != '') {
		$results = $wpdb->get_results("SELECT searchvar, cntaccess FROM $table_name WHERE searchvar = '$s' LIMIT 1");
		$test = 0;
		if ($results) {
			foreach ($results as $result) {
				$wpdb->query("UPDATE $table_name SET cntaccess = cntaccess + 1 WHERE searchvar = '$result->searchvar'");
				$test = 1;
			}
		}
		if ($test == 0) {
			$wpdb->query("INSERT INTO $table_name (searchvar, cntaccess) VALUES('$s', '1')");
		}
		// Now update daily count
		$current_date = $wpdb->get_var("SELECT CURDATE() ");

		$results = $wpdb->get_results("SELECT searchvar, cntaccess, dp_date FROM $table_name_daily WHERE searchvar = '$s' AND dp_date = '$current_date' ");
		$test = 0;
		if ($results) {
			foreach ($results as $result) {
				$wpdb->query("UPDATE $table_name_daily SET cntaccess = cntaccess + 1 WHERE searchvar = '$result->searchvar' AND dp_date = '$current_date' ");
				$test = 1;
			}
		}
		if ($test == 0) {
			$wpdb->query("INSERT INTO $table_name_daily (searchvar, cntaccess, dp_date) VALUES('$s', '1', '$current_date' )");
		}
	}
}

?>
