<?php
/**
 * Amazonia Theme functions and definitions
 *
 * @package Amazonia_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Includes
require_once get_template_directory() . '/inc/invite-codes.php';
require_once get_template_directory() . '/inc/community-admin.php';
require_once get_template_directory() . '/inc/community-cpt.php';
require_once get_template_directory() . '/inc/community-admin-panel.php';
require_once get_template_directory() . '/inc/vendor-whatsapp.php';

/**
 * Theme Setup
 */
function amazonia_theme_setup() {
	// Add default posts and comments RSS feed links to head.
	add_theme_support( 'automatic-feed-links' );

	// Let WordPress manage the document title.
	add_theme_support( 'title-tag' );

	// Enable support for Post Thumbnails on posts and pages.
	add_theme_support( 'post-thumbnails' );

	// Register Navigation Menus
	register_nav_menus(
		array(
			'menu-1' => esc_html__( 'Primary Menu', 'amazonia-theme' ),
			'footer' => esc_html__( 'Footer Menu', 'amazonia-theme' ),
		)
	);

	// WooCommerce Theme Support
	add_theme_support( 'woocommerce', array(
		'thumbnail_image_width' => 400,
		'gallery_thumbnail_image_width' => 100,
		'single_image_width' => 600,
	) );
	add_theme_support( 'wc-product-gallery-zoom' );
	add_theme_support( 'wc-product-gallery-lightbox' );
	add_theme_support( 'wc-product-gallery-slider' );

	// Register Custom Image Sizes
	add_image_size( 'amazonia-hero', 1920, 1080, true );
	add_image_size( 'amazonia-product-card', 400, 400, true );
}
add_action( 'after_setup_theme', 'amazonia_theme_setup' );

/**
 * ─── Cache busting de assets ────────────────────────────────────────────────
 *
 * El .htaccess de la raíz de WordPress sirve CSS/JS con "Expires: 1 month" y
 * las fuentes woff2 con "1 year". Si un asset se encola con una versión
 * literal escrita a mano, al desplegar un cambio el navegador de un usuario
 * recurrente sigue sirviendo el archivo viejo durante todo ese tiempo.
 *
 * Por eso NINGÚN asset del tema debe encolarse con una versión escrita a mano:
 * usa amazonia_style() / amazonia_script(), que derivan la versión del mtime
 * del archivo. El pipeline de despliegue solo actualiza el mtime de lo que
 * realmente cambió, así que un cambio en main.css no invalida tailwind.css.
 *
 * Ver docs/08_cicd_despliegue.md.
 */

/**
 * Versión de un asset del tema, para cache busting.
 *
 * @param string $rel Ruta relativa a la raíz del tema (ej. 'assets/css/main.css').
 * @return string Timestamp de modificación, o la versión del tema si falta el archivo.
 */
function amazonia_asset_ver( $rel ) {
	$path = get_template_directory() . '/' . ltrim( $rel, '/' );
	return file_exists( $path ) ? (string) filemtime( $path ) : wp_get_theme()->get( 'Version' );
}

/**
 * URL de un asset del tema con su versión ya incrustada como query string.
 * Para casos donde no se puede usar wp_enqueue_* (preloads, filtros de tag).
 *
 * @param string $rel Ruta relativa a la raíz del tema.
 * @return string URL absoluta con ?ver=.
 */
function amazonia_asset_url( $rel ) {
	return add_query_arg( 'ver', amazonia_asset_ver( $rel ), get_template_directory_uri() . '/' . ltrim( $rel, '/' ) );
}

/**
 * Encola un estilo del tema con su versión calculada a partir del mtime.
 *
 * @param string $handle Handle de WordPress.
 * @param string $rel    Ruta relativa a la raíz del tema.
 * @param array  $deps   Dependencias.
 */
function amazonia_style( $handle, $rel, $deps = array() ) {
	wp_enqueue_style( $handle, get_template_directory_uri() . '/' . $rel, $deps, amazonia_asset_ver( $rel ) );
}

/**
 * Encola un script del tema con su versión calculada a partir del mtime.
 *
 * @param string $handle    Handle de WordPress.
 * @param string $rel       Ruta relativa a la raíz del tema.
 * @param array  $deps      Dependencias.
 * @param bool   $in_footer Cargar en el footer.
 */
