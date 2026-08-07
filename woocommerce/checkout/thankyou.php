<?php
/**
 * Thankyou page
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/thankyou.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 8.1.0
 *
 * Personalizado para Amazonia: la maquetación vive en
 * template-parts/checkout/order-received.php (diseño Tailwind).
 *
 * @var WC_Order $order
 */

defined( 'ABSPATH' ) || exit;

if ( $order ) {
	do_action( 'woocommerce_before_thankyou', $order->get_id() );
}

// La vista ya dibuja los productos, totales y direcciones del pedido, así que
// se desengancha la tabla por defecto para no duplicar el contenido.
remove_action( 'woocommerce_thankyou', 'woocommerce_order_details_table', 10 );

// El resto de hooks (woocommerce_thankyou, woocommerce_thankyou_{pasarela} y
// los de order/customer details) se disparan DENTRO de la vista, para que el
// contenido que inyectan los plugins quede dentro del contenedor maquetado.
get_template_part( 'template-parts/checkout/order-received', null, array( 'order' => $order ) );
