/**
 * site-content-admin.js — Pantalla "Contenido del sitio" (WP Admin → Amazonia).
 * Pestañas, selector de imagen única y carrusel ordenable (wp.media).
 * Ver inc/site-content.php y templates/admin-site-content.php.
 */
( function ( $ ) {
	'use strict';

	var cfg = window.amazoniaSiteContent || {};

	/* ── Pestañas ─────────────────────────────────────────────────── */
	function initTabs() {
		var $tabs   = $( '.asc-tabs .nav-tab' );
		var $panels = $( '.asc-panel' );
		var $active = $( '#asc-active-tab' );

		function show( key ) {
			$tabs.removeClass( 'nav-tab-active' ).filter( '[data-tab="' + key + '"]' ).addClass( 'nav-tab-active' );
			$panels.attr( 'hidden', true ).filter( '[data-panel="' + key + '"]' ).removeAttr( 'hidden' );
			$active.val( key );
		}

		$tabs.on( 'click', function ( e ) {
			e.preventDefault();
			show( $( this ).data( 'tab' ) );
			if ( window.history && history.replaceState ) {
				history.replaceState( null, '', '#' + $( this ).data( 'tab' ) );
			}
		} );

		var hash = window.location.hash.replace( '#', '' );
		if ( hash && $tabs.filter( '[data-tab="' + hash + '"]' ).length ) {
			show( hash );
		}
	}

	/* ── Imagen única ─────────────────────────────────────────────── */
	function initImageFields() {
		$( '.asc-image' ).each( function () {
			var $box     = $( this );
			var $input   = $box.find( '.asc-image__input' );
			var $preview = $box.find( '.asc-image__preview' );
			var $remove  = $box.find( '.asc-image__remove' );
			var fallback = $box.data( 'fallback' );
			var frame;

			function setPreview( url ) {
				$preview.find( 'img' ).remove();
				if ( url ) {
					$preview.removeClass( 'is-default is-empty' ).prepend( $( '<img alt="" />' ).attr( 'src', url ) );
				} else if ( fallback ) {
					$preview.removeClass( 'is-empty' ).addClass( 'is-default' ).prepend( $( '<img alt="" />' ).attr( 'src', fallback ) );
				} else {
					$preview.removeClass( 'is-default' ).addClass( 'is-empty' );
				}
			}

			$box.find( '.asc-image__choose' ).on( 'click', function ( e ) {
				e.preventDefault();
				if ( frame ) {
					frame.open();
					return;
				}
				frame = wp.media( {
					title: cfg.chooseTitle,
					button: { text: cfg.chooseBtn },
					library: { type: 'image' },
					multiple: false,
				} );
				frame.on( 'select', function () {
					var att = frame.state().get( 'selection' ).first().toJSON();
					var url = ( att.sizes && att.sizes.medium ) ? att.sizes.medium.url : att.url;
					$input.val( att.id );
					setPreview( url );
					$remove.removeAttr( 'hidden' );
				} );
				frame.open();
			} );

			$remove.on( 'click', function () {
				$input.val( '' );
				setPreview( '' );
				$remove.attr( 'hidden', true );
			} );
		} );
	}

	/* ── Carrusel ─────────────────────────────────────────────────── */
	function initSlides() {
		var $list = $( '#asc-slides' );
		if ( ! $list.length ) {
			return;
		}

		var name    = $list.data( 'name' );
		var counter = Date.now(); // Índices únicos; PHP conserva el orden del DOM.
		var frame;

		$list.sortable( { handle: '.asc-slide__handle', items: '> li', tolerance: 'pointer' } );

		function esc( s ) {
			return $( '<div/>' ).text( s ).html().replace( /"/g, '&quot;' );
		}

		function addSlide( att ) {
			var i     = counter++;
			var thumb = ( att.sizes && att.sizes.thumbnail ) ? att.sizes.thumbnail.url : att.url;
			var $li   = $(
				'<li class="asc-slide">' +
					'<span class="asc-slide__handle dashicons dashicons-menu" aria-hidden="true"></span>' +
					'<img src="' + esc( thumb ) + '" alt="" />' +
					'<div class="asc-slide__fields">' +
						'<input type="hidden" name="' + name + '[' + i + '][id]" value="' + parseInt( att.id, 10 ) + '" />' +
						'<label>' + esc( cfg.altLabel ) + '<input type="text" name="' + name + '[' + i + '][alt]" value="' + esc( att.alt || '' ) + '" /></label>' +
						'<label>' + esc( cfg.linkLabel ) + '<input type="url" name="' + name + '[' + i + '][link]" value="" placeholder="https://" /></label>' +
					'</div>' +
					'<button type="button" class="button-link button-link-delete asc-slide__remove">' + esc( cfg.removeLabel ) + '</button>' +
				'</li>'
			);
			$list.append( $li );
		}

		$list.on( 'click', '.asc-slide__remove', function () {
			$( this ).closest( '.asc-slide' ).remove();
		} );

		$( '#asc-slides-add' ).on( 'click', function ( e ) {
			e.preventDefault();

			if ( $list.children().length >= cfg.maxSlides ) {
				window.alert( cfg.maxMessage );
				return;
			}

			if ( ! frame ) {
				frame = wp.media( {
					title: cfg.slidesTitle,
					button: { text: cfg.slidesBtn },
					library: { type: 'image' },
					multiple: true,
				} );
				frame.on( 'select', function () {
					var room = cfg.maxSlides - $list.children().length;
					frame.state().get( 'selection' ).each( function ( model ) {
						if ( room <= 0 ) {
							return;
						}
						addSlide( model.toJSON() );
						room--;
					} );
					if ( room <= 0 ) {
						window.alert( cfg.maxMessage );
					}
					$( '.asc-notice-default' ).remove();
				} );
			}
			frame.open();
		} );
	}

	$( function () {
		initTabs();
		initImageFields();
		initSlides();
	} );
} )( jQuery );
