<?php
/**
 * Custom Post Type: Comunidad
 *
 * Registra el CPT 'comunidad', sus meta boxes de edición,
 * y la vinculación con administradores de comunidad
 * (meta 'managed_community_id' en el perfil del usuario).
 *
 * @package Amazonia_Theme
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// ─── 1. Registrar CPT ────────────────────────────────────────────────────────
add_action( 'init', 'amazonia_register_comunidad_cpt' );
function amazonia_register_comunidad_cpt() {
	$labels = [
		'name'               => __( 'Comunidades', 'amazonia-theme' ),
		'singular_name'      => __( 'Comunidad', 'amazonia-theme' ),
		'add_new'            => __( 'Nueva Comunidad', 'amazonia-theme' ),
		'add_new_item'       => __( 'Agregar Comunidad', 'amazonia-theme' ),
		'edit_item'          => __( 'Editar Comunidad', 'amazonia-theme' ),
		'new_item'           => __( 'Nueva Comunidad', 'amazonia-theme' ),
		'view_item'          => __( 'Ver Comunidad', 'amazonia-theme' ),
		'search_items'       => __( 'Buscar Comunidades', 'amazonia-theme' ),
		'not_found'          => __( 'No se encontraron comunidades.', 'amazonia-theme' ),
		'not_found_in_trash' => __( 'No hay comunidades en la papelera.', 'amazonia-theme' ),
		'menu_name'          => __( 'Comunidades', 'amazonia-theme' ),
	];

	register_post_type( 'comunidad', [
		'labels'              => $labels,
		'public'              => true,
		'publicly_queryable'  => true,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'show_in_rest'        => false,
		'query_var'           => true,
		'rewrite'             => [ 'slug' => 'comunidad', 'with_front' => false ],
		'capability_type'     => 'post',
		'capabilities'        => [
			'create_posts' => 'manage_options', // solo super admin crea comunidades
		],
		'map_meta_cap'        => true,
		'has_archive'         => false,
		'hierarchical'        => false,
		'menu_position'       => 25,
		'menu_icon'           => 'dashicons-groups',
		'supports'            => [ 'title', 'thumbnail' ],
	] );
}

// ─── 2. Meta boxes en el editor de Comunidad ─────────────────────────────────
add_action( 'add_meta_boxes', 'amazonia_comunidad_meta_boxes' );
function amazonia_comunidad_meta_boxes() {
	add_meta_box(
		'amazonia_comunidad_details',
		__( 'Detalles de la Comunidad', 'amazonia-theme' ),
		'amazonia_render_comunidad_details_box',
		'comunidad',
		'normal',
		'high'
	);
	add_meta_box(
		'amazonia_comunidad_admins',
		__( 'Administradores de la Comunidad', 'amazonia-theme' ),
		'amazonia_render_comunidad_admins_box',
		'comunidad',
		'side',
		'default'
	);
}

// ─── 3. Renderizar meta box: Detalles ────────────────────────────────────────
function amazonia_render_comunidad_details_box( $post ) {
	wp_nonce_field( 'amazonia_comunidad_save', 'amazonia_comunidad_nonce' );

	$meta = [
		'descripcion'  => get_post_meta( $post->ID, '_comunidad_descripcion', true ),
		'historia'     => get_post_meta( $post->ID, '_comunidad_historia', true ),
		'pais'         => get_post_meta( $post->ID, '_comunidad_pais', true ),
		'departamento' => get_post_meta( $post->ID, '_comunidad_departamento', true ),
		'municipio'    => get_post_meta( $post->ID, '_comunidad_municipio', true ),
		'categoria'    => get_post_meta( $post->ID, '_comunidad_categoria', true ),
		'logo_url'     => get_post_meta( $post->ID, '_comunidad_logo_url', true ),
	];
	?>
	<style>
		.comunidad-meta-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px; }
		.comunidad-meta-grid label, .comunidad-meta-full label { display:block; font-weight:600; font-size:12px; color:#374151; margin-bottom:4px; text-transform:uppercase; letter-spacing:.04em; }
		.comunidad-meta-grid input, .comunidad-meta-full input,
		.comunidad-meta-full textarea, .comunidad-meta-grid select { width:100%; padding:8px 10px; border:1px solid #d1d5db; border-radius:6px; font-size:13px; }
		.comunidad-meta-full { margin-bottom:16px; }
		.comunidad-meta-full textarea { min-height:80px; resize:vertical; }
		.comunidad-logo-preview { max-width:80px; border-radius:8px; margin-top:6px; display:block; }
	</style>

	<div class="comunidad-meta-full">
		<label><?php esc_html_e( 'Descripción corta', 'amazonia-theme' ); ?></label>
		<textarea name="comunidad_descripcion" rows="3"><?php echo esc_textarea( $meta['descripcion'] ); ?></textarea>
	</div>

	<div class="comunidad-meta-full">
		<label><?php esc_html_e( 'Historia / Sobre la comunidad', 'amazonia-theme' ); ?></label>
		<textarea name="comunidad_historia" rows="5"><?php echo esc_textarea( $meta['historia'] ); ?></textarea>
	</div>

	<div class="comunidad-meta-grid">
		<div>
			<label><?php esc_html_e( 'País', 'amazonia-theme' ); ?></label>
			<input type="text" name="comunidad_pais" value="<?php echo esc_attr( $meta['pais'] ); ?>" placeholder="Ej: Colombia" />
		</div>
		<div>
			<label><?php esc_html_e( 'Departamento / Región', 'amazonia-theme' ); ?></label>
			<input type="text" name="comunidad_departamento" value="<?php echo esc_attr( $meta['departamento'] ); ?>" placeholder="Ej: Amazonas" />
		</div>
		<div>
			<label><?php esc_html_e( 'Municipio', 'amazonia-theme' ); ?></label>
			<input type="text" name="comunidad_municipio" value="<?php echo esc_attr( $meta['municipio'] ); ?>" placeholder="Ej: Leticia" />
		</div>
		<div>
			<label><?php esc_html_e( 'Categoría', 'amazonia-theme' ); ?></label>
			<input type="text" name="comunidad_categoria" value="<?php echo esc_attr( $meta['categoria'] ); ?>" placeholder="Ej: Productor Local, Artesanías" />
		</div>
	</div>

	<div class="comunidad-meta-full">
		<label><?php esc_html_e( 'URL del Logo', 'amazonia-theme' ); ?></label>
		<input type="url" name="comunidad_logo_url" id="comunidad_logo_url" value="<?php echo esc_attr( $meta['logo_url'] ); ?>" placeholder="https://..." />
		<?php if ( $meta['logo_url'] ) : ?>
			<img src="<?php echo esc_url( $meta['logo_url'] ); ?>" class="comunidad-logo-preview" alt="Logo" loading="lazy" width="80" height="80" />
		<?php endif; ?>
		<p class="description" style="margin-top:4px;">
			<?php esc_html_e( 'También puedes usar la imagen destacada del post como logo.', 'amazonia-theme' ); ?>
		</p>
	</div>
	<?php
}

// ─── 4. Renderizar meta box: Admins vinculados ───────────────────────────────
function amazonia_render_comunidad_admins_box( $post ) {
	$admins = amazonia_get_community_admins( $post->ID );
	?>
	<style>
		.comunidad-admin-list { list-style:none; margin:0; padding:0; }
		.comunidad-admin-list li { display:flex; align-items:center; gap:8px; padding:6px 0; border-bottom:1px solid #f1f5f9; font-size:13px; }
		.comunidad-admin-list li:last-child { border-bottom:none; }
		.comunidad-admin-avatar { border-radius:50%; }
	</style>
	<?php if ( empty( $admins ) ) : ?>
		<p style="color:#94a3b8;font-size:13px;margin:0;">
			<?php esc_html_e( 'Ningún administrador asignado aún.', 'amazonia-theme' ); ?>
		</p>
		<p style="font-size:12px;color:#cbd5e1;">
			<?php esc_html_e( 'Para asignar un admin, edita su perfil de usuario y selecciona esta comunidad.', 'amazonia-theme' ); ?>
		</p>
	<?php else : ?>
		<ul class="comunidad-admin-list">
			<?php foreach ( $admins as $admin ) : ?>
				<li>
					<?php echo get_avatar( $admin->ID, 28, '', '', [ 'class' => 'comunidad-admin-avatar' ] ); ?>
					<span><?php echo esc_html( $admin->display_name ); ?></span>
					<a href="<?php echo esc_url( get_edit_user_link( $admin->ID ) ); ?>" style="margin-left:auto;font-size:11px;color:#4ade80;">
						<?php esc_html_e( 'Editar', 'amazonia-theme' ); ?>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>
	<?php
}

// ─── 5. Guardar meta boxes ───────────────────────────────────────────────────
add_action( 'save_post_comunidad', 'amazonia_save_comunidad_meta' );
function amazonia_save_comunidad_meta( $post_id ) {
	if ( ! isset( $_POST['amazonia_comunidad_nonce'] ) ) return;
	if ( ! wp_verify_nonce( $_POST['amazonia_comunidad_nonce'], 'amazonia_comunidad_save' ) ) return;
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
	if ( ! current_user_can( 'edit_post', $post_id ) ) return;

	$fields = [
		'comunidad_descripcion'  => '_comunidad_descripcion',
		'comunidad_historia'     => '_comunidad_historia',
		'comunidad_pais'         => '_comunidad_pais',
		'comunidad_departamento' => '_comunidad_departamento',
		'comunidad_municipio'    => '_comunidad_municipio',
		'comunidad_categoria'    => '_comunidad_categoria',
		'comunidad_logo_url'     => '_comunidad_logo_url',
	];

	foreach ( $fields as $post_key => $meta_key ) {
		if ( isset( $_POST[ $post_key ] ) ) {
			$value = ( $post_key === 'comunidad_logo_url' )
				? esc_url_raw( $_POST[ $post_key ] )
				: sanitize_textarea_field( $_POST[ $post_key ] );
			update_post_meta( $post_id, $meta_key, $value );
		}
	}
}

// ─── 6. Campo en perfil de usuario: Selector de comunidad ────────────────────
add_action( 'show_user_profile', 'amazonia_user_community_field' );
add_action( 'edit_user_profile', 'amazonia_user_community_field' );
function amazonia_user_community_field( $user ) {
	if ( ! current_user_can( 'manage_options' ) ) return;
	if ( ! in_array( 'amazonia_community_admin', (array) $user->roles, true ) ) return;

	$current = (int) get_user_meta( $user->ID, 'managed_community_id', true );
	$communities = get_posts( [
		'post_type'      => 'comunidad',
		'post_status'    => 'publish',
		'posts_per_page' => 200,         // dropdown admin — número alto pero acotado
		'orderby'        => 'title',
		'order'          => 'ASC',
		'no_found_rows'  => true,
	] );
	?>
	<h2><?php esc_html_e( 'Amazonia Market', 'amazonia-theme' ); ?></h2>
	<table class="form-table">
		<tr>
			<th><label for="managed_community_id"><?php esc_html_e( 'Comunidad gestionada', 'amazonia-theme' ); ?></label></th>
			<td>
				<select name="managed_community_id" id="managed_community_id">
					<option value=""><?php esc_html_e( '— Sin asignar —', 'amazonia-theme' ); ?></option>
					<?php foreach ( $communities as $community ) : ?>
						<option value="<?php echo esc_attr( $community->ID ); ?>" <?php selected( $current, $community->ID ); ?>>
							<?php echo esc_html( $community->post_title ); ?>
						</option>
					<?php endforeach; ?>
				</select>
				<p class="description">
					<?php esc_html_e( 'Esta persona administrará las tiendas y vendedores de la comunidad seleccionada.', 'amazonia-theme' ); ?>
				</p>
			</td>
		</tr>
	</table>
	<?php
}

// ─── 7. Guardar el selector de comunidad en el perfil ────────────────────────
add_action( 'personal_options_update', 'amazonia_save_user_community_field' );
add_action( 'edit_user_profile_update', 'amazonia_save_user_community_field' );
function amazonia_save_user_community_field( $user_id ) {
	if ( ! current_user_can( 'manage_options' ) ) return;
	if ( ! isset( $_POST['managed_community_id'] ) ) return;

	$community_id = absint( $_POST['managed_community_id'] );
	update_user_meta( $user_id, 'managed_community_id', $community_id );
}

// ─── 8. Helpers ─────────────────────────────────────────────────────────────

/**
 * Devuelve todos los admins vinculados a una comunidad.
 *
 * @param int $community_id ID del post de tipo 'comunidad'
 * @return WP_User[]
 */
