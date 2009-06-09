<?php
/*
Plugin Name: Better Search
Version:     1.1.4
Plugin URI:  http://ajaydsouza.com/wordpress/plugins/better-search/
Description: Replace the default WordPress search with a contextual search. Search results are sorted by relevancy ensuring a better visitor search experience. <a href="options-general.php?page=bsearch_options">Configure...</a>
Author:      Ajay D'Souza
Author URI:  http://ajaydsouza.com/
*/

if (!defined('ABSPATH')) die("Aren't you supposed to come here via WP-Admin?");

global $bsearch_db_version;
$bsearch_db_version = "1.0";

define('ALD_bsearch_DIR', dirname(__FILE__));
define('BSEARCH_LOCAL_NAME', 'better-search');

function ald_bsearch_init() {
	//* Begin Localization Code */
	$bsearch_localizationName = BSEARCH_LOCAL_NAME;
	$bsearch_comments_locale = get_locale();
	$bsearch_comments_mofile = ALD_bsearch_DIR . "/languages/" . $bsearch_localizationName . "-". $bsearch_comments_locale.".mo";
	load_textdomain($bsearch_localizationName, $bsearch_comments_mofile);
	//* End Localization Code */
}
add_action('init', 'ald_bsearch_init');

/*********************************************************************
*				Main Function (Do not edit)							*
********************************************************************/

add_action('template_redirect', 'bsearch_template_redirect', 1);
function bsearch_template_redirect() {
	// not a search page; don't do anything and return
	if ( (stripos($_SERVER['REQUEST_URI'], '?s=') === FALSE) && (stripos($_SERVER['REQUEST_URI'], '/search/') === FALSE))
	{
		return;
	}
	
	$s = attribute_escape(apply_filters('the_search_query', get_search_query()));
	$s = quote_smart($s);
	$s = RemoveXSS($s);

	$bsearch_settings = bsearch_read_options();
	add_action('wp_head', 'bsearch_head');
	add_filter('wp_title', 'bsearch_title');

	// If there is a template file then we use it
	$exists = file_exists(TEMPLATEPATH . '/better-search-template.php');
	if ($exists)
	{
		include_once(TEMPLATEPATH . '/better-search-template.php');
		exit;
	}
	elseif(file_exists(TEMPLATEPATH . '/search.php'))
	{
		include_once(TEMPLATEPATH . '/search.php');
		exit;
	}


	get_header();

	echo '<div id="content" class="bsearch_results_page">';
	echo '<div id="heatmap">';
	echo '<div style="padding: 5px; border-bottom: 1px dashed #ccc">';
	echo '<h2>';
	echo strip_tags($bsearch_settings['title_daily']);
	echo '</h2>';
	echo get_bsearch_heatmap(true);
	echo '</div>';
	echo '<div style="padding: 5px;">';
	echo '<h2>';
	echo strip_tags($bsearch_settings['title']);
	echo '</h2>';
	echo get_bsearch_heatmap(false);
	echo '</div>';
	echo '<div style="clear:both">&nbsp;</div>';
	echo '</div>';


	$form = get_bsearch_form($s);
	echo $form;	

	echo '<div id="searchresults"><h2>';
	_e('Search Results for: ', BSEARCH_LOCAL_NAME);
	echo '&quot;'.$s.'&quot;';
	echo '</h2>';

	bsearch_results($s,$limit);

	echo '</div>';
	echo '</div>';

	//echo get_sidebar();

	get_footer();
	exit;
}

