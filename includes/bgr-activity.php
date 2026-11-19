<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName -- Legacy file name.
/**
 * Generate Activity on reviewed any groups.
 *
 * @link       https://wbcomdesigns.com/
 * @since      1.0.0
 *
 * @package    BuddyPress_Group_Review
 * @subpackage BuddyPress_Group_Review/includes
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Generate Activity on reviewed any groups.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    BuddyPress_Group_Review
 * @subpackage BuddyPress_Group_Review/includes
 * @author     Wbcom Designs <admin@wbcomdesigns.com>
 */
class BGR_Activity {

	/**
	 * The single instance of the class.
	 *
	 * @var BGR_Activity|null
	 * @since 1.0.0
	 */
	protected static $instance = null;

	/**
	 * Main BGR_Activity Instance.
	 *
	 * Ensures only one instance of BGR_Activity is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @return BGR_Activity Main instance.
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * BGR_Activity Constructor.
	 *
	 * @since  1.0.0
	 */
	public function __construct() {
		$this->init_hooks();
	}

	/**
	 * Hook into actions and filters.
	 *
	 * @since  1.0.0
	 */
	private function init_hooks() {
		add_action( 'bp_register_activity_actions', array( $this, 'bp_group_review_register_activity_actions' ), 10 );
		add_action( 'transition_post_status', array( $this, 'bp_group_review_group_post_activity_after_approval' ), 15, 3 );
		add_action( 'bgr_group_after_review_submit', array( $this, 'bp_group_review_create_group_post_activity' ), 15, 3 );
		add_filter( 'bp_get_activity_content_body', array( $this, 'bp_group_review_added_activity_star_rating' ), 10, 2 );
	}

	/**
	 * Register activity actions for the Activity component.
	 *
	 * @since 1.0.0
	 * @return false|null False on failure.
	 */
	public function bp_group_review_register_activity_actions() {
		if ( ! bp_is_active( 'groups' ) || ! bp_is_active( 'activity' ) ) {
			return;
		}

		// The action text is stored on each activity row when it is created, so
		// no format callback is needed - registering the type is what puts group
		// reviews in the activity "Show" filter.
		bp_activity_set_action(
			buddypress()->groups->id,
			'bgr_group_posts_activity_action',
			__( 'Posted a group review', 'bp-group-reviews' ),
			false,
			__( 'Group Reviews', 'bp-group-reviews' ),
			array( 'activity', 'group', 'member', 'member_groups' )
		);
	}

	/**
	 * Add activity when add review in a group.
	 *
	 * @since 1.0.0
	 *
	 * @param int $author_id Author user ID.
	 * @param int $group_id  Group ID.
	 * @param int $post_id   Post ID.
	 * @return false|void
	 */
	public function bp_group_review_create_group_post_activity( $author_id, $group_id, $post_id ) {
		global $bp;
		global $bgr;
		$allow_activity       = $bgr['allow_activity'];
		$auto_approve_reviews = $bgr['auto_approve_reviews'];
		if ( 'no' === $auto_approve_reviews ) {
			return false;
		}

		if ( ! bp_is_active( 'groups' ) || ! bp_is_active( 'activity' ) ) {
			return false;
		}

		$posts_id       = $post_id;
		$post           = get_post( $posts_id );
		$user_id        = $post->post_author;
		$user_link      = bp_core_get_userlink( $user_id );
		$post_title     = get_the_title( $posts_id );
		$group_name     = bp_get_group_name( groups_get_group( $group_id ) );
		$group_obj      = groups_get_group( $group_id );
		$review_data    = get_post( $posts_id );
		$review_content = $review_data->post_content;
		$group_link = bgr_get_group_url( $group_obj );
		$group_link_html = '<a href="' . esc_url( $group_link ) . '">' . $group_name . '</a>';
		$action          = sprintf(
			apply_filters(
				'bgr_activity_action',
				/* translators: %1$s: User Link, %2$s: Group Link HTML. */
				__( '%1$s posted a review in the group %2$s', 'bp-group-reviews' )
			),
			$user_link,
			$group_link_html
		);
		// Determine if the activity should be hidden sitewide.
		$hide_sitewide = ( 'private' === $group_obj->status || 'hidden' === $group_obj->status );
		if ( 'yes' === $allow_activity ) {
			$args        = array(
				'action'            => $action,
				'content'           => $review_content,
				'component'         => 'groups',
				'type'              => defined( 'YOUZIFY_VERSION' ) ? 'activity_status' : 'bgr_group_posts_activity_action',
				'user_id'           => $user_id,
				'item_id'           => $group_id,
				'secondary_item_id' => $posts_id,
				'hide_sitewide'     => $hide_sitewide,
				'is_spam'           => false,
				'privacy'           => 'public',
				'error_type'        => 'bool',
			);
			$activity_id = bp_activity_add( $args );
		}
	}