function amazonia_get_community_admins( $community_id ) {
	return get_users( [
		'role'       => 'amazonia_community_admin',
		'meta_key'   => 'managed_community_id',
		'meta_value' => $community_id,
	] );
}

/**
 * Devuelve los vendors (wcfm_vendor) vinculados a una comunidad.
 *
 * @param int $community_id
 * @return WP_User[]
 */
function amazonia_get_community_vendors( $community_id ) {
	return get_users( [
		'role'       => 'wcfm_vendor',
		'meta_key'   => 'community_id',
		'meta_value' => $community_id,
	] );
}

/**
 * Datos completos de una comunidad para mostrar en frontend.
 *
 * @param int $community_id
 * @return array|null null si no existe
 */
function amazonia_get_community_data( $community_id ) {
	$post = get_post( $community_id );
	if ( ! $post || $post->post_type !== 'comunidad' ) return null;

	$logo = get_post_meta( $community_id, '_comunidad_logo_url', true );
	if ( ! $logo ) {
		$thumb_id = get_post_thumbnail_id( $community_id );
		$logo     = $thumb_id ? wp_get_attachment_image_url( $thumb_id, 'thumbnail' ) : '';
	}

	return [
		'id'           => $community_id,
		'nombre'       => get_the_title( $community_id ),
		'descripcion'  => get_post_meta( $community_id, '_comunidad_descripcion', true ),
		'historia'     => get_post_meta( $community_id, '_comunidad_historia', true ),
		'pais'         => get_post_meta( $community_id, '_comunidad_pais', true ),
		'departamento' => get_post_meta( $community_id, '_comunidad_departamento', true ),
		'municipio'    => get_post_meta( $community_id, '_comunidad_municipio', true ),
		'categoria'    => get_post_meta( $community_id, '_comunidad_categoria', true ),
		'logo'         => $logo,
		'url'          => get_permalink( $community_id ),
		'vendors'      => amazonia_get_community_vendors( $community_id ),
	];
}

