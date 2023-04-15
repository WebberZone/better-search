<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link  https://webberzone.com
 * @since 2.2.0
 *
 * @package    Better Search
 * @subpackage Admin
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}


/**
 * Creates the admin submenu pages under the Downloads menu and assigns their
 * links to global variables
 *
 * @since 2.2.0
 *
 * @global $bsearch_settings_page
 * @return void
 */
function bsearch_add_admin_pages_links() {
	global $bsearch_settings_page, $bsearch_settings_tools_help, $bsearch_settings_popular_searches, $bsearch_settings_popular_searches_daily;

	$bsearch_settings_page = add_menu_page( esc_html__( 'Better Search Settings', 'better-search' ), esc_html__( 'Better Search', 'better-search' ), 'manage_options', 'bsearch_options_page', 'bsearch_options_page', 'dashicons-search' );
	add_action( "load-$bsearch_settings_page", 'bsearch_settings_help' ); // Load the settings contextual help.

	$plugin_page = add_submenu_page( 'bsearch_options_page', esc_html__( 'Better Search Settings', 'better-search' ), esc_html__( 'Settings', 'better-search' ), 'manage_options', 'bsearch_options_page', 'bsearch_options_page' );

	// Initialise Better Search Statistics pages.
	$bsearch_stats_screen = new Better_Search_Statistics();

	$bsearch_settings_popular_searches = add_submenu_page( 'bsearch_options_page', __( 'Better Search Popular Searches', 'better-search' ), __( 'Popular Searches', 'better-search' ), 'manage_options', 'bsearch_popular_searches', array( $bsearch_stats_screen, 'plugin_settings_page' ) );
	add_action( "load-$bsearch_settings_popular_searches", array( $bsearch_stats_screen, 'screen_option' ) );

	$bsearch_settings_popular_searches_daily = add_submenu_page( 'bsearch_options_page', __( 'Better Search Daily Popular Searches', 'better-search' ), __( 'Daily Popular Searches', 'better-search' ), 'manage_options', 'bsearch_popular_searches&orderby=daily_count&order=desc', array( $bsearch_stats_screen, 'plugin_settings_page' ) );
	add_action( "load-$bsearch_settings_popular_searches_daily", array( $bsearch_stats_screen, 'screen_option' ) );

	// Add links to Tools pages.
	$bsearch_settings_tools_help = add_submenu_page( 'bsearch_options_page', esc_html__( 'Better Search Tools', 'better-search' ), esc_html__( 'Tools', 'better-search' ), 'manage_options', 'bsearch_tools_page', 'bsearch_tools_page' );
	add_action( "load-$bsearch_settings_tools_help", 'bsearch_settings_tools_help' );
}
add_action( 'admin_menu', 'bsearch_add_admin_pages_links' );


/**
 * Add rating links to the admin dashboard
 *
 * @since 2.2.0
 *
 * @param string $footer_text The existing footer text.
 * @return string Updated Footer text
 */
function bsearch_admin_footer( $footer_text ) {

	if ( get_current_screen()->parent_base === 'bsearch_options_page' ) {

		$text = sprintf(
			/* translators: 1: Better Search website, 2: Plugin reviews link. */
			__( 'Thank you for using <a href="%1$s" target="_blank">Better Search</a>! Please <a href="%2$s" target="_blank">rate us</a> on <a href="%2$s" target="_blank">WordPress.org</a>', 'better-search' ),
			'https://webberzone.com/better-search',
			'https://wordpress.org/support/plugin/better-search/reviews/#new-post'
		);

		return str_replace( '</span>', '', $footer_text ) . ' | ' . $text . '</span>';

	} else {

		return $footer_text;

	}
}
add_filter( 'admin_footer_text', 'bsearch_admin_footer' );


/**
 * Adding WordPress plugin action links.
 *
 * @version 1.9.2
 *
 * @param   array $links Action links.
 * @return  array   Links array with our settings link added.
 */
function bsearch_plugin_actions_links( $links ) {

	return array_merge(
		array(
			'settings' => '<a href="' . admin_url( 'admin.php?page=bsearch_options_page' ) . '">' . __( 'Settings', 'better-search' ) . '</a>',
		),
		$links
	);
}
add_filter( 'plugin_action_links_' . plugin_basename( BETTER_SEARCH_PLUGIN_FILE ), 'bsearch_plugin_actions_links' );


/**
 * Add links to the plugin action row.
 *
 * @since   1.5
 *
 * @param   array $links Action links.
 * @param   array $file Plugin file name.
 * @return  array   Links array with our links added
 */
function bsearch_plugin_actions( $links, $file ) {
	$plugin = plugin_basename( BETTER_SEARCH_PLUGIN_FILE );

	if ( $file === $plugin ) {
		$links[] = '<a href="https://wordpress.org/support/plugin/better-search/">' . __( 'Support', 'better-search' ) . '</a>';
		$links[] = '<a href="https://ajaydsouza.com/donate/">' . __( 'Donate', 'better-search' ) . '</a>';
		$links[] = '<a href="https://github.com/WebberZone/better-search">' . __( 'Contribute', 'better-search' ) . '</a>';
	}
	return $links;
}
add_filter( 'plugin_row_meta', 'bsearch_plugin_actions', 10, 2 );


/**
 * Enqueue Admin JS
 *
 * @since 2.5.0
 *
 * @param string $hook The current admin page.
 */
function bsearch_load_admin_scripts( $hook ) {

	global $bsearch_settings_page, $bsearch_settings_tools_help, $bsearch_settings_popular_searches, $bsearch_settings_popular_searches_daily;

	wp_register_script(
		'better-search-admin-js',
		BETTER_SEARCH_PLUGIN_URL . 'includes/admin/js/admin-scripts.min.js',
		array( 'jquery', 'jquery-ui-tabs', 'jquery-ui-datepicker', 'wp-color-picker' ),
		BETTER_SEARCH_VERSION,
		true
	);
	wp_register_script(
		'better-search-suggest-js',
		BETTER_SEARCH_PLUGIN_URL . 'includes/admin/js/better-search-suggest.min.js',
		array( 'jquery', 'jquery-ui-autocomplete' ),
		BETTER_SEARCH_VERSION,
		true
	);

	if ( in_array( $hook, array( $bsearch_settings_page, $bsearch_settings_tools_help, $bsearch_settings_popular_searches, $bsearch_settings_popular_searches_daily ), true ) ) {

		wp_enqueue_script( 'better-search-admin-js' );
		wp_enqueue_script( 'better-search-suggest-js' );
		wp_enqueue_script( 'plugin-install' );
		add_thickbox();
		wp_enqueue_style( 'wp-color-picker' );

		wp_enqueue_code_editor(
			array(
				'type'       => 'text/html',
				'codemirror' => array(
					'indentUnit' => 2,
					'tabSize'    => 2,
				),
			)
		);
		wp_localize_script(
			'better-search-admin-js',
			'bsearch_admin_data',
			array(
				'security' => wp_create_nonce( 'bsearch-admin' ),
			)
		);

	}

	// Only enqueue the styles if this is a popular posts page.
	if ( in_array( $hook, array( $bsearch_settings_popular_searches, $bsearch_settings_popular_searches_daily ), true ) ) {
		wp_enqueue_style(
			'bsearch-admin-ui-css',
			BETTER_SEARCH_PLUGIN_URL . 'includes/admin/css/better-search-admin.min.css',
			false,
			BETTER_SEARCH_VERSION,
			false
		);
	}
}
add_action( 'admin_enqueue_scripts', 'bsearch_load_admin_scripts' );
