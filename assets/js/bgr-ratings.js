jQuery(document).ready(function() {
	
	reviews_pluginurl = jQuery( '#reviews_pluginurl' ).val();
	bgrRatingColor = jQuery("#bgr-rate-color").val();
	
	//Change the stars on hover
	jQuery('.stars').mouseenter(function() {            
		jQuery(this).parent().children().eq(0).val( 'not_clicked' );
		var id = jQuery(this).attr('data-attr');
                var parent_id = jQuery(this).parent().attr('id');                
		for( i = 1; i <= id; i++ ) {
			jQuery('#'+ parent_id).children('.'+ i).attr( 'src', reviews_pluginurl+'assets/images/star.png' );
		}
	});

	jQuery('.stars').mouseleave(function() {    
        var clicked_id = jQuery(this).parent().children().eq(1).val();
		var id = jQuery(this).attr('data-attr');
                var parent_id = jQuery(this).parent().attr('id');
		if( jQuery(this).parent().children().eq(0).val() !== 'clicked' ) {
			var j = parseInt ( clicked_id ) + 1;
			for( i = j; i <= 5; i++ ) {
				jQuery('#'+ parent_id).children('.'+ i).attr( 'src', reviews_pluginurl+'assets/images/star_off.png' );
			}
		}
	});

	//Color the stars on click
	jQuery('.stars').on('click',function() {
		attr = jQuery(this).attr('data-attr');
		clicked_id = attr;
                var parent_id = jQuery(this).parent().attr('id');    
		jQuery(this).parent().children().eq(1).val( attr );                          
		jQuery(this).parent().children().eq(0).val('clicked');
		for( i = 1; i <= attr; i++ ) {
			jQuery('#'+ parent_id).children('.'+ i).attr( 'src', reviews_pluginurl+'assets/images/star.png' );
		}

		var k = parseInt( attr ) + 1;
		for( j = k; j <= 5; j++ ) {
			jQuery('#'+ parent_id).children('.'+ j).attr( 'src', reviews_pluginurl+'assets/images/star_off.png' );
		}
	});
	
		//Change the bar on hover
	
	jQuery('.bgr-bars').mouseenter(function(){            
		jQuery(this).parent().children().eq(0).val( 'not_clicked' );
		var id = jQuery(this).attr('data-attr');
                var parent_id = jQuery(this).parent().attr('id');                
		for( i = 1; i <= id; i++ ) {
			jQuery('#'+ parent_id).children('.'+ i).css({ "background-color" : bgrRatingColor});
		}
	});

	jQuery('.bgr-bars').mouseleave(function(){    
                var clicked_id = jQuery(this).parent().children().eq(1).val();
		var id = jQuery(this).attr('data-attr');
                var parent_id = jQuery(this).parent().attr('id');
		if( jQuery(this).parent().children().eq(0).val() !== 'clicked' ) {
			var j = parseInt ( clicked_id ) + 1;
			for( i = j; i <= 5; i++ ) {
				jQuery('#'+ parent_id).children('.'+ i).css( 'background-color', '#f1f1f1' );
			}
		}
	});

	//Color the bar on click
	jQuery('.bgr-bars').on('click',function(){
		attr = jQuery(this).attr('data-attr');
		clicked_id = attr;
        var parent_id = jQuery(this).parent().attr('id');    
		jQuery(this).parent().children().eq(1).val( attr );                          
		jQuery(this).parent().children().eq(0).val('clicked');
		for( i = 1; i <= attr; i++ ) {
			jQuery('#'+ parent_id).children('.'+ i).css( 'background-color', bgrRatingColor );
		}

		var k = parseInt( attr ) + 1;
		for( j = k; j <= 5; j++ ) {
			jQuery('#'+ parent_id).children('.'+ j).css( 'background-color', '#f1f1f1' );
		}
	});
	
	//Change the number on hover
	
	jQuery('.bgr-numbers').mouseenter(function(){            
		jQuery(this).parent().children().eq(0).val( 'not_clicked' );
		var id = jQuery(this).attr('data-attr');
                var parent_id = jQuery(this).parent().attr('id');                
		for( i = 1; i <= id; i++ ) {
			jQuery('#'+ parent_id).children('.'+ i).css({ "background-color" : bgrRatingColor,"color": "#fff" });
		}
	});

	jQuery('.bgr-numbers').mouseleave(function(){    
                var clicked_id = jQuery(this).parent().children().eq(1).val();
		var id = jQuery(this).attr('data-attr');
                var parent_id = jQuery(this).parent().attr('id');
		if( jQuery(this).parent().children().eq(0).val() !== 'clicked' ) {
			var j = parseInt ( clicked_id ) + 1;
			for( i = j; i <= 5; i++ ) {
				jQuery('#'+ parent_id).children('.'+ i).css({'background-color': '#ffffff', 'color': bgrRatingColor });
			}
		}
	});

	//Color the number on click
	jQuery('.bgr-numbers').on('click',function(){
		attr = jQuery(this).attr('data-attr');
		clicked_id = attr;
		var parent_id = jQuery(this).parent().attr('id');    
		jQuery(this).parent().children().eq(1).val( attr );                          
		jQuery(this).parent().children().eq(0).val('clicked');
		for( i = 1; i <= attr; i++ ) {
			jQuery('#'+ parent_id).children('.'+ i).css({ 'background-color' : bgrRatingColor, 'color': '#ffffff' } );
		}

		var k = parseInt( attr ) + 1;
		for( j = k; j <= 5; j++ ) {
			jQuery('#'+ parent_id).children('.'+ j).css({'background-color': '#ffffff','color': bgrRatingColor });
		}
	});
});