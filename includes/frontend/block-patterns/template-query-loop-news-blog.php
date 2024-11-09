<?php
/**
 * News blog query loop template block pattern.
 *
 * @package Better_Search
 */

return array(
	'title'       => _x( 'Better Search - News blog query loop', 'Block pattern title', 'better-search' ),
	'description' => __( 'Query Loop template to display search results in three columns grid with meta data, title and excerpt, and post thumbnail.', 'better-search' ),
	'categories'  => array( 'better-search', 'query', 'posts' ),
	'content'     => '
<!-- wp:query {"query":{"better_search_query":true,"perPage":10,"pages":0,"offset":0,"postType":"post","order":"desc","orderBy":"date","author":"","search":"","exclude":[],"sticky":"","inherit":true,"taxQuery":null,"parents":[]}} -->
<div class="wp-block-query">
    <!-- wp:post-template -->
    <!-- wp:columns {"style":{"spacing":{"blockGap":{"top":"var:preset|spacing|50","left":"var:preset|spacing|50"},"padding":{"top":"var:preset|spacing|50","bottom":"var:preset|spacing|50"}},"border":{"top":{"color":"var:preset|color|accent-6","width":"1px"}}}} -->
    <div class="wp-block-columns" style="border-top-color:var(--wp--preset--color--accent-6);border-top-width:1px;padding-top:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--50)">
        <!-- wp:column {"width":"20%"} -->
        <div class="wp-block-column" style="flex-basis:20%">
            <!-- wp:post-date {"isLink":true} /-->
        </div>
        <!-- /wp:column -->

        <!-- wp:column -->
        <div class="wp-block-column">
            <!-- wp:post-title {"isLink":true} /-->

            <!-- wp:post-terms {"term":"category","style":{"typography":{"textTransform":"uppercase","letterSpacing":"1.4px"}}} /-->

            <!-- wp:post-excerpt {"showMoreOnNewLine":false,"fontSize":"medium"} /-->

            <!-- wp:group {"style":{"spacing":{"blockGap":"0.12em"}},"layout":{"type":"flex","flexWrap":"nowrap"}} -->
            <div class="wp-block-group">
                <!-- wp:paragraph {"style":{"elements":{"link":{"color":{"text":"var:preset|color|accent-4"}}}},"textColor":"accent-4","fontSize":"small"} -->
                <p class="has-accent-4-color has-text-color has-link-color has-small-font-size">' . esc_html_x( 'Written by', 'Prefix before the author name. The post author name is displayed in a separate block.', 'better-search' ) . '</p>
                <!-- /wp:paragraph -->
                <!-- wp:post-author-name {"isLink":true,"fontSize":"small"} /-->
            </div>
            <!-- /wp:group -->
        </div>
        <!-- /wp:column -->

        <!-- wp:column {"width":"20%"} -->
        <div class="wp-block-column" style="flex-basis:20%">
            <!-- wp:post-featured-image {"aspectRatio":"1"} /-->
        </div>
        <!-- /wp:column -->
    </div>
    <!-- /wp:columns -->
    <!-- /wp:post-template -->

    <!-- wp:group {"style":{"spacing":{"padding":{"top":"var:preset|spacing|40","bottom":"var:preset|spacing|40"}}},"layout":{"type":"default"}} -->
    <div class="wp-block-group" style="padding-top:var(--wp--preset--spacing--40);padding-bottom:var(--wp--preset--spacing--40)">
        <!-- wp:query-pagination {"paginationArrow":"arrow","layout":{"type":"flex","justifyContent":"space-between"}} -->
        <!-- wp:query-pagination-previous {"label":"' . esc_html__( 'Previous', 'better-search' ) . '"} /-->

        <!-- wp:query-pagination-numbers /-->

        <!-- wp:query-pagination-next {"label":"' . esc_html__( 'Next', 'better-search' ) . '"} /-->
        <!-- /wp:query-pagination -->
    </div>
    <!-- /wp:group -->

    <!-- wp:query-no-results -->
    <!-- wp:paragraph -->
    <p>' . esc_html_x( 'Sorry, but nothing was found. Please try a search with different keywords.', 'Message explaining that there are no results returned from a search.', 'better-search' ) . '</p>
    <!-- /wp:paragraph -->
    <!-- /wp:query-no-results -->

    <!-- wp:spacer {"height":"var:preset|spacing|70"} -->
    <div style="height:var(--wp--preset--spacing--70)" aria-hidden="true" class="wp-block-spacer"></div>
    <!-- /wp:spacer -->
</div>
<!-- /wp:query -->
    ',
);
