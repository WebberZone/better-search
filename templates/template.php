<?php
/**
 * Default template when there is no template in the theme folder
 *
 * @package Better_Search
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/* Set the search query if it is missing */
if ( ! isset( $bsearch_settings ) ) {
	global $bsearch_settings;
}

$limit = isset( $_GET['limit'] ) ? intval( $_GET['limit'] ) : $bsearch_settings['limit'];  // phpcs:ignore WordPress.Security.NonceVerification.Recommended

/* Set the search query if it is missing */
if ( ! isset( $search_query ) ) {
	$search_query = get_bsearch_query();
}

// Get Header.
get_header();

?>

	<div id="content" class="bsearch_results_page">

		<?php echo get_bsearch_form( $search_query ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

		<div id="bsearchresults">
			<h1 class="page-title">
				<?php echo __( 'Search Results for: ', 'better-search' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<span>
					<?php echo esc_html( $search_query ); ?>
				</span>
			</h1>

			<?php echo get_bsearch_results( $search_query, $limit ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</div>	<!-- Close id="bsearchresults" -->

		<?php echo get_bsearch_form( $search_query ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

		<?php if ( $bsearch_settings['include_heatmap'] ) : ?>

			<div id="heatmap">
				<div class="heatmap_daily">
					<h2>
						<?php echo wp_strip_all_tags( $bsearch_settings['title_daily'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</h2>

					<?php echo get_bsearch_heatmap( 'daily=1' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</div>

				<div class="heatmap_overall">
					<h2>
						<?php echo wp_strip_all_tags( $bsearch_settings['title'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</h2>

					<?php echo get_bsearch_heatmap( 'daily=0' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</div>

				<div style="clear:both">&nbsp;</div>
			</div>

		<?php endif; ?>

	</div>	<!-- Close id="content" -->

<?php
	// Get the sidebar.
	// get_sidebar();
	// Get the footer.
	get_footer();