function amazonia_script( $handle, $rel, $deps = array(), $in_footer = true ) {
	wp_enqueue_script( $handle, get_template_directory_uri() . '/' . $rel, $deps, amazonia_asset_ver( $rel ), $in_footer );
}

/**
 * Preload de fuentes críticas (Work Sans e Inter).
 * Debe ejecutarse con prioridad 1 para emitirse ANTES de los estilos.
 * Esto elimina el FOUT (Flash of Unstyled Text) y reduce el LCP percibido.
 *
 * Las URLs llevan la misma versión que el @font-face de main.css (que el
 * despliegue reescribe con el mismo mtime), para que el navegador reconozca
 * preload y @font-face como el mismo recurso y no descargue la fuente dos veces.
 */
add_action( 'wp_head', function() {
	// Work Sans — fuente de títulos y navegación (crítica above-the-fold)
	echo '<link rel="preload" as="font" type="font/woff2" crossorigin href="' . esc_url( amazonia_asset_url( 'assets/fonts/work-sans-latin.woff2' ) ) . '">' . "\n";
	// Inter — fuente de cuerpo (crítica para legibilidad inmediata)
	echo '<link rel="preload" as="font" type="font/woff2" crossorigin href="' . esc_url( amazonia_asset_url( 'assets/fonts/inter-latin.woff2' ) ) . '">' . "\n";
}, 1 );

/**
 * Carga Material Symbols de forma asíncrona (non-blocking).
 * El woff2 pesa 3.8 MB — con font-display:block bloquea el render hasta 3 s.
 * Estrategia: imprimir primero como media=print y en onload cambiar a all,
 * así el browser puede pintar el HTML sin esperar la fuente de iconos.
 * Los iconos serán invisibles el primer frame y se muestran en cuanto carga.
 */
add_filter( 'style_loader_tag', function( $tag, $handle ) {
	if ( $handle !== 'material-symbols' ) return $tag;
	// La versión se recalcula aquí porque este filtro reconstruye el <link> entero
	// y descarta el href que ya había generado wp_enqueue_style().
	$href = esc_url( amazonia_asset_url( 'assets/css/material-symbols.css' ) );
	return '<link rel="preload" as="style" href="' . $href . '" onload="this.onload=null;this.rel=\'stylesheet\'">' . "\n"
		 . '<noscript><link rel="stylesheet" href="' . $href . '"></noscript>' . "\n";
}, 10, 2 );

/**
 * Enqueue scripts and styles.
 */
function amazonia_theme_scripts() {
	// Tailwind CSS compilado localmente (sin JS runtime, sin CDN)
	amazonia_style( 'amazonia-tailwind', 'assets/css/tailwind.css' );

	// Material Symbols — self-hosted para evitar dependencia de Google Fonts en el servidor.
	// El archivo woff2 está en assets/fonts/material-symbols-outlined.woff2
	// Work Sans, Inter y Outfit también son self-hosted (ver main.css).
	amazonia_style( 'material-symbols', 'assets/css/material-symbols.css' );

	// Enqueue main stylesheet (style.css fallback)
	wp_enqueue_style( 'amazonia-theme-style', get_stylesheet_uri(), array(), amazonia_asset_ver( 'style.css' ) );

	// Enqueue compiled main.css (incluye @font-face de Work Sans, Inter, Outfit)
	amazonia_style( 'amazonia-main-style', 'assets/css/main.css' );

	// Enqueue navigation.js
	amazonia_script( 'amazonia-navigation-js', 'assets/js/navigation.js' );

	// Enqueue asset constants (ES module — sets window.AMAZONIA_ASSETS)
	amazonia_script( 'amazonia-assets-constants', 'assets/js/constants/assets.js' );

	// Enqueue favorites.js
	amazonia_script( 'amazonia-favorites-js', 'assets/js/favorites.js', array( 'jquery' ) );
	$user_favorites = array();
	if ( is_user_logged_in() ) {
		$meta = get_user_meta( get_current_user_id(), 'amazonia_favorites', true );
		if ( is_array( $meta ) ) {
			$user_favorites = $meta;
		}
	}
	wp_localize_script( 'amazonia-favorites-js', 'amazonia_favorites_data', array(
		'ajax_url' => admin_url( 'admin-ajax.php' ),
		'nonce'    => wp_create_nonce( 'amazonia-favorites-nonce' ),
		'is_logged_in' => is_user_logged_in() ? '1' : '0',
		'user_favorites' => $user_favorites
	) );

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply', false, [], false, true );
	}
}
add_action( 'wp_enqueue_scripts', 'amazonia_theme_scripts' );

