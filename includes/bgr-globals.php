<?php
/**
 * Group Review Plugin Global Variables
 *
 * @since   1.0.0
 * @author  Wbcom Designs
 *
 * @package    BuddyPress_Group_Review
 * @subpackage BuddyPress_Group_Review/includes
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * The rating criteria a fresh install ships with.
 *
 * These strings are IDENTIFIERS, not display copy. They are persisted as the
 * array keys of the `review_star_rating` post meta and are compared with
 * in_array() / isset() / array_key_exists() throughout the plugin (see
 * BGR_Group_Criteria::criterion_has_reviews()). They must therefore never be
 * translated at rest: wrapping them in __() would make every stored rating key
 * locale-dependent, and a site that changed its language would orphan every
 * rating ever submitted. Translate at render time with
 * bp_group_review_get_criteria_label() instead.
 *
 * @since   3.9.0
 * @return  string[] Untranslated criterion identifiers.
 */
function bp_group_review_get_default_criteria() {
	return array( 'Quality', 'Relevance', 'Engagement' );
}

/**
 * Translate a criterion identifier for display.
 *
 * Render-time lookup only - the stored key is never rewritten. Only the shipped
 * defaults have translations; a criterion a site owner typed in themselves is
 * their own copy and is returned verbatim.
 *
 * Do NOT use this anywhere the value is written back as an identifier - the
 * global criteria editor (admin/views/settings-criteria.php) and the per-group
 * criteria checkboxes must keep emitting the raw key, or saving the form in a
 * non-English locale would rewrite the stored keys.
 *
 * @since   3.9.0
 * @param   string $name Stored criterion identifier.
 * @return  string Translated label, or $name when there is nothing to translate.
 */
function bp_group_review_get_criteria_label( $name ) {
	$labels = array(
		'Quality'    => __( 'Quality', 'bp-group-reviews' ),
		'Relevance'  => __( 'Relevance', 'bp-group-reviews' ),
		'Engagement' => __( 'Engagement', 'bp-group-reviews' ),
	);

	return isset( $labels[ $name ] ) ? $labels[ $name ] : $name;
}

/**
 * Group Review Plugin Global Variables
 *
 *  @since   1.0.0
 *  @author  Wbcom Designs
 */
