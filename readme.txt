=== Better Search ===
Tags: search, Better Search, related search, relevant search, search results, contextual search, heatmap, popular searches, top searches, relevance
Contributors: webberzone, Ajay
Donate link: https://ajaydsouza.com/donate/
Stable tag: trunk
Requires at least: 3.9
Tested up to: 4.5
License: GPLv2 or later


Better Search replaces the default WordPress search with a better search that gives contextual results sorted by relevance

== Description ==

[Better Search](https://webberzone.com/plugins/better-search/) replaces the default WordPress search engine with a more powerful search engine that gives search results relevant to the title and content of the post. This means that visitors to your blog will find will find what they are looking for quicker than if you didn't have **Better Search** installed.

Better Search can search through not just posts, but also pages and other custom post types. Let your visitors find what they are looking for.

The plugin is packed with options to allow you to easily customise the output. You can also fine tune the results by assigning a greater weight to either the title or the content. The default mode is a seamless integration with your WordPress theme. And, for power users, Better Search supports templates for that extra something.

Additionally, the plugin also tracks the searches and you to display a "search heatmap" of the most popular searches. Support for WordPress widgets will allow you to easily add this heatmap to your theme's sidebar or footer.

= Features =
* **Automatic**: Once activated, Better Search will automatically replace your default WordPress search with more relevant search results
* **Seamless integration**: Search results are perfectly integrated into your theme without the need for custom search templates
* **Relevance**: Search results are automatically sorted by relevance. You can also turn off relevancy based searching, in which case, results are sorted by date
* **Control the results**: Fine tune the results by changing the weighting of post title and post content. Turn on BOOLEAN search to override the default NATURAL LANGUAGE search of mySQL
* **Popular searches**: Find out what visitors are searching for on your blog. Display a list of popular search terms (daily and overall) on your blog in the form of a heatmap. Widget support for easy integration in your theme as well as a shortcode [[bsearch_heatmap]]
* **Customisation**: Support for a template file for perfect integration into your blog template. Alternatively, just input your own CSS styles in the *Custom Styles* tab in the Settings Page. Check the FAQ for more information
* **Supports cache plugins**: Works with caching plugins like WP-Super-Cache and W3 Total Cache
* **Profanity filter**: Customise the list of stop words that will automatically be filtered out of search queries
* **Translation ready**: Better Search is translation ready


= Contribute =
Better Search is also available on [Github](https://github.com/ajaydsouza/better-search)
So, if you've got some cool feature that you'd like to implement into the plugin or a bug you've been able to fix, consider forking the project and sending me a pull request.


== Screenshots ==

1. Options in WP-Admin - General options
2. Options in WP-Admin - Search results options
3. Options in WP-Admin - Heatmap options
4. Options in WP-Admin - Custom styles
5. Options in WP-Admin - Reset count and Maintenance
6. Better Search widget


== Installation ==

= WordPress install =
1. Navigate to Plugins within your WordPress Admin Area

2. Click "Add new" and in the search box enter "Better Search"

3. Find the plugin in the list (usually the first result) and click "Install Now"

= Manual install =
1. Download the plugin

2. Extract the contents of better-search.zip to wp-content/plugins/ folder. You should get a folder called better-search.

3. Activate the Plugin in WP-Admin.

4. Goto **Settings > Better Search** to configure

5. Goto **Appearance > Widgets** to add the Popular Searches sidebar widgets to your theme

6. Optionally visit the **Custom Styles** tab to add any custom CSS styles. These are added to `wp_head` on the pages where the posts are displayed


== Frequently Asked Questions ==

If your question isn't listed there, please create a new post in the [WordPress.org support forum](http://wordpress.org/support/plugin/better-search). I monitor the forums on an ongoing basis. If you're looking for more advanced _paid_ support, please see [details here](https://webberzone.com/support/).

= Can I customize the output? =

Better Search has a huge set of options that help you customise the output or fine tune the results without leaving the comfort of your WordPress site. Goto **Settings > Better Search** to configure.

The plugin also supports the use of template files within your theme. You can create a file called `better-search-template.php` in your theme's directory and the plugin will use it to display the results.

= My search words are getting filtered or *How does the profanity filter work* =

Better Search includes a very cool profanity filter using the script from <a href-"http://banbuilder.com/">Banbuilder</a>. You can customise which list of words you want to filter out from the Better Search settings page. Find the setting called "Filter these words:". The plugin will automatically strip out both partial and complete references of these words.
You can turn the filter off by emptying the list.

Know of a better profanity filter? Suggest one in the [forums](http://wordpress.org/support/plugin/better-search).

= Functions =

**get_bsearch_heatmap**

Returns a formatted heatmap of popular searches. You can use this function in your search template or anywhere in your WordPress theme pages.

Example Usage:

`
<?php if function_exists( 'get_bsearch_heatmap' ) {
	$args = array(
		'daily' => FALSE,
		'smallest' => '10',			// Heatmap - Smallest Font Size
		'largest' => '20',			// Heatmap - Largest Font Size
		'unit' => 'pt',				// Heatmap - We'll use pt for font size
		'cold' => 'ccc',			// Heatmap - cold searches
		'hot' => '000',				// Heatmap - hot searches
		'before' => '',				// Heatmap - Display before each search term
		'after' => '&nbsp;',		// Heatmap - Display after each search term
		'heatmap_limit' => '30',	// Heatmap - Maximum number of searches to display in heatmap
		'daily_range' => '7',		// Daily Popular will contain posts of how many days?
	);

	echo get_bsearch_heatmap( $args );
}

`

== Changelog ==

= 2.1.0 =
* Features:
	* Cache enable/disable option + Clear cache button. Replaces "Delete transients"
	* Search form widget. Find it under Appearances > Widgets or in the Customizer
	* Heatmap search widget: Display the popular searches heatmap using `[bsearch_heatmap]`

* Enhancements:
	* Query filters now work for the LIKE search as well
	* New filter to override the censor character (currently a space) e.g. usage at https://gist.github.com/ajaydsouza/f23f35704431485c6922
	* Banned terms will now filter the main search query and will no longer display the search term even in seamless mode

* Bug fixes:
	* Highlighting now works when the search term has double or single quotes

= 2.0.1 =
* Bug fixes:
	* Bug where highlighting search terms broke HTML in links

= 2.0.0 =
* Features:
	* Network Activate and Deactivate the plugin on WordPress Multisite
	* Option to highlight search results. If missing, add: <code>.bsearch_highlight { background:#ffc; }</code> under Custom Styles
	* Fully filterable search query
	* Recreate Index button in the settings page
	* Delete transients button in the settings page

* Enhancements:
	* Better Search will now try BOOLEAN MODE and non-FULLTEXT modes in case FULLTEXT search doesn't return any results
	* Deprecated always dynamic heatmap option that bypassed cache
	* Reorganised admin interface
	* Seamless mode is now the default mode
	* Better Search uses transients to catch results when not using seamless mode
	* Search form uses `class` instead of `id`

* Bug fixes:
	* Seamless mode would overwrite all queries, even those outside the loop
	* WordPress widget settings

For previous changelog entries, please refer to the separate changelog.txt file


== Upgrade Notice ==

= 2.1.0 =
* New features and bug fixes. Upgrade highly recommended. Please do verify your settings after the upgrade.
Check the Changelog for details

