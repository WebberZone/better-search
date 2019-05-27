<?php
/**
 * Seamless mode
 *
 * @package Better_Search
 */

// If this file is called directly, then abort execution.
if ( ! defined( 'WPINC' ) ) {
	die( "Aren't you supposed to come here via WP-Admin?" );
}

/**
 * Modify search results page with results from Better Search. Filters posts_where.
 *
 * @since   1.3.3
 *
 * @param   string $where  WHERE clause of main query.
 * @param   object $query  WordPress query.
 * @return  Formatted WHERE clause
 */
function bsearch_where_clause( $where, $query ) {
	global $wpdb;

	if ( $query->is_search() && bsearch_get_option( 'seamless' ) && ! is_admin() && $query->is_main_query() ) {
		$search_info = get_bsearch_terms();

		// Replace the WHERE clause with our own.
		$where = bsearch_posts_where( $search_info );
	}

	/**
	 * Filters Better Search WHERE clause
	 *
	 * @since   2.0.0
	 *
	 * @param   string  $where  WHERE clause of main query
	 * @param   object  $query  WordPress query
	 */
	return apply_filters( 'bsearch_where_clause', $where, $query );
}
add_filter( 'posts_where', 'bsearch_where_clause', 9, 2 );


/**
 * Modify search results page with results from Better Search. Filters posts_orderby.
 *
 * @since   1.3.3
 *
 * @param   string $orderby    ORDERBY clause of main query.
 * @param   object $query      WordPress query.
 * @return  Formatted ORDERBY clause
 */
function bsearch_orderby_clause( $orderby, $query ) {
	global $wpdb;

	if ( $query->is_search() && bsearch_get_option( 'seamless' ) && ! is_admin() && $query->is_main_query() ) {
		$search_info = get_bsearch_terms();
		$orderby     = bsearch_posts_orderby( $search_info[0] );
	}

	/**
	 * Filters Better Search ORDERBY clause
	 *
	 * @since   2.0.0
	 *
	 * @param   string  $orderby  ORDERBY clause of main query
	 * @param   object  $query  WordPress query
	 */
	return apply_filters( 'bsearch_orderby_clause', $orderby, $query );
}
add_filter( 'posts_orderby', 'bsearch_orderby_clause', 10, 2 );


/**
 * Modify search results page with results from Better Search. Filters posts_orderby.
 *
 * @since   1.3.3
 *
 * @param   string $fields    ORDERBY clause of main query.
 * @param   object $query     WordPress query.
 * @return  Formatted ORDERBY clause
 */
function bsearch_fields_clause( $fields, $query ) {
	global $wpdb;

	if ( $query->is_search() && bsearch_get_option( 'seamless' ) && ! is_admin() && $query->is_main_query() ) {
		$search_info = get_bsearch_terms();
		$fields     .= ', ' . bsearch_posts_fields( $search_info[0] );
	}

	/**
	 * Filters Better Search FIELDS clause
	 *
	 * @since   2.0.0
	 *
	 * @param   string  $fields  FIELDS clause of main query
	 * @param   object  $query  WordPress query
	 */
	return apply_filters( 'bsearch_fields_clause', $fields, $query );
}
add_filter( 'posts_fields', 'bsearch_fields_clause', 10, 2 );

