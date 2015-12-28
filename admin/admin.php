<?php
/**
 * Generates the settings page in the Admin
 *
 * @package Better_Search
 */

// If this file is called directly, then abort execution.
if ( ! defined( 'WPINC' ) ) {
	die( "Aren't you supposed to come here via WP-Admin?" );
}

/**
 * Better Search options.
 *
 * @since	1.0
 */
function bsearch_options() {

	global $wpdb;

	$bsearch_settings = bsearch_read_options();

	// Parse post types
	parse_str( $bsearch_settings['post_types'], $post_types );
	$wp_post_types = get_post_types( array(
		'public' => true,
	) );
	$posts_types_inc = array_intersect( $wp_post_types, $post_types );

	if ( ( isset( $_POST['bsearch_save'] ) ) && ( check_admin_referer( 'bsearch-plugin-settings' ) ) ) {

		/* General options */
		$bsearch_settings['seamless'] = isset( $_POST['seamless'] ) ? true : false;
		$bsearch_settings['track_popular'] = isset( $_POST['track_popular'] ) ? true : false;
		$bsearch_settings['track_admins'] = isset( $_POST['track_admins'] ) ? true : false;
		$bsearch_settings['track_editors'] = isset( $_POST['track_editors'] ) ? true : false;
		$bsearch_settings['cache'] = isset( $_POST['cache'] ) ? true : false;

		$bsearch_settings['meta_noindex'] = isset( $_POST['meta_noindex'] ) ? true : false;

		$bsearch_settings['show_credit'] = isset( $_POST['show_credit'] ) ? true : false;

		/* Search options */
		$bsearch_settings['limit'] = intval( $_POST['limit'] );

		$bsearch_settings['use_fulltext'] = isset( $_POST['use_fulltext'] ) ? true : false;
		$bsearch_settings['weight_content'] = intval( $_POST['weight_content'] );
		$bsearch_settings['weight_title'] = intval( $_POST['weight_title'] );
		$bsearch_settings['boolean_mode'] = isset( $_POST['boolean_mode'] ) ? true : false;

		// Update post types
		$wp_post_types	= get_post_types( array(
			'public' => true,
		) );
		$post_types_arr = ( is_array( $_POST['post_types'] ) ) ? $_POST['post_types'] : array( 'post' => 'post' );
		$post_types = array_intersect( $wp_post_types, $post_types_arr );
		$bsearch_settings['post_types'] = http_build_query( $post_types, '', '&' );
		$posts_types_inc = array_intersect( $wp_post_types, $post_types );

		$bsearch_settings['highlight'] = isset( $_POST['highlight'] ) ? true : false;

		$bsearch_settings['excerpt_length'] = intval( $_POST['excerpt_length'] );
		$bsearch_settings['link_new_window'] = isset( $_POST['link_new_window'] ) ? true : false;
		$bsearch_settings['link_nofollow'] = isset( $_POST['link_nofollow'] ) ? true : false;
		$bsearch_settings['include_thumb'] = isset( $_POST['include_thumb'] ) ? true : false;

		$bsearch_settings['badwords'] = wp_kses_post( $_POST['badwords'] );

		/* Heatmap options */
		$bsearch_settings['include_heatmap'] = isset( $_POST['include_heatmap'] ) ? true : false;
		$bsearch_settings['title'] = wp_kses_post( $_POST['title'] );
		$bsearch_settings['title_daily'] = wp_kses_post( $_POST['title_daily'] );
		$bsearch_settings['daily_range'] = intval( $_POST['daily_range'] );

		$bsearch_settings['heatmap_limit'] = $_POST['heatmap_limit'];
		$bsearch_settings['heatmap_smallest'] = $_POST['heatmap_smallest'];
		$bsearch_settings['heatmap_largest'] = $_POST['heatmap_largest'];
		$bsearch_settings['heatmap_cold'] = $_POST['heatmap_cold'];
		$bsearch_settings['heatmap_hot'] = $_POST['heatmap_hot'];
		$bsearch_settings['heatmap_before'] = $_POST['heatmap_before'];
		$bsearch_settings['heatmap_after'] = $_POST['heatmap_after'];

		/* Custom styles */
		$bsearch_settings['custom_CSS'] = wp_kses_post( $_POST['custom_CSS'] );

		update_option( 'ald_bsearch_settings', $bsearch_settings );

		bsearch_cache_delete();

		$str = '<div id="message" class="updated fade"><p>'. __( 'Options saved successfully. If enabled, cache has been cleared.', 'better-search' ) .'</p></div>';
		echo $str;
	}

	if ( ( isset( $_POST['bsearch_default'] ) ) && ( check_admin_referer( 'bsearch-plugin-settings' ) ) ) {
		delete_option( 'ald_bsearch_settings' );
		$bsearch_settings = bsearch_default_options();
		update_option( 'ald_bsearch_settings', $bsearch_settings );

		$str = '<div id="message" class="updated fade"><p>' . __( 'Options set to Default.', 'better-search' ) . '</p></div>';
		echo $str;
	}

	if ( ( isset( $_POST['bsearch_trunc_all'] ) ) && ( check_admin_referer( 'bsearch-plugin-settings' ) ) ) {
		bsearch_trunc_count( false );
		$str = '<div id="message" class="updated fade"><p>' . __( 'Popular searches count reset', 'better-search' ) . '</p></div>';
		echo $str;
	}

	if ( ( isset( $_POST['bsearch_trunc_daily'] ) ) && ( check_admin_referer( 'bsearch-plugin-settings' ) ) ) {
		bsearch_trunc_count( true );
		$str = '<div id="message" class="updated fade"><p>' . __( 'Daily popular searches count reset', 'better-search' ) . '</p></div>';
		echo $str;
	}

	if ( ( isset( $_POST['bsearch_recreate'] ) ) && ( check_admin_referer( 'bsearch-plugin-settings' ) ) ) {
		$wpdb->query( 'ALTER TABLE ' . $wpdb->posts . ' DROP INDEX bsearch' );
		$wpdb->query( 'ALTER TABLE ' . $wpdb->posts . ' DROP INDEX bsearch_title' );
		$wpdb->query( 'ALTER TABLE ' . $wpdb->posts . ' DROP INDEX bsearch_content' );

	    $wpdb->query( 'ALTER TABLE ' . $wpdb->posts . ' ADD FULLTEXT bsearch (post_title, post_content);' );
	    $wpdb->query( 'ALTER TABLE ' . $wpdb->posts . ' ADD FULLTEXT bsearch_title (post_title);' );
	    $wpdb->query( 'ALTER TABLE ' . $wpdb->posts . ' ADD FULLTEXT bsearch_content (post_content);' );

		$str = '<div id="message" class="updated fade"><p>'. __( 'Index recreated', 'better-search' ) .'</p></div>';
		echo $str;
	}

	require_once( 'main-view.php' );

}


