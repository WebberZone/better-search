<?php
/**
 * Functions controlling the tracker
 *
 * @package Better_Search
 */

namespace WebberZone\Better_Search;

use WebberZone\Better_Search\Util\Hook_Registry;
use WebberZone\Better_Search\Util\Helpers;

if ( ! defined( 'WPINC' ) ) {
	exit;
}

/**
 * Tracks search queries via the tracker endpoint and its AJAX handlers.
 *
 * @since 3.3.0
 */
class Tracker {

	/**
	 * Constructor class.
	 *
	 * @since 3.3.0
	 */
	public function __construct() {
		Hook_Registry::add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		Hook_Registry::add_action( 'wp_ajax_nopriv_bsearch_tracker', array( $this, 'tracker_parser' ) );
		Hook_Registry::add_action( 'wp_ajax_bsearch_tracker', array( $this, 'tracker_parser' ) );
	}

	/**
	 * Enqueues the scripts needed by Better Search.
	 *
	 * @since 1.9.7
	 * @return void
	 */
	public static function enqueue_scripts() {
		$include_code = is_search() && self::should_track();

		if ( $include_code ) {
			$search_query = rawurlencode( get_bsearch_query() );
			$home_url     = admin_url( 'admin-ajax.php' );

			/**
			 * Filter the URL of the tracker.
			 *
			 * Other tracker types can override the URL processed by the jQuery.post request
			 * The corresponding tracker can use the below variables or append their own to $ajax_bsearch_tracker
			 *
			 * @since 2.2.4
			 */
			$home_url = apply_filters( 'bsearch_tracker_url', $home_url );

			// Strip any query strings since we don't need them.
			$home_url = strtok( $home_url, '?' );

			$ajax_bsearch_tracker = array(
				'ajax_url'             => $home_url,
				'bsearch_search_query' => $search_query,
				'bsearch_rnd'          => wp_rand( 1, time() ),
			);

			global $wp_query;
			if ( $wp_query instanceof \WP_Query && 0 === (int) $wp_query->found_posts && 0 === (int) $wp_query->post_count ) {
				$ajax_bsearch_tracker['bsearch_no_results'] = 1;
				$ajax_bsearch_tracker['bsearch_token']      = No_Results::get_token( $search_query );
			}

			/**
			 * Filter the localize script arguments for the Better Search tracker.
			 *
			 * @since 2.2.4
			 */
			$ajax_bsearch_tracker = apply_filters( 'bsearch_tracker_script_args', $ajax_bsearch_tracker );

			wp_enqueue_script(
				'bsearch_tracker',
				plugins_url( 'includes/js/better-search-tracker.min.js', BETTER_SEARCH_PLUGIN_FILE ),
				array(),
				BETTER_SEARCH_VERSION,
				true
			);

			wp_localize_script( 'bsearch_tracker', 'ajax_bsearch_tracker', $ajax_bsearch_tracker );

		}
	}

