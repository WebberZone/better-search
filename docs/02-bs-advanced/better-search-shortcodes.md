---
slug: better-search-shortcodes
title: "Better Search Shortcodes"
products: [better-search]
sections: [02-bs-advanced]
tags: [better-search,shortcode]
status: publish
order: 0
---

Better Search provides two shortcodes: `[bsearch_heatmap]` and `[bsearch_form]`. These are available in both the free and pro versions of the plugin and can display a heatmap of popular searches or embed a customizable search form.

## 1. \[bsearch_form\]

Better Search can work with any search form on your site. However, it also includes its own advanced search form that allows you to display and pass post types where you’d like to allow users to search only within one or more post types.

This shortcode embeds a customizable Better Search form anywhere on your site.

### **Attributes for bsearch_form**

<figure class="wp-block-table">
<table class="has-fixed-layout">
<thead>
<tr>
<th><strong>Attribute</strong></th>
<th>Type</th>
<th><strong>Description</strong></th>
</tr>
</thead>
<tbody>
<tr>
<td><code>before</code></td>
<td>String</td>
<td>Text or HTML to display before the search form.</td>
</tr>
<tr>
<td><code>after</code></td>
<td>String</td>
<td>Text or HTML to display after the search form.</td>
</tr>
<tr>
<td><code>aria_label</code></td>
<td>String</td>
<td>ARIA label for the search form (accessibility).</td>
</tr>
<tr>
<td><code>post_types</code></td>
<td>String (csv)</td>
<td>Post types to search (comma-separated list).</td>
</tr>
<tr>
<td><code>selected_post_types</code></td>
<td>String (csv)</td>
<td>Preselect post types in the dropdown (comma-separated list).</td>
</tr>
<tr>
<td><code>show_post_types</code></td>
<td>Boolean</td>
<td>Show a dropdown to select post types (<code>1</code> or <code>0</code>).</td>
</tr>
</tbody>
</table>
</figure>

### **Example Usage**

This example wraps the search form in a custom `div` and includes a dropdown for post-type selection.

```text
[bsearch_form before="<div class='search-wrapper'>" after="</div>" show_post_types="true"]
```

## 2. \[bsearch_heatmap\]

This shortcode generates a heatmap of your site’s popular search terms. Various attributes allow you to customize its appearance and functionality.

### **Attributes for bsearch_heatmap**

<figure class="wp-block-table">
<table class="has-fixed-layout">
<thead>
<tr>
<th><strong>Attribute</strong></th>
<th>Type</th>
<th><strong>Description</strong></th>
</tr>
</thead>
<tbody>
<tr>
<td><code>daily</code></td>
<td>Boolean</td>
<td>Display searches from the past X days (<code>1</code> or <code>0</code>).</td>
</tr>
<tr>
<td><code>daily_range</code></td>
<td>Integer</td>
<td>Number of days for the daily heatmap.</td>
</tr>
<tr>
<td><code>smallest</code></td>
<td>Integer</td>
<td>Smallest font size for the heatmap.</td>
</tr>
<tr>
<td><code>largest</code></td>
<td>Integer</td>
<td>Largest font size for the heatmap.</td>
</tr>
<tr>
<td><code>unit</code></td>
<td>String</td>
<td>Font size unit (<code>px</code>, <code>pt</code>, etc.).</td>
</tr>
<tr>
<td><code>hot</code></td>
<td>String (hex)</td>
<td>Color for the most popular searches.</td>
</tr>
<tr>
<td><code>cold</code></td>
<td>String (hex)</td>
<td>Color for the least popular searches.</td>
</tr>
<tr>
<td><code>number</code></td>
<td>Integer</td>
<td>Maximum number of terms to display.</td>
</tr>
<tr>
<td><code>before_term</code></td>
<td>String</td>
<td>Text or HTML before each search term.</td>
</tr>
<tr>
<td><code>after_term</code></td>
<td>String</td>
<td>Text or HTML after each search term.</td>
</tr>
<tr>
<td><code>link_nofollow</code></td>
<td>Boolean</td>
<td>Adds <code>rel="nofollow"</code> to links (<code>1</code> or <code>0</code>).</td>
</tr>
<tr>
<td><code>link_new_window</code></td>
<td>Boolean</td>
<td>Opens links in a new tab (<code>1</code> or <code>0</code>).</td>
</tr>
<tr>
<td><code>format</code></td>
<td>String</td>
<td>Heatmap format: <code>flat</code> (inline), <code>array</code> or <code>list</code>.</td>
</tr>
<tr>
<td><code>separator</code></td>
<td>String</td>
<td>Separator for terms when <code>format</code> is <code>flat</code>.</td>
</tr>
<tr>
<td><code>orderby</code></td>
<td>String</td>
<td>How to order terms: <code>count</code> or <code>name</code>.</td>
</tr>
<tr>
<td><code>order</code></td>
<td>String</td>
<td>Order direction: <code>ASC</code>, <code>DESC</code> or <code>RAND</code>.</td>
</tr>
<tr>
<td><code>topic_count_text</code></td>
<td>String</td>
<td>Custom text for displaying term counts.</td>
</tr>
<tr>
<td><code>show_count</code></td>
<td>Boolean</td>
<td>Show the count of searches for each term (<code>1</code> or <code>0</code>).</td>
</tr>
<tr>
<td><code>no_results_text</code></td>
<td>String</td>
<td>This text is displayed when there are no search terms available.</td>
</tr>
</tbody>
</table>
</figure>

### **Example Usage**

This example shows a heatmap of the top 10 daily searches with custom font sizes and colors.

```text
[bsearch_heatmap daily="1" number="10" smallest="10" largest="20" hot="#ff0000" cold="#cccccc"]
```
