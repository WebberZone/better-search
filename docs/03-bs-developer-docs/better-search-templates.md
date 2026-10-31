---
slug: better-search-templates
title: "Understanding Better Search Templates"
products: [better-search]
sections: ["03-bs-developer-docs"]
tags: [better-search, developer, search]
status: publish
order: 0
---

[Better Search](https://webberzone.com/plugins/better-search/) works out of the box. Install and activate it and you’re ready to go. By default, Better Search integrates with your theme by overriding the main query to display relevant search results.

If you turn [Seamless Mode off](https://webberzone.com/support/knowledgebase/better-search-settings-general/#enable-seamless-integration), then Better Search will look for a file called `better-search-template.php` in your current themes folder. If this is found, the plugin will use this file, otherwise, it will default to the one hardcoded into the plugin. If you’re using a block theme aka Full Site Editing (FSE), then Better Search will look for `better-search-template.html` instead.

The classic theme layout included within the plugin differs from the block theme template, primarily driven by the limitations of the extensive use of PHP.

## For Classic Themes

You can view the code behind the <a href="https://github.com/WebberZone/better-search/blob/master/templates/better-search-template.php" target="_blank" rel="noreferrer noopener">default template</a> Better Search uses if your site uses a classic theme. Behind the scenes, Better Search uses `Better_Search_Query`, a wrapper for <a href="https://developer.wordpress.org/reference/classes/wp_query/" target="_blank" rel="noreferrer noopener">WP_Query</a> with additional settings needed for Better Search to fetch the relevant search results. This means that if you’re familiar with `WP_Query`, then you only need to make a few minor tweaks.

### Results header

The classic template calls `the_bsearch_header()` to show the results count, page number, sort links and results-per-page links above the results. From 4.5.3, the header is built with `div` elements laid out with CSS Grid instead of a table, so screen readers no longer announce it as a data table:

```html
<div class="bsearch_nav">
	<div class="bsearch_nav_row1">
		<div class="bsearch_nav_results">Results 1 - 10 of 27</div>
		<div class="bsearch_nav_pages">Page 1 of 3</div>
	</div>
	<div class="bsearch_nav_row2">
		<div class="bsearch_nav_sort">Sorted by: Relevance | Sort by: Date</div>
		<div class="bsearch_nav_limits">Results per-page: 10 | 20 | All</div>
	</div>
</div>
```

Each row shows two columns, which stack into one column on screens up to 600px wide. Update any custom CSS that targeted the old `table`, `tr` or `td` elements to use these classes. The `bsearch_nav`, `bsearch_nav_row1` and `bsearch_nav_row2` classes are unchanged. The grid layout comes from the Better Search stylesheet, so it needs [Include inbuilt styles](https://webberzone.com/support/knowledgebase/better-search-settings-output/#include-inbuilt-styles) enabled on the Output settings tab.

## For Block Themes

Block Themes use HTML files for templates. Create a `better-search-template.html` file in your theme’s `templates` directory. WordPress will automatically find and register the template for you. Better Search will use that instead of <a href="https://github.com/WebberZone/better-search/blob/master/templates/better-search-template.html" target="_blank" rel="noreferrer noopener">the one included in the plugin</a>.

The template uses custom block patterns: `better-search/search-form` and `better-search/template-query-loop-news-blog`. The former displays a search form, while the latter displays the posts in a three column format.

Alternatively, you can keep seamless mode enabled and edit the search template for your site. The plugin registers a design called **Better Search Results** which you can find in the sidebar.

![](https://webberzone.com/wp-content/uploads/2024/11/Edit-the-Search-Template-1024x492.webp)
