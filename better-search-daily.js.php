<?php
//"better-search-daily.js.php" Display Daily Popular Lists.
Header("content-type: application/x-javascript");

if (!function_exists('add_action')) {
	$wp_root = '../../..';
	if (file_exists($wp_root.'/wp-load.php')) {
		require_once($wp_root.'/wp-load.php');
	} else {
		require_once($wp_root.'/wp-config.php');
	}
}

// Display counter using Ajax
function bsearch_daily_searches() {
	global $wpdb, $siteurl, $tableposts, $s;
	$table_name = $wpdb->prefix . "bsearch_daily";
	
	$is_widget = intval($_GET['widget']);
	
	$bsearch_settings = bsearch_read_options();
	$limit = $bsearch_settings[heatmap_limit];
	$largest = intval($bsearch_settings[heatmap_largest]);
	$smallest = intval($bsearch_settings[heatmap_smallest]);
	$hot = $bsearch_settings[heatmap_hot];
	$cold = $bsearch_settings[heatmap_cold];
	$unit = $bsearch_settings[heatmap_unit];
	$before = $bsearch_settings[heatmap_before];
	$after = $bsearch_settings[heatmap_after];

	$output = '';
	
	if(!$is_widget) {
		$output .= '<div class="bsearch_heatmap">';	
		$output .= $bsearch_settings['title_daily'];
	}
	
	$output .= '<div text-align:center>'.get_bsearch_heatmap(true, $smallest, $largest, $unit, $cold, $hot, $before, $after, '',$limit).'</div>';
		
	if ($bsearch_settings['show_credit']) $output .= '<br /><small>Powered by <a href="http://ajaydsouza.com/wordpress/plugins/better-search/">Better Search plugin</a></small>';
		
	if(!$is_widget) {
		$output .= '</div>';
	}
	
	echo "document.write('".$output."')";
}
bsearch_daily_searches();
?>
