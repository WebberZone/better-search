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
 * Dashboard for Better Search.
 *
 * @since   1.0
 */
function bsearch_pop_dashboard() {

	echo get_bsearch_heatmap( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		array(
			'daily' => 0,
		)
	);

	if ( bsearch_get_option( 'show_credit' ) ) {
		echo '<br /><small>Powered by <a href="https://webberzone.com/plugins/better-search/">Better Search plugin</a></small>';
	}
}


/**
 * Dashboard for Daily Better Search.
 *
 * @since   1.0
 */
function bsearch_pop_daily_dashboard() {

	echo get_bsearch_heatmap( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		array(
			'daily' => 1,
		)
	);

	if ( bsearch_get_option( 'show_credit' ) ) {
		echo '<br /><small>Powered by <a href="https://webberzone.com/plugins/better-search/">Better Search plugin</a></small>';
	}
}


/**
 * Add the dashboard widgets.
 *
 * @since   1.3.3
 */
function bsearch_dashboard_setup() {
	wp_add_dashboard_widget( 'bsearch_pop_dashboard', __( 'Popular Searches', 'better-search' ), 'bsearch_pop_dashboard' );
	wp_add_dashboard_widget( 'bsearch_pop_daily_dashboard', __( 'Daily Popular Searches', 'better-search' ), 'bsearch_pop_daily_dashboard' );
}
add_action( 'wp_dashboard_setup', 'bsearch_dashboard_setup' );


