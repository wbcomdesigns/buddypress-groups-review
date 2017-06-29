<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if( isset( $_GET ) ) {
    if( isset( $_GET['tab'] ) ) {
        $bgr_setting_tab = sanitize_text_field( $_GET['tab'] );
        bgr_include_admin_setting_tabs( $bgr_setting_tab ); 
    }else{
		bgr_include_admin_setting_tabs('general');
    }
}

/** Actions performed on Display review admin settings tabs
 * @since    1.0.0                
 * @author   Wbcom Designs              
 */
 
function bgr_include_admin_setting_tabs( $bgr_setting_tab = 'general') {
    switch( $bgr_setting_tab ){
        case 'criteria':
            bgr_criteria_setting();
            break;
        case 'shortcode':
            bgr_shortcode_setting();
            break;
        case 'display':
            bgr_display_setting();
            break;
        case 'support':
            bgr_support_setting();
            break;
        default:
            bgr_general_setting();
    }
}

/** Actions performed on Group Reviews Settings : Criteria Tab Content
 * @since    1.0.0                
 * @author   Wbcom Designs              
 */
 
function bgr_criteria_setting() {
	global $bgr;
        $spinner_src = includes_url().'/images/spinner.gif';
		$review_rating_fields = $bgr['review_rating_fields'];
		$active_rating_fields = $bgr['active_rating_fields'];
    
?>    
    <div class="bgr-col-12"><h2><?php _e('Criteria Settings', BGR_DOMAIN ); ?></h2></div>
    <div class="bgr-row">
        <div class="bgr-col-3">
            <label><?php _e('Criteria(s)', BGR_DOMAIN );?></label>
            </span>
        </div>
        <div class="bgr-col-9">
            <div id="bgr-textbox-container">
                <?php if( !empty( $review_rating_fields ) ) { 
							foreach( $review_rating_fields as $review_rating_field ): ?>
								<div class="rating-review-div"><span>&equiv;</span><input name = "BGRDynamicTextBox" class="draggable" type="text" value = "<?php _e( $review_rating_field , BGR_DOMAIN ); ?>" />
									<input type="button" value="<?php _e( 'Remove', BGR_DOMAIN ); ?>" class="remove button button-secondary" />
									<label class="bgr-switch">
										<input type="checkbox" class="bgr-criteria-state" name="bgr-criteria-state" data-attr="<?php _e( $review_rating_field , BGR_DOMAIN ); ?>" <?php if( in_array( $review_rating_field, $active_rating_fields ) ) { _e( 'checked="checked"', BGR_DOMAIN ); } ?>>
										<div class="bgr-slider round"></div>
									</label>
								</div>    
					<?php 	endforeach; 
						} ?>                 
            </div>			
            <input id="bgr-field-add" type="button" value="<?php _e( 'Add Criteria', BGR_DOMAIN );?>" class="button button-secondary"/>
			<p class="bgr-admin-des"><?php _e( "This option provide you to add multple rating criteria. By default, no criteria will be shown until you active it.", BGR_DOMAIN); ?></p>
        </div>
    </div>
    <div class="bgr-col-12">
        <div class="bgr-col-6">
            <input type="button" class="button button-primary" id="bgr-save-admin-criteria-settings" value="<?php _e( 'Save Settings', BGR_DOMAIN );?>">
            <img src="<?php _e( $spinner_src, BGR_DOMAIN );?>" class="bgr-admin-criteria-settings-spinner" />
        </div>
    </div> 
<?php }

 /* Actions performed on Group Reviews Settings : Shortcode Tab Content
 * @since    1.0.0                
 * @author   Wbcom Designs              
 */ 
 
function bgr_shortcode_setting() { ?>
    <div class="bgr-col-12"><h2><?php _e( 'Shortcodes', BGR_DOMAIN );?></h2></div>
    <div class="bgr-row"> 
        <div class="bgr-col-3"><label><?php _e( '[add_group_review_form]', BGR_DOMAIN );?></label></div>
        <div class="bgr-col-9"><?php _e( 'This shortcode will be display Group Review Form.', BGR_DOMAIN );?></div>
    </div>
<?php }

