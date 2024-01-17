<?php
/**
 * Utlity functions used by Better Search
 *
 * @package Better_Search
 */

use WebberZone\Better_Search\Util\Helpers;

// If this file is called directly, then abort execution.
if ( ! defined( 'WPINC' ) ) {
	die( "Aren't you supposed to come here via WP-Admin?" );
}

/**
 * Fetch the search query for Better Search.
 *
 * @since   2.0.0
 *
 * @param bool $escaped Whether the result is escaped. Default true.
 *                      Always escape this if you are going to display it.
 * @return  string  Better Search query
 */
function get_bsearch_query( $escaped = true ) {

	$search_query = trim(
		Helpers::clean_terms(
			get_search_query( $escaped )
		)
	);

	/**
	 * Filter search terms string
	 *
	 * @since   2.0.0
	 *
	 * @param   string  $search_query   Search query
	 */
	return apply_filters( 'get_bsearch_query', $search_query );
}


/**
 * Returns an array with the cleaned-up search string at the zero index and possibly a list of terms in the second.
 *
 * @since 1.2
 *
 * @param mixed $search_query   The search term.
 * @param array $args {
 *      Optional. Array or string of Query parameters.
 *
 *      @type bool $use_fulltext Use fulltext flag.
 * }
 * @return array Cleaned up search string. Search query is at [0], array of terms at [1], fulltext status at [2].
 */
function get_bsearch_terms( $search_query = '', $args = array() ) {

	$defaults = array(
		'use_fulltext' => bsearch_get_option( 'use_fulltext' ),
	);
	$args     = wp_parse_args( $args, $defaults );

	if ( empty( $search_query ) ) {
		$search_query = get_bsearch_query();
	}
	$search_words = array();

	// Extract the search terms. We respect quotes.
	$search_query = stripslashes( $search_query ); // Added slashes screw with quote grouping when done early, so done later.
	if ( preg_match_all( '/".*?("|$)|((?<=[\t ",+])|^)[^\t ",+]+/', $search_query, $matches ) ) {
		$search_words = $matches[0];
	}
	$use_fulltext = $args['use_fulltext'];

	// if search terms are less than 3 then turn fulltext off.
	if ( $use_fulltext ) {
		$use_fulltext_proxy = false;
		foreach ( $search_words as $search_word ) {
			if ( strlen( $search_word ) > 3 ) {
				$use_fulltext_proxy = true;
			}
		}
		$use_fulltext = $use_fulltext_proxy;
	}

	$s_array[0] = $search_query;    // Save original query at [0].
	$s_array[1] = $search_words;    // Save array of terms at [1].
	$s_array[2] = $use_fulltext;    // Save fulltext status at [2].

	/**
	 * Filter array holding the search query and terms
	 *
	 * @since 1.2
	 *
	 * @param array $s_array Search query is at [0], array of terms at [1], fulltext status at [2]
	 */
	return apply_filters( 'get_bsearch_terms', $s_array );
}
