<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

//Add admin page for importing Review(s)
if (!class_exists('BGR_Admin')) {
    class BGR_Admin {

        /* constructor for admin settings
		* @since    1.0.0
		* @author   Wbcom Designs
		*/

        function __construct() {
            add_action('admin_menu', array($this, 'bgr_add_submenu_page_admin_settings'));
            $post_types = get_post_types();
            if (!in_array('review', $post_types)) {
                //Custom Post Type
                add_action('init', array($this, 'bgr_review_cpt'));
                add_action('init', array($this, 'bgr_review_taxonomy_cpt'));
            }
        }

        /* Actions performed on loading admin_menu
		 * @since    1.0.0
		 * @author   Wbcom Designs
		*/

        function bgr_add_submenu_page_admin_settings() {
            add_submenu_page('edit.php?post_type=review', __('Group Reviews Admin Settings', 'bp-group-reviews'), __('Group Settings', 'bp-group-reviews'), 'manage_options', 'group-review-settings', array($this, 'bgr_admin_options_page'));
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
                            'general'   	=> __("General" , 'bp-group-reviews' ),
                            'criteria'  	=> __("Criteria" , 'bp-group-reviews' ),
                            'shortcode'   => __("Shortcode", 'bp-group-reviews' ),
                            'display'   	=> __("Display" , 'bp-group-reviews' ),
                            'support'  	=> __("Support" , 'bp-group-reviews' )
                    );
                    ?>
                    <div id="wpbody-content" class="bgr-setting-page" aria-label="Main content" tabindex="0">
						<div class="wrap">
				        	<div class="bgr-header">
								<div class="bgr-extra-actions">
									<button type="button" class="button button-secondary" onclick="window.open('https://wbcomdesigns.com/contact/', '_blank');"><i class="fa fa-envelope" aria-hidden="true"></i> <?php _e( 'Email Support', 'bp-group-reviews' ); ?></button>
									<button type="button" class="button button-secondary" onclick="window.open('https://wbcomdesigns.com/helpdesk/article-categories/buddypress-group-reviews/', '_blank');"><i class="fa fa-file" aria-hidden="true"></i> <?php _e( 'User Manual', 'bp-group-reviews' ); ?></button>
									<button type="button" class="button button-secondary" onclick="window.open('https://wordpress.org/support/plugin/review-buddypress-groups/reviews/', '_blank');"><i class="fa fa-star" aria-hidden="true"></i> <?php _e( 'Rate Us on WordPress.org', 'bp-group-reviews' ); ?></button>
								</div>
							</div>
		                    <h1>
		                        <?php _e( 'Group Reviews Settings', 'bp-group-reviews' );?>
		                    </h1>
		                    <div id="bgr-settings-updated" class="updated settings-error notice is-dismissible">
		                        <p><strong><?php _e( 'Group Reviews Settings Saved.', 'bp-group-reviews' );?></strong></p>
		                        <button type="button" class="notice-dismiss">
		                            <span class="screen-reader-text"><?php _e( 'Dismiss this notice.', 'bp-group-reviews' );?></span>
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
                'name'						=>	__('Reviews', 'bp-group-reviews'),
                'singular_name'			=>	__('Review', 'bp-group-reviews'),
                'menu_name'				=>	__('Reviews', 'bp-group-reviews'),
                'name_admin_bar'		=>	__('Reviews', 'bp-group-reviews'),
                'add_new'					=>	__('Add New Review', 'bp-group-reviews'),
                'add_new_item'			=>	__('Add New Review', 'bp-group-reviews'),
                'new_item'					=>	__('New Review', 'bp-group-reviews'),
                'view_item'					=>	__('View Reviews', 'bp-group-reviews'),
                'all_items'					=>	__('All Reviews', 'bp-group-reviews'),
                'search_items'			=>	__('Search Reviews', 'bp-group-reviews'),
                'parent_item_colon'	=>	__('Parent Review', 'bp-group-reviews'),
                'not_found'					=>	__('No Review Found', 'bp-group-reviews'),
                'not_found_in_trash'	=>	__('No Review Found In Trash', 'bp-group-reviews'),
            );
            $args = array(
                'labels'						=>	$labels,
                'public'						=>	true,
                'menu_icon'				=>	'dashicons-testimonial',
                'publicly_queryable'	=>	true,
                'show_ui'					=>	true,
                'show_in_menu'			=>	true,
                'query_var'					=>	true,
                'rewrite'						=>	array('slug' => 'review', 'with_front' => false),
                'capability_type'			=>	'post',
                'capabilities'				=>	array(
                    'create_posts'		=>	'do_not_allow'
                ),
                'map_meta_cap'			=>	true,
                'has_archive'				=>	true,
                'hierarchical'				=>	false,
                'menu_position'			=>	null,
                'supports'					=>	array('title', 'editor', 'author', 'thumbnail'),
            );
            register_post_type('review', $args);
        }

        /**
         * Actions performed to create Review cpt taxonomy
         * @since    1.0.0
         * @author   Wbcom Designs
         */

        function bgr_review_taxonomy_cpt() {
            $category_labels = array(
                'name'              		=> _x( 'Reviews Category', 'taxonomy general name', 'bp-group-reviews'  ),
                'singular_name'     		=> _x( 'Review Category', 'taxonomy singular name', 'bp-group-reviews' ),
                'search_items'      		=> __( 'Search Categories', 'bp-group-reviews'  ),
                'all_items'         		=> __( 'All Categories', 'bp-group-reviews'  ),
                'parent_item'       		=> __( 'Parent Category', 'bp-group-reviews'  ),
                'parent_item_colon' 		=> __( 'Parent Category:', 'bp-group-reviews'  ),
                'edit_item'         		=> __( 'Edit Category' , 'bp-group-reviews' ),
                'update_item'       		=> __( 'Update Category', 'bp-group-reviews'  ),
                'add_new_item'      		=> __( 'Add Category', 'bp-group-reviews'  ),
                'new_item_name'    			=> __( 'New Category Name', 'bp-group-reviews'  ),
                'menu_name'        		 	=> __( 'Category' , 'bp-group-reviews' ),
            );
            $category_args = array(
                'hierarchical'      		=> true,
                'labels'            		=> $category_labels,
                'show_ui'           		=> false,
                'show_admin_column' 		=> true,
                'query_var'         		=> true,
                'rewrite'           		=> array('slug' => 'review_category'),
            );
            register_taxonomy('review_category', array('review'), $category_args);
        }
    }
    new BGR_Admin();
}
