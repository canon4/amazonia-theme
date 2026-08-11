/**
 * amz-lightbox.js — Lightbox mínimo para cualquier <a data-amz-lightbox href="...">.
 * Usado hoy por la galería de tienda (wcfm/store/wcfmmp-view-store.php).
 */
( function () {
	function openLightbox( src, alt ) {
		var overlay = document.createElement( 'div' );
		overlay.className = 'amz-lightbox-overlay';
		overlay.innerHTML =
			'<button type="button" class="amz-lightbox-close" aria-label="Cerrar">&times;</button>' +
			'<img src="' + src + '" alt="' + ( alt || '' ) + '" />';

		document.body.appendChild( overlay );
		document.body.style.overflow = 'hidden';
		requestAnimationFrame( function () {
			overlay.classList.add( 'is-open' );
		} );

		function close() {
			overlay.classList.remove( 'is-open' );
			document.body.style.overflow = '';
			setTimeout( function () {
				overlay.remove();
			}, 200 );
			document.removeEventListener( 'keydown', onKeydown );
		}

		function onKeydown( e ) {
			if ( 'Escape' === e.key ) {
				close();
			}
		}

		overlay.addEventListener( 'click', function ( e ) {
			if ( e.target === overlay || e.target.closest( '.amz-lightbox-close' ) ) {
				close();
			}
		} );
		document.addEventListener( 'keydown', onKeydown );
	}

	document.addEventListener( 'click', function ( e ) {
		var link = e.target.closest( '[data-amz-lightbox]' );
		if ( ! link ) {
			return;
		}
		e.preventDefault();
		var img = link.querySelector( 'img' );
		openLightbox( link.getAttribute( 'href' ), img ? img.getAttribute( 'alt' ) : '' );
	} );
} )();
