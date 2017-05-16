<?php
// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Class to serve AJAX Calls
 */
if( !class_exists( 'BGR_AJAX' ) ) {
	class BGR_AJAX{

		/**
		 * Constructor for Group Reviews ajax
		 *  @since   1.0.0
                 *  @author  Wbcom Designs
                */
		function __construct() {
			add_action( 'wp_ajax_bgr_save_admin_settings', array( $this, 'bgr_save_admin_settings' ) );
			add_action( 'wp_ajax_nopriv_bgr_save_admin_settings', array( $this, 'bgr_save_admin_settings' ) );
                        add_action( 'wp_ajax_bgr_accept_review', array( $this, 'bgr_accept_review' ) );
			add_action( 'wp_ajax_nopriv_bgr_accept_review', array( $this, 'bgr_accept_review' ) );
                        add_action( 'wp_ajax_bgr_deny_review', array( $this, 'bgr_deny_review' ) );
			add_action( 'wp_ajax_nopriv_bgr_deny_review', array( $this, 'bgr_deny_review' ) );
                        add_action( 'wp_ajax_bgr_remove_review', array( $this, 'bgr_remove_review' ) );
			add_action( 'wp_ajax_nopriv_bgr_remove_review', array( $this, 'bgr_remove_review' ) );
		}

		/**
		 * Actions performed for saving admin settings
		 *  @since   1.0.0
                 *  @author  Wbcom Designs
                */
		function bgr_save_admin_settings() {
			if( isset( $_POST['action'] ) && $_POST['action'] === 'bgr_save_admin_settings' ) {
				$allow_popup         = sanitize_text_field( $_POST['allow_popup'] );
                                $reviews_per_page    = sanitize_text_field( $_POST['reviews_per_page'] );
                                $rating_fields       = array_map('sanitize_text_field', wp_unslash($_POST['field_values']));
                                $rating_field_values = array_unique($rating_fields); 
				$bgr_admin_settings  = array(
                                    	  'add_review_allow_popup'   => $allow_popup,
                                          'reviews_per_page'         => $reviews_per_page,   
                                          'add_review_rating_fields' => $rating_field_values
				);
				update_option( 'bgr_admin_settings', $bgr_admin_settings );
				_e('admin-settings-saved',BGR_DOMAIN);                                                           
                                  
                                
				die;
			}
		}
                /**
		 * Actions performed when accept review
		 *  @since   1.0.0
                 *  @author  Wbcom Designs
                */
                function bgr_accept_review(){
                    $post_id = sanitize_text_field($_POST['accept_review_id']);                     
                    $group_id = sanitize_text_field($_POST['group_id']);  
                    $post_author_id = get_post_field( 'post_author', $post_id );
                    wp_publish_post( $post_id );                   
                    die;
                }
                /**
		 * Actions performed when deny review
		 *  @since   1.0.0
                 *  @author  Wbcom Designs
                */
                function bgr_deny_review(){
                   $post_id = sanitize_text_field($_POST['deny_review_id']); 
                   $group_id = sanitize_text_field($_POST['group_id']); 
                   $post_author_id = get_post_field( 'post_author', $post_id );
                   wp_trash_post( $post_id );                 
                   die;
                }
                /**
		 * Actions performed when remove review
		 *  @since   1.0.0
                 *  @author  Wbcom Designs
                */
                function bgr_remove_review(){
                   $post_id = sanitize_text_field($_POST['remove_review_id']); 
                   wp_trash_post( $post_id ); 
                   die;
                }
                
	}
	new BGR_AJAX();
}