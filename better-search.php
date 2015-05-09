<?php
/**
 * Better Search replaces the default WordPress search with a better search that gives contextual results sorted by relevance
 *
 * Better Search is a plugin that will replace the default WordPress search page
 * with highly relevant search results improving your visitors search experience.
 *
 * @package BSearch
 * @author    Ajay D'Souza <me@ajaydsouza.com>
 * @license   GPL-2.0+
 * @link      http://ajaydsouza.com
 * @copyright 2009-2015 Ajay D'Souza
 *
 *
 * @wordpress-plugin
 * Plugin Name: Better Search
 * Plugin URI:  http://ajaydsouza.com/wordpress/plugins/better-search/
 * Description: Replace the default WordPress search with a contextual search. Search results are sorted by relevancy ensuring a better visitor search experience.
 * Version:     2.0.1
 * Author:      Ajay D'Souza
 * Author URI:  http://ajaydsouza.com/
 * Text Domain:	better-search
 * License:		GPL-2.0+
 * License URI:	http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:	/languages
 * GitHub Plugin URI: https://github.com/ajaydsouza/better-search/
 */

// If this file is called directly, then abort execution.
if ( ! defined( 'WPINC' ) ) {
	die( "Aren't you supposed to come here via WP-Admin?" );
}


/**
 * Holds the filesystem directory path
 *
 * @since	1.3.3
 */
define( 'ALD_BSEARCH_DIR', dirname( __FILE__ ) );


/**
 * Localisation name
 *
 * @since	1.3.3
 */
define( 'BSEARCH_LOCAL_NAME', 'better-search' );

/**
 * Holds the filesystem directory path (with trailing slash) for Better Search
 *
 * @since	1.0
 *
 * @var string
 */
$bsearch_path = plugin_dir_path( __FILE__ );


/**
 * Holds the URL for Better Search folder
 *
 * @since	1.0
 *
 * @var string
 */
$bsearch_url = plugins_url() . '/' . plugin_basename( dirname( __FILE__ ) );


/**
 * Global variable holding the current database version of Better Search
 *
 * @since	1.0
 *
 * @var string
 */
global $bsearch_db_version;
$bsearch_db_version = "1.0";


/**
 * Declare $bsearch_settings global so that it can be accessed in every function
 *
 * @since	1.3
 */
global $bsearch_settings;
$bsearch_settings = bsearch_read_options();


/**
 * Function to load translation files.
 *
 * @since	1.3.3
 */
function bsearch_lang_init() {
	load_plugin_textdomain( BSEARCH_LOCAL_NAME, false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}
add_action( 'init', 'bsearch_lang_init' );


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
	if ( ( stripos( $_SERVER['REQUEST_URI'], '?s=' ) === FALSE ) && ( stripos( $_SERVER['REQUEST_URI'], '/search/' ) === FALSE ) && ( ! is_search() ) ) {
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
	@header( "HTTP/1.1 200 OK", 1 );
	@header( "Status: 200 OK", 1 );

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
		plugin_dir_path( __FILE__ ) . 'templates/template.php',
	);

	foreach( $priority_template_lookup as $exists ) {

		if( file_exists( $exists ) ) {

			include_once( $exists );
			exit;

		}

	}
}
add_action( 'template_redirect', 'bsearch_template_redirect', 1 );


/**
 * Gets the search results
 *
 * @since	1.2
 *
 * @param	string		$search_query	Search term
 * @param	int|string	$limit			Maximum number of search results
 * @return	string		Search results
 */
function get_bsearch_results( $search_query = '', $limit ) {
	global $wpdb, $bsearch_settings;

	if ( ! ( $limit ) ) {
		$limit = isset( $_GET['limit'] ) ? intval( $_GET['limit'] ) : $bsearch_settings['limit']; // Read from GET variable
	}

	$bydate = isset( $_GET['bydate'] ) ? intval( $_GET['bydate'] ) : 0;		// Order by date or by score?

	$topscore = 0;

	$matches = get_bsearch_matches( $search_query, $bydate );		// Fetch the search results for the search term stored in $search_query
	$searches = $matches[0];		// 0 index contains the search results always

	if ( $searches ) {
		foreach ( $searches as $search ) {
			if ( $topscore < $search->score ) {
				$topscore = $search->score;
			}
		}
		$numrows = count( $searches );
	} else {
		$numrows = 1;
	}

	$match_range = get_bsearch_range( $numrows, $limit );
	$searches = array_slice( $searches, $match_range[0], $match_range[1] - $match_range[0] + 1 );	// Extract the elements for the page from the complete results array

	$output = '';

	// Lets start printing the results
	if ( '' != $search_query ) {
		if ( $searches ) {
			$output .= get_bsearch_header( $search_query, $numrows, $limit );

			$search_query = preg_quote( $search_query, '/' );
			$keys = explode( " ", $search_query );

			foreach ( $searches as $search ) {
				$score = $search->score;
				$search = get_post( $search->ID );
				$post_title = get_the_title( $search->ID );

				/* Highlight the search terms in the title */
				if ( $bsearch_settings['highlight'] ) {
					$post_title  = preg_replace( '/(' . implode( '|', $keys ) . ')/iu', '<span class="bsearch_highlight">$1</span>', $post_title );
				}

				$output .= '<h2><a href="' . get_permalink( $search->ID ).'" rel="bookmark">' . $post_title . '</a></h2>';

				$output .= '<p>';
				$output .= '<span class="bsearch_score">' . get_bsearch_score( $search, $score, $topscore ) . '</span>';

				$before = __( 'Posted on: ', BSEARCH_LOCAL_NAME );

				$output .= '<span class="bsearch_date">' . get_bsearch_date( $search, __( 'Posted on: ', BSEARCH_LOCAL_NAME ) ) . '</span>';
				$output .= '</p>';

				$output .= '<p>';
				if ( $bsearch_settings['include_thumb']) {
					$output .= '<p class="bsearch_thumb">' . get_the_post_thumbnail( $search->ID, 'thumbnail' ) . '</p>';
				}

				$excerpt = get_bsearch_excerpt( $search->ID, $bsearch_settings['excerpt_length'] );

				/* Highlight the search terms in the excerpt */
				if ( $bsearch_settings['highlight'] ) {
					$excerpt = preg_replace( '/(' . implode( '|', $keys ) . ')/iu', '<span class="bsearch_highlight">$1</span>', $excerpt );
				}

				$output .= '<span class="bsearch_excerpt">' . $excerpt . '</span>';

				$output .= '</p>';
			} //end of foreach loop

			$output .= get_bsearch_footer( $search_query, $numrows, $limit );

		} else {
			$output .= '<p>';
			$output .= __( 'No results.', BSEARCH_LOCAL_NAME );
			$output .= '</p>';
		}
	} else {
		$output .= '<p>';
		$output .= __( 'Please type in your search terms. Use descriptive words since this search is intelligent.', BSEARCH_LOCAL_NAME );
		$output .= '</p>';
	}

	if ( $bsearch_settings['show_credit'] ) {
		$output .= '<hr /><p style="text-align:center">';
		$output .= __( 'Powered by ', BSEARCH_LOCAL_NAME );
		$output .= '<a href="http://ajaydsouza.com/wordpress/plugins/better-search/">Better Search plugin</a></p>';
	}

	/**
	 * Filter formatted string with search results
	 *
	 * @since	1.2
	 *
	 * @param	string	$output			Formatted results
	 * @param	string	$search_query	Search query
	 * @param	int		$limit			Number of results per page
	 */
	return apply_filters( 'get_bsearch_results', $output, $search_query, $limit );
}


