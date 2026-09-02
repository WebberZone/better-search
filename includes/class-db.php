<?php
/**
 * Database operations for Better Search.
 *
 * @package Better_Search
 */

namespace WebberZone\Better_Search;

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Database class
 *
 * @since 4.2.0
 */
class Db {

	/**
	 * FULLTEXT index schema version. Bump whenever the registered index set changes.
	 *
	 * @since 4.4.0
	 * @var int
	 */
	const INDEX_VERSION = 2;

	/**
	 * Option used to cache local table status.
	 *
	 * @since 4.4.3
	 * @var string
	 */
	const TABLE_STATUS_OPTION = 'bsearch_table_status';

	/**
	 * Option used to cache FULLTEXT index status.
	 *
	 * @since 4.4.3
	 * @var string
	 */
	const INDEX_STATUS_OPTION = 'bsearch_index_status';

	/**
	 * Option used to cache network table status.
	 *
	 * @since 4.4.3
	 * @var string
	 */
	const NETWORK_TABLE_STATUS_OPTION = 'bsearch_network_table_status';

	/**
	 * Hook used to refresh the network table-status cache.
	 *
	 * @since 4.4.3
	 * @var string
	 */
	const NETWORK_TABLE_STATUS_CRON = 'bsearch_refresh_network_table_status';

	/**
	 * Option used to rate-limit the admin health check.
	 *
	 * @since 4.4.3
	 * @var string
	 */
	const NETWORK_TABLE_HEALTH_OPTION = 'bsearch_network_table_health_check';

	/**
	 * Lifetime of schema status caches.
	 *
	 * Lifecycle events invalidate the caches immediately. The expiry is a backstop for
	 * tables changed outside WordPress.
	 *
	 * @since 4.4.3
	 * @var int
	 */
	const TABLE_STATUS_CACHE_TTL = DAY_IN_SECONDS;

	/**
	 * In-request local table status cache.
	 *
	 * @since 4.4.3
	 * @var array|null
	 */
	private static $table_status_cache = null;

	/**
	 * In-request FULLTEXT index status cache.
	 *
	 * @since 4.4.3
	 * @var array|null
	 */
	private static $index_status_cache = null;

	/**
	 * Cache key for the in-request FULLTEXT index status cache.
	 *
	 * @since 4.4.3
	 * @var string|null
	 */
	private static $index_status_cache_key = null;

	/**
	 * In-request network table status cache.
	 *
	 * @since 4.4.3
	 * @var array|null
	 */
	private static $network_table_status_cache = null;

	/**
	 * Name of the main table.
	 *
	 * @since 4.2.0
	 * @var string
	 */
	public static $table_name = 'bsearch';

	/**
	 * Name of the daily table.
	 *
	 * @since 4.2.0
	 * @var string
	 */
	public static $table_name_daily = 'bsearch_daily';

	/**
	 * Create fulltext indexes on the posts table.
	 *
	 * @since 3.3.0
	 */
	public static function create_fulltext_indexes() {
		self::clear_index_status_cache();

		// Get the list of fulltext indexes.
		$indexes = self::get_fulltext_indexes();

		// Loop through the indexes and create them if not exist.
		foreach ( $indexes as $index => $columns ) {
			if ( ! self::is_index_installed( $index ) ) {
				self::install_fulltext_index( $index, $columns );
			}
		}

		self::clear_index_status_cache();
	}

