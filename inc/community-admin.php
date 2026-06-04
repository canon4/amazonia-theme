<?php
/**
 * Rol: Administrador de Comunidad (amazonia_community_admin)
 *
 * Responsabilidades del rol:
 *  - Crear y gestionar tiendas/vendedores dentro de su comunidad
 *  - Ver métricas de su comunidad
 *  - NO publicar productos ni gestionar el panel WCFM de vendedor
 *
 * @package Amazonia_Theme
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// ─── 1. Registrar el rol ─────────────────────────────────────────────────────
// add_role es idempotente: si el rol ya existe no hace nada.
add_action( 'init', 'amazonia_register_community_admin_role' );
function amazonia_register_community_admin_role() {
	add_role(
		'amazonia_community_admin',
		__( 'Admin de Comunidad', 'amazonia-theme' ),
		[
			'read'                       => true,
			'amazonia_community_admin'   => true,  // capability propia para checks internos
		]
	);
}

// ─── 2. Asignar el rol correcto al registrarse vía /vendor-register ──────────

// 2a. Filtro en la creación del usuario (primera capa)
add_filter( 'wcfmvm_registration_default_role', 'amazonia_set_registration_role' );
function amazonia_set_registration_role( $role ) {
	return 'amazonia_community_admin';
}

// 2b. Forzar el rol después del registro (segunda capa, por si WCFM lo sobreescribe)
add_action( 'wcfm_membership_registration', 'amazonia_force_community_admin_role', 5, 2 );
function amazonia_force_community_admin_role( $member_id, $form_data ) {
	if ( ! $member_id || is_wp_error( $member_id ) ) return;

	$user = get_userdata( $member_id );
	if ( ! $user ) return;

	// Solo actuar si el usuario NO es ya un administrador de WordPress
	if ( in_array( 'administrator', (array) $user->roles, true ) ) return;

	$user->set_role( 'amazonia_community_admin' );
}

// ─── 3. Impedir que WCFM trate a este rol como vendedor ──────────────────────

// wcfm_is_vendor() verifica el rol 'wcfm_vendor' — nuestro rol no lo tiene,
// así que automáticamente queda excluido del panel de vendedor WCFM.
// Aun así, bloqueamos explícitamente el acceso al dashboard WCFM.
add_action( 'template_redirect', 'amazonia_redirect_community_admin_from_wcfm' );
function amazonia_redirect_community_admin_from_wcfm() {
	if ( ! is_user_logged_in() ) return;

	$user = wp_get_current_user();
	if ( ! in_array( 'amazonia_community_admin', (array) $user->roles, true ) ) return;

	// Si intenta acceder al dashboard WCFM de vendedor → redirigir a su panel
	if ( function_exists( 'is_wcfm_page' ) && is_wcfm_page() ) {
		wp_redirect( home_url( '/community-admin/' ) );
		exit;
	}
}

// ─── 4. Limpiar el rol al desactivar el tema (buena práctica) ────────────────
add_action( 'switch_theme', 'amazonia_remove_community_admin_role' );
function amazonia_remove_community_admin_role() {
	remove_role( 'amazonia_community_admin' );
}

// ─── 5. Helpers ─────────────────────────────────────────────────────────────

/**
 * Verifica si el usuario actual es administrador de comunidad.
 */
function amazonia_is_community_admin( $user_id = 0 ) {
	if ( ! $user_id ) $user_id = get_current_user_id();
	if ( ! $user_id ) return false;
	$user = get_userdata( $user_id );
	return $user && in_array( 'amazonia_community_admin', (array) $user->roles, true );
}

/**
 * Devuelve el ID de la comunidad gestionada por el usuario.
 * Retorna 0 si no tiene comunidad asignada aún.
 */
function amazonia_get_managed_community_id( $user_id = 0 ) {
	if ( ! $user_id ) $user_id = get_current_user_id();
	return (int) get_user_meta( $user_id, 'managed_community_id', true );
}
