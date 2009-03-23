<?php /* Sample template for Better Search Plugin for WordPress Default theme */

	get_header(); ?>
	<div id="content" class="narrowcolumn">

	<div id="heatmap" style="padding: 5px; border: 1px dashed #ccc">
	<div style="padding: 5px; border-bottom: 1px dashed #ccc">
	<h2>
	<?php echo get_bsearch_title_daily(); ?>
	</h2>
	<?php echo get_bsearch_heatmap(true); ?>
	</div>
	<div style="padding: 5px;">
	<h2>
	<?php echo get_bsearch_title(); ?>
	</h2>
	<?php echo get_bsearch_heatmap(false); ?>
	</div>
	<div style="clear:both">&nbsp;</div>
	</div>

	<div style="padding: 5px;margin: 5px;">
	<?php echo get_bsearch_form($s); ?>
	</div>

	<div id="searchresults"><h2 class="pagetitle">Search Results for:	&quot;<?php echo $s; ?>&quot;</h2>

	<?php bsearch_results($s,$limit); ?>

	</div>
	</div>

<?php get_sidebar(); ?>
<?php get_footer(); ?>