	/**
	 * Whether the current request should have its search query tracked.
	 *
	 * Callers must establish that the request is a search themselves.
	 *
	 * @since 4.4.0
	 *
	 * @return bool Whether the search query should be tracked.
	 */
	public static function should_track() {
		if ( ! \bsearch_get_option( 'track_popular' ) ) {
			return false;
		}

		if ( \bsearch_get_option( 'no_bots' ) && Helpers::is_bot() ) {
			return false;
		}

		$bpaged = ( isset( $_GET['bpaged'] ) ) ? absint( $_GET['bpaged'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		if ( $bpaged || is_paged() ) {
			return false;
		}

		$current_user_admin  = ( current_user_can( 'manage_options' ) ) ? true : false;  // Is the current user an admin?
		$current_user_editor = ( ( current_user_can( 'edit_others_posts' ) ) && ( ! current_user_can( 'manage_options' ) ) ) ? true : false;    // Is the current user pure editor?

		// If user is an admin.
		if ( ( $current_user_admin ) && ( ! \bsearch_get_option( 'track_admins' ) ) ) {
			return false;
		}

		// If user is an editor.
		if ( ( $current_user_editor ) && ( ! \bsearch_get_option( 'track_editors' ) ) ) {
			return false;
		}

		/**
		 * Filter whether the current request should have its search query tracked.
		 *
		 * @since 4.4.0
		 *
		 * @param bool $should_track Whether the search query should be tracked.
		 */
		return (bool) apply_filters( 'bsearch_should_track', true );
	}

	/**
	 * Parse the ajax response.
	 *
	 * @since 2.4.0
	 */
	public static function tracker_parser() {

		$search_query = isset( $_POST['bsearch_search_query'] ) && is_string( $_POST['bsearch_search_query'] ) ? wp_unslash( $_POST['bsearch_search_query'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$debug        = isset( $_POST['bsearch_debug'] ) ? absint( $_POST['bsearch_debug'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Missing

		$token = isset( $_POST['bsearch_token'] ) && is_string( $_POST['bsearch_token'] ) ? sanitize_text_field( wp_unslash( $_POST['bsearch_token'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing

		// The token is issued only on a results page that had no results, so browsers cannot flag other terms.
		$no_results = ! empty( $_POST['bsearch_no_results'] ) && '' !== $token && hash_equals( No_Results::get_token( $search_query ), $token ); // phpcs:ignore WordPress.Security.NonceVerification.Missing

		$str = self::update_count( $search_query, $no_results );

		if ( 1 === $debug ) {
			header( 'Content-Type: text/plain; charset=UTF-8' );
			echo $str; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			wp_die();
		}

		wp_die( '', '', array( 'response' => 200 ) );
	}

	/**
	 * Function to update the count in the database.
	 *
	 * @since 3.3.0
	 * @since 4.6.0 Added $no_results parameter.
	 *
	 * @param mixed $search_query Search Query.
	 * @param bool  $no_results   Whether the search returned no results.
	 * @return string Response on database update.
	 */
	public static function update_count( $search_query, $no_results = false ) {

		global $wpdb;

		$table_name       = $wpdb->prefix . 'bsearch';
		$table_name_daily = $wpdb->prefix . 'bsearch_daily';
		$str              = '';
		$search_query     = is_scalar( $search_query ) ? Helpers::clean_terms( (string) $search_query ) : '';

		global $bsearch_error;
		$banned_term = isset( $bsearch_error ) && $bsearch_error instanceof \WP_Error && in_array( 'bsearch_banned', $bsearch_error->get_error_codes(), true );

		if ( '' !== $search_query && ! $banned_term && mb_strlen( $search_query ) <= 100 && self::should_track() && ! self::is_rate_limited() ) {
			$bst = $wpdb->query( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
				$wpdb->prepare(
					"INSERT INTO $table_name (searchvar, cntaccess) VALUES (%s, 1) ON DUPLICATE KEY UPDATE cntaccess = cntaccess + 1", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
					$search_query
				)
			); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching

			$str .= ( false === $bst ) ? ' bst_error' : ' bst_' . $bst;

			// Now update daily count.
			$current_date = gmdate( 'Y-m-d', ( time() + ( get_option( 'gmt_offset' ) * 3600 ) ) );

			$bsd = $wpdb->query( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
				$wpdb->prepare(
					"INSERT INTO $table_name_daily (searchvar, cntaccess, dp_date) VALUES (%s, 1, %s) ON DUPLICATE KEY UPDATE cntaccess = cntaccess + 1", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
					$search_query,
					$current_date
				)
			); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching

			$str .= ( false === $bsd ) ? ' bsd_error' : ' bsd_' . $bsd;

			if ( $no_results ) {
				$str .= No_Results::record( $search_query ) ? ' bsn_1' : ' bsn_error';
			}
		}

		/**
		 * Filter the response on database update.
		 *
		 * @since 2.2.4
		 *
		 * @param string $str           Response string.
		 * @param string $search_query  Search query.
		 */
		return apply_filters( 'bsearch_update_count', $str, $search_query );
	}

	/**
	 * Enforces a site-wide search tracking budget without storing visitor identifiers.
	 *
	 * @since 4.6.0
	 *
	 * @return bool Whether the tracking budget has been reached.
	 */
	private static function is_rate_limited() {
		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			return false;
		}

		$limit = min( 1000000, max( 1, absint( apply_filters( 'bsearch_tracker_rate_limit_per_minute', bsearch_get_option( 'tracker_limit', 20 ) ) ) ) );
		$slot  = intdiv( time(), MINUTE_IN_SECONDS );

		global $wpdb;

		if ( ! empty( $wpdb->is_mysql ) ) {
			$option_name = 'bsearch_tracker_rate_limit';
			$result      = $wpdb->query( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
				$wpdb->prepare(
					"INSERT INTO {$wpdb->options} (option_name, option_value, autoload) VALUES (%s, CONCAT(%d, ':1'), 'no') ON DUPLICATE KEY UPDATE option_value = CASE WHEN CAST(SUBSTRING_INDEX(option_value, ':', 1) AS UNSIGNED) != %d THEN CONCAT(%d, ':1') WHEN CAST(SUBSTRING_INDEX(option_value, ':', -1) AS UNSIGNED) < %d THEN CONCAT(%d, ':', CAST(SUBSTRING_INDEX(option_value, ':', -1) AS UNSIGNED) + 1) ELSE option_value END",
					$option_name,
					$slot,
					$slot,
					$slot,
					$limit,
					$slot
				)
			);

			wp_cache_delete( $option_name, 'options' );

			return false === $result || 0 === (int) $wpdb->rows_affected;
		}

		$key   = 'bsearch_tracker_rate_' . $slot;
		$count = (int) get_transient( $key );

		if ( $count >= $limit ) {
			return true;
		}

		set_transient( $key, $count + 1, MINUTE_IN_SECONDS );

		return false;
	}
}
