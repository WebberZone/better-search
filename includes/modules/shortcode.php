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
			'daily'            => false,
			'daily_range'      => absint( bsearch_get_option( 'daily_range' ) ),
			'smallest'         => absint( bsearch_get_option( 'heatmap_smallest' ) ),
			'largest'          => absint( bsearch_get_option( 'heatmap_largest' ) ),
			'unit'             => bsearch_get_option( 'heatmap_unit', 'pt' ),
			'hot'              => bsearch_get_option( 'heatmap_hot' ),
			'cold'             => bsearch_get_option( 'heatmap_cold' ),
			'number'           => absint( bsearch_get_option( 'heatmap_limit' ) ),
			'before_term'      => bsearch_get_option( 'heatmap_before' ),
			'after_term'       => bsearch_get_option( 'heatmap_after' ),
			'link_nofollow'    => bsearch_get_option( 'link_nofollow' ),
			'link_new_window'  => bsearch_get_option( 'link_new_window' ),
			'format'           => 'flat',
			'separator'        => "\n",
			'orderby'          => 'count',
			'order'            => 'RAND',
			'topic_count_text' => null,
			'show_count'       => 0,
			'no_results_text'  => __( 'No searches made yet', 'better-search' ),
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

	$atts = shortcode_atts(
		array(
			'before'              => '',
			'after'               => '',
			'aria_label'          => '',
			'post_types'          => bsearch_get_option( 'post_types' ),
			'selected_post_types' => '',
			'show_post_types'     => false,
		),
		$atts,
		'bsearch_form'
	);

	return get_bsearch_form( '', $atts );
}
add_shortcode( 'bsearch_form', 'bsearch_search_form_func' );