/* Actions performed on Group Reviews Settings : Display Tab Content
 * @since    1.0.0                
 * @author   Wbcom Designs              
 */ 
 
function bgr_display_setting() {
        global $bgr;
        $spinner_src = includes_url().'/images/spinner.gif';
        $bgrReviewLabel           = $bgr['review_label'];
        $bgrManageReviewLabel     = $bgr['manage_review_label'];
        $bgr_rating_color         = $bgr['rating_color'];
        $bgr_rating_type          = $bgr['rating_type'];           
    ?>
    <div class="bgr-col-12"><h2><?php _e( 'Display Settings', BGR_DOMAIN );?></h2></div>
    <div class="bgr-col-12"><h3><?php _e( 'Labels', BGR_DOMAIN );?></h3></div>
    <div class="bgr-row">                    
        <div class="bgr-col-3"><label for="bgrReviewLabel"><?php _e( 'Review', BGR_DOMAIN );?></label></div>
        <div class="bgr-col-9">
			<input name = "bgrReviewLabel" id="bgrReviewLabel" type="text" value = "<?php _e( $bgrReviewLabel , BGR_DOMAIN ); ?>" />
			<p class="bgr-admin-des"><?php _e( 'This option provide flexibility to change review label. By default it shows "Review"' , BGR_DOMAIN); ?></p>
		</div>
    </div>   
    <div class="bgr-row">                    
        <div class="bgr-col-3"><label for="bgrManageReviewLabel"><?php _e( 'Review (In Manage Section)', BGR_DOMAIN );?></label></div>
        <div class="bgr-col-9">
			<input name = "bgrManageReviewLabel" id="bgrManageReviewLabel" type="text" value = "<?php _e( $bgrManageReviewLabel , BGR_DOMAIN ); ?>" />
			<p class="bgr-admin-des"><?php _e( 'This option provide flexibility to change review label in <b>Buddypress Group Manage </b> Section. By default it shows "Reviews"' , BGR_DOMAIN); ?></p>
		</div>
    </div> 
    <div class="bgr-col-12"><h3><?php _e( 'Colors', BGR_DOMAIN );?></h3></div>
    <div class="bgr-row"> 
        <div class="bgr-col-3"><label for="bgr-rating-color"><?php _e( 'Rating Color', BGR_DOMAIN );?></label></div>
        <div class="bgr-col-9">
			<input id="bgr-rating-color" class="bgr-review-color" type="text" data-default-color="#effeff" value="<?php _e( $bgr_rating_color , BGR_DOMAIN ); ?>" />
			<p class="bgr-admin-des"><?php _e( 'This option lets you to change rating type color( except star rating ).' , BGR_DOMAIN); ?></p>
		</div>
    </div>
    <div class="bgr-col-12"><h3><?php _e( 'Rating Type', BGR_DOMAIN );?></h3></div>
    <div class="bgr-row"> 
        <div class="bgr-col-3"><label for="bgr-rating-type"><?php _e( 'Types', BGR_DOMAIN );?></label></div>
        <div class="bgr-col-9">
            <select id="bgr-rating-type" name="bgr-rating-type">                
                <option value="<?php _e( 'star', BGR_DOMAIN );?>" <?php if( $bgr_rating_type == 'star' ) { _e( 'selected="selected"', BGR_DOMAIN ); } ?>><?php _e( 'Star Rating', BGR_DOMAIN );?></option> 
                <option value="<?php _e( 'numbers', BGR_DOMAIN );?>" <?php if( $bgr_rating_type == 'numbers' ) { _e( 'selected="selected"', BGR_DOMAIN ); } ?>><?php _e( 'Number Rating', BGR_DOMAIN );?></option>
                <option value="<?php _e( 'bar', BGR_DOMAIN );?>" <?php if( $bgr_rating_type == 'bar' ) { _e( 'selected="selected"', BGR_DOMAIN ); } ?>><?php _e( 'Bar Rating', BGR_DOMAIN );?></option>
            </select>
			<p class="bgr-admin-des"><?php _e( "This option lets you to choose display of ratings.By default <b>Star Rating</b> will be shown.", BGR_DOMAIN); ?></p>
        </div>
    </div> 
    <div class="bgr-col-12">
        <div class="bgr-col-6">
            <input type="button" class="button button-primary" id="bgr-save-admin-display-settings" value="<?php _e( 'Save Settings', BGR_DOMAIN );?>">
            <img src="<?php _e( $spinner_src, BGR_DOMAIN );?>" class="bgr-admin-display-settings-spinner" />
        </div>
    </div> 
<?php }

 /* Actions performed on Group Reviews Settings : Support Tab Content
 * @since    1.0.0                
 * @author   Wbcom Designs              
 */ 
 
