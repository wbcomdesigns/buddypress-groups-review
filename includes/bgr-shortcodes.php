<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) { 
    exit; 
}

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
             *  @access  public
             *  @author  Wbcom Designs
             */
        
            public function __construct() {
                add_shortcode( 'add_review_form', array($this,'add_new_review') );	
            }
            
            /**
             *  Display Review form when member logged in
             *  @since   1.0.0
             *  @access  public
             *  @author  Wbcom Designs
             */
            
            public function add_new_review() {

                global $bp;
                $bgr_admin_settings = get_option( 'bgr_admin_settings' );                   
                if( !empty( $bgr_admin_settings ) ) {            
                    $review_rating_fields = $bgr_admin_settings['add_review_rating_fields'];                            
                }        
                ?>

                <div id="bgr-message" class="bgr-success">
                    <?php _e( "Review added. Your review will be published when group admin approved it.", BGR_DOMAIN); ?>
                </div>
                <?php    
                if(!is_user_logged_in()){ ?>
                    <div id="message" class="info">
                        <p><?php _e('You should log in for post review.', BGR_DOMAIN ); ?></p>
                    </div>    
                <?php } 
                elseif( !groups_is_user_admin(bp_loggedin_user_id(), bp_get_current_group_id() ) && is_user_logged_in()){ 
                    //Get All groups of user in which user is admin.                    
                    $current_user   = wp_get_current_user();
                    $member_id      = $current_user->ID; 
                    $user_groups    = BP_Groups_Member::get_is_admin_of( $member_id );
                    $exclude_group  = array();

                    if( !empty( $user_groups ) ) {
                        foreach ( $user_groups['groups'] as $user_group ) {
                            array_push( $exclude_group,$user_group->id );
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
                        <input type="hidden" id="reviews_pluginurl" value="<?php _e(BGR_PLUGIN_URL, BGR_DOMAIN );?>">                        
                        <div class="group-add-form">
                            <p>
                                <?php _e( '<b>Fill In Details To Submit Review</b>', BGR_DOMAIN );?>
                            </p>
                            <p class="bgr-form-group-id">                               
                                <select id="<?php _e('form-group-id', BGR_DOMAIN ); ?>" name="<?php _e('form-group-id', BGR_DOMAIN ); ?>" style="margin-left: 12px;" required>
                                    <option value=""><?php _e('Select Group--', BGR_DOMAIN ); ?></option>
                                    <?php
                                    if( !empty( $allgroups ) ) {
                                        foreach ( $allgroups['groups'] as $formGroup ) { ?>                                            
                                            <option value="<?php _e($formGroup->id, BGR_DOMAIN ); ?>" <?php if( bp_is_group() ) { if( bp_get_group_id()== $formGroup->id ): _e('selected="selected"', BGR_DOMAIN ); endif; } ?>><?php _e( $formGroup->name, BGR_DOMAIN ); ?></option>
                                    <?php } }  ?>                                    
                                </select>       
                            </p> 
                            <p>
                                <input name="review-subject" type="text" placeholder="Review Subject" required>
                            </p>
                            <p>
                                <textarea name="review-desc" placeholder="Review Description" rows="3" cols="50" required></textarea>
                            </p>
                            <?php   if( !empty( $review_rating_fields ) ) {
                                    $field_counter = 1;
                                        foreach( $review_rating_fields as $review_rating_field ):                                                  
                            ?>
                                <p id="review<?php echo $field_counter;?>"> 
                                    <label><?php _e($review_rating_field, BGR_DOMAIN ); ?></label>
                                    <input type="hidden" id="<?php echo 'clicked'.$field_counter; ?>" value="not_clicked">
                                    <input type="hidden" name="rated_stars[]" class="rated_stars" id="<?php echo 'rated_stars'.$field_counter; ?>" value="0">
                                    <?php for( $i = 1; $i <= 5; $i++ ) {    ?>
                                            <?php $star_img = BGR_PLUGIN_URL.'assets/images/star_off.png';?>
                                            <img class="stars <?php echo $i;?>" alt="star" id="<?php echo $field_counter.$i;?>" src="<?php echo $star_img;?>" data-attr="<?php echo $i;?>">
                                    <?php } ?>
                                </p> 
                            <?php $field_counter++;  endforeach; ?>
                             <input type="hidden" id="rating_field_counter" value="<?php echo --$field_counter; ?>">                                        
                            <?php } ?>
                            <p>
                                <input type="hidden" name="bgr-flag" value="1">   
                            </p>
                            <p>
                                <?php wp_nonce_field( 'save-group-review', 'security-nonce'); ?>
                                <button class="btn btn-default bgr-submit-review" name="bgr-submit-review">
                                    <?php _e( 'Submit Review', BGR_DOMAIN ); ?>
                                </button>
                            </p>
                        </div>
                    </form>
                <?php } else { ?>
                <div id="message" class="error">
                   <p><?php _e("You can't post review on your own group.", BGR_DOMAIN ); ?> </p>
                </div>    
               <?php  } 			
            }

    }
    new BGR_Shortcodes();
}