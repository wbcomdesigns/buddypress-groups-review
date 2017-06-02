<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) { 
    exit; 
}

/**
 * Class to add custom hooks for this plugin
 */
if( !class_exists( 'BGR_Custom_Hooks' ) ) {
	class BGR_Custom_Hooks{

		/** Constructor for custom hooks
                 *  @since   1.0.0
                 *  @access   public
                 *  @author  Wbcom Designs
                 */
            
		public function __construct() {
			add_action( 'wp', array( $this, 'bgr_add_groups_reviews_tab' ) );
			add_action( 'init', array( $this, 'bgr_add_groups_reviews_taxonomy_term' ) );
			add_filter( 'post_row_actions', array( $this, 'bgr_group_reviews_row_actions' ), 10, 2 );
			add_action( 'admin_footer-edit.php', array( $this, 'bgr_disable_review_title_edit_link' ) );
			add_filter( 'bulk_actions-edit-review', array( $this, 'bgr_remove_edit_bulk_actions' ), 10, 1 );
			add_action( 'bp_before_group_header_meta', array( $this, 'bgr_group_average_rating') );
                        add_action( 'bgr_display_group_average_ratings', array( $this, 'bgr_group_avg' ) );		
		}

		/** Actions performed to show average rating on a group
		 *  @since   1.0.0
                 *  @access   public
                 *  @author  Wbcom Designs
                 */
                
		public function bgr_group_average_rating() {			
			//Gather all the group reviews
			$args = array(
				'post_type' => 'review',
				'posts_per_page' => -1,
				'post_status' => 'publish',
				'category' => 'group',
				'meta_query' => array(
					array(
						'key'		=>	'linked_group',
						'value'		=>	bp_get_group_id(),
						'compare'	=>	'=',
					),
				),
			);
			$reviews = get_posts( $args );
                        $bgr_admin_settings = get_option( 'bgr_admin_settings' );
                        $review_rating_fields = $bgr_admin_settings['add_review_rating_fields'];
                        if( !empty( $review_rating_fields ) ) { 
				if( !empty( $reviews ) ) {						
						$ttl_rating    = 0;
						$reviews_count = 0;
						$total_review_count = count($reviews);
						foreach( $reviews as $review ) {
                                                    $rate = 0;
                                                    $review_ratings       = get_post_meta( $review->ID, 'review_star_rating', false );                                               

                                                    $reviews_field_count  = 0;
                                                    if( !empty( $review_rating_fields ) && !empty( $review_ratings[0] ) ):                                                  
                                                            foreach( $review_ratings[0] as $field => $value ) {
                                                                    if( in_array( $field, $review_rating_fields  )) {
                                                                            $rate += $value;
                                                                            $reviews_field_count++;                                                                                        
                                                                    }
                                                            }
                                                            if($reviews_field_count > 0 ) {
                                                                    $reviews_count++;
                                                                    $reviews_field_count = ( $reviews_field_count == 0 ) ? 1 : $reviews_field_count;   
                                                                    $ttl_rating += (int)$rate/$reviews_field_count;                                                                       
                                                            }                                                                        
                                                    endif;                                       					
						}
                                                
						$reviews_count = ( $reviews_count == 0 ) ? 1 : $reviews_count;   
						$avg_rating = $ttl_rating / $reviews_count;
						$avg_rating = round( $avg_rating, 2 );	 ?>
						<div class="bgr-col-12"><?php
						 do_action('bgr_display_group_average_ratings', $avg_rating );
						_e("</div><div>Rating: <span>($avg_rating)</span></div>" , BGR_DOMAIN );
						echo "<div>Total Reviews : $total_review_count</div>";
				} else {
						echo 'No Rating Available!';
				}
			}   
		}

                /**
		*  Actions performed for rating display in  Group avg 
		*  @since   1.0.0
                *  @access   public
		*  @author  Wbcom Designs
		*/
                
		public function bgr_group_avg( $avg_rating ) {			
			$type = gettype($avg_rating);
			$var = (int)$avg_rating - $avg_rating; 
                        $stars_on = 0;
                        $stars_off = 0;
                        $stars_half = 0;
                        
			if( $var == 0 ) {
					$stars_on = $avg_rating;
					$stars_off = 5 - $avg_rating;
					$stars_half = 0;
			}
			else {	
					$stars_on = intval( $avg_rating );
					$stars_half = 1;
					$stars_off = 5 - ( $stars_on + $stars_half );
			}
                        
                        for( $i = 1; $i <= $stars_on; $i++ ) {
                                        ?><img class="stars" src="<?php _e( BGR_PLUGIN_URL."assets/images/star.png", BGR_DOMAIN );?>" alt="<?php _e('star', BGR_DOMAIN );?>"><?php
                        }

                        for( $i = 1; $i <= $stars_half; $i++ ) {
                                        ?><img class="stars" src="<?php _e( BGR_PLUGIN_URL."assets/images/star_half.png", BGR_DOMAIN );?>" alt="<?php _e('star', BGR_DOMAIN );?>"><?php
                        }

                        for( $i = 1; $i <= $stars_off; $i++ ) {
                                        ?><img class="stars" src="<?php _e( BGR_PLUGIN_URL."assets/images/star_off.png", BGR_DOMAIN );?>" alt="<?php _e('star', BGR_DOMAIN );?>"><?php
                        } 
			
			
		}
                
		/** Actions performed to remove edit from bulk options
		 *  @since   1.0.0
                 *  @access   public
                 *  @author  Wbcom Designs
                 */
                
		public function bgr_remove_edit_bulk_actions( $actions ) {
			unset( $actions['edit'] );
			return $actions;
		}

		/** Actions performed to hide row actions
		 *  @since   1.0.0
                 *  @access   public
                 *  @author  Wbcom Designs
                 */
                
		public function bgr_group_reviews_row_actions( $actions, $post ) {
			global $bp;
			if( $post->post_type == 'review' ) {
				unset( $actions['edit'] );
				unset( $actions['view'] );
				unset( $actions['inline hide-if-no-js'] );

				if( wp_get_object_terms( $post->ID, 'review_category' )[0]->name == 'Group' ) {
					//Add a link to view the review
					$review_title = $post->post_title;
					$linked_group = get_post_meta( $post->ID, 'linked_group', true );
					$group = groups_get_group( array( 'group_id' => $linked_group ) );
					$group_url = bp_get_group_permalink( $group );
					$review_url = $group_url.'reviews/view/'.$post->ID;
					
					$actions['view_review'] = '<a href="'.$review_url.'" title="'.$review_title.'">View Review</a>';
				}
			}
			return $actions;
		}

		/** Action performed to add taxonomy term for group reviews
		 *  @since   1.0.0
                 *  @access   public
                 *  @author  Wbcom Designs
                 */
                
		public function bgr_add_groups_reviews_taxonomy_term(){
			$termExists = term_exists( 'Group', 'review_category' );
			if( $termExists === 0 || $termExists === null ) {
				wp_insert_term( 'Group', 'review_category' );
			}
		}

		/** Action performed to add a tab for group reviews
		 *  @since   1.0.0
                 *  @access   public
                 *  @author  Wbcom Designs
                 */
                
		public function bgr_add_groups_reviews_tab() {
			if ( bp_is_group_single() ) {
				global $bp;
				global $post, $wp, $wpdb;
				
					$args = array(						
						'category' => 'group',
						'orderby' => 'post_date',
						'order' => 'DESC',						
						'meta_key' => 'linked_group',
						'meta_value' => $bp->groups->current_group->id,
						'post_type' => 'review',
						'post_status' => 'publish',
						'suppress_filters' => true
					);
					
                                        $post_count = 0;
					$recent_posts = wp_get_recent_posts( $args );
					if( !empty( $recent_posts ) ):		
						foreach( $recent_posts as $recent ){
							$post_count++;
						}
					endif;
					wp_reset_query();
					
					$tab_args = array(
						'name' => __( 'Reviews <span>'.$post_count.'</span>', BGR_DOMAIN ),
						'slug' => 'reviews',
						'parent_slug' => $bp->groups->current_group->slug,
						'parent_url' => bp_get_group_permalink( $bp->groups->current_group ),
						'screen_function' => array( $this, 'bgr_group_reviews_tab' ),
						'position' => 199,
					);
					bp_core_new_subnav_item( $tab_args );
				
			}
		}

		/** Action performed to show screen of reviews listing tab
		 *  @since   1.0.0
                 *  @access   public
                 *  @author  Wbcom Designs
                 */
                
		public function bgr_group_reviews_tab() {
			add_action( 'bp_template_content', array( $this, 'bgr_group_reviews_tab_template' ) );

			$templates = array('groups/single/plugins.php','plugin-template.php');
			if( strstr( locate_template($templates), 'groups/single/plugins.php' ) ) {
				bp_core_load_template( apply_filters( 'bp_core_template_plugin', 'groups/single/plugins' ) );
			} else {
				bp_core_load_template( apply_filters( 'bp_core_template_plugin', 'plugin-template' ) );
			}
		}

		/** Action performed to show the content of reviews list tab
		 *  @since   1.0.0
                 *  @access   public
                 *  @author  Wbcom Designs
                 */
                
		public function bgr_group_reviews_tab_template() {
			if( strpos( $_SERVER['REQUEST_URI'], 'reviews/view' ) !== false ) {
				include 'templates/bgr-single-review-template.php';
			} else {
				include 'templates/bgr-reviews-tab-template.php';
			}
		}

	}
	new BGR_Custom_Hooks();
}