	/**
	 * Delete the FULLTEXT index.
	 *
	 * @since 4.2.0
	 */
	public static function delete_fulltext_indexes() {
		global $wpdb;

		$indexes = array_merge( self::get_fulltext_indexes(), self::get_old_fulltext_indexes() );

		foreach ( $indexes as $index => $columns ) {
			if ( self::index_exists( $index ) ) {
				$index = esc_sql( $index );
				$wpdb->query( "ALTER TABLE {$wpdb->posts} DROP INDEX $index" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.SchemaChange,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			}
		}

		self::clear_index_status_cache();
	}

	/**
	 * Check if a fulltext index already exists on the posts table.
	 *
	 * @since 4.0.0
	 *
	 * @param string $index Index name.
	 * @return bool True if the index exists, false otherwise.
	 */
	public static function is_index_installed( $index ) {
		$aliases = self::get_legacy_index_aliases();

		return self::index_exists( $index ) || self::index_exists( $aliases[ $index ] ?? '' );
	}

	/**
	 * Check if an index with this exact name exists on the posts table.
	 *
	 * Unlike is_index_installed(), this ignores legacy aliases - use it when acting on the
	 * index itself, e.g. building DROP statements.
	 *
	 * @since 4.4.0
	 *
	 * @param string $index Index name.
	 * @return bool True if the index exists, false otherwise.
	 */
	public static function index_exists( $index ) {
		global $wpdb;

		if ( '' === $index ) {
			return false;
		}

		$exists = $wpdb->get_var( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->prepare(
				"SHOW INDEX FROM {$wpdb->posts} WHERE Key_name = %s",
				$index
			)
		);

		return (bool) $exists;
	}

	/**
	 * Install a fulltext index on the posts table.
	 *
	 * @since 4.0.0
	 *
	 * @param string $index   Index name.
	 * @param string $columns Columns to be indexed.
	 * @return void
	 */
	public static function install_fulltext_index( $index, $columns ) {
		global $wpdb;

		// Install the fulltext index if it doesn't exist.
		$wpdb->query( "ALTER TABLE {$wpdb->posts} ADD FULLTEXT {$index} {$columns};" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.SchemaChange,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		self::clear_index_status_cache();
	}

	/**
	 * Get the list of fulltext indexes to be created on the posts table.
	 *
	 * @since 4.0.0
	 *
	 * @return array Array of fulltext indexes with their respective columns.
	 */
	public static function get_fulltext_indexes() {
		$indexes = array(
			'wz_title_content' => '(post_title, post_content)',
			'wz_title'         => '(post_title)',
			'wz_content'       => '(post_content)',
		);

		/**
		 * Filter the fulltext indexes.
		 *
		 * @since 4.0.0
		 *
		 * @param array $indexes Array of fulltext indexes.
		 */
		return apply_filters( 'bsearch_fulltext_indexes', $indexes );
	}

	/**
	 * Get the list of old fulltext indexes.
	 *
	 * @since 4.2.0
	 *
	 * @return array Array of fulltext indexes with their respective columns.
	 */
	public static function get_old_fulltext_indexes() {
		return array(
			'bsearch'         => '(post_title, post_content)',
			'bsearch_title'   => '(post_title)',
			'bsearch_content' => '(post_content)',
		);
	}

	/**
	 * Get the map of current index names to the legacy index name each one replaces.
	 *
	 * The wz_ indexes are shared across WebberZone plugins, so a sibling plugin's index on the
	 * same columns satisfies ours.
	 *
	 * @since 4.4.0
	 *
	 * @return array Map of current index name => legacy index name.
	 */
	public static function get_legacy_index_aliases() {
		$aliases = array(
			'wz_title_content' => 'bsearch',
			'wz_title'         => 'bsearch_title',
			'wz_content'       => 'bsearch_content',
			'wz_excerpt'       => 'crp_related_excerpt',
		);

		/**
		 * Filter the map of current index names to legacy index names.
		 *
		 * @since 4.4.0
		 *
		 * @param array $aliases Map of current index name => legacy index name.
		 */
		return apply_filters( 'bsearch_legacy_index_aliases', $aliases );
	}

	/**
	 * Create any missing FULLTEXT indexes once per index schema version.
	 *
	 * Existing installs never re-run activation, so a newly registered index would otherwise
	 * wait on the user clicking through the missing-index notice.
	 *
	 * @since 4.4.0
	 */
	public static function maybe_heal_fulltext_indexes() {
		global $wpdb;

		if ( (int) get_option( 'bsearch_index_version', 0 ) >= self::INDEX_VERSION ) {
			return;
		}

		if ( ! bsearch_get_option( 'use_fulltext' ) ) {
			return;
		}

		$show_errors = $wpdb->hide_errors();
		self::create_fulltext_indexes();
		$wpdb->show_errors( $show_errors );

		// Only mark the version as healed if every index is actually in place, so a
		// failed ALTER is retried instead of being silently recorded as done.
		if ( self::is_fulltext_index_installed() ) {
			update_option( 'bsearch_index_version', self::INDEX_VERSION );
		}
	}

	/**
	 * Check the status of all fulltext indexes.
	 *
	 * @since 4.0.0
	 *
	 * @return array Array of index statuses with 'installed' boolean flag and 'status' text.
	 */
	public static function check_fulltext_indexes() {
		// Get the list of fulltext indexes.
		$indexes  = self::get_fulltext_indexes();
		$statuses = array();

		// Check if each index is installed and add to the report.
		foreach ( $indexes as $index => $columns ) {
			$is_installed = self::is_index_installed( $index );

			$statuses[ $index ] = array(
				'columns'   => $columns,
				'installed' => $is_installed,
				'status'    => $is_installed
					? '<span style="color: #006400;">' . __( 'Installed', 'better-search' ) . '</span>'
					: '<span style="color: #8B0000;">' . __( 'Not Installed', 'better-search' ) . '</span>',
			);
		}

		/**
		 * Filter the index statuses report.
		 *
		 * @since 4.0.0
		 *
		 * @param array $statuses Array of index statuses.
		 */
		return apply_filters( 'bsearch_fulltext_index_statuses', $statuses );
	}

	/**
	 * Check if all fulltext indexes are installed.
	 *
	 * @since 4.0.0
	 *
	 * @param bool $use_cache Whether to use the persistent status cache.
	 * @return bool True if all fulltext indexes are installed, false if any are missing.
	 */
	public static function is_fulltext_index_installed( $use_cache = false ) {
		$indexes = self::get_fulltext_indexes();

		if ( ! $use_cache ) {
			foreach ( $indexes as $index => $columns ) {
				if ( ! self::is_index_installed( $index ) ) {
					return false; // Return false if any index is missing.
				}
			}

			return true; // Return true if all indexes are installed.
		}

		$aliases     = self::get_legacy_index_aliases();
		$index_names = array_unique(
			array_merge(
				array_keys( $indexes ),
				array_values( $aliases )
			)
		);
		$cache_key   = self::get_index_status_cache_key( $index_names );
		$statuses    = self::get_index_status_cache( $cache_key, $index_names );

		foreach ( $indexes as $index => $columns ) {
			if ( empty( $statuses[ $index ] ) && empty( $statuses[ $aliases[ $index ] ?? '' ] ) ) {
				return false; // Return false if any index is missing.
			}
		}

		return true; // Return true if all indexes are installed.
	}

	/**
	 * Get a cache key for the configured FULLTEXT index names and current site.
	 *
	 * @since 4.4.3
	 *
	 * @param array $index_names Index names included in the status scan.
	 * @return string Cache key.
	 */
	private static function get_index_status_cache_key( $index_names ) {
		global $wpdb;

		return implode(
			'|',
			array(
				BETTER_SEARCH_DB_VERSION,
				self::INDEX_VERSION,
				$wpdb->posts,
				implode( '|', $index_names ),
			)
		);
	}

	/**
	 * Get the cached FULLTEXT index status.
	 *
	 * One metadata query discovers all relevant index names on a cold cache. The status is
	 * persisted because the fulltext-index notice is evaluated on ordinary admin requests.
	 *
	 * @since 4.4.3
	 *
	 * @param string   $cache_key   Cache key.
	 * @param string[] $index_names Index names to include in the cache.
	 * @return array Index status keyed by index name.
	 */
	private static function get_index_status_cache( $cache_key, $index_names ) {
		global $wpdb;

		if ( self::$index_status_cache_key === $cache_key && is_array( self::$index_status_cache ) ) {
			return self::$index_status_cache;
		}

		$cache = get_option( self::INDEX_STATUS_OPTION, array() );

		if (
			is_array( $cache ) &&
			( $cache['key'] ?? null ) === $cache_key &&
			isset( $cache['generated'], $cache['indexes'] ) &&
			is_array( $cache['indexes'] ) &&
			( time() - (int) $cache['generated'] ) < self::TABLE_STATUS_CACHE_TTL
		) {
			self::$index_status_cache_key = $cache_key;
			self::$index_status_cache     = $cache['indexes'];

			return self::$index_status_cache;
		}

		$statuses = array_fill_keys( $index_names, false );
		$indexes  = $wpdb->get_results( "SHOW INDEX FROM {$wpdb->posts}" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		if ( ! empty( $wpdb->last_error ) ) {
			self::clear_index_status_cache();

			return $statuses;
		}

		foreach ( (array) $indexes as $index ) {
			$index_data = (array) $index;
			if ( isset( $index_data['Key_name'] ) && array_key_exists( $index_data['Key_name'], $statuses ) ) {
				$statuses[ $index_data['Key_name'] ] = true;
			}
		}

		self::$index_status_cache_key = $cache_key;
		self::$index_status_cache     = $statuses;
		update_option(
			self::INDEX_STATUS_OPTION,
			array(
				'key'       => $cache_key,
				'generated' => time(),
				'indexes'   => $statuses,
			),
			true
		);

		return $statuses;
	}

	/**
	 * Clear the FULLTEXT index status cache.
	 *
	 * @since 4.4.3
	 */
	public static function clear_index_status_cache() {
		self::$index_status_cache_key = null;
		self::$index_status_cache     = null;
		delete_option( self::INDEX_STATUS_OPTION );
	}

	/**
	 * Check if the Better Search table is installed.
	 *
	 * @since 4.0.2
	 *
	 * @param string $table_name Table name.
	 * @param bool   $use_cache Whether to use the persistent table-status cache.
	 * @return bool True if the table exists, false otherwise.
	 */
	public static function is_table_installed( $table_name, $use_cache = true ) {
		global $wpdb;

		if ( $use_cache ) {
			if (
				null === self::$table_status_cache ||
				( self::$table_status_cache['prefix'] ?? null ) !== $wpdb->prefix
			) {
				self::$table_status_cache = self::get_table_status_cache();
			}

			if ( array_key_exists( $table_name, self::$table_status_cache['tables'] ) ) {
				return self::$table_status_cache['tables'][ $table_name ];
			}
		}

		$is_installed = $wpdb->get_var( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->prepare(
				'SHOW TABLES LIKE %s',
				$wpdb->esc_like( $table_name )
			)
		) === $table_name;

		if ( ! empty( $wpdb->last_error ) ) {
			self::clear_table_status_cache();

			return false;
		}

		if ( ! $use_cache ) {
			return $is_installed;
		}

		self::$table_status_cache['tables'][ $table_name ] = $is_installed;
		self::$table_status_cache['version']               = BETTER_SEARCH_DB_VERSION;
		self::$table_status_cache['prefix']                = $wpdb->prefix;
		self::$table_status_cache['generated']             = time();
		update_option( self::TABLE_STATUS_OPTION, self::$table_status_cache, true );

		return $is_installed;
	}

	/**
	 * Get the cached status of local Better Search tables.
	 *
	 * @since 4.4.3
	 *
	 * @return array Table-status cache.
	 */
	private static function get_table_status_cache() {
		global $wpdb;

		$cache = get_option( self::TABLE_STATUS_OPTION, array() );

		if (
			! is_array( $cache ) ||
			( $cache['version'] ?? null ) !== BETTER_SEARCH_DB_VERSION ||
			( $cache['prefix'] ?? null ) !== $wpdb->prefix ||
			! isset( $cache['generated'], $cache['tables'] ) ||
			! is_array( $cache['tables'] ) ||
			( time() - (int) $cache['generated'] ) >= self::TABLE_STATUS_CACHE_TTL
		) {
			$cache = array(
				'version'   => BETTER_SEARCH_DB_VERSION,
				'prefix'    => $wpdb->prefix,
				'generated' => 0,
				'tables'    => array(),
			);
		}

		return $cache;
	}

	/**
	 * Clear the local table-status cache.
	 *
	 * @since 4.4.3
	 */
	public static function clear_table_status_cache() {
		self::$table_status_cache = null;
		delete_option( self::TABLE_STATUS_OPTION );
	}

	/**
	 * Get the network-wide status of Better Search tables.
	 *
	 * A single SHOW TABLES query discovers both Better Search table families. The result is
	 * persisted because the per-site network statistics code otherwise performs one metadata
	 * query for every site and table suffix on each request.
	 *
	 * @since 4.4.3
	 *
	 * @param bool $force Whether to bypass the persistent and in-request caches.
	 * @return array Network table-status cache.
	 */
	public static function get_network_table_status( $force = false ) {
		global $wpdb;

		if ( ! $force && null !== self::$network_table_status_cache ) {
			return self::$network_table_status_cache;
		}

		$cache = get_site_option( self::NETWORK_TABLE_STATUS_OPTION, array() );

		if (
			! $force &&
			is_array( $cache ) &&
			( $cache['version'] ?? null ) === BETTER_SEARCH_DB_VERSION &&
			( $cache['base_prefix'] ?? null ) === $wpdb->base_prefix &&
			isset( $cache['generated'], $cache['tables'] ) &&
			is_array( $cache['tables'] ) &&
			( time() - (int) $cache['generated'] ) < self::TABLE_STATUS_CACHE_TTL
		) {
			self::$network_table_status_cache = $cache;
			return $cache;
		}

		$tables = $wpdb->get_col( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->prepare(
				'SHOW TABLES LIKE %s',
				$wpdb->esc_like( $wpdb->base_prefix ) . '%bsearch%'
			)
		);

		if ( ! empty( $wpdb->last_error ) ) {
			self::clear_network_table_status_cache();

			return self::get_empty_network_table_status();
		}

		$cache = array(
			'version'     => BETTER_SEARCH_DB_VERSION,
			'base_prefix' => $wpdb->base_prefix,
			'generated'   => time(),
			'tables'      => array(
				self::$table_name       => array(),
				self::$table_name_daily => array(),
			),
		);

		foreach ( (array) $tables as $table ) {
			foreach ( array_keys( $cache['tables'] ) as $table_suffix ) {
				if ( substr( $table, -strlen( $table_suffix ) ) === $table_suffix ) {
					$cache['tables'][ $table_suffix ][ $table ] = true;
				}
			}
		}

		self::$network_table_status_cache = $cache;
		update_site_option( self::NETWORK_TABLE_STATUS_OPTION, $cache );

		return $cache;
	}

	/**
	 * Refresh the network-wide table-status cache from the database.
	 *
	 * This is intentionally a live check. It is used by the scheduled health check and
	 * diagnostic tools, while normal dashboard requests use the cached status.
	 *
	 * @since 4.4.3
	 *
	 * @return array Network table-status cache.
	 */
	public static function refresh_network_table_status() {
		$status = self::get_network_table_status( true );

		if ( ! empty( $status['generated'] ) ) {
			update_site_option( self::NETWORK_TABLE_HEALTH_OPTION, time() );
		}

		return $status;
	}

	/**
	 * Run the live network table check when the admin health interval has elapsed.
	 *
	 * @since 4.4.3
	 *
	 * @return array Network table-status cache.
	 */
	public static function maybe_refresh_network_table_status() {
		self::schedule_network_table_status_check();

		$last_check = (int) get_site_option( self::NETWORK_TABLE_HEALTH_OPTION, 0 );

		if ( $last_check > 0 && ( time() - $last_check ) < self::TABLE_STATUS_CACHE_TTL ) {
			return self::get_network_table_status();
		}

		return self::refresh_network_table_status();
	}

	/**
	 * Return the empty network table-status structure used when discovery fails.
	 *
	 * An empty result keeps network reports safe during a transient database failure and
	 * avoids persisting a false all-clear state over a previously valid cache.
	 *
	 * @since 4.4.3
	 *
	 * @return array Empty network table-status cache.
	 */
	private static function get_empty_network_table_status() {
		global $wpdb;

		return array(
			'version'     => BETTER_SEARCH_DB_VERSION,
			'base_prefix' => $wpdb->base_prefix,
			'generated'   => 0,
			'tables'      => array(
				self::$table_name       => array(),
				self::$table_name_daily => array(),
			),
		);
	}

	/**
	 * Schedule the network table-status health check if it is not already scheduled.
	 *
	 * @since 4.4.3
	 */
	public static function schedule_network_table_status_check() {
		if ( ! wp_next_scheduled( self::NETWORK_TABLE_STATUS_CRON ) ) {
			wp_schedule_event( time() + self::TABLE_STATUS_CACHE_TTL, 'daily', self::NETWORK_TABLE_STATUS_CRON );
		}
	}

	/**
	 * Unschedule the network table-status health check.
	 *
	 * @since 4.4.3
	 */
	public static function unschedule_network_table_status_check() {
		wp_clear_scheduled_hook( self::NETWORK_TABLE_STATUS_CRON );
	}

	/**
	 * Clear the network-wide table-status cache.
	 *
	 * @since 4.4.3
	 */
	public static function clear_network_table_status_cache() {
		self::$network_table_status_cache = null;

		if ( is_multisite() ) {
			delete_site_option( self::NETWORK_TABLE_STATUS_OPTION );
			delete_site_option( self::NETWORK_TABLE_HEALTH_OPTION );
		}
	}

	/**
	 * Create table if not exists.
	 *
	 * @since 4.2.0
	 *
	 * @param string $table_name Table name.
	 * @param string $sql        SQL to create the table.
	 */
	public static function maybe_create_table( $table_name, $sql ) {
		global $wpdb;
		if ( ! self::is_table_installed( $table_name, false ) ) {
			require_once ABSPATH . 'wp-admin/includes/upgrade.php';

			$show_errors = $wpdb->hide_errors();
			dbDelta( $sql );
			$wpdb->show_errors( $show_errors );
			self::clear_table_status_cache();
		}
	}

	/**
	 * Create tables.
	 *
	 * @since 4.2.0
	 */
	public static function create_tables() {
		global $wpdb;

		self::maybe_create_table( $wpdb->prefix . self::$table_name, self::create_full_table_sql() );
		self::maybe_create_table( $wpdb->prefix . self::$table_name_daily, self::create_daily_table_sql() );
		self::clear_network_table_status_cache();
	}

	/**
	 * Create full table sql.
	 *
	 * @since 4.2.0
	 *
	 * @return string SQL to create the full table.
	 */
	public static function create_full_table_sql() {
		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();
		$table_name      = $wpdb->prefix . self::$table_name;

		$sql = "CREATE TABLE {$table_name}" . // phpcs:ignore WordPress.DB.DirectDatabaseQuery.SchemaChange
		" (
			searchvar VARCHAR(100) NOT NULL,
			cntaccess int NOT NULL,
			PRIMARY KEY  (searchvar)
		) $charset_collate;";

		return $sql;
	}

	/**
	 * Create full daily table sql.
	 *
	 * @since 4.2.0
	 *
	 * @return string SQL to create the daily table.
	 */
	public static function create_daily_table_sql() {
		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();
		$table_name      = $wpdb->prefix . self::$table_name_daily;

		$sql = "CREATE TABLE {$table_name}" . // phpcs:ignore WordPress.DB.DirectDatabaseQuery.SchemaChange
		" (
			searchvar VARCHAR(100) NOT NULL,
			cntaccess int NOT NULL,
			dp_date date NOT NULL,
			PRIMARY KEY  (searchvar, dp_date),
			KEY dp_date (dp_date)
		) $charset_collate;";

		return $sql;
	}

	/**
	 * Recreate a table.
	 *
	 * This method recreates a table by creating a backup, dropping the original table,
	 * and then creating a new table with the original name and inserting the data from the backup.
	 *
	 * @since 4.2.0
	 *
	 * @param string $table_name        The name of the table to recreate.
	 * @param string $create_table_sql  The SQL statement to create the new table.
	 * @param bool   $backup            Whether to backup the table or not.
	 * @param array  $fields            The fields to include in the temporary table and on duplicate key code.
	 * @param array  $group_by_fields   The fields to group by in the temporary table.
	 *
	 * @return bool|\WP_Error True if recreated, error message if failed.
	 */
	public static function recreate_table( $table_name, $create_table_sql, $backup = true, $fields = array( 'searchvar', 'cntaccess' ), $group_by_fields = array( 'searchvar' ) ) {
		global $wpdb;

		$backup_table_name = $backup ? $table_name . '_backup' : $table_name . '_temp';
		$success           = false;

		$fields_sql          = implode( ', ', $fields );
		$fields_sql_with_sum = str_replace( 'cntaccess', 'SUM(cntaccess) as cntaccess', $fields_sql );
		$group_by_sql        = implode( ', ', $group_by_fields );

		if ( $backup ) {
			$success = $wpdb->query( "CREATE TABLE $backup_table_name LIKE $table_name" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.SchemaChange,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			if ( false !== $success ) {
				$success = $wpdb->query( "INSERT INTO $backup_table_name SELECT * FROM $table_name" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			} else {
				/* translators: 1: Site number, 2: Error message */
				return new \WP_Error( 'bsearch_database_backup_failed', sprintf( esc_html__( 'Database backup failed on site %1$s. Error message: %2$s', 'better-search' ), get_site_url(), $wpdb->last_error ) );
			}
		} else {
			$success = $wpdb->query( "CREATE TEMPORARY TABLE $backup_table_name AS SELECT $fields_sql_with_sum FROM $table_name GROUP BY $group_by_sql" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.SchemaChange,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		}

		if ( false !== $success ) {
			$wpdb->query( "DROP TABLE IF EXISTS $table_name" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.SchemaChange,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			self::clear_table_status_cache();
			self::maybe_create_table( $table_name, $create_table_sql );
			$insert_fields_sql = 'bs.' . implode( ', bs.', $fields );

			$success = $wpdb->query( "INSERT INTO $table_name ($fields_sql) SELECT $insert_fields_sql FROM $backup_table_name AS bs ON DUPLICATE KEY UPDATE $table_name.cntaccess = $table_name.cntaccess + VALUES(cntaccess)" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.InterpolatedNotPrepared

			if ( false === $success ) {
				/* translators: 1: Site number, 2: Error message */
				return new \WP_Error( 'bsearch_database_insert_failed', sprintf( esc_html__( 'Database insert failed on site %1$s. Error message: %2$s', 'better-search' ), get_site_url(), $wpdb->last_error ) );
			}
		}

		if ( ! $backup ) {
			$wpdb->query( "DROP TABLE $backup_table_name" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.SchemaChange,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		}

		self::clear_network_table_status_cache();

		return $success;
	}

	/**
	 * Recreate overall table.
	 *
	 * @since 4.2.0
	 *
	 * @param bool $backup Whether to backup the table or not.
	 *
	 * @return bool|\WP_Error True if recreated, error message if failed.
	 */
	public static function recreate_overall_table( $backup = true ) {
		global $wpdb;
		return self::recreate_table(
			$wpdb->prefix . self::$table_name,
			self::create_full_table_sql(),
			$backup
		);
	}

	/**
	 * Recreate daily table.
	 *
	 * @since 4.2.0
	 *
	 * @param bool $backup Whether to backup the table or not.
	 *
	 * @return bool|\WP_Error True if recreated, error message if failed.
	 */
	public static function recreate_daily_table( $backup = true ) {
		global $wpdb;
		return self::recreate_table(
			$wpdb->prefix . self::$table_name_daily,
			self::create_daily_table_sql(),
			$backup,
			array( 'searchvar', 'cntaccess', 'dp_date' ),
			array( 'searchvar', 'dp_date' )
		);
	}
}
