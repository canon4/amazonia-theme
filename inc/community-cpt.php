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

// ─── 1b. Helpers compartidos (front + admin) ─────────────────────────────────

/**
 * Íconos disponibles para los "Valores de la comunidad".
 * Fuente única usada tanto por template-community-admin.php (front) como
 * por la meta box de Valores en wp-admin.
 */
function amazonia_get_comunidad_valor_icons() {
	return [
		'eco'                => 'Sostenibilidad',
		'handshake'          => 'Comercio Justo',
		'diversity_3'        => 'Comunidad',
		'forest'             => 'Naturaleza',
		'agriculture'        => 'Producción',
		'workspace_premium'  => 'Calidad',
		'spa'                => 'Bienestar',
		'volunteer_activism' => 'Solidaridad',
		'groups'             => 'Unidad',
		'favorite'           => 'Pasión',
		'recycling'          => 'Reciclaje',
		'water_drop'         => 'Agua',
	];
}

/**
 * Sanitiza el JSON de "valores" ({icono,texto}[]) enviado desde el front o
 * desde wp-admin. Máximo 4, descarta filas sin texto, fuerza un ícono válido.
 *
 * @param string $raw JSON crudo (ya con wp_unslash aplicado por el llamador).
 * @return array<int,array{icono:string,texto:string}>
 */
function amazonia_sanitize_comunidad_valores( $raw ) {
	$valores = json_decode( (string) $raw, true );
	if ( ! is_array( $valores ) ) {
		$valores = [];
	}
	$valores = array_values( array_filter(
		array_slice( $valores, 0, 4 ),
		fn( $v ) => is_array( $v ) && ! empty( $v['texto'] )
	) );

	$icons = amazonia_get_comunidad_valor_icons();

	return array_map( fn( $v ) => [
		'icono' => array_key_exists( $v['icono'] ?? '', $icons ) ? $v['icono'] : 'eco',
		'texto' => sanitize_text_field( $v['texto'] ?? '' ),
	], $valores );
}

/**
 * Sanitiza el JSON de la galería (array de attachment IDs) enviado desde el
 * front o desde wp-admin. Descarta IDs que no correspondan a un adjunto real.
 *
 * @param string $raw JSON crudo (ya con wp_unslash aplicado por el llamador).
 * @return int[]
 */
