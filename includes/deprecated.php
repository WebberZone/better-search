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
$bsearch_url = plugins_url() . '/' . plugin_basename( dirname( __FILE__ ) );


/**
 * Default options.
 *
 * @since   1.0
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
	 * @since	2.0.0
	 *
	 * @param	array	$bsearch_settings	default options
	 */
	return apply_filters( 'bsearch_default_options', $bsearch_settings );
}


/**
 * Function to read options from the database.
 *
 * @since   1.0
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

