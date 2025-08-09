<?php
/**
 * Main plugin class.
 *
 * @package WebberZone\Better_Search
 */

namespace WebberZone\Better_Search;

use WebberZone\Better_Search\Util\Hook_Registry;

if ( ! defined( 'WPINC' ) ) {
	exit;
}
/**
 * Main plugin class.
 *
 * @since 3.3.0
 */
final class Main {
	/**
	 * The single instance of the class.
	 *
	 * @var Main
	 */
	private static $instance;

	/**
	 * Admin.
	 *
	 * @since 3.3.0
	 *
	 * @var object Admin.
	 */
	public $admin;

	/**
	 * Network Admin class object.
	 *
	 * @since 4.2.0
	 *
	 * @var object Network Admin class object.
	 */
	public $network_admin;

	/**
	 * Shortcodes.
	 *
	 * @since 3.3.0
	 *
	 * @var object Shortcodes.
	 */
	public $shortcodes;

	/**
	 * Tracker.
	 *
	 * @since 3.3.0
	 *
	 * @var object Tracker.
	 */
	public $tracker;

	/**
	 * Styles.
	 *
	 * @since 3.3.0
	 *
	 * @var object Styles.
	 */
	public $styles;

	/**
	 * Language Handler.
	 *
	 * @since 3.3.0
	 *
	 * @var object Language Handler.
	 */
	public $language;

	/**
	 * Display.
	 *
	 * @since 3.3.0
	 *
	 * @var object Display.
	 */
	public $display;

	/**
	 * Live Search.
	 *
	 * @since 4.0.0
	 *
	 * @var object Live Search.
	 */
	public $live_search;

	/**
	 * Template Handler.
	 *
	 * @since 4.0.0
	 *
	 * @var object Template Handler.
	 */
	public $template_handler;

	/**
	 * Pro.
	 *
	 * @since 4.0.0
	 *
	 * @var object Pro.
	 */
	public $pro;

	/**
	 * Gets the instance of the class.
	 *
	 * @since 3.3.0
	 *
	 * @return Main
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
			self::$instance->init();
		}
		return self::$instance;
	}

	/**
	 * A dummy constructor.
	 *
	 * @since 3.3.0
	 */
	private function __construct() {
		// Do nothing.
	}

	/**
	 * Initializes the plugin.
	 *
	 * @since 3.3.0
	 */
	private function init() {
		// Initialize components.
		$this->language         = new Frontend\Language_Handler();
		$this->styles           = new Frontend\Styles_Handler();
		$this->tracker          = new Tracker();
		$this->shortcodes       = new Frontend\Shortcodes();
		$this->display          = new Frontend\Display();
		$this->live_search      = new Frontend\Live_Search();
		$this->template_handler = new Frontend\Template_Handler();

		// Load all hooks.
		new Hook_Loader();

		// Initialize pro features.
		if ( bsearch_freemius()->is__premium_only() ) {
			if ( bsearch_freemius()->can_use_premium_code() ) {
				$this->pro = new Pro\Pro();
			}
		}

		// Initialize admin.
		if ( is_admin() ) {
			$this->admin = new Admin\Admin();

			if ( is_multisite() ) {
				$this->network_admin = new Admin\Network\Admin();
			}
		}
	}
}
