<?php
/**
 * Seamless mode
 *
 * @package Better_Search
 */


/**
 * Modify search results page with results from Better Search. Filters posts_where.
 *
 * @since	1.3.3
 *
 * @param	string $where  WHERE clause of main query
 * @param	object $query  WordPress query
 * @return	Formatted WHERE clause
 */
function bsearch_where_clause( $where, $query ) {
	global $wpdb, $bsearch_settings;

	if ( $query->is_search() && $bsearch_settings['seamless'] && ! is_admin() && $query->is_main_query() ) {
		$search_ids = bsearch_clause_prepare();

		if ( '' != $search_ids ) {
			$where = " AND {$wpdb->posts}.ID IN ({$search_ids}) ";
		}
	}

	/**
	 * Filters Better Search WHERE clause
	 *
	 * @since	2.0.0
	 *
	 * @param	string	$where	WHERE clause of main query
	 * @param	object	$query	WordPress query
	 */
	return apply_filters( 'bsearch_where_clause', $where, $query );
}
add_filter( 'posts_where' , 'bsearch_where_clause', 10, 2 );


/**
 * Modify search results page with results from Better Search. Filters posts_orderby.
 *
 * @since	1.3.3
 *
 * @param	string $orderby    ORDERBY clause of main query
 * @param	object $query      WordPress query
 * @return	Formatted ORDERBY clause
 */
function bsearch_orderby_clause( $orderby, $query ) {
	global $wpdb, $bsearch_settings;

	if ( $query->is_search() && $bsearch_settings['seamless'] && ! is_admin() && $query->is_main_query() ) {
		$search_ids = bsearch_clause_prepare();

		if ( '' != $search_ids ) {
			$orderby = " FIELD( {$wpdb->posts}.ID, {$search_ids} ) ";
		}
	}

	/**
	 * Filters Better Search ORDERBY clause
	 *
	 * @since	2.0.0
	 *
	 * @param	string	$where	ORDERBY clause of main query
	 * @param	object	$query	WordPress query
	 */
	return apply_filters( 'bsearch_orderby_clause', $orderby, $query );
}
add_filter( 'posts_orderby' , 'bsearch_orderby_clause', 10, 2 );


/**
 * Fetches the search results for the current search query and returns a comma separated string of IDs.
 *
 * @since	1.3.3
 *
 * @return	string	Blank string or comma separated string of search results' IDs
 */
function bsearch_clause_prepare() {
	global $wp_query, $wpdb;

	$search_ids = '';

	if ( $wp_query->is_search ) {
		$search_query = get_bsearch_query();

		$matches = get_bsearch_matches( $search_query, 0 );		// Fetch the search results for the search term stored in $search_query

		$searches = $matches[0];		// 0 index contains the search results always

		if ( $searches ) {
			$search_ids = implode( ',', wp_list_pluck( $searches, 'ID' ) );
		}
	}

	/**
	 * Filters the string of SEARCH IDs returned
	 *
	 * @since	2.0.0
	 *
	 * @return	string	$search_ids	Blank string or comma separated string of search results' IDs
	 */
	return apply_filters( 'bsearch_clause_prepare', $search_ids );
}