function amazonia_sanitize_comunidad_galeria( $raw ) {
	$ids = json_decode( (string) $raw, true );
	if ( ! is_array( $ids ) ) {
		$ids = [];
	}
	$ids = array_map( 'absint', $ids );

	return array_values( array_filter( $ids, fn( $id ) => $id && get_post_type( $id ) === 'attachment' ) );
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
		'amazonia_comunidad_stores',
		__( 'Tiendas de la Comunidad', 'amazonia-theme' ),
		'amazonia_render_comunidad_stores_box',
		'comunidad',
		'normal',
		'default'
	);
	add_meta_box(
		'amazonia_comunidad_gallery',
		__( 'Galería de fotos', 'amazonia-theme' ),
		'amazonia_render_comunidad_gallery_box',
		'comunidad',
		'normal',
		'default'
	);
	add_meta_box(
		'amazonia_comunidad_storytelling',
		__( 'Imágenes de Storytelling', 'amazonia-theme' ),
		'amazonia_render_comunidad_storytelling_box',
		'comunidad',
		'normal',
		'default'
	);
	add_meta_box(
		'amazonia_comunidad_valores',
		__( 'Valores de la Comunidad', 'amazonia-theme' ),
		'amazonia_render_comunidad_valores_box',
		'comunidad',
		'normal',
		'default'
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
		'descripcion'    => get_post_meta( $post->ID, '_comunidad_descripcion', true ),
		'historia'       => get_post_meta( $post->ID, '_comunidad_historia', true ),
		'pais'           => get_post_meta( $post->ID, '_comunidad_pais', true ),
		'departamento'   => get_post_meta( $post->ID, '_comunidad_departamento', true ),
		'municipio'      => get_post_meta( $post->ID, '_comunidad_municipio', true ),
		'categoria'      => get_post_meta( $post->ID, '_comunidad_categoria', true ),
		'logo_url'       => get_post_meta( $post->ID, '_comunidad_logo_url', true ),
		'banner_url'     => get_post_meta( $post->ID, '_comunidad_banner_url', true ),
		'video_url'      => get_post_meta( $post->ID, '_comunidad_video_url', true ),
		'fundacion'      => get_post_meta( $post->ID, '_comunidad_fundacion', true ),
		'num_familias'   => get_post_meta( $post->ID, '_comunidad_num_familias', true ),
		'instagram'      => get_post_meta( $post->ID, '_comunidad_instagram', true ),
		'facebook'       => get_post_meta( $post->ID, '_comunidad_facebook', true ),
		'certificaciones'=> get_post_meta( $post->ID, '_comunidad_certificaciones', true ),
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
		<label><?php esc_html_e( 'Logo de la comunidad', 'amazonia-theme' ); ?></label>
		<input type="hidden" name="comunidad_logo_url" id="ca-logo-url" value="<?php echo esc_attr( $meta['logo_url'] ); ?>" />
		<div class="ca-logo-uploader">
			<div id="ca-logo-preview" class="ca-logo-preview <?php echo $meta['logo_url'] ? 'has-image' : ''; ?>">
				<?php if ( $meta['logo_url'] ) : ?>
					<img id="ca-logo-img" src="<?php echo esc_url( $meta['logo_url'] ); ?>" alt="Logo" />
				<?php else : ?>
					<span class="material-symbols-outlined ca-logo-placeholder-icon">add_photo_alternate</span>
				<?php endif; ?>
			</div>
			<div class="ca-logo-actions">
				<button type="button" id="ca-logo-upload-btn" class="ca-btn-outline">
					<span class="material-symbols-outlined">upload</span>
					<?php echo $meta['logo_url'] ? esc_html__( 'Cambiar imagen', 'amazonia-theme' ) : esc_html__( 'Subir imagen', 'amazonia-theme' ); ?>
				</button>
				<button type="button" id="ca-logo-remove-btn" class="ca-btn-danger" style="<?php echo $meta['logo_url'] ? '' : 'display:none;'; ?>">
					<span class="material-symbols-outlined">delete</span>
					<?php esc_html_e( 'Eliminar', 'amazonia-theme' ); ?>
				</button>
			</div>
		</div>
		<p class="description" style="margin-top:8px;">
			<?php esc_html_e( 'También puedes usar la imagen destacada del post como logo.', 'amazonia-theme' ); ?>
		</p>
	</div>

	<div class="comunidad-meta-full">
		<label><?php esc_html_e( 'Imagen de portada (banner)', 'amazonia-theme' ); ?></label>
		<input type="hidden" name="comunidad_banner_url" id="ca-banner-url" value="<?php echo esc_attr( $meta['banner_url'] ); ?>" />
		<div class="ca-logo-uploader">
			<div id="ca-banner-preview" class="ca-banner-preview <?php echo $meta['banner_url'] ? 'has-image' : ''; ?>"
				<?php if ( $meta['banner_url'] ) : ?>style="background-image:url('<?php echo esc_url( $meta['banner_url'] ); ?>')"<?php endif; ?>>
				<?php if ( ! $meta['banner_url'] ) : ?>
					<span class="material-symbols-outlined ca-logo-placeholder-icon">panorama</span>
				<?php endif; ?>
			</div>
			<div class="ca-logo-actions">
				<button type="button" id="ca-banner-upload-btn" class="ca-btn-outline">
					<span class="material-symbols-outlined">upload</span>
					<?php echo $meta['banner_url'] ? esc_html__( 'Cambiar portada', 'amazonia-theme' ) : esc_html__( 'Subir portada', 'amazonia-theme' ); ?>
				</button>
				<button type="button" id="ca-banner-remove-btn" class="ca-btn-danger" style="<?php echo $meta['banner_url'] ? '' : 'display:none;'; ?>">
					<span class="material-symbols-outlined">delete</span>
					<?php esc_html_e( 'Eliminar', 'amazonia-theme' ); ?>
				</button>
			</div>
		</div>
	</div>

	<div class="comunidad-meta-full">
		<label><?php esc_html_e( 'URL Video de presentación (YouTube / Vimeo)', 'amazonia-theme' ); ?></label>
		<input type="url" name="comunidad_video_url" value="<?php echo esc_attr( $meta['video_url'] ); ?>" placeholder="https://www.youtube.com/watch?v=..." />
	</div>

	<input type="file" id="ca-logo-file"   accept="image/*" style="display:none;" />
	<input type="file" id="ca-banner-file" accept="image/*" style="display:none;" />

	<div class="comunidad-meta-grid">
		<div>
			<label><?php esc_html_e( 'Año de fundación', 'amazonia-theme' ); ?></label>
			<input type="text" name="comunidad_fundacion" value="<?php echo esc_attr( $meta['fundacion'] ); ?>" placeholder="1987" />
		</div>
		<div>
			<label><?php esc_html_e( 'Familias / artesanos', 'amazonia-theme' ); ?></label>
			<input type="text" name="comunidad_num_familias" value="<?php echo esc_attr( $meta['num_familias'] ); ?>" placeholder="42, o &quot;más de 50 artesanos&quot;" />
		</div>
	</div>

	<div class="comunidad-meta-grid">
		<div>
			<label><?php esc_html_e( 'Instagram URL', 'amazonia-theme' ); ?></label>
			<input type="url" name="comunidad_instagram" value="<?php echo esc_attr( $meta['instagram'] ); ?>" placeholder="https://instagram.com/..." />
		</div>
		<div>
			<label><?php esc_html_e( 'Facebook URL', 'amazonia-theme' ); ?></label>
			<input type="url" name="comunidad_facebook" value="<?php echo esc_attr( $meta['facebook'] ); ?>" placeholder="https://facebook.com/..." />
		</div>
	</div>

	<div class="comunidad-meta-full">
		<label><?php esc_html_e( 'Certificaciones', 'amazonia-theme' ); ?></label>
		<input type="text" name="comunidad_certificaciones" value="<?php echo esc_attr( $meta['certificaciones'] ); ?>" placeholder="Comercio Justo · Orgánico" />
	</div>
	<?php
}