/**
 * Fetch the search query for Better Search.
 *
 * @since	2.0.0
 *
 * @return	string	Better Search query
 */
function get_bsearch_query() {

	$search_query = trim( bsearch_clean_terms(
		apply_filters( 'the_search_query', get_search_query() )
	) );

	/**
	 * Filter search terms string
	 *
	 * @since	2.0.0
	 *
	 * @param	string	$search_query	Search query
	 */
	return apply_filters( 'get_bsearch_query', $search_query );

}


/**
 * returns an array with the cleaned-up search string at the zero index and possibly a list of terms in the second.
 *
 * @since	1.2
 *
 * @param	mixed	$search_query	The search term
 * @return	array	Cleaned up search string
 */
function get_bsearch_terms( $search_query = '' ) {
	global $bsearch_settings;

	if ( ( '' == $search_query ) || empty( $search_query ) ) {
		$search_query = get_bsearch_query();
	}
	$s_array[0] = $search_query;

	$use_fulltext = $bsearch_settings['use_fulltext'];

	// if use_fulltext is false OR if all the words are shorter than four chars, add the array of search terms.
	// Currently this will disable match ranking and won't be quote-savvy.

	// if we are using fulltext, turn it off unless there's a search word longer than three chars
	// ideally we'd also check against stopwords here
	$search_words = explode( ' ', $search_query );

	if ( $use_fulltext ) {
		$use_fulltext_proxy = false;
		foreach( $search_words as $search_word ) {
			if ( strlen( $search_word ) > 3 ) {
				$use_fulltext_proxy = true;
			}
		}
		$use_fulltext = $use_fulltext_proxy;
	}

	if ( ! $use_fulltext ) {
		// strip out all the fancy characters that fulltext would use
		$search_query = addslashes_gpc( $search_query );
		$search_query = preg_replace( '/, +/', ' ', $search_query );
		$search_query = str_replace( ',', ' ', $search_query );
		$search_query = str_replace( '"', ' ', $search_query );
		$search_query = trim( $search_query );
		$search_words = explode( ' ', $search_query );

		$s_array[0] = $search_query;	// Save original query at [0]
		$s_array[1] = $search_words;	// Save array of terms at [1]
	}

	/**
	 * Filter array holding the search query and terms
	 *
	 * @since	1.2
	 *
	 * @param	array	$s_array	Original query is at [0] and array of terms at [1]
	 */
	return apply_filters( 'get_bsearch_terms', $s_array );
}


/**
 * Get the matches for the search term.
 *
 * @since	1.2
 *
 * @param	string	$search_info	Search terms array
 * @param	bool	$bydate			Sort by date?
 * @return	array	Search results
 */
function get_bsearch_matches( $search_query, $bydate ) {
	global $wpdb, $bsearch_settings;

	// if there are two items in $search_info, the string has been broken into separate terms that
	// are listed at $search_info[1]. The cleaned-up version of $search_query is still at the zero index.
	// This is when fulltext is disabled, and we search using LIKE
	$search_info = get_bsearch_terms( $search_query );

	// Get search transient
	$search_query_transient = substr( 'bs_' . preg_replace( '/[^A-Za-z0-9\-]/', '', str_replace( " ", "", $search_query ) ), 0, 40 );	// Name of the transient limited to 40 chars

	$matches = get_transient( $search_query_transient );

	if ( $matches ) {

		if ( isset( $matches['search_query'] ) ) {

			if ( $matches['search_query'] == $search_query ) {
				$results = $matches[0];

				/**
				 * Filter array holding the search results
				 *
				 * @since	1.2
				 *
				 * @param	object	$matches	Search results object
				 * @param	array	$search_info	Search query
				 */
				return apply_filters( 'get_bsearch_matches', $matches, $search_info );

			}

		}
	}

	// If no transient is set
	if ( ! isset( $results ) ) {
		$sql = bsearch_sql_prepare( $search_info, $bsearch_settings['boolean_mode'], $bydate );

		$results = $wpdb->get_results( $sql );
	}

	// If no results are found then force BOOLEAN mode
	if ( ! $results ) {
		$sql = bsearch_sql_prepare( $search_info, 1, $bydate );

		$results = $wpdb->get_results( $sql );
	}

	// If no results are found then force LIKE mode
	if ( ! $results ) {
		// strip out all the fancy characters that fulltext would use
		$search_query = addslashes_gpc( $search_query );
		$search_query = preg_replace( '/, +/', ' ', $search_query );
		$search_query = str_replace( ',', ' ', $search_query );
		$search_query = str_replace( '"', ' ', $search_query );
		$search_query = trim( $search_query );
		$search_words = explode( ' ', $search_query );

		$s_array[0] = $search_query;	// Save original query at [0]
		$s_array[1] = $search_words;	// Save array of terms at [1]

		$search_info = $s_array;

		$sql = bsearch_sql_prepare( $search_info, 0, $bydate );

		$results = $wpdb->get_results( $sql );
	}

	$matches[0] = $results;
	$matches['search_query'] = $search_query;

	// Set search transient
	set_transient( $search_query_transient, $matches, 7200 );

	/**
	 * Described in better-search.php
	 */
	return apply_filters( 'get_bsearch_matches', $matches, $search_info );
}


/**
 * returns an array with the first and last indices to be displayed on the page.
 *
 * @since	2.0.0
 *
 * @param	array	$search_info	Search query
 * @param 	bool	$boolean_mode	Set BOOLEAN mode for FULLTEXT searching
 * @param	bool	$bydate			Sort by date?
 * @return	array	First and last indices to be displayed on the page
 */
