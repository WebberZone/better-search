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
				'<p>' . __( 'Enable the trackers and cache, configure basic tracker settings and uninstall settings.', 'better-search' ) . '</p>',
		)
	);

	$screen->add_help_tab(
		array(
			'id'      => 'bsearch-settings-counter',
			'title'   => __( 'Counter/Tracker', 'better-search' ),
			'content' =>
			'<p>' . __( 'This screen provides settings to tweak the display counter and the tracker.', 'better-search' ) . '</p>' .
				'<p>' . __( 'Choose where to display the counter and customize the text. Select the type of tracker and which user groups to track.', 'better-search' ) . '</p>',
		)
	);

	$screen->add_help_tab(
		array(
			'id'      => 'bsearch-settings-list',
			'title'   => __( 'Posts list', 'better-search' ),
			'content' =>
			'<p>' . __( 'This screen provides settings to tweak the output of the list of popular posts.', 'better-search' ) . '</p>' .
				'<p>' . __( 'Set the number of posts, which categories or posts to exclude, customize what to display and specific basic HTML markup used to create the posts.', 'better-search' ) . '</p>',
		)
	);

	$screen->add_help_tab(
		array(
			'id'      => 'bsearch-settings-thumbnail',
			'title'   => __( 'Thumbnail', 'better-search' ),
			'content' =>
			'<p>' . __( 'This screen provides settings to tweak the thumbnail that can be displayed for each post in the list.', 'better-search' ) . '</p>' .
				'<p>' . __( 'Set the location and size of the thumbnail. Additionally, you can choose additional sources for the thumbnail i.e. a meta field, first image or a default thumbnail when nothing is available.', 'better-search' ) . '</p>',
		)
	);

	$screen->add_help_tab(
		array(
			'id'      => 'bsearch-settings-styles',
			'title'   => __( 'Styles', 'better-search' ),
			'content' =>
			'<p>' . __( 'This screen provides options to control the look and feel of the popular posts list.', 'better-search' ) . '</p>' .
				'<p>' . __( 'Choose for default set of styles or add your own custom CSS to tweak the display of the posts.', 'better-search' ) . '</p>',
		)
	);

	$screen->add_help_tab(
		array(
			'id'      => 'bsearch-settings-maintenance',
			'title'   => __( 'Maintenance', 'better-search' ),
			'content' =>
			'<p>' . __( 'This screen provides options to control the maintenance cron.', 'better-search' ) . '</p>' .
				'<p>' . __( 'Choose how often to run maintenance and at what time of the day.', 'better-search' ) . '</p>',
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