// ─── 3b. Renderizar meta box: Galería de fotos ───────────────────────────────
function amazonia_render_comunidad_gallery_box( $post ) {
	$galeria_raw = get_post_meta( $post->ID, '_comunidad_galeria', true );
	$galeria_ids = $galeria_raw ? json_decode( $galeria_raw, true ) : [];
	if ( ! is_array( $galeria_ids ) ) $galeria_ids = [];
	?>
	<p class="description" style="margin:0 0 12px;">
		<?php esc_html_e( 'Proceso productivo, paisaje, artesanos — construye confianza visual.', 'amazonia-theme' ); ?>
	</p>
	<input type="hidden" name="comunidad_galeria_ids" id="ca-galeria-ids" value="<?php echo esc_attr( wp_json_encode( $galeria_ids ) ); ?>" />
	<div class="ca-gallery-grid" id="ca-gallery-grid">
		<?php foreach ( $galeria_ids as $att_id ) :
			$thumb = wp_get_attachment_image_url( $att_id, 'thumbnail' );
			if ( ! $thumb ) continue;
		?>
			<div class="ca-gallery-item" data-id="<?php echo esc_attr( $att_id ); ?>">
				<img src="<?php echo esc_url( $thumb ); ?>" alt="" width="80" height="80" loading="lazy" />
				<button type="button" class="ca-gallery-remove" title="<?php esc_attr_e( 'Eliminar', 'amazonia-theme' ); ?>">
					<span class="material-symbols-outlined">close</span>
				</button>
			</div>
		<?php endforeach; ?>
	</div>
	<button type="button" id="ca-gallery-add-btn" class="ca-btn-outline" style="margin-top:.75rem;">
		<span class="material-symbols-outlined">add_photo_alternate</span>
		<?php esc_html_e( 'Añadir imágenes', 'amazonia-theme' ); ?>
	</button>
	<input type="file" id="ca-gallery-file" accept="image/*" multiple style="display:none;" />
	<?php
}

// ─── 3c. Renderizar meta box: Imágenes de Storytelling ───────────────────────
function amazonia_render_comunidad_storytelling_box( $post ) {
	$story_fields = [
		1 => [ 'label' => 'Card 1 — La Comunidad',       'key' => 'storytelling_img_1' ],
		2 => [ 'label' => 'Card 2 — Tradición & Cultura', 'key' => 'storytelling_img_2' ],
		3 => [ 'label' => 'Card 3 — Valores',             'key' => 'storytelling_img_3' ],
	];
	?>
	<p class="description" style="margin:0 0 16px;">
		<?php esc_html_e( 'Estas imágenes aparecen en las cards de descripción de cada producto de la comunidad.', 'amazonia-theme' ); ?>
	</p>
	<div style="display:flex;flex-direction:column;gap:1rem;">
		<?php foreach ( $story_fields as $n => $sf ) :
			$val = get_post_meta( $post->ID, '_comunidad_' . $sf['key'], true );
		?>
		<div style="display:flex;gap:12px;align-items:center;">
			<?php if ( $val ) : ?>
				<img id="ca-story-<?php echo $n; ?>-preview"
				     src="<?php echo esc_url( $val ); ?>"
				     style="width:100px;height:68px;object-fit:cover;border-radius:6px;border:1px solid #d1d5db;flex-shrink:0;" />
			<?php else : ?>
				<div id="ca-story-<?php echo $n; ?>-preview"
				     style="width:100px;height:68px;border-radius:6px;border:2px dashed #d1d5db;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
					<span class="material-symbols-outlined" style="color:#94a3b8;font-size:28px;">panorama</span>
				</div>
			<?php endif; ?>
			<div>
				<p style="font-size:.8rem;font-weight:600;color:#374151;margin:0 0 6px;">
					<?php echo esc_html( $sf['label'] ); ?>
				</p>
				<div style="display:flex;gap:8px;flex-wrap:wrap;">
					<button type="button" class="ca-btn-outline ca-story-upload-btn" data-n="<?php echo $n; ?>" style="font-size:.8rem;padding:6px 12px;">
						<span class="material-symbols-outlined" style="font-size:16px;">upload</span>
						<?php echo $val ? esc_html__( 'Cambiar', 'amazonia-theme' ) : esc_html__( 'Subir', 'amazonia-theme' ); ?>
					</button>
					<button type="button" class="ca-btn-danger ca-story-remove-btn" data-n="<?php echo $n; ?>" style="font-size:.8rem;padding:6px 12px;<?php echo $val ? '' : 'display:none;'; ?>">
						<span class="material-symbols-outlined" style="font-size:16px;">delete</span>
						<?php esc_html_e( 'Eliminar', 'amazonia-theme' ); ?>
					</button>
				</div>
			</div>
			<input type="hidden" name="comunidad_<?php echo esc_attr( $sf['key'] ); ?>"
			       id="ca-story-<?php echo $n; ?>-url"
			       value="<?php echo esc_attr( $val ); ?>" />
			<input type="file" id="ca-story-<?php echo $n; ?>-file" accept="image/*" style="display:none;" />
		</div>
		<?php endforeach; ?>
	</div>
	<?php
}

