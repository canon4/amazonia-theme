<?php
/**
 * Habilitar el envío por defecto en todas las tiendas del marketplace.
 *
 * WCFM exige que cada vendedor active "Enable Shipping" a mano en su panel
 * (WCFM → Ajustes → Envío) y elija un tipo de envío antes de que
 * wcfmmp_is_shipping_enabled() devuelva true para su tienda. Este archivo:
 *
 *  1. Agrega el envío por defecto (habilitado + tipo) a cada tienda NUEVA
 *     creada desde el panel de administrador de comunidad.
 *  2. Ofrece una acción en WP Admin → Amazonia → Envío de tiendas para
 *     aplicar lo mismo, de una sola vez, a las tiendas que ya existen.
 *
 * @package Amazonia_Theme
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// ─── Tipo de envío por defecto ────────────────────────────────────────────────
// Usa el primer tipo que el marketplace tenga habilitado (mismo criterio que
// wcfmmp-view-shipping-settings.php), con "by_country" como último recurso.
function amazonia_default_wcfmmp_shipping_type() {
	if ( ! function_exists( 'wcfmmp_get_shipping_types' ) ) return 'by_country';

	$types = wcfmmp_get_shipping_types();
	unset( $types[''] );

	$marketplace_option_map = [
		'by_country'  => 'woocommerce_wcfmmp_product_shipping_by_country_settings',
		'by_zone'     => 'woocommerce_wcfmmp_product_shipping_by_zone_settings',
		'by_weight'   => 'woocommerce_wcfmmp_product_shipping_by_weight_settings',
		'by_distance' => 'woocommerce_wcfmmp_product_shipping_by_distance_settings',
	];

	foreach ( $marketplace_option_map as $type => $option_name ) {
		$option = get_option( $option_name, [] );
		if ( ! isset( $types[ $type ] ) ) continue;
		if ( empty( $option['enabled'] ) || $option['enabled'] !== 'yes' ) {
			unset( $types[ $type ] );
		}
	}

	$first = array_key_first( $types );
	return $first ?: 'by_country';
}

// ─── Habilitar envío para un vendedor puntual (no pisa configuración previa) ──
function amazonia_enable_vendor_shipping_defaults( $vendor_id ) {
	$shipping = get_user_meta( $vendor_id, '_wcfmmp_shipping', true );
	if ( ! is_array( $shipping ) ) $shipping = [];

	$changed = false;

	if ( empty( $shipping['_wcfmmp_user_shipping_enable'] ) || 'yes' !== $shipping['_wcfmmp_user_shipping_enable'] ) {
		$shipping['_wcfmmp_user_shipping_enable'] = 'yes';
		$changed = true;
	}

	if ( empty( $shipping['_wcfmmp_user_shipping_type'] ) ) {
		$shipping['_wcfmmp_user_shipping_type'] = amazonia_default_wcfmmp_shipping_type();
		$changed = true;
	}

	if ( $changed ) {
		update_user_meta( $vendor_id, '_wcfmmp_shipping', $shipping );
	}

	return $changed;
}

// ─── 1. Tiendas nuevas: habilitar envío al crearlas desde el panel de comunidad ──
add_action( 'amazonia_vendor_created', 'amazonia_enable_vendor_shipping_defaults' );

// ─── 2. Submenú en WP Admin → Amazonia ────────────────────────────────────────
add_action( 'admin_menu', 'amazonia_vendor_shipping_defaults_menu' );
function amazonia_vendor_shipping_defaults_menu() {
	add_submenu_page(
		'amazonia-invite-codes',
		__( 'Envío de tiendas', 'amazonia-theme' ),
		__( 'Envío de tiendas', 'amazonia-theme' ),
		'manage_options',
		'amazonia-vendor-shipping-defaults',
		'amazonia_render_vendor_shipping_defaults_page'
	);
}

// ─── 3. Procesar la acción masiva ─────────────────────────────────────────────
add_action( 'admin_init', 'amazonia_handle_vendor_shipping_defaults_action' );
function amazonia_handle_vendor_shipping_defaults_action() {
	if ( ! current_user_can( 'manage_options' ) ) return;
	if ( ! isset( $_POST['amazonia_enable_all_shipping'] ) ) return;
	if ( ! check_admin_referer( 'amazonia_vendor_shipping_defaults_nonce' ) ) return;

	$vendors = get_users( [ 'role' => 'wcfm_vendor', 'fields' => [ 'ID' ] ] );
	$updated = 0;

	foreach ( $vendors as $vendor ) {
		if ( amazonia_enable_vendor_shipping_defaults( $vendor->ID ) ) {
			$updated++;
		}
	}

	wp_redirect( add_query_arg( [
		'page'    => 'amazonia-vendor-shipping-defaults',
		'updated' => $updated,
		'total'   => count( $vendors ),
	], admin_url( 'admin.php' ) ) );
	exit;
}

// ─── 4. Renderizar el panel ───────────────────────────────────────────────────
function amazonia_render_vendor_shipping_defaults_page() {
	if ( ! current_user_can( 'manage_options' ) ) return;

	$vendors = get_users( [ 'role' => 'wcfm_vendor', 'fields' => [ 'ID', 'display_name' ] ] );
	$enabled_count = 0;
	foreach ( $vendors as $vendor ) {
		if ( function_exists( 'wcfmmp_is_shipping_enabled' ) && wcfmmp_is_shipping_enabled( $vendor->ID ) ) {
			$enabled_count++;
		}
	}
	$total = count( $vendors );
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Envío de tiendas', 'amazonia-theme' ); ?></h1>

		<?php if ( isset( $_GET['updated'] ) ) : ?>
			<div class="notice notice-success is-dismissible">
				<p>
					<?php
					printf(
						/* translators: 1: tiendas actualizadas, 2: total de tiendas */
						esc_html__( 'Envío habilitado en %1$d tienda(s) de %2$d. Las que ya lo tenían activo no se tocaron.', 'amazonia-theme' ),
						(int) $_GET['updated'],
						(int) $_GET['total']
					);
					?>
				</p>
			</div>
		<?php endif; ?>

		<p>
			<?php
			printf(
				/* translators: 1: tiendas con envío activo, 2: total de tiendas */
				esc_html__( 'Actualmente %1$d de %2$d tienda(s) tienen el envío habilitado.', 'amazonia-theme' ),
				(int) $enabled_count,
				(int) $total
			);
			?>
		</p>
		<p><?php esc_html_e( 'Esta acción activa "Enable Shipping" en cada tienda que aún lo tenga apagado y, si no tiene un tipo de envío elegido, le asigna el que el marketplace tiene habilitado por defecto. No modifica tiendas que ya tengan el envío configurado.', 'amazonia-theme' ); ?></p>

		<form method="post">
			<?php wp_nonce_field( 'amazonia_vendor_shipping_defaults_nonce' ); ?>
			<?php submit_button( __( 'Habilitar envío en todas las tiendas', 'amazonia-theme' ), 'primary', 'amazonia_enable_all_shipping' ); ?>
		</form>
	</div>
	<?php
}
