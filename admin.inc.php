<?php
/**********************************************************************
*					Admin Page										*
*********************************************************************/
if (!defined('ABSPATH')) die("Aren't you supposed to come here via WP-Admin?");

if (!defined('BSEARCH_LOCAL_NAME')) define('BSEARCH_LOCAL_NAME', 'better-search');

// Pre-2.6 compatibility
if ( ! defined( 'WP_CONTENT_URL' ) )
      define( 'WP_CONTENT_URL', get_option( 'siteurl' ) . '/wp-content' );
if ( ! defined( 'WP_CONTENT_DIR' ) )
      define( 'WP_CONTENT_DIR', ABSPATH . 'wp-content' );
if ( ! defined( 'WP_PLUGIN_URL' ) )
      define( 'WP_PLUGIN_URL', WP_CONTENT_URL. '/plugins' );
if ( ! defined( 'WP_PLUGIN_DIR' ) )
      define( 'WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins' );

// Guess the location
$bsearch_path = WP_PLUGIN_DIR.'/'.plugin_basename(dirname(__FILE__));
$bsearch_url = WP_PLUGIN_URL.'/'.plugin_basename(dirname(__FILE__));

function bsearch_options() {
	
	global $wpdb;
    $poststable = $wpdb->posts;

	$bsearch_settings = bsearch_read_options();

	if($_POST['bsearch_save']){
		$bsearch_settings[title] = ($_POST['title']);
		$bsearch_settings[title_daily] = ($_POST['title_daily']);
		$bsearch_settings[daily_range] = intval($_POST['daily_range']);
		$bsearch_settings[limit] = intval($_POST['limit']);
		$bsearch_settings[use_fulltext] = (($_POST['use_fulltext']) ? true : false);
		$bsearch_settings[d_use_js] = (($_POST['d_use_js']) ? true : false);
		$bsearch_settings[include_pages] = (($_POST['include_pages']) ? true : false);
		$bsearch_settings[include_attachments] = (($_POST['include_attachments']) ? true : false);
		$bsearch_settings[show_credit] = (($_POST['show_credit']) ? true : false);
		
		$bsearch_settings[heatmap_smallest] = ($_POST['heatmap_smallest']);
		$bsearch_settings[heatmap_largest] = ($_POST['heatmap_largest']);
		$bsearch_settings[heatmap_limit] = ($_POST['heatmap_limit']);
		$bsearch_settings[heatmap_cold] = ($_POST['heatmap_cold']);
		$bsearch_settings[heatmap_hot] = ($_POST['heatmap_hot']);
		$bsearch_settings[heatmap_before] = ($_POST['heatmap_before']);
		$bsearch_settings[heatmap_after] = ($_POST['heatmap_after']);

		$bsearch_settings[weight_content] = intval($_POST['weight_content']);
		$bsearch_settings[weight_title] = intval($_POST['weight_title']);
		
		update_option('ald_bsearch_settings', $bsearch_settings);
		
		$str = '<div id="message" class="updated fade"><p>'. __('Options saved successfully.', BSEARCH_LOCAL_NAME) .'</p></div>';
		echo $str;
	}
	
	if ($_POST['bsearch_default']){
		delete_option('ald_bsearch_settings');
		$bsearch_settings = bsearch_default_options();
		update_option('ald_bsearch_settings', $bsearch_settings);
		
		$str = '<div id="message" class="updated fade"><p>'. __('Options set to Default.', BSEARCH_LOCAL_NAME) .'</p></div>';
		echo $str;
	}
	if ($_POST['bsearch_trunc_all']){
		bsearch_trunc_count(false);
		$str = '<div id="message" class="updated fade"><p>'. __('Popular searches count reset',bsearch_LOCAL_NAME) .'</p></div>';
		echo $str;
	}

	if ($_POST['bsearch_trunc_daily']){
		bsearch_trunc_count(true);
		$str = '<div id="message" class="updated fade"><p>'. __('Daily popular searches count reset',bsearch_LOCAL_NAME) .'</p></div>';
		echo $str;
	}

?>

<div class="wrap">
  <h2>Better Search </h2>
  <div id="options-div">
  <form method="post" id="bsearch_options" name="bsearch_options" style="border: #ccc 1px solid; padding: 10px">
    <fieldset class="options">
    <legend>
    <h3>
      <?php _e('Options:', BSEARCH_LOCAL_NAME); ?>
    </h3>
    </legend>
    <p>
      <label>
      <?php _e('Number of Search Results per page: ', BSEARCH_LOCAL_NAME); ?>
      <input type="textbox" name="limit" id="limit" value="<?php echo stripslashes($bsearch_settings[limit]); ?>">
      </label>
    </p>
    <p>
      <label>
      <?php _e('Title of Overall Popular Searches: ', BSEARCH_LOCAL_NAME); ?>
      <input type="textbox" name="title" id="title" value="<?php echo stripslashes($bsearch_settings[title]); ?>">
      </label>
    </p>
    <p>
      <label>
      <?php _e('Title of Daily Popular Searches: ', BSEARCH_LOCAL_NAME); ?>
      <input type="textbox" name="title_daily" id="title_daily" value="<?php echo stripslashes($bsearch_settings[title_daily]); ?>">
      </label>
    </p>
    <p>
      <label>
      <?php _e('Daily Popular should contain searches of how many days? ', BSEARCH_LOCAL_NAME); ?>
      <input type="textbox" name="daily_range" id="daily_range" size="3" value="<?php echo stripslashes($bsearch_settings[daily_range]); ?>">
      </label>
    </p>
    <p>
      <label>
      <input type="checkbox" name="include_pages" id="include_pages" <?php if ($bsearch_settings[include_pages]) echo 'checked="checked"' ?> />
      <?php _e('Include WordPress static pages in Search Results', BSEARCH_LOCAL_NAME); ?>
      </label>
    </p>
    <p>
      <label>
      <input type="checkbox" name="include_attachments" id="include_attachments" <?php if ($bsearch_settings[include_attachments]) echo 'checked="checked"' ?> />
      <?php _e('Include attachments in Search Results', BSEARCH_LOCAL_NAME); ?>
      </label>
    </p>
    <p>
      <label>
      <input type="checkbox" name="use_fulltext" id="use_fulltext" <?php if ($bsearch_settings[use_fulltext]) echo 'checked="checked"' ?> />
      <?php _e('Enable mySQL FULLTEXT searching. Disabling this option will no longer give relevancy based results.', BSEARCH_LOCAL_NAME); ?>
      </label>
    </p>
    <p>
      <label>
      <input type="checkbox" name="d_use_js" id="d_use_js" <?php if ($bsearch_settings[d_use_js]) echo 'checked="checked"' ?> />
      <?php _e('Bypass Cache for daily popular searches\' heatmap? This options uses JavaScript to load the post and can increase your page load time', BSEARCH_LOCAL_NAME); ?>
      </label>
    </p>
    <p>
      <label>
      <input type="checkbox" name="show_credit" id="show_credit" <?php if ($bsearch_settings[show_credit]) echo 'checked="checked"' ?> />
      <?php _e('A link to the plugin is added as an extra list item to the list of popular searches. Not mandatory, but thanks if you do it!', BSEARCH_LOCAL_NAME); ?>
      </label>
    </p>
	</fieldset>
    <fieldset class="options">
    <legend>
    <h3>
      <?php _e('Fine tuning:', BSEARCH_LOCAL_NAME); ?>
    </h3>
    </legend>
    <p>
      <label>
      <?php _e('Weight of the title: ', BSEARCH_LOCAL_NAME); ?>
      <input type="textbox" name="weight_title" id="weight_title" value="<?php echo stripslashes($bsearch_settings[weight_title]); ?>">
      </label>
    </p>
    <p>
      <label>
      <?php _e('Weight of the content: ', BSEARCH_LOCAL_NAME); ?>
      <input type="textbox" name="weight_content" id="weight_content" value="<?php echo stripslashes($bsearch_settings[weight_content]); ?>">
      </label>
    </p>
	</fieldset>
    <fieldset class="options">
    <legend>
    <h3>
      <?php _e('Heatmap (Popular searches) Options:', BSEARCH_LOCAL_NAME); ?>
    </h3>
    </legend>
    <p>
      <label>
      <?php _e('Number of search terms to display: ', BSEARCH_LOCAL_NAME); ?>
      <input type="textbox" name="heatmap_limit" id="heatmap_limit" value="<?php echo stripslashes($bsearch_settings[heatmap_limit]); ?>">
      </label>
    </p>
    <p>
      <label>
      <?php _e('Font size of least popular search term: ', BSEARCH_LOCAL_NAME); ?>
      <input type="textbox" name="heatmap_smallest" id="heatmap_smallest" value="<?php echo stripslashes($bsearch_settings[heatmap_smallest]); ?>">
      </label>
    </p>
    <p>
      <label>
      <?php _e('Font size of most popular search term: ', BSEARCH_LOCAL_NAME); ?>
      <input type="textbox" name="heatmap_largest" id="heatmap_largest" value="<?php echo stripslashes($bsearch_settings[heatmap_largest]); ?>">
      </label>
    </p>
    <p>
      <label>
      <?php _e('Color of least popular search term: ', BSEARCH_LOCAL_NAME); ?>
      <input type="textbox" class="color" name="heatmap_cold" id="heatmap_cold" value="<?php echo stripslashes($bsearch_settings[heatmap_cold]); ?>">
      </label>
    </p>
    <p>
      <label>
      <?php _e('Color of most popular search term: ', BSEARCH_LOCAL_NAME); ?>
      <input type="textbox" class="color" name="heatmap_hot" id="heatmap_hot" value="<?php echo stripslashes($bsearch_settings[heatmap_hot]); ?>">
      </label>
    </p>
    <p>
      <label>
      <?php _e('Text to include before each search term in heatmap', BSEARCH_LOCAL_NAME); ?>
      <input type="textbox" name="heatmap_before" id="heatmap_before" size="3" value="<?php echo stripslashes($bsearch_settings[heatmap_before]); ?>">
      </label>
    </p>
    <p>
      <label>
      <?php _e('Text to include after each search term in heatmap', BSEARCH_LOCAL_NAME); ?>
      <input type="textbox" name="heatmap_after" id="heatmap_after" size="3" value="<?php echo stripslashes($bsearch_settings[heatmap_after]); ?>">
      </label>
    </p>
    </fieldset>
    <p>
      <input type="submit" name="bsearch_save" id="bsearch_save" value="Save Options" style="border:#00CC00 1px solid" />
      <input name="bsearch_default" type="submit" id="bsearch_default" value="Default Options" style="border:#FF0000 1px solid" onclick="if (!confirm('<?php _e('Do you want to set options to Default?', BSEARCH_LOCAL_NAME); ?>')) return false;" />
    </p>
    <h4>
      <?php _e('Reset count',bsearch_LOCAL_NAME); ?>
    </h4>
    <p>
      <?php _e('This cannot be reversed. Make sure that your database has been backed up before proceeding',bsearch_LOCAL_NAME); ?>
    </p>
    <p>
      <input name="bsearch_trunc_all" type="submit" id="bsearch_trunc_all" value="<?php _e('Reset popular search count', BSEARCH_LOCAL_NAME); ?>" style="border:#900 1px solid" onclick="if (!confirm('<?php _e('Are you sure you want to reset the popular searches?',bsearch_LOCAL_NAME); ?>')) return false;" />
      <input name="bsearch_trunc_daily" type="submit" id="bsearch_trunc_daily" value="<?php _e('Reset daily popular search count', BSEARCH_LOCAL_NAME); ?>" style="border:#C00 1px solid" onclick="if (!confirm('<?php _e('Are you sure you want to reset the daily popular searches?',bsearch_LOCAL_NAME); ?>')) return false;" />
    </p>
  </form>
</div>
  <div id="side">
	<div class="side-widget">
		<span class="title"><?php _e('Support the development',BSEARCH_LOCAL_NAME) ?></span>
		<div id="donate-form">
			<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
			<input type="hidden" name="cmd" value="_xclick">
			<input type="hidden" name="business" value="KGVN7LJLLZCMY">
			<input type="hidden" name="lc" value="IN">
			<input type="hidden" name="item_name" value="Donation for Better Search">
			<input type="hidden" name="item_number" value="bsearch">
			<strong><?php _e('Enter amount in USD: ',BSEARCH_LOCAL_NAME) ?></strong> <input name="amount" value="10.00" size="6" type="text"><br />
			<input type="hidden" name="currency_code" value="USD">
			<input type="hidden" name="button_subtype" value="services">
			<input type="hidden" name="bn" value="PP-BuyNowBF:btn_donate_LG.gif:NonHosted">
			<input type="image" src="https://www.paypal.com/en_US/i/btn/btn_donate_LG.gif" border="0" name="submit" alt="<?php _e('Send your donation to the author of',BSEARCH_LOCAL_NAME) ?> Better Search?">
			<img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1">
			</form>
		</div>
	</div>
	<div class="side-widget">
	<span class="title"><?php _e('Quick links') ?></span>				
	<ul>
		<li><a href="http://ajaydsouza.com/wordpress/plugins/better-search/"><?php _e('Better Search ');_e('plugin page',BSEARCH_LOCAL_NAME) ?></a></li>
		<li><a href="http://ajaydsouza.com/wordpress/plugins/"><?php _e('Other plugins',BSEARCH_LOCAL_NAME) ?></a></li>
		<li><a href="http://ajaydsouza.com/"><?php _e('Ajay\'s blog',BSEARCH_LOCAL_NAME) ?></a></li>
		<li><a href="http://ajaydsouza.com/support/"><?php _e('Support',BSEARCH_LOCAL_NAME) ?></a></li>
		<li><a href="http://twitter.com/ajaydsouza"><?php _e('Follow @ajaydsouza on Twitter',BSEARCH_LOCAL_NAME) ?></a></li>
		<li><a href="http://facebook.com/ajaydsouzacom"><?php _e('Become our fan on Facebook',BSEARCH_LOCAL_NAME) ?></a></li>
	</ul>
	</div>
	<div class="side-widget">
	<span class="title"><?php _e('Recent developments',BSEARCH_LOCAL_NAME) ?></span>				
	<?php require_once(ABSPATH . WPINC . '/rss.php'); wp_widget_rss_output('http://ajaydsouza.com/archives/category/wordpress/plugins/feed/', array('items' => 5, 'show_author' => 0, 'show_date' => 1));
	?>
	</div>
  </div>
  
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
		$plugin_page = add_options_page(__("Better Search", BSEARCH_LOCAL_NAME), __("Better Search", BSEARCH_LOCAL_NAME), 9, 'bsearch_options', 'bsearch_options');
		add_action( 'admin_head-'. $plugin_page, 'bsearch_adminhead' );
	}
}
add_action('admin_menu', 'bsearch_adminmenu');