// ─── 3d. Renderizar meta box: Valores de la comunidad ────────────────────────
function amazonia_render_comunidad_valores_box( $post ) {
	$valores_raw = get_post_meta( $post->ID, '_comunidad_valores', true );
	$valores     = $valores_raw ? json_decode( $valores_raw, true ) : [];
	if ( ! is_array( $valores ) ) $valores = [];
	$valor_icons = amazonia_get_comunidad_valor_icons();
	?>
	<script type="application/json" id="ca-valor-icons-data">
	<?php echo wp_json_encode( $valor_icons ); ?>
	</script>

	<p class="description" style="margin:0 0 12px;">
		<?php esc_html_e( 'Máximo 4 valores.', 'amazonia-theme' ); ?>
	</p>
	<input type="hidden" name="comunidad_valores_json" id="ca-valores-json" value="<?php echo esc_attr( wp_json_encode( $valores ) ); ?>" />
	<div id="ca-valores-list" style="display:flex;flex-direction:column;gap:.5rem;margin-bottom:.75rem;">
		<?php foreach ( $valores as $valor ) : ?>
			<div class="ca-valor-row">
				<span class="material-symbols-outlined ca-valor-icon-preview"><?php echo esc_html( $valor['icono'] ?? 'eco' ); ?></span>
				<select class="ca-icon-select">
					<?php foreach ( $valor_icons as $icon => $label ) : ?>
						<option value="<?php echo esc_attr( $icon ); ?>" <?php selected( $valor['icono'] ?? '', $icon ); ?>>
							<?php echo esc_html( $icon . ' — ' . $label ); ?>
						</option>
					<?php endforeach; ?>
				</select>
				<input type="text" class="ca-valor-texto"
					value="<?php echo esc_attr( $valor['texto'] ?? '' ); ?>"
					placeholder="<?php esc_attr_e( 'Ej: Sostenibilidad', 'amazonia-theme' ); ?>" />
				<button type="button" class="ca-valor-remove" title="<?php esc_attr_e( 'Eliminar valor', 'amazonia-theme' ); ?>">
					<span class="material-symbols-outlined">close</span>
				</button>
			</div>
		<?php endforeach; ?>
	</div>
	<button type="button" id="ca-valor-add-btn" class="ca-btn-outline" <?php echo count( $valores ) >= 4 ? 'disabled' : ''; ?>>
		<span class="material-symbols-outlined">add</span>
		<?php esc_html_e( 'Añadir valor', 'amazonia-theme' ); ?>
	</button>
	<?php
}

