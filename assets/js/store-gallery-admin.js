/**
 * store-gallery-admin.js — Uploader de la "Galería de la tienda" en el panel
 * del vendedor (WCFM Dashboard > Ajustes > Store), inyectado por
 * inc/store-gallery.php. Usa el selector de medios nativo de WordPress
 * (wp.media), igual que cualquier otro uploader del admin.
 */
( function ( $ ) {
	'use strict';

	function init() {
		var $grid  = $( '#amazonia_store_gallery_grid' );
		var $input = $( '#amazonia_store_gallery_input' );
		var $add   = $( '#amazonia_store_gallery_add' );

		if ( ! $grid.length || ! window.wp || ! wp.media ) {
			return;
		}

		var max = parseInt( $grid.data( 'max' ), 10 ) || 6;
		var cfg = window.amazoniaStoreGallery || {};

		function currentIds() {
			var val = $input.val();
			return val ? val.split( ',' ).filter( Boolean ) : [];
		}

		function setIds( ids ) {
			$input.val( ids.join( ',' ) );
		}

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
			var $item = $( this ).closest( '.amazonia-gallery-admin-item' );
			var id    = String( $item.data( 'id' ) );
			$item.remove();
			setIds( currentIds().filter( function ( existing ) {
				return existing !== id;
			} ) );
		} );

		var frame;
		$add.on( 'click', function ( e ) {
			e.preventDefault();

			var remaining = max - currentIds().length;
			if ( remaining <= 0 ) {
				window.alert( cfg.limitMessage || ( 'Puedes subir hasta ' + max + ' fotos.' ) );
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

				setIds( ids );

				if ( room <= 0 ) {
					window.alert( cfg.limitMessage || ( 'Puedes subir hasta ' + max + ' fotos.' ) );
				}
			} );

			frame.open();
		} );
	}

	$( init );
} )( jQuery );