/**
 * Function to generate the right sidebar of the Settings and Admin popular posts pages.
 *
 * @since	1.3.3
 */
function bsearch_admin_side() {
?>
    <div id="donatediv" class="postbox"><div class="handlediv" title="<?php _e( 'Click to toggle', 'better-search' ); ?>"><br /></div>
      <h3 class='hndle'><span><?php _e( 'Support the development', 'better-search' ); ?></span></h3>
      <div class="inside">
        <div id="donate-form">
            <form action="https://www.paypal.com/cgi-bin/webscr" method="post">
                <input type="hidden" name="cmd" value="_xclick">
                <input type="hidden" name="business" value="donate@ajaydsouza.com">
                <input type="hidden" name="lc" value="IN">
				<input type="hidden" name="item_name" value="<?php _e( 'Donation for Better Search', 'better-search' ); ?>">
                <input type="hidden" name="item_number" value="bsearch_admin">
				<strong><?php _e( 'Enter amount in USD', 'better-search' ); ?></strong>: <input name="amount" value="10.00" size="6" type="text"><br />
                <input type="hidden" name="currency_code" value="USD">
                <input type="hidden" name="button_subtype" value="services">
                <input type="hidden" name="bn" value="PP-BuyNowBF:btn_donate_LG.gif:NonHosted">
				<input type="image" src="https://www.paypal.com/en_US/i/btn/btn_donate_LG.gif" border="0" name="submit" alt="<?php _e( 'Send your donation to the author of Better Search', 'better-search' ); ?>">
                <img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1">
            </form>
        </div>
      </div>
    </div>
    <div id="followdiv" class="postbox"><div class="handlediv" title="<?php _e( 'Click to toggle', 'better-search' ); ?>"><br /></div>
      <h3 class='hndle'><span><?php _e( 'Follow me', 'better-search' ); ?></span></h3>
      <div class="inside">
		<div id="twitter">
			<div style="text-align:center"><a href="https://twitter.com/WebberZoneWP" class="twitter-follow-button" data-show-count="false" data-size="large" data-dnt="true">Follow @WebberZoneWP</a>
			<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="//platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script></div>
		</div>
		<div id="facebook">
			<div id="fb-root"></div>
			<script>
			//<![CDATA[
				(function(d, s, id) {
				var js, fjs = d.getElementsByTagName(s)[0];
				if (d.getElementById(id)) return;
				js = d.createElement(s); js.id = id;
				js.src = "//connect.facebook.net/en_US/sdk.js#xfbml=1&version=v2.4&appId=458036114376706";
				fjs.parentNode.insertBefore(js, fjs);
				}(document, 'script', 'facebook-jssdk'));
			//]]>
			</script>
			<div class="fb-page" data-href="https://www.facebook.com/WebberZone" data-small-header="false" data-adapt-container-width="true" data-hide-cover="false" data-show-facepile="false" data-show-posts="false"><div class="fb-xfbml-parse-ignore"><blockquote cite="https://www.facebook.com/WebberZone"><a href="https://www.facebook.com/WebberZone">WebberZone</a></blockquote></div></div>
		</div>
      </div>
    </div>
    <div id="qlinksdiv" class="postbox"><div class="handlediv" title="<?php _e( 'Click to toggle', 'better-search' ); ?>"><br /></div>
      <h3 class='hndle'><span><?php _e( 'Quick links', 'better-search' ); ?></span></h3>
      <div class="inside">
        <div id="quick-links">
            <ul>
				<li><a href="https://webberzone.com/plugins/better-search/"><?php _e( 'Better Search plugin page', 'better-search' ); ?></a></li>
				<li><a href="https://webberzone.com/plugins/"><?php _e( 'Other plugins', 'better-search' ); ?></a></li>
				<li><a href="https://ajaydsouza.com/"><?php _e( "Ajay's blog", 'better-search' ); ?></a></li>
				<li><a href="https://wordpress.org/plugins/better-search/faq/"><?php _e( 'FAQ', 'better-search' ); ?></a></li>
				<li><a href="http://wordpress.org/support/plugin/better-search"><?php _e( 'Support', 'better-search' ); ?></a></li>
				<li><a href="https://wordpress.org/support/view/plugin-reviews/better-search"><?php _e( 'Reviews', 'better-search' ); ?></a></li>
            </ul>
        </div>
      </div>
    </div>

<?php
}