function bsearch_sql_prepare( $search_info, $boolean_mode, $bydate ) {
	global $wpdb, $bsearch_settings;

	// Initialise some variables
	$fields = '';
	$where = '';
	$join = '';
	$groupby = '';
	$orderby = '';
	$limits = '';
	$match_fields = '';

	parse_str( $bsearch_settings['post_types'], $post_types );	// Save post types in $post_types variable

	$n = '%';

	if ( count( $search_info ) > 1 ) {

		$search_terms = $search_info[1];

		// Fields to return
		$fields = " ID, 0 AS score ";

		// Create the WHERE Clause
		$where = " AND ( ";
		$where .= $wpdb->prepare(
			" ((post_title LIKE '%s') OR (post_content LIKE '%s')) ",
			$n . $search_terms[0] . $n,
			$n . $search_terms[0] . $n
		);

		for ( $i = 1; $i < count( $search_terms ); $i = $i + 1) {
			$where .= $wpdb->prepare(
				" AND ((post_title LIKE '%s') OR (post_content LIKE '%s')) ",
				$n . $search_terms[ $i ] . $n,
				$n . $search_terms[ $i ] . $n
			);
		}

		$where .= $wpdb->prepare(
			" OR (post_title LIKE '%s') OR (post_content LIKE '%s') ",
			$n . $search_terms[0] . $n,
			$n . $search_terms[0] . $n
		);

		$where .= " ) ";

		$where .= " AND post_status = 'publish' ";

		// Array of post types
		$where .= " AND $wpdb->posts.post_type IN ('" . join( "', '", $post_types ) . "') ";


		// Create the ORDERBY Clause
		$orderby = " post_date DESC ";

	} else {
		// Set BOOLEAN Mode
		$boolean_mode = ( $boolean_mode ) ? ' IN BOOLEAN MODE' : '';

		$field_args = array(
			$search_info[0],
			$bsearch_settings['weight_title'],
			$search_info[0],
			$bsearch_settings['weight_content'],
		);

		$fields = " ID";

		// Create the base MATCH part of the FIELDS clause
		$field_score = ", (MATCH(post_title) AGAINST ('%s' {$boolean_mode} ) * %d ) + ";
		$field_score .= "(MATCH(post_content) AGAINST ('%s' {$boolean_mode} ) * %d ) ";
		$field_score .= "AS score ";

		$field_score = $wpdb->prepare( $field_score, $field_args );

		/**
		 * Filter the MATCH part of the FIELDS clause of the query.
		 *
		 * @since	2.0.0
		 *
		 * @param string   $field_score  	The MATCH section of the FIELDS clause of the query, i.e. score
		 * @param string   $search_info[0]	Search query
		 * @param int	   $bsearch_settings['weight_title']	Weight of title
		 * @param int	   $bsearch_settings['weight_content']	Weight of content
		 */
		$field_score = apply_filters( 'bsearch_posts_match_field', $field_score, $search_info[0], $bsearch_settings['weight_title'], $bsearch_settings['weight_content'] );

		$fields .= $field_score;

		/**
		 * Filter the SELECT clause of the query.
		 *
		 * @since	2.0.0
		 *
		 * @param string   $fields  		The SELECT clause of the query.
		 * @param string   $search_info[0]	Search query
		 */
		$fields = apply_filters( 'bsearch_posts_fields', $fields, $search_info[0] );


		// Construct the MATCH part of the WHERE clause
		$match = " AND MATCH (post_title,post_content) AGAINST ('%s' {$boolean_mode} ) ";

		$match = $wpdb->prepare( $match, $search_info[0] );

		/**
		 * Filter the MATCH clause of the query.
		 *
		 * @since	2.0.0
		 *
		 * @param string   $match  		The MATCH section of the WHERE clause of the query
		 * @param string   $search_info[0]	Search query
		 */
		$match = apply_filters( 'bsearch_posts_match', $match, $search_info[0] );


		// Construct the WHERE clause
		$where = $match;

		$where .= " AND post_status = 'publish' ";

		// Array of post types
		if ( $post_types ) {
			$where .= " AND $wpdb->posts.post_type IN ('" . join( "', '", $post_types ) . "') ";
		}

		/**
		 * Filter the WHERE clause of the query.
		 *
		 * @since	2.0.0
		 *
		 * @param string   $where  		The WHERE clause of the query
		 * @param string   $search_info[0]	Search query
		 */
		$where = apply_filters( 'bsearch_posts_where', $where, $search_info[0] );


		// ORDER BY clause
		if ( $bydate ) {
			$orderby = " post_date DESC ";
		} else {
			$orderby = " score DESC ";
		}

		/**
		 * Filter the ORDER BY clause of the query.
		 *
		 * @since	2.0.0
		 *
		 * @param string   $orderby  		The ORDER BY clause of the query
		 * @param string   $search_info[0]	Search query
		 */
		$orderby = apply_filters( 'bsearch_posts_orderby', $orderby, $search_info[0] );

		/**
		 * Filter the GROUP BY clause of the query.
		 *
		 * @since	2.0.0
		 *
		 * @param string   $groupby  		The GROUP BY clause of the query
		 * @param string   $search_info[0]	Search query
		 */
		$groupby = apply_filters( 'bsearch_posts_groupby', $groupby, $search_info[0] );

		/**
		 * Filter the JOIN clause of the query.
		 *
		 * @since	2.0.0
		 *
		 * @param string   $join  		The JOIN clause of the query
		 * @param string   $search_info[0]	Search query
		 */
		$join = apply_filters( 'bsearch_posts_join', $join, $search_info[0] );

		/**
		 * Filter the JOIN clause of the query.
		 *
		 * @since	2.0.0
		 *
		 * @param string   $limits  		The JOIN clause of the query
		 * @param string   $search_info[0]	Search query
		 */
		$limits = apply_filters( 'bsearch_posts_limits', $limits, $search_info[0] );

	}

	if ( ! empty( $groupby ) ) {
		$groupby = 'GROUP BY ' . $groupby;
	}
	if ( ! empty( $orderby ) ) {
		$orderby = 'ORDER BY ' . $orderby;
	}

	$sql = "SELECT DISTINCT $fields FROM $wpdb->posts $join WHERE 1=1 $where $groupby $orderby $limits";


	/**
	 * Filter MySQL string used to fetch results.
	 *
	 * @since	1.3
	 *
	 * @param	string	$sql			MySQL string
	 * @param	array	$search_info	Search query
	 * @param 	bool	$boolean_mode	Set BOOLEAN mode for FULLTEXT searching
	 * @param	bool	$bydate			Sort by date?
	 */
	return apply_filters( 'bsearch_sql_prepare', $sql, $search_info, $boolean_mode, $bydate );
}


/**
 * returns an array with the first and last indices to be displayed on the page.
 *
 * @since	1.2
 *
 * @param	int		$numrows	Total results
 * @param 	int		$limit		Results per page
 * @return	array	First and last indices to be displayed on the page
 */
function get_bsearch_range( $numrows, $limit ) {
	global $bsearch_settings;

	if ( ! ( $limit ) ) {
		$limit = isset( $_GET['limit'] ) ? intval( $_GET['limit'] ) : $bsearch_settings['limit']; // Read from GET variable
	}
	$page = isset( $_GET['bpaged'] ) ? intval( bsearch_clean_terms( $_GET['bpaged'] ) ) : 0; // Read from GET variable

	$last = min( $page + $limit - 1, $numrows - 1 );

	$match_range = array( $page, $last );

	/**
	 * Filter array with the first and last indices to be displayed on the page.
	 *
	 * @since	1.3
	 *
	 * @param	array	$match_range	First and last indices to be displayed on the page
	 * @param	int		$numrows		Total results
	 * @param	int		$limit			Results per page
	 */
	return apply_filters( 'get_bsearch_range', $match_range, $numrows, $limit );
}


