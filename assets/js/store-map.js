/**
 * store-map.js — Inicializa un mapa Leaflet real en el bloque "Dónde estamos"
 * de la página de tienda (wcfm/store/wcfmmp-view-store.php), en reemplazo del
 * panel decorativo estático.
 *
 * Solo actúa si el vendedor fijó su ubicación en "Mi tienda > Ajustes >
 * Location" (WCFM guarda esas coordenadas como data-lat/data-lng en el
 * contenedor .amz-map vía PHP). Si no hay coordenadas, se deja el panel
 * decorativo tal cual (no todos los vendedores han configurado su mapa).
 */
( function () {
	function init() {
		var el = document.querySelector( '.amz-map[data-lat][data-lng]' );
		if ( ! el || ! window.L ) {
			return;
		}

		var lat = parseFloat( el.getAttribute( 'data-lat' ) );
		var lng = parseFloat( el.getAttribute( 'data-lng' ) );
		if ( isNaN( lat ) || isNaN( lng ) ) {
			return;
		}

		// Quita el ícono decorativo y el fondo degradado: el mapa real ocupa su lugar.
		el.classList.add( 'amz-map--live' );
		el.innerHTML = '';

		var map = L.map( el, {
			center: [ lat, lng ],
			zoom: 14,
			scrollWheelZoom: false,
			attributionControl: true,
		} );

		L.tileLayer( 'https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
			maxZoom: 20,
			subdomains: 'abcd',
			attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> &copy; <a href="https://carto.com/attributions">CARTO</a>',
		} ).addTo( map );

		var storeName = el.getAttribute( 'data-name' ) || '';
		L.marker( [ lat, lng ] ).addTo( map ).bindPopup( storeName );
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}
} )();
