/**
 * BuddyPress Group Reviews: Admin JS (toast + confirm helpers).
 *
 * Skill Part 6 rules 10 + 12: no browser alert()/confirm() anywhere.
 * Exposes window.bgrToast() and window.bgrConfirm() (Promise-based)
 * so the retained legacy admin JS (bgr-admin.js) and any future code
 * share one feedback surface.
 *
 * @since 3.9.0
 */
( function ( $ ) {
	'use strict';

	var i18n = ( window.bgrAdminPanel && window.bgrAdminPanel.i18n ) || {};

	// wp.i18n helper (handle depends on wp-i18n; guarded for safety).
	var __ = ( window.wp && window.wp.i18n ) ? window.wp.i18n.__ : function ( text ) { return text; };

	/* ─── Toast ─────────────────────────────────────────────── */

	function getToastHost() {
		var host = document.querySelector( '.bgr-toast-host' );
		if ( ! host ) {
			host = document.createElement( 'div' );
			host.className = 'bgr-toast-host';
			document.body.appendChild( host );
		}
		return host;
	}

	function toast( message, tone ) {
		tone = tone || 'info';
		var host = getToastHost();
		var el = document.createElement( 'div' );
		el.className = 'bgr-toast bgr-toast--' + tone;
		el.setAttribute( 'role', 'status' );
		el.textContent = String( message );
		host.appendChild( el );

		requestAnimationFrame( function () {
			el.classList.add( 'bgr-toast--visible' );
		} );

		window.setTimeout( function () {
			el.classList.remove( 'bgr-toast--visible' );
			window.setTimeout( function () {
				if ( el.parentNode ) {
					el.parentNode.removeChild( el );
				}
			}, 250 );
		}, 3600 );
	}

	window.bgrToast = toast;

	/* ─── Confirm modal (returns a Promise<boolean>) ─────────── */

	function confirmModal( opts ) {
		opts = opts || {};
		return new Promise( function ( resolve ) {
			var backdrop = document.createElement( 'div' );
			backdrop.className = 'bgr-confirm-backdrop';

			var card = document.createElement( 'div' );
			card.className = 'bgr-confirm';
			card.setAttribute( 'role', 'dialog' );
			card.setAttribute( 'aria-modal', 'true' );

			if ( opts.title ) {
				var title = document.createElement( 'h2' );
				title.className = 'bgr-confirm__title';
				title.textContent = opts.title;
				card.appendChild( title );
			}

			var desc = document.createElement( 'p' );
			desc.className = 'bgr-confirm__desc';
			desc.textContent = opts.message || i18n.confirmDanger || '';
			if ( opts.message || i18n.confirmDanger ) {
				card.appendChild( desc );
			}

			var actions = document.createElement( 'div' );
			actions.className = 'bgr-confirm__actions';

			var cancelBtn = document.createElement( 'button' );
			cancelBtn.type = 'button';
			cancelBtn.className = 'bgr-btn bgr-btn-secondary';
			cancelBtn.textContent = opts.cancelLabel || i18n.confirmCancel || __( 'Cancel', 'bp-group-reviews' );

			var confirmBtn = document.createElement( 'button' );
			confirmBtn.type = 'button';
			confirmBtn.className = 'bgr-btn ' + ( 'danger' === opts.tone ? 'bgr-btn-danger' : 'bgr-btn-primary' );
			confirmBtn.textContent = opts.confirmLabel || i18n.confirmContinue || __( 'Continue', 'bp-group-reviews' );

			actions.appendChild( cancelBtn );
			actions.appendChild( confirmBtn );
			card.appendChild( actions );
			backdrop.appendChild( card );
			document.body.appendChild( backdrop );

			function cleanup( result ) {
				document.removeEventListener( 'keydown', onKey );
				if ( backdrop.parentNode ) {
					backdrop.parentNode.removeChild( backdrop );
				}
				resolve( result );
			}

			function onKey( e ) {
				if ( 'Escape' === e.key ) { cleanup( false ); }
				if ( 'Enter' === e.key ) { cleanup( true ); }
			}

			cancelBtn.addEventListener( 'click', function () { cleanup( false ); } );
			confirmBtn.addEventListener( 'click', function () { cleanup( true ); } );
			backdrop.addEventListener( 'click', function ( e ) {
				if ( e.target === backdrop ) { cleanup( false ); }
			} );
			document.addEventListener( 'keydown', onKey );
			confirmBtn.focus();
		} );
	}

	window.bgrConfirm = confirmModal;

	// Note: the rating color picker is initialised by the retained
	// legacy bgr-colorpicker.js (targets .bgr-review-color); we do not
	// re-init it here to avoid a double wpColorPicker() on the same input.

}( jQuery ) );
