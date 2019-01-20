<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link  https://webberzone.com
 * @since 2.2.0
 *
 * @package    Better Search
 * @subpackage Admin
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}


/**
 * Creates the admin submenu pages under the Downloads menu and assigns their
 * links to global variables
 *
 * @since 2.2.0
 *
 * @global $bsearch_settings_page
 * @return void
 */
function bsearch_add_admin_pages_links() {
	global $bsearch_settings_page, $bsearch_settings_tools_help;

	$bsearch_settings_page = add_menu_page( esc_html__( 'Better Search Settings', 'better-search' ), esc_html__( 'Better Search', 'better-search' ), 'manage_options', 'bsearch_options_page', 'bsearch_options_page', 'dashicons-search' );
	add_action( "load-$bsearch_settings_page", 'bsearch_settings_help' ); // Load the settings contextual help.
	add_action( "admin_head-$bsearch_settings_page", 'bsearch_adminhead' ); // Load the admin head.

	$plugin_page = add_submenu_page( 'bsearch_options_page', esc_html__( 'Better Search Settings', 'better-search' ), esc_html__( 'Settings', 'better-search' ), 'manage_options', 'bsearch_options_page', 'bsearch_options_page' );
	add_action( 'admin_head-' . $plugin_page, 'bsearch_adminhead' );

	$bsearch_settings_tools_help = add_submenu_page( 'bsearch_options_page', esc_html__( 'Better Search Tools', 'better-search' ), esc_html__( 'Tools', 'better-search' ), 'manage_options', 'bsearch_tools_page', 'bsearch_tools_page' );
	add_action( "load-$bsearch_settings_tools_help", 'bsearch_settings_tools_help' );
	add_action( 'admin_head-' . $bsearch_settings_tools_help, 'bsearch_adminhead' );

}
add_action( 'admin_menu', 'bsearch_add_admin_pages_links' );


/**
 * Function to add CSS and JS to the Admin header.
 *
 * @since 2.2.0
 * @return void
 */
function bsearch_adminhead() {

	wp_enqueue_script( 'jquery' );
	wp_enqueue_script( 'jquery-ui-autocomplete' );
	wp_enqueue_script( 'jquery-ui-tabs' );
	wp_enqueue_script( 'plugin-install' );
	wp_enqueue_script( 'jscolor', BETTER_SEARCH_PLUGIN_URL . 'includes/admin/jscolor/jscolor.js', array(), '1.0', true );
	add_thickbox();
	?>
	<script type="text/javascript">
	//<![CDATA[
		// Function to clear the cache.
		function clearCache() {
			jQuery.post(ajaxurl, {
				action: 'bsearch_clear_cache'
			}, function (response, textStatus, jqXHR) {
				alert(response.message);
			}, 'json');
		}

		// Function to add auto suggest.
		jQuery(document).ready(function($) {
			$.fn.bsearchTagsSuggest = function( options ) {

				var cache;
				var last;
				var $element = $( this );

				var taxonomy = $element.attr( 'data-wp-taxonomy' ) || 'category';

				function split( val ) {
					return val.split( /,\s*/ );
				}

				function extractLast( term ) {
					return split( term ).pop();
				}

				$element.on( "keydown", function( event ) {
						// Don't navigate away from the field on tab when selecting an item.
						if ( event.keyCode === $.ui.keyCode.TAB &&
						$( this ).autocomplete( 'instance' ).menu.active ) {
							event.preventDefault();
						}
					})
					.autocomplete({
						minLength: 2,
						source: function( request, response ) {
							var term;

							if ( last === request.term ) {
								response( cache );
								return;
							}

							term = extractLast( request.term );

							if ( last === request.term ) {
								response( cache );
								return;
							}

							$.ajax({
								type: 'POST',
								dataType: 'json',
								url: '<?php echo admin_url( 'admin-ajax.php' ); //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>',
								data: {
									action: 'bsearch_tag_search',
									tax: taxonomy,
									q: term
								},
								success: function( data ) {
									cache = data;

									response( data );
								}
							});

							last = request.term;

						},
						search: function() {
							// Custom minLength.
							var term = extractLast( this.value );

							if ( term.length < 2 ) {
								return false;
							}
						},
						focus: function( event, ui ) {
							// Prevent value inserted on focus.
							event.preventDefault();
						},
						select: function( event, ui ) {
							var terms = split( this.value );

							// Remove the last user input.
							terms.pop();

							// Add the selected item.
							terms.push( ui.item.value );

							// Add placeholder to get the comma-and-space at the end.
							terms.push( "" );
							this.value = terms.join( ", " );
							return false;
						}
					});

			};

			$( '.category_autocomplete' ).each( function ( i, element ) {
				$( element ).bsearchTagsSuggest();
			});

			// Prompt the user when they leave the page without saving the form.
			formmodified=0;

			$('form *').change(function(){
				formmodified=1;
			});

			window.onbeforeunload = confirmExit;

			function confirmExit() {
				if (formmodified == 1) {
					return "<?php esc_html__( 'New information not saved. Do you wish to leave the page?', 'where-did-they-go-from-here' ); ?>";
				}
			}

			$( "input[name='submit']" ).click( function() {
				formmodified = 0;
			});

			$( function() {
				$( "#post-body-content" ).tabs({
					create: function( event, ui ) {
						$( ui.tab.find("a") ).addClass( "nav-tab-active" );
					},
					activate: function( event, ui ) {
						$( ui.oldTab.find("a") ).removeClass( "nav-tab-active" );
						$( ui.newTab.find("a") ).addClass( "nav-tab-active" );
					}
				});
			});

		});

	//]]>
	</script>
	<?php
}


