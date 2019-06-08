=== Better Search ===
Tags: search, Better Search, related search, relevant search, search results, contextual search, heatmap, popular searches, top searches, relevance
Contributors: webberzone, Ajay
Donate link: https://ajaydsouza.com/donate/
Stable tag: trunk
Requires at least: 4.7
Tested up to: 5.2
License: GPLv2 or later


Better Search replaces the default WordPress search with a better search engine that gives contextual results sorted by relevance

== Description ==

[Better Search](https://webberzone.com/plugins/better-search/) replaces the default WordPress search engine with a more powerful search engine that gives search results relevant to the title and content of the post. This means that visitors to your blog will find what they are looking for quicker than if you didn't have **Better Search** installed.

Better Search can search through not just posts, but also pages and other custom post types. Let your visitors find what they are looking for.

The plugin is packed with options to allow you to easily customise the output. You can also fine tune the results by assigning a greater weight to either the title or the content. The default mode is a seamless integration with your WordPress theme. And, for power users, Better Search supports templates for that extra something.

And for even more advanced users, Better Search is packed with filters and actions that allow you to easily extend the plugin's feature set.

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
Better Search is also available on [Github](https://github.com/WebberZone/better-search)
So, if you've got some cool feature that you'd like to implement into the plugin or a bug you've been able to fix, consider forking the project and sending me a pull request.


== Screenshots ==

1. Options in WP-Admin - General options
2. Options in WP-Admin - Search results options
3. Options in WP-Admin - Heatmap options
4. Options in WP-Admin - Custom styles
5. Options in WP-Admin - Tools
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

If your question isn't listed there, please create a new post in the [WordPress.org support forum](https://wordpress.org/support/plugin/better-search). I monitor the forums regularly. If you're looking for more advanced _paid_ support, please see [details here](https://webberzone.com/support/).

= Can I customize the output? =

Better Search has a huge set of options that help you customise the output or fine tune the results without leaving the comfort of your WordPress site. Goto **Settings > Better Search** to configure.

The plugin also supports the use of template files within your theme. You can create a file called `better-search-template.php` in your theme's directory and the plugin will use it to display the results.

= My search words are getting filtered or *How does the profanity filter work* =

Better Search includes a very cool profanity filter using the script from [Banbuilder](https://banbuilder.com/). You can customise which list of words you want to filter out from the Better Search settings page. Find the setting called "Filter these words:". The plugin will automatically strip out both partial and complete references of these words.
You can turn the filter off by emptying the list.

Know of a better profanity filter? Suggest one in the [forums](https://wordpress.org/support/plugin/better-search).


== Changelog ==

= 2.3.0 =

Release post: [https://wzn.io/2WU0QUq](https://wzn.io/2WU0QUq)

* Features:
	* New option to disable aggressive searching when no results are found

* Enhancements:
	* HTML markup for the search results page when seamless mode is OFF has been rewritten with several new classes. [View example markup](https://gist.github.com/ajaydsouza/5f88766b9ea6cad3b032533383733813)

* Bug fixes:
	* WHERE clause will be the same with Seamless mode ON or OFF
	* The plugin will only force BOOLEAN mode if no results are found and this wasn't already enabled

For previous changelog entries, please refer to the separate changelog.txt file


== Upgrade Notice ==

= 2.3.0 =
New options and bug fixes.
For details on the update check the changelog and release post on https://webberzone.com
