<?php
/**
 * Template functions used by Better Search
 *
 * @package Better_Search
 */

// If this file is called directly, then abort execution.
if ( ! defined( 'WPINC' ) ) {
	die( "Aren't you supposed to come here via WP-Admin?" );
}

/**
 * Function to return the header links of the results page.
 *
 * @since   1.2
 *
 * @param   string $search_query   Search string.
 * @param   int    $numrows        Total number of results.
 * @param   int    $limit          Results per page.
 * @return  string  Formatted header table of search results pages
 */
function get_bsearch_header( $search_query, $numrows, $limit ) {

	$output      = '';
	$match_range = get_bsearch_range( $numrows, $limit );

	$pages = intval( $numrows / $limit ); // Number of results pages.

	if ( $numrows % $limit ) {
		$pages++;   // If remainder so add one page.
	}

	if ( ( $pages < 1 ) || ( 0 == $pages ) ) { //phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
		$total = 1; // If $pages is less than one or equal to 0, total pages is 1.
	} else {
		$total = $pages;    // Else total pages is $pages value.
	}

	$first   = $match_range[0] + 1;   // The first result on the page (Starts with 0).
	$last    = $match_range[1] + 1;    // The last result on the page (Starts with 0).
	$current = ( $match_range[0] / $limit ) + 1; // Current page number.

	$output .= '<table width="100%" border="0" class="bsearch_nav">
	 <tr class="bsearch_nav_row1">
	  <td width="50%" style="text-align:left">';

	/* translators: 1: First, 2: Last, 3: Number of rows */
	$output .= sprintf( __( 'Results <strong>%1$s</strong> - <strong>%2$s</strong> of <strong>%3$s</strong>', 'better-search' ), $first, $last, $numrows );

	$output .= '
	  </td>
	  <td width="50%" style="text-align:right">';
	/* translators: 1: Current page number, 2: Total pages */
	$output .= sprintf( __( 'Page <strong>%1$s</strong> of <strong>%2$s</strong>', 'better-search' ), $current, $total );

	$sencoded = rawurlencode( $search_query );

	$output .= '
	  </td>
	 </tr>
	 <tr class="bsearch_nav_row2">
	  <td style="text-align:left"></td>';
	$output .= '<td style="text-align:right">';
	$output .= __( 'Results per-page', 'better-search' );
	$output .= ': <a href="' . home_url() . '/?s=' . $sencoded . '&limit=10">10</a> | <a href="' . home_url() . '/?s=' . $sencoded . '&limit=20">20</a> | <a href="' . home_url() . '/?s=' . $sencoded . '&limit=50">50</a> | <a href="' . home_url() . '/?s=' . $sencoded . '&limit=100">100</a>
	  </td>
	 </tr>
	</table>';

	/**
	 * Filter formatted string with header of page
	 *
	 * @since   1.2
	 *
	 * @param   string  $output         HTML of header table
	 * @param   string  $search_query   Search string
	 * @param   int     $numrows        Total number of results
	 * @param   int     $limit          Results per page
	 */
	return apply_filters( 'get_bsearch_header', $output, $search_query, $numrows, $limit );
}


/**
 * Function to return the footer links of the results page.
 *
 * @since   1.2
 *
 * @param   string $search_query   Search string.
 * @param   int    $numrows        Total results.
 * @param   int    $limit          Results per page.
 * @return  string  Formatted footer of search results pages
 */
function get_bsearch_footer( $search_query, $numrows, $limit ) {

	$match_range = get_bsearch_range( $numrows, $limit );
	$page        = $match_range[0];
	$pages       = intval( $numrows / $limit ); // Number of results pages.
	if ( $numrows % $limit ) {
		$pages++;   // If remainder so add one page.
	}

	$search_query = rawurlencode( $search_query );

	$output = '<p class="bsearch_footer">';
	if ( 0 != $page ) { //phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
		$back_page = $page - $limit;
		$output   .= '<a href="' . home_url() . "/?s=$search_query&limit=$limit&bpaged=$back_page\">&laquo; ";
		$output   .= __( 'Previous', 'better-search' );
		$output   .= "</a>    \n";
	}

	$pagination_range = 4;          // Number of pagination elements.

	for ( $i = 1; $i <= $pages; $i++ ) { // loop through each page and give link to it.
		$current = ( $match_range[0] / $limit ) + 1; // Current page number.
		if ( $i >= $current + $pagination_range && $i < $pages ) {
			if ( $i == $current + $pagination_range ) { //phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
				$output .= '&hellip;&nbsp;';
			}
			continue;
		}
		if ( $i < $current - $pagination_range + 1 && $i < $pages ) {
			continue;
		}
		$ppage = $limit * ( $i - 1 );
		if ( $ppage == $page ) { //phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
			$output .= "<b>$i</b>\n";   // If current page don't give link, just text.
		} else {
			$output .= '<a href="' . home_url() . "/?s=$search_query&limit=$limit&bpaged=$ppage\">$i</a> \n";
		}
	}

	if ( ! ( ( ( $page + $limit ) / $limit ) >= $pages ) && 1 != $pages ) { //phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
		$next_page = $page + $limit;
		$output   .= '    <a href="' . home_url() . "/?s=$search_query&limit=$limit&bpaged=$next_page\">";
		$output   .= __( 'Next', 'better-search' );
		$output   .= ' &raquo;</a>';
	}
	$output .= '</p>';

	/**
	 * Filter formatted string with footer of page
	 *
	 * @since   1.2
	 *
	 * @param   string  $output         HTML of footer
	 * @param   string  $search_query   Search string
	 * @param   int     $numrows        Total results
	 * @param   int     $limit          Results per page
	 */
	return apply_filters( 'get_bsearch_footer', $output, $search_query, $numrows, $limit );
}


