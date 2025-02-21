<?php
/**
 * Better_Search: Main search class.
 *
 * @package Better_Search
 * @subpackage Better_Search
 * @since 4.0.0
 */

use WebberZone\Better_Search\Util\Cache;
use WebberZone\Better_Search\Util\Helpers;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Better_Search: Main search class.
 *
 * @since 4.0.0
 */
class Better_Search_Core_Query extends \WP_Query {

	/**
	 * Blog ID.
	 *
	 * @since 3.0.0
	 * @var int[]
	 */
	public $blog_id;

	/**
	 * Flag to indicate if multiple blogs are being queried.
	 *
	 * @since 4.0.0
	 * @var bool
	 */
	public $multiple_blogs = false;

	/**
	 * Cache set flag.
	 *
	 * @since 3.0.0
	 * @var bool
	 */
	public $in_cache = false;

	/**
	 * Seamless mode flag.
	 *
	 * @since 3.0.0
	 * @var bool
	 */
	public $is_seamless_mode = false;

	/**
	 * Boolean mode flag.
	 *
	 * @since 3.0.0
	 * @var bool
	 */
	public $is_boolean_mode = false;

	/**
	 * Fulltext mode flag.
	 *
	 * @since 3.0.0
	 * @var bool
	 */
	public $use_fulltext = true;

	/**
	 * Holds the search terms.
	 *
	 * @since 3.0.0
	 * @var array
	 */
	public $search_terms = array();

	/**
	 * Holds the search query.
	 *
	 * @since 3.0.0
	 * @var string
	 */
	public $search_query;

	/**
	 * Query vars, before parsing.
	 *
	 * @since 3.0.0
	 * @var array
	 */
	public $input_query_args = array();

	/**
	 * Query vars, after parsing.
	 *
	 * @since 3.0.0
	 * @var array
	 */
	public $query_args = array();

	/**
	 * Holds the array of stopwords.
	 *
	 * @since 3.0.0
	 * @var array
	 */
	public $stopwords = array();

	/**
	 * Holds the Top score.
	 *
	 * @since 3.0.0
	 * @var float
	 */
	public $topscore = 0;

	/**
	 * Holds the match SQL.
	 *
	 * @since 4.0.0
	 * @var string
	 */
	public $match_sql = '';

	/**
	 * Main constructor.
	 *
	 * @since 3.0.0
	 *
	 * @param array|string $args The Query variables. Accepts an array or a query string.
	 */
	public function __construct( $args = array() ) {
		$this->prepare_query_args( $args );

		if ( ( $this->is_seamless_mode && is_main_query() && ! wp_is_serving_rest_request() ) ||
			! empty( $args['better_search_query'] ) ||
			! empty( $this->query_args['better_search_query'] )
		) {
			$this->hooks();
		}
	}

	/**
	 * Initialise search hooks.
	 *
	 * @since 3.0.0
	 */
	public function hooks() {

		if ( isset( $this->query_args['is_better_search_loaded'] ) && $this->query_args['is_better_search_loaded'] ) {
			return;
		}

		add_filter( 'pre_get_posts', array( $this, 'pre_get_posts' ), 10 );
		add_filter( 'posts_fields', array( $this, 'posts_fields' ), 10, 2 );
		add_filter( 'posts_join', array( $this, 'posts_join' ), 10, 2 );
		add_filter( 'posts_search', array( $this, 'posts_search' ), 10, 2 );
		add_filter( 'posts_where', array( $this, 'posts_where' ), 10, 2 );
		add_filter( 'posts_distinct', array( $this, 'posts_distinct' ), 10, 2 );
		add_filter( 'posts_orderby', array( $this, 'posts_orderby' ), 10, 2 );
		add_filter( 'posts_groupby', array( $this, 'posts_groupby' ), 10, 2 );
		add_filter( 'posts_clauses', array( $this, 'posts_clauses' ), 10, 2 );
		add_filter( 'posts_request', array( $this, 'posts_request' ), 10, 2 );
		add_filter( 'better_search_query_posts_request', array( $this, 'set_topscore' ), 999, 2 );
		add_filter( 'posts_pre_query', array( $this, 'posts_pre_query' ), 10, 2 );
		add_filter( 'the_posts', array( $this, 'the_posts' ), 10, 2 );
	}

