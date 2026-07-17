<?php
/**
 * WB Plugins hub — landing dashboard at ?page=wbcomplugins.
 *
 * Lists every Wbcom plugin that has registered a submenu under the
 * shared wbcomplugins parent. Legacy wbcom-wrapper plugins register
 * boilerplate helper pages (Our Plugins, Our Themes, Support, License)
 * under this hub; those are not real plugins and are filtered out via
 * wbcom_hub_wrapper_helper_slugs. See playbook Part 16.
 *
 * @package    BuddyPress_Group_Review
 * @subpackage BuddyPress_Group_Review/admin
 * @since      3.9.0
 */

defined( 'ABSPATH' ) || exit;

$bgr_submenu_entries = isset( $GLOBALS['submenu']['wbcomplugins'] ) && is_array( $GLOBALS['submenu']['wbcomplugins'] )
	? $GLOBALS['submenu']['wbcomplugins']
	: array();

// Slugs of the legacy wbcom-wrapper boilerplate helper pages.
$bgr_wrapper_helper_slugs = apply_filters(
	'wbcom_hub_wrapper_helper_slugs',
	array(
		'wbcom-plugins-page',
		'wbcom-themes-page',
		'wbcom-support-page',
		'wbcom-license-page',
	)
);

$bgr_plugins = array();
foreach ( $bgr_submenu_entries as $bgr_entry ) {
	$bgr_slug = isset( $bgr_entry[2] ) ? (string) $bgr_entry[2] : '';
	if ( '' === $bgr_slug || 'wbcomplugins' === $bgr_slug ) {
		continue;
	}
	if ( in_array( $bgr_slug, $bgr_wrapper_helper_slugs, true ) ) {
		continue;
	}
	$bgr_plugins[] = array(
		'slug'       => $bgr_slug,
		'menu_title' => isset( $bgr_entry[0] ) ? wp_strip_all_tags( (string) $bgr_entry[0] ) : $bgr_slug,
		'page_title' => isset( $bgr_entry[3] ) ? wp_strip_all_tags( (string) $bgr_entry[3] ) : '',
		'url'        => admin_url( 'admin.php?page=' . rawurlencode( $bgr_slug ) ),
	);
}

$bgr_plugin_count = count( $bgr_plugins );
?>
<div class="wrap bgr-admin">
	<header class="bgr-page-header">
		<div class="bgr-page-header__title">
			<span class="dashicons dashicons-lightbulb" aria-hidden="true"></span>
			<div>
				<h1><?php esc_html_e( 'WB Plugins', 'bp-group-reviews' ); ?></h1>
				<p class="bgr-page-header__subtitle">
					<?php
					echo esc_html(
						sprintf(
							/* translators: %d: active Wbcom plugin count */
							_n(
								'%d Wbcom plugin active on this site.',
								'%d Wbcom plugins active on this site.',
								$bgr_plugin_count,
								'bp-group-reviews'
							),
							$bgr_plugin_count
						)
					);
					?>
				</p>
			</div>
		</div>
	</header>

	<?php if ( 0 === $bgr_plugin_count ) : ?>
		<div class="bgr-empty-state">
			<span class="bgr-empty-state__icon" aria-hidden="true">
				<span class="dashicons dashicons-lightbulb"></span>
			</span>
			<p class="bgr-empty-state__title"><?php esc_html_e( 'No Wbcom plugins attached to this hub yet', 'bp-group-reviews' ); ?></p>
			<p class="bgr-empty-state__desc">
				<?php esc_html_e( 'Activate one or more Wbcom plugins and they will appear here automatically.', 'bp-group-reviews' ); ?>
			</p>
		</div>
	<?php else : ?>
		<div class="bgr-hub-grid">
			<?php foreach ( $bgr_plugins as $bgr_plugin ) : ?>
				<a href="<?php echo esc_url( $bgr_plugin['url'] ); ?>" class="bgr-hub-card">
					<span class="bgr-hub-card__icon" aria-hidden="true">
						<span class="dashicons dashicons-admin-plugins"></span>
					</span>
					<span class="bgr-hub-card__title"><?php echo esc_html( $bgr_plugin['menu_title'] ); ?></span>
					<?php if ( ! empty( $bgr_plugin['page_title'] ) && $bgr_plugin['page_title'] !== $bgr_plugin['menu_title'] ) : ?>
						<span class="bgr-hub-card__subtitle"><?php echo esc_html( $bgr_plugin['page_title'] ); ?></span>
					<?php endif; ?>
					<span class="bgr-hub-card__cta">
						<?php esc_html_e( 'Open settings', 'bp-group-reviews' ); ?>
						<span class="dashicons dashicons-arrow-right-alt" aria-hidden="true"></span>
					</span>
				</a>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>

	<div class="bgr-card bgr-card--spaced">
		<div class="bgr-card__head">
			<p class="bgr-card__title"><?php esc_html_e( 'About WB Plugins', 'bp-group-reviews' ); ?></p>
		</div>
		<div class="bgr-card__body">
			<p class="bgr-card__body-text">
				<?php esc_html_e( 'This hub is the single entry point for every Wbcom Designs plugin installed on your site. Each plugin lives on its own page under this menu and keeps its own settings and data.', 'bp-group-reviews' ); ?>
			</p>
			<p class="bgr-card__body-text">
				<a href="https://wbcomdesigns.com/" target="_blank" rel="noopener noreferrer">
					<?php esc_html_e( 'Visit wbcomdesigns.com for more plugins and themes', 'bp-group-reviews' ); ?>
				</a>
			</p>
		</div>
	</div>
</div>
