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
			$better_search = new Better_Search( $args );

			add_filter( 'pre_get_posts', array( $better_search, 'pre_get_posts' ), 10 );
			add_filter( 'posts_fields', array( $better_search, 'posts_fields' ), 10, 2 );
			add_filter( 'posts_join', array( $better_search, 'posts_join' ), 10, 2 );
			add_filter( 'posts_search', array( $better_search, 'posts_search' ), 10, 2 );
			add_filter( 'posts_where', array( $better_search, 'posts_where' ), 10, 2 );
			add_filter( 'posts_distinct', array( $better_search, 'posts_distinct' ), 10, 2 );
			add_filter( 'posts_orderby', array( $better_search, 'posts_orderby' ), 10, 2 );
			add_filter( 'posts_groupby', array( $better_search, 'posts_groupby' ), 10, 2 );
			add_filter( 'posts_clauses_request', array( $better_search, 'set_topscore' ), 10, 2 );
			add_filter( 'posts_pre_query', array( $better_search, 'posts_pre_query' ), 10, 2 );
			add_filter( 'the_posts', array( $better_search, 'the_posts' ), 10, 2 );

			parent::__construct( $better_search->query_args );

			// Remove filters after use.
			remove_filter( 'pre_get_posts', array( $better_search, 'pre_get_posts' ) );
			remove_filter( 'posts_fields', array( $better_search, 'posts_fields' ) );
			remove_filter( 'posts_join', array( $better_search, 'posts_join' ) );
			remove_filter( 'posts_search', array( $better_search, 'posts_search' ) );
			remove_filter( 'posts_where', array( $better_search, 'posts_where' ) );
			remove_filter( 'posts_distinct', array( $better_search, 'posts_distinct' ) );
			remove_filter( 'posts_orderby', array( $better_search, 'posts_orderby' ) );
			remove_filter( 'posts_groupby', array( $better_search, 'posts_groupby' ) );
			remove_filter( 'posts_clauses_request', array( $better_search, 'set_topscore' ) );
			remove_filter( 'posts_pre_query', array( $better_search, 'posts_pre_query' ) );
			remove_filter( 'the_posts', array( $better_search, 'the_posts' ) );
		}
	}
endif;
