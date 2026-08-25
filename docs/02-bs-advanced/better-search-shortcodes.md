---
slug: better-search-shortcodes
title: "Better Search Shortcodes"
products: [better-search]
sections: ["02-bs-advanced"]
tags: [better-search, shortcode]
status: publish
---

Better Search provides two shortcodes: `[[bsearch_heatmap]]` and `[[bsearch_form]]`. These are available in both the free and pro versions of the plugin and can display a heatmap of popular searches or embed a customizable search form.

## 1. [bsearch_form]

Better Search can work with any search form on your site. However, it also includes its own advanced search form that allows you to display and pass post types where you’d like to allow users to search only within one or more post types.

This shortcode embeds a customizable Better Search form anywhere on your site.

### **Attributes for bsearch_form**

| **Attribute** | Type | **Description** |
| --- | --- | --- |
| `before` | String | Text or HTML to display before the search form. |
| `after` | String | Text or HTML to display after the search form. |
| `aria_label` | String | ARIA label for the search form (accessibility). |
| `post_types` | String (csv) | Post types to search (comma-separated list). |
| `selected_post_types` | String (csv) | Preselect post types in the dropdown (comma-separated list). |
| `show_post_types` | Boolean | Show a dropdown to select post types (`1` or `0`). |

### **Example Usage**

This example wraps the search form in a custom `div` and includes a dropdown for post-type selection.

```text
[[bsearch_form before="<div class='search-wrapper'>" after="</div>" show_post_types="true"]]
```

## 2. [bsearch_heatmap]

This shortcode generates a heatmap of your site’s popular search terms. Various attributes allow you to customize its appearance and functionality.

### **Attributes for bsearch_heatmap**

| **Attribute** | Type | **Description** |
| --- | --- | --- |
| `daily` | Boolean | Display searches from the past X days (`1` or `0`). |
| `daily_range` | Integer | Number of days for the daily heatmap. |
| `smallest` | Integer | Smallest font size for the heatmap. |
| `largest` | Integer | Largest font size for the heatmap. |
| `unit` | String | Font size unit (`px`, `pt`, etc.). |
| `hot` | String (hex) | Color for the most popular searches. |
| `cold` | String (hex) | Color for the least popular searches. |
| `number` | Integer | Maximum number of terms to display. |
| `before_term` | String | Text or HTML before each search term. |
| `after_term` | String | Text or HTML after each search term. |
| `link_nofollow` | Boolean | Adds `rel="nofollow"` to links (`1` or `0`). |
| `link_new_window` | Boolean | Opens links in a new tab (`1` or `0`). |
| `format` | String | Heatmap format: `flat` (inline), `array` or `list`. |
| `separator` | String | Separator for terms when `format` is `flat`. |
| `orderby` | String | How to order terms: `count` or `name`. |
| `order` | String | Order direction: `ASC`, `DESC` or `RAND`. |
| `topic_count_text` | String | Custom text for displaying term counts. |
| `show_count` | Boolean | Show the count of searches for each term (`1` or `0`). |
| `no_results_text` | String | This text is displayed when there are no search terms available. |

### **Example Usage**

This example shows a heatmap of the top 10 daily searches with custom font sizes and colors.

```text
[[bsearch_heatmap daily="1" number="10" smallest="10" largest="20" hot="#ff0000" cold="#cccccc"]]
```
