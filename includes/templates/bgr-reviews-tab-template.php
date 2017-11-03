<?php
// Exit if accessed directly
defined( 'ABSPATH' ) || exit;
global $bp,$post;
global $bgr;
$current_user       	= wp_get_current_user();
$member_id          	= $current_user->ID;

$allow_popup          	= $bgr['add_review_allow_popup'];
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
	<div class="reviews-header">
		<p>
			<?php
			if( !groups_is_user_admin( bp_loggedin_user_id(), bp_get_current_group_id()) || !is_user_logged_in() ) {
				_e( $review_label, BGR_DOMAIN ); ?>
				<?php if( $allow_popup == 'yes' ) { ?>
					<span class="bgr-add-review">
						<a href="javascript:void(0)" id="add-review"><?php _e('Add ', BGR_DOMAIN ); echo $review_label; ?></a>
					</span>
				<?php } else { ?>
					<span class="bgr-add-review">
						<a href="javascript:void(0)" id="add-review-no-popup"><?php _e('Add ', BGR_DOMAIN ); echo $review_label; ?></a>
					</span>
				<?php }
			} ?>
		</p>
	</div>

	<?php if( $allow_popup == 'no' ) { ?>
		<!-- ADD REVIEW IF NO POPUP -->
		<div class="bgr-group-review-no-popup-add-block">
                    <?php do_shortcode( '[add_group_review_form]' ); ?>
		</div>
	<?php } ?>

	<!-- MODAL FOR USER LOGIN -->

	<div id="bgr-add-review-modal" class="modal">
		<!-- Modal content -->
		<div class="modal-content">
			<div class="modal-header">
				<span class="close close-modal">&times;</span>
				<h2><?php _e('Add ', BGR_DOMAIN ); echo $review_label; ?></h2>
			</div>
			<div class="modal-body">
				<div class="bgr-group-review-add-block">
					<?php do_shortcode( '[add_group_review_form]' ); ?>
				</div>
			</div>
		</div>
	</div>
	<div class="bgr-bp-success">
		<div id="message" class="success bgr-bp-success">
			<p><?php _e( "Your Response added. This will be published when group admin approved it.", BGR_DOMAIN); ?></p>
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
	                                                    <a href="<?php echo $url; ?>"><i><b><?php _e('read more...', BGR_DOMAIN ); ?></b></i></a>
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
                                            <a class='remove-review-button'> <?php _e('Delete', BGR_DOMAIN ); ?> </a><input type="hidden" name="remove_review_id" value="<?php _e( $post->ID, BGR_DOMAIN ); ?>">
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
                                            'prev_text' => __('prev'),
                                            'next_text' => __('next'),
                                            ));?>

                            </div><?php
                            }
                            wp_reset_postdata();

                    } else {
                     ?>
                    <div id="message" class="info">
                        <p><?php _e( "Sorry, No $review_label were found.", BGR_DOMAIN ); ?></p>
                    </div>
                    <?php } ?>
			</div>
		</div>
	</div>
</div>
