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
global $bsearch_error;

// Initialise some variables.
$bsearch_settings = bsearch_get_settings();
$search_query     = get_search_query();

$limit = filter_input(
	INPUT_GET,
	'limit',
	FILTER_VALIDATE_INT,
	array(
		'options' => array(
			'default'   => $bsearch_settings['limit'],
			'min_range' => 1,
		),
	)
);

$bydate = filter_input(
	INPUT_GET,
	'bydate',
	FILTER_VALIDATE_INT,
	array(
		'options' => array(
			'default'   => 0,
			'min_range' => 0,
		),
	)
);

$current_page        = (int) get_query_var( 'paged', 1 );
$selected_post_types = 'any';
$current_blog_id     = get_current_blog_id();

$post_types_param = filter_input( INPUT_GET, 'post_types', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
$post_type_param  = filter_input( INPUT_GET, 'post_type', FILTER_SANITIZE_FULL_SPECIAL_CHARS );

if ( $post_types_param ) {
	$selected_post_types = sanitize_title( wp_unslash( $post_types_param ) );
} elseif ( $post_type_param ) {
	$selected_post_types = sanitize_title( wp_unslash( $post_type_param ) );
}

$post_types = ( 'any' === $selected_post_types ) ? bsearch_get_option( 'post_types' ) : $selected_post_types;
$post_types = wp_parse_list( $post_types );

// Reset wp_query temporary.
global $wp_query;
$tmp_wpquery = $wp_query;

// Set up Better_Search_Query to replace $wp_query.
$args = array(
	's'              => $search_query,
	'posts_per_page' => $limit,
	'paged'          => $current_page,
	'orderby'        => $bydate ? 'date' : 'relevance',
	'post_type'      => $post_types,
);

/**
 * Filter the arguments that are passed to Better_Search_Query.
 *
 * @since 3.1.0
 *
 * @param array $args Arguments array.
 */
$args = apply_filters( 'bsearch_template_query_args', $args );

$search_results = new Better_Search_Query( $args );
$wp_query       = $search_results; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
$topscore       = $wp_query->topscore;

// Get Header.
get_header();

?>

	<?php do_action( 'bsearch_before_content' ); ?>

	<div id="content" class="bsearch_results_page">

		<?php the_bsearch_form( $search_query, array( 'selected_post_types' => $selected_post_types ) ); ?>

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
					$post_score = $post->score ?? bsearch_get_post_score( $post->ID );
					?>

					<?php do_action( 'bsearch_before_article' ); ?>

					<article id="post-<?php the_ID(); ?>" <?php post_class( 'bsearch-post' ); ?>>

						<?php
						// Display post thumbnail.
						if ( bsearch_get_option( 'include_thumb' ) ) :
							?>
							<div class="thumbnail bsearch_thumb_wrapper">
								<a href="<?php the_bsearch_permalink( $post ); ?>" class="thumbnail-link">
									<?php
										/**
										 * Filter the post thumbnail size in the search results.
										 *
										 * @since 3.1.0
										 *
										 * @param string  $size Thumbnail size. Default is 'thumbnail'.
										 * @param WP_Post $post WP_Post object.
										 */
										$size = apply_filters( 'bsearch_post_thumbnail_size', 'thumbnail', $post );

										the_bsearch_post_thumbnail(
											$size,
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
								<a href="<?php the_bsearch_permalink( $post ); ?>" title="<?php the_title_attribute(); ?>" rel="bookmark"><?php the_title(); ?></a>
							</h2>

							<ul class="bsearch_post_meta">
								<?php do_action( 'bsearch_before_post_meta' ); ?>
								<?php if ( bsearch_get_option( 'display_relevance' ) && $post_score > 0 && $topscore > 0 ) : ?>
								<li class="meta-relevance">
									<?php

									the_bsearch_score(
										array(
											'score'    => $post_score,
											'topscore' => $topscore,
										)
									);
									?>
								</li>
								<?php endif; ?>
								<?php if ( bsearch_get_option( 'display_post_type' ) ) : ?>
									<li class="meta-type"><?php esc_html_e( 'Type', 'better-search' ); ?>: <?php the_bsearch_post_type( $post ); ?></li>
								<?php endif; ?>
								<?php if ( bsearch_get_option( 'display_author' ) ) : ?>
									<li class="meta-author"><?php esc_html_e( 'Post author', 'better-search' ); ?>: <?php the_author_posts_link(); ?></li>
								<?php endif; ?>
								<?php if ( bsearch_get_option( 'display_date' ) ) : ?>
									<li class="meta-date"><?php esc_html_e( 'Published date', 'better-search' ); ?>: <?php the_bsearch_date(); ?></li>
								<?php endif; ?>
								<?php if ( bsearch_get_option( 'display_taxonomies' ) ) : ?>
									<li class="meta-cat"><?php esc_html_e( 'Terms', 'better-search' ); ?>: <?php the_bsearch_term_list( $post ); ?></li>
								<?php endif; ?>
								<?php do_action( 'bsearch_after_post_meta' ); ?>
							</ul>

							<?php do_action( 'bsearch_after_entry_header_inner' ); ?>

						</header><!-- .entry-header -->

						<?php do_action( 'bsearch_after_entry_header' ); ?>

						<div class="entry-content">
							<?php the_bsearch_excerpt(); ?>
						</div><!-- .entry-content -->

						<footer class="entry-footer default-max-width">
							<div class="bsearch-entry-readmore">
								<a href="<?php the_bsearch_permalink(); ?>" title="<?php the_title_attribute(); ?>"><?php esc_html_e( 'Read more &raquo;', 'better-search' ); ?></a>
								<span class="screen-reader-text"><?php the_title(); ?></span>
							</div><!-- .search-entry-readmore -->
						</footer><!-- .entry-footer -->

						<?php do_action( 'bsearch_after_article_inner' ); ?>

					</article><!-- #post-${ID} -->

					<?php do_action( 'bsearch_after_article' ); ?>

				<?php endwhile; ?>
				
				<div style="text-align:center">
				<?php
				if ( function_exists( 'switch_to_blog' ) ) {
					switch_to_blog( $current_blog_id );
				}
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
				<p class="no-posts"><?php ( '' !== $bsearch_error->get_error_message( 'bsearch_banned' ) ) ? esc_html( $bsearch_error->get_error_message( 'bsearch_banned' ) ) : esc_html_e( 'No results found.', 'better-search' ); ?></p>
				<?php
			}

			// Reset wp_query back to what it was.
			$wp_query = null; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
			$wp_query = $tmp_wpquery; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
			?>
		</div>	<!-- Close id="bsearchresults" -->

		<?php the_bsearch_form( $search_query, array( 'selected_post_types' => $selected_post_types ) ); ?>

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
