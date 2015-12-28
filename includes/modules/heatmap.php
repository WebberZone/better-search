<?php
/**
 * Better Search Heatmap Functions
 *
 * @package Better_Search
 */

// If this file is called directly, then abort execution.
if ( ! defined( 'WPINC' ) ) {
	die;
}


/**
 * Get the Search Heatmap.
 *
 * @since	1.2
 *
 * @param	array|string $args   Heatmap Parameters
 * @return	string	Search heatmap
 */
function get_bsearch_heatmap( $args = array() ) {
	global $wpdb, $bsearch_url, $bsearch_settings;

	$defaults = array(
		'daily' => false,
		'smallest' => intval( $bsearch_settings['heatmap_smallest'] ),
		'largest' => intval( $bsearch_settings['heatmap_largest'] ),
		'unit' => $bsearch_settings['heatmap_unit'],
		'cold' => $bsearch_settings['heatmap_cold'],
		'hot' => $bsearch_settings['heatmap_hot'],
		'before' => $bsearch_settings['heatmap_before'],
		'after' => $bsearch_settings['heatmap_after'],
		'heatmap_limit' => intval( $bsearch_settings['heatmap_limit'] ),
		'daily_range' => intval( $bsearch_settings['daily_range'] ),
	);

	// Parse incomming $args into an array and merge it with $defaults
	$args = wp_parse_args( $args, $defaults );

	$table_name = $wpdb->prefix . 'bsearch';

	if ( $args['daily'] ) {
		$table_name .= '_daily';	// If we're viewing daily posts, set this to true
	}
	$output = '';

	if ( ! $args['daily'] ) {
		$sargs = array(
			$args['heatmap_limit'],
		);

		$sql = "SELECT searchvar, cntaccess FROM {$table_name} WHERE accessedid IN (SELECT accessedid FROM {$table_name} WHERE searchvar <> '' ORDER BY cntaccess DESC, searchvar ASC) ORDER by accessedid LIMIT %d";
	} else {
		$current_time = current_time( 'timestamp', 0 );
		$current_time = $current_time - ( $args['daily_range'] - 1 ) * 3600 * 24;
		$current_date = date_i18n( 'Y-m-j', $current_time );

		$sargs = array(
			$current_date,
			$args['heatmap_limit'],
		);

		$sql = "
			SELECT DISTINCT wp1.searchvar, wp2.sumCount
			FROM {$table_name} wp1,
					(SELECT searchvar, SUM(cntaccess) as sumCount
					FROM {$table_name}
					WHERE dp_date >= '%s'
					GROUP BY searchvar
					ORDER BY sumCount DESC LIMIT %d) wp2
					WHERE wp1.searchvar = wp2.searchvar
			ORDER by wp1.searchvar ASC
		";
	}

	$results = $wpdb->get_results( $wpdb->prepare( $sql, $sargs ) );

	if ( $results ) {
		foreach ( $results as $result ) {
			if ( ! $args['daily'] ) {
				$cntaccesss[] = $result->cntaccess;
			} else {
				$cntaccesss[] = $result->sumCount;
			}
		}
		$min = min( $cntaccesss );
		$max = max( $cntaccesss );
		$spread = $max - $min;

		// Calculate various font sizes
		if ( $args['largest'] != $args['smallest'] ) {
			$fontspread = $args['largest'] - $args['smallest'];
			if ( 0 != $spread ) {
				$fontstep = $fontspread / $spread;
			} else {
				$fontstep = 0;
			}
		}

		// Calculate colors
		if ( $args['hot'] != $args['cold'] ) {
			$hotdec = bsearch_html2rgb( $args['hot'] );
			$colddec = bsearch_html2rgb( $args['cold'] );
			for ( $i = 0; $i < 3; $i++ ) {
				$coldval[] = $colddec[ $i ];
				$hotval[] = $hotdec[ $i ];
				$colorspread[] = $hotdec[ $i ] - $colddec[ $i ];
				if ( 0 != $spread ) {
					$colorstep[] = ( $hotdec[ $i ] - $colddec[ $i ] ) / $spread;
				} else {
					$colorstep[] = 0;
				}
			}
		}

		foreach ( $results as $result ) {
			if ( ! $args['daily'] ) {
				$cntaccess = $result->cntaccess;
			} else {
				$cntaccess = $result->sumCount;
			}

			$textsearchvar = esc_attr( $result->searchvar );
			$url  = home_url() . '/?s=' . $textsearchvar;
			$fraction = $cntaccess - $min;
			$fontsize = $args['smallest'] + $fontstep * $fraction;

			$color = '';

			for ( $i = 0; $i < 3; $i++ ) {
				$color .= dechex( $coldval[ $i ] + ( $colorstep[ $i ] * $fraction ) );
			}
			$style = 'style="';
			if ( $args['largest'] != $args['smallest'] ) {
				$style .= 'font-size:' . round( $fontsize ) . $args['unit'] . ';';
			}
			if ( $args['hot'] != $args['cold'] ) {
				$style .= 'color:#' . $color . ';';
			}
			$style .= '"';

			$output .= $args['before'] . '<a href="' . $url . '" title="';
			$output .= sprintf( _n( 'Search for %1$s (%2$s search)', 'Search for %1$s (%2$s searches)', $cntaccess, 'better-search' ), $textsearchvar, $cntaccess );
			$output .= '" '.$style;
			if ( $bsearch_settings['link_nofollow'] ) {
				$output .= ' rel="nofollow" ';
			}
			if ( $bsearch_settings['link_new_window'] ) {
				$output .= ' target="_blank" ';
			}
			$output .= '>' . $textsearchvar . '</a>' . $args['after'] . ' ';
		}
	} else {
		$output = __( 'No searches made yet', 'better-search' );
	}

	/**
	 * Filter formatted string with the search heatmap
	 *
	 * @since	1.2
	 *
	 * @param	string			$output		Formatted excerpt
	 * @param	string|array 	$args		Arguments
	 */
	return apply_filters( 'get_bsearch_heatmap', $output, $args );
}