// ─── 3e. Renderizar meta box: Tiendas de la comunidad ────────────────────────
function amazonia_render_comunidad_stores_box( $post ) {
	$vendors = amazonia_get_community_vendors( $post->ID );
	?>
	<ul class="ca-store-list" id="ca-store-list">
		<?php if ( empty( $vendors ) ) : ?>
			<li class="ca-empty">
				<span class="material-symbols-outlined">store</span>
				<?php esc_html_e( 'Aún no hay tiendas vinculadas.', 'amazonia-theme' ); ?>
			</li>
		<?php else : ?>
			<?php foreach ( $vendors as $vendor ) :
				$store_name = wcfm_get_vendor_store_name( $vendor->ID );
				$logo       = function_exists( 'wcfm_get_vendor_store_logo_by_vendor' ) ? wcfm_get_vendor_store_logo_by_vendor( $vendor->ID ) : '';
				$store_url  = function_exists( 'wcfmmp_get_store_url' ) ? wcfmmp_get_store_url( $vendor->ID ) : get_author_posts_url( $vendor->ID );
			?>
				<li class="ca-store-item">
					<?php if ( $logo ) : ?>
						<img src="<?php echo esc_url( $logo ); ?>" class="ca-store-avatar" alt="<?php echo esc_attr( $store_name ); ?>" loading="lazy" width="40" height="40" />
					<?php else : ?>
						<div class="ca-store-avatar-placeholder">
							<span class="material-symbols-outlined">storefront</span>
						</div>
					<?php endif; ?>
					<div class="ca-store-info">
						<div class="ca-store-name"><?php echo esc_html( $store_name ?: $vendor->display_name ); ?></div>
						<div class="ca-store-email"><?php echo esc_html( $vendor->user_email ); ?></div>
					</div>
					<?php if ( $store_url ) : ?>
						<a href="<?php echo esc_url( $store_url ); ?>" target="_blank" class="ca-store-link">
							<?php esc_html_e( 'Ver tienda', 'amazonia-theme' ); ?> ↗
						</a>
					<?php endif; ?>
					<button type="button" class="ca-btn-danger ca-store-unlink-btn" data-id="<?php echo esc_attr( $vendor->ID ); ?>" style="margin-left:8px;padding:.375rem .625rem;">
						<span class="material-symbols-outlined" style="font-size:16px;">link_off</span>
					</button>
				</li>
			<?php endforeach; ?>
		<?php endif; ?>
	</ul>

	<hr style="margin:20px 0;border-color:#e2e8f0;" />

	<h4 style="margin:0 0 12px;font-size:13px;text-transform:uppercase;letter-spacing:.04em;color:#374151;">
		<?php esc_html_e( 'Crear nueva tienda', 'amazonia-theme' ); ?>
	</h4>
	<div id="ca-create-form" class="ca-form">
		<div class="ca-field">
			<label for="ca-store-name"><?php esc_html_e( 'Nombre de la tienda', 'amazonia-theme' ); ?> *</label>
			<input type="text" name="store_name" id="ca-store-name" placeholder="<?php esc_attr_e( 'Ej: Artesanías Shipibo', 'amazonia-theme' ); ?>" />
		</div>
		<div class="ca-field">
			<label for="ca-email"><?php esc_html_e( 'Email del vendedor', 'amazonia-theme' ); ?> *</label>
			<input type="email" name="email" id="ca-email" placeholder="vendedor@ejemplo.com" />
		</div>
		<div class="ca-field-row">
			<div class="ca-field">
				<label for="ca-first-name"><?php esc_html_e( 'Nombre', 'amazonia-theme' ); ?></label>
				<input type="text" name="first_name" id="ca-first-name" placeholder="<?php esc_attr_e( 'Juan', 'amazonia-theme' ); ?>" />
			</div>
			<div class="ca-field">
				<label for="ca-last-name"><?php esc_html_e( 'Apellido', 'amazonia-theme' ); ?></label>
				<input type="text" name="last_name" id="ca-last-name" placeholder="<?php esc_attr_e( 'Pérez', 'amazonia-theme' ); ?>" />
			</div>
		</div>
		<p style="font-size:.8rem;color:#94a3b8;margin:-.25rem 0 0;">
			<?php esc_html_e( 'Se generará una contraseña automática que podrás copiar y entregar al vendedor.', 'amazonia-theme' ); ?>
		</p>
		<button type="button" id="ca-create-vendor-btn" class="ca-btn" data-original-text="<?php esc_attr_e( 'Crear tienda', 'amazonia-theme' ); ?>">
			<?php esc_html_e( 'Crear tienda', 'amazonia-theme' ); ?>
		</button>
		<div id="ca-create-feedback" class="ca-feedback"></div>
		<div id="ca-create-credentials" class="ca-credentials-box" style="display:none;">
			<p class="ca-credentials-title"><?php esc_html_e( 'Credenciales de acceso — cópialas y envíaselas al vendedor:', 'amazonia-theme' ); ?></p>
			<div class="ca-credentials-row">
				<label><?php esc_html_e( 'Usuario', 'amazonia-theme' ); ?></label>
				<input type="text" id="ca-cred-username" readonly />
			</div>
			<div class="ca-credentials-row">
				<label><?php esc_html_e( 'Contraseña', 'amazonia-theme' ); ?></label>
				<input type="text" id="ca-cred-password" readonly />
				<button type="button" id="ca-cred-copy" class="ca-btn ca-btn-secondary">
					<?php esc_html_e( 'Copiar', 'amazonia-theme' ); ?>
				</button>
			</div>
		</div>
	</div>

	<hr style="margin:20px 0;border-color:#e2e8f0;" />

	<h4 style="margin:0 0 12px;font-size:13px;text-transform:uppercase;letter-spacing:.04em;color:#374151;">
		<?php esc_html_e( 'Vincular tienda existente', 'amazonia-theme' ); ?>
	</h4>
	<div class="ca-field">
		<label for="ca-search-input"><?php esc_html_e( 'Buscar por nombre o email', 'amazonia-theme' ); ?></label>
		<input type="text" id="ca-search-input" placeholder="<?php esc_attr_e( 'Escribe al menos 2 caracteres...', 'amazonia-theme' ); ?>" />
	</div>
	<div id="ca-search-results" class="ca-search-results"></div>
	<div id="ca-link-feedback" class="ca-feedback"></div>
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

	$text_fields = [
		'comunidad_descripcion'  => '_comunidad_descripcion',
		'comunidad_historia'     => '_comunidad_historia',
		'comunidad_pais'         => '_comunidad_pais',
		'comunidad_departamento' => '_comunidad_departamento',
		'comunidad_municipio'    => '_comunidad_municipio',
		'comunidad_categoria'    => '_comunidad_categoria',
		'comunidad_fundacion'    => '_comunidad_fundacion',
		'comunidad_num_familias' => '_comunidad_num_familias',
		'comunidad_certificaciones' => '_comunidad_certificaciones',
	];
	$url_fields = [
		'comunidad_logo_url'   => '_comunidad_logo_url',
		'comunidad_banner_url' => '_comunidad_banner_url',
		'comunidad_video_url'  => '_comunidad_video_url',
		'comunidad_instagram'  => '_comunidad_instagram',
		'comunidad_facebook'   => '_comunidad_facebook',
	];

	foreach ( $text_fields as $post_key => $meta_key ) {
		if ( isset( $_POST[ $post_key ] ) ) {
			update_post_meta( $post_id, $meta_key, sanitize_textarea_field( $_POST[ $post_key ] ) );
		}
	}
	foreach ( $url_fields as $post_key => $meta_key ) {
		if ( isset( $_POST[ $post_key ] ) ) {
			update_post_meta( $post_id, $meta_key, esc_url_raw( $_POST[ $post_key ] ) );
		}
	}

	foreach ( [ 1, 2, 3 ] as $n ) {
		$post_key = "comunidad_storytelling_img_{$n}";
		if ( isset( $_POST[ $post_key ] ) ) {
			update_post_meta( $post_id, "_comunidad_storytelling_img_{$n}", esc_url_raw( $_POST[ $post_key ] ) );
		}
	}

	if ( isset( $_POST['comunidad_galeria_ids'] ) ) {
		$galeria_ids = amazonia_sanitize_comunidad_galeria( wp_unslash( $_POST['comunidad_galeria_ids'] ) );
		update_post_meta( $post_id, '_comunidad_galeria', wp_json_encode( $galeria_ids ) );
	}

	if ( isset( $_POST['comunidad_valores_json'] ) ) {
		$valores = amazonia_sanitize_comunidad_valores( wp_unslash( $_POST['comunidad_valores_json'] ) );
		update_post_meta( $post_id, '_comunidad_valores', wp_json_encode( $valores ) );
	}
}

