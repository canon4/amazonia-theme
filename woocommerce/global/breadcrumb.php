<?php
/**
 * Shop breadcrumb
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 2.3.0
 */

defined( 'ABSPATH' ) || exit;

if ( empty( $breadcrumb ) ) {
	return;
}

$total = count( $breadcrumb );

echo wp_kses_post( $wrap_before );

foreach ( $breadcrumb as $key => $crumb ) {
	$is_last = ( $key + 1 === $total );

	echo wp_kses_post( $before );

	if ( ! empty( $crumb[1] ) && ! $is_last ) {
		echo '<a href="' . esc_url( $crumb[1] ) . '" class="text-slate-500 dark:text-slate-400 hover:text-primary transition-colors no-underline text-sm font-medium">' . esc_html( $crumb[0] ) . '</a>';
	} else {
		echo '<span class="text-slate-800 dark:text-slate-200 text-sm font-semibold">' . esc_html( $crumb[0] ) . '</span>';
	}

	echo wp_kses_post( $after );

	if ( ! $is_last ) {
		echo wp_kses_post( $delimiter );
	}
}

echo wp_kses_post( $wrap_after );