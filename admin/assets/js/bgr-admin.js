jQuery(document).ready(function(){
    
        // Save Criteria Settings
        jQuery(document).on('click', '#bgr-save-admin-criteria-settings', function(){
            jQuery(this).addClass('bgr-btn-ajax');
            jQuery('.bgr-admin-criteria-settings-spinner').show();
            var field_values = [];
            jQuery("input[name=BGRDynamicTextBox]").each(function () {
                if(jQuery(this).val().trim()!= '') {         
                    field_values.push(jQuery(this).val());
                }
            });
            jQuery.post(
                ajaxurl,
                {
                'action'       : 'bgr_save_admin_criteria_settings',
                'field_values' : field_values,
                },
                function ( response ) {
                        if( response === 'admin-criteria-settings-saved' ) {
                                jQuery( '#bgr_settings_updated' ).show();
                                jQuery( '#bgr-save-admin-criteria-settings' ).removeClass('bgr-btn-ajax');
                                jQuery( '.bgr-admin-criteria-settings-spinner' ).hide();                               
                        }
                }
            );            
        });
        
        // Save General Settings
        jQuery(document).on('click', '#bgr-save-admin-general-settings', function(){
            jQuery(this).addClass('bgr-btn-ajax');
            jQuery('.bgr-admin-general-settings-spinner').show();
            if( jQuery( '#bgr-allow-popup' ).is( ':checked' ) ) {
                    allow_popup = 'yes';
            } else {
                    allow_popup = 'no';
            }
            var reviews_per_page        = jQuery('#reviews_per_page').val();
            var bgr_allow_email         = jQuery('#bgr-email').is(':checked')? 'yes' : 'no';
            var bgr_allow_notification  = jQuery('#bgr-notification').is(':checked')? 'yes' : 'no';
            
            jQuery.post(
                ajaxurl,
                {
                'action'             : 'bgr_save_admin_general_settings',
                'allow_popup'        : allow_popup,
                'reviews_per_page'   : reviews_per_page,
                'allow_email'        : bgr_allow_email,
                'allow_notification' : bgr_allow_notification
                },
                function ( response ) {
                        if( response === 'admin-general-settings-saved' ) {
                                jQuery( '#bgr_settings_updated' ).show();
                                jQuery( '#bgr-save-admin-general-settings' ).removeClass('bgr-btn-ajax');
                                jQuery('.bgr-admin-general-settings-spinner').hide();                          
                        }
                }
            ); 
        });
        
        // Save Display Settings
        jQuery(document).on('click', '#bgr-save-admin-display-settings', function(){
            jQuery(this).addClass('bgr-btn-ajax');
            jQuery('.bgr-admin-display-settings-spinner').show();
            var review_label        = jQuery('#bgrReviewLabel').val(); 
            var manage_review_label = jQuery('#bgrManageReviewLabel').val(); 
            var bgr_rating_color    = jQuery('#bgr-rating-color').val(); 
            var bgr_rating_type     = jQuery('#bgr-rating-type').val();            
            jQuery.post(
                ajaxurl,
                {
                'action'             : 'bgr_save_admin_display_settings',
                'review_label'       : review_label,
                'manage_review_label': manage_review_label,
                'bgr_rating_color'   : bgr_rating_color,
                'bgr_rating_type'    : bgr_rating_type
                },
                function ( response ) {
                        if( response === 'admin-display-settings-saved' ) {
                                jQuery( '#bgr_settings_updated' ).show();
                                jQuery( '#bgr-save-admin-display-settings' ).removeClass('bgr-btn-ajax');
                                jQuery( '.bgr-admin-display-settings-spinner' ).hide();                          
                        }
                }
            ); 
        });
        
        jQuery("#btnAdd").bind("click", function () {
            var div = jQuery("<div />");
            div.html(BGRGetBGRDynamicTextBox(""));
            jQuery("#BGRTextBoxContainer").append(div);
        });

        jQuery("body").on("click", ".remove", function () {
            jQuery(this).closest("div").remove();
        });
          
        /*** Accordion changes remains ***/
        var acc = document.getElementsByClassName("accordion");
        var i;

        for (i = 0; i < acc.length; i++) {
            acc[i].onclick = function(){
                /* Toggle between adding and removing the "active" class,
                to highlight the button that controls the panel */
                this.classList.toggle("active");

                /* Toggle between hiding and showing the active panel */
                var panel = this.nextElementSibling;
                if (panel.style.display === "block") {
                    panel.style.display = "none";
                } else {
                    panel.style.display = "block";
                }
            }
        }
        
        jQuery(document).on('click','.bgr-faq-accordion', function() {
            var display = jQuery(this).next().css('display');
            if (display === 'block') {
                jQuery('.bgr-faq-panel').slideUp(500);
                jQuery('.bgr-faq-accordion').removeClass('bgr-faq-accordion-active');
                
            }else{
                jQuery('.bgr-faq-panel').hide();
                jQuery('.bgr-faq-accordion').removeClass('bgr-faq-accordion-active');
                jQuery(this).next().slideDown(500);
                jQuery(this).addClass('bgr-faq-accordion-active');
                
            }
           
        });
        
        /*** Make Draggable admin criteria input fields ***/        
            jQuery( "#BGRTextBoxContainer" ).sortable({
              revert: true
            });     
});
function BGRGetBGRDynamicTextBox(value) {
    return '<input name = "BGRDynamicTextBox" class="draggable" type="text" value = "' + value + '" />&nbsp;' +
           '<input type="button" value="Remove" class="remove button button-secondary bgr-remove" />'
}

function BGRopenSettings(evt, bgrSettingName) {
    var i, x, bgrtablinks;
    x = document.getElementsByClassName("bgr-setting-content");
    for (i = 0; i < x.length; i++) {
       x[i].style.display = "none";
    }
    bgrtablinks = document.getElementsByClassName("bgrtablink");
    for (i = 0; i < x.length; i++) {
        bgrtablinks[i].className = bgrtablinks[i].className.replace(" bgr-blue", ""); 
    }
    document.getElementById(bgrSettingName).style.display = "block";
    evt.currentTarget.className += " bgr-blue";
    
    
}