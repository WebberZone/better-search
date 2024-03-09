<?php
/**
 * Functions controlling the tracker
 *
 * @package Better_Search
 */

namespace WebberZone\Better_Search;

use WebberZone\Better_Search\Util\Helpers;

if ( ! defined( 'WPINC' ) ) {
	exit;
}

/**
 * Admin Columns Class.
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
		add_action( 'parse_request', array( $this, 'parse_request' ) );
		add_filter( 'query_vars', array( $this, 'query_vars' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'wp_ajax_nopriv_bsearch_tracker', array( $this, 'tracker_parser' ) );
		add_action( 'wp_ajax_bsearch_tracker', array( $this, 'tracker_parser' ) );
	}

	/**
	 * Enqueues the scripts needed by Top 10.
	 *
	 * @since 1.9.7
	 * @return void
	 */
	public static function enqueue_scripts() {
		$include_code = true;

		if ( ! is_search() ) {
			$include_code = false;
		}

		$bpaged = ( isset( $_GET['bpaged'] ) ) ? absint( $_GET['bpaged'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		if ( $bpaged || ! \bsearch_get_option( 'track_popular' ) || is_paged() ) {
			$include_code = false;
		}

		$current_user_admin  = ( current_user_can( 'manage_options' ) ) ? true : false;  // Is the current user an admin?
		$current_user_editor = ( ( current_user_can( 'edit_others_posts' ) ) && ( ! current_user_can( 'manage_options' ) ) ) ? true : false;    // Is the current user pure editor?

		// If user is an admin.
		if ( ( $current_user_admin ) && ( ! \bsearch_get_option( 'track_admins' ) ) ) {
			$include_code = false;
		}

		// If user is an editor.
		if ( ( $current_user_editor ) && ( ! \bsearch_get_option( 'track_editors' ) ) ) {
			$include_code = false;
		}

		if ( $include_code ) {
			$search_query = rawurlencode( get_bsearch_query() );
			$home_url     = home_url( '/' );

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
	 * Function to add additional queries to query_vars.
	 *
	 * @since   2.0.0
	 *
	 * @param   array $vars   Query variables array.
	 * @return  array Query variables array with Top 10 parameters appended
	 */
	public static function query_vars( $vars ) {
		// Add these to the list of queryvars that WP gathers.
		$vars[] = 'bsearch_search_query';

		/**
		 * Function to add additional queries to query_vars.
		 *
		 * @since 2.2.4
		 *
		 * @param array $vars Updated Query variables array with Better Search queries added.
		 */
		return apply_filters( 'bsearch_query_vars', $vars );
	}

	/**
	 * Parses the WordPress object to update/display the count.
	 *
	 * @since   2.0.0
	 *
	 * @param \WP $wp Current WordPress environment instance.
	 */
	public static function parse_request( $wp ) {

		if ( empty( $wp ) ) {
			global $wp;
		}

		if ( ! isset( $wp->query_vars ) || ! is_array( $wp->query_vars ) ) {
			return;
		}

		if ( array_key_exists( 'bsearch_search_query', $wp->query_vars ) && empty( $wp->query_vars['bsearch_search_query'] ) ) {
			exit;
		}

		if ( array_key_exists( 'bsearch_search_query', $wp->query_vars ) && ! empty( $wp->query_vars['bsearch_search_query'] ) ) {

			$search_query = rawurldecode( wp_kses_data( wp_unslash( $wp->query_vars['bsearch_search_query'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

			$str = self::update_count( $search_query );

			// If the debug parameter is set then we output $str else we send a No Content header.
			if ( array_key_exists( 'bsearch_debug', $wp->query_vars ) && 1 === absint( $wp->query_vars['bsearch_debug'] ) ) {
				header( 'content-type: application/x-javascript' );
				wp_send_json( $str );
			} else {
				header( 'HTTP/1.0 204 No Content' );
				header( 'Cache-Control: max-age=15, s-maxage=0' );
			}

			// Stop anything else from loading as it is not needed.
			exit;

		} else {
			return;
		}
	}

	/**
	 * Parse the ajax response.
	 *
	 * @since 2.4.0
	 */
	public static function tracker_parser() {

		$search_query = isset( $_POST['bsearch_search_query'] ) ? rawurldecode( wp_kses_data( wp_unslash( $_POST['bsearch_search_query'] ) ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$debug        = isset( $_POST['bsearch_debug'] ) ? absint( $_POST['bsearch_debug'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Missing

		$str = self::update_count( $search_query );

		// If the debug parameter is set then we output $str else we send a No Content header.
		if ( 1 === $debug ) {
			echo esc_html( $str );
		} else {
			header( 'HTTP/1.0 204 No Content' );
			header( 'Cache-Control: max-age=15, s-maxage=0' );
		}

		wp_die();
	}

	/**
	 * Function to update the count in the database.
	 *
	 * @since 3.3.0
	 *
	 * @param string $search_query Search Query.
	 * @return string Response on database update.
	 */
	public static function update_count( $search_query ) {

		global $wpdb;

		$table_name       = $wpdb->prefix . 'bsearch';
		$table_name_daily = $wpdb->prefix . 'bsearch_daily';
		$search_query     = str_replace( '&quot;', '"', $search_query );
		$str              = '';

		if ( '' !== $search_query ) {
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
}
