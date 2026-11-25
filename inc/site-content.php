<?php
/**
 * Gestor de contenido estático del sitio (WP Admin → Amazonia → Contenido del sitio).
 *
 * Centraliza lo que antes estaba fijo en las plantillas o en assets.js:
 *   - Imágenes: carrusel del inicio, banners/portadas, Sobre Nosotros, fondo de login.
 *   - Favicon (opción nativa `site_icon`) e imágenes de posicionamiento (SEO):
 *     imagen por defecto al compartir en redes (Open Graph/Twitter) y logo para
 *     buscadores. Se inyectan en Rank Math por filtro, sin tocar el plugin.
 *   - Textos: hero del inicio, Sobre Nosotros y pie de página.
 *   - Redes sociales y contacto del sitio (pie de página).
 *
 * Todo se define UNA vez en amazonia_site_content_schema(): de ahí salen los
 * valores por defecto, el formulario del admin y la sanitización. Si un campo
 * está vacío se usa el valor original del tema, así que el sitio nunca queda
 * en blanco. Se guarda en una sola opción: `amazonia_site_content`.
 *
 * @package Amazonia_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const AMAZONIA_SITE_CONTENT_OPTION = 'amazonia_site_content';
const AMAZONIA_CAROUSEL_MAX        = 12;

/* ---------------------------------------------------------------------------
 * 1. Esquema (única fuente de verdad)
 * ------------------------------------------------------------------------ */

/**
 * Pestañas → grupos → campos.
 *
 * Tipos: text, textarea, url, email, image, slides, site_icon.
 *
 * @return array
 */
