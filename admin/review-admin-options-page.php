<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if(isset($_GET)){
        $bgr_setting_tab = $_GET['tab'];
        bgr_include_admin_setting_tabs($bgr_setting_tab); 
}

function bgr_include_admin_setting_tabs($bgr_setting_tab = 'general'){
    switch($bgr_setting_tab){
        case 'criteria'  : bgr_criteria_setting();
                           break;   
        case 'shortcode' : bgr_shortcode_setting();
                           break;   
        case 'display'   : bgr_display_setting();
                           break;   
        case 'support'   : bgr_support_setting();
                           break;   
        default          : bgr_general_setting();                
              
    }
}

function bgr_criteria_setting(){
    $spinner_src = includes_url().'/images/spinner.gif';
    $bgr_admin_settings = get_option( 'bgr_admin_criteria_settings', true );
    if( !empty( $bgr_admin_settings ) ) {
        $review_rating_fields = $bgr_admin_settings['add_review_rating_fields'];
    }  
?>    
    <div class="bgr-col-12"><h2><?php _e( 'Criteria Settings', BGR_DOMAIN );?></h2></div>
    <div class="bgr-row">
        <div class="bgr-col-6">
            <label><?php _e( 'Criteria(s)', BGR_DOMAIN );?></label>
        </div>
        <div class="bgr-col-6">
            <div id="BGRTextBoxContainer">
                <?php if(!empty($review_rating_fields)){ 
                         foreach($review_rating_fields as $review_rating_field):                                            
                ?>
                    <div class="rating-review-div"><input name = "BGRDynamicTextBox" type="text" value = "<?php _e( $review_rating_field , BGR_DOMAIN ); ?>" />
                        <input type="button" value="<?php _e( 'Remove', BGR_DOMAIN );?>" class="remove button button-secondary" /><span>&equiv;</span></div>
                <?php endforeach; } ?>
                <!--Criteria(s) Textboxes will be added here -->                    
            </div> 
            <input id="btnAdd" type="button" value="<?php _e( 'Add Criteria', BGR_DOMAIN );?>" class="button button-secondary"/>
        </div>
    </div>
    <div class="bgr-col-12">
        <div class="bgr-col-6">
            <input type="button" class="button button-primary" id="bgr-save-admin-criteria-settings" value="<?php _e( 'Save Settings', BGR_DOMAIN );?>">
            <img src="<?php _e( $spinner_src, BGR_DOMAIN );?>" class="bgr-admin-criteria-settings-spinner" />
        </div>
    </div> 
<?php }

function bgr_shortcode_setting(){ ?>
    <div class="bgr-col-12"><h2><?php _e( 'Shortcodes', BGR_DOMAIN );?></h2></div>
    <div class="bgr-row"> 
        <div class="bgr-col-6"><label><?php _e( '[add_review_form]', BGR_DOMAIN );?></label></div>
        <div class="bgr-col-6"><?php _e( 'This shortcode will be display Group Review Form.', BGR_DOMAIN );?></div>
    </div>
<?php }

