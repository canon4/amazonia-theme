<?php
/**
 * Sistema de códigos de invitación para el registro de administradores de comunidad.
 *
 * Flujo:
 *  1. El super admin genera códigos desde WP Admin → Amazonia → Códigos de invitación
 *  2. El admin de comunidad ingresa el código en el formulario /vendor-register
 *  3. Se valida antes de procesar el registro (hook wcfm_form_custom_validation)
 *  4. Al registrarse con éxito el código se marca como "usado" (hook wcfm_membership_registration)
 *
 * @package Amazonia_Theme
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// ─── Opciones de WordPress ──────────────────────────────────────────────────
// 'amazonia_invite_codes' → array asociativo keyed por código:
// [ 'CODE123' => [ 'status' => 'active'|'used', 'created_at' => '', 'used_at' => '', 'used_by' => 0, 'label' => '' ] ]

// ─── 1. Mostrar el campo en el formulario de registro ────────────────────────
add_action( 'begin_wcfm_membership_registration_form', 'amazonia_render_invite_code_field' );
function amazonia_render_invite_code_field() {
	?>
	<div class="amazonia-invite-code-wrap">
		<p class="wcfm_title amazonia_invite_code_label">
			<label for="amazonia_invite_code">
				<?php esc_html_e( 'Código de invitación', 'amazonia-theme' ); ?>
				<span class="required" style="color:#ef4444">*</span>
			</label>
		</p>
		<input
			type="text"
			id="amazonia_invite_code"
			name="amazonia_invite_code"
			class="wcfm-text wcfm_ele"
			placeholder="<?php esc_attr_e( 'Ingresa el código recibido', 'amazonia-theme' ); ?>"
			autocomplete="off"
			data-required="1"
			data-required_message="<?php esc_attr_e( 'El código de invitación es obligatorio.', 'amazonia-theme' ); ?>"
		/>
		<p class="amazonia-invite-hint">
			<?php esc_html_e( 'Este código fue entregado por el equipo de Amazonia Market.', 'amazonia-theme' ); ?>
		</p>
	</div>
	<?php
}

// ─── 2. Validar el código antes de crear el usuario ─────────────────────────
add_filter( 'wcfm_form_custom_validation', 'amazonia_validate_invite_code', 10, 2 );
function amazonia_validate_invite_code( $form_data, $form_type ) {
	if ( 'vendor_registration' !== $form_type ) {
		return $form_data;
	}

	$code = isset( $form_data['amazonia_invite_code'] )
		? strtoupper( trim( sanitize_text_field( $form_data['amazonia_invite_code'] ) ) )
		: '';

	if ( empty( $code ) ) {
		return [
			'has_error' => true,
			'message'   => __( 'El código de invitación es obligatorio.', 'amazonia-theme' ),
		];
	}

	$codes = get_option( 'amazonia_invite_codes', [] );

	if ( ! isset( $codes[ $code ] ) ) {
		return [
			'has_error' => true,
			'message'   => __( 'El código de invitación no es válido.', 'amazonia-theme' ),
		];
	}

	if ( 'active' !== $codes[ $code ]['status'] ) {
		return [
			'has_error' => true,
			'message'   => __( 'El código de invitación ya fue utilizado.', 'amazonia-theme' ),
		];
	}

	return $form_data;
}

// ─── 3. Marcar el código como usado tras el registro exitoso ────────────────
add_action( 'wcfm_membership_registration', 'amazonia_consume_invite_code', 10, 2 );
function amazonia_consume_invite_code( $member_id, $form_data ) {
	$code = isset( $form_data['amazonia_invite_code'] )
		? strtoupper( trim( sanitize_text_field( $form_data['amazonia_invite_code'] ) ) )
		: '';

	if ( empty( $code ) ) return;

	$codes = get_option( 'amazonia_invite_codes', [] );

	if ( isset( $codes[ $code ] ) && 'active' === $codes[ $code ]['status'] ) {
		$codes[ $code ]['status']  = 'used';
		$codes[ $code ]['used_at'] = current_time( 'mysql' );
		$codes[ $code ]['used_by'] = $member_id;
		update_option( 'amazonia_invite_codes', $codes );
	}
}

// ─── 4. Menú de administración ───────────────────────────────────────────────
add_action( 'admin_menu', 'amazonia_invite_codes_menu' );
function amazonia_invite_codes_menu() {
	add_menu_page(
		__( 'Códigos de Invitación', 'amazonia-theme' ),
		__( 'Amazonia', 'amazonia-theme' ),
		'manage_options',
		'amazonia-invite-codes',
		'amazonia_render_invite_codes_page',
		'dashicons-tickets-alt',
		56
	);
}

// ─── 5. Procesar acciones del panel (generar / revocar) ─────────────────────
add_action( 'admin_init', 'amazonia_handle_invite_code_actions' );
function amazonia_handle_invite_code_actions() {
	if ( ! current_user_can( 'manage_options' ) ) return;

	// Generar nuevo código
	if ( isset( $_POST['amazonia_generate_code'] ) && check_admin_referer( 'amazonia_invite_codes_nonce' ) ) {
		$label  = sanitize_text_field( $_POST['amazonia_code_label'] ?? '' );
		$code   = amazonia_generate_unique_code();
		$codes  = get_option( 'amazonia_invite_codes', [] );

		$codes[ $code ] = [
			'status'     => 'active',
			'created_at' => current_time( 'mysql' ),
			'used_at'    => '',
			'used_by'    => 0,
			'label'      => $label,
		];

		update_option( 'amazonia_invite_codes', $codes );
		wp_redirect( add_query_arg( [ 'page' => 'amazonia-invite-codes', 'generated' => '1' ], admin_url( 'admin.php' ) ) );
		exit;
	}

	// Revocar código
	if ( isset( $_GET['amazonia_revoke'] ) && isset( $_GET['_wpnonce'] ) && wp_verify_nonce( $_GET['_wpnonce'], 'amazonia_revoke_' . $_GET['amazonia_revoke'] ) ) {
		$code  = strtoupper( sanitize_text_field( $_GET['amazonia_revoke'] ) );
		$codes = get_option( 'amazonia_invite_codes', [] );

		if ( isset( $codes[ $code ] ) ) {
			$codes[ $code ]['status'] = 'revoked';
			update_option( 'amazonia_invite_codes', $codes );
		}

		wp_redirect( add_query_arg( [ 'page' => 'amazonia-invite-codes', 'revoked' => '1' ], admin_url( 'admin.php' ) ) );
		exit;
	}
}

// ─── 6. Renderizar el panel de administración ────────────────────────────────
function amazonia_render_invite_codes_page() {
	if ( ! current_user_can( 'manage_options' ) ) return;
	$codes = get_option( 'amazonia_invite_codes', [] );
	include get_template_directory() . '/templates/admin-invite-codes.php';
}

// ─── Utilidad: generar código único de 8 caracteres ─────────────────────────
function amazonia_generate_unique_code() {
	$chars  = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789'; // sin 0,O,1,I para evitar confusión
	$length = 8;
	$codes  = get_option( 'amazonia_invite_codes', [] );

	do {
		$code = '';
		for ( $i = 0; $i < $length; $i++ ) {
			$code .= $chars[ random_int( 0, strlen( $chars ) - 1 ) ];
		}
		// Formato legible: XXXX-XXXX
		$code = substr( $code, 0, 4 ) . '-' . substr( $code, 4 );
	} while ( isset( $codes[ $code ] ) );

	return $code;
}