// Search Results
function bsearch_results($s = '',$limit) {
	global $wpdb;
	$bsearch_settings = bsearch_read_options();

	if (!($limit)) $limit = intval(RemoveXSS(quote_smart($_GET['limit']))); // Read from GET variable
	if (!($limit)) $limit = $bsearch_settings['limit']; // Default number of results as entered in WP-Admin
	$page = intval(RemoveXSS(quote_smart($_GET['paged']))); // Read from GET variable
	if (!($page)) $page = 0; // Default page value.
	
	if ($s == '') {
		$s = attribute_escape(apply_filters('the_search_query', get_search_query()));
		$s = quote_smart($s);
		$s = RemoveXSS($s);
	}
	$cntaccess_wordsize = explode(' ', $s);	// Store words in search query
	$cntaccesser1 = count($cntaccess_wordsize);		// Count number of words in search query
	$use_fulltext = false;

	while ($cntaccesser1 > 0) {	// Disable Fulltext Search if length of all words in search is less than 3.
		if (strlen($cntaccess_wordsize[$cntaccesser1-1]) > 3) { $use_fulltext = true;}
		$cntaccesser1--;
	}
	if (!$bsearch_settings['use_fulltext'])	$use_fulltext = false;

	if ($use_fulltext == false) {
		$s = addslashes_gpc($s);
		$s = preg_replace('/, +/', ' ', $s);
		$s = str_replace(',', ' ', $s);
		$s = str_replace('"', ' ', $s);
		$s = trim($s);
		if ($exact) {
			$n = '';
		} else {
			$n = '%';
		}

		$s_array = explode(' ',$s);

		$sql = "SELECT ID,post_title,post_content,post_excerpt,post_date FROM ".$wpdb->posts." WHERE (";
		$sql .= "((post_title LIKE '".$n.$s_array[0].$n."') OR (post_content LIKE '".$n.$s_array[0].$n."'))";
		for ( $i = 1; $i < count($s_array); $i = $i + 1) {
			$sql .= " AND ((post_title LIKE '".$n.$s_array[$i].$n."') OR (post_content LIKE '".$n.$s_array[$i].$n."'))";
		}
		$sql .= " OR (post_title LIKE '".$n.$s.$n."') OR (post_content LIKE '".$n.$s.$n."')";
		$sql .= ") AND post_status = 'publish' ";
		if ($bsearch_settings['include_pages']) 
			$sql .= "AND (post_type='post' OR post_type = 'page') "; 
		else 
			$sql .= "AND post_type = 'post' ";
	} else {
		$sql = "SELECT ID,post_title,post_content,post_excerpt,post_date, MATCH(post_title,post_content) AGAINST ('".$s."') AS score FROM ".$wpdb->posts." WHERE MATCH (post_title,post_content) AGAINST ('".$s."') AND post_status = 'publish' ";
		if ($bsearch_settings['include_pages']) 
			$sql .= "AND (post_type='post' OR post_type = 'page') "; 
		else 
			$sql .= "AND post_type = 'post' ";
	}

	$searches = $wpdb->get_results($sql);
	$numrows = 0;
	if ($searches) {
		foreach ($searches as $search) {
			$numrows++;
		}
	}
	
	$pages = intval($numrows/$limit); // Number of results pages.

	// $pages now contains int of pages, unless there is a remainder from division.

	if ($numrows % $limit) {$pages++;} // has remainder so add one page

	$current = ($page/$limit) + 1; // Current page number.

	if (($pages < 1) || ($pages == 0)) {$total = 1;} // If $pages is less than one or equal to 0, total pages is 1.
	else {	$total = $pages;} // Else total pages is $pages value.

	$first = $page + 1; // The first result.

	if (!((($page + $limit) / $limit) >= $pages) && $pages != 1) {$last = $page + $limit;} //If not last results page, last result equals $page plus $limit.
	else{$last = $numrows;} // If last results page, last result equals total number of results.
	

	if ($use_fulltext == false) {
		$s = addslashes_gpc($s);
		$s = preg_replace('/, +/', ' ', $s);
		$s = str_replace(',', ' ', $s);
		$s = str_replace('"', ' ', $s);
		$s = trim($s);
		if ($exact) {
			$n = '';
		} else {
			$n = '%';
		}

		$s_array = explode(' ',$s);

		$sql .= "LIMIT $page, $limit";
	} else {
		$sql .= "LIMIT $page, $limit";
	}

	$searches = $wpdb->get_results($sql);
	
	$output = '';

	// Lets start printing the results
	if($s != ''){
		if($searches){
			$output .= '<table width="100%" border="0">
			 <tr>
			  <td width="50%" align="left">';
			$output .= __('Results', BSEARCH_LOCAL_NAME);
			$output .= ' <strong>'.$first.'</strong> - <strong>'.$last.'</strong> ';
			$output .= __('of', BSEARCH_LOCAL_NAME);
			$output .= ' <strong>'.$numrows.'</strong>
			  </td>
			  <td width="50%" align="right">';
			$output .= 'Page', BSEARCH_LOCAL_NAME);
			$output .= ' <strong>'.$current.'</strong> ';
			$output .= __('of', BSEARCH_LOCAL_NAME);
			$output .= ' <strong>'.$total.'</strong>
			  </td>
			 </tr>
			 <tr>
			  <td colspan="2" align="right">&nbsp;</td>
			 </tr>
			 <tr>
			  <td align="left"></td>';
			$output .= '<td align="right">';
			$output .= __('Results per-page', BSEARCH_LOCAL_NAME);
			$output .= ': <a href="'.get_settings('siteurl').'/?s='.$s.'&limit=10">10</a> | <a href="'.get_settings('siteurl').'/?s='.$s.'&limit=20">20</a> | <a href="'.get_settings('siteurl').'/?s='.$s.'&limit=50">50</a> | <a href="'.get_settings('siteurl').'/?s='.$s.'&limit=100">100</a> 
			  </td>
			 </tr>
			 <tr>
			  <td colspan="2" align="right"><hr /></td>
			 </tr>
			</table>';
			echo $output;

			foreach($searches as $search){
				$post_title = trim(stripslashes($search->post_title));
				$excerpt = htmlspecialchars(trim(stripslashes($search->post_excerpt)));
				$content = trim(stripslashes($search->post_content)); ?>
				<h2><a href="<?php echo get_permalink($search->ID);?>" rel="bookmark"><?php echo $post_title;?></a></h2>
				<p>
					<?php if ($search->score > 0) {
						_e('Relevance Score: ', BSEARCH_LOCAL_NAME); printf("%.3f", $search->score);
						echo '&nbsp;&nbsp;&nbsp;&nbsp;';
					}
					echo date('Y-m-d H:i:s',strtotime($search->post_date)); ?>
				</p>
				<p><?php if($excerpt){echo $excerpt;} else {echo search_excerpt($content);} ?></p>
				<?php 
			} //end of foreach loop


			get_settings('siteurl');
			$output =   '<p style="text-align:center">';
			if ($page != 0) { // Don't show back link if current page is first page.
				$back_page = $page - $limit;
				$output .=  "<a href=\"".get_settings('siteurl')."/?s=$s&paged=$back_page&limit=$limit\">&laquo; ";
				$output .=  __('Previous', BSEARCH_LOCAL_NAME);
				$output .=  "</a>    \n";
			}

			for ($i=1; $i <= $pages; $i++) // loop through each page and give link to it.
			{
				$ppage = $limit*($i - 1);
				if ($ppage == $page){
				$output .=  ("<b>$i</b>\n");} // If current page don't give link, just text.
				else{
					$output .=  ("<a href=\"".get_settings('siteurl')."/?s=$s&paged=$ppage&limit=$limit\">$i</a> \n");
				}
			}

			if (!((($page+$limit) / $limit) >= $pages) && $pages != 1) { // If last page don't give next link.
				$next_page = $page + $limit;
				$output .=  "    <a href=\"".get_settings('siteurl')."/?s=$s&paged=$next_page&limit=$limit\">";
				$output .=  __('Next', BSEARCH_LOCAL_NAME);
				$output .=  " &raquo;</a>";
			}
			$output .=   '</p>';
			echo $output;


		}else{
			echo '<p>';
			_e('No results.', BSEARCH_LOCAL_NAME);
			echo '</p>';
		}
	}else{
		echo '<p>';
		_e('Please type in your search terms. Use descriptive words since this search is intelligent.', BSEARCH_LOCAL_NAME);
		echo '</p>';
	}

	if ($bsearch_settings['show_credit']) echo '<hr /><p style="text-align:center">Powered by <a href="http://ajaydsouza.com/wordpress/plugins/better-search/">Better Search plugin</a></p>';

}