/**
 * Manual Daily Better Search Heatmap.
 *
 * @since	1.0
 *
 * @return	string	Daily search heatmap
 */
function get_bsearch_pop_daily() {

	global $bsearch_settings, $bsearch_url;

	$output = '';

	$output .= '<div class="bsearch_heatmap">';
	$output .= $bsearch_settings['title_daily'];
	$output .= '<div text-align:center>';

	$output .= get_bsearch_heatmap( array(
		'daily' => 1,
	) );
	$output .= '</div>';

	if ( $bsearch_settings['show_credit'] ) {
		$output .= '<br /><small>Powered by <a href="https://webberzone.com/plugins/better-search/">Better Search plugin</a></small>';
	}

	$output .= '</div>';

	/**
	 * Filters the daily search heatmap HTML
	 *
	 * @since	1.2
	 *
	 * @param	string	$output	Daily search heatmap HTML
	 */
	return apply_filters( 'get_bsearch_pop_daily', $output );
}


/**
 * Echo daily popular searches.
 *
 * @since	1.0
 */
function the_pop_searches_daily() {
	echo get_bsearch_pop_daily();
}

/**
 * Manual Overall Better Search Heatmap.
 *
 * @since	1.0
 *
 * @return	$string	Popular searches heatmap
 */
function get_bsearch_pop() {

	global $bsearch_settings;

	$output = '';

	$output .= '<div class="bsearch_heatmap">';
	$output .= $bsearch_settings['title'];
	$output .= '<div text-align:center>';

	$output .= get_bsearch_heatmap( array(
		'daily' => 0,
	) );
	$output .= '</div>';

	if ( $bsearch_settings['show_credit'] ) {
		$output .= '<br /><small>Powered by <a href="https://webberzone.com/plugins/better-search/">Better Search plugin</a></small>';
	}

	$output .= '</div>';

	/**
	 * Filters the overall popular searches heatmap HTML
	 *
	 * @since	1.2
	 *
	 * @param	string	$output	Daily search heatmap HTML
	 */
	return apply_filters( 'get_bsearch_pop', $output );
}


/**
 * Echo popular searches list.
 *
 * @since	1.0
 */
function the_pop_searches() {
	echo get_bsearch_pop();
}


