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
		'general' => bsearch_settings_general(),
		'search'  => bsearch_settings_search(),
		'heatmap' => bsearch_settings_heatmap(),
		'styles'  => bsearch_settings_styles(),
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
 * Retrieve the array of General settings
 *
 * @since 2.5.0
 *
 * @return array General settings array
 */
function bsearch_settings_general() {

	$settings = array(
		'seamless'            => array(
			'id'      => 'seamless',
			'name'    => esc_html__( 'Enable seamless integration', 'better-search' ),
			'desc'    => esc_html__( "Complete integration with your theme. Enabling this option will ignore better-search-template.php. It will continue to display the search results sorted by relevance, although it won't display the percentage relevance.", 'better-search' ),
			'type'    => 'checkbox',
			'options' => true,
		),
		'track_popular'       => array(
			'id'      => 'track_popular',
			'name'    => esc_html__( 'Enable search tracking', 'better-search' ),
			'desc'    => esc_html__( 'If you turn this off, then the plugin will no longer track and display the popular search terms.', 'better-search' ),
			'type'    => 'checkbox',
			'options' => true,
		),
		'track_admins'        => array(
			'id'      => 'track_admins',
			'name'    => esc_html__( 'Track admin searches', 'better-search' ),
			'desc'    => esc_html__( 'Disabling this option will stop searches made by admins from being tracked.', 'better-search' ),
			'type'    => 'checkbox',
			'options' => true,
		),
		'track_editors'       => array(
			'id'      => 'track_editors',
			'name'    => esc_html__( 'Track editor user group searches', 'better-search' ),
			'desc'    => esc_html__( 'Disabling this option will stop searches made by editors from being tracked.', 'better-search' ),
			'type'    => 'checkbox',
			'options' => true,
		),
		'cache'               => array(
			'id'      => 'cache',
			'name'    => esc_html__( 'Enable cache', 'better-search' ),
			'desc'    => esc_html__( 'If activated, Better Search will use the Transients API to cache the search results for 1 hour.', 'better-search' ),
			'type'    => 'checkbox',
			'options' => false,
		),
		'cache_time'          => array(
			'id'      => 'cache_time',
			'name'    => esc_html__( 'Time to cache', 'top-10' ),
			'desc'    => esc_html__( 'Enter the number of seconds to cache the output.', 'top-10' ),
			'type'    => 'text',
			'options' => HOUR_IN_SECONDS,
		),
		'meta_noindex'        => array(
			'id'      => 'meta_noindex',
			'name'    => esc_html__( 'Stop search engines from indexing search results pages', 'better-search' ),
			'desc'    => esc_html__( 'This is a recommended option to turn ON. Adds noindex,follow meta tag to the head of the page', 'better-search' ),
			'type'    => 'checkbox',
			'options' => true,
		),
		'number_format_count' => array(
			'id'      => 'number_format_count',
			'name'    => esc_html__( 'Number format count', 'better-search' ),
			'desc'    => esc_html__( 'Activating this option will convert the search counts into a number format based on the locale', 'better-search' ),
			'type'    => 'checkbox',
			'options' => true,
		),
		'show_credit'         => array(
			'id'      => 'show_credit',
			'name'    => esc_html__( 'Link to Better Search plugin page', 'better-search' ),
			'desc'    => esc_html__( 'A nofollow link to the plugin is added as an extra list item to the list of popular searches. Not mandatory, but thanks if you do it!', 'better-search' ),
			'type'    => 'checkbox',
			'options' => false,
		),
	);

	/**
	 * Filters the General settings array
	 *
	 * @since 2.5.0
	 *
	 * @param array $settings General settings array
	 */
	return apply_filters( 'bsearch_settings_general', $settings );
}


/**
 * Retrieve the array of Search settings
 *
 * @since 2.5.0
 *
 * @return array Search settings array
 */