// ─── 5b. Encolar CSS/JS del panel de comunidad en la pantalla de edición ─────
add_action( 'admin_enqueue_scripts', 'amazonia_enqueue_comunidad_admin_assets' );
function amazonia_enqueue_comunidad_admin_assets( $hook ) {
	if ( ! in_array( $hook, [ 'post.php', 'post-new.php' ], true ) ) return;

	$screen = get_current_screen();
	if ( ! $screen || $screen->post_type !== 'comunidad' ) return;

	// El CSS de Material Symbols (.material-symbols-outlined) normalmente solo
	// se encola en el front (wp_enqueue_scripts, ver amazonia_theme_scripts en
	// functions.php). Las meta boxes de esta pantalla reutilizan esos mismos
	// iconos, así que hay que encolarlo también aquí o se ven como cajas vacías.
	amazonia_style( 'material-symbols', 'assets/css/material-symbols.css' );

	global $post;
	amazonia_enqueue_community_admin_assets( $post ? $post->ID : 0 );
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

	$galeria_raw = get_post_meta( $community_id, '_comunidad_galeria', true );
	$galeria_ids = $galeria_raw ? json_decode( $galeria_raw, true ) : [];
	if ( ! is_array( $galeria_ids ) ) $galeria_ids = [];

	$valores_raw = get_post_meta( $community_id, '_comunidad_valores', true );
	$valores = $valores_raw ? json_decode( $valores_raw, true ) : [];
	if ( ! is_array( $valores ) ) $valores = [];

	return [
		'id'             => $community_id,
		'nombre'         => get_the_title( $community_id ),
		'descripcion'    => get_post_meta( $community_id, '_comunidad_descripcion', true ),
		'historia'       => get_post_meta( $community_id, '_comunidad_historia', true ),
		'pais'           => get_post_meta( $community_id, '_comunidad_pais', true ),
		'departamento'   => get_post_meta( $community_id, '_comunidad_departamento', true ),
		'municipio'      => get_post_meta( $community_id, '_comunidad_municipio', true ),
		'categoria'      => get_post_meta( $community_id, '_comunidad_categoria', true ),
		'logo'           => $logo,
		'banner'         => get_post_meta( $community_id, '_comunidad_banner_url', true ),
		'galeria_ids'    => $galeria_ids,
		'video_url'      => get_post_meta( $community_id, '_comunidad_video_url', true ),
		'fundacion'      => get_post_meta( $community_id, '_comunidad_fundacion', true ),
		'num_familias'   => get_post_meta( $community_id, '_comunidad_num_familias', true ),
		'valores'        => $valores,
		'instagram'      => get_post_meta( $community_id, '_comunidad_instagram', true ),
		'facebook'       => get_post_meta( $community_id, '_comunidad_facebook', true ),
		'certificaciones'=> get_post_meta( $community_id, '_comunidad_certificaciones', true ),
		'url'                => get_permalink( $community_id ),
		'vendors'            => amazonia_get_community_vendors( $community_id ),
		'storytelling_img_1' => get_post_meta( $community_id, '_comunidad_storytelling_img_1', true ),
		'storytelling_img_2' => get_post_meta( $community_id, '_comunidad_storytelling_img_2', true ),
		'storytelling_img_3' => get_post_meta( $community_id, '_comunidad_storytelling_img_3', true ),
	];
}

/**
 * Convierte una URL de YouTube o Vimeo en URL de embed.
 * Retorna cadena vacía si no reconoce el formato.
 */
