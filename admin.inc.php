<?php
/**
 * Generates the settings page in the Admin
 *
 * @package BSearch
 */

// If this file is called directly, then abort execution.
if ( ! defined( 'WPINC' ) ) {
	die( "Aren't you supposed to come here via WP-Admin?" );
}

/**
 * Better Search options.
 *
 * @return void
 */
function bsearch_options() {

	global $wpdb;
    $poststable = $wpdb->posts;

	$bsearch_settings = bsearch_read_options();

	parse_str( $bsearch_settings['post_types'], $post_types );
	$wp_post_types = get_post_types( array(
		'public' => true,
	) );
	$posts_types_inc = array_intersect( $wp_post_types, $post_types );

	if( ( isset( $_POST['bsearch_save'] ) ) && ( check_admin_referer('bsearch-plugin') ) ) {

		$bsearch_settings['track_popular'] = isset( $_POST['track_popular'] ) ? true : false;
		$bsearch_settings['seamless'] = isset( $_POST['seamless'] ) ? true : false;

		$bsearch_settings['title'] = wp_kses_post( $_POST['title'] );
		$bsearch_settings['title_daily'] = wp_kses_post( $_POST['title_daily'] );
		$bsearch_settings['daily_range'] = intval( $_POST['daily_range'] );
		$bsearch_settings['limit'] = intval( $_POST['limit'] );
		$bsearch_settings['use_fulltext'] = isset( $_POST['use_fulltext'] ) ? true : false;
		$bsearch_settings['d_use_js'] = isset( $_POST['d_use_js'] ) ? true : false;
		$bsearch_settings['show_credit'] = isset( $_POST['show_credit'] ) ? true : false;

		$bsearch_settings['track_admins'] = isset( $_POST['track_admins']) ? true : false;
		$bsearch_settings['track_editors'] = isset( $_POST['track_editors']) ? true : false;

		$bsearch_settings['include_heatmap'] = isset( $_POST['include_heatmap'] ) ? true : false;
		$bsearch_settings['include_thumb'] = isset( $_POST['include_thumb'] ) ? true : false;
		$bsearch_settings['heatmap_smallest'] = $_POST['heatmap_smallest'];
		$bsearch_settings['heatmap_largest'] = $_POST['heatmap_largest'];
		$bsearch_settings['heatmap_limit'] = $_POST['heatmap_limit'];
		$bsearch_settings['heatmap_cold'] = $_POST['heatmap_cold'];
		$bsearch_settings['heatmap_hot'] = $_POST['heatmap_hot'];
		$bsearch_settings['heatmap_before'] = $_POST['heatmap_before'];
		$bsearch_settings['heatmap_after'] = $_POST['heatmap_after'];

		$bsearch_settings['weight_content'] = intval( $_POST['weight_content'] );
		$bsearch_settings['weight_title'] = intval( $_POST['weight_title'] );
		$bsearch_settings['boolean_mode'] = isset( $_POST['boolean_mode'] ) ? true : false;
		$bsearch_settings['badwords'] = wp_kses_post( $_POST['badwords'] );

		$bsearch_settings['excerpt_length'] = intval( $_POST['excerpt_length'] );
		$bsearch_settings['link_new_window'] = isset( $_POST['link_new_window'] ) ? true : false;
		$bsearch_settings['link_nofollow'] = isset( $_POST['link_nofollow'] ) ? true : false;

		$bsearch_settings['custom_CSS'] = wp_kses_post( $_POST['custom_CSS'] );

		// Update post types
		$wp_post_types	= get_post_types( array(
			'public' => true,
		) );
		$post_types_arr = ( is_array( $_POST['post_types'] ) ) ? $_POST['post_types'] : array( 'post' => 'post' );
		$post_types = array_intersect( $wp_post_types, $post_types_arr );
		$bsearch_settings['post_types'] = http_build_query( $post_types, '', '&' );
		$posts_types_inc = array_intersect( $wp_post_types, $post_types );

		update_option( 'ald_bsearch_settings', $bsearch_settings );

		$str = '<div id="message" class="updated fade"><p>'. __( 'Options saved successfully.', BSEARCH_LOCAL_NAME ) .'</p></div>';
		echo $str;
	}

	if ( ( isset( $_POST['bsearch_default'] ) ) && ( check_admin_referer('bsearch-plugin') ) ) {
		delete_option( 'ald_bsearch_settings' );
		$bsearch_settings = bsearch_default_options();
		update_option( 'ald_bsearch_settings', $bsearch_settings );

		$str = '<div id="message" class="updated fade"><p>' . __( 'Options set to Default.', BSEARCH_LOCAL_NAME ) . '</p></div>';
		echo $str;
	}

	if ( ( isset( $_POST['bsearch_trunc_all'] ) ) && ( check_admin_referer('bsearch-plugin') ) ) {
		bsearch_trunc_count( false );
		$str = '<div id="message" class="updated fade"><p>' . __( 'Popular searches count reset', BSEARCH_LOCAL_NAME ) . '</p></div>';
		echo $str;
	}

	if ( ( isset( $_POST['bsearch_trunc_daily'] ) ) && ( check_admin_referer('bsearch-plugin') ) ) {
		bsearch_trunc_count( true );
		$str = '<div id="message" class="updated fade"><p>' . __( 'Daily popular searches count reset', BSEARCH_LOCAL_NAME ) . '</p></div>';
		echo $str;
	}

?>

<div class="wrap">
	<h2>Better Search</h2>
	<div id="poststuff">
	<div id="post-body" class="metabox-holder columns-2">
	<div id="post-body-content">
	  <form method="post" id="bsearch_options" name="bsearch_options" onsubmit="return checkForm()">
	    <div id="genopdiv" class="postbox closed"><div class="handlediv" title="<?php _e( 'Click to toggle', BSEARCH_LOCAL_NAME ); ?>"><br /></div>
	      <h3 class='hndle'><span><?php _e( 'General options', BSEARCH_LOCAL_NAME ); ?></span></h3>
	      <div class="inside">
			<table class="form-table">
			<tbody>
				<tr><th scope="row"><label for="track_popular"><?php _e( 'Enable search tracking?', BSEARCH_LOCAL_NAME ); ?></label></th>
					<td>
					  <input type="checkbox" name="track_popular" id="track_popular" <?php if ( $bsearch_settings['track_popular'] ) echo 'checked="checked"' ?> />
					  <p class="description"><?php _e( 'If you turn this off, then the plugin will no longer track and display the popular search terms.', BSEARCH_LOCAL_NAME ); ?></p>
					</td>
				</tr>
				<tr><th scope="row"><label for="seamless"><?php _e( 'Enable seamless integration?', BSEARCH_LOCAL_NAME ); ?></label></th>
					<td>
					  <input type="checkbox" name="seamless" id="seamless" <?php if ( $bsearch_settings['seamless'] ) echo 'checked="checked"' ?> />
					  <p class="description"><?php _e( "Enabling this option will make the plugin return the search results just how your theme intended to be. This will ignore better-search-template.php. It will continue to display the search results sorted by relevance, although it won't display the percentage relevance.", BSEARCH_LOCAL_NAME ); ?></p>
					</td>
				</tr>
				<tr><th scope="row"><label for="track_admins"><?php _e( 'Track visits of admins?', BSEARCH_LOCAL_NAME ); ?></label></th>
				  <td><input type="checkbox" name="track_admins" id="track_admins" <?php if ( $bsearch_settings['track_admins'] ) echo 'checked="checked"' ?> />
				    <p class="description"><?php _e( 'Disabling this option will stop admin visits being tracked.', BSEARCH_LOCAL_NAME ); ?></p>
				  </td>
				</tr>
				<tr><th scope="row"><label for="track_editors"><?php _e( 'Track visits of Editors?', BSEARCH_LOCAL_NAME ); ?></label></th>
				  <td><input type="checkbox" name="track_editors" id="track_editors" <?php if ( $bsearch_settings['track_editors'] ) echo 'checked="checked"' ?> />
				    <p class="description"><?php _e( 'Disabling this option will stop editor visits being tracked.', BSEARCH_LOCAL_NAME ); ?></p>
				  </td>
				</tr>
				<tr><th scope="row"><label for="limit"><?php _e( 'Number of Search Results per page: ', BSEARCH_LOCAL_NAME ); ?></label></th>
					<td>
					  <input type="textbox" name="limit" id="limit" value="<?php echo stripslashes( $bsearch_settings['limit'] ); ?>">
					  <p class="description"><?php _e( 'This is the maximum number of search results that will be displayed per page by default', BSEARCH_LOCAL_NAME ); ?></p>
					</td>
				</tr>
				<tr><th scope="row"><?php _e( 'Post types to include in results (including custom post types)', BSEARCH_LOCAL_NAME ); ?></th>
				<td>
					<?php foreach ( $wp_post_types as $wp_post_type ) {
						$post_type_op = '<input type="checkbox" name="post_types[]" value="' . $wp_post_type . '" ';
						if ( in_array( $wp_post_type, $posts_types_inc ) ) {
							$post_type_op .= ' checked="checked" ';
						}
						$post_type_op .= ' />' . $wp_post_type . '&nbsp;&nbsp;';
						echo $post_type_op;
					}
					?>
				</td>
				</tr>
				<tr><th scope="row"><label for="daily_range"><?php _e( 'Daily Popular should contain searches of how many days? ', BSEARCH_LOCAL_NAME ); ?></label></th>
					<td>
					  <input type="textbox" name="daily_range" id="daily_range" size="3" value="<?php echo stripslashes( $bsearch_settings['daily_range'] ); ?>">
					</td>
				</tr>
				<tr><th scope="row"><label for="use_fulltext"><?php _e( 'Enable mySQL FULLTEXT searching', BSEARCH_LOCAL_NAME ); ?></label></th>
					<td>
					  <input type="checkbox" name="use_fulltext" id="use_fulltext" <?php if ( $bsearch_settings['use_fulltext'] ) echo 'checked="checked"' ?> />
					  <p class="description"><?php _e( 'Disabling this option will no longer give relevancy based results.', BSEARCH_LOCAL_NAME ); ?></p>
					</td>
				</tr>
				<tr><th scope="row"><label for="d_use_js"><?php _e( 'Bypass Cache for daily popular searches\' heatmap?', BSEARCH_LOCAL_NAME ); ?></label></th>
					<td>
					  <input type="checkbox" name="d_use_js" id="d_use_js" <?php if ( $bsearch_settings['d_use_js'] ) echo 'checked="checked"' ?> />
					  <p class="description"><?php _e( 'This options uses JavaScript to load the post and can increase your page load time', BSEARCH_LOCAL_NAME ); ?></p>
					</td>
				</tr>
				<tr><th scope="row"><label for="show_credit"><?php _e( 'Link to plugin homepage', BSEARCH_LOCAL_NAME ); ?></label></th>
					<td>
					  <input type="checkbox" name="show_credit" id="show_credit" <?php if ( $bsearch_settings['show_credit'] ) echo 'checked="checked"' ?> />
					  <p class="description"><?php _e( 'A nofollow link to the plugin is added as an extra list item to the list of popular searches. Not mandatory, but thanks if you do it!', BSEARCH_LOCAL_NAME ); ?></p>
					</td>
				</tr>
				<tr style="background: #eee"><th scope="row" colspan="2"><strong><?php _e( 'Fine tune the results', BSEARCH_LOCAL_NAME ); ?></strong></th>
				</tr>
				<tr><th scope="row"><label for="weight_title"><?php _e( 'Weight of the title: ', BSEARCH_LOCAL_NAME ); ?></label></th>
					<td>
					  <input type="textbox" name="weight_title" id="weight_title" value="<?php echo stripslashes( $bsearch_settings['weight_title'] ); ?>">
					</td>
				</tr>
				<tr><th scope="row"><label for="weight_content"><?php _e( 'Weight of the content: ', BSEARCH_LOCAL_NAME ); ?></label></th>
					<td>
					  <input type="textbox" name="weight_content" id="weight_content" value="<?php echo stripslashes( $bsearch_settings['weight_content'] ); ?>">
					</td>
				</tr>
				<tr><th scope="row"><label for="boolean_mode"><?php _e( 'Activate BOOLEAN mode of FULLTEXT search:', BSEARCH_LOCAL_NAME ); ?></label></th>
					<td>
					  <input type="checkbox" name="boolean_mode" id="boolean_mode" <?php if ( $bsearch_settings['boolean_mode'] ) echo 'checked="checked"' ?> />
					  <p class="description"><?php _e( 'Limits relevancy matches but removes several limitations of NATURAL LANGUAGE mode. <a href="https://dev.mysql.com/doc/refman/5.0/en/fulltext-boolean.html" target="_blank">Check the mySQL docs for further information on BOOLEAN indices</a>', BSEARCH_LOCAL_NAME ); ?></p>
					</td>
				</tr>
				<tr><th scope="row"><label for="badwords"><?php _e( 'Filter these words:', BSEARCH_LOCAL_NAME ); ?></label></th>
					<td>
					  <textarea name="badwords" id="badwords" rows="15" cols="50"><?php echo stripslashes( $bsearch_settings['badwords'] ); ?></textarea>
					  <p class="description"><?php _e( 'Words in this list will be stripped out of the search results. Enter these as a comma-separated list.', BSEARCH_LOCAL_NAME ); ?></p>
					</td>
				</tr>
			</tbody>
			</table>
	      </div>
	    </div>
	    <div id="outputopdiv" class="postbox closed"><div class="handlediv" title="<?php _e( 'Click to toggle', BSEARCH_LOCAL_NAME ); ?>"><br /></div>
	      <h3 class='hndle'><span><?php _e( 'Output options', BSEARCH_LOCAL_NAME ); ?></span></h3>
	      <div class="inside">
			<table class="form-table">
			<tbody>
				<tr><th scope="row"><label for="title"><?php _e( 'Title of Overall Popular Searches: ', BSEARCH_LOCAL_NAME ); ?></label></th>
					<td><input type="textbox" name="title" id="title" value="<?php echo stripslashes( $bsearch_settings['title'] ); ?>" style="width:250px">
					</td>
				</tr>
				<tr><th scope="row"><label for="title_daily"><?php _e( 'Title of Daily Popular Searches: ', BSEARCH_LOCAL_NAME ); ?></label></th>
					<td><input type="textbox" name="title_daily" id="title_daily" value="<?php echo stripslashes( $bsearch_settings['title_daily'] ); ?>" style="width:250px">
					</td>
				</tr>
				<tr><th scope="row"><label for="excerpt_length"><?php _e( 'Length of excerpt (in words): ', BSEARCH_LOCAL_NAME ); ?></label></th>
					<td><input type="textbox" name="excerpt_length" id="excerpt_length" value="<?php echo stripslashes( $bsearch_settings['excerpt_length'] ); ?>" />
					</td>
				</tr>
				<tr><th scope="row"><label for="link_new_window"><?php _e( 'Open links in new window', BSEARCH_LOCAL_NAME ); ?></label></th>
					<td><input type="checkbox" name="link_new_window" id="link_new_window" <?php if ( $bsearch_settings['link_new_window'] ) echo 'checked="checked"' ?> /></td>
				</tr>
				<tr><th scope="row"><label for="link_nofollow"><?php _e( 'Add nofollow attribute to links in the list', BSEARCH_LOCAL_NAME ); ?></label></th>
					<td><input type="checkbox" name="link_nofollow" id="link_nofollow" <?php if ( $bsearch_settings['link_nofollow'] ) echo 'checked="checked"' ?> /></td>
				</tr>
				<tr><th scope="row"><label for="include_thumb"><?php _e( 'Include thumbnails in search results', BSEARCH_LOCAL_NAME ); ?></label></th>
					<td>
					  <input type="checkbox" name="include_thumb" id="include_thumb" <?php if ( $bsearch_settings['include_thumb'] ) echo 'checked="checked"' ?> />
					  <p class="description"><?php _e( 'Displays the featured image (post thumbnail) wherever available', BSEARCH_LOCAL_NAME ); ?></p>
					</td>
				</tr>
				<tr style="background: #eee"><th scope="row" colspan="2"><strong><?php _e( 'Heatmap (Popular searches) Options:', BSEARCH_LOCAL_NAME ); ?></strong></th>
				</tr>
				<tr><th scope="row"><label for="include_heatmap"><?php _e( 'Include heatmap on the search results:', BSEARCH_LOCAL_NAME ); ?></label></th>
					<td>
					  <input type="checkbox" name="include_heatmap" id="include_heatmap" <?php if ( $bsearch_settings['include_heatmap'] ) echo 'checked="checked"' ?> />
					  <p class="description"><?php _e( 'This option will display the heatmaps at the bottom of the search results page. Display popular searches to your visitors', BSEARCH_LOCAL_NAME ); ?></p>
					</td>
				</tr>
				<tr><th scope="row"><label for="heatmap_limit"><?php _e( 'Number of search terms to display: ', BSEARCH_LOCAL_NAME ); ?></label></th>
					<td>
					  <input type="textbox" name="heatmap_limit" id="heatmap_limit" value="<?php echo stripslashes( $bsearch_settings['heatmap_limit'] ); ?>">
					</td>
				</tr>
				<tr><th scope="row"><label for="heatmap_smallest"><?php _e( 'Font size of least popular search term: ', BSEARCH_LOCAL_NAME ); ?></label></th>
					<td>
					  <input type="textbox" name="heatmap_smallest" id="heatmap_smallest" value="<?php echo stripslashes( $bsearch_settings['heatmap_smallest'] ); ?>">
					</td>
				</tr>
				<tr><th scope="row"><label for="heatmap_largest"><?php _e( 'Font size of most popular search term: ', BSEARCH_LOCAL_NAME ); ?></label></th>
					<td>
					  <input type="textbox" name="heatmap_largest" id="heatmap_largest" value="<?php echo stripslashes( $bsearch_settings['heatmap_largest'] ); ?>">
					</td>
				</tr>
				<tr><th scope="row"><label for="heatmap_cold"><?php _e( 'Color of least popular search term: ', BSEARCH_LOCAL_NAME ); ?></label></th>
					<td>
					  <input type="textbox" class="color" name="heatmap_cold" id="heatmap_cold" value="<?php echo stripslashes( $bsearch_settings['heatmap_cold'] ); ?>">
					</td>
				</tr>
				<tr><th scope="row"><label for="heatmap_hot"><?php _e( 'Color of most popular search term: ', BSEARCH_LOCAL_NAME ); ?></label></th>
					<td>
					  <input type="textbox" class="color" name="heatmap_hot" id="heatmap_hot" value="<?php echo stripslashes( $bsearch_settings['heatmap_hot'] ); ?>">
					</td>
				</tr>
				<tr><th scope="row"><label for="heatmap_before"><?php _e( 'Text to include before each search term in heatmap', BSEARCH_LOCAL_NAME ); ?></label></th>
					<td>
					  <input type="textbox" name="heatmap_before" id="heatmap_before" value="<?php echo stripslashes( $bsearch_settings['heatmap_before'] ); ?>">
					</td>
				</tr>
				<tr><th scope="row"><label for="heatmap_after"><?php _e( 'Text to include after each search term in heatmap', BSEARCH_LOCAL_NAME ); ?></label></th>
					<td>
					  <input type="textbox" name="heatmap_after" id="heatmap_after" value="<?php echo stripslashes( $bsearch_settings['heatmap_after'] ); ?>">
					</td>
				</tr>
			</tbody>
			</table>
	      </div>
	    </div>
	    <div id="customcssdiv" class="postbox closed"><div class="handlediv" title="<?php _e( 'Click to toggle', BSEARCH_LOCAL_NAME ); ?>"><br /></div>
	      <h3 class='hndle'><span><?php _e( 'Custom CSS', BSEARCH_LOCAL_NAME ); ?></span></h3>
	      <div class="inside">
			<table class="form-table">
			<tr><th scope="row" colspan="2"><?php _e( 'Custom CSS to add to header:', BSEARCH_LOCAL_NAME ); ?></th>
			</tr>
			<tr><td scope="row" colspan="2"><textarea name="custom_CSS" id="custom_CSS" rows="15" cols="80"><?php echo stripslashes( $bsearch_settings['custom_CSS'] ); ?></textarea>
			<p class="description"><?php _e( 'Do not include <code>style</code> tags. Check out the <a href="http://wordpress.org/extend/plugins/better-search/faq/" target="_blank">FAQ</a> for available CSS classes to style.', BSEARCH_LOCAL_NAME ); ?></p></td></tr>
			</table>
	      </div>
	    </div>
		<p>
		  <input type="submit" name="bsearch_save" id="bsearch_save" value="<?php _e( 'Save Options', BSEARCH_LOCAL_NAME ); ?>" class="button button-primary" />
		  <input type="submit" name="bsearch_default" id="bsearch_default" value="<?php _e( 'Default Options', BSEARCH_LOCAL_NAME ); ?>" class="button button-secondary" onclick="if ( ! confirm( '<?php _e( "Do you want to set options to Default?", BSEARCH_LOCAL_NAME ); ?>' ) ) return false;" />
		</p>
		<?php wp_nonce_field( 'bsearch-plugin' ); ?>
	  </form>

	  <form method="post" id="bsearch_reset_options" name="bsearch_reset_options" onsubmit="return checkForm()">
	    <div id="resetopdiv" class="postbox closed"><div class="handlediv" title="<?php _e( 'Click to toggle', BSEARCH_LOCAL_NAME ); ?>"><br /></div>
	      <h3 class='hndle'><span><?php _e( 'Reset count', BSEARCH_LOCAL_NAME ); ?></span></h3>
	      <div class="inside">
		    <p class="description">
		      <?php _e( 'This cannot be reversed. Make sure that your database has been backed up before proceeding', BSEARCH_LOCAL_NAME ); ?>
		    </p>
		    <p>
		      <input name="bsearch_trunc_all" type="submit" id="bsearch_trunc_all" value="<?php _e( 'Reset Popular Searches', BSEARCH_LOCAL_NAME ); ?>" class="button button-secondary" onclick="if ( ! confirm( '<?php _e( "Are you sure you want to reset the popular posts?", BSEARCH_LOCAL_NAME ); ?>' ) ) return false;" />
		      <input name="bsearch_trunc_daily" type="submit" id="bsearch_trunc_daily" value="<?php _e( 'Reset Daily Popular Searches', BSEARCH_LOCAL_NAME ); ?>" class="button button-secondary" onclick="if ( ! confirm( '<?php _e( "Are you sure you want to reset the daily popular posts?", BSEARCH_LOCAL_NAME ); ?>' ) ) return false;" />
		    </p>
	      </div>
	    </div>
		<?php wp_nonce_field( 'bsearch-plugin' ); ?>
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
 * @return void
 */
function bsearch_admin_side() {
?>
    <div id="donatediv" class="postbox"><div class="handlediv" title="<?php _e( 'Click to toggle', BSEARCH_LOCAL_NAME ); ?>"><br /></div>
      <h3 class='hndle'><span><?php _e( 'Support the development', BSEARCH_LOCAL_NAME ); ?></span></h3>
      <div class="inside">
		<div id="donate-form">
			<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
				<input type="hidden" name="cmd" value="_xclick">
				<input type="hidden" name="business" value="donate@ajaydsouza.com">
				<input type="hidden" name="lc" value="IN">
				<input type="hidden" name="item_name" value="<?php _e( 'Donation for Better Search', BSEARCH_LOCAL_NAME ); ?>">
				<input type="hidden" name="item_number" value="bsearch_admin">
				<strong><?php _e( 'Enter amount in USD: ', BSEARCH_LOCAL_NAME ); ?></strong> <input name="amount" value="10.00" size="6" type="text"><br />
				<input type="hidden" name="currency_code" value="USD">
				<input type="hidden" name="button_subtype" value="services">
				<input type="hidden" name="bn" value="PP-BuyNowBF:btn_donate_LG.gif:NonHosted">
				<input type="image" src="https://www.paypal.com/en_US/i/btn/btn_donate_LG.gif" border="0" name="submit" alt="<?php _e( 'Send your donation to the author of Better Search', BSEARCH_LOCAL_NAME ); ?>">
				<img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1">
			</form>
		</div>
      </div>
    </div>
    <div id="followdiv" class="postbox"><div class="handlediv" title="<?php _e( 'Click to toggle', BSEARCH_LOCAL_NAME ); ?>"><br /></div>
      <h3 class='hndle'><span><?php _e( 'Follow me', BSEARCH_LOCAL_NAME ); ?></span></h3>
      <div class="inside">
		<div id="follow-us">
			<iframe src="//www.facebook.com/plugins/likebox.php?href=http%3A%2F%2Fwww.facebook.com%2Fajaydsouzacom&amp;width=292&amp;height=62&amp;colorscheme=light&amp;show_faces=false&amp;border_color&amp;stream=false&amp;header=true&amp;appId=113175385243" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:292px; height:62px;" allowTransparency="true"></iframe>
			<div style="text-align:center"><a href="https://twitter.com/ajaydsouza" class="twitter-follow-button" data-show-count="false" data-size="large" data-dnt="true">Follow @ajaydsouza</a>
			<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="//platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script></div>
		</div>
      </div>
    </div>
    <div id="qlinksdiv" class="postbox"><div class="handlediv" title="<?php _e( 'Click to toggle', BSEARCH_LOCAL_NAME ); ?>"><br /></div>
      <h3 class='hndle'><span><?php _e( 'Quick links', BSEARCH_LOCAL_NAME ); ?></span></h3>
      <div class="inside">
        <div id="quick-links">
			<ul>
				<li><a href="http://ajaydsouza.com/wordpress/plugins/better-search/"><?php _e( 'Better Search plugin page', BSEARCH_LOCAL_NAME ); ?></a></li>
				<li><a href="http://ajaydsouza.com/wordpress/plugins/"><?php _e( 'Other plugins', BSEARCH_LOCAL_NAME ); ?></a></li>
				<li><a href="http://ajaydsouza.com/"><?php _e( "Ajay's blog", BSEARCH_LOCAL_NAME ); ?></a></li>
				<li><a href="https://wordpress.org/plugins/better-search/faq/"><?php _e( 'FAQ', BSEARCH_LOCAL_NAME ); ?></a></li>
				<li><a href="http://wordpress.org/support/plugin/better-search"><?php _e( 'Support', BSEARCH_LOCAL_NAME ); ?></a></li>
				<li><a href="https://wordpress.org/support/view/plugin-reviews/better-search"><?php _e( 'Reviews', BSEARCH_LOCAL_NAME ); ?></a></li>
			</ul>
        </div>
      </div>
    </div>

<?php
}


/**
 * Add menu item in WP-Admin.
 *
 */
function bsearch_adminmenu() {

	$plugin_page = add_options_page( __( "Better Search", BSEARCH_LOCAL_NAME ), __( "Better Search", BSEARCH_LOCAL_NAME ), 'manage_options', 'bsearch_options', 'bsearch_options');
	add_action( 'admin_head-'. $plugin_page, 'bsearch_adminhead' );
}
add_action( 'admin_menu', 'bsearch_adminmenu' );


/**
 * Add CSS and JS to the admin head.
 *
 * @return void
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

	<link rel="stylesheet" type="text/css" href="<?php echo $bsearch_url ?>/wick/wick.css" />
	<script type="text/javascript" language="JavaScript">
		//<![CDATA[
		<?php
		function wick_data() {
			global $wpdb;

			$categories = get_categories( 'hide_empty=0' );
			$str = 'collection = [';
			foreach ( $categories as $cat ) {
				$str .= "'" . $cat->slug . "',";
			}
			$str = substr( $str, 0, -1 );	// Remove trailing comma
			$str .= '];';

			echo $str;
		}
		wick_data();
		?>
		//]]>
	</script>

	<script type="text/javascript" src="<?php echo $bsearch_url ?>/wick/wick.js"></script>
	<script type="text/javascript" src="<?php echo $bsearch_url ?>/jscolor/jscolor.js"></script>
<?php
}


/**
 * Function to clean the database.
 *
 * @param bool $daily (default: true)
 */
function bsearch_trunc_count( $daily = true ) {
	global $wpdb;
	$table_name = ( $daily ) ? $wpdb->prefix . "bsearch_daily" : $wpdb->prefix . "bsearch";

	$sql = "TRUNCATE TABLE $table_name";
	$wpdb->query( $sql );
}


/**
 * Dashboard for Better Search.
 *
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
 */
function bsearch_dashboard_setup() {
	wp_add_dashboard_widget( 'bsearch_pop_dashboard', __( 'Popular Searches', BSEARCH_LOCAL_NAME ), 'bsearch_pop_dashboard' );
	wp_add_dashboard_widget( 'bsearch_pop_daily_dashboard', __( 'Daily Popular Searches', BSEARCH_LOCAL_NAME ), 'bsearch_pop_daily_dashboard' );
}
add_action( 'wp_dashboard_setup', 'bsearch_dashboard_setup' );


/**
 * Better Search plugin notice.
 *
 * @param mixed $plugin
 * @return void
 */
function bsearch_plugin_notice( $plugin ) {
	global $cache_enabled;
 	if ( $plugin == 'better-search/admin.inc.php' && ! $cache_enabled && function_exists( 'admin_url' ) ) {
		echo '<td colspan="5" class="plugin-update">Better Search must be configured. Go to <a href="' . admin_url( 'options-general.php?page=bsearch_options' ) . '">the admin page</a> to enable and configure the plugin.</td>';
	}
}
//add_action( 'after_plugin_row', 'bsearch_plugin_notice' );

?>