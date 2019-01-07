jQuery( document ).ready(
	function() {

		reviews_pluginurl = jQuery( '#reviews_pluginurl' ).val();
		bgrRatingColor    = jQuery( "#bgr-rate-color" ).val();

		// Change the stars on hover
		jQuery( '.bgr-stars' ).mouseenter(
			function() {
				jQuery( this ).parent().children().eq( 0 ).val( 'not_clicked' );
				var id        = jQuery( this ).attr( 'data-attr' );
				var parent_id = jQuery( this ).parent().attr( 'id' );
				for ( i = 1; i <= id; i++ ) {
					jQuery( '#' + parent_id ).children( '.' + i ).addClass( 'fas' ).removeClass( 'far' );
				}
			}
		);

		jQuery( '.bgr-stars' ).mouseleave(
			function() {
				var clicked_id = jQuery( this ).parent().children().eq( 1 ).val();
				var id         = jQuery( this ).attr( 'data-attr' );
				var parent_id  = jQuery( this ).parent().attr( 'id' );
				if ( jQuery( this ).parent().children().eq( 0 ).val() !== 'clicked' ) {
					var j = parseInt( clicked_id ) + 1;
					for ( i = j; i <= 5; i++ ) {
						jQuery( '#' + parent_id ).children( '.' + i ).addClass( 'far' ).removeClass( 'fas' );
					}
				}
			}
		);

		// Color the stars on click
		jQuery( '.bgr-stars' ).on(
			'click',function() {
				attr          = jQuery( this ).attr( 'data-attr' );
				clicked_id    = attr;
				var parent_id = jQuery( this ).parent().attr( 'id' );
				jQuery( this ).parent().children().eq( 1 ).val( attr );
				jQuery( this ).parent().children().eq( 0 ).val( 'clicked' );
				for ( i = 1; i <= attr; i++ ) {
					jQuery( '#' + parent_id ).children( '.' + i ).addClass( 'fas' ).removeClass( 'far' );					
				}

				var k = parseInt( attr ) + 1;
				for ( j = k; j <= 5; j++ ) {
					jQuery( '#' + parent_id ).children( '.' + j ).addClass( 'far' ).removeClass( 'fas' );
				}
			}
		);

	}
);
