<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
$spinner_src = includes_url().'/images/spinner.gif';

//Admin Settings
$bgr_admin_settings = get_option( 'bgr_admin_settings', true );
$allow_popup = '';
if( !empty( $bgr_admin_settings ) ) {
	$allow_popup              = $bgr_admin_settings['add_review_allow_popup'];
        $review_rating_fields     = $bgr_admin_settings['add_review_rating_fields'];
        $reviews_per_page         = $bgr_admin_settings['reviews_per_page'];
        $bgrReviewLabel           = $bgr_admin_settings['review_label'];
        $bgrManageReviewLabel     = $bgr_admin_settings['manage_review_label'];
        $bgr_rating_color         = $bgr_admin_settings['bgr_rating_color'];
}
?>

<div class="bgr-col-12">
    <div class="bgr-admin-settings-header">    
        <p><?php _e( 'Group Reviews Settings', BGR_DOMAIN );?></p>
    </div>
    <div id="bgr_settings_updated" class="updated settings-error notice is-dismissible">
        <p><strong><?php _e( 'Group Reviews Settings Saved.', BGR_DOMAIN );?></strong></p>
        <button type="button" class="notice-dismiss">
            <span class="screen-reader-text"><?php _e( 'Dismiss this notice.', BGR_DOMAIN );?></span>
        </button>
    </div>
    <div class="bgr-admin-settings-block bgr-row"> 
        <div class="bgr-setting-sidebar bgr-col-3">
          <h3 class="bgr-menu-heading"><i class="fa fa-cogs" aria-hidden="true"></i><?php _e( 'Settings Menu', BGR_DOMAIN );?></h3>
          <button class="bgrtablink bgr-blue" onclick="BGRopenSettings(event, 'General')"><i class="fa fa-tasks" aria-hidden="true"></i><?php _e( 'General', BGR_DOMAIN );?></button>
          <button class="bgrtablink" onclick="BGRopenSettings(event, 'Criteria')"><i class="fa fa-cog" aria-hidden="true"></i><?php _e( 'Criteria', BGR_DOMAIN );?></button>
          <button class="bgrtablink" onclick="BGRopenSettings(event, 'Display')"><i class="fa fa-bar-chart" aria-hidden="true"></i><?php _e( 'Display', BGR_DOMAIN );?></button>
          <button class="bgrtablink" onclick="BGRopenSettings(event, 'Shortcodes')"><i class="fa fa-code" aria-hidden="true"></i><?php _e( 'Shortcodes', BGR_DOMAIN );?></button>
          <button class="bgrtablink" onclick="BGRopenSettings(event, 'Support')"><i class="fa fa-life-ring" aria-hidden="true"></i><?php _e( 'Support', BGR_DOMAIN );?></button>
        </div>

        <div class="bgr-col-9">
            <div class="bgr-row">
                <div class="bgr-col-6">
                </div>
                <div class="bgr-col-6">
                    <input type="button" class="button button-primary" id="bgr-save-admin-settings" value="<?php _e( 'Save Settings', BGR_DOMAIN );?>">
                    <img src="<?php _e( $spinner_src, BGR_DOMAIN );?>" class="bgr-admin-settings-spinner" />
                </div>
            </div>    
            <div id="General" class="bgr-setting-content">
                <div class="bgr-row">
                    <div class="bgr-col-6">
                        <label for="bgr-allow-popup"><?php _e( 'Add Review Popup', BGR_DOMAIN );?></label>
                    </div>
                    <div class="bgr-col-6">
                        <input type="checkbox" id="bgr-allow-popup" <?php if( $allow_popup == 'yes' ) _e( 'checked="checked"', BGR_DOMAIN ); ?>>                        
                    </div>
                </div>
                <div class="bgr-row">
                    <div class="bgr-col-6">
                        <label for="reviews_per_page"><?php _e( 'Reviews pages show at most', BGR_DOMAIN );?></label>
                    </div>
                    <div class="bgr-col-6">
                        <input id="reviews_per_page" class="small-text" name="reviews_per_page" step="1" min="1" value="<?php if( !empty($reviews_per_page) ){  _e( $reviews_per_page, BGR_DOMAIN ); }else{ _e( "3", BGR_DOMAIN );  }?>" type="number">
                        <?php _e( 'Reviews', BGR_DOMAIN ); ?>
                    </div>
                </div>
                <div class="bgr-row">
                    <div class="bgr-col-6"><label><?php _e( 'Notifications', BGR_DOMAIN );?></label></div>
                    <div class="bgr-col-6">
                        <label class="bgr-switch">
                            <input type="checkbox">
                            <div class="bgr-slider round"></div>
                        </label>
                    </div>
                </div>
                <div class="bgr-row">
                    <div class="bgr-col-6"><label><?php _e( 'Emails', BGR_DOMAIN );?></label></div>
                    <div class="bgr-col-6">
                        <label class="bgr-switch">
                            <input type="checkbox">
                            <div class="bgr-slider round"></div>
                        </label>
                    </div>
                </div>                
            </div>

            <div id="Criteria" class="bgr-setting-content" style="display:none">
                <div class="bgr-row">
                    <div class="bgr-col-6">
                        <label><?php _e( 'Criteria(s)', BGR_DOMAIN );?></label>
                    </div>
                    <div class="bgr-col-6">
                        <div id="TextBoxContainer">
                            <?php if(!empty($review_rating_fields)){ 
                                     foreach($review_rating_fields as $review_rating_field):                                            
                            ?>
                                <div class="rating-review-div"><input name = "DynamicTextBox" type="text" value = "<?php _e( $review_rating_field , BGR_DOMAIN ); ?>" />
                                    <input type="button" value="<?php _e( 'Remove', BGR_DOMAIN );?>" class="remove button button-secondary" /></div>
                            <?php endforeach; } ?>
                            <!--Criteria(s) Textboxes will be added here -->                    
                        </div> 
                        <input id="btnAdd" type="button" value="<?php _e( 'Add Criteria', BGR_DOMAIN );?>" class="button button-secondary"/>
                    </div>
                </div>
            </div>    

            <div id="Display" class="bgr-setting-content" style="display:none">
                <div class="bgr-col-12"><h3><?php _e( 'Labels', BGR_DOMAIN );?></h3></div>
                <div class="bgr-row">                    
                    <div class="bgr-col-6"><label for="bgrReviewLabel"><?php _e( 'Reviews', BGR_DOMAIN );?></label></div>
                    <div class="bgr-col-6"><input name = "bgrReviewLabel" id="bgrReviewLabel" type="text" value = "<?php if(isset($bgrReviewLabel)){ _e( $bgrReviewLabel , BGR_DOMAIN ); } ?>" /></div>
                </div>   
                <div class="bgr-row">                    
                    <div class="bgr-col-6"><label for="bgrManageReviewLabel"><?php _e( 'Reviews (In Manage Section)', BGR_DOMAIN );?></label></div>
                    <div class="bgr-col-6"><input name = "bgrManageReviewLabel" id="bgrManageReviewLabel" type="text" value = "<?php if(isset($bgrManageReviewLabel)){ _e( $bgrManageReviewLabel , BGR_DOMAIN ); } ?>" /></div>
                </div> 
                <div class="bgr-col-12"><h3><?php _e( 'Colors', BGR_DOMAIN );?></h3></div>
                <div class="bgr-row"> 
                    <div class="bgr-col-6"><label><?php _e( 'Rating Color', BGR_DOMAIN );?></label></div>
                    <div class="bgr-col-6"><input id="bgr-rating-color" type="color" value="<?php if(isset($bgr_rating_color)){ _e( $bgr_rating_color , BGR_DOMAIN ); } ?>" /></div>
                </div>
                <div class="bgr-col-12"><h3><?php _e( 'Rating Type', BGR_DOMAIN );?></h3></div>
                <div class="bgr-row"> 
                    <div class="bgr-col-6"><label><?php _e( 'Types', BGR_DOMAIN );?></label></div>
                    <div class="bgr-col-6">
                        <select class="bgr-select-type">                            
                            <option value="<?php _e( 'star', BGR_DOMAIN );?>"><?php _e( 'Star Rating', BGR_DOMAIN );?></option> 
                            <option value="<?php _e( 'percentage', BGR_DOMAIN );?>"><?php _e( 'Percentage Rating', BGR_DOMAIN );?></option>
                            <option value="<?php _e( 'bar', BGR_DOMAIN );?>"><?php _e( 'Bar Rating', BGR_DOMAIN );?></option>
                            <option value="<?php _e( 'donut', BGR_DOMAIN );?>"><?php _e( 'Donut chart Rating', BGR_DOMAIN );?></option>
                        </select>
                    </div>
                </div>                
            </div>

            <div id="Shortcodes" class="bgr-setting-content" style="display:none">
                <div class="bgr-row"> 
                    <div class="bgr-col-6"><label><?php _e( '[add_review_form]', BGR_DOMAIN );?></label></div>
                    <div class="bgr-col-6"><?php _e( 'This shortcode will be display Group Review Form.', BGR_DOMAIN );?></div>
                </div>
            </div>

            <div id="Support" class="bgr-setting-content" style="display:none">
                <button class="accordion"><?php _e( 'Section 1', BGR_DOMAIN );?></button>
                <div class="panel">
                     <p>Lorem ipsum section1...</p>
                </div>

                <button class="accordion"><?php _e( 'Section 2', BGR_DOMAIN );?></button>
                <div class="panel">
                     <p>Lorem ipsum section2...</p>
                </div>

                <button class="accordion"><?php _e( 'Section 3', BGR_DOMAIN );?></button>
                <div class="panel">
                     <p>Lorem ipsum section 3...</p>
                </div> 
            </div>        

        </div>
    </div>    
</div>
