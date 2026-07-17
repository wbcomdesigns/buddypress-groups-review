/**
 * bgrConfirm — promise-based confirmation dialog (front-end).
 *
 * Replacement for window.confirm() on member-facing screens (group
 * criteria management). Returns a Promise<boolean>.
 *
 *   bgrConfirm({
 *     title:     'Archive criteria?',
 *     message:   'Historical reviews will be preserved.',
 *     confirm:   'Archive',
 *     cancel:    'Cancel',
 *     dangerous: true,
 *   }).then(function(ok) { if (ok) { ... } });
 *
 * @since 3.9.0
 */
( function () {
	'use strict';

	// wp.i18n helper (handle depends on wp-i18n; guarded for safety).
	var __ = ( window.wp && window.wp.i18n ) ? window.wp.i18n.__ : function ( text ) { return text; };

	var i18n = window.bgrConfirmI18n || {
		confirm: __( 'Confirm', 'bp-group-reviews' ),
		cancel: __( 'Cancel', 'bp-group-reviews' )
	};

	window.bgrConfirm = function ( options ) {
		return new Promise( function ( resolve ) {
			var opts = options || {};

			var overlay = document.createElement( 'div' );
			overlay.className = 'bgr-confirm-overlay';

			var box = document.createElement( 'div' );
			box.className = 'bgr-confirm-box';
			box.setAttribute( 'role', 'alertdialog' );
			box.setAttribute( 'aria-modal', 'true' );
			box.setAttribute( 'aria-labelledby', 'bgr-confirm-title' );
			box.setAttribute( 'aria-describedby', 'bgr-confirm-message' );

			if ( opts.title ) {
				var title = document.createElement( 'h2' );
				title.className = 'bgr-confirm-box__title';
				title.id = 'bgr-confirm-title';
				title.textContent = opts.title;
				box.appendChild( title );
			}

			var msg = document.createElement( 'p' );
			msg.className = 'bgr-confirm-box__message';
			msg.id = 'bgr-confirm-message';
			msg.textContent = opts.message || '';
			box.appendChild( msg );

			var actions = document.createElement( 'div' );
			actions.className = 'bgr-confirm-box__actions';

			var cancelBtn = document.createElement( 'button' );
			cancelBtn.type = 'button';
			cancelBtn.className = 'bgr-confirm-box__btn bgr-confirm-box__btn--cancel';
			cancelBtn.textContent = opts.cancel || i18n.cancel;
			actions.appendChild( cancelBtn );

			var confirmBtn = document.createElement( 'button' );
			confirmBtn.type = 'button';
			confirmBtn.className = 'bgr-confirm-box__btn bgr-confirm-box__btn--confirm' + ( opts.dangerous ? ' bgr-confirm-box__btn--dangerous' : '' );
			confirmBtn.textContent = opts.confirm || i18n.confirm;
			actions.appendChild( confirmBtn );

			box.appendChild( actions );
			overlay.appendChild( box );
			document.body.appendChild( overlay );

			var previousFocus = document.activeElement;

			function close( result ) {
				document.removeEventListener( 'keydown', onKey );
				overlay.classList.remove( 'bgr-confirm-overlay--visible' );
				overlay.addEventListener( 'transitionend', function () {
					overlay.remove();
					if ( previousFocus && previousFocus.focus ) { previousFocus.focus(); }
				}, { once: true } );
				resolve( result );
			}

			function onKey( e ) {
				if ( e.key === 'Escape' ) { close( false ); }
				if ( e.key === 'Enter' && document.activeElement !== cancelBtn ) { close( true ); }
			}

			cancelBtn.addEventListener( 'click', function () { close( false ); } );
			confirmBtn.addEventListener( 'click', function () { close( true ); } );
			overlay.addEventListener( 'click', function ( e ) {
				if ( e.target === overlay ) { close( false ); }
			} );
			document.addEventListener( 'keydown', onKey );

			void overlay.offsetWidth;
			overlay.classList.add( 'bgr-confirm-overlay--visible' );
			window.setTimeout( function () { confirmBtn.focus(); }, 0 );
		} );
	};
}() );
