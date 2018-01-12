<?php
// Exit if accessed directly
defined( 'ABSPATH' ) || exit;
global $bp,$post;
global $bgr;
$current_user       	= wp_get_current_user();
$member_id          	= $current_user->ID;
$reviews_per_page     	= $bgr['reviews_per_page'];
$review_rating_fields 	= $bgr['review_rating_fields'];
$review_label 		  	= $bgr['review_label'];
$paged 					= ( get_query_var('paged') ) ? get_query_var('paged') : 1;
$args				 	= 	array(
								'post_type' 		=> 'review',
								'post_status' 		=> 'publish',
								'category' 			=> 'group',
								'posts_per_page' 	=> $reviews_per_page,
								'paged'				=> $paged,
								'meta_query' 		=> array(
															array(
																'key'		=>	'linked_group',
																'value'		=>	bp_get_group_id(),
																'compare'	=>	'=',
															),
														),
							);
$reviews 				= new WP_Query($args);
?>
<div class="bgr-group-reviews-block">
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
                                    	<b><?php echo bp_core_get_userlink( $author ); ?></b>
                                    </div>

                                    <div class="item-description">
                                        <div class="review-description">
                                            <?php
                                            	$trimcontent  = get_the_content();
                                            	$url = bp_get_group_permalink().'reviews/view/'.get_the_id();
	                                            if( !empty( $trimcontent ) ) {
	                                                $len = strlen( $trimcontent );
	                                                if( $len > 150 ) {
	                                                    $shortexcerpt = substr( $trimcontent, 0, 150 );
	                                                    echo $shortexcerpt;    ?>
	                                                    <a href="<?php echo $url; ?>"><i><b><?php _e('read more...', 'bp-group-reviews' ); ?></b></i></a>
	                                                <?php } else {
	                                                    echo $trimcontent;
	                                                }
	                                            }
                                		 	?>
                                            <div class="review-ratings">
                                                <?php do_action( 'bgr_display_ratings', $post->ID ); ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="bgr-col-2">
                                    <?php if( groups_is_user_admin( $member_id, bp_get_group_id() ) ): ?>
                                        <div class='remove-review generic-button'>
                                            <a class='remove-review-button'> <?php _e('Delete', 'bp-group-reviews' ); ?> </a><input type="hidden" name="remove_review_id" value="<?php _e( $post->ID, 'bp-group-reviews' ); ?>">
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="clear"></div>
                            </div>

                    <?php endwhile;
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
                                            'prev_text' => __('prev','bp-group-reviews'),
                                            'next_text' => __('next','bp-group-reviews'),
                                            ));?>

                            </div><?php
                            }
                            wp_reset_postdata();

                    } else {
                     ?>
                    <div id="message" class="info">
                        <p><?php _e( "Sorry, No $review_label were found.", 'bp-group-reviews' ); ?></p>
                    </div>
                    <?php } ?>
			</div>
		</div>
	</div>
</div>