/**
 * Function to return the header links of the results page.
 *
 * @since	1.2
 *
 * @param	string	$search_query	Search string
 * @param	int		$numrows 		Total number of results
 * @param	int 	$limit 			Results per page
 * @return	string	Formatted header table of search results pages
 */
function get_bsearch_header( $search_query, $numrows, $limit ) {

	$output = '';
	$match_range = get_bsearch_range( $numrows, $limit );

	$pages = intval( $numrows / $limit ); // Number of results pages.

	if ( $numrows % $limit ) {
		$pages++;	// If remainder so add one page
	}

	if ( ( $pages < 1 ) || ( $pages == 0 ) ) {
		$total = 1;	// If $pages is less than one or equal to 0, total pages is 1.
	} else {
		$total = $pages;	// Else total pages is $pages value.
	}

	$first = $match_range[0] + 1;	// the first result on the page (Starts with 0)
	$last = $match_range[1] + 1;	// the last result on the page (Starts with 0)
	$current = ( $match_range[0] / $limit ) + 1; // Current page number.

	$output .= '<table width="100%" border="0" class="bsearch_nav">
	 <tr class="bsearch_nav_row1">
	  <td width="50%" style="text-align:left">';
	$output .= sprintf( __( 'Results <strong>%1$s</strong> - <strong>%2$s</strong> of <strong>%3$s</strong>', BSEARCH_LOCAL_NAME ), $first, $last, $numrows );

	$output .= '
	  </td>
	  <td width="50%" style="text-align:right">';
	$output .= sprintf( __( 'Page <strong>%1$s</strong> of <strong>%2$s</strong>', BSEARCH_LOCAL_NAME ), $current, $total );

	$sencoded = urlencode( $search_query );

	$output .= '
	  </td>
	 </tr>
	 <tr class="bsearch_nav_row2">
	  <td style="text-align:left"></td>';
	$output .= '<td style="text-align:right">';
	$output .= __( 'Results per-page', BSEARCH_LOCAL_NAME );
	$output .= ': <a href="' . home_url() . '/?s=' . $sencoded . '&limit=10">10</a> | <a href="' . home_url() . '/?s=' . $sencoded . '&limit=20">20</a> | <a href="' . home_url() . '/?s=' . $sencoded . '&limit=50">50</a> | <a href="' . home_url() . '/?s=' . $sencoded . '&limit=100">100</a>
	  </td>
	 </tr>
	</table>';

	/**
	 * Filter formatted string with header of page
	 *
	 * @since	1.2
	 *
	 * @param	string	$output			HTML of header table
	 * @param	string	$search_query	Search string
	 * @param	int		$numrows 		Total number of results
	 * @param	int 	$limit 			Results per page
	 */
	return apply_filters( 'get_bsearch_header', $output, $search_query, $numrows, $limit );
}


/**
 * Function to return the footer links of the results page.
 *
 * @since	1.2
 *
 * @param	string	$search_query	Search string
 * @param	int 	$numrows		Total results
 * @param	int 	$limit			Results per page
 * @return	string	Formatted footer of search results pages
 */
function get_bsearch_footer( $search_query, $numrows, $limit ) {

	$match_range = get_bsearch_range( $numrows, $limit );
	$page = $match_range[0];
	$pages = intval( $numrows / $limit ); // Number of results pages.
	if ( $numrows % $limit ) {
		$pages++;	// If remainder so add one page
	}

	$search_query = urlencode( $search_query );

	$output =   '<p class="bsearch_footer">';
	if ( 0 != $page ) { // Don't show back link if current page is first page.
		$back_page = $page - $limit;
		$output .=  "<a href=\"" . home_url() . "/?s=$search_query&limit=$limit&bpaged=$back_page\">&laquo; ";
		$output .=  __( 'Previous', BSEARCH_LOCAL_NAME );
		$output .=  "</a>    \n";
	}

	$pagination_range = 4;			// Number of pagination elements

	for ( $i=1; $i <= $pages; $i++ ) { // loop through each page and give link to it.
		$current = ( $match_range[0] / $limit ) + 1; // Current page number.
		if ( $i >= $current + $pagination_range && $i < $pages ) {
			if ( $i == $current + $pagination_range ) {
				$output .= '&hellip;&nbsp;';
			}
			continue;
		}
		if ( $i < $current - $pagination_range + 1 && $i < $pages ) {
			continue;
		}
		$ppage = $limit * ( $i - 1 );
		if ( $ppage == $page ) {
			$output .=  "<b>$i</b>\n";	// If current page don't give link, just text.
		} else {
			$output .=  "<a href=\"" . home_url() . "/?s=$search_query&limit=$limit&bpaged=$ppage\">$i</a> \n";
		}
	}

	if ( ! ( ( ( $page + $limit ) / $limit ) >= $pages ) && $pages != 1 ) { // If last page don't give next link.
		$next_page = $page + $limit;
		$output .=  "    <a href=\"" . home_url() . "/?s=$search_query&limit=$limit&bpaged=$next_page\">";
		$output .=  __( 'Next', BSEARCH_LOCAL_NAME );
		$output .=  " &raquo;</a>";
	}
	$output .=   '</p>';

	/**
	 * Filter formatted string with footer of page
	 *
	 * @since	1.2
	 *
	 * @param	string	$output			HTML of footer
	 * @param	string	$search_query	Search string
	 * @param	int 	$numrows		Total results
	 * @param	int 	$limit			Results per page
	 */
	return apply_filters( 'get_bsearch_footer', $output, $search_query, $numrows, $limit );
}


/**
 * Function to convert the mySQL score to percentage.
 *
 * @since	1.2
 *
 * @param	object	$search		Search result object
 * @param	int 	$score 		Score for the search result
 * @param	int 	$topscore 	Score for the most relevant search result
 * @return	int 	Score converted to percentage
 */
function get_bsearch_score( $search, $score, $topscore ) {

	$output = '';

	if ( $score > 0 ) {
		$score = $score * 100 / $topscore;
		$output = __( 'Relevance: ', BSEARCH_LOCAL_NAME );
		$output .= number_format_i18n( $score, 0 ) . '% &nbsp;&nbsp;&nbsp;&nbsp; ';
	}

	/**
	 * Filter search result score
	 *
	 * @since	1.2
	 *
	 * @param	string	$output		HTML of footer
	 * @param	string	$search		Search result object
	 * @param	int 	$score		Score for the search result
	 * @param	int 	$topscore	Score for the most relevant result
	 */
	return apply_filters( 'get_bsearch_score', $output, $search, $score, $topscore );
}