// ─── 9. Banner de comunidad debajo del header de la tienda WCFM ─────────────
//
// Usa wcfmmp_store_after_header (fuera del div#wcfm_store_header)
// para no competir con el CSS del plugin y poder usar Tailwind libremente.

add_action( 'wcfmmp_store_after_header', 'amazonia_store_community_banner' );
/**
 * Muestra un banner de comunidad debajo del header WCFM.
 * Solo se muestra si el vendor tiene 'community_id' asignado.
 *
 * @param int $vendor_id
 */
function amazonia_store_community_banner( $vendor_id ) {
	$community_id = (int) get_user_meta( $vendor_id, 'community_id', true );
	if ( ! $community_id ) return;

	$data = amazonia_get_community_data( $community_id );
	if ( ! $data ) return;

	$nombre    = esc_html( $data['nombre'] );
	$url       = esc_url( $data['url'] );
	$logo      = $data['logo'] ? esc_url( $data['logo'] ) : '';
	$categoria = $data['categoria'] ? esc_html( $data['categoria'] ) : '';
	$location  = implode( ', ', array_filter( [ $data['municipio'], $data['departamento'], $data['pais'] ] ) );
	?>
	<div style="background:#f0fdf4;border-top:2px solid #bbf7d0;border-bottom:2px solid #bbf7d0;padding:14px 24px;margin:0 0 4px;">
		<a href="<?php echo $url; ?>"
		   style="display:inline-flex;align-items:center;gap:12px;text-decoration:none;color:inherit;max-width:700px;">

			<?php if ( $logo ) : ?>
				<img src="<?php echo $logo; ?>"
				     alt="<?php echo $nombre; ?>"
				     style="width:44px;height:44px;border-radius:50%;object-fit:cover;border:2px solid #86efac;flex-shrink:0;"
				     loading="lazy" width="44" height="44" />
			<?php else : ?>
				<span class="material-symbols-outlined"
				      style="font-size:36px;color:#4ade80;flex-shrink:0;line-height:1;">groups</span>
			<?php endif; ?>

			<div style="display:flex;flex-direction:column;gap:1px;">
				<span style="font-size:10px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;color:#16a34a;">
					<?php esc_html_e( 'Tienda de la comunidad', 'amazonia-theme' ); ?>
				</span>
				<span style="font-size:16px;font-weight:800;color:#0f172a;line-height:1.2;">
					<?php echo $nombre; ?>
				</span>
				<?php if ( $categoria || $location ) : ?>
					<span style="font-size:12px;color:#64748b;margin-top:1px;">
						<?php if ( $categoria ) echo $categoria . ( $location ? ' · ' : '' ); ?>
						<?php echo esc_html( $location ); ?>
					</span>
				<?php endif; ?>
			</div>

			<span class="material-symbols-outlined"
			      style="font-size:18px;color:#4ade80;margin-left:auto;flex-shrink:0;">arrow_forward</span>
		</a>
	</div>
	<?php
}
