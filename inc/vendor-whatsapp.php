<?php
/**
 * Contacto por WhatsApp cliente ↔ vendedor (click-to-chat).
 *
 * Genera enlaces `wa.me` con mensaje contextual pre-rellenado, reutilizando el
 * teléfono que el vendedor ya tiene guardado en WCFM. Sin API ni costo.
 *
 * Se usa desde la ficha de producto y desde la página de tienda del vendedor.
 *
 * @package Amazonia_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Normaliza un teléfono a formato apto para wa.me (solo dígitos, con código de país).
 *
 * Los vendedores cargan el número de forma inconsistente, así que aplicamos una
 * heurística tolerante y filtrable:
 *   1. Quitar todo lo que no sea dígito.
 *   2. Quitar prefijo internacional `00`, o un `0` troncal nacional inicial.
 *   3. Si el número tiene largo local y no empieza por el código de país por
 *      defecto, anteponerlo.
 *   4. Descartar (devolver '') lo que quede demasiado corto para ser un número real.
 *
 * @param string $raw       Teléfono tal como está guardado.
 * @param int    $vendor_id ID del vendedor (para los filtros).
 * @return string Número solo-dígitos listo para wa.me, o '' si no es válido.
 */
function amazonia_normalize_whatsapp_number( $raw, $vendor_id = 0 ) {
	$digits = preg_replace( '/\D+/', '', (string) $raw );
	if ( '' === $digits ) {
		return '';
	}

	// Prefijo internacional (00...) o troncal nacional (0...).
	if ( 0 === strpos( $digits, '00' ) ) {
		$digits = substr( $digits, 2 );
	} elseif ( '0' === substr( $digits, 0, 1 ) ) {
		$digits = ltrim( $digits, '0' );
	}

	// Código de país por defecto (57 = Colombia) y largo local, ambos filtrables.
	$cc        = preg_replace( '/\D+/', '', (string) apply_filters( 'amazonia_whatsapp_default_cc', '57', $vendor_id ) );
	$local_len = (int) apply_filters( 'amazonia_whatsapp_local_length', 10, $vendor_id );

	// Si parece local (corto) y no trae ya el código de país, anteponerlo.
	if ( $cc && strlen( $digits ) <= $local_len && 0 !== strpos( $digits, $cc ) ) {
		$digits = $cc . $digits;
	}

	// Número final implausible → no mostrar botón.
	if ( strlen( $digits ) < 8 ) {
		return '';
	}

	return $digits;
}

/**
 * Devuelve el número de WhatsApp normalizado de un vendedor, o '' si no tiene.
 *
 * @param int $vendor_id ID del usuario vendedor.
 * @return string
 */
function amazonia_get_vendor_whatsapp( $vendor_id ) {
	$vendor_id = absint( $vendor_id );
	if ( ! $vendor_id || ! function_exists( 'wcfmmp_get_store' ) ) {
		return '';
	}

	$store = wcfmmp_get_store( $vendor_id );
	if ( ! $store ) {
		return '';
	}

	return amazonia_normalize_whatsapp_number( $store->get_phone(), $vendor_id );
}

/**
 * Construye la URL wa.me de un vendedor con un mensaje pre-rellenado.
 *
 * @param int    $vendor_id ID del vendedor.
 * @param string $message   Mensaje sin codificar.
 * @return string URL completa, o '' si el vendedor no tiene WhatsApp.
 */
function amazonia_vendor_whatsapp_url( $vendor_id, $message ) {
	$number = amazonia_get_vendor_whatsapp( $vendor_id );
	if ( '' === $number ) {
		return '';
	}

	return 'https://wa.me/' . $number . '?text=' . rawurlencode( $message );
}

/**
 * SVG del logo de WhatsApp (fill: currentColor para heredar el color del enlace).
 *
 * @return string
 */
function amazonia_whatsapp_icon_svg() {
	return '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" class="shrink-0"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>';
}

