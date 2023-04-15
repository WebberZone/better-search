<?php
/**
 * Better Search replaces the default WordPress search with a better search that gives contextual results sorted by relevance
 *
 * Better Search is a plugin that will replace the default WordPress search page
 * with highly relevant search results improving your visitors search experience.
 *
 * @package Better_Search
 * @author    Ajay D'Souza <me@ajaydsouza.com>
 * @license   GPL-2.0+
 * @link      https://webberzone.com
 * @copyright 2009-2023 Ajay D'Souza
 *
 * @wordpress-plugin
 * Plugin Name: Better Search
 * Plugin URI:  https://webberzone.com/plugins/better-search/
 * Description: Replace the default WordPress search with a contextual search. Search results are sorted by relevancy ensuring a better visitor search experience.
 * Version:     3.2.1
 * Author:      Ajay D'Souza
 * Author URI:  https://webberzone.com/
 * Text Domain: better-search
 * License:     GPL-2.0+
 * License URI: https://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path: /languages
 * GitHub Plugin URI: https://github.com/WebberZone/better-search/
 */

// If this file is called directly, then abort execution.
if ( ! defined( 'WPINC' ) ) {
	die( "Aren't you supposed to come here via WP-Admin?" );
}

/**
 * Holds the version of Contextual Related Posts.
 *
 * @since 2.9.3
 *
 * @var string Contextual Related Posts Version.
 */
if ( ! defined( 'BETTER_SEARCH_VERSION' ) ) {
	define( 'BETTER_SEARCH_VERSION', '3.2.0' );
}

/**
 * Holds the filesystem directory path (with trailing slash) for Better Search
 *
 * @since 2.2.0
 *
 * @var string Plugin folder path
 */
if ( ! defined( 'BETTER_SEARCH_PLUGIN_DIR' ) ) {
	define( 'BETTER_SEARCH_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
}

/**
 * Holds the filesystem directory path (with trailing slash) for Better Search
 *
 * @since 2.2.0
 *
 * @var string Plugin folder URL
 */
if ( ! defined( 'BETTER_SEARCH_PLUGIN_URL' ) ) {
	define( 'BETTER_SEARCH_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}

/**
 * Holds the filesystem directory path (with trailing slash) for Better Search
 *
 * @since 2.2.0
 *
 * @var string Plugin Root File
 */
if ( ! defined( 'BETTER_SEARCH_PLUGIN_FILE' ) ) {
	define( 'BETTER_SEARCH_PLUGIN_FILE', __FILE__ );
}

/**
 * Global variable holding the current database version of Better Search
 *
 * @since   1.0
 *
 * @var string
 */
global $bsearch_db_version;
$bsearch_db_version = '1.0';

/*
 * ----------------------------------------------------------------------------*
 * Include files
 *----------------------------------------------------------------------------
 */

	require_once BETTER_SEARCH_PLUGIN_DIR . 'includes/admin/register-settings.php';
	require_once BETTER_SEARCH_PLUGIN_DIR . 'includes/admin/default-settings.php';
	require_once BETTER_SEARCH_PLUGIN_DIR . 'includes/activation.php';
	require_once BETTER_SEARCH_PLUGIN_DIR . 'includes/class-better-search.php';
	require_once BETTER_SEARCH_PLUGIN_DIR . 'includes/class-better-search-query.php';
	require_once BETTER_SEARCH_PLUGIN_DIR . 'includes/main-functions.php';
	require_once BETTER_SEARCH_PLUGIN_DIR . 'includes/general-template.php';
	require_once BETTER_SEARCH_PLUGIN_DIR . 'includes/l10n.php';
	require_once BETTER_SEARCH_PLUGIN_DIR . 'includes/template-redirect.php';
	require_once BETTER_SEARCH_PLUGIN_DIR . 'includes/utilities.php';
	require_once BETTER_SEARCH_PLUGIN_DIR . 'includes/media.php';
	require_once BETTER_SEARCH_PLUGIN_DIR . 'includes/wp-filters.php';
	require_once BETTER_SEARCH_PLUGIN_DIR . 'includes/modules/tracker.php';
	require_once BETTER_SEARCH_PLUGIN_DIR . 'includes/modules/cache.php';
	require_once BETTER_SEARCH_PLUGIN_DIR . 'includes/modules/class-better-search-heatmap.php';
	require_once BETTER_SEARCH_PLUGIN_DIR . 'includes/modules/class-bsearch-search-box.php';
	require_once BETTER_SEARCH_PLUGIN_DIR . 'includes/modules/heatmap.php';
	require_once BETTER_SEARCH_PLUGIN_DIR . 'includes/modules/shortcode.php';
	require_once BETTER_SEARCH_PLUGIN_DIR . 'includes/deprecated.php';

/*
 *----------------------------------------------------------------------------*
 * Dashboard and Administrative Functionality
 *----------------------------------------------------------------------------
 */
if ( is_admin() ) {

	/**
	 *  Load the admin pages if we're in the Admin.
	 */
	require_once BETTER_SEARCH_PLUGIN_DIR . 'includes/admin/admin.php';
	require_once BETTER_SEARCH_PLUGIN_DIR . 'includes/admin/settings-page.php';
	require_once BETTER_SEARCH_PLUGIN_DIR . 'includes/admin/save-settings.php';
	require_once BETTER_SEARCH_PLUGIN_DIR . 'includes/admin/help-tab.php';
	require_once BETTER_SEARCH_PLUGIN_DIR . 'includes/admin/tools.php';
	require_once BETTER_SEARCH_PLUGIN_DIR . 'includes/admin/admin-dashboard.php';
	require_once BETTER_SEARCH_PLUGIN_DIR . 'includes/admin/class-better-search-statistics.php';
	require_once BETTER_SEARCH_PLUGIN_DIR . 'includes/admin/class-better-search-statistics-table.php';

}


/**
 * Declare $bsearch_settings global so that it can be accessed in every function
 *
 * @since   1.3
 */
global $bsearch_settings;
$bsearch_settings = bsearch_get_settings();


/**
 * Get Settings.
 *
 * Retrieves all plugin settings
 *
 * @since  2.2.0
 *
 * @return array Better Search settings
 */
function bsearch_get_settings() {

	$settings = get_option( 'bsearch_settings' );

	/**
	 * Settings array
	 *
	 * Retrieves all plugin settings
	 *
	 * @since 1.2.0
	 * @param array $settings Settings array
	 */
	return apply_filters( 'bsearch_get_settings', $settings );
}
