/**
 * store-gallery-admin.js — Botón flotante + modal de la "Galería de la
 * tienda" en el dashboard del vendedor. Vive por fuera de cualquier
 * formulario de WCFM: abre/cierra su propio modal y guarda por AJAX
 * (wp_ajax_amazonia_save_store_gallery), sin depender de cómo WCFM
 * renderice su HTML. Ver inc/store-gallery.php.
 */
( function ( $ ) {
	'use strict';

	function init() {
		var $fab    = $( '#amazonia_gallery_fab' );
		var $modal  = $( '#amazonia_gallery_modal' );
		var $grid   = $( '#amazonia_store_gallery_grid' );
		var $add    = $( '#amazonia_store_gallery_add' );
		var $save   = $( '#amazonia_store_gallery_save' );
		var $status = $( '#amazonia_store_gallery_status' );
		var cfg     = window.amazoniaStoreGallery || {};

		if ( ! $fab.length || ! $modal.length ) {
			return;
		}

		var max = parseInt( $grid.data( 'max' ), 10 ) || 6;

		function currentIds() {
			return $grid.find( '.amazonia-gallery-admin-item' ).map( function () {
				return String( $( this ).data( 'id' ) );
			} ).get();
		}

		function openModal() {
			$modal.removeAttr( 'hidden' );
			requestAnimationFrame( function () {
				$modal.addClass( 'is-open' );
			} );
			document.body.style.overflow = 'hidden';
		}

		function closeModal() {
			$modal.removeClass( 'is-open' );
			document.body.style.overflow = '';
			setTimeout( function () {
				$modal.attr( 'hidden', true );
			}, 200 );
		}

		$fab.on( 'click', openModal );
		$modal.on( 'click', function ( e ) {
			if ( e.target === $modal.get( 0 ) || $( e.target ).closest( '.amazonia-gallery-modal__close' ).length ) {
				closeModal();
			}
		} );
		$( document ).on( 'keydown', function ( e ) {
			if ( 'Escape' === e.key && $modal.hasClass( 'is-open' ) ) {
				closeModal();
			}
		} );

		function addItem( id, thumbUrl ) {
			var $item = $(
				'<div class="amazonia-gallery-admin-item" data-id="' + id + '">' +
					'<img src="' + thumbUrl + '" alt="" width="150" height="150" />' +
					'<button type="button" class="amazonia-gallery-admin-remove" aria-label="Quitar foto">&times;</button>' +
				'</div>'
			);
			$grid.append( $item );
		}

		$grid.on( 'click', '.amazonia-gallery-admin-remove', function () {
			$( this ).closest( '.amazonia-gallery-admin-item' ).remove();
			$status.text( '' );
		} );

		var frame;
		$add.on( 'click', function ( e ) {
			e.preventDefault();

			var remaining = max - currentIds().length;
			if ( remaining <= 0 ) {
				window.alert( cfg.limitMessage || ( 'Puedes subir hasta ' + max + ' fotos.' ) );
				return;
			}

			if ( ! window.wp || ! wp.media ) {
				return;
			}

			if ( frame ) {
				frame.open();
				return;
			}

			frame = wp.media( {
				title: cfg.title || 'Elige las fotos de tu galería',
				button: { text: cfg.button || 'Usar estas fotos' },
				library: { type: 'image' },
				multiple: true,
			} );

			frame.on( 'select', function () {
				var selection = frame.state().get( 'selection' );
				var ids       = currentIds();
				var room      = max - ids.length;

				selection.each( function ( attachment ) {
					if ( room <= 0 ) {
						return;
					}
					attachment = attachment.toJSON();
					if ( ids.indexOf( String( attachment.id ) ) !== -1 ) {
						return;
					}
					var thumb = ( attachment.sizes && attachment.sizes.thumbnail )
						? attachment.sizes.thumbnail.url
						: attachment.url;
					addItem( attachment.id, thumb );
					ids.push( String( attachment.id ) );
					room--;
				} );

				if ( room <= 0 ) {
					window.alert( cfg.limitMessage || ( 'Puedes subir hasta ' + max + ' fotos.' ) );
				}
				$status.text( '' );
			} );

			frame.open();
		} );

		$save.on( 'click', function () {
			$save.prop( 'disabled', true );
			$status.text( '…' );

			$.post( cfg.ajaxUrl, {
				action: 'amazonia_save_store_gallery',
				nonce: cfg.nonce,
				ids: currentIds().join( ',' ),
			} ).done( function ( res ) {
				$status.text( ( res && res.success ) ? ( cfg.saved || 'Guardado.' ) : ( cfg.saveError || 'Error.' ) );
			} ).fail( function () {
				$status.text( cfg.saveError || 'No se pudo guardar la galería. Intenta de nuevo.' );
			} ).always( function () {
				$save.prop( 'disabled', false );
			} );
		} );
	}

	$( init );
} )( jQuery );
