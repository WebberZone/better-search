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
 * @since   1.2
 *
 * @param   array|string $args   Heatmap Parameters.
 * @return  string  Search heatmap
 */
function get_bsearch_heatmap( $args = array() ) {
	global $wpdb;

	$defaults = array(
		'daily'         => false,
		'smallest'      => intval( bsearch_get_option( 'heatmap_smallest' ) ),
		'largest'       => intval( bsearch_get_option( 'heatmap_largest' ) ),
		'unit'          => bsearch_get_option( 'heatmap_unit' ),
		'cold'          => bsearch_get_option( 'heatmap_cold' ),
		'hot'           => bsearch_get_option( 'heatmap_hot' ),
		'before'        => bsearch_get_option( 'heatmap_before' ),
		'after'         => bsearch_get_option( 'heatmap_after' ),
		'heatmap_limit' => intval( bsearch_get_option( 'heatmap_limit' ) ),
		'daily_range'   => intval( bsearch_get_option( 'daily_range' ) ),
	);

	// Parse incomming $args into an array and merge it with $defaults.
	$args = wp_parse_args( $args, $defaults );

	$table_name = $wpdb->prefix . 'bsearch';

	if ( $args['daily'] ) {
		$table_name .= '_daily';    // If we are viewing daily posts, set this to true.
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
			SELECT DISTINCT wp1.searchvar, wp2.sum_count
			FROM {$table_name} wp1,
					(SELECT searchvar, SUM(cntaccess) as sum_count
					FROM {$table_name}
					WHERE dp_date >= '%s'
					GROUP BY searchvar
					ORDER BY sum_count DESC LIMIT %d) wp2
					WHERE wp1.searchvar = wp2.searchvar
			ORDER by wp1.searchvar ASC
		";
	}

	$results = $wpdb->get_results( $wpdb->prepare( $sql, $sargs ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching

	if ( $results ) {
		foreach ( $results as $result ) {
			if ( ! $args['daily'] ) {
				$cntaccesss[] = $result->cntaccess;
			} else {
				$cntaccesss[] = $result->sum_count;
			}
		}
		$min    = min( $cntaccesss );
		$max    = max( $cntaccesss );
		$spread = $max - $min;

		// Calculate various font sizes.
		if ( $args['largest'] != $args['smallest'] ) { //phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
			$fontspread = $args['largest'] - $args['smallest'];
			if ( 0 != $spread ) { //phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
				$fontstep = $fontspread / $spread;
			} else {
				$fontstep = 0;
			}
		}

		// Calculate colors.
		if ( $args['hot'] != $args['cold'] ) { //phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
			$hotdec  = bsearch_html2rgb( $args['hot'] );
			$colddec = bsearch_html2rgb( $args['cold'] );
			for ( $i = 0; $i < 3; $i++ ) {
				$coldval[]     = $colddec[ $i ];
				$hotval[]      = $hotdec[ $i ];
				$colorspread[] = $hotdec[ $i ] - $colddec[ $i ];
				if ( 0 != $spread ) { //phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
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
				$cntaccess = $result->sum_count;
			}

			$textsearchvar = esc_attr( $result->searchvar );
			$url           = home_url() . '/?s=' . $textsearchvar;
			$fraction      = $cntaccess - $min;
			$fontsize      = $args['smallest'] + $fontstep * $fraction;

			$color = '';

			for ( $i = 0; $i < 3; $i++ ) {
				$color .= dechex( $coldval[ $i ] + ( $colorstep[ $i ] * $fraction ) );
			}
			$style = 'style="';
			if ( $args['largest'] != $args['smallest'] ) { //phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
				$style .= 'font-size:' . round( $fontsize ) . $args['unit'] . ';';
			}
			if ( $args['hot'] != $args['cold'] ) { //phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
				$style .= 'color:#' . $color . ';';
			}
			$style .= '"';

			$output .= $args['before'] . '<a href="' . $url . '" title="';
			/* translators: 1: Search term, 2: Number of searches */
			$output .= sprintf( _n( 'Search for %1$s (%2$s search)', 'Search for %1$s (%2$s searches)', $cntaccess, 'better-search' ), $textsearchvar, $cntaccess );
			$output .= '" ' . $style;
			if ( bsearch_get_option( 'link_nofollow' ) ) {
				$output .= ' rel="nofollow" ';
			}
			if ( bsearch_get_option( 'link_new_window' ) ) {
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
	 * @since   1.2
	 *
	 * @param   string          $output     Formatted excerpt
	 * @param   string|array    $args       Arguments
	 */
	return apply_filters( 'get_bsearch_heatmap', $output, $args );
}


/**
 * Manual Daily Better Search Heatmap.
 *
 * @since   1.0
 *
 * @return  string  Daily search heatmap
 */
function get_bsearch_pop_daily() {

	$output = '';

	$output .= '<div class="bsearch_heatmap">';
	$output .= bsearch_get_option( 'title_daily' );
	$output .= '<div text-align:center>';

	$output .= get_bsearch_heatmap(
		array(
			'daily' => 1,
		)
	);
	$output .= '</div>';

	if ( bsearch_get_option( 'show_credit' ) ) {
		$output .= '<br /><small>Powered by <a href="https://webberzone.com/plugins/better-search/">Better Search plugin</a></small>';
	}

	$output .= '</div>';

	/**
	 * Filters the daily search heatmap HTML
	 *
	 * @since   1.2
	 *
	 * @param   string  $output Daily search heatmap HTML
	 */
	return apply_filters( 'get_bsearch_pop_daily', $output );
}


/**
 * Echo daily popular searches.
 *
 * @since   1.0
 */
function the_pop_searches_daily() {
	echo get_bsearch_pop_daily(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

/**
 * Manual Overall Better Search Heatmap.
 *
 * @since   1.0
 *
 * @return  $string Popular searches heatmap
 */
function get_bsearch_pop() {

	$output = '';

	$output .= '<div class="bsearch_heatmap">';
	$output .= bsearch_get_option( 'title' );
	$output .= '<div text-align:center>';

	$output .= get_bsearch_heatmap(
		array(
			'daily' => 0,
		)
	);
	$output .= '</div>';

	if ( bsearch_get_option( 'show_credit' ) ) {
		$output .= '<br /><small>Powered by <a href="https://webberzone.com/plugins/better-search/">Better Search plugin</a></small>';
	}

	$output .= '</div>';

	/**
	 * Filters the overall popular searches heatmap HTML
	 *
	 * @since   1.2
	 *
	 * @param   string  $output Daily search heatmap HTML
	 */
	return apply_filters( 'get_bsearch_pop', $output );
}


/**
 * Echo popular searches list.
 *
 * @since   1.0
 */
function the_pop_searches() {
	echo get_bsearch_pop(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}


