<?php
/**
 * Register Settings.
 *
 * @since 4.0.0
 *
 * @package WebberZone\Better_Search\Admin
 */

namespace WebberZone\Better_Search\Admin\Network;

use WebberZone\Better_Search\Util\Hook_Registry;

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
	 * Tools page instance.
	 *
	 * @var Tools_Page
	 */
	public $tools_page;

	/**
	 * Main constructor class.
	 *
	 * @since 4.0.0
	 */
	public function __construct() {
		$this->tools_page = new Tools_Page();

		$this->hooks();
	}

	/**
	 * Run the hooks.
	 *
	 * @since 4.0.0
	 */
	public function hooks() {
		Hook_Registry::add_action( 'network_admin_menu', array( $this, 'network_admin_menu' ) );
		Hook_Registry::add_action( 'admin_post_bsearch_copy_settings', array( $this, 'handle_copy_settings' ) );
		Hook_Registry::add_action( 'network_admin_notices', array( $this, 'show_settings_copied_notice' ) );
		Hook_Registry::add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
	}

	/**
	 * Enqueue admin scripts on network admin pages.
	 *
	 * @since 4.2.0
	 *
	 * @param string $hook Current admin page hook.
	 */
	public function admin_enqueue_scripts( $hook ) {
		if ( $this->parent_id === $hook ) {
			wp_enqueue_script( 'better-search-admin-js' );
			wp_localize_script(
				'better-search-admin-js',
				'bsearch_admin_data',
				array(
					'ajax_url' => admin_url( 'admin-ajax.php' ),
					'security' => wp_create_nonce( 'bsearch-admin' ),
				)
			);
		}
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
			<h1><?php esc_html_e( 'Better Search Pro Multisite Settings', 'better-search' ); ?></h1>
			<?php do_action( 'bsearch_network_admin_settings_page_content_header' ); ?>

			<p><?php esc_html_e( 'This page allows you to configure the settings for Better Search on your multisite network.', 'better-search' ); ?></p>

			<?php settings_errors(); ?>

			<div id="poststuff">
				<div id="post-body" class="metabox-holder columns-2">
					<div id="post-body-content">
						<?php
						\WebberZone\Better_Search\Admin\Admin::pro_upgrade_banner(
							false,
							sprintf(
								/* translators: 1: link to Network Plugins page, 2: link to account page */
								__( 'If you are running Better Search Pro and see the upgrade banner instead of the settings, you may need to activate your license. Go to the %1$s, locate Better Search Pro, and activate your license from there. View your %2$s to check the status of your license after activation.', 'better-search' ),
								'<a href="' . esc_url( network_admin_url( 'plugins.php' ) ) . '" target="_blank">' . esc_html__( 'Network Plugins page', 'better-search' ) . '</a>',
								'<a href="' . esc_url( \WebberZone\Better_Search\bsearch_freemius()->get_account_url() ) . '" target="_blank">' . esc_html__( 'account page', 'better-search' ) . '</a>'
							)
						);
						?>
						<?php do_action( 'bsearch_network_admin_settings_page_content' ); ?>
					</div><!-- /#post-body-content -->

					<div id="postbox-container-1" class="postbox-container">
					<?php \WebberZone\Better_Search\Admin\Admin::display_admin_sidebar(); ?>
					</div><!-- /#postbox-container-1 -->
				</div><!-- /#post-body -->
				<br class="clear" />
			</div><!-- /#poststuff -->
		</div>
		<?php
	}

	/**
	 * Handle copying Better Search settings from a source site to destination sites.
	 *
	 * @since 4.2.0
	 */
	public function handle_copy_settings() {
		if (
			! isset( $_POST['bsearch_copy_settings_nonce'], $_POST['source_blog_id'], $_POST['target_blog_ids'] ) ||
			! wp_verify_nonce( sanitize_key( $_POST['bsearch_copy_settings_nonce'] ), 'bsearch_copy_settings' ) ||
			! current_user_can( 'manage_network_options' )
		) {
			wp_die( esc_html__( 'Security check failed.', 'better-search' ) );
		}

		// Validate and sanitize input data.
		if ( empty( $_POST['source_blog_id'] ) || empty( $_POST['target_blog_ids'] ) ) {
			wp_die( esc_html__( 'Missing required data.', 'better-search' ) );
		}

		$source_blog_id  = (int) absint( $_POST['source_blog_id'] );
		$target_blog_ids = wp_parse_id_list( wp_unslash( $_POST['target_blog_ids'] ) );

		// Additional validation to ensure we have valid IDs.
		if ( 0 === $source_blog_id || empty( $target_blog_ids ) ) {
			wp_die( esc_html__( 'Invalid blog IDs provided.', 'better-search' ) );
		}

		switch_to_blog( $source_blog_id );
		$settings = bsearch_get_settings();
		restore_current_blog();

		foreach ( $target_blog_ids as $target_blog_id ) {
			if ( $target_blog_id === $source_blog_id ) {
				continue;
			}
			switch_to_blog( $target_blog_id );
			update_option( 'bsearch_settings', $settings );
			restore_current_blog();
		}

		// Redirect or display success notice.
		wp_safe_redirect(
			add_query_arg(
				array(
					'page'                          => 'bsearch_dashboard',
					'settings_copied'               => 1,
					'source_blog_id'                => $source_blog_id,
					'target_blog_ids'               => implode( ',', array_diff( $target_blog_ids, array( $source_blog_id ) ) ),
					'bsearch_settings_copied_nonce' => wp_create_nonce( 'bsearch_settings_copied_notice' ),
				),
				network_admin_url( 'admin.php' )
			)
		);
		exit;
	}

	/**
	 * Display a notice if settings were copied successfully.
	 *
	 * @since 4.2.0
	 */
	public function show_settings_copied_notice() {
		$nonce_ok = isset( $_GET['bsearch_settings_copied_nonce'] )
			&& wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['bsearch_settings_copied_nonce'] ) ), 'bsearch_settings_copied_notice' );

		if ( $nonce_ok && isset( $_GET['settings_copied'] ) && 1 === absint( $_GET['settings_copied'] ) ) {
			$source_blog_id  = isset( $_GET['source_blog_id'] )
				? absint( sanitize_text_field( wp_unslash( $_GET['source_blog_id'] ) ) )
				: '';
			$target_blog_ids = isset( $_GET['target_blog_ids'] )
				? wp_parse_id_list( sanitize_text_field( wp_unslash( $_GET['target_blog_ids'] ) ) )
				: array();
			if ( $source_blog_id && $target_blog_ids ) {
				$targets = implode( ', ', $target_blog_ids );
				$message = sprintf(
					/* translators: 1: source blog ID, 2: comma-separated target blog IDs */
					__( 'Better Search settings copied from site ID %1$s to %2$s.', 'better-search' ),
					$source_blog_id,
					$targets
				);
			} else {
				$message = __( 'Better Search settings copied successfully.', 'better-search' );
			}
			echo '<div class="notice notice-success is-dismissible"><p>' . esc_html( $message ) . '</p></div>';
			?>
			<script>
			if (window.history.replaceState) {
				var url = new URL(window.location.href);
				url.searchParams.delete('settings_copied');
				url.searchParams.delete('source_blog_id');
				url.searchParams.delete('target_blog_ids');
				url.searchParams.delete('bsearch_settings_copied_nonce');
				window.history.replaceState({}, document.title, url.pathname + url.search);
			}
			</script>
			<?php
		}
	}
}
