<?php
/**
 * Amazonia Theme functions and definitions
 *
 * @package Amazonia_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

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
 * Enqueue scripts and styles.
 */
function amazonia_theme_scripts() {
	// Tailwind JS via CDN and config
	wp_enqueue_script( 'tailwind-cdn', 'https://cdn.tailwindcss.com?plugins=forms,container-queries', array(), null, false );
	wp_enqueue_script( 'tailwind-config', get_template_directory_uri() . '/assets/js/tailwind-config.js', array('tailwind-cdn'), '1.0.0', false );

	// Google Fonts & Material Symbols
	wp_enqueue_style( 'google-font-work-sans', 'https://fonts.googleapis.com/css2?family=Work+Sans:wght@300;400;500;600;700;900&display=swap', array(), null );
	wp_enqueue_style( 'material-symbols', 'https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined', array(), null );

	// Enqueue main stylesheet (style.css fallback)
	wp_enqueue_style( 'amazonia-theme-style', get_stylesheet_uri(), array(), '1.0.0' );

	// Enqueue compiled main.css
	wp_enqueue_style( 'amazonia-main-style', get_template_directory_uri() . '/assets/css/main.css', array(), '1.0.0' );

	// Enqueue main.js
	wp_enqueue_script( 'amazonia-main-js', get_template_directory_uri() . '/assets/js/main.js', array('jquery'), '1.0.0', true );

	// Enqueue navigation.js
	wp_enqueue_script( 'amazonia-navigation-js', get_template_directory_uri() . '/assets/js/navigation.js', array(), '1.0.0', true );

	// Enqueue favorites.js
	wp_enqueue_script( 'amazonia-favorites-js', get_template_directory_uri() . '/assets/js/favorites.js', array('jquery'), '1.0.0', true );
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
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'amazonia_theme_scripts' );

/**
 * Encola el CSS personalizado del dashboard WCFM.
 * Solo se carga en páginas que usen la plantilla template-wcfm-dashboard.php
 * para no agregar peso innecesario al resto del sitio.
 */
function amazonia_enqueue_wcfm_dashboard_styles() {
	if ( is_page_template( 'template-wcfm-dashboard.php' ) ) {
		wp_enqueue_style(
			'amazonia-wcfm-dashboard',
			get_template_directory_uri() . '/assets/css/wcfm-dashboard.css',
			array(),
			'1.0.0'
		);
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
