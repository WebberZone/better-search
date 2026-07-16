---
slug: customising-the-heatmap-popular-searches-widget
title: "Customizing the Heatmap and Popular Searches Widget"
products: [better-search]
sections: [02-bs-advanced]
tags: [better-search,customization]
status: publish
order: 0
---

The **Popular Searches \[Better Search\]** widget displays a heatmap of searches made on your site. More popular terms appear larger and use the “hot” color, while less popular terms appear smaller and use the “cold” color.

Each term links to the search results page for that query.

## Add the widget

1. Go to **Appearance \> Widgets**.
2. Add **the “Popular Searches \[Better Search\]”** to a widget area.
3. Enter a widget title.
4. Choose **Overall** or **Custom time period**.
5. If you choose **Custom time period**, enter the number of days to include.

For example, `7` shows searches from the last seven days.

## Change the default heatmap settings

Go to **Better Search \> Settings**, then click the **Heatmap** tab.

The main heatmap settings are:

- Number of search terms to display, defaults to 20
- Font size of the least popular search term defaults to 10pt
- Font size of most popular search term, defaults to 20pt
- Color of the least popular search term, defaults to `#cccccc`
- Color of the most popular search term, defaults to `#000000`
- Text to include before each search term
- Text to include after each search term
- Open links in a new window
- Add nofollow to links

These settings apply to the widget and shortcode unless overridden with code.

## Use the shortcode

Display the heatmap in a post or page:

``` php
[bsearch_heatmap]
```

Display searches from the last seven days:

``` php
[bsearch_heatmap daily="1" daily_range="7"]
```

Customize the number, size, and colors:

``` php
[bsearch_heatmap number="25" smallest="12" largest="28" cold="#999999" hot="#111111"]
```

Common attributes:

``` php
daily="0"
daily_range="7"
number="20"
smallest="10"
largest="20"
unit="pt"
cold="#cccccc"
hot="#000000"
show_count="0"
orderby="count"
order="RAND"
```

## Customize the widget with code

Use the `widget_bsearch_heatmap_args` filter to override the widget output:

``` php
add_filter(
 'widget_bsearch_heatmap_args',
 function ( $args, $instance ) {
  $args['number']     = 30;
  $args['smallest']   = 12;
  $args['largest']    = 24;
  $args['unit']       = 'px';
  $args['cold']       = '#777777';
  $args['hot']        = '#111111';
  $args['show_count'] = 1;
  $args['orderby']    = 'count';
  $args['order']      = 'DESC';

  return $args;
 },
 10,
 2
);
```

## Style the heatmap

Each search term uses the `bsearch_heatmap_link` class. The widget wrapper uses `widget_bsearch_pop`.

``` php
.widget_bsearch_pop .bsearch_heatmap_link {
 display: inline-block;
 margin: 0 0.35em 0.35em 0;
 line-height: 1.4;
 text-decoration: none;
}

.widget_bsearch_pop .bsearch_heatmap_link:hover {
 text-decoration: underline;
}
```

If counts are enabled with `show_count`, use:

``` php
.widget_bsearch_pop .bsearch_heatmap_link_count {
 font-size: 0.85em;
 opacity: 0.75;
}
```