/**
 * Function to get post date.
 *
 * @since	1.2
 *
 * @param 	object 	$search 	Search result object
 * @param 	string 	$before 	Added before the date
 * @param 	string 	$after 		Added after the date
 * @param 	string 	$format 	Date format
 * @return 	string 	Formatted date string
 */
function get_bsearch_date( $search, $before = '', $after = '', $format = '' ) {
	if ( ! $format ) {
		$format = get_option('date_format');
	}

	$output = $before . date_i18n( $format, strtotime( $search->post_date ) ) . $after;

	/**
	 * Filter formatted string with search result date
	 *
	 * @since	1.2
	 *
	 * @param 	string	$output		Formatted date string
	 * @param 	object 	$search 	Search result object
	 * @param 	string 	$before 	Added before the date
	 * @param 	string 	$after 		Added after the date
	 * @param 	string 	$format 	Date format
	 */
	return apply_filters( 'get_bsearch_date', $output, $search, $before, $after, $format );
}


/**
 * Function to create an excerpt for the post.
 *
 * @since	1.2
 *
 * @param	int 		$id				Post ID
 * @param	int|string	$excerpt_length	Length of the excerpt in words
 * @param	bool		$use_excerpt	Use post excerpt or content?
 * @return	string 		Excerpt
 */
function get_bsearch_excerpt( $id, $excerpt_length = 0, $use_excerpt = true ) {
	$content = $excerpt = '';
	if ( $use_excerpt ) {
		$content = get_post( $id )->post_excerpt;
	}
	if ( '' == $content ) {
		$content = get_post( $id )->post_content;
	}

	$output = strip_tags( strip_shortcodes( $content ) );

	if ( $excerpt_length > 0 ) {
		$output = wp_trim_words( $output, $excerpt_length );
	}

	/**
	 * Filter formatted string with search result exeerpt
	 *
	 * @since	1.2
	 *
	 * @param	string		$output			Formatted excerpt
	 * @param	int 		$id				Post ID
	 * @param	int|string	$excerpt_length	Length of the excerpt in words
	 * @param	bool		$use_excerpt	Use post excerpt or content?
	 */
	return apply_filters( 'get_bsearch_excerpt', $output, $id, $excerpt_length, $use_excerpt );
}


/**
 * Get the Search Heatmap.
 *
 * @since	1.2
 *
 * @param	array|string	$args	Heatmap Parameters
 * @return	string	Search heatmap
 */
function get_bsearch_heatmap( $args = array() ) {
	global $wpdb, $bsearch_url, $bsearch_settings;

	$defaults = array(
		'daily' => FALSE,
		'smallest' => intval( $bsearch_settings['heatmap_smallest'] ),
		'largest' => intval( $bsearch_settings['heatmap_largest'] ),
		'unit' => $bsearch_settings['heatmap_unit'],
		'cold' => $bsearch_settings['heatmap_cold'],
		'hot' => $bsearch_settings['heatmap_hot'],
		'before' => $bsearch_settings['heatmap_before'],
		'after' => $bsearch_settings['heatmap_after'],
		'heatmap_limit' => intval( $bsearch_settings['heatmap_limit'] ),
		'daily_range' => intval( $bsearch_settings['daily_range'] ),
	);

	// Parse incomming $args into an array and merge it with $defaults
	$args = wp_parse_args( $args, $defaults );

	// OPTIONAL: Declare each item in $args as its own variable i.e. $type, $before.
	extract( $args, EXTR_SKIP );

	$table_name = $wpdb->prefix . "bsearch";

	if ( $daily ) {
		$table_name .= "_daily";	// If we're viewing daily posts, set this to true
	}
	$output = '';

	if ( ! $daily ) {
		$args = array(
			$heatmap_limit,
		);

		$sql = "SELECT searchvar, cntaccess FROM {$table_name} WHERE accessedid IN (SELECT accessedid FROM {$table_name} WHERE searchvar <> '' ORDER BY cntaccess DESC, searchvar ASC) ORDER by accessedid LIMIT %d";
	} else {
		$current_time = current_time( 'timestamp', 0 );
		$current_time = $current_time - ( $daily_range - 1 ) * 3600 * 24;
		$current_date = date_i18n( 'Y-m-j', $current_time );

		$args = array(
			$current_date,
			$heatmap_limit,
		);

		$sql = "
			SELECT DISTINCT wp1.searchvar, wp2.sumCount
			FROM {$table_name} wp1,
					(SELECT searchvar, SUM(cntaccess) as sumCount
					FROM {$table_name}
					WHERE dp_date >= '%s'
					GROUP BY searchvar
					ORDER BY sumCount DESC LIMIT %d) wp2
					WHERE wp1.searchvar = wp2.searchvar
			ORDER by wp1.searchvar ASC
		";
	}

	$results = $wpdb->get_results( $wpdb->prepare( $sql, $args ) );

	if ( $results ) {
		foreach ( $results as $result ) {
			if ( ! $daily ) {
				$cntaccesss[] = $result->cntaccess;
			} else {
				$cntaccesss[] = $result->sumCount;
			}
		}
		$min = min( $cntaccesss );
		$max = max( $cntaccesss );
		$spread = $max - $min;

		// Calculate various font sizes
		if ( $largest != $smallest ) {
			$fontspread = $largest - $smallest;
			if ( 0 != $spread ) {
				$fontstep = $fontspread / $spread;
			} else {
				$fontstep = 0;
			}
		}

		// Calculate colors
		if ( $hot != $cold ) {
			$hotdec = bsearch_html2rgb( $hot );
			$colddec = bsearch_html2rgb( $cold );
			for ( $i = 0; $i < 3; $i++ ) {
				$coldval[] = $colddec[ $i ];
				$hotval[] = $hotdec[ $i ];
				$colorspread[] = $hotdec[ $i ] - $colddec[ $i ];
				if ( 0 != $spread ) {
					$colorstep[] = ( $hotdec[ $i ] - $colddec[ $i ] ) / $spread;
				} else {
					$colorstep[] = 0;
				}
			}
		}

		foreach ( $results as $result ) {
			if ( ! $daily ) {
				$cntaccess = $result->cntaccess;
			} else {
				$cntaccess = $result->sumCount;
			}

			$textsearchvar = esc_attr( $result->searchvar );
			$url  = home_url() . '/?s=' . $textsearchvar;
			$fraction = $cntaccess - $min;
			$fontsize = $smallest + $fontstep * $fraction;

			$color = "";

			for ( $i = 0; $i < 3; $i++ ) {
				$color .= dechex( $coldval[ $i ] + ( $colorstep[ $i ] * $fraction ) );
			}
			$style = 'style="';
			if ( $largest != $smallest ) {
				$style .= "font-size:" . round( $fontsize ) . $unit . ";";
			}
			if ( $hot != $cold ) {
				$style .= "color:#" . $color . ";";
			}
			$style .= '"';

			$output .= $before . '<a href="' . $url . '" title="';
			$output .= sprintf( _n( 'Search for %1$s (%2$s search)', 'Search for %1$s (%2$s searches)', $cntaccess, BSEARCH_LOCAL_NAME ), $textsearchvar, $cntaccess );
			$output .= '" '.$style;
			if ( $bsearch_settings['link_nofollow'] ) {
				$output .= ' rel="nofollow" ';
			}
			if ( $bsearch_settings['link_new_window'] ) {
				$output .= ' target="_blank" ';
			}
			$output .= '>' . $textsearchvar . '</a>' . $after . ' ';
		}
	} else {
		$output = __( 'No searches made yet', BSEARCH_LOCAL_NAME );
	}

	/**
	 * Filter formatted string with the search heatmap
	 *
	 * @since	1.2
	 *
	 * @param	string			$output		Formatted excerpt
	 * @param	string|array 	$args		Arguments
	 */
	return apply_filters( 'get_bsearch_heatmap', $output, $args );
}