// Add type="module" to the asset constants script tag
add_filter( 'script_loader_tag', function ( $tag, $handle, $src ) {
	if ( 'amazonia-assets-constants' === $handle ) {
		return '<script type="module" src="' . esc_url( $src ) . '"></script>' . "\n";
	}
	return $tag;
}, 10, 3 );

// Evita que WooCommerce redirija al producto cuando la búsqueda devuelve un único resultado.
add_filter( 'woocommerce_redirect_single_search_result', '__return_false' );

// Breadcrumb global: barra verde con separador chevron en todas las páginas WooCommerce.
// La página Comunidades no se ve afectada porque llama woocommerce_breadcrumb() con sus propios args.
add_filter( 'woocommerce_breadcrumb_defaults', function( $defaults ) {
	$defaults['delimiter']   = '<span class="material-symbols-outlined text-[16px] text-slate-400 select-none" aria-hidden="true">chevron_right</span>';
	$defaults['wrap_before'] = '<nav class="woocommerce-breadcrumb w-full border-b border-slate-200 dark:border-slate-700" aria-label="' . esc_attr__( 'Ruta de navegación', 'amazonia-theme' ) . '"><div class="max-w-[1400px] mx-auto px-4 sm:px-8 py-3 flex items-center gap-1.5 flex-wrap">';
	$defaults['wrap_after']  = '</div></nav>';
	$defaults['before']      = '';
	$defaults['after']       = '';
	return $defaults;
} );

/**
 * Encola el CSS personalizado del dashboard WCFM.
 * Solo se carga en páginas que usen la plantilla template-wcfm-dashboard.php
 * para no agregar peso innecesario al resto del sitio.
 */
function amazonia_enqueue_wcfm_dashboard_styles() {
	if ( is_page_template( 'template-wcfm-dashboard.php' ) ) {
		amazonia_style( 'amazonia-wcfm-dashboard', 'assets/css/wcfm-dashboard.css' );
	}
}
add_action( 'wp_enqueue_scripts', 'amazonia_enqueue_wcfm_dashboard_styles' );

/**
 * Agrega el enlace "Volver a la Tienda" dentro del header de WCFM.
 * Se inyecta antes de los iconos del panel derecho usando el hook del plugin.
 * Reemplaza el botón flotante que existía en template-wcfm-dashboard.php.
 */
function amazonia_wcfm_back_to_store_button() {
	?>
	<a href="<?php echo esc_url( home_url( '/' ) ); ?>"
	   class="wcfm_header_back_to_store">
		<i class="wcfmfa fa-arrow-left"></i>
		<span><?php esc_html_e( 'Tienda', 'amazonia-theme' ); ?></span>
	</a>
	<?php
}
add_action( 'wcfm_before_header_panel_item', 'amazonia_wcfm_back_to_store_button' );

/**
 * Encola el CSS personalizado del formulario de registro de vendedor.
 * Solo se carga en la página con slug "vendor-register".
 */
function amazonia_enqueue_vendor_register_styles() {
	if ( is_page( 'vendor-register' ) ) {
		amazonia_style( 'amazonia-vendor-register', 'assets/css/vendor-register.css' );
	}
}
add_action( 'wp_enqueue_scripts', 'amazonia_enqueue_vendor_register_styles' );

/**
 * Encola el CSS del checkout (layout horizontal de los formularios de
 * facturación/envío). Solo se carga en la página de finalizar compra.
 */
function amazonia_enqueue_checkout_styles() {
	if ( function_exists( 'is_checkout' ) && is_checkout() ) {
		amazonia_style( 'amazonia-checkout', 'assets/css/checkout.css', array( 'amazonia-main-style' ) );
	}
}
add_action( 'wp_enqueue_scripts', 'amazonia_enqueue_checkout_styles' );

/**
 * Mejora el mapa "Lugar de entrega" del checkout (WCFM/Leaflet): reemplaza las
 * teselas planas de OpenStreetMap por CARTO Voyager (mas detalle, sin API key).
 * Se carga en el footer para parchear L.TileLayer antes de que WCFM cree el mapa.
 */
function amazonia_enqueue_checkout_map_script() {
	if ( function_exists( 'is_checkout' ) && is_checkout() ) {
		amazonia_script( 'amazonia-checkout-map', 'assets/js/checkout-map.js' );
	}
}
add_action( 'wp_enqueue_scripts', 'amazonia_enqueue_checkout_map_script' );

