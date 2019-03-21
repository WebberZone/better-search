<?php
/**
 * The template for displaying Search Results pages.
 *
 * @package WordPress
 * @subpackage Twenty_Eleven
 * @since Twenty Eleven 1.0
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

$search_query = bsearch_clean_terms( apply_filters( 'the_search_query', get_search_query() ) );
get_header(); ?>

	<section id="primary">
		<div id="content" role="main">
			<?php
			$form = get_bsearch_form( $search_query );
			echo $form;
			?>
			<header class="page-header">
				<h1 class="page-title">
					<?php printf( __( 'Better Search TEMPLATE Search Results for: %s', 'twentyeleven' ), '<span>' . get_search_query() . '</span>' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</h1>
			</header>

			<?php echo get_bsearch_results( $search_query, $limit ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<?php echo $form; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</div><!-- #content -->
	</section><!-- #primary -->

<?php
get_sidebar();
get_footer();
