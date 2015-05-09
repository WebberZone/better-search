=== Better Search ===
Tags: search, Better Search, related search, relevant search, search results, heatmap, popular searches, top searches, relevance
Contributors: Ajay
Donate link: http://ajaydsouza.com/donate/
Stable tag: trunk
Requires at least: 3.5
Tested up to: 4.3
License: GPLv2 or later


Better Search replaces the default WordPress search with a better search that gives contextual results sorted by relevance

== Description ==

<a href="http://ajaydsouza.com/wordpress/plugins/better-search/">Better Search</a> replaces the default WordPress search engine with a more powerful search engine that gives search results relevant to the title and content of the post. This means that visitors to your blog will find will find what they are looking for quicker than if you didn't have **Better Search** installed.

Better Search can search through not just posts, but also pages and other custom post types. Let your visitors find what they are looking for.

The plugin is packed with options to allow you to easily customise the output. You can also fine tune the results by assigning a greater weight to either the title or the content. The default mode is a seamless integration with your WordPress theme. And, for power users, Better Search supports templates for that extra something.

Additionally, the plugin also tracks the searches and you to display a "search heatmap" of the most popular searches. Support for WordPress widgets will allow you to easily add this heatmap to your theme's sidebar or footer.


= Features =
* **Automatic**: Once activated, Better Search will automatically replace your default WordPress search with more relevant search results
* **Seamless integration**: From v1.3.3, you can activate seamless integration which will output the search results perfectly integrated into your theme without the need for custom search templates
* **Relevance**: Search results are automatically sorted by relevance. You can also turn off relevancy based searching, in which case, results are sorted by date
* **Control the results**: Fine tune the results by changing the weighting of post title and post content. Turn on BOOLEAN search to override the default NATURAL LANGUAGE search of mySQL
* **Highlight**: Highlight the search terms in the results
* **Popular searches**: Find out what visitors are searching for on your blog. Display a list of popular search terms (daily and overall) on your blog in the form of a heatmap. Widget support for easy integration in your theme
* **Customisation**: Support for a template file for perfect integration into your blog template. Alternatively, just input your own CSS styles in the *Custom Styles* tab in the Settings Page. Check the FAQ for more information
* **Supports cache plugins**: Works with caching plugins like WP-Super-Cache and W3 Total Cache
* **Profanity filter**: Customise the list of stop words that will automatically be filtered out of search queries
* **Translation ready**: Better Search is translation ready. If you're interested in translating Better Search into your own language <a href="http://ajaydsouza.com/contact/">let me know</a>.


= Contribute =
Better Search is also available on Github at https://github.com/ajaydsouza/better-search
So, if you've got some cool feature that you'd like to implement into the plugin or a bug you've been able to fix, consider forking the project and sending me a pull request.


== Upgrade Notice ==

= 2.0.1 =
Highlight search results; Filterable search query; multisite support; bug fixes;
Check the Changelog for details


== Changelog ==

= 2.0.1 =
* Fixed: Bug where highlighting search terms broke HTML in links

