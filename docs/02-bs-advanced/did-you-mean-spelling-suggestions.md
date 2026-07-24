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
5. Optionally, enable **Use enchant as a fallback** to fall back to the server's enchant spellchecker when neither your search log nor your site content has a close match. The setting shows the extension's detected status and is disabled if enchant isn't installed on your server — see [Installing enchant on your server](#installing-enchant-on-your-server) below.
6. Save your changes.

## How Suggestions Are Found

Better Search checks each unmatched search term against three corpora, in order, stopping at the first one that finds a match:

1. **Search log corpus**: Terms your visitors have searched at least the number of times set in **Minimum searches to qualify as a suggestion**.
2. **Content-index corpus**: Words drawn from published post titles and public taxonomy term names, kept in a dedicated dictionary table that refreshes automatically twice daily and whenever a post is saved.
3. **Enchant fallback**: The server's enchant spellchecker, if the setting is enabled and the extension is available.

Quoted phrases (`"like this"`) and terms shorter than the **Minimum characters** setting (Search tab, default `4`) are left alone — there's too little signal in a short token to correct safely.

> [!NOTE]
> ⓘ "Did you mean" is automatically skipped for a search that already returned results — it only activates on zero-result searches.

## Installing enchant on your server

The enchant fallback needs PHP's `enchant` extension. Most managed WordPress hosts don't enable it, but if you run your own dedicated server or VPS, you can install it yourself.

### Check whether it's already installed

The **Use enchant as a fallback** setting shows a live status message: green if the extension is detected, red if it isn't. You can also check from the command line:

```bash
php -m | grep enchant
```

### Install the extension

Enchant itself is a spellchecking library; PHP's `enchant` extension is a wrapper around it, and it also needs at least one backend dictionary provider (`aspell` or `hunspell`) plus a language dictionary package to have anything to suggest from.

**Ubuntu/Debian**

```bash
sudo apt install php-enchant enchant-2 aspell aspell-en
```

Swap `aspell-en` for the package matching your site's language (e.g. `aspell-fr` for French, `hunspell-de-de` for German).

**RHEL/CentOS/AlmaLinux**

```bash
sudo yum install php-enchant enchant2 aspell aspell-en
```

**macOS (Herd / Homebrew PHP)**

```bash
brew install enchant
pecl install enchant
```

Then add `extension=enchant.so` to your `php.ini` if the install doesn't do it automatically.

After installing, restart PHP-FPM (or Apache/`mod_php`) so the extension loads:

```bash
sudo systemctl restart php8.3-fpm   # match your PHP version
```

Then reload the plugin's **Search** settings tab — the status message should switch to "The enchant extension is installed on this server."

### Choosing a dictionary locale

Enchant looks up a dictionary matching the locale from the `bsearch_did_you_mean_enchant_locale` filter (default `en_US`). If your site's content is in another language, make sure the matching dictionary package is installed (e.g. `aspell-de` for German) and filter the locale to match:

```php
add_filter( 'bsearch_did_you_mean_enchant_locale', fn() => 'de_DE' );
```

If no dictionary exists for the requested locale, the fallback silently returns no suggestion rather than erroring — the search-log and content-index corpora are unaffected either way.

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

- `bsearch_spelling_suggestion` — Filters the final suggestion resolved for a single search token, after the search log, content index, and enchant fallback have all been tried.
- `bsearch_did_you_mean_min_searches` — Filters the minimum search-log count required for a term to qualify as a suggestion, overriding the **Minimum searches to qualify as a suggestion** setting.
- `bsearch_did_you_mean_enchant_locale` — Filters the locale (default `en_US`) used to request a dictionary from enchant for the fallback.
- `get_bsearch_did_you_mean` / `get_bsearch_autocorrect_notice` — Filter the generated markup for each notice.

## Important Considerations

- **Zero-result searches only**: Suggestions are only computed when a search returns no results — there's no performance impact on normal searches.
- **Auto-correct is re-run, not guessed**: In Auto-correct mode, the corrected phrase is only adopted if re-running the search with it actually returns results; otherwise Better Search falls back to showing the normal zero-results page.
- **Accepted corrections reinforce your search log**: When Auto-correct mode succeeds, the corrected term is logged as a search, strengthening future suggestions.

## See also

- [Fuzzy Searches in Better Search Pro](https://webberzone.com/support/knowledgebase/fuzzy-matches/)
- [Understanding Better Search Templates](https://webberzone.com/support/knowledgebase/better-search-templates/)
