<?php
/**
 * Language handler
 *
 * @package Better_Search
 */

namespace WebberZone\Better_Search\Frontend;

use WebberZone\Better_Search\Util\Hook_Registry;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Language handler class.
 *
 * @since 3.3.0
 */
class Language_Handler {

	/**
	 * Constructor.
	 *
	 * @since 3.3.0
	 */
	public function __construct() {
		Hook_Registry::add_action( 'init', array( $this, 'load_plugin_textdomain' ) );
		Hook_Registry::add_filter( 'better_search_query_the_posts', array( $this, 'translate_ids' ), 999 );
	}

	/**
	 * Initialises text domain for l10n.
	 *
	 * @since 3.3.0
	 *
	 * @return void
	 */
	public static function load_plugin_textdomain() {
		load_plugin_textdomain( 'better-search', false, dirname( plugin_basename( BETTER_SEARCH_PLUGIN_FILE ) ) . '/languages/' );
	}

	/**
	 * Get the ID of a post in the current language. Works with WPML and PolyLang.
	 *
	 * @since 3.3.0
	 *
	 * @param int[] $results Arry of Posts.
	 * @return \WP_Post[] Updated array of WP_Post objects.
	 */
	public static function translate_ids( $results ) {
		$processed_ids     = array();
		$processed_results = array();

		foreach ( $results as $result ) {

			$result = self::object_id_cur_lang( $result );
			if ( ! $result ) {
				continue;
			}

			// If this is NULL or already processed ID or matches current post then skip processing this loop.
			if ( ! $result->ID || in_array( $result->ID, $processed_ids ) ) { // phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
				continue;
			}

			// Push the current ID into the array to ensure we're not repeating it.
			array_push( $processed_ids, $result->ID );

			// Let's get the Post using the ID.
			$result = get_post( $result );
			array_push( $processed_results, $result );
		}
		return $processed_results;
	}

	/**
	 * Returns the object identifier for the current language (WPML).
	 *
	 * @since 3.3.0
	 *
	 * @param int|string|\WP_Post $post Post object or Post ID.
	 * @return \WP_Post|array|null Post opbject, updated if needed.
	 */
	public static function object_id_cur_lang( $post ) {

		$return_original_if_missing = false;

		$post         = get_post( $post );
		$current_lang = apply_filters( 'wpml_current_language', null );

		// Polylang implementation.
		if ( function_exists( 'pll_get_post' ) ) {
			$translated_post = \pll_get_post( $post->ID );
			if ( $translated_post ) {
				$post = get_post( $translated_post );
			}
		}

		// WPML implementation.
		if ( class_exists( 'SitePress' ) ) {
			/**
			 * Filter to modify if the original language ID is returned.
			 *
			 * @since 2.2.3
			 *
			 * @param bool $return_original_if_missing Flag to return original post ID if translated post ID is missing.
			 * @param int  $id                         Post ID
			 */
			$return_original_if_missing = apply_filters( 'bsearch_wpml_return_original', $return_original_if_missing, $post->ID );

			$translated_post = apply_filters( 'wpml_object_id', $post->ID, $post->post_type, $return_original_if_missing, $current_lang );
			if ( $translated_post ) {
				$post = get_post( $translated_post );
			}
		}

		/**
		 * Filters Post object for current language.
		 *
		 * @since 3.3.0
		 *
		 * @param \WP_Post|array|null $id Post object.
		 */
		return apply_filters( 'bsearch_object_id_cur_lang', $post );
	}

	/**
	 * Whether TranslatePress is active and exposes the API this integration needs.
	 *
	 * @since 4.4.5
	 *
	 * @return bool True if TranslatePress can be used.
	 */
	public static function is_translatepress_active(): bool {
		return function_exists( 'trp_translate' ) && class_exists( 'TRP_Translate_Press' );
	}

	/**
	 * Get the TranslatePress settings array.
	 *
	 * @since 4.4.5
	 *
	 * @return array TranslatePress settings.
	 */
	public static function get_trp_settings(): array {
		$settings = get_option( 'trp_settings', array() );

		return is_array( $settings ) ? $settings : array();
	}

	/**
	 * Get the TranslatePress language the current front-end request is rendering in.
	 *
	 * @since 4.4.5
	 *
	 * @return string Language code, or an empty string when TranslatePress is inactive
	 *                or the request is in the default language.
	 */
	public static function get_trp_current_language(): string {
		if ( ! self::is_translatepress_active() ) {
			return '';
		}

		global $TRP_LANGUAGE; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.VariableNotSnakeCase -- TranslatePress global.

		$language = is_string( $TRP_LANGUAGE ) ? $TRP_LANGUAGE : ''; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.VariableNotSnakeCase -- TranslatePress global.
		$settings = self::get_trp_settings();
		$default  = isset( $settings['default-language'] ) ? (string) $settings['default-language'] : '';

		return ( '' === $language || $language === $default ) ? '' : $language;
	}

	/**
	 * Fetch a TranslatePress component instance.
	 *
	 * @since 4.4.5
	 *
	 * @param string $component Component name, e.g. `url_converter`.
	 * @return object|null Component instance or null when unavailable.
	 */
	public static function get_trp_component( string $component ) {
		if ( ! self::is_translatepress_active() ) {
			return null;
		}

		$trp = \TRP_Translate_Press::get_trp_instance();
		if ( ! is_object( $trp ) || ! method_exists( $trp, 'get_component' ) ) {
			return null;
		}

		$instance = $trp->get_component( $component );

		return is_object( $instance ) ? $instance : null;
	}

