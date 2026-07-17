<?php
/**
 * Discover partial: ecosystem cross-promotion (read-only display view).
 *
 * Rendered inside shell.php. This is a pure presentation view — product
 * cards linking out to other free Wbcom Designs tools. No forms, no
 * settings, no options, no AJAX.
 *
 * @package    BuddyPress_Group_Review
 * @subpackage BuddyPress_Group_Review/admin
 * @since      3.9.0
 */

defined( 'ABSPATH' ) || exit;

$bgr_ecosystem_img = BGR_PLUGIN_URL . 'assets/images/ecosystem/';

/*
 * Each product: brand mark (shipped in assets/images/ecosystem/), name,
 * a short admin-specific blurb (worded differently from readme.txt), and
 * the wbcomdesigns.com download URL.
 */
$bgr_ecosystem = array(
	array(
		'name' => __( 'BuddyX', 'bp-group-reviews' ),
		'logo' => 'buddyx.png',
		'icon' => 'admin-appearance',
		'desc' => __( 'A free, fast community theme for BuddyPress, BuddyBoss and PeepSo with a modern layout and dark mode.', 'bp-group-reviews' ),
		'url'  => 'https://wbcomdesigns.com/downloads/buddyx-theme/',
	),
	array(
		'name' => __( 'BuddyNext', 'bp-group-reviews' ),
		'logo' => 'buddynext.svg',
		'desc' => __( 'Spin up a complete BuddyPress social network with activity feeds, member profiles, and private messaging.', 'bp-group-reviews' ),
		'url'  => 'https://wbcomdesigns.com/downloads/buddynext/',
	),
	array(
		'name' => __( 'Jetonomy', 'bp-group-reviews' ),
		'logo' => 'jetonomy.svg',
		'desc' => __( 'Add self-moderating forums and Q&A spaces where trust levels keep discussions clean at scale.', 'bp-group-reviews' ),
		'url'  => 'https://wbcomdesigns.com/downloads/jetonomy/',
	),
	array(
		'name' => __( 'Mediaverse', 'bp-group-reviews' ),
		'logo' => 'mediaverse.svg',
		'desc' => __( 'Let members post photos and video to a shared gallery, with AI moderation screening every upload.', 'bp-group-reviews' ),
		'url'  => 'https://wbcomdesigns.com/downloads/mediaverse/',
	),
	array(
		'name' => __( 'Eventonomy', 'bp-group-reviews' ),
		'logo' => 'eventonomy.svg',
		'icon' => 'calendar-alt',
		'desc' => __( 'Run community events with RSVPs, calendars, and front-end submissions.', 'bp-group-reviews' ),
		'url'  => 'https://wbcomdesigns.com/downloads/eventonomy/',
	),
	array(
		'name' => __( 'WB Gamification', 'bp-group-reviews' ),
		'logo' => 'wb-gamification.svg',
		'icon' => 'awards',
		'desc' => __( 'Reward members with points, badges, and leaderboards to keep engagement high.', 'bp-group-reviews' ),
		'url'  => 'https://wbcomdesigns.com/downloads/wordpress-gamification-plugin/',
	),
	array(
		'name' => __( 'Listora', 'bp-group-reviews' ),
		'logo' => 'listora.svg',
		'desc' => __( 'Publish searchable directories using ten ready-made listing types straight out of the box.', 'bp-group-reviews' ),
		'url'  => 'https://wbcomdesigns.com/downloads/listora/',
	),
	array(
		'name' => __( 'WP Career Board', 'bp-group-reviews' ),
		'logo' => 'wp-career-board.svg',
		'icon' => 'businessman',
		'desc' => __( 'Add a job board with front-end listings, applications, and employer profiles.', 'bp-group-reviews' ),
		'url'  => 'https://wbcomdesigns.com/downloads/wp-career-board/',
	),
	array(
		'name' => __( 'Learnomy', 'bp-group-reviews' ),
		'logo' => 'learnomy.svg',
		'desc' => __( 'Turn your community into a learning hub with a free, full-featured course platform.', 'bp-group-reviews' ),
		'url'  => 'https://wbcomdesigns.com/downloads/learnomy/',
	),
	array(
		'name' => __( 'WP Sell Services', 'bp-group-reviews' ),
		'logo' => 'wp-sell-services.svg',
		'icon' => 'cart',
		'desc' => __( 'A free service marketplace with vendor dashboards, an 11-status order flow, and Stripe or PayPal built in.', 'bp-group-reviews' ),
		'url'  => 'https://wbcomdesigns.com/downloads/wp-sell-services/',
	),
);
?>

<div class="bgr-card">
	<div class="bgr-card__head">
		<p class="bgr-card__title"><?php esc_html_e( 'More Free Tools from Wbcom Designs', 'bp-group-reviews' ); ?></p>
		<p class="bgr-card__desc"><?php esc_html_e( 'Group Reviews adds ratings and reputation to your groups. These free plugins from Wbcom Designs build out everything around them: the theme and network itself, forums, media, events, gamification, directories, jobs, courses, and services.', 'bp-group-reviews' ); ?></p>
	</div>
	<div class="bgr-card__body">
		<div class="bgr-discover-grid">
			<?php foreach ( $bgr_ecosystem as $bgr_product ) : ?>
				<div class="bgr-discover-card">
					<span class="bgr-discover-card__logo" aria-hidden="true">
						<img src="<?php echo esc_url( $bgr_ecosystem_img . $bgr_product['logo'] ); ?>" alt="<?php echo esc_attr( $bgr_product['name'] ); ?>" width="52" height="52" loading="lazy" />
					</span>
					<h3 class="bgr-discover-card__title"><?php echo esc_html( $bgr_product['name'] ); ?></h3>
					<p class="bgr-discover-card__desc"><?php echo esc_html( $bgr_product['desc'] ); ?></p>
					<a class="bgr-btn bgr-btn-secondary bgr-discover-card__cta" href="<?php echo esc_url( $bgr_product['url'] ); ?>" target="_blank" rel="noopener">
						<?php esc_html_e( 'Get it free', 'bp-group-reviews' ); ?>
						<span class="dashicons dashicons-external" aria-hidden="true"></span>
						<span class="screen-reader-text">
							<?php
							/* translators: %s: product name. */
							echo esc_html( sprintf( __( '%s (opens in a new tab)', 'bp-group-reviews' ), $bgr_product['name'] ) );
							?>
						</span>
					</a>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</div>
