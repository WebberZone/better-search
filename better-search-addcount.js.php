<?php
//"better-search-addcount.js.php" Add count to database
Header("content-type: application/x-javascript");

if (!function_exists('add_action')) {
	$wp_root = '../../..';
	if (file_exists($wp_root.'/wp-load.php')) {
		require_once($wp_root.'/wp-load.php');
	} else {
		require_once($wp_root.'/wp-config.php');
	}
}

// Ajax Increment Counter
bsearch_inc_count();
function bsearch_inc_count() {
	global $wpdb;
	$table_name = $wpdb->prefix . "bsearch";
	$table_name_daily = $wpdb->prefix . "bsearch_daily";
	
	$s = quote_smart($_GET['bsearch_id']);
	$s = RemoveXSS($s);

	if($s != '') {
		$results = $wpdb->get_results("SELECT searchvar, cntaccess FROM $table_name WHERE searchvar = '$s'");
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
