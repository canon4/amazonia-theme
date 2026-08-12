<?php
/**
 * Galería de fotos del vendedor ("quiénes son"): taller, equipo, proceso.
 *
 * WCFM solo trae logo + banner (una imagen cada uno), sin galería, y su
 * dashboard renderiza el contenido de "Ajustes" de forma que un hook normal
 * de WordPress (do_action dentro de la plantilla del formulario) no es fiable
 * para inyectar campos propios ahí. Por eso esta función NO toca el
 * formulario de WCFM en absoluto: es un botón flotante independiente con su
 * propio modal y su propio guardado por AJAX, que vive por fuera del <form>
 * de WCFM.
 *
 *  1. amazonia_render_store_gallery_fab(), enganchado a 'wp_footer' (se
 *     imprime siempre, sin depender de cómo WCFM arme su HTML).
 *  2. Guardado vía wp_ajax_amazonia_save_store_gallery, independiente del
 *     guardado de WCFM.
 *  3. amazonia_get_store_gallery() se usa en el perfil público de la tienda
 *     (wcfm/store/wcfmmp-view-store.php).
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
 * ¿El usuario actual es un vendedor con tienda WCFM? Único requisito para
 * ver el botón flotante y para poder guardar su galería.
 */
function amazonia_current_user_is_vendor() {
	if ( ! is_user_logged_in() || ! function_exists( 'wcfmmp_get_store' ) ) {
		return false;
	}
	$store = wcfmmp_get_store( get_current_user_id() );
	return ! empty( $store );
}

/**
 * Encola el uploader (wp.media) + el script del botón flotante, solo en el
 * dashboard de WCFM y solo para vendedores.
 */
function amazonia_enqueue_store_gallery_admin_assets() {
	if ( ! is_page_template( 'template-wcfm-dashboard.php' ) || ! amazonia_current_user_is_vendor() ) {
		return;
	}

	wp_enqueue_media();
	amazonia_script( 'amazonia-store-gallery-admin', 'assets/js/store-gallery-admin.js', array( 'jquery' ) );

	wp_localize_script( 'amazonia-store-gallery-admin', 'amazoniaStoreGallery', array(
		'ajaxUrl'      => admin_url( 'admin-ajax.php' ),
		'nonce'        => wp_create_nonce( 'amazonia-store-gallery-nonce' ),
		'max'          => amazonia_store_gallery_max(),
		'title'        => __( 'Elige las fotos de tu galería', 'amazonia-theme' ),
		'button'       => __( 'Usar estas fotos', 'amazonia-theme' ),
		'limitMessage' => sprintf(
			/* translators: %d: número máximo de fotos permitido */
			__( 'Puedes subir hasta %d fotos.', 'amazonia-theme' ),
			amazonia_store_gallery_max()
		),
		'saved'        => __( 'Galería guardada.', 'amazonia-theme' ),
		'saveError'    => __( 'No se pudo guardar la galería. Intenta de nuevo.', 'amazonia-theme' ),
	) );
}
add_action( 'wp_enqueue_scripts', 'amazonia_enqueue_store_gallery_admin_assets' );

/**
 * Botón flotante + modal de la galería. Se imprime en wp_footer, por fuera
 * de cualquier formulario o markup de WCFM, así que no depende de cómo el
 * plugin renderice (o no) su propio HTML.
 */
