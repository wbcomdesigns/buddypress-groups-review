<?php
// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

global $bgr;
$review_rating_fields 	= $bgr['review_rating_fields'];
$url 					= $_SERVER['REQUEST_URI'];
$review_id				= basename($url); 
$review 				= get_post( $review_id );

$review_title   		= $review->post_title;
$author         		= $review->post_author;
$author_details 		= get_userdata( $author );
?>
<div class="bgr-single-review">
    <div class="bgr-row item-list group-request-list">
            <div class="bgr-col-2">
                <?php bp_displayed_user_avatar( array( 'item_id' => $author ) ); ?>
            </div>
            <div class="bgr-col-10">
                <div class="reviewer">
                    <?php _e( '<b> '.bp_core_get_userlink($author).'</b> ', BGR_DOMAIN ); ?>
                </div>  

                <div class="item-description">
                    <div class="review-subject">
                        <b> <?php _e('Subject: ', BGR_DOMAIN ); ?> </b>
                        <?php _e( $review_title, BGR_DOMAIN ); ?>
                    </div>
                    <div class="review-description">                                                                       
                        <div class="review-excerpt bgr-col-12">
                        <b> <?php _e('<b>Short Description:</b> ', BGR_DOMAIN ); ?> </b>
                        <?php $content      = $review->post_content;                                                     
                              $shortContent = preg_split( '/(\.|!|\?)\s/', $content, 2, PREG_SPLIT_DELIM_CAPTURE );    
                              _e( $shortContent[0].".", BGR_DOMAIN );
                         ?>
                        </div> 
                        <div class="review-full-description bgr-col-12">
                            <div class="bgr-col-12"> 
                            <?php _e('<b>Full Description:</b> ', BGR_DOMAIN );
                            _e( $review->post_content, BGR_DOMAIN ); ?>
                            </div>
							<?php	                          
                            $review_ratings       = get_post_meta( $review_id, 'review_star_rating', false );
							do_action( 'bgr_display_ratings', $review_id );                                                                           
                            ?>
                        </div>
                        <a class="expand-review-des"><?php _e('View More..', BGR_DOMAIN ); ?> </a>
                    </div>
                </div>
            </div>                                
    </div>    	                        
 </div>