/**
 * Corrige un bug de WC Frontend Manager: cuando el admin nunca elige
 * explicitamente la libreria de mapas en Ajustes > WCFM Marketplace, el
 * plugin usa por defecto la cadena mal escrita "leaftlet" en vez de
 * "leaflet" (ver wc-frontend-manager/views/settings/wcfm-view-wcfmmarketplace-settings.php).
 * Como la condicion que muestra el selector de ubicacion en "Mi tienda >
 * Ajustes > Location" solo compara con "leaflet" (bien escrito), ese bug
 * hace que el mapa para fijar la ubicacion de la tienda nunca aparezca
 * para los vendedores mientras no se configure una API key de Google Maps.
 * Aqui fijamos la opcion correctamente una sola vez (sin pisar la eleccion
 * del admin si ya la configuro).
 */
function amazonia_fix_wcfm_map_lib_default() {
	$options = get_option( 'wcfmmp_marketplace_options', array() );
	if ( empty( $options['wcfm_map_lib'] ) ) {
		$options['wcfm_map_lib'] = 'leaflet';
		update_option( 'wcfmmp_marketplace_options', $options );
	}
}
add_action( 'init', 'amazonia_fix_wcfm_map_lib_default' );

/**
 * Encola el CSS del carrito (resumen "Total del carrito"). Evita que la
 * direccion de envio por tienda se desborde de la caja. Solo se carga en el
 * carrito, no en el checkout.
 */
function amazonia_enqueue_cart_styles() {
	if ( function_exists( 'is_cart' ) && is_cart() ) {
		amazonia_style( 'amazonia-cart', 'assets/css/cart.css', array( 'amazonia-main-style' ) );
	}
}
add_action( 'wp_enqueue_scripts', 'amazonia_enqueue_cart_styles' );

/**
 * Encola el CSS de los perfiles (diseño "Amazonia Perfiles").
 * Cubre las dos pantallas hermanas: el single del CPT "comunidad" (Pantalla A)
 * y la página de tienda de WCFM (Pantalla B). Solo ahí, para no pesar en el
 * resto del sitio.
 */
function amazonia_enqueue_community_profile_styles() {
	$is_store = function_exists( 'wcfmmp_is_store_page' ) && wcfmmp_is_store_page();

	if ( is_singular( 'comunidad' ) || $is_store ) {
		amazonia_style( 'amazonia-community-profile', 'assets/css/community-profile.css', array( 'amazonia-main-style' ) );
	}
}
add_action( 'wp_enqueue_scripts', 'amazonia_enqueue_community_profile_styles' );

/**
 * Mapa real (Leaflet) en el bloque "Dónde estamos" de la página de tienda,
 * reemplazando el panel decorativo cuando el vendedor fijó su ubicación en
 * "Mi tienda > Ajustes > Location" (ver amazonia_fix_wcfm_map_lib_default).
 * Reutiliza la libreria Leaflet que WCFM ya trae incluida (sin API key).
 */
function amazonia_enqueue_store_map_script() {
	if ( function_exists( 'wcfmmp_is_store_page' ) && wcfmmp_is_store_page() ) {
		$leaflet_base = WP_PLUGIN_URL . '/wc-frontend-manager/includes/libs/leaflet/';
		wp_enqueue_style( 'wcfm-leaflet-map-style-css', $leaflet_base . 'leaflet.css', array(), '1.9.4' );
		wp_enqueue_script( 'wcfm-leaflet-map-js', $leaflet_base . 'leaflet.js', array( 'jquery' ), '1.9.4', true );
		amazonia_script( 'amazonia-store-map', 'assets/js/store-map.js', array( 'wcfm-leaflet-map-js' ) );
	}
}
add_action( 'wp_enqueue_scripts', 'amazonia_enqueue_store_map_script' );

/**
 * Register widget area.
 */
function amazonia_widgets_init() {
	register_sidebar(
		array(
			'name'          => esc_html__( 'Sidebar', 'amazonia-theme' ),
			'id'            => 'sidebar-1',
			'description'   => esc_html__( 'Add widgets here.', 'amazonia-theme' ),
			'before_widget' => '<section id="%1$s" class="widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="widget-title">',
			'after_title'   => '</h2>',
		)
	);
}
add_action( 'widgets_init', 'amazonia_widgets_init' );

