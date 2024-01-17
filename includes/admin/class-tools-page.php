<?php
/**
 * Generates the Tools page.
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
 * Generates the Tools page.
 *
 * @since 3.3.0
 */
class Tools_Page {

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
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
		add_action( 'admin_init', array( $this, 'process_settings_export' ) );
		add_action( 'admin_init', array( $this, 'process_settings_import' ), 9 );
	}

	/**
	 * Admin Menu.
	 *
	 * @since 3.3.0
	 */
	public function admin_menu() {

		$this->parent_id = add_submenu_page(
			'bsearch_dashboard',
			esc_html__( 'Better Search Tools', 'better-search' ),
			esc_html__( 'Tools', 'better-search' ),
			'manage_options',
			'bsearch_tools_page',
			array( $this, 'render_page' )
		);

		add_action( 'load-' . $this->parent_id, array( $this, 'help_tabs' ) );
	}

	/**
	 * Enqueue scripts in admin area.
	 *
	 * @since 3.3.0
	 *
	 * @param string $hook The current admin page.
	 */
	public function admin_enqueue_scripts( $hook ) {
		if ( $hook === $this->parent_id ) {
			wp_enqueue_script( 'better-search-admin-js' );
			wp_enqueue_style( 'bsearch-admin-ui-css', );
			wp_localize_script(
				'better-search-admin-js',
				'bsearch_admin_data',
				array(
					'security' => wp_create_nonce( 'bsearch-admin' ),
				)
			);
		}
	}

	/**
	 * Render the tools settings page.
	 *
	 * @since 3.3.0
	 *
	 * @return void
	 */
	public function render_page() {
		global $wpdb;

		/* Recreate index */
		if ( ( isset( $_POST['bsearch_recreate'] ) ) && ( check_admin_referer( 'bsearch-tools-settings' ) ) ) {
			self::recreate_index();
			add_settings_error( 'bsearch-notices', '', esc_html__( 'FULLTEXT index has been recreated', 'better-search' ), 'error' );
		}

		/* Truncate overall posts table */
		if ( ( isset( $_POST['bsearch_trunc_all'] ) ) && ( check_admin_referer( 'bsearch-tools-settings' ) ) ) {
			self::trunc_count( false );
			add_settings_error( 'bsearch-notices', '', esc_html__( 'Better Search popular searches table reset', 'better-search' ), 'error' );
		}

		/* Truncate daily posts table */
		if ( ( isset( $_POST['bsearch_trunc_daily'] ) ) && ( check_admin_referer( 'bsearch-tools-settings' ) ) ) {
			self::trunc_count( true );
			add_settings_error( 'bsearch-notices', '', esc_html__( 'Better Search daily searches table reset', 'better-search' ), 'error' );
		}

		/* Delete old settings */
		if ( ( isset( $_POST['bsearch_delete_old_settings'] ) ) && ( check_admin_referer( 'bsearch-tools-settings' ) ) ) {
			$old_settings = get_option( 'ald_bsearch_settings' );

			if ( empty( $old_settings ) ) {
				add_settings_error( 'bsearch-notices', '', esc_html__( 'Old settings key does not exist', 'autoclose' ), 'error' );
			} else {
				delete_option( 'ald_bsearch_settings' );
				add_settings_error( 'bsearch-notices', '', esc_html__( 'Old settings key has been deleted', 'autoclose' ), 'updated' );
			}
		}

		/* Message for successful file import */
		if ( isset( $_GET['settings_import'] ) && 'success' === $_GET['settings_import'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			add_settings_error( 'bsearch-notices', '', esc_html__( 'Settings have been imported successfully', 'better-search' ), 'updated' );
		}

		ob_start();
		?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Better Search Tools', 'better-search' ); ?></h1>

		<?php settings_errors(); ?>

		<div id="poststuff">
		<div id="post-body" class="metabox-holder columns-2">
		<div id="post-body-content">

			<form method="post" >

				<h2 style="padding-left:0px"><?php esc_html_e( 'Clear cache', 'better-search' ); ?></h2>
				<p>
					<input type="button" name="cache_clear" id="cache_clear"  value="<?php esc_attr_e( 'Clear cache', 'better-search' ); ?>" class="button button-secondary" onclick="return clearCache();" />
				</p>
				<p class="description">
					<?php esc_html_e( 'Clear the Better Search cache. This will also be cleared automatically when you save the settings page.', 'better-search' ); ?>
				</p>

				<h2 style="padding-left:0px"><?php esc_html_e( 'Recreate FULLTEXT index', 'better-search' ); ?></h2>
				<p>
					<input name="bsearch_recreate" type="submit" id="bsearch_recreate" value="<?php esc_attr_e( 'Recreate Index', 'better-search' ); ?>" class="button button-secondary" onclick="if ( ! confirm('<?php esc_attr_e( 'Are you sure you want to recreate the index?', 'better-search' ); ?>') ) return false;" />
				</p>
				<p class="description">
					<?php esc_html_e( 'Recreate the FULLTEXT index that Better Search uses to get the relevant search results. This might take a lot of time to regenerate if you have a lot of posts.', 'better-search' ); ?>
				</p>
				<p class="description"><?php esc_html_e( 'If the Recreate Index button fails, please run the following queries in phpMyAdmin or Adminer', 'better-search' ); ?></p>
				<p>
					<code style="display:block">ALTER TABLE <?php echo esc_attr( $wpdb->posts ); ?> DROP INDEX bsearch;</code>
					<code style="display:block">ALTER TABLE <?php echo esc_attr( $wpdb->posts ); ?> DROP INDEX bsearch_title;</code>
					<code style="display:block">ALTER TABLE <?php echo esc_attr( $wpdb->posts ); ?> DROP INDEX bsearch_content;</code>
					<code style="display:block">ALTER TABLE <?php echo esc_attr( $wpdb->posts ); ?> ADD FULLTEXT bsearch_related (post_title, post_content);</code>
					<code style="display:block">ALTER TABLE <?php echo esc_attr( $wpdb->posts ); ?> ADD FULLTEXT bsearch_related_title (post_title);</code>
					<code style="display:block">ALTER TABLE <?php echo esc_attr( $wpdb->posts ); ?> ADD FULLTEXT bsearch_related_content (post_content);</code>
				</p>

				<h2 style="padding-left:0px"><?php esc_html_e( 'Reset database', 'better-search' ); ?></h2>
				<p>
					<input name="bsearch_trunc_all" type="submit" id="bsearch_trunc_all" value="<?php esc_attr_e( 'Reset Popular searches table', 'better-search' ); ?>" class="button button-secondary" style="color:#f00" onclick="if (!confirm('<?php esc_attr_e( 'Are you sure you want to reset the popular searches?', 'better-search' ); ?>')) return false;" />
					<input name="bsearch_trunc_daily" type="submit" id="bsearch_trunc_daily" value="<?php esc_attr_e( 'Reset Daily Popular searches table', 'better-search' ); ?>" class="button button-secondary" style="color:#f00" onclick="if (!confirm('<?php esc_attr_e( 'Are you sure you want to reset the daily popular searches?', 'better-search' ); ?>')) return false;" />
				</p>
				<p class="description">
					<?php esc_html_e( 'This will reset the Better Search tables. If you are running Better Search on multisite then it will delete the popular posts across the entire network. This cannot be reversed. Make sure that your database has been backed up before proceeding', 'better-search' ); ?>
				</p>

				<?php wp_nonce_field( 'bsearch-tools-settings' ); ?>
			</form>

			<form method="post">

				<h2 style="padding-left:0px"><?php esc_html_e( 'Export/Import settings', 'better-search' ); ?></h2>
				<p class="description">
					<?php esc_html_e( 'Export the plugin settings for this site as a .json file. This allows you to easily import the configuration into another site.', 'better-search' ); ?>
				</p>
				<p><input type="hidden" name="bsearch_action" value="export_settings" /></p>
				<p>
					<?php submit_button( esc_html__( 'Export Settings', 'better-search' ), 'primary', 'bsearch_export_settings', false ); ?>
				</p>

				<?php wp_nonce_field( 'bsearch_export_settings_nonce', 'bsearch_export_settings_nonce' ); ?>
			</form>

			<form method="post" enctype="multipart/form-data">

				<p class="description">
					<?php esc_html_e( 'Import the plugin settings from a .json file. This file can be obtained by exporting the settings on another site using the form above.', 'better-search' ); ?>
				</p>
				<p>
					<input type="file" name="import_settings_file" />
				</p>
				<p>
					<?php submit_button( esc_html__( 'Import Settings', 'better-search' ), 'primary', 'bsearch_import_settings', false ); ?>
				</p>

				<input type="hidden" name="bsearch_action" value="import_settings" />
				<?php wp_nonce_field( 'bsearch_import_settings_nonce', 'bsearch_import_settings_nonce' ); ?>
			</form>

			<form method="post">
				<h2 style="padding-left:0px"><?php esc_html_e( 'Other tools', 'better-search' ); ?></h2>
				<p>
					<input name="bsearch_delete_old_settings" type="submit" id="bsearch_delete_old_settings" value="<?php esc_attr_e( 'Delete old settings', 'better-search' ); ?>" class="button button-secondary" onclick="if (!confirm('<?php esc_attr_e( 'This will delete the settings before v2.5.x. Proceed?', 'better-search' ); ?>')) return false;" />
				</p>
				<p class="description">
					<?php esc_html_e( 'From v2.2.x, Better Search stores the settings in a new key in the database. This will delete the old settings for the current blog. It is recommended that you do this at the earliest after upgrade. However, you should do this only if you are comfortable with the new settings.', 'better-search' ); ?>
				</p>

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
	 * Function to clean the database.
	 *
	 * @since 3.3.0
	 *
	 * @param bool $daily  TRUE = Daily tables, FALSE = Overall tables.
	 */
	public static function trunc_count( $daily = true ) {
		global $wpdb;
		$table_name = ( $daily ) ? $wpdb->prefix . 'bsearch_daily' : $wpdb->prefix . 'bsearch';

		$sql = "TRUNCATE TABLE $table_name";
		$wpdb->query( $sql ); //phpcs:ignore WordPress.DB.DirectDatabaseQuery.SchemaChange,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.PreparedSQL.NotPrepared
	}


	/**
	 * Recreate FULLTEXT indices.
	 *
	 * @since 3.3.0
	 */
	public static function recreate_index() {

		global $wpdb;

		$wpdb->query( 'START TRANSACTION;' ); //phpcs:ignore WordPress.DB.DirectDatabaseQuery.SchemaChange,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.DirectQuery

		$wpdb->query( 'ALTER TABLE ' . $wpdb->posts . ' DROP INDEX bsearch;' ); //phpcs:ignore WordPress.DB.DirectDatabaseQuery.SchemaChange,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.DirectQuery
		$wpdb->query( 'ALTER TABLE ' . $wpdb->posts . ' DROP INDEX bsearch_title;' ); //phpcs:ignore WordPress.DB.DirectDatabaseQuery.SchemaChange,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.DirectQuery
		$wpdb->query( 'ALTER TABLE ' . $wpdb->posts . ' DROP INDEX bsearch_content;' ); //phpcs:ignore WordPress.DB.DirectDatabaseQuery.SchemaChange,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.DirectQuery

		$wpdb->query( 'ALTER TABLE ' . $wpdb->posts . ' ADD FULLTEXT bsearch (post_title, post_content);' ); //phpcs:ignore WordPress.DB.DirectDatabaseQuery.SchemaChange,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.DirectQuery
		$wpdb->query( 'ALTER TABLE ' . $wpdb->posts . ' ADD FULLTEXT bsearch_title (post_title);' ); //phpcs:ignore WordPress.DB.DirectDatabaseQuery.SchemaChange,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.DirectQuery
		$wpdb->query( 'ALTER TABLE ' . $wpdb->posts . ' ADD FULLTEXT bsearch_content (post_content);' ); //phpcs:ignore WordPress.DB.DirectDatabaseQuery.SchemaChange,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.DirectQuery

		$wpdb->query( 'COMMIT;' ); //phpcs:ignore WordPress.DB.DirectDatabaseQuery.SchemaChange,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.DirectQuery
	}

	/**
	 * Process a settings export that generates a .json file of the shop settings
	 *
	 * @since 3.3.0
	 */
	public static function process_settings_export() {

		if ( empty( $_POST['bsearch_action'] ) || 'export_settings' !== $_POST['bsearch_action'] ) {
			return;
		}

		if ( ! isset( $_POST['bsearch_export_settings_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['bsearch_export_settings_nonce'] ), 'bsearch_export_settings_nonce' ) ) {
			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$settings = get_option( 'bsearch_settings' );

		ignore_user_abort( true );

		nocache_headers();
		header( 'Content-Type: application/json; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=bsearch-settings-export-' . gmdate( 'm-d-Y' ) . '.json' );
		header( 'Expires: 0' );

		echo wp_json_encode( $settings );
		exit;
	}

	/**
	 * Process a settings import from a json file
	 *
	 * @since 3.3.0
	 */
	public static function process_settings_import() {

		if ( empty( $_POST['bsearch_action'] ) || 'import_settings' !== $_POST['bsearch_action'] ) {
			return;
		}

		if ( ! isset( $_POST['bsearch_import_settings_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['bsearch_import_settings_nonce'] ), 'bsearch_import_settings_nonce' ) ) {
			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$filename  = 'import_settings_file';
		$extension = isset( $_FILES[ $filename ]['name'] ) ? pathinfo( sanitize_file_name( wp_unslash( $_FILES[ $filename ]['name'] ) ), PATHINFO_EXTENSION ) : '';

		if ( 'json' !== $extension ) {
			wp_die( esc_html__( 'Please upload a valid .json file', 'better-search' ) );
		}

		$import_file = isset( $_FILES[ $filename ]['tmp_name'] ) ? ( wp_unslash( $_FILES[ $filename ]['tmp_name'] ) ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

		if ( empty( $import_file ) ) {
			wp_die( esc_html__( 'Please upload a file to import', 'better-search' ) );
		}

		// Retrieve the settings from the file and convert the json object to an array.
		$settings = (array) json_decode( file_get_contents( $import_file ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents

		update_option( 'bsearch_settings', $settings );

		wp_safe_redirect(
			add_query_arg(
				array(
					'page'            => 'bsearch_tools_page',
					'settings_import' => 'success',
				),
				admin_url( 'admin.php' )
			)
		);
		exit;
	}

	/**
	 * Generates the Tools help page.
	 *
	 * @since 3.3.0
	 */
	public static function help_tabs() {
		$screen = get_current_screen();

		$screen->set_help_sidebar(
		/* translators: 1: Support link. */
			'<p>' . sprintf( __( 'For more information or how to get support visit the <a href="%1$s">WebberZone support site</a>.', 'better-search' ), esc_url( 'https://webberzone.com/support/' ) ) . '</p>' .
			/* translators: 1: Forum link. */
			'<p>' . sprintf( __( 'Support queries should be posted in the <a href="%1$s">WordPress.org support forums</a>.', 'better-search' ), esc_url( 'https://wordpress.org/support/plugin/better-search' ) ) . '</p>' .
			'<p>' . sprintf(
			/* translators: 1: Github Issues link, 2: Github page. */
				__( '<a href="%1$s">Post an issue</a> on <a href="%2$s">GitHub</a> (bug reports only).', 'better-search' ),
				esc_url( 'https://github.com/WebberZone/better-search/issues' ),
				esc_url( 'https://github.com/WebberZone/better-search' )
			) . '</p>'
		);

		$screen->add_help_tab(
			array(
				'id'      => 'bsearch-settings-general',
				'title'   => __( 'General', 'better-search' ),
				'content' =>
				'<p>' . __( 'This screen provides some tools that help maintain certain features of Better Search.', 'better-search' ) . '</p>' .
					'<p>' . __( 'Clear the cache, reset the popular posts tables plus some miscellaneous fixes for older versions of Better Search.', 'better-search' ) . '</p>',
			)
		);

		do_action( 'bsearch_settings_tools_help', $screen );
	}
}
