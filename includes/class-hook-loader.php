<?php
/**
 * Hook Loader class.
 *
 * Handles all hook registrations and callbacks for the plugin.
 *
 * @package WebberZone\Better_Search
 */

namespace WebberZone\Better_Search;

use WebberZone\Better_Search\Admin\Activator;
use WebberZone\Better_Search\Util\Hook_Registry;

if ( ! defined( 'WPINC' ) ) {
	exit;
}

/**
 * Hook Loader class.
 *
 * Centralizes all hook registrations and their callback implementations.
 *
 * @since 3.3.0
 */
final class Hook_Loader {

	/**
	 * Constructor.
	 *
	 * @since 3.3.0
	 */
	public function __construct() {
		$this->register_hooks();
	}

	/**
	 * Register all plugin hooks.
	 *
	 * @since 3.3.0
	 */
	private function register_hooks(): void {
		$this->register_init_hooks();
		$this->register_plugin_management_hooks();
	}

	/**
	 * Register initialization hooks.
	 *
	 * @since 3.3.0
	 */
	private function register_init_hooks(): void {
		Hook_Registry::add_action( 'init', array( $this, 'initiate_plugin' ) );
		Hook_Registry::add_action( 'widgets_init', array( $this, 'register_widgets' ) );
	}

	/**
	 * Register plugin management hooks.
	 *
	 * @since 3.3.0
	 */
	private function register_plugin_management_hooks(): void {
		Hook_Registry::add_action( 'activated_plugin', array( $this, 'activated_plugin' ), 10, 2 );
		Hook_Registry::add_action( 'pre_current_active_plugins', array( $this, 'plugin_deactivated_notice' ) );
	}

	/**
	 * Initialise the plugin translations and media.
	 *
	 * @since 3.3.0
	 */
	public function initiate_plugin(): void {
		Frontend\Media_Handler::add_image_sizes();
	}

	/**
	 * Initialise the Better Search widgets.
	 *
	 * @since 3.3.0
	 */
	public function register_widgets(): void {
		register_widget( '\WebberZone\Better_Search\Frontend\Widgets\Search_Box' );
		register_widget( '\WebberZone\Better_Search\Frontend\Widgets\Search_Heatmap' );
	}

	/**
	 * Checks if another version of Better Search/Better Search Pro is active and deactivates it.
	 * Hooked on `activated_plugin` so other plugin is deactivated when current plugin is activated.
	 *
	 * @since 3.5.0
	 *
	 * @param string $plugin        The plugin being activated.
	 * @param bool   $network_wide  Whether the plugin is being activated network-wide.
	 */
	public function activated_plugin( string $plugin, bool $network_wide ): void {
		if ( ! in_array( $plugin, array( 'better-search/better-search.php', 'better-search-pro/better-search.php' ), true ) ) {
			return;
		}

		Activator::activation_hook( $network_wide );

		$plugin_to_deactivate  = 'better-search/better-search.php';
		$deactivated_notice_id = '1';

		// If we just activated the free version, deactivate the pro version.
		if ( $plugin === $plugin_to_deactivate ) {
			$plugin_to_deactivate  = 'better-search-pro/better-search.php';
			$deactivated_notice_id = '2';
		}

		if ( is_multisite() && is_network_admin() ) {
			$active_plugins = (array) get_site_option( 'active_sitewide_plugins', array() );
			$active_plugins = array_keys( $active_plugins );
		} else {
			$active_plugins = (array) get_option( 'active_plugins', array() );
		}

		foreach ( $active_plugins as $plugin_basename ) {
			if ( $plugin_to_deactivate === $plugin_basename ) {
				set_transient( 'bsearch_deactivated_notice_id', $deactivated_notice_id, 1 * HOUR_IN_SECONDS );
				deactivate_plugins( $plugin_basename );
				return;
			}
		}
	}

	/**
	 * Displays a notice when either Better Search or Better Search Pro is automatically deactivated.
	 *
	 * @since 3.5.0
	 */
	public function plugin_deactivated_notice(): void {
		$deactivated_notice_id = (int) get_transient( 'bsearch_deactivated_notice_id' );
		if ( ! in_array( $deactivated_notice_id, array( 1, 2 ), true ) ) {
			return;
		}

		$message = __( "Better Search and Better Search Pro should not be active at the same time. We've automatically deactivated Better Search.", 'better-search' );
		if ( 2 === $deactivated_notice_id ) {
			$message = __( "Better Search and Better Search Pro should not be active at the same time. We've automatically deactivated Better Search Pro.", 'better-search' );
		}

		?>
			<div class="updated" style="border-left: 4px solid #ffba00;">
				<p><?php echo esc_html( $message ); ?></p>
			</div>
			<?php

			delete_transient( 'bsearch_deactivated_notice_id' );
	}
}
