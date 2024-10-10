<?php
/**
 * Main plugin class.
 *
 * @package WebberZone\Better_Search
 */

namespace WebberZone\Better_Search;

use Better_Search_Core_Query;
use WebberZone\Better_Search\Admin\Activator;

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
		$this->language    = new Frontend\Language_Handler();
		$this->styles      = new Frontend\Styles_Handler();
		$this->tracker     = new Tracker();
		$this->shortcodes  = new Frontend\Shortcodes();
		$this->display     = new Frontend\Display();
		$this->live_search = new Frontend\Live_Search();
		$this->pro         = new Pro\Pro();

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
		add_action( 'wp_head', array( $this, 'wp_head' ) );
		add_action( 'parse_query', array( $this, 'load_seamless_mode' ) );
		add_filter( 'template_include', array( $this, 'template_include' ) );
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
	 * Initialise the Better Search widgets.
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
		if ( $query->is_search() ) {
			if ( bsearch_get_option( 'seamless' ) || true === $query->get( 'better_search_query' ) ) {
				new \Better_Search_Core_Query( $query->query_vars );
			}
		}
	}

	/**
	 * Displays the search results
	 * First checks if the theme contains a search template and uses that
	 * If search template is missing, generates the results below
	 *
	 * @since 3.3.0
	 *
	 * @param string $template Search template to use.
	 */
	public function template_include( $template ) {
		// Early return if not a search page.
		$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
		if ( false === stripos( $request_uri, '?s=' )
			&& false === stripos( $request_uri, '/search/' )
			&& ! is_search() ) {
			return $template;
		}

		global $wp_query;

		// Early return if seamless integration mode is activated.
		if ( bsearch_get_option( 'seamless' ) ) {
			return $template;
		}

		// If we have a 404 status, set status of 404 to false.
		if ( $wp_query->is_404 ) {
			$wp_query->is_404     = false;
			$wp_query->is_archive = true;
		}

		// Change status code to 200 OK since /search/ returns status code 404.
		status_header( 200 );

		// Add necessary code to the head.
		add_action( 'wp_head', array( $this, 'wp_head' ) );

		// Set the title.
		add_filter( 'pre_get_document_title', array( $this, 'document_title' ) );

		// Check for a template file within the parent or child theme.
		$template_paths = array(
			get_stylesheet_directory() . '/better-search-template.php',
			get_template_directory() . '/better-search-template.php',
			plugin_dir_path( __DIR__ ) . 'templates/template.php',
		);

		foreach ( $template_paths as $template_path ) {
			if ( file_exists( $template_path ) ) {
				return $template_path;
			}
		}

		return $template;
	}

	/**
	 * Insert styles into WordPress Head. Filters `wp_head`.
	 *
	 * @since   1.0
	 */
	public static function wp_head() {

		if ( is_search() ) {
			// Add noindex to search results page.
			if ( bsearch_get_option( 'meta_noindex' ) ) {
				echo '<meta name="robots" content="noindex,follow" />';
			}
		}
	}


	/**
	 * Change page title. Filters `wp_title`.
	 *
	 * @since   1.0
	 *
	 * @param   string $title Title of the page.
	 * @return  string  Filtered title of the page
	 */
	public static function document_title( $title ) {

		if ( ! is_search() ) {
			return $title;
		}

		$search_query = get_bsearch_query();

		if ( $search_query ) {
			/* translators: 1: search query, 2: title of the page */
			$bsearch_title = sprintf( __( 'Search Results for "%1$s" | %2$s', 'better-search' ), $search_query, $title );

			/**
			 * Filters the title of the page
			 *
			 * @since   2.0.0
			 *
			 * @param   string  $bsearch_title  Title of the page set by Better Search
			 * @param   string  $title          Original Title of the page
			 * @param   string  $search_query   Search query
			 */
			return apply_filters( 'bsearch_title', $bsearch_title, $title, $search_query );
		}

		return $title;
	}

	/**
	 * Hook into WP_Query to check if better_search_query is set and true.
	 * If so, load the Better Search query.
	 *
	 * @since 3.5.0
	 *
	 * @param \WP_Query $query The WP_Query object.
	 */
	public function parse_query( $query ) {
		if ( true === $query->get( 'better_search_query' ) ) {
			// Load the Better Search query only if it's not already initialized.
			if ( ! isset( $query->query_vars['is_better_search_loaded'] ) || ! $query->query_vars['is_better_search_loaded'] ) {
				$query->set( 'is_better_search_loaded', true );
				new Better_Search_Core_Query( $query->query_vars );
			}
		}
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
	public function activated_plugin( $plugin, $network_wide ) {
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
	public function plugin_deactivated_notice() {
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