	/**
	 * Prepare the query variables.
	 *
	 * @since 3.0.0
	 * @see WP_Query::parse_query()
	 * @see bsearch_get_registered_settings()
	 *
	 * @param string|array $args {
	 *     Optional. Array or string of Query parameters.
	 *
	 *     @type array|int[]  $blog_id          An array or comma-separated string of blog IDs.
	 *     @type array|int[]  $include_cat_ids  An array or comma-separated string of category/custom taxonomy term_taxonomy_ids.
	 *     @type array|int[]  $include_post_ids An array or comma-separated string of post IDs.
	 *     @type int          $how_old          How old should published posts be?
	 *     @type bool         $bydate           Sort by date?
	 * }
	 */
	public function prepare_query_args( $args = array() ) {

		$bsearch_settings = bsearch_get_settings();

		$defaults = array(
			'blog_id'          => get_current_blog_id(),
			'include_cat_ids'  => 0,
			'include_post_ids' => 0,
			'how_old'          => 0,
			'bydate'           => 0,
			'is_nested_query'  => false,
		);
		$defaults = array_merge( $defaults, bsearch_settings_defaults(), (array) $bsearch_settings );
		$args     = wp_parse_args( $args, $defaults );
		$args     = Helpers::parse_wp_query_arguments( $args );

		// Set necessary variables.
		$args['better_search_query'] = true;
		$args['suppress_filters']    = false;
		$args['ignore_sticky_posts'] = true;

		// Store query args before we manipulate them.
		$this->input_query_args = $args;

		/**
		 * Applies filters to the query arguments before executing the query.
		 *
		 * This function allows developers to modify the query arguments before the query is executed.
		 *
		 * @since 4.0.0
		 *
		 * @param array                                         $args           The query arguments.
		 * @param \WP_Term|\WP_Post_Type|\WP_Post|\WP_User|null $queried_object The queried object.
		 */
		$args = apply_filters( 'better_search_query_args_before', $args, get_queried_object() );

		// Set some class variables.
		$search_query = isset( $args['s'] ) ? $args['s'] : '';
		$this->set_class_variables( $search_query );

		// Set the number of posts to be retrieved. Use posts_per_page if set else use limit.
		$args['posts_per_page'] = empty( $args['posts_per_page'] ) ? $args['limit'] : $args['posts_per_page'];

		// Set the post types.
		if ( empty( $args['post_type'] ) ) {

			// If post_types is empty or contains a query string then use parse_str else consider it comma-separated.
			if ( ! empty( $args['post_types'] ) && is_array( $args['post_types'] ) ) {
				$post_types = $args['post_types'];
			} elseif ( ! empty( $args['post_types'] ) && false === strpos( $args['post_types'], '=' ) ) {
				$post_types = explode( ',', $args['post_types'] );
			} else {
				parse_str( $args['post_types'], $post_types );  // Save post types in $post_types variable.
			}

			// If post_types is empty or if we want all the post types.
			if ( empty( $post_types ) || 'all' === $args['post_types'] ) {
				$post_types = get_post_types(
					array(
						'public' => true,
					)
				);
			}

			/**
			 * Filter the post_types passed to the query.
			 *
			 * @since 3.0.0
			 *
			 * @param array   $post_types  Array of post types to filter by.
			 * @param array   $args        Arguments array.
			 */
			$args['post_type'] = apply_filters( 'better_search_query_post_types', $post_types, $args );

		}

		// Parse the blog_id argument to get an array of IDs.
		$this->blog_id = wp_parse_id_list( $args['blog_id'] );
		if ( is_multisite() ) {
			if ( count( $this->blog_id ) > 1 || ( 1 === count( $this->blog_id ) && get_current_blog_id() !== (int) $this->blog_id[0] ) ) {
				$this->multiple_blogs = true;
			}
		}

		// Tax Query.
		if ( ! empty( $args['tax_query'] ) && is_array( $args['tax_query'] ) ) {
			$tax_query = $args['tax_query'];
		} else {
			$tax_query = array();
		}

		if ( ! empty( $args['include_cat_ids'] ) ) {
			$tax_query[] = array(
				'field'            => 'term_taxonomy_id',
				'terms'            => wp_parse_id_list( $args['include_cat_ids'] ),
				'include_children' => false,
			);
		}

		if ( ! empty( $args['exclude_categories'] ) ) {
			$tax_query[] = array(
				'field'            => 'term_taxonomy_id',
				'terms'            => wp_parse_id_list( $args['exclude_categories'] ),
				'operator'         => 'NOT IN',
				'include_children' => false,
			);
		}

		/**
		 * Filter the tax_query passed to the query.
		 *
		 * @since 3.0.0
		 *
		 * @param array $tax_query Array of tax_query parameters.
		 * @param array $args      Arguments array.
		 */
		$tax_query = apply_filters( 'better_search_query_tax_query', $tax_query, $args );

		// Add a relation key if more than one $tax_query.
		if ( count( $tax_query ) > 1 ) {
			$tax_query['relation'] = 'AND';
		}

		// Add a relation key if more than one $tax_query.
		if ( count( $tax_query ) > 1 && ! isset( $tax_query['relation'] ) ) {
			/**
			 * Filter the tax_query relation parameter.
			 *
			 * @since 4.0.0
			 *
			 * @param string  $relation The logical relationship between each inner taxonomy array when there is more than one. Default is 'AND'.
			 * @param array   $args     Arguments array.
			 */
			$tax_query['relation'] = apply_filters( 'better_search_query_tax_query_relation', 'AND', $args );
		}

		$args['tax_query'] = $tax_query; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query

		// Set date_query.
		$date_query = array(
			array(
				'after'     => ( 0 === absint( $args['how_old'] ) ) ? '' : gmdate( 'Y-m-d', strtotime( current_time( 'mysql' ) ) - ( absint( $args['how_old'] ) * DAY_IN_SECONDS ) ),
				'before'    => current_time( 'mysql' ),
				'inclusive' => true,
			),
		);

		/**
		 * Filter the date_query passed to WP_Query.
		 *
		 * @since 3.2.2
		 *
		 * @param array   $date_query Array of date parameters to be passed to WP_Query.
		 * @param array   $args       Arguments array.
		 */
		$args['date_query'] = apply_filters( 'better_search_query_date_query', $date_query, $args );

		// Meta Query.
		if ( ! empty( $args['meta_query'] ) && is_array( $args['meta_query'] ) ) {
			$meta_query = $args['meta_query'];
		} else {
			$meta_query = array();
		}

		/**
		 * Filter the meta_query passed to WP_Query.
		 *
		 * @since 3.2.2
		 *
		 * @param array   $meta_query Array of meta_query parameters.
		 * @param array   $args       Arguments array.
		 */
		$meta_query = apply_filters( 'better_search_query_meta_query', $meta_query, $args ); // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query

		// Validate meta_query structure.
		if ( ! is_array( $meta_query ) ) {
			$meta_query = array();
		}

		// Add a relation key if more than one $meta_query and if 'relation' is not already set.
		if ( count( $meta_query ) > 1 && ! isset( $meta_query['relation'] ) ) {
			/**
			 * Filter the meta_query relation parameter.
			 *
			 * @since 3.2.2
			 *
			 * @param string  $relation The logical relationship between each inner meta_query array when there is more than one. Default is 'AND'.
			 * @param array   $args     Arguments array.
			 */
			$meta_query['relation'] = apply_filters( 'better_search_query_meta_query_relation', 'AND', $args );
		}

		$args['meta_query'] = $meta_query; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query

		// Set post_status.
		$args['post_status'] = empty( $args['post_status'] ) ? array( 'publish', 'inherit' ) : $args['post_status'];

		// Set post__not_in for WP_Query using exclude_post_ids.
		$exclude_post_ids = empty( $args['exclude_post_ids'] ) ? array() : wp_parse_id_list( $args['exclude_post_ids'] );

		/**
		 * Filter exclude post IDs array.
		 *
		 * @since 3.0.0
		 *
		 * @param int[] $exclude_post_ids Array of post IDs.
		 * @param array $args             Arguments array.
		 */
		$exclude_post_ids = apply_filters( 'bsearch_exclude_post_ids', $exclude_post_ids, $args );

		$args['post__not_in'] = $exclude_post_ids;

		// Unset what we don't need.
		$args = self::unset_unnecessary_args( $args );

		/**
		 * Filters the arguments of the query.
		 *
		 * @since 4.0.0
		 *
		 * @param array                   $args  Array of arguments.
		 * @param Better_Search_Core_Query $query The Better_Search instance (passed by reference).
		 */
		$this->query_args = apply_filters_ref_array( 'better_search_query_args', array( $args, &$this ) );
	}