function amazonia_site_content_schema() {
	static $schema = null;
	if ( null !== $schema ) {
		return $schema;
	}

	$schema = array(

		'imagenes'        => array(
			'label'  => __( 'Imágenes', 'amazonia-theme' ),
			'groups' => array(
				array(
					'title'  => __( 'Carrusel del inicio', 'amazonia-theme' ),
					'desc'   => __( 'Imágenes que rotan a la derecha del banner principal. Arrastra para ordenar. Si no agregas ninguna, se usa el carrusel original del tema.', 'amazonia-theme' ),
					'fields' => array(
						'home_carousel' => array(
							'type'  => 'slides',
							'label' => __( 'Imágenes del carrusel', 'amazonia-theme' ),
							'hint'  => __( 'Recomendado: horizontales o cuadradas, mínimo 1200×1000 px.', 'amazonia-theme' ),
						),
					),
				),
				array(
					'title'  => __( 'Banners y portadas', 'amazonia-theme' ),
					'desc'   => __( 'Si no eliges una imagen, se usa la imagen original del tema.', 'amazonia-theme' ),
					'fields' => array(
						'img_comunidades_banner' => array(
							'type'     => 'image',
							'label'    => __( 'Banner de la página Comunidades', 'amazonia-theme' ),
							'hint'     => __( 'Recomendado: 1920×600 px.', 'amazonia-theme' ),
							'fallback' => 'assets/img/amazonia-hero-selva.jpg',
						),
						'img_categorias_hero'    => array(
							'type'     => 'image',
							'label'    => __( 'Banner de la página Categorías', 'amazonia-theme' ),
							'hint'     => __( 'Recomendado: 1920×700 px.', 'amazonia-theme' ),
							'fallback' => 'assets/img/amazonia-hero-selva.jpg',
						),
						'img_home_impacto'       => array(
							'type'     => 'image',
							'label'    => __( 'Inicio: imagen de "Impacto Directo en la Selva"', 'amazonia-theme' ),
							'hint'     => __( 'Recomendado: 1200×675 px (16:9).', 'amazonia-theme' ),
							'fallback' => 'assets/img/amazonia-artesanas.jpg',
						),
						'img_about_hero'         => array(
							'type'     => 'image',
							'label'    => __( 'Sobre Nosotros: imagen principal', 'amazonia-theme' ),
							'hint'     => __( 'Recomendado: 1920×1080 px.', 'amazonia-theme' ),
							'fallback' => 'assets/img/amazon_rainforest_hero.png',
						),
						'img_about_historia'     => array(
							'type'     => 'image',
							'label'    => __( 'Sobre Nosotros: imagen de "Nuestra Historia"', 'amazonia-theme' ),
							'hint'     => __( 'Recomendado: vertical, 1000×1200 px.', 'amazonia-theme' ),
							'fallback' => 'assets/img/indigenous_crafts_community.png',
						),
						'img_login_bg'           => array(
							'type'     => 'image',
							'label'    => __( 'Fondo de Iniciar sesión / Registro de vendedor', 'amazonia-theme' ),
							'hint'     => __( 'Recomendado: vertical, 1200×1600 px.', 'amazonia-theme' ),
							'fallback' => 'assets/img/amazonia-login-bg.jpg',
						),
					),
				),
			),
		),

		'posicionamiento' => array(
			'label'  => __( 'Favicon y SEO', 'amazonia-theme' ),
			'groups' => array(
				array(
					'title'  => __( 'Favicon', 'amazonia-theme' ),
					'desc'   => __( 'Ícono que aparece en la pestaña del navegador, favoritos y resultados de Google.', 'amazonia-theme' ),
					'fields' => array(
						'site_icon' => array(
							'type'  => 'site_icon',
							'label' => __( 'Favicon del sitio', 'amazonia-theme' ),
							'hint'  => __( 'PNG cuadrado de al menos 512×512 px.', 'amazonia-theme' ),
						),
					),
				),
				array(
					'title'  => __( 'Imágenes de posicionamiento', 'amazonia-theme' ),
					'desc'   => __( 'Se envían a Rank Math y se usan al compartir el sitio en redes y en los resultados de buscadores. Las páginas y productos que tengan su propia imagen social la conservan.', 'amazonia-theme' ),
					'fields' => array(
						'seo_og_image' => array(
							'type'  => 'image',
							'label' => __( 'Imagen por defecto al compartir (Facebook, WhatsApp, X…)', 'amazonia-theme' ),
							'hint'  => __( 'Recomendado: 1200×630 px.', 'amazonia-theme' ),
						),
						'seo_logo'     => array(
							'type'  => 'image',
							'label' => __( 'Logo para buscadores (Google)', 'amazonia-theme' ),
							'hint'  => __( 'Recomendado: PNG cuadrado o apaisado, mínimo 112×112 px.', 'amazonia-theme' ),
						),
					),
				),
			),
		),

		'textos'          => array(
			'label'  => __( 'Textos', 'amazonia-theme' ),
			'groups' => array(
				array(
					'title'  => __( 'Inicio: banner principal', 'amazonia-theme' ),
					'fields' => array(
						'home_hero_badge' => array(
							'type'    => 'text',
							'label'   => __( 'Etiqueta superior', 'amazonia-theme' ),
							'default' => __( 'Saberes Ancestrales & Comercio Justo', 'amazonia-theme' ),
						),
						'home_hero_title' => array(
							'type'    => 'text',
							'label'   => __( 'Título', 'amazonia-theme' ),
							'default' => __( 'El Latido de la Selva en Cada Creación', 'amazonia-theme' ),
						),
						'home_hero_text'  => array(
							'type'    => 'textarea',
							'label'   => __( 'Descripción', 'amazonia-theme' ),
							'default' => __( 'Cada producto cuenta una historia sagrada. Conecta directamente con comunidades indígenas y afro-amazónicas, y apoya la preservación de su territorio y cultura.', 'amazonia-theme' ),
						),
					),
				),
				array(
					'title'  => __( 'Sobre Nosotros: encabezado', 'amazonia-theme' ),
					'fields' => array(
						'about_badge' => array(
							'type'    => 'text',
							'label'   => __( 'Etiqueta superior', 'amazonia-theme' ),
							'default' => __( 'Nuestra Esencia', 'amazonia-theme' ),
						),
						'about_title' => array(
							'type'    => 'text',
							'label'   => __( 'Título', 'amazonia-theme' ),
							'default' => __( 'El corazón de la Amazonía en cada detalle.', 'amazonia-theme' ),
						),
						'about_text'  => array(
							'type'    => 'textarea',
							'label'   => __( 'Descripción', 'amazonia-theme' ),
							'default' => __( 'Somos un puente entre la sabiduría ancestral de nuestras comunidades y el resto del mundo, protegiendo nuestro ecosistema a través del comercio justo.', 'amazonia-theme' ),
						),
					),
				),
				array(
					'title'  => __( 'Sobre Nosotros: historia', 'amazonia-theme' ),
					'fields' => array(
						'about_quote'         => array(
							'type'    => 'text',
							'label'   => __( 'Cita sobre la imagen', 'amazonia-theme' ),
							'default' => __( 'Tejemos nuestras historias y el respeto por nuestra madre selva en cada fibra.', 'amazonia-theme' ),
						),
						'about_story_eyebrow' => array(
							'type'    => 'text',
							'label'   => __( 'Etiqueta de la sección', 'amazonia-theme' ),
							'default' => __( 'Nuestra Historia', 'amazonia-theme' ),
						),
						'about_story_title'   => array(
							'type'    => 'text',
							'label'   => __( 'Título de la sección', 'amazonia-theme' ),
							'default' => __( 'Preservando un Legado Milenario', 'amazonia-theme' ),
						),
						'about_story_p1'      => array(
							'type'    => 'textarea',
							'label'   => __( 'Primer párrafo', 'amazonia-theme' ),
							'default' => __( 'Nacimos en el corazón de la cuenca amazónica con un propósito claro: revalorizar los conocimientos de los pueblos originarios y las comunidades locales.', 'amazonia-theme' ),
						),
						'about_story_p2'      => array(
							'type'    => 'textarea',
							'label'   => __( 'Segundo párrafo', 'amazonia-theme' ),
							'default' => __( 'Cada producto que encuentras en nuestra plataforma no es solo un objeto, es una obra de arte que representa días de dedicación, materiales 100% naturales y prácticas completamente sostenibles que protegen la biodiversidad.', 'amazonia-theme' ),
						),
						'about_card1_title'   => array(
							'type'    => 'text',
							'label'   => __( 'Tarjeta 1: título', 'amazonia-theme' ),
							'default' => __( '100% Sostenible', 'amazonia-theme' ),
						),
						'about_card1_text'    => array(
							'type'    => 'text',
							'label'   => __( 'Tarjeta 1: texto', 'amazonia-theme' ),
							'default' => __( 'Respetamos los ciclos naturales de recolección.', 'amazonia-theme' ),
						),
						'about_card2_title'   => array(
							'type'    => 'text',
							'label'   => __( 'Tarjeta 2: título', 'amazonia-theme' ),
							'default' => __( 'Comercio Justo', 'amazonia-theme' ),
						),
						'about_card2_text'    => array(
							'type'    => 'text',
							'label'   => __( 'Tarjeta 2: texto', 'amazonia-theme' ),
							'default' => __( 'Impacto directo en más de 20 comunidades aliadas.', 'amazonia-theme' ),
						),
					),
				),
				array(
					'title'  => __( 'Sobre Nosotros: cifras', 'amazonia-theme' ),
					'fields' => array(
						'about_stat1_n' => array(
							'type'    => 'text',
							'label'   => __( 'Cifra 1', 'amazonia-theme' ),
							'default' => '+50',
						),
						'about_stat1_l' => array(
							'type'    => 'text',
							'label'   => __( 'Cifra 1: descripción', 'amazonia-theme' ),
							'default' => __( 'Familias Beneficiadas', 'amazonia-theme' ),
						),
						'about_stat2_n' => array(
							'type'    => 'text',
							'label'   => __( 'Cifra 2', 'amazonia-theme' ),
							'default' => '100%',
						),
						'about_stat2_l' => array(
							'type'    => 'text',
							'label'   => __( 'Cifra 2: descripción', 'amazonia-theme' ),
							'default' => __( 'Materiales Orgánicos', 'amazonia-theme' ),
						),
						'about_stat3_n' => array(
							'type'    => 'text',
							'label'   => __( 'Cifra 3', 'amazonia-theme' ),
							'default' => '+10K',
						),
						'about_stat3_l' => array(
							'type'    => 'text',
							'label'   => __( 'Cifra 3: descripción', 'amazonia-theme' ),
							'default' => __( 'Hectáreas Protegidas', 'amazonia-theme' ),
						),
					),
				),
				array(
					'title'  => __( 'Pie de página', 'amazonia-theme' ),
					'fields' => array(
						'footer_tagline'   => array(
							'type'    => 'textarea',
							'label'   => __( 'Descripción de la marca', 'amazonia-theme' ),
							'default' => __( 'Conectamos la sabiduría ancestral de comunidades afrocolombianas e indígenas de la Amazonía con el mercado digital global.', 'amazonia-theme' ),
						),
						'footer_location'  => array(
							'type'    => 'text',
							'label'   => __( 'Ubicación (etiqueta)', 'amazonia-theme' ),
							'default' => __( 'Caquetá, Colombia', 'amazonia-theme' ),
						),
						'footer_copy'      => array(
							'type'    => 'text',
							'label'   => __( 'Frase junto al copyright', 'amazonia-theme' ),
							'default' => __( 'Preservando el corazón del mundo.', 'amazonia-theme' ),
						),
						'footer_made_with' => array(
							'type'    => 'text',
							'label'   => __( 'Frase final', 'amazonia-theme' ),
							'default' => __( 'Hecho con ❤️ para la Amazonía', 'amazonia-theme' ),
						),
					),
				),
			),
		),

		'redes'           => array(
			'label'  => __( 'Redes y contacto', 'amazonia-theme' ),
			'groups' => array(
				array(
					'title'  => __( 'Redes sociales y contacto del sitio', 'amazonia-theme' ),
					'desc'   => __( 'Aparecen como íconos en el pie de página. Los que dejes vacíos no se muestran.', 'amazonia-theme' ),
					'fields' => array(
						'social_web'       => array(
							'type'        => 'url',
							'label'       => __( 'Sitio web', 'amazonia-theme' ),
							'placeholder' => 'https://',
						),
						'social_email'     => array(
							'type'        => 'email',
							'label'       => __( 'Correo de contacto', 'amazonia-theme' ),
							'placeholder' => 'contacto@amazoniamarket.com',
						),
						'social_whatsapp'  => array(
							'type'        => 'text',
							'label'       => __( 'WhatsApp (número)', 'amazonia-theme' ),
							'placeholder' => '+57 300 123 4567',
						),
						'social_instagram' => array(
							'type'        => 'url',
							'label'       => 'Instagram',
							'placeholder' => 'https://instagram.com/…',
						),
						'social_facebook'  => array(
							'type'        => 'url',
							'label'       => 'Facebook',
							'placeholder' => 'https://facebook.com/…',
						),
						'social_tiktok'    => array(
							'type'        => 'url',
							'label'       => 'TikTok',
							'placeholder' => 'https://tiktok.com/@…',
						),
						'social_youtube'   => array(
							'type'        => 'url',
							'label'       => 'YouTube',
							'placeholder' => 'https://youtube.com/@…',
						),
						'social_x'         => array(
							'type'        => 'url',
							'label'       => 'X / Twitter',
							'placeholder' => 'https://x.com/…',
						),
					),
				),
			),
		),
	);

	return $schema;
}

