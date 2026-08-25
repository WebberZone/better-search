---
slug: better-search-rest-api
title: "Better Search REST API integration for search results"
products: [better-search]
sections: ["03-bs-developer-docs"]
tags: [better-search, wp-rest-api]
status: publish
---

[Better Search Pro](https://webberzone.com/plugins/better-search/pro/) enhances the <a href="https://developer.wordpress.org/rest-api/reference/search-results/" target="_blank" rel="noreferrer noopener">WordPress REST API search results</a> to show posts ordered by relevance. When you enable this feature, a REST API search is performed, and Better Search Pro applies the default search configuration, just as it does for standard WordPress search requests.

WordPress core utilizes the REST API in various scenarios, including the ‘Add Link’ modal when searching for content. If you’ve customized Better Search settings to use specific configurations for admin searches, those settings will be applied in these cases as well. Depending on your search setup, this may result in unexpected behavior.

## Enable REST API support

You can enable this by navigating to the plugin settings page under **Better Search > Settings**. Enable ‘Enable REST API’ and save the settings. Alternatively, you can pass `better_search_query` as the parameter to enable relevance searching.

## Better Search REST API Parameters

| **Parameter** | **Description** |
| --- | --- |
| `better_search_query` | Enables Better Search for the REST API query when set to `true`. |
| `search_excerpt` | Includes post excerpts in the search query. |
| `search_taxonomies` | Searches within taxonomy terms (e.g., categories or tags). |
| `search_meta` | Searches within custom post meta fields. |
| `search_authors` | Enables searching by author names. |
| `search_comments` | Includes post comments in the search query. |
| `search_slug` | Enables searching within the post slug. |
| `exclude_protected_posts` | Excludes password-protected posts from the search results. |
| `exclude_post_ids` | A list of post IDs to exclude from the search results. |
| `exclude_categories` | A list of category IDs to exclude posts from those categories in the results. |
| `weight_title` | Sets the weight for matching keywords in post titles. |
| `weight_content` | Sets the weight for matching keywords in post content. |
| `min_relevance` | Filters results to only include those with a relevance score equal to or greater than this value. |

All parameters are optional. If not provided, Better Search will use its default configuration. You can use these parameters to fine-tune search behavior for specific REST API requests.
