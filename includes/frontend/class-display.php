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
	 * Indicates if the current content is the primary content.
	 *
	 * @since 4.0.2
	 * @var int
	 */
	private static $title_count = 0;

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
		if ( is_admin() || ! in_the_loop() ) {
			return $content;
		}
		$referer = wp_get_referer() ? urldecode( wp_get_referer() ) : '';
		if ( is_search() ) {
			$is_referer_search_engine = true;
		} else {
			// Compare without the scheme: the home option may be http while visitors arrive over https (or vice versa).
			$siteurl            = preg_replace( '#^https?://#i', '', (string) get_option( 'home' ) );
			$schemeless_referer = preg_replace( '#^https?://#i', '', $referer );
			if ( '' !== $siteurl && 0 === stripos( $schemeless_referer, $siteurl ) ) {
				parse_str( (string) wp_parse_url( $referer, PHP_URL_QUERY ), $queries );
				if ( ! empty( $queries['s'] ) || preg_match( '#/search/.*#i', $referer ) ) {
					$is_referer_search_engine = true;
				}
			}
		}

		if ( empty( $is_referer_search_engine ) ) {
			return $content;
		}

		if ( bsearch_get_option( 'highlight' ) && is_search() ) {
			$search_query = get_bsearch_query();
		} elseif ( bsearch_get_option( 'highlight_followed_links' ) ) {
			if ( preg_match( '/\/search\/([^\/\?]+)/i', $referer, $matches ) ) {
				$search_query = $matches[1];
			} else {
				$search_query = preg_replace( '/^.*s=([^&]+)&?.*$/i', '$1', $referer );
			}
			if ( current_filter() === 'the_title' ) {
				if ( self::$title_count > 0 ) {
					return $content;
				}
				++self::$title_count;
			}
		}

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
