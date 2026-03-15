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
		'thumbnail_image_width' => 300,
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
	// Enqueue main stylesheet (style.css fallback)
	wp_enqueue_style( 'amazonia-theme-style', get_stylesheet_uri(), array(), '1.0.0' );

	// Enqueue compiled main.css
	wp_enqueue_style( 'amazonia-main-style', get_template_directory_uri() . '/assets/css/main.css', array(), '1.0.0' );

	// Enqueue main.js
	wp_enqueue_script( 'amazonia-main-js', get_template_directory_uri() . '/assets/js/main.js', array('jquery'), '1.0.0', true );

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'amazonia_theme_scripts' );
