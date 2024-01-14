<?php
/**
 * Template functions used by Better Search
 *
 * @package Better_Search
 */

use WebberZone\Better_Search\Frontend\Media_Handler;
use WebberZone\Better_Search\Util\Helpers;

// If this file is called directly, then abort execution.
if ( ! defined( 'WPINC' ) ) {
	die( "Aren't you supposed to come here via WP-Admin?" );
}

/**
 * Display the Better Search post excerpt.
 *
 * @since 3.0.0
 * @since 3.3.0 Removed $echo parameter. This function will always display the excerpt.
 *
 * @param string|array $args {
 *     Optional. Array or string of parameters.
 *
 *     @type string  $before      HTML output before the date.
 *     @type string  $after       HTML output after the date.
 *     @type WP_Post $post        Post ID or WP_Post object. Default current post.
 *     @type string  $format      PHP date format. Defaults to the 'date_format' option.
 *     @type bool    $use_excerpt Use the excerpt or create it from post content.
 * }
 */
function the_bsearch_excerpt( $args = array() ) {

	$defaults = array(
		'before'         => '',
		'after'          => '',
		'post'           => get_post(),
		'excerpt_length' => bsearch_get_option( 'excerpt_length' ),
		'use_excerpt'    => false,
		'relevant'       => true,
	);
	$args     = wp_parse_args( $args, $defaults );

	/**
	 * Filter the arguments used by get_bsearch_excerpt().
	 *
	 * @since 3.1.0
	 *
	 * @param array $args Arguments array.
	 */
	$args = apply_filters( 'the_bsearch_excerpt_args', $args );

	$excerpt = get_bsearch_excerpt( $args['post'], $args['excerpt_length'], $args['use_excerpt'], $args['relevant'] );

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

	echo $output; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}


/**
 * Function to create an excerpt for the post.
 *
 * @since   1.2
 *
 * @param   int|\WP_Post $post           Post ID or WP_Post instance.
 * @param   int          $excerpt_length Length of the excerpt in words.
 * @param   bool         $use_excerpt    Use post excerpt or content.
 * @param   bool         $relevant       Only relevant portion of excerpt.
 * @return  string      Excerpt
 */
