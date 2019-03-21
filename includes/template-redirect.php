<?php
/**
 * Redirect function
 *
 * @package Better_Search
 */

// If this file is called directly, then abort execution.
if ( ! defined( 'WPINC' ) ) {
	die( "Aren't you supposed to come here via WP-Admin?" );
}

/**
 * Displays the search results
 *
 * First checks if the theme contains a search template and uses that
 * If search template is missing, generates the results below
 *
 * @since   1.0
 *
 * @param string $template Search template to use.
 */
function bsearch_template_redirect( $template ) {
	// Not a search page; don't do anything and return.
	if ( ( isset( $_SERVER['REQUEST_URI'] ) && stripos( wp_unslash( $_SERVER['REQUEST_URI'] ), '?s=' ) === false )  // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		&& ( isset( $_SERVER['REQUEST_URI'] ) && stripos( wp_unslash( $_SERVER['REQUEST_URI'] ), '/search/' ) === false )  // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		&& ( ! is_search() ) ) {
		return $template;
	}

	global $wp_query;

	// If seamless integration mode is activated; return.
	if ( bsearch_get_option( 'seamless' ) ) {
		return $template;
	}

	// If we have a 404 status.
	if ( $wp_query->is_404 ) {
		// Set status of 404 to false.
		$wp_query->is_404     = false;
		$wp_query->is_archive = true;
	}

	// Change status code to 200 OK since /search/ returns status code 404.
	@header( 'HTTP/1.1 200 OK', 1 ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
	@header( 'Status: 200 OK', 1 ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged

	$search_query = get_bsearch_query();

	$limit = isset( $_GET['limit'] ) ? intval( $_GET['limit'] ) : bsearch_get_option( 'limit' ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

	// Added necessary code to the head.
	add_action( 'wp_head', 'bsearch_head' );

	// Set the title.
	add_filter( 'wp_title', 'bsearch_title' );

	// If there is a template file within the parent or child theme then we use it.
	$priority_template_lookup = array(
		get_stylesheet_directory() . '/better-search-template.php',
		get_template_directory() . '/better-search-template.php',
		plugin_dir_path( dirname( __FILE__ ) ) . 'templates/template.php',
	);

	foreach ( $priority_template_lookup as $exists ) {

		if ( file_exists( $exists ) ) {

			return $exists;

		}
	}

	return $template;
}
add_action( 'template_include', 'bsearch_template_redirect', 1 );


/**
 * Insert styles into WordPress Head. Filters `wp_head`.
 *
 * @since   1.0
 */
function bsearch_head() {

	$bsearch_custom_css = stripslashes( bsearch_get_option( 'custom_css' ) );

	$search_query = get_bsearch_query();

	// Add custom CSS to header.
	if ( ( '' != $bsearch_custom_css ) && is_search() ) { //phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
		echo '<style type="text/css">' . $bsearch_custom_css . '</style>'; //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	// Add noindex to search results page.
	if ( bsearch_get_option( 'meta_noindex' ) ) {
		echo '<meta name="robots" content="noindex,follow" />';
	}

}


/**
 * Change page title. Filters `wp_title`.
 *
 * @since   1.0
 *
 * @param   string $title Title of the page.
 * @return  string  Filtered title of the page
 */
function bsearch_title( $title ) {

	if ( ! is_search() ) {
		return $title;
	}

	$search_query = get_bsearch_query( true );

	if ( isset( $search_query ) ) {
		/* translators: 1: search query, 2: title of the page */
		$bsearch_title = sprintf( __( 'Search Results for "%1$s" | %2$s', 'better-search' ), $search_query, $title );

	}

	/**
	 * Filters the title of the page
	 *
	 * @since   2.0.0
	 *
	 * @param   string  $bsearch_title  Title of the page set by Better Search
	 * @param   string  $title          Original Title of the page
	 * @param   string  $search_query   Search query
	 */
	return apply_filters( 'bsearch_title', $bsearch_title, $title, $search_query );

}

