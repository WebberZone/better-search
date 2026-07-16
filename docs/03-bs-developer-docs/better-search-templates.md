---
slug: better-search-templates
title: "Understanding Better Search Templates"
products: [better-search]
sections: [03-bs-developer-docs]
tags: [better-search,developer,search]
status: publish
order: 0
---

<a href="https://webberzone.com/plugins/better-search/" data-type="wzkb_category" data-id="46">Better Search</a> works out of the box. Install and activate it and you’re ready to go. By default, Better Search integrates with your theme by overriding the main query to display relevant search results.

If you turn [Seamless Mode off](https://webberzone.com/support/knowledgebase/better-search-settings-general/#enable-seamless-integration), then Better Search will look for a file called `better-search-template.php` in your current themes folder. If this is found, the plugin will use this file, otherwise, it will default to the one hardcoded into the plugin. If you’re using a block theme aka Full Site Editing (FSE), then Better Search will look for `better-search-template.html` instead.

The classic theme layout included within the plugin differs from the block theme template, primarily driven by the limitations of the extensive use of PHP.

## For Classic Themes

You can view the code behind the <a href="https://github.com/WebberZone/better-search/blob/master/templates/better-search-template.php" target="_blank" rel="noreferrer noopener">default template</a> Better Search uses if your site uses a classic theme. Behind the scenes, Better Search uses `Better_Search_Query`, a wrapper for <a href="https://developer.wordpress.org/reference/classes/wp_query/" target="_blank" rel="noreferrer noopener">WP_Query</a> with additional settings needed for Better Search to fetch the relevant search results. This means that if you’re familiar with `WP_Query`, then you only need to make a few minor tweaks.

## For Block Themes

Block Themes use HTML files for templates. Create a `better-search-template.html` file in your theme’s `templates` directory. WordPress will automatically find and register the template for you. Better Search will use that instead of <a href="https://github.com/WebberZone/better-search/blob/master/templates/better-search-template.html" target="_blank" rel="noreferrer noopener">the one included in the plugin</a>.

The template uses custom block patterns: `better-search/search-form` and `better-search/template-query-loop-news-blog`. The former displays a search form, while the latter displays the posts in a three column format.

Alternatively, you can keep seamless mode enabled and edit the search template for your site. The plugin registers a design called **Better Search Results** which you can find in the sidebar.

<figure class="wp-block-image size-large">
<img src="https://webberzone.com/wp-content/uploads/2024/11/Edit-the-Search-Template-1024x492.webp" class="wp-image-8482" loading="lazy" decoding="async" srcset="https://webberzone.com/wp-content/uploads/2024/11/Edit-the-Search-Template-1024x492.webp 1024w, https://webberzone.com/wp-content/uploads/2024/11/Edit-the-Search-Template-300x144.webp 300w, https://webberzone.com/wp-content/uploads/2024/11/Edit-the-Search-Template-768x369.webp 768w, https://webberzone.com/wp-content/uploads/2024/11/Edit-the-Search-Template-1536x738.webp 1536w, https://webberzone.com/wp-content/uploads/2024/11/Edit-the-Search-Template-2048x984.webp 2048w" sizes="auto, (max-width: 1024px) 100vw, 1024px" width="1024" height="492" />
</figure>