	/**
	 * Unset unnecessary arguments that are not needed for the WP_Query.
	 *
	 * @since 4.0.0
	 *
	 * @param array $args Array of arguments.
	 * @return array Modified array of arguments.
	 */
	public static function unset_unnecessary_args( $args ) {
		$unnecessary_args = array(
			'include_heatmap',
			'title',
			'title_daily',
			'daily_range',
			'heatmap_limit',
			'heatmap_smallest',
			'heatmap_largest',
			'heatmap_cold',
			'heatmap_hot',
			'heatmap_before',
			'heatmap_after',
			'link_new_window',
			'link_nofollow',
			'custom_css',
			'excerpt_length',
			'include_thumb',
			'highlight',
			'show_credit',
			'number_format_count',
			'meta_noindex',
		);

		foreach ( $unnecessary_args as $arg ) {
			unset( $args[ $arg ] );
		}

		return $args;
	}

	/**
	 * Sets some of the variables used by the CLASS.
	 *
	 * @since 3.0.0
	 *
	 * @param string $search_query Search query.
	 */
	public function set_class_variables( $search_query = '' ) {

		$use_fulltext = bsearch_get_option( 'use_fulltext' );
		$search_query = empty( $search_query ) ? get_bsearch_query() : Helpers::clean_terms( $search_query );
		$search_words = array();

		// Extract the search terms. We respect quotes.
		$search_query = stripslashes( $search_query ); // Added slashes screw with quote grouping when done early, so done later.
		if ( preg_match_all( '/".*?("|$)|((?<=[\t ",+])|^)[^\t ",+]+/', $search_query, $matches ) ) {
			$search_words = $matches[0];
		}

		// if search terms are less than 3 then turn fulltext off.
		if ( $use_fulltext ) {
			$use_fulltext_proxy = false;
			foreach ( $search_words as $search_word ) {
				if ( strlen( $search_word ) > 3 ) {
					$use_fulltext_proxy = true;
				}
			}
			$use_fulltext = $use_fulltext_proxy;
		}

		$this->search_query     = $search_query;
		$this->search_terms     = $search_words;
		$this->use_fulltext     = $use_fulltext;
		$this->is_boolean_mode  = $this->input_query_args['boolean_mode'];
		$this->is_seamless_mode = $this->input_query_args['seamless'];
	}

	/**
	 * Modify the pre_get_posts clause.
	 *
	 * @since 3.0.0
	 *
	 * @param WP_Query $query The WP_Query instance.
	 */
	public function pre_get_posts( $query ) {

		if ( $this->is_better_search( $query ) ) {
			$query_args = $this->query_args;

			$fields = array(
				'date_query',
				'tax_query',
				'meta_query',
				'post_type',
				'post__not_in',
				'post_status',
				'posts_per_page',
				'author',
				'no_found_rows',
				'suppress_filters',
				'ignore_sticky_posts',
			);

			foreach ( $fields as $field ) {
				if ( ! empty( $query_args[ $field ] ) ) {
					$query->set( $field, $query_args[ $field ] );
				}
			}

			remove_filter( 'pre_get_posts', array( $this, 'pre_get_posts' ) );
		}
	}

	/**
	 * Get the MATCH field of the query
	 *
	 * @since 3.0.0
	 *
	 * @param string $search_query Search query.
	 * @param array  $args Array of arguments.
	 * @return string MATCH field
	 */
	public function get_match_sql( $search_query, $args = array() ) {
		global $wpdb;

		/**
		 * Filter the source post before getting the match SQL.
		 *
		 * @since 3.5.0
		 *
		 * @param string  $match_sql   The match SQL.
		 * @param string  $search_query Source Post instance.
		 * @param array   $args  Arguments array.
		 * @param Better_Search_Core_Query $query The Better_Search instance.
		 */
		$match = apply_filters_ref_array( 'better_search_query_get_match_sql', array( '', $search_query, $args, &$this ) );

		if ( ! empty( $match ) ) {
			return $match;
		}

		// Use argument defaults with a fallback to options, avoiding multiple inline checks.
		$weight_title   = $args['weight_title'] ?? bsearch_get_option( 'weight_title' );
		$weight_content = $args['weight_content'] ?? bsearch_get_option( 'weight_content' );
		$boolean_mode   = $this->is_boolean_mode ? ' IN BOOLEAN MODE' : '';
		$search_query   = wp_specialchars_decode( $search_query, ENT_QUOTES );

		$field_score = '';

		// Create the base MATCH part of the FIELDS clause.
		if ( $this->use_fulltext ) {
			// Prepare the query once and use it with prepared arguments.
			$field_score = $wpdb->prepare(
				"(MATCH({$wpdb->posts}.post_title) AGAINST ('{$search_query}' {$boolean_mode}) * %d) + " . // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				"(MATCH({$wpdb->posts}.post_content) AGAINST ('{$search_query}' {$boolean_mode}) * %d)", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$weight_title,
				$weight_content
			);
		}

		/**
		 * Filter the MATCH part of the FIELDS clause of the query.
		 *
		 * @since 2.0.0
		 * @since 4.0.0 Added $query parameter.
		 *
		 * @param string $field_score    The MATCH section of the FIELDS clause of the query, i.e. score.
		 * @param string $search_query   Search query.
		 * @param int    $weight_title   Weight of title.
		 * @param int    $weight_content Weight of content.
		 * @param array  $args           Array of arguments.
		 * @param Better_Search_Core_Query $query The Better_Search instance (passed by reference).
		 */
		$field_score = apply_filters_ref_array( 'bsearch_posts_match_field', array( $field_score, $search_query, $weight_title, $weight_content, $args, &$this ) );

		$this->match_sql = $field_score;

		return $field_score;
	}

