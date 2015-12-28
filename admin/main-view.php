<?php
/**
 * Main settings page in the Admin
 *
 * @package Better_Search
 */

// If this file is called directly, then abort execution.
if ( ! defined( 'WPINC' ) ) {
	die( "Aren't you supposed to come here via WP-Admin?" );
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

				<tr>
					<th scope="row"><label for="cache"><?php _e( 'Enable cache:', 'better-search' ); ?></label></th>
					<td>
						<p><input type="checkbox" name="cache" id="cache" <?php if ( $bsearch_settings['cache'] ) { echo 'checked="checked"'; } ?> /></p>
						<p class="description"><?php _e( 'If activated, Better Search will use the Transients API to cache the search results for 1 hour.', 'better-search' ); ?></p>
						<p><input type="button" name="cache_clear" id="cache_clear"  value="<?php _e( 'Clear cache', 'better-search' ); ?>" class="button-secondary" onclick="return clearCache();" /></p>
					</td>
				</tr>

				<tr><th scope="row"><label for="meta_noindex"><?php _e( 'Stop search engines from indexing search results pages', 'better-search' ); ?></label></th>
                    <td>
						<input type="checkbox" name="meta_noindex" id="meta_noindex" <?php if ( $bsearch_settings['meta_noindex'] ) { echo 'checked="checked"'; } ?> />
						<p class="description"><?php _e( 'This is a recommended option to turn ON. Adds <code>noindex,follow</code> meta tag to the head of the page', 'better-search' ); ?></p>
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