function bp_group_review_globals() {
	global $bgr;
	$bgr_admin_general_settings     = get_option( 'bgr_admin_general_settings' );
	$bgr_admin_criteria_settings    = get_option( 'bgr_admin_criteria_settings' );
	$bgr_admin_display_settings     = get_option( 'bgr_admin_display_settings' );
	$bp_group_review_email_settings = get_option( 'bp_group_review_email_settings' );

	/**** Global variable values for General settings */

	if ( ! empty( $bgr_admin_general_settings ) ) {
		$auto_approve_reviews  = isset( $bgr_admin_general_settings['auto_approve_reviews'] ) ? $bgr_admin_general_settings['auto_approve_reviews'] : 'no';
		$reviews_per_page      = isset( $bgr_admin_general_settings['reviews_per_page'] ) ? $bgr_admin_general_settings['reviews_per_page'] : 5;
		$allow_notification    = isset( $bgr_admin_general_settings['allow_notification'] ) ? $bgr_admin_general_settings['allow_notification'] : 'yes';
		$allow_activity        = isset( $bgr_admin_general_settings['allow_activity'] ) ? $bgr_admin_general_settings['allow_activity'] : 'yes';
		$exclude_groups        = isset( $bgr_admin_general_settings['exclude_groups'] ) ? $bgr_admin_general_settings['exclude_groups'] : array();
		$multi_reviews         = isset( $bgr_admin_general_settings['multi_reviews'] ) ? $bgr_admin_general_settings['multi_reviews'] : 'no';
		$enable_group_criteria = isset( $bgr_admin_general_settings['enable_group_criteria'] ) ? $bgr_admin_general_settings['enable_group_criteria'] : 'no';
	} else {
		$multi_reviews         = 'no';
		$reviews_per_page      = 5;
		$allow_notification    = 'yes';
		$allow_activity        = 'yes';
		$exclude_groups        = array();
		$auto_approve_reviews  = 'no';
		$enable_group_criteria = 'no';
	}

	/**** Global variable values for Email settings */

	$review_accept_email_subject = ''; // Initialize to avoid warnings.
	$review_accept_email_message = ''; // Initialize to avoid warnings.
	$review_deny_email_subject   = ''; // Initialize to avoid warnings.
	$review_deny_email_message   = ''; // Initialize to avoid warnings.

	if ( ! empty( $bp_group_review_email_settings ) ) {
		$allow_email                 = isset( $bp_group_review_email_settings['bgr_allow_email'] ) ? $bp_group_review_email_settings['bgr_allow_email'] : 'yes';
		$accept_email_enable         = isset( $bp_group_review_email_settings['bgr_accept_enable'] ) ? $bp_group_review_email_settings['bgr_accept_enable'] : 'yes';
		$deny_email_enable           = isset( $bp_group_review_email_settings['bgr_deny_email'] ) ? $bp_group_review_email_settings['bgr_deny_email'] : 'yes';
		$review_email_subject        = isset( $bp_group_review_email_settings['review_email_subject'] ) ? $bp_group_review_email_settings['review_email_subject'] : '';
		$review_email_message        = isset( $bp_group_review_email_settings['review_email_message'] ) ? $bp_group_review_email_settings['review_email_message'] : '';
		$review_accept_email_subject = isset( $bp_group_review_email_settings['review_accept_email_subject'] ) ? $bp_group_review_email_settings['review_accept_email_subject'] : '';
		$review_accept_email_message = isset( $bp_group_review_email_settings['review_accept_email_message'] ) ? $bp_group_review_email_settings['review_accept_email_message'] : '';
		$review_deny_email_subject   = isset( $bp_group_review_email_settings['review_deny_email_subject'] ) ? $bp_group_review_email_settings['review_deny_email_subject'] : '';
		$review_deny_email_message   = isset( $bp_group_review_email_settings['review_deny_email_message'] ) ? $bp_group_review_email_settings['review_deny_email_message'] : '';
	} else {
		$allow_email                 = 'yes';
		$accept_email_enable         = 'yes';
		$deny_email_enable           = 'yes';
		$review_email_subject        = '';
		$review_email_message        = '';
		$review_accept_email_subject = '';
		$review_accept_email_message = '';
		$review_deny_email_subject   = '';
		$review_deny_email_message   = '';
	}

	/**** Global variable values for Criteria settings */

	if ( ! empty( $bgr_admin_criteria_settings ) ) {
		$review_rating_fields = isset( $bgr_admin_criteria_settings['add_review_rating_fields'] ) ? $bgr_admin_criteria_settings['add_review_rating_fields'] : array();
		$active_rating_fields = isset( $bgr_admin_criteria_settings['active_rating_fields'] ) ? $bgr_admin_criteria_settings['active_rating_fields'] : array();
	} else {
		$review_rating_fields = bp_group_review_get_default_criteria();
		$active_rating_fields = bp_group_review_get_default_criteria();
		$bgr_admin_settings   = array(
			'add_review_rating_fields' => $review_rating_fields,
			'active_rating_fields'     => $active_rating_fields,
		);
		update_option( 'bgr_admin_criteria_settings', $bgr_admin_settings );
	}

	/**** Global variable values for Display settings */

	if ( ! empty( $bgr_admin_display_settings ) ) {
		$rating_color = isset( $bgr_admin_display_settings['bgr_rating_color'] ) ? $bgr_admin_display_settings['bgr_rating_color'] : '#FFC400';
	} else {
		$rating_color = '#FFC400';

		/*
		 * Seed the labels EMPTY, never with a translated string. An empty label
		 * means "no rename - use the default", and the default is translated at
		 * render time by bp_group_review_tab_name() / _add_review_tab_name(). The
		 * old code called esc_html__() here and update_option()'d the result, which
		 * froze whichever locale the very first admin happened to be using into the
		 * database forever - so a site that later switched language kept rendering
		 * group nav tabs in the original one. Only a genuine rename is persisted.
		 */
		$bgr_admin_display_settings = array(
			'review_label'        => '',
			'manage_review_label' => '',
			'bgr_rating_color'    => $rating_color,
		);
		update_option( 'bgr_admin_display_settings', $bgr_admin_display_settings );
	}

	// Resolve through the accessors so the stored-vs-default fallback lives in
	// exactly one place.
	$review_label        = bp_group_review_add_review_tab_name();
	$manage_review_label = bp_group_review_tab_name();

	// A blank, zero or negative page size (saved by an older build) would hide every review
	// in the tab and make the Manage list unbounded, so heal it here for every reader.
	$reviews_per_page = bgr_sanitize_reviews_per_page( $reviews_per_page );

	$bgr = array(
		'multi_reviews'               => $multi_reviews,
		'auto_approve_reviews'        => $auto_approve_reviews,
		'reviews_per_page'            => $reviews_per_page,
		'allow_email'                 => $allow_email,
		'accept_email_enable'         => $accept_email_enable,
		'deny_email_enable'           => $deny_email_enable,
		'allow_notification'          => $allow_notification,
		'allow_activity'              => $allow_activity,
		'exclude_groups'              => $exclude_groups,
		'enable_group_criteria'       => $enable_group_criteria,
		'review_email_subject'        => $review_email_subject,
		'review_email_message'        => $review_email_message,
		'review_accept_email_subject' => $review_accept_email_subject,
		'review_accept_email_message' => $review_accept_email_message,
		'review_deny_email_subject'   => $review_deny_email_subject,
		'review_deny_email_message'   => $review_deny_email_message,
		'review_rating_fields'        => $review_rating_fields,
		'active_rating_fields'        => $active_rating_fields,
		'review_label'                => $review_label,
		'manage_review_label'         => $manage_review_label,
		'rating_color'                => $rating_color,
	);
}
add_action( 'init', 'bp_group_review_globals' );

