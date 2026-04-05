<?php
/**
 * PHPUnit bootstrap file.
 */

$_tests_dir = getenv( 'WP_TESTS_DIR' );

// Check if we're installed in a src checkout.
$pos = stripos( __FILE__, '/src/wp-content/plugins/' );
if ( ! $_tests_dir && false !== $pos ) {
	$_tests_dir = substr( __FILE__, 0, $pos ) . '/tests/phpunit/';
}

if ( ! $_tests_dir ) {
	$_tests_dir = rtrim( sys_get_temp_dir(), '/\\' ) . '/wordpress-tests-lib';
}

if ( ! file_exists( $_tests_dir . '/includes/functions.php' ) ) {
	echo "Could not find $_tests_dir/includes/functions.php\n";
	exit( 1 );
}

// Give access to tests_add_filter() function.
require_once $_tests_dir . '/includes/functions.php';

function _manually_load_plugin() {
	require dirname( __DIR__ ) . '/better-search.php';
}
tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );

/**
 * Create plugin DB tables and FULLTEXT indexes after WP finishes loading.
 * This runs before any individual test's setUp(), so the tables are real
 * (not temporary) and the FULLTEXT indexes are available for all tests.
 */
function _bsearch_create_tables_and_indexes() {
	global $wpdb;
	$wpdb->hide_errors();
	\WebberZone\Better_Search\Db::create_tables();
	\WebberZone\Better_Search\Db::create_fulltext_indexes();
	$wpdb->show_errors();
}
tests_add_filter( 'wp_loaded', '_bsearch_create_tables_and_indexes' );

// Include the PHPUnit Polyfills autoloader.
require dirname( __DIR__ ) . '/vendor/yoast/phpunit-polyfills/phpunitpolyfills-autoload.php';

// Start up the WP testing environment.
require $_tests_dir . '/includes/bootstrap.php';