/**
 * Function to convert the mySQL score to percentage.
 *
 * @since   1.2
 *
 * @param   object $search     Search result object.
 * @param   int    $score      Score for the search result.
 * @param   int    $topscore   Score for the most relevant search result.
 * @return  int     Score converted to percentage
 */
function get_bsearch_score( $search, $score, $topscore ) {

	$output = '';

	if ( $score > 0 ) {
		$score   = $score * 100 / $topscore;
		$output  = __( 'Relevance: ', 'better-search' );
		$output .= bsearch_number_format_i18n( $score, 0 ) . '%';
	}

	/**
	 * Filter search result score
	 *
	 * @since   1.2
	 *
	 * @param   string  $output     HTML of footer
	 * @param   string  $search     Search result object
	 * @param   int     $score      Score for the search result
	 * @param   int     $topscore   Score for the most relevant result
	 */
	return apply_filters( 'get_bsearch_score', $output, $search, $score, $topscore );
}


/**
 * Function to get post date.
 *
 * @since   1.2
 *
 * @param   object $search     Search result object.
 * @param   string $before     Added before the date.
 * @param   string $after      Added after the date.
 * @param   string $format     Date format.
 * @return  string  Formatted date string
 */
function get_bsearch_date( $search, $before = '', $after = '', $format = '' ) {
	if ( ! $format ) {
		$format = get_option( 'date_format' );
	}

	$output = $before . date_i18n( $format, strtotime( $search->post_date ) ) . $after;

	/**
	 * Filter formatted string with search result date
	 *
	 * @since   1.2
	 *
	 * @param   string  $output     Formatted date string
	 * @param   object  $search     Search result object
	 * @param   string  $before     Added before the date
	 * @param   string  $after      Added after the date
	 * @param   string  $format     Date format
	 */
	return apply_filters( 'get_bsearch_date', $output, $search, $before, $after, $format );
}


/**
 * Function to create an excerpt for the post.
 *
 * @since   1.2
 *
 * @param   int        $id             Post ID.
 * @param   int|string $excerpt_length Length of the excerpt in words.
 * @param   bool       $use_excerpt    Use post excerpt or content.
 * @return  string      Excerpt
 */
function get_bsearch_excerpt( $id, $excerpt_length = 0, $use_excerpt = true ) {
	$content = '';

	$post = get_post( $id );
	if ( empty( $post ) ) {
		return '';
	}
	if ( $use_excerpt ) {
		$content = $post->post_excerpt;
	}
	if ( empty( $content ) ) {
		$content = $post->post_content;
	}

	$output = wp_strip_all_tags( strip_shortcodes( $content ) );

	if ( $excerpt_length > 0 ) {
		$output = wp_trim_words( $output, $excerpt_length );
	}

	if ( post_password_required( $post ) ) {
		$output = __( 'There is no excerpt because this is a protected post.', 'better-search' );
	}

	/**
	 * Filter formatted string with search result exeerpt
	 *
	 * @since   1.2
	 *
	 * @param   string      $output         Formatted excerpt
	 * @param   int         $id             Post ID
	 * @param   int|string  $excerpt_length Length of the excerpt in words
	 * @param   bool        $use_excerpt    Use post excerpt or content?
	 */
	return apply_filters( 'get_bsearch_excerpt', $output, $id, $excerpt_length, $use_excerpt );
}


/**
 * Function to fetch search form.
 *
 * @since   1.1
 *
 * @param   string $search_query   Search query.
 * @return  string  Search form
 */
function get_bsearch_form( $search_query ) {

	if ( '' === $search_query ) {
		$search_query = get_bsearch_query();
	}
	$search_query = esc_attr( $search_query );

	$form = '
	<div style="text-align:center"><form method="get" class="bsearchform" action="' . home_url() . '/" >
	<label class="hidden" for="s">' . __( 'Search for:', 'better-search' ) . '</label>
	<input type="text" value="' . $search_query . '" name="s" class="s" />
	<input type="submit" class="searchsubmit" value="' . __( 'Search', 'better-search' ) . '" />
	</form></div>
	';

	/**
	 * Filters the title of the page
	 *
	 * @since   1.2
	 *
	 * @param   string  $form   HTML to display the form
	 * @param   string  $search_query   Search query
	 */
	return apply_filters( 'get_bsearch_form', $form, $search_query );
}


/**
 * Function to retrieve Daily Popular Searches Title.
 *
 * @since   1.1
 *
 * @param   bool $text_only  With or without tags.
 * @return  string  Title of Daily Popular searches.
 */
function get_bsearch_title_daily( $text_only = true ) {

	$title = ( $text_only ) ? wp_strip_all_tags( bsearch_get_option( 'title_daily' ) ) : bsearch_get_option( 'title_daily' );

	/**
	 * Filters the title of the widget
	 *
	 * @since   1.2
	 *
	 * @param   string  $title  Title of the daily popular searches
	 */
	return apply_filters( 'get_bsearch_title_daily', $title );
}


/**
 * Function to retrieve Overall Popular Searches Title.
 *
 * @since   1.1
 *
 * @param   bool $text_only  With or without tags.
 * @return  string  Title of Overall Popular searches
 */
function get_bsearch_title( $text_only = true ) {

	$title = ( $text_only ) ? wp_strip_all_tags( bsearch_get_option( 'title' ) ) : bsearch_get_option( 'title' );

	/**
	 * Filters the title of the widget
	 *
	 * @since   1.2
	 *
	 * @param   string  $title  Title of the daily popular searches
	 */
	return apply_filters( 'get_bsearch_title', $title );
}