/**
 * Add menu item in WP-Admin.
 *
 * @since	1.0
 */
function bsearch_adminmenu() {

	$plugin_page = add_options_page( __( 'Better Search', 'better-search' ), __( 'Better Search', 'better-search' ), 'manage_options', 'bsearch_options', 'bsearch_options' );
	add_action( 'admin_head-'. $plugin_page, 'bsearch_adminhead' );
}
add_action( 'admin_menu', 'bsearch_adminmenu' );


/**
 * Add CSS and JS to the admin head.
 *
 * @since	1.0
 */
function bsearch_adminhead() {
	global $bsearch_url;

	wp_enqueue_script( 'common' );
	wp_enqueue_script( 'wp-lists' );
	wp_enqueue_script( 'postbox' );
?>
    <style type="text/css">
    .postbox .handlediv:before {
        right:12px;
        font:400 20px/1 dashicons;
        speak:none;
        display:inline-block;
        top:0;
        position:relative;
        -webkit-font-smoothing:antialiased;
        -moz-osx-font-smoothing:grayscale;
        text-decoration:none!important;
        content:'\f142';
        padding:8px 10px;
    }
    .postbox.closed .handlediv:before {
        content: '\f140';
    }
    .wrap h2:before {
        content: "\f179";
        display: inline-block;
        -webkit-font-smoothing: antialiased;
        font: normal 29px/1 'dashicons';
        vertical-align: middle;
        margin-right: 0.3em;
    }
    </style>

    <script type="text/javascript">
        //<![CDATA[
        jQuery(document).ready( function($) {
            // close postboxes that should be closed
            $('.if-js-closed').removeClass('if-js-closed').addClass('closed');
            // postboxes setup
            postboxes.add_postbox_toggles('bsearch_options');
        });
        //]]>
    </script>

    <script type="text/javascript" language="JavaScript">
        //<![CDATA[
        function checkForm() {
        answer = true;
        if (siw && siw.selectingSomething)
            answer = false;
        return answer;
        }//

		function clearCache() {
			/**** since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php ****/
			jQuery.post(ajaxurl, {action: 'bsearch_clear_cache'}, function(response, textStatus, jqXHR) {
				alert( response.message );
			}, 'json');
		}

        //]]>
    </script>

	<script type="text/javascript" src="<?php echo $bsearch_url ?>/admin/jscolor/jscolor.js"></script>
<?php
}


