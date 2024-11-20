<?php
/**
 * Main plugin class.
 *
 * @package WebberZone\Better_Search
 */

namespace WebberZone\Better_Search;

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
		$this->language         = new Frontend\Language_Handler();
		$this->styles           = new Frontend\Styles_Handler();
		$this->tracker          = new Tracker();
		$this->shortcodes       = new Frontend\Shortcodes();
		$this->display          = new Frontend\Display();
		$this->live_search      = new Frontend\Live_Search();
		$this->template_handler = new Frontend\Template_Handler();
		$this->hooks();
		if ( ! function_exists( 'bsearch_freemius' ) ) {
			require_once __DIR__ . '/load-freemius.php';
		}
		if ( is_admin() ) {
			$this->admin = new Admin\Admin();
			if ( is_multisite() ) {
				$this->admin = new Admin\Network\Admin();
			}
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

		add_action( 'activated_plugin', array( $this, 'activated_plugin' ), 10, 2 );
		add_action( 'pre_current_active_plugins', array( $this, 'plugin_deactivated_notice' ) );
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
		register_widget( '\\WebberZone\\Better_Search\\Frontend\\Widgets\\Search_Box' );
		register_widget( '\\WebberZone\\Better_Search\\Frontend\\Widgets\\Search_Heatmap' );
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
				<p>
					<?php
					echo esc_html( $message );
					?>
				</p>
			</div>
			<?php
			delete_transient( 'bsearch_deactivated_notice_id' );
	}

	/**
	 * Display the pro upgrade banner.
	 *
	 * @since 4.0.0
	 *
	 * @param bool $donate Whether to show the donate banner.
	 */
	public static function pro_upgrade_banner( $donate = true ) {
		if ( ! bsearch_freemius()->is_paying() ) {
			?>
				<div id="pro-upgrade-banner">
					<div class="inside">
						<p><a href="https://webberzone.com/plugins/better-search/pro/" target="_blank"><img src="
						<?php
						echo esc_url( BETTER_SEARCH_PLUGIN_URL . 'includes/admin/images/better-search-pro-banner.png' );
						?>
			" alt="
			<?php
			esc_html_e( 'Better Search Pro - Coming soon. Sign up to find out more', 'better-search' );
			?>
			" width="300" height="300" style="max-width: 100%;" /></a></p>

						<?php
						if ( $donate ) {
							?>
											
							<p style="text-align:center;">
							<?php
							esc_html_e( 'OR' );
							?>
				</p>
							<p><a href="https://wzn.io/donate-bs" target="_blank"><img src="
							<?php
							echo esc_url( BETTER_SEARCH_PLUGIN_URL . 'includes/admin/images/support.webp' );
							?>
				" alt="
							<?php
							esc_html_e( 'Support the development - Send us a donation today.', 'better-search' );
							?>
				" width="300" height="169" style="max-width: 100%;" /></a></p>
							<?php
						}
						?>
					</div>
				</div>
			<?php
		}
	}
}