function bsearch_settings_search() {

	$settings = array(
		'limit'                    => array(
			'id'      => 'limit',
			'name'    => esc_html__( 'Number of Search Results per page', 'better-search' ),
			'desc'    => esc_html__( 'This is the maximum number of search results that will be displayed per page by default', 'better-search' ),
			'type'    => 'number',
			'options' => '10',
			'size'    => 'small',
		),
		'post_types'               => array(
			'id'      => 'post_types',
			'name'    => esc_html__( 'Post types to include', 'better-search' ),
			'desc'    => esc_html__( 'Select which post types you want to include in the search results', 'better-search' ),
			'type'    => 'posttypes',
			'options' => 'post,page',
		),
		'use_fulltext'             => array(
			'id'      => 'use_fulltext',
			'name'    => esc_html__( 'Enable mySQL FULLTEXT searching', 'better-search' ),
			'desc'    => esc_html__( 'Disabling this option will no longer give relevancy based results', 'better-search' ),
			'type'    => 'checkbox',
			'options' => true,
		),
		'boolean_mode'             => array(
			'id'      => 'boolean_mode',
			'name'    => esc_html__( 'Activate BOOLEAN mode', 'better-search' ),
			/* translators: 1: Opening anchor tag, 2: Closing anchor tag, */
			'desc'    => sprintf( esc_html__( 'Limits relevancy matches but removes several limitations of NATURAL LANGUAGE mode. %1$sCheck the mySQL docs for further information on BOOLEAN indices%2$s', 'better-search' ), '<a href="https://dev.mysql.com/doc/refman/8.0/en/fulltext-boolean.html" target="_blank">', '</a>' ),
			'type'    => 'checkbox',
			'options' => false,
		),
		'weight_title'             => array(
			'id'      => 'weight_title',
			'name'    => esc_html__( 'Weight of the title', 'better-search' ),
			'desc'    => esc_html__( 'Set this to a bigger number than the next option to prioritize the post title', 'better-search' ),
			'type'    => 'number',
			'options' => '10',
			'size'    => 'small',
		),
		'weight_content'           => array(
			'id'      => 'weight_content',
			'name'    => esc_html__( 'Weight of the post content', 'better-search' ),
			'desc'    => esc_html__( 'Set this to a bigger number than the previous option to prioritize the post content', 'better-search' ),
			'type'    => 'number',
			'options' => '1',
			'size'    => 'small',
		),
		'search_excerpt'           => array(
			'id'      => 'search_excerpt',
			'name'    => esc_html__( 'Search Excerpt', 'better-search' ),
			'desc'    => esc_html__( 'Select to search the post excerpt.', 'better-search' ),
			'type'    => 'checkbox',
			'options' => true,
		),
		'search_taxonomies'        => array(
			'id'      => 'search_taxonomies',
			'name'    => esc_html__( 'Search Taxonomies', 'better-search' ),
			'desc'    => esc_html__( 'Select to include posts where all taxonomies match the search term(s). This includes categories, tags and custom post types.', 'better-search' ),
			'type'    => 'checkbox',
			'options' => false,
		),
		'search_meta'              => array(
			'id'      => 'search_meta',
			'name'    => esc_html__( 'Search Meta', 'better-search' ),
			'desc'    => esc_html__( 'Select to include posts where meta values match the search term(s).', 'better-search' ),
			'type'    => 'checkbox',
			'options' => false,
		),
		'search_authors'           => array(
			'id'      => 'search_authors',
			'name'    => esc_html__( 'Search Authors', 'better-search' ),
			'desc'    => esc_html__( 'Select to include posts from authors that match the search term(s).', 'better-search' ),
			'type'    => 'checkbox',
			'options' => false,
		),
		'search_comments'          => array(
			'id'      => 'search_comments',
			'name'    => esc_html__( 'Search Comments', 'better-search' ),
			'desc'    => esc_html__( 'Select to include posts where comments include the search term(s).', 'better-search' ),
			'type'    => 'checkbox',
			'options' => false,
		),
		'exclude_protected_posts'  => array(
			'id'      => 'exclude_protected_posts',
			'name'    => esc_html__( 'Exclude password protected posts', 'better-search' ),
			'desc'    => esc_html__( 'Enabling this option will remove password protected posts from the search results', 'better-search' ),
			'type'    => 'checkbox',
			'options' => true,
		),
		'exclude_post_ids'         => array(
			'id'      => 'exclude_post_ids',
			'name'    => esc_html__( 'Exclude post IDs', 'better-search' ),
			'desc'    => esc_html__( 'Enter a comma separated list of post/page/custom post type IDs e.g. 188,1024,50', 'better-search' ),
			'type'    => 'numbercsv',
			'options' => '',
		),
		'exclude_cat_slugs'        => array(
			'id'               => 'exclude_cat_slugs',
			'name'             => esc_html__( 'Exclude Categories', 'better-search' ),
			'desc'             => esc_html__( 'Comma separated list of category slugs. The field above has an autocomplete so simply start typing in the starting letters and it will prompt you with options. Does not support custom taxonomies.', 'better-search' ),
			'type'             => 'csv',
			'options'          => '',
			'size'             => 'large',
			'field_class'      => 'category_autocomplete',
			'field_attributes' => array(
				'data-wp-taxonomy' => 'category',
			),
		),
		'exclude_categories'       => array(
			'id'       => 'exclude_categories',
			'name'     => esc_html__( 'Exclude category IDs', 'better-search' ),
			'desc'     => esc_html__( 'This is a readonly field that is automatically populated based on the above input when the settings are saved. These might differ from the IDs visible in the Categories page which use the term_id. Better Search uses the term_taxonomy_id which is unique to this taxonomy.', 'better-search' ),
			'type'     => 'text',
			'options'  => '',
			'readonly' => true,
		),
		'display_header'           => array(
			'id'   => 'display_header',
			'name' => '<h3>' . esc_html__( 'Display options', 'better-search' ) . '</h3>',
			'desc' => esc_html__( 'These settings allow you to customize the output of the search results page. Except for the highlight setting, these only apply when Seamless mode is off.', 'better-search' ),
			'type' => 'header',
		),
		'highlight'                => array(
			'id'      => 'highlight',
			'name'    => esc_html__( 'Highlight search terms', 'better-search' ),
			'desc'    => esc_html__( 'If enabled, the search terms are wrapped with the class <code>bsearch_highlight</code> on the search results page. The default stylesheet includes CSS to add some colour.', 'better-search' ),
			'type'    => 'checkbox',
			'options' => true,
		),
		'highlight_followed_links' => array(
			'id'      => 'highlight_followed_links',
			'name'    => esc_html__( 'Highlight followed links', 'better-search' ),
			'desc'    => esc_html__( 'If enabled, the plugin will highlight the search terms on posts/pages when visits them from the search results page.', 'better-search' ),
			'type'    => 'checkbox',
			'options' => true,
		),
		'include_thumb'            => array(
			'id'      => 'include_thumb',
			'name'    => esc_html__( 'Display thumbnail', 'better-search' ),
			'desc'    => '',
			'type'    => 'checkbox',
			'options' => true,
		),
		'display_relevance'        => array(
			'id'      => 'display_relevance',
			'name'    => esc_html__( 'Display relevance', 'better-search' ),
			'desc'    => '',
			'type'    => 'checkbox',
			'options' => true,
		),
		'display_post_type'        => array(
			'id'      => 'display_post_type',
			'name'    => esc_html__( 'Display post type', 'better-search' ),
			'desc'    => '',
			'type'    => 'checkbox',
			'options' => true,
		),
		'display_author'           => array(
			'id'      => 'display_author',
			'name'    => esc_html__( 'Display author', 'better-search' ),
			'desc'    => '',
			'type'    => 'checkbox',
			'options' => true,
		),
		'display_date'             => array(
			'id'      => 'display_date',
			'name'    => esc_html__( 'Display date', 'better-search' ),
			'desc'    => '',
			'type'    => 'checkbox',
			'options' => true,
		),
		'display_taxonomies'       => array(
			'id'      => 'display_taxonomies',
			'name'    => esc_html__( 'Display taxonomies', 'better-search' ),
			'desc'    => '',
			'type'    => 'checkbox',
			'options' => true,
		),
		'excerpt_length'           => array(
			'id'      => 'excerpt_length',
			'name'    => esc_html__( 'Length of excerpt (in words)', 'better-search' ),
			'desc'    => '',
			'type'    => 'number',
			'options' => '100',
			'size'    => 'small',
		),
		'banned_header'            => array(
			'id'   => 'banned_header',
			'name' => '<h3>' . esc_html__( 'Banned words options', 'better-search' ) . '</h3>',
			'desc' => '',
			'type' => 'header',
		),
		'badwords'                 => array(
			'id'      => 'badwords',
			'name'    => esc_html__( 'Filter these words', 'better-search' ),
			'desc'    => esc_html__( 'Words in this list will be stripped out of the search results. Enter these as a comma-separated list.', 'better-search' ),
			'type'    => 'textarea',
			'options' => implode( ',', bsearch_get_badwords() ),
		),
		'banned_whole_words'       => array(
			'id'      => 'banned_whole_words',
			'name'    => esc_html__( 'Filter whole words only', 'better-search' ),
			'desc'    => esc_html__( 'When activated, only whole words in the search query are filtered. Partial words are ignored. e.g. grow will not ban grown or grower.', 'better-search' ),
			'type'    => 'checkbox',
			'options' => false,
		),
		'banned_stop_search'       => array(
			'id'      => 'banned_stop_search',
			'name'    => esc_html__( 'Stop query on banned words filter', 'better-search' ),
			'desc'    => esc_html__( 'When activated, this option will return no results if the search query includes any of the words in the box above. If you have seamless mode off, Better Search will display an error message. With seamless mode on, this will give a Nothing found message. You can customize it by editing your theme.', 'better-search' ),
			'type'    => 'checkbox',
			'options' => false,
		),
	);

	/**
	 * Filters the Search settings array
	 *
	 * @since 2.5.0
	 *
	 * @param array $settings Search settings array
	 */
	return apply_filters( 'bsearch_settings_search', $settings );
}


