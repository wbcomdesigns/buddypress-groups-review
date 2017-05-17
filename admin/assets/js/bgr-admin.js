jQuery(document).ready(function(){
	//Save admin settings
	jQuery(document).on('click', '#bgr-save-admin-settings', function(){
		jQuery(this).addClass('bgr-btn-ajax');
		jQuery('.bgr-admin-settings-spinner').show();
               
                var field_values = [];
                jQuery("input[name=DynamicTextBox]").each(function () {
                    if(jQuery(this).val().trim()!= '') {         
                        field_values.push(jQuery(this).val());
                    }
                });
                
                var reviews_per_page    = jQuery('#reviews_per_page').val();
                var review_label        = jQuery('#bgrReviewLabel').val(); 
                var manage_review_label = jQuery('#bgrManageReviewLabel').val(); 
                var bgr_rating_color    = jQuery('#bgr-rating-color').val(); 
		if( jQuery( '#bgr-allow-popup' ).is( ':checked' ) ) {
			allow_popup = 'yes';
		} else {
			allow_popup = 'no';
		}
		jQuery.post(
			ajaxurl,
			{
				'action' : 'bgr_save_admin_settings',
				'allow_popup' : allow_popup,
                                'reviews_per_page': reviews_per_page,
                                'field_values': field_values,
                                'review_label': review_label,
                                'manage_review_label': manage_review_label,
                                'bgr_rating_color': bgr_rating_color
			},
			function ( response ) {
				if( response === 'admin-settings-saved' ) {
					jQuery( '#bgr_settings_updated' ).show();
					jQuery( '#bgr-save-admin-settings' ).removeClass('bgr-btn-ajax');
					jQuery('.bgr-admin-settings-spinner').hide();
					console.log( response );
				}
			}
		);
	});         

        jQuery("#btnAdd").bind("click", function () {
          var div = jQuery("<div />");
          div.html(BGRGetDynamicTextBox(""));
          jQuery("#TextBoxContainer").append(div);
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
              
       // jQuery(".bgr-select-type").select2();      
              
});
function BGRGetDynamicTextBox(value) {
    return '<input name = "DynamicTextBox" type="text" value = "' + value + '" />&nbsp;' +
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