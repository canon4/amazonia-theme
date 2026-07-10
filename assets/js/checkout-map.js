/**
 * checkout-map.js — Mejora el mapa "Lugar de entrega" del checkout (WCFM/Leaflet).
 *
 * WCFM usa Leaflet con teselas de OpenStreetMap estandar, que se ven planas
 * (p. ej. una zona de bosque queda toda verde). Aqui cambiamos el proveedor de
 * teselas por CARTO Voyager: mismo dato de OSM pero con calles y etiquetas mas
 * legibles y aspecto moderno. Es gratis y NO requiere API key.
 *
 * Se hace parcheando L.TileLayer ANTES de que WCFM cree el mapa (lo crea dentro
 * de un setTimeout de 1000 ms), en vez de editar el plugin, para no perder el
 * cambio en cada actualizacion de WCFM.
 *
 * Para usar SATELITE en su lugar, cambia TILES por la URL de Esri comentada
 * mas abajo (ese proveedor no trae etiquetas de calles).
 */
( function () {
	// Calles con detalle (por defecto):
	var TILES = 'https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png';
	var OPTS = {
		maxZoom: 20,
		subdomains: 'abcd',
		attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> &copy; <a href="https://carto.com/attributions">CARTO</a>'
	};

	// --- Alternativa SATELITE (Esri World Imagery). Para usarla, comenta las
	//     dos constantes de arriba y descomenta estas:
	// var TILES = 'https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}';
	// var OPTS = { maxZoom: 19, attribution: 'Tiles &copy; Esri' };

	function isPatched() {
		return !!( window.L && L.TileLayer && L.TileLayer.__amazoniaPatched );
	}

	function patch() {
		if ( ! window.L || ! L.TileLayer || L.TileLayer.__amazoniaPatched ) {
			return;
		}
		var origInit = L.TileLayer.prototype.initialize;
		L.TileLayer.prototype.initialize = function ( url, options ) {
			// Solo reemplazamos la capa base de OSM que pone WCFM.
			if ( typeof url === 'string' && url.indexOf( 'tile.openstreetmap.org' ) !== -1 ) {
				url = TILES;
				options = L.extend( {}, OPTS, options || {} );
			}
			return origInit.call( this, url, options );
		};
		L.TileLayer.__amazoniaPatched = true;
	}

	// Parchea cuanto antes; si Leaflet aun no cargo, reintenta hasta ~5 s.
	patch();
	if ( ! isPatched() ) {
		var tries = 0;
		var timer = setInterval( function () {
			patch();
			if ( isPatched() || ++tries > 100 ) {
				clearInterval( timer );
			}
		}, 50 );
	}
} )();
