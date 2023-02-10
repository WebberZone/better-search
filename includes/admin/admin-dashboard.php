<?php
/**
 * Generates the dashboard widgets.
 *
 * @link  https://webberzone.com
 * @since 2.2.0
 *
 * @package Better_Search
 * @subpackage Admin/Dashboard
 */

// If this file is called directly, then abort execution.
if ( ! defined( 'WPINC' ) ) {
	die( "Aren't you supposed to come here via WP-Admin?" );
}

/**
 *  Create the Dashboard Widget and content of the Popular pages
 *
 * @since 3.2.0
 *
 * @param   bool $daily  Switch for Daily or Overall popular posts.
 * @return  string Better Search widget.
 */
function bsearch_dashboard_widget( $daily = false ) {

	$output = get_bsearch_heatmap( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		array(
			'daily' => $daily,
		)
	);

	$output .= '<div style="text-align:center;margin-top:10px;">';

	if ( $daily ) {
		$output .= '<a href="' . admin_url( 'admin.php?page=bsearch_popular_searches&orderby=daily_count&order=desc' ) . '">' . __( 'View all daily popular searches', 'top-10' ) . '</a>';
	} else {
		$output .= '<a href="' . admin_url( 'admin.php?page=bsearch_popular_searches' ) . '">' . __( 'View all popular searches', 'top-10' ) . '</a>';
	}

	$output .= '</div>';
	$output .= bsearch_get_credit_link();

	return $output;
}

/**
 * Dashboard for Better Search.
 *
 * @since 1.0
 */
function bsearch_pop_dashboard() {
	echo bsearch_dashboard_widget( false ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}


/**
 * Dashboard for Daily Better Search.
 *
 * @since 1.0
 */
function bsearch_pop_daily_dashboard() {
	echo bsearch_dashboard_widget( true ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}


/**
 * Add the dashboard widgets.
 *
 * @since 1.3.3
 */
function bsearch_dashboard_setup() {
	wp_add_dashboard_widget( 'bsearch_pop_dashboard', __( 'Popular Searches', 'better-search' ), 'bsearch_pop_dashboard' );
	wp_add_dashboard_widget( 'bsearch_pop_daily_dashboard', __( 'Daily Popular Searches', 'better-search' ), 'bsearch_pop_daily_dashboard' );
}
add_action( 'wp_dashboard_setup', 'bsearch_dashboard_setup' );
