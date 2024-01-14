<?php
/**
 * Main plugin class.
 *
 * @package WebberZone\Better_Search
 */

namespace WebberZone\Better_Search;

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
		$this->language   = new Frontend\Language_Handler();
		$this->styles     = new Frontend\Styles_Handler();
		$this->tracker    = new Tracker();
		$this->shortcodes = new Frontend\Shortcodes();

		$this->hooks();

		if ( is_admin() ) {
			$this->admin = new Admin\Admin();
		}
	}

	/**
	 * Run the hooks.
	 *
	 * @since 3.3.0
	 */
	public function hooks() {
		add_action( 'init', array( $this, 'initiate_plugin' ) );
		add_action( 'widgets_init', array( $this, 'register_widgets' ) );
		add_action( 'parse_query', array( $this, 'load_seamless_mode' ) );
	}

	/**
	 * Initialise the plugin translations and media.
	 *
	 * @since 3.3.0
	 */
	public function initiate_plugin() {
		Frontend\Media_Handler::add_image_sizes();
	}

	/**
	 * Initialise the Top 10 widgets.
	 *
	 * @since 3.3.0
	 */
	public function register_widgets() {
		register_widget( '\WebberZone\Better_Search\Frontend\Widgets\Search_Box' );
		register_widget( '\WebberZone\Better_Search\Frontend\Widgets\Search_Heatmap' );
	}

	/**
	 * Load seamless mode.
	 *
	 * @since 3.3.0
	 *
	 * @param \WP_Query $query Query object.
	 */
	public function load_seamless_mode( $query ) {
		if ( $query->is_search() && bsearch_get_option( 'seamless' ) ) {
			new \Better_Search();
		}
	}
}