/**
 * Todos los campos del esquema en un solo arreglo plano (clave => definición).
 *
 * @return array
 */
function amazonia_site_content_fields() {
	static $fields = null;
	if ( null === $fields ) {
		$fields = array();
		foreach ( amazonia_site_content_schema() as $tab ) {
			foreach ( $tab['groups'] as $group ) {
				$fields += $group['fields'];
			}
		}
	}
	return $fields;
}

/* ---------------------------------------------------------------------------
 * 2. Lectura (lo que usan las plantillas)
 * ------------------------------------------------------------------------ */

/**
 * Valores guardados (sin defaults).
 *
 * @return array
 */
function amazonia_site_content_saved() {
	static $saved = null;
	if ( null === $saved ) {
		$option = get_option( AMAZONIA_SITE_CONTENT_OPTION, array() );
		$saved  = is_array( $option ) ? $option : array();
	}
	return $saved;
}

/**
 * Texto de un campo: el guardado, o el original del tema si está vacío.
 * Devuelve el valor SIN escapar: escápalo al imprimirlo.
 *
 * @param string $key
 * @return string
 */
function amazonia_content( $key ) {
	$saved = amazonia_site_content_saved();
	if ( isset( $saved[ $key ] ) && is_string( $saved[ $key ] ) && '' !== trim( $saved[ $key ] ) ) {
		return $saved[ $key ];
	}
	$fields = amazonia_site_content_fields();
	return isset( $fields[ $key ]['default'] ) ? (string) $fields[ $key ]['default'] : '';
}