= 2.0.0 =
* New: Network Activate and Deactivate the plugin on WordPress Multisite
* New: Option to highlight search results. If missing, add: <code>.bsearch_highlight { background:#ffc; }</code> under Custom Styles
* New: Fully filterable search query
* New: Recreate Index button in the settings page
* New: Delete transients button in the settings page
* Modified: Better Search will now try BOOLEAN MODE and non-FULLTEXT modes in case FULLTEXT search doesn't return any results
* Modified: Deprecated always dynamic heatmap option that bypassed cache
* Modified: Reorganised admin interface
* Modified: Seamless mode is now the default mode
* Modified: Better Search uses transients to catch results when not using seamless mode
* Modified: Search form uses `class` instead of `id`
* Fixed: Seamless mode would overwrite all queries, even those outside the loop
* Fixed: WordPress widget settings

= 1.3.6 =
* Fixes missing wick files in Settings page 404 error

= 1.3.5 =
* Fixed: Seamless mode interfered with the Media search in the Admin
* Fixed: Potential Reflective XSS vulnerability

= 1.3.4 =
* New: Option to add `noindex,nofollow` meta tag to the header
* Modified: Tracking script now set to bypass <a href="https://support.cloudflare.com/hc/en-us/articles/200168056-What-does-Rocket-Loader-do-">Rocket Loader</a>
* Fixed: Class of header row on search results page. You can now add your custom styles to `bsearch_nav_row1` and `bsearch_nav_row2`
* Fixed: Widget search heatmap colours were not loaded properly

= 1.3.3 =
* New: Responsive admin interface
* New: Seamless integration mode. With this enabled, you can benefit from relevant search results displayed how your theme intended it to be!
* Modified: Modified `get_bsearch_heatmap` to accept an array of parameters. If you're using this function, please note the modified usage in the FAQ
* New: Option to turn off tracking searches of Admins and Editors
* Fixed: Widget initialisation
* Modified: Reformatted code to follow WordPress PHP Standards

= 1.3.2 =
* New: Profanity filter. Courtesy <a href="http://banbuilder.com/">Banbuilders</a>
* New: Option to turn of the search results tracking. Ideal if you don't care about the popular search terms on your blog
* New: Option to include the thumbnails in the search results
* Modified: Search results now have better pagination. This is especially good when you have lots of search results - Thanks to J Norton for this feature
* Modified: Plugin should now return results even if the search word is less than 4 characters
* Fixed: Bug fixes - Thanks to Rich for some of the fixes

= 1.3.1 =
Fixed: PHP Notices

= 1.3 =
* Modified: Revamp of admin interface of the plugin
* Added: New option to activate BOOLEAN mode of mySQL FULLTEXT searching. <a href="https://dev.mysql.com/doc/refman/5.0/en/fulltext-boolean.html" target="_blank">Check the mySQL docs for further information on this</a>
* Added: Custom post type support. Now choose what visitors are allowed to search
* Added: Links in the search heatmap are no-follow by default. You can turn this off in the Settings page
* Added: Option to make make heatmap links to open in a new window
* Added: Option to turn off the display of the heatmap on the results page
* Added: New CSS classes for heatmaps on the search results page - `heatmap_daily` and `heatmap_overall`
* Fixed: Possible cross-site request forgery issue in the Settings page

= 1.2.1 =
* Fixed: "Missing argument" error for heatmaps

= 1.2 =
* New: Updates for better search template compatibility. New template included in this release
* Modified: Relevance score is now displayed as a percentage
* Fixed: Daily search terms were not being cleared
* Modified: Default search colours for the heatmap are grey and black instead of blue and red
* Modified: Plugin will not add a link to <a href="http://ajaydsouza.com/wordpress/plugins/better-search/">Better Search homepage</a> by default
* Modified: New WordPress widget to display the popular searches in your sidebar
* New: New template file based on Twenty Eleven WordPress theme

= 1.1.7 =
* Two new fulltext indexes added... the first step to better control on search results

= 1.1.6 =
* Bug fixed in display of daily search heatmap

= 1.1.5 =
* Fixed a bug

= 1.1.4 =
* Fixed a bug

= 1.1.3 =
* Critical Fix: Possible security hole

= 1.1.2 =
* Fixed: Searches not tracked when not using template

= 1.1.1 =
* Fixed: Certain search terms didn't work

= 1.1 =
* Plugin now allows use of template file. Create a file `better-search-template.php` in your themes folder.
* Pages are also included in the results. You can turn it off in options
* Drafts are no longer included in results
* WordPress Title rewritten
* Added support for localization.

= 1.0 =
* Release


== Installation ==

= WordPress install =
1. Navigate to Plugins within your WordPress Admin Area

2. Click "Add new" and in the search box enter "Better Search" and select "Keyword" from the dropdown

3. Find the plugin in the list (usually the first result) and click "Install Now"

= Manual install =
1. Download the plugin

2. Extract the contents of better-search.zip to wp-content/plugins/ folder. You should get a folder called better-search.

3. Activate the Plugin in WP-Admin.

4. Goto **Settings > Better Search** to configure

5. Goto **Appearance > Widgets** to add the Popular Searches sidebar widgets to your theme

6. Optionally visit the **Custom Styles** tab to add any custom CSS styles. These are added to `wp_head` on the pages where the posts are displayed


== Screenshots ==

1. Options in WP-Admin - General options
2. Options in WP-Admin - Search options
3. Options in WP-Admin - Heatmap options
4. Options in WP-Admin - Custom styles
5. Options in WP-Admin - Reset count and Maintenance
6. Better Search widget


== Frequently Asked Questions ==

If your question isn't listed here, please open a new thread at the <a href="hhttp://wordpress.org/support/plugin/better-search">WordPress.org support forum</a>. I monitor the forums on an ongoing basis. If you're looking for email based support, please see <a href="http://ajaydsouza.com/support/">details here</a>.

= Can I customize the output? =

Better Search has a huge set of options that help you customise the output or fine tune the results without leaving the comfort of your WordPress site. Goto **Settings > Better Search** to configure.

The plugin also supports the use of template files within your theme. You can create a file called `better-search-template.php` in your theme's directory and the plugin will use it to display the results.

Take a look at http://ajaydsouza.com/wordpress/plugins/better-search/bsearch-templates for use of custom templates and template tags supported by Better Search

= Can you create a search template for my theme? =

Yes I can. However, there are no guarantee on the time frame for the same. Additionally, if I am unable to access the theme for testing, e.g. with paid premium themes, I won't be able to create this template.

If you have already created a template that you would like to share with the WordPress Community, you can <a href="http://ajaydsouza.com/contact/">contact me</a> and I will add it into the package.

= My search words are getting filtered or *How does the profanity filter work* =

From v1.3.2, Better Search includes a very cool profanity filter using the script from <a href-"http://banbuilder.com/">Banbuilder</a>. You can customise which list of words you want to filter out from the Better Search settings page. Find the setting called "Filter these words:". The plugin will automatically strip out both partial and complete references of these words.
You can turn the filter off by emptying the list.

Know of a better profanity filter? Suggest one in the <a href="hhttp://wordpress.org/support/plugin/better-search">forums</a>.

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
