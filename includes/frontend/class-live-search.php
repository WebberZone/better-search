<?php
/**
 * Functions dealing with live search.
 *
 * @package   Better_Search
 */

namespace WebberZone\Better_Search\Frontend;

use WebberZone\Better_Search\Util\Hook_Registry;

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Class Live_Search
 *
 * @since 4.0.0
 */
class Live_Search {

	/**
	 * Constructor to initialize the class.
	 */
	public function __construct() {
		Hook_Registry::add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		Hook_Registry::add_action( 'wp_ajax_bsearch_live_search', array( $this, 'live_search' ) );
		Hook_Registry::add_action( 'wp_ajax_nopriv_bsearch_live_search', array( $this, 'live_search' ) );
	}

	/**
	 * Enqueue the live search script.
	 */
	public function enqueue_scripts() {
		if ( ! \bsearch_get_option( 'enable_live_search' ) ) {
			return;
		}

		$minimize = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

		wp_enqueue_script(
			'bsearch-live-search',
			plugins_url( 'includes/js/better-search-live-search' . $minimize . '.js', BETTER_SEARCH_PLUGIN_FILE ),
			array(),
			BETTER_SEARCH_VERSION,
			true
		);
		wp_localize_script(
			'bsearch-live-search',
			'bsearch_live_search',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'strings'  => array(
					'no_results'         => __( 'No results found', 'better-search-pro' ),
					'searching'          => __( 'Searching...', 'better-search-pro' ),
					'min_chars'          => __( 'Please enter at least 3 characters to search', 'better-search-pro' ),
					'suggestions_closed' => __( 'Search suggestions closed', 'better-search-pro' ),
					'back_to_search'     => __( 'Back to search', 'better-search-pro' ),
					'back_to_input'      => __( 'Back to search input', 'better-search-pro' ),
					'error_loading'      => __( 'Error loading search results', 'better-search-pro' ),
					'no_suggestions'     => __( 'No search suggestions found', 'better-search-pro' ),
					/* translators: %d is the number of suggestions found */
					'suggestions_found'  => __( '%d search suggestions found. Use up and down arrow keys to navigate.', 'better-search-pro' ),
					/* translators: %s is the destination being navigated to */
					'navigating_to'      => __( 'Navigating to %s', 'better-search-pro' ),
					'submitting_search'  => __( 'Submitting search', 'better-search-pro' ),
					/* translators: %1$d is the current result number, %2$d is the total number of results */
					'result_position'    => __( 'Result %1$d of %2$d', 'better-search-pro' ),
					/* translators: %s is the post title */
					'view_post'          => __( 'View post: %s', 'better-search-pro' ),
				),
			)
		);

		wp_enqueue_style(
			'bsearch-live-search-style',
			plugins_url( 'includes/css/bsearch-live-search' . $minimize . '.css', BETTER_SEARCH_PLUGIN_FILE ),
			array(),
			BETTER_SEARCH_VERSION
		);
	}

	/**
	 * Live search function.
	 */
	public function live_search() {
		$search_query = isset( $_POST['s'] ) ? sanitize_text_field( wp_unslash( $_POST['s'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing

		if ( empty( $search_query ) ) {
			wp_send_json( array() );
		}

		/**
		 * Filter the number of posts to show in the live search.
		 *
		 * @since 4.0.0
		 *
		 * @param int $posts_per_page Number of posts to show in the live search.
		 */
		$posts_per_page = (int) apply_filters( 'bsearch_live_search_posts_per_page', 5 );

		$query = new \Better_Search_Query(
			array(
				'better_search_query' => true,
				's'                   => $search_query,
				'posts_per_page'      => $posts_per_page,
				'post_type'           => wp_parse_list( \bsearch_get_option( 'post_types' ) ),
				'post_status'         => 'publish',
			)
		);

		$results = array();
		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				$results[] = array(
					'title' => html_entity_decode( get_the_title(), ENT_QUOTES, 'UTF-8' ),
					'link'  => get_permalink(),
				);
			}
		}
		wp_reset_postdata();

		$response = array(
			'results' => $results,
			'total'   => count( $results ),
			'query'   => $search_query,
		);

		wp_send_json( $response );
	}
}
