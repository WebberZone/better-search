---
slug: did-you-mean-spelling-suggestions
title: "\"Did You Mean\" Spelling Suggestions in Better Search Pro"
products: [better-search]
sections: [02-bs-advanced]
tags: [better-search,search,pro]
status: publish
order: 0
---

[kbtoc]

## What is "Did You Mean"?

When a visitor's search returns zero results — often because of a typo or misspelling — "Did You Mean" suggests a corrected search term instead of leaving them with an empty results page. Depending on the mode you choose, Better Search Pro either shows a clickable suggestion link or transparently re-runs the search with the corrected term.

## Why Use It?

- **Recovers dead-end searches**: Turns a "no results found" page into a productive one.
- **Learns from your site**: Suggestions are drawn first from your own search log, so corrections reflect what your visitors actually search for.
- **Falls back gracefully**: If your search log has no close match, Better Search checks your published content, and optionally the server's spellchecker.

This is especially useful for sites with long-tail search traffic, where visitors frequently misspell product names, brands, or uncommon terms.

## How to Enable "Did You Mean"

[Better Search Pro](https://webberzone.com/plugins/better-search/pro/) adds these settings under the **Search** tab:

1. Go to **Better Search → Settings** in the WordPress admin.
2. Under the **Search** tab, check **Enable "Did you mean" suggestions**.
3. Choose a **"Did you mean" mode**:
    - **Suggest ("Did you mean")**: Shows a "Did you mean" link on the zero-results page. The original (empty) results are still displayed.
    - **Auto-correct**: Transparently re-runs the search with the corrected term when it actually returns results, and shows a link back to the original query.
4. Set **Minimum searches to qualify as a suggestion** — a term must have been searched at least this many times in your search log before it can be suggested as a correction. Default: `3`.
5. Optionally, enable **Use pspell/enchant as a fallback** to fall back to the server's pspell/enchant spellchecker when neither your search log nor your site content has a close match. This has no effect if the extension isn't installed on your server (pspell was also removed from PHP entirely in PHP 8.4, so the fallback is unavailable on 8.4+).
6. Save your changes.

## How Suggestions Are Found

Better Search checks each unmatched search term against three corpora, in order, stopping at the first one that finds a match:

1. **Search log corpus**: Terms your visitors have searched at least the number of times set in **Minimum searches to qualify as a suggestion**.
2. **Content-index corpus**: Words drawn from published post titles and public taxonomy term names, kept in a dedicated dictionary table that refreshes automatically twice daily and whenever a post is saved.
3. **pspell/enchant fallback**: The server's spellchecker, if the setting is enabled and the extension is available.

Quoted phrases (`"like this"`) and terms shorter than the **Minimum characters** setting (Search tab, default `4`) are left alone — there's too little signal in a short token to correct safely.

> [!NOTE]
> ⓘ "Did you mean" is automatically skipped for a search that already returned results — it only activates on zero-result searches.

## Theme Integration

If [Seamless Mode](https://webberzone.com/support/knowledgebase/better-search-settings-general/#enable-seamless-integration) is off, Better Search's own [search template](https://webberzone.com/support/knowledgebase/better-search-templates/) already displays "Did you mean" and auto-correct notices automatically — no extra work needed.

If Seamless Mode is on, your theme renders its own `search.php`, so call these template tags directly wherever you want the notice to appear:

### `the_bsearch_did_you_mean()`

Displays a "Did you mean: `<suggested term>`?" link when Suggest mode found a correction but the original search still returned no results.

```php
<?php the_bsearch_did_you_mean(); ?>
```

Accepts an optional `$args` array with `before` and `after` markup, defaulting to `<p class="bsearch-did-you-mean">` / `</p>`. Returns an empty string when there's no suggestion for the current query — safe to call unconditionally. A `get_bsearch_did_you_mean()` variant returns the markup instead of echoing it.

### `the_bsearch_autocorrect_notice()`

Displays a "Showing results for **`<corrected term>`** — search instead for `<original term>`" notice when Auto-correct mode transparently corrected the query.

```php
<?php the_bsearch_autocorrect_notice(); ?>
```

Accepts the same `before` / `after` arguments, defaulting to `<p class="bsearch-autocorrect-notice">` / `</p>`. A `get_bsearch_autocorrect_notice()` variant returns the markup instead of echoing it.

## Developer Filters

- `bsearch_spelling_suggestion` — Filters the final suggestion resolved for a single search token, after the search log, content index, and pspell/enchant fallback have all been tried.
- `bsearch_did_you_mean_min_searches` — Filters the minimum search-log count required for a term to qualify as a suggestion, overriding the **Minimum searches to qualify as a suggestion** setting.
- `bsearch_did_you_mean_pspell_locale` — Filters the locale (default `en`) passed to `pspell_new()` for the pspell/enchant fallback.
- `get_bsearch_did_you_mean` / `get_bsearch_autocorrect_notice` — Filter the generated markup for each notice.

## Important Considerations

- **Zero-result searches only**: Suggestions are only computed when a search returns no results — there's no performance impact on normal searches.
- **Auto-correct is re-run, not guessed**: In Auto-correct mode, the corrected phrase is only adopted if re-running the search with it actually returns results; otherwise Better Search falls back to showing the normal zero-results page.
- **Accepted corrections reinforce your search log**: When Auto-correct mode succeeds, the corrected term is logged as a search, strengthening future suggestions.

## See also

- [Fuzzy Searches in Better Search Pro](https://webberzone.com/support/knowledgebase/fuzzy-matches/)
- [Understanding Better Search Templates](https://webberzone.com/support/knowledgebase/better-search-templates/)
