<?php
/*
Plugin Name: Better Search
Version:     1.3.2
Plugin URI:  http://ajaydsouza.com/wordpress/plugins/better-search/
Description: Replace the default WordPress search with a contextual search. Search results are sorted by relevancy ensuring a better visitor search experience. 
Author:      Ajay D'Souza
Author URI:  http://ajaydsouza.com/
*/

if (!defined('ABSPATH')) die("Aren't you supposed to come here via WP-Admin?");

global $bsearch_db_version;
$bsearch_db_version = "1.0";

define('ALD_BSEARCH_DIR', dirname(__FILE__));
define('BSEARCH_LOCAL_NAME', 'better-search');

// Guess the location
$bsearch_path = plugin_dir_path(__FILE__);
$bsearch_url = plugins_url().'/'.plugin_basename(dirname(__FILE__));


/**
 * Declare $bsearch_settings global so that it can be accessed in every function
 */
global $bsearch_settings;
$bsearch_settings = bsearch_read_options();


/**
 * Initialise the plugin - Load Language files
 */
function ald_bsearch_init() {
	//* Begin Localization Code */
	$tc_localizationName = BSEARCH_LOCAL_NAME;
	load_plugin_textdomain( $tc_localizationName, false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	//* End Localization Code */
}
add_action('init', 'ald_bsearch_init');

/**
 * Displays the search results
 *
 * First checks if the theme contains a search template and uses that 
 * If search template is missing, generates the results below
 */
function bsearch_template_redirect() {
	// not a search page; don't do anything and return
	if ( (stripos($_SERVER['REQUEST_URI'], '?s=') === FALSE) && (stripos($_SERVER['REQUEST_URI'], '/search/') === FALSE) && (!is_search()) )
	{
		return;
	}
	
	// change status code to 200 OK since /search/ returns status code 404
	@header("HTTP/1.1 200 OK",1);
	@header("Status: 200 OK", 1);

	global $bsearch_settings;

	$s = trim(bsearch_clean_terms(apply_filters('the_search_query', get_search_query())));
	$limit = isset($_GET['limit']) ? intval($_GET['limit']) : $bsearch_settings['limit']; // Read from GET variable

	add_action('wp_head', 'bsearch_head');
	add_filter('wp_title', 'bsearch_title');

	// If there is a template file then we use it
	$exists = file_exists(get_stylesheet_directory() . '/better-search-template.php');
	if ($exists) {
		include_once(get_stylesheet_directory() . '/better-search-template.php');
		exit;
	}

	get_header();

	echo '<div id="content" class="bsearch_results_page">';
	echo get_bsearch_form($s);	

	echo '<div id="bsearchresults"><h1 class="page-title">';
	echo __( 'Search Results for: ', BSEARCH_LOCAL_NAME ). '<span>' . $s . '</span>' ;
	echo '</h1>';
	
	echo get_bsearch_results($s,$limit);

	echo '</div>';	// Close id="bsearchresults"
	echo get_bsearch_form($s);	

	if ($bsearch_settings['include_heatmap']) {
		echo '<div id="heatmap">';
		echo '<div class="heatmap_daily">';
		echo '<h2>';
		echo strip_tags($bsearch_settings['title_daily']);
		echo '</h2>';
		echo get_bsearch_heatmap(true);
		echo '</div>';	// Close class="heatmap_daily"
		echo '<div class="heatmap_overall">';
		echo '<h2>';
		echo strip_tags($bsearch_settings['title']);
		echo '</h2>';
		echo get_bsearch_heatmap(false);
		echo '</div>';	// Close class="heatmap_overall"
		echo '<div style="clear:both">&nbsp;</div>';
		echo '</div>';
	}

	echo '</div>';	// Close id="content"

	//get_sidebar();

	get_footer();
	exit;
}
add_action('template_redirect', 'bsearch_template_redirect', 1);

/**
 * Gets the search results
 *
 * @param string $s Search term
 * @param int|string $limit Maximum number of search results
 * @return string Search results
 */
function get_bsearch_results($s = '',$limit) {
	global $wpdb;
	global $bsearch_settings;

	if (!($limit)) $limit = isset($_GET['limit']) ? intval($_GET['limit']) : $bsearch_settings['limit']; // Read from GET variable


	$bydate = isset($_GET['bydate']) ? intval($_GET['bydate']) : 0;		// Order by date or by score?
	
	$topscore = 0;

	$matches = get_bsearch_matches($s,$bydate);		// Fetch the search results for the search term stored in $s
	$searches = $matches[0];		// 0 index contains the search results always
	if ($searches) {
		foreach ($searches as $search) {
			if($topscore < $search->score) $topscore = $search->score;
		}
		$numrows = count($searches);
	} else {
		$numrows = 1; 
	}

	$match_range = get_bsearch_range($numrows,$limit);
	$searches = array_slice($searches,$match_range[0],$match_range[1]-$match_range[0]+1);	// Extract the elements for the page from the complete results array
	
	$output = '';

	// Lets start printing the results
	if($s != ''){
		if($searches){
			$output .= get_bsearch_header($s,$numrows,$limit);

			foreach($searches as $search){
				$score = $search->score;
				$search = get_post($search->ID);
				$post_title = get_the_title($search->ID);
				
				$output .= '<h2><a href="'.get_permalink($search->ID).'" rel="bookmark">'.$post_title.'</a></h2>';
				$output .= '<p>';
				$output .= '<span class="bsearch_score">'.get_bsearch_score($search,$score,$topscore).'</span>';
				$before = __('Posted on: ', BSEARCH_LOCAL_NAME);
				$output .= '<span class="bsearch_date">'.get_bsearch_date($search,__('Posted on: ', BSEARCH_LOCAL_NAME) ).'</span>';
				$output .= '</p>';
				$output .= '<p>';
				if ($bsearch_settings['include_thumb']) $output .= '<p class="bsearch_thumb">'.get_the_post_thumbnail( $search->ID, 'thumbnail' ).'</p>';
				$output .= '<span class="bsearch_excerpt">'.get_bsearch_excerpt($search->ID,$bsearch_settings['excerpt_length']).'</span>';	// This displays the post excerpt / creates it. Replace with $output .= $content; to use content instead of excerpt
				$output .= '</p>';
			} //end of foreach loop

			$output .= get_bsearch_footer($s,$numrows,$limit);

		}else{
			$output .= '<p>';
			$output .= __('No results.', BSEARCH_LOCAL_NAME);
			$output .= '</p>';
		}
	}else{
		$output .= '<p>';
		$output .= __('Please type in your search terms. Use descriptive words since this search is intelligent.', BSEARCH_LOCAL_NAME);
		$output .= '</p>';
	}

	if ($bsearch_settings['show_credit']) {
		$output .= '<hr /><p style="text-align:center">';
		$output .= __('Powered by ', BSEARCH_LOCAL_NAME);
		$output .= '<a href="http://ajaydsouza.com/wordpress/plugins/better-search/">Better Search plugin</a></p>';
	}

	// Use apply_filters, so that get_bsearch_* can be editted
	return apply_filters('get_bsearch_results',$output);
}

/**
 * returns an array with the cleaned-up search string at the zero index and possibly a list of terms in the second.
 * 
 * @access public
 * @param mixed $s The search term
 * @return array Cleaned up search string
 */
function get_bsearch_terms($s) {
	global $bsearch_settings;

	if ($s == '') {
		$s = bsearch_clean_terms(apply_filters('the_search_query', get_search_query()));
	}
	$s_array[0] = $s;
		
	$use_fulltext = $bsearch_settings['use_fulltext'];

	// if use_fulltext is false OR if all the words are shorter than four chars, add the array of search terms.
	// Currently this will disable match ranking and won't be quote-savvy.

	// if we are using fulltext, turn it off unless there's a search word longer than three chars
	// ideally we'd also check against stopwords here
	$search_words = explode(' ',$s);
	if ($use_fulltext) {
		$use_fulltext_proxy = false;
		foreach($search_words as $search_word) {
			if ( strlen($search_word) > 3 ) { $use_fulltext_proxy = true; }
		}
		$use_fulltext = $use_fulltext_proxy;

	}

	if (!$use_fulltext) {
		// strip out all the fancy characters that fulltext would use
		$s = addslashes_gpc($s);
		$s = preg_replace('/, +/', ' ', $s);
		$s = str_replace(',', ' ', $s);
		$s = str_replace('"', ' ', $s);
		$s = trim($s);
		$search_words = explode(' ',$s);
	
		$s_array[0] = $s;
		$s_array[1] = $search_words;
	}
	
	// Use apply_filters, so that get_bsearch_* can be editted
	return apply_filters('get_bsearch_terms',$s_array);
}

/**
 * Get the matches for the search term.
 * 
 * @param mixed $search_info Search terms array
 * @param mixed $bydate Sort by date?
 * @return array Search results
 */
function get_bsearch_matches($search_info,$bydate) {
	global $wpdb;
	global $bsearch_settings;
	
	parse_str($bsearch_settings['post_types'],$post_types);	// Save post types in $post_types variable

	$n = '%';
	
	// if there are two items in $search_info, the string has been broken into separate terms that
	// are listed at $search_info[1]. The cleaned-up version of $s is still at the zero index.
	// This is when fulltext is disabled, and we search using LIKE
	$search_info = get_bsearch_terms('');
	
	if (count($search_info) > 1) {
		$search_terms = $search_info[1];
		$args = array(
			$n.$search_terms[0].$n,
			$n.$search_terms[0].$n,
		);

		$sql = "SELECT ID, 0 AS score FROM ".$wpdb->posts." WHERE (";
		$sql .= "((post_title LIKE '%s') OR (post_content LIKE '%s'))";
		for ( $i = 1; $i < count($search_terms); $i = $i + 1) {	
			$sql .= " AND ((post_title LIKE '%s') OR (post_content LIKE '%s'))";
			$args[] = $n.$search_terms[$i].$n;
			$args[] = $n.$search_terms[$i].$n;
		}
		$sql .= " OR (post_title LIKE '%s') OR (post_content LIKE '%s')";

		$args[] = $n.$search_info[0].$n;
		$args[] = $n.$search_info[0].$n;

		$sql .= ") AND post_status = 'publish' ";
		$sql .= "AND ( ";
		$multiple = false;
		foreach ($post_types as $post_type) {
			if ( $multiple ) $sql .= ' OR ';
			$sql .= " post_type = '%s' ";
			$multiple = true;
			$args[] = $post_type;	// Add the post types to the $args array
		}
		$sql .=" ) ";
		$sql .= "ORDER BY post_date DESC ";
	} else {
		$boolean_mode = ($bsearch_settings['boolean_mode']) ? ' IN BOOLEAN MODE' : '';
		$args = array(
			$search_info[0],
			$bsearch_settings['weight_title'],
			$search_info[0],
			$bsearch_settings['weight_content'],
			$search_info[0],
		);
		
		$sql = "SELECT ID, ";
		$sql .= "(MATCH(post_title) AGAINST ('%s' ".$boolean_mode." ) * %d ) + ";
		$sql .= "(MATCH(post_content) AGAINST ('%s' ".$boolean_mode.") * %d ) ";
		$sql .= "AS score FROM ".$wpdb->posts." WHERE MATCH (post_title,post_content) AGAINST ('%s' ".$boolean_mode.") AND post_status = 'publish' ";
		$sql .= "AND ( ";
		$multiple = false;
		foreach ($post_types as $post_type) {
			if ( $multiple ) $sql .= ' OR ';
			$sql .= " post_type = '%s' ";
			$multiple = true;
			$args[] = $post_type;	// Add the post types to the $args array
		}
		$sql .=" ) ";
		if ($bydate) {
			$sql .= "ORDER BY post_date DESC ";
		} else {
			$sql .= "ORDER BY score DESC ";
		}
	}
	
	$matches[0] = $wpdb->get_results($wpdb->prepare($sql, $args));
	$matches[1] = ($sql);

	// Use apply_filters, so that get_bsearch_* can be editted
	return apply_filters('get_bsearch_matches',$matches);
}

/**
 * returns an array with the first and last indices to be displayed on the page.
 * 
 * @access public
 * @param int $numrows
 * @param int $limit
 * @return array First and last indices to be displayed on the page 
 */
function get_bsearch_range($numrows, $limit) {
	global $bsearch_settings;

	if (!($limit)) $limit = isset($_GET['limit']) ? intval($_GET['limit']) : $bsearch_settings['limit']; // Read from GET variable
	$page = isset($_GET['bpaged']) ? intval(bsearch_clean_terms($_GET['bpaged'])) : 0; // Read from GET variable
	
	$last = min($page + $limit - 1, $numrows - 1);
	
	$match_range = array($page, $last);

	// Use apply_filters, so that get_bsearch_* can be editted
	return apply_filters('get_bsearch_range',$match_range);
}

/**
 * Function to return the header links of the results page.
 * 
 * @access public
 * @param string $s Search string
 * @param int $numrows 
 * @param int $limit
 * @return string
 */
function get_bsearch_header($s,$numrows,$limit) {

	$output = '';
	$match_range = get_bsearch_range($numrows,$limit);
	
	$pages = intval($numrows/$limit); // Number of results pages.
	if ($numrows % $limit) {$pages++;} // If remainder so add one page
	if (($pages < 1) || ($pages == 0)) {$total = 1;} // If $pages is less than one or equal to 0, total pages is 1.
		else { $total = $pages;} // Else total pages is $pages value.

	$first = $match_range[0]+1;	// the first result on the page (Starts with 0)
	$last = $match_range[1]+1;	// the last result on the page (Starts with 0)
	$current = ($match_range[0]/$limit) + 1; // Current page number.

	$output .= '<table width="100%" border="0" class="bsearch_nav">
	 <tr>
	  <td width="50%" style="text-align:left">';
	$output .= sprintf( __('Results <strong>%1$s</strong> - <strong>%2$s</strong> of <strong>%3$s</strong>', BSEARCH_LOCAL_NAME), $first, $last, $numrows );
	
	$output .= '
	  </td>
	  <td width="50%" style="text-align:right">';
	$output .= sprintf( __('Page <strong>%1$s</strong> of <strong>%2$s</strong>', BSEARCH_LOCAL_NAME), $current, $total );

	$sencoded = urlencode($s);

	$output .= '
	  </td>
	 </tr>
	 <tr>
	  <td style="text-align:left"></td>';
	$output .= '<td style="text-align:right">';
	$output .= __('Results per-page', BSEARCH_LOCAL_NAME);
	$output .= ': <a href="'.home_url().'/?s='.$sencoded.'&limit=10">10</a> | <a href="'.home_url().'/?s='.$sencoded.'&limit=20">20</a> | <a href="'.home_url().'/?s='.$sencoded.'&limit=50">50</a> | <a href="'.home_url().'/?s='.$sencoded.'&limit=100">100</a>
	  </td>
	 </tr>
	</table>';
	
	// Use apply_filters, so that get_bsearch_* can be editted
	return apply_filters('get_bsearch_header',$output);
}

/**
 * Function to return the footer links of the results page.
 * 
 * @access public
 * @param string $s
 * @param int $numrows
 * @param int $limit
 * @return string
 */
function get_bsearch_footer($s,$numrows,$limit) {

	$match_range = get_bsearch_range($numrows,$limit);
	$page = $match_range[0];
	$pages = intval($numrows/$limit); // Number of results pages.
	if ($numrows % $limit) {$pages++;} // If remainder so add one page

	$s = urlencode($s);

	$output =   '<p class="bsearch_footer">';
	if ($page != 0) { // Don't show back link if current page is first page.
		$back_page = $page - $limit;
		$output .=  "<a href=\"".home_url()."/?s=$s&limit=$limit&bpaged=$back_page\">&laquo; ";
		$output .=  __('Previous', BSEARCH_LOCAL_NAME);
		$output .=  "</a>    \n";
	}

	$pagination_range = 4;			// Number of pagination elements
	for ($i=1; $i <= $pages; $i++) // loop through each page and give link to it.
	{
		$current = ($match_range[0]/$limit) + 1; // Current page number.
		if($i >= $current+$pagination_range && $i <$pages){
			if($i == $current+$pagination_range){
				$output .= '&hellip;&nbsp;';
			}
			continue;
		}
		if($i < $current-$pagination_range+1 && $i <$pages) {
			continue;
		}
		$ppage = $limit*($i - 1);
		if ($ppage == $page){
		$output .=  ("<b>$i</b>\n");} // If current page don't give link, just text.
		else{
			$output .=  ("<a href=\"".home_url()."/?s=$s&limit=$limit&bpaged=$ppage\">$i</a> \n");
		}
	}

	if (!((($page+$limit) / $limit) >= $pages) && $pages != 1) { // If last page don't give next link.
		$next_page = $page + $limit;
		$output .=  "    <a href=\"".home_url()."/?s=$s&limit=$limit&bpaged=$next_page\">";
		$output .=  __('Next', BSEARCH_LOCAL_NAME);
		$output .=  " &raquo;</a>";
	}
	$output .=   '</p>';
	
	// Use apply_filters, so that get_bsearch_* can be editted
	return apply_filters('get_bsearch_footer',$output);
}

// Function to get the score
function get_bsearch_score($search,$score,$topscore) {

	$output = '';
	if ($score > 0) {
		$score = $score * 100 / $topscore;
		$output = __('Relevance: ', BSEARCH_LOCAL_NAME);
		$output .= number_format($score,0).'% &nbsp;&nbsp;&nbsp;&nbsp; ';
	}
	// Use apply_filters, so that get_bsearch_* can be editted
	return apply_filters('get_bsearch_score',$output);
}

/**
 * Function to get post date.
 * 
 * @access public
 * @param string $search
 * @param string $before (default: '')
 * @param string $after (default: '')
 * @param string $format (default: get_option('date_format'))
 * @return string
 */
function get_bsearch_date($search,$before ='',$after ='',$format='' ) {
	if (!$format) $format = get_option('date_format');
	$output = $before.date($format,strtotime($search->post_date)).$after;
	// Use apply_filters, so that get_bsearch_* can be editted
	return apply_filters('get_bsearch_date',$output);
}

/**
 * Create an excerpt with the post content.
 * 
 * @access public
 * @param string $content
 * @return string
 */
function get_bsearch_excerpt($id,$excerpt_length){
	$post = get_post($id);
	$content = $post->post_excerpt;
	if ($content=='') $content = $post->post_content;
	$out = strip_tags(strip_shortcodes($content));
	
	$blah = explode(' ',$out);
	if (!$excerpt_length) $excerpt_length = 50;
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

	// Use apply_filters, so that get_bsearch_* can be editted
	return apply_filters('get_bsearch_excerpt',$out);
}

/**
 * Search Heatmap.
 * 
 * @access public
 * @param bool $daily (default: false)
 * @param int $smallest (default: 10)
 * @param int $largest (default: 20)
 * @param string $unit (default: "pt")
 * @param string $cold (default: "ccc")
 * @param string $hot (default: "000")
 * @param string $before (default: '')
 * @param string $after (default: '&nbsp;')
 * @param string $exclude (default: '')
 * @param string $limit (default: '30')
 * @param boolean $daily_range (default: null)
 * @return string
 */
function get_bsearch_heatmap($daily=false, $smallest=10, $largest=20, $unit="pt", $cold="ccc", $hot="000", $before='', $after='&nbsp;', $exclude='', $limit='30', $daily_range=null) {
	global $wpdb,$bsearch_url;
	global $bsearch_settings;

	$table_name = $wpdb->prefix . "bsearch";
	if ($daily) $table_name .= "_daily";	// If we're viewing daily posts, set this to true
	$output = '';
	
	if(!$daily) {
		$args = array(
			$limit,
		);
	
		$sql = "SELECT searchvar, cntaccess FROM ".$table_name." WHERE accessedid IN (SELECT accessedid FROM ".$table_name." WHERE searchvar <> '' ORDER BY cntaccess DESC, searchvar ASC) ORDER by accessedid LIMIT %d";
	} else {
		if (is_null($daily_range)) $daily_range = $bsearch_settings['daily_range'];
		$daily_range = $daily_range. ' DAY';
		$current_date = $wpdb->get_var("SELECT DATE_ADD(DATE_SUB(CURDATE(), INTERVAL ".$daily_range."), INTERVAL 1 DAY) ");
	
		$args = array(
			$current_date,
			$limit,
		);
	
		$sql = "
			SELECT DISTINCT wp1.searchvar, wp2.sumCount
			FROM ".$table_name." wp1,
					(SELECT searchvar, SUM(cntaccess) as sumCount
					FROM ".$table_name."
					WHERE dp_date >= '%d' 
					GROUP BY searchvar
					ORDER BY sumCount DESC LIMIT %d) wp2
					WHERE wp1.searchvar = wp2.searchvar
			ORDER by wp1.searchvar ASC
		";
	}

	$results = $wpdb->get_results($wpdb->prepare($sql, $args));
	
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
			$textsearchvar = esc_attr($result->searchvar);
			$url  = home_url().'/?s='.$textsearchvar;
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
			$output .= sprintf(_n('Search for %1$s (%2$s search)','Search for %1$s (%2$s searches)',$cntaccess, BSEARCH_LOCAL_NAME),$textsearchvar,$cntaccess);
			$output .= '" '.$style;
			if ($bsearch_settings['link_nofollow']) $output .= ' rel="nofollow" ';
			if ($bsearch_settings['link_new_window']) $output .= ' target="_blank" ';
			$output .= '>'.$textsearchvar.'</a>'.$after.' ';
		}
	} else {
		$output = __('No searches made yet', BSEARCH_LOCAL_NAME);
	}

	// Use apply_filters, so that get_bsearch_* can be editted
	return apply_filters('get_bsearch_heatmap',$output);
}

