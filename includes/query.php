<?php
/**
 * Template functions used by Better Search
 *
 * @package Better_Search
 */

// If this file is called directly, then abort execution.
if ( ! defined( 'WPINC' ) ) {
	die( "Aren't you supposed to come here via WP-Admin?" );
}

/**
 * Returns an array with the first and last indices to be displayed on the page.
 *
 * @since   2.0.0
 *
 * @param   array $search_info    Search query.
 * @param   bool  $boolean_mode   Set BOOLEAN mode for FULLTEXT searching.
 * @param   bool  $bydate         Sort by date.
 * @return  array   First and last indices to be displayed on the page
 */
function bsearch_sql_prepare( $search_info, $boolean_mode, $bydate ) {
	global $wpdb;

	// Initialise some variables.
	$fields  = '';
	$where   = '';
	$join    = '';
	$groupby = '';
	$orderby = '';
	$limits  = '';

	$post_types = bsearch_post_types();

	// Create a FULLTEXT clause only if there is no second element of the $search_info array. Use LIKE otherwise.
	$use_fulltext = count( $search_info ) > 1 ? false : true;

	// Set BOOLEAN Mode.
	$boolean_mode = ( $boolean_mode ) ? ' IN BOOLEAN MODE' : '';

	$args = array(
		'use_fulltext' => $use_fulltext,
		'boolean_mode' => $boolean_mode,
		'bydate'       => $bydate,
		'post_types'   => $post_types,
	);

	$fields  = bsearch_posts_fields( $search_info[0], $args );
	$join    = bsearch_posts_join( $search_info[0], $args );
	$where   = bsearch_posts_where( $search_info, $args );
	$orderby = bsearch_posts_orderby( $search_info[0], $args );
	$groupby = bsearch_posts_groupby( $search_info[0], $args );
	$limits  = bsearch_posts_limits( $search_info[0], $args );

	if ( ! empty( $groupby ) ) {
		$groupby = 'GROUP BY ' . $groupby;
	}
	if ( ! empty( $orderby ) ) {
		$orderby = 'ORDER BY ' . $orderby;
	}
	if ( ! empty( $limits ) ) {
		$orderby = 'LIMIT ' . $limits;
	}

	$sql = "SELECT DISTINCT $fields FROM $wpdb->posts $join WHERE 1=1 $where $groupby $orderby $limits";

	/**
	 * Filter MySQL string used to fetch results.
	 *
	 * @since   1.3
	 *
	 * @param   string  $sql            MySQL string
	 * @param   array   $search_info    Search query
	 * @param   bool    $boolean_mode   Set BOOLEAN mode for FULLTEXT searching
	 * @param   bool    $bydate         Sort by date?
	 */
	return apply_filters( 'bsearch_sql_prepare', $sql, $search_info, $boolean_mode, $bydate );
}


/**
 * Get the MATCH field of the query
 *
 * @since 2.2.0
 *
 * @param string $search_query Search query.
 * @param array  $args Array of arguments.
 * @return string MATCH field
 */
function bsearch_posts_match_field( $search_query, $args = array() ) {
	global $wpdb;

	$weight_title   = bsearch_get_option( 'weight_title' );
	$weight_content = bsearch_get_option( 'weight_content' );
	$boolean_mode   = $args['boolean_mode'];
	$search_query   = str_replace( '&quot;', '"', $search_query );

	// Create the base MATCH part of the FIELDS clause.
	if ( $args['use_fulltext'] ) {
		$field_args = array(
			$search_query,
			$weight_title,
			$search_query,
			$weight_content,
		);

		$field_score  = ", (MATCH({$wpdb->posts}.post_title) AGAINST ('%s' {$boolean_mode} ) * %d ) + ";
		$field_score .= "(MATCH({$wpdb->posts}.post_content) AGAINST ('%s' {$boolean_mode} ) * %d ) ";
		$field_score  = $wpdb->prepare( $field_score, $field_args ); //phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$field_score  = stripslashes( $field_score );
	} else {
		$field_score = ', 0 ';
	}

	$field_score .= 'AS score ';

	/**
	 * Filter the MATCH part of the FIELDS clause of the query.
	 *
	 * @since   2.0.0
	 *
	 * @param string   $field_score     The MATCH section of the FIELDS clause of the query, i.e. score
	 * @param string   $search_query    Search query
	 * @param int      $weight_title    Weight of title
	 * @param int      $weight_content  Weight of content
	 * @param array    $args            Array of arguments
	 */
	return apply_filters( 'bsearch_posts_match_field', $field_score, $search_query, $weight_title, $weight_content, $args );

}


/**
 * Get the Fields clause for the Better Search query.
 *
 * @since 2.2.0
 *
 * @param  string $search_query Search query.
 * @param  array  $args Array of arguments.
 * @return string Fields clause
 */