	/**
	 * Modify the SELECT clause - posts_fields.
	 *
	 * @since 3.0.0
	 *
	 * @param string   $fields  The SELECT clause of the query.
	 * @param WP_Query $query The WP_Query instance.
	 * @return string  Updated Fields
	 */
	public function posts_fields( $fields, $query ) {
		global $wpdb;

		if ( ! $this->is_better_search( $query ) ) {
			return $fields;
		}

		$_fields[] = "{$wpdb->posts}.ID as ID";

		$score = $this->get_match_sql( $this->search_query, $this->query_args );
		if ( ! empty( $score ) ) {
			$_fields[] = $score . ' as score';
		}

		$_fields = implode( ', ', $_fields );

		$fields .= ',' . $_fields;

		/**
		 * Filter the SELECT clause of the query.
		 *
		 * @since 2.0.0
		 *
		 * @param string     $fields       The SELECT clause of the query.
		 * @param string     $search_query Search query
		 * @param array      $args         Array of arguments
		 * @param Better_Search_Core_Query $query The Better_Search instance (passed by reference).
		 */
		$fields = apply_filters_ref_array( 'bsearch_posts_fields', array( $fields, $this->search_query, $this->query_args, &$query ) );

		remove_filter( 'posts_fields', array( $this, 'posts_fields' ) );

		return $fields;
	}

	/**
	 * Modify the posts_join clause.
	 *
	 * @since 3.0.0
	 *
	 * @param string   $join  The JOIN clause of the query.
	 * @param WP_Query $query The WP_Query instance.
	 * @return string  Updated JOIN
	 */
	public function posts_join( $join, $query ) {
		global $wpdb;

		if ( $this->is_better_search( $query ) ) {

			// Check for duplicate joins to prevent adding the same join multiple times.
			if ( false === strpos( $join, 'bsq_tr' ) && ! empty( $this->query_args['search_taxonomies'] ) ) {
				$join .= " LEFT JOIN $wpdb->term_relationships AS bsq_tr ON ($wpdb->posts.ID = bsq_tr.object_id) ";
				$join .= " LEFT JOIN $wpdb->term_taxonomy AS bsq_tt ON (bsq_tr.term_taxonomy_id = bsq_tt.term_taxonomy_id) ";
				$join .= " LEFT JOIN $wpdb->terms AS bsq_t ON (bsq_t.term_id = bsq_tt.term_id) ";
			}

			if ( false === strpos( $join, 'bsq_meta' ) && ! empty( $this->query_args['search_meta'] ) ) {
				$join .= " LEFT JOIN $wpdb->postmeta AS bsq_meta ON ($wpdb->posts.ID = bsq_meta.post_id) ";
			}

			if ( false === strpos( $join, 'bsq_users' ) && ! empty( $this->query_args['search_authors'] ) ) {
				$join .= " LEFT JOIN $wpdb->users AS bsq_users ON ($wpdb->posts.post_author = bsq_users.ID) ";
			}

			if ( false === strpos( $join, 'bsq_comments' ) && ! empty( $this->query_args['search_comments'] ) ) {
				$join .= " LEFT JOIN $wpdb->comments AS bsq_comments ON ($wpdb->posts.ID = bsq_comments.comment_post_ID) ";
			}

			/**
			 * Filters the JOIN clause of Better_Search.
			 *
			 * @since 3.0.0
			 *
			 * @param string                    $join  The JOIN clause of the query.
			 * @param Better_Search_Core_Query  $query The Better_Search instance (passed by reference).
			 */
			$join = apply_filters_ref_array( 'better_search_query_posts_join', array( $join, &$query ) );

			// Remove the filter after it's applied to avoid duplicates in future queries.
			remove_filter( 'posts_join', array( $this, 'posts_join' ) );
		}

		return $join;
	}


	/**
	 * Modify the posts_where clause.
	 *
	 * @since 3.0.0
	 *
	 * @param string   $where The WHERE clause of the query.
	 * @param WP_Query $query The WP_Query instance.
	 * @return string  Updated WHERE
	 */
	public function posts_where( $where, $query ) {
		global $bsearch_error;

		if ( ! $this->is_better_search( $query ) ) {
			return $where;
		}

		if ( '' !== $bsearch_error->get_error_message( 'bsearch_banned' ) && bsearch_get_option( 'banned_stop_search' ) ) {
			return ' AND 1=0 ';
		}

		/**
		 * Filters the WHERE clause of the Better_Search.
		 *
		 * @since 3.0.0
		 *
		 * @param string                    $where The WHERE clause of the query.
		 * @param Better_Search_Core_Query  $query The Better_Search instance (passed by reference).
		 */
		$where = apply_filters_ref_array( 'better_search_query_posts_where', array( $where, &$query ) );

		remove_filter( 'posts_where', array( $this, 'posts_where' ) );

		return $where;
	}

