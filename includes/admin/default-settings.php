<?php
/**
 * Default settings.
 *
 * @link  https://webberzone.com
 * @since 2.2.0
 *
 * @package Better Search
 * @subpackage Admin/Default_Settings
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Retrieve the array of plugin settings
 *
 * @since 2.2.0
 *
 * @return array Settings array
 */
function bsearch_get_registered_settings() {

	$bsearch_settings = array(
		/*** General settings */
		'general' => apply_filters(
			'bsearch_settings_general',
			array(
				'seamless'      => array(
					'id'      => 'seamless',
					'name'    => esc_html__( 'Enable seamless integration', 'better-search' ),
					'desc'    => esc_html__( "Complete integration with your theme. Enabling this option will ignore better-search-template.php. It will continue to display the search results sorted by relevance, although it won't display the percentage relevance.", 'better-search' ),
					'type'    => 'checkbox',
					'options' => true,
				),
				'track_popular' => array(
					'id'      => 'track_popular',
					'name'    => esc_html__( 'Enable search tracking', 'better-search' ),
					'desc'    => esc_html__( 'If you turn this off, then the plugin will no longer track and display the popular search terms.', 'better-search' ),
					'type'    => 'checkbox',
					'options' => true,
				),
				'track_admins'  => array(
					'id'      => 'track_admins',
					'name'    => esc_html__( 'Track admin searches', 'better-search' ),
					'desc'    => esc_html__( 'Disabling this option will stop searches made by admins from being tracked.', 'better-search' ),
					'type'    => 'checkbox',
					'options' => true,
				),
				'track_editors' => array(
					'id'      => 'track_editors',
					'name'    => esc_html__( 'Track editor user group searches', 'better-search' ),
					'desc'    => esc_html__( 'Disabling this option will stop searches made by editors from being tracked.', 'better-search' ),
					'type'    => 'checkbox',
					'options' => true,
				),
				'cache'         => array(
					'id'      => 'cache',
					'name'    => esc_html__( 'Enable cache', 'better-search' ),
					'desc'    => esc_html__( 'If activated, Better Search will use the Transients API to cache the search results for 1 hour.', 'better-search' ),
					'type'    => 'checkbox',
					'options' => true,
				),
				'meta_noindex'  => array(
					'id'      => 'meta_noindex',
					'name'    => esc_html__( 'Stop search engines from indexing search results pages', 'better-search' ),
					'desc'    => esc_html__( 'This is a recommended option to turn ON. Adds noindex,follow meta tag to the head of the page', 'better-search' ),
					'type'    => 'checkbox',
					'options' => true,
				),
				'show_credit'   => array(
					'id'      => 'show_credit',
					'name'    => esc_html__( 'Link to Better Search plugin page', 'better-search' ),
					'desc'    => esc_html__( 'A nofollow link to the plugin is added as an extra list item to the list of popular searches. Not mandatory, but thanks if you do it!', 'better-search' ),
					'type'    => 'checkbox',
					'options' => false,
				),
			)
		),
		/*** Search settings */
		'search'  => apply_filters(
			'bsearch_settings_search',
			array(
				'limit'                   => array(
					'id'      => 'limit',
					'name'    => esc_html__( 'Number of Search Results per page', 'better-search' ),
					'desc'    => esc_html__( 'This is the maximum number of search results that will be displayed per page by default', 'better-search' ),
					'type'    => 'number',
					'options' => '10',
					'size'    => 'small',
				),
				'post_types'              => array(
					'id'      => 'post_types',
					'name'    => esc_html__( 'Post types to include', 'better-search' ),
					'desc'    => esc_html__( 'Select which post types you want to include in the search results', 'better-search' ),
					'type'    => 'posttypes',
					'options' => 'post,page',
				),
				'exclude_protected_posts' => array(
					'id'      => 'exclude_protected_posts',
					'name'    => esc_html__( 'Exclude password protected posts', 'better-search' ),
					'desc'    => esc_html__( 'Enabling this option will remove password protected posts from the search results', 'better-search' ),
					'type'    => 'checkbox',
					'options' => false,
				),
				'exclude_post_ids'        => array(
					'id'      => 'exclude_post_ids',
					'name'    => esc_html__( 'Exclude post IDs', 'better-search' ),
					'desc'    => esc_html__( 'Enter a comma separated list of post/page/custom post type IDs e.g. 188,1024,50', 'better-search' ),
					'type'    => 'numbercsv',
					'options' => '',
				),
				'use_fulltext'            => array(
					'id'      => 'use_fulltext',
					'name'    => esc_html__( 'Enable mySQL FULLTEXT searching', 'better-search' ),
					'desc'    => esc_html__( 'Disabling this option will no longer give relevancy based results', 'better-search' ),
					'type'    => 'checkbox',
					'options' => true,
				),
				'boolean_mode'            => array(
					'id'      => 'boolean_mode',
					'name'    => esc_html__( 'Activate BOOLEAN mode', 'better-search' ),
					/* translators: 1: Opening anchor tag, 2: Closing anchor tag, */
					'desc'    => sprintf( esc_html__( 'Limits relevancy matches but removes several limitations of NATURAL LANGUAGE mode. %1$sCheck the mySQL docs for further information on BOOLEAN indices%2$s', 'better-search' ), '<a href="https://dev.mysql.com/doc/refman/5.0/en/fulltext-boolean.html" target="_blank">', '</a>' ),
					'type'    => 'checkbox',
					'options' => false,
				),
				'weight_title'            => array(
					'id'      => 'weight_title',
					'name'    => esc_html__( 'Weight of the title', 'better-search' ),
					'desc'    => esc_html__( 'Set this to a bigger number than the next option to prioritise the post title', 'better-search' ),
					'type'    => 'number',
					'options' => '10',
					'size'    => 'small',
				),
				'weight_content'          => array(
					'id'      => 'weight_content',
					'name'    => esc_html__( 'Weight of the post content', 'better-search' ),
					'desc'    => esc_html__( 'Set this to a bigger number than the previous option to prioritise the post content', 'better-search' ),
					'type'    => 'number',
					'options' => '1',
					'size'    => 'small',
				),
				'aggressive_search'       => array(
					'id'      => 'aggressive_search',
					'name'    => esc_html__( 'Aggressive search', 'better-search' ),
					'desc'    => esc_html__( 'Enable this to search using BOOLEAN mode ON and/or using LIKE in case no results are found for the search term. This only applies if Seamless mode is disabled.', 'better-search' ),
					'type'    => 'checkbox',
					'options' => false,
				),
				'highlight'               => array(
					'id'      => 'highlight',
					'name'    => esc_html__( 'Highlight search terms', 'better-search' ),
					'desc'    => esc_html__( 'If enabled, the search terms are wrapped with the class <code>bsearch_highlight</code>. You will also need to add this CSS code under custom styles box below.', 'better-search' ),
					'type'    => 'checkbox',
					'options' => false,
				),
				'include_thumb'           => array(
					'id'      => 'include_thumb',
					'name'    => esc_html__( 'Include thumbnails in search results', 'better-search' ),
					'desc'    => esc_html__( 'Displays the featured image (post thumbnail) whenever available. This setting does not apply when Seamless mode is activated.', 'better-search' ),
					'type'    => 'checkbox',
					'options' => false,
				),
				'excerpt_length'          => array(
					'id'      => 'excerpt_length',
					'name'    => esc_html__( 'Length of excerpt (in words)', 'better-search' ),
					'desc'    => esc_html__( 'This setting does not apply when Seamless mode is activated.', 'better-search' ),
					'type'    => 'number',
					'options' => '100',
					'size'    => 'small',
				),
				'badwords'                => array(
					'id'      => 'badwords',
					'name'    => esc_html__( 'Filter these words', 'better-search' ),
					'desc'    => esc_html__( 'Words in this list will be stripped out of the search results. Enter these as a comma-separated list.', 'better-search' ),
					'type'    => 'textarea',
					'options' => implode( ',', bsearch_get_badwords() ),
				),
			)
		),
		/*** Heatmap settings */
		'heatmap' => apply_filters(
			'bsearch_settings_heatmap',
			array(
				'include_heatmap'  => array(
					'id'      => 'include_heatmap',
					'name'    => esc_html__( 'Include heatmap on the search results', 'better-search' ),
					'desc'    => esc_html__( 'This option will display the heatmaps at the bottom of the search results page. Display popular searches to your visitors. Does not apply when Seamless mode is enabled.', 'better-search' ),
					'type'    => 'checkbox',
					'options' => false,
				),
				'title'            => array(
					'id'      => 'title',
					'name'    => esc_html__( 'Heading of Overall Popular Searches', 'better-search' ),
					'desc'    => esc_html__( 'Displayed before the list of the searches as a the master heading', 'better-search' ),
					'type'    => 'text',
					'options' => '<h3>' . esc_html__( 'Popular searches:', 'better-search' ) . '</h3>',
					'size'    => 'large',
				),
				'title_daily'      => array(
					'id'      => 'title_daily',
					'name'    => esc_html__( 'Heading of Daily Popular Searches', 'better-search' ),
					'desc'    => esc_html__( 'Displayed before the list of the searches as a the master heading', 'better-search' ),
					'type'    => 'text',
					'options' => '<h3>' . esc_html__( 'Currently trending searches:', 'better-search' ) . '</h3>',
					'size'    => 'large',
				),
				'daily_range'      => array(
					'id'      => 'daily_range',
					'name'    => esc_html__( 'Currently trending should contain searches of how many days?', 'better-search' ),
					'desc'    => esc_html__( 'This settings allows you to change the number of days for the currently trending heatmap. This used to be called Daily popular in previous versions.', 'better-search' ),
					'type'    => 'number',
					'options' => '7',
					'size'    => 'small',
				),
				'heatmap_limit'    => array(
					'id'      => 'heatmap_limit',
					'name'    => esc_html__( 'Number of search terms to display', 'better-search' ),
					'desc'    => '',
					'type'    => 'number',
					'options' => '20',
					'size'    => 'small',
				),
				'heatmap_smallest' => array(
					'id'      => 'heatmap_smallest',
					'name'    => esc_html__( 'Font size of least popular search term', 'better-search' ),
					'desc'    => '',
					'type'    => 'number',
					'options' => '10',
					'size'    => 'small',
				),
				'heatmap_largest'  => array(
					'id'      => 'heatmap_largest',
					'name'    => esc_html__( 'Font size of most popular search term', 'better-search' ),
					'desc'    => '',
					'type'    => 'number',
					'options' => '20',
					'size'    => 'small',
				),
				'heatmap_cold'     => array(
					'id'          => 'heatmap_cold',
					'name'        => esc_html__( 'Color of least popular search term', 'better-search' ),
					'desc'        => '',
					'type'        => 'text',
					'options'     => 'CCCCCC',
					'field_class' => 'jscolor',
				),
				'heatmap_hot'      => array(
					'id'          => 'heatmap_hot',
					'name'        => esc_html__( 'Color of most popular search term', 'better-search' ),
					'desc'        => '',
					'type'        => 'text',
					'options'     => '000000',
					'field_class' => 'jscolor',
				),
				'heatmap_before'   => array(
					'id'      => 'heatmap_before',
					'name'    => esc_html__( 'Text to include before each search term', 'better-search' ),
					'desc'    => '',
					'type'    => 'text',
					'options' => '',
				),
				'heatmap_after'    => array(
					'id'      => 'heatmap_after',
					'name'    => esc_html__( 'Text to include after each search term', 'better-search' ),
					'desc'    => '',
					'type'    => 'text',
					'options' => '&nbsp;',
				),
				'link_new_window'  => array(
					'id'      => 'link_new_window',
					'name'    => esc_html__( 'Open links in new window', 'better-search' ),
					'desc'    => '',
					'type'    => 'checkbox',
					'options' => false,
				),
				'link_nofollow'    => array(
					'id'      => 'link_nofollow',
					'name'    => esc_html__( 'Add nofollow to links', 'better-search' ),
					'desc'    => '',
					'type'    => 'checkbox',
					'options' => true,
				),
			)
		),
		/*** Styles settings */
		'styles'  => apply_filters(
			'bsearch_settings_styles',
			array(
				'custom_css' => array(
					'id'      => 'custom_css',
					'name'    => esc_html__( 'Custom CSS', 'better-search' ),
					/* translators: 1: Opening a tag, 2: Closing a tag, 3: Opening code tage, 4. Closing code tag. */
					'desc'    => sprintf( esc_html__( 'Do not include %3$sstyle%4$s tags. Check out the %1$sFAQ%2$s for available CSS classes to style.', 'better-search' ), '<a href="' . esc_url( 'http://wordpress.org/plugins/better-search/faq/' ) . '" target="_blank">', '</a>', '<code>', '</code>' ),
					'type'    => 'css',
					'options' => bsearch_get_custom_css(),
				),
			)
		),
	);

	/**
	 * Filters the settings array
	 *
	 * @since 2.2.0
	 *
	 * @param array   $bsearch_setings Settings array
	 */
	return apply_filters( 'bsearch_registered_settings', $bsearch_settings );

}

