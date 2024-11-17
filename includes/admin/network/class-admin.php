<?php
/**
 * Register Settings.
 *
 * @since 4.0.0
 *
 * @package WebberZone\Better_Search\Admin
 */

namespace WebberZone\Better_Search\Admin\Network;

use WebberZone\Better_Search\Main;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Class to register the settings.
 *
 * @since 4.0.0
 */
class Admin {

	/**
	 * Parent ID.
	 *
	 * @var string
	 */
	public $parent_id;

	/**
	 * Main constructor class.
	 *
	 * @since 4.0.0
	 */
	public function __construct() {
		$this->hooks();
	}

	/**
	 * Run the hooks.
	 *
	 * @since 4.0.0
	 */
	public function hooks() {
		add_action( 'network_admin_menu', array( $this, 'network_admin_menu' ) );
	}

	/**
	 * Add the network admin menu.
	 *
	 * @since 4.0.0
	 */
	public function network_admin_menu() {
		$this->parent_id = add_menu_page(
			esc_html__( 'Better Search Multisite Dashboard', 'better-search' ),
			esc_html__( 'Better Search', 'better-search' ),
			'manage_network_options',
			'bsearch_dashboard',
			array( $this, 'render_page' ),
			'dashicons-search'
		);

		add_submenu_page(
			'bsearch_dashboard',
			esc_html__( 'Better Search Multisite Settings', 'better-search' ),
			esc_html__( 'Settings', 'better-search' ),
			'manage_network_options',
			'bsearch_dashboard',
			array( $this, 'render_page' )
		);
	}

	/**
	 * Render the page.
	 *
	 * @since 4.0.0
	 */
	public function render_page() {
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Better Search Multisite Settings', 'better-search' ); ?></h1>
			<p><?php esc_html_e( 'This page allows you to configure the settings for Better Search on your multisite network.', 'better-search' ); ?></p>

			<?php Main::pro_upgrade_banner( false ); ?>

			<?php do_action( 'bsearch_multisite_settings' ); ?>
		</div>
		<?php
	}
}