/**
 * Retrieve the array of Heatmap settings
 *
 * @since 2.5.0
 *
 * @return array Heatmap settings array
 */
function bsearch_settings_heatmap() {

	$settings = array(
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
			'id'               => 'heatmap_cold',
			'name'             => esc_html__( 'Color of least popular search term', 'better-search' ),
			'desc'             => '',
			'type'             => 'color',
			'options'          => '#cccccc',
			'field_class'      => 'color-field',
			'field_attributes' => array(
				'data-default-color' => '#cccccc',
			),
		),
		'heatmap_hot'      => array(
			'id'               => 'heatmap_hot',
			'name'             => esc_html__( 'Color of most popular search term', 'better-search' ),
			'desc'             => '',
			'type'             => 'color',
			'options'          => '#000000',
			'field_class'      => 'color-field',
			'field_attributes' => array(
				'data-default-color' => '#000000',
			),
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
	);

	/**
	 * Filters the Heatmap settings array
	 *
	 * @since 2.5.0
	 *
	 * @param array $settings Heatmap settings array
	 */
	return apply_filters( 'bsearch_settings_heatmap', $settings );
}


/**
 * Retrieve the array of Styles settings
 *
 * @since 2.5.0
 *
 * @return array Styles settings array
 */
function bsearch_settings_styles() {

	$settings = array(
		'include_styles' => array(
			'id'      => 'include_styles',
			'name'    => esc_html__( 'Include inbuilt styles', 'better-search' ),
			'desc'    => esc_html__( 'Uncheck this to disable this plugin from adding the inbuilt styles. You will need to add your own CSS styles if you disable this option', 'better-search' ),
			'type'    => 'checkbox',
			'options' => true,
		),
		'custom_css'     => array(
			'id'          => 'custom_css',
			'name'        => esc_html__( 'Custom CSS', 'better-search' ),
			/* translators: 1: Opening a tag, 2: Closing a tag, 3: Opening code tage, 4. Closing code tag. */
			'desc'        => sprintf( esc_html__( 'Do not include %3$sstyle%4$s tags. Check out the %1$sFAQ%2$s for available CSS classes to style.', 'better-search' ), '<a href="' . esc_url( 'https://wordpress.org/plugins/better-search/faq/' ) . '" target="_blank">', '</a>', '<code>', '</code>' ),
			'type'        => 'css',
			'options'     => '',
			'field_class' => 'codemirror_css',
		),
	);

	/**
	 * Filters the Styles settings array
	 *
	 * @since 2.5.0
	 *
	 * @param array $settings Styles settings array
	 */
	return apply_filters( 'bsearch_settings_styles', $settings );
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