/**
 * Get badwords to filter.
 *
 * @since 2.2.0
 *
 * @return array Array containing bad words to filter
 */
function bsearch_get_badwords() {

	$badwords = array(
		'anal',
		'anus',
		'bastard',
		'beastiality',
		'bestiality',
		'bewb',
		'bitch',
		'blow',
		'blumpkin',
		'boob',
		'cawk',
		'cock',
		'choad',
		'cooter',
		'cornhole',
		'cum',
		'cunt',
		'dick',
		'dildo',
		'dong',
		'dyke',
		'douche',
		'fag',
		'faggot',
		'fart',
		'foreskin',
		'fuck',
		'fuk',
		'gangbang',
		'gook',
		'handjob',
		'homo',
		'honkey',
		'humping',
		'jiz',
		'jizz',
		'kike',
		'kunt',
		'labia',
		'muff',
		'nigger',
		'nutsack',
		'pen1s',
		'penis',
		'piss',
		'poon',
		'poop',
		'porn',
		'punani',
		'pussy',
		'queef',
		'queer',
		'quim',
		'rimjob',
		'rape',
		'rectal',
		'rectum',
		'semen',
		'shit',
		'slut',
		'spick',
		'spoo',
		'spooge',
		'taint',
		'titty',
		'titties',
		'twat',
		'vagina',
		'vulva',
		'wank',
		'whore',
	);

	/**
	 * Filters bad words array.
	 *
	 * @since 2.2.0
	 *
	 * @param array Array containing bad words to filter
	 */
	return apply_filters( 'bsearch_get_badwords', $badwords );
}

/**
 * Get custom CSS.
 *
 * @since 2.2.0
 *
 * @return string Custom CSS
 */
function bsearch_get_custom_css() {

	$custom_css = '
#bsearchform { margin: 20px; padding: 20px; }
#heatmap { margin: 20px; padding: 20px; border: 1px dashed #ccc }
.bsearch_results_page { max-width:90%; margin: 20px; padding: 20px; }
.bsearch_footer { text-align: center; }
.bsearch_highlight { background:#ffc; }
	';

	/**
	 * Filters custom CSS.
	 *
	 * @since 2.2.0
	 *
	 * @param string Custom CSS
	 */
	return apply_filters( 'bsearch_get_badwords', $custom_css );
}