// Search Heatmap
function get_bsearch_heatmap($daily=false, $smallest=10, $largest=20, $unit="pt", $cold="00f", $hot="f00", $before='', $after='&nbsp;', $exclude='', $limit='30') {
	global $wpdb;
	$table_name = $wpdb->prefix . "bsearch";
	if ($daily) $table_name .= "_daily";	// If we're viewing daily posts, set this to true
	$output = '';
	
	if(!$daily) {
		$query = "SELECT searchvar, cntaccess FROM $table_name WHERE accessedid IN (SELECT accessedid FROM $table_name WHERE searchvar <> '' ORDER BY cntaccess DESC, searchvar ASC) ORDER by accessedid LIMIT $limit";
	} else {
		$daily_range = $bsearch_settings[daily_range]. ' DAY';
		$current_date = $wpdb->get_var("SELECT DATE_ADD(DATE_SUB(CURDATE(), INTERVAL $daily_range), INTERVAL 1 DAY) ");
	
		$query = "
			SELECT DISTINCT wp1.searchvar, wp2.sumCount
			FROM $table_name wp1,
					(SELECT searchvar, SUM(cntaccess) as sumCount
					FROM $table_name
					WHERE dp_date >= '$current_date' 
					GROUP BY searchvar
					ORDER BY sumCount DESC LIMIT $limit) wp2
					WHERE wp1.searchvar = wp2.searchvar
			ORDER by wp1.searchvar ASC
		";
	}

	$results = $wpdb->get_results($query);
	
	if ($results) {
		foreach ($results as $result) {
			if(!$daily) $cntaccesss[] = $result->cntaccess; else $cntaccesss[] = $result->sumCount;
		}
		$min = min($cntaccesss);
		$max = max($cntaccesss);
		$spread = $max - $min;

		// Calculate various font sizes
		if ($largest != $smallest) {
			$fontspread = $largest - $smallest;
			if ($spread != 0) {
				$fontstep = $fontspread / $spread;
			} else {
				$fontstep = 0;
			}
		}
		
		// Calculate colors
		if ($hot != $cold) {		
			for ($i = 0; $i < 3; $i++) {
				$coldval[] = hexdec($cold[$i]);
				$hotval[] = hexdec($hot[$i]);
				$colorspread[] = hexdec($hot[$i]) - hexdec($cold[$i]); 
				if ($spread != 0) {
					$colorstep[] = (hexdec($hot[$i]) - hexdec($cold[$i])) / $spread;
				} else {
					$colorstep[] = 0;
				}
			}
		}
		
		foreach ($results as $result) {
			if(!$daily) $cntaccess = $result->cntaccess; else $cntaccess = $result->sumCount;
			$textsearchvar = stripslashes($result->searchvar);
			$url  = get_settings('siteurl').'/?s='.$textsearchvar;
			$fraction = ($cntaccess - $min);
			$fontsize = $smallest + ($fontstep * $fraction);
			$color = "";
			for ($i = 0; $i < 3; $i++) {
				$color .= dechex($coldval[$i] + ($colorstep[$i] * $fraction));
			}
			$style = 'style="';
			if ($largest != $smallest) {
				$style .= "font-size:".round($fontsize).$unit.";";
			}
			if ($hot != $cold) {
				$style .= "color:#".$color.";";
			}
			$style .= '"';
			
			$output .= $before.'<a href="'.$url.'" title="';
			$output .= __('Search for ', BSEARCH_LOCAL_NAME);
			$output .= $textsearchvar;
			$output .= __(' (searched ', BSEARCH_LOCAL_NAME);
			$output .= $cntaccess;
			$output .= __(' times)', BSEARCH_LOCAL_NAME);
			$output .= '" '.$style.'>'.$textsearchvar.'</a>'.$after.' ';
		}
	} else {
		$output = __('No searches made yet', BSEARCH_LOCAL_NAME);
	}
	return $output;
}