/**
 * Function to clean the database.
 *
 * @since	1.0
 *
 * @param	bool $daily  TRUE = Daily tables, FALSE = Overall tables
 */
function bsearch_trunc_count( $daily = true ) {
	global $wpdb;
	$table_name = ( $daily ) ? $wpdb->prefix . 'bsearch_daily' : $wpdb->prefix . 'bsearch';

	$sql = "TRUNCATE TABLE $table_name";
	$wpdb->query( $sql );
}


/**
 * Dashboard for Better Search.
 *
 * @since	1.0
 */
function bsearch_pop_dashboard() {
	global $bsearch_settings;

	echo get_bsearch_heatmap( array(
		'daily' => 0,
	) );

	if ( $bsearch_settings['show_credit'] ) {
		echo '<br /><small>Powered by <a href="https://webberzone.com/plugins/better-search/">Better Search plugin</a></small>';
	}
}


/**
 * Dashboard for Daily Better Search.
 *
 * @since	1.0
 */
function bsearch_pop_daily_dashboard() {
	global $bsearch_settings;

	echo get_bsearch_heatmap( array(
		'daily' => 1,
	) );

	if ( $bsearch_settings['show_credit'] ) {
		echo '<br /><small>Powered by <a href="https://webberzone.com/plugins/better-search/">Better Search plugin</a></small>';
	}
}


/**
 * Add the dashboard widgets.
 *
 * @since	1.3.3
 */
function bsearch_dashboard_setup() {
	wp_add_dashboard_widget( 'bsearch_pop_dashboard', __( 'Popular Searches', 'better-search' ), 'bsearch_pop_dashboard' );
	wp_add_dashboard_widget( 'bsearch_pop_daily_dashboard', __( 'Daily Popular Searches', 'better-search' ), 'bsearch_pop_daily_dashboard' );
}
add_action( 'wp_dashboard_setup', 'bsearch_dashboard_setup' );


/**
 * Better Search plugin notice.
 *
 * @since	1.3.3
 *
 * @param	string $plugin
 */
function bsearch_plugin_notice( $plugin ) {
	global $cache_enabled;

	if ( $plugin == 'better-search/admin.inc.php' && ! $cache_enabled && function_exists( 'admin_url' ) ) {

		echo '<td colspan="5" class="plugin-update">Better Search must be configured. Go to <a href="' . admin_url( 'options-general.php?page=bsearch_options' ) . '">the admin page</a> to enable and configure the plugin.</td>';

	}
}
// add_action( 'after_plugin_row', 'bsearch_plugin_notice' );
/**
 * Adding WordPress plugin action links.
 *
 * @since	1.3
 *
 * @param	array $links  Existing array of links
 * @return	array	Updated array
 */
function bsearch_plugin_actions_links( $links ) {

	return array_merge(
		array(
			'settings' => '<a href="' . admin_url( 'options-general.php?page=bsearch_options' ) . '">' . __( 'Settings', 'better-search' ) . '</a>',
		),
		$links
	);

}
add_filter( 'plugin_action_links_' . plugin_basename( plugin_dir_path( __DIR__ ) . 'better-search.php' ), 'bsearch_plugin_actions_links' );


/**
 * Add meta links on Plugins page.
 *
 * @since	1.1.3
 *
 * @param	array  $links  Existing array of links
 * @param	string $file   File
 * @return	array	Updated array
 */
function bsearch_plugin_actions( $links, $file ) {
	$plugin = plugin_basename( plugin_dir_path( __DIR__ ) . 'better-search.php' );

	/**** Add links ****/
	if ( $file == $plugin ) {
		$links[] = '<a href="https://wordpress.org/support/plugin/better-search">' . __( 'Support', 'better-search' ) . '</a>';
		$links[] = '<a href="https://ajaydsouza.com/donate/">' . __( 'Donate', 'better-search' ) . '</a>';
		$links[] = '<a href="https://github.com/ajaydsouza/better-search">' . __( 'Contribute', 'better-search' ) . '</a>';
	}
	return $links;
}
add_filter( 'plugin_row_meta', 'bsearch_plugin_actions', 10, 2 ); // only 2.8 and higher


/**
 * Function to clear the Cache with Ajax.
 *
 * @since	2.2.0
 */
function bsearch_ajax_clearcache() {

	bsearch_cache_delete();

	exit( json_encode( array(
		'success' => 1,
		'message' => __( 'Better Search cache has been cleared', 'better-search' ),
	) ) );
}
add_action( 'wp_ajax_bsearch_clear_cache', 'bsearch_ajax_clearcache' );


