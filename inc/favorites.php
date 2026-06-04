<?php
/**
 * Favorites Feature AJAX Handlers
 *
 * @package Amazonia_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Handle Add/Remove Favorite for Logged-in Users
 */
function amazonia_toggle_favorite() {
	check_ajax_referer( 'amazonia-favorites-nonce', 'nonce' );

	if ( ! is_user_logged_in() ) {
		wp_send_json_error( 'User not logged in' );
	}

	$product_id = isset( $_POST['product_id'] ) ? intval( $_POST['product_id'] ) : 0;
	if ( ! $product_id ) {
		wp_send_json_error( 'Invalid product ID' );
	}

	$user_id = get_current_user_id();
	$favorites = get_user_meta( $user_id, 'amazonia_favorites', true );
	if ( ! is_array( $favorites ) ) {
		$favorites = array();
	}

	$is_favorite = false;
	if ( in_array( $product_id, $favorites ) ) {
		// Remove it
		$favorites = array_diff( $favorites, array( $product_id ) );
		$favorites = array_values( $favorites ); // Re-index array
	} else {
		// Add it
		$favorites[] = $product_id;
		$is_favorite = true;
	}

	update_user_meta( $user_id, 'amazonia_favorites', array_unique( $favorites ) );

	wp_send_json_success( array(
		'is_favorite' => $is_favorite,
		'product_id'  => $product_id,
	) );
}
add_action( 'wp_ajax_amazonia_toggle_favorite', 'amazonia_toggle_favorite' );
// We don't need nopriv here because guests use LocalStorage, but we add it to prevent errors just in case.
add_action( 'wp_ajax_nopriv_amazonia_toggle_favorite', function() {
	wp_send_json_error( 'Guest user. Use local storage.' );
} );

/**
 * Handle Fetching Products for Favorites Page
 * Used by guests via localStorage and logged-in users initially
 */
function amazonia_get_favorites() {
	check_ajax_referer( 'amazonia-favorites-nonce', 'nonce' );

	$product_ids = isset( $_POST['product_ids'] ) ? $_POST['product_ids'] : array();
	
	// If the user is logged in, we can optionally ignore what the client sent or merge it.
	// For simplicity, we just trust what the client sends (it's validated below), 
	// OR if logged in and no IDs sent, we fetch from User Meta.
	if ( is_user_logged_in() && empty( $product_ids ) ) {
		$user_id = get_current_user_id();
		$favorites = get_user_meta( $user_id, 'amazonia_favorites', true );
		if ( is_array( $favorites ) ) {
			$product_ids = $favorites;
		}
	}

	if ( ! is_array( $product_ids ) || empty( $product_ids ) ) {
		wp_send_json_success( array(
			'html' => '<div class="col-span-full text-center py-12"><p class="text-slate-500">You haven\'t added any favorites yet.</p></div>'
		) );
	}

	// Sanitize array to integers
	$product_ids = array_map( 'intval', $product_ids );

	// Query products
	$args = array(
		'post_type'      => 'product',
		'post_status'    => 'publish',
		'post__in'       => $product_ids,
		'posts_per_page' => -1,
		'orderby'        => 'post__in', // Keep the order of the ids
	);

	$query = new WP_Query( $args );

	ob_start();

	if ( $query->have_posts() ) {
		while ( $query->have_posts() ) {
			$query->the_post();
			wc_get_template_part( 'content', 'product' );
		}
		wp_reset_postdata();
	} else {
		echo '<div class="col-span-full text-center py-12"><p class="text-slate-500">You haven\'t added any favorites yet.</p></div>';
	}

	$html = ob_get_clean();

	wp_send_json_success( array(
		'html' => $html
	) );
}
add_action( 'wp_ajax_amazonia_get_favorites', 'amazonia_get_favorites' );
add_action( 'wp_ajax_nopriv_amazonia_get_favorites', 'amazonia_get_favorites' );
