<?php
/**
 * Better Search Tracking function
 *
 * @package Better_Search
 */

// If this file is called directly, then abort execution.
if ( ! defined( 'WPINC' ) ) {
	die( "Aren't you supposed to come here via WP-Admin?" );
}

/**
 * Function to update search count.
 *
 * @since 2.2.4
 */
function bsearch_enqueue_scripts() {

	$include_code = true;

	if ( ! is_search() ) {
		$include_code = false;
	}

	$bpaged = ( isset( $_GET['bpaged'] ) ) ? absint( $_GET['bpaged'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

	if ( $bpaged || ! bsearch_get_option( 'track_popular' ) || is_paged() ) {
		$include_code = false;
	}

	$current_user_admin  = ( current_user_can( 'manage_options' ) ) ? true : false;  // Is the current user an admin?
	$current_user_editor = ( ( current_user_can( 'edit_others_posts' ) ) && ( ! current_user_can( 'manage_options' ) ) ) ? true : false;    // Is the current user pure editor?

	// If user is an admin.
	if ( ( $current_user_admin ) && ( ! bsearch_get_option( 'track_admins' ) ) ) {
		$include_code = false;
	}

	// If user is an editor.
	if ( ( $current_user_editor ) && ( ! bsearch_get_option( 'track_editors' ) ) ) {
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
		 * Filter the localize script arguments for the Top 10 tracker.
		 *
		 * @since 2.2.4
		 */
		$ajax_bsearch_tracker = apply_filters( 'bsearch_tracker_script_args', $ajax_bsearch_tracker );

		wp_enqueue_script( 'bsearch_tracker', plugins_url( 'includes/js/better-search-tracker.min.js', BETTER_SEARCH_PLUGIN_FILE ), array( 'jquery' ), '1.0', true );

		wp_localize_script( 'bsearch_tracker', 'ajax_bsearch_tracker', $ajax_bsearch_tracker );

	}

}
add_action( 'wp_enqueue_scripts', 'bsearch_enqueue_scripts' );


/**
 * Function to add additional queries to query_vars.
 *
 * @since   2.2.4
 *
 * @param   array $vars   Query variables array.
 * @return  array Query variables array with Top 10 parameters appended
 */
function bsearch_query_vars( $vars ) {
	// Add these to the list of queryvars that WP gathers.
	$vars[] = 'bsearch_search_query';

	/**
	 * Function to add additional queries to query_vars.
	 *
	 * @since   2.2.4
	 *
	 * @param array $vars Updated Query variables array with Top 10 queries added.
	 */
	return apply_filters( 'bsearch_query_vars', $vars );
}
add_filter( 'query_vars', 'bsearch_query_vars' );


/**
 * Parses the WordPress object to update/display the count.
 *
 * @since   2.2.4
 *
 * @param   object $wp WordPress object.
 */
function bsearch_parse_request( $wp ) {

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

		$search_query = isset( $wp->query_vars['bsearch_search_query'] ) ? rawurldecode( wp_kses( wp_unslash( $wp->query_vars['bsearch_search_query'] ), array() ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		$str = bsearch_update_count( $search_query );

		header( 'content-type: application/x-javascript' );
		echo esc_html( $str );

		// Stop anything else from loading as it is not needed.
		exit;

	} else {
		return;
	}
}
add_action( 'parse_request', 'bsearch_parse_request' );


/**
 * Function to update the count in the database.
 *
 * @since 2.2.4
 *
 * @param string $search_query Search Query.
 *
 * @return string Response on database update.
 */
function bsearch_update_count( $search_query ) {

	global $wpdb;

	$table_name       = $wpdb->prefix . 'bsearch';
	$table_name_daily = $wpdb->prefix . 'bsearch_daily';
	$search_query     = str_replace( '&quot;', '"', $search_query );
	$str              = '';

	if ( '' !== $search_query ) {
		$results = $wpdb->get_results( $wpdb->prepare( "SELECT searchvar, cntaccess FROM $table_name WHERE searchvar = %s LIMIT 1 ", $search_query ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
		$test    = 0;
		if ( $results ) {
			foreach ( $results as $result ) {
				$tt   = $wpdb->query( $wpdb->prepare( "UPDATE $table_name SET cntaccess = cntaccess + 1 WHERE searchvar = %s ", $result->searchvar ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
				$str .= ( false === $tt ) ? 'e_' : 's_' . $tt;
				$test = 1;
			}
		}
		if ( 0 === $test ) {
			$tt   = $wpdb->query( $wpdb->prepare( "INSERT INTO $table_name (searchvar, cntaccess) VALUES( %s, '1') ", $search_query ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
			$str .= ( false === $tt ) ? 'e_' : 's_' . $tt;
		}

		// Now update daily count.
		$current_date = gmdate( 'Y-m-d', ( time() + ( get_option( 'gmt_offset' ) * 3600 ) ) );

		$results = $wpdb->get_results( $wpdb->prepare( "SELECT searchvar, cntaccess, dp_date FROM $table_name_daily WHERE searchvar = %s AND dp_date = %s ", $search_query, $current_date ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
		$test    = 0;
		if ( $results ) {
			foreach ( $results as $result ) {
				$ttd  = $wpdb->query( $wpdb->prepare( "UPDATE $table_name_daily SET cntaccess = cntaccess + 1 WHERE searchvar = %s AND dp_date = %s ", $result->searchvar, $current_date ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
				$str .= ( false === $ttd ) ? '_e' : '_s' . $ttd;
				$test = 1;
			}
		}
		if ( 0 === $test ) {
			$ttd  = $wpdb->query( $wpdb->prepare( "INSERT INTO $table_name_daily (searchvar, cntaccess, dp_date) VALUES( %s, '1', %s )", $search_query, $current_date ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
			$str .= ( false === $ttd ) ? '_e' : '_s' . $ttd;
		}
	}

	/**
	 * Filter the response on database update.
	 *
	 * @since 2.2.4
	 *
	 * @param string $str Response string.
	 * @param int $search_query Search query.
	 */
	return apply_filters( 'bsearch_update_count', $str, $search_query );
}


