<?php
// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

$url = $_SERVER['REQUEST_URI'];
$review_id = basename($url); 
$review = get_post( $review_id );

$review_title = $review->post_title;
$author       = $review->post_author;
$author_details = get_userdata( $author );
$review_author = $author_details->data->user_login;
$review_author_url = home_url().'/author/'.$review_author;
$review_date_time = $review->post_date;
$review_date = explode( ' ' , $review_date_time )[0];
?>
<div class="bgr-single-review">
    <div class="bgr-row item-list group-request-list">
            <div class="bgr-col-2">                 <?php                                           
                        bp_displayed_user_avatar( array( 'item_id' => $author));
                ?>
            </div>
            <div class="bgr-col-10">
                <div class="reviewer">
                    <?php _e( '<b>Reviewer : '.bp_core_get_userlink($author).'</b> ', BGR_DOMAIN );                                         
                                        ?>
                </div>  

                <div class="item-description">
                    <div class="review-subject">
                        <b> <?php _e('Review Subject: ',BGR_DOMAIN); ?> </b>
                        <?php _e($review_title,BGR_DOMAIN); ?>
                    </div>
                    <div class="review-description">                                                                       
                        <div class="review-excerpt bgr-col-12">
                        <b> <?php _e('Review Short Description: ',BGR_DOMAIN); ?> </b>
                        <?php $content      = $review->post_content;                                                     
                              $shortContent = preg_split( '/(\.|!|\?)\s/', $content, 2, PREG_SPLIT_DELIM_CAPTURE );    
                              _e($shortContent[0].".",BGR_DOMAIN);
                         ?>
                        </div> 
                        <div class="review-full-description bgr-col-12">
						    <div class="bgr-col-12"> 
                               <?php _e('<b>Review Full Description:</b> ',BGR_DOMAIN);
                                     _e($review->post_content,BGR_DOMAIN); ?>
							</div>
							<?php	
                            $bgr_admin_settings   = get_option( 'bgr_admin_settings' );
                            $review_rating_fields = $bgr_admin_settings['add_review_rating_fields'];
                            $review_ratings       = get_post_meta( $review_id, 'review_star_rating',false);
                            if(!empty($review_rating_fields) && !empty($review_ratings[0])):                                                  
                                foreach($review_ratings[0] as $field => $value){
                                    if(in_array($field,$review_rating_fields)){
                                    ?>
                                    <div class="bgr-col-6"> 
                                    <?php
                                         _e($field.' : ',BGR_DOMAIN);
                                                                    
                                        /*** Ratings *****/
                                        $stars_on  = $value;
                                        $stars_off = 5 - $stars_on; 
                                        for( $i = 1; $i <= $stars_on; $i++ ){
                                        ?><img class="stars" src="<?php _e(BGR_PLUGIN_URL."assets/images/star.png", BGR_DOMAIN );?>" alt="<?php _e('star', BGR_DOMAIN ); ?>"><?php
                                        }
                                        for( $i = 1; $i <= $stars_off; $i++ ){
                                        ?><img class="stars" src="<?php _e(BGR_PLUGIN_URL."assets/images/star_off.png", BGR_DOMAIN );?>" alt="<?php _e('star', BGR_DOMAIN ); ?>"><?php
                                        }
                                        ?>
                                    </div>
																	<?php                                                                    
                                 }
                             }
                            endif;                                                      
                                                
                            ?>
                        </div>
                     <a class="expand-review-des"><?php _e('View More..',BGR_DOMAIN); ?> </a>
                    </div>

                </div>
            </div>                                
    </div>    	                        
 </div>