function bgr_support_setting() {  ?>
    <div class="bgr-col-12"><h2><?php _e( 'FAQ(s)', BGR_DOMAIN );?></h2></div>
    <div id="bgr-settings-tbl">
        <div class="bgr-admin-row">
            <div>
               <button class="bgr-accordion">
                <?php _e( 'How can we submit a review to a group using this plugin?', BGR_DOMAIN );?>
                </button>
                <div class="panel">
                    <p> 
                        <?php _e( 'When visiting the "/groups" section in the site, go for single group view page, there you can see a menu namely, "Reviews" that will allow you if you are a site member to add group review.', BGR_DOMAIN );?>
                    </p>
                </div>
            </div>
        </div>

        <div class="bgr-admin-row">
            <div>
               <button class="bgr-accordion">
                <?php _e( 'How can I use the review form on any other page?', BGR_DOMAIN );?>
                </button>
                <div class="panel">
                    <p>
                        <?php _e( 'Yes you can use the review form on other page, just copy shortcode from review setting page and paste it on the other page.', BGR_DOMAIN );
                        ?>
                    </p>
                </div>
            </div>
        </div>
    </div>
<?php     
}

 /* Actions performed on Group Reviews Settings : General Tab Content
 * @since    1.0.0                
 * @author   Wbcom Designs              
 */ 
 