/**
 * Function to update search count.
 * 
 * @access public
 * @param string $s
 * @return string
 */
function bsearch_increment_counter($s) {
	global $bsearch_url;
	$output = '<script type="text/javascript" src="'.$bsearch_url.'/better-search-addcount.js.php?bsearch_id='.$s.'"></script>';
	return $output;
}

/**
 * Insert styles into WordPress Head.
 * 
 * @access public
 * @return string
 */
function bsearch_head()
{
	global $bsearch_settings;
	$bsearch_custom_CSS = stripslashes($bsearch_settings['custom_CSS']);
	
	$s = bsearch_clean_terms(apply_filters('the_search_query', get_search_query()));

	$limit = (isset($_GET['limit'])) ? intval($_GET['limit']) : $bsearch_settings['limit']; // Read from GET variable
	$bpaged = (isset($_GET['bpaged'])) ? intval($_GET['bpaged']) : 0; // Read from GET variable

	if (!$bpaged && $bsearch_settings['track_popular']) echo bsearch_increment_counter($s);	// Increment the count if we are on the first page of the results

	// Add custom CSS to header
	if ( ($bsearch_custom_CSS != '') && is_search() ) {
		echo '<style type="text/css">'.$bsearch_custom_CSS.'</style>';
	}
}

/**
 * Change page title.
 * 
 * @access public
 * @param string $title
 * @return string
 */
