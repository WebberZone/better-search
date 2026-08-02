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

#### BOOLEAN mode operators

With BOOLEAN mode active, visitors can use the following operators in the search box. They are passed straight through to MySQL.

| Operator | Example | What it does |
|---|---|---|
| `+` | `+wordpress plugin` | The word **must** be present in every result. |
| `-` | `wordpress -theme` | The word must **not** be present. Posts containing it are removed from the results entirely. |
| `~` | `wordpress ~beta` | **Demotes** rather than excludes. The post is still returned, but the word contributes negatively to its relevance score, so it ranks lower. |
| `>` | `>plugin` | Increases the word's contribution to the relevance score, pushing matching posts **higher** up the results. |
| `<` | `<theme` | Decreases the word's contribution, pushing matching posts **lower** down the results. |
| `*` | `plug*` | Trailing wildcard. Matches `plugin`, `plugins`, `plugged`, and so on. |
| `""` | `"wordpress plugin"` | Exact phrase match. |
| `( )` | `+wordpress +(plugin theme)` | Groups words into a subexpression, so operators can be applied to the group. |

A few things worth knowing:

- **`-` and `~` are not the same.** `-` filters a post out of the results; `~` keeps it but ranks it lower. Use `~` for "noise" words that shouldn't disqualify a post.
- **`>` and `<` only affect ranking, not matching.** They change the relevance score, so the effect is only visible when results are sorted by relevance (the default). They also do not make a word required — a word with no `+` in front of it stays optional. A typical use is inside a group: `+wordpress +(>plugin <theme)` returns posts containing "wordpress" plus at least one of "plugin" or "theme", ranking the "plugin" matches higher.
- **Very short words are ignored.** MySQL skips words below its minimum token length — 3 characters for InnoDB (`innodb_ft_min_token_size`) and 4 for MyISAM (`ft_min_word_len`) by default.
- **Phrase searches work even with BOOLEAN mode off.** If a query contains double-quoted text, Better Search enables BOOLEAN mode automatically for that query so the phrase is matched correctly.
- **Fuzzy search is skipped for these queries.** If a search uses any of `+ - ~ > < *`, [fuzzy matching](https://webberzone.com/support/knowledgebase/fuzzy-matches/) is automatically disabled for it, since the visitor has asked for a precise query.

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

### "Did you mean" suggestions *(Pro only)*

These settings only appear when **"Did you mean" suggestions** is turned on in the [Features tab](https://webberzone.com/support/knowledgebase/better-search-feature-manager/). See [Did You Mean Spelling Suggestions](https://webberzone.com/support/knowledgebase/did-you-mean-spelling-suggestions/) for full configuration details.

**Minimum searches to qualify as a suggestion** — a term must have been searched at least this many times before it can be suggested as a correction. Default: `3`.

**"Did you mean" mode** — **Suggest ("Did you mean")** shows a "Did you mean" link but still displays the original (empty) results. **Auto-correct** transparently re-runs the search with the corrected term when it actually returns results, showing a link back to the original query.

**Use enchant as a fallback** — falls back to the server's enchant spellchecker if your search log and site content have no close match. Disabled if the extension isn't installed. See [Installing enchant on your server](https://webberzone.com/support/knowledgebase/did-you-mean-spelling-suggestions/#installing-enchant-on-your-server).

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

Comma-separated list of category slugs to exclude. Autocomplete is available. Does not support custom taxonomies.

### Exclude category IDs

Read-only field automatically populated based on the above input.

> [!NOTE]
> ⓘ Uses term_taxonomy_id, which may differ from the IDs on the Categories page.

### Exclude Front page *(Pro only)*

When enabled, excludes the front page from search results.

### Exclude Posts page *(Pro only)*

When enabled, excludes the Posts page from search results.

## Banned words options

### Filter these words

Words in this list will be stripped out of the search results. Enter as a comma-separated list.

### Match whole words only

When enabled, only whole words in the search query are filtered. Partial matches are ignored (e.g., “grow” will not ban “grown” or “grower”).

### Block searches containing banned words

When enabled, no results are returned if the search query includes any banned words.\
If Seamless mode is disabled, an error message is displayed. With Seamless mode enabled, a “Nothing found” message is shown (customizable via your theme).
