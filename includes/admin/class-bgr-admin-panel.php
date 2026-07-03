<?php
/**
 * BGR Admin: menu, enqueue, page rendering.
 *
 * Replaces the legacy Wbcom wrapper chrome (admin/wbcom/) with the
 * card-panel admin pattern. Follows the wp-plugin-development skill
 * Part 6 and references/wbcom-wrapper-migration.md playbook (Parts 5,
 * 6, 16).
 *
 * IMPORTANT — settings persistence is NOT changed by this migration:
 *   - General / Criteria / Display tabs save via the existing custom
 *     AJAX handlers in includes/bgr-ajax.php (each owns one option key
 *     and rebuilds it whole). The new views keep the SAME element IDs
 *     the admin JS reads, so saving is byte-identical to the old UI.
 *   - The Emails tab saves via options.php + register_setting(
 *     'bp_group_review_email_settings' ) — unchanged. Its form is
 *     rendered inside its own <form action="options.php">.
 *
 * Because each option key is owned by exactly one tab, there is no
 * multi-tab merge / sentinel concern (Playbook 7.1 does not apply).
 *
 * @package    BuddyPress_Group_Review
 * @subpackage BuddyPress_Group_Review/admin
 * @since      3.9.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class BGR_Admin_Panel
 *
 * @since 3.9.0
 */
class BGR_Admin_Panel {

	/**
	 * Menu slug — kept identical to the legacy page so existing
	 * bookmarks, the plugin "Settings" action link, and the
	 * activation redirect all keep working.
	 */
	const MENU_SLUG = 'group-review-settings';

	/**
	 * Option group + option name used by the Emails tab register_setting.
	 * Unchanged from the legacy implementation.
	 */
	const EMAIL_OPTION_GROUP = 'bp_group_review_email_settings';

	/**
	 * Sidebar tabs rendered inside the single admin page.
	 *
	 * @return array<string, array{label:string, icon:string, group:string}>
	 */
	public static function get_tabs() {
		$tabs = array(
			'welcome'  => array(
				'label' => __( 'Welcome', 'bp-group-reviews' ),
				'icon'  => 'dashicons-megaphone',
				'group' => 'main',
			),
			'general'  => array(
				'label' => __( 'General', 'bp-group-reviews' ),
				'icon'  => 'dashicons-admin-settings',
				'group' => 'settings',
			),
			'criteria' => array(
				'label' => __( 'Criteria', 'bp-group-reviews' ),
				'icon'  => 'dashicons-star-filled',
				'group' => 'settings',
			),
			'display'  => array(
				'label' => __( 'Display', 'bp-group-reviews' ),
				'icon'  => 'dashicons-admin-appearance',
				'group' => 'settings',
			),
			'emails'   => array(
				'label' => __( 'Emails', 'bp-group-reviews' ),
				'icon'  => 'dashicons-email',
				'group' => 'settings',
			),
		);
		return apply_filters( 'bgr_admin_tabs', $tabs );
	}

	/**
	 * Bootstrap hooks.
	 */
	public function register() {
		add_action( 'admin_menu', array( $this, 'add_menu' ) );
		// Priority 999 runs after every other plugin has registered its
		// admin_menu entries. Reclaims the hub landing render when a
		// legacy wbcom-wrapper plugin registered wbcomplugins first.
		add_action( 'admin_menu', array( $this, 'takeover_hub_landing' ), 999 );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_action( 'in_admin_header', array( $this, 'suppress_foreign_notices' ), 1 );
	}

	/**
	 * Attach as a single submenu under the shared WB Plugins hub.
	 */
	public function add_menu() {
		$cap = apply_filters( 'bgr_admin_capability', 'manage_options' );

		// First Wbcom plugin to load creates the shared WB Plugins hub.
		if ( empty( $GLOBALS['admin_page_hooks']['wbcomplugins'] ) ) {
			add_menu_page(
				esc_html__( 'WB Plugins', 'bp-group-reviews' ),
				esc_html__( 'WB Plugins', 'bp-group-reviews' ),
				$cap,
				'wbcomplugins',
				array( $this, 'render_hub' ),
				'dashicons-lightbulb',
				59
			);
		}

		add_submenu_page(
			'wbcomplugins',
			esc_html__( 'BuddyPress Group Reviews', 'bp-group-reviews' ),
			esc_html__( 'Group Reviews', 'bp-group-reviews' ),
			$cap,
			self::MENU_SLUG,
			array( $this, 'render_page' )
		);
	}

	/**
	 * Force the shared hub's landing page to render our card-panel
	 * dashboard, overriding whatever a legacy wbcom-wrapper plugin may
	 * have registered. See playbook Part 16.
	 */
	public function takeover_hub_landing() {
		global $admin_page_hooks;
		if ( empty( $admin_page_hooks['wbcomplugins'] ) ) {
			return;
		}
		remove_all_actions( 'toplevel_page_wbcomplugins' );
		add_action( 'toplevel_page_wbcomplugins', array( $this, 'render_hub' ) );
	}

