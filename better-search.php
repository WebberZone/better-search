<?php
/**
 * Better Search replaces the default WordPress search with a better search that gives contextual results sorted by relevance
 *
 * Better Search is a plugin that will replace the default WordPress search page
 * with highly relevant search results improving your visitors search experience.
 *
 * @package Better_Search
 * @author    Ajay D'Souza <me@ajaydsouza.com>
 * @license   GPL-2.0+
 * @link      https://webberzone.com
 * @copyright 2009-2016 Ajay D'Souza
 *
 * @wordpress-plugin
 * Plugin Name: Better Search
 * Plugin URI:  https://webberzone.com/plugins/better-search/
 * Description: Replace the default WordPress search with a contextual search. Search results are sorted by relevancy ensuring a better visitor search experience.
 * Version:     2.1.1
 * Author:      Ajay D'Souza
 * Author URI:  https://webberzone.com/
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
$bsearch_db_version = '1.0';


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
	load_plugin_textdomain( 'better-search', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}
add_action( 'plugins_loaded', 'bsearch_lang_init' );


/**
 * Gets the search results.
 *
 * @since	1.2
 *
 * @param	string     $search_query	Search term.
 * @param	int|string $limit			Maximum number of search results.
 * @return	string     Search results
 */
function get_bsearch_results( $search_query = '', $limit = '' ) {
	global $wpdb, $bsearch_settings;

	if ( ! ( $limit ) ) {
		$limit = isset( $_GET['limit'] ) ? intval( $_GET['limit'] ) : $bsearch_settings['limit']; // Read from GET variable.
	}

	$bydate = isset( $_GET['bydate'] ) ? intval( $_GET['bydate'] ) : 0;		// Order by date or by score?

	$topscore = 0;

	$matches = get_bsearch_matches( $search_query, $bydate );	// Fetch the search results for the search term stored in $search_query.
	$searches = $matches[0];	// 0 index contains the search results always.

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
	$searches = array_slice( $searches, $match_range[0], $match_range[1] - $match_range[0] + 1 );	// Extract the elements for the page from the complete results array.

	$output = '';

	/* Lets start printing the results */
	if ( '' != $search_query ) {
		if ( $searches ) {
			$output .= get_bsearch_header( $search_query, $numrows, $limit );

			$search_query = preg_quote( $search_query, '/' );
			$keys = explode( ' ', str_replace( array( "'", "\"", "&quot;", "\+", "\-" ), "", $search_query ) );

			foreach ( $searches as $search ) {
				$score = $search->score;
				$search = get_post( $search->ID );
				$post_title = get_the_title( $search->ID );

				/* Highlight the search terms in the title */
				if ( $bsearch_settings['highlight'] ) {
					$post_title  = preg_replace( '/(?!<[^>]*?>)('. implode( '|', $keys ) . ')(?![^<]*?>)/iu', '<span class="bsearch_highlight">$1</span>', $post_title );
				}

				$output .= '<h2><a href="' . get_permalink( $search->ID ).'" rel="bookmark">' . $post_title . '</a></h2>';

				$output .= '<p>';
				$output .= '<span class="bsearch_score">' . get_bsearch_score( $search, $score, $topscore ) . '</span>';

				$before = __( 'Posted on: ', 'better-search' );

				$output .= '<span class="bsearch_date">' . get_bsearch_date( $search, __( 'Posted on: ', 'better-search' ) ) . '</span>';
				$output .= '</p>';

				$output .= '<p>';
				if ( $bsearch_settings['include_thumb'] ) {
					$output .= '<p class="bsearch_thumb">' . get_the_post_thumbnail( $search->ID, 'thumbnail' ) . '</p>';
				}

				$excerpt = get_bsearch_excerpt( $search->ID, $bsearch_settings['excerpt_length'] );

				/* Highlight the search terms in the excerpt */
				if ( $bsearch_settings['highlight'] ) {
					$excerpt  = preg_replace( '/(?!<[^>]*?>)('. implode( '|', $keys ) . ')(?![^<]*?>)/iu', '<span class="bsearch_highlight">$1</span>', $excerpt );
				}

				$output .= '<span class="bsearch_excerpt">' . $excerpt . '</span>';

				$output .= '</p>';
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

	if ( $bsearch_settings['show_credit'] ) {
		$output .= '<hr /><p style="text-align:center">';
		$output .= __( 'Powered by ', 'better-search' );
		$output .= '<a href="https://webberzone.com/plugins/better-search/">Better Search plugin</a></p>';
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
 * Returns an array with the cleaned-up search string at the zero index and possibly a list of terms in the second.
 *
 * @since	1.2
 *
 * @param	mixed $search_query   The search term.
 * @return	array	Cleaned up search string
 */
function get_bsearch_terms( $search_query = '' ) {
	global $bsearch_settings;

	if ( ( '' == $search_query ) || empty( $search_query ) ) {
		$search_query = get_bsearch_query();
	}
	$s_array[0] = $search_query;

	$use_fulltext = $bsearch_settings['use_fulltext'];

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
 * @param	string $search_info    Search terms array
 * @param	bool   $bydate         Sort by date?
 * @return	array	Search results
 */
function get_bsearch_matches( $search_query, $bydate ) {
	global $wpdb, $bsearch_settings;

	// if there are two items in $search_info, the string has been broken into separate terms that
	// are listed at $search_info[1]. The cleaned-up version of $search_query is still at the zero index.
	// This is when fulltext is disabled, and we search using LIKE
	$search_info = get_bsearch_terms( $search_query );

	// Get search transient
	$search_query_transient = 'bs_' . preg_replace( '/[^A-Za-z0-9\-]/', '', str_replace( ' ', '', $search_query ) );

	/**
	 * Filter name of the search transient
	 *
	 * @since	2.1.0
	 *
	 * @param	string	$search_query_transient	Transient name
	 * @param	array	$search_query	Search query
	 */
	$search_query_transient = apply_filters( 'bsearch_transient_name', $search_query_transient, $search_query );
	$search_query_transient = substr( $search_query_transient, 0, 40 );	// Name of the transient limited to 40 chars

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

	if ( $bsearch_settings['cache'] ) {
		// Set search transient
		set_transient( $search_query_transient, $matches, 7200 );
	}

	/**
	 * Described in better-search.php
	 */
	return apply_filters( 'get_bsearch_matches', $matches, $search_info );
}


/**
 * returns an array with the first and last indices to be displayed on the page.
 *
 * @since	1.2
 *
 * @param	int $numrows    Total results
 * @param 	int $limit      Results per page
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
 * Default options.
 *
 * @since	1.0
 *
 * @return	array	Default options array
 */
function bsearch_default_options() {
	$title = __( '<h3>Popular Searches</h3>', 'better-search' );
	$title_daily = __( '<h3>Weekly Popular Searches</h3>', 'better-search' );

	// Get relevant post types.
	$args = array(
		'public' => true,
		'_builtin' => true,
	);
	$post_types	= http_build_query( get_post_types( $args ), '', '&' );

	$custom_CSS = '
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
		'seamless' => true,				// Seamless integration mode
		'track_popular' => true,		// Track the popular searches
		'track_admins' => true,			// Track Admin searches
		'track_editors' => true,		// Track Editor searches
		'cache' => true,				// Enable Cache
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
 * @since	1.0
 *
 * @return	array	Better Search options array
 */
function bsearch_read_options() {

	// Upgrade table code.
	global $bsearch_db_version, $network_wide;

	$bsearch_settings_changed = false;

	$defaults = bsearch_default_options();

	$bsearch_settings = array_map( 'stripslashes', (array) get_option( 'ald_bsearch_settings' ) );
	unset( $bsearch_settings[0] ); // Produced by the (array) casting when there's nothing in the DB.

	foreach ( $defaults as $k => $v ) {
		if ( ! isset( $bsearch_settings[ $k ] ) ) {
			$bsearch_settings[ $k ] = $v;
			$bsearch_settings_changed = true;
		}
	}
	if ( true == $bsearch_settings_changed ) {
		update_option( 'ald_bsearch_settings', $bsearch_settings );
	}

	/**
	 * Filters options read from DB for Better Search
	 *
	 * @since	2.0.0
	 *
	 * @param	array	$bsearch_settings	Read options
	 */
	return apply_filters( 'bsearch_read_options', $bsearch_settings );
}


/**
 * Fired for each blog when the plugin is activated.
 *
 * @since	1.0
 *
 * @param    boolean $network_wide    True if WPMU superadmin uses
 *                                    "Network Activate" action, false if
 *                                    WPMU is disabled or plugin is
 *                                    activated on an individual blog.
 */
function bsearch_install( $network_wide ) {
	global $wpdb;

	if ( is_multisite() && $network_wide ) {

		// Get all blogs in the network and activate plugin on each one.
		$blog_ids = $wpdb->get_col( "
        	SELECT blog_id FROM $wpdb->blogs
			WHERE archived = '0' AND spam = '0' AND deleted = '0'
		" );
		foreach ( $blog_ids as $blog_id ) {
			switch_to_blog( $blog_id );
			bsearch_single_activate();
		}

		// Switch back to the current blog.
		restore_current_blog();

	} else {
		bsearch_single_activate();
	}
}
register_activation_hook( __FILE__, 'bsearch_install' );


/*
 * ----------------------------------------------------------------------------*
 * Include files
 *----------------------------------------------------------------------------
 */

	require_once( plugin_dir_path( __FILE__ ) . 'includes/activation.php' );
	require_once( plugin_dir_path( __FILE__ ) . 'includes/wp-filters.php' );
	require_once( plugin_dir_path( __FILE__ ) . 'includes/query.php' );
	require_once( plugin_dir_path( __FILE__ ) . 'includes/general-template.php' );
	require_once( plugin_dir_path( __FILE__ ) . 'includes/template-redirect.php' );
	require_once( plugin_dir_path( __FILE__ ) . 'includes/utilities.php' );
	require_once( plugin_dir_path( __FILE__ ) . 'includes/modules/tracker.php' );
	require_once( plugin_dir_path( __FILE__ ) . 'includes/modules/cache.php' );
	require_once( plugin_dir_path( __FILE__ ) . 'includes/modules/seamless.php' );
	require_once( plugin_dir_path( __FILE__ ) . 'includes/modules/class-widget.php' );
	require_once( plugin_dir_path( __FILE__ ) . 'includes/modules/heatmap.php' );
	require_once( plugin_dir_path( __FILE__ ) . 'includes/modules/shortcode.php' );

/*
 *----------------------------------------------------------------------------*
 * Dashboard and Administrative Functionality
 *----------------------------------------------------------------------------
 */
if ( is_admin() ) {

	/**
	 *  Load the admin pages if we're in the Admin.
	 */
	require_once( plugin_dir_path( __FILE__ ) . '/admin/admin.php' );

} // End admin.inc

