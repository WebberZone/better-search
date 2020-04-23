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
		'smallest'      => absint( bsearch_get_option( 'heatmap_smallest' ) ),
		'largest'       => absint( bsearch_get_option( 'heatmap_largest' ) ),
		'unit'          => bsearch_get_option( 'heatmap_unit', 'pt' ),
		'cold'          => bsearch_get_option( 'heatmap_cold' ),
		'hot'           => bsearch_get_option( 'heatmap_hot' ),
		'before'        => bsearch_get_option( 'heatmap_before' ),
		'after'         => bsearch_get_option( 'heatmap_after' ),
		'heatmap_limit' => absint( bsearch_get_option( 'heatmap_limit' ) ),
		'daily_range'   => absint( bsearch_get_option( 'daily_range' ) ),
	);

	// Parse incomming $args into an array and merge it with $defaults.
	$args = wp_parse_args( $args, $defaults );

	$output = '';

	$results = get_bsearch_heatmap_counts( $args );

	if ( $results ) {
		$counts = wp_list_pluck( $results, 'count' );

		$min    = min( $counts );
		$max    = max( $counts );
		$spread = absint( $max - $min );

		// Calculate various font sizes.
		$fontspread = $args['largest'] - $args['smallest'];
		if ( 0 !== $spread ) {
			$fontstep = $fontspread / $spread;
		} else {
			$fontstep = 0;
		}

		// Calculate colors.
		$hotdec  = bsearch_html2rgb( $args['hot'] );
		$colddec = bsearch_html2rgb( $args['cold'] );
		for ( $i = 0; $i < 3; $i++ ) {
			$coldval[]     = $colddec[ $i ];
			$hotval[]      = $hotdec[ $i ];
			$colorspread[] = $hotdec[ $i ] - $colddec[ $i ];
			if ( 0 !== $spread ) {
				$colorstep[] = ( $hotdec[ $i ] - $colddec[ $i ] ) / $spread;
			} else {
				$colorstep[] = 0;
			}
		}

		foreach ( $results as $result ) {
			$count     = $result->count;
			$searchvar = esc_attr( $result->searchvar );
			$url       = add_query_arg( array( 's' => $searchvar ), home_url( '/' ) );
			$fraction  = $count - $min;
			$fontsize  = $args['smallest'] + $fontstep * $fraction;

			$color = '';

			for ( $i = 0; $i < 3; $i++ ) {
				$color .= dechex( $coldval[ $i ] + ( $colorstep[ $i ] * $fraction ) );
			}
			$style = sprintf( 'font-size:%1$s%2$s;color:#%3$s;', round( $fontsize ), $args['unit'], $color );

			/**
			 * Filter the value of the style tag of heatmap links.
			 *
			 * @since 2.5.0
			 *
			 * @param string $style     Value of the style tag of the link.
			 * @param string $searchvar Search term.
			 * @param object $result    Search results object.
			 */
			$style = apply_filters( 'bsearch_heatmap_style', $style, $searchvar, $result );

			$class = '';

			/**
			 * Filter the value of the class tag of heatmap links.
			 *
			 * @since 2.5.0
			 *
			 * @param string $style     Value of the class tag of the link.
			 * @param string $searchvar Search term.
			 * @param object $result    Search results object.
			 */
			$class = apply_filters( 'bsearch_heatmap_class', $class, $searchvar, $result );

			$title = sprintf(
				/* translators: 1: Search term, 2: Number of searches */
				_n( 'Search for %1$s (%2$s search)', 'Search for %1$s (%2$s searches)', $count, 'better-search' ),
				$searchvar,
				$count
			);

			/**
			 * Filter the value of the title tag of heatmap links.
			 *
			 * @since 2.5.0
			 *
			 * @param string $title     Value of the title tag of the link.
			 * @param string $searchvar Search term.
			 * @param int    $count     Count.
			 * @param object $result    Search results object.
			 */
			$title = apply_filters( 'bsearch_heatmap_title', $title, $searchvar, $count, $result );

			$rel    = ( bsearch_get_option( 'link_nofollow' ) ) ? 'rel="nofollow"' : '';
			$target = ( bsearch_get_option( 'link_new_window' ) ) ? 'target="_blank"' : '';

			$output .= $args['before'];
			$output .= sprintf(
				'<a href="%1$s" title="%2$s" style="%3$s" class="$4$s" %5$s %6$s>%7$s</a>',
				esc_url( $url ),
				esc_attr( $title ),
				esc_attr( $style ),
				esc_attr( $class ),
				$rel,
				$target,
				$searchvar
			);
			$output .= $args['after'] . ' ';
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
 * Get the Search Heatmap terms.
 *
 * @since 2.5.0
 *
 * @param  array|string $args Heatmap Parameters.
 * @return array              Array of heatmap terms.
 */
function get_bsearch_heatmap_counts( $args = array() ) {
	global $wpdb;

	$defaults = array(
		'daily'         => false,
		'heatmap_limit' => intval( bsearch_get_option( 'heatmap_limit' ) ),
		'daily_range'   => intval( bsearch_get_option( 'daily_range' ) ),
	);

	// Parse incomming $args into an array and merge it with $defaults.
	$args = wp_parse_args( $args, $defaults );

	$table_name = $wpdb->prefix . 'bsearch';

	if ( $args['daily'] ) {
		$table_name .= '_daily';    // If we are viewing daily posts, set this to true.
	}

	if ( ! $args['daily'] ) {
		$sargs = array(
			$args['heatmap_limit'],
		);

		$sql = "
			SELECT searchvar, cntaccess as count
			FROM {$table_name} WHERE accessedid IN
				(SELECT accessedid
				FROM {$table_name}
				WHERE searchvar <> ''
				ORDER BY cntaccess DESC, searchvar ASC)
			ORDER by accessedid LIMIT %d
		";
	} else {
		$current_date = bsearch_get_from_date( null, $args['daily_range'] );

		$sargs = array(
			$current_date,
			$args['heatmap_limit'],
		);

		$sql = "
			SELECT DISTINCT wp1.searchvar, wp2.count
			FROM {$table_name} wp1,
				(SELECT searchvar, SUM(cntaccess) as count
				FROM {$table_name}
				WHERE dp_date >= '%s'
				GROUP BY searchvar
				ORDER BY count DESC LIMIT %d) wp2
				WHERE wp1.searchvar = wp2.searchvar
			ORDER by wp1.searchvar ASC
		";
	}

	$results = $wpdb->get_results( $wpdb->prepare( $sql, $sargs ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching

	/**
	 * Filter formatted string with the search heatmap
	 *
	 * @since 2.5.0
	 *
	 * @param array $results Array of search terms.
	 * @param array $args    Array of arguments.
	 */
	return apply_filters( 'get_bsearch_heatmap_counts', $results, $args );
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
		$output .= bsearch_get_credit_link();
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
		$output .= bsearch_get_credit_link();
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


