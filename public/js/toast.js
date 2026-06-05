/**
 * bgrToast — stacking toast notifications (front-end).
 *
 * Replacement for window.alert() on member-facing screens (group
 * criteria management). Never use native alert().
 *
 *   bgrToast( 'Saved.', 'success' );
 *
 * Types: 'info' | 'success' | 'warning' | 'error'
 *
 * @since 3.9.0
 */
( function () {
	'use strict';

	window.bgrToast = function ( message, type, duration ) {
		type     = type || 'info';
		duration = duration || 3500;

		var container = document.getElementById( 'bgr-toast-container' );
		if ( ! container ) {
			container = document.createElement( 'div' );
			container.id = 'bgr-toast-container';
			container.className = 'bgr-toast-container';
			document.body.appendChild( container );
		}

		var toast = document.createElement( 'div' );
		toast.className = 'bgr-toast bgr-toast--' + type;
		toast.setAttribute( 'role', 'status' );
		toast.setAttribute( 'aria-live', 'polite' );
		toast.textContent = message;

		container.appendChild( toast );

		void toast.offsetWidth;
		toast.classList.add( 'bgr-toast--visible' );

		window.setTimeout( function () {
			toast.classList.remove( 'bgr-toast--visible' );
			toast.addEventListener( 'transitionend', function () {
				toast.remove();
			}, { once: true } );
		}, duration );
	};
}() );
