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
			add_submenu_page( 'edit.php?post_type=review', __( 'Group Reviews Admin Settings', BGR_DOMAIN ), __( 'BP Group Review Settings', BGR_DOMAIN ), 'manage_options', 'group-review-settings', array( $this, 'bgr_admin_options_page' ) );
		}

		/* Actions performed on changing admin settings tab
		* @since    1.0.0
		* @author   Wbcom Designs
		*/

		function bgr_admin_options_page(	$current = 'general'	) {
                    if( isset( $_GET['tab'] ) ) {
                        $current = sanitize_text_field( $_GET['tab'] );
                    } else {
                        $current = 'general';
                    }
                    $bgr_tabs = array(
                          'general'   	=> __("General" , BGR_DOMAIN ),
                          'criteria'  	=> __("Criteria" , BGR_DOMAIN ),
                          'shortcode'   => __("Shortcode", BGR_DOMAIN ),
                          'display'   	=> __("Display" , BGR_DOMAIN ),
                          'support'  	=> __("Support" , BGR_DOMAIN )
                    );
                    ?>
                    <div id="wpbody-content" class="bgr-setting-page" aria-label="Main content" tabindex="0">
						<div class="wrap">
				        	<div class="bgr-header">
								<div class="bgr-extra-actions">
									<button type="button" class="button button-secondary" onclick="window.open('https://wbcomdesigns.com/contact/', '_blank');"><i class="fa fa-envelope" aria-hidden="true"></i> <?php _e( 'Email Support', BGR_DOMAIN ); ?></button>
									<button type="button" class="button button-secondary" onclick="window.open('https://wbcomdesigns.com/helpdesk/article-categories/buddypress-group-reviews/', '_blank');"><i class="fa fa-file" aria-hidden="true"></i> <?php _e( 'User Manual', BGR_DOMAIN ); ?></button>
									<button type="button" class="button button-secondary" onclick="window.open('https://wordpress.org/support/plugin/review-buddypress-groups/reviews/', '_blank');"><i class="fa fa-star" aria-hidden="true"></i> <?php _e( 'Rate Us on WordPress.org', BGR_DOMAIN ); ?></button>
								</div>
							</div>
		                    <h1>
		                        <?php _e( 'Group Reviews Settings', BGR_DOMAIN );?>
		                    </h1>
		                    <div id="bgr-settings-updated" class="updated settings-error notice is-dismissible">
		                        <p><strong><?php _e( 'Group Reviews Settings Saved.', BGR_DOMAIN );?></strong></p>
		                        <button type="button" class="notice-dismiss">
		                            <span class="screen-reader-text"><?php _e( 'Dismiss this notice.', BGR_DOMAIN );?></span>
		                        </button>
		                    </div> <?php
		                    $bgr_tab_html =  '<h2 class="nav-tab-wrapper">';
		                    foreach( $bgr_tabs as $bgr_tab => $bgr_name ){
		                            $class = ($bgr_tab == $current) ? 'nav-tab-active' : '';
		                            $bgr_tab_html .=  '<a class="nav-tab ' . $class . '" href="admin.php?page=group-review-settings&tab=' . $bgr_tab . '">' . $bgr_name . '</a>';
		                    }
		                    $bgr_tab_html .= '</h2>';
		                    echo $bgr_tab_html;
		                    include 'review-admin-options-page.php';
		}

		/**
		* Actions performed to create Review cpt
		* @since    1.0.0
		* @author   Wbcom Designs
		*/

		function bgr_review_cpt() {
			$labels = array(
				'name'						=>	__( 'Reviews', BGR_DOMAIN ),
				'singular_name'			=>	__( 'Review', BGR_DOMAIN ),
				'menu_name'				=>	__( 'Reviews', BGR_DOMAIN ),
				'name_admin_bar'		=>	__( 'Reviews', BGR_DOMAIN ),
				'add_new'					=>	__( 'Add New Review', BGR_DOMAIN ),
				'add_new_item'			=>	__( 'Add New Review', BGR_DOMAIN ),
				'new_item'					=>	__( 'New Review', BGR_DOMAIN ),
				'view_item'					=>	__( 'View Reviews', BGR_DOMAIN ),
				'all_items'					=>	__( 'All Reviews', BGR_DOMAIN ),
				'search_items'			=>	__( 'Search Reviews', BGR_DOMAIN ),
				'parent_item_colon'	=>	__( 'Parent Review:', BGR_DOMAIN ),
				'not_found'					=>	__( 'No Review Found', BGR_DOMAIN ),
				'not_found_in_trash'	=>	__( 'No Review Found In Trash', BGR_DOMAIN ),
			);
			$args = array(
				'labels'						=>	$labels,
				'public'						=>	true,
				'menu_icon'				=>	'dashicons-testimonial',
				'publicly_queryable'	=>	true,
				'show_ui'					=>	true,
				'show_in_menu'			=>	true,
				'query_var'					=>	true,
				'rewrite'						=>	array( 'slug' => 'review', 'with_front' => false ),
				'capability_type'			=>	'post',
				'capabilities'				=>	array(
					'create_posts'		=>	'do_not_allow'
				),
				'map_meta_cap'			=>	true,
				'has_archive'				=>	true,
				'hierarchical'				=>	false,
				'menu_position'			=>	null,
				'supports'					=>	array( 'title', 'editor', 'author', 'thumbnail' ),
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
				'name'              		=> _x( 'Reviews Category', 'taxonomy general name' ),
				'singular_name'     		=> _x( 'Review Category', 'taxonomy singular name' ),
				'search_items'      		=> __( 'Search Categories' ),
				'all_items'         		=> __( 'All Categories' ),
				'parent_item'       		=> __( 'Parent Category' ),
				'parent_item_colon' 		=> __( 'Parent Category:' ),
				'edit_item'         		=> __( 'Edit Category' ),
				'update_item'       		=> __( 'Update Category' ),
				'add_new_item'      		=> __( 'Add Category' ),
				'new_item_name'    			=> __( 'New Category Name' ),
				'menu_name'        		 	=> __( 'Category' ),
			);
			$category_args = array(
				'hierarchical'      		=> true,
				'labels'            		=> $category_labels,
				'show_ui'           		=> false,
				'show_admin_column' 		=> true,
				'query_var'         		=> true,
				'rewrite'           		=> array( 'slug' => 'review_category' ),
			);
			register_taxonomy( 'review_category', array( 'review' ), $category_args );
		}
	}
	new BGR_Admin();
}