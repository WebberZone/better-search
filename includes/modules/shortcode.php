<?php
/**
 * Shortcode functions used by Better Search
 *
 * @package Better_Search
 */

/**
 * Get the Better Search heatmap
 *
 * @param array $atts Heatmap attributes.
 * @return string HTML output of the heatmap
 */
function bsearch_heatmap_func( $atts ) {
	global $wpdb, $bsearch_url;

	$atts = shortcode_atts(
		array(
			'daily'         => false,
			'smallest'      => intval( bsearch_get_option( 'heatmap_smallest' ) ),
			'largest'       => intval( bsearch_get_option( 'heatmap_largest' ) ),
			'unit'          => bsearch_get_option( 'heatmap_unit' ),
			'cold'          => bsearch_get_option( 'heatmap_cold' ),
			'hot'           => bsearch_get_option( 'heatmap_hot' ),
			'before'        => bsearch_get_option( 'heatmap_before' ),
			'after'         => bsearch_get_option( 'heatmap_after' ),
			'heatmap_limit' => intval( bsearch_get_option( 'heatmap_limit' ) ),
			'daily_range'   => intval( bsearch_get_option( 'daily_range' ) ),
		), $atts, 'bsearch_heatmap'
	);

	return get_bsearch_heatmap( $atts );

}
add_shortcode( 'bsearch_heatmap', 'bsearch_heatmap_func' );
