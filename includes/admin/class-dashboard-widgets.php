<?php
/**
 * Dashboard widgets display.
 *
 * @package Better_Search
 */

namespace WebberZone\Better_Search\Admin;

use WebberZone\Better_Search\Util\Helpers;

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Admin Columns Class.
 *
 * @since 3.3.0
 */
class Dashboard_Widgets {

	/**
	 * Constructor class.
	 *
	 * @since 3.3.0
	 */
	public function __construct() {
		add_filter( 'wp_dashboard_setup', array( $this, 'wp_dashboard_setup' ) );
	}

	/**
	 * Function to add the widgets to the Dashboard.
	 *
	 * @since 3.3.0
	 */
	public function wp_dashboard_setup() {

		if ( ( current_user_can( 'manage_options' ) ) || ( \bsearch_get_option( 'show_count_non_admins' ) ) ) {
			wp_add_dashboard_widget(
				'bsearch_pop_dashboard',
				__( 'Popular Searches', 'better-search' ),
				array( $this, 'widget' ),
			);
			wp_add_dashboard_widget(
				'bsearch_pop_daily_dashboard',
				__( 'Daily Popular Searches', 'better-search' ),
				array( $this, 'widget_daily' ),
			);
		}
	}

	/**
	 *  Create the Dashboard Widget and content of the Popular searches
	 *
	 * @since 3.3.0
	 *
	 * @param   bool $daily  Switch for Daily or Overall popular searches.
	 * @return  string Formatted list of popular searches.
	 */
	public static function display( $daily = false ) {

		$output = get_bsearch_heatmap( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			array(
				'daily' => $daily,
			)
		);

		$output .= '<div style="text-align:center;margin-top:10px;">';

		if ( $daily ) {
			$output .= sprintf(
				'<a href="%s">%s</a>',
				admin_url( 'admin.php?page=bsearch_popular_searches&orderby=daily_count&order=desc' ),
				__( 'View all daily popular searches', 'better-search' )
			);
		} else {
			$output .= sprintf(
				'<a href="%s">%s</a>',
				admin_url( 'admin.php?page=bsearch_popular_searches' ),
				__( 'View all popular searches', 'better-search' )
			);
		}
		$output .= '</div>';
		$output .= Helpers::get_credit_link();

		return $output;
	}


	/**
	 * Widget for Popular Searches.
	 *
	 * @since 3.3.0
	 */
	public static function widget() {
		echo self::display( false ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}


	/**
	 * Widget for Daily Popular Searches.
	 *
	 * @since 3.3.0
	 */
	public static function widget_daily() {
		echo self::display( true ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}
