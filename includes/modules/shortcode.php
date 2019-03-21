<?php
/**
 * Shortcode functions used by Better Search
 *
 * @package Better_Search
 */

// If this file is called directly, then abort execution.
if ( ! defined( 'WPINC' ) ) {
	die( "Aren't you supposed to come here via WP-Admin?" );
}

/**
 * Shortcode to get the Better Search heatmap.
 *
 * @since 2.1.0
 *
 * @param array $atts Heatmap attributes.
 * @return string HTML output of the heatmap
 */
function bsearch_heatmap_func( $atts ) {

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
		),
		$atts,
		'bsearch_heatmap'
	);

	return get_bsearch_heatmap( $atts );

}
add_shortcode( 'bsearch_heatmap', 'bsearch_heatmap_func' );

/**
 * Shortcode to get the Better Search form
 *
 * @since 2.2.0
 *
 * @param array $atts Search form attributes.
 * @return string HTML output of the search form
 */
function bsearch_search_form_func( $atts ) {

	return get_bsearch_form( '' );

}
add_shortcode( 'bsearch_form', 'bsearch_search_form_func' );
