<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

//Add admin page for importing Review(s)
if( !class_exists( 'BGR_Admin' ) ) {
	class BGR_Admin{

		/* constructor for admin settings
                * @since    1.0.0                
                * @author   Wbcom Designs              
                */            
		function __construct() {
			add_action( 'admin_menu', array( $this, 'bgr_add_submenu_page_admin_settings' ) );

			$post_types = get_post_types();
			if( !in_array( 'review', $post_types ) ) {
				//Custom Post Type
				add_action( 'init', array( $this, 'bgr_review_cpt' ) );
				add_action( 'init', array( $this, 'bgr_review_taxonomy_cpt' ) );
			}
		}

		
                /* Actions performed on loading admin_menu
                 * @since    1.0.0                
                 * @author   Wbcom Designs              
                */
                
		function bgr_add_submenu_page_admin_settings() {
			add_submenu_page( 'edit.php?post_type=review', __( 'Group Reviews Admin Settings', BGR_DOMAIN ), __( 'Group Review Settings', BGR_DOMAIN ), 'manage_options', 'group-review-settings', array( $this, 'bgr_admin_options_page' ) );
		}

		function bgr_admin_options_page() {
			include 'review-admin-options-page.php';
		}

		/**
		  * Actions performed to create Review cpt
		  * @since    1.0.0                
                  * @author   Wbcom Designs              
                */
                
		function bgr_review_cpt() {
			$labels = array(
                        'name'				=>	__( 'Reviews', BGR_DOMAIN ),
                        'singular_name'			=>	__( 'Review', BGR_DOMAIN ),
                        'menu_name'			=>	__( 'Reviews', BGR_DOMAIN ),
                        'name_admin_bar'		=>	__( 'Reviews', BGR_DOMAIN ),
                        'add_new'			=>	__( 'Add New Review', BGR_DOMAIN ),
                        'add_new_item'			=>	__( 'Add New Review', BGR_DOMAIN ),
                        'new_item'			=>	__( 'New Review', BGR_DOMAIN ),
                        'view_item'			=>	__( 'View Reviews', BGR_DOMAIN ),
                        'all_items'			=>	__( 'All Reviews', BGR_DOMAIN ),
                        'search_items'			=>	__( 'Search Reviews', BGR_DOMAIN ),
                        'parent_item_colon'		=>	__( 'Parent Review:', BGR_DOMAIN ),
                        'not_found'			=>	__( 'No Review Found', BGR_DOMAIN ),
                        'not_found_in_trash'            =>	__( 'No Review Found In Trash', BGR_DOMAIN ),
			);
			$args = array(
				'labels'			=>	$labels,
				'public'			=>	true,
				'menu_icon'			=>	BGR_PLUGIN_URL.'admin/assets/images/review.png',
				'publicly_queryable'            =>	true,
				'show_ui'			=>	true,
				'show_in_menu'			=>	true,
				'query_var'			=>	true,
				'rewrite'			=>	array( 'slug' => 'review', 'with_front' => false ),
				'capability_type'		=>	'post',
				'capabilities'			=>	array(
				'create_posts'                  =>	'do_not_allow'
				),
				'map_meta_cap'			=>	true,
				'has_archive'			=>	true,
				'hierarchical'			=>	false,
				'menu_position'			=>	null,
				'supports'			=>	array( 'title', 'editor', 'author', 'thumbnail' ),
			);
			register_post_type( 'review', $args );
		}

		/**
		 * Actions performed to create Review cpt taxonomy
		 * @since    1.0.0                
                 * @author   Wbcom Designs              
                */
		function bgr_review_taxonomy_cpt() {
			$category_labels = array(
				'name'              =>  _x( 'Reviews Category', 'taxonomy general name' ),
				'singular_name'     =>  _x( 'Review Category', 'taxonomy singular name' ),
				'search_items'      =>  __( 'Search Categories' ),
				'all_items'         =>  __( 'All Categories' ),
				'parent_item'       =>  __( 'Parent Category' ),
				'parent_item_colon' =>  __( 'Parent Category:' ),
				'edit_item'         =>  __( 'Edit Category' ),
				'update_item'       =>  __( 'Update Category' ),
				'add_new_item'      =>  __( 'Add Category' ),
				'new_item_name'     =>  __( 'New Category Name' ),
				'menu_name'         =>  __( 'Category' ),
			);
			$category_args = array(
				'hierarchical'      =>  true,
				'labels'            =>  $category_labels,
				'show_ui'           =>  false,
				'show_admin_column' =>  true,
				'query_var'         =>  true,
				'rewrite'           =>  array( 'slug' => 'review_category' ),
			);
			register_taxonomy( 'review_category', array( 'review' ), $category_args );
		}
	}
	new BGR_Admin();
}