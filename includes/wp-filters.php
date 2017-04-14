<?php
/**
 * Functions to add to header, footer and content.
 *
 * @package Better_Search
 */


/**
 * Clause to add code to wp_head
 *
 * @since	1.3.3
 *
 * @return	string	HTML added to the wp_head
 */
function bsearch_clause_head() {
	global $wp_query, $bsearch_settings;
	$bsearch_custom_CSS = stripslashes( $bsearch_settings['custom_CSS'] );

	$output = '';

	if ( $wp_query->is_search ) {

		if ( $bsearch_settings['seamless'] && ! is_paged() ) {
			$search_query = get_bsearch_query();
			$output .= bsearch_increment_counter( $search_query );
		}

		if ( $bsearch_settings['meta_noindex'] ) {
			$output .= '<meta name="robots" content="noindex,follow" />';
		}

		// Add custom CSS to header
		if ( '' != $bsearch_custom_CSS ) {
			$output .= '<style type="text/css">' . $bsearch_custom_CSS . '</style>';
		}
	}

	/**
	 * Filters the output HTML added to wp_head
	 *
	 * @since	2.0.0
	 *
	 * @return	string	$output	Output HTML added to wp_head
	 */
	$output = apply_filters( 'bsearch_clause_head', $output );

	echo $output;
}
add_action( 'wp_head', 'bsearch_clause_head' );



/**
 * Highlight the search term
 *
 * @since	2.0.0
 *
 * @param	string $content    Post content
 * @return 	string	Post Content
 */
function bsearch_content( $content ) {
	global $bsearch_settings, $wp_query;

	if ( $wp_query->is_search() && $bsearch_settings['seamless'] && ! is_admin() && in_the_loop() && $bsearch_settings['highlight'] ) {
		$search_query = get_bsearch_query();

		$search_query = preg_quote( $search_query, '/' );
		$keys = explode( ' ', str_replace( array( "'", "\"", "&quot;", "\+", "\-" ), "", $search_query ) );

		$regEx = '/(?!<[^>]*?>)('. implode( '|', $keys ) . ')(?![^<]*?>)/iu';
		$content  = preg_replace( $regEx, '<span class="bsearch_highlight">$1</span>', $content );

	}

	return apply_filters( 'bsearch_content', $content );
}
add_filter( 'the_content', 'bsearch_content' );
add_filter( 'get_the_excerpt', 'bsearch_content' );
add_filter( 'the_title', 'bsearch_content' );


