<?php
/**
 * Template Handler
 *
 * @package Better_Search
 */

namespace WebberZone\Better_Search\Frontend;

use WebberZone\Better_Search\Util\Hook_Registry;

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Display Class.
 *
 * @since 4.0.0
 */
class Template_Handler {

	/**
	 * Search results pre-fetched during load_seamless_mode() so the template can reuse them.
	 *
	 * @since 4.3.0
	 *
	 * @var \Better_Search_Query|null
	 */
	private static $prefetched_results = null;

	/**
	 * Constructor class.
	 *
	 * @since 4.0.0
	 */
	public function __construct() {
		Hook_Registry::add_action( 'parse_query', array( $this, 'load_seamless_mode' ) );
		Hook_Registry::add_filter( 'template_include', array( $this, 'template_include' ) );
		Hook_Registry::add_action( 'init', array( $this, 'register_patterns' ) );
		Hook_Registry::add_filter( 'get_block_templates', array( $this, 'manage_block_templates' ), 10, 3 );

		$template_types = array( 'search', 'archive', 'index' );

		foreach ( $template_types as $template_type ) {
			$callback = "add_custom_{$template_type}_template";
			Hook_Registry::add_filter( "{$template_type}_template_hierarchy", array( $this, $callback ) );
		}
	}

	/**
	 * Load seamless mode and hook into WP_Query to check if better_search_query is set and true.
	 * If so, load the Better Search query.
	 * For non-seamless mode, add a filter to bypass the main query for search requests.
	 *
	 * @since 4.0.0
	 *
	 * @param \WP_Query $query Query object.
	 */
	public function load_seamless_mode( $query ) {
		if ( ! $query instanceof \WP_Query ) {
			return;
		}

		$is_seamless           = (bool) bsearch_get_option( 'seamless' );
		$should_force_seamless = $query->get( 'better_search_query' ) ||
			( wp_is_block_theme() && $query->is_search() ) ||
			( $query->is_search() && $is_seamless );

		if ( $should_force_seamless ) {
			if ( ! isset( $query->query_vars['is_better_search_loaded'] ) || ! $query->query_vars['is_better_search_loaded'] ) {
				new \Better_Search_Core_Query( $query->query_vars );
				$query->set( 'is_better_search_loaded', true );
			}
			return;
		}

		if ( $is_seamless || ! $query->is_main_query() || ! $query->is_search() || is_admin() ) {
			return;
		}

		$search_args = self::build_search_args(
			array(
				'better_search_query'     => true,
				'is_better_search_loaded' => true,
			)
		);

		$search_results           = new \Better_Search_Query( $search_args );
		self::$prefetched_results = $search_results;

		$populate_main_query = null;
		$populate_main_query = static function ( $posts, $current_query ) use ( &$populate_main_query, $search_results, $query ) {
			if ( ! $current_query instanceof \WP_Query || $current_query !== $query ) {
				return $posts;
			}

			if ( ! $current_query->is_main_query() || ! $current_query->is_search() ) {
				return $posts;
			}

			remove_filter( 'posts_pre_query', $populate_main_query );

			$current_query->found_posts   = $search_results->found_posts;
			$current_query->max_num_pages = $search_results->max_num_pages;

			return $search_results->posts;
		};

		add_filter( 'posts_pre_query', $populate_main_query, 10, 2 );
	}

