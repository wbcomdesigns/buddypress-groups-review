jQuery(document).ready(function(e){

        jQuery('#bgr_review_success_msg_modal').hide();
        //Open Add Review Form
        jQuery(document).on('click', '#add-review', function(){
                jQuery( '#bgr-add-review-modal' ).css( 'display', 'block' );
        });

        var activediv = jQuery(location).attr('hash');
        var hashval = activediv.split('=').pop();        
        if( hashval == 'form-modal' ) {
            jQuery('#bgr-add-review-modal').show();
        }   else {
            jQuery('.bgr-group-review-no-popup-add-block').show();
        }
        // When the user clicks on <span> (x), close the modal
        jQuery(document).on('click', '.close-modal', function(){
                jQuery( '#bgr-add-review-modal' ).css( 'display', 'none' );
        });

        /*----------------------------------------
        * Display message after review submit
        *-----------------------------------------*/
        jQuery(document).on('click', '#bgr_review_success_msg_modal .close', function (){
            jQuery('#bgr_review_success_msg_modal').hide();
        });

        // When the user clicks anywhere outside of the modal, close it
        jQuery(document.body).click(function(event){
                var modal = document.getElementById('bgr-add-review-modal');
                if( event.target == modal ) {
                        jQuery( '#bgr-add-review-modal' ).css( 'display', 'none' );
                }
        });

		//Slide the add review form
		jQuery(document).on('click', '#add-review-no-popup', function(){
			jQuery('.bgr-group-review-no-popup-add-block').slideToggle();
		});
        
        // Accept review        
        jQuery(document).on('click', '.accept-button', function(){
            var accept_review_id = jQuery(this).next().val();  
            var accept_gid       = jQuery(this).attr("data-group-type");
            jQuery.post(
                ajaxurl,
                        {
                        'action'           : 'bgr_accept_review',
                        'accept_review_id' :  accept_review_id,
                        'group_id'         :  accept_gid
                        },
                        function(response) {                           
                           location.reload();                      
                        }
                );
        });
        
        // Deny review
        jQuery(document).on('click', '.deny-button', function(){
            var deny_review_id   = jQuery(this).next().val();
            var deny_gid         = jQuery(this).attr("data-group-type");
            jQuery.post(
                ajaxurl,
                        {
                        'action'         : 'bgr_deny_review',
                        'deny_review_id' :  deny_review_id, 
                        'group_id'       :  deny_gid 
                        },
                        function(response) {
							location.reload();                      
                        }
                );
        });
        
    // Delete review
    jQuery(document).on('click', '.remove-review-button', function(){
        var remove_review_id = jQuery(this).next().val();  
        jQuery.post(
            ajaxurl,
                    {
                    'action'            : 'bgr_remove_review',
                    'remove_review_id'  :  remove_review_id                                       
                    },
                    function(response) {
                       location.reload();                      
                    }
            );
    });

    // Show & hide full description
    jQuery(document).on('click','.expand-review-des', function() {
        var display = jQuery(this).parent().children('.review-full-description').css('display');
        if (display === 'block') {
            jQuery('.review-full-description').slideUp(500);
            jQuery(this).parent().children('.review-excerpt').slideDown(500);
            jQuery(this).text("View More...");
        }else{
            jQuery('.review-full-description').hide();
            jQuery('.review-excerpt').show();
            jQuery('.review-full-description').next().text("View More..");
            jQuery(this).parent().children('.review-excerpt').slideUp(500);
            jQuery(this).parent().children('.review-full-description').slideDown(500);
            jQuery(this).text("View Less...");
        }
       
    });
	
	 // Submit Group Review
    jQuery('#bgr-add-review-form').submit(function(e) {
        e.preventDefault();
        jQuery.post(
            ajaxurl,
           {
               'action' : 'bgr_submit_review',
               'data'   : jQuery(this).serialize()
           },
           function(response) {
                jQuery('.bgr-bp-success p').html( response ).css("display","block");
                jQuery('.bgr-group-review-no-popup-add-block').hide();
                jQuery('#bgr-add-review-modal').hide();
                jQuery('#bgr-message').css("display","block");
                 jQuery('#bgr_review_success_msg_modal p').text(response);
                jQuery('#bgr_review_success_msg_modal').show();
           }
        );
    });
});