// Function to update search count
function bsearch_increment_counter($s) {
	$output = '<script type="text/javascript" src="'.get_bloginfo('wpurl').'/wp-content/plugins/better-search/better-search-addcount.js.php?bsearch_id='.$s.'"></script>';
	echo $output;
}


// Insert into WordPress Head
function bsearch_head()
{
	$s = attribute_escape(apply_filters('the_search_query', get_search_query()));
	$s = quote_smart($s);
	$s = RemoveXSS($s);
	if((empty($paged))&&(empty($limit))) bsearch_increment_counter($s);

	// If there is a template file then we use it
	$exists = file_exists(TEMPLATEPATH . '/better-search-template.php');
	if (!$exists)
	{
?>
	<style type="text/css">
	#bsearchform { margin: 20px; padding: 20px; }
	#heatmap { margin: 20px; padding: 20px; border: 1px dashed #ccc }
	.bsearch_results_page { width:90%; margin: 20px; padding: 20px; }
	</style>
<?php	}
}

// Insert into WordPress Title
function bsearch_title($title)
{
	$s = apply_filters('the_search_query', get_search_query());
	if (isset($s))
	{
		// change status code to 200 OK since /search/ returns status code 404
		@header("HTTP/1.1 200 OK",1);
		@header("Status: 200 OK", 1);
		if ($s == '') return $s; else return __('Search Results for ', BSEARCH_LOCAL_NAME). '&quot;' . $s.'&quot; | ';
	}
	else
	{
		return $title;
	}
}