/**
 * ID de adjunto de un campo de imagen (0 si no hay o ya no existe).
 *
 * @param string $key
 * @return int
 */
function amazonia_content_image_id( $key ) {
	$saved = amazonia_site_content_saved();
	$id    = isset( $saved[ $key ] ) ? absint( $saved[ $key ] ) : 0;
	return ( $id && 'attachment' === get_post_type( $id ) ) ? $id : 0;
}

/**
 * URL de la imagen elegida en el gestor ('' si no se eligió ninguna).
 *
 * @param string $key
 * @param string $size
 * @return string
 */
function amazonia_content_image_url( $key, $size = 'full' ) {
	$id = amazonia_content_image_id( $key );
	return $id ? (string) wp_get_attachment_image_url( $id, $size ) : '';
}

/**
 * Atributos `src` (y `data-asset`) listos para imprimir dentro de un <img>.
 *
 * Con imagen elegida en el gestor: solo `src`, SIN data-asset (si no, assets.js
 * la pisaría con la URL de Cloudinary). Sin ella: la imagen original del tema
 * y el data-asset de siempre.
 *
 * @param string $key          Campo del gestor.
 * @param string $fallback_rel Ruta relativa en el tema de la imagen original.
 * @param string $slot         Slot de assets.js ('' si no tiene).
 * @param string $size         Tamaño de imagen de WordPress.
 * @return string Ya escapado.
 */