	/**
	 * Register the Emails settings option. Sanitization stays with the
	 * existing global function so the save contract is unchanged.
	 */
	public function register_settings() {
		register_setting(
			self::EMAIL_OPTION_GROUP,
			self::EMAIL_OPTION_GROUP,
			array(
				'type'              => 'array',
				'sanitize_callback' => 'bp_group_review_sanitize_email_settings',
			)
		);
	}

	/**
	 * Enqueue admin assets only on our screen + the shared hub landing.
	 *
	 * The General/Criteria/Display tabs depend on the existing admin
	 * JS (bgr-admin.js), the color picker, selectize, and jquery-ui
	 * sortable — those are enqueued by BGRScriptsStyles for this same
	 * screen and are intentionally left in place. Here we only add the
	 * card-panel shell CSS + toast/confirm helper JS.
	 *
	 * @param string $hook_suffix Current admin page hook.
	 */
	public function enqueue_assets( $hook_suffix ) {
		unset( $hook_suffix );
		$screen = get_current_screen();
		if ( ! $screen || ! $this->is_our_screen( $screen ) ) {
			return;
		}

		$version = defined( 'BGR_PLUGIN_VERSION' ) ? BGR_PLUGIN_VERSION : '1.0.0';

		wp_enqueue_style(
			'bgr-admin-panel',
			BGR_PLUGIN_URL . 'assets/css/admin.css',
			array(),
			$version
		);

		wp_enqueue_script(
			'bgr-admin-panel',
			BGR_PLUGIN_URL . 'assets/js/admin.js',
			array( 'jquery', 'wp-i18n' ),
			$version,
			true
		);
		wp_set_script_translations( 'bgr-admin-panel', 'bp-group-reviews', BGR_PLUGIN_PATH . 'languages' );

		wp_localize_script(
			'bgr-admin-panel',
			'bgrAdminPanel',
			array(
				'i18n' => array(
					'confirmDanger'   => __( 'Are you sure? This cannot be undone.', 'bp-group-reviews' ),
					'confirmContinue' => __( 'Continue', 'bp-group-reviews' ),
					'confirmCancel'   => __( 'Cancel', 'bp-group-reviews' ),
					'denyReview'      => __( 'Are you sure you want to deny this review? It will be moved to trash.', 'bp-group-reviews' ),
				),
			)
		);
	}

	/**
	 * Suppress 3rd-party admin notices on our screen + the hub.
	 */
	public function suppress_foreign_notices() {
		$screen = get_current_screen();
		if ( ! $screen || ! $this->is_our_screen( $screen ) ) {
			return;
		}
		remove_all_actions( 'admin_notices' );
		remove_all_actions( 'all_admin_notices' );
	}

	/**
	 * True when the current screen is the plugin's admin page OR the
	 * shared hub landing (our callback owns the hub render too).
	 *
	 * @param WP_Screen $screen Current screen.
	 * @return bool
	 */
	private function is_our_screen( $screen ) {
		if ( empty( $screen->id ) ) {
			return false;
		}
		return (bool) preg_match( '/_page_' . preg_quote( self::MENU_SLUG, '/' ) . '$/', $screen->id )
			|| 'toplevel_page_wbcomplugins' === $screen->id;
	}

	/**
	 * Render the shared WB Plugins hub landing page.
	 */
	public function render_hub() {
		$view = BGR_PLUGIN_PATH . 'includes/admin/views/hub.php';
		if ( file_exists( $view ) ) {
			include $view;
		}
	}

	/**
	 * Render the single admin page. Routes to the active tab.
	 */
	public function render_page() {
		global $bgr;

		$bgr_tabs  = self::get_tabs();
		$tab_slugs = array_keys( $bgr_tabs );
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only tab switch.
		$active = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : $tab_slugs[0];
		if ( ! isset( $bgr_tabs[ $active ] ) ) {
			$active = $tab_slugs[0];
		}

		$page_url = admin_url( 'admin.php?page=' . self::MENU_SLUG );

		$view_map = array(
			'welcome'  => 'welcome',
			'general'  => 'settings-general',
			'criteria' => 'settings-criteria',
			'display'  => 'settings-display',
			'emails'   => 'settings-emails',
		);
		$view     = isset( $view_map[ $active ] ) ? $view_map[ $active ] : 'welcome';

		// The Emails tab provides its own <form action="options.php">;
		// the AJAX-saved tabs (general/criteria/display) provide their
		// own AJAX submit button. The shell therefore never wraps the
		// content in a form — each view owns its submit mechanism.
		$view_path = BGR_PLUGIN_PATH . 'includes/admin/views/' . $view . '.php';
		$shell     = BGR_PLUGIN_PATH . 'includes/admin/views/shell.php';

		include $shell;
	}
}