// Function to fetch search form
function get_bsearch_form($s)
{
	if ($s == '') {
		$s = attribute_escape(apply_filters('the_search_query', get_search_query()));
		$s = quote_smart($s);
		$s = RemoveXSS($s);
	}
	$form = '<div style="text-align:center"><form method="get" id="bsearchform" action="' . get_option('home') . '/" >
	<label class="hidden" for="s">' . __('Search for:', BSEARCH_LOCAL_NAME) . '</label>
	<input type="text" value="' . $s . '" name="s" id="s" />
	<input type="submit" id="searchsubmit" value="'.attribute_escape(__('Search Again'), BSEARCH_LOCAL_NAME).'" />
	</form></div>';

	return $form;
}

// Function to retrieve Daily Popular Searches Title
function get_bsearch_title_daily($text_only = true)
{
	$bsearch_settings = bsearch_read_options();
	$title = ($text_only) ? strip_tags($bsearch_settings['title_daily']) : $bsearch_settings['title_daily'];
	return $title;
}

// Function to retrieve Overall Popular Searches Title
function get_bsearch_title($text_only = true)
{
	$bsearch_settings = bsearch_read_options();
	$title = ($text_only) ? strip_tags($bsearch_settings['title']) : $bsearch_settings['title'];
	return $title;
}

// Default Options
function bsearch_default_options() {
	$title = __('<h3>Popular Searches</h3>', BSEARCH_LOCAL_NAME);
	$title_daily = __('<h3>Weekly Popular Searches</h3>', BSEARCH_LOCAL_NAME);

	$bsearch_settings = 	Array (
						show_credit => true,			// Add link to plugin page of my blog in top posts list
						use_fulltext => true,			// Full text searches
						d_use_js => false,				// Use JavaScript for displaying Weekly Popular Searches
						include_pages => true,			// Include static pages in search results
						title => $title,				// Title of Search Heatmap
						title_daily => $title_daily,	// Title of Daily Search Heatmap
						limit => '10',					// Search results per page
						daily_range => '7',				// Daily Popular will contain posts of how many days?
						heatmap_smallest => '10',		// Heatmap - Smallest Font Size
						heatmap_largest => '20',		// Heatmap - Largest Font Size
						heatmap_unit => 'pt',			// Heatmap - We'll use pt for font size
						heatmap_cold => '00f',			// Heatmap - cold searches
						heatmap_hot => 'f00',			// Heatmap - hot searches
						heatmap_before => '',			// Heatmap - Display before each search term
						heatmap_after => '&nbsp;',		// Heatmap - Display after each search term
						heatmap_limit => '30',			// Heatmap - Maximum number of searches to display in heatmap
						);
	return $bsearch_settings;
}

// Function to read options from the database
function bsearch_read_options() {

	// Upgrade table code
	global $bsearch_db_version;
	$installed_ver = get_option( "bsearch_db_version" );

	if( $installed_ver != $bsearch_db_version ) bsearch_install();

	$bsearch_settings_changed = false;
	
	$defaults = bsearch_default_options();
	
	$bsearch_settings = array_map('stripslashes',(array)get_option('ald_bsearch_settings'));
	unset($bsearch_settings[0]); // produced by the (array) casting when there's nothing in the DB
	
	foreach ($defaults as $k=>$v) {
		if (!isset($bsearch_settings[$k])) {
			$bsearch_settings[$k] = $v;
			$bsearch_settings_changed = true;
		}
	}
	if ($bsearch_settings_changed == true)
		update_option('ald_bsearch_settings', $bsearch_settings);
	
	return $bsearch_settings;

}