function bgr_display_setting(){
    $spinner_src = includes_url().'/images/spinner.gif';
    $bgr_admin_settings = get_option( 'bgr_admin_display_settings', true );
    if( !empty( $bgr_admin_settings ) ) {
        $bgrReviewLabel           = $bgr_admin_settings['review_label'];
        $bgrManageReviewLabel     = $bgr_admin_settings['manage_review_label'];
        $bgr_rating_color         = $bgr_admin_settings['bgr_rating_color'];
        $bgr_rating_type          = $bgr_admin_settings['bgr_rating_type'];           
    }
    ?>
    <div class="bgr-col-12"><h2><?php _e( 'Display Settings', BGR_DOMAIN );?></h2></div>
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
        <div class="bgr-col-6"><label for="bgr-rating-color"><?php _e( 'Rating Color', BGR_DOMAIN );?></label></div>
        <div class="bgr-col-6"><input id="bgr-rating-color" type="color" value="<?php if(isset($bgr_rating_color)){ _e( $bgr_rating_color , BGR_DOMAIN ); } ?>" /></div>
    </div>
    <div class="bgr-col-12"><h3><?php _e( 'Rating Type', BGR_DOMAIN );?></h3></div>
    <div class="bgr-row"> 
        <div class="bgr-col-6"><label for="bgr-rating-type"><?php _e( 'Types', BGR_DOMAIN );?></label></div>
        <div class="bgr-col-6">
            <select id="bgr-rating-type" name="bgr-rating-type">                
                <option value="<?php _e( 'star', BGR_DOMAIN );?>" <?php if($bgr_rating_type=='star'){ _e( 'selected', BGR_DOMAIN ); } ?>><?php _e( 'Star Rating', BGR_DOMAIN );?></option> 
                <option value="<?php _e( 'numbers', BGR_DOMAIN );?>" <?php if($bgr_rating_type=='numbers'){ _e( 'selected', BGR_DOMAIN ); } ?>><?php _e( 'Number Rating', BGR_DOMAIN );?></option>
                <option value="<?php _e( 'bar', BGR_DOMAIN );?>" <?php if($bgr_rating_type=='bar'){ _e( 'selected', BGR_DOMAIN ); } ?>><?php _e( 'Bar Rating', BGR_DOMAIN );?></option>
                <option value="<?php _e( 'donut', BGR_DOMAIN );?>" <?php if($bgr_rating_type=='donut'){ _e( 'selected', BGR_DOMAIN ); } ?>><?php _e( 'Donut Bar Rating', BGR_DOMAIN );?></option>
            </select>
        </div>
    </div> 
    <div class="bgr-col-12">
        <div class="bgr-col-6">
            <input type="button" class="button button-primary" id="bgr-save-admin-display-settings" value="<?php _e( 'Save Settings', BGR_DOMAIN );?>">
            <img src="<?php _e( $spinner_src, BGR_DOMAIN );?>" class="bgr-admin-display-settings-spinner" />
        </div>
    </div> 
<?php }

function bgr_support_setting(){ ?>
    <div class="bgr-col-12"><h2><?php _e( 'FAQ(s)', BGR_DOMAIN );?></h2></div>
    <div class="bgr-row">
        <button class="bgr-faq-accordion"><?php _e( 'Section 1', BGR_DOMAIN );?></button>
        <div class="bgr-faq-panel">
            <p>Lorem ipsum section1...</p>
        </div>

        <button class="bgr-faq-accordion"><?php _e( 'Section 2', BGR_DOMAIN );?></button>
        <div class="bgr-faq-panel">
            <p>Lorem ipsum section2...</p>
        </div>

        <button class="bgr-faq-accordion"><?php _e( 'Section 3', BGR_DOMAIN );?></button>
        <div class="bgr-faq-panel">
            <p>Lorem ipsum section 3...</p>
        </div> 
    </div>
<?php     
}

function bgr_general_setting(){ 
    $spinner_src = includes_url().'/images/spinner.gif';
    $bgr_admin_settings = get_option( 'bgr_admin_general_settings', true );
    $allow_popup = '';
    if( !empty( $bgr_admin_settings ) ) {
            $allow_popup              = $bgr_admin_settings['add_review_allow_popup'];
            $reviews_per_page         = $bgr_admin_settings['reviews_per_page']; 
            $allow_email              = $bgr_admin_settings['allow_email'];  
            $allow_notification       = $bgr_admin_settings['allow_notification'];  
    }    
    ?>  
        <div class="bgr-col-12"><h2><?php _e( 'General Settings', BGR_DOMAIN );?></h2></div>
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
                <label class="bgr-switch" for="bgr-notification">
                    <input type="checkbox" id="bgr-notification" <?php if( $allow_notification == 'yes' ){ _e( 'checked="checked"', BGR_DOMAIN );} ?>>
                    <div class="bgr-slider round"></div>
                </label>
            </div>
        </div>
        <div class="bgr-row">
            <div class="bgr-col-6"><label><?php _e( 'Emails', BGR_DOMAIN );?></label></div>
            <div class="bgr-col-6">
                <label class="bgr-switch" for="bgr-email">
                    <input type="checkbox" id="bgr-email" <?php if( $allow_email == 'yes' ){ _e( 'checked="checked"', BGR_DOMAIN ); } ?>>
                    <div class="bgr-slider round"></div>
                </label>
            </div>
        </div>
        <div class="bgr-col-12">
            <div class="bgr-col-6">
                <input type="button" class="button button-primary" id="bgr-save-admin-general-settings" value="<?php _e( 'Save Settings', BGR_DOMAIN );?>">
                <img src="<?php _e( $spinner_src, BGR_DOMAIN );?>" class="bgr-admin-general-settings-spinner" />
            </div>
        </div> 
<?php }