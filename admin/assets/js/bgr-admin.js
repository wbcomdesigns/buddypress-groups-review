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
                
                var reviews_per_page = jQuery('#reviews_per_page').val();
               
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
                                'field_values': field_values
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
});
function BGRGetDynamicTextBox(value) {
    return '<input name = "DynamicTextBox" type="text" value = "' + value + '" />&nbsp;' +
           '<input type="button" value="Remove" class="remove button button-secondary bgr-remove" />'
}