// Create tables to store pageviews
function bsearch_install() {
	global $wpdb;
	global $bsearch_db_version;

    // Create full text index
	$wpdb->hide_errors();
    $wpdb->query('ALTER TABLE '.$wpdb->posts.' ENGINE = MYISAM;');
    $wpdb->query('ALTER TABLE '.$wpdb->posts.' ADD FULLTEXT bsearch (post_title, post_content);');
    $wpdb->show_errors();

	// Create the tables
	$table_name = $wpdb->prefix . "bsearch";
	$table_name_daily = $wpdb->prefix . "bsearch_daily";
   
	if($wpdb->get_var("show tables like '$table_name'") != $table_name) {
		  
		$sql = "CREATE TABLE " . $table_name . " (
			accessedid int NOT NULL AUTO_INCREMENT,
			searchvar VARCHAR(100) NOT NULL,
			cntaccess int NOT NULL,
			PRIMARY KEY  (accessedid)
		);";

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);

		$wpdb->hide_errors();
		$wpdb->query('CREATE INDEX IDX_searhvar ON '.$table_name.' (searchvar)');
		$wpdb->show_errors();

		add_option("bsearch_db_version", $bsearch_db_version);
	}

	if($wpdb->get_var("show tables like '$table_name_daily'") != $table_name_daily) {
	  
		$sql = "CREATE TABLE " . $table_name_daily . " (
			accessedid int NOT NULL AUTO_INCREMENT,
			searchvar VARCHAR(100) NOT NULL,
			cntaccess int NOT NULL,
			dp_date date NOT NULL,
			PRIMARY KEY  (accessedid)
		);";

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);

		$wpdb->hide_errors();
		$wpdb->query('CREATE INDEX IDX_searhvar ON '.$table_name_daily.' (searchvar)');
		$wpdb->show_errors();

		add_option("bsearch_db_version", $bsearch_db_version);
	}

	// Upgrade table code
	$installed_ver = get_option( "bsearch_db_version" );

	if( $installed_ver != $bsearch_db_version ) {

		$sql = "CREATE TABLE " . $table_name . " (
			accessedid int NOT NULL AUTO_INCREMENT,
			searchvar VARCHAR(100) NOT NULL,
			cntaccess int NOT NULL,
			PRIMARY KEY  (accessedid)
		);";

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);

		$wpdb->hide_errors();
		$wpdb->query('ALTER '.$table_name.' DROP INDEX IDX_searhvar ');
		$wpdb->query('CREATE INDEX IDX_searhvar ON '.$table_name.' (searchvar)');
		$wpdb->show_errors();
	  
		$sql = "DROP TABLE $table_name_daily";
		$wpdb->query($sql);

		$sql = "CREATE TABLE " . $table_name_daily . " (
			accessedid int NOT NULL AUTO_INCREMENT,
			searchvar VARCHAR(100) NOT NULL,
			cntaccess int NOT NULL,
			dp_date date NOT NULL,
			PRIMARY KEY  (accessedid)
		);";

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);

		$wpdb->hide_errors();
		$wpdb->query('ALTER '.$table_name_daily.' DROP INDEX IDX_searhvar ');
		$wpdb->query('CREATE INDEX IDX_searhvar ON '.$table_name_daily.' (searchvar)');
		$wpdb->show_errors();

		update_option( "bsearch_db_version", $bsearch_db_version );
	}

}
if (function_exists('register_activation_hook')) {
	register_activation_hook(__FILE__,'bsearch_install');
}


// Function to delete all rows in the daily searches table
function bsearch_trunc_count() {
	global $wpdb;
	$table_name_daily = $wpdb->prefix . "bsearch_daily";

	$sql = "TRUNCATE TABLE $table_name_daily";
	$wpdb->query($sql);
}

// Manual Daily Better Search Heatmap
function get_bsearch_pop_daily() {

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
	
	if ($bsearch_settings['d_use_js']) {
		$output .= '<script type="text/javascript" src="'.get_bloginfo('wpurl').'/wp-content/plugins/better-search/better-search-daily.js.php?widget=1"></script>';
	} else {
		$output .= '<div class="bsearch_heatmap">';	
		$output .= $bsearch_settings['title_daily'];
		$output .= '<div text-align:center>'.get_bsearch_heatmap(true, $smallest, $largest, $unit, $cold, $hot, $before, $after, '',$limit).'</div>';
		if ($bsearch_settings['show_credit']) $output .= '<br /><small>Powered by <a href="http://ajaydsouza.com/wordpress/plugins/better-search/">Better Search plugin</a></small>';
		$output .= '</div>';
	}
	
	return $output;
}

function the_pop_searches_daily()
{
	echo get_bsearch_pop_daily();
}

// Manual Overall Better Search Heatmap
function get_bsearch_pop() {	
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
	
	$output .= '<div class="bsearch_heatmap">';	
	$output .= $bsearch_settings['title'];
	$output .= '<div text-align:center>'.get_bsearch_heatmap(false, $smallest, $largest, $unit, $cold, $hot, $before, $after, '',$limit).'</div>';
	if ($bsearch_settings['show_credit']) $output .= '<br /><small>Powered by <a href="http://ajaydsouza.com/wordpress/plugins/better-search/">Better Search plugin</a></small>';
	$output .= '</div>';
	return $output;
}