/**
 * Imprime el botón "Contactar por WhatsApp" de un vendedor, ya escapado.
 *
 * No imprime nada si el vendedor no tiene un número válido (estado vacío limpio).
 *
 * @param int    $vendor_id ID del vendedor.
 * @param string $message   Mensaje contextual pre-rellenado.
 * @param array  $args {
 *     @type string $variant 'product' (Tailwind, ficha de producto) o 'store'
 *                           (estilos inline, dentro del markup de WCFM). Default 'product'.
 *     @type string $label   Texto del botón. Default 'Contactar al vendedor'.
 * }
 * @return void
 */
function amazonia_whatsapp_button( $vendor_id, $message, $args = array() ) {
	$url = amazonia_vendor_whatsapp_url( $vendor_id, $message );
	if ( '' === $url ) {
		return;
	}

	$variant = isset( $args['variant'] ) ? $args['variant'] : 'product';
	$label   = isset( $args['label'] ) ? $args['label'] : __( 'Contactar al vendedor', 'amazonia-theme' );
	$aria    = __( 'Contactar al vendedor por WhatsApp', 'amazonia-theme' );
	$icon    = amazonia_whatsapp_icon_svg();

	if ( 'store' === $variant ) {
		// Estilos inline: el hook de WCFM imprime fuera de las plantillas del tema.
		?>
		<div style="padding:12px 24px 6px;">
			<a href="<?php echo esc_url( $url ); ?>" target="_blank" rel="noopener nofollow"
			   aria-label="<?php echo esc_attr( $aria ); ?>"
			   style="display:inline-flex;align-items:center;gap:8px;background:#25D366;color:#fff;text-decoration:none;font-weight:700;font-size:14px;padding:10px 18px;border-radius:9999px;box-shadow:0 6px 18px -8px rgba(37,211,102,.7);">
				<?php echo $icon; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- SVG estático. ?>
				<?php echo esc_html( $label ); ?>
			</a>
		</div>
		<?php
		return;
	}

	// Variante 'product': idéntica al botón original de la ficha de producto.
	?>
	<div class="mb-8">
		<a href="<?php echo esc_url( $url ); ?>" target="_blank" rel="noopener nofollow"
		   aria-label="<?php echo esc_attr( $aria ); ?>"
		   class="inline-flex w-full items-center justify-center gap-2.5 rounded-xl border border-slate-200 bg-white px-7 py-3.5 font-['Outfit'] text-sm font-semibold tracking-wide !text-[#128C7E] !no-underline transition-all duration-200 hover:border-[#25D366] hover:bg-[#25D366] hover:!text-white active:scale-[0.98]">
			<?php echo $icon; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- SVG estático. ?>
			<?php echo esc_html( $label ); ?>
		</a>
	</div>
	<?php
}

/**
 * CTA de WhatsApp bajo el header de la tienda WCFM.
 *
 * Usa el mismo hook que el banner de comunidad. Solo aparece si el vendedor tiene
 * un número válido.
 *
 * @param int $vendor_id ID del vendedor.
 * @return void
 */
function amazonia_store_whatsapp_cta( $vendor_id ) {
	$vendor_id = absint( $vendor_id );
	if ( ! $vendor_id ) {
		return;
	}

	$store_name = function_exists( 'wcfm_get_vendor_store_name' ) ? wcfm_get_vendor_store_name( $vendor_id ) : '';
	$store_url  = function_exists( 'wcfmmp_get_store_url' ) ? wcfmmp_get_store_url( $vendor_id ) : get_author_posts_url( $vendor_id );

	$message = sprintf(
		/* translators: 1: nombre de la tienda, 2: URL de la tienda */
		__( 'Hola, vengo desde tu tienda "%1$s" en Amazonia: %2$s', 'amazonia-theme' ),
		$store_name,
		$store_url
	);

	amazonia_whatsapp_button( $vendor_id, $message, array(
		'variant' => 'store',
		'label'   => __( 'Escríbele por WhatsApp', 'amazonia-theme' ),
	) );
}
add_action( 'wcfmmp_store_after_header', 'amazonia_store_whatsapp_cta' );
