<?php
/**
 * Class to generate bp notification.
 *
 * @link       https://wbcomdesigns.com/
 * @since      1.0.0
 *
 * @package    BuddyPress_Group_Review
 * @subpackage BuddyPress_Group_Review/includes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
// Exit if accessed directly.
/**
 * Class to add custom scripts on woocommerce hooks
 */
if ( ! class_exists( 'BGR_Notifications' ) ) {
	/**
	 * Class to generate bp notification.
	 *
	 * @link       https://wbcomdesigns.com/
	 * @since      1.0.0
	 *
	 * @package    BuddyPress_Group_Review
	 * @subpackage BuddyPress_Group_Review/includes
	 */
	class BGR_Notifications extends BP_Component {

		/**
		 * Component id.
		 *
		 * @since   1.0.0
		 * @author  Wbcom Designs
		 *
		 * @var $_component_name.
		 */
		protected $_component_name = 'bgr_bp_review';

		/**
		 * Constructor for  generate bp notification.
		 *
		 *  @since   1.0.0
		 *  @author  Wbcom Designs
		 */
		public function __construct() {
			$this->slug = $this->_component_name;

			parent::start(
				$this->_component_name,
				esc_html__( 'Group Reviews', 'bp-group-reviews' ),
				dirname( __FILE__ )
			);

			buddypress()->active_components[ $this->_component_name ] = '1';
		}

		/**
		 * Set up Globals.
		 *
		 * @param  array $args Arguments.
		 * @return void
		 */
		public function setup_globals( $args = array() ) {
			parent::setup_globals(
				array(
					'slug'                  => $this->_component_name,
					'has_directory'         => false,
					'notification_callback' => 'bgr_format_notifications',
				)
			);
		}

		/**
		 * Set up actions.
		 *
		 * @return void
		 */
		public function setup_actions() {
			// When review added.
			add_action( 'bgr_group_add_review', array( $this, 'bgr_add_review_notification' ), 99, 2 );
			add_action( 'bgr_group_accept_review', array( $this, 'bgr_accept_review_notification' ), 99, 2 );
			add_action( 'bgr_group_deny_review', array( $this, 'bgr_deny_review_notification' ), 99, 2 );
			parent::setup_actions();
		}

		/**
		 * Component Name.
		 */
		public function component_name() {
			return $this->_component_name;
		}

		/**
		 * Adding notifications for new review posted by any buddypress member
		 *
		 * @param  int $group_id Linked Group ID.
		 * @param  int $group_admin Linked Group Admin ID.
		 * @return void
		 */
		public function bgr_add_review_notification( $group_id, $group_admin ) {
			if ( bp_is_active( 'notifications' ) ) {

				$current_user = wp_get_current_user();
				$member_id    = $current_user->ID;
				$args         = array(
					'user_id'           => $group_admin,
					'item_id'           => $group_id,
					'secondary_item_id' => $member_id,
					'component_name'    => $this->_component_name,
					'component_action'  => 'bgr_add_review_action',
					'date_notified'     => bp_core_current_time(),
					'is_new'            => 1,
					'allow_duplicate'   => true,
				);
				bp_notifications_add_notification( $args );
			}
		}

		/**
		 * Adding notifications when review accepted by group admin
		 *
		 * @param  int $review_id Review ID.
		 * @return void
		 */
		public function bgr_accept_review_notification( $review_id ) {
			if ( bp_is_active( 'notifications' ) ) {
				$group_id       = bp_get_current_group_id();
				$post_author_id = get_post_field( 'post_author', $review_id );
				$args           = bp_notifications_add_notification(
					array(
						'user_id'           => $post_author_id,
						'item_id'           => $group_id,
						'secondary_item_id' => $review_id,
						'component_name'    => $this->_component_name,
						'component_action'  => 'bgr_accept_review_action',
						'date_notified'     => bp_core_current_time(),
						'is_new'            => 1,
						'allow_duplicate'   => true,
					)
				);
			}
		}

		/**
		 * Adding notifications when review denied by group admin
		 *
		 * @param  int $review_id Review ID.
		 * @return void
		 */
		public function bgr_deny_review_notification( $review_id ) {
			if ( bp_is_active( 'notifications' ) ) {
				$group_id       = bp_get_current_group_id();
				$post_author_id = get_post_field( 'post_author', $review_id );
				bp_notifications_add_notification(
					array(
						'user_id'           => $post_author_id,
						'item_id'           => $group_id,
						'secondary_item_id' => $review_id,
						'component_name'    => $this->_component_name,
						'component_action'  => 'bgr_deny_review_action',
						'date_notified'     => bp_core_current_time(),
						'is_new'            => 1,
						'allow_duplicate'   => true,
					)
				);
			}
		}

		/**
		 * Formatting notifications for review when added.
		 *
		 * @param  int    $grp_id Group ID.
		 * @param  int    $member_id Member ID.
		 * @param  int    $user_id User ID.
		 * @param  string $format Notification format.
		 */
		public function bgr_add_review_notification_format( $grp_id, $member_id, $user_id, $format = '' ) {
			global $bp;
			global $bgr;
			$review_label = $bgr['review_label'];
			$admin_id     = bp_displayed_user_id();
			$group        = groups_get_group( array( 'group_id' => $grp_id ) );
			$group_name   = $group->name;
			$user_info    = get_userdata( $member_id );
			$user_name    = $user_info->user_login;
			if ( $bgr['auto_approve_reviews'] == 'no' ) {
				$notification_link = bp_get_group_url( $group ) . 'reviews/add-' . bgr_group_review_tab_slug() . '/';
			} else {
				$notification_link = bp_get_group_url( $group ) . sanitize_title( bgr_group_review_tab_name() );
			}
			/* translators: %1$s is replaced with review_label */
			$notification_title = sprintf( esc_html__( 'A new %1$s posted.', 'bp-group-reviews' ), $review_label );
			/* translators: %1$s, %2$s and %3$s is replaced with user_name, review_label and group name respectively */
			$notification_content = sprintf( esc_html__( '%1$s posted a %2$s for %3$s.', 'bp-group-reviews' ), $user_name, $review_label, $group_name );

			if ( 'string' == $format ) {
				$return = sprintf( "<a href='%s' title='%s'>%s</a>", esc_url( $notification_link ), $notification_title, $notification_content );
			} else {
				$return = array(
					'text' => $notification_content,
					'link' => $notification_link,
				);
			}
			return apply_filters( 'bgr_add_review_notification_format', $return, $grp_id, $format );
		}


		/**
		 * Formatting notifications when review accepted by group admin
		 *
		 * @param  int    $grp_id Group ID.
		 * @param  int    $review_id Member ID.
		 * @param  string $format Notification format.
		 */
		public function bgr_accept_review_notification_format( $grp_id, $review_id, $format = '' ) {
			global $bgr;
			$review_label      = $bgr['review_label'];
			$group             = groups_get_group( array( 'group_id' => $grp_id ) );
			$group_name        = $group->name;
			$notification_link = bp_get_group_url( $group ) . "reviews/view/$review_id/";
			/* translators: %1$s is replaced with review_label */
			$notification_title = sprintf( esc_html__( '%1$s accepted.', 'bp-group-reviews' ), $review_label );
			/* translators: %1$s and %2$s is replaced with review_label and group name resepectively*/
			$notification_content = sprintf( esc_html__( 'Your %1$s for %2$s accepted by group admin.', 'bp-group-reviews' ), $review_label, $group_name );

			if ( 'string' == $format ) {
				$return = sprintf( "<a href='%s' title='%s'>%s</a>", esc_url( $notification_link ), $notification_title, $notification_content );
			} else {
				$return = array(
					'text' => $notification_content,
					'link' => $notification_link,
				);
			}
			return apply_filters( 'bgr_accept_review_notification_format', $return, $grp_id, $format );
		}

		/**
		 * Formatting notifications when review denied by group admin
		 *
		 * @param  int    $grp_id Group ID.
		 * @param  int    $review_id Member ID.
		 * @param  string $format Notification format.
		 */
		public function bgr_deny_review_notification_format( $grp_id, $review_id, $format = '' ) {
			global $bgr;
			global $bp;
			$review_label      = $bgr['review_label'];
			$group             = groups_get_group( array( 'group_id' => $grp_id ) );
			$group_name        = $group->name;
			$notification_link = bp_get_group_url( $group ) . 'reviews/';
			/* translators: %1$s is replaced with review_label */
			$notification_title = sprintf( esc_html__( '%1$s denied.', 'bp-group-reviews' ), $review_label );
			/* translators: %1$s and %2$s is replaced with review_label and group_name respectively */
			$notification_content = sprintf( esc_html__( 'Your %1$s for %2$s denied by group admin.', 'bp-group-reviews' ), $review_label, $group_name );

			if ( 'string' == $format ) {
				$return = sprintf( "<a href='%s' title='%s'>%s</a>", esc_url( $notification_link ), $notification_title, $notification_content );
			} else {
				$return = array(
					'text' => $notification_content,
					'link' => $notification_link,
				);
			}
			return apply_filters( 'bgr_deny_review_notification_format', $return, $grp_id, $format );
		}
	}
}
add_filter( 'bp_notifications_get_notifications_for_user', 'bgr_format_notifications', 10, 5 );
/**
 * Actions performed to add notifications
 *
 * @param  string $action Notification Action.
 * @param  int    $item_id Group ID.
 * @param  int    $secondary_item_id Review ID.
 * @param  int    $user_id Member ID.
 * @param  string $format Notification format.
 */
function bgr_format_notifications( $action, $item_id, $secondary_item_id, $user_id, $format = 'string' ) {
	if ( bp_is_active( 'notifications' ) && bp_is_active( 'groups' ) && $action != 'bbp_new_reply' ) {
		switch ( $action ) {

			case 'bgr_add_review_action':
					$return = buddypress()->bgr_bp_review->bgr_add_review_notification_format( $item_id, $secondary_item_id, $user_id, $format );
				break;
			case 'bgr_accept_review_action':
					$return = buddypress()->bgr_bp_review->bgr_accept_review_notification_format( $item_id, $secondary_item_id, $format );
				break;
			case 'bgr_deny_review_action':
					$return = buddypress()->bgr_bp_review->bgr_deny_review_notification_format( $item_id, $secondary_item_id, $format );
				break;
			default:
					$return = '';
				break;

			return $return;
		}
		if ( $return ) {
			return $return;
		} else {
			return $action;
		}
	}
}