function the_pop_searches()
{
	echo get_bsearch_pop();
}


// Create a WordPress Widget for Daily Better Search
function widget_bsearch_pop_daily($args) {

	extract($args); // extracts before_widget,before_title,after_title,after_widget

	$bsearch_settings = bsearch_read_options();
	$limit = $bsearch_settings[heatmap_limit];
	$largest = intval($bsearch_settings[heatmap_largest]);
	$smallest = intval($bsearch_settings[heatmap_smallest]);
	$hot = $bsearch_settings[heatmap_hot];
	$cold = $bsearch_settings[heatmap_cold];
	$unit = $bsearch_settings[heatmap_unit];
	$before = $bsearch_settings[heatmap_before];
	$after = $bsearch_settings[heatmap_after];

	$title = (($bsearch_settings['title_daily']) ? strip_tags($bsearch_settings['title_daily']) : __('Weekly Heatmap'));
	echo $before_widget;
	echo $before_title.$title.$after_title;
		
	if ($bsearch_settings['d_use_js']) {
		echo '<script type="text/javascript" src="'.get_bloginfo('wpurl').'/wp-content/plugins/better-search/better-search-daily.js.php?widget=1"></script>';
	} else {
		echo get_bsearch_heatmap(true, $smallest, $largest, $unit, $cold, $hot, $before, $after, '', $limit);
	}
	
	if ($bsearch_settings['show_credit']) echo '<br /><small>Powered by <a href="http://ajaydsouza.com/wordpress/plugins/better-search/">Better Search plugin</a></small>';
	echo $after_widget;
}

// Create a WordPress Widget for Better Search
function widget_bsearch_pop($args) {	

	extract($args); // extracts before_widget,before_title,after_title,after_widget

	$bsearch_settings = bsearch_read_options();
	$limit = $bsearch_settings[heatmap_limit];
	$largest = intval($bsearch_settings[heatmap_largest]);
	$smallest = intval($bsearch_settings[heatmap_smallest]);
	$hot = $bsearch_settings[heatmap_hot];
	$cold = $bsearch_settings[heatmap_cold];
	$unit = $bsearch_settings[heatmap_unit];
	$before = $bsearch_settings[heatmap_before];
	$after = $bsearch_settings[heatmap_after];
	
	$title = (($bsearch_settings['title']) ? strip_tags($bsearch_settings['title']) : __('Popular Searches'));
	
	echo $before_widget;
	echo $before_title.$title.$after_title;
	
	echo get_bsearch_heatmap(false, $smallest, $largest, $unit, $cold, $hot, $before, $after, '', $limit);
	if ($bsearch_settings['show_credit']) echo '<br /><small>Powered by <a href="http://ajaydsouza.com/wordpress/plugins/better-search/">Better Search plugin</a></small>';
	
	echo $after_widget;
}

function init_bsearch(){
	register_sidebar_widget(__('Popular Searches', BSEARCH_LOCAL_NAME), 'widget_bsearch_pop');
	register_sidebar_widget(__('Weekly Popular Searches', BSEARCH_LOCAL_NAME), 'widget_bsearch_pop_daily');
}
add_action("plugins_loaded", "init_bsearch");

