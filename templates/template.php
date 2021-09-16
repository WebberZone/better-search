<?php
/**
 * Default search template when there is no template in the theme folder
 *
 * @package Better_Search
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Initialise some variables.
$bsearch_settings = bsearch_get_settings();
$search_query     = get_bsearch_query();
$limit            = isset( $_GET['limit'] ) ? absint( $_GET['limit'] ) : $bsearch_settings['limit'];  // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$bydate           = isset( $_GET['bydate'] ) ? absint( $_GET['bydate'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$paged            = (int) get_query_var( 'paged', 1 ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited

// Reset wp_query temporary.
$tmp_wpquery = $wp_query;
$wp_query    = null; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited

// Set up Better_Search_Query to replace $wp_query.
$args           = array(
	's'      => $search_query,
	'limit'  => $limit,
	'paged'  => $paged,
	'bydate' => $bydate,
);
$search_results = new Better_Search_Query( $args );
$wp_query       = $search_results; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
$topscore       = $wp_query->topscore;

// Get Header.
get_header();

?>

	<?php do_action( 'bsearch_before_content' ); ?>

	<div id="content" class="bsearch_results_page">

		<?php echo get_bsearch_form( $search_query ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

		<div id="bsearchresults">
			<?php do_action( 'bsearch_before_page_title' ); ?>
			<h1 class="page-title">
				<?php
				printf(
					/* translators: %s: Search term. */
					esc_html__( 'Search results for "%s"', 'better-search' ),
					'<span class="page-description search-term">' . esc_html( $search_query ) . '</span>'
				);
				?>
			</h1>
			<?php do_action( 'bsearch_after_page_title' ); ?>

			<?php
			// Loop through the results.
			if ( have_posts() ) {

				the_bsearch_header();

				while ( have_posts() ) :
					the_post();
					?>

					<?php do_action( 'bsearch_before_article' ); ?>

					<article id="post-<?php the_ID(); ?>" <?php post_class( 'bsearch-post' ); ?>>

						<?php
						if ( bsearch_get_option( 'include_thumb' ) ) :
							?>
							<div class="thumbnail bsearch_thumb_wrapper">
								<a href="<?php the_permalink(); ?>" class="thumbnail-link">
									<?php
										// Display post thumbnail.
										the_bsearch_post_thumbnail(
											'post-thumbnail',
											array(
												'post' => $post,
											)
										);
									?>
								</a>
							</div><!-- .thumbnail -->
						<?php endif; ?>

						<?php do_action( 'bsearch_before_entry_header' ); ?>

						<header class="entry-header bsearch-entry-header">

							<?php do_action( 'bsearch_before_entry_header_inner' ); ?>

							<h2 class="search-entry-title entry-title bsearch-entry-title">
								<a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>" rel="bookmark"><?php the_title(); ?></a>
							</h2>

							<ul class="bsearch_post_meta">
								<?php do_action( 'bsearch_before_post_meta' ); ?>
								<?php if ( isset( $post->score ) ) : ?>
								<li class="meta-relevance">
									<?php
									the_bsearch_score(
										array(
											'score'    => $post->score,
											'topscore' => $topscore,
										)
									);
									?>
								</li>
								<?php endif; ?>
								<li class="meta-author"><?php esc_html_e( 'Post author:', 'better-search' ); ?> <?php the_author_posts_link(); ?></li>
								<li class="meta-date"><?php esc_html_e( 'Published date:', 'better-search' ); ?> <?php the_bsearch_date(); ?></li>
								<li class="meta-cat"><?php esc_html_e( 'Categories:', 'better-search' ); ?><?php the_category( ', ' ); ?></li>
								<?php do_action( 'bsearch_after_post_meta' ); ?>
							</ul>

							<?php do_action( 'bsearch_after_entry_header_inner' ); ?>

						</header><!-- .entry-header -->

						<?php do_action( 'bsearch_after_entry_header' ); ?>

						<div class="entry-content">
							<?php $args['excerpt_length'] = apply_filters( 'bsearch_results_excerpt_length', 55 ); ?>
							<?php the_bsearch_excerpt( $args ); ?>
						</div><!-- .entry-content -->

						<footer class="entry-footer default-max-width">
							<div class="bsearch-entry-readmore">
								<a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>"><?php esc_html_e( 'Read more &raquo;', 'better-search' ); ?></a>
								<span class="screen-reader-text"><?php the_title(); ?></span>
							</div><!-- .search-entry-readmore -->
						</footer><!-- .entry-footer -->

						<?php do_action( 'bsearch_after_article_inner' ); ?>

					</article><!-- #post-${ID} -->

					<?php do_action( 'bsearch_after_article' ); ?>

					<?php
				endwhile;
				?>
				<div style="text-align:center">
				<?php
				the_posts_pagination(
					array(
						'mid_size'  => 3,
						'prev_text' => esc_html__( '« Previous', 'better-search' ),
						'next_text' => esc_html__( 'Next »', 'better-search' ),
					)
				);
				?>
				</div>
				<?php
			} else {
				?>
				<p class="no-posts"><?php esc_html_e( 'No results found.', 'better-search' ); ?></p>
				<?php
			}

			// Reset wp_query back to what it was.
			$wp_query = null; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
			$wp_query = $tmp_wpquery; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
			?>
		</div>	<!-- Close id="bsearchresults" -->

		<?php echo get_bsearch_form( $search_query ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

		<?php if ( $bsearch_settings['include_heatmap'] ) : ?>

			<div id="heatmap">
				<div class="heatmap_daily">
					<?php the_bsearch_heatmap( 'daily=1' ); ?>
				</div>
				<div class="heatmap_overall">
					<?php the_bsearch_heatmap( 'daily=0' ); ?>
				</div>

				<div style="clear:both">&nbsp;</div>
			</div>

		<?php endif; ?>

	</div>	<!-- Close id="content" -->

	<?php do_action( 'bsearch_after_content' ); ?>

<?php
	// Get footer.
	get_footer();