	/**
	 * Get the referring URL, but only when it points at this site.
	 *
	 * @since 4.4.5
	 *
	 * @return string Referring URL on this host, or an empty string.
	 */
	protected static function get_same_origin_referer(): string {
		$referer = function_exists( 'wp_get_raw_referer' ) ? wp_get_raw_referer() : '';

		if ( ! is_string( $referer ) || '' === $referer ) {
			return '';
		}

		$referer_host = wp_parse_url( $referer, PHP_URL_HOST );
		$home_host    = wp_parse_url( home_url(), PHP_URL_HOST );

		if ( empty( $referer_host ) || empty( $home_host ) || strtolower( (string) $referer_host ) !== strtolower( (string) $home_host ) ) {
			return '';
		}

		return $referer;
	}

	/**
	 * Resolve the TranslatePress language for an admin-ajax request.
	 *
	 * @since 4.4.5
	 *
	 * @return string Language code, or an empty string when no translation is needed.
	 */
	public static function get_trp_ajax_language(): string {
		if ( ! self::is_translatepress_active() ) {
			return '';
		}

		$settings  = self::get_trp_settings();
		$default   = isset( $settings['default-language'] ) ? (string) $settings['default-language'] : '';
		$available = isset( $settings['publish-languages'] ) ? (array) $settings['publish-languages'] : array();

		if ( current_user_can( (string) apply_filters( 'trp_translating_capability', 'manage_options' ) ) ) {
			$available = array_merge( $available, isset( $settings['translation-languages'] ) ? (array) $settings['translation-languages'] : array() );
		}
		$available = array_values( array_unique( array_map( 'strval', $available ) ) );

		$raw_language = isset( $_POST['lang'] ) && is_string( $_POST['lang'] ) ? sanitize_text_field( wp_unslash( $_POST['lang'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Language is a read-only transport value.
		$language     = $raw_language;
		$url_slugs    = isset( $settings['url-slugs'] ) && is_array( $settings['url-slugs'] ) ? $settings['url-slugs'] : array();
		foreach ( $url_slugs as $locale => $slug ) {
			if ( is_string( $slug ) && $slug === $language ) {
				$language = (string) $locale;
				break;
			}
		}

		if ( '' === $language ) {
			$url_converter = self::get_trp_component( 'url_converter' );
			if ( $url_converter && method_exists( $url_converter, 'get_lang_from_url_string' ) ) {
				$referer = self::get_same_origin_referer();
				if ( '' !== $referer ) {
					$language = (string) $url_converter->get_lang_from_url_string( $referer );
				}
			}
		}

		$language = (string) apply_filters( 'bsearch_trp_ajax_language', $language );

		if ( '' === $language || $language === $default || ! in_array( $language, $available, true ) ) {
			return '';
		}

		return $language;
	}

	/**
	 * Translate a string with TranslatePress.
	 *
	 * @since 4.4.5
	 *
	 * @param string $content  Content in the default language.
	 * @param string $language Target language code.
	 * @return string Translated content.
	 */
	public static function trp_translate_content( string $content, string $language ): string {
		if ( '' === $content || '' === $language || ! self::is_translatepress_active() ) {
			return $content;
		}

		return (string) \trp_translate( $content, $language, false );
	}

	/**
	 * Convert a URL to its TranslatePress equivalent in the given language.
	 *
	 * @since 4.4.5
	 *
	 * @param string $url      URL in the default language.
	 * @param string $language Target language code.
	 * @return string Converted URL.
	 */
	public static function trp_translate_url( string $url, string $language ): string {
		if ( '' === $url || '' === $language ) {
			return $url;
		}

		$url_converter = self::get_trp_component( 'url_converter' );
		if ( ! $url_converter || ! method_exists( $url_converter, 'get_url_for_language' ) ) {
			return $url;
		}

		$converted = $url_converter->get_url_for_language( $language, $url, '' );

		return is_string( $converted ) && '' !== $converted ? $converted : $url;
	}

	/**
	 * Get a language identifier for cache keys.
	 *
	 * Rendered output is language-specific — TranslatePress filters `home_url()`, and
	 * WPML/Polylang resolve different post IDs — so cached HTML and post lists must not
	 * be shared between languages.
	 *
	 * @since 4.4.5
	 *
	 * @return string Current language code, or an empty string when the site is monolingual.
	 */
	public static function get_cache_language(): string {
		$language = self::get_trp_current_language();

		if ( '' === $language && function_exists( 'pll_current_language' ) ) {
			$language = (string) \pll_current_language( 'locale' );
		}

		if ( '' === $language && class_exists( 'SitePress' ) ) {
			$language = (string) apply_filters( 'wpml_current_language', null );
		}

		/**
		 * Filters the language component added to Better Search cache keys.
		 *
		 * @since 4.4.5
		 *
		 * @param string $language Current language code, or an empty string.
		 */
		return (string) apply_filters( 'bsearch_cache_language', $language );
	}
}
