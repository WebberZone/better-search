<?php
/**
 * Better Search Tracking function
 *
 * @package Better_Search
 */


/**
 * Function to update search count.
 *
 * @since	1.0
 *
 * @param	string $search_query   Search query
 * @return	string	Search tracker code
 */
function bsearch_increment_counter( $search_query ) {
	global $bsearch_url, $bsearch_settings;

	$output = '';

	$current_user = wp_get_current_user();
	$current_user_admin = ( current_user_can( 'manage_options' ) ) ? true : false;	// Is the current user an admin?
	$current_user_editor = ( ( current_user_can( 'edit_others_posts' ) ) && ( ! current_user_can( 'manage_options' ) ) ) ? true : false;	// Is the current user pure editor?

	$include_code = true;

	// If user is an admin
	if ( ( $current_user_admin ) && ( ! $bsearch_settings['track_admins'] ) ) {
		$include_code = false;
	}

	// If user is an editor
	if ( ( $current_user_editor ) && ( ! $bsearch_settings['track_editors'] ) ) {
		$include_code = false;
	}

	if ( $include_code ) {
		$output = '<script type="text/javascript" data-cfasync="false" src="' . $bsearch_url . '/includes/better-search-addcount.js.php?bsearch_id=' . $search_query . '"></script>';
	}

	/**
	 * Filter the search tracker code
	 *
	 * @since	2.0.0
	 *
	 * @param	string	$output			Formatted output string
	 * @param	string	$search_query	Search query
	 */
	return apply_filters( 'bsearch_increment_counter', $output, $search_query );
}



