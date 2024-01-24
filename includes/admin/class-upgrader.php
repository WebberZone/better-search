<?php
/**
 * Generates and controls upgrade page.
 *
 * @link  https://webberzone.com
 * @since 3.3.0
 *
 * @package Better_Search
 */

namespace WebberZone\Better_Search\Admin;

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Upgrader class.
 *
 * @since 3.3.0
 */
class Upgrader {

	/**
	 * Parent Menu ID.
	 *
	 * @since 3.3.0
	 *
	 * @var string Parent Menu ID.
	 */
	public $parent_id;

	/**
	 * Constructor class.
	 *
	 * @since 3.3.0
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_action( 'network_admin_menu', array( $this, 'network_admin_menu' ) );
	}

	/**
	 * Admin Menu.
	 *
	 * @since 3.3.0
	 */
	public function admin_menu() {

		$this->parent_id = add_submenu_page(
			'null',
			esc_html__( 'Upgrade Better Search Database', 'better-search' ),
			esc_html__( 'Upgrade', 'better-search' ),
			'manage_options',
			'bsearch-upgrader',
			array( $this, 'render_page' )
		);
	}

	/**
	 * Network Admin Menu.
	 *
	 * @since 3.3.0
	 */
	public function network_admin_menu() {

		$this->parent_id = add_submenu_page(
			'null',
			esc_html__( 'Upgrade Better Search Database', 'better-search' ),
			esc_html__( 'Upgrade', 'better-search' ),
			'manage_options',
			'bsearch-upgrader',
			array( $this, 'render_page' )
		);
	}

	/**
	 * Render the tools settings page.
	 *
	 * @since 3.3.0
	 *
	 * @return void
	 */
	public function render_page() {
		get_admin_page_title();

		/* Recreate index */
		if ( ( isset( $_POST['bsearch_upgrade_db'] ) ) && ( check_admin_referer( 'bsearch-tools-settings' ) ) ) {
			$status = isset( $_POST['bsearch_network_admin'] ) ? self::upgrade_network() : self::upgrade_db();
			foreach ( $status as $message ) {
				add_settings_error( 'bsearch-notices', '', $message, 'info' );
			}
		}
		ob_start();
		?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Better Search Database Upgrade', 'better-search' ); ?></h1>

		<?php settings_errors(); ?>

		<div id="poststuff">
		<div id="post-body" class="metabox-holder columns-2">
		<div id="post-body-content">

			<form method="post">
				<p class="description">
					<?php esc_html_e( 'Upgrade the Better Search Database Tables.', 'better-search' ); ?>
					<?php if ( is_network_admin() ) { ?>
						<strong><?php esc_html_e( 'This will upgrade the database across all sites in the network.', 'better-search' ); ?></strong>
					<?php } ?>
				</p>
				<p class="description">
					<strong><?php esc_html_e( 'Please ensure you have a database backup before proceeding with the upgrade!', 'better-search' ); ?></strong>
				</p>
				<p>
					<input name="bsearch_upgrade_db" type="submit" id="bsearch_upgrade_db" value="<?php esc_attr_e( 'Click to begin', 'better-search' ); ?>" class="button button-secondary" />
				</p>

				<input type="hidden" name="bsearch_network_admin" value="<?php echo( is_network_admin() ? 1 : 0 ); ?>" />

				<?php wp_nonce_field( 'bsearch-tools-settings' ); ?>
			</form>

		</div><!-- /#post-body-content -->

		<div id="postbox-container-1" class="postbox-container">

			<div id="side-sortables" class="meta-box-sortables ui-sortable">
				<?php include_once 'settings/sidebar.php'; ?>
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
	 * Upgrade the database.
	 *
	 * @since 3.3.0
	 *
	 * @return string True if upgraded, false if not.
	 */
	public static function upgrade_db() {

		// Upgrade table code for 2.0.0.
		$current_db_version = get_option( 'bsearch_db_version' );

		if ( version_compare( $current_db_version, BETTER_SEARCH_DB_VERSION, '<' ) ) {
			$success_overall = Activator::recreate_overall_table();
			$success_daily   = Activator::recreate_daily_table();

			if ( is_wp_error( $success_overall ) ) {
				return $success_overall->get_error_message();
			}
			if ( is_wp_error( $success_daily ) ) {
				return $success_daily->get_error_message();
			}

			update_option( 'bsearch_db_version', BETTER_SEARCH_DB_VERSION );
			return sprintf( esc_html__( 'Database upgraded on site %s', 'better-search' ), get_site_url() );
		}
		return sprintf( esc_html__( 'Database is already up to date on site %s', 'better-search' ), get_site_url() );
	}

	/**
	 * Upgrade across all sites in the network.
	 *
	 * @since 3.3.0
	 *
	 * @return array Status of the upgrade.
	 */
	public static function upgrade_network() {
		$network_wide = isset( $_POST['bsearch_network_admin'] ) ? absint( $_POST['bsearch_network_admin'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Missing

		$status = array();

		if ( is_multisite() && $network_wide ) {
			$sites = get_sites(
				array(
					'archived' => 0,
					'spam'     => 0,
					'deleted'  => 0,
				)
			);

			foreach ( $sites as $site ) {
				switch_to_blog( (int) $site->blog_id );
				$status[] = self::upgrade_db();
			}

			// Switch back to the current blog.
			restore_current_blog();

		} else {
			$status[] = self::upgrade_db();
		}

		return $status;
	}
}
