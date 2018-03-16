<?php
/**
 * Translation functions
 *
 * @package Better_Search
 */

/**
 * Function to load translation files.
 *
 * @since   1.3.3
 */
function bsearch_lang_init() {
	load_plugin_textdomain( 'better-search', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}
add_action( 'plugins_loaded', 'bsearch_lang_init' );