/**
 * BGR group review tab name (plural, e.g. "Reviews").
 *
 * A stored label is a deliberate rename by the site owner and is returned as-is.
 * An empty/absent label means "not renamed", so the shipped default is translated
 * here, at render time, in the current request's locale.
 *
 * NOTE: this is DISPLAY copy. Never sanitize_title() the result to build a URL -
 * that would make the slug follow the visitor's language. Use
 * bp_group_review_manage_tab_slug() for slugs.
 */
function bp_group_review_tab_name() {
	$bgr_admin_display_settings = get_option( 'bgr_admin_display_settings' );
	$stored                     = isset( $bgr_admin_display_settings['manage_review_label'] ) ? trim( (string) $bgr_admin_display_settings['manage_review_label'] ) : '';
	$group_review_tab_name      = '' !== $stored ? $stored : esc_html__( 'Reviews', 'bp-group-reviews' );

	return apply_filters( 'bp_group_review_tab_name', $group_review_tab_name );
}
/**
 * BGR add group review tab name (singular, e.g. "Review").
 *
 * Same contract as bp_group_review_tab_name(): stored rename wins, otherwise the
 * default is translated at render time. Display copy - not for slugs.
 */
function bp_group_review_add_review_tab_name() {
	$bgr_admin_display_settings = get_option( 'bgr_admin_display_settings' );
	$stored                     = isset( $bgr_admin_display_settings['review_label'] ) ? trim( (string) $bgr_admin_display_settings['review_label'] ) : '';
	$group_add_review_tab_name  = '' !== $stored ? $stored : esc_html__( 'Review', 'bp-group-reviews' );

	return apply_filters( 'bp_group_review_add_review_tab_name', $group_add_review_tab_name );
}
/**
 * Review count with the singular or plural review label, e.g. "1 Review",
 * "3 Reviews". Every count label goes through here so they pluralise alike.
 *
 * @since 3.9.2
 *
 * @param int $count Number of reviews.
 * @return string Unescaped label.
 */
