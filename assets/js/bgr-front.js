jQuery(document).ready(function(){        
	jQuery('.dataTables_filter input[type="search"]').attr('placeholder','Enter Keywords....');

	//Open Add Review Form
	jQuery(document).on('click', '#add-review', function(){
		jQuery( '#bgr-add-review-modal' ).css( 'display', 'block' );
	});

	// When the user clicks on <span> (x), close the modal
	jQuery(document).on('click', '.close-modal', function(){
		jQuery( '#bgr-add-review-modal' ).css( 'display', 'none' );
	});

	// When the user clicks anywhere outside of the modal, close it
	jQuery(document.body).click(function(event){
		var modal = document.getElementById('bgr-add-review-modal');
		if( event.target == modal ) {
			jQuery( '#bgr-add-review-modal' ).css( 'display', 'none' );
		}
	});

	reviews_pluginurl = jQuery( '#reviews_pluginurl' ).val();
	
	//Change the stars on hover
	
	jQuery('.stars').mouseenter(function(){            
		jQuery(this).parent().children().eq(1).val( 'not_clicked' );
		var id = jQuery(this).attr('data-attr');
                var parent_id = jQuery(this).parent().attr('id');                
		for( i = 1; i <= id; i++ ) {
			jQuery('#'+ parent_id).children('.'+ i).attr( 'src', reviews_pluginurl+'assets/images/star.png' );
		}
	});

	jQuery('.stars').mouseleave(function(){    
                var clicked_id = jQuery(this).parent().children().eq(2).val();
		var id = jQuery(this).attr('data-attr');
                var parent_id = jQuery(this).parent().attr('id');
		if( jQuery(this).parent().children().eq(1).val() !== 'clicked' ) {
			var j = parseInt ( clicked_id ) + 1;
			for( i = j; i <= 5; i++ ) {
				jQuery('#'+ parent_id).children('.'+ i).attr( 'src', reviews_pluginurl+'assets/images/star_off.png' );
			}
		}
	});

	//Color the stars on click
	jQuery('.stars').on('click',function(){
		attr = jQuery(this).attr('data-attr');
		clicked_id = attr;
                var parent_id = jQuery(this).parent().attr('id');    
		jQuery(this).parent().children().eq(2).val( attr );                          
		jQuery(this).parent().children().eq(1).val('clicked');
		for( i = 1; i <= attr; i++ ) {
			jQuery('#'+ parent_id).children('.'+ i).attr( 'src', reviews_pluginurl+'assets/images/star.png' );
		}

		var k = parseInt( attr ) + 1;
		for( j = k; j <= 5; j++ ) {
			jQuery('#'+ parent_id).children('.'+ j).attr( 'src', reviews_pluginurl+'assets/images/star_off.png' );
		}
	});

	//Slide the add review form
	jQuery(document).on('click', '#add-review-no-popup', function(){
		jQuery('.bgr-group-review-no-popup-add-block').slideToggle();
	});
        
        // Accept review        
        jQuery(document).on('click', '.accept-button', function(){
            var accept_review_id = jQuery(this).next().val();  
            jQuery.post(
                ajaxurl,
                        {
                        'action'           : 'bgr_accept_review',
                        'accept_review_id' :  accept_review_id,
                        },
                        function(response) {                           
                           location.reload();                      
                        }
                );
        });
        
        // Deny review
        jQuery(document).on('click', '.deny-button', function(){
            var deny_review_id   = jQuery(this).next().val();
            jQuery.post(
                ajaxurl,
                        {
                        'action'         : 'bgr_deny_review',
                        'deny_review_id' :  deny_review_id,   
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
                        'action'         : 'bgr_remove_review',
                        'remove_review_id' :  remove_review_id,                                       
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

        jQuery('#bgr-add-review-form').submit(function(e) {  
            e.preventDefault();
            jQuery.post(
                   ajaxurl,
                       {
                       'action' : 'bgr_submit_review',
                       'data'   : jQuery(this).serialize(),
                       },
                       function(response) {
                            jQuery('.bgr-bp-success').css("display","block");
                            jQuery('.bgr-group-review-no-popup-add-block').hide();
                            jQuery('#bgr-message').css("display","block");
                            
                       }
                   );
            });
});
