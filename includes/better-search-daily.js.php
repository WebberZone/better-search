<?php
/**
 * Display Daily Popular Search terms.
 *
 * @package Better_Search
 */

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


/**
 * Display heatmap of top results using Ajax.
 */
function bsearch_daily_searches() {
	global $wpdb, $siteurl, $tableposts, $search_query;
	$table_name = $wpdb->prefix . 'bsearch_daily';

	$is_widget = intval( $_GET['widget'] );

	$bsearch_settings = bsearch_read_options();

	$output = '';

	$output .= '<div class="bsearch_heatmap">';

	if ( ! $is_widget ) {
		$output .= $bsearch_settings['title_daily'];
	}

	$output .= '<div text-align:center>';
	$output .= get_bsearch_heatmap( array(
		'daily' => 1,
	) );
	$output .= '</div>';

	if ( $bsearch_settings['show_credit'] ) {
		$output .= '<br /><small>Powered by <a href="https://webberzone.com/plugins/better-search/">Better Search plugin</a></small>';
	}
	$output .= '</div>';

	echo "document.write('" . $output . "')";
}
bsearch_daily_searches();