function bgr_review_count_label( $count ) {
	$count = (int) $count;
	return sprintf(
		/* translators: 1: number of reviews, 2: singular review label (e.g. "Review"), 3: plural review label (e.g. "Reviews"). */
		_n( '%1$s %2$s', '%1$s %3$s', $count, 'bp-group-reviews' ), // phpcs:ignore WordPress.WP.I18n.MismatchedPlaceholders -- Each form picks its own owner-set label.
		number_format_i18n( $count ),
		bp_group_review_add_review_tab_name(),
		bp_group_review_tab_name()
	);
}
/**
 * BGR group review tab slug (singular base, e.g. "review").
 *
 * Derived from the stored rename when there is one, otherwise a hard-coded,
 * locale-independent constant. It deliberately does NOT fall back to the
 * translated label: group nav URLs must not change when a visitor switches
 * language, and stored links must keep resolving.
 */
function bp_group_review_tab_slug() {
	$bgr_admin_display_settings = get_option( 'bgr_admin_display_settings' );
	$stored                     = isset( $bgr_admin_display_settings['review_label'] ) ? trim( (string) $bgr_admin_display_settings['review_label'] ) : '';
	$group_review_tab_slug      = '' !== $stored ? sanitize_title( $stored ) : 'review';

	return apply_filters( 'bp_group_review_tab_slug', $group_review_tab_slug );
}

/**
 * BGR manage-reviews tab slug (plural base, e.g. "reviews").
 *
 * The locale-independent counterpart to bp_group_review_tab_name(). Every place
 * that used to build this slug with sanitize_title( bp_group_review_tab_name() )
 * must call this instead - once the plural default became translatable, running
 * sanitize_title() over the display name would have produced "avis" for a French
 * visitor and "reviews" for an English one on the same site, breaking group nav
 * matching and every stored review URL.
 *
 * @since   3.9.0
 * @return  string
 */
function bp_group_review_manage_tab_slug() {
	$bgr_admin_display_settings = get_option( 'bgr_admin_display_settings' );
	$stored                     = isset( $bgr_admin_display_settings['manage_review_label'] ) ? trim( (string) $bgr_admin_display_settings['manage_review_label'] ) : '';
	$manage_tab_slug            = '' !== $stored ? sanitize_title( $stored ) : 'reviews';

	return apply_filters( 'bp_group_review_manage_tab_slug', $manage_tab_slug );
}

/**
 * One-time migration: unfreeze display labels that were only ever a default.
 *
 * Before 3.9.0 the first page load persisted esc_html__( 'Review' / 'Reviews' )
 * into bgr_admin_display_settings, freezing the first admin's locale into the DB.
 * Clearing those rows lets the render-time default take over, so the tab finally
 * follows the visitor's language.
 *
 * SAFETY: we only clear a label that exactly matches the UNTRANSLATED English
 * default. We deliberately do not compare against the current locale's
 * translation, because we cannot distinguish "frozen French default" from
 * "owner deliberately renamed the tab to that word" - and clearing it would
 * silently move the tab's URL from /avis/ to /reviews/, breaking existing links.
 * Sites first installed in a non-English locale therefore keep their frozen
 * label; that is the pre-3.9.0 behaviour, unchanged, and a rename in the Display
 * tab still works. See the release notes for the QA follow-up.
 *
 * @since   3.9.0
 * @return  void
 */
function bp_group_review_migrate_display_labels() {
	if ( 'done' === get_option( 'bgr_display_labels_migrated' ) ) {
		return;
	}

	$settings = get_option( 'bgr_admin_display_settings' );

	if ( ! empty( $settings ) && is_array( $settings ) ) {
		$defaults = array(
			'review_label'        => 'Review',
			'manage_review_label' => 'Reviews',
		);
		$changed  = false;

		foreach ( $defaults as $key => $english_default ) {
			$stored = isset( $settings[ $key ] ) ? trim( (string) $settings[ $key ] ) : '';

			if ( '' !== $stored && $stored === $english_default ) {
				$settings[ $key ] = '';
				$changed          = true;
			}
		}

		if ( $changed ) {
			update_option( 'bgr_admin_display_settings', $settings );
		}
	}

	update_option( 'bgr_display_labels_migrated', 'done' );
}
add_action( 'init', 'bp_group_review_migrate_display_labels', 5 );
