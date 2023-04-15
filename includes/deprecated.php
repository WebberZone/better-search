<?php
/**
 * Deprecated functions
 *
 * @package Better_Search
 */

// If this file is called directly, then abort execution.
if ( ! defined( 'WPINC' ) ) {
	die( "Aren't you supposed to come here via WP-Admin?" );
}

/**
 * Holds the URL for Better Search folder
 *
 * @since   1.0
 *
 * @deprecated 2.2.0
 *
 * @var string
 */
$bsearch_url = plugins_url() . '/' . plugin_basename( __DIR__ );


/**
 * Default options.
 *
 * @since 1.0
 * @deprecated 3.0.0
 *
 * @return  array   Default options array
 */
function bsearch_default_options() {

	_deprecated_function( __FUNCTION__, '2.2.0' );

	$title       = __( '<h3>Popular Searches</h3>', 'better-search' );
	$title_daily = __( '<h3>Weekly Popular Searches</h3>', 'better-search' );

	// Get relevant post types.
	$args       = array(
		'public'   => true,
		'_builtin' => true,
	);
	$post_types = http_build_query( get_post_types( $args ), '', '&' );

	$custom_css = '
#bsearchform { margin: 20px; padding: 20px; }
#heatmap { margin: 20px; padding: 20px; border: 1px dashed #ccc }
.bsearch_results_page { max-width:90%; margin: 20px; padding: 20px; }
.bsearch_footer { text-align: center; }
.bsearch_highlight { background:#ffc; }
	';

	$badwords = array(
		'anal',
		'anus',
		'bastard',
		'beastiality',
		'bestiality',
		'bewb',
		'bitch',
		'blow',
		'blumpkin',
		'boob',
		'cawk',
		'cock',
		'choad',
		'cooter',
		'cornhole',
		'cum',
		'cunt',
		'dick',
		'dildo',
		'dong',
		'dyke',
		'douche',
		'fag',
		'faggot',
		'fart',
		'foreskin',
		'fuck',
		'fuk',
		'gangbang',
		'gook',
		'handjob',
		'homo',
		'honkey',
		'humping',
		'jiz',
		'jizz',
		'kike',
		'kunt',
		'labia',
		'muff',
		'nigger',
		'nutsack',
		'pen1s',
		'penis',
		'piss',
		'poon',
		'poop',
		'porn',
		'punani',
		'pussy',
		'queef',
		'queer',
		'quim',
		'rimjob',
		'rape',
		'rectal',
		'rectum',
		'semen',
		'shit',
		'slut',
		'spick',
		'spoo',
		'spooge',
		'taint',
		'titty',
		'titties',
		'twat',
		'vagina',
		'vulva',
		'wank',
		'whore',
	);

	$bsearch_settings = array(

		/* General options */
		'seamless'         => true,             // Seamless integration mode.
		'track_popular'    => true,        // Track the popular searches.
		'track_admins'     => true,         // Track Admin searches.
		'track_editors'    => true,        // Track Editor searches.
		'cache'            => true,                // Enable Cache.
		'meta_noindex'     => true,         // Add noindex,follow meta tag to head.
		'show_credit'      => false,         // Add link to plugin page of my blog in top posts list.

		/* Search options */
		'limit'            => '10',                // Search results per page.
		'post_types'       => $post_types,    // WordPress custom post types.

		'use_fulltext'     => true,         // Full text searches.
		'weight_content'   => '10',       // Weightage for content.
		'weight_title'     => '1',          // Weightage for title.
		'boolean_mode'     => false,        // Turn BOOLEAN mode on if true.

		'highlight'        => false,           // Highlight search terms.
		'excerpt_length'   => '100',      // Length of excerpt in words.
		'include_thumb'    => false,       // Include thumbnail in search results.
		'link_new_window'  => false,     // Open link in new window - Includes target="_blank" to links.
		'link_nofollow'    => true,        // Includes rel="nofollow" to links in heatmap.

		'badwords'         => implode( ',', $badwords ),        // Bad words filter.

		/* Heatmap options */
		'include_heatmap'  => false,     // Include heatmap of searches in the search page.
		'title'            => $title,              // Title of Search Heatmap.
		'title_daily'      => $title_daily,  // Title of Daily Search Heatmap.
		'daily_range'      => '7',           // Daily Popular will contain posts of how many days?

		'heatmap_limit'    => '30',        // Heatmap - Maximum number of searches to display in heatmap.
		'heatmap_smallest' => '10',     // Heatmap - Smallest Font Size.
		'heatmap_largest'  => '20',      // Heatmap - Largest Font Size.
		'heatmap_unit'     => 'pt',         // Heatmap - We'll use pt for font size.
		'heatmap_cold'     => 'CCCCCC',     // Heatmap - cold searches.
		'heatmap_hot'      => '000000',      // Heatmap - hot searches.
		'heatmap_before'   => '',         // Heatmap - Display before each search term.
		'heatmap_after'    => '&nbsp;',    // Heatmap - Display after each search term.

		/* Custom styles */
		'custom_CSS'       => $custom_css,    // Custom CSS.

	);

	/*
	 * Filters default options for Better Search
	 *
	 * @since   2.0.0
	 *
	 * @param   array   $bsearch_settings   default options
	 */
	return apply_filters( 'bsearch_default_options', $bsearch_settings );
}


/**
 * Function to read options from the database.
 *
 * @since 1.0
 * @deprecated 3.0.0
 *
 * @return  array   Better Search options array
 */
function bsearch_read_options() {

	_deprecated_function( __FUNCTION__, '2.2.0', 'bsearch_get_settings()' );

	return bsearch_get_settings();
}


/**
 * Fetches the search results for the current search query and returns a comma separated string of IDs.
 *
 * @since   1.3.3
 *
 * @deprecated 2.2.0
 *
 * @return  string  Blank string or comma separated string of search results' IDs
 */
function bsearch_clause_prepare() {
	global $wp_query, $wpdb;

	_deprecated_function( __FUNCTION__, '2.2.0' );

	$search_ids = '';

	if ( $wp_query->is_search ) {
		$search_query = get_bsearch_query();

		$matches = get_bsearch_matches( $search_query, 0 );     // Fetch the search results for the search term stored in $search_query.

		$searches = $matches[0];        // 0 index contains the search results always

		if ( $searches ) {
			$search_ids = implode( ',', wp_list_pluck( $searches, 'ID' ) );
		}
	}

	/**
	 * Filters the string of SEARCH IDs returned
	 *
	 * @since   2.0.0
	 *
	 * @return  string  $search_ids Blank string or comma separated string of search results' IDs
	 */
	return apply_filters( 'bsearch_clause_prepare', $search_ids );
}


/**
 * Function to update search count.
 *
 * @since   1.0
 * @deprecated 2.2.4
 *
 * @param   string $search_query   Search query.
 * @return  string  Search tracker code
 */
function bsearch_increment_counter( $search_query ) {

	_deprecated_function( __FUNCTION__, '2.2.4' );

	$output = '';

	/**
	 * Filter the search tracker code
	 *
	 * @since   2.0.0
	 *
	 * @param   string  $output         Formatted output string
	 * @param   string  $search_query   Search query
	 */
	return apply_filters( 'bsearch_increment_counter', $output, $search_query );
}

/**
 * Function to return the header links of the results page.
 *
 * @since   1.2
 * @deprecated 3.0.0
 *
 * @param   string $search_query   Search string.
 * @param   int    $numrows        Total number of results.
 * @param   int    $limit          Results per page.
 * @return  string  Formatted header table of search results pages
 */
function get_bsearch_header( $search_query, $numrows, $limit ) {

	_deprecated_function( __FUNCTION__, '3.0.0', 'the_bsearch_header' );

	$args = array(
		'echo'         => false,
		'limit'        => $limit,
		'found_posts'  => $numrows,
		'search_query' => $search_query,
	);

	return the_bsearch_header( $args );
}


/**
 * Function to return the footer links of the results page.
 *
 * @since   1.2
 * @deprecated 3.0.0
 *
 * @param   string $search_query   Search string.
 * @param   int    $numrows        Total results.
 * @param   int    $limit          Results per page.
 * @return  string  Formatted footer of search results pages
 */
function get_bsearch_footer( $search_query, $numrows, $limit ) {

	_deprecated_function( __FUNCTION__, '3.0.0', 'get_the_posts_pagination' );

	$args = array(
		'mid_size'  => 3,
		'prev_text' => esc_html__( '« Previous', 'better-search' ),
		'next_text' => esc_html__( 'Next »', 'better-search' ),
	);

	return get_the_posts_pagination( $args );
}


/**
 * Function to convert the mySQL score to percentage.
 *
 * @since   1.2
 * @deprecated 3.0.0
 *
 * @param   object $search     Search result object.
 * @param   int    $score      Score for the search result.
 * @param   int    $topscore   Score for the most relevant search result.
 * @return  int     Score converted to percentage
 */
function get_bsearch_score( $search, $score, $topscore ) {

	_deprecated_function( __FUNCTION__, '3.0.0', 'the_bsearch_score' );

	$args = array(
		'score'    => $score,
		'topscore' => $topscore,
		'echo'     => false,
	);

	return the_bsearch_score( $args );
}

/**
 * Gets the search results.
 *
 * @since   1.2
 * @deprecated 3.0.0
 *
 * @param   string     $search_query    Search term.
 * @param   int|string $limit           Maximum number of search results.
 * @return  string     Search results
 */
function get_bsearch_results( $search_query = '', $limit = '' ) {

	_deprecated_function( __FUNCTION__, '3.0.0' );

	global $bsearch_error;

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
	if ( '' !== $search_query ) {
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
					$post_title = bsearch_highlight( $post_title, $keys );
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
					$output .= '<p class="bsearch_thumb_wrapper">';
					$output .= bsearch_get_the_post_thumbnail(
						array(
							'post' => $search,
							'size' => 'thumbnail',
						)
					);
					$output .= '</p>';
				}

				$excerpt = get_bsearch_excerpt( $search->ID, bsearch_get_option( 'excerpt_length' ) );

				/* Highlight the search terms in the excerpt */
				if ( bsearch_get_option( 'highlight' ) ) {
					$excerpt = bsearch_highlight( $excerpt, $keys );
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
		if ( '' !== $bsearch_error->get_error_message( 'bsearch_banned' ) && bsearch_get_option( 'banned_stop_search' ) ) {
			foreach ( $bsearch_error->get_error_messages() as $error ) {
				$output .= $error . '<br/>';
			}
		} else {
			$output .= __( 'Please type in your search terms. Use descriptive words since this search is intelligent.', 'better-search' );
		}
		$output .= '</p>';
	}

	if ( bsearch_get_option( 'show_credit' ) ) {
		$output .= bsearch_get_credit_link();
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
 * Get the matches for the search term.
 *
 * @since   1.2
 * @deprecated 3.0.0
 *
 * @param   string $search_query    Search terms array.
 * @param   bool   $bydate         Sort by date flag.
 * @return  array   Search results
 */
function get_bsearch_matches( $search_query, $bydate ) {

	_deprecated_function( __FUNCTION__, '3.0.0' );

	global $wpdb, $bsearch_error;

	// if there are two items in $search_info, the string has been broken into separate terms that
	// are listed at $search_info[1]. The cleaned-up version of $search_query is still at the zero index.
	// This is when fulltext is disabled, and we search using LIKE.
	$search_info = get_bsearch_terms( $search_query );

	if ( '' !== $bsearch_error->get_error_message( 'bsearch_banned' ) && bsearch_get_option( 'banned_stop_search' ) ) {
		$matches[0]              = array();
		$matches['search_query'] = $search_query;

		return $matches;
	}

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

			if ( $matches['search_query'] === $search_query ) {
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
		set_transient( $search_query_transient, $matches, bsearch_get_option( 'cache_time' ) );
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
 * @deprecated 3.0.0
 *
 * @param   int $numrows    Total results.
 * @param   int $limit      Results per page.
 * @return  array   First and last indices to be displayed on the page
 */
function get_bsearch_range( $numrows, $limit ) {

	_deprecated_function( __FUNCTION__, '3.0.0' );

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


/**
 * Returns an array with the first and last indices to be displayed on the page.
 *
 * @since 2.0.0
 * @deprecated 3.0.0
 *
 * @param   array $search_info    Search query.
 * @param   bool  $boolean_mode   Set BOOLEAN mode for FULLTEXT searching.
 * @param   bool  $bydate         Sort by date.
 * @return  array   First and last indices to be displayed on the page
 */
function bsearch_sql_prepare( $search_info, $boolean_mode, $bydate ) {

	_deprecated_function( __FUNCTION__, '3.0.0' );

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
	$use_fulltext = $search_info[2];

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
		$limits = 'LIMIT ' . $limits;
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
 * @deprecated 3.0.0
 *
 * @param string $search_query Search query.
 * @param array  $args Array of arguments.
 * @return string MATCH field
 */
function bsearch_posts_match_field( $search_query, $args = array() ) {

	_deprecated_function( __FUNCTION__, '3.0.0' );

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

	/** This filter has been defined in class-better-search.php */
	return apply_filters( 'bsearch_posts_match_field', $field_score, $search_query, $weight_title, $weight_content, $args );
}


/**
 * Get the Fields clause for the Better Search query.
 *
 * @since 2.2.0
 * @deprecated 3.0.0
 *
 * @param  string $search_query Search query.
 * @param  array  $args Array of arguments.
 * @return string Fields clause
 */
function bsearch_posts_fields( $search_query, $args = array() ) {

	_deprecated_function( __FUNCTION__, '3.0.0' );

	global $wpdb;

	// Parse incomming $args into an array and merge it with $defaults.
	$args = wp_parse_args( $args, bsearch_query_default_args() );

	$fields = " {$wpdb->posts}.ID as ID";

	$fields .= bsearch_posts_match_field( $search_query, $args );

	/** This filter has been defined in class-better-search.php */
	return apply_filters( 'bsearch_posts_fields', $fields, $search_query, $args );
}


/**
 * Get the MATCH clause for the Better Search WHERE clause.
 *
 * @since 2.2.0
 * @deprecated 3.0.0
 *
 * @param  string $search_query Search query.
 * @param  array  $args Array of arguments.
 * @return string MATCH clause
 */
function bsearch_posts_match( $search_query, $args = array() ) {

	_deprecated_function( __FUNCTION__, '3.0.0' );

	global $wpdb;

	// Parse incomming $args into an array and merge it with $defaults.
	$args = wp_parse_args( $args, bsearch_query_default_args() );

	$boolean_mode = $args['boolean_mode'];

	$search_query = str_replace( '&quot;', '"', $search_query );

	// Construct the MATCH part of the WHERE clause.
	$match = " AND MATCH ({$wpdb->posts}.post_title,{$wpdb->posts}.post_content) AGAINST ('%s' {$boolean_mode} ) ";

	$match = $wpdb->prepare( $match, $search_query ); //phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	$match = stripslashes( $match );

	/** This filter has been defined in class-better-search.php */
	return apply_filters( 'bsearch_posts_match', $match, $search_query, $args );
}


/**
 * Get the WHERE clause.
 *
 * @since 2.2.0
 * @deprecated 3.0.0
 *
 * @param  array $search_info Search query. This will have two elemnts if we're using LIKE.
 * @param  array $args Array of arguments.
 * @return string WHERE clause
 */
function bsearch_posts_where( $search_info, $args = array() ) {

	_deprecated_function( __FUNCTION__, '3.0.0' );

	global $wpdb, $bsearch_error;

	if ( '' !== $bsearch_error->get_error_message( 'bsearch_banned' ) && bsearch_get_option( 'banned_stop_search' ) ) {
		return ' AND 1=0 ';
	}

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
 * @deprecated 3.0.0
 *
 * @param  string $search_query Search query.
 * @param  array  $args Array of arguments.
 * @return string ORDERBY clause
 */
function bsearch_posts_orderby( $search_query, $args = array() ) {

	_deprecated_function( __FUNCTION__, '3.0.0' );

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
 * @deprecated 3.0.0
 *
 * @param  string $search_query Search query.
 * @param  array  $args Array of arguments.
 * @return string GROUPBY clause
 */
function bsearch_posts_groupby( $search_query, $args = array() ) {

	_deprecated_function( __FUNCTION__, '3.0.0' );

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
 * @deprecated 3.0.0
 *
 * @param  string $search_query Search query.
 * @param  array  $args Array of arguments.
 * @return string JOIN clause
 */
function bsearch_posts_join( $search_query, $args = array() ) {

	_deprecated_function( __FUNCTION__, '3.0.0' );

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
 * @deprecated 3.0.0
 *
 * @param  string $search_query Search query.
 * @param  array  $args Array of arguments.
 * @return string LIMITS clause
 */
function bsearch_posts_limits( $search_query, $args = array() ) {

	_deprecated_function( __FUNCTION__, '3.0.0' );

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
 * @deprecated 3.0.0
 *
 * @return array Default quesry arguments
 */
function bsearch_query_default_args() {

	_deprecated_function( __FUNCTION__, '3.0.0' );

	// if there are two items in $search_info, the string has been broken into separate terms that
	// are listed at $search_info[1]. The cleaned-up version of $search_query is still at the zero index.
	// This is when fulltext is disabled, and we search using LIKE.
	$search_info = get_bsearch_terms();

	$args = array(
		'use_fulltext' => isset( $search_info[2] ) ? $search_info[2] : false,
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
 * @deprecated 3.0.0
 *
 * @return array Post types
 */
function bsearch_post_types() {

	_deprecated_function( __FUNCTION__, '3.0.0' );

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

/**
 * Filter JOIN clause of bsearch query to add taxonomy tables.
 *
 * @since 2.4.0
 * @deprecated 3.0.0
 *
 * @param   mixed $join Join clause.
 * @return  string  Filtered JOIN clause
 */
function bsearch_exclude_categories_join( $join ) {

	_deprecated_function( __FUNCTION__, '3.0.0' );

	global $wpdb;

	if ( '' !== bsearch_get_option( 'exclude_categories' ) ) {

		$sql  = $join;
		$sql .= " LEFT JOIN $wpdb->term_relationships AS excat_tr ON ($wpdb->posts.ID = excat_tr.object_id) ";
		$sql .= " LEFT JOIN $wpdb->term_taxonomy AS excat_tt ON (excat_tr.term_taxonomy_id = excat_tt.term_taxonomy_id) ";

		return $sql;
	} else {
		return $join;
	}
}
add_filter( 'bsearch_posts_join', 'bsearch_exclude_categories_join' );


/**
 * Filter WHERE clause of bsearch query to exclude posts belonging to certain categories.
 *
 * @since 2.4.0
 * @deprecated 3.0.0
 *
 * @param   mixed $where WHERE clause.
 * @return  string  Filtered WHERE clause
 */
function bsearch_exclude_categories_where( $where ) {

	_deprecated_function( __FUNCTION__, '3.0.0' );

	global $wpdb;

	if ( '' === bsearch_get_option( 'exclude_categories' ) ) {
		return $where;
	} else {

		$terms = bsearch_get_option( 'exclude_categories' );

		$sql = $where;

		$sql .= " AND $wpdb->posts.ID NOT IN (
            SELECT object_id
            FROM $wpdb->term_relationships
            WHERE term_taxonomy_id IN ($terms)
        )";

		return $sql;
	}
}
add_filter( 'bsearch_posts_where', 'bsearch_exclude_categories_where' );


/**
 * Filter GROUP BY clause of bsearch query to exclude posts belonging to certain categories.
 *
 * @since 2.4.0
 * @deprecated 3.0.0
 *
 * @param   mixed $groupby GROUP BY clause.
 * @return  string  Filtered GROUP BY clause
 */
function bsearch_exclude_categories_groupby( $groupby ) {

	_deprecated_function( __FUNCTION__, '3.0.0' );

	if ( '' !== bsearch_get_option( 'exclude_categories' ) && '' !== $groupby ) {

		$sql  = $groupby;
		$sql .= ' excat_tt.term_taxonomy_id ';

		return $sql;
	} else {
		return $groupby;
	}
}
add_filter( 'bsearch_posts_groupby', 'bsearch_exclude_categories_groupby' );


/**
 * Function to exclude protected posts.
 *
 * @since 2.2.0
 * @deprecated 3.0.0
 *
 * @param string $where WHERE clause.
 * @return string Updated WHERE clause
 */
function bsearch_exclude_protected( $where ) {

	_deprecated_function( __FUNCTION__, '3.0.0' );

	global $wpdb;

	if ( bsearch_get_option( 'exclude_protected_posts' ) ) {
		$where .= " AND {$wpdb->posts}.post_password = '' ";
	}

	return $where;
}
add_filter( 'bsearch_posts_where', 'bsearch_exclude_protected' );


/**
 * Function to exclude post IDs.
 *
 * @since 2.2.0
 * @deprecated 3.0.0
 *
 * @param string $where WHERE clause.
 * @return string Updated WHERE clause
 */
function bsearch_exclude_post_ids( $where ) {

	_deprecated_function( __FUNCTION__, '3.0.0' );

	global $wpdb;

	$exclude_post_ids = bsearch_get_option( 'exclude_post_ids' );

	if ( ! empty( $exclude_post_ids ) ) {
		$where .= " AND {$wpdb->posts}.ID NOT IN ({$exclude_post_ids}) ";
	}

	return $where;
}
add_filter( 'bsearch_posts_where', 'bsearch_exclude_post_ids' );
