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

	$bsearch_custom_css = stripslashes( bsearch_get_option( 'custom_css' ) );

	$output = '';

	if ( is_search() ) {

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

	if ( is_admin() || ! in_the_loop() ) {
		return $content;
	}

	$referer = wp_get_referer() ? urldecode( wp_get_referer() ) : '';
	if ( is_search() ) {
		$is_referer_search_engine = true;
	} else {
		$siteurl = get_option( 'home' );
		if ( preg_match( "#^$siteurl#i", $referer ) ) {
			parse_str( (string) wp_parse_url( $referer, PHP_URL_QUERY ), $queries );
			if ( ! empty( $queries['s'] ) ) {
				$is_referer_search_engine = true;
			}
		}
	}

	if ( empty( $is_referer_search_engine ) ) {
		return $content;
	}

	if ( bsearch_get_option( 'highlight' ) && is_search() ) {
		$search_query = get_bsearch_query();
	} elseif ( bsearch_get_option( 'highlight_followed_links' ) ) {
		$search_query = preg_replace( '/^.*s=([^&]+)&?.*$/i', '$1', $referer );
		$search_query = preg_replace( '/\'|"/', '', $search_query );
	}

	if ( ! empty( $search_query ) ) {
		$search_query = str_replace( array( "'", '"', '&quot;', '\+', '\-' ), '', $search_query );
		$keys         = preg_split( '/[\s,\+\.]+/', $search_query );
		$content      = bsearch_highlight( $content, $keys );
	}

	return apply_filters( 'bsearch_content', $content );
}
add_filter( 'the_content', 'bsearch_content', 999 );
add_filter( 'get_the_excerpt', 'bsearch_content', 999 );
add_filter( 'the_title', 'bsearch_content', 999 );
add_filter( 'the_bsearch_excerpt', 'bsearch_content', 999 );

/**
 * Enqueue styles and scripts.
 *
 * @since 3.0.0
 */
function bsearch_enqueue_scripts_styles() {

	if ( bsearch_get_option( 'include_styles' ) ) {
		wp_register_style( 'bsearch-style', plugins_url( 'includes/css/bsearch-styles.min.css', BETTER_SEARCH_PLUGIN_FILE ), array(), BETTER_SEARCH_VERSION );
	}

	if ( ! is_admin() && ( is_search() || is_singular() ) ) {
		wp_enqueue_style( 'bsearch-style' );
		wp_add_inline_style( 'bsearch-style', esc_html( bsearch_get_option( 'custom_css' ) ) );
	}
}
add_action( 'wp_enqueue_scripts', 'bsearch_enqueue_scripts_styles' );