/**
 * Function to update search count.
 *
 * @since	1.0
 *
 * @param	string	$search_query	Search query
 * @return	string	Search tracker code
 */
function bsearch_increment_counter( $search_query ) {
	global $bsearch_url, $bsearch_settings;

	$output = '';

	$current_user = wp_get_current_user();
	$current_user_admin = ( current_user_can( 'manage_options' ) ) ? true : false;	// Is the current user an admin?
	$current_user_editor = ( ( current_user_can( 'edit_others_posts' ) ) && ( ! current_user_can( 'manage_options' ) ) ) ? true : false;	// Is the current user pure editor?

	$include_code = true;

	// If user is an admin
	if ( ( $current_user_admin ) && ( ! $bsearch_settings['track_admins'] ) ) {
		$include_code = false;
	}

	// If user is an editor
	if ( ( $current_user_editor ) && ( ! $bsearch_settings['track_editors'] ) ) {
		$include_code = false;
	}

	if ( $include_code ) {
		$output = '<script type="text/javascript" data-cfasync="false" src="' . $bsearch_url . '/includes/better-search-addcount.js.php?bsearch_id=' . $search_query . '"></script>';
	}

	/**
	 * Filter the search tracker code
	 *
	 * @since	2.0.0
	 *
	 * @param	string	$output			Formatted output string
	 * @param	string	$search_query	Search query
	 */
	return apply_filters( 'bsearch_increment_counter', $output, $search_query );
}


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
 * @param	string	$title
 * @return	string	Title of the page
 */
