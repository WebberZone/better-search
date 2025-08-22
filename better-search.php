<?php
/**
 * Better Search is a plugin that will replace the default WordPress search page
 * with highly relevant search results improving your visitors search experience.
 *
 * @package   Better_Search
 * @author    Ajay D'Souza
 * @license   GPL-2.0+
 * @link      https://webberzone.com
 * @copyright 2009-2025 Ajay D'Souza
 *
 * @wordpress-plugin
 * Plugin Name: Better Search
 * Plugin URI:  https://webberzone.com/plugins/better-search/
 * Description: Replace the default WordPress search with a contextual search. Search results are sorted by relevancy ensuring a better visitor search experience.
 * Version:     4.2.0
 * Author:      WebberZone
 * Author URI:  https://webberzone.com/
 * Text Domain: better-search
 * License:     GPL-2.0+
 * License URI: https://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path: /languages
 */

namespace WebberZone\Better_Search;

if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! defined( 'BETTER_SEARCH_VERSION' ) ) {
	/**
	 * Holds the version of Better Search.
	 *
	 * @since 2.9.3
	 */
	define( 'BETTER_SEARCH_VERSION', '4.2.0' );
}

if ( ! defined( 'BETTER_SEARCH_PLUGIN_DIR' ) ) {
	/**
	 * Holds the filesystem directory path (with trailing slash) for Better Search
	 *
	 * @since 2.2.0
	 */
	define( 'BETTER_SEARCH_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'BETTER_SEARCH_PLUGIN_URL' ) ) {
	/**
	 * Holds the filesystem directory path (with trailing slash) for Better Search
	 *
	 * @since 2.2.0
	 */
	define( 'BETTER_SEARCH_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}

if ( ! defined( 'BETTER_SEARCH_PLUGIN_FILE' ) ) {
	/**
	 * Holds the filesystem directory path (with trailing slash) for Better Search
	 *
	 * @since 2.2.0
	 */
	define( 'BETTER_SEARCH_PLUGIN_FILE', __FILE__ );
}

if ( ! defined( 'BETTER_SEARCH_DB_VERSION' ) ) {
	/**
	 * Holds the version of Better Search.
	 *
	 * @since 3.3.0
	 */
	define( 'BETTER_SEARCH_DB_VERSION', '2.0' );
}

if ( ! function_exists( __NAMESPACE__ . '\bsearch_deactivate_other_instances' ) ) {
	/**
	 * Deactivate other instances of Better Search when this plugin is activated.
	 *
	 * @param string $plugin The plugin being activated.
	 * @param bool   $network_wide Whether the plugin is being activated network-wide.
	 */
	function bsearch_deactivate_other_instances( $plugin, $network_wide = false ) {
		$free_plugin = 'better-search/better-search.php';
		$pro_plugin  = 'better-search-pro/better-search.php';

		// Only proceed if one of our plugins is being activated.
		if ( ! in_array( $plugin, array( $free_plugin, $pro_plugin ), true ) ) {
			return;
		}

		$plugins_to_deactivate = array();
		$deactivated_plugin    = '';

		// If pro is being activated, deactivate free.
		if ( $pro_plugin === $plugin ) {
			if ( is_plugin_active( $free_plugin ) || ( $network_wide && is_plugin_active_for_network( $free_plugin ) ) ) {
				$plugins_to_deactivate[] = $free_plugin;
				$deactivated_plugin      = 'Better Search';
			}
		}

		// If free is being activated, deactivate pro.
		if ( $free_plugin === $plugin ) {
			if ( is_plugin_active( $pro_plugin ) || ( $network_wide && is_plugin_active_for_network( $pro_plugin ) ) ) {
				$plugins_to_deactivate[] = $pro_plugin;
				$deactivated_plugin      = 'Better Search Pro';
			}
		}

		if ( ! empty( $plugins_to_deactivate ) ) {
			deactivate_plugins( $plugins_to_deactivate, false, $network_wide );
			set_transient( 'bsearch_deactivated_notice', $deactivated_plugin, 1 * HOUR_IN_SECONDS );
		}
	}
	add_action( 'activated_plugin', __NAMESPACE__ . '\bsearch_deactivate_other_instances', 10, 2 );
}

// Show admin notice about automatic deactivation.
if ( ! has_action( 'admin_notices', __NAMESPACE__ . '\bsearch_show_deactivation_notice' ) ) {
	add_action(
		'admin_notices',
		function () {
			$deactivated_plugin = get_transient( 'bsearch_deactivated_notice' );
			if ( $deactivated_plugin ) {
				/* translators: %s: Name of the deactivated plugin */
				$message = sprintf( __( "Better Search and Better Search PRO should not be active at the same time. We've automatically deactivated %s.", 'better-search' ), $deactivated_plugin );
				?>
			<div class="updated" style="border-left: 4px solid #ffba00;">
				<p><?php echo esc_html( $message ); ?></p>
			</div>
				<?php
				delete_transient( 'bsearch_deactivated_notice' );
			}
		}
	);
}

if ( ! function_exists( __NAMESPACE__ . '\bsearch_freemius' ) ) {
	// Finally load Freemius integration.
	require_once BETTER_SEARCH_PLUGIN_DIR . 'load-freemius.php';
}

// Load custom autoloader.
if ( ! function_exists( __NAMESPACE__ . '\autoload' ) ) {
	require_once BETTER_SEARCH_PLUGIN_DIR . 'includes/autoloader.php';
}

if ( ! function_exists( __NAMESPACE__ . '\better_search' ) ) {
	/**
	 * Returns the main instance of Better_Search to prevent the need to use globals.
	 *
	 * @since 4.0.6
	 *
	 * @return Main Main instance of the plugin.
	 */
	function better_search() {
		return Main::get_instance();
	}
}

if ( ! function_exists( __NAMESPACE__ . '\load' ) ) {
	/**
	 * The main function responsible for returning the one true WebberZone Better Search instance to functions everywhere.
	 *
	 * @since 3.3.0
	 */
	function load(): void {
		better_search();
	}
	add_action( 'plugins_loaded', __NAMESPACE__ . '\load' );
}

/*
 *----------------------------------------------------------------------------
 * Include files
 *----------------------------------------------------------------------------
 */
if ( ! function_exists( 'bsearch_get_settings' ) ) {
	require_once BETTER_SEARCH_PLUGIN_DIR . 'includes/options-api.php';
	require_once BETTER_SEARCH_PLUGIN_DIR . 'includes/class-better-search-core-query.php';
	require_once BETTER_SEARCH_PLUGIN_DIR . 'includes/class-better-search-query.php';
	require_once BETTER_SEARCH_PLUGIN_DIR . 'includes/functions.php';
	require_once BETTER_SEARCH_PLUGIN_DIR . 'includes/general-template.php';
	require_once BETTER_SEARCH_PLUGIN_DIR . 'includes/heatmap.php';
}

// Register activation hook.
register_activation_hook( __FILE__, __NAMESPACE__ . '\Admin\Activator::activation_hook' );

/**
 * Declare $bsearch_settings global so that it can be accessed in every function
 *
 * @since 1.3
 */
global $bsearch_settings;
$bsearch_settings = bsearch_get_settings();