function bsearch_posts_fields( $search_query, $args = array() ) {
	global $wpdb;

	// Parse incomming $args into an array and merge it with $defaults.
	$args = wp_parse_args( $args, bsearch_query_default_args() );

	$fields = " {$wpdb->posts}.ID as ID";

	$fields .= bsearch_posts_match_field( $search_query, $args );

	/**
	 * Filter the SELECT clause of the query.
	 *
	 * @since   2.0.0
	 *
	 * @param string   $fields          The SELECT clause of the query.
	 * @param string   $search_query    Search query
	 * @param array    $args            Array of arguments
	 */
	return apply_filters( 'bsearch_posts_fields', $fields, $search_query, $args );

}


/**
 * Get the MATCH clause for the Better Search WHERE clause.
 *
 * @since 2.2.0
 *
 * @param  string $search_query Search query.
 * @param  array  $args Array of arguments.
 * @return string MATCH clause
 */
function bsearch_posts_match( $search_query, $args = array() ) {
	global $wpdb;

	// Parse incomming $args into an array and merge it with $defaults.
	$args = wp_parse_args( $args, bsearch_query_default_args() );

	$boolean_mode = $args['boolean_mode'];

	$search_query = str_replace( '&quot;', '"', $search_query );

	// Construct the MATCH part of the WHERE clause.
	$match = " AND MATCH ({$wpdb->posts}.post_title,{$wpdb->posts}.post_content) AGAINST ('%s' {$boolean_mode} ) ";

	$match = $wpdb->prepare( $match, $search_query ); //phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	$match = stripslashes( $match );

	/**
	 * Filter the MATCH clause of the query.
	 *
	 * @since   2.0.0
	 *
	 * @param string   $match           The MATCH section of the WHERE clause of the query
	 * @param string   $search_query    Search query
	 * @param array    $args            Array of arguments
	 */
	return apply_filters( 'bsearch_posts_match', $match, $search_query, $args );

}


/**
 * Get the WHERE clause.
 *
 * @since 2.2.0
 *
 * @param  array $search_info Search query. This will have two elemnts if we're using LIKE.
 * @param  array $args Array of arguments.
 * @return string WHERE clause
 */
function bsearch_posts_where( $search_info, $args = array() ) {
	global $wpdb;

	// Parse incomming $args into an array and merge it with $defaults.
	$args = wp_parse_args( $args, bsearch_query_default_args() );

	$n = '%';

	if ( ! $args['use_fulltext'] ) {

		$search_terms    = $search_info[1];
		$no_search_terms = count( $search_terms );

		// Create the WHERE Clause.
		$where  = ' AND ( ';
		$where .= $wpdb->prepare(
			" (({$wpdb->posts}.post_title LIKE %s) OR ({$wpdb->posts}.post_content LIKE %s)) ",
			$n . $search_terms[0] . $n,
			$n . $search_terms[0] . $n
		);

		for ( $i = 1; $i < $no_search_terms; $i++ ) {
			$where .= $wpdb->prepare(
				" AND (({$wpdb->posts}.post_title LIKE %s) OR ({$wpdb->posts}.post_content LIKE %s)) ",
				$n . $search_terms[ $i ] . $n,
				$n . $search_terms[ $i ] . $n
			);
		}

		$where .= $wpdb->prepare(
			" OR ({$wpdb->posts}.post_title LIKE %s) OR ({$wpdb->posts}.post_content LIKE %s) ",
			$n . $search_terms[0] . $n,
			$n . $search_terms[0] . $n
		);

		$where .= ' ) ';

	} else {

		$where = bsearch_posts_match( $search_info[0], $args );
	}

	$where .= " AND ({$wpdb->posts}.post_status = 'publish' OR {$wpdb->posts}.post_status = 'inherit')";

	// Array of post types.
	if ( $args['post_types'] ) {
		$where .= " AND {$wpdb->posts}.post_type IN ('" . join( "', '", $args['post_types'] ) . "') ";
	}

	/**
	 * Filter the WHERE clause of the query.
	 *
	 * @since   2.0.0
	 *
	 * @param string   $where          The WHERE clause of the query
	 * @param string   $search_info[0] Search query
	 * @param array    $args           Array of arguments
	 */
	return apply_filters( 'bsearch_posts_where', $where, $search_info[0], $args );

}


/**
 * Get the ORDERBY clause.
 *
 * @since 2.2.0
 *
 * @param  string $search_query Search query.
 * @param  array  $args Array of arguments.
 * @return string ORDERBY clause
 */
