<?php
/**
 * Generates the Tools page.
 *
 * @since 3.3.0
 *
 * @package Better_Search
 */

namespace WebberZone\Better_Search\Admin;

use WebberZone\Better_Search\Util\Hook_Registry;
use WebberZone\Better_Search\Db;

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
		Hook_Registry::add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		Hook_Registry::add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
		Hook_Registry::add_action( 'admin_init', array( $this, 'process_settings_export' ) );
		Hook_Registry::add_action( 'admin_init', array( $this, 'process_settings_import' ), 9 );
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

		Hook_Registry::add_action( 'load-' . $this->parent_id, array( $this, 'help_tabs' ) );
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
			wp_enqueue_style( 'better-search-admin-ui-css' );
			wp_enqueue_style( 'wp-spinner' );
			wp_localize_script(
				'better-search-admin-js',
				'bsearch_admin_data',
				array(
					'ajax_url'             => admin_url( 'admin-ajax.php' ),
					'security'             => wp_create_nonce( 'bsearch-admin' ),
					'confirm_message'      => esc_html__( 'Are you sure you want to clear the cache?', 'better-search' ),
					'success_message'      => esc_html__( 'Cache cleared successfully!', 'better-search' ),
					'fail_message'         => esc_html__( 'Failed to clear cache. Please try again.', 'better-search' ),
					'request_fail_message' => esc_html__( 'Request failed: ', 'better-search' ),
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
			add_settings_error( 'bsearch-notices', '', esc_html__( 'Indices have been recreated', 'better-search' ), 'success' );
		}

		/* Truncate overall posts table */
		if ( ( isset( $_POST['bsearch_trunc_all'] ) ) && ( check_admin_referer( 'bsearch-tools-settings' ) ) ) {
			self::trunc_count( false );
			add_settings_error( 'bsearch-notices', '', esc_html__( 'Better Search popular searches table reset', 'better-search' ), 'success' );
		}

		/* Truncate daily posts table */
		if ( ( isset( $_POST['bsearch_trunc_daily'] ) ) && ( check_admin_referer( 'bsearch-tools-settings' ) ) ) {
			self::trunc_count( true );
			add_settings_error( 'bsearch-notices', '', esc_html__( 'Better Search daily searches table reset', 'better-search' ), 'success' );
		}

		/* Create tables */
		if ( ( isset( $_POST['bsearch_create_tables'] ) ) && ( check_admin_referer( 'bsearch-tools-settings' ) ) ) {
			Db::create_tables();
			add_settings_error( 'bsearch-notices', '', esc_html__( 'Tables have been created', 'better-search' ), 'success' );
		}

		/* Recreate tables */
		if ( ( isset( $_POST['bsearch_recreate_overall'] ) ) && ( check_admin_referer( 'bsearch-tools-settings' ) ) ) {
			Db::recreate_overall_table( false );
			add_settings_error( 'bsearch-notices', '', esc_html__( 'Overall tables have been recreated', 'better-search' ), 'success' );
		}
		if ( ( isset( $_POST['bsearch_recreate_daily'] ) ) && ( check_admin_referer( 'bsearch-tools-settings' ) ) ) {
			Db::recreate_daily_table( false );
			add_settings_error( 'bsearch-notices', '', esc_html__( 'Daily tables have been recreated', 'better-search' ), 'success' );
		}

		/* Restore backup tables */
		if ( ( isset( $_POST['bsearch_restore_overall'] ) ) && ( check_admin_referer( 'bsearch-tools-settings' ) ) ) {
			$restore_flag = self::restore_backup_tables( false );
			if ( ! $restore_flag ) {
				add_settings_error( 'bsearch-notices', '', esc_html__( 'Backup tables do not exist', 'better-search' ), 'error' );
			} else {
				add_settings_error( 'bsearch-notices', '', esc_html__( 'Backup tables have been restored', 'better-search' ), 'success' );
			}
		}
		if ( ( isset( $_POST['bsearch_restore_daily'] ) ) && ( check_admin_referer( 'bsearch-tools-settings' ) ) ) {
			$restore_flag = self::restore_backup_tables( true );
			if ( ! $restore_flag ) {
				add_settings_error( 'bsearch-notices', '', esc_html__( 'Backup tables do not exist', 'better-search' ), 'error' );
			} else {
				add_settings_error( 'bsearch-notices', '', esc_html__( 'Backup tables have been restored', 'better-search' ), 'success' );
			}
		}

		/* Delete backup tables */
		if ( ( isset( $_POST['bsearch_delete_backup_tables'] ) ) && ( check_admin_referer( 'bsearch-tools-settings' ) ) ) {
			self::delete_backup_tables();
			add_settings_error( 'bsearch-notices', '', esc_html__( 'Backup tables have been deleted', 'better-search' ), 'success' );
		}

		/* Message for successful file import */
		if ( isset( $_GET['settings_import'] ) && 'success' === $_GET['settings_import'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			add_settings_error( 'bsearch-notices', '', esc_html__( 'Settings have been imported successfully', 'better-search' ), 'success' );
		}

		ob_start();
		?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Better Search Tools', 'better-search' ); ?></h1>

		<?php settings_errors(); ?>

		<div id="poststuff">
		<div id="post-body" class="metabox-holder columns-2">
		<div id="post-body-content">

			<form method="post">

				<div class="postbox">
					<h2 id="bsearch-clear-cache"><span><?php esc_html_e( 'Clear cache', 'better-search' ); ?></span></h2>
					<div class="inside">
						<p>
							<?php
								printf(
									'<button type="button" name="bsearch_cache_clear" class="button button-secondary bsearch_cache_clear" aria-label="%1$s">%1$s</button>',
									esc_html__( 'Clear cache', 'better-search' )
								);
							?>
						</p>
						<p class="description">
							<?php esc_html_e( 'Clear the Better Search cache. This will also be cleared automatically when you save the settings page.', 'better-search' ); ?>
						</p>
					</div>
				</div>

				<?php wp_nonce_field( 'bsearch-tools-settings' ); ?>
			</form>

			<form method="post">
				<div class="postbox">
					<h2 id="bsearch-recreate-index"><span><?php esc_html_e( 'Recreate FULLTEXT index', 'better-search' ); ?></span></h2>
					<div class="inside">
						<p>
							<?php
								printf(
									'<button type="submit" name="bsearch_recreate" id="bsearch_recreate" class="button button-secondary" onclick="if ( ! confirm(\'%1$s\') ) return false;">%2$s</button>',
									esc_attr__( 'Are you sure you want to recreate the index?', 'better-search' ),
									esc_attr__( 'Recreate Index', 'better-search' )
								);
							?>
						</p>
						<p class="description">
							<?php esc_html_e( 'Recreate the FULLTEXT index that Better Search uses to get the relevant search results. This might take a lot of time to regenerate if you have a lot of posts.', 'better-search' ); ?>
						</p>
						<p class="description"><?php esc_html_e( 'If the Recreate Index button fails, please run the following queries in phpMyAdmin or Adminer', 'better-search' ); ?></p>

						<div class="bsearch-code-wrapper">
							<?php $sql_queries = self::recreate_indices_sql(); ?>
							<pre id="bsearch-indices-sql"><code><?php echo implode( "\n", array_map( 'esc_html', $sql_queries ) ); ?></code></pre>
						</div>
						<script>
							jQuery(document).ready(function($) {
								bsearchAddCopyButton('bsearch-indices-sql');
							});
						</script>

					</div>
				</div>

				<?php wp_nonce_field( 'bsearch-tools-settings' ); ?>
			</form>

			<form method="post">
				<div class="postbox">
					<h2 id="bsearch-create-tables"><span><?php esc_html_e( 'Create tables', 'better-search' ); ?></span></h2>
					<div class="inside">
						<p class="description">
							<?php esc_html_e( 'These buttons will create the tables in which Better Search stores its data.', 'better-search' ); ?>
						</p>
						<p>
							<?php
								printf(
									'<button type="submit" name="bsearch_create_tables" id="bsearch_create_tables" class="button button-secondary" onclick="if (!confirm(\'%1$s\')) return false;">%2$s</button>',
									esc_attr__( 'This will create the overall tables. Have you backed up your database?', 'better-search' ),
									esc_attr__( 'Create overall tables', 'better-search' )
								);
							?>
						</p>
					</div>
				</div>

				<?php wp_nonce_field( 'bsearch-tools-settings' ); ?>
			</form>

			<form method="post">
				<div class="postbox">
					<h2 id="bsearch-recreate-tables"><span><?php esc_html_e( 'Recreate Tables', 'better-search' ); ?></span></h2>
					<div class="inside">
						<p class="description">
							<?php esc_html_e( 'These buttons will recreate the tables in which Better Search stores its data. This is particularly useful if you are noticing issues with tracking or if there was a problem with the database upgrade', 'better-search' ); ?>
						</p>
						<p>
							<?php
								printf(
									'<button type="submit" name="bsearch_recreate_overall" id="bsearch_recreate_overall" class="button button-secondary" onclick="if (!confirm(\'%1$s\')) return false;">%2$s</button>',
									esc_attr__( 'This will recreate the overall tables. Have you backed up your database?', 'better-search' ),
									esc_attr__( 'Recreate overall tables', 'better-search' )
								);
								printf(
									'<button type="submit" name="bsearch_recreate_daily" id="bsearch_recreate_daily" class="button button-secondary" onclick="if (!confirm(\'%1$s\')) return false;">%2$s</button>',
									esc_attr__( 'This will recreate the daily tables. Have you backed up your database?', 'better-search' ),
									esc_attr__( 'Recreate daily tables', 'better-search' )
								);
							?>
						</p>
					</div>
				</div>

				<?php wp_nonce_field( 'bsearch-tools-settings' ); ?>
			</form>

			<form method="post">
				<div class="postbox">
					<h2 id="bsearch-reset-database"><span><?php esc_html_e( 'Reset database', 'better-search' ); ?></span></h2>
					<div class="inside">
						<p>
							<?php
								printf(
									'<button type="submit" name="bsearch_trunc_all" id="bsearch_trunc_all" class="button button-secondary" style="color:#f00" onclick="if (!confirm(\'%1$s\')) return false;">%2$s</button>',
									esc_attr__( 'Are you sure you want to reset the popular searches?', 'better-search' ),
									esc_attr__( 'Reset Popular searches table', 'better-search' )
								);
								printf(
									'<button type="submit" name="bsearch_trunc_daily" id="bsearch_trunc_daily" class="button button-secondary" style="color:#f00" onclick="if (!confirm(\'%1$s\')) return false;">%2$s</button>',
									esc_attr__( 'Are you sure you want to reset the daily popular searches?', 'better-search' ),
									esc_attr__( 'Reset Daily Popular searches table', 'better-search' )
								);
							?>
						</p>
						<p class="description">
							<?php esc_html_e( 'This will reset the Better Search tables. If you are running Better Search on multisite then it will delete the popular posts across the entire network. This cannot be reversed. Make sure that your database has been backed up before proceeding', 'better-search' ); ?>
						</p>
					</div>
				</div>

				<?php wp_nonce_field( 'bsearch-tools-settings' ); ?>
			</form>

			<form method="post">
				<div class="postbox">
					<h2 id="bsearch-backup-tables"><span><?php esc_html_e( 'Backup Tables', 'better-search' ); ?></span></h2>
					<div class="inside">
						<p class="description">
							<?php esc_html_e( 'From v3.3, Better Search uses a new database table format.', 'better-search' ); ?>
						</p>
						<p class="description">
							<?php esc_html_e( 'As part of the upgrade process, the plugin backed up the older tables. Restoring any of the tables will also reset the database version so you will once again be prompted to upgrade the tables.', 'better-search' ); ?>
						</p>
						<p class="description">
							<strong><?php esc_html_e( 'You will need to restore both tables and delete the backup tables before you can begin the upgrade process.', 'better-search' ); ?></strong>
						</p>
						<p>
							<button name="bsearch_restore_overall" type="submit" id="bsearch_restore_overall" class="button button-secondary" onclick="if (!confirm('<?php esc_attr_e( 'Are you sure you want to restore the popular searches table from the backup?', 'better-search' ); ?>')) return false;"><?php esc_attr_e( 'Restore Popular searches table', 'better-search' ); ?></button>
							<button name="bsearch_restore_daily" type="submit" id="bsearch_restore_daily" class="button button-secondary" onclick="if (!confirm('<?php esc_attr_e( 'Are you sure you want to restore the daily popular searches table from the backup?', 'better-search' ); ?>')) return false;"><?php esc_attr_e( 'Restore Daily Popular searches table', 'better-search' ); ?></button>
						</p>

						<p class="description">
							<?php esc_html_e( 'If your site has been working fine and populating with new information, then you can delete these backed up tables to save database space.', 'better-search' ); ?>
						</p>
						<p>
							<?php
								printf(
									'<button type="submit" name="bsearch_delete_backup_tables" id="bsearch_delete_backup_tables" class="button button-secondary" style="color:#f00" onclick="if (!confirm(\'%1$s\')) return false;">%2$s</button>',
									esc_attr__( 'This will delete the backup tables of Better Search. Have you backed up your database?', 'better-search' ),
									esc_attr__( 'Delete backup tables', 'better-search' )
								);
							?>
						</p>
					</div>
				</div>

				<?php wp_nonce_field( 'bsearch-tools-settings' ); ?>
			</form>

			<?php
			/**
			 * Action hook to add additional tools page content.
			 *
			 * @since 4.2.0
			 */
			do_action( 'bsearch_admin_tools_page_content' );
			?>

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
	 * Retrieves the SQL code to recreate the fulltext indexes.
	 *
	 * @since 4.2.0
	 */
	public static function recreate_indices_sql() {
		global $wpdb;

		$old_indexes = Db::get_old_fulltext_indexes();
		$new_indexes = Db::get_fulltext_indexes();
		$all_indexes = array_keys( array_merge( $old_indexes, $new_indexes ) );

		$sql = array();

		// Add DROP statements for all possible indexes.
		foreach ( $all_indexes as $index ) {
			if ( Db::is_index_installed( $index ) ) {
				$sql[] = "ALTER TABLE {$wpdb->posts} DROP INDEX {$index};";
			}
		}

		// Add ADD statements only for the new indexes.
		if ( ! empty( $new_indexes ) ) {
			foreach ( $new_indexes as $index => $value ) {
				$sql[] = "ALTER TABLE {$wpdb->posts} ADD FULLTEXT {$index} {$value};";
			}
		}

		return $sql;
	}

	/**
	 * Recreate FULLTEXT indices.
	 *
	 * @since 3.3.0
	 */
	public static function recreate_index() {
		global $wpdb;

		$old_indexes = Db::get_old_fulltext_indexes();
		$new_indexes = Db::get_fulltext_indexes();
		$all_indexes = array_keys( array_merge( $old_indexes, $new_indexes ) );

		foreach ( $all_indexes as $index ) {
			if ( Db::is_index_installed( $index ) ) {
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.SchemaChange,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$wpdb->query( "ALTER TABLE {$wpdb->posts} DROP INDEX {$index}" );
			}
		}

		Db::create_fulltext_indexes();
	}

	/**
	 * Restore tables from backup.
	 *
	 * @since 3.3.0
	 *
	 * @param bool $daily  TRUE = Daily tables, FALSE = Overall tables.
	 *
	 * @return bool True if backup tables exist, false otherwise.
	 */
	public static function restore_backup_tables( $daily = true ) {
		global $wpdb;

		// Check if backup tables exist.
		$backup_table_name = ( $daily ) ? $wpdb->prefix . 'bsearch_daily_backup' : $wpdb->prefix . 'bsearch_backup';
		if ( ! $wpdb->get_var( "SHOW TABLES LIKE '$backup_table_name'" ) ) { // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.SchemaChange,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			return false;
		}

		$table_name = ( $daily ) ? $wpdb->prefix . 'bsearch_daily' : $wpdb->prefix . 'bsearch';

		// Start transaction.
		$wpdb->query( 'START TRANSACTION;' ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.SchemaChange,WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		// Rename tables.
		$wpdb->query( "DROP TABLE IF EXISTS $table_name;" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.SchemaChange,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$wpdb->query( "RENAME TABLE $backup_table_name TO $table_name;" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.SchemaChange,WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		$wpdb->query( 'COMMIT;' ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.SchemaChange,WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		// Restore the database version.
		update_option( 'bsearch_db_version', '1.0' );

		return true;
	}

	/**
	 * Delete Better Search backup tables.
	 *
	 * @since 3.3.0
	 */
	public static function delete_backup_tables() {
		global $wpdb;

		$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}bsearch_backup" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.SchemaChange,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}bsearch_daily_backup" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.SchemaChange,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
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
