---
slug: multilingual-search
title: "Multilingual Search with WPML, Polylang and TranslatePress"
products: [better-search]
sections: ["02-bs-advanced"]
tags: [better-search, multilingual]
status: publish
order: 0
toc: true
---

[toc]

[Better Search](https://webberzone.com/plugins/better-search/) works with WPML, Polylang, and TranslatePress to return results in the visitor's active language. It resolves each result to its translation where one exists and keeps its caches separate per language, so results from one language are not shown in another.

## How Better Search handles languages

The language logic lives in one class, `Language_Handler`, in `includes/frontend/class-language-handler.php`. It ships in the free plugin and runs on every front-end search.

- The `better_search_query_the_posts` filter runs `translate_ids()`, which maps each result to its translation for WPML and Polylang.
- `get_cache_language()` adds a language component to Better Search cache keys.
- TranslatePress support detects the active language and translates live search output, because TranslatePress translates text at render time instead of creating separate posts for each language.

You do not need any code for a standard WPML, Polylang, or TranslatePress setup. Detection and translation happen automatically.

## WPML

Better Search detects WPML through the `SitePress` class. For each result it calls `wpml_object_id`, passing the original post ID, the post type, a fallback flag, and the current language from `wpml_current_language`. When a translation exists, Better Search swaps the result for the translated post.

Two filters customize the WPML lookup:

- `bsearch_wpml_return_original` — controls whether the original post is returned when a translation is missing. Default: `false`.
- `bsearch_object_id_cur_lang` — filters the post object resolved for the current language.

## Polylang

With Polylang, Better Search detects `pll_get_post()` and uses it to fetch each result's translation in the current language. No configuration is required.

If you use Pro's multisite search together with Polylang, configure Polylang identically on every site in the network.

## TranslatePress

TranslatePress translates your site at render time instead of maintaining separate posts for each language, so Better Search does not swap post IDs for TranslatePress. It detects the active language, keeps cached results separate per language, and translates the titles and URLs it outputs. TranslatePress does not create a separate search index of translated text; it translates the displayed results with the rest of the page.

Better Search recognizes TranslatePress when both `trp_translate()` and the `TRP_Translate_Press` class are available. The active front-end language comes from `get_trp_current_language()`, which reads TranslatePress's `$TRP_LANGUAGE` global and returns an empty string in the default language.

Search-term highlighting splits titles with markup, which can stop TranslatePress from translating the complete title. If translated titles remain in the default language, turn off **Highlight search terms** on the **Output** tab of the Better Search settings page.

## Language-isolated caches

Better Search adds the active language to its cache keys through `get_cache_language()`. It checks TranslatePress first, then Polylang (`pll_current_language( 'locale' )`), then WPML (`wpml_current_language`). The result is included in the internal output cache key, so cached results from one language are not reused in another.

Use the `bsearch_cache_language` filter to supply a language key when your setup uses custom language detection that Better Search does not recognize:

```php
// Provide a language key for a custom multilingual setup.
add_filter( 'bsearch_cache_language', function ( $language ) {
    if ( '' === $language && function_exists( 'my_custom_language' ) ) {
        return my_custom_language();
    }
    return $language;
} );
```

## Live search in translated languages

On TranslatePress sites, live search requests carry the active language. The AJAX handler resolves it with `get_trp_ajax_language()`, which reads the `lang` POST field, resolves a language URL slug to its locale, or falls back to the referring URL. The language is included in the response cache key, and when a non-default language is active, Better Search translates each result title with `trp_translate()` and converts each result URL with TranslatePress's URL converter before sending the response.

Override the resolved language with the `bsearch_trp_ajax_language` filter:

```php
// Force live search to translate results into a specific language.
add_filter( 'bsearch_trp_ajax_language', function () {
    return 'de_DE';
} );
```

## See also

- [Enable AJAX Live Search](https://webberzone.com/support/knowledgebase/enable-live-search/)
- [WordPress Search Term Highlighting in Better Search](https://webberzone.com/support/knowledgebase/wordpress-search-highlighting/)
