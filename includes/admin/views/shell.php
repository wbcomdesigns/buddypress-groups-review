<?php
/**
 * Admin page shell: page header, sidebar nav, body slot.
 *
 * Receives from BGR_Admin_Panel::render_page():
 *
 * @var array  $bgr_tabs  Tab registry keyed by slug.
 * @var string $active    Active tab slug.
 * @var string $page_url  Base URL (admin.php?page=group-review-settings).
 * @var string $view      View slug (e.g. 'welcome', 'settings-general').
 * @var string $view_path Absolute path to the active tab partial.
 *
 * Each tab partial owns its own submit mechanism (AJAX button for
 * general/criteria/display, an options.php <form> for emails), so the
 * shell does NOT wrap the body in a form.
 *
 * @package    BuddyPress_Group_Review
 * @subpackage BuddyPress_Group_Review/admin
 * @since      3.9.0
 */

defined( 'ABSPATH' ) || exit;

$bgr_version = defined( 'BGR_PLUGIN_VERSION' ) ? BGR_PLUGIN_VERSION : '';
?>
<div class="wrap bgr-admin">

	<header class="bgr-page-header">
		<div class="bgr-page-header__title">
			<span class="dashicons dashicons-star-filled" aria-hidden="true"></span>
			<div>
				<?php // Product name - a brand, deliberately not translatable. ?>
				<h1>BuddyPress Group Reviews</h1>
				<p class="bgr-page-header__subtitle"><?php esc_html_e( 'Let members rate and review your BuddyPress groups across multiple criteria, with admin approval, notifications, and activity integration.', 'bp-group-reviews' ); ?></p>
			</div>
		</div>
		<div class="bgr-page-header__actions">
			<?php if ( $bgr_version ) : ?>
				<span class="bgr-version-pill">v<?php echo esc_html( $bgr_version ); ?></span>
			<?php endif; ?>
		</div>
	</header>

	<?php
	/*
	 * Without this marker, core's common.js re-parents every .notice to
	 * sit right after the first <h1> it finds, which slots the "Settings
	 * saved" banner between our title and its subtitle.
	 */
	?>
	<hr class="wp-header-end">

	<div class="bgr-settings-layout">

		<aside class="bgr-settings-sidebar">
			<div class="bgr-settings-sidebar-brand">
				<span class="bgr-settings-brand-icon" aria-hidden="true">
					<span class="dashicons dashicons-star-filled"></span>
				</span>
				<div class="bgr-settings-brand-text">
					<p class="bgr-settings-brand-name"><?php esc_html_e( 'Group Reviews', 'bp-group-reviews' ); ?></p>
					<p class="bgr-settings-brand-sub"><?php esc_html_e( 'Plugin', 'bp-group-reviews' ); ?></p>
				</div>
			</div>
			<nav class="bgr-settings-sidebar-nav" aria-label="<?php esc_attr_e( 'Group Reviews navigation', 'bp-group-reviews' ); ?>">
				<?php
				$bgr_printed_groups = array();
				$bgr_group_labels   = array(
					'settings' => esc_html__( 'Settings', 'bp-group-reviews' ),
					'discover' => esc_html__( 'More Tools', 'bp-group-reviews' ),
				);
				foreach ( $bgr_tabs as $bgr_slug => $bgr_tab ) {
					$bgr_group = isset( $bgr_tab['group'] ) ? $bgr_tab['group'] : 'main';
					if ( 'main' !== $bgr_group && ! in_array( $bgr_group, $bgr_printed_groups, true ) ) {
						echo '<div class="bgr-snav-divider" role="separator"></div>';
						if ( isset( $bgr_group_labels[ $bgr_group ] ) ) {
							echo '<p class="bgr-snav-section-label">' . esc_html( $bgr_group_labels[ $bgr_group ] ) . '</p>';
						}
						$bgr_printed_groups[] = $bgr_group;
					}
					$bgr_classes  = 'bgr-snav-link';
					$bgr_classes .= $active === $bgr_slug ? ' bgr-snav-link--active' : '';
					echo '<a href="' . esc_url( $page_url . '&tab=' . $bgr_slug ) . '" class="' . esc_attr( $bgr_classes ) . '">';
					echo '<span class="dashicons ' . esc_attr( $bgr_tab['icon'] ) . '" aria-hidden="true"></span>';
					echo esc_html( $bgr_tab['label'] );
					echo '</a>';
				}
				?>

				<div class="bgr-snav-divider" role="separator"></div>
				<p class="bgr-snav-section-label"><?php esc_html_e( 'Resources', 'bp-group-reviews' ); ?></p>
				<a href="https://docs.wbcomdesigns.com/docs/buddypress-group-review/" class="bgr-snav-link" target="_blank" rel="noopener noreferrer">
					<span class="dashicons dashicons-book" aria-hidden="true"></span>
					<?php esc_html_e( 'Documentation', 'bp-group-reviews' ); ?>
					<span class="dashicons dashicons-external bgr-snav-link__ext" aria-hidden="true"></span>
					<span class="screen-reader-text"><?php esc_html_e( '(opens in a new tab)', 'bp-group-reviews' ); ?></span>
				</a>
			</nav>
		</aside>

		<div class="bgr-settings-main">
			<?php
			settings_errors();

			/*
			 * AJAX-saved tabs (general/criteria/display) show a saved
			 * banner via JS; this static banner mirrors the legacy
			 * "#bgr-settings-updated" element the admin JS toggles.
			 */
			?>
			<div id="bgr-settings-updated" class="notice notice-success is-dismissible bgr-ajax-saved-notice" style="display:none;">
				<p><strong><?php esc_html_e( 'Settings saved.', 'bp-group-reviews' ); ?></strong></p>
			</div>

			<?php
			if ( file_exists( $view_path ) ) {
				include $view_path;
			}
			?>
		</div>

	</div>
</div>
