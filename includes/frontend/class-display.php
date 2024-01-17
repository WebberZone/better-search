<?php
/**
 * Display module
 *
 * @package Better_Search
 */

namespace WebberZone\Better_Search\Frontend;

use WebberZone\Better_Search\Util\Helpers;

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
		add_filter( 'the_content', array( $this, 'content' ), 999 );
		add_filter( 'get_the_excerpt', array( $this, 'content' ), 999 );
		add_filter( 'the_title', array( $this, 'content' ), 999 );
		add_filter( 'the_bsearch_excerpt', array( $this, 'content' ), 999 );
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
			$siteurl = get_option( 'home' );
			if ( preg_match( "#^$siteurl#i", $referer ) ) {
				parse_str( (string) wp_parse_url( $referer, PHP_URL_QUERY ), $queries );
				if ( ! empty( $queries['s'] ) ) {
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
			$search_query = preg_replace( '/^.*s=([^&]+)&?.*$/i', '$1', $referer );
			$search_query = preg_replace( '/\'|"/', '', $search_query );
		}

		if ( ! empty( $search_query ) ) {
			$search_query = str_replace( array( "'", '"', '&quot;', '\+', '\-' ), '', $search_query );
			$keys         = preg_split( '/[\s,\+\.]+/', $search_query );
			$content      = Helpers::highlight( $content, $keys );
		}

		return $content;
	}
}
