<?php
/**
 * Class to add custom scripts and styles.
 *
 * @link       https://wbcomdesigns.com/
 * @since      1.0.0
 *
 * @package    BuddyPress_Group_Review
 * @subpackage BuddyPress_Group_Review/includes
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;


if ( ! class_exists( 'BGRScriptsStyles' ) ) {
	/**
	 * Class to add custom scripts and styles.
	 *
	 * @link       https://wbcomdesigns.com/
	 * @since      1.0.0
	 *
	 * @package    BuddyPress_Group_Review
	 * @subpackage BuddyPress_Group_Review/includes
	 */
	class BGRScriptsStyles {

		/**
		 * Constructor
		 *
		 * @since   1.0.0
		 * @author  Wbcom Designs
		 *
		 * @return void
		 */
		public function __construct() {

			// Add Scripts only on reviews tab.
			add_action( 'wp_enqueue_scripts', array( $this, 'bp_group_review_custom_variables' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'bp_group_review_admin_custom_variables' ) );
		}

		/**
		 * Check if BGR widgets are active on the current page
		 *
		 * @since   1.0.0
		 * @author  Wbcom Designs
		 *
		 * @return bool
		 */
		private function bp_group_review_is_widget_active() {
			// Get all active widgets from all sidebars

			$sidebars_widgets = function_exists( 'wp_get_sidebars_widgets' ) ? wp_get_sidebars_widgets() : array(); //phpcs:ignore

			if ( empty( $sidebars_widgets ) ) {
				return false;
			}

			foreach ( $sidebars_widgets as $sidebar_id => $widgets ) {
				if ( is_array( $widgets ) ) {
					foreach ( $widgets as $widget_id ) {
						// Check for group review widgets by widget ID prefix
						if ( strpos( $widget_id, 'bgr_group_review_widget' ) === 0 || strpos( $widget_id, 'bgr_group_rating_widget' ) === 0 ) {
							return true;
						}
					}
				}
			}

			// Also check if we're on BuddyPress pages where widgets might be used
			if ( function_exists( 'bp_is_activity_component' ) && bp_is_activity_component() ) {
				return true;
			}
			if ( function_exists( 'bp_is_groups_component' ) && bp_is_groups_component() ) {
				return true;
			}
			if ( function_exists( 'bp_is_group' ) && bp_is_group() ) {
				return true;
			}

			return false;
		}

		/**
		 * Actions performed for enqueuing scripts and styles for site front.
		 *
		 * @since   1.0.0
		 * @author  Wbcom Designs
		 *
		 * @return void
		 */
		public function bp_group_review_custom_variables() {
			// Set up file extensions based on debug mode.
			if ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) {
				$script_extension = '.js';
				$script_path      = '';
			} else {
				$script_extension = '.min.js';
				$script_path      = '/min';
			}
			if ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) {
				$style_extension = is_rtl() ? '.rtl.css' : '.css';
				$style_path      = is_rtl() ? '/rtl' : '';
			} else {
				$style_extension = is_rtl() ? '.rtl.css' : '.min.css';
				$style_path      = is_rtl() ? '/rtl' : '/min';
			}

			// Register bgr-ratings-css globally so dynamic inline styles can attach to it.
			// This is required for the rating color setting to work via wp_add_inline_style().
			wp_register_style( 'bgr-ratings-css', BGR_PLUGIN_URL . 'assets/css' . $style_path . '/bgr-ratings' . $style_extension, array(), BGR_PLUGIN_VERSION, 'all' );

			// Enqueue on BuddyPress group pages for rating color to display.
			$should_enqueue_ratings_css = false;

			// Single group page.
			if ( function_exists( 'bp_is_group' ) && bp_is_group() ) {
				$should_enqueue_ratings_css = true;
			}

			// Groups directory page.
			if ( function_exists( 'bp_is_groups_directory' ) && bp_is_groups_directory() ) {
				$should_enqueue_ratings_css = true;
			}

			// Groups component (covers all group-related pages).
			if ( function_exists( 'bp_is_groups_component' ) && bp_is_groups_component() ) {
				$should_enqueue_ratings_css = true;
			}

			// Activity component (reviews might appear in activity stream).
			if ( function_exists( 'bp_is_activity_component' ) && bp_is_activity_component() ) {
				$should_enqueue_ratings_css = true;
			}

			if ( $should_enqueue_ratings_css ) {
				wp_enqueue_style( 'bgr-ratings-css' );
			}

			// Only enqueue full scripts when BGR widgets are available
			if ( $this->bp_group_review_is_widget_active() ) {
				// Replaced FontAwesome CDN with local CSS-based icon styles for WordPress.org compliance.
				wp_register_style( 'bgr-icons', BGR_PLUGIN_URL . 'assets/css/bgr-icons.css', array(), BGR_PLUGIN_VERSION, 'all' );
				wp_register_style( 'bgr-reviews-css', BGR_PLUGIN_URL . 'assets/css' . $style_path . '/bgr-reviews' . $style_extension, array(), BGR_PLUGIN_VERSION, 'all' );
				wp_register_style( 'bgr-front-css', BGR_PLUGIN_URL . 'assets/css' . $style_path . '/bgr-front' . $style_extension, array(), BGR_PLUGIN_VERSION, 'all' );

				wp_register_script( 'bgr-front-js', BGR_PLUGIN_URL . 'assets/js' . $script_path . '/bgr-front' . $script_extension, array( 'jquery' ), BGR_PLUGIN_VERSION, false );
				wp_register_script( 'bgr-ratings-js', BGR_PLUGIN_URL . 'assets/js' . $script_path . '/bgr-ratings' . $script_extension, array( 'jquery' ), BGR_PLUGIN_VERSION, false );
				wp_register_script( 'bgr-accessibility-js', BGR_PLUGIN_URL . 'assets/js/bgr-accessibility.js', array( 'jquery' ), BGR_PLUGIN_VERSION, true );

				wp_enqueue_style( 'bgr-icons' ); // Local CSS-based icons (replaces FontAwesome CDN)
				wp_enqueue_style( 'bgr-reviews-css' );
				wp_enqueue_style( 'bgr-front-css' );
				wp_enqueue_script( 'bgr-front-js' );
				wp_enqueue_script( 'bgr-accessibility-js' );

				$bgr_admin_settings         = get_option( 'bgr_admin_general_settings' );
				$bgr_admin_display_settings = get_option( 'bgr_admin_display_settings' );
				$exclude_groups             = isset( $bgr_admin_settings['exclude_groups'] ) ? array_map( 'absint', (array) $bgr_admin_settings['exclude_groups'] ) : array();
				$current_group_id           = absint( bp_get_current_group_id() );
				if ( groups_is_user_admin( bp_loggedin_user_id(), $current_group_id ) ) {
					$group_admin = 'yes';
				} else {
					$group_admin = 'no';
				}
				if ( in_array( $current_group_id, $exclude_groups, true ) ) {
					$exclude_groups = 'true';
				} else {
					$exclude_groups = 'false';
				}
				$auto_approve_reviews = '';
				if ( isset( $bgr_admin_settings['auto_approve_reviews'] ) && 'yes' == $bgr_admin_settings['auto_approve_reviews'] ) {
					$auto_approve_reviews = $bgr_admin_settings['auto_approve_reviews']; // hide review-management tab when moderation in disbale.
				}
				wp_localize_script(
					'bgr-front-js',
					'bgr_front_js_object',
					array(
						'view_more_text'       => esc_html__( 'View More..', 'bp-group-reviews' ),
						'view_less_text'       => esc_html__( 'View Less..', 'bp-group-reviews' ),
						'wbcom_nonce'          => wp_create_nonce( 'ajax-nonce' ),
						'auto_approve_reviews' => $auto_approve_reviews,
						'exclude_groups'       => $exclude_groups,
						'review_label'         => isset( $bgr_admin_display_settings['review_label'] ) ? strtolower( $bgr_admin_display_settings['review_label'] ) : esc_html( strtolower( 'Review' ) ),
						'group_id'             => bp_get_current_group_id(),
						'check_group_admin'    => $group_admin,
					)
				);
				wp_enqueue_style( 'bgr-ratings-css' );
				wp_enqueue_script( 'bgr-ratings-js' );
			}

			// Enqueue group criteria admin script on group admin pages.
			if ( function_exists( 'bp_is_group' ) && bp_is_group() && bp_is_group_admin_page() ) {
				wp_register_script(
					'bgr-group-criteria-admin-js',
					BGR_PLUGIN_URL . 'assets/js' . $script_path . '/bgr-group-criteria-admin' . $script_extension,
					array( 'jquery' ),
					BGR_PLUGIN_VERSION,
					true
				);
				wp_enqueue_script( 'bgr-group-criteria-admin-js' );
				wp_localize_script(
					'bgr-group-criteria-admin-js',
					'bgr_group_criteria',
					array(
						'ajaxurl' => admin_url( 'admin-ajax.php' ),
						'nonce'   => wp_create_nonce( 'bgr_group_criteria_nonce' ),
					)
				);
			}
		}

		/**
		 * Actions performed for enqueuing scripts and styles for admin page.
		 *
		 * @since 1.0.0
		 * @author Wbcom Designs
		 *
		 * @return void
		 */
		public function bp_group_review_admin_custom_variables() {

			if ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) {
				$script_extension = '.js';
				$script_path      = '';
			} else {
				$script_extension = '.min.js';
				$script_path      = '/min';
			}
			if ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) {
				$style_extension = is_rtl() ? '.rtl.css' : '.css';
				$style_path      = is_rtl() ? '/rtl' : '';
			} else {
				$style_extension = is_rtl() ? '.rtl.css' : '.min.css';
				$style_path      = is_rtl() ? '/rtl' : '/min';
			}
			$curr_url = isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
			$screen   = get_current_screen();
			$page     = filter_input( INPUT_GET, 'page' );

			if ( ( 'review' == get_post_type() ) || ( 'wb-plugins_page_group-review-settings' == $screen->base ) || ( 'group-review-settings' == $page ) || 'toplevel_page_wbcomplugins' == $screen->base ) {

				wp_register_script( 'bgr-js-admin', BGR_PLUGIN_URL . 'admin/assets/js' . $script_path . '/bgr-admin' . $script_extension, array( 'jquery' ), BGR_PLUGIN_VERSION, false );
				wp_enqueue_script( 'bgr-js-admin' );
				wp_localize_script(
					'bgr-js-admin',
					'bgr_admin_js',
					array(
						'ajax_url'        => admin_url( 'admin-ajax.php' ),
						'activate_text'   => esc_html__( 'Activate', 'bp-group-reviews' ),
						'deactivate_text' => esc_html__( 'Deactivate', 'bp-group-reviews' ),
						'wbcom_nonce'     => wp_create_nonce( 'admin-ajax-nonce' ),
					)
				);

				wp_enqueue_style( 'wp-color-picker' );
				wp_register_script( 'bgr-colorpicker-handle', BGR_PLUGIN_URL . 'admin/assets/js' . $script_path . '/bgr-colorpicker' . $script_extension, array( 'wp-color-picker' ), BGR_PLUGIN_VERSION, false );
				wp_enqueue_script( 'bgr-colorpicker-handle' );

				if ( ! wp_style_is( 'wbcom-selectize-css', 'enqueued' ) ) {
					wp_register_style( 'wbcom-selectize-css', BGR_PLUGIN_URL . 'admin/assets/css/vendor/selectize.css', array(), BGR_PLUGIN_VERSION, 'all' );
					wp_enqueue_style( 'wbcom-selectize-css' );
				}

				wp_register_style( 'bgr-css-admin', BGR_PLUGIN_URL . 'admin/assets/css' . $style_path . '/bgr-admin' . $style_extension, array(), BGR_PLUGIN_VERSION, 'all' );
				wp_enqueue_style( 'bgr-css-admin' );

				if ( ! wp_script_is( 'wbcom-selectize-js', 'enqueued' ) ) {
					wp_register_script( 'wbcom-selectize-js', BGR_PLUGIN_URL . 'admin/assets/js/vendor/selectize.min.js', array( 'jquery' ), BGR_PLUGIN_VERSION, false );
					wp_enqueue_script( 'wbcom-selectize-js' );
				}

				if ( ! wp_script_is( 'jquery-ui-sortable', 'enqueued' ) ) {
					wp_enqueue_script( 'jquery-ui-sortable' );
				}
			}
		}
	}
	new BGRScriptsStyles();
}