function bsearch_title( $title ) {

	if ( ! is_search() ) {
		return $title;
	}

	$search_query = get_bsearch_query();

	if ( isset( $search_query ) ) {

		$bsearch_title = sprintf( __( 'Search Results for "%s" | %s', BSEARCH_LOCAL_NAME ), $search_query, $title );

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


/**
 * Function to fetch search form.
 *
 * @since	1.1
 *
 * @param 	string 	$search_query	Search query
 * @return 	string	Search form
 */
function get_bsearch_form( $search_query ) {

	if ( $search_query == '' ) {
		$search_query = get_bsearch_query();
	}

	$form = '<div style="text-align:center"><form method="get" class="bsearchform" action="' . home_url() . '/" >
	<label class="hidden" for="s">' . __( 'Search for:', BSEARCH_LOCAL_NAME ) . '</label>
	<input type="text" value="' . $search_query . '" name="s" class="s" />
	<input type="submit" class="searchsubmit" value="' . __( 'Search Again', BSEARCH_LOCAL_NAME ) . '" />
	</form></div>';

	/**
	 * Filters the title of the page
	 *
	 * @since	1.2
	 *
	 * @param	string	$form	HTML to display the form
	 * @param	string	$search_query	Search query
	 */
	return apply_filters( 'get_bsearch_form', $form, $search_query );
}


/**
 * Function to retrieve Daily Popular Searches Title.
 *
 * @since	1.1
 *
 * @param	bool	$text_only	With or without tags?
 * @return	string	Title of Daily Popular searches
 */
function get_bsearch_title_daily( $text_only = true ) {

	global $bsearch_settings;
	$title = ( $text_only ) ? strip_tags( $bsearch_settings['title_daily'] ) : $bsearch_settings['title_daily'];

	/**
	 * Filters the title of the widget
	 *
	 * @since	1.2
	 *
	 * @param	string	$title	Title of the daily popular searches
	 */
	return apply_filters( 'get_bsearch_title_daily', $title );
}


/**
 * Function to retrieve Overall Popular Searches Title.
 *
 * @since	1.1
 *
 * @param	bool	$text_only	With or without tags?
 * @return	string	Title of Overall Popular searches
 */
function get_bsearch_title( $text_only = true ) {

	global $bsearch_settings;
	$title = ( $text_only ) ? strip_tags( $bsearch_settings['title'] ) : $bsearch_settings['title'];

	/**
	 * Filters the title of the widget
	 *
	 * @since	1.2
	 *
	 * @param	string	$title	Title of the daily popular searches
	 */
	return apply_filters( 'get_bsearch_title', $title );
}


/**
 * Manual Daily Better Search Heatmap.
 *
 * @since	1.0
 *
 * @return	string	Daily search heatmap
 */
function get_bsearch_pop_daily() {

	global $bsearch_settings, $bsearch_url;

	$output = '';

	$output .= '<div class="bsearch_heatmap">';
	$output .= $bsearch_settings['title_daily'];
	$output .= '<div text-align:center>';

	$output .= get_bsearch_heatmap( array(
		'daily' => 1,
	) );
	$output .= '</div>';

	if ( $bsearch_settings['show_credit'] ) {
		$output .= '<br /><small>Powered by <a href="http://ajaydsouza.com/wordpress/plugins/better-search/">Better Search plugin</a></small>';
	}

	$output .= '</div>';

	/**
	 * Filters the daily search heatmap HTML
	 *
	 * @since	1.2
	 *
	 * @param	string	$output	Daily search heatmap HTML
	 */
	return apply_filters( 'get_bsearch_pop_daily', $output );
}


/**
 * Echo daily popular searches.
 *
 * @since	1.0
 */
function the_pop_searches_daily() {
	echo get_bsearch_pop_daily();
}


/**
 * Manual Overall Better Search Heatmap.
 *
 * @since	1.0
 *
 * @return	$string	Popular searches heatmap
 */
function get_bsearch_pop() {

	global $bsearch_settings;

	$output = '';

	$output .= '<div class="bsearch_heatmap">';
	$output .= $bsearch_settings['title'];
	$output .= '<div text-align:center>';

	$output .= get_bsearch_heatmap( array(
		'daily' => 0,
	) );
	$output .= '</div>';

	if ( $bsearch_settings['show_credit'] ) {
		$output .= '<br /><small>Powered by <a href="http://ajaydsouza.com/wordpress/plugins/better-search/">Better Search plugin</a></small>';
	}

	$output .= '</div>';

	/**
	 * Filters the overall popular searches heatmap HTML
	 *
	 * @since	1.2
	 *
	 * @param	string	$output	Daily search heatmap HTML
	 */
	return apply_filters( 'get_bsearch_pop', $output );
}


/**
 * Echo popular searches list.
 *
 * @since	1.0
 *
 */
function the_pop_searches() {
	echo get_bsearch_pop();
}


/**
 * Modify search results page with results from Better Search. Filters posts_where.
 *
 * @since	1.3.3
 *
 * @param	string	$where	WHERE clause of main query
 * @param	object	$query	WordPress query
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
 * @param	string	$orderby	ORDERBY clause of main query
 * @param	object	$query		WordPress query
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
 * @param	string	$content	Post content
 * @return 	string	Post Content
 */
function bsearch_content( $content ) {
	global $bsearch_settings, $wp_query;

	if ( $wp_query->is_search() && $bsearch_settings['seamless'] && ! is_admin() && in_the_loop() && $bsearch_settings['highlight'] ) {
		$search_query = get_bsearch_query();

		$search_query = preg_quote( $search_query, '/' );
		$keys = explode( " ", $search_query );

		$regEx = "/(?!<[^>]*?>)(". implode( '|', $keys ) . ")(?![^<]*?>)/iu";
		$content  = preg_replace( $regEx, '<span class="bsearch_highlight">$1</span>', $content );

	}

	return apply_filters( 'bsearch_content', $content );
}
add_filter( 'the_content', 'bsearch_content' );
add_filter( 'get_the_excerpt', 'bsearch_content' );
add_filter( 'the_title', 'bsearch_content' );


/**
 * Default options.
 *
 * @since	1.0
 *
 * @return	array	Default options array
 */
function bsearch_default_options() {
	$title = __( '<h3>Popular Searches</h3>', BSEARCH_LOCAL_NAME );
	$title_daily = __( '<h3>Weekly Popular Searches</h3>', BSEARCH_LOCAL_NAME );

	// get relevant post types
	$args = array (
		'public' => true,
		'_builtin' => true
	);
	$post_types	= http_build_query( get_post_types( $args ), '', '&' );

	$custom_CSS = '
#bsearchform { margin: 20px; padding: 20px; }
#heatmap { margin: 20px; padding: 20px; border: 1px dashed #ccc }
.bsearch_results_page { max-width:90%; margin: 20px; padding: 20px; }
.bsearch_footer { text-align: center; }
.bsearch_highlight { background:#ffc; }
	';

	$badwords = array( 'anal', 'anus', 'bastard', 'beastiality', 'bestiality', 'bewb', 'bitch', 'blow', 'blumpkin', 'boob', 'cawk', 'cock', 'choad', 'cooter', 'cornhole', 'cum', 'cunt', 'dick', 'dildo', 'dong', 'dyke', 'douche', 'fag', 'faggot', 'fart', 'foreskin', 'fuck', 'fuk', 'gangbang', 'gook', 'handjob', 'homo', 'honkey', 'humping', 'jiz', 'jizz', 'kike', 'kunt', 'labia', 'muff', 'nigger', 'nutsack', 'pen1s', 'penis', 'piss', 'poon', 'poop', 'porn', 'punani', 'pussy', 'queef', 'queer', 'quim', 'rimjob', 'rape', 'rectal', 'rectum', 'semen', 'shit', 'slut', 'spick', 'spoo', 'spooge', 'taint', 'titty', 'titties', 'twat', 'vagina', 'vulva', 'wank', 'whore', );

	$bsearch_settings = array(

		/* General options */
		'seamless' => true,				// Seamless integration mode
		'track_popular' => true,		// Track the popular searches
		'track_admins' => true,			// Track Admin searches
		'track_editors' => true,		// Track Editor searches
		'meta_noindex' => true,			// Add noindex,follow meta tag to head
		'show_credit' => false,			// Add link to plugin page of my blog in top posts list

		/* Search options */
		'limit' => '10',				// Search results per page
		'post_types' => $post_types,	// WordPress custom post types

		'use_fulltext' => true,			// Full text searches
		'weight_content' => '10',		// Weightage for content
		'weight_title' => '1',			// Weightage for title
		'boolean_mode' => false,		// Turn BOOLEAN mode on if true

		'highlight' => false,			// Highlight search terms
		'excerpt_length' => '100',		// Length of excerpt in words
		'include_thumb' => false,		// Include thumbnail in search results
		'link_new_window' => false,		// Open link in new window - Includes target="_blank" to links
		'link_nofollow' => true,		// Includes rel="nofollow" to links in heatmap

		'badwords' => implode( ',', $badwords ),		// Bad words filter

		/* Heatmap options */
		'include_heatmap' => false,		// Include heatmap of searches in the search page
		'title' => $title,				// Title of Search Heatmap
		'title_daily' => $title_daily,	// Title of Daily Search Heatmap
		'daily_range' => '7',			// Daily Popular will contain posts of how many days?

		'heatmap_limit' => '30',		// Heatmap - Maximum number of searches to display in heatmap
		'heatmap_smallest' => '10',		// Heatmap - Smallest Font Size
		'heatmap_largest' => '20',		// Heatmap - Largest Font Size
		'heatmap_unit' => 'pt',			// Heatmap - We'll use pt for font size
		'heatmap_cold' => 'CCCCCC',		// Heatmap - cold searches
		'heatmap_hot' => '000000',		// Heatmap - hot searches
		'heatmap_before' => '',			// Heatmap - Display before each search term
		'heatmap_after' => '&nbsp;',	// Heatmap - Display after each search term


		/* Custom styles */
		'custom_CSS' => $custom_CSS,	// Custom CSS

	);

	/**
	 * Filters Default options for Better Search
	 *
	 * @since	2.0.0
	 *
	 * @param	array	$bsearch_settings	Default options
	 **/
	return apply_filters( 'bsearch_default_options', $bsearch_settings);
}


/**
 * Function to read options from the database.
 *
 * @since	1.0
 *
 * @return	array	Better Search options array
 */
function bsearch_read_options() {

	// Upgrade table code
	global $bsearch_db_version, $network_wide;

	$bsearch_settings_changed = false;

	$defaults = bsearch_default_options();

	$bsearch_settings = array_map( 'stripslashes', (array) get_option( 'ald_bsearch_settings' ) );
	unset( $bsearch_settings[0] ); // produced by the (array) casting when there's nothing in the DB

	foreach ( $defaults as $k => $v ) {
		if ( ! isset( $bsearch_settings[ $k ] ) ) {
			$bsearch_settings[ $k ] = $v;
			$bsearch_settings_changed = true;
		}
	}
	if ( $bsearch_settings_changed == true ) {
		update_option( 'ald_bsearch_settings', $bsearch_settings );
	}

	/**
	 * Filters options read from DB for Better Search
	 *
	 * @since	2.0.0
	 *
	 * @param	array	$bsearch_settings	Read options
	 **/
	return apply_filters( 'bsearch_read_options', $bsearch_settings);
}


/**
 * Fired for each blog when the plugin is activated.
 *
 * @since	1.0
 *
 * @param    boolean    $network_wide    True if WPMU superadmin uses
 *                                       "Network Activate" action, false if
 *                                       WPMU is disabled or plugin is
 *                                       activated on an individual blog.
 */
function bsearch_install( $network_wide ) {
    global $wpdb;

    if ( is_multisite() && $network_wide ) {

        // Get all blogs in the network and activate plugin on each one
        $blog_ids = $wpdb->get_col( "
        	SELECT blog_id FROM $wpdb->blogs
			WHERE archived = '0' AND spam = '0' AND deleted = '0'
		" );
        foreach ( $blog_ids as $blog_id ) {
        	switch_to_blog( $blog_id );
			bsearch_single_activate();
        }

        // Switch back to the current blog
        restore_current_blog();

    } else {
        bsearch_single_activate();
    }
}
register_activation_hook( __FILE__, 'bsearch_install' );


/**
 * Create tables to store pageviews.
 *
 * @since	2.0.0
 */
function bsearch_single_activate() {
	global $wpdb, $bsearch_db_version;

	$bsearch_settings = bsearch_read_options();

	// Create full text index
	$wpdb->hide_errors();
    $wpdb->query( 'ALTER TABLE ' . $wpdb->posts . ' ENGINE = MYISAM;' );
    $wpdb->query( 'ALTER TABLE ' . $wpdb->posts . ' ADD FULLTEXT bsearch (post_title, post_content);' );
    $wpdb->query( 'ALTER TABLE ' . $wpdb->posts . ' ADD FULLTEXT bsearch_title (post_title);' );
    $wpdb->query( 'ALTER TABLE ' . $wpdb->posts . ' ADD FULLTEXT bsearch_content (post_content);' );
    $wpdb->show_errors();

	// Create the tables
	$table_name = $wpdb->prefix . "bsearch";
	$table_name_daily = $wpdb->prefix . "bsearch_daily";

	if ( $wpdb->get_var( "show tables like '$table_name'" ) != $table_name ) {

		$sql = "CREATE TABLE " . $table_name . " (
			accessedid int NOT NULL AUTO_INCREMENT,
			searchvar VARCHAR(100) NOT NULL,
			cntaccess int NOT NULL,
			PRIMARY KEY  (accessedid)
		);";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );

		$wpdb->hide_errors();
		$wpdb->query( 'CREATE INDEX IDX_searhvar ON ' . $table_name . ' (searchvar)' );
		$wpdb->show_errors();

		add_option( "bsearch_db_version", $bsearch_db_version );
	}

	if ( $wpdb->get_var( "show tables like '$table_name_daily'") != $table_name_daily ) {

		$sql = "CREATE TABLE " . $table_name_daily . " (
			accessedid int NOT NULL AUTO_INCREMENT,
			searchvar VARCHAR(100) NOT NULL,
			cntaccess int NOT NULL,
			dp_date date NOT NULL,
			PRIMARY KEY  (accessedid)
		);";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );

		$wpdb->hide_errors();
		$wpdb->query( 'CREATE INDEX IDX_searhvar ON ' . $table_name_daily . ' (searchvar)' );
		$wpdb->show_errors();

		add_option( "bsearch_db_version", $bsearch_db_version );
	}

	// Upgrade table code
	$installed_ver = get_option( "bsearch_db_version" );

	if ( $installed_ver != $bsearch_db_version ) {

		$sql = "CREATE TABLE " . $table_name . " (
			accessedid int NOT NULL AUTO_INCREMENT,
			searchvar VARCHAR(100) NOT NULL,
			cntaccess int NOT NULL,
			PRIMARY KEY  (accessedid)
		);";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );

		$wpdb->hide_errors();
		$wpdb->query( 'ALTER '.$table_name.' DROP INDEX IDX_searhvar ' );
		$wpdb->query( 'CREATE INDEX IDX_searhvar ON '.$table_name.' (searchvar)' );
		$wpdb->show_errors();

		$sql = "DROP TABLE $table_name_daily";
		$wpdb->query( $sql );

		$sql = "CREATE TABLE " . $table_name_daily . " (
			accessedid int NOT NULL AUTO_INCREMENT,
			searchvar VARCHAR(100) NOT NULL,
			cntaccess int NOT NULL,
			dp_date date NOT NULL,
			PRIMARY KEY  (accessedid)
		);";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );

		$wpdb->hide_errors();
		$wpdb->query( 'ALTER ' . $table_name_daily . ' DROP INDEX IDX_searhvar ' );
		$wpdb->query( 'CREATE INDEX IDX_searhvar ON ' . $table_name_daily . ' (searchvar)' );
		$wpdb->show_errors();

		update_option( "bsearch_db_version", $bsearch_db_version );
	}

}