function bgr_general_setting() { 
    global $bp, $bgr;

    $spinner_src       	  	= includes_url().'/images/spinner.gif';
	$allow_popup          	= $bgr['add_review_allow_popup'];
    $auto_approve_reviews   = $bgr['auto_approve_reviews'];
	$reviews_per_page     	= $bgr['reviews_per_page']; 
	$allow_email      	 	= $bgr['allow_email'];  
	$allow_notification   	= $bgr['allow_notification'];
	$exclude_groups       	= $bgr['exclude_groups']; 
	$group_args 		  	= array(  
		'order'       => 'DESC', 
		'orderby'     => 'date_created',
        'per_page'    => -1
	);
    $allgroups  			= groups_get_groups( $group_args );
    
    ?>
    <div class="bgr-col-12"><h2><?php _e( 'General Settings', BGR_DOMAIN );?></h2></div>
    <div class="bgr-row">
        <div class="bgr-col-3">
            <label><?php _e( 'Add Review Popup', BGR_DOMAIN );?></label>
        </div>
        <div class="bgr-col-9">
            <label class="bgr-switch" for="bgr-allow-popup">
                <input type="checkbox" id="bgr-allow-popup" <?php if( 'yes'  == $allow_popup ){  _e( 'checked="checked"', BGR_DOMAIN ); } ?>>
                <div class="bgr-slider round"></div>
            </label>
			<p class="bgr-admin-des"><?php _e( 'Enable this option,if you want to show <b>Add Review</b> in modal box.' , BGR_DOMAIN); ?></p>
        </div>
    </div>

    <div class="bgr-row">
        <div class="bgr-col-3">
            <label><?php _e( 'Auto Approve Reviews', BGR_DOMAIN );?></label>
        </div>
        <div class="bgr-col-9">
            <label class="bgr-switch" for="bgr-auto-approve-reviews">
                <input type="checkbox" id="bgr-auto-approve-reviews" <?php if( 'yes'  == $auto_approve_reviews ){  _e( 'checked="checked"', BGR_DOMAIN ); } ?>>
                <div class="bgr-slider round"></div>
            </label>
            <p class="bgr-admin-des"><?php _e( 'Enable this option,if you want to have the reviews automatically approved, else manual approval will be required.' , BGR_DOMAIN); ?></p>
        </div>
    </div>

    <div class="bgr-row">
        <div class="bgr-col-3">
            <label for="reviews_per_page"><?php _e( 'Reviews pages show at most', BGR_DOMAIN );?></label>
        </div>
        <div class="bgr-col-9">
            <input id="reviews_per_page" class="small-text" name="reviews_per_page" step="1" min="1" value="<?php _e( $reviews_per_page, BGR_DOMAIN ); ?>" type="number">
            <?php _e( 'Reviews', BGR_DOMAIN ); ?>
			<p class="bgr-admin-des"><?php _e( 'This option lets you limit number of reviews in <b> Group Reviews </b> page & <b>Manage Reviews </b> Page .' , BGR_DOMAIN); ?></p>
        </div>
    </div>
    <div class="bgr-row">
        <div class="bgr-col-3"><label><?php _e( 'Notifications', BGR_DOMAIN );?></label></div>
        <div class="bgr-col-9">
			<?php  if( bp_is_active( 'notifications' ) ) { ?>
				<label class="bgr-switch" for="bgr-notification">
					<input type="checkbox" id="bgr-notification" <?php if( $allow_notification == 'yes' ){ _e( 'checked="checked"', BGR_DOMAIN );} ?>>
					<div class="bgr-slider round"></div>
				</label>					
				<p class="bgr-admin-des"><?php _e( 'Enable this option,if you want to group admin & reviewer revieve notification when add,accept & deny review .' , BGR_DOMAIN); ?></p>
			<?php }  else { ?>
				<p class="bgr-admin-des"><?php _e( 'This setting requires <b>BuddyPress Notifications </b>Component to be active.' , BGR_DOMAIN); ?></p>
			<?php } ?>	
        </div>
    </div>
    <div class="bgr-row">
        <div class="bgr-col-3"><label><?php _e( 'Emails', BGR_DOMAIN );?></label></div>
        <div class="bgr-col-9">
            <label class="bgr-switch" for="bgr-email">
                <input type="checkbox" id="bgr-email" <?php if( $allow_email == 'yes' ){ _e( 'checked="checked"', BGR_DOMAIN ); } ?>>
                <div class="bgr-slider round"></div>
            </label>
			<p class="bgr-admin-des"><?php _e( 'Enable this option,if you want to group admin & reviewer revieve email when add,accept & deny review .' , BGR_DOMAIN); ?></p>
        </div>
    </div>
    <div class="bgr-row">
        <div class="bgr-col-3"><label><?php _e( 'Exclude Groups from reviews.', BGR_DOMAIN ); ?></label></div>
        <div class="bgr-col-9">
            <select id="bgr-exclude-group-review" name="bgr-exclude-group[]" multiple >
                <?php if( $allgroups ) {
                    foreach( $allgroups['groups'] as $group ):
                        if( !empty( $exclude_groups ) ) {
                            if( in_array( $group->id, $exclude_groups ) ) {?>
                                <option value="<?php _e( $group->id, BGR_DOMAIN ); ?>" <?php _e( 'selected = selected', BGR_DOMAIN ); ?>><?php _e( $group->name, BGR_DOMAIN );?></option> 
                            <?php } else { ?>
                                <option value="<?php _e( $group->id, BGR_DOMAIN ); ?>"><?php _e( $group->name, BGR_DOMAIN );?></option> 
                            <?php } } else { ?>
                                <option value="<?php _e( $group->id, BGR_DOMAIN ); ?>"><?php _e( $group->name, BGR_DOMAIN );?></option> 
                        <?php  }  
                    endforeach;
				}?>
            </select>
			<p class="bgr-admin-des"><?php _e( "This option lets you to choose those groups that you don't want to provide review functionality.", BGR_DOMAIN); ?></p>
        </div>
    </div>
    <div class="bgr-col-12">
        <div class="bgr-col-6">
            <input type="button" class="button button-primary" id="bgr-save-admin-general-settings" value="<?php _e( 'Save Settings', BGR_DOMAIN );?>">
            <img src="<?php _e( $spinner_src, BGR_DOMAIN );?>" class="bgr-admin-general-settings-spinner" />
        </div>
    </div> 
<?php }