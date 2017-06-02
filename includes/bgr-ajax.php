<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) { 
    exit; 
}

/**
 * Class to serve AJAX Calls
 */

if( !class_exists( 'BGR_AJAX' ) ) {
	class BGR_AJAX{

		/**
		 * Constructor for Group Reviews ajax
		 *  @since   1.0.0
                 *  @access   public 
                 *  @author  Wbcom Designs
                 */
            
		public function __construct() {
			add_action( 'wp_ajax_bgr_save_admin_settings', array( $this, 'bgr_save_admin_settings' ) );
			add_action( 'wp_ajax_nopriv_bgr_save_admin_settings', array( $this, 'bgr_save_admin_settings' ) );
                        add_action( 'wp_ajax_bgr_accept_review', array( $this, 'bgr_accept_review' ) );
			add_action( 'wp_ajax_nopriv_bgr_accept_review', array( $this, 'bgr_accept_review' ) );
                        add_action( 'wp_ajax_bgr_deny_review', array( $this, 'bgr_deny_review' ) );
			add_action( 'wp_ajax_nopriv_bgr_deny_review', array( $this, 'bgr_deny_review' ) );
                        add_action( 'wp_ajax_bgr_remove_review', array( $this, 'bgr_remove_review' ) );
			add_action( 'wp_ajax_nopriv_bgr_remove_review', array( $this, 'bgr_remove_review' ) );
                        add_action( 'wp_ajax_bgr_submit_review', array( $this, 'bgr_submit_review' ) );
			add_action( 'wp_ajax_nopriv_bgr_submit_review', array( $this, 'bgr_submit_review' ) );
		}

		/**
		 *  Actions performed for saving admin settings
		 *  @since   1.0.0
                 *  @access   public 
                 *  @author  Wbcom Designs
                 */
                
		public function bgr_save_admin_settings() {
			if( isset( $_POST['action'] ) && $_POST['action'] === 'bgr_save_admin_settings' ) {
				$allow_popup         = sanitize_text_field( $_POST['allow_popup'] );
                                $reviews_per_page    = sanitize_text_field( $_POST['reviews_per_page'] );
                                $rating_fields       = array_map('sanitize_text_field', wp_unslash( $_POST['field_values'] ) );
                                $rating_field_values = array_unique( $rating_fields ); 
				$bgr_admin_settings  = array(
                                    	  'add_review_allow_popup'   => $allow_popup,
                                          'reviews_per_page'         => $reviews_per_page,   
                                          'add_review_rating_fields' => $rating_field_values
				);
				update_option( 'bgr_admin_settings', $bgr_admin_settings );
				_e('admin-settings-saved', BGR_DOMAIN );                                                           
				die;
			}
		}
                
                /**
		 *  Actions performed when submit review
		 *  @since   1.0.0
                 *  @access   public 
                 *  @author  Wbcom Designs
                 */   
                
                public function bgr_submit_review() {
                    //Submit Review 
                    global $bp;
                    $bgr_admin_settings = get_option( 'bgr_admin_settings' );                   
                    if( !empty( $bgr_admin_settings ) ) {            
                        $review_rating_fields = $bgr_admin_settings['add_review_rating_fields'];                            
                    }
                    parse_str( $_POST['data'], $formarray );             
                    $review_subject = sanitize_text_field( $formarray['review-subject'] );
                    $review_desc    = sanitize_text_field( $formarray['review-desc'] );
                    $form_group_id  = sanitize_text_field( $formarray['form-group-id'] ); 
                    
                    if( !empty( $formarray['rated_stars'] ) ) {
                        $rated_field_values = array_map('sanitize_text_field', wp_unslash( $formarray['rated_stars'] ) );                         
                    }
                    if( !empty( $review_rating_fields ) && !empty( $rated_field_values ) ) {
                        $rated_stars       = array_combine( $review_rating_fields, $rated_field_values );
                    }
                    $add_review_args = array(
                            'post_type'    => 'review',
                            'post_title'   => $review_subject,
                            'post_content' => $review_desc,
                            'post_status'  => 'draft'
                    );
                    $review_id = wp_insert_post( $add_review_args );

                    wp_set_object_terms( $review_id, 'Group', 'review_category' );	
                    update_post_meta( $review_id, 'linked_group', $form_group_id );
                    if( !empty( $rated_stars ) ) {
                        update_post_meta( $review_id, 'review_star_rating', $rated_stars );
                    } 
                    exit;
                }
                
                /**
		 *  Actions performed when accept review
		 *  @since   1.0.0
                 *  @access   public 
                 *  @author  Wbcom Designs
                 */
                
                public function bgr_accept_review() {
                    $post_id = sanitize_text_field( $_POST['accept_review_id'] );                 
                    wp_publish_post( $post_id );                   
                    die;
                }
                
                /**
		 *  Actions performed when deny review
		 *  @since   1.0.0
                 *  @access   public 
                 *  @author  Wbcom Designs
                 */
                
                public function bgr_deny_review() {
                    $post_id = sanitize_text_field( $_POST['deny_review_id'] );                   
                    wp_trash_post( $post_id );                 
                    die;
                }
                
                /**
		 *  Actions performed when remove review
		 *  @since   1.0.0
                 *  @access   public 
                 *  @author  Wbcom Designs
                 */
                
                public function bgr_remove_review() {
                    $post_id = sanitize_text_field($_POST['remove_review_id']); 
                    wp_trash_post( $post_id ); 
                    die;
                }
                
	}
	new BGR_AJAX();
}