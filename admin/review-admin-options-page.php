<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) { 
    exit; 
}   

$spinner_src = includes_url().'/images/spinner.gif';

//Admin Settings
$bgr_admin_settings = get_option( 'bgr_admin_settings', true );
$allow_popup = '';
if( !empty( $bgr_admin_settings ) ) {
	$allow_popup          = $bgr_admin_settings['add_review_allow_popup'];
        $review_rating_fields = $bgr_admin_settings['add_review_rating_fields'];
        $reviews_per_page     = $bgr_admin_settings['reviews_per_page'];
}
?>

<div class="bgr-admin-settings-block bgr-col-12">
    <div class="bgr-col-6">
        <div class="bgr-admin-settings-header">    
            <p><?php _e( 'Group Reviews Settings', BGR_DOMAIN );?></p>
        </div>
    
	<div id="bgr_settings_updated" class="updated settings-error notice is-dismissible">
		<p><strong><?php _e( 'Group Reviews Settings Saved.', BGR_DOMAIN );?></strong></p>
		<button type="button" class="notice-dismiss">
			<span class="screen-reader-text"><?php _e( 'Dismiss this notice.', BGR_DOMAIN );?></span>
		</button>
	</div>
	<table cellspacing="0" id="bgr-settings-tbl">            
		<tr>
                        <th scope="row" class="bgr-row-setting">
                            <label for="bgr-allow-popup"><?php _e( 'Add Review Popup', BGR_DOMAIN );?></label>
                        </th>
			<td>
				<input type="checkbox" id="bgr-allow-popup" <?php if( $allow_popup == 'yes' ) _e( 'checked="checked"', BGR_DOMAIN ); ?>>
			</td>
		</tr>
                <tr>
                        <th scope="row" class="bgr-row-setting">
                            <label for="reviews_per_page"><?php _e( 'Reviews pages show at most', BGR_DOMAIN );?></label>
                        </th>
                        <td>
                            <input id="reviews_per_page" class="small-text" name="reviews_per_page" step="1" min="1" value="<?php if( !empty($reviews_per_page) ){  _e( $reviews_per_page, BGR_DOMAIN ); }else{ _e( "3", BGR_DOMAIN );  }?>" type="number">
                                <?php _e( 'Reviews', BGR_DOMAIN ); ?>
                        </td>
                </tr>
                <tr>
                        <th scope="row" class="bgr-row-setting">
                            <label for="TextBoxContainer"><?php _e( 'Review Criteria(s)', BGR_DOMAIN ); ?></label>                           
                        </th>
                </tr>
                <tr>
                        <td>                           
                            <div id="TextBoxContainer">
                                <?php if(!empty($review_rating_fields)){ 
                                         foreach($review_rating_fields as $review_rating_field):                                            
                                ?>
                                    <div class="rating-review-div"><input name = "DynamicTextBox" type="text" value = "<?php _e( $review_rating_field , BGR_DOMAIN ); ?>" />
                                        <input type="button" value="<?php _e( 'Remove', BGR_DOMAIN );?>" class="remove button button-secondary" /></div>
                                <?php endforeach; } ?>
                                <!--Textboxes will be added here -->
                            </div>                            
                        </td>                        
                </tr>        
                <tr>
                    <td>
                         <input id="btnAdd" type="button" value="<?php _e( 'Add Criteria', BGR_DOMAIN );?>" class="button button-secondary"/>
                    </td>
                </tr>
		<tr>
			<td>
				<input type="button" class="button button-primary" id="bgr-save-admin-settings" value="<?php _e( 'Save Settings', BGR_DOMAIN );?>">
				<img src="<?php _e( $spinner_src, BGR_DOMAIN );?>" class="bgr-admin-settings-spinner" />
			</td>
		</tr>
	</table>
    </div>
    <div class="bgr-col-6">
        <div class="bgr-admin-settings-header">    
            <p><?php _e( 'Group Reviews Shortcodes', BGR_DOMAIN );?></p>
        </div>
        <table cellspacing="0" id="bgr-settings-tbl" class="bgr-shortcode-tbl">            
		<tr>
                        <th scope="row" class="bgr-row-setting">
                            <label for="bgr-allow-popup"><?php _e( '1) [add_review_form]', BGR_DOMAIN );?></label>
                        </th>
			<td>
		            <?php _e( 'This shortcode will be display Group Review Form.', BGR_DOMAIN );?>		
			</td>
		</tr>                
        </table>        
    </div>
</div>
