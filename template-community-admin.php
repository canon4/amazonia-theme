<?php
/**
 * Template Name: Community Admin Panel
 *
 * Panel de gestión para administradores de comunidad.
 * Asignar esta plantilla a la página /community-admin/ en WordPress Admin.
 *
 * @package Amazonia_Theme
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// ─── Control de acceso ───────────────────────────────────────────────────────
if ( ! is_user_logged_in() ) {
	wp_redirect( wc_get_page_permalink( 'myaccount' ) );
	exit;
}

if ( ! amazonia_is_community_admin() ) {
	wp_redirect( home_url( '/' ) );
	exit;
}

$community_id = amazonia_get_managed_community_id();
if ( ! $community_id ) {
	// Logueado pero sin comunidad asignada aún
	get_header();
	echo '<div style="max-width:600px;margin:4rem auto;padding:2rem;text-align:center;font-family:Inter,sans-serif;">';
	echo '<span class="material-symbols-outlined" style="font-size:48px;color:#4ade80;">pending</span>';
	echo '<h2 style="margin:.5rem 0;">Comunidad pendiente de asignación</h2>';
	echo '<p style="color:#64748b;">Tu cuenta está activa pero aún no tienes una comunidad asignada. El administrador de Amazonia Market te asignará pronto.</p>';
	echo '<a href="' . esc_url( home_url( '/' ) ) . '" style="color:#4ade80;font-weight:600;">← Volver a la tienda</a>';
	echo '</div>';
	get_footer();
	exit;
}

$community  = amazonia_get_community_data( $community_id );
$vendors    = amazonia_get_community_vendors( $community_id );
$num_stores = count( $vendors );

// Contar pedidos totales de los vendors de la comunidad
$total_orders = 0;
foreach ( $vendors as $vendor ) {
	$vendor_orders = wc_get_orders( [
		'meta_key'   => '_vendor_id',
		'meta_value' => $vendor->ID,
		'return'     => 'ids',
		'limit'      => -1,
	] );
	$total_orders += count( $vendor_orders );
}

get_header();
?>

<style>
footer, .site-footer,
.woocommerce-breadcrumb, h1.page-title, h1.entry-title { display: none !important; }
main#primary, .site-main, .page-content, .type-page {
	padding: 0 !important; margin: 0 !important; max-width: 100% !important; width: 100% !important;
}
.woocommerce { margin: 0 !important; padding: 0 !important; width: 100% !important; }
body { margin: 0; padding: 0; }
</style>

<div class="ca-wrap">

	<div class="ca-container">

		<!-- Hero: info de la comunidad -->
		<div class="ca-hero">
			<?php if ( ! empty( $community['logo'] ) ) : ?>
				<img src="<?php echo esc_url( $community['logo'] ); ?>" alt="<?php echo esc_attr( $community['nombre'] ); ?>" class="ca-hero-logo" loading="lazy" width="80" height="80" />
			<?php else : ?>
				<div class="ca-hero-logo-placeholder">
					<span class="material-symbols-outlined">groups</span>
				</div>
			<?php endif; ?>

			<div class="ca-hero-info">
				<h1><?php echo esc_html( $community['nombre'] ); ?></h1>
				<?php if ( $community['descripcion'] ) : ?>
					<p style="margin:.25rem 0 .5rem;color:#64748b;font-size:.9rem;">
						<?php echo esc_html( $community['descripcion'] ); ?>
					</p>
				<?php endif; ?>
				<div class="ca-hero-meta">
					<?php if ( $community['pais'] || $community['departamento'] ) : ?>
						<span>
							<span class="material-symbols-outlined">location_on</span>
							<?php echo esc_html( implode( ', ', array_filter( [ $community['municipio'], $community['departamento'], $community['pais'] ] ) ) ); ?>
						</span>
					<?php endif; ?>
					<?php if ( $community['categoria'] ) : ?>
						<span>
							<span class="material-symbols-outlined">label</span>
							<?php echo esc_html( $community['categoria'] ); ?>
						</span>
					<?php endif; ?>
				</div>
			</div>
		</div>

		<!-- Métricas -->
		<div class="ca-metrics">
			<div class="ca-metric-card">
				<div class="ca-metric-icon"><span class="material-symbols-outlined">storefront</span></div>
				<div class="ca-metric-value" id="ca-metric-stores"><?php echo esc_html( $num_stores ); ?></div>
				<div class="ca-metric-label"><?php esc_html_e( 'Tiendas activas', 'amazonia-theme' ); ?></div>
			</div>
			<div class="ca-metric-card">
				<div class="ca-metric-icon"><span class="material-symbols-outlined">shopping_bag</span></div>
				<div class="ca-metric-value"><?php echo esc_html( $total_orders ); ?></div>
				<div class="ca-metric-label"><?php esc_html_e( 'Pedidos totales', 'amazonia-theme' ); ?></div>
			</div>
		</div>

		<!-- Editar información de la comunidad -->
		<div class="ca-card ca-edit-card">
			<div class="ca-card-header ca-edit-toggle" role="button" tabindex="0" aria-expanded="false">
				<span class="material-symbols-outlined">edit_note</span>
				<h2><?php esc_html_e( 'Editar información de la comunidad', 'amazonia-theme' ); ?></h2>
				<span class="material-symbols-outlined ca-edit-chevron" style="margin-left:auto;transition:transform .25s;">expand_more</span>
			</div>
			<div class="ca-edit-body" style="display:none;">
				<div class="ca-card-body">
					<form id="ca-edit-form" class="ca-form" novalidate>

						<div class="ca-field">
							<label for="ca-nombre"><?php esc_html_e( 'Nombre de la comunidad', 'amazonia-theme' ); ?> *</label>
							<input type="text" name="nombre" id="ca-nombre" required
								value="<?php echo esc_attr( $community['nombre'] ); ?>" />
						</div>

						<div class="ca-field">
							<label for="ca-descripcion"><?php esc_html_e( 'Descripción corta', 'amazonia-theme' ); ?></label>
							<textarea name="descripcion" id="ca-descripcion" rows="3"><?php echo esc_textarea( $community['descripcion'] ); ?></textarea>
						</div>

						<div class="ca-field">
							<label for="ca-historia"><?php esc_html_e( 'Historia / Sobre la comunidad', 'amazonia-theme' ); ?></label>
							<textarea name="historia" id="ca-historia" rows="5"><?php echo esc_textarea( $community['historia'] ); ?></textarea>
						</div>

						<div class="ca-field-row">
							<div class="ca-field">
								<label for="ca-pais"><?php esc_html_e( 'País', 'amazonia-theme' ); ?></label>
								<input type="text" name="pais" id="ca-pais" value="<?php echo esc_attr( $community['pais'] ); ?>" placeholder="Colombia" />
							</div>
							<div class="ca-field">
								<label for="ca-departamento"><?php esc_html_e( 'Departamento / Región', 'amazonia-theme' ); ?></label>
								<input type="text" name="departamento" id="ca-departamento" value="<?php echo esc_attr( $community['departamento'] ); ?>" placeholder="Amazonas" />
							</div>
						</div>

						<div class="ca-field-row">
							<div class="ca-field">
								<label for="ca-municipio"><?php esc_html_e( 'Municipio', 'amazonia-theme' ); ?></label>
								<input type="text" name="municipio" id="ca-municipio" value="<?php echo esc_attr( $community['municipio'] ); ?>" placeholder="Leticia" />
							</div>
							<div class="ca-field">
								<label for="ca-categoria"><?php esc_html_e( 'Categoría', 'amazonia-theme' ); ?></label>
								<input type="text" name="categoria" id="ca-categoria" value="<?php echo esc_attr( $community['categoria'] ); ?>" placeholder="Artesanías" />
							</div>
						</div>

						<div class="ca-field">
							<label><?php esc_html_e( 'Logo de la comunidad', 'amazonia-theme' ); ?></label>
							<?php $logo_meta = get_post_meta( $community_id, '_comunidad_logo_url', true ); ?>
							<input type="hidden" name="logo_url" id="ca-logo-url" value="<?php echo esc_attr( $logo_meta ); ?>" />
							<div class="ca-logo-uploader">
								<div id="ca-logo-preview" class="ca-logo-preview <?php echo $logo_meta ? 'has-image' : ''; ?>">
									<?php if ( $logo_meta ) : ?>
										<img id="ca-logo-img" src="<?php echo esc_url( $logo_meta ); ?>" alt="Logo" />
									<?php else : ?>
										<span class="material-symbols-outlined ca-logo-placeholder-icon">add_photo_alternate</span>
									<?php endif; ?>
								</div>
								<div class="ca-logo-actions">
									<button type="button" id="ca-logo-upload-btn" class="ca-btn-outline">
										<span class="material-symbols-outlined">upload</span>
										<?php echo $logo_meta ? esc_html__( 'Cambiar imagen', 'amazonia-theme' ) : esc_html__( 'Subir imagen', 'amazonia-theme' ); ?>
									</button>
									<button type="button" id="ca-logo-remove-btn" class="ca-btn-danger" style="<?php echo $logo_meta ? '' : 'display:none;'; ?>">
										<span class="material-symbols-outlined">delete</span>
										<?php esc_html_e( 'Eliminar', 'amazonia-theme' ); ?>
									</button>
								</div>
							</div>
						</div>

						<!-- Imagen de portada (banner) -->
						<div class="ca-field">
							<label><?php esc_html_e( 'Imagen de portada (banner)', 'amazonia-theme' ); ?></label>
							<p style="font-size:.8rem;color:#94a3b8;margin:-.25rem 0 .5rem;">
								<?php esc_html_e( 'Se usa como fondo del hero en la página pública de la comunidad.', 'amazonia-theme' ); ?>
							</p>
							<input type="hidden" name="banner_url" id="ca-banner-url" value="<?php echo esc_attr( $community['banner'] ); ?>" />
							<div class="ca-logo-uploader">
								<div id="ca-banner-preview" class="ca-banner-preview <?php echo $community['banner'] ? 'has-image' : ''; ?>"
									<?php if ( $community['banner'] ) : ?>style="background-image:url('<?php echo esc_url( $community['banner'] ); ?>')"<?php endif; ?>>
									<?php if ( ! $community['banner'] ) : ?>
										<span class="material-symbols-outlined ca-logo-placeholder-icon">panorama</span>
									<?php endif; ?>
								</div>
								<div class="ca-logo-actions">
									<button type="button" id="ca-banner-upload-btn" class="ca-btn-outline">
										<span class="material-symbols-outlined">upload</span>
										<?php echo $community['banner'] ? esc_html__( 'Cambiar portada', 'amazonia-theme' ) : esc_html__( 'Subir portada', 'amazonia-theme' ); ?>
									</button>
									<button type="button" id="ca-banner-remove-btn" class="ca-btn-danger" style="<?php echo $community['banner'] ? '' : 'display:none;'; ?>">
										<span class="material-symbols-outlined">delete</span>
										<?php esc_html_e( 'Eliminar', 'amazonia-theme' ); ?>
									</button>
								</div>
							</div>
						</div>

						<!-- Galería de fotos -->
						<div class="ca-field">
							<label><?php esc_html_e( 'Galería de fotos', 'amazonia-theme' ); ?></label>
							<p style="font-size:.8rem;color:#94a3b8;margin:-.25rem 0 .5rem;">
								<?php esc_html_e( 'Proceso productivo, paisaje, artesanos — construye confianza visual.', 'amazonia-theme' ); ?>
							</p>
							<input type="hidden" name="galeria_ids" id="ca-galeria-ids"
								value="<?php echo esc_attr( wp_json_encode( $community['galeria_ids'] ) ); ?>" />
							<div class="ca-gallery-grid" id="ca-gallery-grid">
								<?php foreach ( $community['galeria_ids'] as $att_id ) :
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
						</div>

						<!-- Imágenes de Storytelling (cards de producto) -->
						<div class="ca-field">
							<label><?php esc_html_e( 'Imágenes de Storytelling', 'amazonia-theme' ); ?></label>
							<p style="font-size:.8rem;color:#94a3b8;margin:-.25rem 0 .75rem;">
								<?php esc_html_e( 'Estas imágenes aparecen en las cards de descripción de cada producto de la comunidad.', 'amazonia-theme' ); ?>
							</p>
							<div style="display:flex;flex-direction:column;gap:1rem;">
								<?php
								$story_fields = [
									1 => [ 'label' => 'Card 1 — La Comunidad',        'key' => 'storytelling_img_1' ],
									2 => [ 'label' => 'Card 2 — Tradición & Cultura',  'key' => 'storytelling_img_2' ],
									3 => [ 'label' => 'Card 3 — Valores',              'key' => 'storytelling_img_3' ],
								];
								foreach ( $story_fields as $n => $sf ) :
									$val = $community[ $sf['key'] ] ?? '';
								?>
								<div style="display:flex;gap:12px;align-items:center;">
									<?php if ( $val ) : ?>
										<img id="ca-story-<?php echo $n; ?>-preview"
										     src="<?php echo esc_url( $val ); ?>"
										     style="width:100px;height:68px;object-fit:cover;border-radius:6px;border:1px solid #334155;flex-shrink:0;" />
									<?php else : ?>
										<div id="ca-story-<?php echo $n; ?>-preview"
										     style="width:100px;height:68px;border-radius:6px;border:2px dashed #334155;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
											<span class="material-symbols-outlined" style="color:#64748b;font-size:28px;">panorama</span>
										</div>
									<?php endif; ?>
									<div>
										<p style="font-size:.8rem;font-weight:600;color:#94a3b8;margin:0 0 6px;">
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
									<input type="hidden" name="<?php echo esc_attr( $sf['key'] ); ?>"
									       id="ca-story-<?php echo $n; ?>-url"
									       value="<?php echo esc_attr( $val ); ?>" />
								</div>
								<?php endforeach; ?>
							</div>
						</div>

						<!-- Video de presentación -->
						<div class="ca-field">
							<label for="ca-video-url"><?php esc_html_e( 'Video de presentación', 'amazonia-theme' ); ?></label>
							<input type="url" name="video_url" id="ca-video-url"
								value="<?php echo esc_attr( $community['video_url'] ); ?>"
								placeholder="https://www.youtube.com/watch?v=..." />
							<?php $embed = amazonia_get_video_embed_url( $community['video_url'] ); ?>
							<div id="ca-video-preview" class="ca-video-preview" style="<?php echo $embed ? '' : 'display:none;'; ?>">
								<iframe src="<?php echo esc_url( $embed ); ?>" frameborder="0" allowfullscreen
									allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"></iframe>
							</div>
						</div>

						<!-- Datos clave -->
						<div class="ca-field-row">
							<div class="ca-field">
								<label for="ca-fundacion"><?php esc_html_e( 'Año de fundación', 'amazonia-theme' ); ?></label>
								<input type="text" name="fundacion" id="ca-fundacion"
									value="<?php echo esc_attr( $community['fundacion'] ); ?>" placeholder="1987" />
							</div>
							<div class="ca-field">
								<label for="ca-num-familias"><?php esc_html_e( 'Familias / artesanos', 'amazonia-theme' ); ?></label>
								<input type="text" name="num_familias" id="ca-num-familias"
									value="<?php echo esc_attr( $community['num_familias'] ); ?>" placeholder="42 familias" />
							</div>
						</div>

						<!-- Valores -->
						<?php
						$valor_icons = [
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
						?>
						<script type="application/json" id="ca-valor-icons-data">
						<?php echo wp_json_encode( $valor_icons ); ?>
						</script>

						<div class="ca-field">
							<label><?php esc_html_e( 'Valores de la comunidad', 'amazonia-theme' ); ?> <span style="color:#94a3b8;font-weight:400;"><?php esc_html_e( '(máx. 4)', 'amazonia-theme' ); ?></span></label>
							<input type="hidden" name="valores" id="ca-valores-json"
								value="<?php echo esc_attr( wp_json_encode( $community['valores'] ) ); ?>" />
							<div id="ca-valores-list" style="display:flex;flex-direction:column;gap:.5rem;margin-bottom:.75rem;">
								<?php foreach ( $community['valores'] as $valor ) : ?>
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
							<button type="button" id="ca-valor-add-btn" class="ca-btn-outline"
								<?php echo count( $community['valores'] ) >= 4 ? 'disabled' : ''; ?>>
								<span class="material-symbols-outlined">add</span>
								<?php esc_html_e( 'Añadir valor', 'amazonia-theme' ); ?>
							</button>
						</div>

						<!-- Redes sociales -->
						<div class="ca-field-row">
							<div class="ca-field">
								<label for="ca-instagram">
									<span class="material-symbols-outlined" style="font-size:15px;vertical-align:middle;">photo_camera</span>
									<?php esc_html_e( 'Instagram', 'amazonia-theme' ); ?>
								</label>
								<input type="url" name="instagram" id="ca-instagram"
									value="<?php echo esc_attr( $community['instagram'] ); ?>"
									placeholder="https://instagram.com/..." />
							</div>
							<div class="ca-field">
								<label for="ca-facebook">
									<span class="material-symbols-outlined" style="font-size:15px;vertical-align:middle;">thumb_up</span>
									<?php esc_html_e( 'Facebook', 'amazonia-theme' ); ?>
								</label>
								<input type="url" name="facebook" id="ca-facebook"
									value="<?php echo esc_attr( $community['facebook'] ); ?>"
									placeholder="https://facebook.com/..." />
							</div>
						</div>

						<!-- Certificaciones -->
						<div class="ca-field">
							<label for="ca-certificaciones"><?php esc_html_e( 'Certificaciones', 'amazonia-theme' ); ?></label>
							<input type="text" name="certificaciones" id="ca-certificaciones"
								value="<?php echo esc_attr( $community['certificaciones'] ); ?>"
								placeholder="Comercio Justo · Orgánico · WFTO" />
						</div>

						<button type="submit" class="ca-btn" data-original-text="<?php esc_attr_e( 'Guardar cambios', 'amazonia-theme' ); ?>">
							<?php esc_html_e( 'Guardar cambios', 'amazonia-theme' ); ?>
						</button>
						<div id="ca-edit-feedback" class="ca-feedback"></div>

						<input type="file" id="ca-logo-file"    accept="image/*"          style="display:none;" />
						<input type="file" id="ca-banner-file"  accept="image/*"          style="display:none;" />
						<input type="file" id="ca-gallery-file" accept="image/*" multiple style="display:none;" />
						<input type="file" id="ca-story-1-file" accept="image/*"          style="display:none;" />
						<input type="file" id="ca-story-2-file" accept="image/*"          style="display:none;" />
						<input type="file" id="ca-story-3-file" accept="image/*"          style="display:none;" />

					</form>
				</div>
			</div>
		</div>

		<!-- Grid principal -->
		<div class="ca-grid">

			<!-- Tiendas de la comunidad -->
			<div class="ca-card">
				<div class="ca-card-header">
					<span class="material-symbols-outlined">storefront</span>
					<h2><?php esc_html_e( 'Tiendas de la comunidad', 'amazonia-theme' ); ?></h2>
				</div>
				<div class="ca-card-body">
					<ul class="ca-store-list" id="ca-store-list">
						<?php if ( empty( $vendors ) ) : ?>
							<li class="ca-empty">
								<span class="material-symbols-outlined">store</span>
								<?php esc_html_e( 'Aún no hay tiendas. Crea la primera abajo.', 'amazonia-theme' ); ?>
							</li>
						<?php else : ?>
							<?php foreach ( $vendors as $vendor ) :
								$store_name = wcfm_get_vendor_store_name( $vendor->ID );
								$logo       = function_exists( 'wcfm_get_vendor_store_logo_by_vendor' ) ? wcfm_get_vendor_store_logo_by_vendor( $vendor->ID ) : '';
								$store_url  = function_exists( 'wcfmmp_get_store_url' ) ? wcfmmp_get_store_url( $vendor->ID ) : get_author_posts_url( $vendor->ID );
							?>
								<li class="ca-store-item">
									<?php if ( $logo ) : ?>
										<img src="<?php echo esc_url( $logo ); ?>" class="ca-store-avatar" alt="<?php echo esc_attr( $store_name ); ?>" loading="lazy" width="80" height="80" />
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
								</li>
							<?php endforeach; ?>
						<?php endif; ?>
					</ul>
				</div>
			</div>

			<!-- Columna derecha: acciones -->
			<div style="display:flex;flex-direction:column;gap:1.5rem;">

				<!-- Crear nueva tienda -->
				<div class="ca-card">
					<div class="ca-card-header">
						<span class="material-symbols-outlined">add_business</span>
						<h2><?php esc_html_e( 'Crear nueva tienda', 'amazonia-theme' ); ?></h2>
					</div>
					<div class="ca-card-body">
						<form id="ca-create-form" class="ca-form" novalidate>
							<div class="ca-field">
								<label for="ca-store-name"><?php esc_html_e( 'Nombre de la tienda', 'amazonia-theme' ); ?> *</label>
								<input type="text" name="store_name" id="ca-store-name" required
									placeholder="<?php esc_attr_e( 'Ej: Artesanías Shipibo', 'amazonia-theme' ); ?>" />
							</div>
							<div class="ca-field">
								<label for="ca-email"><?php esc_html_e( 'Email del vendedor', 'amazonia-theme' ); ?> *</label>
								<input type="email" name="email" id="ca-email" required
									placeholder="vendedor@ejemplo.com" />
							</div>
							<div class="ca-field-row">
								<div class="ca-field">
									<label for="ca-first-name"><?php esc_html_e( 'Nombre', 'amazonia-theme' ); ?></label>
									<input type="text" name="first_name" id="ca-first-name"
										placeholder="<?php esc_attr_e( 'Juan', 'amazonia-theme' ); ?>" />
								</div>
								<div class="ca-field">
									<label for="ca-last-name"><?php esc_html_e( 'Apellido', 'amazonia-theme' ); ?></label>
									<input type="text" name="last_name" id="ca-last-name"
										placeholder="<?php esc_attr_e( 'Pérez', 'amazonia-theme' ); ?>" />
								</div>
							</div>
							<p style="font-size:.8rem;color:#94a3b8;margin:-.25rem 0 0;">
								<?php esc_html_e( 'Se generará una contraseña automática y se enviará al email del vendedor.', 'amazonia-theme' ); ?>
							</p>
							<button type="submit" class="ca-btn">
								<?php esc_html_e( 'Crear tienda', 'amazonia-theme' ); ?>
							</button>
							<div id="ca-create-feedback" class="ca-feedback"></div>
						</form>
					</div>
				</div>

				<!-- Vincular tienda existente -->
				<div class="ca-card">
					<div class="ca-card-header">
						<span class="material-symbols-outlined">link</span>
						<h2><?php esc_html_e( 'Vincular tienda existente', 'amazonia-theme' ); ?></h2>
					</div>
					<div class="ca-card-body">
						<div class="ca-field">
							<label for="ca-search-input"><?php esc_html_e( 'Buscar por nombre o email', 'amazonia-theme' ); ?></label>
							<input type="text" id="ca-search-input"
								placeholder="<?php esc_attr_e( 'Escribe al menos 2 caracteres...', 'amazonia-theme' ); ?>" />
						</div>
						<div id="ca-search-results" class="ca-search-results"></div>
						<div id="ca-link-feedback" class="ca-feedback"></div>
					</div>
				</div>

			</div>
		</div>

	</div><!-- .ca-container -->
</div><!-- .ca-wrap -->

<?php get_footer(); ?>