function amazonia_get_video_embed_url( $url ) {
	if ( ! $url ) return '';
	if ( preg_match( '/(?:youtube\.com\/watch\?v=|youtu\.be\/)([a-zA-Z0-9_-]{11})/', $url, $m ) ) {
		return 'https://www.youtube.com/embed/' . $m[1] . '?rel=0&modestbranding=1';
	}
	if ( preg_match( '/vimeo\.com\/(\d+)/', $url, $m ) ) {
		return 'https://player.vimeo.com/video/' . $m[1];
	}
	return '';
}

// ─── 10. Capacidad: el admin de comunidad puede editar SU comunidad ──────────
//
// Cuando WordPress comprueba edit_post para un post concreto, $args[0] es 'edit_post'
// y $args[2] es el post_id. Si coincide con managed_community_id del usuario,
// concedemos los primitive caps que WordPress requiere para esa acción específica.
add_filter( 'user_has_cap', 'amazonia_community_admin_edit_cap', 999, 4 );
function amazonia_community_admin_edit_cap( $allcaps, $caps, $args, $user ) {
	if ( ! in_array( 'amazonia_community_admin', (array) $user->roles, true ) ) return $allcaps;

	$check_cap = $args[0] ?? '';
	if ( $check_cap !== 'edit_post' ) return $allcaps;

	$post_id = isset( $args[2] ) ? (int) $args[2] : 0;
	if ( ! $post_id ) return $allcaps;

	$managed = (int) get_user_meta( $user->ID, 'managed_community_id', true );
	if ( ! $managed || $managed !== $post_id ) return $allcaps;

	// Conceder exactamente los primitive caps que WP pidió para este edit_post
	foreach ( $caps as $cap ) {
		$allcaps[ $cap ] = true;
	}
	return $allcaps;
}

// ─── 9. Banner de comunidad debajo del header de la tienda WCFM ─────────────
//
// DESACTIVADO: la pertenencia a la comunidad pasó a estar integrada dentro de la
// cabecera del perfil de tienda (badge en wcfm/store/wcfmmp-view-store.php), tal
// como pide el diseño "Amazonia Perfiles" — no como una banda suelta pegada
// debajo del header. La función se conserva por si otra plantilla la necesita.
//
// add_action( 'wcfmmp_store_after_header', 'amazonia_store_community_banner' );
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

/**
 * Estadísticas públicas de una tienda (vendedor WCFM).
 *
 * El rating se agrega desde las reseñas de PRODUCTO de WooCommerce, porque el
 * módulo de reseñas de tienda de WCFM no está en uso: la tabla
 * {prefix}wcfm_marketplace_reviews existe pero está vacía y el plugin no expone
 * helpers de rating de tienda en esta instalación.
 *
 * Devuelve rating = null cuando la tienda todavía no tiene ninguna reseña, para
 * que la vista pueda ocultar el dato en lugar de pintar un 0 engañoso.
 *
 * @param int $vendor_id ID del usuario vendedor.
 * @return array{product_count:int, rating:float|null, review_count:int}
 */
function amazonia_get_store_stats( $vendor_id ) {
	global $wpdb;

	$vendor_id = absint( $vendor_id );
	$empty     = [ 'product_count' => 0, 'rating' => null, 'review_count' => 0 ];
	if ( ! $vendor_id ) {
		return $empty;
	}

	$cache_key = 'amazonia_store_stats_' . $vendor_id;
	$cached    = get_transient( $cache_key );
	if ( false !== $cached ) {
		return $cached;
	}

	// Una sola consulta: cuenta productos publicados y agrega las medias que
	// WooCommerce ya mantiene por producto (_wc_average_rating / _wc_review_count).
	$row = $wpdb->get_row(
		$wpdb->prepare(
			"SELECT COUNT(p.ID) AS product_count,
			        COALESCE( SUM( CAST( rc.meta_value AS UNSIGNED ) ), 0 ) AS review_count,
			        COALESCE( SUM( CAST( ar.meta_value AS DECIMAL(10,2) ) * CAST( rc.meta_value AS UNSIGNED ) ), 0 ) AS rating_sum
			   FROM {$wpdb->posts} p
			   LEFT JOIN {$wpdb->postmeta} ar ON ar.post_id = p.ID AND ar.meta_key = '_wc_average_rating'
			   LEFT JOIN {$wpdb->postmeta} rc ON rc.post_id = p.ID AND rc.meta_key = '_wc_review_count'
			  WHERE p.post_author = %d
			    AND p.post_type   = 'product'
			    AND p.post_status = 'publish'",
			$vendor_id
		)
	);

	if ( ! $row ) {
		return $empty;
	}

	$review_count = (int) $row->review_count;
	$stats        = [
		'product_count'    => (int) $row->product_count,
		'review_count'     => $review_count,
		// Media ponderada por nº de reseñas de cada producto.
		'rating'           => $review_count > 0 ? round( (float) $row->rating_sum / $review_count, 1 ) : null,
		'completed_orders' => amazonia_count_vendor_completed_orders( $vendor_id ),
	];

	set_transient( $cache_key, $stats, 15 * MINUTE_IN_SECONDS );

	return $stats;
}