function bsearch_title($title)
{
	$s = bsearch_clean_terms(apply_filters('the_search_query', get_search_query()));
	if (isset($s)) {
		if ($s == '') return $s; else return __('Search Results for ', BSEARCH_LOCAL_NAME). '&quot;' . $s.'&quot; | ';
	} else {
		return $title;
	}
}

/**
 * Function to fetch search form.
 * 
 * @access public
 * @param string $s
 * @return string
 */
function get_bsearch_form($s)
{
	if ($s == '') {
		$s = bsearch_clean_terms(apply_filters('the_search_query', get_search_query()));
	}
	$form = '<div style="text-align:center"><form method="get" id="bsearchform" action="' . home_url() . '/" >
	<label class="hidden" for="s">' . __('Search for:', BSEARCH_LOCAL_NAME) . '</label>
	<input type="text" value="' . $s . '" name="s" id="s" />
	<input type="submit" id="searchsubmit" value="'.__('Search Again', BSEARCH_LOCAL_NAME).'" />
	</form></div>';

	// Use apply_filters, so that get_bsearch_* can be editted
	return apply_filters('get_bsearch_form',$form);
}

/**
 * Function to retrieve Daily Popular Searches Title.
 * 
 * @access public
 * @param bool $text_only (default: true)
 * @return string
 */