function amazonia_content_img_attrs( $key, $fallback_rel, $slot = '', $size = 'full' ) {
	$url = amazonia_content_image_url( $key, $size );
	if ( $url ) {
		return 'src="' . esc_url( $url ) . '"';
	}
	$attrs = 'src="' . esc_url( get_template_directory_uri() . '/' . ltrim( $fallback_rel, '/' ) ) . '"';
	if ( $slot ) {
		$attrs .= ' data-asset="' . esc_attr( $slot ) . '"';
	}
	return $attrs;
}

/**
 * Slides del carrusel del inicio elegidos en el gestor.
 *
 * @return array[] Cada uno: id, alt, link. Vacío si no hay (se usa el carrusel original).
 */
function amazonia_get_home_carousel() {
	$saved  = amazonia_site_content_saved();
	$slides = ( isset( $saved['home_carousel'] ) && is_array( $saved['home_carousel'] ) ) ? $saved['home_carousel'] : array();
	$out    = array();

	foreach ( $slides as $slide ) {
		$id = isset( $slide['id'] ) ? absint( $slide['id'] ) : 0;
		if ( ! $id || 'attachment' !== get_post_type( $id ) ) {
			continue;
		}
		$out[] = array(
			'id'   => $id,
			'alt'  => isset( $slide['alt'] ) ? (string) $slide['alt'] : '',
			'link' => isset( $slide['link'] ) ? (string) $slide['link'] : '',
		);
	}

	return array_slice( $out, 0, AMAZONIA_CAROUSEL_MAX );
}

/**
 * Redes sociales/contacto configurados para el pie de página.
 *
 * @return array[] Cada uno: url, label, icon (Material Symbols) o svg=true.
 */
function amazonia_get_footer_socials() {
	$whatsapp = '';
	$raw_wa   = amazonia_content( 'social_whatsapp' );
	if ( '' !== $raw_wa && function_exists( 'amazonia_normalize_whatsapp_number' ) ) {
		$whatsapp = amazonia_normalize_whatsapp_number( $raw_wa );
	}
	$email = amazonia_content( 'social_email' );

	$all = array(
		array( 'url' => amazonia_content( 'social_web' ), 'label' => __( 'Sitio web', 'amazonia-theme' ), 'icon' => 'language' ),
		array( 'url' => $email ? 'mailto:' . $email : '', 'label' => __( 'Correo', 'amazonia-theme' ), 'icon' => 'mail' ),
		array( 'url' => $whatsapp ? 'https://wa.me/' . $whatsapp : '', 'label' => 'WhatsApp', 'svg' => true ),
		array( 'url' => amazonia_content( 'social_instagram' ), 'label' => 'Instagram', 'icon' => 'photo_camera' ),
		array( 'url' => amazonia_content( 'social_facebook' ), 'label' => 'Facebook', 'icon' => 'thumb_up' ),
		array( 'url' => amazonia_content( 'social_tiktok' ), 'label' => 'TikTok', 'icon' => 'music_note' ),
		array( 'url' => amazonia_content( 'social_youtube' ), 'label' => 'YouTube', 'icon' => 'smart_display' ),
		array( 'url' => amazonia_content( 'social_x' ), 'label' => 'X', 'icon' => 'alternate_email' ),
	);

	return array_values( array_filter( $all, function ( $item ) {
		return ! empty( $item['url'] );
	} ) );
}

