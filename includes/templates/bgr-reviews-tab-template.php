<?php
// Exit if accessed directly
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) { 
    exit; 
}   

global $bp,$post;
$current_user       = wp_get_current_user();
$member_id          = $current_user->ID; 

//Admin Settings
$bgr_admin_settings = get_option( 'bgr_admin_settings' );
$allow_popup = 'no';
if( !empty( $bgr_admin_settings ) ) {
	$allow_popup          = $bgr_admin_settings['add_review_allow_popup'];
        $reviews_per_page     = $bgr_admin_settings['reviews_per_page'];
        $review_rating_fields = $bgr_admin_settings['add_review_rating_fields']; 	
}

//Gather all the group reviews
if(empty($reviews_per_page)){
	$reviews_per_page = -1;
}
$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
$args = array(
	'post_type'     => 'review',	
	'post_status'   => 'publish',
	'category'      => 'group',
        'posts_per_page'=> $reviews_per_page,
        'paged'         => $paged,
	'meta_query'    => array(
		array(
			'key'		=>	'linked_group',
			'value'		=>	bp_get_group_id(),
			'compare'	=>	'=',
		),
	),
);
$reviews = new WP_Query($args);
?> 
<div class="bgr-group-reviews-block">
	<div class="reviews-header">
		<p>
			<?php _e('Reviews ', BGR_DOMAIN ); ?>			
				<?php if( $allow_popup == 'yes' ) {?>
					<span class="bgr-add-review">
						<a href="javascript:void(0)" id="add-review"><?php _e('+Add ', BGR_DOMAIN ); ?></a>
					</span>
				<?php } else {?>
					<span class="bgr-add-review">
						<a href="javascript:void(0)" id="add-review-no-popup"><?php _e('+Add ', BGR_DOMAIN ); ?></a>
					</span>
				<?php }?>			
		</p>
	</div>
          
	<?php if( $allow_popup == 'no' ) { ?>
		<!-- ADD REVIEW IF NO POPUP -->
		<div class="bgr-group-review-no-popup-add-block">
                    <?php do_shortcode('[add_review_form]'); ?>
		</div>
	<?php }?>

	<!-- MODAL FOR USER LOGIN -->
	
	<div id="bgr-add-review-modal" class="modal">
		<!-- Modal content -->
		<div class="modal-content">
			<div class="modal-header">
				<span class="close close-modal">&times;</span>
				<h2><?php _e('Add Review ', BGR_DOMAIN ); ?></h2>
			</div>
			<div class="modal-body">
				<div class="bgr-group-review-add-block">
                                    <?php do_shortcode('[add_review_form]'); ?>	
				</div>
			</div>
		</div>
	</div> 
        <div class="bgr-bp-success">
            <div id="message" class="success">
                <p><?php _e( "Review added. Your review will be published when group admin approved it.", BGR_DOMAIN ); ?></p>
            </div>                       	
        </div>    
	<div class="group-reviews">
		<div id="group-reviews-list">
                    <div id="request-review-list" class="item-list">
		    <?php if ( $reviews->have_posts() ){
                           while ( $reviews->have_posts() ): $reviews->the_post(); ?>
                            <div class="bgr-row item-list group-request-list">
                                <div class="bgr-col-2"> 
                                    <?php $author = $reviews->post->post_author;                                          
                                      bp_displayed_user_avatar( array( 'item_id' => $author));
                                     ?>
                                </div>
                                <div class="bgr-col-8">
                                    <div class="reviewer">
                                        <?php _e( '<b>Reviewer : '.bp_core_get_userlink($author).'</b> ', BGR_DOMAIN );                                         
                                        ?>
                                    </div>  
                                   
                                    <div class="item-description">
                                        <div class="review-subject">
                                            <b> <?php _e('Review Subject: ', BGR_DOMAIN ); ?> </b>
                                            <?php the_title(); ?>
                                        </div>
                                        <div class="review-description">
                                            <b> <?php _e('Review Description: ', BGR_DOMAIN ); ?> </b>
                                                <?php $trimexcerpt  = get_the_excerpt();	
                                                      $shortexcerpt = wp_trim_words( $trimexcerpt, $num_words = 10, $more = '… ' ); 
                                                      _e($shortexcerpt, BGR_DOMAIN );                                                      
                                                ?>
                                                <?php $url = bp_get_group_permalink().'reviews/view/'.get_the_id(); ?>
                                            <a href="<?php echo $url; ?>"><?php _e('Read More>> ', BGR_DOMAIN ); ?></a>
                                            <div class="review-ratings">
                                                
                                                    <?php
                                                        $bgr_admin_settings   = get_option( 'bgr_admin_settings' );
                                                        $review_rating_fields = $bgr_admin_settings['add_review_rating_fields'];
                                                        $review_ratings       = get_post_meta( $post->ID, 'review_star_rating',false);
                                                        if( !empty( $review_rating_fields ) && !empty( $review_ratings[0] ) ):                                                  
                                                            foreach( $review_ratings[0] as $field => $value ) {
                                                                if( in_array( $field,$review_rating_fields ) ) {    ?>                                                               
                                                                   <div class="bgr-col-6"> 
                                                                    <?php        
                                                                    _e($field, BGR_DOMAIN );                                                                    
                                                                    /*** Ratings *****/
                                                                    $stars_on  = $value;
                                                                    $stars_off = 5 - $stars_on; 
                                                                    
                                                                    for( $i = 1; $i <= $stars_on; $i++ ){
                                                                        ?><img class="stars" src="<?php _e( BGR_PLUGIN_URL."assets/images/star.png", BGR_DOMAIN );?>" alt="<?php _e('star', BGR_DOMAIN ); ?>"><?php
                                                                    }
                                                                    for( $i = 1; $i <= $stars_off; $i++ ){
                                                                        ?><img class="stars" src="<?php _e( BGR_PLUGIN_URL."assets/images/star_off.png", BGR_DOMAIN );?>" alt="<?php _e('star', BGR_DOMAIN ); ?>"><?php
                                                                    } ?>
                                                                    </div>
                                                                <?php    
                                                                }
                                                            }
                                                        endif; 
                                                    ?>                    
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="bgr-col-2">
                                    <?php if(groups_is_user_admin( $member_id, bp_get_group_id() )): ?> 
                                        <div class='remove-review generic-button'>
                                            <a class='remove-review-button'> <?php _e('Delete',BGR_DOMAIN); ?> </a><input type="hidden" name="remove_review_id" value="<?php echo $post->ID; ?>"> 
                                        </div>
                                    <?php endif; ?>                                  
                                </div>
                                
                                <div class="clear"></div>	                        
                            </div>                      
                    
                    <?php endwhile; 
                        $total_pages = $reviews->max_num_pages;
                        if ($total_pages > 1) { ?>
                            <div class="review-pagination">
                                <?php
                                    $current_page = max(1, get_query_var('paged'));
                                    echo paginate_links(array(
                                            'base' => get_pagenum_link(1) . '%_%',
                                            'format' => 'page/%#%',
                                            'current' => $current_page,
                                            'total' => $total_pages,
                                            'prev_text' => __('prev'),
                                            'next_text' => __('next'),
                                            ));?>
					
                            </div><?php
                            }
                            wp_reset_postdata();
                    
                    } else{                                            
                     ?>    
                    <div id="message" class="info">
                        <p><?php _e( "Sorry, no reviews were found.", BGR_DOMAIN ); ?></p>
                    </div>
                    <?php } ?>
                    </div>        
		</div>
	</div>
</div>