// Utility functions 
function RemoveXSS($val) {
   // remove all non-printable characters. CR(0a) and LF(0b) and TAB(9) are allowed
   // this prevents some character re-spacing such as <java\0script>
   // note that you have to handle splits with \n, \r, and \t later since they *are* allowed in some inputs
   $val = preg_replace('/([\x00-\x08,\x0b-\x0c,\x0e-\x19])/', '', $val);
   
   // straight replacements, the user should never need these since they're normal characters
   // this prevents like <IMG SRC=&#X40&#X61&#X76&#X61&#X73&#X63&#X72&#X69&#X70&#X74&#X3A &#X61&#X6C&#X65&#X72&#X74&#X28&#X27&#X58&#X53&#X53&#X27&#X29>
   $search = 'abcdefghijklmnopqrstuvwxyz';
   $search .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
   $search .= '1234567890!@#$%^&*()';
   $search .= '~`";:?+/={}[]-_|\'\\';
   for ($i = 0; $i < strlen($search); $i++) {
      // ;? matches the ;, which is optional
      // 0{0,7} matches any padded zeros, which are optional and go up to 8 chars
   
      // &#x0040 @ search for the hex values
      $val = preg_replace('/(&#[xX]0{0,8}'.dechex(ord($search[$i])).';?)/i', $search[$i], $val); // with a ;
      // &#00064 @ 0{0,7} matches '0' zero to seven times
      $val = preg_replace('/(&#0{0,8}'.ord($search[$i]).';?)/', $search[$i], $val); // with a ;
   }
   
   // now the only remaining whitespace attacks are \t, \n, and \r
   $ra1 = Array(); //Array('javascript', 'vbscript', 'expression', 'applet', 'meta', 'xml', 'blink', 'link', 'style', 'script', 'embed', 'object', 'iframe', 'frame', 'frameset', 'ilayer', 'layer', 'bgsound', 'title', 'base');
   $ra2 = Array('onabort', 'onactivate', 'onafterprint', 'onafterupdate', 'onbeforeactivate', 'onbeforecopy', 'onbeforecut', 'onbeforedeactivate', 'onbeforeeditfocus', 'onbeforepaste', 'onbeforeprint', 'onbeforeunload', 'onbeforeupdate', 'onblur', 'onbounce', 'oncellchange', 'onchange', 'onclick', 'oncontextmenu', 'oncontrolselect', 'oncopy', 'oncut', 'ondataavailable', 'ondatasetchanged', 'ondatasetcomplete', 'ondblclick', 'ondeactivate', 'ondrag', 'ondragend', 'ondragenter', 'ondragleave', 'ondragover', 'ondragstart', 'ondrop', 'onerror', 'onerrorupdate', 'onfilterchange', 'onfinish', 'onfocus', 'onfocusin', 'onfocusout', 'onhelp', 'onkeydown', 'onkeypress', 'onkeyup', 'onlayoutcomplete', 'onload', 'onlosecapture', 'onmousedown', 'onmouseenter', 'onmouseleave', 'onmousemove', 'onmouseout', 'onmouseover', 'onmouseup', 'onmousewheel', 'onmove', 'onmoveend', 'onmovestart', 'onpaste', 'onpropertychange', 'onreadystatechange', 'onreset', 'onresize', 'onresizeend', 'onresizestart', 'onrowenter', 'onrowexit', 'onrowsdelete', 'onrowsinserted', 'onscroll', 'onselect', 'onselectionchange', 'onselectstart', 'onstart', 'onstop', 'onsubmit', 'onunload');
   $ra = array_merge($ra1, $ra2);
   
   $found = true; // keep replacing as long as the previous round replaced something
   while ($found == true) {
      $val_before = $val;
      for ($i = 0; $i < sizeof($ra); $i++) {
         $pattern = '/';
         for ($j = 0; $j < strlen($ra[$i]); $j++) {
            if ($j > 0) {
               $pattern .= '(';
               $pattern .= '(&#[xX]0{0,8}([9ab]);)';
               $pattern .= '|';
               $pattern .= '|(&#0{0,8}([9|10|13]);)';
               $pattern .= ')*';
            }
            $pattern .= $ra[$i][$j];
         }
         $pattern .= '/i';
         $replacement = substr($ra[$i], 0, 2).'<x>'.substr($ra[$i], 2); // add in <> to nerf the tag
         $val = preg_replace($pattern, $replacement, $val); // filter out the hex tags
         if ($val_before == $val) {
            // no replacements were made, so exit the loop
            $found = false;
         }
      }
   }
   return $val;
} 

function search_excerpt($content){
	$out = strip_tags($content);
	$blah = explode(' ',$out);
	$excerpt_length = 50;
	if(count($blah) > $excerpt_length){
		$k = $excerpt_length;
		$use_dotdotdot = 1;
	}else{
		$k = count($blah);
		$use_dotdotdot = 0;
	}
	$excerpt = '';
	for($i=0; $i<$k; $i++){
		$excerpt .= $blah[$i].' ';
	}
	$excerpt .= ($use_dotdotdot) ? '...' : '';
	$out = $excerpt;
	return $out;
}

function quote_smart($value)
{
	// Strip Tags
	$value = strip_tags($value);
	
	// Stripslashes
	if (get_magic_quotes_gpc()) {
		$value = stripslashes($value);
	}
	// Quote if not integer
	if (!is_numeric($value)) {
		$value = mysql_real_escape_string($value);
	}
	return $value;
}

// This function adds an Options page in WP Admin
if (is_admin() || strstr($_SERVER['PHP_SELF'], 'wp-admin/')) {
	require_once(ALD_bsearch_DIR . "/admin.inc.php");
}

?>