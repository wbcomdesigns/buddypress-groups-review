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
					'notification_callback' => 'bp_group_review_format_notifications',
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
			add_action( 'bgr_group_add_review', array( $this, 'bp_group_review_add_review_notification' ), 99, 2 );
			add_action( 'bgr_group_accept_review', array( $this, 'bp_group_review_accept_review_notification' ), 99, 2 );
			add_action( 'bgr_group_deny_review', array( $this, 'bp_group_review_deny_review_notification' ), 99, 2 );
			add_action( 'bp_actions', array( $this, 'bp_group_review_mark_group_notification_as_read' ), 1 );
			parent::setup_actions();
		}

		
		/**
		 * Mark Notification as read.
		 */
		public function bp_group_review_mark_group_notification_as_read() {
			$current_user_id = get_current_user_id();
			$group_id        = bp_get_current_group_id();

			// Security: Must be on groups component and reviews action.
			if ( ! bp_is_groups_component() || ! bp_is_current_action( 'reviews' ) ) {
				return;
			}

			// Security: Must have referer parameter.
			if ( ! isset( $_GET['referer'] ) || empty( $_GET['referer'] ) ) {		// phpcs:ignore
				return;
			}

			// Security: Verify nonce.
			if ( ! bp_verify_nonce_request( "mark-as-read-notification-{$group_id}" ) ) {
				return;
			}

			// Security: Sanitize and validate notification ID.
			$notification_id = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;		// phpcs:ignore
			if ( $notification_id <= 0 ) {
				return;
			}

			// Sanitize referer.
			$referer = sanitize_text_field( wp_unslash( $_GET['referer'] ) );		// phpcs:ignore

			// Mark notification as read based on referer type.
			if ( 'add_reviews' === $referer ) {
				BP_Notifications_Notification::update(
					array( 'is_new' => false ),
					array(
						'component_action' => 'bgr_add_review_action',
						'user_id'          => $current_user_id,
						'id'               => $notification_id,
					)
				);
			} elseif ( 'accept_reviews' === $referer ) {
				BP_Notifications_Notification::update(
					array( 'is_new' => false ),
					array(
						'component_action' => 'bgr_accept_review_action',
						'user_id'          => $current_user_id,
						'id'               => $notification_id,
					)
				);
			} elseif ( 'deny_reviews' === $referer ) {
				BP_Notifications_Notification::update(
					array( 'is_new' => false ),
					array(
						'component_action' => 'bgr_deny_review_action',
						'user_id'          => $current_user_id,
						'id'               => $notification_id,
					)
				);
			}
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
		public function bp_group_review_add_review_notification( $group_id, $group_admin ) {
			if ( bp_is_active( 'notifications' ) ) {

				$current_user = wp_get_current_user();
				$member_id    = $current_user->ID;
				$args         = array(
					'user_id'           => $group_admin,
					'item_id'           => $member_id,
					'secondary_item_id' => $group_id,
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
		public function bp_group_review_accept_review_notification( $review_id ) {
			if ( bp_is_active( 'notifications' ) ) {
				$group_id       = get_post_meta( $review_id, 'linked_group', true );
				$post_author_id = get_post_field( 'post_author', $review_id );
				$args           = bp_notifications_add_notification(
					array(
						'user_id'           => $post_author_id,
						'item_id'           => $review_id,
						'secondary_item_id' => $group_id,
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
		public function bp_group_review_deny_review_notification( $review_id ) {
			if ( bp_is_active( 'notifications' ) ) {
				$group_id       = get_post_meta( $review_id, 'linked_group', true );				
				$post_author_id = get_post_field( 'post_author', $review_id );
				bp_notifications_add_notification(
					array(
						'user_id'           => $post_author_id,
						'item_id'           => $review_id,
						'secondary_item_id' => $group_id,
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
		 * @param  int    $review_id Review ID.
		 * @param  int    $user_id User ID.
		 * @param  int    $id Notification ID.
		 * @param  string $format Notification format.
		 */
		public function bp_group_review_add_review_notification_format( $grp_id, $review_id, $user_id, $id, $format = '' ) {
			global $bgr;
			$review_label = $bgr['review_label'];
			$group        = groups_get_group( array( 'group_id' => $grp_id ) );
			$group_name   = $group->name;
			// $review_id is actually the member_id who posted the review (item_id from notification).
			// $user_id is the admin who receives the notification.
			$user_info    = get_userdata( $review_id );
			$user_name    = $user_info->user_login;
			// Build group URL safely for both BuddyPress and BuddyBoss.
			if ( function_exists( 'bp_get_group_url' ) ) {
				$group_link = bp_get_group_url( $group );
			} else {
				// Fallback for older versions - build URL manually.
				$group_link = bp_get_groups_directory_permalink() . $group->slug . '/';
			}
			$review_plural_label = ! empty( $bgr['manage_review_label'] ) ? sanitize_title( $bgr['manage_review_label'] ) : 'reviews';
			if ( $bgr['auto_approve_reviews'] == 'yes' ) {
				// For auto-approved reviews, link to the public reviews tab.
				$notification_link = trailingslashit( $group_link . $review_plural_label );
			} else {
				// For non-auto-approved, link to admin area to manage reviews.
				$notification_link = $group_link . 'admin/' . sanitize_title( 'manage-' . bp_group_review_tab_slug() );
			}
			/* translators: %1$s is replaced with review_label */
			$notification_title = sprintf( esc_html__( 'A new %1$s was posted.', 'bp-group-reviews' ), $review_label );
			/* translators: %1$s, %2$s and %3$s is replaced with user_name, review_label and group name respectively */
			$notification_content = sprintf( esc_html__( '%1$s posted a %2$s for %3$s.', 'bp-group-reviews' ), $user_name, strtolower( $review_label ), $group_name );
			$notification_link    = add_query_arg( array( 'referer' => 'add_reviews', 'id' => $id ), $notification_link );			// for identification of mark as read action 
			$notification_link    = wp_nonce_url( $notification_link, 'mark-as-read-notification-' . $grp_id );		// for security purpose when mark as read notification.
			if ( 'string' == $format ) {
				$return = sprintf( "<a href='%s' title='%s'>%s</a>", esc_url( $notification_link ), $notification_title, $notification_content );
			} else {
			// BuddyBoss expects 'link' first, then 'text' (matches BuddyBoss core notification format).


				$return = array(
					'link' => $notification_link,
					'text' => $notification_content,
				);
			}
			return apply_filters( 'bp_group_review_add_review_notification_format', $return, $grp_id, $format );
		}


		/**
		 * Formatting notifications when review accepted by group admin
		 *
		 * @param  int    $grp_id Group ID.
		 * @param  int    $review_id Member ID.
		 * @param  int    $id Notification ID.
		 * @param  string $format Notification format.
		 */
		public function bp_group_review_accept_review_notification_format( $grp_id, $review_id, $id, $format = '' ) {
			global $bgr;
			$review_label      = $bgr['review_label'];
			$group             = groups_get_group( array( 'group_id' => $grp_id ) );			
			$group_name        = $group->name;
			// Build group URL safely for both BuddyPress and BuddyBoss.
			if ( function_exists( 'bp_get_group_url' ) ) {
				$group_link = bp_get_group_url( $group );
			} else {
				// Fallback for older versions - build URL manually.
				$group_link = bp_get_groups_directory_permalink() . $group->slug . '/';
			}

			// Link to public reviews tab (not admin) so members can access it.
			// Use manage_review_label (defaults to 'reviews') instead of bp_group_review_tab_slug() (returns 'review').
			$review_plural_label = ! empty( $bgr['manage_review_label'] ) ? sanitize_title( $bgr['manage_review_label'] ) : 'reviews';
			$notification_link = trailingslashit( $group_link . $review_plural_label );
			/* translators: %1$s is replaced with review_label */
			$notification_title = sprintf( esc_html__( '%1$s accepted.', 'bp-group-reviews' ), $review_label );
			/* translators: %1$s and %2$s is replaced with review_label and group name resepectively*/
			$notification_content = sprintf( esc_html__( 'Your %1$s for %2$s was accepted by the group admin.', 'bp-group-reviews' ), strtolower( $review_label ), $group_name );
			$notification_link           = add_query_arg( array( 'referer' => 'accept_reviews', 'id' =>$id ), $notification_link );			// for identification of mark as read action 
			$notification_link           = wp_nonce_url( $notification_link, 'mark-as-read-notification-' . $grp_id );		// for security purpose when mark as read notification.

			if ( 'string' == $format ) {
				$return = sprintf( "<a href='%s' title='%s'>%s</a>", esc_url( $notification_link ), $notification_title, $notification_content );

			} else {
				$return = array(
					'link' => $notification_link,
					'text' => $notification_content,
				);
			}
			return apply_filters( 'bp_group_review_accept_review_notification_format', $return, $grp_id, $format );
		}

		/**
		 * Formatting notifications when review denied by group admin
		 *
		 * @param  int    $grp_id Group ID.
		 * @param  int    $review_id Member ID.
		 * @param  int    $id Notification ID.
		 * @param  string $format Notification format.
		 */
		public function bp_group_review_deny_review_notification_format( $grp_id, $review_id, $id, $format = '' ) {
			global $bgr;
			$review_label      = $bgr['review_label'];
			$group             = groups_get_group( array( 'group_id' => $grp_id ) );
			$group_name        = $group->name;
			// Build group URL safely for both BuddyPress and BuddyBoss.
			if ( function_exists( 'bp_get_group_url' ) ) {
				$group_link = bp_get_group_url( $group );
			} else {
				// Fallback for older versions - build URL manually.
				$group_link = bp_get_groups_directory_permalink() . $group->slug . '/';
			}

			$review_plural_label = ! empty( $bgr['manage_review_label'] ) ? sanitize_title( $bgr['manage_review_label'] ) : 'reviews';
			$notification_link   = trailingslashit( $group_link . $review_plural_label );
			/* translators: %1$s is replaced with review_label */
			$notification_title = sprintf( esc_html__( '%1$s denied.', 'bp-group-reviews' ), $review_label );
			/* translators: %1$s and %2$s is replaced with review_label and group_name respectively */
			$notification_content = sprintf( esc_html__( 'Your %1$s for %2$s was denied by the group admin.', 'bp-group-reviews' ), strtolower( $review_label ), $group_name );
			$notification_link           = add_query_arg( array( 'referer' => 'deny_reviews', 'id' => $id ), $notification_link );			// for identification of mark as read action 
			$notification_link           = wp_nonce_url( $notification_link, 'mark-as-read-notification-' . $grp_id );		// for security purpose when mark as read notification.

			if ( 'string' == $format ) {
				$return = sprintf( "<a href='%s' title='%s'>%s</a>", esc_url( $notification_link ), $notification_title, $notification_content );

			} else {
				$return = array(
					'link' => $notification_link,
					'text' => $notification_content,
				);
			}
			return apply_filters( 'bp_group_review_deny_review_notification_format', $return, $grp_id, $format );
		}
	}
}

/**
 * Actions performed to add notifications
 *
 * @param  string $action Notification Action.
 * @param  int    $item_id Group ID.
 * @param  int    $secondary_item_id Review ID.
 * @param  int    $user_id Member ID.
 * @param  string $format Notification format ('string' for BP, 'array' for BuddyBoss).
 * @param  int    $id Notification ID.
 * @param  string $screen Notification screen context (BuddyBoss only).
 */
function bp_group_review_format_notifications( $action, $item_id, $secondary_item_id, $user_id, $format = 'string', $id = 0, $screen = 'web' ) {	
	
	if ( bp_is_active( 'notifications' ) && bp_is_active( 'groups' ) && $action != 'bbp_new_reply' ) {
		switch ( $action ) {

			case 'bgr_add_review_action':
				$return = buddypress()->bgr_bp_review->bp_group_review_add_review_notification_format( $secondary_item_id, $item_id, $user_id, $id, $format );
				break;

			case 'bgr_accept_review_action':
				$return = buddypress()->bgr_bp_review->bp_group_review_accept_review_notification_format( $secondary_item_id, $item_id, $id, $format );
				break;

			case 'bgr_deny_review_action':
				$return = buddypress()->bgr_bp_review->bp_group_review_deny_review_notification_format( $secondary_item_id, $item_id, $id, $format );
				break;

			default:
				$return = '';
				break;
		}

		// Return the formatted notification.
		if ( ! empty( $return ) ) {
			return $return;
		} else {
			return $action;
		}
	}
}
