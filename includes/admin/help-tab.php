<?php
/**
 * Help tab.
 *
 * Functions to generated the help tab on the Settings page.
 *
 * @link  https://webberzone.com
 * @since 2.2.0
 *
 * @package Better Search
 * @subpackage Admin/Help
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Generates the settings help page.
 *
 * @since 2.2.0
 */
function bsearch_settings_help() {
	global $bsearch_settings_page;

	$screen = get_current_screen();

	if ( $screen->id !== $bsearch_settings_page ) {
		return;
	}

	$screen->set_help_sidebar(
		/* translators: 1: Support link. */
		'<p>' . sprintf( __( 'For more information or how to get support visit the <a href="%1$s">WebberZone support site</a>.', 'better-search' ), esc_url( 'https://webberzone.com/support/' ) ) . '</p>' .
		/* translators: 1: Forum link. */
		'<p>' . sprintf( __( 'Support queries should be posted in the <a href="%1$s">WordPress.org support forums</a>.', 'better-search' ), esc_url( 'https://wordpress.org/support/plugin/better-search' ) ) . '</p>' .
		'<p>' . sprintf(
			/* translators: 1: Github Issues link, 2: Github page. */
			__( '<a href="%1$s">Post an issue</a> on <a href="%2$s">GitHub</a> (bug reports only).', 'better-search' ),
			esc_url( 'https://github.com/WebberZone/better-search/issues' ),
			esc_url( 'https://github.com/WebberZone/better-search' )
		) . '</p>'
	);

	$screen->add_help_tab(
		array(
			'id'      => 'bsearch-settings-general',
			'title'   => __( 'General', 'better-search' ),
			'content' =>
			'<p>' . __( 'This screen provides the basic settings for configuring Better Search.', 'better-search' ) . '</p>' .
				'<p>' . __( 'Enable tracking, seamless mode and the cache, configure basic tracker and uninstall settings.', 'better-search' ) . '</p>',
		)
	);

	$screen->add_help_tab(
		array(
			'id'      => 'bsearch-settings-search',
			'title'   => __( 'Search', 'better-search' ),
			'content' =>
			'<p>' . __( 'This screen provides settings to tweak the search algorithm.', 'better-search' ) . '</p>' .
				'<p>' . __( 'Configure number of search results, enable FULLTEXT and BOOLEAN mode, tweak the weight of title and content and block words.', 'better-search' ) . '</p>',
		)
	);

	$screen->add_help_tab(
		array(
			'id'      => 'bsearch-settings-heatmap',
			'title'   => __( 'Heatmap', 'better-search' ),
			'content' =>
			'<p>' . __( 'This screen provides settings to tweak the output of the search heatmap to display popular searches.', 'better-search' ) . '</p>' .
				'<p>' . __( 'Configure title of the searches, period of trending searches, color and font sizes of the heatmap.', 'better-search' ) . '</p>',
		)
	);

	$screen->add_help_tab(
		array(
			'id'      => 'bsearch-settings-styles',
			'title'   => __( 'Styles', 'better-search' ),
			'content' =>
			'<p>' . __( 'This screen provides options to control the look and feel of the search page.', 'better-search' ) . '</p>' .
				'<p>' . __( 'Choose for default set of styles or add your own custom CSS to tweak the display of the search results page.', 'better-search' ) . '</p>',
		)
	);

	do_action( 'bsearch_settings_help', $screen );

}

/**
 * Generates the Tools help page.
 *
 * @since 2.2.0
 */
function bsearch_settings_tools_help() {
	global $bsearch_settings_tools_help;

	$screen = get_current_screen();

	if ( $screen->id !== $bsearch_settings_tools_help ) {
		return;
	}

	$screen->set_help_sidebar(
		/* translators: 1: Support link. */
		'<p>' . sprintf( __( 'For more information or how to get support visit the <a href="%1$s">WebberZone support site</a>.', 'better-search' ), esc_url( 'https://webberzone.com/support/' ) ) . '</p>' .
		/* translators: 1: Forum link. */
		'<p>' . sprintf( __( 'Support queries should be posted in the <a href="%1$s">WordPress.org support forums</a>.', 'better-search' ), esc_url( 'https://wordpress.org/support/plugin/better-search' ) ) . '</p>' .
		'<p>' . sprintf(
			/* translators: 1: Github Issues link, 2: Github page. */
			__( '<a href="%1$s">Post an issue</a> on <a href="%2$s">GitHub</a> (bug reports only).', 'better-search' ),
			esc_url( 'https://github.com/WebberZone/better-search/issues' ),
			esc_url( 'https://github.com/WebberZone/better-search' )
		) . '</p>'
	);

	$screen->add_help_tab(
		array(
			'id'      => 'bsearch-settings-general',
			'title'   => __( 'General', 'better-search' ),
			'content' =>
			'<p>' . __( 'This screen provides some tools that help maintain certain features of Better Search.', 'better-search' ) . '</p>' .
				'<p>' . __( 'Clear the cache, reset the popular posts tables plus some miscellaneous fixes for older versions of Better Search.', 'better-search' ) . '</p>',
		)
	);

	do_action( 'bsearch_settings_tools_help', $screen );
}
