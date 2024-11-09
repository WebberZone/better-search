<?php
/**
 * Search Form Block Pattern.
 *
 * @package Better_Search
 */

return array(
	'title'       => _x( 'Better Search Form', 'Block pattern title', 'better-search' ),
	'description' => __( 'Display the search results in a three column grid with meta data, title and excerpt, and post thumbnail.', 'better-search' ),
	'categories'  => array( 'better-search', 'query', 'posts' ),
	'content'     => '
        <!-- wp:search {
            "label":"' . esc_html_x( 'Search', 'Search form label.', 'better-search' ) . '",
            "showLabel":false,
            "placeholder":"' . esc_attr_x( 'Type here...', 'Search input field placeholder text.', 'better-search' ) . '",
            "buttonText":"' . esc_attr_x( 'Search', 'Button text. Verb.', 'better-search' ) . '"
        } /-->
    ',
);