/**
 * Fired when a new site is activated with a WPMU environment.
 *
 * @since	2.0.0
 *
 * @param    int    $blog_id    ID of the new blog.
 */
function bsearch_activate_new_site( $blog_id ) {

	if ( 1 !== did_action( 'wpmu_new_blog' ) ) {
		return;
	}

	switch_to_blog( $blog_id );
	bsearch_single_activate();
	restore_current_blog();

}
add_action( 'wpmu_new_blog', 'bsearch_activate_new_site' );


/**
 * Fired when a site is deleted in a WPMU environment.
 *
 * @since	2.0.0
 *
 * @param    array    $tables    Tables in the blog.
 */
function bsearch_on_delete_blog( $tables ) {
    global $wpdb;

	$tables[] = $wpdb->prefix . "bsearch";
	$tables[] = $wpdb->prefix . "bsearch_daily";

    return $tables;
}
add_filter( 'wpmu_drop_tables', 'bsearch_on_delete_blog' );


/*----------------------------------------------------------------------------*
 * Dashboard and Administrative Functionality
 *----------------------------------------------------------------------------*/

// This function adds an Options page in WP Admin
if ( is_admin() || strstr( $_SERVER['PHP_SELF'], 'wp-admin/' ) ) {

	/**
	 *  Load the admin pages if we're in the Admin.
	 *
	 */
	require_once( plugin_dir_path( __FILE__ ) . '/admin/admin.php' );

} // End admin.inc

/*----------------------------------------------------------------------------*
 * Include files
 *----------------------------------------------------------------------------*/

	require_once( plugin_dir_path( __FILE__ ) . 'includes/utilities.php' );
	require_once( plugin_dir_path( __FILE__ ) . 'includes/class-widget.php' );

?>