function bsearch_adminhead() {
	global $bsearch_url;

?>
<script type="text/javascript" src="<?php echo $bsearch_url ?>/jscolor/jscolor.js"></script>
<link rel="stylesheet" type="text/css" href="<?php echo $bsearch_url ?>/admin-styles.css" />
<?php }

// Function to clean the database
function bsearch_trunc_count($daily=true) {
	global $wpdb;
	$table_name = ($daily) ? $wpdb->prefix . "bsearch_daily" : $wpdb->prefix . "bsearch";

	$sql = "TRUNCATE TABLE $table_name";
	$wpdb->query($sql);
}

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

	echo get_bsearch_heatmap(false, $smallest, $largest, $unit, $cold, $hot, $before, $after, '', $limit);
	
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

	echo get_bsearch_heatmap(true, $smallest, $largest, $unit, $cold, $hot, $before, $after, '', $limit);
	
	if ($bsearch_settings['show_credit']) echo '<br /><small>Powered by <a href="http://ajaydsouza.com/wordpress/plugins/better-search/">Better Search plugin</a></small>';
}
 
function bsearch_pop_dashboard_setup() {
	if (function_exists('wp_add_dashboard_widget')) {
		wp_add_dashboard_widget( 'bsearch_pop_dashboard', __( 'Popular Searches', BSEARCH_LOCAL_NAME ), 'bsearch_pop_dashboard' );
		wp_add_dashboard_widget( 'bsearch_pop_daily_dashboard', __( 'Daily Popular Searches', BSEARCH_LOCAL_NAME ), 'bsearch_pop_daily_dashboard' );
	}
}
add_action('wp_dashboard_setup', 'bsearch_pop_dashboard_setup');

function bsearch_plugin_notice( $plugin ) {
	global $cache_enabled;
 	if( $plugin == 'better-search/admin.inc.php' && !$cache_enabled && function_exists( "admin_url" ) )
		echo '<td colspan="5" class="plugin-update">Better Search must be configured. Go to <a href="' . admin_url( 'options-general.php?page=bsearch_options' ) . '">the admin page</a> to enable and configure the plugin.</td>';
}
//add_action( 'after_plugin_row', 'bsearch_plugin_notice' );

?>