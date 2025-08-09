<?php
/**
 * Generates the Tools page for the network.
 *
 * @since 4.2.0
 *
 * @package WebberZone\Better_Search\Admin\Network
 */

namespace WebberZone\Better_Search\Admin\Network;

use WebberZone\Better_Search\Admin\Admin;
use WebberZone\Better_Search\Util\Hook_Registry;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Tools page class.
 *
 * @since 4.2.0
 */
class Tools_Page {

	/**
	 * Parent Menu ID.
	 *
	 * @since 4.2.0
	 *
	 * @var string Parent Menu ID.
	 */
	public $parent_id;

	/**
	 * Constructor.
	 */
	public function __construct() {
		Hook_Registry::add_action( 'network_admin_menu', array( $this, 'network_admin_menu' ), 11 );
		Hook_Registry::add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
	}

	/**
	 * Network Admin Menu.
	 *
	 * @since 4.2.0
	 */
	public function network_admin_menu() {
		$this->parent_id = add_submenu_page(
			'bsearch_dashboard',
			esc_html__( 'Better Search Multisite Tools', 'better-search' ),
			esc_html__( 'Tools', 'better-search' ),
			'manage_network_options',
			'bsearch_tools_page',
			array( $this, 'render_page' )
		);
	}

	/**
	 * Render the tools settings page.
	 *
	 * @since 4.2.0
	 *
	 * @return void
	 */
	public static function render_page() {

		ob_start();
		?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Better Search Pro Multisite Tools', 'better-search' ); ?></h1>
		<?php do_action( 'bsearch_tools_network_page_header' ); ?>

		<p><?php esc_html_e( 'This page allows you to run tools for Better Search on your multisite network.', 'better-search' ); ?></p>

		<?php settings_errors(); ?>

		<div id="poststuff">
		<div id="post-body" class="metabox-holder columns-2">
		<div id="post-body-content">

			<?php
				Admin::pro_upgrade_banner(
					false,
					sprintf(
						/* translators: 1: link to Network Plugins page, 2: link to account page */
						__( 'If you are running Better Search Pro and see the upgrade banner instead of the settings, you may need to activate your license. Go to the %1$s, locate Better Search Pro, and activate your license from there. View your %2$s to check the status of your license.', 'better-search' ),
						'<a href="' . esc_url( network_admin_url( 'plugins.php' ) ) . '" target="_blank">' . esc_html__( 'Network Plugins page', 'better-search' ) . '</a>',
						'<a href="' . esc_url( \WebberZone\Better_Search\bsearch_freemius()->get_account_url() ) . '" target="_blank">' . esc_html__( 'account page', 'better-search' ) . '</a>'
					)
				);
			?>

			<?php
			/**
			 * Action hook to add additional tools page content.
			 *
			 * @since 4.2.0
			 */
			do_action( 'bsearch_network_admin_tools_page_content' );
			?>

		</div><!-- /#post-body-content -->

		<div id="postbox-container-1" class="postbox-container">

			<div id="side-sortables" class="meta-box-sortables ui-sortable">
			<?php Admin::display_admin_sidebar(); ?>
			</div><!-- /#side-sortables -->

		</div><!-- /#postbox-container-1 -->
		</div><!-- /#post-body -->
		<br class="clear" />
		</div><!-- /#poststuff -->

	</div><!-- /.wrap -->

		<?php
		echo ob_get_clean(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Enqueue scripts and styles.
	 *
	 * @since 4.2.0
	 *
	 * @param string $hook The current screen hook.
	 */
	public function admin_enqueue_scripts( $hook ) {
		$screen = get_current_screen();

		if ( $this->parent_id === $screen->id || $this->parent_id === $hook ) {
			wp_enqueue_script( 'better-search-admin-js' );
			wp_enqueue_style( 'better-search-admin-ui-css' );
			wp_localize_script(
				'better-search-admin-js',
				'bsearch_admin_data',
				array(
					'security'       => wp_create_nonce( 'bsearch-admin' ),
					'clear_cache'    => __( 'Clear cache', 'better-search' ),
					'clearing_cache' => __( 'Clearing cache', 'better-search' ),
				)
			);
		}
	}
}