	/**
	 * Displays the search results
	 * First checks if the theme contains a search template and uses that
	 * If search template is missing, generates the results below
	 *
	 * @since 4.0.0
	 *
	 * @param string $template Search template to use.
	 */
	public function template_include( $template ) {
		if ( wp_is_block_theme() ) {
			return $template;
		}

		// Early return if not a search page.
		$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
		if ( false === stripos( $request_uri, '?s=' )
			&& false === stripos( $request_uri, '/search/' )
			&& ! is_search() ) {
			return $template;
		}

		global $wp_query;

		// Early return if seamless integration mode is activated.
		if ( bsearch_get_option( 'seamless' ) ) {
			if ( $wp_query->is_404 ) {
				$wp_query->is_404     = false;
				$wp_query->is_archive = true;
			}

			status_header( 200 );
			return $template;
		}

		// If we have a 404 status, set status of 404 to false.
		if ( $wp_query->is_404 ) {
			$wp_query->is_404     = false;
			$wp_query->is_archive = true;
		}

		// Change status code to 200 OK since /search/ returns status code 404.
		status_header( 200 );

		// Add necessary code to the head.
		Hook_Registry::add_action( 'wp_head', array( $this, 'wp_head' ) );

		// Check for a template file within the parent or child theme.
		$template_paths = array(
			get_stylesheet_directory() . '/better-search-template.php',
			get_template_directory() . '/better-search-template.php',
			__DIR__ . '/templates/better-search-template.php',
		);

		foreach ( $template_paths as $template_path ) {
			if ( file_exists( $template_path ) ) {
				return $template_path;
			}
		}

		return $template;
	}

	/**
	 * Register block patters
	 *
	 * @since 4.0.0
	 */
	public function register_patterns() {
		register_block_pattern_category(
			'better-search',
			array( 'label' => esc_html__( 'Better Search', 'better-search' ) )
		);

		$block_patterns = array(
			'search-form',
			'search-results',
			'template-query-loop-news-blog',
		);

		foreach ( $block_patterns as $block_pattern ) {
			$pattern           = require __DIR__ . '/block-patterns/' . $block_pattern . '.php';
			$pattern['source'] = 'plugin';
			register_block_pattern( 'better-search/' . $block_pattern, $pattern );
		}
	}

	/**
	 * Manage block templates for the wz_knowledgebase custom post type.
	 *
	 * @since 4.0.0
	 *
	 * @param array  $query_result   Array of found block templates.
	 * @param array  $query          Arguments to retrieve templates.
	 * @param string $template_type  $template_type wp_template or wp_template_part.
	 * @return array Updated array of found block templates.
	 */
	public function manage_block_templates( $query_result, $query, $template_type ) {
		if ( 'wp_template' !== $template_type ) {
			return $query_result;
		}

		if ( ! is_search() || bsearch_get_option( 'seamless' ) ) {
			return $query_result;
		}

		$theme         = wp_get_theme();
		$block_source  = 'plugin';
		$template_name = 'better-search-template';

		$template_file_path = $theme->get_template_directory() . '/templates/' . $template_name . '.html';
		if ( file_exists( $template_file_path ) ) {
			$block_source = 'theme';
		} else {
			$template_file_path = __DIR__ . '/templates/' . $template_name . '.html';
		}

		$template_contents = self::get_template_content( $template_file_path );

		$new_block                 = new \WP_Block_Template();
		$new_block->type           = 'wp_template';
		$new_block->theme          = $theme->stylesheet;
		$new_block->slug           = $template_name;
		$new_block->id             = 'wzkb//' . $template_name;
		$new_block->title          = 'Better Search Results Template - ' . $template_name;
		$new_block->description    = '';
		$new_block->source         = $block_source;
		$new_block->status         = 'publish';
		$new_block->has_theme_file = true;
		$new_block->is_custom      = true;
		$new_block->content        = $template_contents;

		$query_result[] = $new_block;

		return $query_result;
	}

	/**
	 * Return pre-fetched search results cached during load_seamless_mode().
	 *
	 * @since 4.3.0
	 *
	 * @return \Better_Search_Query|null
	 */
	public static function get_prefetched_results() {
		return self::$prefetched_results;
	}

