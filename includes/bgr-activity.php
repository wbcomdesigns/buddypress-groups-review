<?php
/**
 * Generate Activity  on reviewd any groups.
 *
 * @link       https://wbcomdesigns.com/
 * @since      1.0.0
 *
 * @package    BuddyPress_Group_Review
 * @subpackage BuddyPress_Group_Review/includes
 */

/**
 * Generate Activity  on reviewd any groups.
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
	 * @var Buddypress_Member_Blog_Pro_Groups
	 * @since 1.0.0
	 */
	protected static $instance = null;

	/**
	 * Main BuddyPress_Member_Blog_Pro Instance.
	 *
	 * Ensures only one instance of BuddyPress_Member_Blog_Pro is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @see instantiateb_buddypress_member_blog_pro()
	 * @return BGR_Activity - Main instance.
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * BuddyPress_Member_Blog_Pro Constructor.
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
		add_action( 'bp_register_activity_actions', array( $this, 'bgr_register_activity_actions' ), 10 );
		add_action( 'transition_post_status', array( $this, 'bgr_create_group_post_activity_after_approval' ), 15, 3 );
		add_action( 'bgr_group_after_review_submit', array( $this, 'bgr_create_group_post_activity' ), 15, 3 );
		add_filter( 'bp_get_activity_content_body', array( $this, 'bgr_added_activity_star_rating' ), 10, 2 );
	}

	/**
	 * Register activity actions for the Activity component.
	 *
	 * @since 1.0.0
	 * @return false|null False on failure.
	 */
	public function bgr_register_activity_actions() {
		$bp = buddypress();

		// Bail out if activity component not activated.
		if ( ! bp_is_active( 'groups' ) ) {
			return;
		}

		bp_activity_set_action(
			$bp->members->id,
			'bpmb_pro_group_posts',
			__( 'New Post', 'bp-group-reviews' ),
			'bpmb_pro_group_create_posts_activity_action',
			__( 'New Post', 'bp-group-reviews' ),
			array( 'groups' )
		);

	}

	/**
	 * Format product create activity actions.
	 *
	 * @since 1.0.0
	 * @param String $action Activity Action.
	 * @param object $activity Activity Object.
	 * @return mixed|void
	 */
	public function bpmb_pro_group_create_posts_activity_action( $action, $activity ) {

		$posts_id        = $activity->secondary_item_id;
		$user_link       = bp_core_get_userlink( $activity->user_id );
		$post_title      = get_the_title( $posts_id );
		$post_link       = get_permalink( $posts_id );
		$group_id        = bp_get_current_group_id();
		$group_name      = bp_get_group_name( groups_get_group( $group_id ) );
		$post_link_html  = '<a href="' . esc_url( $post_link ) . '">' . $product_title . '</a>';
		$group_link_html = '<a href="' . esc_url( $post_link ) . '">' . $group_name . '</a>';
		/* translators: %1$s: User Link, %2$s: Post Link HTML, %3$s: Group Name */
		$action = sprintf( __( '%1$s posted a new post %2$s in group %3$s', 'bp-group-reviews' ), $user_link, $product_link_html, $group_name );

		return apply_filters( 'bpmb_pro_group_create_posts_activity_action', $action );
	}

	/**
	 * Add activity on new product created by vendor.
	 *
	 * @since 1.0.0
	 *
	 * @param int     $id Post ID.
	 * @param WP_Post $post       Post object.
	 */
	public function bgr_create_group_post_activity( $author_id, $group_id, $post_id ) {
		global $bp;
		global $bgr;
		$allow_activity       = $bgr['allow_activity'];
		$auto_approve_reviews = $bgr['auto_approve_reviews'];
		if ( 'no' === $auto_approve_reviews ) {
			return false;
		}

		if ( ! bp_is_active( 'groups' ) && ! bp_is_active( 'activity' ) ) {
			return false;
		}
		
		$posts_id        = $post_id;
		$post 			 = get_post( $posts_id );
		$user_id         = $post->post_author;
		$user_link       = bp_core_get_userlink( $user_id );
		$post_title      = get_the_title( $posts_id );
		$group_name      = bp_get_group_name( groups_get_group( $group_id ) );
		$group_obj       = groups_get_group( $group_id );
		$review_data     = get_post( $posts_id );
		$review_content  = $review_data->post_content;
		$post_link       = bp_get_group_url( $group_obj ) . sanitize_title( bgr_group_review_tab_name() ) . '/view/' . $posts_id;
		$group_link      = bp_get_group_url( $group_obj );
		$post_link_html  = '<a href="' . esc_url( $post_link ) . '">' . $post_title . '</a>';
		$group_link_html = '<a href="' . esc_url( $group_link ) . '">' . $group_name . '</a>';
		$action          = sprintf(
			apply_filters(
				'bgr_activity_action',
				/* translators: %1$s: User Link, %2$s: Post Link HTML, %3$s: Group Name */
				__( '%1$s posted an review in the group %2$s', 'bp-group-reviews' )
			),
			$user_link,
			$group_link_html
		);
		if ( 'yes' == $allow_activity ) {
			$args        = array(
				'action'            => $action,
				'content'           => $review_content,
				'component'         => 'groups',
				'type'              => 'bgr_group_posts_activity_action',
				'user_id'           => bp_loggedin_user_id(),
				'item_id'           => $group_id,
				'secondary_item_id' => $posts_id,
				'hide_sitewide'     => false,
				'is_spam'           => false,
				'privacy'           => 'public',
				'error_type'        => 'bool',
			);
			$activity_id = bp_activity_add( $args );
		}

	}

	/**
	 * Add activity on new product created by vendor.
	 *
	 * @since 1.0.0
	 *
	 * @param array   $new_status New post status.
	 * @param array   $old_status Old post status.
	 * @param WP_Post $post       Post object.
	 */
	public function bgr_create_group_post_activity_after_approval( $new_status, $old_status, $post ) {
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

		if ( 'yes' == $allow_activity ) {
			if ( ( 'publish' === $new_status && 'publish' !== $old_status ) && 'review' === $post->post_type ) {
				$posts_id        = $post->ID;
				$user_id         = $post->post_author;
				$user_link       = bp_core_get_userlink( $user_id );
				$post_title      = get_the_title( $posts_id );
				$group_id        = get_post_meta( $post->ID, 'linked_group', true );
				$group_name      = bp_get_group_name( groups_get_group( $group_id ) );
				$group_obj       = groups_get_group( $group_id );
				$review_data     = get_post( $posts_id );
				$review_content  = $review_data->post_content;
				$post_link       = bp_get_group_url( $group_obj ) . sanitize_title( bgr_group_review_tab_name() ) . '/view/' . $posts_id;
				$group_link      = bp_get_group_url( $group_obj );
				$post_link_html  = '<a href="' . esc_url( $post_link ) . '">' . $post_title . '</a>';
				$group_link_html = '<a href="' . esc_url( $group_link ) . '">' . $group_name . '</a>';
				$action          = sprintf(
					apply_filters(
						'bgr_activity_action',
						/* translators: %1$s: User Link, %2$s: Post Link HTML, %3$s: Group Name */
						__( '%1$s posted an review in the group %2$s', 'bp-group-reviews' )
					),
					$user_link,
					$group_link_html
				);
				$args        = array(
					'action'            => $action,
					'content'           => $review_content,
					'component'         => 'groups',
					'type'              => 'bgr_group_posts_activity_action',
					'user_id'           => bp_loggedin_user_id(),
					'item_id'           => $group_id,
					'secondary_item_id' => $posts_id,
					'hide_sitewide'     => false,
					'is_spam'           => false,
					'privacy'           => 'public',
					'error_type'        => 'bool',
				);
				$activity_id = bp_activity_add( $args );
			}
		}

	}

	/**
	 * BGR added star rating in activity.
	 *
	 * @param  string $activity_content Activity Content.
	 * @param  object $activity Activity Object.
	 */
	public function bgr_added_activity_star_rating( $activity_content, $activity ) {
		global $bgr;
		$post_id              = $activity->secondary_item_id;
		$review_rating_fields = $bgr['review_rating_fields'];
		$review_ratings       = get_post_meta( $post_id, 'review_star_rating', false );
		$review_start         = '';
		if ( ! empty( $review_rating_fields ) && ! empty( $review_ratings[0] ) ) {
			$review_start .= '<div class="bgr-multi-review">';
			foreach ( $review_rating_fields as $review_field ) {
				if ( array_key_exists( $review_field, $review_ratings[0] ) ) {
					$review_start .= '<div class="multi-review">';
					$review_start .= '<div class="bgr-col-6">' . esc_html( $review_field ) . '</div>';
					$review_start .= '<div class="bgr-col-6">';
					/*** Ratings */
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
