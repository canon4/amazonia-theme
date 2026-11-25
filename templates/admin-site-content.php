<?php
/**
 * Panel de administración: Contenido del sitio
 * Renderizado por amazonia_render_site_content_page() en inc/site-content.php
 *
 * @package Amazonia_Theme
 */

if ( ! defined( 'ABSPATH' ) ) exit;
if ( ! current_user_can( 'manage_options' ) ) return;

$schema    = amazonia_site_content_schema();
$saved     = amazonia_site_content_saved();
$tab_keys  = array_keys( $schema );
$active    = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : $tab_keys[0]; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$active    = in_array( $active, $tab_keys, true ) ? $active : $tab_keys[0];

/**
 * Selector de una sola imagen (campo `image` y `site_icon`).
 */
$render_image_field = function ( $name, $attachment_id, $fallback_rel = '' ) {
	$attachment_id = absint( $attachment_id );
	$thumb         = $attachment_id ? wp_get_attachment_image_url( $attachment_id, 'medium' ) : '';
	$fallback_url  = $fallback_rel ? get_template_directory_uri() . '/' . ltrim( $fallback_rel, '/' ) : '';
	?>
	<div class="asc-image" data-fallback="<?php echo esc_url( $fallback_url ); ?>">
		<input type="hidden" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $attachment_id ?: '' ); ?>" class="asc-image__input" />
		<div class="asc-image__preview<?php echo $thumb ? '' : ( $fallback_url ? ' is-default' : ' is-empty' ); ?>">
			<?php if ( $thumb ) : ?>
				<img src="<?php echo esc_url( $thumb ); ?>" alt="" />
			<?php elseif ( $fallback_url ) : ?>
				<img src="<?php echo esc_url( $fallback_url ); ?>" alt="" />
			<?php endif; ?>
			<span class="asc-image__badge"><?php esc_html_e( 'Imagen original del tema', 'amazonia-theme' ); ?></span>
		</div>
		<p class="asc-image__actions">
			<button type="button" class="button asc-image__choose"><?php esc_html_e( 'Elegir imagen', 'amazonia-theme' ); ?></button>
			<button type="button" class="button-link button-link-delete asc-image__remove"<?php echo $attachment_id ? '' : ' hidden'; ?>><?php esc_html_e( 'Quitar', 'amazonia-theme' ); ?></button>
		</p>
	</div>
	<?php
};
?>
<div class="wrap asc-wrap">
	<h1><?php esc_html_e( 'Contenido del sitio', 'amazonia-theme' ); ?></h1>
	<p class="description">
		<?php esc_html_e( 'Administra las imágenes, textos y redes del sitio. Si dejas un texto o una imagen vacíos, se usa el original del tema: el sitio nunca queda en blanco.', 'amazonia-theme' ); ?>
	</p>

	<?php if ( isset( $_GET['saved'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>
		<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Cambios guardados.', 'amazonia-theme' ); ?></p></div>
	<?php endif; ?>
	<?php if ( isset( $_GET['icon_error'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>
		<div class="notice notice-error is-dismissible"><p><?php esc_html_e( 'El favicon no se actualizó: debe ser una imagen cuadrada de al menos 512×512 px. El resto de cambios sí se guardó.', 'amazonia-theme' ); ?></p></div>
	<?php endif; ?>

	<h2 class="nav-tab-wrapper asc-tabs">
		<?php foreach ( $schema as $tab_key => $tab ) : ?>
			<a href="#<?php echo esc_attr( $tab_key ); ?>" class="nav-tab<?php echo $tab_key === $active ? ' nav-tab-active' : ''; ?>" data-tab="<?php echo esc_attr( $tab_key ); ?>">
				<?php echo esc_html( $tab['label'] ); ?>
			</a>
		<?php endforeach; ?>
	</h2>

	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" id="asc-form">
		<input type="hidden" name="action" value="amazonia_save_site_content" />
		<input type="hidden" name="amazonia_active_tab" id="asc-active-tab" value="<?php echo esc_attr( $active ); ?>" />
		<?php wp_nonce_field( 'amazonia_save_site_content' ); ?>

		<?php foreach ( $schema as $tab_key => $tab ) : ?>
			<div class="asc-panel" data-panel="<?php echo esc_attr( $tab_key ); ?>"<?php echo $tab_key === $active ? '' : ' hidden'; ?>>

				<?php foreach ( $tab['groups'] as $group ) : ?>
					<section class="asc-group">
						<h3><?php echo esc_html( $group['title'] ); ?></h3>
						<?php if ( ! empty( $group['desc'] ) ) : ?>
							<p class="description"><?php echo esc_html( $group['desc'] ); ?></p>
						<?php endif; ?>

						<?php foreach ( $group['fields'] as $key => $field ) :
							$name  = 'amazonia_content[' . $key . ']';
							$value = isset( $saved[ $key ] ) ? $saved[ $key ] : '';
							$id    = 'asc-' . $key;
							?>
							<div class="asc-field asc-field--<?php echo esc_attr( $field['type'] ); ?>">

								<?php if ( 'slides' === $field['type'] ) : ?>
									<?php $slides = amazonia_get_home_carousel(); ?>
									<label><?php echo esc_html( $field['label'] ); ?></label>
									<?php if ( ! empty( $field['hint'] ) ) : ?>
										<p class="description"><?php echo esc_html( $field['hint'] ); ?></p>
									<?php endif; ?>

									<?php if ( ! $slides ) : ?>
										<p class="asc-notice-default"><?php esc_html_e( 'Ahora se muestra el carrusel original del tema (10 imágenes). Agrega imágenes para reemplazarlo.', 'amazonia-theme' ); ?></p>
									<?php endif; ?>

									<ul class="asc-slides" id="asc-slides" data-name="<?php echo esc_attr( $name ); ?>">
										<?php foreach ( $slides as $i => $slide ) : ?>
											<li class="asc-slide">
												<span class="asc-slide__handle dashicons dashicons-menu" aria-hidden="true"></span>
												<img src="<?php echo esc_url( wp_get_attachment_image_url( $slide['id'], 'thumbnail' ) ); ?>" alt="" />
												<div class="asc-slide__fields">
													<input type="hidden" name="<?php echo esc_attr( $name . '[' . $i . '][id]' ); ?>" value="<?php echo esc_attr( $slide['id'] ); ?>" />
													<label><?php esc_html_e( 'Texto alternativo', 'amazonia-theme' ); ?>
														<input type="text" name="<?php echo esc_attr( $name . '[' . $i . '][alt]' ); ?>" value="<?php echo esc_attr( $slide['alt'] ); ?>" />
													</label>
													<label><?php esc_html_e( 'Enlace (opcional)', 'amazonia-theme' ); ?>
														<input type="url" name="<?php echo esc_attr( $name . '[' . $i . '][link]' ); ?>" value="<?php echo esc_attr( $slide['link'] ); ?>" placeholder="https://" />
													</label>
												</div>
												<button type="button" class="button-link button-link-delete asc-slide__remove"><?php esc_html_e( 'Quitar', 'amazonia-theme' ); ?></button>
											</li>
										<?php endforeach; ?>
									</ul>
									<button type="button" class="button" id="asc-slides-add"><?php esc_html_e( 'Agregar imágenes', 'amazonia-theme' ); ?></button>

								<?php elseif ( 'image' === $field['type'] ) : ?>
									<label><?php echo esc_html( $field['label'] ); ?></label>
									<?php if ( ! empty( $field['hint'] ) ) : ?>
										<p class="description"><?php echo esc_html( $field['hint'] ); ?></p>
									<?php endif; ?>
									<?php $render_image_field( $name, amazonia_content_image_id( $key ), isset( $field['fallback'] ) ? $field['fallback'] : '' ); ?>

								<?php elseif ( 'site_icon' === $field['type'] ) : ?>
									<label><?php echo esc_html( $field['label'] ); ?></label>
									<?php if ( ! empty( $field['hint'] ) ) : ?>
										<p class="description"><?php echo esc_html( $field['hint'] ); ?></p>
									<?php endif; ?>
									<?php $render_image_field( $name, (int) get_option( 'site_icon', 0 ), '' ); ?>

								<?php elseif ( 'textarea' === $field['type'] ) : ?>
									<label for="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $field['label'] ); ?></label>
									<textarea id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $name ); ?>" rows="3" class="large-text" placeholder="<?php echo esc_attr( isset( $field['default'] ) ? $field['default'] : '' ); ?>"><?php echo esc_textarea( is_string( $value ) ? $value : '' ); ?></textarea>

								<?php else : ?>
									<label for="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $field['label'] ); ?></label>
									<input type="<?php echo esc_attr( in_array( $field['type'], array( 'url', 'email' ), true ) ? $field['type'] : 'text' ); ?>"
									       id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $name ); ?>" class="regular-text"
									       value="<?php echo esc_attr( is_string( $value ) ? $value : '' ); ?>"
									       placeholder="<?php echo esc_attr( isset( $field['default'] ) ? $field['default'] : ( isset( $field['placeholder'] ) ? $field['placeholder'] : '' ) ); ?>" />
								<?php endif; ?>

							</div>
						<?php endforeach; ?>
					</section>
				<?php endforeach; ?>

			</div>
		<?php endforeach; ?>

		<?php submit_button( __( 'Guardar cambios', 'amazonia-theme' ) ); ?>
	</form>
</div>
