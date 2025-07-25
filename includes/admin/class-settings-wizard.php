<?php
/**
 * Settings Wizard for Better Search.
 *
 * Provides a guided setup experience for new users.
 *
 * @since 4.2.0
 *
 * @package Better_Search
 */

namespace WebberZone\Better_Search\Admin;

use WebberZone\Better_Search\Util\Hook_Registry;
use WebberZone\Better_Search\Admin\Settings\Settings_Wizard_API;
use function WebberZone\Better_Search\better_search;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Settings Wizard class for Better Search.
 *
 * @since 4.2.0
 */
class Settings_Wizard extends Settings_Wizard_API {

	/**
	 * Main constructor class.
	 *
	 * @since 4.2.0
	 */
	public function __construct() {
		$settings_key = 'bsearch_settings';
		$prefix       = 'bsearch';

		$args = array(
			'steps'               => $this->get_wizard_steps(),
			'translation_strings' => $this->get_translation_strings(),
			'page_slug'           => 'bsearch_wizard',
			'menu_args'           => array(
				'parent'     => 'bsearch_dashboard',
				'capability' => 'manage_options',
			),
		);

		parent::__construct( $settings_key, $prefix, $args );

		$this->additional_hooks();
	}

	/**
	 * Additional hooks specific to Better Search.
	 *
	 * @since 4.2.0
	 */
	protected function additional_hooks() {
		Hook_Registry::add_action( 'bsearch_activate', array( $this, 'trigger_wizard_on_activation' ) );
		Hook_Registry::add_action( 'admin_init', array( $this, 'register_wizard_notice' ) );
	}

	/**
	 * Get wizard steps configuration.
	 *
	 * @since 4.2.0
	 *
	 * @return array Wizard steps.
	 */
	public function get_wizard_steps() {
		$all_settings_grouped = Settings::get_registered_settings();
		$all_settings         = array();
		foreach ( $all_settings_grouped as $section_settings ) {
			$all_settings = array_merge( $all_settings, $section_settings );
		}

		$basic_search_keys = array(
			'seamless',
			'enable_live_search',
			'use_fulltext',
			'limit',
			'highlight',
		);

		$content_tuning_keys = array(
			'post_types',
			'weight_title',
			'weight_content',
			'search_excerpt',
			'search_taxonomies',
			'search_meta',
			'search_authors',
			'search_comments',
		);

		$pro_features_keys = array(
			'use_custom_tables',
			'enable_like_fallback',
			'weight_excerpt',
			'weight_taxonomy_category',
			'weight_taxonomy_post_tag',
			'weight_taxonomy_default',
			'fuzzy_search_level',
		);

		return array(
			'welcome'        => array(
				'title'       => __( 'Welcome to Better Search', 'better-search' ),
				'description' => __( 'Thank you for installing Better Search! This wizard will help you configure the essential settings to get started quickly.', 'better-search' ),
				'settings'    => array(),
			),
			'basic_search'   => array(
				'title'       => __( 'Basic Search Settings', 'better-search' ),
				'description' => __( 'Configure the fundamental search behavior for your site.', 'better-search' ),
				'settings'    => $this->build_step_settings( $basic_search_keys, $all_settings ),
			),
			'content_tuning' => array(
				'title'       => __( 'Content Tuning', 'better-search' ),
				'description' => __( 'Fine-tune which content is included and how results are weighted.', 'better-search' ),
				'settings'    => $this->build_step_settings( $content_tuning_keys, $all_settings ),
			),
			'pro_settings'   => array(
				'title'       => __( 'Pro Settings', 'better-search' ),
				'description' => __( 'Upgrade to Better Search Pro to unlock advanced features such as additional weighting, LIKE fallback, custom tables, and more. <strong>Take your site search to the next level!</strong>', 'better-search' ) . '<br /><br /><a href="https://webberzone.com/plugins/better-search/pro/" target="_blank" class="button button-primary">' . __( 'Learn more about Pro', 'better-search' ) . '</a>',
				'settings'    => $this->build_step_settings( $pro_features_keys, $all_settings ),
			),
			'completion'     => array(
				'title'       => __( 'Setup Complete!', 'better-search' ),
				'description' => __( 'Your Better Search setup is now complete. You can always modify these settings later from the Settings page.', 'better-search' ),
				'settings'    => array(),
			),
		);
	}

	/**
	 * Build settings array for a wizard step from keys.
	 *
	 * @since 4.2.0
	 *
	 * @param array $keys Setting keys for this step.
	 * @param array $all_settings All settings array.
	 * @return array
	 */
	private function build_step_settings( $keys, $all_settings ) {
		$settings = array();
		foreach ( $keys as $key ) {
			if ( isset( $all_settings[ $key ] ) ) {
				$settings[ $key ] = $all_settings[ $key ];
			}
		}
		return $settings;
	}


