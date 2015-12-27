<?php
/**
 * Shortcode functions used by Better Search
 *
 * @package Better_Search
 */

function bsearch_heatmap_func( $atts ) {
	global $wpdb, $bsearch_url, $bsearch_settings;

	$atts = shortcode_atts( array(
		'daily' => false,
		'smallest' => intval( $bsearch_settings['heatmap_smallest'] ),
		'largest' => intval( $bsearch_settings['heatmap_largest'] ),
		'unit' => $bsearch_settings['heatmap_unit'],
		'cold' => $bsearch_settings['heatmap_cold'],
		'hot' => $bsearch_settings['heatmap_hot'],
		'before' => $bsearch_settings['heatmap_before'],
		'after' => $bsearch_settings['heatmap_after'],
		'heatmap_limit' => intval( $bsearch_settings['heatmap_limit'] ),
		'daily_range' => intval( $bsearch_settings['daily_range'] ),
	), $atts, 'bsearch_heatmap' );

	return get_bsearch_heatmap( $atts );

}
add_shortcode( 'bsearch_heatmap', 'bsearch_heatmap_func' );
