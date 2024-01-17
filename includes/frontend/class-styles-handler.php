<?php
/**
 * Functions dealing with styles.
 *
 * @package   Better_Search
 */

namespace WebberZone\Better_Search\Frontend;

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Admin Columns Class.
 *
 * @since 3.3.0
 */
class Styles_Handler {

	/**
	 * Constructor class.
	 *
	 * @since 3.3.0
	 */
	public function __construct() {
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'register_styles' ) );
	}

	/**
	 * Enqueue styles.
	 */
	public static function register_styles() {

		// Register bsearch-style as a placeholder to insert other styles.
		wp_register_style(
			'bsearch-style',
			plugins_url( 'includes/css/bsearch-styles.min.css', BETTER_SEARCH_PLUGIN_FILE ),
			array(),
			BETTER_SEARCH_VERSION
		);

		// Register bsearch-custom-style as a placeholder to insert custom styles.
		wp_register_style(
			'bsearch-custom-style',
			false,
			array(),
			BETTER_SEARCH_VERSION
		);

		// Add custom CSS to header.
		$custom_css = stripslashes( bsearch_get_option( 'custom_css' ) );
		if ( $custom_css ) {
			wp_add_inline_style( 'bsearch-custom-style', $custom_css );
		}

		if ( ! is_admin() && ( is_search() || is_singular() ) && bsearch_get_option( 'include_styles' ) ) {
			wp_enqueue_style( 'bsearch-style' );
			wp_enqueue_style( 'bsearch-custom-style' );
		}
	}
}
