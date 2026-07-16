---
slug: wordpress-search-highlighting
title: "WordPress Search Term Highlighting in Better Search"
products: [better-search]
sections: [03-bs-developer-docs]
tags: [better-search,search,highlighting,developer]
status: publish
order: 0
---

[Better Search](https://webberzone.com/plugins/better-search/) can highlight the terms a visitor searched for, both on the search results page and on the post or page they click through to. This makes it easy for visitors to spot why a result matched their query.

## How it works

Highlighting has two settings on the **Output** tab of the Better Search settings page:

- **Highlight search terms** — wraps matching terms in the search results excerpt with the `bsearch_highlight` CSS class.
- **Highlight followed links** — when a visitor clicks through from the search results page to a post or page, the terms they searched for are highlighted there too.

Both settings work together with two different mechanisms depending on where the highlighting needs to happen:

### Server-side highlighting (search results page)

On the search results page itself, Better Search highlights terms server-side. The `Display` class hooks into `the_title`, `the_content`, `get_the_excerpt`, and `the_bsearch_excerpt` and wraps each matched term while the page is being generated. Because this only runs on `is_search()`, it always works — it doesn't depend on the visitor's browser or any caching layer.

### Client-side highlighting (followed links)

"Highlight followed links" is a different problem: by the time a visitor lands on the post or page, the request is often served from a full-page cache (e.g. LiteSpeed Cache), so PHP never runs and the server-side highlighting logic has nothing to hook into.

To handle this, Better Search enqueues a small JavaScript file (`better-search-highlight.js`) on singular posts/pages whenever "Highlight followed links" is enabled. In the browser, the script:

1. Reads `document.referrer` to check whether the visitor arrived from a search on your own site.
2. Extracts the search terms from the referrer URL (supporting both `?s=term` and pretty-permalink `/search/term/` formats).
3. Walks the page's text nodes within the main content area and wraps each matching term in a highlight element.

If the referrer isn't a search on your site, or no search terms can be extracted, the script does nothing — it never adds highlighting based on the current page's own URL.

## Developer reference

The client-side script is deliberately dependency-free (no jQuery, no build step) and mirrors the term-extraction logic used server-side, so the two stay in sync. It reads its configuration from a small `bsearch_highlight` object injected via `wp_localize_script()`, which is populated from four filters.

### Filters

#### `bsearch_highlight_tag`

Filters the HTML tag used to wrap each highlighted term.

```php
add_filter( 'bsearch_highlight_tag', function () {
    return 'strong';
} );
```

Allowed values: `mark`, `span`, `strong`, `em`. Anything else falls back to `mark`. Default: `mark`.

#### `bsearch_highlight_class`

Filters the CSS class applied to each highlight wrapper.

```php
add_filter( 'bsearch_highlight_class', function () {
    return 'my-theme-highlight';
} );
```

The value is passed through `sanitize_html_class()`. Default: `bsearch_highlight`.

#### `bsearch_highlight_max_terms`

Filters the maximum number of search terms the client-side script will highlight.

```php
add_filter( 'bsearch_highlight_max_terms', function () {
    return 10;
} );
```

Default: `50`.

#### `bsearch_highlight_js_selectors`

Filters the CSS selector(s) the script uses to scope where it looks for text to highlight.

```php
add_filter( 'bsearch_highlight_js_selectors', function () {
    return '.entry-content, .entry-title';
} );
```

Default: `.entry-content, .entry-title, .entry-summary`. Use this if your theme wraps post content in different markup — anything not matched by the selector is left untouched.

### When the script is enqueued

`better-search-highlight.js` (or its minified build, unless `SCRIPT_DEBUG` is on) is only enqueued when all of the following are true:

- The request is not in `wp_admin`.
- "Highlight followed links" is enabled in settings.
- The current page is a singular post or page (`is_singular()`).

It is not enqueued on archives, the homepage, or the search results page itself — those are handled server-side.

## See also

- [Better Search Settings – Output](/knowledgebase/better-search-settings-output/)
