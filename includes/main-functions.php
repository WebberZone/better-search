<?php
/**
 * Utlity functions used by Better Search
 *
 * @package Better_Search
 */

// If this file is called directly, then abort execution.
if ( ! defined( 'WPINC' ) ) {
	die( "Aren't you supposed to come here via WP-Admin?" );
}

/**
 * Gets the search results.
 *
 * @since   1.2
 *
 * @param   string     $search_query    Search term.
 * @param   int|string $limit           Maximum number of search results.
 * @return  string     Search results
 */
function get_bsearch_results( $search_query = '', $limit = '' ) {

	if ( ! ( $limit ) ) {
		$limit = isset( $_GET['limit'] ) ? intval( $_GET['limit'] ) : bsearch_get_option( 'limit' ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	}

	// Order by date or by score?
	$bydate = isset( $_GET['bydate'] ) ? intval( $_GET['bydate'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

	$topscore = 0;

	$matches  = get_bsearch_matches( $search_query, $bydate );   // Fetch the search results for the search term stored in $search_query.
	$searches = $matches[0];    // 0 index contains the search results always.

	if ( $searches ) {
		$topscore = max( wp_list_pluck( (array) $searches, 'score' ) );
		$numrows  = count( $searches );
	} else {
		$numrows = 1;
	}

	$match_range = get_bsearch_range( $numrows, $limit );
	$searches    = array_slice( $searches, $match_range[0], $match_range[1] - $match_range[0] + 1 );   // Extract the elements for the page from the complete results array.

	$output = '';

	/* Lets start printing the results */
	if ( '' != $search_query ) { //phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
		if ( $searches ) {
			$output .= get_bsearch_header( $search_query, $numrows, $limit );

			$search_query = preg_quote( $search_query, '/' );
			$keys         = explode( ' ', str_replace( array( "'", '"', '&quot;', '\+', '\-' ), '', $search_query ) );

			foreach ( $searches as $search ) {
				$score      = $search->score;
				$search     = get_post( $search->ID );
				$post_title = get_the_title( $search->ID );

				/* Highlight the search terms in the title */
				if ( bsearch_get_option( 'highlight' ) ) {
					$post_title = preg_replace( '/(?!<[^>]*?>)(' . implode( '|', $keys ) . ')(?![^<]*?>)/iu', '<span class="bsearch_highlight">$1</span>', $post_title );
				}

				$output .= '<article id="post-' . $search->ID . '" ';
				$output .= 'class="' . join( ' ', get_post_class( 'bsearch-post', $search->ID ) ) . '"';
				$output .= '>';

				$output .= '<header class="bsearch-entry-header">';

				$output .= sprintf( '<h2 class="bsearch-entry-title"><a href="%1$s" rel="bookmark">%2$s</a></h2>', esc_url( get_permalink( $search->ID ) ), $post_title );

				$output .= sprintf( '<p><span class="bsearch_score">%1$s</span> &nbsp;&nbsp;&nbsp;&nbsp; <span class="bsearch_date">%2$s</span></p>', get_bsearch_score( $search, $score, $topscore ), get_bsearch_date( $search, __( 'Posted on: ', 'better-search' ) ) );

				$output .= '</header>';

				$output .= '<div class="bsearch-entry-content">';

				if ( bsearch_get_option( 'include_thumb' ) ) {
					$output .= '<p class="bsearch_thumb">' . get_the_post_thumbnail( $search->ID, 'thumbnail' ) . '</p>';
				}

				$excerpt = get_bsearch_excerpt( $search->ID, bsearch_get_option( 'excerpt_length' ) );

				/* Highlight the search terms in the excerpt */
				if ( bsearch_get_option( 'highlight' ) ) {
					$excerpt = preg_replace( '/(?!<[^>]*?>)(' . implode( '|', $keys ) . ')(?![^<]*?>)/iu', '<span class="bsearch_highlight">$1</span>', $excerpt );
				}

				$output .= sprintf( '<p class="bsearch_excerpt">%1$s</p>', $excerpt );

				$output .= '</div>';
				$output .= '</article>';
			} //end of foreach loop

			$output .= get_bsearch_footer( $search_query, $numrows, $limit );

		} else {
			$output .= '<p>';
			$output .= __( 'No results.', 'better-search' );
			$output .= '</p>';
		}
	} else {
		$output .= '<p>';
		$output .= __( 'Please type in your search terms. Use descriptive words since this search is intelligent.', 'better-search' );
		$output .= '</p>';
	}

	if ( bsearch_get_option( 'show_credit' ) ) {
		$output .= '<hr /><p style="text-align:center">';
		$output .= __( 'Powered by ', 'better-search' );
		$output .= '<a href="https://webberzone.com/plugins/better-search/">Better Search plugin</a></p>';
	}

	/**
	 * Filter formatted string with search results
	 *
	 * @since   1.2
	 *
	 * @param   string  $output         Formatted results
	 * @param   string  $search_query   Search query
	 * @param   int     $limit          Number of results per page
	 */
	return apply_filters( 'get_bsearch_results', $output, $search_query, $limit );
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
		bsearch_clean_terms(
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
 * @since   1.2
 *
 * @param   mixed $search_query   The search term.
 * @return  array   Cleaned up search string
 */
function get_bsearch_terms( $search_query = '' ) {

	if ( empty( $search_query ) ) {
		$search_query = get_bsearch_query();
	}
	$s_array[0] = $search_query;

	$use_fulltext = bsearch_get_option( 'use_fulltext' );

	/**
		If use_fulltext is false OR if all the words are shorter than four chars, add the array of search terms.
		Currently this will disable match ranking and won't be quote-savvy.
		If we are using fulltext, turn it off unless there's a search word longer than three chars
		ideally we'd also check against stopwords here
	*/
	$search_words = explode( ' ', $search_query );

	if ( $use_fulltext ) {
		$use_fulltext_proxy = false;
		foreach ( $search_words as $search_word ) {
			if ( strlen( $search_word ) > 3 ) {
				$use_fulltext_proxy = true;
			}
		}
		$use_fulltext = $use_fulltext_proxy;
	}

	if ( ! $use_fulltext ) {
		// Strip out all the fancy characters that fulltext would use.
		$search_query = addslashes_gpc( $search_query );
		$search_query = preg_replace( '/, +/', ' ', $search_query );
		$search_query = str_replace( ',', ' ', $search_query );
		$search_query = str_replace( '"', ' ', $search_query );
		$search_query = trim( $search_query );
		$search_words = explode( ' ', $search_query );

		$s_array[0] = $search_query;    // Save original query at [0].
		$s_array[1] = $search_words;    // Save array of terms at [1].
	}

	/**
	 * Filter array holding the search query and terms
	 *
	 * @since   1.2
	 *
	 * @param   array   $s_array    Original query is at [0] and array of terms at [1]
	 */
	return apply_filters( 'get_bsearch_terms', $s_array );
}


/**
 * Get the matches for the search term.
 *
 * @since   1.2
 *
 * @param   string $search_query    Search terms array.
 * @param   bool   $bydate         Sort by date flag.
 * @return  array   Search results
 */
function get_bsearch_matches( $search_query, $bydate ) {
	global $wpdb;

	// if there are two items in $search_info, the string has been broken into separate terms that
	// are listed at $search_info[1]. The cleaned-up version of $search_query is still at the zero index.
	// This is when fulltext is disabled, and we search using LIKE.
	$search_info = get_bsearch_terms( $search_query );

	// Get search transient.
	$search_query_transient = 'bs_' . preg_replace( '/[^A-Za-z0-9\-]/', '', str_replace( ' ', '', $search_query ) );

	/**
	 * Filter name of the search transient
	 *
	 * @since   2.1.0
	 *
	 * @param   string  $search_query_transient Transient name
	 * @param   array   $search_query   Search query
	 */
	$search_query_transient = apply_filters( 'bsearch_transient_name', $search_query_transient, $search_query );
	$search_query_transient = substr( $search_query_transient, 0, 40 ); // Name of the transient limited to 40 chars.

	$matches = get_transient( $search_query_transient );

	if ( $matches ) {

		if ( isset( $matches['search_query'] ) ) {

			if ( $matches['search_query'] == $search_query ) { //phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
				$results = $matches[0];

				/**
				 * Filter array holding the search results
				 *
				 * @since   1.2
				 *
				 * @param   object  $matches    Search results object
				 * @param   array   $search_info    Search query
				 */
				return apply_filters( 'get_bsearch_matches', $matches, $search_info );

			}
		}
	}

	$boolean_mode      = bsearch_get_option( 'boolean_mode' );
	$aggressive_search = bsearch_get_option( 'aggressive_search' );

	// If no transient is set.
	if ( ! isset( $results ) ) {
		$sql = bsearch_sql_prepare( $search_info, $boolean_mode, $bydate );

		$results = $wpdb->get_results( $sql ); //phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	}

	// If no results are found then force BOOLEAN mode only if this isn't ON before.
	if ( ! $results && ! $boolean_mode && $aggressive_search ) {
		$sql = bsearch_sql_prepare( $search_info, 1, $bydate );

		$results = $wpdb->get_results( $sql ); //phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	}

	// If no results are found then force LIKE mode.
	if ( ! $results && $aggressive_search ) {
		// Strip out all the fancy characters that fulltext would use.
		$search_query = addslashes_gpc( $search_query );
		$search_query = preg_replace( '/, +/', ' ', $search_query );
		$search_query = str_replace( ',', ' ', $search_query );
		$search_query = str_replace( '"', ' ', $search_query );
		$search_query = trim( $search_query );
		$search_words = explode( ' ', $search_query );

		$s_array[0] = $search_query;    // Save original query at [0].
		$s_array[1] = $search_words;    // Save array of terms at [1].

		$search_info = $s_array;

		$sql = bsearch_sql_prepare( $search_info, 0, $bydate );

		$results = $wpdb->get_results( $sql ); //phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	}

	$matches[0]              = $results;
	$matches['search_query'] = $search_query;

	if ( bsearch_get_option( 'cache' ) ) {
		// Set search transient.
		set_transient( $search_query_transient, $matches, 7200 );
	}

	/**
	 * Described in better-search.php
	 */
	return apply_filters( 'get_bsearch_matches', $matches, $search_info );
}


/**
 * Returns an array with the first and last indices to be displayed on the page.
 *
 * @since   1.2
 *
 * @param   int $numrows    Total results.
 * @param   int $limit      Results per page.
 * @return  array   First and last indices to be displayed on the page
 */
function get_bsearch_range( $numrows, $limit ) {

	if ( ! ( $limit ) ) {
		$limit = isset( $_GET['limit'] ) ? intval( $_GET['limit'] ) : bsearch_get_option( 'limit' ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	}
	$page = isset( $_GET['bpaged'] ) ? intval( wp_unslash( $_GET['bpaged'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

	$last = min( $page + $limit - 1, $numrows - 1 );

	$match_range = array( $page, $last );

	/**
	 * Filter array with the first and last indices to be displayed on the page.
	 *
	 * @since   1.3
	 *
	 * @param   array   $match_range    First and last indices to be displayed on the page
	 * @param   int     $numrows        Total results
	 * @param   int     $limit          Results per page
	 */
	return apply_filters( 'get_bsearch_range', $match_range, $numrows, $limit );
}

