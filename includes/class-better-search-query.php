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

			add_filter( 'pre_get_posts', array( $core_query, 'pre_get_posts' ), 10 );
			add_filter( 'posts_fields', array( $core_query, 'posts_fields' ), 10, 2 );
			add_filter( 'posts_join', array( $core_query, 'posts_join' ), 10, 2 );
			add_filter( 'posts_search', array( $core_query, 'posts_search' ), 10, 2 );
			add_filter( 'posts_where', array( $core_query, 'posts_where' ), 10, 2 );
			add_filter( 'posts_distinct', array( $core_query, 'posts_distinct' ), 10, 2 );
			add_filter( 'posts_orderby', array( $core_query, 'posts_orderby' ), 10, 2 );
			add_filter( 'posts_groupby', array( $core_query, 'posts_groupby' ), 10, 2 );
			add_filter( 'posts_clauses', array( $core_query, 'posts_clauses' ), 10, 2 );
			add_filter( 'posts_request', array( $core_query, 'posts_request' ), 10, 2 );
			add_filter( 'better_search_query_posts_request', array( $core_query, 'set_topscore' ), PHP_INT_MAX, 2 );
			add_filter( 'posts_pre_query', array( $core_query, 'posts_pre_query' ), 10, 2 );
			add_filter( 'the_posts', array( $core_query, 'the_posts' ), 10, 2 );

			parent::__construct( $core_query->query_args );

			// Remove filters after use.
			remove_filter( 'pre_get_posts', array( $core_query, 'pre_get_posts' ) );
			remove_filter( 'posts_fields', array( $core_query, 'posts_fields' ) );
			remove_filter( 'posts_join', array( $core_query, 'posts_join' ) );
			remove_filter( 'posts_search', array( $core_query, 'posts_search' ) );
			remove_filter( 'posts_where', array( $core_query, 'posts_where' ) );
			remove_filter( 'posts_distinct', array( $core_query, 'posts_distinct' ) );
			remove_filter( 'posts_orderby', array( $core_query, 'posts_orderby' ) );
			remove_filter( 'posts_groupby', array( $core_query, 'posts_groupby' ) );
			remove_filter( 'posts_clauses', array( $core_query, 'posts_clauses' ) );
			remove_filter( 'posts_request', array( $core_query, 'posts_request' ) );
			remove_filter( 'better_search_query_posts_request', array( $core_query, 'set_topscore' ), PHP_INT_MAX );
			remove_filter( 'posts_pre_query', array( $core_query, 'posts_pre_query' ) );
			remove_filter( 'the_posts', array( $core_query, 'the_posts' ) );
		}
	}
endif;
