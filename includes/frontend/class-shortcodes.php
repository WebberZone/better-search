<?php
/**
 * Shortcode module
 *
 * @package Better_Search
 */

namespace WebberZone\Better_Search\Frontend;

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Admin Columns Class.
 *
 * @since 3.3.0
 */
class Shortcodes {

	/**
	 * Constructor class.
	 *
	 * @since 3.3.0
	 */
	public function __construct() {
		add_shortcode( 'bsearch_heatmap', array( __CLASS__, 'bsearch_heatmap' ) );
		add_shortcode( 'bsearch_form', array( __CLASS__, 'bsearch_form' ) );
	}

	/**
	 * Shortcode to get the Better Search heatmap.
	 *
	 * @param   array $atts       Shortcode attributes.
	 * @return  string  HTML output of the heatmap.
	 */
	public static function bsearch_heatmap( $atts ) {
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

	/**
	 * Creates a shortcode [bsearch_form daily="0"].
	 *
	 * @param   array $atts           Shortcode attributes.
	 * @return  string The Better Search form.
	 */
	public static function bsearch_form( $atts ) {
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
}
