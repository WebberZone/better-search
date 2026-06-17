---
slug: better-search-settings-search
title: "Better Search Settings &#8211; Search"
products: [better-search]
sections: [01-bs-getting-started]
tags: [better-search,search,settings]
status: publish
order: 0
---

[kbtoc]

The **Search** tab of the <a href="https://webberzone.com/plugins/better-search/" data-type="page" data-id="168">Better Search</a> settings page offers a collection of options that allow you to fine-tune how search results are generated, displayed, and filtered. Below is an explanation of each available setting.

## Search Configuration

### Number of Search Results per page

Set the maximum number of search results displayed per page.

### Post types to include

Select which post types you want to include in the search results (e.g., post, page, custom post types).

### Enable MySQL FULLTEXT searching

Disabling this option will no longer give relevance-based results. If you’re using Better Search Pro, you will see the installation status of the FULLTEXT indexes along with a button which allows you to recreate them.

### Minimum characters *(Pro only)*

Minimum characters required for a fulltext search. If the search term has fewer characters, a LIKE search will be performed instead.

### Activate BOOLEAN mode

Use MySQL BOOLEAN mode for FULLTEXT searches. Allows advanced operators, but may limit some relevancy features. <a href="https://dev.mysql.com/doc/refman/8.0/en/fulltext-boolean.html" target="_blank" rel="noreferrer noopener">MySQL BOOLEAN Mode Documentation</a>.

### Enable LIKE fallback *(Pro only)*

If FULLTEXT returns zero results, a LIKE search is performed instead.

> [!NOTE]
> ⓘ This feature does not work with custom tables currently.

### Minimum relevance percentage *(Pro only)*

The minimum relevance percentage required for a post to be included in the search results (0–100).

### Fuzzy search level *(Pro only)*

Enable [fuzzy search](https://webberzone.com/support/knowledgebase/fuzzy-matches/) and adjust the level of flexibility for matching search terms that contain misspellings. Higher levels may include more results with potential misspellings.

> [!WARNING]
> ⚠️ Fuzzy searching can be computationally intensive; caching is recommended for high-traffic sites.

## Weighting

### Post title

The weight to give to the post title when calculating the relevance of the post. Set this to a higher number than the following option to prioritize the post title in the relevance calculation.

### Post content

The weight to give to the post content when calculating the relevance of the post.

### Post excerpt *(Pro only)*

Set the importance of the post excerpt in relevance calculation.

### Categories *(Pro only)*

Set the weight for category matches in relevance calculation.

### Tags *(Pro only)*

Set the weight for tag matches in relevance calculation.

### Default taxonomy weight *(Pro only)*

Weight to give other taxonomy matches when calculating relevance.

### Use precomputed taxonomy score *(Pro only)*

Enable the use of precomputed taxonomy scores for relevance calculation. Improves performance but ignores the above taxonomy weights for live queries. This only works when ECSI is enabled in the <a href="https://webberzone.com/support/knowledgebase/better-search-settings-performance/" data-type="wz_knowledgebase" data-id="9146">Performance tab</a>.

## Inclusion options

### Search Post slug *(Pro only)*

Include post slugs in the search.

### Search Excerpt

Include post excerpts in the search.

### Search Taxonomies

Include posts where all taxonomies (categories, tags, custom taxonomies) match the search terms.

### Search Meta

Include posts where meta values match the search terms.

### Search Authors

Include posts from authors that match the search terms.

### Search Comments

Include posts where comments include the search terms.

## Exclusion options

### Exclude password-protected posts

Remove password-protected posts from search results.

### Exclude post IDs

Enter a comma-separated list of post/page/custom post type IDs to exclude (e.g., 188,1024,50).

### Exclude Categories

Comma-separated list of category slugs to exclude. Autocomplete is available.\
**Note:** Does not support custom taxonomies.

### Exclude category IDs

Read-only field automatically populated based on the above input.

> [!NOTE]
> ⓘ Uses term_taxonomy_id, which may differ from the IDs on the Categories page.

## Banned words options

### Filter these words

Words in this list will be stripped out of the search results. Enter as a comma-separated list.

### Match whole words only

When enabled, only whole words in the search query are filtered. Partial matches are ignored (e.g., “grow” will not ban “grown” or “grower”).

### Block searches containing banned words

When enabled, no results are returned if the search query includes any banned words.\
If Seamless mode is disabled, an error message is displayed. With Seamless mode enabled, a “Nothing found” message is shown (customizable via your theme).

### Exclude Front page *(Pro only)*

When enabled, excludes the front page from search results.

### Exclude Posts page *(Pro only)*

When enabled, excludes the Posts page from search results.
