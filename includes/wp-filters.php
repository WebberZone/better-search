<?php
/**
 * Functions to add to header, footer and content.
 *
 * @package Better_Search
 */

// If this file is called directly, then abort execution.
if ( ! defined( 'WPINC' ) ) {
	die( "Aren't you supposed to come here via WP-Admin?" );
}

/**
 * Echoes the code to wp_head
 *
 * @since   1.3.3
 */
function bsearch_clause_head() {
	global $wp_query;
	$bsearch_custom_css = stripslashes( bsearch_get_option( 'custom_css' ) );

	$output = '';

	if ( $wp_query->is_search ) {

		if ( bsearch_get_option( 'meta_noindex' ) ) {
			$output .= '<meta name="robots" content="noindex,follow" />';
		}

		// Add custom CSS to header.
		if ( '' != $bsearch_custom_css ) { //phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
			$output .= '<style type="text/css">' . $bsearch_custom_css . '</style>';
		}
	}

	/**
	 * Filters the output HTML added to wp_head
	 *
	 * @since   2.0.0
	 *
	 * @return  string  $output Output HTML added to wp_head
	 */
	$output = apply_filters( 'bsearch_clause_head', $output );

	echo $output; //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}
add_action( 'wp_head', 'bsearch_clause_head' );



/**
 * Highlight the search term
 *
 * @since   2.0.0
 *
 * @param   string $content    Post content.
 * @return  string  Post Content
 */
function bsearch_content( $content ) {

	if ( ! is_admin() && in_the_loop() && is_search() && bsearch_get_option( 'seamless' ) && bsearch_get_option( 'highlight' ) ) {
		$search_query = get_bsearch_query();

		$search_query = preg_quote( $search_query, '/' );
		$keys         = explode( ' ', str_replace( array( "'", '"', '&quot;', '\+', '\-' ), '', $search_query ) );
		$content      = bsearch_highlight( $content, $keys );

	}

	return apply_filters( 'bsearch_content', $content );
}
add_filter( 'the_content', 'bsearch_content' );
add_filter( 'get_the_excerpt', 'bsearch_content' );
add_filter( 'the_title', 'bsearch_content' );


