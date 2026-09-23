<?php
/**
 * Registers Better Search abilities.
 *
 * @package WebberZone\Better_Search
 */

namespace WebberZone\Better_Search;

use WebberZone\Better_Search\Util\Hook_Registry;

if ( ! defined( 'WPINC' ) ) {
	exit;
}

/**
 * Registers the shared Better Search abilities.
 *
 * @since 4.5.0
 */
class Abilities {

	/**
	 * Register ability hooks.
	 *
	 * @since 4.5.0
	 */
	public function __construct() {
		// The Abilities API arrived in WordPress 6.9; without it there is nothing to register.
		if ( ! Feature_Manager::is_enabled( 'abilities_api' ) || ! function_exists( 'wp_register_ability' ) ) {
			return;
		}

		Hook_Registry::add_action( 'wp_abilities_api_categories_init', array( $this, 'register_category' ) );
		Hook_Registry::add_action( 'wp_abilities_api_init', array( $this, 'register_abilities' ) );
	}

	/**
	 * Register the WebberZone ability category if it is not already present.
	 *
	 * @since 4.5.0
	 */
	public function register_category(): void {
		if ( \wp_has_ability_category( 'webberzone' ) ) {
			return;
		}

		\wp_register_ability_category(
			'webberzone',
			array(
				'label'       => __( 'WebberZone', 'better-search' ),
				'description' => __( 'Abilities provided by WebberZone plugins.', 'better-search' ),
			)
		);
	}

	/**
	 * Register the shared abilities.
	 *
	 * @since 4.5.0
	 */
	public function register_abilities(): void {
		\wp_register_ability(
			'better-search/search',
			array(
				'label'               => __( 'Search Site Content', 'better-search' ),
				'description'         => __( 'Runs a relevance-weighted search of the site content and returns the best matching posts. Pass a few distinctive keywords rather than a sentence or question: search terms are combined with AND, so a long natural-language question usually matches nothing while its keywords match well. Optionally limit the search to given post types. Returns the matching results plus the total number of matches, so use total with limit and offset to page through everything. Results are ordered by relevance and include each post ID, title, URL, excerpt, and a relevance score that is only comparable within the same result set.', 'better-search' ),
				'category'            => 'webberzone',
				'input_schema'        => array(
					'type'                 => 'object',
					'required'             => array( 'query' ),
					'additionalProperties' => false,
					'properties'           => array(
						'query'      => array(
							'type'        => 'string',
							'minLength'   => 1,
							'maxLength'   => 500,
							'description' => __( 'Search phrase to look for.', 'better-search' ),
						),
						'post_types' => array(
							'type'        => 'array',
							'items'       => array( 'type' => 'string' ),
							'description' => __( 'Public post type names to search. Defaults to the post types configured in Better Search.', 'better-search' ),
						),
						'limit'      => array(
							'type'        => 'integer',
							'default'     => 10,
							'minimum'     => 1,
							'maximum'     => 100,
							'description' => __( 'Maximum number of results to return.', 'better-search' ),
						),
						'offset'     => array(
							'type'        => 'integer',
							'default'     => 0,
							'minimum'     => 0,
							'description' => __( 'Number of results to skip before returning matches.', 'better-search' ),
						),
					),
				),
				'output_schema'       => array(
					'type'                 => 'object',
					'required'             => array( 'results', 'total' ),
					'additionalProperties' => false,
					'properties'           => array(
						'results' => array(
							'type'  => 'array',
							'items' => array(
								'type'                 => 'object',
								'required'             => array( 'id', 'title', 'url', 'excerpt', 'relevance' ),
								'additionalProperties' => false,
								'properties'           => array(
									'id'        => array( 'type' => 'integer' ),
									'title'     => array( 'type' => 'string' ),
									'url'       => array(
										'type'   => 'string',
										'format' => 'uri',
									),
									'excerpt'   => array( 'type' => 'string' ),
									'relevance' => array(
										'type'        => 'number',
										'minimum'     => 0,
										'description' => __( 'Raw relevance score. Comparable only against other results for the same search.', 'better-search' ),
									),
								),
							),
						),
						'total'   => array(
							'type'        => 'integer',
							'minimum'     => 0,
							'description' => __( 'Total number of posts matching the search, ignoring limit and offset.', 'better-search' ),
						),
					),
				),
				'execute_callback'    => array( $this, 'search' ),
				'permission_callback' => array( $this, 'can_search' ),
				'meta'                => array(
					'public'       => true,
					'show_in_rest' => true,
					'annotations'  => array(
						'readonly'    => true,
						'destructive' => false,
						'idempotent'  => true,
					),
				),
			)
		);
	}

