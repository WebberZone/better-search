<?php
/**
 * Query API: Better_Search_Query class
 *
 * @package Better_Search
 * @subpackage Better_Search_Query
 * @since 3.0.0
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

use WebberZone\Better_Search\Util\Hook_Registry;

if ( ! class_exists( 'Better_Search_Query' ) ) :
	/**
	 * Query API: Better_Search_Query class.
	 *
	 * @since 3.0.0
	 */
	class Better_Search_Query extends WP_Query {

		/**
		 * Main constructor.
		 *
		 * @since 3.0.0
		 *
		 * @param array|string $args The Query variables. Accepts an array or a query string.
		 */
		public function __construct( $args = array() ) {
			$args       = wp_parse_args( $args, array( 'is_better_search_loaded' => true ) );
			$core_query = new Better_Search_Core_Query( $args );

			Hook_Registry::add_filter( 'pre_get_posts', array( $core_query, 'pre_get_posts' ), 10 );
			Hook_Registry::add_filter( 'posts_fields', array( $core_query, 'posts_fields' ), 10, 2 );
			Hook_Registry::add_filter( 'posts_join', array( $core_query, 'posts_join' ), 10, 2 );
			Hook_Registry::add_filter( 'posts_search', array( $core_query, 'posts_search' ), 10, 2 );
			Hook_Registry::add_filter( 'posts_where', array( $core_query, 'posts_where' ), 10, 2 );
			Hook_Registry::add_filter( 'posts_distinct', array( $core_query, 'posts_distinct' ), 10, 2 );
			Hook_Registry::add_filter( 'posts_orderby', array( $core_query, 'posts_orderby' ), 10, 2 );
			Hook_Registry::add_filter( 'posts_groupby', array( $core_query, 'posts_groupby' ), 10, 2 );
			Hook_Registry::add_filter( 'posts_clauses', array( $core_query, 'posts_clauses' ), 10, 2 );
			Hook_Registry::add_filter( 'posts_request', array( $core_query, 'posts_request' ), 10, 2 );
			Hook_Registry::add_filter( 'better_search_query_posts_request', array( $core_query, 'set_topscore' ), 99999, 3 );
			Hook_Registry::add_filter( 'posts_pre_query', array( $core_query, 'posts_pre_query' ), 10, 2 );
			Hook_Registry::add_filter( 'the_posts', array( $core_query, 'the_posts' ), 10, 2 );

			parent::__construct( $core_query->query_args );

			// Remove filters after use.
			Hook_Registry::remove_filter( 'pre_get_posts', array( $core_query, 'pre_get_posts' ) );
			Hook_Registry::remove_filter( 'posts_fields', array( $core_query, 'posts_fields' ) );
			Hook_Registry::remove_filter( 'posts_join', array( $core_query, 'posts_join' ) );
			Hook_Registry::remove_filter( 'posts_search', array( $core_query, 'posts_search' ) );
			Hook_Registry::remove_filter( 'posts_where', array( $core_query, 'posts_where' ) );
			Hook_Registry::remove_filter( 'posts_distinct', array( $core_query, 'posts_distinct' ) );
			Hook_Registry::remove_filter( 'posts_orderby', array( $core_query, 'posts_orderby' ) );
			Hook_Registry::remove_filter( 'posts_groupby', array( $core_query, 'posts_groupby' ) );
			Hook_Registry::remove_filter( 'posts_clauses', array( $core_query, 'posts_clauses' ) );
			Hook_Registry::remove_filter( 'posts_request', array( $core_query, 'posts_request' ) );
			Hook_Registry::remove_filter( 'better_search_query_posts_request', array( $core_query, 'set_topscore' ), 99999 );
			Hook_Registry::remove_filter( 'posts_pre_query', array( $core_query, 'posts_pre_query' ) );
			Hook_Registry::remove_filter( 'the_posts', array( $core_query, 'the_posts' ) );
		}
	}
endif;