	/**
	 * Get translation strings for the wizard.
	 *
	 * @since 4.2.0
	 *
	 * @return array Translation strings.
	 */
	protected function get_translation_strings() {
		return array(
			'wizard_title'    => __( 'Better Search Setup Wizard', 'better-search' ),
			'next_step'       => __( 'Continue', 'better-search' ),
			'previous_step'   => __( 'Go Back', 'better-search' ),
			'finish_setup'    => __( 'Complete Setup', 'better-search' ),
			'skip_wizard'     => __( 'Skip Setup Wizard', 'better-search' ),
			'step_of'         => __( 'Step %1$d of %2$d', 'better-search' ),
			'wizard_complete' => __( 'Setup Complete!', 'better-search' ),
			'setup_complete'  => __( 'Better Search has been configured successfully. Your search functionality is now ready to use!', 'better-search' ),
		);
	}

	/**
	 * Get post types options for the wizard.
	 *
	 * @since 4.2.0
	 *
	 * @return array Post types options.
	 */
	protected function get_post_types_options() {
		$post_types = get_post_types(
			array(
				'public' => true,
			),
			'objects'
		);

		$options = array();
		foreach ( $post_types as $post_type ) {
			$options[ $post_type->name ] = $post_type->labels->name;
		}

		return $options;
	}

	/**
	 * Get categories options for the wizard.
	 *
	 * @since 4.2.0
	 *
	 * @return array Categories options.
	 */
	protected function get_categories_options() {
		$categories = get_categories(
			array(
				'hide_empty' => false,
			)
		);

		$options = array();
		foreach ( $categories as $category ) {
			$options[ $category->term_id ] = $category->name;
		}

		return $options;
	}

	/**
	 * Trigger wizard on plugin activation.
	 *
	 * @since 4.2.0
	 */
	public function trigger_wizard_on_activation() {
		// Set a transient that will trigger the wizard on first admin page visit.
		// This works better than an option because it's temporary and won't persist
		// if the wizard is never accessed.
		set_transient( 'bsearch_show_wizard_activation_redirect', true, 30 );
	}

	/**
	 * Register the wizard notice with the Admin_Notices_API.
	 *
	 * @since 4.2.0
	 */
	public function register_wizard_notice() {
		// Get the Admin_Notices_API instance.
		$admin_notices_api = better_search()->admin->admin_notices_api;
		if ( ! $admin_notices_api ) {
			return;
		}

		$admin_notices_api->register_notice(
			array(
				'id'          => 'bsearch_wizard_notice',
				'message'     => sprintf(
					'<p>%s</p><p><a href="%s" class="button button-primary">%s</a></p>',
					esc_html__( 'Welcome to Better Search! Would you like to run the setup wizard to configure the plugin?', 'better-search' ),
					esc_url( admin_url( 'admin.php?page=bsearch_wizard' ) ),
					esc_html__( 'Run Setup Wizard', 'better-search' )
				),
				'type'        => 'info',
				'dismissible' => true,
				'capability'  => 'manage_options',
				'conditions'  => array(
					function () {
						// Only show if wizard is not completed, not dismissed, and activation flag is set.
						return ! $this->is_wizard_completed() &&
							! get_option( 'bsearch_wizard_notice_dismissed', false ) &&
							get_transient( 'bsearch_show_wizard_activation_redirect' );
					},
				),
			)
		);
	}


	/**
	 * Get the URL to redirect to after wizard completion.
	 *
	 * @since 4.2.0
	 *
	 * @return string Redirect URL.
	 */
	protected function get_completion_redirect_url() {
		return admin_url( 'admin.php?page=bsearch_options_page&tab=general' );
	}

	/**
	 * Override the render completion page to show Better Search specific content.
	 *
	 * @since 4.2.0
	 */
	protected function render_completion_page() {
		?>
		<div class="wrap wizard-wrap wizard-complete">
			<div class="wizard-completion-header">
				<h1><?php echo esc_html( $this->translation_strings['wizard_complete'] ); ?></h1>
				<p class="wizard-completion-message">
					<?php echo esc_html( $this->translation_strings['setup_complete'] ); ?>
				</p>
			</div>

			<div class="wizard-completion-content">
				<div class="wizard-completion-features">
					<h3><?php esc_html_e( 'What\'s Next?', 'better-search' ); ?></h3>
					<ul>
						<li><?php esc_html_e( 'Test your search functionality on the frontend', 'better-search' ); ?></li>
						<li><?php esc_html_e( 'Customize search templates if needed', 'better-search' ); ?></li>
						<li><?php esc_html_e( 'Monitor search statistics in the dashboard', 'better-search' ); ?></li>
						<li><?php esc_html_e( 'Fine-tune settings based on user behavior', 'better-search' ); ?></li>
					</ul>
				</div>

				<div class="wizard-completion-actions">
					<a href="<?php echo esc_url( $this->get_completion_redirect_url() ); ?>" class="button button-primary button-large">
						<?php esc_html_e( 'Go to Settings', 'better-search' ); ?>
					</a>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=bsearch_dashboard' ) ); ?>" class="button button-secondary">
						<?php esc_html_e( 'View Dashboard', 'better-search' ); ?>
					</a>
					<a href="<?php echo esc_url( home_url( '?s=test' ) ); ?>" class="button button-secondary" target="_blank">
						<?php esc_html_e( 'Test Search', 'better-search' ); ?>
					</a>
				</div>
			</div>
		</div>
		<?php
	}
}
