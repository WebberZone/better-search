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
 * Display the Better Search post excerpt.
 *
 * @since 3.0.0
 *
 * @param string|array $args {
 *     Optional. Array or string of parameters.
 *
 *     @type string  $before      HTML output before the date.
 *     @type string  $after       HTML output after the date.
 *     @type bool    $echo        Echo or return?
 *     @type WP_Post $post        Post ID or WP_Post object. Default current post.
 *     @type string  $format      PHP date format. Defaults to the 'date_format' option.
 *     @type bool    $use_excerpt Use the excerpt or create it from post content.
 * }
 * @return void|string Void if 'echo' argument is true, the post excerpt if 'echo' is false.
 */
function the_bsearch_excerpt( $args = array() ) {

	$defaults = array(
		'before'         => '',
		'after'          => '',
		'echo'           => true,
		'post'           => get_post(),
		'excerpt_length' => 0,
		'use_excerpt'    => true,
	);
	$args     = wp_parse_args( $args, $defaults );

	$excerpt = get_bsearch_excerpt( $args['post'], $args['excerpt_length'], $args['use_excerpt'] );

	$output = $args['before'] . $excerpt . $args['after'];

	/**
	 * Filters the displayed post excerpt.
	 *
	 * @since 3.0.0
	 *
	 * @param string $output The post excerpt.
	 * @param array  $args   Arguments array.
	 */
	$output = apply_filters( 'the_bsearch_excerpt', $output, $args );

	if ( $args['echo'] ) {
		echo $output; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	} else {
		return $output;
	}
}


/**
 * Function to create an excerpt for the post.
 *
 * @since   1.2
 *
 * @param   int|WP_Post $post           Post ID or WP_Post instance.
 * @param   int         $excerpt_length Length of the excerpt in words.
 * @param   bool        $use_excerpt    Use post excerpt or content.
 * @return  string      Excerpt
 */
function get_bsearch_excerpt( $post = '', $excerpt_length = 0, $use_excerpt = true ) {
	$content = '';

	$post = get_post( $post );
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
	 * @since 1.2
	 * @since 3.0.0 Added $content parameter
	 *
	 * @param string  $output         Formatted excerpt
	 * @param WP_Post $post           WP_Post instance.
	 * @param int     $excerpt_length Length of the excerpt in words
	 * @param bool    $use_excerpt    Use post excerpt or content?
	 * @param string  $content        Content that is used to create the excerpt.
	 */
	return apply_filters( 'get_bsearch_excerpt', $output, $post, $excerpt_length, $use_excerpt, $content );
}


/**
 * Function to fetch search form.
 *
 * @since 1.1
 * @since 3.0.0 Add $args
 *
 * @param string       $search_query Search query.
 * @param string|array $args {
 *     Optional. Array or string of parameters.
 *
 *     @type string $before     Markup to prepend to the search form.
 *     @type string $after      Markup to append to the search form.
 *     @type bool   $echo       Echo or return?
 *     @type string $aria_label ARIA label for the search form.
 *                              Useful to distinguish multiple search forms on the same page and improve accessibility.
 * }
 * @return void|string Void if 'echo' argument is true, the search form if 'echo' is false.
 */
