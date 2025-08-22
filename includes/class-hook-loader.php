<?php
/**
 * Hook Loader class.
 *
 * Handles all hook registrations and callbacks for the plugin.
 *
 * @package WebberZone\Better_Search
 */

namespace WebberZone\Better_Search;

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
}