/* ---------------------------------------------------------------------------
 * 3. SEO: imágenes de posicionamiento → Rank Math (sin tocar el plugin)
 * ------------------------------------------------------------------------ */

/**
 * Rank Math lee su imagen social por defecto y el logo de la organización de
 * la opción `rank-math-options-titles`. Solo en el front: así su pantalla de
 * ajustes sigue mostrando lo que el admin configuró allí.
 */
add_filter( 'option_rank-math-options-titles', function ( $value ) {
	if ( is_admin() && ! wp_doing_ajax() ) {
		return $value;
	}
	$value = is_array( $value ) ? $value : array();

	$og_id = amazonia_content_image_id( 'seo_og_image' );
	if ( $og_id ) {
		$url = wp_get_attachment_image_url( $og_id, 'full' );
		if ( $url ) {
			$value['open_graph_image']    = $url;
			$value['open_graph_image_id'] = $og_id;
		}
	}

	$logo_id = amazonia_content_image_id( 'seo_logo' );
	if ( $logo_id ) {
		$url = wp_get_attachment_image_url( $logo_id, 'full' );
		if ( $url ) {
			$value['knowledgegraph_logo']    = $url;
			$value['knowledgegraph_logo_id'] = $logo_id;
		}
	}

	return $value;
} );

/**
 * Respaldo si Rank Math no está activo: etiquetas mínimas para compartir.
 */
add_action( 'wp_head', function () {
	if ( function_exists( 'rank_math' ) ) {
		return;
	}
	$url = amazonia_content_image_url( 'seo_og_image' );
	if ( ! $url ) {
		return;
	}
	echo '<meta property="og:image" content="' . esc_url( $url ) . '" />' . "\n";
	echo '<meta name="twitter:card" content="summary_large_image" />' . "\n";
	echo '<meta name="twitter:image" content="' . esc_url( $url ) . '" />' . "\n";
}, 5 );

/* ---------------------------------------------------------------------------
 * 4. Guardado
 * ------------------------------------------------------------------------ */

/**
 * Sanitiza lo enviado por el formulario según el tipo de cada campo.
 *
 * @param array $input Datos ya sin barras (wp_unslash).
 * @return array
 */
function amazonia_site_content_sanitize( $input ) {
	$clean = array();

	foreach ( amazonia_site_content_fields() as $key => $field ) {
		$raw = isset( $input[ $key ] ) ? $input[ $key ] : '';

		switch ( $field['type'] ) {
			case 'text':
				$clean[ $key ] = sanitize_text_field( (string) $raw );
				break;
			case 'textarea':
				$clean[ $key ] = sanitize_textarea_field( (string) $raw );
				break;
			case 'url':
				$clean[ $key ] = esc_url_raw( (string) $raw );
				break;
			case 'email':
				$clean[ $key ] = sanitize_email( (string) $raw );
				break;
			case 'image':
				$id            = absint( $raw );
				$clean[ $key ] = ( $id && wp_attachment_is_image( $id ) ) ? $id : '';
				break;
			case 'slides':
				$slides = array();
				if ( is_array( $raw ) ) {
					foreach ( $raw as $slide ) {
						$id = isset( $slide['id'] ) ? absint( $slide['id'] ) : 0;
						if ( ! $id || ! wp_attachment_is_image( $id ) ) {
							continue;
						}
						$slides[] = array(
							'id'   => $id,
							'alt'  => isset( $slide['alt'] ) ? sanitize_text_field( (string) $slide['alt'] ) : '',
							'link' => isset( $slide['link'] ) ? esc_url_raw( (string) $slide['link'] ) : '',
						);
						if ( count( $slides ) >= AMAZONIA_CAROUSEL_MAX ) {
							break;
						}
					}
				}
				$clean[ $key ] = $slides;
				break;
			case 'site_icon':
				// Vive en la opción nativa `site_icon`, se guarda aparte.
				break;
		}
	}

	return $clean;
}