/**
 * Invalida la caché de estadísticas cuando cambia una reseña de producto.
 */
function amazonia_flush_store_stats_cache( $comment_id ) {
	$comment = get_comment( $comment_id );
	if ( ! $comment ) {
		return;
	}
	$product = get_post( $comment->comment_post_ID );
	if ( $product && 'product' === $product->post_type ) {
		delete_transient( 'amazonia_store_stats_' . $product->post_author );
	}
}
add_action( 'wp_insert_comment', 'amazonia_flush_store_stats_cache' );
add_action( 'wp_set_comment_status', 'amazonia_flush_store_stats_cache' );

/**
 * Resuelve una URL de imagen guardada en los metadatos de comunidad.
 *
 * Los campos de comunidad guardan la URL absoluta del archivo ORIGINAL (a menudo
 * la variante "-scaled" de 2560 px, de varios MB). Servir eso directamente en una
 * tarjeta de 140 px desperdicia ancho de banda y bloquea el render.
 *
 * Además, esas URLs quedan fijadas al host con el que se subió el archivo
 * (p. ej. http://localhost). Si el sitio se sirve desde otro dominio (ngrok,
 * producción), la imagen se rompe. Por eso se reescribe el host al baseurl de
 * uploads actual ANTES de buscar el adjunto.
 *
 * @param string $url  URL guardada en el meta.
 * @param string $size Tamaño registrado de WordPress a devolver.
 * @return array{id:int, url:string} id = 0 si no se pudo resolver el adjunto.
 */
function amazonia_resolve_community_image( $url, $size = 'large' ) {
	$url = trim( (string) $url );
	if ( '' === $url ) {
		return [ 'id' => 0, 'url' => '' ];
	}

	$uploads = wp_get_upload_dir();

	// Reescribe cualquier host anterior al baseurl de uploads vigente.
	if ( preg_match( '#/wp-content/uploads/(.+)$#', $url, $m ) ) {
		$url = trailingslashit( $uploads['baseurl'] ) . $m[1];
	}

	$cache_key = 'amazonia_img_' . md5( $url . '|' . $size );
	$cached    = get_transient( $cache_key );
	if ( is_array( $cached ) ) {
		return $cached;
	}

	$id     = attachment_url_to_postid( $url );
	$result = [ 'id' => (int) $id, 'url' => $url ];

	if ( $id ) {
		$src = wp_get_attachment_image_src( $id, $size );
		if ( $src ) {
			$result['url'] = $src[0];
		}
	}

	set_transient( $cache_key, $result, DAY_IN_SECONDS );

	return $result;
}

/**
 * Devuelve el <img> de una imagen de comunidad ya optimizada (srcset + lazy).
 * Si el adjunto no se puede resolver, cae a una <img> simple con la URL normalizada.
 *
 * @param string $url  URL guardada en el meta.
 * @param string $size Tamaño de WordPress.
 * @param string $alt  Texto alternativo.
 * @param array  $attr Atributos extra.
 * @return string HTML escapado listo para imprimir.
 */
function amazonia_community_image_html( $url, $size, $alt, $attr = [] ) {
	$img = amazonia_resolve_community_image( $url, $size );
	if ( '' === $img['url'] ) {
		return '';
	}

	$attr = wp_parse_args( $attr, [ 'alt' => $alt, 'loading' => 'lazy', 'decoding' => 'async' ] );

	if ( $img['id'] ) {
		// wp_get_attachment_image añade srcset/sizes automáticamente.
		return wp_get_attachment_image( $img['id'], $size, false, $attr );
	}

	$out = '<img src="' . esc_url( $img['url'] ) . '"';
	foreach ( $attr as $k => $v ) {
		$out .= ' ' . esc_attr( $k ) . '="' . esc_attr( $v ) . '"';
	}
	return $out . ' />';
}

/**
 * Nº de pedidos COMPLETADOS de un vendedor.
 *
 * Se lee de la tabla de pedidos de WCFM ({prefix}wcfm_marketplace_orders), que ya
 * guarda el estado por vendedor en `order_status`. Así funciona igual con HPOS
 * activo o sin él, sin tener que unir contra wc_orders/posts.
 *
 * Se cuenta DISTINCT order_id porque la tabla tiene una fila por línea de pedido.
 * El "tiempo medio de despacho" del diseño no se calcula: no hay un dato fiable
 * de fecha de despacho en esta instalación.
 *
 * @param int $vendor_id
 * @return int
 */
function amazonia_count_vendor_completed_orders( $vendor_id ) {
	global $wpdb;

	$vendor_id = absint( $vendor_id );
	if ( ! $vendor_id ) {
		return 0;
	}

	$table = $wpdb->prefix . 'wcfm_marketplace_orders';
	if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) !== $table ) {
		return 0;
	}

	return (int) $wpdb->get_var(
		$wpdb->prepare(
			"SELECT COUNT( DISTINCT order_id )
			   FROM {$table}
			  WHERE vendor_id    = %d
			    AND is_trashed   = 0
			    AND order_status = 'completed'",
			$vendor_id
		)
	);
}
