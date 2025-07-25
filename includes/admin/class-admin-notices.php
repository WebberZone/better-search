<?php
/**
 * Controls admin notices.
 *
 * @package Better_Search
 */

namespace WebberZone\Better_Search\Admin;

use WebberZone\Better_Search\Util\Hook_Registry;
use WebberZone\Better_Search\Db;
use function WebberZone\Better_Search\better_search;

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Admin Notices Class.
 *
 * @since 3.3.0
 */
class Admin_Notices {

	/**
	 * Admin Notices API instance.
	 *
	 * @since 4.2.0
	 *
	 * @var Admin_Notices_API
	 */
	private ?Admin_Notices_API $admin_notices_api = null;

	/**
	 * Constructor class.
	 *
	 * @since 3.3.0
	 */
	public function __construct() {
		// Add initialization hook that runs after full plugin setup.
		Hook_Registry::add_action( 'admin_init', array( $this, 'init' ), 5 );
		Hook_Registry::add_action( 'admin_init', array( $this, 'update_db_check' ) );
	}

	/**
	 * Initialize the notices API reference after full plugin initialization.
	 *
	 * @since 3.3.0
	 */
	public function init() {
		$this->admin_notices_api = better_search()->admin->admin_notices_api;
		$this->register_notices();
	}

	/**
	 * Update DB check and register notice if needed.
	 *
	 * @since 3.3.0
	 */
	public function update_db_check() {
		$current_db_version = get_option( 'bsearch_db_version' );

		if ( $current_db_version && version_compare( $current_db_version, BETTER_SEARCH_DB_VERSION, '<' ) ) {
			$this->register_db_update_notice();
		}
	}

	/**
	 * Register database update notice.
	 *
	 * @since 4.2.0
	 */
	private function register_db_update_notice() {
		$is_upgrader_page = isset( $_GET['page'] ) && 'bsearch-upgrader' === $_GET['page']; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		if ( $is_upgrader_page ) {
			return;
		}

		// Check if admin_notices_api is available.
		if ( ! $this->admin_notices_api ) {
			return;
		}

		$message = sprintf(
			'<p>%s</p><p><a href="%s" class="button button-primary">%s</a>',
			esc_html__( 'Better Search database needs to be updated. Please click on the button below to update the database.', 'better-search' ),
			esc_url( admin_url( 'admin.php?page=bsearch-upgrader&bsearch_action=update_db' ) ),
			esc_html__( 'Update Database', 'better-search' )
		);

		if ( is_multisite() && current_user_can( 'manage_network_options' ) ) {
			$message .= sprintf(
				' <a href="%s" class="button button-primary">%s</a>',
				esc_url( network_admin_url( 'admin.php?page=bsearch-upgrader&bsearch_action=update_db' ) ),
				esc_html__( 'Update Database (Network)', 'better-search' )
			);
		}

		$message .= '</p>';

		$this->admin_notices_api->register_notice(
			array(
				'id'          => 'bsearch_update_db',
				'message'     => $message,
				'type'        => 'warning',
				'dismissible' => false,
				'screens'     => array(),
				'capability'  => 'manage_options',
			)
		);

		if ( is_multisite() ) {
			Hook_Registry::add_action(
				'network_admin_notices',
				function () use ( $message ) {
					// Check if admin_notices_api is available.
					if ( ! $this->admin_notices_api ) {
						return;
					}

					$this->admin_notices_api->register_notice(
						array(
							'id'          => 'bsearch_update_db_network',
							'message'     => $message,
							'type'        => 'warning',
							'dismissible' => false,
							'capability'  => 'manage_network_options',
						)
					);
				}
			);
		}
	}

	/**
	 * Register all notices with the API.
	 *
	 * @since 4.2.0
	 */
	private function register_notices() {
		// Only register notices if the API is available.
		if ( ! $this->admin_notices_api ) {
			return;
		}

		$this->register_fulltext_index_notice();
		$this->register_missing_table_notice();
	}

	/**
	 * Register fulltext index notice.
	 *
	 * @since 4.2.0
	 */
	private function register_fulltext_index_notice() {
		// Check if admin_notices_api is available.
		if ( ! $this->admin_notices_api ) {
			return;
		}

		$this->admin_notices_api->register_notice(
			array(
				'id'          => 'bsearch_missing_fulltext_index',
				'message'     => sprintf(
					'<p>%s <a href="%s">%s</a></p>',
					esc_html__( 'Better Search: Some fulltext indexes are missing, which will affect search results.', 'better-search' ),
					esc_url( admin_url( 'admin.php?page=bsearch_tools_page#bsearch-recreate-index' ) ),
					esc_html__( 'Click here to recreate indexes.', 'better-search' )
				),
				'type'        => 'warning',
				'dismissible' => true,
				'capability'  => 'manage_options',
				'conditions'  => array(
					function () {
						return current_user_can( 'manage_options' ) &&
								\bsearch_get_option( 'use_fulltext' ) &&
								! Db::is_fulltext_index_installed();
					},
				),
			)
		);
	}

	/**
	 * Register missing table notice.
	 *
	 * @since 4.2.0
	 */
	private function register_missing_table_notice() {
		// Check if admin_notices_api is available.
		if ( ! $this->admin_notices_api ) {
			return;
		}

		global $wpdb;

		$table_name       = $wpdb->prefix . 'bsearch';
		$table_name_daily = $wpdb->prefix . 'bsearch_daily';

		$this->admin_notices_api->register_notice(
			array(
				'id'          => 'bsearch_missing_tables',
				'message'     => sprintf(
					'<p>%s <a href="%s">%s</a></p>',
					esc_html__( 'Better Search: Some tables are missing, which will affect search results.', 'better-search' ),
					esc_url( admin_url( 'admin.php?page=bsearch_tools_page#bsearch-recreate-tables' ) ),
					esc_html__( 'Click here to recreate tables.', 'better-search' )
				),
				'type'        => 'warning',
				'dismissible' => true,
				'capability'  => 'manage_options',
				'conditions'  => array(
					function () use ( $table_name, $table_name_daily ) {
						return current_user_can( 'manage_options' ) &&
								( ! Db::is_table_installed( $table_name ) ||
								! Db::is_table_installed( $table_name_daily ) );
					},
				),
			)
		);
	}
}