/**
 * Custom WooCommerce Wrappers
 */
remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );
remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10 );

if ( ! function_exists( 'amazonia_woocommerce_wrapper_before' ) ) {
	function amazonia_woocommerce_wrapper_before() {
		echo '<main id="primary" class="site-main">';
	}
}
add_action( 'woocommerce_before_main_content', 'amazonia_woocommerce_wrapper_before', 10 );

if ( ! function_exists( 'amazonia_woocommerce_wrapper_after' ) ) {
	function amazonia_woocommerce_wrapper_after() {
		echo '</main>';
	}
}
add_action( 'woocommerce_after_main_content', 'amazonia_woocommerce_wrapper_after', 10 );

/**
 * Unhook default WooCommerce single product functions.
 * This allows us to use core hooks in our custom template so plugins (like WCFM) can inject buttons (e.g., Edit Product)
 * without duplicating the title, price, etc.
 */
function amazonia_unhook_single_product_defaults() {
	if ( is_product() ) {
		// Before Summary
		remove_action( 'woocommerce_before_single_product_summary', 'woocommerce_show_product_sale_flash', 10 );
		remove_action( 'woocommerce_before_single_product_summary', 'woocommerce_show_product_images', 20 );

		// Summary
		remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_title', 5 );
		remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_rating', 10 );
		remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 10 );
		remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 20 );
		remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30 );
		remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40 );
		remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_sharing', 50 );
		
		// After Summary
		remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_product_data_tabs', 10 );
		remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_upsell_display', 15 );
		remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20 );
	}
}
add_action( 'wp', 'amazonia_unhook_single_product_defaults' );

/**
 * Override standard template correctly for WCFM Dashboard
 */
add_filter( 'template_include', function( $template ) {
	global $post;
	// Si la página tiene el shortcode de WCFM, forzamos la plantilla correcta.
	if ( is_singular() && is_a( $post, 'WP_Post' ) && ( has_shortcode( $post->post_content, 'wc_frontend_manager' ) || has_shortcode( $post->post_content, 'wcfm_store_manager' ) ) ) {
		$new_template = locate_template( array( 'template-wcfm-dashboard.php' ) );
		if ( ! empty( $new_template ) ) {
			return $new_template;
		}
	}
	return $template;
}, 99 );

/**
 * Auto-create "About Us" page when theme loads to fix 404
 */
add_action('init', function() {
    $page_slug = 'about-us';
    $page_check = get_page_by_path($page_slug);
    if (!$page_check) {
        wp_insert_post(array(
            'post_type'   => 'page',
            'post_title'  => 'About Us',
            'post_name'   => $page_slug,
            'post_status' => 'publish',
            'post_author' => 1,
        ));
    }
});

/**
 * Auto-create página "Categorías" y asignarle la plantilla page-categorias.php.
 */
add_action( 'init', function() {
    $page_slug  = 'categorias';
    $page_check = get_page_by_path( $page_slug );
    if ( ! $page_check ) {
        $page_id = wp_insert_post( array(
            'post_type'   => 'page',
            'post_title'  => 'Categorías',
            'post_name'   => $page_slug,
            'post_status' => 'publish',
            'post_author' => 1,
        ) );
        if ( $page_id && ! is_wp_error( $page_id ) ) {
            update_post_meta( $page_id, '_wp_page_template', 'page-categorias.php' );
        }
    } elseif ( get_post_meta( $page_check->ID, '_wp_page_template', true ) !== 'page-categorias.php' ) {
        update_post_meta( $page_check->ID, '_wp_page_template', 'page-categorias.php' );
    }
} );

/**
 * Custom Shortcodes
 */
require_once get_template_directory() . '/shortcodes.php';

/**
 * Favorites Feature Logic
 */
require_once get_template_directory() . '/inc/favorites.php';

/**
 * WooCommerce Mini Cart AJAX Fragments
 */
add_filter( 'woocommerce_add_to_cart_fragments', 'amazonia_cart_count_fragments', 10, 1 );
function amazonia_cart_count_fragments( $fragments ) {
    $fragments['span.cart-count'] = '<span class="cart-count absolute -top-1 -right-1 bg-primary text-[10px] font-bold text-white h-4 w-4 rounded-full flex items-center justify-center">' . wp_kses_data( WC()->cart->get_cart_contents_count() ) . '</span>';
    return $fragments;
}
