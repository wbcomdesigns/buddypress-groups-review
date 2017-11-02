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
				$rating_color					= $bgr['rating_color'];
				$auto_approve_reviews   		= $bgr['auto_approve_reviews'];

				$current_group_id = 0;
				if( !empty( bp_get_current_group_id() ) ) {
					$current_group_id = bp_get_current_group_id();
				}
                ?>
                <div id="bgr-message" class="bgr-success">
                    <?php
                    	if( $auto_approve_reviews == 'yes' ) {
                    		_e( " Thank you for taking the time to write this wonderful $review_label!", BGR_DOMAIN );
                    	} else {
                    		_e( " Thank you for taking the time to write this wonderful $review_label. Your $review_label will display after moderator approval.", BGR_DOMAIN );
                    	}
                    ?>
                </div>
                <?php
                if(!is_user_logged_in()) { ?>
                    <div id="message" class="info">
                        <p><?php _e("You should log in for post $review_label.", BGR_DOMAIN ); ?></p>
                    </div>
                <?php }
                elseif( !groups_is_user_admin( bp_loggedin_user_id(), bp_get_current_group_id()) && is_user_logged_in() ) {
                    //Get All groups of user in which user is admin.
                    $current_user   	= wp_get_current_user();
                    $member_id      = $current_user->ID;
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
                        <input type="hidden" id="bgr-rate-color" name="bgr-rate-color" value="<?php _e( $rating_color, BGR_DOMAIN ); ?>">
						<div class="group-add-form">
                            <p>
                                <?php _e( "<b>Write a $review_label</b>", BGR_DOMAIN ); ?>
                            </p>

                            <?php if( $current_group_id === 0 ) {?>
                            <p class="bgr-form-group-id">
                                <select id="form-group-id" name="form-group-id">
                                    <option value=""><?php _e('--Select Group--', BGR_DOMAIN ); ?></option>
                                    <?php
                                    if( !empty( $allgroups ) ) {
                                        foreach ( $allgroups['groups'] as $formGroup ){ ?>
                                            <option value="<?php _e( $formGroup->id, BGR_DOMAIN ); ?>" <?php if( bp_is_group() ) { if( bp_get_group_id() == $formGroup->id ): _e('selected="selected"', BGR_DOMAIN ); endif; } ?>><?php _e( $formGroup->name, BGR_DOMAIN ); ?></option>
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
                    </form>
                <?php } else { ?>
	                <div id="message" class="error">
	                   <p><?php _e("You can't post $review_label on your own group.", BGR_DOMAIN ); ?> </p>
	                </div>
               <?php  }
            }

			function bgr_display_form_rating() {
				global $bgr;
				$rating_type = $bgr['rating_type'];

				switch( $rating_type ) {

					case 'star'			: 	$this->bgr_display_form_star_rating();
												break;
					case 'bar'			: 	$this->bgr_display_form_bar_rating();
												break;
					case 'numbers'		: 	$this->bgr_display_form_numbers_rating();
												break;
				}
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
													<img class="stars <?php echo $i; ?>" alt="<?php _e( 'star', BGR_DOMAIN ); ?>" id="<?php _e( $field_counter.$i, BGR_DOMAIN ); ?>" src="<?php _e( $star_img, BGR_DOMAIN ); ?>" data-attr="<?php _e( $i, BGR_DOMAIN ); ?>">
											<?php } ?>
									</div>
									<div class="bgr-col-12 bgr-error-fields">*<?php _e('This field is required.', BGR_DOMAIN ); ?></div>
								</div>
							<?php $field_counter++; }
						endforeach; ?>
					<input type="hidden" id="rating_field_counter" value="<?php echo --$field_counter; ?>">
				<?php   }
			}

			/**
			*  Display Ratings when rating type = bar
			*  @since   1.0.0
			*  @author  Wbcom Designs
			*/

			function bgr_display_form_bar_rating() {
				global $bgr;
				$review_rating_fields 			= $bgr['review_rating_fields'];
				$active_rating_fields 			= $bgr['active_rating_fields'];
				if ( !empty( $review_rating_fields ) ) {
					$field_counter = 1;
						foreach( $review_rating_fields as $review_rating_field ):
							if( in_array( $review_rating_field, $active_rating_fields ) ) { ?>
								<div class="bgr-row">
									<div class="bgr-col-6"><label><?php _e( $review_rating_field, BGR_DOMAIN ); ?></label></div>
									<div id="bgrbar<?php echo $field_counter;?>" class="bgr-col-6" >
										<input type="hidden" id="<?php echo 'clicked'.$field_counter; ?>" value="not_clicked">
										<input type="hidden" name="rated_stars[]" class="rated_bars bgr_mrating" id="<?php echo 'rated_bars'.$field_counter; ?>" value="0">
										<?php for( $i = 1; $i <= 5; $i++ ) {    ?>
												<a class="bgr-bars <?php echo $i;?>" alt="<?php _e( 'bar', BGR_DOMAIN ); ?>" id="<?php _e( $field_counter.$i, BGR_DOMAIN ); ?>" data-attr="<?php _e( $i, BGR_DOMAIN ); ?>"></a>
										<?php } ?>
									</div>
									<div class="bgr-col-12 bgr-error-fields">*<?php _e('This field is required.', BGR_DOMAIN ); ?></div>
								</div>
							<?php $field_counter++; }
						endforeach; ?>
					<input type="hidden" id="rating_field_counter" value="<?php echo --$field_counter; ?>">
				<?php   }
			}

			/**
			*  Display Ratings when rating type = numbers
			*  @since   1.0.0
			*  @author  Wbcom Designs
			*/

			function bgr_display_form_numbers_rating() {
				global $bgr;
				$review_rating_fields 			= $bgr['review_rating_fields'];
				$active_rating_fields 			= $bgr['active_rating_fields'];
				if ( !empty( $review_rating_fields ) ) {
					$field_counter = 1;
						foreach( $review_rating_fields as $review_rating_field ):
							if( in_array( $review_rating_field, $active_rating_fields ) ) { ?>
								<div class="bgr-row">
									<div class="bgr-col-6"><label><?php _e( $review_rating_field, BGR_DOMAIN ); ?></label></div>
									<div id="bgrnumbers<?php echo $field_counter;?>" class="bgr-col-6" >
										<input type="hidden" id="<?php echo 'clicked'.$field_counter; ?>" value="not_clicked">
										<input type="hidden" name="rated_stars[]" class="rated_bars bgr_mrating" id="<?php _e( 'rated_bars'.$field_counter, BGR_DOMAIN ); ?>" value="0">
										<?php for( $i = 1; $i <= 5; $i++ ) {  ?>
												<a class="bgr-numbers <?php echo $i;?>" alt="<?php _e( 'bar', BGR_DOMAIN ); ?>" id="<?php _e( $field_counter.$i, BGR_DOMAIN ); ?>" data-attr="<?php _e( $i, BGR_DOMAIN ); ?>"><?php _e( $i, BGR_DOMAIN ); ?></a>
										<?php } ?>
									</div>
									<div class="bgr-col-12 bgr-error-fields">*<?php _e('This field is required.', BGR_DOMAIN ); ?></div>
								</div>
							<?php $field_counter++; }
						endforeach; ?>
					<input type="hidden" id="rating_field_counter" value="<?php echo --$field_counter; ?>">
				<?php  }
			}

    }
    new BGR_Shortcodes();
}