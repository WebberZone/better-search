<?php
/**
 * Generates the settings page in the Admin
 *
 * @package Better_Search
 */

// If this file is called directly, then abort execution.
if ( ! defined( 'WPINC' ) ) {
	die( "Aren't you supposed to come here via WP-Admin?" );
}

/**
 * Better Search options.
 *
 * @since	1.0
 */
function bsearch_options() {

	global $wpdb;

	$bsearch_settings = bsearch_read_options();

	// Parse post types
	parse_str( $bsearch_settings['post_types'], $post_types );
	$wp_post_types = get_post_types( array(
		'public' => true,
	) );
	$posts_types_inc = array_intersect( $wp_post_types, $post_types );

	if ( ( isset( $_POST['bsearch_save'] ) ) && ( check_admin_referer( 'bsearch-plugin-settings' ) ) ) {

		/* General options */
		$bsearch_settings['seamless'] = isset( $_POST['seamless'] ) ? true : false;
		$bsearch_settings['track_popular'] = isset( $_POST['track_popular'] ) ? true : false;
		$bsearch_settings['track_admins'] = isset( $_POST['track_admins'] ) ? true : false;
		$bsearch_settings['track_editors'] = isset( $_POST['track_editors'] ) ? true : false;

		$bsearch_settings['meta_noindex'] = isset( $_POST['meta_noindex'] ) ? true : false;

		$bsearch_settings['show_credit'] = isset( $_POST['show_credit'] ) ? true : false;

		/* Search options */
		$bsearch_settings['limit'] = intval( $_POST['limit'] );

		$bsearch_settings['use_fulltext'] = isset( $_POST['use_fulltext'] ) ? true : false;
		$bsearch_settings['weight_content'] = intval( $_POST['weight_content'] );
		$bsearch_settings['weight_title'] = intval( $_POST['weight_title'] );
		$bsearch_settings['boolean_mode'] = isset( $_POST['boolean_mode'] ) ? true : false;

		// Update post types
		$wp_post_types	= get_post_types( array(
			'public' => true,
		) );
		$post_types_arr = ( is_array( $_POST['post_types'] ) ) ? $_POST['post_types'] : array( 'post' => 'post' );
		$post_types = array_intersect( $wp_post_types, $post_types_arr );
		$bsearch_settings['post_types'] = http_build_query( $post_types, '', '&' );
		$posts_types_inc = array_intersect( $wp_post_types, $post_types );

		$bsearch_settings['highlight'] = isset( $_POST['highlight'] ) ? true : false;

		$bsearch_settings['excerpt_length'] = intval( $_POST['excerpt_length'] );
		$bsearch_settings['link_new_window'] = isset( $_POST['link_new_window'] ) ? true : false;
		$bsearch_settings['link_nofollow'] = isset( $_POST['link_nofollow'] ) ? true : false;
		$bsearch_settings['include_thumb'] = isset( $_POST['include_thumb'] ) ? true : false;

		$bsearch_settings['badwords'] = wp_kses_post( $_POST['badwords'] );

		/* Heatmap options */
		$bsearch_settings['include_heatmap'] = isset( $_POST['include_heatmap'] ) ? true : false;
		$bsearch_settings['title'] = wp_kses_post( $_POST['title'] );
		$bsearch_settings['title_daily'] = wp_kses_post( $_POST['title_daily'] );
		$bsearch_settings['daily_range'] = intval( $_POST['daily_range'] );

		$bsearch_settings['heatmap_limit'] = $_POST['heatmap_limit'];
		$bsearch_settings['heatmap_smallest'] = $_POST['heatmap_smallest'];
		$bsearch_settings['heatmap_largest'] = $_POST['heatmap_largest'];
		$bsearch_settings['heatmap_cold'] = $_POST['heatmap_cold'];
		$bsearch_settings['heatmap_hot'] = $_POST['heatmap_hot'];
		$bsearch_settings['heatmap_before'] = $_POST['heatmap_before'];
		$bsearch_settings['heatmap_after'] = $_POST['heatmap_after'];

		/* Custom styles */
		$bsearch_settings['custom_CSS'] = wp_kses_post( $_POST['custom_CSS'] );

		update_option( 'ald_bsearch_settings', $bsearch_settings );

		$str = '<div id="message" class="updated fade"><p>'. __( 'Options saved successfully.', 'better-search' ) .'</p></div>';
		echo $str;
	}

	if ( ( isset( $_POST['bsearch_default'] ) ) && ( check_admin_referer( 'bsearch-plugin-settings' ) ) ) {
		delete_option( 'ald_bsearch_settings' );
		$bsearch_settings = bsearch_default_options();
		update_option( 'ald_bsearch_settings', $bsearch_settings );

		$str = '<div id="message" class="updated fade"><p>' . __( 'Options set to Default.', 'better-search' ) . '</p></div>';
		echo $str;
	}

	if ( ( isset( $_POST['bsearch_trunc_all'] ) ) && ( check_admin_referer( 'bsearch-plugin-settings' ) ) ) {
		bsearch_trunc_count( false );
		$str = '<div id="message" class="updated fade"><p>' . __( 'Popular searches count reset', 'better-search' ) . '</p></div>';
		echo $str;
	}

	if ( ( isset( $_POST['bsearch_trunc_daily'] ) ) && ( check_admin_referer( 'bsearch-plugin-settings' ) ) ) {
		bsearch_trunc_count( true );
		$str = '<div id="message" class="updated fade"><p>' . __( 'Daily popular searches count reset', 'better-search' ) . '</p></div>';
		echo $str;
	}

	if ( ( isset( $_POST['bsearch_recreate'] ) ) && ( check_admin_referer( 'bsearch-plugin-settings' ) ) ) {
		$wpdb->query( 'ALTER TABLE ' . $wpdb->posts . ' DROP INDEX bsearch' );
		$wpdb->query( 'ALTER TABLE ' . $wpdb->posts . ' DROP INDEX bsearch_title' );
		$wpdb->query( 'ALTER TABLE ' . $wpdb->posts . ' DROP INDEX bsearch_content' );

	    $wpdb->query( 'ALTER TABLE ' . $wpdb->posts . ' ADD FULLTEXT bsearch (post_title, post_content);' );
	    $wpdb->query( 'ALTER TABLE ' . $wpdb->posts . ' ADD FULLTEXT bsearch_title (post_title);' );
	    $wpdb->query( 'ALTER TABLE ' . $wpdb->posts . ' ADD FULLTEXT bsearch_content (post_content);' );

		$str = '<div id="message" class="updated fade"><p>'. __( 'Index recreated', 'better-search' ) .'</p></div>';
		echo $str;
	}

	if ( ( isset( $_POST['bsearch_delete_transients'] ) ) && ( check_admin_referer( 'bsearch-plugin-settings' ) ) ) {
		$wpdb->query( 'DELETE FROM ' . $wpdb->options . " WHERE option_name LIKE '_transient_bs_%'" );
		$wpdb->query( 'DELETE FROM ' . $wpdb->options . " WHERE option_name LIKE '_transient_timeout_bs_%'" );
	}

?>

<div class="wrap">
	<h2><?php _e( 'Better Search', 'better-search' ); ?></h2>

    <ul class="subsubsub">
		<?php
			/**
			 * Fires before the navigation bar in the Settings page
			 *
			 * @since	2.0.0
			 */
			do_action( 'bsearch_admin_nav_bar_before' )
		?>

	  	<li><a href="#genopdiv"><?php _e( 'General options', 'better-search' ); ?></a> | </li>
	  	<li><a href="#searchopdiv"><?php _e( 'Search options', 'better-search' ); ?></a> | </li>
	  	<li><a href="#heatmapopdiv"><?php _e( 'Heatmap options', 'better-search' ); ?></a> | </li>
	  	<li><a href="#customcssdiv"><?php _e( 'Custom styles', 'better-search' ); ?></a></li>

		<?php
			/**
			 * Fires after the navigation bar in the Settings page
			 *
			 * @since	2.0.0
			 */
			do_action( 'bsearch_admin_nav_bar_after' )
		?>
    </ul>

    <div id="poststuff">
    <div id="post-body" class="metabox-holder columns-2">
    <div id="post-body-content">
      <form method="post" id="bsearch_options" name="bsearch_options">

	    <div id="genopdiv" class="postbox"><div class="handlediv" title="<?php _e( 'Click to toggle', 'better-search' ); ?>"><br /></div>
	      <h3 class='hndle'><span><?php _e( 'General options', 'better-search' ); ?></span></h3>
          <div class="inside">
            <table class="form-table">
            <tbody>

				<?php
					/**
					 * Fires before General options block.
					 *
					 * @since	2.0.0
					 *
					 * @param	array	$bsearch_settings	Better Search settings array
					 */
					 do_action( 'bsearch_admin_general_options_before', $bsearch_settings );
				?>

				<tr><th scope="row"><label for="seamless"><?php _e( 'Enable seamless integration?', 'better-search' ); ?></label></th>
                    <td>
						<input type="checkbox" name="seamless" id="seamless" <?php if ( $bsearch_settings['seamless'] ) { echo 'checked="checked"'; } ?> />
						<p class="description"><?php _e( "Complete integration with your theme. Enabling this option will ignore better-search-template.php. It will continue to display the search results sorted by relevance, although it won't display the percentage relevance.", 'better-search' ); ?></p>
                    </td>
                </tr>

				<tr><th scope="row"><label for="track_popular"><?php _e( 'Enable search tracking?', 'better-search' ); ?></label></th>
                    <td>
						<input type="checkbox" name="track_popular" id="track_popular" <?php if ( $bsearch_settings['track_popular'] ) { echo 'checked="checked"'; } ?> />
						<p class="description"><?php _e( 'If you turn this off, then the plugin will no longer track and display the popular search terms.', 'better-search' ); ?></p>
                    </td>
                </tr>

				<tr><th scope="row"><label for="track_admins"><?php _e( 'Track visits of admins?', 'better-search' ); ?></label></th>
                    <td>
						<input type="checkbox" name="track_admins" id="track_admins" <?php if ( $bsearch_settings['track_admins'] ) { echo 'checked="checked"'; } ?> />
						<p class="description"><?php _e( 'Disabling this option will stop admin visits being tracked.', 'better-search' ); ?></p>
                    </td>
                </tr>

				<tr><th scope="row"><label for="track_editors"><?php _e( 'Track visits of Editors?', 'better-search' ); ?></label></th>
                    <td>
						<input type="checkbox" name="track_editors" id="track_editors" <?php if ( $bsearch_settings['track_editors'] ) { echo 'checked="checked"'; } ?> />
						<p class="description"><?php _e( 'Disabling this option will stop editor visits being tracked.', 'better-search' ); ?></p>
                    </td>
                </tr>

				<tr><th scope="row"><label for="meta_noindex"><?php _e( 'Stop search engines from indexing search results pages', 'better-search' ); ?></label></th>
                    <td>
						<input type="checkbox" name="meta_noindex" id="meta_noindex" <?php if ( $bsearch_settings['meta_noindex'] ) { echo 'checked="checked"'; } ?> />
						<p class="description"><?php _e( 'This is a recommended option to turn ON. Adds noindex,follow meta tag to the head of the page', 'better-search' ); ?></p>
                    </td>
                </tr>

				<tr><th scope="row"><label for="show_credit"><?php _e( 'Link to plugin homepage', 'better-search' ); ?></label></th>
                    <td>
						<input type="checkbox" name="show_credit" id="show_credit" <?php if ( $bsearch_settings['show_credit'] ) { echo 'checked="checked"'; } ?> />
						<p class="description"><?php _e( 'A nofollow link to the plugin is added as an extra list item to the list of popular searches. Not mandatory, but thanks if you do it!', 'better-search' ); ?></p>
                    </td>
                </tr>

				<?php
					/**
					 * Fires after General options block.
					 *
					 * @since	2.0.0
					 *
					 * @param	array	$bsearch_settings	Better Search settings array
					 */
					 do_action( 'bsearch_admin_general_options_after', $bsearch_settings );
				?>

                <tr>
                    <td scope="row" colspan="2">
						<input type="submit" name="bsearch_save" id="bsearch_genop_save" value="<?php _e( 'Save Options', 'better-search' ); ?>" class="button button-primary" />
                    </td>
                </tr>

            </tbody>
            </table>
          </div>
        </div>

	    <div id="searchopdiv" class="postbox"><div class="handlediv" title="<?php _e( 'Click to toggle', 'better-search' ); ?>"><br /></div>
	      <h3 class='hndle'><span><?php _e( 'Search result options', 'better-search' ); ?></span></h3>
          <div class="inside">
            <table class="form-table">
            <tbody>

				<?php
					/**
					 * Fires before Search options block.
					 *
					 * @since	2.0.0
					 *
					 * @param	array	$bsearch_settings	Better Search settings array
					 */
					 do_action( 'bsearch_admin_search_options_before', $bsearch_settings );
				?>

				<tr><th scope="row"><label for="limit"><?php _e( 'Number of Search Results per page', 'better-search' ); ?></label></th>
                    <td>
						<input type="textbox" name="limit" id="limit" value="<?php echo stripslashes( $bsearch_settings['limit'] ); ?>">
						<p class="description"><?php _e( 'This is the maximum number of search results that will be displayed per page by default', 'better-search' ); ?></p>
                    </td>
                </tr>

				<tr><th scope="row"><?php _e( 'Post types to include in results (including custom post types)', 'better-search' ); ?></th>
                    <td>
						<?php foreach ( $wp_post_types as $wp_post_type ) { ?>

							<input type="checkbox" name="post_types[]" value="<?php echo $wp_post_type; ?>" <?php if ( in_array( $wp_post_type, $posts_types_inc ) ) { echo 'checked="checked"'; } ?> />
							<?php echo $wp_post_type; ?>
                            <br />

						<?php } ?>
                    </td>
                </tr>

				<tr><th scope="row"><label for="use_fulltext"><?php _e( 'Enable mySQL FULLTEXT searching', 'better-search' ); ?></label></th>
                    <td>
						<input type="checkbox" name="use_fulltext" id="use_fulltext" <?php if ( $bsearch_settings['use_fulltext'] ) { echo 'checked="checked"'; } ?> />
						<p class="description"><?php _e( 'Disabling this option will no longer give relevancy based results', 'better-search' ); ?></p>
                    </td>
                </tr>

				<tr><th scope="row"><label for="boolean_mode"><?php _e( 'Activate BOOLEAN mode of FULLTEXT search', 'better-search' ); ?></label></th>
                    <td>
						<input type="checkbox" name="boolean_mode" id="boolean_mode" <?php if ( $bsearch_settings['boolean_mode'] ) { echo 'checked="checked"'; } ?> />
						<p class="description"><?php _e( 'Limits relevancy matches but removes several limitations of NATURAL LANGUAGE mode. <a href="https://dev.mysql.com/doc/refman/5.0/en/fulltext-boolean.html" target="_blank">Check the mySQL docs for further information on BOOLEAN indices</a>', 'better-search' ); ?></p>
                    </td>
                </tr>

				<tr><th scope="row"><label for="weight_title"><?php _e( 'Weight of the title', 'better-search' ); ?></label></th>
                    <td>
						<input type="textbox" name="weight_title" id="weight_title" value="<?php echo stripslashes( $bsearch_settings['weight_title'] ); ?>">
						<p class="description"><?php _e( 'Set this to a bigger number than the next option to prioritise the post title', 'better-search' ); ?></p>
                    </td>
                </tr>

				<tr><th scope="row"><label for="weight_content"><?php _e( 'Weight of the content', 'better-search' ); ?></label></th>
                    <td>
						<input type="textbox" name="weight_content" id="weight_content" value="<?php echo stripslashes( $bsearch_settings['weight_content'] ); ?>">
						<p class="description"><?php _e( 'Set this to a bigger number than the previous option to prioritise the post content', 'better-search' ); ?></p>
                    </td>
                </tr>

				<tr><th scope="row"><label for="highlight"><?php _e( 'Highlight search terms', 'better-search' ); ?></label></th>
                    <td>
						<input type="checkbox" name="highlight" id="highlight" <?php if ( $bsearch_settings['highlight'] ) { echo 'checked="checked"'; } ?> />
                        <p class="description">
							<?php _e( 'If enabled, the search terms are wrapped with the class <code>bsearch_highlight</code>. You will also need to add this CSS code under custom styles box below', 'better-search' ); ?>:
                            <br />
                            <code>.bsearch_highlight { background:#ffc; }</code>
                        </p>
                    </td>
                </tr>

				<tr><th scope="row"><label for="include_thumb"><?php _e( 'Include thumbnails in search results', 'better-search' ); ?></label></th>
                    <td>
						<input type="checkbox" name="include_thumb" id="include_thumb" <?php if ( $bsearch_settings['include_thumb'] ) { echo 'checked="checked"'; } ?> />
						<p class="description"><?php _e( 'Displays the featured image (post thumbnail) whenever available', 'better-search' ); ?></p>

						<?php if ( $bsearch_settings['seamless'] ) { ?>
							<p class="description" style="color: #f00"><?php _e( 'This setting does not apply because Seamless mode is activated.', 'better-search' ); ?></p>
						<?php } ?>

                    </td>
                </tr>

				<tr><th scope="row"><label for="excerpt_length"><?php _e( 'Length of excerpt (in words)', 'better-search' ); ?></label></th>
                    <td>
						<input type="textbox" name="excerpt_length" id="excerpt_length" value="<?php echo stripslashes( $bsearch_settings['excerpt_length'] ); ?>" />

						<?php if ( $bsearch_settings['seamless'] ) { ?>
							<p class="description" style="color: #f00"><?php _e( 'This setting does not apply because Seamless mode is activated.', 'better-search' ); ?></p>
						<?php } ?>

                    </td>
                </tr>

				<tr><th scope="row"><label for="badwords"><?php _e( 'Filter these words', 'better-search' ); ?></label></th>
                    <td>
						<textarea name="badwords" id="badwords" rows="15" cols="50"><?php echo stripslashes( $bsearch_settings['badwords'] ); ?></textarea>
						<p class="description"><?php _e( 'Words in this list will be stripped out of the search results. Enter these as a comma-separated list.', 'better-search' ); ?></p>
                    </td>
                </tr>

				<?php
					/**
					 * Fires after Search options block.
					 *
					 * @since	2.0.0
					 *
					 * @param	array	$bsearch_settings	Better Search settings array
					 */
					 do_action( 'bsearch_admin_search_options_after', $bsearch_settings );
				?>

                <tr>
                    <td scope="row" colspan="2">
						<input type="submit" name="bsearch_save" id="bsearch_searchop_save" value="<?php _e( 'Save Options', 'better-search' ); ?>" class="button button-primary" />
                    </td>
                </tr>

            </tbody>
            </table>
          </div>
        </div>

	    <div id="heatmapopdiv" class="postbox"><div class="handlediv" title="<?php _e( 'Click to toggle', 'better-search' ); ?>"><br /></div>
	      <h3 class='hndle'><span><?php _e( 'Heatmap options', 'better-search' ); ?></span></h3>
          <div class="inside">
            <table class="form-table">
            <tbody>

				<?php
					/**
					 * Fires before Heatmap options block.
					 *
					 * @since	2.0.0
					 *
					 * @param	array	$bsearch_settings	Better Search settings array
					 */
					 do_action( 'bsearch_admin_heatmap_options_before', $bsearch_settings );
				?>

				<tr><th scope="row"><label for="include_heatmap"><?php _e( 'Include heatmap on the search results', 'better-search' ); ?></label></th>
                    <td>
						<input type="checkbox" name="include_heatmap" id="include_heatmap" <?php if ( $bsearch_settings['include_heatmap'] ) { echo 'checked="checked"'; } ?> />
						<p class="description"><?php _e( 'This option will display the heatmaps at the bottom of the search results page. Display popular searches to your visitors', 'better-search' ); ?></p>

						<?php if ( $bsearch_settings['seamless'] ) { ?>
							<p class="description" style="color: #f00"><?php _e( 'This setting does not apply because Seamless mode is activated. You can use the Widget instead to display the popular searches', 'better-search' ); ?></p>
						<?php } ?>

                    </td>
                </tr>

				<tr><th scope="row"><label for="title"><?php _e( 'Title of Overall Popular Searches', 'better-search' ); ?></label></th>
                    <td>
						<input type="textbox" name="title" id="title" value="<?php echo stripslashes( $bsearch_settings['title'] ); ?>" style="width:250px">
                    </td>
                </tr>

				<tr><th scope="row"><label for="title_daily"><?php _e( 'Title of Daily Popular Searches', 'better-search' ); ?></label></th>
                    <td>
						<input type="textbox" name="title_daily" id="title_daily" value="<?php echo stripslashes( $bsearch_settings['title_daily'] ); ?>" style="width:250px">
                    </td>
                </tr>

				<tr><th scope="row"><label for="daily_range"><?php _e( 'Daily Popular should contain searches of how many days?', 'better-search' ); ?></label></th>
                    <td>
						<input type="textbox" name="daily_range" id="daily_range" size="3" value="<?php echo stripslashes( $bsearch_settings['daily_range'] ); ?>">
                    </td>
                </tr>

				<tr><th scope="row"><label for="heatmap_limit"><?php _e( 'Number of search terms to display', 'better-search' ); ?></label></th>
                    <td>
						<input type="textbox" name="heatmap_limit" id="heatmap_limit" value="<?php echo stripslashes( $bsearch_settings['heatmap_limit'] ); ?>">
                    </td>
                </tr>

				<tr><th scope="row"><label for="heatmap_smallest"><?php _e( 'Font size of least popular search term', 'better-search' ); ?></label></th>
                    <td>
						<input type="textbox" name="heatmap_smallest" id="heatmap_smallest" value="<?php echo stripslashes( $bsearch_settings['heatmap_smallest'] ); ?>">
                    </td>
                </tr>

				<tr><th scope="row"><label for="heatmap_largest"><?php _e( 'Font size of most popular search term', 'better-search' ); ?></label></th>
                    <td>
						<input type="textbox" name="heatmap_largest" id="heatmap_largest" value="<?php echo stripslashes( $bsearch_settings['heatmap_largest'] ); ?>">
                    </td>
                </tr>

				<tr><th scope="row"><label for="heatmap_cold"><?php _e( 'Color of least popular search term', 'better-search' ); ?></label></th>
                    <td>
						<input type="textbox" class="color" name="heatmap_cold" id="heatmap_cold" value="<?php echo stripslashes( $bsearch_settings['heatmap_cold'] ); ?>">
                    </td>
                </tr>

				<tr><th scope="row"><label for="heatmap_hot"><?php _e( 'Color of most popular search term', 'better-search' ); ?></label></th>
                    <td>
						<input type="textbox" class="color" name="heatmap_hot" id="heatmap_hot" value="<?php echo stripslashes( $bsearch_settings['heatmap_hot'] ); ?>">
                    </td>
                </tr>

				<tr><th scope="row"><label for="heatmap_before"><?php _e( 'Text to include before each search term in heatmap', 'better-search' ); ?></label></th>
                    <td>
						<input type="textbox" name="heatmap_before" id="heatmap_before" value="<?php echo stripslashes( $bsearch_settings['heatmap_before'] ); ?>">
                    </td>
                </tr>

				<tr><th scope="row"><label for="heatmap_after"><?php _e( 'Text to include after each search term in heatmap', 'better-search' ); ?></label></th>
                    <td>
						<input type="textbox" name="heatmap_after" id="heatmap_after" value="<?php echo stripslashes( $bsearch_settings['heatmap_after'] ); ?>">
                    </td>
                </tr>

				<tr><th scope="row"><label for="link_new_window"><?php _e( 'Open links in new window', 'better-search' ); ?></label></th>
                    <td>
						<input type="checkbox" name="link_new_window" id="link_new_window" <?php if ( $bsearch_settings['link_new_window'] ) { echo 'checked="checked"'; } ?> />
                    </td>
                </tr>

				<tr><th scope="row"><label for="link_nofollow"><?php _e( 'Add nofollow attribute to links', 'better-search' ); ?></label></th>
                    <td>
						<input type="checkbox" name="link_nofollow" id="link_nofollow" <?php if ( $bsearch_settings['link_nofollow'] ) { echo 'checked="checked"'; } ?> />
                    </td>
                </tr>

				<?php
					/**
					 * Fires after Heatmap options block.
					 *
					 * @since	2.0.0
					 *
					 * @param	array	$bsearch_settings	Better Search settings array
					 */
					 do_action( 'bsearch_admin_heatmap_options_after', $bsearch_settings );
				?>

                <tr>
                    <td scope="row" colspan="2">
						<input type="submit" name="bsearch_save" id="bsearch_hmop_save" value="<?php _e( 'Save Options', 'better-search' ); ?>" class="button button-primary" />
                    </td>
                </tr>

            </tbody>
            </table>
          </div>
        </div>

	    <div id="customcssdiv" class="postbox"><div class="handlediv" title="<?php _e( 'Click to toggle', 'better-search' ); ?>"><br /></div>
	      <h3 class='hndle'><span><?php _e( 'Custom CSS', 'better-search' ); ?></span></h3>
          <div class="inside">
            <table class="form-table">

				<?php
					/**
					 * Fires before Custom styles options block.
					 *
					 * @since	2.0.0
					 *
					 * @param	array	$bsearch_settings	Better Search settings array
					 */
					 do_action( 'bsearch_admin_custom_styles_before', $bsearch_settings );
				?>

                <tr>
					<th scope="row" colspan="2"><?php _e( 'Custom CSS to add to header', 'better-search' ); ?></th>
                </tr>

                <tr>
                    <td scope="row" colspan="2">
						<textarea name="custom_CSS" id="custom_CSS" rows="15" cols="80"><?php echo stripslashes( $bsearch_settings['custom_CSS'] ); ?></textarea>
						<p class="description"><?php _e( 'Do not include <code>style</code> tags. Check out the <a href="http://wordpress.org/extend/plugins/better-search/faq/" target="_blank">FAQ</a> for available CSS classes to style.', 'better-search' ); ?></p>
                    </td>
                </tr>

				<?php
					/**
					 * Fires after Custom styles options block.
					 *
					 * @since	2.0.0
					 *
					 * @param	array	$bsearch_settings	Better Search settings array
					 */
					 do_action( 'bsearch_admin_custom_styles_after', $bsearch_settings );
				?>

                <tr>
                    <td scope="row" colspan="2">
						<input type="submit" name="bsearch_save" id="bsearch_cssop_save" value="<?php _e( 'Save Options', 'better-search' ); ?>" class="button button-primary" />
                    </td>
                </tr>

            </table>
          </div>
        </div>

		<?php
			/**
			 * Fires after all the options are displayed. Allows a custom function to add a new option block.
			 *
			 * @since	2.0.0
			 */
			do_action( 'bsearch_admin_more_options' )
		?>

        <p>
			<input type="submit" name="bsearch_save" id="bsearch_save" value="<?php _e( 'Save Options', 'better-search' ); ?>" class="button button-primary" />
			<input type="submit" name="bsearch_default" id="bsearch_default" value="<?php _e( 'Default Options', 'better-search' ); ?>" class="button button-secondary" onclick="if ( ! confirm( '<?php _e( 'Do you want to set options to Default?', 'better-search' ); ?>' ) ) return false;" />
        </p>

		<?php wp_nonce_field( 'bsearch-plugin-settings' ); ?>

      </form>

      <form method="post" id="bsearch_reset_options" name="bsearch_reset_options">
	    <div id="resetopdiv" class="postbox"><div class="handlediv" title="<?php _e( 'Click to toggle', 'better-search' ); ?>"><br /></div>
	      <h3 class='hndle'><span><?php _e( 'Reset count and Maintenance', 'better-search' ); ?></span></h3>
          <div class="inside">
            <p class="description">
				<?php _e( 'This cannot be reversed. Make sure that your database has been backed up before proceeding', 'better-search' ); ?>
            </p>
            <p>
		      <input name="bsearch_trunc_all" type="submit" id="bsearch_trunc_all" value="<?php _e( 'Reset Popular Searches', 'better-search' ); ?>" class="button button-secondary" onclick="if ( ! confirm( '<?php _e( 'Are you sure you want to reset the popular posts?', 'better-search' ); ?>' ) ) return false;" />
		      <input name="bsearch_trunc_daily" type="submit" id="bsearch_trunc_daily" value="<?php _e( 'Reset Daily Popular Searches', 'better-search' ); ?>" class="button button-secondary" onclick="if ( ! confirm( '<?php _e( 'Are you sure you want to reset the daily popular posts?', 'better-search' ); ?>' ) ) return false;" />
			  <input name="bsearch_recreate" type="submit" id="bsearch_recreate" value="<?php _e( 'Recreate Index', 'better-search' ); ?>" class="button button-secondary" onclick="if ( ! confirm('<?php _e( 'Are you sure you want to recreate the index?', 'better-search' ); ?>') ) return false;" />
			  <input name="bsearch_delete_transients" type="submit" id="bsearch_delete_transients" value="<?php _e( 'Delete transients', 'better-search' ); ?>" class="button button-secondary" onclick="if ( ! confirm('<?php _e( 'Are you sure you want to delete all transients?', 'better-search' ); ?>' ) ) return false;" />
            </p>
          </div>
        </div>
		<?php wp_nonce_field( 'bsearch-plugin-settings' ); ?>
      </form>

    </div><!-- /post-body-content -->
    <div id="postbox-container-1" class="postbox-container">
      <div id="side-sortables" class="meta-box-sortables ui-sortable">
			<?php bsearch_admin_side(); ?>
      </div><!-- /side-sortables -->
    </div><!-- /postbox-container-1 -->
    </div><!-- /post-body -->
    <br class="clear" />
    </div><!-- /poststuff -->
</div><!-- /wrap -->

<?php
}


/**
 * Function to generate the right sidebar of the Settings and Admin popular posts pages.
 *
 * @since	1.3.3
 */
function bsearch_admin_side() {
?>
    <div id="donatediv" class="postbox"><div class="handlediv" title="<?php _e( 'Click to toggle', 'better-search' ); ?>"><br /></div>
      <h3 class='hndle'><span><?php _e( 'Support the development', 'better-search' ); ?></span></h3>
      <div class="inside">
        <div id="donate-form">
            <form action="https://www.paypal.com/cgi-bin/webscr" method="post">
                <input type="hidden" name="cmd" value="_xclick">
                <input type="hidden" name="business" value="donate@ajaydsouza.com">
                <input type="hidden" name="lc" value="IN">
				<input type="hidden" name="item_name" value="<?php _e( 'Donation for Better Search', 'better-search' ); ?>">
                <input type="hidden" name="item_number" value="bsearch_admin">
				<strong><?php _e( 'Enter amount in USD', 'better-search' ); ?></strong>: <input name="amount" value="10.00" size="6" type="text"><br />
                <input type="hidden" name="currency_code" value="USD">
                <input type="hidden" name="button_subtype" value="services">
                <input type="hidden" name="bn" value="PP-BuyNowBF:btn_donate_LG.gif:NonHosted">
				<input type="image" src="https://www.paypal.com/en_US/i/btn/btn_donate_LG.gif" border="0" name="submit" alt="<?php _e( 'Send your donation to the author of Better Search', 'better-search' ); ?>">
                <img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1">
            </form>
        </div>
      </div>
    </div>
    <div id="followdiv" class="postbox"><div class="handlediv" title="<?php _e( 'Click to toggle', 'better-search' ); ?>"><br /></div>
      <h3 class='hndle'><span><?php _e( 'Follow me', 'better-search' ); ?></span></h3>
      <div class="inside">
        <div id="follow-us">
            <iframe src="//www.facebook.com/plugins/likebox.php?href=http%3A%2F%2Fwww.facebook.com%2Fajaydsouzacom&amp;width=292&amp;height=62&amp;colorscheme=light&amp;show_faces=false&amp;border_color&amp;stream=false&amp;header=true&amp;appId=113175385243" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:292px; height:62px;" allowTransparency="true"></iframe>
            <div style="text-align:center"><a href="https://twitter.com/ajaydsouza" class="twitter-follow-button" data-show-count="false" data-size="large" data-dnt="true">Follow @ajaydsouza</a>
            <script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="//platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script></div>
        </div>
      </div>
    </div>
    <div id="qlinksdiv" class="postbox"><div class="handlediv" title="<?php _e( 'Click to toggle', 'better-search' ); ?>"><br /></div>
      <h3 class='hndle'><span><?php _e( 'Quick links', 'better-search' ); ?></span></h3>
      <div class="inside">
        <div id="quick-links">
            <ul>
				<li><a href="http://ajaydsouza.com/wordpress/plugins/better-search/"><?php _e( 'Better Search plugin page', 'better-search' ); ?></a></li>
				<li><a href="http://ajaydsouza.com/wordpress/plugins/"><?php _e( 'Other plugins', 'better-search' ); ?></a></li>
				<li><a href="http://ajaydsouza.com/"><?php _e( "Ajay's blog", 'better-search' ); ?></a></li>
				<li><a href="https://wordpress.org/plugins/better-search/faq/"><?php _e( 'FAQ', 'better-search' ); ?></a></li>
				<li><a href="http://wordpress.org/support/plugin/better-search"><?php _e( 'Support', 'better-search' ); ?></a></li>
				<li><a href="https://wordpress.org/support/view/plugin-reviews/better-search"><?php _e( 'Reviews', 'better-search' ); ?></a></li>
            </ul>
        </div>
      </div>
    </div>

<?php
}


/**
 * Add menu item in WP-Admin.
 *
 * @since	1.0
 */
function bsearch_adminmenu() {

	$plugin_page = add_options_page( __( 'Better Search', 'better-search' ), __( 'Better Search', 'better-search' ), 'manage_options', 'bsearch_options', 'bsearch_options' );
	add_action( 'admin_head-'. $plugin_page, 'bsearch_adminhead' );
}
add_action( 'admin_menu', 'bsearch_adminmenu' );


/**
 * Add CSS and JS to the admin head.
 *
 * @since	1.0
 */
function bsearch_adminhead() {
	global $bsearch_url;

	wp_enqueue_script( 'common' );
	wp_enqueue_script( 'wp-lists' );
	wp_enqueue_script( 'postbox' );
?>
    <style type="text/css">
    .postbox .handlediv:before {
        right:12px;
        font:400 20px/1 dashicons;
        speak:none;
        display:inline-block;
        top:0;
        position:relative;
        -webkit-font-smoothing:antialiased;
        -moz-osx-font-smoothing:grayscale;
        text-decoration:none!important;
        content:'\f142';
        padding:8px 10px;
    }
    .postbox.closed .handlediv:before {
        content: '\f140';
    }
    .wrap h2:before {
        content: "\f179";
        display: inline-block;
        -webkit-font-smoothing: antialiased;
        font: normal 29px/1 'dashicons';
        vertical-align: middle;
        margin-right: 0.3em;
    }
    </style>

    <script type="text/javascript">
        //<![CDATA[
        jQuery(document).ready( function($) {
            // close postboxes that should be closed
            $('.if-js-closed').removeClass('if-js-closed').addClass('closed');
            // postboxes setup
            postboxes.add_postbox_toggles('bsearch_options');
        });
        //]]>
    </script>

    <script type="text/javascript" language="JavaScript">
        //<![CDATA[
        function checkForm() {
        answer = true;
        if (siw && siw.selectingSomething)
            answer = false;
        return answer;
        }//
        //]]>
    </script>

	<script type="text/javascript" src="<?php echo $bsearch_url ?>/admin/jscolor/jscolor.js"></script>
<?php
}


/**
 * Function to clean the database.
 *
 * @since	1.0
 *
 * @param	bool $daily  TRUE = Daily tables, FALSE = Overall tables
 */
function bsearch_trunc_count( $daily = true ) {
	global $wpdb;
	$table_name = ( $daily ) ? $wpdb->prefix . 'bsearch_daily' : $wpdb->prefix . 'bsearch';

	$sql = "TRUNCATE TABLE $table_name";
	$wpdb->query( $sql );
}


/**
 * Dashboard for Better Search.
 *
 * @since	1.0
 */
function bsearch_pop_dashboard() {
	global $bsearch_settings;

	echo get_bsearch_heatmap( array(
		'daily' => 0,
	) );

	if ( $bsearch_settings['show_credit'] ) {
		echo '<br /><small>Powered by <a href="http://ajaydsouza.com/wordpress/plugins/better-search/">Better Search plugin</a></small>';
	}
}


/**
 * Dashboard for Daily Better Search.
 *
 * @since	1.0
 */
function bsearch_pop_daily_dashboard() {
	global $bsearch_settings;

	echo get_bsearch_heatmap( array(
		'daily' => 1,
	) );

	if ( $bsearch_settings['show_credit'] ) {
		echo '<br /><small>Powered by <a href="http://ajaydsouza.com/wordpress/plugins/better-search/">Better Search plugin</a></small>';
	}
}


/**
 * Add the dashboard widgets.
 *
 * @since	1.3.3
 */
function bsearch_dashboard_setup() {
	wp_add_dashboard_widget( 'bsearch_pop_dashboard', __( 'Popular Searches', 'better-search' ), 'bsearch_pop_dashboard' );
	wp_add_dashboard_widget( 'bsearch_pop_daily_dashboard', __( 'Daily Popular Searches', 'better-search' ), 'bsearch_pop_daily_dashboard' );
}
add_action( 'wp_dashboard_setup', 'bsearch_dashboard_setup' );


/**
 * Better Search plugin notice.
 *
 * @since	1.3.3
 *
 * @param	string $plugin
 */
function bsearch_plugin_notice( $plugin ) {
	global $cache_enabled;

	if ( $plugin == 'better-search/admin.inc.php' && ! $cache_enabled && function_exists( 'admin_url' ) ) {

		echo '<td colspan="5" class="plugin-update">Better Search must be configured. Go to <a href="' . admin_url( 'options-general.php?page=bsearch_options' ) . '">the admin page</a> to enable and configure the plugin.</td>';

	}
}
// add_action( 'after_plugin_row', 'bsearch_plugin_notice' );
/**
 * Adding WordPress plugin action links.
 *
 * @since	1.3
 *
 * @param	array $links  Existing array of links
 * @return	array	Updated array
 */
function bsearch_plugin_actions_links( $links ) {

	return array_merge(
		array(
			'settings' => '<a href="' . admin_url( 'options-general.php?page=bsearch_options' ) . '">' . __( 'Settings', 'better-search' ) . '</a>',
		),
		$links
	);

}
add_filter( 'plugin_action_links_' . plugin_basename( plugin_dir_path( __DIR__ ) . 'better-search.php' ), 'bsearch_plugin_actions_links' );


/**
 * Add meta links on Plugins page.
 *
 * @since	1.1.3
 *
 * @param	array  $links  Existing array of links
 * @param	string $file   File
 * @return	array	Updated array
 */
function bsearch_plugin_actions( $links, $file ) {
	$plugin = plugin_basename( plugin_dir_path( __DIR__ ) . 'better-search.php' );

	/**** Add links ****/
	if ( $file == $plugin ) {
		$links[] = '<a href="https://wordpress.org/support/plugin/better-search">' . __( 'Support', 'better-search' ) . '</a>';
		$links[] = '<a href="https://ajaydsouza.com/donate/">' . __( 'Donate', 'better-search' ) . '</a>';
		$links[] = '<a href="https://github.com/ajaydsouza/better-search">' . __( 'Contribute', 'better-search' ) . '</a>';
	}
	return $links;
}
add_filter( 'plugin_row_meta', 'bsearch_plugin_actions', 10, 2 ); // only 2.8 and higher

