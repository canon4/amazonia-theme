<?php
/**
 * Lógica AJAX del Panel de Administrador de Comunidad
 *
 * Acciones disponibles:
 *  - amazonia_create_vendor   → crea nuevo usuario wcfm_vendor vinculado a la comunidad
 *  - amazonia_link_vendor     → asigna community_id a un vendor existente
 *  - amazonia_search_vendors  → búsqueda de usuarios para vincular
 *
 * @package Amazonia_Theme
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// ─── Registrar acciones AJAX ─────────────────────────────────────────────────
add_action( 'wp_ajax_amazonia_create_vendor',        'amazonia_ajax_create_vendor' );
add_action( 'wp_ajax_amazonia_link_vendor',          'amazonia_ajax_link_vendor' );
add_action( 'wp_ajax_amazonia_search_vendors',       'amazonia_ajax_search_vendors' );
add_action( 'wp_ajax_amazonia_save_community_info',  'amazonia_ajax_save_community_info' );
add_action( 'wp_ajax_amazonia_upload_image',         'amazonia_ajax_upload_image' );

// ─── Localizar datos JS en el panel ─────────────────────────────────────────
add_action( 'wp_enqueue_scripts', 'amazonia_enqueue_community_admin_panel' );
function amazonia_enqueue_community_admin_panel() {
	if ( ! is_page_template( 'template-community-admin.php' ) ) return;

	wp_enqueue_style(
		'amazonia-community-admin',
		get_template_directory_uri() . '/assets/css/community-admin.css',
		[],
		'1.3.0'
	);

	wp_enqueue_script(
		'amazonia-community-admin-js',
		get_template_directory_uri() . '/assets/js/community-admin.js',
		[ 'jquery' ],
		'1.3.0',
		true
	);

	wp_localize_script( 'amazonia-community-admin-js', 'amazoniaCommunityAdmin', [
		'ajaxUrl'      => admin_url( 'admin-ajax.php' ),
		'nonce'        => wp_create_nonce( 'amazonia_community_admin_nonce' ),
		'uploadAction' => 'amazonia_upload_image',
		'i18n'         => [
			'creating'     => __( 'Creando tienda...', 'amazonia-theme' ),
			'linking'      => __( 'Vinculando...', 'amazonia-theme' ),
			'searching'    => __( 'Buscando...', 'amazonia-theme' ),
			'saving'       => __( 'Guardando...', 'amazonia-theme' ),
			'confirm_link' => __( '¿Vincular este usuario a tu comunidad?', 'amazonia-theme' ),
		],
	] );
}

// ─── AJAX: Subir imagen al media library ─────────────────────────────────────
function amazonia_ajax_upload_image() {
	if ( ! check_ajax_referer( 'amazonia_community_admin_nonce', 'nonce', false ) ) {
		wp_send_json_error( [ 'message' => __( 'Nonce inválido.', 'amazonia-theme' ) ] );
	}
	if ( ! amazonia_is_community_admin() ) {
		wp_send_json_error( [ 'message' => __( 'Sin permisos.', 'amazonia-theme' ) ] );
	}

	require_once ABSPATH . 'wp-admin/includes/image.php';
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';

	$attachment_id = media_handle_upload( 'file', 0 );

	if ( is_wp_error( $attachment_id ) ) {
		wp_send_json_error( [ 'message' => $attachment_id->get_error_message() ] );
	}

	wp_send_json_success( [
		'id'            => $attachment_id,
		'url'           => wp_get_attachment_url( $attachment_id ),
		'thumbnail_url' => wp_get_attachment_image_url( $attachment_id, 'thumbnail' ),
	] );
}

// ─── Helper: verificar que el usuario es admin de comunidad ─────────────────
function amazonia_verify_panel_access() {
	if ( ! is_user_logged_in() ) {
		wp_send_json_error( [ 'message' => __( 'Acceso denegado.', 'amazonia-theme' ) ] );
	}
	if ( ! check_ajax_referer( 'amazonia_community_admin_nonce', 'nonce', false ) ) {
		wp_send_json_error( [ 'message' => __( 'Token inválido.', 'amazonia-theme' ) ] );
	}
	if ( ! amazonia_is_community_admin() ) {
		wp_send_json_error( [ 'message' => __( 'No tienes permisos para esta acción.', 'amazonia-theme' ) ] );
	}
	$community_id = amazonia_get_managed_community_id();
	if ( ! $community_id ) {
		wp_send_json_error( [ 'message' => __( 'No tienes una comunidad asignada.', 'amazonia-theme' ) ] );
	}
	return $community_id;
}

// ─── AJAX: Crear nuevo vendedor/tienda ───────────────────────────────────────
function amazonia_ajax_create_vendor() {
	$community_id = amazonia_verify_panel_access();

	$store_name = sanitize_text_field( $_POST['store_name'] ?? '' );
	$email      = sanitize_email( $_POST['email'] ?? '' );
	$first_name = sanitize_text_field( $_POST['first_name'] ?? '' );
	$last_name  = sanitize_text_field( $_POST['last_name'] ?? '' );

	if ( empty( $store_name ) || empty( $email ) ) {
		wp_send_json_error( [ 'message' => __( 'El nombre de la tienda y el email son obligatorios.', 'amazonia-theme' ) ] );
	}

	if ( ! is_email( $email ) ) {
		wp_send_json_error( [ 'message' => __( 'El email no es válido.', 'amazonia-theme' ) ] );
	}

	if ( email_exists( $email ) ) {
		wp_send_json_error( [ 'message' => __( 'Ya existe un usuario con ese email. Usa la opción "Vincular tienda existente".', 'amazonia-theme' ) ] );
	}

	// Generar username desde email
	$username = sanitize_user( current( explode( '@', $email ) ), true );
	$append   = 1;
	$base     = $username;
	while ( username_exists( $username ) ) {
		$username = $base . $append++;
	}

	$password = wp_generate_password( 12, false );

	$user_id = wp_insert_user( [
		'user_login'   => $username,
		'user_email'   => $email,
		'user_pass'    => $password,
		'display_name' => $store_name,
		'first_name'   => $first_name,
		'last_name'    => $last_name,
		'role'         => 'wcfm_vendor',
	] );

	if ( is_wp_error( $user_id ) ) {
		wp_send_json_error( [ 'message' => $user_id->get_error_message() ] );
	}

	// Vincular a la comunidad
	update_user_meta( $user_id, 'community_id', $community_id );
	update_user_meta( $user_id, 'store_name', $store_name );

	// Inicializar perfil WCFM básico
	update_user_meta( $user_id, 'wcfmmp_profile_settings', [
		'store_name'       => $store_name,
		'shop_description' => '',
		'address'          => [],
	] );

	// Ocultar barra de admin al vendedor
	update_user_meta( $user_id, 'show_admin_bar_front', false );

	// Enviar credenciales por email
	$blogname = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );
	$subject  = sprintf( __( '[%s] Tus credenciales de acceso', 'amazonia-theme' ), $blogname );
	$message  = sprintf(
		__( "Hola %s,\n\nTu tienda \"%s\" ha sido creada en %s.\n\nUsuario: %s\nContraseña: %s\nAcceder: %s\n\n¡Bienvenido/a!", 'amazonia-theme' ),
		$first_name ?: $store_name,
		$store_name,
		$blogname,
		$username,
		$password,
		wc_get_page_permalink( 'myaccount' )
	);
	wp_mail( $email, $subject, $message );

	wp_send_json_success( [
		'message'  => sprintf( __( 'Tienda "%s" creada. Se enviaron las credenciales a %s.', 'amazonia-theme' ), $store_name, $email ),
		'user_id'  => $user_id,
		'username' => $username,
		'store'    => $store_name,
		'email'    => $email,
	] );
}

// ─── AJAX: Vincular vendedor existente a la comunidad ────────────────────────
function amazonia_ajax_link_vendor() {
	$community_id = amazonia_verify_panel_access();

	$user_id = absint( $_POST['user_id'] ?? 0 );
	if ( ! $user_id ) {
		wp_send_json_error( [ 'message' => __( 'Usuario no válido.', 'amazonia-theme' ) ] );
	}

	$user = get_userdata( $user_id );
	if ( ! $user ) {
		wp_send_json_error( [ 'message' => __( 'Usuario no encontrado.', 'amazonia-theme' ) ] );
	}

	// Solo se pueden vincular usuarios con rol wcfm_vendor
	if ( ! in_array( 'wcfm_vendor', (array) $user->roles, true ) ) {
		wp_send_json_error( [ 'message' => __( 'El usuario no es un vendedor. Solo puedes vincular tiendas existentes.', 'amazonia-theme' ) ] );
	}

	// No vincular si ya pertenece a otra comunidad
	$existing = (int) get_user_meta( $user_id, 'community_id', true );
	if ( $existing && $existing !== $community_id ) {
		wp_send_json_error( [ 'message' => __( 'Esta tienda ya pertenece a otra comunidad.', 'amazonia-theme' ) ] );
	}

	update_user_meta( $user_id, 'community_id', $community_id );

	wp_send_json_success( [
		'message' => sprintf(
			__( 'La tienda "%s" fue vinculada a tu comunidad.', 'amazonia-theme' ),
			wcfm_get_vendor_store_name( $user_id )
		),
		'user_id' => $user_id,
	] );
}

// ─── AJAX: Guardar información de la comunidad ───────────────────────────────
function amazonia_ajax_save_community_info() {
	$community_id = amazonia_verify_panel_access();

	$post = get_post( $community_id );
	if ( ! $post || $post->post_type !== 'comunidad' ) {
		wp_send_json_error( [ 'message' => __( 'Comunidad no encontrada.', 'amazonia-theme' ) ] );
	}

	$nombre          = sanitize_text_field( $_POST['nombre'] ?? '' );
	$descripcion     = sanitize_textarea_field( $_POST['descripcion'] ?? '' );
	$historia        = sanitize_textarea_field( $_POST['historia'] ?? '' );
	$pais            = sanitize_text_field( $_POST['pais'] ?? '' );
	$departamento    = sanitize_text_field( $_POST['departamento'] ?? '' );
	$municipio       = sanitize_text_field( $_POST['municipio'] ?? '' );
	$categoria       = sanitize_text_field( $_POST['categoria'] ?? '' );
	$logo_url        = esc_url_raw( $_POST['logo_url'] ?? '' );
	$banner_url      = esc_url_raw( $_POST['banner_url'] ?? '' );
	$video_url       = esc_url_raw( $_POST['video_url'] ?? '' );
	$fundacion       = sanitize_text_field( $_POST['fundacion'] ?? '' );
	$num_familias    = sanitize_text_field( $_POST['num_familias'] ?? '' );
	$instagram       = esc_url_raw( $_POST['instagram'] ?? '' );
	$facebook        = esc_url_raw( $_POST['facebook'] ?? '' );
	$certificaciones    = sanitize_text_field( $_POST['certificaciones'] ?? '' );
	$storytelling_img_1 = esc_url_raw( $_POST['storytelling_img_1'] ?? '' );
	$storytelling_img_2 = esc_url_raw( $_POST['storytelling_img_2'] ?? '' );
	$storytelling_img_3 = esc_url_raw( $_POST['storytelling_img_3'] ?? '' );

	// Galería: array de attachment IDs enviado como JSON string
	$galeria_raw = isset( $_POST['galeria_ids'] ) ? wp_unslash( $_POST['galeria_ids'] ) : '[]';
	$galeria_ids = json_decode( $galeria_raw, true );
	$galeria_ids = is_array( $galeria_ids ) ? array_values( array_map( 'absint', $galeria_ids ) ) : [];

	// Valores: array de {icono, texto} enviado como JSON string
	$valores_raw = isset( $_POST['valores'] ) ? wp_unslash( $_POST['valores'] ) : '[]';
	$valores = json_decode( $valores_raw, true );
	if ( ! is_array( $valores ) ) $valores = [];
	$valores = array_values( array_filter(
		array_slice( $valores, 0, 4 ),
		fn( $v ) => ! empty( $v['texto'] )
	) );
	$valores = array_map( fn( $v ) => [
		'icono' => sanitize_text_field( $v['icono'] ?? 'eco' ),
		'texto' => sanitize_text_field( $v['texto'] ?? '' ),
	], $valores );

	if ( $nombre ) {
		$result = wp_update_post( [
			'ID'         => $community_id,
			'post_title' => $nombre,
		], true );
		if ( is_wp_error( $result ) ) {
			wp_send_json_error( [ 'message' => $result->get_error_message() ] );
		}
	}

	update_post_meta( $community_id, '_comunidad_descripcion', $descripcion );
	update_post_meta( $community_id, '_comunidad_historia', $historia );
	update_post_meta( $community_id, '_comunidad_pais', $pais );
	update_post_meta( $community_id, '_comunidad_departamento', $departamento );
	update_post_meta( $community_id, '_comunidad_municipio', $municipio );
	update_post_meta( $community_id, '_comunidad_categoria', $categoria );
	update_post_meta( $community_id, '_comunidad_logo_url', $logo_url );
	update_post_meta( $community_id, '_comunidad_banner_url', $banner_url );
	update_post_meta( $community_id, '_comunidad_galeria', wp_json_encode( $galeria_ids ) );
	update_post_meta( $community_id, '_comunidad_video_url', $video_url );
	update_post_meta( $community_id, '_comunidad_fundacion', $fundacion );
	update_post_meta( $community_id, '_comunidad_num_familias', $num_familias );
	update_post_meta( $community_id, '_comunidad_valores', wp_json_encode( $valores ) );
	update_post_meta( $community_id, '_comunidad_instagram', $instagram );
	update_post_meta( $community_id, '_comunidad_facebook', $facebook );
	update_post_meta( $community_id, '_comunidad_certificaciones',    $certificaciones );
	update_post_meta( $community_id, '_comunidad_storytelling_img_1', $storytelling_img_1 );
	update_post_meta( $community_id, '_comunidad_storytelling_img_2', $storytelling_img_2 );
	update_post_meta( $community_id, '_comunidad_storytelling_img_3', $storytelling_img_3 );

	wp_send_json_success( [
		'message' => __( 'Información actualizada correctamente.', 'amazonia-theme' ),
		'nombre'  => $nombre ?: $post->post_title,
	] );
}

// ─── AJAX: Buscar vendedores para vincular ───────────────────────────────────
function amazonia_ajax_search_vendors() {
	amazonia_verify_panel_access();

	$term = sanitize_text_field( $_POST['term'] ?? '' );
	if ( strlen( $term ) < 2 ) {
		wp_send_json_success( [] );
	}

	$users = get_users( [
		'role'       => 'wcfm_vendor',
		'search'     => '*' . $term . '*',
		'search_columns' => [ 'user_login', 'user_email', 'display_name' ],
		'number'     => 10,
	] );

	$results = [];
	foreach ( $users as $user ) {
		$community = (int) get_user_meta( $user->ID, 'community_id', true );
		$results[] = [
			'id'        => $user->ID,
			'store'     => wcfm_get_vendor_store_name( $user->ID ) ?: $user->display_name,
			'email'     => $user->user_email,
			'community' => $community ? get_the_title( $community ) : null,
		];
	}

	wp_send_json_success( $results );
}