	/**
	 * Check whether the current user can run a search.
	 *
	 * @since 4.5.0
	 *
	 * @param  mixed $input Ability input.
	 * @return bool Whether the search may run.
	 */
	public function can_search( $input = array() ): bool {
		unset( $input );

		// The abilities REST controller has no auth gate of its own, so this keeps the endpoint signed in.
		return current_user_can( 'read' );
	}

	/**
	 * Run a relevance-weighted search.
	 *
	 * @since 4.5.0
	 *
	 * @param  mixed $input Ability input.
	 * @return array|\WP_Error Search results or an error.
	 */
	public function search( $input ) {
		if ( ! is_array( $input ) || ! isset( $input['query'] ) || ! is_string( $input['query'] ) || '' === trim( $input['query'], " \t\n\r\0\x0B" ) ) {
			return new \WP_Error(
				'bsearch_invalid_ability_input',
				__( 'A search phrase is required.', 'better-search' ),
				array( 'status' => 400 )
			);
		}

		$args = array(
			's'      => trim( $input['query'], " \t\n\r\0\x0B" ),
			'limit'  => isset( $input['limit'] ) ? absint( $input['limit'] ) : 10,
			'offset' => isset( $input['offset'] ) ? absint( $input['offset'] ) : 0,
		);

		if ( isset( $input['post_types'] ) ) {
			$post_types = $this->sanitize_post_types( $input['post_types'] );

			if ( is_wp_error( $post_types ) ) {
				return $post_types;
			}

			$args['post_types'] = $post_types;
		}

		$query   = new \Better_Search_Query( $args );
		$results = array();

		foreach ( (array) $query->posts as $post ) {
			if ( ! $post instanceof \WP_Post ) {
				$post = get_post( $post );
			}

			if ( ! $post instanceof \WP_Post || ! $this->is_readable_post( $post ) ) {
				continue;
			}

			$results[] = array(
				'id'        => (int) $post->ID,
				'title'     => $this->to_plain_text( get_the_title( $post ) ),
				'url'       => (string) get_permalink( $post ),
				'excerpt'   => $this->to_plain_text( get_the_excerpt( $post ) ),
				'relevance' => $this->get_score( $query, $post ),
			);
		}

		if ( empty( $results ) && 0 === (int) $query->found_posts ) {
			$prose_error = $this->maybe_prose_query_error( $args['s'], $query );

			if ( is_wp_error( $prose_error ) ) {
				return $prose_error;
			}
		}

		return array(
			'results' => $results,
			'total'   => (int) $query->found_posts,
		);
	}