function get_bsearch_title_daily($text_only = true)
{
	global $bsearch_settings;
	$title = ($text_only) ? strip_tags($bsearch_settings['title_daily']) : $bsearch_settings['title_daily'];

	// Use apply_filters, so that get_bsearch_* can be editted
	return apply_filters('get_bsearch_title_daily',$title);
}

/**
 * Function to retrieve Overall Popular Searches Title.
 * 
 * @access public
 * @param bool $text_only (default: true)
 * @return string
 */
function get_bsearch_title($text_only = true)
{
	global $bsearch_settings;
	$title = ($text_only) ? strip_tags($bsearch_settings['title']) : $bsearch_settings['title'];

	// Use apply_filters, so that get_bsearch_* can be editted
	return apply_filters('get_bsearch_title',$title);
}

/**
 * Manual Daily Better Search Heatmap.
 * 
 * @access public
 * @return string
 */
function get_bsearch_pop_daily() {

	global $bsearch_settings;
	$limit = $bsearch_settings['heatmap_limit'];
	$largest = intval($bsearch_settings['heatmap_largest']);
	$smallest = intval($bsearch_settings['heatmap_smallest']);
	$hot = $bsearch_settings['heatmap_hot'];
	$cold = $bsearch_settings['heatmap_cold'];
	$unit = $bsearch_settings['heatmap_unit'];
	$before = $bsearch_settings['heatmap_before'];
	$after = $bsearch_settings['heatmap_after'];
	$daily_range = $bsearch_settings['daily_range'];

	$output = '';
	
	if ($bsearch_settings['d_use_js']) {
		$output .= '<script type="text/javascript" src="'.get_bloginfo('wpurl').'/wp-content/plugins/better-search/better-search-daily.js.php?widget=1"></script>';
	} else {
		$output .= '<div class="bsearch_heatmap">';	
		$output .= $bsearch_settings['title_daily'];
		$output .= '<div text-align:center>'.get_bsearch_heatmap(true, $smallest, $largest, $unit, $cold, $hot, $before, $after, '',$limit,$daily_range).'</div>';
		if ($bsearch_settings['show_credit']) $output .= '<br /><small>Powered by <a href="http://ajaydsouza.com/wordpress/plugins/better-search/">Better Search plugin</a></small>';
		$output .= '</div>';
	}
	
	// Use apply_filters, so that get_bsearch_* can be editted
	return apply_filters('get_bsearch_pop_daily',$output);
}