function get_bsearch_excerpt( $post = null, $excerpt_length = 0, $use_excerpt = true, $relevant = true ) {
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
	$output = strip_shortcodes( $content );
	$output = excerpt_remove_blocks( $output );

	/** This filter is documented in wp-includes/post-template.php */
	$output = apply_filters( 'the_content', $output );
	$output = str_replace( ']]>', ']]&gt;', $output );
	$output = wp_strip_all_tags( $output );

	/**
	 * Filters the string in the "more" link displayed after a trimmed excerpt.
	 *
	 * @since 3.1.0
	 *
	 * @param string $more_string The string shown within the more link.
	 */
	$excerpt_more = apply_filters( 'bsearch_excerpt_more', ' [&hellip;]' );

	if ( $relevant ) {
		$search_query = get_bsearch_query();
		$search_query = str_replace( array( "'", '"', '&quot;', '\+', '\-' ), '', $search_query );
		$words        = preg_split( '/[\s,\+\.]+/', $search_query );

		$output = Helpers::extract_relevant_excerpt( $words, $output, $excerpt_more );
	}

	if ( $excerpt_length > 0 ) {
		$output = wp_trim_words( $output, $excerpt_length, $excerpt_more );
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
 * Echo the Better Search form.
 *
 * @since 3.0.0
 * @see get_bsearch_form()
 *
 * @param string $search_query Search query.
 * @param array  $args         Array or string of parameters.
 */
function the_bsearch_form( $search_query = '', $args = array() ) {
	echo get_bsearch_form( $search_query, $args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}


/**
 * Retrieve the Better Search form.
 *
 * @since 1.1
 * @since 3.0.0 Add $args
 * @since 3.3.0 Remove $echo parameter. This function will always return the form.
 *
 * @param string       $search_query Search query.
 * @param string|array $args {
 *     Optional. Array or string of parameters.
 *
 *     @type string   $before           Markup to prepend to the search form.
 *     @type string   $after            Markup to append to the search form.
 *     @type string   $aria_label       ARIA label for the search form.
 *                                      Useful to distinguish multiple search forms on the same page and improve accessibility.
 *     @type string[] $post_types       Comma separated list or array of post types.
 *     @type bool     $show_post_types  Whether to show the post types dropdown.
 * }
 * @return string The Better Search form.
 */
function get_bsearch_form( $search_query = '', $args = array() ) {

	if ( empty( $search_query ) ) {
		$search_query = get_bsearch_query();
	}
	$search_query = esc_attr( $search_query );

	$defaults = array(
		'before'              => '',
		'after'               => '',
		'echo'                => false,
		'aria_label'          => '',
		'post_types'          => bsearch_get_option( 'post_types' ),
		'selected_post_types' => '',
		'show_post_types'     => false,
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

	// Parse post_types.
	$post_types          = wp_parse_slug_list( $args['post_types'] );
	$selected_post_types = wp_parse_slug_list( $args['selected_post_types'] );

	$select = '';
	if ( ! empty( $post_types ) && $args['show_post_types'] ) {
		$select  = '<div class="bsearch-form-post-types">';
		$select .= '<span class="screen-reader-text">' . _x( 'Post types:', 'label', 'better-search' ) . '</span>';
		$select .= '<select name="post_types" id="post_types">';
		$select .= sprintf( '<option value="any">%1$s</option>', __( 'Any Post Type', 'better-search' ) );
		foreach ( $post_types as $post_type ) {
			$post_type = get_post_type_object( $post_type );
			$select   .= sprintf(
				'<option value="%1$s" %3$s>%2$s</option>',
				$post_type->name,
				$post_type->labels->singular_name,
				selected( true, in_array( $post_type->name, $selected_post_types, true ), false )
			);
		}
		$select .= '</select></div>';
	}

	$form = '
	<div class="bsearch-form-container">
		<form role="search" ' . $aria_label . 'method="get" class="bsearchform" action="' . esc_url( home_url( '/' ) ) . '">
			<div class="bsearch-form-search-field">
				<span class="screen-reader-text">' . _x( 'Search for:', 'label' ) . '</span>
				<input type="search" class="bsearch-field search-field" placeholder="' . esc_attr_x( 'Search &hellip;', 'placeholder' ) . '" value="' . $search_query . '" name="s" />
			</div>
			' . $select . '
			<input type="submit" class="bsearch-submit searchsubmit search-submit" value="' . esc_attr_x( 'Search', 'submit button' ) . '" />
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

	return $result;
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
 * @since 3.3.0 Removed $echo parameter. This function will always display the header.
 *
 * @see get_bsearch_header()
 *
 * @global WP_Query $wp_query WP_Query
 *
 * @param string|array $args Array or string of parameters.
 */
function the_bsearch_header( $args = array() ) {

	/**
	 * Filter the header table.
	 *
	 * @since 3.0.0
	 *
	 * @see get_bsearch_header()
	 *
	 * @param string $output Header table.
	 * @param array  $args   Array of arguments.
	 */
	echo apply_filters( 'the_bsearch_header', get_bsearch_header( $args ), $args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

/**
 * Retrieve the Better Search header.
 *
 * @since 3.3.0
 *
 * @global WP_Query $wp_query WP_Query
 *
 * @param string|array $args {
 *     Optional. Array or string of parameters.
 *
 *     @type string $before        Markup to prepend to the header table.
 *     @type string $after         Markup to append to the header table.
 *     @type int    $limit         Number of posts per page.
 *     @type int    $found_posts   Total number of posts found.
 *     @type int    $max_num_pages Maximum number of pages of results.
 *     @type int    $paged         Current page of results.
 *     @type string $search_query  Search query.
 *     @type bool   $bydate        Sory by date. If false, sort by relevance.
 * }
 * @return string The Better Search header.
 */
function get_bsearch_header( $args = array() ) {
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
		'post_types'    => isset( $_GET['post_types'] ) ? sanitize_title( wp_unslash( $_GET['post_types'] ) ) : '', // phpcs:ignore WordPress.Security.NonceVerification.Recommended
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

			if ( isset( $_GET['post_types'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$link_args['post_types'] = $args['post_types'];
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

		if ( isset( $_GET['post_types'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$link_args['post_types'] = $args['post_types'];
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

	$rel_or_date_link_args['s']          = $args['search_query'];
	$rel_or_date_link_args['post_types'] = $args['post_types'];
	$rel_or_date_link_args['bydate']     = $args['bydate'] ? 0 : 1;
	if ( isset( $_GET['limit'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$rel_or_date_link_args['limit'] = $args['limit'];
	}

	$rel_or_date_link = esc_url( add_query_arg( $rel_or_date_link_args, home_url() ) );

	if ( $args['bydate'] ) {
		$sorted_by = __( 'Date', 'better-search' );
		$sort_by   = __( 'Relevance', 'better-search' );
	} else {
		$sorted_by = __( 'Relevance', 'better-search' );
		$sort_by   = __( 'Date', 'better-search' );
	}

	$sorted_by = sprintf( '%1$s: %2$s', __( 'Sorted by', 'better-search' ), $sorted_by );
	$sort_by   = sprintf( '%2$s: <a href="%1$s">%3$s</a>', $rel_or_date_link, __( 'Sort by', 'better-search' ), $sort_by );

	$output .= $sorted_by . ' | ' . $sort_by;
	$output .= '
	  </td>
	  <td style="text-align:right">';
	/* translators: 1: Results per page. */
	$output .= sprintf( __( 'Results per-page: %s ', 'better-search' ), $rpp );
	$output .= '
	  </td>
	 </tr>
	</table>';

	/**
	 * Filter the Better Search header HTML.
	 *
	 * @since 3.3.0
	 *
	 * @param string $output Better Search header HTML.
	 * @param array  $args   Array of arguments.
	 */
	$output = apply_filters( 'get_bsearch_header', $output, $args );

	return $output;
}


/**
 * Display the relevance score for the post.
 *
 * @since 3.0.0
 * @since 3.3.0 Removed $echo parameter. This function will always display the score.
 *
 * @param string|array $args {
 *     Optional. Array or string of parameters.
 *
 *     @type int    $score    Score of the search result.
 *     @type int    $topscore Top score for which relevance is 100%.
 *     @type string $before   Markup to prepend to the relevance score.
 *     @type string $after    Markup to append to the relevance score.
 * }
 * @return void|string Void if 'echo' argument is true, the title attribute if 'echo' is false.
 */
function the_bsearch_score( $args = array() ) {

	/**
	 * Filter the relevance score text.
	 *
	 * @since 3.0.0
	 *
	 * @param string $output Relevance score text.
	 * @param array  $args   Array of arguments.
	 */
	echo apply_filters( 'the_bsearch_score', get_bsearch_score( $args ), $args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

/**
 * Retrieve the relevance score for the post in the search results.
 *
 * @since 3.3.0
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
 * @return string Better Search score.
 */
function get_bsearch_score( $args = array() ) {

	$defaults = array(
		'score'    => 0,
		'topscore' => 0,
		'before'   => __( 'Relevance:', 'better-search' ) . ' ',
		'after'    => '',
	);
	$args     = wp_parse_args( $args, $defaults );

	$score = Helpers::score2percent( $args['score'], $args['topscore'] );

	$output = $args['before'] . $score . $args['after'];

	/**
	 * Filter the relevance score text.
	 *
	 * @since 3.3.0
	 *
	 * @param string $output Relevance score text.
	 * @param array  $args   Array of arguments.
	 */
	$output = apply_filters( 'get_bsearch_score', $output, $args );

	return $output;
}


/**
 * Display the Better Search Post Thumbnail.
 *
 * @since 3.0.0
 * @since 3.3.0 Removed $echo parameter. This function will always display the thumbnail.
 *
 * @param string|int[] $size Optional. Image size. Accepts any registered image size name, or an array of
 *                           width and height values in pixels (in that order). Default 'post-thumbnail'.
 * @param string|array $args {
 *     Optional. Array or string of parameters.
 *
 *     @type string  $before Display before the thumbnail.
 *     @type string  $after  Display after the thumbnail.
 *     @type WP_Post $post   Post object.
 * }
 */
function the_bsearch_post_thumbnail( $size = 'thumbnail', $args = array() ) {

	$defaults = array(
		'before' => '',
		'after'  => '',
		'post'   => get_post(),
		'size'   => $size,
	);
	$args     = wp_parse_args( $args, $defaults );

	$thumb = Media_Handler::get_the_post_thumbnail( $args );

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

	echo $output; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}


/**
 * Display the Better Search Date.
 *
 * @since 3.0.0
 * @since 3.3.0 Removed $echo parameter. This function will always display the date.
 *
 * @param string|array $args {
 *     Optional. Array or string of parameters.
 *
 *     @type string  $before HTML output before the date.
 *     @type string  $after  HTML output after the date.
 *     @type WP_Post $post   Post ID or WP_Post object. Default current post.
 *     @type string  $format PHP date format. Defaults to the 'date_format' option.
 * }
 */
function the_bsearch_date( $args = array() ) {

	$defaults = array(
		'before' => '',
		'after'  => '',
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

	echo $output; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
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
 * Displays the permalink for the current post.
 *
 * @since 3.0.0
 *
 * @param int|WP_Post $post       Optional. Post ID or post object. Default is the global `$post`.
 * @param array       $query_args Optional. Additional query arguments to add to the permalink.
 */
function the_bsearch_permalink( $post = 0, $query_args = array() ) {

	$permalink = get_permalink( $post );

	if ( $permalink && ! empty( $query_args ) ) {
		$permalink = add_query_arg( $query_args, $permalink );
	}

	/**
	 * Filters the display of the permalink for the current post.
	 *
	 * @since 3.0.0
	 *
	 * @param string      $permalink  The permalink for the current post.
	 * @param int|WP_Post $post       WP_Post object.
	 * @param array       $query_args Additional query arguments to add to the permalink.
	 */
	echo esc_url( apply_filters( 'the_bsearch_permalink', $permalink, $post, $query_args ) );
}


/**
 * Retrieves a post’s terms as a list with specified format. Works with custom post types.
 *
 * @since 3.0.0
 * @since 3.3.0 Removed $echo parameter. This function will always display the terms.
 *
 * @see get_bsearch_term_list()
 *
 * @param int|\WP_Post $post Optional. Post ID or post object. Default is the global `$post`.
 * @param string|array $args Optional. Array or string of parameters.
 */
function the_bsearch_term_list( $post = 0, $args = array() ) {

	/**
	 * Filters the post terms for the current post.
	 *
	 * @since 3.0.0
	 *
	 * @param string       $output The post term list.
	 * @param int|\WP_Post $post   WP_Post object.
	 * @param array        $args   Array of arguments.
	 */
	echo apply_filters( 'the_bsearch_term_list', get_bsearch_term_list( $post, $args ), $post, $args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

/**
 * Retrieves a post’s terms as a list with specified format. Works with custom post types.
 *
 * @since 3.3.0
 *
 * @param int|\WP_Post $post Optional. Post ID or post object. Default is the global `$post`.
 * @param string|array $args {
 *     Optional. Array or string of parameters.
 *
 *     @type string          $before_terms String to use before the terms.
 *     @type string          $sep_terms    String to use between the terms.
 *     @type string          $after_terms  String to use after the terms.
 *     @type string          $before       String to use before the terms list.
 *     @type string          $sep          String to use between the terms list for each taxonomy.
 *     @type string          $after        String to use after the terms list.
 *     @type bool            $echo         Echo or return?
 *     @type string|string[] $taxonomy     The taxonomy slug or array of slugs for which to retrieve terms.
 * }
 * @return string The post terms list.
 */
function get_bsearch_term_list( $post = 0, $args = array() ) {

	$post = get_post( $post );
	if ( empty( $post ) ) {
		return '';
	}

	$defaults = array(
		'before_terms' => '',
		'sep_terms'    => ', ',
		'after_terms'  => '',
		'before'       => '',
		'sep'          => ' | ',
		'after'        => '',
		'taxonomy'     => get_object_taxonomies( $post->post_type ),
	);
	$args     = wp_parse_args( $args, $defaults );

	$output = array();

	foreach ( $args['taxonomy'] as $taxonomy ) {
		$output[] = get_the_term_list( $post->ID, $taxonomy, $args['before_terms'], $args['sep_terms'], $args['after_terms'] );
	}
	$output = array_filter( $output );

	$output = $args['before'] . implode( $args['sep'], $output ) . $args['after'];

	/**
	 * Filters the post terms for the current post.
	 *
	 * @since 3.3.0
	 *
	 * @param string      $output The post term list.
	 * @param int|WP_Post $post   WP_Post object.
	 * @param array       $args   Array of arguments.
	 */
	$output = apply_filters( 'get_bsearch_term_list', $output, $post, $args );

	return $output;
}

/**
 * Display the Post Type on the search results page.
 *
 * @since 3.0.0
 * @since 3.3.0 Removed $echo parameter. This function will always display the post type.
 *
 * @see get_bsearch_post_type()
 *
 * @param int|WP_Post  $post Optional. Post ID or post object. Default is the global `$post`.
 * @param string|array $args Optional. Array or string of parameters.
 */
function the_bsearch_post_type( $post = 0, $args = array() ) {

	/**
	 * Filters the post type for the current post.
	 *
	 * @since 3.0.0
	 *
	 * @param string      $output The post type.
	 * @param int|WP_Post $post   WP_Post object.
	 * @param array       $args   Array of arguments.
	 */
	echo apply_filters( 'the_bsearch_term_list', get_bsearch_post_type( $post, $args ), $post, $args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

/**
 * Display the Post Type label.
 *
 * @since 3.3.0
 *
 * @param int|\WP_Post $post Optional. Post ID or post object. Default is the global `$post`.
 * @param string|array $args {
 *     Optional. Array or string of parameters.
 *
 *     @type string  $before HTML output before the post type.
 *     @type string  $after  HTML output after the post type.
 * }
 * @return string The post type label.
 */
function get_bsearch_post_type( $post = 0, $args = array() ) {

	$post = get_post( $post );
	if ( empty( $post ) ) {
		return '';
	}

	$defaults = array(
		'before' => '',
		'after'  => '',
	);
	$args     = wp_parse_args( $args, $defaults );

	$obj    = get_post_type_object( $post->post_type );
	$output = $obj->labels->singular_name;
	$output = $args['before'] . $output . $args['after'];

	/**
	 * Filters the post type label for the current post.
	 *
	 * @since 3.3.0
	 *
	 * @param string      $output The post type.
	 * @param int|\WP_Post $post   WP_Post object.
	 * @param array       $args   Array of arguments.
	 */
	$output = apply_filters( 'get_bsearch_term_list', $output, $post, $args );

	return $output;
}