/**
 * Add rating links to the admin dashboard
 *
 * @since 2.2.0
 *
 * @param string $footer_text The existing footer text.
 * @return string Updated Footer text
 */
function bsearch_admin_footer( $footer_text ) {

	if ( get_current_screen()->parent_base === 'bsearch_options_page' ) {

		$text = sprintf(
			/* translators: 1: Better Search website, 2: Plugin reviews link. */
			__( 'Thank you for using <a href="%1$s" target="_blank">Better Search</a>! Please <a href="%2$s" target="_blank">rate us</a> on <a href="%2$s" target="_blank">WordPress.org</a>', 'better-search' ),
			'https://webberzone.com/better-search',
			'https://wordpress.org/support/plugin/better-search/reviews/#new-post'
		);

		return str_replace( '</span>', '', $footer_text ) . ' | ' . $text . '</span>';

	} else {

		return $footer_text;

	}
}
add_filter( 'admin_footer_text', 'bsearch_admin_footer' );


/**
 * Adding WordPress plugin action links.
 *
 * @version 1.9.2
 *
 * @param   array $links Action links.
 * @return  array   Links array with our settings link added.
 */
function bsearch_plugin_actions_links( $links ) {

	return array_merge(
		array(
			'settings' => '<a href="' . admin_url( 'options-general.php?page=bsearch_options_page' ) . '">' . __( 'Settings', 'better-search' ) . '</a>',
		),
		$links
	);

}
add_filter( 'plugin_action_links_' . plugin_basename( BETTER_SEARCH_PLUGIN_FILE ), 'bsearch_plugin_actions_links' );


/**
 * Add links to the plugin action row.
 *
 * @since   1.5
 *
 * @param   array $links Action links.
 * @param   array $file Plugin file name.
 * @return  array   Links array with our links added
 */
function bsearch_plugin_actions( $links, $file ) {
	$plugin = plugin_basename( BETTER_SEARCH_PLUGIN_FILE );

	if ( $file === $plugin ) {
		$links[] = '<a href="https://wordpress.org/support/plugin/better-search/">' . __( 'Support', 'better-search' ) . '</a>';
		$links[] = '<a href="https://ajaydsouza.com/donate/">' . __( 'Donate', 'better-search' ) . '</a>';
		$links[] = '<a href="https://github.com/WebberZone/better-search">' . __( 'Contribute', 'better-search' ) . '</a>';
	}
	return $links;
}
add_filter( 'plugin_row_meta', 'bsearch_plugin_actions', 10, 2 );


