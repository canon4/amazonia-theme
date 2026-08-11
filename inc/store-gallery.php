<?php
/**
 * Galería de fotos del vendedor ("quiénes son"): taller, equipo, proceso.
 *
 * WCFM solo trae logo + banner (una imagen cada uno), sin galería. Este
 * archivo agrega un campo de galería libre (hasta amazonia_store_gallery_max
 * fotos, 6 por defecto) al formulario "Mi tienda > Ajustes > Store" de WCFM,
 * sin tocar el plugin:
 *
 *  1. Se inyecta el campo con el hook 'wcfm_vendor_settings_after_location'
 *     (existe dentro del mismo <form> que WCFM ya envía al guardar).
 *  2. Al guardar, WCFM dispara 'wcfm_wcfmmp_settings_update' con los datos
 *     crudos del POST; ahí leemos y guardamos nuestro campo.
 *  3. Se expone amazonia_get_store_gallery() para pintarla en el perfil
 *     público de la tienda (wcfm/store/wcfmmp-view-store.php).
 *
 * @package Amazonia_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Número máximo de fotos que un vendedor puede subir a su galería.
 */
function amazonia_store_gallery_max() {
	return (int) apply_filters( 'amazonia_store_gallery_max', 6 );
}

/**
 * IDs de adjunto válidos guardados en la galería de un vendedor.
 *
 * @param int $vendor_id
 * @return int[]
 */
function amazonia_get_store_gallery( $vendor_id ) {
	$vendor_id = absint( $vendor_id );
	if ( ! $vendor_id ) {
		return array();
	}

	$ids = get_user_meta( $vendor_id, '_amazonia_store_gallery', true );
	if ( ! is_array( $ids ) ) {
		return array();
	}

	$ids = array_slice( array_map( 'absint', $ids ), 0, amazonia_store_gallery_max() );

	// Descarta IDs huérfanos (adjunto borrado de la biblioteca de medios).
	return array_values( array_filter( $ids, function ( $id ) {
		return $id && get_post_type( $id ) === 'attachment';
	} ) );
}

/**
 * Encola el uploader (wp.media) solo en la página de Ajustes del dashboard WCFM.
 */
function amazonia_enqueue_store_gallery_admin_assets() {
	if ( ! is_page_template( 'template-wcfm-dashboard.php' ) ) {
		return;
	}

	wp_enqueue_media();
	amazonia_script( 'amazonia-store-gallery-admin', 'assets/js/store-gallery-admin.js', array( 'jquery' ) );
	wp_localize_script( 'amazonia-store-gallery-admin', 'amazoniaStoreGallery', array(
		'max'          => amazonia_store_gallery_max(),
		'title'        => __( 'Elige las fotos de tu galería', 'amazonia-theme' ),
		'button'       => __( 'Usar estas fotos', 'amazonia-theme' ),
		'limitMessage' => sprintf(
			/* translators: %d: número máximo de fotos permitido */
			__( 'Puedes subir hasta %d fotos.', 'amazonia-theme' ),
			amazonia_store_gallery_max()
		),
	) );
}
add_action( 'wp_enqueue_scripts', 'amazonia_enqueue_store_gallery_admin_assets' );

/**
 * Campo de galería en "Mi tienda > Ajustes > Store", justo después de "Location".
 *
 * @param int $user_id
 */
function amazonia_render_store_gallery_field( $user_id ) {
	$gallery_ids = amazonia_get_store_gallery( $user_id );
	$max         = amazonia_store_gallery_max();
	?>
	<div class="wcfm_clearfix"></div><br />
	<div class="page_collapsible" id="wcfm_settings_gallery_head">
		<label class="wcfmfa fa-image"></label>
		<?php esc_html_e( 'Galería de la tienda', 'amazonia-theme' ); ?><span></span>
	</div>
	<div class="wcfm-container">
		<div id="wcfm_settings_form_gallery_expander" class="wcfm-content">
			<div class="wcfm_vendor_settings_heading">
				<h2><?php esc_html_e( 'Galería de la tienda', 'amazonia-theme' ); ?></h2>
			</div>
			<p class="wcfm_page_options_desc">
				<?php
				printf(
					/* translators: %d: número máximo de fotos permitido */
					esc_html__( 'Muestra tu taller, tu equipo o el proceso de tus productos. Hasta %d fotos.', 'amazonia-theme' ),
					(int) $max
				);
				?>
			</p>
			<div class="wcfm_clearfix"></div><br />

			<input type="hidden" name="amazonia_store_gallery" id="amazonia_store_gallery_input"
			       value="<?php echo esc_attr( implode( ',', $gallery_ids ) ); ?>" />

			<div id="amazonia_store_gallery_grid" class="amazonia-gallery-admin-grid" data-max="<?php echo esc_attr( $max ); ?>">
				<?php foreach ( $gallery_ids as $attachment_id ) : ?>
					<div class="amazonia-gallery-admin-item" data-id="<?php echo esc_attr( $attachment_id ); ?>">
						<?php echo wp_get_attachment_image( $attachment_id, 'thumbnail' ); ?>
						<button type="button" class="amazonia-gallery-admin-remove" aria-label="<?php esc_attr_e( 'Quitar foto', 'amazonia-theme' ); ?>">&times;</button>
					</div>
				<?php endforeach; ?>
			</div>

			<button type="button" id="amazonia_store_gallery_add" class="wcfm_submit_button" style="margin-top:12px;">
				<?php esc_html_e( 'Agregar fotos', 'amazonia-theme' ); ?>
			</button>
		</div>
	</div>
	<div class="wcfm_clearfix"></div>
	<?php
}
add_action( 'wcfm_vendor_settings_after_location', 'amazonia_render_store_gallery_field' );

/**
 * Guarda la galería cuando WCFM procesa el guardado del formulario de Ajustes.
 *
 * @param int   $user_id
 * @param array $form_data POST crudo del formulario de ajustes de WCFM.
 */
function amazonia_save_store_gallery( $user_id, $form_data ) {
	$user_id = absint( $user_id );
	if ( ! $user_id ) {
		return;
	}

	$raw = isset( $form_data['amazonia_store_gallery'] ) ? (string) $form_data['amazonia_store_gallery'] : '';
	$ids = array_filter( array_map( 'absint', explode( ',', $raw ) ) );
	$ids = array_slice( array_values( array_unique( $ids ) ), 0, amazonia_store_gallery_max() );

	// Solo persiste adjuntos de imagen reales; evita que se guarde cualquier ID arbitrario.
	$ids = array_values( array_filter( $ids, function ( $id ) {
		return wp_attachment_is_image( $id );
	} ) );

	update_user_meta( $user_id, '_amazonia_store_gallery', $ids );
}
add_action( 'wcfm_wcfmmp_settings_update', 'amazonia_save_store_gallery', 10, 2 );