	/**
	 * Modify the posts_search clause.
	 *
	 * @since 3.0.0
	 *
	 * @param string   $where The search part of the WHERE clause of the query.
	 * @param WP_Query $query The WP_Query instance.
	 * @return string  Updated WHERE
	 */
	public function posts_search( $where, $query ) {
		global $wpdb;

		if ( ! $this->is_better_search( $query ) ) {
			return $where;
		}

		$n             = ! empty( $this->query_args['exact'] ) ? '' : '%';
		$searchand     = '';
		$search        = '';
		$search_clause = '';

		/**
		 * Filters the prefix that indicates that a search term should be excluded from results.
		 *
		 * @since 3.0.0
		 *
		 * @param string $exclusion_prefix The prefix. Default '-'. Returning
		 *                                 an empty value disables exclusions.
		 */
		$exclusion_prefix = apply_filters( 'better_search_query_exclusion_prefix', '-' );

		$search_terms = $this->search_terms;

		// If this is not a fulltext search, we revert to LIKE based searching.
		if ( ! $this->use_fulltext ) {

			// Check if terms are suitable for searching.
			$search_terms = $this->parse_search_terms( $search_terms );

			// If the search string has only short terms or stopwords, or is 10+ terms long, match it as sentence.
			if ( empty( $search_terms ) || count( $search_terms ) > 9 ) {
				$search_terms = $this->search_query;
			}

			foreach ( (array) $search_terms as $term ) {
				$term = str_replace( array( "'", '"', '&quot;', '\+', '\-' ), '', $term );

				// If there is an $exclusion_prefix, terms prefixed with it should be excluded.
				$exclude = $exclusion_prefix && ( substr( $term, 0, 1 ) === $exclusion_prefix );
				if ( $exclude ) {
					$like_op  = 'NOT LIKE';
					$andor_op = 'AND';
					$term     = substr( $term, 1 );
				} else {
					$like_op  = 'LIKE';
					$andor_op = 'OR';
				}

				$like      = $n . $wpdb->esc_like( $term ) . $n;
				$search   .= $wpdb->prepare( "{$searchand}(({$wpdb->posts}.post_title $like_op %s) $andor_op ({$wpdb->posts}.post_content $like_op %s))", $like, $like ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$searchand = ' AND ';
			}
		} else {
			$search .= $this->get_match_sql( $this->search_query, $this->query_args );
		}

		// Let's do a LIKE search for all other fields.
		$searchand = '';
		foreach ( (array) $search_terms as $term ) {
			$term   = str_replace( array( "'", '"', '&quot;', '\+', '\-' ), '', $term );
			$clause = array();

			// If there is an $exclusion_prefix, terms prefixed with it should be excluded.
			$exclude = $exclusion_prefix && ( substr( $term, 0, 1 ) === $exclusion_prefix );
			if ( $exclude ) {
				$like_op  = 'NOT LIKE';
				$andor_op = ' AND ';
				$term     = substr( $term, 1 );
			} else {
				$like_op  = 'LIKE';
				$andor_op = ' OR ';
			}

			$term = $n . $wpdb->esc_like( $term ) . $n;

			if ( ! empty( $this->query_args['search_taxonomies'] ) ) {
				$clause[] = $wpdb->prepare( "(bsq_t.name $like_op %s)", $term ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$clause[] = $wpdb->prepare( "(bsq_tt.description $like_op %s)", $term ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			}

			if ( ! empty( $this->query_args['search_excerpt'] ) ) {
				$clause[] = $wpdb->prepare( "({$wpdb->posts}.post_excerpt $like_op %s)", $term ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			}

			if ( ! empty( $this->query_args['search_meta'] ) ) {
				$clause[] = $wpdb->prepare( "(bsq_meta.meta_value $like_op %s)", $term ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			}

			if ( ! empty( $this->query_args['search_authors'] ) ) {
				$clause[] = $wpdb->prepare( "(bsq_users.display_name $like_op %s)", $term ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			}

			if ( ! empty( $this->query_args['search_comments'] ) ) {
				$clause[] = $wpdb->prepare( "(bsq_comments.comment_content $like_op %s)", $term ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			}

			/**
			 * Filters the search clauses of Better_Search. This can be used to add custom search clauses.
			 *
			 * @since 4.0.0
			 *
			 * @param array                     $clause The search clause of the query.
			 * @param string                    $term The search term.
			 * @param string                    $like_op The LIKE operator.
			 * @param string                    $andor_op The AND/OR operator.
			 * @param Better_Search_Core_Query  $query The Better_Search instance (passed by reference).
			 */
			$clause = apply_filters_ref_array( 'better_search_query_posts_search_clauses', array( $clause, $term, $like_op, $andor_op, &$query ) );

			if ( ! empty( $clause ) ) {
				$search_clause .= " {$searchand} (" . implode( $andor_op, (array) $clause ) . ') ';
				$searchand      = ' AND ';
			}
		}

		if ( ! empty( $search_clause ) ) {
			$search .= " OR ({$search_clause}) ";
		}

		if ( ! empty( $search ) ) {
			$where = " AND ({$search}) ";

			if ( $this->query_args['exclude_protected_posts'] ) {
				$where .= " AND ({$wpdb->posts}.post_password = '') ";
			}
		}

		/**
		 * Filters the Search part of the WHERE clause of the Better_Search.
		 *
		 * @since 3.0.0
		 *
		 * @param string                    $where The search part of the WHERE clause of the query.
		 * @param Better_Search_Core_Query  $query The Better_Search instance (passed by reference).
		 */
		$where = apply_filters_ref_array( 'better_search_query_posts_search', array( $where, &$query ) );

		remove_filter( 'posts_search', array( $this, 'posts_search' ) );

		return $where;
	}

	/**
	 * Modify the posts_distinct clause.
	 *
	 * @since 3.0.0
	 *
	 * @param string   $distinct The DISTINCT clause of the query.
	 * @param WP_Query $query    The WP_Query instance.
	 * @return string  Updated DISTNCT
	 */
	public function posts_distinct( $distinct, $query ) {

		if ( ! $this->is_better_search( $query ) ) {
			return $distinct;
		}

		$distinct = 'DISTINCT';

		/**
		 * Filters the DISTINCT clause of the Better_Search.
		 *
		 * @since 3.0.0
		 *
		 * @param string                    $distinct The DISTINCT clause of the query.
		 * @param Better_Search_Core_Query  $query The Better_Search instance (passed by reference).
		 */
		$distinct = apply_filters_ref_array( 'better_search_query_posts_distinct', array( $distinct, &$query ) );

		remove_filter( 'posts_distinct', array( $this, 'posts_distinct' ) );

		return $distinct;
	}

	/**
	 * Modify the posts_orderby clause.
	 *
	 * @since 3.0.0
	 *
	 * @param string   $orderby  The ORDER BY clause of the query.
	 * @param WP_Query $query    The WP_Query instance.
	 * @return string  Updated ORDER BY
	 */
	public function posts_orderby( $orderby, $query ) {
		global $wpdb;

		if ( ! $this->is_better_search( $query ) ) {
			return $orderby;
		}

		// If orderby is set, then this was done intentionally and we don't make any modifications.
		if ( ! empty( $query->get( 'orderby' ) ) ) {
			// if orderby is set to relevance, then we need to set the orderby to the match clause.
			if ( ( 'relevance' === $query->get( 'orderby' ) || 'relatedness' === $query->get( 'orderby' ) ) && ! empty( $this->match_sql ) && $this->use_fulltext ) {
				$orderby = ' score DESC ';
			}
			return apply_filters_ref_array( 'better_search_query_posts_orderby', array( $orderby, &$query ) );
		}

		// Initialize an array to build the orderby clauses.
		$orderby_clauses = array();

		if ( ! empty( $this->use_fulltext ) && ! empty( $this->match_sql ) ) {
			$orderby_clauses[] = ' score DESC ';
		}

		if ( ! empty( $this->query_args['bydate'] ) || ( isset( $this->query_args['ordering'] ) && 'date' === $this->query_args['ordering'] ) ) {
			$orderby_clauses[] = " $wpdb->posts.post_date DESC ";
		}

		/**
		 * Filters the posts_orderby of Better_Search after processing and before returning.
		 *
		 * @since 4.0.0
		 *
		 * @param string[]                  $orderby_clauses The SELECT clause of the query.
		 * @param Better_Search_Core_Query  $query The Better_Search instance (passed by reference).
		 */
		$orderby_clauses = apply_filters_ref_array( 'better_search_query_posts_orderby_clauses', array( $orderby_clauses, &$query ) );

		// Combine all the orderby clauses.
		if ( ! empty( $orderby_clauses ) ) {
			$orderby = implode( ', ', $orderby_clauses );
		}

		/**
		 * Filters the GROUP BY clause of the Better_Search.
		 *
		 * @since 3.0.0
		 *
		 * @param string                    $orderby The ORDER BY clause of the query.
		 * @param Better_Search_Core_Query  $query The Better_Search instance (passed by reference).
		 */
		$orderby = apply_filters_ref_array( 'better_search_query_posts_orderby', array( $orderby, &$query ) );

		remove_filter( 'posts_orderby', array( $this, 'posts_orderby' ) );

		return $orderby;
	}

	/**
	 * Modify the posts_groupby clause.
	 *
	 * @since 3.0.0
	 *
	 * @param string   $groupby  The GROUP BY clause of the query.
	 * @param WP_Query $query    The WP_Query instance.
	 * @return string  Updated GROUP BY
	 */
	public function posts_groupby( $groupby, $query ) {

		if ( ! $this->is_better_search( $query ) ) {
			return $groupby;
		}

		/**
		 * Filters the GROUP BY clause of the Better_Search.
		 *
		 * @since 3.0.0
		 *
		 * @param string                    $groupby The GROUP BY clause of the query.
		 * @param Better_Search_Core_Query  $query The Better_Search instance (passed by reference).
		 */
		$groupby = apply_filters_ref_array( 'better_search_query_posts_groupby', array( $groupby, &$query ) );

		remove_filter( 'posts_groupby', array( $this, 'posts_groupby' ) );

		return $groupby;
	}

	/**
	 * Filter posts_clauses in WP_Query.
	 *
	 * @since 4.0.0
	 *
	 * @param string[]  $clauses Array of clauses.
	 * @param \WP_Query $query The WP_Query instance.
	 *
	 * @return array Updated array of clauses.
	 */
	public function posts_clauses( $clauses, $query ) {

		if ( ! $this->is_better_search( $query ) ) {
			return $clauses;
		}

		/**
		 * Filters the posts_clauses of Better_Search after processing and before returning.
		 *
		 * @since 4.0.0
		 *
		 * @param string[]                 $clauses Array of clauses.
		 * @param Better_Search_Core_Query $query   The Better_Search instance (passed by reference).
		 */
		$clauses = apply_filters_ref_array( 'better_search_query_posts_clauses', array( $clauses, &$query ) );

		remove_filter( 'posts_clauses', array( $this, 'posts_clauses' ) );

		return $clauses;
	}

	/**
	 * Filter posts_request in WP_Query.
	 *
	 * @since 4.0.0
	 *
	 * @param string    $request The request.
	 * @param \WP_Query $query   The WP_Query instance.
	 *
	 * @return string Updated request.
	 */
	public function posts_request( $request, $query ) {

		if ( ! $this->is_better_search( $query ) ) {
			return $request;
		}

		/**
		 * Filters the posts_request of Better_Search after processing and before returning.
		 *
		 * @since 4.0.0
		 *
		 * @param string                   $request Array of clauses.
		 * @param Better_Search_Core_Query $query   The Better_Search instance (passed by reference).
		 */
		$request = apply_filters_ref_array( 'better_search_query_posts_request', array( $request, &$query ) );

		remove_filter( 'posts_request', array( $this, 'posts_request' ) );

		return $request;
	}

	/**
	 * Filter posts_pre_query to allow caching to work.
	 *
	 * @since 3.0.0
	 *
	 * @param WP_Post[] $posts Array of post data.
	 * @param WP_Query  $query The WP_Query instance.
	 * @return WP_Post[] Updated Array of post objects.
	 */
	public function posts_pre_query( $posts, $query ) {

		if ( ! $this->is_better_search( $query ) ) {
			return $posts;
		}

		// Check the cache if there are any posts saved.
		if ( ! empty( $this->query_args['cache'] ) && ! ( $query->is_preview() || is_admin() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) ) {
			$cache_name  = $this->get_cache_key( $query );
			$cached_data = get_transient( $cache_name );

			if ( ! empty( $cached_data ) ) {
				$post__in = $cached_data;
				unset( $post__in['found_posts'] );
				$posts = get_posts(
					array(
						'post__in'       => array_keys( $post__in ),
						'fields'         => $query->get( 'fields' ),
						'orderby'        => 'post__in',
						'post_type'      => $query->get( 'post_type' ),
						'posts_per_page' => $query->get( 'posts_per_page' ),
					)
				);
				// Set the score for each of the posts.
				if ( $posts ) {
					foreach ( $posts as $post ) {
						$post->score = isset( $cached_data[ $post->ID ] ) ? $cached_data[ $post->ID ] : 0;
					}
				}
				$query->found_posts   = isset( $cached_data['found_posts'] ) ? $cached_data['found_posts'] : count( $posts );
				$query->max_num_pages = intval( ceil( $query->found_posts / $query->get( 'posts_per_page' ) ) );
				$this->in_cache       = true;
			}
		}

		/**
		 * Filters the posts_pre_query of CRP_Query after processing and before returning.
		 *
		 * @since 3.2.0
		 *
		 * @param \WP_Post[]                $posts Array of post data.
		 * @param Better_Search_Core_Query  $query The Better_Search instance (passed by reference).
		 */
		$posts = apply_filters_ref_array( 'better_search_query_posts_pre_query', array( $posts, &$query ) );

		remove_filter( 'posts_pre_query', array( $this, 'posts_pre_query' ) );

		return $posts;
	}

	/**
	 * Modify the array of retrieved posts.
	 *
	 * @since 3.0.0
	 *
	 * @param WP_Post[] $posts Array of post objects.
	 * @param WP_Query  $query The WP_Query instance (passed by reference).
	 * @return WP_Post[] Updated Array of post objects.
	 */
	public function the_posts( $posts, $query ) {

		if ( ! $this->is_better_search( $query ) ) {
			return $posts;
		}

		// Support caching to speed up retrieval.
		if ( ! empty( $posts ) && ! empty( $this->query_args['cache'] ) && ! $this->in_cache && ! ( $query->is_preview() || is_admin() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) ) {

			/**
			 * Filter the cache time which allows a function to override this
			 *
			 * @since 3.0.0
			 *
			 * @param int                       $cache_time Cache time in seconds
			 * @param array                     $args       Array of all the arguments
			 * @param Better_Search_Core_Query  $query The Better_Search instance (passed by reference).
			 */
			$cache_time = apply_filters_ref_array( 'better_search_query_cache_time', array( $this->query_args['cache_time'], $this->query_args, &$query ) );
			$cache_name = $this->get_cache_key( $query );

			$cached_data = array();
			foreach ( $query->posts as $post ) {
				$cached_data[ $post->ID ] = isset( $post->score ) ? floatval( $post->score ) : 0;
			}
			$cached_data['found_posts'] = $query->found_posts;

			set_transient( $cache_name, $cached_data, $cache_time );
		}

		// Include post IDs.
		if ( ! empty( $this->query_args['include_post_ids'] ) ) { // phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
			$include_post_ids = wp_parse_id_list( $this->query_args['include_post_ids'] );
		}
		if ( ! empty( $include_post_ids ) ) {
			$extra_posts = get_posts(
				array(
					'post__in'  => $include_post_ids,
					'fields'    => $query->get( 'fields' ),
					'orderby'   => 'post__in',
					'post_type' => $query->get( 'post_type' ),
				)
			);
			$posts       = array_merge( $extra_posts, $posts );
		}

		/**
		 * Filter array of WP_Post objects before it is returned to the Better_Search instance.
		 *
		 * @since 3.0.0
		 * @since 4.0.0 Added $query parameter.
		 *
		 * @param WP_Post[]                $posts Array of post objects.
		 * @param array                    $args  Arguments array.
		 * @param Better_Search_Core_Query $query The Better_Search instance (passed by reference).
		 */
		$posts = apply_filters_ref_array( 'better_search_query_the_posts', array( $posts, $this->query_args, &$query ) );

		remove_filter( 'the_posts', array( $this, 'the_posts' ) );

		return $posts;
	}

	/**
	 * Set up the top score for the query. This runs an extra query.
	 *
	 * @since 3.0.0
	 * @since 4.0.6 Changed filter to `better_search_query_posts_request`
	 *
	 * @param string                   $request The complete SQL query.
	 * @param Better_Search_Core_Query $query   The Better_Search instance (passed by reference).
	 * @return string   The SQL query.
	 */
	public function set_topscore( $request, $query ) {
		global $wpdb;

		if ( ! $this->is_better_search( $query ) || ! $this->use_fulltext || ! empty( $query->query_args['is_nested_query'] ) || ! empty( $query->query_vars['is_nested_query'] ) ) {
			$this->topscore  = 0;
			$query->topscore = 0;
			return $request;
		}

		// Check cache first.
		$topscore = 0;
		if ( ! empty( $this->query_args['cache'] ) ) {
			$cache_time = apply_filters( 'better_search_query_cache_time', $this->query_args['cache_time'], $this->query_args );
			$cache_name = $this->get_cache_key( $query, 'ts' );
			$topscore   = get_transient( $cache_name );
		}

		if ( $topscore ) {
			$this->topscore  = $topscore;
			$query->topscore = $topscore;
			return $request;
		}

		// Take $request. Check if there is a LIMIT clause. If so, make sure it's limited to a single entry only. If there is no LIMIT then add it to extract a single entry only. Also, check if there is an ORDER BY clause. If so, make sure it's ordered by the score column. If there is no ORDER BY clause then add one.
		if ( strpos( $request, 'LIMIT' ) !== false ) {
			$score_query = preg_replace( '/LIMIT\s+\\d+(,\\d+)?/', 'LIMIT 1', $request );
		} else {
			$score_query = $request . ' LIMIT 1';
		}

		if ( strpos( $request, 'ORDER BY' ) !== false ) {
			// Only replace if it's not already ordered by score DESC.
			if ( strpos( $request, 'ORDER BY score DESC' ) === false ) {
				// Extract everything after ORDER BY until LIMIT or end of string.
				if ( preg_match( '/ORDER BY\s+(.*?)(?=\s+LIMIT|\s*$)/i', $request, $matches ) ) {
					$current_order = $matches[1];
					// Replace the current ORDER BY clause with score DESC.
					$score_query = str_replace(
						'ORDER BY ' . $current_order,
						'ORDER BY score DESC',
						$score_query
					);
				}
			}
		} else {
			$score_query = $score_query . ' ORDER BY score DESC';
		}

		$this->topscore = (float) $wpdb->get_var( $score_query ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching

		$query->topscore = $this->topscore;

		if ( ! empty( $this->query_args['cache'] ) ) {
			set_transient( $cache_name, $this->topscore, $cache_time );
		}

		return $request;
	}

	/**
	 * Get the cache key.
	 *
	 * @param WP_Query $query The WP_Query instance.
	 * @param string   $context Context of the cache key to be set.
	 * @return string Cache meta key.
	 */
	public function get_cache_key( $query, $context = 'query' ) {
		$cache_attr          = $this->input_query_args;
		$cache_attr['s']     = $this->search_query;
		$cache_attr['paged'] = 1;
		if ( isset( $this->query_args['paged'] ) ) {
			$cache_attr['paged'] = $this->query_args['paged'];
		}
		if ( isset( $query->query_vars['paged'] ) ) {
			$cache_attr['paged'] = $query->query_vars['paged'];
		}

		return Cache::get_key( $cache_attr, $context );
	}

	/**
	 * Check if a query is for a search.
	 *
	 * @since 4.0.0
	 *
	 * @param WP_Query $query The WP_Query instance.
	 * @return bool Whether a query is for a search.
	 */
	public function is_better_search( $query ) {
		if ( ( ! is_admin() && $query->is_search() ) || true === $query->get( 'better_search_query' ) ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Check if the terms are suitable for searching.
	 *
	 * Uses an array of stopwords (terms) that are excluded from the separate
	 * term matching when searching for posts. The list of English stopwords is
	 * the approximate search engines list, and is translatable.
	 *
	 * @since 3.0.0
	 *
	 * @param string[] $terms Array of terms to check.
	 * @return string[] Terms that are not stopwords.
	 */
	protected function parse_search_terms( $terms ) {
		$strtolower = function_exists( 'mb_strtolower' ) ? 'mb_strtolower' : 'strtolower';
		$checked    = array();

		$stopwords = $this->get_search_stopwords();

		foreach ( $terms as $term ) {
			// Keep before/after spaces when term is for exact match.
			if ( preg_match( '/^".+"$/', $term ) ) {
				$term = trim( $term, "\"'" );
			} else {
				$term = trim( $term, "\"' " );
			}

			// Avoid single A-Z and single dashes.
			if ( ! $term || ( 1 === strlen( $term ) && preg_match( '/^[a-z\-]$/i', $term ) ) ) {
				continue;
			}

			if ( in_array( call_user_func( $strtolower, $term ), $stopwords, true ) ) {
				continue;
			}

			$checked[] = $term;
		}

		return $checked;
	}

	/**
	 * Retrieve stopwords used when parsing search terms.
	 *
	 * @since 3.0.0
	 *
	 * @return string[] Stopwords.
	 */
	protected function get_search_stopwords() {
		if ( ! empty( $this->stopwords ) ) {
			return $this->stopwords;
		}

		/*
		* translators: This is a comma-separated list of very common words that should be excluded from a search,
		* like a, an, and the. These are usually called "stopwords". You should not simply translate these individual
		* words into your language. Instead, look for and provide commonly accepted stopwords in your language.
		*/
		$words = explode(
			',',
			_x(
				'about,an,are,as,at,be,by,com,for,from,how,in,is,it,of,on,or,that,the,this,to,was,what,when,where,who,will,with,www',
				'Comma-separated list of search stopwords in your language'
			)
		);

		$stopwords = array();
		foreach ( $words as $word ) {
			$word = trim( $word, "\r\n\t " );
			if ( $word ) {
				$stopwords[] = $word;
			}
		}

		/**
		 * This filter is documented in class-wp-query.php.
		 */
		$this->stopwords = apply_filters( 'wp_search_stopwords', $stopwords );
		return $this->stopwords;
	}
}
