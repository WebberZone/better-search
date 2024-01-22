<?php
/**
 * Admin class.
 *
 * @since 3.3.0
 *
 * @package Better_Search
 */

namespace WebberZone\Better_Search\Admin;

use WebberZone\Better_Search\Util\Cache;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Class to register the Better Search Admin Area.
 *
 * @since 3.3.0
 */
class Admin {

	/**
	 * Admin Dashboard.
	 *
	 * @since 3.3.0
	 *
	 * @var object Admin Dashboard.
	 */
	public $admin_dashboard;

	/**
	 * Settings API.
	 *
	 * @since 3.3.0
	 *
	 * @var object Settings API.
	 */
	public $settings;

	/**
	 * Statistics table.
	 *
	 * @since 3.3.0
	 *
	 * @var object Statistics table.
	 */
	public $statistics;

	/**
	 * Activator class.
	 *
	 * @since 3.3.0
	 *
	 * @var object Activator class.
	 */
	public $activator;

	/**
	 * Upgrader class.
	 *
	 * @since 3.3.0
	 *
	 * @var object Upgrader class.
	 */
	public $upgrader;

	/**
	 * Admin Notices.
	 *
	 * @since 3.3.0
	 *
	 * @var object Admin Notices.
	 */
	public $admin_notices;

	/**
	 * Tools page.
	 *
	 * @since 3.3.0
	 *
	 * @var object Tools page.
	 */
	public $tools_page;

	/**
	 * Dashboard widgets.
	 *
	 * @since 3.3.0
	 *
	 * @var object Dashboard widgets.
	 */
	public $dashboard_widgets;

	/**
	 * Cache.
	 *
	 * @since 3.3.0
	 *
	 * @var object Cache.
	 */
	public $cache;

	/**
	 * Settings Page in Admin area.
	 *
	 * @since 3.3.0
	 *
	 * @var string Settings Page.
	 */
	public $settings_page;

	/**
	 * Prefix which is used for creating the unique filters and actions.
	 *
	 * @since 3.3.0
	 *
	 * @var string Prefix.
	 */
	public static $prefix;

	/**
	 * Settings Key.
	 *
	 * @since 3.3.0
	 *
	 * @var string Settings Key.
	 */
	public $settings_key;

	/**
	 * The slug name to refer to this menu by (should be unique for this menu).
	 *
	 * @since 3.3.0
	 *
	 * @var string Menu slug.
	 */
	public $menu_slug;

	/**
	 * Main constructor class.
	 *
	 * @since 3.3.0
	 */
	public function __construct() {
		$this->hooks();

		// Initialise admin classes.
		$this->admin_dashboard   = new Dashboard();
		$this->statistics        = new Statistics();
		$this->settings          = new Settings\Settings();
		$this->activator         = new Activator();
		$this->upgrader          = new Upgrader();
		$this->admin_notices     = new Admin_Notices();
		$this->tools_page        = new Tools_Page();
		$this->dashboard_widgets = new Dashboard_Widgets();
		$this->cache             = new Cache();
	}

	/**
	 * Run the hooks.
	 *
	 * @since 3.3.0
	 */
	public function hooks() {
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
	}

	/**
	 * Enqueue scripts in admin area.
	 *
	 * @since 3.0.0
	 */
	public function admin_enqueue_scripts() {

		// Register charj.js, luxon and chartjs-adapter-luxon.
		wp_register_script(
			'better-search-chartjs',
			BETTER_SEARCH_PLUGIN_URL . 'includes/admin/js/chart.min.js',
			array(),
			BETTER_SEARCH_VERSION,
			true
		);
		wp_register_script(
			'better-search-luxon',
			BETTER_SEARCH_PLUGIN_URL . 'includes/admin/js/luxon.min.js',
			array(),
			BETTER_SEARCH_VERSION,
			true
		);
		wp_register_script(
			'better-search-chartjs-adapter-luxon',
			BETTER_SEARCH_PLUGIN_URL . 'includes/admin/js/chartjs-adapter-luxon.min.js',
			array( 'better-search-chartjs', 'better-search-luxon' ),
			BETTER_SEARCH_VERSION,
			true
		);
		wp_register_script(
			'better-search-chartjs-plugin-datalabels',
			BETTER_SEARCH_PLUGIN_URL . 'includes/admin/js/chartjs-plugin-datalabels.min.js',
			array( 'better-search-chartjs' ),
			BETTER_SEARCH_VERSION,
			true
		);
		wp_register_script(
			'better-search-chart-data-js',
			BETTER_SEARCH_PLUGIN_URL . 'includes/admin/js/chart-data.min.js',
			array( 'jquery', 'better-search-chartjs', 'better-search-chartjs-adapter-luxon', 'better-search-luxon', 'better-search-chartjs-plugin-datalabels' ),
			BETTER_SEARCH_VERSION,
			true
		);

		wp_register_script(
			'better-search-admin-js',
			BETTER_SEARCH_PLUGIN_URL . 'includes/admin/js/admin-scripts.min.js',
			array( 'jquery', 'jquery-ui-tabs', 'jquery-ui-datepicker' ),
			BETTER_SEARCH_VERSION,
			true
		);
		wp_localize_script(
			'better-search-admin-js',
			'better_search_admin',
			array(
				'nonce' => wp_create_nonce( 'better_search_admin_nonce' ),
			)
		);
		wp_register_style(
			'better-search-admin-ui-css',
			BETTER_SEARCH_PLUGIN_URL . 'includes/admin/css/better-search-admin.min.css',
			array(),
			BETTER_SEARCH_VERSION
		);
	}

	/**
	 * Display admin sidebar.
	 *
	 * @since 3.3.0
	 */
	public static function display_admin_sidebar() {
		require_once BETTER_SEARCH_PLUGIN_DIR . 'includes/admin/settings/sidebar.php';
	}
}
