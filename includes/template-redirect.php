<?php
/**
 * Redirect function
 *
 * @package Better_Search
 */

/**
 * Displays the search results
 *
 * First checks if the theme contains a search template and uses that
 * If search template is missing, generates the results below
 *
 * @since	1.0
 */
function bsearch_template_redirect() {
	// not a search page; don't do anything and return
	if ( ( stripos( $_SERVER['REQUEST_URI'], '?s=' ) === false ) && ( stripos( $_SERVER['REQUEST_URI'], '/search/' ) === false ) && ( ! is_search() ) ) {
		return;
	}

	global $wp_query, $bsearch_settings;

	// If seamless integration mode is activated; return
	if ( $bsearch_settings['seamless'] ) {
	    return;
	}

	// if we have a 404 status
	if ( $wp_query->is_404 ) {
		// set status of 404 to false
		$wp_query->is_404 = false;
		$wp_query->is_archive = true;
	}

		// change status code to 200 OK since /search/ returns status code 404
	@header( 'HTTP/1.1 200 OK', 1 );
	@header( 'Status: 200 OK', 1 );

	$search_query = get_bsearch_query();

	$limit = isset( $_GET['limit'] ) ? intval( $_GET['limit'] ) : $bsearch_settings['limit']; // Read from GET variable

		// Added necessary code to the head
	add_action( 'wp_head', 'bsearch_head' );

		// Set thw title
	add_filter( 'wp_title', 'bsearch_title' );

	// If there is a template file within the parent or child theme then we use it
	$priority_template_lookup = array(
		get_stylesheet_directory() . '/better-search-template.php',
		get_template_directory() . '/better-search-template.php',
		plugin_dir_path( dirname( __FILE__ ) ) . 'templates/template.php',
	);

	foreach ( $priority_template_lookup as $exists ) {

		if ( file_exists( $exists ) ) {

			include_once( $exists );
			exit;

		}
	}
}
add_action( 'template_redirect', 'bsearch_template_redirect', 1 );


/**
 * Insert styles into WordPress Head. Filters `wp_head`.
 *
 * @since	1.0
 */
function bsearch_head() {

	global $bsearch_settings;
	$bsearch_custom_CSS = stripslashes( $bsearch_settings['custom_CSS'] );

	$search_query = get_bsearch_query();

	$limit = ( isset( $_GET['limit'] ) ) ? intval( $_GET['limit'] ) : $bsearch_settings['limit']; // Read from GET variable
	$bpaged = ( isset( $_GET['bpaged'] ) ) ? intval( $_GET['bpaged'] ) : 0; // Read from GET variable

	if ( ! $bpaged && $bsearch_settings['track_popular'] ) {
		echo bsearch_increment_counter( $search_query );	// Increment the count if we are on the first page of the results
	}

	// Add custom CSS to header
	if ( ( '' != $bsearch_custom_CSS ) && is_search() ) {
		echo '<style type="text/css">' . $bsearch_custom_CSS . '</style>';
	}

	// Add noindex to search results page
	if ( $bsearch_settings['meta_noindex'] ) {
		echo '<meta name="robots" content="noindex,follow" />';
	}

}


/**
 * Change page title. Filters `wp_title`.
 *
 * @since	1.0
 *
 * @param	string $title
 * @return	string	Title of the page
 */
function bsearch_title( $title ) {

	if ( ! is_search() ) {
		return $title;
	}

	$search_query = get_bsearch_query();

	if ( isset( $search_query ) ) {

		$bsearch_title = sprintf( __( 'Search Results for "%s" | %s', 'better-search' ), $search_query, $title );

	}

	/**
	 * Filters the title of the page
	 *
	 * @since	2.0.0
	 *
	 * @param	string	$bsearch_title	Title of the page set by Better Search
	 * @param	string	$title			Original Title of the page
	 * @param	string	$search_query	Search query
	 */
	return apply_filters( 'bsearch_title', $bsearch_title, $title, $search_query );

}

