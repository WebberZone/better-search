<?php
/**
 * Register Settings.
 *
 * @link  https://webberzone.com
 * @since 3.3.0
 *
 * @package WebberZone\Better_Search\Admin
 */

namespace WebberZone\Better_Search\Admin\Settings;

use WebberZone\Better_Search\Util\Helpers;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Class to register the settings.
 *
 * @since   3.3.0
 */
class Settings {


	/**
	 * Admin Dashboard.
	 *
	 * @since 3.3.0
	 *
	 * @var object Admin Dashboard.
	 */
	public $admin_dashboard;

	/**
	 * Settings API.
	 *
	 * @since 3.3.0
	 *
	 * @var object Settings API.
	 */
	public $settings_api;

	/**
	 * Statistics table.
	 *
	 * @since 3.3.0
	 *
	 * @var object Statistics table.
	 */
	public $statistics;

	/**
	 * Activator class.
	 *
	 * @since 3.3.0
	 *
	 * @var object Activator class.
	 */
	public $activator;

	/**
	 * Admin Columns.
	 *
	 * @since 3.3.0
	 *
	 * @var object Admin Columns.
	 */
	public $admin_columns;

	/**
	 * Metabox functions.
	 *
	 * @since 3.3.0
	 *
	 * @var object Metabox functions.
	 */
	public $metabox;

	/**
	 * Import Export functions.
	 *
	 * @since 3.3.0
	 *
	 * @var object Import Export functions.
	 */
	public $import_export;

	/**
	 * Tools page.
	 *
	 * @since 3.3.0
	 *
	 * @var object Tools page.
	 */
	public $tools_page;

	/**
	 * Settings Page in Admin area.
	 *
	 * @since 3.3.0
	 *
	 * @var string Settings Page.
	 */
	public $settings_page;

	/**
	 * Prefix which is used for creating the unique filters and actions.
	 *
	 * @since 3.3.0
	 *
	 * @var string Prefix.
	 */
	public static $prefix;

	/**
	 * Settings Key.
	 *
	 * @since 3.3.0
	 *
	 * @var string Settings Key.
	 */
	public $settings_key;

	/**
	 * The slug name to refer to this menu by (should be unique for this menu).
	 *
	 * @since 3.3.0
	 *
	 * @var string Menu slug.
	 */
	public $menu_slug;

	/**
	 * Main constructor class.
	 *
	 * @since 3.3.0
	 */
	public function __construct() {
		$this->settings_key = 'bsearch_settings';
		self::$prefix       = 'bsearch';
		$this->menu_slug    = 'bsearch_options_page';

		add_action( 'admin_menu', array( $this, 'initialise_settings' ) );
		add_filter( 'plugin_row_meta', array( $this, 'plugin_row_meta' ), 11, 2 );
		add_filter( 'plugin_action_links_' . plugin_basename( BETTER_SEARCH_PLUGIN_FILE ), array( $this, 'plugin_actions_links' ) );
		add_filter( 'bsearch_settings_sanitize', array( $this, 'change_settings_on_save' ), 99 );
	}

	/**
	 * Initialise the settings API.
	 *
	 * @since 3.3.0
	 */
	public function initialise_settings() {
		$props = array(
			'default_tab'       => 'general',
			'help_sidebar'      => $this->get_help_sidebar(),
			'help_tabs'         => $this->get_help_tabs(),
			'admin_footer_text' => $this->get_admin_footer_text(),
			'menus'             => $this->get_menus(),
		);

		$args = array(
			'props'               => $props,
			'translation_strings' => $this->get_translation_strings(),
			'settings_sections'   => $this->get_settings_sections(),
			'registered_settings' => $this->get_registered_settings(),
			'upgraded_settings'   => array(),
		);

		$this->settings_api = new Settings_API( $this->settings_key, self::$prefix, $args );
	}

	/**
	 * Array containing the translation strings.
	 *
	 * @since 1.8.0
	 *
	 * @return array Translation strings.
	 */
	public function get_translation_strings() {
		$strings = array(
			'page_header'          => esc_html__( 'Better Search Settings', 'better-search' ),
			'reset_message'        => esc_html__( 'Settings have been reset to their default values. Reload this page to view the updated settings.', 'better-search' ),
			'success_message'      => esc_html__( 'Settings updated.', 'better-search' ),
			'save_changes'         => esc_html__( 'Save Changes', 'better-search' ),
			'reset_settings'       => esc_html__( 'Reset all settings', 'better-search' ),
			'reset_button_confirm' => esc_html__( 'Do you really want to reset all these settings to their default values?', 'better-search' ),
			'checkbox_modified'    => esc_html__( 'Modified from default setting', 'better-search' ),
		);

		/**
		 * Filter the array containing the settings' sections.
		 *
		 * @since 1.8.0
		 *
		 * @param array $strings Translation strings.
		 */
		return apply_filters( self::$prefix . '_translation_strings', $strings );
	}

