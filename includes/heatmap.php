<?php
/**
 * Better Search Heatmap Functions
 *
 * @package Better_Search
 */

use WebberZone\Better_Search\Util\Helpers;

// If this file is called directly, then abort execution.
if ( ! defined( 'WPINC' ) ) {
	die;
}


/**
 * Display the header table on the search results page.
 *
 * @since 3.0.0
 *
 * @see get_bsearch_heatmap() for all available arguments.
 *
 * @param string|array $args {
 *     Optional. Array or string of parameters.
 *
 *     @type bool        $daily  True for Daily, False for Overall searches.
 *     @type string      $before Markup to prepend to the relevance score.
 *     @type string      $after  Markup to append to the relevance score.
 *     @type bool        $echo   Echo or return?
 *     @type string|null $title  Title of the heatmap. If null, then use 'title' or 'title_daily' setting.
 * }
 * @return void|string Void if 'echo' argument is true, the title attribute if 'echo' is false.
 */
function the_bsearch_heatmap( $args = array() ) {

	$defaults = array(
		'daily'  => false,
		'before' => '',
		'after'  => '',
		'echo'   => true,
		'title'  => null,
	);
	$args     = wp_parse_args( $args, $defaults );

	$heatmap = get_bsearch_heatmap( $args );

	if ( is_null( $args['title'] ) ) {
		$title = $args['daily'] ? bsearch_get_option( 'title_daily' ) : bsearch_get_option( 'title' );
	} else {
		$title = $args['title'];
	}

	$output  = '<div class="bsearch_heatmap">';
	$output .= '<h2>' . $title . '</h2>';
	$output .= $args['before'] . $heatmap . $args['after'];

	if ( bsearch_get_option( 'show_credit' ) ) {
		$output .= Helpers::get_credit_link();
	}

	$output .= '</div>';

	/**
	 * Filters the displayed heatmap.
	 *
	 * @since 3.0.0
	 *
	 * @param string $output The heatmap.
	 * @param array  $args   Arguments array.
	 */
	$output = apply_filters( 'the_bsearch_heatmap', $output, $args );

	if ( $args['echo'] ) {
		echo $output; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	} else {
		return $output;
	}
}


/**
 * Get the Search Heatmap.
 *
 * @since 1.2
 *
 * @param string|array $args {
 *     Optional. Array or string of parameters.
 *
 *     @type bool     $daily                      True for Daily, False for Overall searches.
 *     @type int      $daily_range                Number of days if `$daily` is true.
 *     @type int      $smallest                   Smallest font size used to display search terms. Paired
 *                                                with the value of `$unit`, to determine CSS text
 *                                                size unit.
 *     @type int      $largest                    Largest font size used to display search terms. Paired
 *                                                with the value of `$unit`, to determine CSS text
 *                                                size unit.
 *     @type string   $unit                       CSS text size unit to use with the `$smallest`
 *                                                and `$largest` values. Accepts any valid CSS text
 *                                                size unit. Default 'pt'.
 *     @type string   $hot                        Font color of largest search term.
 *     @type string   $cold                       Font color of smallest search term.
 *     @type int      $number                     The number of search terms to return. Accepts any
 *                                                positive integer or zero to return all.
 *     @type string   $before_term                Text to display before the search term.
 *     @type string   $after_term                 Text to display after the search term.
 *     @type bool     link_nofollow               Whether to add the "nofollow" attribute to the link.
 *     @type bool     link_new_window             Whether to add the "_blank" attribute to the link.
 *     @type string   $format                     Format to display the search terms cloud in. Accepts 'flat'
 *                                                (search terms separated with spaces), 'list' (search terms displayed
 *                                                in an unordered list), or 'array' (returns an array).
 *                                                Default 'flat'.
 *     @type string   $separator                  HTML or text to separate the search terms. Default "\n" (newline).
 *     @type string   $orderby                    Value to order search terms by. Accepts 'name' or 'count'.
 *                                                Default 'name'. The {@see 'bsearch_heatmap_sort'} filter
 *                                                can also affect how search terms are sorted.
 *     @type string   $order                      How to order the search terms. Accepts 'ASC' (ascending),
 *                                                'DESC' (descending), or 'RAND' (random). Default 'RAND'.
 *     @type string   $topic_count_text           Nooped plural text from _n_noop() to supply to
 *                                                search result counts. Default null.
 *     @type bool|int $show_count                 Whether to display the search result counts. Default 0. Accepts
 *                                                0, 1, or their bool equivalents.
 *     @type string   $no_results_text            Text to display if there are no search results.
 * }
 * @return string|string[] Search terms cloud as a string or an array, depending on 'format' argument.
 */
