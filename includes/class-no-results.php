<?php
/**
 * Count of searches that returned no results.
 *
 * @package Better_Search
 */

namespace WebberZone\Better_Search;

use WebberZone\Better_Search\Util\Hook_Registry;

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Counts the searches that returned no results, one row per day (site time).
 *
 * Only the number of searches is stored here, never the search terms.
 *
 * @since 4.6.0
 */
class No_Results {

	/**
	 * Schema version of the table.
	 *
	 * @since 4.6.0
	 * @var string
	 */
	const DB_VERSION = '1.0';

	/**
	 * Option holding the installed schema version.
	 *
	 * @since 4.6.0
	 * @var string
	 */
	const DB_VERSION_OPTION = 'bsearch_no_results_daily_db_version';

	/**
	 * Table name without the prefix.
	 *
	 * @since 4.6.0
	 * @var string
	 */
	const TABLE = 'bsearch_no_results_daily';

	/**
	 * Constructor.
	 *
	 * @since 4.6.0
	 */
	public function __construct() {
		Hook_Registry::add_action( 'init', array( $this, 'maybe_install' ) );
	}

	/**
	 * Table name.
	 *
	 * @since 4.6.0
	 *
	 * @return string Table name with prefix.
	 */
	public static function table(): string {
		global $wpdb;

		return $wpdb->prefix . self::TABLE;
	}

	/**
	 * Create or update the table when the schema version changes.
	 *
	 * @since 4.6.0
	 */
	public function maybe_install(): void {
		if ( self::DB_VERSION !== get_option( self::DB_VERSION_OPTION ) ) {
			self::install();
		}
	}

	/**
	 * Create or update the table.
	 *
	 * @since 4.6.0
	 */
	public static function install(): void {
		global $wpdb;

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$table           = self::table();
		$charset_collate = $wpdb->get_charset_collate();

		dbDelta(
			"CREATE TABLE {$table} (
				dp_date date NOT NULL,
				cntaccess int(10) unsigned NOT NULL DEFAULT 0,
				PRIMARY KEY  (dp_date)
			) {$charset_collate};"
		);

		update_option( self::DB_VERSION_OPTION, self::DB_VERSION );
	}

	/**
	 * Token proving that a results page for a term was rendered with no results.
	 *
	 * It is the same for every visitor so that cached pages keep working, and differs for each site of a network.
	 *
	 * @since 4.6.0
	 *
	 * @param string $term Search term exactly as sent to the tracker.
	 * @return string Token.
	 */
	public static function get_token( string $term ): string {
		return wp_hash( 'bsearch_no_results|' . get_current_blog_id() . '|' . $term, 'nonce' );
	}

	/**
	 * Count a no-result search for today.
	 *
	 * @since 4.6.0
	 *
	 * @param string $term Cleaned search term. It is passed on to the action and not stored.
	 * @return bool True if counted.
	 */
	public static function record( string $term ): bool {
		global $wpdb;

		if ( '' === $term ) {
			return false;
		}

		$table = self::table();

		$result = $wpdb->query( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->prepare(
				"INSERT INTO {$table} (dp_date, cntaccess) VALUES (%s, 1) ON DUPLICATE KEY UPDATE cntaccess = cntaccess + 1", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				current_time( 'Y-m-d' )
			)
		);

		// A failed write usually means the table is missing; clearing the version recreates it on the next request.
		if ( false === $result ) {
			delete_option( self::DB_VERSION_OPTION );

			return false;
		}

		/**
		 * Fires when a search that returned no results is counted.
		 *
		 * @since 4.6.0
		 *
		 * @param string $term Cleaned search term.
		 */
		do_action( 'bsearch_no_result_search', $term );

		return true;
	}

	/**
	 * Number of no-result searches in a date range.
	 *
	 * @since 4.6.0
	 *
	 * @param array $args {
	 *     Optional. Query parameters.
	 *
	 *     @type string|null $from_date From date. A date/time string, or null for no lower bound.
	 *     @type string|null $to_date   To date. A date/time string, or null for no upper bound.
	 * }
	 * @return int Number of searches.
	 */
	public static function get_count( array $args = array() ): int {
		global $wpdb;

		$table = self::table();
		$where = self::get_date_where( $args );

		$show_errors = $wpdb->hide_errors();
		$count       = (int) $wpdb->get_var( "SELECT COALESCE(SUM(cntaccess), 0) FROM {$table} WHERE 1=1 {$where}" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$wpdb->show_errors( $show_errors );

		return $count;
	}

	/**
	 * Date conditions on a `dp_date` column.
	 *
	 * @since 4.6.0
	 *
	 * @param array $args Query parameters with optional `from_date` and `to_date`.
	 * @return string Prepared SQL fragment.
	 */
	public static function get_date_where( array $args ): string {
		global $wpdb;

		$where = '';
		if ( ! empty( $args['from_date'] ) ) {
			$where .= $wpdb->prepare( ' AND dp_date >= %s', gmdate( 'Y-m-d', strtotime( $args['from_date'] ) ) );
		}
		if ( ! empty( $args['to_date'] ) ) {
			$where .= $wpdb->prepare( ' AND dp_date <= %s', gmdate( 'Y-m-d', strtotime( $args['to_date'] ) ) );
		}

		return $where;
	}

	/**
	 * Delete every row.
	 *
	 * @since 4.6.0
	 */
	public static function clear(): void {
		global $wpdb;

		$table = self::table();

		$wpdb->query( "TRUNCATE TABLE {$table}" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		/**
		 * Fires after the count of no-result searches is cleared.
		 *
		 * @since 4.6.0
		 */
		do_action( 'bsearch_no_results_cleared' );
	}
}