add_action( 'admin_post_amazonia_save_site_content', 'amazonia_handle_save_site_content' );
function amazonia_handle_save_site_content() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'No tienes permiso para hacer esto.', 'amazonia-theme' ), '', array( 'response' => 403 ) );
	}
	check_admin_referer( 'amazonia_save_site_content' );

	$input = isset( $_POST['amazonia_content'] ) ? wp_unslash( $_POST['amazonia_content'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- se sanea campo por campo en amazonia_site_content_sanitize().
	$input = is_array( $input ) ? $input : array();

	update_option( AMAZONIA_SITE_CONTENT_OPTION, amazonia_site_content_sanitize( $input ) );

	// Favicon: opción nativa de WordPress. Debe ser cuadrado y de al menos 512 px.
	$args = array( 'page' => 'amazonia-site-content', 'saved' => '1' );
	if ( isset( $input['site_icon'] ) ) {
		$icon_id = absint( $input['site_icon'] );
		if ( ! $icon_id ) {
			delete_option( 'site_icon' );
		} elseif ( wp_attachment_is_image( $icon_id ) ) {
			$meta = wp_get_attachment_metadata( $icon_id );
			$w    = isset( $meta['width'] ) ? (int) $meta['width'] : 0;
			$h    = isset( $meta['height'] ) ? (int) $meta['height'] : 0;
			if ( $w >= 512 && $w === $h ) {
				update_option( 'site_icon', $icon_id );
			} else {
				$args['icon_error'] = '1';
			}
		}
	}

	if ( ! empty( $_POST['amazonia_active_tab'] ) ) {
		$args['tab'] = sanitize_key( wp_unslash( $_POST['amazonia_active_tab'] ) );
	}

	wp_safe_redirect( add_query_arg( $args, admin_url( 'admin.php' ) ) );
	exit;
}

/* ---------------------------------------------------------------------------
 * 5. Pantalla de administración
 * ------------------------------------------------------------------------ */

add_action( 'admin_menu', function () {
	$hook = add_submenu_page(
		'amazonia-invite-codes',
		__( 'Contenido del sitio', 'amazonia-theme' ),
		__( 'Contenido del sitio', 'amazonia-theme' ),
		'manage_options',
		'amazonia-site-content',
		'amazonia_render_site_content_page'
	);

	add_action( 'admin_enqueue_scripts', function ( $current ) use ( $hook ) {
		if ( $current !== $hook ) {
			return;
		}
		wp_enqueue_media();
		wp_enqueue_script( 'jquery-ui-sortable' );
		amazonia_style( 'amazonia-site-content-admin', 'assets/css/site-content-admin.css' );
		amazonia_script( 'amazonia-site-content-admin', 'assets/js/site-content-admin.js', array( 'jquery', 'jquery-ui-sortable' ) );
		wp_localize_script( 'amazonia-site-content-admin', 'amazoniaSiteContent', array(
			'maxSlides'   => AMAZONIA_CAROUSEL_MAX,
			'chooseTitle' => __( 'Elige una imagen', 'amazonia-theme' ),
			'chooseBtn'   => __( 'Usar esta imagen', 'amazonia-theme' ),
			'slidesTitle' => __( 'Elige las imágenes del carrusel', 'amazonia-theme' ),
			'slidesBtn'   => __( 'Agregar al carrusel', 'amazonia-theme' ),
			'maxMessage'  => sprintf(
				/* translators: %d: máximo de imágenes del carrusel */
				__( 'El carrusel admite hasta %d imágenes.', 'amazonia-theme' ),
				AMAZONIA_CAROUSEL_MAX
			),
			'altLabel'    => __( 'Texto alternativo', 'amazonia-theme' ),
			'linkLabel'   => __( 'Enlace (opcional)', 'amazonia-theme' ),
			'removeLabel' => __( 'Quitar', 'amazonia-theme' ),
		) );
	} );
} );

function amazonia_render_site_content_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	include get_template_directory() . '/templates/admin-site-content.php';
}
