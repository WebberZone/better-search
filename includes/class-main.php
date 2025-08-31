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
	private static ?self $instance = null;

	/**
	 * Admin.
	 *
	 * @since 3.3.0
	 *
	 * @var Admin\Admin|null
	 */
	public ?Admin\Admin $admin = null;

	/**
	 * Network Admin class object.
	 *
	 * @since 4.2.0
	 *
	 * @var Admin\Network\Admin|null
	 */
	public ?Admin\Network\Admin $network_admin = null;

	/**
	 * Shortcodes.
	 *
	 * @since 3.3.0
	 *
	 * @var Frontend\Shortcodes
	 */
	public Frontend\Shortcodes $shortcodes;

	/**
	 * Tracker.
	 *
	 * @since 3.3.0
	 *
	 * @var Tracker
	 */
	public Tracker $tracker;

	/**
	 * Styles.
	 *
	 * @since 3.3.0
	 *
	 * @var Frontend\Styles_Handler
	 */
	public Frontend\Styles_Handler $styles;

	/**
	 * Language Handler.
	 *
	 * @since 3.3.0
	 *
	 * @var Frontend\Language_Handler
	 */
	public Frontend\Language_Handler $language;

	/**
	 * Display.
	 *
	 * @since 3.3.0
	 *
	 * @var Frontend\Display
	 */
	public Frontend\Display $display;

	/**
	 * Live Search.
	 *
	 * @since 4.0.0
	 *
	 * @var Frontend\Live_Search
	 */
	public Frontend\Live_Search $live_search;

	/**
	 * Template Handler.
	 *
	 * @since 4.0.0
	 *
	 * @var Frontend\Template_Handler
	 */
	public Frontend\Template_Handler $template_handler;

	/**
	 * Pro modules.
	 *
	 * @since 4.0.0
	 *
	 * @var Pro\Pro|null
	 */
	public ?Pro\Pro $pro = null;

	/**
	 * Gets the instance of the class.
	 *
	 * @since 3.3.0
	 *
	 * @return Main
	 */
	public static function get_instance(): self {
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
	private function init(): void {
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
		// Initialize admin on init action to ensure translations are loaded.
		Hook_Registry::add_action( 'init', array( $this, 'init_admin' ) );
	}

	/**
	 * Initialize admin components.
	 *
	 * @since 4.2.0
	 */
	public function init_admin(): void {
		if ( is_admin() ) {
			$this->admin = new Admin\Admin();
			if ( is_multisite() ) {
				$this->network_admin = new Admin\Network\Admin();
			}
		}
	}
}
