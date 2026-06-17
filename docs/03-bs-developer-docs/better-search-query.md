---
slug: better-search-query
title: "Building an Advanced WordPress Search with Better_Search_Query"
products: [better-search]
sections: [03-bs-developer-docs]
tags: [better-search,developer,query]
status: publish
order: 0
---

[Better Search](https://webberzone.com/plugins/better-search/) enhances WordPress’s native search capabilities with powerful features that deliver more relevant results to visitors. At the heart of this functionality is the `Better_Search_Query` class. This guide will help you understand how to leverage this class for optimal search performance on your site.

## What is Better_Search_Query?

The `Better_Search_Query` class extends WordPress’s native `WP_Query` class, significantly improving search relevance and performance. It implements MySQL’s FULLTEXT indexing and provides advanced search operators that help users find relevant search results.

## Implementing Better_Search_Query in Your Theme

### Basic Implementation

To use Better_Search_Query in your theme, you can create a custom search template:

```php
<?php
/**
 * Custom search template using Better_Search_Query
 *
 * @package YourTheme
 */

get_header();

// Parameters for Better_Search_Query.
$args = array(
    's'              => get_search_query(),
    'post_type'      => 'post',
    'posts_per_page' => 10,
);

// Create a new Better_Search_Query instance.
$better_search_query = new Better_Search_Query( $args );

// Get the search results.
$search_results = $better_search_query->get_posts();

if ( $search_results ) :
    echo '<div class="search-results">';
    
    foreach ( $search_results as $post ) :
        setup_postdata( $post );
        ?>
        <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
            <header class="entry-header">
                <h2 class="entry-title">
                    <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                </h2>
            </header>
            
            <div class="entry-summary">
                <?php the_excerpt(); ?>
            </div>
            
            <div class="search-relevance">
                <?php echo esc_html( $post->score ); ?> relevance
            </div>
        </article>
        <?php
    endforeach;
    
    wp_reset_postdata();
    echo '</div>';
else :
    ?>
    <p><?php esc_html_e( 'No results found. Please try different search terms.', 'your-theme-textdomain' ); ?></p>
    <?php
endif;

get_footer();
```

### Advanced Configuration

For more control, you can configure `Better_Search_Query` with additional parameters. You can also post most <a href="https://developer.wordpress.org/reference/classes/wp_query/#parameters" target="_blank" rel="noreferrer noopener">WP_Query parameters</a>.

```php
<?php
// Advanced parameters for Better_Search_Query.
$args = array(
    's'                      => get_search_query(), // This tells WP_Query that it is a search result.
    'post_type'              => array( 'post', 'page', 'product' ),
    'posts_per_page'         => 20,
    'orderby'                => 'date', // Order by date instead of relevance.
    'order'                  => 'DESC', // Descending order of dates i.e. newest first.
    'boolean_mode'           => true, // Enable Boolean mode.
);

$better_search_query = new Better_Search_Query( $args );
```

## Advanced Features

### Using Boolean Operators

Better Search supports MySQL’s Boolean mode for complex searches:

- `+word`: Word must be present
- `-word`: Word must not be present
- `>word`: Increases relevance of word
- `<word`: Decreases relevance of word
- `*`: Wildcard for partial matching
- `"phrase"`: Exact phrase matching

## Troubleshooting

### No Results Found

If your searches return no results, check these common issues:

1.  **Query Errors**: [Check this article](https://webberzone.com/support/knowledgebase/debugging-with-query-monitor/) to understand how to identify the Better Search Query.
2.  **FULLTEXT Indexing**: Ensure your database tables have FULLTEXT indexes enabled. You can recreate these in the Tools page.
3.  **Post Types**: Verify that the post types you’re searching for are included in your query parameters.

### Performance Optimization

For large sites, consider these performance tips:

1.  **Limit Search Fields**: Include only the necessary fields.
2.  **Cache Results**: Implement a caching mechanism for search results. WP_Query now supports caching via the object cache.
3.  **Paginate Results**: Use pagination to limit the number of results per page.

## Conclusion

The `Better_Search_Query` class provides a robust framework for enhancing search functionality on your WordPress site. By understanding its features and implementing appropriate configurations, you can deliver highly relevant search results to your users.

For more information and support, visit the [Better Search documentation](https://webberzone.com/support/product/better-search/) or contact us should you have any questions.
