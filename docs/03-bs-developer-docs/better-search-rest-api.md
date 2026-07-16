---
slug: better-search-rest-api
title: "Better Search REST API integration for search results"
products: [better-search]
sections: [03-bs-developer-docs]
tags: [better-search,wp-rest-api]
status: publish
order: 0
---

<a href="https://webberzone.com/plugins/better-search/pro/" data-type="page" data-id="7797">Better Search Pro</a> enhances the <a href="https://developer.wordpress.org/rest-api/reference/search-results/" target="_blank" rel="noreferrer noopener">WordPress REST API search results</a> to show posts ordered by relevance. When you enable this feature, a REST API search is performed, and Better Search Pro applies the default search configuration, just as it does for standard WordPress search requests.

WordPress core utilizes the REST API in various scenarios, including the ‘Add Link’ modal when searching for content. If you’ve customized Better Search settings to use specific configurations for admin searches, those settings will be applied in these cases as well. Depending on your search setup, this may result in unexpected behavior.

## Enable REST API support

You can enable this by navigating to the plugin settings page under **Better Search \> Settings**. Enable ‘Enable REST API’ and save the settings. Alternatively, you can pass `better_search_query` as the parameter to enable relevance searching.

## Better Search REST API Parameters

<figure class="wp-block-table">
<table class="has-fixed-layout">
<thead>
<tr>
<th><strong>Parameter</strong></th>
<th><strong>Description</strong></th>
</tr>
</thead>
<tbody>
<tr>
<td><code>better_search_query</code></td>
<td>Enables Better Search for the REST API query when set to <code>true</code>.</td>
</tr>
<tr>
<td><code>search_excerpt</code></td>
<td>Includes post excerpts in the search query.</td>
</tr>
<tr>
<td><code>search_taxonomies</code></td>
<td>Searches within taxonomy terms (e.g., categories or tags).</td>
</tr>
<tr>
<td><code>search_meta</code></td>
<td>Searches within custom post meta fields.</td>
</tr>
<tr>
<td><code>search_authors</code></td>
<td>Enables searching by author names.</td>
</tr>
<tr>
<td><code>search_comments</code></td>
<td>Includes post comments in the search query.</td>
</tr>
<tr>
<td><code>search_slug</code></td>
<td>Enables searching within the post slug.</td>
</tr>
<tr>
<td><code>exclude_protected_posts</code></td>
<td>Excludes password-protected posts from the search results.</td>
</tr>
<tr>
<td><code>exclude_post_ids</code></td>
<td>A list of post IDs to exclude from the search results.</td>
</tr>
<tr>
<td><code>exclude_categories</code></td>
<td>A list of category IDs to exclude posts from those categories in the results.</td>
</tr>
<tr>
<td><code>weight_title</code></td>
<td>Sets the weight for matching keywords in post titles.</td>
</tr>
<tr>
<td><code>weight_content</code></td>
<td>Sets the weight for matching keywords in post content.</td>
</tr>
<tr>
<td><code>min_relevance</code></td>
<td>Filters results to only include those with a relevance score equal to or greater than this value.</td>
</tr>
</tbody>
</table>
</figure>

All parameters are optional. If not provided, Better Search will use its default configuration. You can use these parameters to fine-tune search behavior for specific REST API requests.