	/**
	 * Flag a zero-result search whose phrase looks like prose rather than keywords.
	 *
	 * Search terms are combined with AND, and a phrase over the term ceiling is matched
	 * literally, so a natural-language question reliably matches nothing on a site that holds
	 * plenty about its subject. Returning the empty result set alone would tell an agent the
	 * content does not exist, so say what happened instead.
	 *
	 * @since 4.5.0
	 *
	 * @param  string               $phrase Search phrase as supplied.
	 * @param  \Better_Search_Query $query  Executed search query.
	 * @return \WP_Error|null Error when the phrase is too long to match, otherwise null.
	 */
	private function maybe_prose_query_error( string $phrase, \Better_Search_Query $query ): ?\WP_Error {
		$terms = (array) ( $query->query_vars['search_terms'] ?? array() );
		$words = preg_split( '/\s+/u', trim( $phrase, " \t\n\r\0\x0B" ), -1, PREG_SPLIT_NO_EMPTY );
		$words = is_array( $words ) ? $words : array();

		/**
		 * Filters the number of search terms above which a zero-result search is reported as too long.
		 *
		 * @since 4.5.0
		 *
		 * @param int $max_terms Maximum search terms. Default 4.
		 */
		$max_terms = (int) apply_filters( 'bsearch_abilities_max_search_terms', 4 );

		/**
		 * Filters the number of words above which a zero-result search is reported as too long.
		 *
		 * @since 4.5.0
		 *
		 * @param int $max_words Maximum words. Default 9.
		 */
		$max_words = (int) apply_filters( 'bsearch_abilities_max_search_words', 9 );

		if ( count( $terms ) <= $max_terms && count( $words ) <= $max_words ) {
			return null;
		}

		return new \WP_Error(
			'bsearch_search_query_too_long',
			sprintf(
				/* translators: 1: number of search terms, 2: maximum number of keywords to use. */
				__( 'The search ran and matched nothing, but the phrase was too long to match reliably: it produced %1$d search terms, which are combined with AND. Search again with at most %2$d distinctive keywords instead of a sentence or question. A zero result here does not mean the site has no content on the subject.', 'better-search' ),
				count( $terms ),
				$max_terms
			),
			array(
				'status'       => 400,
				'search_terms' => array_values( $terms ),
			)
		);
	}

	/**
	 * Validate requested post types against the public, searchable post types.
	 *
	 * @since 4.5.0
	 *
	 * @param  mixed $post_types Requested post type names.
	 * @return array|\WP_Error Valid post type names or an error.
	 */
	private function sanitize_post_types( $post_types ) {
		$requested = array_filter( array_map( 'strval', (array) $post_types ) );

		if ( empty( $requested ) ) {
			return new \WP_Error(
				'bsearch_invalid_post_types',
				__( 'At least one post type is required when post_types is supplied.', 'better-search' ),
				array( 'status' => 400 )
			);
		}

		$allowed = get_post_types(
			array(
				'public'              => true,
				'exclude_from_search' => false,
			)
		);
		$invalid = array_values( array_diff( $requested, array_keys( $allowed ) ) );

		if ( ! empty( $invalid ) ) {
			return new \WP_Error(
				'bsearch_invalid_post_types',
				sprintf(
					/* translators: %s is a comma separated list of post type names. */
					__( 'These post types are not public and searchable: %s', 'better-search' ),
					implode( ', ', $invalid )
				),
				array( 'status' => 400 )
			);
		}

		return array_values( $requested );
	}

	/**
	 * Reduce rendered post text to plain text for machine-readable output.
	 *
	 * Titles and excerpts pass through theme and core filters that add markup and HTML entities,
	 * neither of which belong in a schema an agent consumes.
	 *
	 * @since 4.5.0
	 *
	 * @param  string $text Rendered text.
	 * @return string Plain text.
	 */
	private function to_plain_text( string $text ): string {
		$text = wp_strip_all_tags( $text );
		$text = html_entity_decode( $text, ENT_QUOTES | ENT_HTML5, get_bloginfo( 'charset' ) );

		return trim( $text, " \t\n\r\0\x0B" );
	}

	/**
	 * Get the raw relevance score recorded for a result.
	 *
	 * @since 4.5.0
	 *
	 * @param  \Better_Search_Query $query Executed search query.
	 * @param  \WP_Post             $post  Result post.
	 * @return float Relevance score.
	 */
	private function get_score( \Better_Search_Query $query, \WP_Post $post ): float {
		if ( isset( $query->post_scores[ $post->ID ] ) ) {
			return (float) $query->post_scores[ $post->ID ];
		}

		return isset( $post->score ) ? (float) $post->score : 0.0;
	}

	/**
	 * Whether a search result may be exposed to the current user.
	 *
	 * @since 4.5.0
	 *
	 * @param  \WP_Post $post Post to check.
	 * @return bool Whether the post is readable.
	 */
	private function is_readable_post( \WP_Post $post ): bool {
		$post_type = get_post_type_object( $post->post_type );

		if ( ! $post_type || ! is_post_type_viewable( $post_type ) ) {
			return false;
		}

		if ( is_post_publicly_viewable( $post ) ) {
			return true;
		}

		return current_user_can( 'read_post', $post->ID );
	}
}
