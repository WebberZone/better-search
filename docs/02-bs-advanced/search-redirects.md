---
slug: search-redirects
title: "Search Redirects in Better Search Pro"
products: [better-search]
sections: [02-bs-advanced]
tags: [better-search, search, pro]
status: publish
order: 0
---

[kbtoc]

Search redirects, introduced in [Better Search Pro](https://webberzone.com/plugins/better-search/pro/) 4.4.0, send visitors straight to a specific post, page or URL when they search for a matching keyword — before the search results page loads. Redirected searches are still recorded in your search statistics.

## Enabling search redirects

1. Go to **Better Search → Settings** in the WordPress admin.
2. Open the **Features** tab and turn on **Search redirects**.
3. Save your changes. The **Redirects** tab will appear.
4. Open the **Redirects** tab and add your rules.

## Adding a redirect rule

Click **Add redirect** and fill in these fields:

- **Keywords** — comma-separated list of search terms that trigger this redirect (e.g. `support, help, contact us`).
- **Send visitors to this post or page** — enter the ID of the post or page. The post must be published and not password protected. Leave this blank to use a URL instead.
- **Or send them to this URL** — a full address such as `https://example.com/help/`, or a path on this site such as `/contact/`. Only used when the post or page field is blank.
- **Match type** — **Exact match** matches the whole search phrase. **Contains** matches when the keyword appears anywhere in the phrase.
- **Redirect type** — **302 (Temporary)** or **301 (Permanent)**. Use 302 unless you are certain the rule is permanent — browsers cache 301 redirects aggressively.
- **Enabled** — uncheck to switch a rule off without deleting it.

You can add as many rules as you need. Rules are checked in order, with exact matches always taking priority over "contains" matches. Matching ignores case, leading/trailing spaces, and repeated spaces.

## How matching works

When a visitor performs a search, Better Search Pro checks every redirect rule before the search runs. Matching respects these behaviors:

- **Exact rules first**: all exact-match rules are tried before any "contains" rule, regardless of list order.
- **First match wins**: when multiple rules could match, the first one in the list takes effect. Reorder rules so more specific matches are higher up.
- **Case and whitespace**: matching is case-insensitive and collapses repeated spaces. Leading and trailing spaces are ignored.
- **Search statistics**: even when redirected, the original search query is recorded so your search statistics and "Did you mean" suggestions stay accurate.

### Bypassing redirects for testing

Append `?bsearch_no_redirect=1` to any search URL to see the results page instead of following the redirect. This only works for administrators.

## Developer hooks

- [`bsearch_before_search_redirect`](https://webberzone.dev/better-search/hooks/bsearch_before_search_redirect/) — fires before a search query is redirected. Receives the destination URL, matched rule array, and search query.
- [`bsearch_search_redirects`](https://webberzone.dev/better-search/hooks/bsearch_search_redirects/) — filters the array of redirect rules before matching.
- [`bsearch_is_redirectable_search`](https://webberzone.dev/better-search/hooks/bsearch_is_redirectable_search/) — filters whether the current request may be redirected by search rules. Return `false` to skip redirection for the current request.
- [`bsearch_allow_offsite_redirect`](https://webberzone.dev/better-search/hooks/bsearch_allow_offsite_redirect/) — filters whether a redirect may send visitors off-site. Default `true`. Return `false` to restrict redirects to the current site only.

## See also

- [Better Search Feature Manager](https://webberzone.com/support/knowledgebase/better-search-feature-manager/)
- [`bsearch_before_search_redirect`](https://webberzone.dev/better-search/hooks/bsearch_before_search_redirect/)
- [`bsearch_search_redirects`](https://webberzone.dev/better-search/hooks/bsearch_search_redirects/)
- [`bsearch_is_redirectable_search`](https://webberzone.dev/better-search/hooks/bsearch_is_redirectable_search/)
- [`bsearch_allow_offsite_redirect`](https://webberzone.dev/better-search/hooks/bsearch_allow_offsite_redirect/)