	/**
	 * Get the admin menus.
	 *
	 * @return array Admin menus.
	 */
	public function get_menus() {
		$menus = array();

		// Settings menu.
		$menus[] = array(
			'settings_page' => true,
			'type'          => 'submenu',
			'parent_slug'   => 'bsearch_dashboard',
			'page_title'    => esc_html__( 'Better Search Settings', 'better-search' ),
			'menu_title'    => esc_html__( 'Settings', 'better-search' ),
			'menu_slug'     => $this->menu_slug,
		);

		return $menus;
	}

	/**
	 * Array containing the settings' sections.
	 *
	 * @since 3.3.0
	 *
	 * @return array Settings array
	 */
	public static function get_settings_sections() {
		$settings_sections = array(
			'general' => __( 'General', 'better-search' ),
			'search'  => __( 'Search', 'better-search' ),
			'heatmap' => __( 'Heatmap', 'better-search' ),
			'styles'  => __( 'Styles', 'better-search' ),
		);

		/**
		 * Filter the array containing the settings' sections.
		 *
		 * @since 3.3.0
		 *
		 * @param array $settings_sections Settings array
		 */
		return apply_filters( self::$prefix . '_settings_sections', $settings_sections );
	}


	/**
	 * Retrieve the array of plugin settings
	 *
	 * @since 3.3.0
	 *
	 * @return array Settings array
	 */
	public static function get_registered_settings() {
		$settings = array();
		$sections = self::get_settings_sections();

		foreach ( $sections as $section => $value ) {
			$method_name = 'settings_' . $section;
			if ( method_exists( __CLASS__, $method_name ) ) {
				$settings[ $section ] = self::$method_name();
			}
		}

		/**
		 * Filters the settings array
		 *
		 * @since 1.2.0
		 *
		 * @param array $bsearch_setings Settings array
		 */
		return apply_filters( self::$prefix . '_registered_settings', $settings );
	}

	/**
	 * Retrieve the array of General settings
	 *
	 * @since 3.3.0
	 *
	 * @return array General settings array
	 */
	public static function settings_general() {
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
		return apply_filters( self::$prefix . '_settings_general', $settings );
	}


	/**
	 * Retrieve the array of Search settings
	 *
	 * @since 3.3.0
	 *
	 * @return array Search settings array
	 */
	public static function settings_search() {
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
				'options' => implode( ',', self::get_badwords() ),
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
		 * Filters the Counter settings array
		 *
		 * @since 2.5.0
		 *
		 * @param array $settings Counter settings array
		 */
		return apply_filters( self::$prefix . '_settings_counter', $settings );
	}


	/**
	 * Retrieve the array of Heatmap settings
	 *
	 * @since 3.3.0
	 *
	 * @return array Heatmap settings array
	 */
	public static function settings_heatmap() {
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
		 * Filters the List settings array
		 *
		 * @since 2.5.0
		 *
		 * @param array $settings List settings array
		 */
		return apply_filters( self::$prefix . '_settings_list', $settings );
	}


	/**
	 * Retrieve the array of Styles settings
	 *
	 * @since 3.3.0
	 *
	 * @return array Styles settings array
	 */
	public static function settings_styles() {
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
		return apply_filters( self::$prefix . '_settings_styles', $settings );
	}


	/**
	 * Get badwords to filter.
	 *
	 * @since 2.2.0
	 *
	 * @return array Array containing bad words to filter
	 */
	public static function get_badwords() {

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
		 * @param array $badwords Array containing bad words to filter.
		 */
		return apply_filters( self::$prefix . '_get_badwords', $badwords );
	}


	/**
	 * Adding WordPress plugin action links.
	 *
	 * @since 3.3.0
	 *
	 * @param array $links Array of links.
	 * @return array
	 */
	public function plugin_actions_links( $links ) {

		return array_merge(
			array(
				'settings' => '<a href="' . admin_url( 'admin.php?page=' . $this->menu_slug ) . '">' . esc_html__( 'Settings', 'better-search' ) . '</a>',
			),
			$links
		);
	}

	/**
	 * Add meta links on Plugins page.
	 *
	 * @since 3.3.0
	 *
	 * @param array  $links Array of Links.
	 * @param string $file Current file.
	 * @return array
	 */
	public function plugin_row_meta( $links, $file ) {

		if ( false !== strpos( $file, 'better-search.php' ) ) {
			$new_links = array(
				'support'    => '<a href = "https://wordpress.org/support/plugin/better-search">' . esc_html__( 'Support', 'better-search' ) . '</a>',
				'donate'     => '<a href = "https://ajaydsouza.com/donate/">' . esc_html__( 'Donate', 'better-search' ) . '</a>',
				'contribute' => '<a href = "https://github.com/WebberZone/better-search">' . esc_html__( 'Contribute', 'better-search' ) . '</a>',
			);

			$links = array_merge( $links, $new_links );
		}
		return $links;
	}

