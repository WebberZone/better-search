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
use WebberZone\Better_Search\Admin\Settings;
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
		Hook_Registry::add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_custom_scripts' ) );
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

		$steps = array(
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
		);

		// Add custom tables indexing step if custom tables are enabled.
		if ( bsearch_get_option( 'use_custom_tables', false ) ) {
			$steps['custom_tables_index'] = array(
				'title'       => __( 'Index Custom Tables', 'better-search' ),
				'description' => __( 'Custom tables have been enabled. Index your content to improve search performance and enable advanced features.', 'better-search' ),
				'settings'    => array(),
				'custom_step' => true, // Flag to indicate this needs custom rendering.
			);
		}

		/**
		 * Filters the wizard steps.
		 *
		 * @since 4.2.0
		 *
		 * @param array $steps Wizard steps.
		 * @param array $all_settings All settings array.
		 * @param array $all_settings_grouped All settings grouped by section.
		 */
		return apply_filters( 'bsearch_settings_wizard_steps', $steps, $all_settings, $all_settings_grouped );
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
			'wizard_title'          => __( 'Better Search Setup Wizard', 'better-search' ),
			'next_step'             => __( 'Next Step', 'better-search' ),
			'previous_step'         => __( 'Previous Step', 'better-search' ),
			'finish_setup'          => __( 'Finish Setup', 'better-search' ),
			'skip_wizard'           => __( 'Skip Wizard', 'better-search' ),
			/* translators: %1$d: Current step number, %2$d: Total number of steps */
			'step_of'               => __( 'Step %1$d of %2$d', 'better-search' ),
			'steps_nav_aria_label'  => __( 'Setup Wizard Steps', 'better-search' ),
			'wizard_complete'       => __( 'Setup Complete!', 'better-search' ),
			'setup_complete'        => __( 'Better Search has been configured successfully. Your search functionality is now ready to use!', 'better-search' ),
			'go_to_settings'        => __( 'Go to Settings', 'better-search' ),
			/* translators: %s: Search query. */
			'tom_select_no_results' => __( 'No results found for "%s"', 'better-search' ),
		);
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
		set_transient( 'bsearch_show_wizard_activation_redirect', true, HOUR_IN_SECONDS );

		// Also set an option for more persistent storage in multisite environments.
		update_option( 'bsearch_show_wizard', true );
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
						$page = sanitize_key( (string) filter_input( INPUT_GET, 'page', FILTER_SANITIZE_FULL_SPECIAL_CHARS ) );

						// Only show if wizard is not completed, not dismissed, and activation flag is set.
						// Check both transient and option to ensure it works in multisite environments.
						return ! $this->is_wizard_completed() &&
							! get_option( 'bsearch_wizard_notice_dismissed', false ) &&
							( get_transient( 'bsearch_show_wizard_activation_redirect' ) || get_option( 'bsearch_show_wizard', false ) ) &&
							'bsearch_wizard' !== $page;
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
	 * Enqueue custom scripts for the wizard.
	 *
	 * @since 4.2.0
	 *
	 * @param string $hook Current admin page hook.
	 */
	public function enqueue_custom_scripts( $hook ) {
		if ( false === strpos( $hook, $this->page_slug ) ) {
			return;
		}

		// Check if we're on the custom tables indexing step.
		$step_config = $this->get_current_step_config();
		if ( ! empty( $step_config['custom_step'] ) ) {
			// Enqueue the reindex script from custom tables admin.
			wp_enqueue_script(
				'bsearch-reindex',
				BETTER_SEARCH_PLUGIN_URL . 'includes/pro/custom-tables/admin/js/reindex.js',
				array( 'jquery' ),
				BETTER_SEARCH_VERSION,
				true
			);

			// Localize script with necessary data.
			wp_localize_script(
				'bsearch-reindex',
				'bsearchReindexSettings',
				array(
					'ajaxurl'        => admin_url( 'admin-ajax.php' ),
					'nonce'          => wp_create_nonce( 'bsearch_reindex_nonce' ),
					'isNetworkAdmin' => is_network_admin(),
					'strings'        => array(
						'buttonText'  => __( 'Start Indexing', 'better-search' ),
						'clickToStop' => __( 'Stop Indexing', 'better-search' ),
						'starting'    => __( 'Starting indexing...', 'better-search' ),
						'completed'   => __( 'Indexing completed successfully!', 'better-search' ),
						'error'       => __( 'An error occurred during indexing.', 'better-search' ),
					),
				)
			);
		}
	}

	/**
	 * Override render_wizard_page to handle custom steps.
	 *
	 * @since 4.2.0
	 */
	public function render_wizard_page() {
		$this->current_step = $this->get_current_step();
		$step_config        = $this->get_current_step_config();

		if ( empty( $step_config ) ) {
			$this->render_completion_page();
			return;
		}

		// Check if this is a custom step.
		if ( ! empty( $step_config['custom_step'] ) ) {
			$this->render_custom_tables_step( $step_config );
			return;
		}

		// Use parent method for regular steps.
		parent::render_wizard_page();
	}

	/**
	 * Render the custom tables indexing step.
	 *
	 * @since 4.2.0
	 *
	 * @param array $step_config Step configuration.
	 */
	protected function render_custom_tables_step( $step_config ) {
		?>
		<div class="wrap wizard-wrap">
			<h1><?php echo esc_html( $this->translation_strings['wizard_title'] ); ?></h1>

			<?php $this->render_wizard_steps_navigation(); ?>

			<div class="wizard-progress">
				<div class="wizard-progress-bar">
					<div class="wizard-progress-fill" style="width: <?php echo esc_attr( (string) ( ( $this->current_step / $this->total_steps ) * 100 ) ); ?>%;"></div>
				</div>
				<p class="wizard-step-counter">
					<?php
					printf(
						esc_html( $this->translation_strings['step_of'] ),
						esc_html( (string) $this->current_step ),
						esc_html( (string) $this->total_steps )
					);
					?>
				</p>
			</div>

			<div class="wizard-content">
				<div class="wizard-step">
					<h2><?php echo esc_html( $step_config['title'] ?? '' ); ?></h2>
					
					<?php if ( ! empty( $step_config['description'] ) ) : ?>
						<p class="wizard-step-description"><?php echo wp_kses_post( $step_config['description'] ); ?></p>
					<?php endif; ?>

					<form method="post" action="">
						<?php wp_nonce_field( "{$this->prefix}_wizard_nonce", "{$this->prefix}_wizard_nonce" ); ?>
						
						<div class="wizard-fields">
							<?php $this->render_custom_tables_interface(); ?>
						</div>

						<div class="wizard-actions">
							<?php $this->render_wizard_buttons(); ?>
						</div>
					</form>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Render the custom tables indexing interface.
	 *
	 * @since 4.2.0
	 */
	protected function render_custom_tables_interface() {
		// Get custom tables admin instance if available.
		if ( ! class_exists( '\WebberZone\Better_Search\Pro\Custom_Tables\Custom_Tables_Admin' ) ) {
			?>
			<div class="notice notice-error inline">
				<p><?php esc_html_e( 'Custom tables functionality is not available.', 'better-search' ); ?></p>
			</div>
			<?php
			return;
		}

		// Get table manager instance with lazy admin initialization.
		$custom_tables = better_search()->pro->custom_tables ?? null;
		if ( ! $custom_tables ) {
			?>
			<div class="notice notice-error inline">
				<p><?php esc_html_e( 'Custom tables are not available.', 'better-search' ); ?></p>
			</div>
			<?php
			return;
		}

		$table_manager = $custom_tables->admin->table_manager;
		$percentage    = $table_manager->get_indexing_percentage();
		$content_count = $table_manager->get_content_count();
		$post_count    = $table_manager->get_post_count();

		// Check if indexing is in progress.
		$reindex_state = $custom_tables->admin->get_reindex_state();
		$is_running    = false;
		$progress      = 0;

		if ( false !== $reindex_state && isset( $reindex_state['status'] ) && 'running' === $reindex_state['status'] ) {
			$progress   = $reindex_state['total'] > 0 ? round( ( $reindex_state['offset'] / $reindex_state['total'] ) * 100 ) : 0;
			$is_running = true;
		}
		?>
		<div class="bsearch-wizard-reindex">
			<div class="bsearch-index-status-wrapper">
				<h3><?php esc_html_e( 'Current Index Status', 'better-search' ); ?></h3>
				<p>
					<?php
					printf(
						/* translators: 1: Number of posts in the content table */
						esc_html__( 'Content Table: %1$d entries', 'better-search' ),
						intval( $content_count )
					);
					echo '<br />';
					printf(
						/* translators: 1: Number of published posts, 2: Percentage of posts indexed */
						esc_html__( 'Published Posts: %1$d, Index Status: %2$d%%', 'better-search' ),
						intval( $post_count ),
						absint( $percentage )
					);
					?>
				</p>

				<div class="bsearch-index-status">
					<div class="bsearch-index-bar" style="width: <?php echo esc_attr( (string) $percentage ); ?>%; background-color: <?php echo $percentage >= 80 ? '#00a32a' : ( $percentage >= 40 ? '#dba617' : '#d63638' ); ?>;"></div>
					<span><?php echo absint( $percentage ); ?>%</span>
				</div>
			</div>

			<div class="bsearch-reindex-controls">
				<h3><?php esc_html_e( 'Index Management', 'better-search' ); ?></h3>
				<p><?php esc_html_e( 'Click the button below to start indexing your content for improved search performance.', 'better-search' ); ?></p>

			<!-- Use exact DOM element IDs expected by reindex.js -->
			<div class="bsearch-reindex-button-wrapper">
				<button type="button" id="bsearch-start-reindex" class="button button-primary">
					<?php esc_html_e( 'Start Indexing', 'better-search' ); ?>
				</button>
				<label for="bsearch_force_reindex" style="margin-left: 15px;">
					<input type="checkbox" id="bsearch_force_reindex" name="bsearch_force_reindex" value="1" />
					<?php esc_html_e( 'Force reindex (clear existing data)', 'better-search' ); ?>
				</label>
			</div>

			<!-- Progress container with exact IDs expected by reindex.js -->
			<div id="bsearch-reindex-progress-container" style="display: none; margin-top: 20px;">
				<div class="bsearch-progress-wrapper">
					<div id="bsearch-progress-bar" style="width: 0%; height: 20px; background: #0073aa; border-radius: 3px; transition: width 0.3s ease;"></div>
				</div>
				<p>
					<span id="bsearch-progress-text">0%</span>
					<span id="bsearch-reindex-status"></span>
				</p>
			</div>
			</div>

			<div class="bsearch-wizard-note">
				<p><strong><?php esc_html_e( 'Note:', 'better-search' ); ?></strong> <?php esc_html_e( 'You can skip this step and index your content later from the Tools page if needed.', 'better-search' ); ?></p>
			</div>
		</div>

		<style>
		.bsearch-wizard-reindex {
			max-width: 600px;
			margin: 0 auto;
		}
		.bsearch-index-status-wrapper,
		.bsearch-reindex-controls {
			margin-bottom: 30px;
			padding: 20px;
			border: 1px solid #ddd;
			background: #f9f9f9;
			border-radius: 4px;
		}
		.bsearch-index-status {
			position: relative;
			height: 20px;
			background: #e0e0e0;
			border-radius: 10px;
			margin: 10px 0;
			overflow: hidden;
		}
		.bsearch-index-bar {
			height: 100%;
			transition: width 0.3s ease;
			border-radius: 10px;
		}
		.bsearch-index-status span {
			position: absolute;
			top: 50%;
			left: 50%;
			transform: translate(-50%, -50%);
			font-size: 12px;
			font-weight: bold;
			color: #333;
		}
		.bsearch-progress-bar {
			height: 20px;
			background: #e0e0e0;
			border-radius: 10px;
			overflow: hidden;
			margin: 10px 0;
		}
		.bsearch-progress-fill {
			height: 100%;
			background: #0073aa;
			transition: width 0.3s ease;
			border-radius: 10px;
		}
		.bsearch-wizard-note {
			margin-top: 20px;
			padding: 15px;
			background: #fff3cd;
			border: 1px solid #ffeaa7;
			border-radius: 4px;
		}
		.bsearch-reindex-button-wrapper {
			margin: 15px 0;
		}
		</style>
		<?php
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
					<h3><?php esc_html_e( "What's Next?", 'better-search' ); ?></h3>
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
					<a href="<?php echo esc_url( home_url( '?s=about' ) ); ?>" class="button button-secondary" target="_blank">
						<?php esc_html_e( 'Test Search', 'better-search' ); ?>
					</a>
				</div>
			</div>
		</div>
		<?php
	}
}