function get_bsearch_heatmap( $args = array() ) {

	$defaults = array(
		'daily'            => false,
		'daily_range'      => absint( bsearch_get_option( 'daily_range' ) ),
		'smallest'         => absint( bsearch_get_option( 'heatmap_smallest' ) ),
		'largest'          => absint( bsearch_get_option( 'heatmap_largest' ) ),
		'unit'             => bsearch_get_option( 'heatmap_unit', 'pt' ),
		'hot'              => bsearch_get_option( 'heatmap_hot' ),
		'cold'             => bsearch_get_option( 'heatmap_cold' ),
		'number'           => absint( bsearch_get_option( 'heatmap_limit' ) ),
		'before_term'      => bsearch_get_option( 'heatmap_before' ),
		'after_term'       => bsearch_get_option( 'heatmap_after' ),
		'link_nofollow'    => bsearch_get_option( 'link_nofollow' ),
		'link_new_window'  => bsearch_get_option( 'link_new_window' ),
		'format'           => 'flat',
		'separator'        => "\n",
		'orderby'          => 'count',
		'order'            => 'RAND',
		'topic_count_text' => null,
		'show_count'       => 0,
		'no_results_text'  => __( 'No searches made yet', 'better-search' ),
	);

	// Parse incomming $args into an array and merge it with $defaults.
	$args = wp_parse_args( $args, $defaults );

	$output = ( 'array' === $args['format'] ) ? array() : '';

	$results = get_bsearch_heatmap_counts( $args );

	if ( empty( $results ) ) {
		return $args['no_results_text'];
	}

	// First look for nooped plural support via topic_count_text.
	if ( isset( $args['topic_count_text'] ) ) {
		$translate_nooped_plural = $args['topic_count_text'];
	} else {
		/* translators: %s: Number of items (search results). */
		$translate_nooped_plural = _n_noop( '%s search', '%s searches' );
	}

	/**
	 * Filters how the items in a search results heatmap are sorted.
	 *
	 * @since 3.0.0
	 *
	 * @param object[] $results Ordered array of search results.
	 * @param array    $args    Arguments array.
	 */
	$results_sorted = apply_filters( 'tag_cloud_sort', $results, $args );
	if ( empty( $results_sorted ) ) {
		return $args['no_results_text'];
	}

	if ( $results_sorted !== $results ) {
		$results = $results_sorted;
		unset( $results_sorted );
	} elseif ( 'RAND' === $args['order'] ) {
			shuffle( $results );
	} else {

		// SQL cannot save you; this is a second (potentially different) sort on a subset of data.
		if ( 'name' === $args['orderby'] ) {
			uasort( $results, '_wp_object_name_sort_cb' );
		} else {
			uasort(
				$results,
				function ( $a, $b ): int {
					return $a->count <=> $b->count;
				}
			);
		}

		if ( 'DESC' === $args['order'] ) {
			$results = array_reverse( $results, true );
		}
	}

	if ( $args['number'] > 0 ) {
		$results = array_slice( $results, 0, $args['number'] );
	}

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
		$hotdec  = Helpers::html2rgb( $args['hot'] );
		$colddec = Helpers::html2rgb( $args['cold'] );
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

	// Determine if aria-label is to be displayed.
	$aria_label = false;
	if ( $args['show_count'] || 0 !== $fontspread ) {
		$aria_label = true;
	}

	foreach ( $results as $key => $result ) {
		$count     = $result->count;
		$searchvar = esc_attr( $result->name );
		$url       = add_query_arg( array( 's' => rawurlencode( $searchvar ) ), home_url( '/' ) );
		$fraction  = $count - $min;
		$fontsize  = $args['smallest'] + $fontstep * $fraction;

		$color = '';

		for ( $i = 0; $i < 3; $i++ ) {
			$color .= dechex( absint( $coldval[ $i ] + ( $colorstep[ $i ] * $fraction ) ) );
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

		$class = 'bsearch_heatmap_link';

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

		$formatted_count = sprintf( translate_nooped_plural( $translate_nooped_plural, $count ), Helpers::number_format_i18n( $count ) );

		$title = '';

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

		$rel    = $args['link_nofollow'] ? 'rel="nofollow"' : '';
		$target = $args['link_new_window'] ? 'target="_blank"' : '';
		$title  = ! empty( $title ) ? sprintf( 'title="%s"', esc_attr( $title ) ) : '';

		$a[] = sprintf(
			'%1$s<a href="%2$s" %3$s style="%4$s" class="%5$s" %6$s %7$s %8$s>%9$s%10$s</a>%11$s',
			$args['before_term'],
			esc_url( $url ),
			$title,
			esc_attr( $style ),
			esc_attr( $class . ' bsearch_heatmap_link_position_' . ( $key + 1 ) ),
			$rel,
			$target,
			$aria_label ? sprintf( ' aria-label="%1$s (%2$s)"', esc_attr( $result->name ), esc_attr( $formatted_count ) ) : '',
			$searchvar,
			$args['show_count'] ? '<span class="bsearch_heatmap_link_count"> (' . Helpers::number_format_i18n( $count ) . ')</span>' : '',
			$args['after_term']
		);
	}

	switch ( $args['format'] ) {
		case 'array':
			$output =& $a;
			break;
		case 'list':
			$output  = "<ul class='bsearch_heatmap_list' role='list'>\n\t<li>";
			$output .= implode( "</li>\n\t<li>", $a );
			$output .= "</li>\n</ul>\n";
			break;
		default:
			$output = implode( $args['separator'], $a );
			break;
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
 * Get the Search Heatmap terms with their counts.
 *
 * @since 2.5.0
 *
 * @param  array|string $args Heatmap Parameters.
 * @return array              Array of heatmap terms.
 */
function get_bsearch_heatmap_counts( $args = array() ) {
	global $wpdb;

	$defaults = array(
		'daily'       => false,
		'number'      => absint( bsearch_get_option( 'heatmap_limit' ) ),
		'daily_range' => absint( bsearch_get_option( 'daily_range' ) ),
	);

	// Parse incomming $args into an array and merge it with $defaults.
	$args = wp_parse_args( $args, $defaults );

	$table_name = Helpers::get_bsearch_table( $args['daily'] );

	if ( ! $args['daily'] ) {
		$sargs = array(
			$args['number'],
		);

		$sql = "
			SELECT searchvar as name, cntaccess as count
			FROM {$table_name}
			WHERE searchvar <> ''
			ORDER BY count DESC, searchvar ASC
			LIMIT %d
		";
	} else {
		$current_date = Helpers::get_from_date( null, $args['daily_range'] );

		$sargs = array(
			$current_date,
			$args['number'],
		);

		$sql = "
			SELECT DISTINCT searchvar as name, SUM(cntaccess) as count
			FROM {$table_name}
			WHERE dp_date >= '%s'
			GROUP BY searchvar
			ORDER BY count DESC, searchvar ASC
			LIMIT %d
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
