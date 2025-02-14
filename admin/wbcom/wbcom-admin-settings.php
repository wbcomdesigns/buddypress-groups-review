<?php
/**
 * Class to add top header pages of Wbcom plugin and additional features.
 *
 * @author   Wbcom Designs
 * @package  Bp_Add_Group_Types
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Wbcom_Admin_Settings' ) ) {

	/**
	 * Class to add Wbcom plugin's admin settings.
	 *
	 * @since 2.0.0
	 */
	class Wbcom_Admin_Settings {

		/**
		 * Wbcom_Admin_Settings Constructor.
		 *
		 * @since 2.0.0
		 */
		public function __construct() {
			add_shortcode( 'wbcom_admin_setting_header', array( $this, 'wbcom_admin_setting_header_html' ) );
			add_action( 'admin_menu', array( $this, 'wbcom_admin_additional_pages' ), 999 );
			add_action( 'admin_enqueue_scripts', array( $this, 'wbcom_enqueue_admin_scripts' ) );
			add_action( 'wp_ajax_wbcom_addons_cards', array( $this, 'wbcom_addons_cards_links' ) );
		}

		/**
		 * Callback function for handling extension cards.
		 *
		 * @return void
		 */
		public function wbcom_addons_cards_links() {
			$wbcom_setting_nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
			$action              = isset( $_POST['action'] ) ? sanitize_text_field( wp_unslash( $_POST['action'] ) ) : '';
			
			if ( ! empty( $wbcom_setting_nonce ) && wp_verify_nonce( $wbcom_setting_nonce, 'wbcom_admin_setting_nonce' ) && 'wbcom_addons_cards' === $action ) {
				$display_extension = isset( $_POST['display_extension'] ) ? sanitize_text_field( wp_unslash( $_POST['display_extension'] ) ) : '';
				echo esc_html( $display_extension );
				wp_die();  // Updated from die() to wp_die() to align with WordPress standards.
			}
		}


		/**
		 * Function to upgrade a plugin.
		 *
		 * @since 2.0.0
		 * @param string $plugin_slug Plugin's slug.
		 * @return mixed
		 */
		public function upgrade_plugin( $plugin_slug ) {
			include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
			
			// Consider the necessity of this cache flush
			wp_cache_flush(); 

			$upgrader = new Plugin_Upgrader();
			$upgraded = $upgrader->upgrade( $plugin_slug );

			return $upgraded;
		}


		/**
		 * Get the download URL for a plugin from the WordPress repository.
		 *
		 * @since 2.0.0
		 * @param string $slug Plugin's slug.
		 * @return string|WP_Error
		 */
		public function get_download_url( $slug ) {
			return $this->get_wp_repo_download_url( $slug );
		}

		/**
		 * Retrieve the download URL for a plugin from the WordPress repository.
		 *
		 * @since 2.0.0
		 * @param string $slug Plugin's slug.
		 * @return string|WP_Error
		 */
		public function get_wp_repo_download_url( $slug ) {
			include_once ABSPATH . 'wp-admin/includes/plugin-install.php'; // Load necessary functions.

			$api = plugins_api(
				'plugin_information',
				array(
					'slug'   => $slug,
					'fields' => array( 'sections' => false ),
				)
			);

			if ( is_wp_error( $api ) ) {
				$error_message = $api->get_error_message();
				wp_send_json_error( array( 'error' => $error_message ) );
				return new WP_Error('api_error', $error_message);  // Return WP_Error for further handling if needed
			}

			return $api->download_link;
		}


		/**
		 * Check if a plugin is installed.
		 *
		 * @since 2.0.0
		 * @param string $slug Plugin's slug.
		 * @return bool
		 */
		public function wbcom_is_plugin_installed( $slug ) {
			if ( ! function_exists( 'get_plugins' ) ) {
				require_once ABSPATH . 'wp-admin/includes/plugin.php';
			}
			$all_plugins = get_plugins();
			$keys        = array_keys( $all_plugins );

			foreach ( $keys as $key ) {
				if ( preg_match( '|^' . $slug . '/|', $key ) ) {
					return true;
				}
			}

			return false;
		}

		/**
		 * Check the status of a plugin (activated, installed, or not installed).
		 *
		 * @since 2.0.0
		 * @param string $slug Plugin's slug.
		 * @return string
		 */
		public function wbcom_plugin_status( $slug ) {
			if ( $this->wbcom_is_plugin_installed( $slug ) ) {
				if ( $this->wbcom_is_plugin_active( $slug ) ) {
					return 'activated';
				} else {
					return 'installed';
				}
			} else {
				return 'not_installed';
			}
		}

		/**
		 * Check if a plugin is activated.
		 *
		 * @since 2.0.0
		 * @param string $slug Plugin's slug.
		 * @return bool
		 */
		public function wbcom_is_plugin_active( $slug ) {
			if ( ! function_exists( 'get_plugins' ) ) {
				require_once ABSPATH . 'wp-admin/includes/plugin.php';
			}
			$all_plugins = get_plugins();
			$keys        = array_keys( $all_plugins );

			foreach ( $keys as $key ) {
				if ( preg_match( '|^' . $slug . '/|', $key ) && is_plugin_active( $key ) ) {
					return true;
				}
			}

			return false;
		}

		/**
		 * Enqueue admin scripts and styles for Wbcom plugins.
		 *
		 * @since 2.0.0
		 */
		public function wbcom_enqueue_admin_scripts() {
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
			if ( ! wp_style_is( 'font-awesome', 'enqueued' ) ) {
				wp_enqueue_style( 'font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css' );
			}
			if ( ! wp_script_is( 'wbcom_admin_setting_js', 'enqueued' ) ) {
				wp_register_script(
					'wbcom_admin_setting_js',
					BGR_PLUGIN_URL . 'admin/wbcom/assets/js'.$script_path.'/wbcom-admin-setting'.$script_extension,
					array( 'jquery' ),
					time(),
					false
				);
				wp_localize_script(
					'wbcom_admin_setting_js',
					'wbcom_plugin_installer_params',
					array(
						'ajax_url'        => admin_url( 'admin-ajax.php' ),
						'activate_text'   => esc_html__( 'Activate', 'bp-group-reviews' ),
						'deactivate_text' => esc_html__( 'Deactivate', 'bp-group-reviews' ),
						'nonce'           => wp_create_nonce( 'wbcom_admin_setting_nonce' ),
					)
				);
				wp_enqueue_script( 'wbcom_admin_setting_js' );
			}

			if ( ! wp_style_is( 'wbcom-admin-setting-css', 'enqueued' ) ) {
				wp_enqueue_style( 'wbcom-admin-setting-css', BGR_PLUGIN_URL . 'admin/wbcom/assets/css'.$style_path.'/wbcom-admin-setting'.$style_extension );
			}
		}

		/**
		 * Add additional admin pages for Wbcom plugins.
		 *
		 * @since 2.0.0
		 */
		public function wbcom_admin_additional_pages() {
			add_submenu_page(
				'wbcomplugins',
				esc_html__( 'Our Plugins', 'bp-group-reviews' ),
				esc_html__( 'Our Plugins', 'bp-group-reviews' ),
				'manage_options',
				'wbcom-plugins-page',
				array( $this, 'wbcom_plugins_submenu_page_callback' )
			);
			add_submenu_page(
				'wbcomplugins',
				esc_html__( 'Our Themes', 'bp-group-reviews' ),
				esc_html__( 'Our Themes', 'bp-group-reviews' ),
				'manage_options',
				'wbcom-themes-page',
				array( $this, 'wbcom_themes_submenu_page_callback' )
			);
			add_submenu_page(
				'wbcomplugins',
				esc_html__( 'Support', 'bp-group-reviews' ),
				esc_html__( 'Support', 'bp-group-reviews' ),
				'manage_options',
				'wbcom-support-page',
				array( $this, 'wbcom_support_submenu_page_callback' )
			);
		}

		/**
		 * Include the Wbcom plugins list page.
		 *
		 * @since 2.0.0
		 */
		public function wbcom_plugins_submenu_page_callback() {
			if ( file_exists( plugin_dir_path( __FILE__ ) . 'templates/wbcom-plugins-page.php' ) ) {
				include plugin_dir_path( __FILE__ ) . 'templates/wbcom-plugins-page.php';
			} else {
				// Handle the case where the file is missing
				echo esc_html__( 'The plugins page template could not be found.', 'bp-group-reviews' );
			}
		}

		/**
		 * Include the themes list page.
		 *
		 * @since 2.0.0
		 */
		public function wbcom_themes_submenu_page_callback() {
			if ( file_exists( plugin_dir_path( __FILE__ ) . 'templates/wbcom-themes-page.php' ) ) {
				include plugin_dir_path( __FILE__ ) . 'templates/wbcom-themes-page.php';
			} else {
				// Handle the case where the file is missing
				echo esc_html__( 'The themes page template could not be found.', 'bp-group-reviews' );
			}
		}

		/**
		 * Include the support page.
		 *
		 * @since 2.0.0
		 */
		public function wbcom_support_submenu_page_callback() {
			if ( file_exists( plugin_dir_path( __FILE__ ) . 'templates/wbcom-support-page.php' ) ) {
				include plugin_dir_path( __FILE__ ) . 'templates/wbcom-support-page.php';
			} else {
				// Handle the case where the file is missing
				echo esc_html__( 'The support page template could not be found.', 'bp-group-reviews' );
			}
		}


		/**
		 * Shortcode to display the top menu header.
		 *
		 * @since 2.0.0
		 */
		public function wbcom_admin_setting_header_html() {
			$page          = filter_input( INPUT_GET, 'page' ) ? filter_input( INPUT_GET, 'page' ) : 'wbcom-themes-page';
			$plugin_active = $theme_active = $support_active = $settings_active = '';

			switch ( $page ) {
				case 'wbcom-plugins-page':
					$plugin_active = 'is_active';
					break;
				case 'wbcom-support-page':
					$support_active = 'is_active';
					break;
				case 'wbcom-license-page':
					$license_active = 'is_active';
					break;
				default:
					$settings_active = 'is_active';
			}
			?>
			<div id="wb_admin_header" class="wp-clearfix">
				<nav id="wb_admin_nav">
					<ul>
						<li class="wb_admin_nav_item <?php echo esc_attr( $settings_active ); ?>">
							<a href="<?php echo esc_url( get_admin_url() ) . 'admin.php?page=wbcomplugins'; ?>" id="wb_admin_nav_trigger_settings">
								<i class="fa fa-sliders" aria-hidden="true"></i>
								<h4><?php esc_html_e( 'Settings', 'bp-group-reviews' ); ?></h4>
							</a>
						</li>
						<li class="wb_admin_nav_item <?php echo esc_attr( $plugin_active ); ?>">
							<a href="<?php echo esc_url( get_admin_url() ) . 'admin.php?page=wbcom-plugins-page'; ?>" id="wb_admin_nav_trigger_extensions">
								<i class="fa fa-th" aria-hidden="true"></i>
								<h4><?php esc_html_e( 'Themes & Extensions', 'bp-group-reviews' ); ?></h4>
							</a>
						</li>
						<li class="wb_admin_nav_item <?php echo esc_attr( $support_active ); ?>">
							<a href="<?php echo esc_url( get_admin_url() ) . 'admin.php?page=wbcom-support-page'; ?>" id="wb_admin_nav_trigger_support">
								<i class="fa fa-question-circle" aria-hidden="true"></i>
								<h4><?php esc_html_e( 'Help & Support', 'bp-group-reviews' ); ?></h4>
							</a>
						</li>
						<?php do_action( 'wbcom_add_header_menu' ); ?>
					</ul>
				</nav>
			</div>
			<?php
		}


	}

	/**
	 * Initialize Wbcom plugin manager.
	 *
	 * @return void
	 */
	function instantiate_wbcom_plugin_manager() {
		new Wbcom_Admin_Settings();
	}

	instantiate_wbcom_plugin_manager();
}