function get_bsearch_form( $search_query = '', $args = array() ) {

	if ( empty( $search_query ) ) {
		$search_query = get_bsearch_query();
	}
	$search_query = esc_attr( $search_query );

	$defaults = array(
		'before'     => '',
		'after'      => '',
		'echo'       => true,
		'aria_label' => '',
	);
	$args     = wp_parse_args( $args, $defaults );

	/**
	 * Filters the array of arguments used when generating the Better Search form.
	 *
	 * @since 3.0.0
	 *
	 * @param array $args The array of arguments for building the search form.
	 *                    See get_bsearch_form() for information on accepted arguments.
	 */
	$args = apply_filters( 'bsearch_form_args', $args );

	// Ensure that the filtered arguments contain all required default values.
	$args = array_merge( $defaults, $args );

	// Build a string containing an aria-label to use for the search form.
	if ( $args['aria_label'] ) {
		$aria_label = 'aria-label="' . esc_attr( $args['aria_label'] ) . '" ';
	} else {
		/*
		 * If there's no custom aria-label, we can set a default here. At the
		 * moment it's empty as there's uncertainty about what the default should be.
		 */
		$aria_label = '';
	}

	$form = '
	<div class="bsearch-form-container">
		<form role="search" ' . $aria_label . 'method="get" class="bsearchform" action="' . esc_url( home_url( '/' ) ) . '">
			<label>
				<span class="screen-reader-text">' . _x( 'Search for:', 'label', 'better-search' ) . '</span>
				<input type="search" class="bsearch-field search-field" placeholder="' . esc_attr_x( 'Search &hellip;', 'placeholder', 'better-search' ) . '" value="' . $search_query . '" name="s" />
			</label>
			<input type="submit" class="bsearch-submit searchsubmit search-submit" value="' . esc_attr_x( 'Search', 'submit button', 'better-search' ) . '" />
		</form>
	</div>
	';

	/**
	 * Filters the HTML output of the search form.
	 *
	 * @since 1.2
	 * @since 3.0.0 The `$args` parameter was added.
	 *
	 * @param string $form         The search form HTML output.
	 * @param string $search_query Search query
	 * @param array  $args         The array of arguments for building the search form.
	 *                             See get_bsearch_form() for information on accepted arguments.
	 */
	$result = apply_filters( 'get_bsearch_form', $form, $search_query, $args );

	if ( $args['echo'] ) {
		echo $result; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	} else {
		return $result;
	}
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


/**
 * Display the header table on the search results page.
 *
 * @since 3.0.0
 *
 * @global WP_Query $wp_query WP_Query
 *
 * @param string|array $args {
 *     Optional. Array or string of parameters.
 *
 *     @type string $before        Markup to prepend to the header table.
 *     @type string $after         Markup to append to the header table.
 *     @type bool   $echo          Echo or return?
 *     @type int    $limit         Number of posts per page.
 *     @type int    $found_posts   Total number of posts found.
 *     @type int    $max_num_pages Maximum number of pages of results.
 *     @type int    $paged         Current page of results.
 *     @type string $search_query  Search query.
 *     @type bool   $bydate        Sory by date. If false, sort by relevance.
 * }
 * @return void|string Void if 'echo' argument is true, the title attribute if 'echo' is false.
 */
function the_bsearch_header( $args = array() ) {
	/**
	 * WP_Query.
	 *
	 * @var WP_Query $wp_query WP_Query */
	global $wp_query;

	$defaults = array(
		'before'        => '',
		'after'         => '',
		'echo'          => true,
		'limit'         => isset( $_GET['limit'] ) ? absint( $_GET['limit'] ) : bsearch_get_option( 'limit' ), // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		'found_posts'   => absint( $wp_query->found_posts ),
		'max_num_pages' => absint( $wp_query->max_num_pages ),
		'paged'         => (int) get_query_var( 'paged', 1 ),
		'search_query'  => empty( $wp_query->search_query ) ? get_bsearch_query() : $wp_query->search_query,
		'bydate'        => isset( $_GET['bydate'] ) ? absint( $_GET['bydate'] ) : 0, // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	);
	$args     = wp_parse_args( $args, $defaults );

	$output = '';

	$current_page = ( $args['paged'] ) ? $args['paged'] : 1;
	$pages        = $args['max_num_pages'];
	$last         = $current_page * $args['limit'];
	$first        = $last - $args['limit'] + 1;
	$last         = min( $args['found_posts'], $last );
	$total_pages  = ( $pages < 1 ) ? 1 : $pages;

	$output .= '
	<table width="100%" border="0" class="bsearch_nav">
	 <tr class="bsearch_nav_row1">
	  <td width="50%" style="text-align:left">';

	/* translators: 1: First, 2: Last, 3: Number of rows */
	$output .= sprintf( __( 'Results <strong>%1$s</strong> - <strong>%2$s</strong> of <strong>%3$s</strong>', 'better-search' ), $first, $last, $args['found_posts'] );

	$output .= '
	  </td>
	  <td width="50%" style="text-align:right">';
	/* translators: 1: Current page number, 2: Total pages */
	$output .= sprintf( __( 'Page <strong>%1$s</strong> of <strong>%2$s</strong>', 'better-search' ), $current_page, $total_pages );

	/**
	 * Filters the number of limit links to display on the results page.
	 *
	 * @since 3.0.0
	 *
	 * @param int[] $steps Array of step sizes.
	 * @param array $args  Arguments array.
	 */
	$limit_steps = apply_filters( 'bsearch_header_limit_steps', array( 10, 20, 50 ), $args );

	$rpp = array();
	foreach ( $limit_steps as $limit_step ) {
		if ( $limit_step <= $args['found_posts'] ) {
			$link_args = array(
				's'     => $args['search_query'],
				'limit' => $limit_step,
			);
			if ( isset( $_GET['bydate'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$link_args['bydate'] = $args['bydate'];
			}

			$link  = esc_url( add_query_arg( $link_args, home_url() ) );
			$rpp[] = sprintf( '<a href="%1$s">%2$s</a>', $link, $limit_step );
		}
	}

	/**
	 * Show link for All results.
	 *
	 * @since 3.0.0
	 *
	 * @param bool $show_all Show all flag.
	 */
	$show_all = apply_filters( 'bsearch_header_show_all', true );
	if ( $show_all ) {
		$link_args = array(
			's'     => $args['search_query'],
			'limit' => $args['found_posts'],
		);
		if ( isset( $_GET['bydate'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$link_args['bydate'] = $args['bydate'];
		}

		$link  = esc_url( add_query_arg( $link_args, home_url() ) );
		$rpp[] = sprintf( '<a href="%1$s">%2$s</a>', $link, __( 'All', 'better-search' ) );
	}
	$rpp = implode( ' | ', $rpp );

	$output .= '
	  </td>
	 </tr>
	 <tr class="bsearch_nav_row2">
	  <td style="text-align:left">
	';

	$rel_or_date_link_args['s']      = $args['search_query'];
	$rel_or_date_link_args['bydate'] = $args['bydate'] ? 0 : 1;
	if ( isset( $_GET['limit'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$rel_or_date_link_args['limit'] = $args['limit'];
	}

	$rel_or_date_link = esc_url( add_query_arg( $rel_or_date_link_args, home_url() ) );
	$rel_or_date_text = $args['bydate'] ? __( 'Relevance', 'better-search' ) : __( 'Date', 'better-search' );
	$rel_or_date_link = sprintf( '<a href="%1$s">%2$s</a>', $rel_or_date_link, $rel_or_date_text );

	$output .= sprintf( __( 'Sort by: %1$s', 'better-search' ), $rel_or_date_link );
	$output .= '
	  </td>
	  <td style="text-align:right">';
	$output .= sprintf( __( 'Results per-page: %s ', 'better-search' ), $rpp );
	$output .= '
	  </td>
	 </tr>
	</table>';

	/**
	 * Filter the header table.
	 *
	 * @since 3.0.0
	 *
	 * @param string $output Header table.
	 * @param array  $args   Array of arguments.
	 */
	$output = apply_filters( 'the_bsearch_header', $output, $args );

	if ( $args['echo'] ) {
		echo $output; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	} else {
		return $output;
	}
}


/**
 * Display the relevance score for the post.
 *
 * @since 3.0.0
 *
 * @param string|array $args {
 *     Optional. Array or string of parameters.
 *
 *     @type int    $score    Score of the search result.
 *     @type int    $topscore Top score for which relevance is 100%.
 *     @type string $before   Markup to prepend to the relevance score.
 *     @type string $after    Markup to append to the relevance score.
 *     @type bool   $echo     Echo or return?
 * }
 * @return void|string Void if 'echo' argument is true, the title attribute if 'echo' is false.
 */
function the_bsearch_score( $args = array() ) {

	$defaults = array(
		'score'    => 0,
		'topscore' => 0,
		'before'   => __( 'Relevance:', 'better-search' ) . ' ',
		'after'    => '',
		'echo'     => true,
	);
	$args     = wp_parse_args( $args, $defaults );

	$score = bsearch_score2percent( $args['score'], $args['topscore'] );

	$output = $args['before'] . $score . $args['after'];

	/**
	 * Filter the relevance score text.
	 *
	 * @since 3.0.0
	 *
	 * @param string $output Relevance score text.
	 * @param array  $args   Array of arguments.
	 */
	$output = apply_filters( 'the_bsearch_score', $output, $args );

	if ( $args['echo'] ) {
		echo $output; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	} else {
		return $output;
	}
}


/**
 * Display the Better Search Post Thumbnail.
 *
 * When a theme adds 'post-thumbnail' support, a special 'post-thumbnail' image size
 * is registered, which differs from the 'thumbnail' image size managed via the
 * Settings > Media screen.
 *
 * When using the_post_thumbnail() or related functions, the 'post-thumbnail' image
 * size is used by default, though a different size can be specified instead as needed.
 *
 * @since 3.0.0
 *
 * @param string|int[] $size Optional. Image size. Accepts any registered image size name, or an array of
 *                           width and height values in pixels (in that order). Default 'post-thumbnail'.
 * @param string|array $args {
 *     Optional. Array or string of parameters.
 *
 *     @type string  $before Display before the thumbnail.
 *     @type string  $after  Display after the thumbnail.
 *     @type bool    $echo   Echo or return?
 *     @type WP_Post $post   Post object.
 * }
 * @return void|string Void if 'echo' argument is true, the thumbnail HTML if 'echo' is false.
 */
function the_bsearch_post_thumbnail( $size = 'post-thumbnail', $args = array() ) {

	$defaults = array(
		'before' => '',
		'after'  => '',
		'echo'   => true,
		'post'   => get_post(),
	);
	$args     = wp_parse_args( $args, $defaults );

	$thumb = bsearch_get_the_post_thumbnail( $args );

	$output = $args['before'] . $thumb . $args['after'];

	/**
	 * Filter the thumbnail.
	 *
	 * @since 3.0.0
	 *
	 * @param string       $output Thumbnail HTML.
	 * @param string|int[] $size   Image size. Accepts any registered image size name, or an array of
	 *                             width and height values in pixels (in that order). Default 'post-thumbnail'.
	 * @param array        $args   Array of arguments.
	 */
	$output = apply_filters( 'the_bsearch_post_thumbnail', $output, $size, $args );

	if ( $args['echo'] ) {
		echo $output; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	} else {
		return $output;
	}
}


/**
 * Display the Better Search Date.
 *
 * @since 3.0.0
 *
 * @param string|array $args {
 *     Optional. Array or string of parameters.
 *
 *     @type string  $before HTML output before the date.
 *     @type string  $after  HTML output after the date.
 *     @type bool    $echo   Echo or return?
 *     @type WP_Post $post   Post ID or WP_Post object. Default current post.
 *     @type string  $format PHP date format. Defaults to the 'date_format' option.
 * }
 * @return void|string Void if 'echo' argument is true, the post date if 'echo' is false.
 */
function the_bsearch_date( $args = array() ) {

	$defaults = array(
		'before' => '',
		'after'  => '',
		'echo'   => true,
		'post'   => get_post(),
		'format' => get_option( 'date_format' ),
	);
	$args     = wp_parse_args( $args, $defaults );

	$output = get_bsearch_date( $args['post'], $args['before'], $args['after'], $args['format'] );

	/**
	 * Filter the date.
	 *
	 * @since 3.0.0
	 *
	 * @param string $output The formatted date string.
	 * @param array  $args   Array of arguments.
	 */
	$output = apply_filters( 'the_bsearch_date', $output, $args );

	if ( $args['echo'] ) {
		echo $output; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	} else {
		return $output;
	}
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
