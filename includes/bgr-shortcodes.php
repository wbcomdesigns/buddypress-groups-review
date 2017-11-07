<?php
// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Class to serve shortcodes
 *  @since   1.0.0
 *  @author   Wbcom Designs
 */
if( !class_exists( 'BGR_Shortcodes' ) ) {
    class BGR_Shortcodes{

            /**
             * Constructor for shortcodes
             *  @since   1.0.0
             *  @author  Wbcom Designs
             */

            function __construct() {
                add_shortcode( 'add_group_review_form', array( $this, 'add_new_review') );
            }

            /**
             *  Display Review form when member logged in
             *  @since   1.0.0
             *  @author  Wbcom Designs
             */

            function add_new_review() {
                global $bp;
                global $bgr;
				$review_rating_fields 			= $bgr['review_rating_fields'];
				$active_rating_fields 			= $bgr['active_rating_fields'];
				$review_label		  	  		= $bgr['review_label'];
				$admin_exclude_groups	  		= $bgr['exclude_groups'];
				$auto_approve_reviews   		= $bgr['auto_approve_reviews'];
				$multi_reviews   				= $bgr['multi_reviews'];
				$current_user   				= wp_get_current_user();
                $member_id          			= $current_user->ID;

				$current_group_id = 0;
				if( !empty( bp_get_current_group_id() ) ) {
					$current_group_id = bp_get_current_group_id();
				}
				$group_args 	= array(
					'post_type' 		=> 'review',
					'category' 			=> 'group',
					'post_status' 		=> array(
									        'draft',
									        'publish'
									    ),
					'author' 			=> $member_id,
					'meta_query' 		=> array(
							array(
								'key'	  =>	'linked_group',
								'value'	  =>	$current_group_id,
								'compare' =>	'=',
							),
						),
				);
				$reviews_args = new WP_Query($group_args);
                ?>
                <div id="bgr-message" class="bgr-success">
                </div>
                <?php
                if(!is_user_logged_in()) { ?>
                    <div id="message" class="info">
                        <p><?php _e("You should log in for post $review_label.", BGR_DOMAIN ); ?></p>
                    </div>
                <?php }
                elseif( !groups_is_user_admin( bp_loggedin_user_id(), bp_get_current_group_id()) && is_user_logged_in() ) {
                    //Get All groups of user in which user is admin.
                    if( bp_is_group_single() ) {
                    	if( $multi_reviews == 'no' ) {
	                    	$user_post_count = $reviews_args->post_count;
	                    } else {
	                    	$user_post_count = 0;
	                    }

	                    if( $user_post_count == 0 ) {
	                    	$this->bgr_review_form( $review_rating_fields, $active_rating_fields, $review_label, $admin_exclude_groups, $auto_approve_reviews, $multi_reviews, $current_group_id, $member_id );
	                    } else { ?>
	                    	<div id="message" class="error">
			                   <p><?php _e("You already posted a review for this group.", BGR_DOMAIN ); ?> </p>
			                </div>
	                    <?php }

                    } else {
                    	$this->bgr_review_form( $review_rating_fields, $active_rating_fields, $review_label, $admin_exclude_groups, $auto_approve_reviews, $multi_reviews, $current_group_id, $member_id );
                    }
                 }  else { ?>
	                <div id="message" class="error">
	                   <p><?php _e("You can't post $review_label on your own group.", BGR_DOMAIN ); ?> </p>
	                </div>
               <?php  }
            }

            function bgr_review_form( $review_rating_fields, $active_rating_fields, $review_label, $admin_exclude_groups, $auto_approve_reviews, $multi_reviews, $current_group_id, $member_id ) {
                global $bp;
                $user_groups    = BP_Groups_Member::get_is_admin_of( $member_id );
                $exclude_group = array();

                if( !empty( $user_groups ) ) {
                    foreach ( $user_groups['groups'] as $user_group ) {
                        array_push( $exclude_group, $user_group->id );
                    }
                }

				if( !empty( $admin_exclude_groups ) ) {
					foreach ( $admin_exclude_groups as $admin_exclude_group ){
						array_push( $exclude_group, $admin_exclude_group );
					}
				}
                //Get All groups
                $group_args = array(
					'order' => 'DESC',
					'orderby' => 'date_created',
					'exclude' => $exclude_group,
				);
                $allgroups  = groups_get_groups( $group_args );
            	?>
                <form id="bgr-add-review-form" method="POST">
                    <input type="hidden" id="reviews_pluginurl"  name="reviews_pluginurl" value="<?php _e( BGR_PLUGIN_URL, BGR_DOMAIN );?>">
					<div class="group-add-form">
                        <p>
                            <b><?php _e( "Write a ", BGR_DOMAIN ); echo $review_label; ?></b>
                        </p>

                        <?php if( $current_group_id === 0 ) {?>
                        <p class="bgr-form-group-id">
                            <select id="form-group-id" name="form-group-id">
                                <option value=""><?php _e('--Select Group--', BGR_DOMAIN ); ?></option>
                                <?php
                                if( !empty( $allgroups ) ) {
                                    foreach ( $allgroups['groups'] as $formGroup ){ ?>
                                        <option value="<?php echo $formGroup->id; ?>" <?php if( bp_is_group() ) { if( bp_get_group_id() == $formGroup->id ): echo 'selected="selected"'; endif; } ?>><?php _e( $formGroup->name, BGR_DOMAIN ); ?></option>
                                <?php } }  ?>
                            </select>
                            <br/>
							<span class="bgr-error-fields">*<?php _e('This field is required.', BGR_DOMAIN ); ?></span>
                        </p>
                        <?php } else {?>
                        	<input type="hidden" name="form-group-id" value="<?php echo $current_group_id;?>" />
                       	<?php }?>
                        <p>
                        	<?php $review_subject = 'Review '.time();?>
                            <input name="review-subject" type="hidden" value="<?php echo $review_subject;?>">
                        </p>
                        <p>
                            <textarea name="review-desc" placeholder="<?php _e( $review_label." Description", BGR_DOMAIN ); ?>" rows="3" cols="50"></textarea>
                            <br/>
							<span class="bgr-error-fields">*<?php _e('This field is required.', BGR_DOMAIN ); ?></span>
                        </p>
                        <p>
                            <input type="hidden" name="bgr-flag" value="1">
                        </p>
						<?php $this->bgr_display_form_rating();?>
                        <p>
                            <?php wp_nonce_field( 'save-group-review', 'security-nonce');?>
                            <button class="btn btn-default bgr-submit-review" name="bgr-submit-review"><?php _e( "Submit $review_label", BGR_DOMAIN );?></button>
                        </p>
                    </div>
                </form> <?php
            }

			function bgr_display_form_rating() {
				global $bgr;
				$this->bgr_display_form_star_rating();
			}

			/**
			*  Display Ratings when rating type = star
			*  @since   1.0.0
			*  @author  Wbcom Designs
			*/

			function bgr_display_form_star_rating() {
				global $bgr;
				$review_rating_fields 			= $bgr['review_rating_fields'];
				$active_rating_fields 			= $bgr['active_rating_fields'];
				if ( !empty( $review_rating_fields ) ) {
					$field_counter = 1;
						foreach( $review_rating_fields as $review_rating_field ):
							if( in_array( $review_rating_field, $active_rating_fields ) ) { ?>
								<div class="bgr-row">
									<div class="bgr-col-6"><label><?php _e( $review_rating_field, BGR_DOMAIN ); ?></label></div>
									<div id="review<?php echo $field_counter;?>" class="bgr-col-6">
											<input type="hidden" id="<?php echo 'clicked'.$field_counter; ?>" value="not_clicked">
											<input type="hidden" name="rated_stars[]" class="rated_stars bgr_mrating" id="<?php echo 'rated_stars'.$field_counter; ?>" value="0">
											<?php for( $i = 1; $i <= 5; $i++ ) {    ?>
													<?php $star_img = BGR_PLUGIN_URL.'assets/images/star_off.png';?>
													<img class="stars <?php echo $i; ?>" alt="<?php _e( 'star', BGR_DOMAIN ); ?>" id="<?php echo $field_counter.$i; ?>" src="<?php echo $star_img; ?>" data-attr="<?php echo $i; ?>">
											<?php } ?>
									</div>
									<div class="bgr-col-12 bgr-error-fields">*<?php _e('This field is required.', BGR_DOMAIN ); ?></div>
								</div>
							<?php $field_counter++; }
						endforeach; ?>
					<input type="hidden" id="rating_field_counter" value="<?php echo --$field_counter; ?>">
				<?php   }
			}
    }
    new BGR_Shortcodes();
}