/**
 * Echo daily popular searches.
 * 
 * @access public
 * @return void
 */
function the_pop_searches_daily()
{
	echo get_bsearch_pop_daily();
}

/**
 * Manual Overall Better Search Heatmap.
 * 
 * @access public
 * @return $string
 */
function get_bsearch_pop() {	
	global $bsearch_settings;
	$limit = $bsearch_settings['heatmap_limit'];
	$largest = intval($bsearch_settings['heatmap_largest']);
	$smallest = intval($bsearch_settings['heatmap_smallest']);
	$hot = $bsearch_settings['heatmap_hot'];
	$cold = $bsearch_settings['heatmap_cold'];
	$unit = $bsearch_settings['heatmap_unit'];
	$before = $bsearch_settings['heatmap_before'];
	$after = $bsearch_settings['heatmap_after'];
	$daily_range = $bsearch_settings['daily_range'];

	$output = '';
	
	$output .= '<div class="bsearch_heatmap">';	
	$output .= $bsearch_settings['title'];
	$output .= '<div text-align:center>'.get_bsearch_heatmap(false, $smallest, $largest, $unit, $cold, $hot, $before, $after, '',$limit,$daily_range).'</div>';
	if ($bsearch_settings['show_credit']) $output .= '<br /><small>Powered by <a href="http://ajaydsouza.com/wordpress/plugins/better-search/">Better Search plugin</a></small>';
	$output .= '</div>';

	// Use apply_filters, so that get_bsearch_* can be editted
	return apply_filters('get_bsearch_pop',$output);
}

