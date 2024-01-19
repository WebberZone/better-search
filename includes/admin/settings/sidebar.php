<?php
/**
 * Sidebar
 *
 * @package WebberZone\Better_Search
 */

?>
<div class="postbox-container">
	<div id="pro-upgrade-banner">
		<div class="inside" style="text-align: center">
			<a href="https://webberzone.com/plugins/better-search/pro/" target="_blank"><img src="<?php echo esc_url( BETTER_SEARCH_PLUGIN_URL . 'includes/admin/images/better-search-pro-banner.png' ); ?>" alt="<?php esc_html_e( 'Better Search Pro - Coming soon. Sign up to find out more', 'better-search' ); ?>" width="300" height="300" style="max-width: 100%;" /></a>
		</div>
	</div>

	<div id="donatediv" class="postbox meta-box-sortables">
		<h2 class='hndle'><span><?php esc_attr_e( 'Support the development', 'better-search' ); ?></span></h2>

		<div class="inside" style="text-align: center">
			<div id="donate-form">
				<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
					<input type="hidden" name="cmd" value="_xclick">
					<input type="hidden" name="business" value="donate@ajaydsouza.com">
					<input type="hidden" name="lc" value="IN">
					<input type="hidden" name="item_name" value="<?php esc_html_e( 'Donation for Better Search', 'better-search' ); ?>">
					<input type="hidden" name="item_number" value="bsearch_plugin_settings">
					<strong><?php esc_html_e( 'Enter amount in USD', 'better-search' ); ?></strong>: <input name="amount" value="15.00" size="6" type="text"><br />
					<input type="hidden" name="currency_code" value="USD">
					<input type="hidden" name="button_subtype" value="services">
					<input type="hidden" name="bn" value="PP-BuyNowBF:btn_donate_LG.gif:NonHosted">
					<input type="image" src="<?php echo esc_url( BETTER_SEARCH_PLUGIN_URL . 'includes/admin/images/paypal_donate_button.webp' ); ?>" border="0" name="submit" alt="<?php esc_html_e( 'Send your donation to the author of', 'better-search' ); ?> Better Search">
				</form>
			</div><!-- /#donate-form -->
		</div><!-- /.inside -->
	</div><!-- /.postbox -->

	<div id="qlinksdiv" class="postbox meta-box-sortables">
		<h2 class='hndle metabox-holder'><span><?php esc_html_e( 'Quick links', 'better-search' ); ?></span></h2>

		<div class="inside">
			<div id="quick-links">
				<ul class="subsub">
					<li>
						<a href="https://webberzone.com/plugins/better-search/"><?php esc_html_e( 'Better Search plugin homepage', 'better-search' ); ?></a>
					</li>

					<li>
						<a href="https://wordpress.org/plugins/better-search/faq/"><?php esc_html_e( 'FAQ', 'better-search' ); ?></a>
					</li>

					<li>
						<a href="https://wordpress.org/support/plugin/better-search/"><?php esc_html_e( 'Support', 'better-search' ); ?></a>
					</li>

					<li>
						<a href="https://wordpress.org/support/plugin/better-search/reviews/"><?php esc_html_e( 'Reviews', 'better-search' ); ?></a>
					</li>

					<li>
						<a href="https://github.com/ajaydsouza/better-search"><?php esc_html_e( 'Github repository', 'better-search' ); ?></a>
					</li>

					<li>
						<a href="https://webberzone.com/plugins/"><?php esc_html_e( 'Other plugins', 'better-search' ); ?></a>
					</li>

					<li>
						<a href="https://webberzone.com/"><?php esc_html_e( "Ajay's blog", 'better-search' ); ?></a>
					</li>
				</ul>
			</div>
		</div><!-- /.inside -->
	</div><!-- /.postbox -->
</div>

<div class="postbox-container">
	<div id="followdiv" class="postbox meta-box-sortables">
		<h2 class='hndle'><span><?php esc_html_e( 'Follow me', 'better-search' ); ?></span></h2>

		<div class="inside" style="text-align: center">
		<a href="https://twitter.com/webberzone/" target="_blank"><img src="<?php echo esc_url( BETTER_SEARCH_PLUGIN_URL . 'includes/admin/images/x.png' ); ?>" width="100" height="100"></a>
			<a href="https://facebook.com/webberzone/" target="_blank"><img src="<?php echo esc_url( BETTER_SEARCH_PLUGIN_URL . 'includes/admin/images/fb.png' ); ?>" width="100" height="100"></a>
		</div><!-- /.inside -->
	</div><!-- /.postbox -->
</div>