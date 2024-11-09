<?php
/**
 * Search Results Block Pattern.
 *
 * @package Better_Search
 */

return array(
	'title'         => _x( 'Better Search Results', 'Block pattern title', 'better-search' ),
	'description'   => __( 'Block pattern to display the search results in a three column grid with meta data, title and excerpt, and post thumbnail.', 'better-search' ),
	'categories'    => array( 'better-search', 'query', 'posts' ),
	'templateTypes' => array( 'search' ),
	'content'       => '
	<!-- wp:template-part {"slug":"header"} /-->

	<!-- wp:group {"tagName":"main","layout":{"type":"constrained"}} -->
	<main class="wp-block-group">
		<!-- wp:group {"align":"wide","layout":{"type":"default"}} -->
		<div class="wp-block-group alignwide">
			<!-- wp:spacer {"height":"var:preset|spacing|80"} -->
			<div style="height:var(--wp--preset--spacing--80)" aria-hidden="true" class="wp-block-spacer"></div>
			<!-- /wp:spacer -->
			<!-- wp:query-title {"type":"search"} /-->
			<!-- wp:pattern {"slug":"better-search/search-form"} /-->
			<!-- wp:spacer {"height":"var:preset|spacing|40"} -->
			<div style="height:var(--wp--preset--spacing--40)" aria-hidden="true" class="wp-block-spacer"></div>
			<!-- /wp:spacer -->
		</div>
		<!-- /wp:group -->
		<!-- wp:group {"align":"wide","layout":{"type":"default"}} -->
		<div class="wp-block-group alignwide">
			<!-- wp:pattern {"slug":"better-search/template-query-loop-news-blog"} /-->
		</div>
		<!-- /wp:group -->
	</main>
	<!-- /wp:group -->

	<!-- wp:template-part {"slug":"footer"} /-->
	',
);
