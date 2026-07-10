<?php
/**
 * Theme integration helper.
 *
 * Centralises "which theme is active + how do its design tokens map to the
 * plugin's --bgr-* tokens" in ONE place, instead of ad-hoc var() fallback
 * chains scattered across every CSS file (UI-013). CSS still carries the
 * fallback chains for portability; this class adds:
 *
 *   1. A body class (bgr-theme-{slug}) so stylesheets and site owners can
 *      target the active theme without sniffing the DOM.
 *   2. A single, filterable theme -> token map used by both PHP and (optionally)
 *      an emitted :root bridge, so new theme support is added here rather than
 *      in N stylesheets.
 *
 * @package    BuddyPress_Group_Review
 * @subpackage BuddyPress_Group_Review/includes
 * @since      3.9.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'BGR_Theme_Integration' ) ) {

	/**
	 * Detects the active theme and exposes its token mapping.
	 */
	class BGR_Theme_Integration {

		/**
		 * Singleton instance.
		 *
		 * @var BGR_Theme_Integration|null
		 */
		private static $instance = null;

		/**
		 * Get the singleton instance.
		 *
		 * @return BGR_Theme_Integration
		 */
		public static function get_instance() {
			if ( null === self::$instance ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 * Hook up the body class filters (front + admin).
		 */
		private function __construct() {
			add_filter( 'body_class', array( $this, 'add_body_class' ) );
			add_filter( 'admin_body_class', array( $this, 'add_admin_body_class' ) );
		}

		/**
		 * Active theme slug (template, so child themes resolve to the parent).
		 *
		 * @return string e.g. "buddyx", "buddyx-pro", "reign-theme", "storefront".
		 */
		public function get_active_theme_slug() {
			$slug = function_exists( 'get_template' ) ? (string) get_template() : '';
			return sanitize_html_class( strtolower( $slug ) );
		}

		/**
		 * Whether the active theme is a BuddyX / Reign family theme (the themes
		 * that expose data-bx-mode + --bx-color-* / --reign-* tokens).
		 *
		 * @return bool
		 */
		public function is_bx_family_theme() {
			$slug = $this->get_active_theme_slug();
			return (bool) preg_match( '/buddyx|reign/', $slug );
		}

		/**
		 * Map of plugin --bgr-* tokens to the active theme's source tokens.
		 *
		 * Keys are the plugin tokens; values are the ordered var() fallback
		 * chain (theme token first, dark literal last). Reign 8 exposes NO
		 * --bx-color-*, hence every chain ends in a literal that is safe for
		 * both families. Filterable so a site can add a bespoke theme.
		 *
		 * @return array<string,string>
		 */
		public function get_theme_token_map() {
			$map = array(
				'--bgr-text-primary' => 'var(--bx-color-fg, var(--reign-site-headings-color, inherit))',
				'--bgr-text-muted'   => 'var(--bx-color-fg-muted, var(--reign-site-alternate-text-color, inherit))',
				'--bgr-border-color' => 'var(--bx-color-border, var(--reign-site-border-color, currentColor))',
				'--bgr-bg-white'     => 'var(--bx-color-bg-elevated, var(--reign-site-sections-bg-color, inherit))',
				'--bgr-bg-light'     => 'var(--bx-color-bg-muted, var(--reign-site-secondary-bg-color, inherit))',
				'--bgr-link-color'   => 'var(--bx-color-accent, var(--reign-primary-color, inherit))',
			);

			/**
			 * Filter the theme -> plugin token map.
			 *
			 * @param array  $map  Token map.
			 * @param string $slug Active theme slug.
			 */
			return (array) apply_filters( 'bgr_theme_token_map', $map, $this->get_active_theme_slug() );
		}

		/**
		 * The body-class string the plugin adds for the active theme.
		 *
		 * @return string
		 */
		public function get_theme_support_class() {
			$slug = $this->get_active_theme_slug();
			return $slug ? 'bgr-theme-' . $slug : '';
		}

		/**
		 * Append the theme support class to the front-end body classes.
		 *
		 * @param array $classes Existing body classes.
		 * @return array
		 */
		public function add_body_class( $classes ) {
			$class = $this->get_theme_support_class();
			if ( $class ) {
				$classes[] = $class;
			}
			return $classes;
		}

		/**
		 * Append the theme support class to the admin body classes.
		 *
		 * @param string $classes Space-separated admin body classes.
		 * @return string
		 */
		public function add_admin_body_class( $classes ) {
			$class = $this->get_theme_support_class();
			if ( $class ) {
				$classes .= ' ' . $class;
			}
			return $classes;
		}
	}
}
