<?php
/**
 * Sidebar
 *
 * @package WebberZone\Better_Search
 */

use WebberZone\Better_Search\Main;

?>
<div class="postbox-container">
	<?php Main::pro_upgrade_banner(); ?>

	<div id="qlinksdiv" class="postbox meta-box-sortables">
		<h2 class='hndle metabox-holder'><span><?php esc_html_e( 'Quick links', 'better-search' ); ?></span></h2>

		<div class="inside">
			<div id="quick-links">
				<ul class="subsub">
					<li>
						<a href="https://webberzone.com/plugins/better-search/" target="_blank"><?php esc_html_e( 'Better Search plugin homepage', 'better-search' ); ?></a>
					</li>
					<li>
						<a href="https://webberzone.com/support/product/better-search/" target="_blank"><?php esc_html_e( 'Knowledge Base', 'better-search' ); ?></a>
					</li>
					<li>
						<a href="https://wordpress.org/plugins/better-search/faq/" target="_blank"><?php esc_html_e( 'FAQ', 'better-search' ); ?></a>
					</li>
					<li>
						<a href="https://webberzone.com/support/" target="_blank"><?php esc_html_e( 'Support', 'better-search' ); ?></a>
					</li>
					<li>
						<a href="https://wordpress.org/support/plugin/better-search/reviews/" target="_blank"><?php esc_html_e( 'Reviews', 'better-search' ); ?></a>
					</li>
					<li>
						<a href="https://github.com/webberzone/better-search" target="_blank"><?php esc_html_e( 'Github repository', 'better-search' ); ?></a>
					</li>
					<li>
						<a href="https://ajaydsouza.com/" target="_blank"><?php esc_html_e( "Ajay's blog", 'better-search' ); ?></a>
					</li>
				</ul>
			</div>
		</div><!-- /.inside -->
	</div><!-- /.postbox -->
	<div id="pluginsdiv" class="postbox meta-box-sortables">
		<h2 class='hndle metabox-holder'><span><?php esc_html_e( 'WebberZone plugins', 'better-search' ); ?></span></h2>

		<div class="inside">
			<div id="quick-links">
				<ul class="subsub">
					<li><a href="https://webberzone.com/plugins/contextual-related-posts/" target="_blank"><?php esc_html_e( 'Contextual Related Posts', 'better-search' ); ?></a></li>
					<li><a href="https://webberzone.com/plugins/top-10/" target="_blank"><?php esc_html_e( 'Top 10', 'better-search' ); ?></a></li>
					<li><a href="https://webberzone.com/plugins/knowledgebase/" target="_blank"><?php esc_html_e( 'Knowledge Base', 'better-search' ); ?></a></li>
					<li><a href="https://webberzone.com/plugins/add-to-all/" target="_blank"><?php esc_html_e( 'Snippetz', 'better-search' ); ?></a></li>
					<li><a href="https://webberzone.com/webberzone-followed-posts/" target="_blank"><?php esc_html_e( 'Followed Posts', 'better-search' ); ?></a></li>
					<li><a href="https://webberzone.com/plugins/popular-authors/" target="_blank"><?php esc_html_e( 'Popular Authors', 'better-search' ); ?></a></li>
					<li><a href="https://webberzone.com/plugins/autoclose/" target="_blank"><?php esc_html_e( 'Auto Close', 'better-search' ); ?></a></li>
				</ul>
			</div>
		</div><!-- /.inside -->
	</div><!-- /.postbox -->	

</div>

<div class="postbox-container">
	<div id="followdiv" class="postbox meta-box-sortables">
		<h2 class='hndle'><span><?php esc_html_e( 'Follow me', 'better-search' ); ?></span></h2>

		<div class="inside" style="text-align: center">
		<a href="https://x.com/webberzone/" target="_blank"><img src="<?php echo esc_url( BETTER_SEARCH_PLUGIN_URL . 'includes/admin/images/x.png' ); ?>" width="100" height="100"></a>
			<a href="https://facebook.com/webberzone/" target="_blank"><img src="<?php echo esc_url( BETTER_SEARCH_PLUGIN_URL . 'includes/admin/images/fb.png' ); ?>" width="100" height="100"></a>
		</div><!-- /.inside -->
	</div><!-- /.postbox -->
</div>