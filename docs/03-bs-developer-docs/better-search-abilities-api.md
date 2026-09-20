---
slug: better-search-abilities-api
title: "Better Search Abilities API"
products: [better-search]
sections: ["03-bs-developer-docs"]
tags: [abilities-api, better-search, developer]
status: publish
order: 0
toc: true
---

[toc]

[Better Search](https://webberzone.com/plugins/better-search/) 4.5.0 registers abilities with the WordPress Abilities API. The shared ability runs a relevance-weighted search; Better Search Pro adds abilities for popular search terms, spelling suggestions, and clearing the cache.

## Requirements

- Better Search 4.5.0 or later.
- WordPress 6.9 or later, which provides the Abilities API used by the plugin.
- Better Search Pro for the Pro abilities, and the `manage_options` capability for the management ones.

## Using an AI assistant

The Abilities API does not add a chat screen to WordPress. It makes plugin actions available to connected software. To use an AI chat assistant, connect your WordPress site to an AI client that supports MCP through an MCP server. One option is the separate MCP Adapter plugin for WordPress; Better Search does not include the adapter. Installing the adapter alone does not connect an AI client. Follow the adapter and client's setup instructions to connect them.

Once connected, you can ask in plain English:

> What does my site have about tigers?

The assistant discovers `better-search/search`, sends a search input to the ability, and summarizes the returned posts. You do not need to enter JSON in the chat. The assistant uses the WordPress account configured for the MCP connection, and that account must be signed in: the ability requires the `read` capability and does not answer anonymous requests.

### Ask in English, search with keywords

Your question is written in English, but the phrase the assistant puts in the `query` property should be **keywords**, not the whole sentence. Better Search combines search terms with `AND`, so a long question rarely matches anything even when the site covers the subject well:

| Search phrase | Matches |
| --- | --- |
| `Where can I find information about tiger facts and conservation?` | 0 |
| `tiger facts` | 161 |

A good assistant does this for you, turning "What does my site have about tigers?" into `{"query": "tiger facts"}`. If it sends the whole question instead, the ability answers with an error explaining that the phrase was too long and asking for a few keywords, so the assistant can try again. See [Long search phrases](#long-search-phrases).

## Search

Both the free and Pro plugins register `better-search/search`. It runs a relevance-weighted search and returns each readable result's ID, title, URL, excerpt, and relevance score.

The input accepts these properties:

| Property | Type | Required | Description |
| --- | --- | --- | --- |
| `query` | String | Yes | The search phrase. Use a few distinctive keywords rather than a sentence. Accepts 1 to 500 characters. |
| `limit` | Integer | No | Maximum number of results. Defaults to `10`; accepted values are `1`–`100`. |
| `offset` | Integer | No | Number of results to skip. Defaults to `0`. Use it with `total` to page through results. |
| `post_types` | String array | No | Post type names to search. Only public, searchable post types are accepted; anything else is rejected. |

For example, a client can pass an input object like this:

```json
{
  "query": "tiger facts",
  "limit": 10,
  "offset": 0,
  "post_types": ["post", "page"]
}
```

The output is an object with two properties:

```json
{
  "results": [
    {
      "id": 456,
      "title": "Tiger Facts",
      "url": "https://example.com/tiger-facts/",
      "excerpt": "Both auroch and tiger are remarkable large animals.",
      "relevance": 340.45
    }
  ],
  "total": 161
}
```

`total` is the number of posts matching the search, ignoring `limit` and `offset`. Compare it with the number of results you have already received to decide whether to request another page.

`relevance` is the raw score Better Search calculated for that result. It is meaningful only when comparing results within the same response — scores from two different searches are not comparable. The search results page shows this as a percentage instead.

`title` and `excerpt` are plain text, with markup and HTML entities removed.

Results the current user cannot view are omitted, and the search runs against published content only.

An empty `results` array with a `total` of `0` is a successful response that means nothing matched. It is not an error.

The ability uses Better Search's normal query and saved settings. It does not change the matching rules. If the results look wrong, compare them with the search results on your site and check the [Search settings](https://webberzone.com/support/knowledgebase/better-search-settings-search/).

### Long search phrases

Because search terms are combined with `AND`, a phrase written as a sentence or question usually matches nothing. Rather than return an empty result set — which would suggest your site has no such content — the ability returns an error when a search finds nothing *and* the phrase was longer than a few keywords:

> The search ran and matched nothing, but the phrase was too long to match reliably: it produced 7 search terms, which are combined with AND. Search again with at most 4 distinctive keywords instead of a sentence or question. A zero result here does not mean the site has no content on the subject.

A short phrase that genuinely matches nothing still returns an ordinary empty response, so a connected client can tell the two situations apart.

Developers can change when this applies with two filters:

```php
// Allow up to 8 search terms before reporting a phrase as too long.
add_filter(
	'bsearch_abilities_max_search_terms',
	function () {
		return 8;
	}
);

// Allow up to 15 words.
add_filter(
	'bsearch_abilities_max_search_words',
	function () {
		return 15;
	}
);
```

## Run the ability through the REST API

WordPress also exposes abilities through its REST API. The `search` ability only reads data, so run it with a `GET` request. Pass the ability input in the `input` query parameter:

```bash
curl --get \
  --data-urlencode 'input[query]=tiger facts' \
  --data-urlencode 'input[limit]=5' \
  'https://example.com/wp-json/wp-abilities/v1/abilities/better-search/search/run'
```

Replace `example.com` and the search phrase with your own. The request must be authenticated. The response is the same object shown above.

The HTTP method depends on what the ability does: read-only abilities use `GET`, abilities that clear or delete use `DELETE`, and other writes use `POST`. Using the wrong method returns a `405` response.

This is a different endpoint from the [Better Search REST API integration](https://webberzone.com/support/knowledgebase/better-search-rest-api/), which changes the results of WordPress's own search endpoint. The abilities endpoint is provided by WordPress core under `wp-abilities/v1`.

## Pro abilities

Better Search Pro registers three more abilities.

### Popular search terms

`better-search/get-popular-searches` returns the search terms visitors used most, with a count for each. It requires the `manage_options` capability, because search-term data is treated as sensitive.

```json
{
  "period": "overall",
  "limit": 20
}
```

`period` accepts `overall` for lifetime totals or `daily` for a recent window — not only the current day. The window is the number of days set by **Currently trending should contain searches of how many days?** on the [Heatmap settings](https://webberzone.com/support/knowledgebase/better-search-settings-heatmap/), which defaults to `7`. `limit` defaults to `20` and accepts `1`–`100`.

The output is an array of objects with `term` and `count` properties.

### Suggest a search term

`better-search/suggest-search-term` suggests a corrected spelling for a search phrase, drawn from your site's own content and search history. It is registered only when the ["Did You Mean" spelling suggestions](https://webberzone.com/support/knowledgebase/did-you-mean-spelling-suggestions/) feature is enabled, so a connected client will not see the ability at all when the feature is switched off.

```json
{
  "query": "Enlgish"
}
```

The result contains a `suggestion` property — `"english"` for the example above, or `null` when there is nothing to suggest.

It only answers for a phrase that found nothing. Correcting a phrase that already matches content produces misleading suggestions, so a search with results always returns `null`.

Like the search ability, this one requires the `read` capability rather than `manage_options`, because the same suggestions already appear to visitors on your search results page.

### Clear the cache

`better-search/clear-cache` clears the cached Better Search results for the site. Its input requires `confirm` to be `true`, and it requires the `manage_options` capability.

```json
{
  "confirm": true
}
```

The result contains a `cleared` boolean and a `count` integer holding the number of cache entries removed.

On a multisite network, the ability also clears the shared network cache, but only for a user who can manage network options.

## Visibility and permissions

The plugin registers these abilities as public and makes them visible through the Abilities API. Public visibility lets compatible clients discover the abilities; it does not bypass their permission checks. The search and suggestion abilities require the `read` capability, and the management abilities require `manage_options`.

## See also

- [Better Search REST API integration for search results](https://webberzone.com/support/knowledgebase/better-search-rest-api/)
- ["Did You Mean" Spelling Suggestions in Better Search Pro](https://webberzone.com/support/knowledgebase/did-you-mean-spelling-suggestions/)
- [Better Search Pro CLI Overview](https://webberzone.com/support/knowledgebase/better-search-wp-cli/)