	/**
	 * Build search args from GET params using the same logic as better-search-template.php.
	 *
	 * @since 4.3.0
	 *
	 * @param array $extra_args Additional args to merge (take precedence).
	 * @return array
	 */
	private static function build_search_args( array $extra_args = array() ) {
		$bsearch_settings = bsearch_get_settings();

		$limit = filter_input(
			INPUT_GET,
			'limit',
			FILTER_VALIDATE_INT,
			array(
				'options' => array(
					'default'   => $bsearch_settings['limit'],
					'min_range' => 1,
				),
			)
		);

		$bydate = filter_input(
			INPUT_GET,
			'bydate',
			FILTER_VALIDATE_INT,
			array(
				'options' => array(
					'default'   => 0,
					'min_range' => 0,
				),
			)
		);

		$current_page     = (int) get_query_var( 'paged', 1 );
		$post_types_param = filter_input( INPUT_GET, 'post_types', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		$post_type_param  = filter_input( INPUT_GET, 'post_type', FILTER_SANITIZE_FULL_SPECIAL_CHARS );

		$selected_post_types = 'any';
		if ( $post_types_param ) {
			$selected_post_types = sanitize_text_field( wp_unslash( $post_types_param ) );
		} elseif ( $post_type_param ) {
			$selected_post_types = sanitize_key( wp_unslash( $post_type_param ) );
		}

		$post_types = ( 'any' === $selected_post_types ) ? bsearch_get_option( 'post_types' ) : $selected_post_types;
		$post_types = wp_parse_list( $post_types );

		$args = array(
			's'              => get_search_query(),
			'posts_per_page' => $limit,
			'paged'          => $current_page,
			'orderby'        => $bydate ? 'date' : 'relevance',
			'post_type'      => $post_types,
		);

		/**
		 * Filter the arguments that are passed to Better_Search_Query.
		 * Documented in includes/frontend/templates/better-search-template.php
		 */
		$args = apply_filters( 'bsearch_template_query_args', $args );

		return array_merge( $args, $extra_args );
	}

	/**
	 * Get the content of a template file.
	 *
	 * @param string $template The template file to include.
	 * @return string The content of the template file.
	 */
	public static function get_template_content( $template ) {
		ob_start();
		include $template;
		return ob_get_clean();
	}

	/**
	 * Insert styles into WordPress Head. Filters `wp_head`.
	 *
	 * @since 4.0.0
	 */
	public static function wp_head() {

		if ( is_search() ) {
			// Add noindex to search results page.
			if ( bsearch_get_option( 'meta_noindex' ) ) {
				echo '<meta name="robots" content="noindex,follow" />';
			}
		}
	}

	/**
	 * Add custom template for the wz_knowledgebase custom post type and wzkb_category taxonomy.
	 *
	 * @since 4.0.0
	 *
	 * @param array  $templates Array of found templates.
	 * @param string $type Type of template (archive, single, taxonomy).
	 * @param string $template_name Template name to add.
	 * @return array Updated array of found templates.
	 */
	protected function add_custom_template( $templates, $type, $template_name ) {
		if ( in_array( $type, array( 'archive', 'index', 'search' ), true ) ) {
			array_unshift( $templates, $template_name );
		}
		return $templates;
	}

	/**
	 * Add custom archive template for the wz_knowledgebase custom post type.
	 *
	 * @since 4.0.0
	 *
	 * @param array $templates Array of found templates.
	 * @return array Updated array of found templates.
	 */
	public function add_custom_archive_template( $templates ) {
		if ( is_search() ) {
			return $this->add_custom_template( $templates, 'search', 'better-search-template' );
		}
		return $this->add_custom_template( $templates, 'archive', 'better-search-template' );
	}

	/**
	 * Add custom archive template for the wz_knowledgebase custom post type.
	 *
	 * @since 4.0.0
	 *
	 * @param array $templates Array of found templates.
	 * @return array Updated array of found templates.
	 */
	public function add_custom_index_template( $templates ) {
		return $this->add_custom_archive_template( $templates );
	}

	/**
	 * Add custom search template for the wz_knowledgebase custom post type.
	 *
	 * @since 4.0.0
	 *
	 * @param array $templates Array of found templates.
	 * @return array Updated array of found templates.
	 */
	public function add_custom_search_template( $templates ) {
		return $this->add_custom_archive_template( $templates );
	}
}
