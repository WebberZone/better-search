<?php
/**
 * Generates the Tools page in the Admin area.
 *
 * @link  https://webberzone.com
 * @since 2.2.0
 *
 * @package    Better Search
 * @subpackage Admin/Tools
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}


/**
 * Render the tools settings page.
 *
 * @since 2.2.0
 *
 * @return void
 */
function bsearch_tools_page() {

	global $wpdb;

	/* Recreate index */
	if ( ( isset( $_POST['bsearch_recreate'] ) ) && ( check_admin_referer( 'bsearch-tools-settings' ) ) ) {
		bsearch_recreate_index();
		add_settings_error( 'bsearch-notices', '', esc_html__( 'FULLTEXT index has been recreated', 'better-search' ), 'error' );
	}

	/* Truncate overall posts table */
	if ( ( isset( $_POST['bsearch_trunc_all'] ) ) && ( check_admin_referer( 'bsearch-tools-settings' ) ) ) {
		bsearch_trunc_count( false );
		add_settings_error( 'bsearch-notices', '', esc_html__( 'Better Search popular searches table reset', 'better-search' ), 'error' );
	}

	/* Truncate daily posts table */
	if ( ( isset( $_POST['bsearch_trunc_daily'] ) ) && ( check_admin_referer( 'bsearch-tools-settings' ) ) ) {
		bsearch_trunc_count( true );
		add_settings_error( 'bsearch-notices', '', esc_html__( 'Better Search daily searches table reset', 'better-search' ), 'error' );
	}

	/* Delete old settings */
	if ( ( isset( $_POST['bsearch_delete_old_settings'] ) ) && ( check_admin_referer( 'bsearch-tools-settings' ) ) ) {
		delete_option( 'ald_bsearch_settings' );
		add_settings_error( 'bsearch-notices', '', esc_html__( 'Old settings key has been deleted', 'better-search' ), 'error' );
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
				<?php include_once 'sidebar.php'; ?>
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
 * @since   1.0
 *
 * @param   bool $daily  TRUE = Daily tables, FALSE = Overall tables.
 */
function bsearch_trunc_count( $daily = true ) {
	global $wpdb;
	$table_name = ( $daily ) ? $wpdb->prefix . 'bsearch_daily' : $wpdb->prefix . 'bsearch';

	$sql = "TRUNCATE TABLE $table_name";
	$wpdb->query( $sql ); //phpcs:ignore WordPress.DB.DirectDatabaseQuery.SchemaChange,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.PreparedSQL.NotPrepared
}


/**
 * Recreate FULLTEXT indices.
 *
 * @since   2.2.0
 */
function bsearch_recreate_index() {

	global $wpdb;

	$wpdb->query( 'ALTER TABLE ' . $wpdb->posts . ' DROP INDEX bsearch' ); //phpcs:ignore WordPress.DB.DirectDatabaseQuery.SchemaChange,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.DirectQuery
	$wpdb->query( 'ALTER TABLE ' . $wpdb->posts . ' DROP INDEX bsearch_title' ); //phpcs:ignore WordPress.DB.DirectDatabaseQuery.SchemaChange,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.DirectQuery
	$wpdb->query( 'ALTER TABLE ' . $wpdb->posts . ' DROP INDEX bsearch_content' ); //phpcs:ignore WordPress.DB.DirectDatabaseQuery.SchemaChange,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.DirectQuery

	$wpdb->query( 'ALTER TABLE ' . $wpdb->posts . ' ADD FULLTEXT bsearch (post_title, post_content);' ); //phpcs:ignore WordPress.DB.DirectDatabaseQuery.SchemaChange,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.DirectQuery
	$wpdb->query( 'ALTER TABLE ' . $wpdb->posts . ' ADD FULLTEXT bsearch_title (post_title);' ); //phpcs:ignore WordPress.DB.DirectDatabaseQuery.SchemaChange,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.DirectQuery
	$wpdb->query( 'ALTER TABLE ' . $wpdb->posts . ' ADD FULLTEXT bsearch_content (post_content);' ); //phpcs:ignore WordPress.DB.DirectDatabaseQuery.SchemaChange,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.DirectQuery

}