	/**
	 * Get the help sidebar content to display on the plugin settings page.
	 *
	 * @since 1.8.0
	 */
	public function get_help_sidebar() {
		$help_sidebar =
			/* translators: 1: Plugin support site link. */
			'<p>' . sprintf( __( 'For more information or how to get support visit the <a href="%s">support site</a>.', 'better-search' ), esc_url( 'https://webberzone.com/support/' ) ) . '</p>' .
			/* translators: 1: WordPress.org support forums link. */
			'<p>' . sprintf( __( 'Support queries should be posted in the <a href="%s">WordPress.org support forums</a>.', 'better-search' ), esc_url( 'https://wordpress.org/support/plugin/better-search' ) ) . '</p>' .
			'<p>' . sprintf(
				/* translators: 1: Github issues link, 2: Github plugin page link. */
				__( '<a href="%1$s">Post an issue</a> on <a href="%2$s">GitHub</a> (bug reports only).', 'better-search' ),
				esc_url( 'https://github.com/WebberZone/better-search/issues' ),
				esc_url( 'https://github.com/WebberZone/better-search' )
			) . '</p>';

		/**
		 * Filter to modify the help sidebar content.
		 *
		 * @since 3.3.0
		 *
		 * @param string $help_sidebar Help sidebar content.
		 */
		return apply_filters( self::$prefix . '_settings_help', $help_sidebar );
	}

	/**
	 * Get the help tabs to display on the plugin settings page.
	 *
	 * @since 3.3.0
	 */
	public function get_help_tabs() {
		$help_tabs = array(
			array(
				'id'      => 'bsearch-settings-general-help',
				'title'   => esc_html__( 'General', 'better-search' ),
				'content' =>
				'<p>' . __( 'This screen provides the basic settings for configuring Better Search.', 'better-search' ) . '</p>' .
				'<p>' . __( 'Enable tracking, seamless mode and the cache, configure basic tracker and uninstall settings.', 'better-search' ) . '</p>',
			),
			array(
				'id'      => 'bsearch-settings-search',
				'title'   => __( 'Search', 'better-search' ),
				'content' =>
				'<p>' . __( 'This screen provides settings to tweak the search algorithm.', 'better-search' ) . '</p>' .
					'<p>' . __( 'Configure number of search results, enable FULLTEXT and BOOLEAN mode, tweak the weight of title and content and block words.', 'better-search' ) . '</p>',
			),
			array(
				'id'      => 'bsearch-settings-heatmap',
				'title'   => __( 'Heatmap', 'better-search' ),
				'content' =>
				'<p>' . __( 'This screen provides settings to tweak the output of the search heatmap to display popular searches.', 'better-search' ) . '</p>' .
					'<p>' . __( 'Configure title of the searches, period of trending searches, color and font sizes of the heatmap.', 'better-search' ) . '</p>',
			),
			array(
				'id'      => 'bsearch-settings-styles',
				'title'   => __( 'Styles', 'better-search' ),
				'content' =>
				'<p>' . __( 'This screen provides options to control the look and feel of the search page.', 'better-search' ) . '</p>' .
					'<p>' . __( 'Choose for default set of styles or add your own custom CSS to tweak the display of the search results page.', 'better-search' ) . '</p>',
			),
		);

		/**
		 * Filter to add more help tabs.
		 *
		 * @since 3.3.0
		 *
		 * @param array $help_tabs Associative array of help tabs.
		 */
		return apply_filters( self::$prefix . '_settings_help', $help_tabs );
	}


	/**
	 * Add footer text on the plugin page.
	 *
	 * @since 2.0.0
	 */
	public static function get_admin_footer_text() {
		return sprintf(
			/* translators: 1: Opening achor tag with Plugin page link, 2: Closing anchor tag, 3: Opening anchor tag with review link. */
			__( 'Thank you for using %1$sWebberZone Better_Search%2$s! Please %3$srate us%2$s on %3$sWordPress.org%2$s', 'knowledgebase' ),
			'<a href="https://webberzone.com/plugins/better-search/" target="_blank">',
			'</a>',
			'<a href="https://wordpress.org/support/plugin/better-search/reviews/#new-post" target="_blank">'
		);
	}

	/**
	 * Modify settings when they are being saved.
	 *
	 * @since 3.3.0
	 *
	 * @param  array $settings Settings array.
	 * @return array Sanitized settings array.
	 */
	public function change_settings_on_save( $settings ) {

		// Sanitize exclude_cat_slugs to save a new entry of exclude_categories.
		if ( isset( $settings['exclude_cat_slugs'] ) ) {

			$exclude_cat_slugs = array_unique( str_getcsv( $settings['exclude_cat_slugs'] ) );

			foreach ( $exclude_cat_slugs as $cat_name ) {
				$cat = get_term_by( 'name', $cat_name, 'category' );

				// Fall back to slugs since that was the default format before v2.4.0.
				if ( false === $cat ) {
					$cat = get_term_by( 'slug', $cat_name, 'category' );
				}
				if ( isset( $cat->term_taxonomy_id ) ) {
					$exclude_categories[]       = $cat->term_taxonomy_id;
					$exclude_categories_slugs[] = $cat->name;
				}
			}
			$settings['exclude_categories'] = isset( $exclude_categories ) ? join( ',', $exclude_categories ) : '';
			$settings['exclude_cat_slugs']  = isset( $exclude_categories_slugs ) ? Helpers::str_putcsv( $exclude_categories_slugs ) : '';

		}

		// Delete the cache.
		\WebberZone\Better_Search\Util\Cache::delete();

		return $settings;
	}
}
