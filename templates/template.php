<?php
/**
 * Default template when there is no template in the theme folder
 *
 * @package Better_Search
 */

	/* Set the search query if it is missing */
if ( ! isset( $bsearch_settings ) ) {
	global $bsearch_settings;
}

	$limit = isset( $_GET['limit'] ) ? intval( $_GET['limit'] ) : $bsearch_settings['limit']; // Read from GET variable

	/* Set the search query if it is missing */
if ( ! isset( $search_query ) ) {
	$search_query = get_bsearch_query();
}

	// Get Header
	get_header();

?>

	<div id="content" class="bsearch_results_page">

		<?php echo get_bsearch_form( $search_query ); ?>

		<div id="bsearchresults">
			<h1 class="page-title">
				<?php echo __( 'Search Results for: ', 'better-search' ); ?>
				<span>
					<?php echo $search_query; ?>
				</span>
			</h1>

			<?php echo get_bsearch_results( $search_query, $limit ); ?>
		</div>	<!-- Close id="bsearchresults" -->

		<?php echo get_bsearch_form( $search_query ); ?>

		<?php if ( $bsearch_settings['include_heatmap'] ) : ?>

			<div id="heatmap">
				<div class="heatmap_daily">
					<h2>
						<?php echo strip_tags( $bsearch_settings['title_daily'] ); ?>
					</h2>

					<?php echo get_bsearch_heatmap( 'daily=1' ); ?>
				</div>

				<div class="heatmap_overall">
					<h2>
						<?php echo strip_tags( $bsearch_settings['title'] ); ?>
					</h2>

					<?php echo get_bsearch_heatmap( 'daily=0' ); ?>
				</div>

				<div style="clear:both">&nbsp;</div>
			</div>

		<?php endif;	?>

	</div>	<!-- Close id="content" -->

<?php
	// Get the sidebar
	// get_sidebar();
	// Get the footer
	get_footer();

