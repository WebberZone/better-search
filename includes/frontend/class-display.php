<?php
/**
 * Display module
 *
 * @package Better_Search
 */

namespace WebberZone\Better_Search\Frontend;

use WebberZone\Better_Search\Util\Helpers;
use WebberZone\Better_Search\Util\Hook_Registry;

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Display Class.
 *
 * @since 3.3.0
 */
class Display {

	/**
	 * Constructor class.
	 *
	 * @since 3.3.0
	 */
	public function __construct() {
		Hook_Registry::add_filter( 'the_content', array( $this, 'content' ), 999 );
		Hook_Registry::add_filter( 'get_the_excerpt', array( $this, 'content' ), 999 );
		Hook_Registry::add_filter( 'the_title', array( $this, 'content' ), 999 );
		Hook_Registry::add_filter( 'the_bsearch_excerpt', array( $this, 'content' ), 999 );
		Hook_Registry::add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_highlight_script' ) );
	}

	/**
	 * Enqueue the client-side highlight script on singular pages.
	 *
	 * PHP-based highlighting relies on HTTP_REFERER being available during PHP
	 * execution. When a full-page cache (e.g. LiteSpeed Cache) serves a cached
	 * response, PHP never runs and the server-side highlighting is skipped. This
	 * script reads document.referrer in the browser and applies the same
	 * highlighting logic client-side, covering cached-page scenarios.
	 *
	 * Only enqueued on singular views where highlight_followed_links is enabled.
	 * Search results are handled server-side by content() via the_title /
	 * the_content filters; archives and the homepage have no followed-link
	 * scenario to highlight.
	 *
	 * @since 4.3.2
	 *
	 * @return void
	 */
	public static function enqueue_highlight_script() {
		if ( is_admin() || ! bsearch_get_option( 'highlight_followed_links' ) || ! is_singular() ) {
			return;
		}

		$minimize = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

		wp_enqueue_script(
			'bsearch-highlight',
			plugins_url( 'includes/js/better-search-highlight' . $minimize . '.js', BETTER_SEARCH_PLUGIN_FILE ),
			array(),
			BETTER_SEARCH_VERSION,
			true
		);

		/**
		 * Filters the HTML tag used to wrap highlighted search terms.
		 *
		 * Allowed values: mark, span, strong, em. Defaults to 'mark'.
		 *
		 * @since 4.3.2
		 *
		 * @param string $tag HTML tag name.
		 */
		$tag          = apply_filters( 'bsearch_highlight_tag', 'mark' );
		$allowed_tags = array( 'mark', 'span', 'strong', 'em' );
		$tag          = in_array( $tag, $allowed_tags, true ) ? $tag : 'mark';

		/**
		 * Filters the CSS class applied to each highlighted term wrapper.
		 *
		 * @since 4.3.2
		 *
		 * @param string $cls CSS class name.
		 */
		$cls = apply_filters( 'bsearch_highlight_class', 'bsearch_highlight' );
		$cls = sanitize_html_class( $cls );
		$cls = '' !== $cls ? $cls : 'bsearch_highlight';

		wp_localize_script(
			'bsearch-highlight',
			'bsearch_highlight',
			array(
				'site_url'  => preg_replace( '#^https?://#i', '', (string) get_option( 'home' ) ),
				'tag'       => $tag,
				'cls'       => $cls,
				/**
				 * Filters the maximum number of search terms passed to the JS highlighter.
				 *
				 * @since 4.3.2
				 *
				 * @param int $max_terms Maximum number of terms. Default 50.
				 */
				'max_terms' => (int) apply_filters( 'bsearch_highlight_max_terms', 50 ),
				/**
				 * Filters the CSS selector(s) used to scope JS highlighting.
				 *
				 * @since 4.3.2
				 *
				 * @param string $selectors A valid CSS selector string. Default targets standard WordPress content landmarks.
				 */
				'selectors' => apply_filters( 'bsearch_highlight_js_selectors', '.entry-content, .entry-title, .entry-summary' ),
			)
		);
	}

	/**
	 * Highlight search queries in the_content.
	 *
	 * @since 3.3.0
	 *
	 * @param string $content Post content.
	 *
	 * @return string Post Content
	 */
	public function content( $content ) {
		if ( is_admin() || ! in_the_loop() || ! is_search() ) {
			return $content;
		}

		if ( ! bsearch_get_option( 'highlight' ) ) {
			return $content;
		}

		$search_query = get_bsearch_query();

		if ( ! empty( $search_query ) ) {
			$keys = self::extract_highlight_terms( $search_query );
			if ( ! empty( $keys ) ) {
				$content = Helpers::highlight( $content, $keys );
			}
		}

		return $content;
	}

	/**
	 * Extract the terms to highlight from a search query, keeping quoted phrases intact.
	 *
	 * Double-quoted phrases are returned as single terms so the whole phrase is
	 * highlighted rather than its individual words. Terms prefixed with the
	 * exclusion operator (-) are skipped as they do not appear in the results.
	 *
	 * @since 4.3.2
	 *
	 * @param string $search_query Search query.
	 * @return string[] Terms and phrases to highlight.
	 */
	public static function extract_highlight_terms( $search_query ) {
		$search_query = wp_specialchars_decode( stripslashes( $search_query ), ENT_QUOTES );

		$keys = array();

		// Tokenize the query respecting double-quoted phrases. Same pattern as get_bsearch_terms().
		if ( ! preg_match_all( '/".*?("|$)|((?<=[\t ",+])|^)[^\t ",+]+/', $search_query, $matches ) ) {
			return $keys;
		}

		foreach ( $matches[0] as $token ) {
			$token = trim( $token );
			if ( '' === $token ) {
				continue;
			}

			// Quoted phrase: highlight the phrase as a whole.
			if ( '"' === $token[0] ) {
				$phrase = trim( $token, '" ' );
				if ( '' !== $phrase ) {
					$keys[] = $phrase;
				}
				continue;
			}

			// Excluded terms do not appear in the results.
			if ( '-' === $token[0] ) {
				continue;
			}

			// Strip boolean mode operators surrounding the term.
			$token = trim( $token, '+-~<>()*' );

			foreach ( preg_split( '/[\s\.]+/', $token ) as $word ) {
				if ( '' !== $word ) {
					$keys[] = $word;
				}
			}
		}

		return array_values( array_unique( $keys ) );
	}
}