function amazonia_render_store_gallery_fab() {
	if ( ! is_page_template( 'template-wcfm-dashboard.php' ) || ! amazonia_current_user_is_vendor() ) {
		return;
	}

	$gallery_ids = amazonia_get_store_gallery( get_current_user_id() );
	$max         = amazonia_store_gallery_max();
	?>
	<button type="button" id="amazonia_gallery_fab" class="amazonia-gallery-fab"
	        aria-label="<?php esc_attr_e( 'Galería de la tienda', 'amazonia-theme' ); ?>">
		<span class="material-symbols-outlined" aria-hidden="true">photo_library</span>
	</button>

	<div id="amazonia_gallery_modal" class="amazonia-gallery-modal" hidden>
		<div class="amazonia-gallery-modal__panel" role="dialog" aria-modal="true"
		     aria-labelledby="amazonia_gallery_modal_title">
			<div class="amazonia-gallery-modal__head">
				<h2 id="amazonia_gallery_modal_title"><?php esc_html_e( 'Galería de la tienda', 'amazonia-theme' ); ?></h2>
				<button type="button" class="amazonia-gallery-modal__close" aria-label="<?php esc_attr_e( 'Cerrar', 'amazonia-theme' ); ?>">&times;</button>
			</div>
			<p class="amazonia-gallery-modal__hint">
				<?php
				printf(
					/* translators: %d: número máximo de fotos permitido */
					esc_html__( 'Muestra tu taller, tu equipo o el proceso de tus productos. Hasta %d fotos.', 'amazonia-theme' ),
					(int) $max
				);
				?>
			</p>

			<div id="amazonia_store_gallery_grid" class="amazonia-gallery-admin-grid" data-max="<?php echo esc_attr( $max ); ?>">
				<?php foreach ( $gallery_ids as $attachment_id ) : ?>
					<div class="amazonia-gallery-admin-item" data-id="<?php echo esc_attr( $attachment_id ); ?>">
						<?php echo wp_get_attachment_image( $attachment_id, 'thumbnail' ); ?>
						<button type="button" class="amazonia-gallery-admin-remove" aria-label="<?php esc_attr_e( 'Quitar foto', 'amazonia-theme' ); ?>">&times;</button>
					</div>
				<?php endforeach; ?>
			</div>

			<div class="amazonia-gallery-modal__actions">
				<button type="button" id="amazonia_store_gallery_add" class="amazonia-gallery-btn amazonia-gallery-btn--ghost">
					<?php esc_html_e( 'Agregar fotos', 'amazonia-theme' ); ?>
				</button>
				<button type="button" id="amazonia_store_gallery_save" class="amazonia-gallery-btn amazonia-gallery-btn--solid">
					<?php esc_html_e( 'Guardar', 'amazonia-theme' ); ?>
				</button>
			</div>
			<p id="amazonia_store_gallery_status" class="amazonia-gallery-modal__status" role="status"></p>
		</div>
	</div>
	<?php
}
add_action( 'wp_footer', 'amazonia_render_store_gallery_fab' );

/**
 * Guarda la galería vía AJAX. Independiente por completo del formulario y
 * del flujo de guardado de WCFM.
 */
function amazonia_ajax_save_store_gallery() {
	check_ajax_referer( 'amazonia-store-gallery-nonce', 'nonce' );

	if ( ! amazonia_current_user_is_vendor() ) {
		wp_send_json_error( array( 'message' => __( 'No autorizado.', 'amazonia-theme' ) ) );
	}

	$user_id = get_current_user_id();
	$raw     = isset( $_POST['ids'] ) ? (string) wp_unslash( $_POST['ids'] ) : '';
	$ids     = array_filter( array_map( 'absint', explode( ',', $raw ) ) );
	$ids     = array_slice( array_values( array_unique( $ids ) ), 0, amazonia_store_gallery_max() );

	// Solo persiste adjuntos de imagen reales y que pertenezcan a este usuario.
	$ids = array_values( array_filter( $ids, function ( $id ) use ( $user_id ) {
		if ( ! wp_attachment_is_image( $id ) ) {
			return false;
		}
		$attachment = get_post( $id );
		return $attachment && (int) $attachment->post_author === $user_id;
	} ) );

	update_user_meta( $user_id, '_amazonia_store_gallery', $ids );

	wp_send_json_success( array( 'ids' => $ids ) );
}
add_action( 'wp_ajax_amazonia_save_store_gallery', 'amazonia_ajax_save_store_gallery' );
