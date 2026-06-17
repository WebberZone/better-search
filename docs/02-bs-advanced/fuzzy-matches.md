---
slug: fuzzy-matches
title: "Fuzzy Searches in Better Search Pro"
products: [better-search]
sections: [02-bs-advanced]
tags: [better-search,search]
status: publish
order: 0
---

## **What is Fuzzy Search?**

Fuzzy search helps users find results even if their query contains typos, misspellings, or partial matches. By loosening the search criteria, fuzzy search improves accuracy and user satisfaction, especially for sites with a diverse audience or complex search terms.

## **Why Use Fuzzy Search?**

- **Handles errors**: Captures results for common misspellings or typos.
- **Improves flexibility**: Matches variations of terms (e.g., “color” and “color”).
- **Boosts engagement**: Users find what they need even with imperfect queries.

This is ideal for e-commerce sites, educational resources, or content-heavy platforms where users might not always type exact keywords.

## **How to Enable Fuzzy Search**

[Better Search Pro](https://webberzone.com/plugins/better-search/pro/) includes a setting to enable and customize fuzzy search levels:

1.  Go to **Better Search → Settings** in the WordPress admin.
2.  Under the **Search** tab, locate the setting **Fuzzy search level**.
3.  Select the desired level of weightage that fuzzy matches add to the Better Search algorithm:
    - **Off**: Disables fuzzy search.
    - **Low**: Slightly relaxed matching criteria.
    - **Medium**: Moderately relaxed criteria for broader matches.
    - **High**: Most flexible, fuzzy searches are given the highest weightage.
4.  If this is the first time that you’ve enabled this feature, then hit the button **‘Create fuzzy search indexes’** to add the necessary functions to your WordPress database. Without these, the fuzzy searching will fail!
5.  Save your changes.

## **Important Considerations**

- **Searches titles only**: To maintain speed and performance, the fuzzy search only checks the words against the post title.
- **Resource-intensive**: Fuzzy search can be computationally intensive, especially at higher levels. On high-traffic sites, this could impact performance.
- **Enable caching**: To mitigate load, ensure caching is enabled on your server, via a WordPress plugin, or through Better Search’s built-in options.
- **Note on Boolean Operators**: Fuzzy search is automatically disabled when your query contains Boolean operators (`+`, `-`, `~`, `>`, `<`, or `*`) and BOOLEAN mode is turned on. These operators express explicit search intent — for example, `+wordpress +plugin` requires both terms. Enabling fuzzy/phonetic matching would undermine that specificity, so fuzzy search doesn’t apply to such queries.

## Fuzzy Match Threshold (Custom Tables)

When fuzzy search is enabled with <a href="https://webberzone.com/support/knowledgebase/efficient-content-storage-and-indexing/" data-type="wz_knowledgebase" data-id="8902">Custom Tables</a>, Better Search applies a minimum fuzzy similarity threshold in the SQL WHERE clause:

```text
wz_phrase_similarity_soundex(title, <term>) * <multiplier> >= <threshold>
```

This gate controls recall vs precision:

- Lower threshold: more fuzzy results (higher recall, more noise).
- Higher threshold: fewer fuzzy results (lower noise, risk of missing valid misspellings).

Current default threshold logic:

```text
max(0.5, fuzzy_multiplier * 0.15)
```

Where fuzzy_multiplier depends on fuzzy level:

- Low: 2.0
- Medium: 5.0
- High: 10.0

This avoids overly permissive matching (\> 0) while retaining common typo matches (for example, spagheti matching spaghetti).

Developers can override the threshold with: `better_search_custom_tables_fuzzy_where_threshold`
