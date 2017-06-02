<?php
/**
 * Group Course settings tab
 * The class_exists() check is recommended, to prevent problems during upgrade
 * or when the Groups component is disabled
 */

if ( class_exists( 'BP_Group_Extension' ) ) :

	class Group_Reviews_Management_Extn extends BP_Group_Extension {
                
		/** constructor for Group Reviews Management
                * @since    1.0.0  
                * @access   public              
                * @author   Wbcom Designs              
                */ 
    
		public function __construct() {
			$args = array(
				'slug'				=>	'manage-reviews',
				'name'				=>	__('Manage Reviews',BGR_DOMAIN),
				'nav_item_position'		=>	99,
				'enable_nav_item'		=>	false,
                                'screens' => array(
                                    'edit' => array(
                                        'name' => 'Reviews',
                                        'enabled' => true,
                                    ),
                                    'create' => array(
                                    'enabled' => false,
                                    )),
                              'show_tab' => false,
                             
			);
			parent::init( $args );
		}                
                
		/**  Display all posted reviews that not checked by group admins.
                 *  @since   1.0.0
                 *  @access   public
                 *  @author  Wbcom Designs
                 */
                
                public function edit_screen( $group_id = NULL ){
                    global $bp,$wpdb,$post;             
                    //Admin Settings
                    $bgr_admin_settings = get_option( 'bgr_admin_settings' );                    
                    if( !empty( $bgr_admin_settings ) ) {
                        $reviews_per_page     = $bgr_admin_settings['reviews_per_page'];
                    }
                    
                    if(empty($reviews_per_page)){
                            $reviews_per_page = -1;
                    }
                    
                    $paged = ( get_query_var('paged') ) ? get_query_var('paged') : 1;
                    $args = array(
						'post_type' => 'review',
						'posts_per_page' => -1,
						'post_status' => 'draft',
                                                'posts_per_page' => $reviews_per_page,
                                                'paged' => $paged,
						'category' => 'group',
						'meta_query' => array(
							array(
								'key'	  =>	'linked_group',
								'value'	  =>	$bp->groups->current_group->id,
								'compare' =>	'=',
							),
						),
			);
                    $reviews = new WP_Query($args); ?>
                    <div id="request-review-list" class="item-list reviews-item-list">
                        <?php if ( $reviews->have_posts() ):                            	
                             while ( $reviews->have_posts() ): $reviews->the_post(); 
                            ?>
                                <div class="bgr-row item-list group-request-list">
                                <div class="bgr-col-2"> 
                                    <?php $author =  $reviews->post->post_author;                                                                        
                                     bp_displayed_user_avatar( array( 'item_id' => $author ) ); ?> 
                                </div>
                                <div class="bgr-col-8">
                                    <div class="item-title">
                                        <?php  _e(bp_core_get_userlink($author), BGR_DOMAIN ); ?>
                                    </div>
                                    <div class="item-description">
                                        <div class="review-subject">
                                            <b> <?php _e('Review Subject: ', BGR_DOMAIN ); ?> </b>
                                            <?php the_title(); ?>
                                        </div>
                                        <div class="review-description">                                                                       
                                            <div class="review-excerpt bgr-col-12">
                                                <b> <?php _e('Review Short Description: ', BGR_DOMAIN ); ?> </b>
                                                <?php $content      = get_the_content();                                                     
                                                      $shortContent = preg_split( '/(\.|!|\?)\s/', $content, 2, PREG_SPLIT_DELIM_CAPTURE );    
                                                      _e($shortContent[0].".", BGR_DOMAIN );
                                                ?>
                                            </div>    
                                            <div class="review-full-description bgr-col-12">
                                                <div class="bgr-col-12">
                                                    <?php _e('<b>Review Full Description:</b> ', BGR_DOMAIN );
                                                    the_content();
                                                    ?>
                                                </div>
						<?php	
                                                    $bgr_admin_settings   = get_option( 'bgr_admin_settings' );
                                                    $review_rating_fields = $bgr_admin_settings['add_review_rating_fields'];
                                                    $review_ratings       = get_post_meta( $post->ID, 'review_star_rating',false );
                                                    if( !empty( $review_rating_fields ) && !empty( $review_ratings[0] ) ):                                                  
                                                        foreach( $review_ratings[0] as $field => $value ) {
                                                            if( in_array( $field, $review_rating_fields ) ) {
                                                                ?>
                                                                <div class="bgr-col-6"> 
                                                                    <?php _e($field.' : ', BGR_DOMAIN );

                                                                    /*** Ratings *****/
                                                                    $stars_on  = $value;
                                                                    $stars_off = 5 - $stars_on; 
                                                                    for( $i = 1; $i <= $stars_on; $i++ ){
                                                                        ?><img class="stars" src="<?php _e( BGR_PLUGIN_URL."assets/images/star.png", BGR_DOMAIN );?>" alt="<?php _e('star', BGR_DOMAIN ); ?>"><?php
                                                                    }
                                                                    for( $i = 1; $i <= $stars_off; $i++ ){
                                                                        ?><img class="stars" src="<?php _e( BGR_PLUGIN_URL."assets/images/star_off.png", BGR_DOMAIN );?>" alt="<?php _e('star', BGR_DOMAIN ); ?>"><?php
                                                                    }
                                                                    ?>
                                                                </div>
                                                                <?php                                                                    
                                                            }
                                                        }
                                                    endif;                                          
                                                ?>
                                            </div>
                                            <a class="expand-review-des"><?php _e('View More..', BGR_DOMAIN ); ?> </a>
                                        </div>                                        
                                    </div>
                                </div>
                                <div class="bgr-col-2">
                                    <div class='bgr-col-12 accept-review generic-button'>
                                        <a class='accept-button'> <?php _e('Accept', BGR_DOMAIN ); ?> </a><input type="hidden" name="accept_review_id" value="<?php _e($post->ID, BGR_DOMAIN ); ?>"> <input type="hidden" name="gid" value="<?php _e($bp->groups->current_group->id, BGR_DOMAIN ); ?>">
                                    </div>
                                    <div class='bgr-col-12 deny-review generic-button'>
                                        <a class='deny-button '> <?php _e('Deny', BGR_DOMAIN); ?> </a><input type="hidden" name="deny_review_id" value="<?php _e($post->ID, BGR_DOMAIN ); ?>"> <input type="hidden" name="gid" value="<?php _e($bp->groups->current_group->id, BGR_DOMAIN ); ?>">
                                    </div>
                                </div>
                                
                                <div class="clear"></div>	                        
                                </div>                               
                            <?php                    
                            endwhile;
                            $total_pages = $reviews->max_num_pages;
                             if ( $total_pages > 1 ) { ?>
                                <div class="review-pagination">
                                    <?php
                                        $current_page = max(1, get_query_var('paged'));
                                        echo paginate_links(array(
                                                'base'      => get_pagenum_link(1) . '%_%',
                                                'format'    => 'page/%#%',
                                                'current'   => $current_page,
                                                'total'     => $total_pages,
                                                'prev_text' => __('prev'),
                                                'next_text' => __('next'),
                                                ));?>
                                </div><?php
                            }
                            wp_reset_postdata();
                            else: ?>
                            <div id="message" class="info">
                                <p><?php _e( "Sorry, no reviews were found.", BGR_DOMAIN ); ?></p>
                            </div>
                            <?php endif; ?>
                    </div>         
                    <?php
                }	
	}
    bp_register_group_extension( 'Group_Reviews_Management_Extn' );
    
endif; 
