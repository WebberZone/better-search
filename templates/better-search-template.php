<?php
/**
 * The template for displaying Search Results pages.
 *
 * @package WordPress
 * @subpackage Twenty_Eleven
 * @since Twenty Eleven 1.0
 */

$s = bsearch_clean_terms( apply_filters( 'the_search_query', get_search_query() ) );
get_header(); ?>

	<section id="primary">
		<div id="content" role="main">
			<?php $form = get_bsearch_form( $s );
			echo $form;	?>
			<header class="page-header">
				<h1 class="page-title"><?php printf( __( 'BS TEMPLATE Search Results for: %s', 'twentyeleven' ), '<span>' . get_search_query() . '</span>' ); ?></h1>
			</header>

			<?php echo get_bsearch_results( $s, $limit ); ?>
			<?php echo $form;	?>
		</div><!-- #content -->
	</section><!-- #primary -->

<?php
get_sidebar();
get_footer();