	/**
	 * Add activity when group review approved.
	 *
	 * @since 1.0.0
	 *
	 * @param string  $new_status New post status.
	 * @param string  $old_status Old post status.
	 * @param WP_Post $post       Post object.
	 * @return false|void
	 */
	public function bp_group_review_group_post_activity_after_approval( $new_status, $old_status, $post ) {
		global $bp;
		global $bgr;
		$allow_activity       = $bgr['allow_activity'];
		$post_status          = get_post_status( $post->ID );
		$auto_approve_reviews = $bgr['auto_approve_reviews'];
		if ( 'yes' === $auto_approve_reviews ) {
			return false;
		}
		if ( ! bp_is_active( 'groups' ) && ! bp_is_active( 'activity' ) ) {
			return false;
		}

		if ( 'yes' === $allow_activity ) {
			if ( ( 'publish' === $new_status && 'publish' !== $old_status ) && 'review' === $post->post_type ) {
				$group_id = get_post_meta( $post->ID, 'linked_group', true );

				// The 'review' CPT is shared with BuddyPress Member Reviews. Only
				// generate a group-review activity when this review genuinely
				// belongs to a group (non-empty linked_group) and is not a member
				// review (which carries linked_bp_member instead). Otherwise a
				// member review would produce a broken group activity with
				// item_id=0 and an empty group link in the sitewide stream.
				$linked_member = get_post_meta( $post->ID, 'linked_bp_member', true );
				if ( empty( $group_id ) || ! empty( $linked_member ) ) {
					return false;
				}

				$posts_id       = $post->ID;
				$user_id        = $post->post_author;
				$user_link      = bp_core_get_userlink( $user_id );
				$post_title     = get_the_title( $posts_id );
				$group_name     = bp_get_group_name( groups_get_group( $group_id ) );
				$group_obj      = groups_get_group( $group_id );
				$review_data    = get_post( $posts_id );
				$review_content = $review_data->post_content;
				$group_link = bgr_get_group_url( $group_obj );
				$group_link_html = '<a href="' . esc_url( $group_link ) . '">' . $group_name . '</a>';
				$action          = sprintf(
					apply_filters(
						'bgr_activity_action',
						/* translators: %1$s: User Link, %2$s: Group Link HTML. */
						__( '%1$s posted a review in the group %2$s', 'bp-group-reviews' )
					),
					$user_link,
					$group_link_html
				);

				// Determine if the activity should be hidden sitewide.
				$hide_sitewide = ( 'private' === $group_obj->status || 'hidden' === $group_obj->status );
				$args          = array(
					'action'            => $action,
					'content'           => $review_content,
					'component'         => 'groups',
					'type'              => defined( 'YOUZIFY_VERSION' ) ? 'activity_status' : 'bgr_group_posts_activity_action',
					'user_id'           => $user_id,
					'item_id'           => $group_id,
					'secondary_item_id' => $posts_id,
					'hide_sitewide'     => $hide_sitewide,
					'is_spam'           => false,
					'privacy'           => 'public',
					'error_type'        => 'bool',
				);
				$activity_id   = bp_activity_add( $args );
			}
		}
	}

	/**
	 * BGR added star rating in activity.
	 *
	 * @param string $activity_content Activity Content.
	 * @param object $activity         Activity Object.
	 * @return string Modified activity content.
	 */
	public function bp_group_review_added_activity_star_rating( $activity_content, $activity ) {
		global $bgr;
		// Only a group review activity: secondary_item_id is a review of the activity's
		// group (type is activity_status under Youzify, so the type alone is not enough).
		if ( ! $activity instanceof BP_Activity_Activity || 'groups' !== $activity->component || 'review' !== get_post_type( (int) $activity->secondary_item_id )
			|| absint( get_post_meta( (int) $activity->secondary_item_id, 'linked_group', true ) ) !== absint( $activity->item_id ) ) {
			return $activity_content;
		}
		$post_id              = $activity->secondary_item_id;
		$review_rating_fields = $bgr['review_rating_fields'];
		$review_ratings       = get_post_meta( $post_id, 'review_star_rating', false );
		$review_start         = '';
		if ( ! empty( $review_rating_fields ) && ! empty( $review_ratings[0] ) ) {
			$review_start .= '<div class="bgr-multi-review">';
			foreach ( $review_rating_fields as $review_field ) {
				if ( array_key_exists( $review_field, $review_ratings[0] ) ) {
					$review_start .= '<div class="multi-review bgr-criteria-row">';
					$review_start .= '<div class="bgr-col-6">' . esc_html( bp_group_review_get_criteria_label( $review_field ) ) . '</div>';
					$review_start .= '<div class="bgr-col-6">';
					// Display star ratings.
					$stars_on  = $review_ratings[0][ $review_field ];
					$stars_off = 5 - $stars_on;
					for ( $i = 1; $i <= $stars_on; $i++ ) {
						$review_start .= '<span class="fas fa-star stars bgr-star-rate"></span>';

					}
					for ( $i = 1; $i <= $stars_off; $i++ ) {
						$review_start .= '<span class="far fa-star stars bgr-star-rate"></span>';
					}
					$review_start .= '</div>';
					$review_start .= '</div>';
				}
			}
			$review_start .= '</div>';
		}
		return $activity_content . $review_start;
	}
}

/**
 * Main instance of BGR_Activity.
 *
 * Returns the main instance of BGR_Activity to prevent the need to use globals.
 *
 * @since  1.0.0
 * @return BGR_Activity
 */
BGR_Activity::instance();
