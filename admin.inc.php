<?php
/**********************************************************************
*					Admin Page										*
*********************************************************************/
// Pre-2.6 compatibility
if ( !defined('WP_CONTENT_URL') )
	define( 'WP_CONTENT_URL', get_option('siteurl') . '/wp-content');
if ( !defined('WP_CONTENT_DIR') )
	define( 'WP_CONTENT_DIR', ABSPATH . 'wp-content' );
// Guess the location
$bsearch_path = WP_CONTENT_DIR.'/plugins/'.plugin_basename(dirname(__FILE__));
$bsearch_url = WP_CONTENT_URL.'/plugins/'.plugin_basename(dirname(__FILE__));

function bsearch_options() {
	
	global $wpdb;
    $poststable = $wpdb->posts;

	$bsearch_settings = bsearch_read_options();

	if($_POST['bsearch_save']){
		$bsearch_settings[title] = ($_POST['title']);
		$bsearch_settings[title_daily] = ($_POST['title_daily']);
		$bsearch_settings[daily_range] = ($_POST['daily_range']);
		$bsearch_settings[limit] = ($_POST['limit']);
		$bsearch_settings[use_fulltext] = (($_POST['use_fulltext']) ? true : false);
		$bsearch_settings[d_use_js] = (($_POST['d_use_js']) ? true : false);
		$bsearch_settings[show_credit] = (($_POST['show_credit']) ? true : false);
		
		$bsearch_settings[heatmap_smallest] = ($_POST['heatmap_smallest']);
		$bsearch_settings[heatmap_largest] = ($_POST['heatmap_largest']);
		$bsearch_settings[heatmap_limit] = ($_POST['heatmap_limit']);
		$bsearch_settings[heatmap_cold] = ($_POST['heatmap_cold']);
		$bsearch_settings[heatmap_hot] = ($_POST['heatmap_hot']);
		$bsearch_settings[heatmap_before] = ($_POST['heatmap_before']);
		$bsearch_settings[heatmap_after] = ($_POST['heatmap_after']);
		
		update_option('ald_bsearch_settings', $bsearch_settings);
		
		$str = '<div id="message" class="updated fade"><p>'. __('Options saved successfully.','ald_bsearch_plugin') .'</p></div>';
		echo $str;
	}
	
	if ($_POST['bsearch_default']){
		delete_option('ald_bsearch_settings');
		$bsearch_settings = bsearch_default_options();
		update_option('ald_bsearch_settings', $bsearch_settings);
		
		$str = '<div id="message" class="updated fade"><p>'. __('Options set to Default.','ald_bsearch_plugin') .'</p></div>';
		echo $str;
	}
?>

<div class="wrap">
  <h2>Better Search </h2>
  <div style="border: #ccc 1px solid; padding: 10px">
    <fieldset class="options">
    <legend>
    <h3>
      <?php _e('Support the Development','ald_bsearch_plugin'); ?>
    </h3>
    </legend>
    <p>
      <?php _e('If you find ','ald_bsearch_plugin'); ?>
      <a href="http://ajaydsouza.com/wordpress/plugins/better-search/">Better Search</a>
      <?php _e('useful, please do','ald_bsearch_plugin'); ?>
      <a href="https://www.paypal.com/cgi-bin/webscr?cmd=_xclick&amp;business=donate@ajaydsouza.com&amp;item_name=Better%20Search%20(From%20WP-Admin)&amp;no_shipping=1&amp;return=http://ajaydsouza.com/wordpress/plugins/better-search/&amp;cancel_return=http://ajaydsouza.com/wordpress/plugins/better-search/&amp;cn=Note%20to%20Author&amp;tax=0&amp;currency_code=USD&amp;bn=PP-DonationsBF&amp;charset=UTF-8" title="Donate via PayPal"><?php _e('drop in your contribution','ald_bsearch_plugin'); ?></a>.
	  (<a href="http://ajaydsouza.com/donate/"><?php _e('Some reasons why you should.','ald_bsearch_plugin'); ?></a>)</p>
    </fieldset>
  </div>
  <form method="post" id="bsearch_options" name="bsearch_options" style="border: #ccc 1px solid; padding: 10px">
    <fieldset class="options">
    <legend>
    <h3>
      <?php _e('Options:','ald_bsearch_plugin'); ?>
    </h3>
    </legend>
    <p>
      <label>
      <?php _e('Number of Search Results per page: ','ald_bsearch_plugin'); ?>
      <input type="textbox" name="limit" id="limit" value="<?php echo stripslashes($bsearch_settings[limit]); ?>">
      </label>
    </p>
    <p>
      <label>
      <?php _e('Title of Overall Popular Searchs: ','ald_bsearch_plugin'); ?>
      <input type="textbox" name="title" id="title" value="<?php echo stripslashes($bsearch_settings[title]); ?>">
      </label>
    </p>
    <p>
      <label>
      <?php _e('Title of Daily Popular Searches: ','ald_bsearch_plugin'); ?>
      <input type="textbox" name="title_daily" id="title_daily" value="<?php echo stripslashes($bsearch_settings[title_daily]); ?>">
      </label>
    </p>
    <p>
      <label>
      <?php _e('Daily Popular should contain searches of how many days? ','ald_bsearch_plugin'); ?>
      <input type="textbox" name="daily_range" id="daily_range" size="3" value="<?php echo stripslashes($bsearch_settings[daily_range]); ?>">
      </label>
    </p>
    <p>
      <label>
      <input type="checkbox" name="d_use_js" id="d_use_js" <?php if ($bsearch_settings[d_use_js]) echo 'checked="checked"' ?> />
      <?php _e('Bypass Cache for daily popular searches\' heatmap? This options uses JavaScript to load the post and can increase your page load time','ald_bsearch_plugin'); ?>
      </label>
    </p>
    <p>
      <label>
      <input type="checkbox" name="show_credit" id="show_credit" <?php if ($bsearch_settings[show_credit]) echo 'checked="checked"' ?> />
      <?php _e('A link to the plugin is added as an extra list item to the list of popular searches. Not mandatory, but thanks if you do it!','ald_bsearch_plugin'); ?>
      </label>
    </p>
	</fieldset>
    <fieldset class="options">
    <legend>
    <h3>
      <?php _e('Heatmap (Popular searches) Options:','ald_bsearch_plugin'); ?>
    </h3>
    </legend>
    <p>
      <label>
      <?php _e('Number of search terms to display: ','ald_bsearch_plugin'); ?>
      <input type="textbox" name="heatmap_limit" id="heatmap_limit" value="<?php echo stripslashes($bsearch_settings[heatmap_limit]); ?>">
      </label>
    </p>
    <p>
      <label>
      <?php _e('Font size of least popular search term: ','ald_bsearch_plugin'); ?>
      <input type="textbox" name="heatmap_smallest" id="heatmap_smallest" value="<?php echo stripslashes($bsearch_settings[heatmap_smallest]); ?>">
      </label>
    </p>
    <p>
      <label>
      <?php _e('Font size of most popular search term: ','ald_bsearch_plugin'); ?>
      <input type="textbox" name="heatmap_largest" id="heatmap_largest" value="<?php echo stripslashes($bsearch_settings[heatmap_largest]); ?>">
      </label>
    </p>
    <p>
      <label>
      <?php _e('Color of least popular search term: ','ald_bsearch_plugin'); ?>
      <input type="textbox" class="color" name="heatmap_cold" id="heatmap_cold" value="<?php echo stripslashes($bsearch_settings[heatmap_cold]); ?>">
      </label>
    </p>
    <p>
      <label>
      <?php _e('Color of most popular search term: ','ald_bsearch_plugin'); ?>
      <input type="textbox" class="color" name="heatmap_hot" id="heatmap_hot" value="<?php echo stripslashes($bsearch_settings[heatmap_hot]); ?>">
      </label>
    </p>
    <p>
      <label>
      <?php _e('Text to include before each search term in heatmap','ald_bsearch_plugin'); ?>
      <input type="textbox" name="heatmap_before" id="heatmap_before" size="3" value="<?php echo stripslashes($bsearch_settings[heatmap_before]); ?>">
      </label>
    </p>
    <p>
      <label>
      <?php _e('Text to include after each search term in heatmap','ald_bsearch_plugin'); ?>
      <input type="textbox" name="heatmap_after" id="heatmap_after" size="3" value="<?php echo stripslashes($bsearch_settings[heatmap_after]); ?>">
      </label>
    </p>
    </fieldset>
    <p>
      <input type="submit" name="bsearch_save" id="bsearch_save" value="Save Options" style="border:#00CC00 1px solid" />
      <input name="bsearch_default" type="submit" id="bsearch_default" value="Default Options" style="border:#FF0000 1px solid" onclick="if (!confirm('<?php _e('Do you want to set options to Default?','ald_bsearch_plugin'); ?>')) return false;" />
    </p>
  </form>
</div>
<?php

}