/**
 * Echo popular searches list.
 * 
 * @access public
 * @return void
 */
function the_pop_searches()
{
	echo get_bsearch_pop();
}


/**
 * Create a Wordpress Widget for Popular search terms.
 * 
 * @extends WP_Widget
 */
class WidgetBSearch extends WP_Widget
{
	function WidgetBSearch()
	{
		$widget_ops = array('classname' => 'widget_bsearch_pop', 'description' => __( 'Display the popular searches',BSEARCH_LOCAL_NAME) );
		$this->WP_Widget('widget_bsearch_pop',__('Popular Searches [Better Search]',BSEARCH_LOCAL_NAME), $widget_ops);
	}
	function form($instance) {
		$title = esc_attr($instance['title']);
		$daily = esc_attr($instance['daily']);
		$daily_range = esc_attr($instance['daily_range']);
		?>
		<p>
		<label for="<?php echo $this->get_field_id('title'); ?>">
		<?php _e('Title', BSEARCH_LOCAL_NAME); ?>: <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" /> 
		</label>
		</p>
		<p>
		<select class="widefat" id="<?php echo $this->get_field_id('daily'); ?>" name="<?php echo $this->get_field_name('daily'); ?>">
		  <option value="overall" <?php if ($daily=='overall') echo 'selected="selected"' ?>><?php _e('Overall', BSEARCH_LOCAL_NAME); ?></option>
		  <option value="daily" <?php if ($daily=='daily') echo 'selected="selected"' ?>><?php _e('Custom time period (Enter below)', BSEARCH_LOCAL_NAME); ?></option>
		</select>
		</p>
		<p>
		<label for="<?php echo $this->get_field_id('daily_range'); ?>">
		<?php _e('Range in number of days (applies only to custom option above)', BSEARCH_LOCAL_NAME); ?>: <input class="widefat" id="<?php echo $this->get_field_id('daily_range'); ?>" name="<?php echo $this->get_field_name('daily_range'); ?>" type="text" value="<?php echo esc_attr($daily_range); ?>" /> 
		</label>
		</p>
		
		<?php
	} //ending form creation
	function update($new_instance, $old_instance) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['daily'] = strip_tags($new_instance['daily']);
		$instance['daily_range'] = strip_tags($new_instance['daily_range']);
		return $instance;
	} //ending update
	function widget($args, $instance) {
		global $wpdb, $bsearch_url;
		
		extract($args, EXTR_SKIP);
		
		global $bsearch_settings;
		$limit = $bsearch_settings['heatmap_limit'];
		$largest = intval($bsearch_settings['heatmap_largest']);
		$smallest = intval($bsearch_settings['heatmap_smallest']);
		$hot = $bsearch_settings['heatmap_hot'];
		$cold = $bsearch_settings['heatmap_cold'];
		$unit = $bsearch_settings['heatmap_unit'];
		$before = $bsearch_settings['heatmap_before'];
		$after = $bsearch_settings['heatmap_after'];
		$daily_range = $instance['daily_range'];
		if (empty($daily_range)) $daily_range = $bsearch_settings['daily_range'];

		$title = apply_filters('widget_title', $instance['title']);
		if (empty($title)) $title = (($bsearch_settings['title']) ? strip_tags($bsearch_settings['title']) : __('Popular Searches', BSEARCH_LOCAL_NAME));
		$daily = $instance['daily'];
		$daily = (($daily=="overall") ? true : false);
		
		echo $before_widget;
		echo $before_title . $title . $after_title;

		if ($daily) {
			echo get_bsearch_heatmap(false, $smallest, $largest, $unit, $cold, $hot, $before, $after, '', $limit,$daily_range);
		} else {
			if ($bsearch_settings['d_use_js']) {
				echo '<script type="text/javascript" src="'.$bsearch_url.'/better-search-daily.js.php?widget=1"></script>';
			} else {
				echo get_bsearch_heatmap(true, $smallest, $largest, $unit, $cold, $hot, $before, $after, '', $limit,$daily_range);
			}
		}
		if ($bsearch_settings['show_credit']) echo '<br /><small>Powered by <a href="http://ajaydsouza.com/wordpress/plugins/better-search/">Better Search plugin</a></small>';

		echo $after_widget;

	} //ending function widget
}


/**
 * Default options.
 * 
 * @access public
 * @return array
 */