function bsearch_posts_orderby( $search_query, $args = array() ) {

	// Parse incomming $args into an array and merge it with $defaults.
	$args = wp_parse_args( $args, bsearch_query_default_args() );

	// ORDER BY clause.
	if ( $args['bydate'] || ! $args['use_fulltext'] ) {
		$orderby = ' post_date DESC ';
	} else {
		$orderby = ' score DESC ';
	}

	/**
	 * Filter the ORDER BY clause of the query.
	 *
	 * @since   2.0.0
	 *
	 * @param string   $orderby      The ORDER BY clause of the query
	 * @param string   $search_query Search query
	 * @param array    $args         Array of arguments
	 */
	return apply_filters( 'bsearch_posts_orderby', $orderby, $search_query, $args );
}


/**
 * Get the GROUPBY clause.
 *
 * @since 2.2.0
 *
 * @param  string $search_query Search query.
 * @param  array  $args Array of arguments.
 * @return string GROUPBY clause
 */
function bsearch_posts_groupby( $search_query, $args = array() ) {

	// Parse incomming $args into an array and merge it with $defaults.
	$args = wp_parse_args( $args, bsearch_query_default_args() );

	$groupby = '';

	/**
	 * Filter the GROUP BY clause of the query.
	 *
	 * @since   2.0.0
	 *
	 * @param string   $groupby      The GROUP BY clause of the query
	 * @param string   $search_query Search query
	 * @param array    $args         Array of arguments
	 */
	return apply_filters( 'bsearch_posts_groupby', $groupby, $search_query, $args );
}


/**
 * Get the JOIN clause.
 *
 * @since 2.2.0
 *
 * @param  string $search_query Search query.
 * @param  array  $args Array of arguments.
 * @return string JOIN clause
 */
function bsearch_posts_join( $search_query, $args = array() ) {

	// Parse incomming $args into an array and merge it with $defaults.
	$args = wp_parse_args( $args, bsearch_query_default_args() );

	$join = '';

	/**
	 * Filter the JOIN clause of the query.
	 *
	 * @since   2.0.0
	 *
	 * @param string   $join         The JOIN clause of the query
	 * @param string   $search_query Search query
	 * @param array    $args         Array of arguments
	 */
	return apply_filters( 'bsearch_posts_join', $join, $search_query, $args );
}


/**
 * Get the LIMITS clause.
 *
 * @since 2.2.0
 *
 * @param  string $search_query Search query.
 * @param  array  $args Array of arguments.
 * @return string LIMITS clause
 */
function bsearch_posts_limits( $search_query, $args = array() ) {

	// Parse incomming $args into an array and merge it with $defaults.
	$args = wp_parse_args( $args, bsearch_query_default_args() );

	$limits = '';

	/**
	 * Filter the LIMITS clause of the query.
	 *
	 * @since   2.0.0
	 *
	 * @param string   $limits       The LIMITS clause of the query
	 * @param string   $search_query Search query
	 * @param array    $args         Array of arguments
	 */
	return apply_filters( 'bsearch_posts_limits', $limits, $search_query, $args );
}


/**
 * Get default query arguments.
 *
 * @return array Default quesry arguments
 */
function bsearch_query_default_args() {

	// if there are two items in $search_info, the string has been broken into separate terms that
	// are listed at $search_info[1]. The cleaned-up version of $search_query is still at the zero index.
	// This is when fulltext is disabled, and we search using LIKE.
	$search_info = get_bsearch_terms();

	// Create a FULLTEXT clause only if there is no second element of the $search_info array. Use LIKE otherwise.
	$use_fulltext = count( $search_info ) > 1 ? false : true;

	$post_types = bsearch_post_types();

	$args = array(
		'use_fulltext' => $use_fulltext,
		'boolean_mode' => bsearch_get_option( 'boolean_mode' ) ? ' IN BOOLEAN MODE' : '',
		'bydate'       => 0,
		'post_types'   => bsearch_post_types(),
	);

	/**
	 * Filter default query arguments.
	 *
	 * @return array Default quesry arguments
	 */
	return apply_filters( 'bsearch_query_default_args', $args );
}


/**
 * Get the Better Search post types.
 *
 * @return array Post types
 */
function bsearch_post_types() {
	// If post_types is empty or contains a query string then use parse_str else consider it comma-separated.
	$post_types_from_db = bsearch_get_option( 'post_types' );

	if ( ! empty( $post_types_from_db ) && is_array( $post_types_from_db ) ) {
		$post_types = $post_types_from_db;
	} elseif ( ! empty( $post_types_from_db ) && false === strpos( $post_types_from_db, '=' ) ) {
		$post_types = explode( ',', $post_types_from_db );
	} else {
		parse_str( $post_types_from_db, $post_types );  // Save post types in $post_types variable.
	}

	// If post_types is empty or if we want all the post types.
	if ( empty( $post_types ) || 'all' === $post_types_from_db ) {
		$post_types = get_post_types(
			array(
				'public' => true,
			)
		);
	}

	return $post_types;
}