/* Add menu item in WP-Admin */
function bsearch_adminmenu() {
	if (function_exists('current_user_can')) {
		// In WordPress 2.x
		if (current_user_can('manage_options')) {
			$bsearch_is_admin = true;
		}
	} else {
		// In WordPress 1.x
		global $user_ID;
		if (user_can_edit_user($user_ID, 0)) {
			$bsearch_is_admin = true;
		}
	}

	if ((function_exists('add_options_page'))&&($bsearch_is_admin)) {
		$plugin_page = add_options_page(__("Better Search", 'myald_bsearch_plugin'), __("Better Search", 'myald_bsearch_plugin'), 9, 'bsearch_options', 'bsearch_options');
		add_action( 'admin_head-'. $plugin_page, 'bsearch_adminhead' );
	}
}
add_action('admin_menu', 'bsearch_adminmenu');

function bsearch_adminhead() {
	global $bsearch_url;

?>
<script type="text/javascript" src="<?php echo $bsearch_url ?>/jscolor/jscolor.js"></script>
<?php }



// Dashboard for Better Search
function bsearch_pop_dashboard() {
	$bsearch_settings = bsearch_read_options();
	$limit = $bsearch_settings[heatmap_limit];
	$largest = intval($bsearch_settings[heatmap_largest]);
	$smallest = intval($bsearch_settings[heatmap_smallest]);
	$hot = $bsearch_settings[heatmap_hot];
	$cold = $bsearch_settings[heatmap_cold];
	$unit = $bsearch_settings[heatmap_unit];
	$before = $bsearch_settings[heatmap_before];
	$after = $bsearch_settings[heatmap_after];

	echo bsearch_heatmap(false, $smallest, $largest, $unit, $cold, $hot, $before, $after, '', $limit);
	
	if ($bsearch_settings['show_credit']) echo '<br /><small>Powered by <a href="http://ajaydsouza.com/wordpress/plugins/better-search/">Better Search plugin</a></small>';
}
// Dashboard for Daily Better Search
function bsearch_pop_daily_dashboard() {
	$bsearch_settings = bsearch_read_options();
	$limit = $bsearch_settings[heatmap_limit];
	$largest = intval($bsearch_settings[heatmap_largest]);
	$smallest = intval($bsearch_settings[heatmap_smallest]);
	$hot = $bsearch_settings[heatmap_hot];
	$cold = $bsearch_settings[heatmap_cold];
	$unit = $bsearch_settings[heatmap_unit];
	$before = $bsearch_settings[heatmap_before];
	$after = $bsearch_settings[heatmap_after];

	echo bsearch_heatmap(true, $smallest, $largest, $unit, $cold, $hot, $before, $after, '', $limit);
	
	if ($bsearch_settings['show_credit']) echo '<br /><small>Powered by <a href="http://ajaydsouza.com/wordpress/plugins/better-search/">Better Search plugin</a></small>';
}
 
function bsearch_pop_dashboard_setup() {
	if (function_exists('wp_add_dashboard_widget')) {
		wp_add_dashboard_widget( 'bsearch_pop_dashboard', __( 'Popular Searches' ), 'bsearch_pop_dashboard' );
		wp_add_dashboard_widget( 'bsearch_pop_daily_dashboard', __( 'Daily Popular Searches' ), 'bsearch_pop_daily_dashboard' );
	}
}
add_action('wp_dashboard_setup', 'bsearch_pop_dashboard_setup');

?>