function bsearch_default_options() {
	$title = __('<h3>Popular Searches</h3>', BSEARCH_LOCAL_NAME);
	$title_daily = __('<h3>Weekly Popular Searches</h3>', BSEARCH_LOCAL_NAME);

	// get relevant post types
	$args = array (
				'public' => true,
				'_builtin' => true
			);
	$post_types	= http_build_query(get_post_types($args), '', '&');
	
	$custom_CSS = '
	#bsearchform { margin: 20px; padding: 20px; }
	#heatmap { margin: 20px; padding: 20px; border: 1px dashed #ccc }
	.bsearch_results_page { max-width:90%; margin: 20px; padding: 20px; }
	.bsearch_footer { text-align: center; }
	';
	
	$badwords = array( 'anal', 'anus', 'ass', 'bastard', 'beastiality', 'bestiality', 'bewb', 'bitch', 'blow', 'blumpkin', 'boob', 'cawk', 'cock', 'choad', 'cooter', 'cornhole', 'cum', 'cunt', 'dick', 'dildo', 'dong', 'dyke', 'douche', 'fag', 'faggot', 'fart', 'foreskin', 'fuck', 'fuk', 'gangbang', 'gook', 'handjob', 'hell', 'homo', 'honkey', 'humping', 'jiz', 'jizz', 'kike', 'kunt', 'labia', 'muff', 'nigger', 'nutsack', 'pen1s', 'penis', 'piss', 'poon', 'poop', 'punani', 'pussy', 'queef', 'queer', 'quim', 'rimjob', 'rape', 'rectal', 'rectum', 'semen', 'sex', 'shit', 'slut', 'spick', 'spoo', 'spooge', 'taint', 'titty', 'titties', 'twat', 'vag', 'vagina', 'vulva', 'wank', 'whore', );

	$bsearch_settings = 	Array (
						'show_credit' => false,			// Add link to plugin page of my blog in top posts list
						'track_popular' => true,			// Track the popular searches
						'use_fulltext' => true,			// Full text searches
						'd_use_js' => false,				// Use JavaScript for displaying Weekly Popular Searches
						'title' => $title,				// Title of Search Heatmap
						'title_daily' => $title_daily,	// Title of Daily Search Heatmap
						'limit' => '10',					// Search results per page
						'daily_range' => '7',				// Daily Popular will contain posts of how many days?

						'heatmap_smallest' => '10',		// Heatmap - Smallest Font Size
						'heatmap_largest' => '20',		// Heatmap - Largest Font Size
						'heatmap_unit' => 'pt',			// Heatmap - We'll use pt for font size
						'heatmap_cold' => 'ccc',			// Heatmap - cold searches
						'heatmap_hot' => '000',			// Heatmap - hot searches
						'heatmap_before' => '',			// Heatmap - Display before each search term
						'heatmap_after' => '&nbsp;',		// Heatmap - Display after each search term
						'heatmap_limit' => '30',			// Heatmap - Maximum number of searches to display in heatmap

						'weight_content' => '10',			// Weightage for content 
						'weight_title' => '1',			// Weightage for title
						'boolean_mode' => false,		// Turn BOOLEAN mode on if true
						
						'custom_CSS' => $custom_CSS,			// Custom CSS
						'post_types' => $post_types,		// WordPress custom post types
						'excerpt_length' => '50',		// Length of characters
						'link_new_window' => false,			// Open link in new window - Includes target="_blank" to links
						'link_nofollow' => true,			// Includes rel="nofollow" to links in heatmap
						
						'include_heatmap' => false,		// Include heatmap of searches in the search page
						'include_thumb' => false,		// Include thumbnail in search results
						
						'badwords' => implode(',', $badwords),		// Bad words filter
						);
	return $bsearch_settings;
}

/**
 * Function to read options from the database.
 * 
 * @access public
 * @return array
 */
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

/**
 * Create tables to store pageviews.
 * 
 * @access public
 * @return void
 */
function bsearch_install() {
	global $wpdb;
	global $bsearch_db_version;

    // Create full text index
	$wpdb->hide_errors();
    $wpdb->query('ALTER TABLE '.$wpdb->posts.' ENGINE = MYISAM;');
    $wpdb->query('ALTER TABLE '.$wpdb->posts.' ADD FULLTEXT bsearch (post_title, post_content);');
    $wpdb->query('ALTER TABLE '.$wpdb->posts.' ADD FULLTEXT bsearch_title (post_title);');
    $wpdb->query('ALTER TABLE '.$wpdb->posts.' ADD FULLTEXT bsearch_content (post_content);');
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

/**
 * Initialise Better Search Widgets.
 * 
 * @access public
 * @return void
 */
function init_bsearch(){
		register_widget('WidgetBSearch');
}
add_action('init', 'init_bsearch', 1); 


/*****************************************
 * UTILITY FUNCTIONS START HERE
 *
 *****************************************/

/**
 * Clean search string from XSS exploits.
 * 
 * @access public
 * @param string $val
 * @return string
 */
function bsearch_clean_terms($val) {
	global $bsearch_settings;
	
	$badwords = array_map('trim',explode(",",$bsearch_settings['badwords']));
	//$val = esc_attr($val);
	//$val = bsearch_RemoveXSS($val);
	//$val = bsearch_quote_smart($val);
	$val = wp_kses_post($val);
	$val_censored = bsearch_censorString($val, $badwords, ' ');
	$val = $val_censored['clean'];
	return $val;
}

function bsearch_RemoveXSS($val) {
   // remove all non-printable characters. CR(0a) and LF(0b) and TAB(9) are allowed
   // this prevents some character re-spacing such as <java\0script>
   // note that you have to handle splits with \n, \r, and \t later since they *are* allowed in some inputs
   $val = preg_replace('/([\x00-\x08\x0b-\x0c\x0e-\x19])/', '', $val);
   
   // straight replacements, the user should never need these since they're normal characters
   // this prevents like <IMG SRC=&#X40&#X61&#X76&#X61&#X73&#X63&#X72&#X69&#X70&#X74&#X3A &#X61&#X6C&#X65&#X72&#X74&#X28&#X27&#X58&#X53&#X53&#X27&#X29>
   $search = 'abcdefghijklmnopqrstuvwxyz';
   $search .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
   $search .= '1234567890!@#$%^&*()';
   $search .= '~`";:?+,/={}[]-_|\'\\';
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

// Clean quotes
function bsearch_quote_smart($value)
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

/**
 *  Generates a random string.
 *  @param        string          $chars        Chars that can be used.
 *  @param        int             $len          Length of the output string.
 *  string
 */
function bsearch_randCensor($chars, $len) {
	
	mt_srand(); // useful for < PHP4.2
	$lastChar = strlen($chars) - 1;
	$randOld = -1;
	$out = '';
	
	// create $len chars
	for ($i = $len; $i > 0; $i--) {
		// generate random char - it must be different from previously generated
		while (($randNew = mt_rand(0, $lastChar)) === $randOld) { }
		$randOld = $randNew;
		$out .= $chars[$randNew];
	}
	
	return $out;
	
}


/**
 *  Apply censorship to $string, replacing $badwords with $censorChar.
 *  @param        string          $string        String to be censored.
 *  @param        string[int]     $badwords      Array of badwords.
 *  @param        string          $censorChar    String which replaces bad words. If it's more than 1-char long,
 *                                               a random string will be generated from these chars. Default: '*'
 *  string[string]
 */
function bsearch_censorString($string, $badwords, $censorChar = '*') {  
              
		$leet_replace = array();
		$leet_replace['a']= '(a|a\.|a\-|4|@|Á|á|À|Â|à|Â|â|Ä|ä|Ã|ã|Å|å|α|Δ|Λ|λ)';
		$leet_replace['b']= '(b|b\.|b\-|8|\|3|ß|Β|β)';
		$leet_replace['c']= '(c|c\.|c\-|Ç|ç|¢|€|<|\(|{|©)';
		$leet_replace['d']= '(d|d\.|d\-|&part;|\|\)|Þ|þ|Ð|ð)';
		$leet_replace['e']= '(e|e\.|e\-|3|€|È|è|É|é|Ê|ê|∑)';
		$leet_replace['f']= '(f|f\.|f\-|ƒ)';
		$leet_replace['g']= '(g|g\.|g\-|6|9)';
		$leet_replace['h']= '(h|h\.|h\-|Η)';
		$leet_replace['i']= '(i|i\.|i\-|!|\||\]\[|]|1|∫|Ì|Í|Î|Ï|ì|í|î|ï)';
		$leet_replace['j']= '(j|j\.|j\-)';
		$leet_replace['k']= '(k|k\.|k\-|Κ|κ)';
		$leet_replace['l']= '(l|1\.|l\-|!|\||\]\[|]|£|∫|Ì|Í|Î|Ï)';
		$leet_replace['m']= '(m|m\.|m\-)';
		$leet_replace['n']= '(n|n\.|n\-|η|Ν|Π)';
		$leet_replace['o']= '(o|o\.|o\-|0|Ο|ο|Φ|¤|°|ø)';
		$leet_replace['p']= '(p|p\.|p\-|ρ|Ρ|¶|þ)';
		$leet_replace['q']= '(q|q\.|q\-)';
		$leet_replace['r']= '(r|r\.|r\-|®)';
		$leet_replace['s']= '(s|s\.|s\-|5|\$|§)';
		$leet_replace['t']= '(t|t\.|t\-|Τ|τ)';
		$leet_replace['u']= '(u|u\.|u\-|υ|µ)';
		$leet_replace['v']= '(v|v\.|v\-|υ|ν)';
		$leet_replace['w']= '(w|w\.|w\-|ω|ψ|Ψ)';
		$leet_replace['x']= '(x|x\.|x\-|Χ|χ)';
		$leet_replace['y']= '(y|y\.|y\-|¥|γ|ÿ|ý|Ÿ|Ý)';
		$leet_replace['z']= '(z|z\.|z\-|Ζ)';
     
        $words = explode(" ", $string);
        
		// is $censorChar a single char?
        $isOneChar = (strlen($censorChar) === 1);
		
        for ($x=0; $x<count($badwords); $x++) {

        	$replacement[$x] = $isOneChar
                ? str_repeat($censorChar,strlen($badwords[$x]))
                : bsearch_randCensor($censorChar,strlen($badwords[$x]));
			
        	$badwords[$x] =  '/'.str_ireplace(array_keys($leet_replace),array_values($leet_replace), $badwords[$x]).'/i';
        }
        
        $newstring = array();
        $newstring['orig'] = html_entity_decode($string);
        $newstring['clean'] =  preg_replace($badwords,$replacement, $newstring['orig']);    
        
        return $newstring;
           
}

/*********************************************************************
*				Admin Functions									*
********************************************************************/
// This function adds an Options page in WP Admin
if (is_admin() || strstr($_SERVER['PHP_SELF'], 'wp-admin/')) {
	require_once(ALD_BSEARCH_DIR . "/admin.inc.php");
	
	/**
	 * Adding WordPress plugin action links.
	 * 
	 * @access public
	 * @param array $links
	 * @return array
	 */
	function bsearch_plugin_actions_links( $links ) {
	
		return array_merge(
			array(
				'settings' => '<a href="' . admin_url( 'options-general.php?page=bsearch_options' ) . '">' . __('Settings', BSEARCH_LOCAL_NAME ) . '</a>'
			),
			$links
		);
	
	}
	add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'bsearch_plugin_actions_links' );

	/**
	 * Add meta links on Plugins page.
	 * 
	 * @access public
	 * @param array $links
	 * @param string $file
	 * @return void
	 */
	function bsearch_plugin_actions( $links, $file ) {
		static $plugin;
		if (!$plugin) $plugin = plugin_basename(__FILE__);
	 
		// create link
		if ($file == $plugin) {
			$links[] = '<a href="http://wordpress.org/support/plugin/better-search">' . __('Support', BSEARCH_LOCAL_NAME ) . '</a>';
			$links[] = '<a href="http://ajaydsouza.com/donate/">' . __('Donate', BSEARCH_LOCAL_NAME ) . '</a>';
		}
		return $links;
	}
	global $wp_version;
	if ( version_compare( $wp_version, '2.8alpha', '>' ) )
		add_filter( 'plugin_row_meta', 'bsearch_plugin_actions', 10, 2 ); // only 2.8 and higher
	else add_filter( 'plugin_action_links', 'bsearch_plugin_actions', 10, 2 );
	
	
} // End admin.inc

?>