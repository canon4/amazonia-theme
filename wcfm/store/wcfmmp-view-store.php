<?php
/**
 * Página individual de tienda (vendedor). Sobrescribe la plantilla por defecto
 * de WCFM Marketplace.
 *
 * Implementa la "Pantalla B · Perfil de Tienda" del diseño Amazonia Perfiles:
 * hermana de single-comunidad.php (mismo hero, mismos stat tiles, misma familia
 * de tarjetas), con la pertenencia a la comunidad integrada en la cabecera.
 *
 * Toda sección sin datos se oculta por completo. Las métricas provienen de la
 * base de datos real: rating agregado de las reseñas de producto y pedidos
 * completados de la tabla de WCFM (ver inc/community-cpt.php).
 *
 * @package Amazonia_Theme
 */
if ( ! defined( 'ABSPATH' ) ) exit;

global $WCFM, $WCFMmp;

$wcfm_store_url  = wcfm_get_option( 'wcfm_store_url', 'store' );
$wcfm_store_name = apply_filters( 'wcfmmp_store_query_var', get_query_var( $wcfm_store_url ) );
if ( empty( $wcfm_store_name ) ) return;
$seller_info = get_user_by( 'slug', $wcfm_store_name );
if ( ! $seller_info ) return;

$vendor_id  = (int) $seller_info->ID;
$store_user = wcfmmp_get_store( $vendor_id );
$store_info = $store_user->get_shop_info();

/* ---------------------------------------------------------------------------
 * Identidad de la tienda
 * ------------------------------------------------------------------------ */
$store_name = ! empty( $store_info['store_name'] ) ? $store_info['store_name'] : $seller_info->display_name;
$store_desc = isset( $store_info['shop_description'] ) ? $store_info['shop_description'] : '';

/*
 * Versión completa de la descripción para la sección "Sobre la tienda"
 * (a diferencia de $store_desc, que se recorta a texto plano para el lede
 * del hero). Admite el video que el vendedor pegue como URL suelta
 * (YouTube/Vimeo/etc.) en su descripción: wp_kses_post() sanea el HTML
 * primero y luego $wp_embed->autoembed() convierte esa URL en el
 * reproductor, igual que hace WordPress con el contenido de una entrada.
 */
$store_desc_html = '';
if ( $store_desc ) {
	global $wp_embed;
	$store_desc_html = wp_kses_post( $store_desc );
	if ( $wp_embed ) {
		$store_desc_html = $wp_embed->autoembed( $store_desc_html );
	}
	$store_desc_html = wpautop( $store_desc_html );
}

/* Fotos del taller/equipo que el vendedor sube en Ajustes > Store (ver inc/store-gallery.php) */
$store_gallery = function_exists( 'amazonia_get_store_gallery' ) ? amazonia_get_store_gallery( $vendor_id ) : array();

$avatar_url = $store_user->get_avatar();
$banner_url = $store_user->get_banner();
$email      = $store_user->get_email();
$phone      = $store_user->get_phone();
$address    = $store_user->get_address_string();
$store_url  = function_exists( 'wcfmmp_get_store_url' ) ? wcfmmp_get_store_url( $vendor_id ) : get_author_posts_url( $vendor_id );

/* Coordenadas guardadas por el vendedor en "Mi tienda > Ajustes > Location" (si las fijó) */
$store_lat = get_user_meta( $vendor_id, '_wcfm_store_lat', true );
$store_lng = get_user_meta( $vendor_id, '_wcfm_store_lng', true );
$has_map_coords = ( '' !== $store_lat && '' !== $store_lng );

/* Ubicación corta para la línea del hero */
$city    = isset( $store_info['address']['city'] ) ? $store_info['address']['city'] : '';
$country = isset( $store_info['address']['country'] ) ? $store_info['address']['country'] : '';
if ( $country && function_exists( 'WC' ) ) {
	$countries = WC()->countries->get_countries();
	if ( isset( $countries[ $country ] ) ) {
		$country = $countries[ $country ];
	}
}
$short_location = implode( ' · ', array_filter( [ $city, $country ] ) );

/* "Vendedor desde" */
$register_date = $store_user->get_register_date();
$member_since  = $register_date ? date_i18n( 'Y', strtotime( $register_date ) ) : '';

/* Categoría del vendedor (chip del hero) */
$badge_text        = '';
$vendor_categories = wp_get_object_terms( $vendor_id, 'wcfm_vendor_category' );
if ( ! is_wp_error( $vendor_categories ) && ! empty( $vendor_categories ) ) {
	$badge_text = $vendor_categories[0]->name;
}

/* ---------------------------------------------------------------------------
 * Pertenencia a comunidad (el puente tienda -> comunidad)
 * ------------------------------------------------------------------------ */
$community_id   = (int) get_user_meta( $vendor_id, 'community_id', true );
$community      = $community_id ? amazonia_get_community_data( $community_id ) : null;

/* ---------------------------------------------------------------------------
 * Métricas de confianza (datos reales)
 * ------------------------------------------------------------------------ */
$stats = amazonia_get_store_stats( $vendor_id );

$tiles = [];
if ( null !== $stats['rating'] ) {
	$tiles[] = [
		'n'    => number_format_i18n( $stats['rating'], 1 ),
		'l'    => sprintf( _n( '%s reseña', '%s reseñas', $stats['review_count'], 'amazonia-theme' ), number_format_i18n( $stats['review_count'] ) ),
		'star' => true,
	];
}
if ( $stats['product_count'] > 0 ) {
	$tiles[] = [
		'n' => number_format_i18n( $stats['product_count'] ),
		'l' => _n( 'Producto', 'Productos', $stats['product_count'], 'amazonia-theme' ),
	];
}
if ( $stats['completed_orders'] > 0 ) {
	$tiles[] = [
		'n' => number_format_i18n( $stats['completed_orders'] ),
		'l' => _n( 'Pedido completado', 'Pedidos completados', $stats['completed_orders'], 'amazonia-theme' ),
	];
}
if ( $member_since ) {
	$tiles[] = [ 'n' => $member_since, 'l' => __( 'Vendedor desde', 'amazonia-theme' ) ];
}

/* ---------------------------------------------------------------------------
 * Catálogo: filtro por categoría vía query string (?product_cat=slug)
 * ------------------------------------------------------------------------ */
$active_cat = isset( $_GET['product_cat'] ) ? sanitize_title( wp_unslash( $_GET['product_cat'] ) ) : '';

// WCFM devuelve IDs de término, no objetos: hay que resolverlos antes de usarlos.
$store_cats = [];
$store_cat_ids = $store_user->get_store_taxonomies( 'product_cat' );
if ( ! is_wp_error( $store_cat_ids ) && ! empty( $store_cat_ids ) ) {
	foreach ( (array) $store_cat_ids as $cat_id ) {
		$term = is_object( $cat_id ) ? $cat_id : get_term( (int) $cat_id, 'product_cat' );
		if ( $term && ! is_wp_error( $term ) ) {
			$store_cats[] = $term;
		}
	}
}

$product_args = [
	'post_type'      => 'product',
	'post_status'    => 'publish',
	'author'         => $vendor_id,
	'posts_per_page' => 12,
	'orderby'        => 'date',
	'order'          => 'DESC',
];
if ( $active_cat ) {
	$product_args['tax_query'] = [
		[ 'taxonomy' => 'product_cat', 'field' => 'slug', 'terms' => $active_cat ],
	];
}
$vendor_products = new WP_Query( $product_args );

/* ---------------------------------------------------------------------------
 * Políticas de envío / devoluciones (solo las que tengan texto)
 * ------------------------------------------------------------------------ */
$policies = array_filter( [
	__( 'Tiempos de envío', 'amazonia-theme' ) => $store_user->get_shipping_policy(),
	__( 'Devoluciones', 'amazonia-theme' )     => $store_user->get_refund_policy(),
] );

/* ---------------------------------------------------------------------------
 * Reseñas: se listan las de los PRODUCTOS de la tienda.
 * El módulo de reseñas de tienda de WCFM no está en uso (tabla vacía).
 * ------------------------------------------------------------------------ */
$reviews     = [];
$product_ids = get_posts( [
	'post_type'   => 'product',
	'post_status' => 'publish',
	'author'      => $vendor_id,
	'numberposts' => -1,
	'fields'      => 'ids',
] );
if ( $product_ids ) {
	$reviews = get_comments( [
		'post__in' => $product_ids,
		'status'   => 'approve',
		'type'     => 'review',
		'number'   => 10,
	] );
}

/* Redes sociales del vendedor (solo las que existan) */
$socials    = $store_user->get_social_profiles();
$social_map = [
	'fb'        => [ 'label' => 'Facebook',  'icon' => 'thumb_up' ],
	'instagram' => [ 'label' => 'Instagram', 'icon' => 'photo_camera' ],
	'twitter'   => [ 'label' => 'Twitter',   'icon' => 'tag' ],
	'youtube'   => [ 'label' => 'YouTube',   'icon' => 'play_circle' ],
	'linkedin'  => [ 'label' => 'LinkedIn',  'icon' => 'work' ],
	'pinterest' => [ 'label' => 'Pinterest', 'icon' => 'push_pin' ],
];

get_header( 'shop' );
?>

<div class="amz-profile wcfmmp-single-store-holder">

	<!-- ── Hero + badge de comunidad integrado ───────────────────── -->
	<header class="amz-hero">
		<div class="amz-hero__bg"></div>
		<?php if ( $banner_url ) : ?>
			<div class="amz-hero__img" style="background-image:url('<?php echo esc_url( $banner_url ); ?>')" role="presentation"></div>
		<?php endif; ?>
		<div class="amz-hero__glow-a"></div>
		<div class="amz-hero__glow-b"></div>

		<div class="amz-shell amz-hero__inner">

			<?php if ( $community ) : ?>
				<a class="amz-badge-comm" href="<?php echo esc_url( $community['url'] ); ?>">
					<?php
					$comm_logo = amazonia_resolve_community_image( $community['logo'], 'thumbnail' );
					if ( $comm_logo['url'] ) :
						?>
						<img src="<?php echo esc_url( $comm_logo['url'] ); ?>" alt="" aria-hidden="true" width="22" height="22" loading="lazy" />
					<?php else : ?>
						<span class="material-symbols-outlined" aria-hidden="true">groups</span>
					<?php endif; ?>
					<?php
					// "Familias / artesanos" es texto libre (ver template-community-admin.php):
					// admite un número puro ("42") o una frase ya redactada ("más de 50
					// artesanos", "Toda la comunidad"...). Solo se formatea como número con
					// number_format_i18n() cuando ES puramente numérico; si no, se muestra
					// el texto tal cual en vez de descartarlo.
					if ( ! empty( $community['num_familias'] ) && is_numeric( $community['num_familias'] ) ) {
						printf(
							/* translators: 1: nombre de la comunidad, 2: nº de familias */
							esc_html__( 'Parte de la %1$s · %2$s familias', 'amazonia-theme' ),
							esc_html( $community['nombre'] ),
							esc_html( number_format_i18n( $community['num_familias'] ) )
						);
					} elseif ( ! empty( $community['num_familias'] ) ) {
						printf(
							/* translators: 1: nombre de la comunidad, 2: descripción libre de familias/artesanos */
							esc_html__( 'Parte de la %1$s · %2$s', 'amazonia-theme' ),
							esc_html( $community['nombre'] ),
							esc_html( $community['num_familias'] )
						);
					} else {
						printf(
							/* translators: %s: nombre de la comunidad */
							esc_html__( 'Parte de la %s', 'amazonia-theme' ),
							esc_html( $community['nombre'] )
						);
					}
					?>
					<span class="material-symbols-outlined" aria-hidden="true">chevron_right</span>
				</a>
			<?php endif; ?>

			<div class="amz-hero__row">
				<?php if ( $avatar_url ) : ?>
					<img class="amz-logo amz-logo--store" src="<?php echo esc_url( $avatar_url ); ?>"
					     alt="<?php echo esc_attr( sprintf( __( 'Logo de %s', 'amazonia-theme' ), $store_name ) ); ?>"
					     width="120" height="120" loading="eager" />
				<?php else : ?>
					<div class="amz-logo amz-logo--store" aria-hidden="true"><?php echo esc_html( mb_substr( $store_name, 0, 1 ) ); ?></div>
				<?php endif; ?>

				<div class="amz-hero__body">
					<?php if ( $badge_text ) : ?>
						<div class="amz-chips">
							<span class="amz-chip amz-chip--cat"><?php echo esc_html( $badge_text ); ?></span>
						</div>
					<?php endif; ?>

					<h1 class="amz-title"><?php echo esc_html( $store_name ); ?></h1>

					<?php if ( $store_desc ) : ?>
						<p class="amz-lede"><?php echo esc_html( wp_trim_words( wp_strip_all_tags( $store_desc ), 28, '…' ) ); ?></p>
					<?php endif; ?>

					<?php if ( $short_location ) : ?>
						<div class="amz-meta">
							<span class="material-symbols-outlined" aria-hidden="true">location_on</span>
							<?php echo esc_html( $short_location ); ?>
						</div>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</header>

	<!-- ── Barra de confianza (stat tiles hermanos de la Pantalla A) ── -->
	<?php if ( $tiles ) : ?>
		<div class="amz-shell amz-stats-wrap">
			<div class="amz-stats">
				<?php foreach ( $tiles as $tile ) : ?>
					<div class="amz-stat">
						<div class="amz-stat__n">
							<?php if ( ! empty( $tile['star'] ) ) : ?>
								<span class="material-symbols-outlined" aria-hidden="true"
								      style="font-size:.72em;color:var(--amz-star);font-variation-settings:'FILL' 1;vertical-align:baseline">star</span>
							<?php endif; ?>
							<?php echo esc_html( $tile['n'] ); ?>
						</div>
						<div class="amz-stat__l"><?php echo esc_html( $tile['l'] ); ?></div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	<?php endif; ?>

	<!-- ── Catálogo ──────────────────────────────────────────────── -->
	<section class="amz-shell amz-section">
		<div class="amz-catalog-head">
			<div class="amz-h2row" style="margin-bottom:0">
				<h2 class="amz-h2"><?php esc_html_e( 'Catálogo', 'amazonia-theme' ); ?></h2>
			</div>

			<?php if ( $store_cats ) : ?>
				<div class="amz-filters">
					<a class="amz-filter" href="<?php echo esc_url( $store_url ); ?>"
					   <?php echo $active_cat ? '' : 'aria-current="true"'; ?>>
						<?php esc_html_e( 'Todos', 'amazonia-theme' ); ?>
					</a>
					<?php foreach ( $store_cats as $cat ) : ?>
						<a class="amz-filter" href="<?php echo esc_url( add_query_arg( 'product_cat', $cat->slug, $store_url ) ); ?>"
						   <?php echo ( $active_cat === $cat->slug ) ? 'aria-current="true"' : ''; ?>>
							<?php echo esc_html( $cat->name ); ?>
						</a>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>

		<?php if ( $vendor_products->have_posts() ) : ?>
			<div class="amz-products">
				<?php
				while ( $vendor_products->have_posts() ) :
					$vendor_products->the_post();
					$product = wc_get_product( get_the_ID() );
					if ( ! $product ) {
						continue;
					}
					$terms    = get_the_terms( get_the_ID(), 'product_cat' );
					$cat_name = ( $terms && ! is_wp_error( $terms ) ) ? $terms[0]->name : '';
					$rating   = (float) $product->get_average_rating();
					?>
					<a class="amz-product" href="<?php the_permalink(); ?>">
						<div class="amz-product__media">
							<?php
							if ( has_post_thumbnail() ) {
								the_post_thumbnail( 'woocommerce_thumbnail', [ 'alt' => esc_attr( get_the_title() ), 'loading' => 'lazy' ] );
							}
							if ( $cat_name ) :
								?>
								<span class="amz-product__cat"><?php echo esc_html( $cat_name ); ?></span>
							<?php endif; ?>
						</div>
						<div class="amz-product__body">
							<h3><?php echo esc_html( get_the_title() ); ?></h3>
							<div class="amz-product__foot">
								<span class="amz-product__price"><?php echo wp_kses_post( $product->get_price_html() ); ?></span>
								<?php if ( $rating > 0 ) : ?>
									<span class="amz-product__rate">
										<span class="material-symbols-outlined" aria-hidden="true">star</span>
										<?php echo esc_html( number_format_i18n( $rating, 1 ) ); ?>
									</span>
								<?php endif; ?>
							</div>
						</div>
					</a>
					<?php
				endwhile;
				wp_reset_postdata();
				?>
			</div>
		<?php else : ?>
			<div class="amz-empty--dashed">
				<div class="amz-empty__ico"><span class="material-symbols-outlined" aria-hidden="true">shopping_bag</span></div>
				<h3><?php esc_html_e( 'Aún no hay productos', 'amazonia-theme' ); ?></h3>
				<p>
					<?php
					echo $active_cat
						? esc_html__( 'No hay piezas en esta categoría. Prueba con otra.', 'amazonia-theme' )
						: esc_html__( 'Este taller está preparando sus primeras piezas. Vuelve pronto.', 'amazonia-theme' );
					?>
				</p>
			</div>
		<?php endif; ?>
	</section>

	<!-- ── Sobre la tienda (descripción completa; admite video pegado como URL) ── -->
	<?php if ( $store_desc_html ) : ?>
		<section class="amz-shell amz-section" style="max-width:1000px">
			<div class="amz-h2row">
				<h2 class="amz-h2"><?php esc_html_e( 'Sobre la tienda', 'amazonia-theme' ); ?></h2>
			</div>
			<div class="amz-about-content">
				<?php echo $store_desc_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- ya saneado con wp_kses_post() más arriba. ?>
			</div>
		</section>
	<?php endif; ?>

	<!-- ── Galería (fotos del taller/equipo que el vendedor sube en Ajustes) ── -->
	<?php if ( $store_gallery ) : ?>
		<section class="amz-shell amz-section" style="max-width:1000px">
			<div class="amz-h2row">
				<h2 class="amz-h2"><?php esc_html_e( 'Galería', 'amazonia-theme' ); ?></h2>
			</div>
			<div class="amz-gallery-grid">
				<?php foreach ( $store_gallery as $attachment_id ) :
					$full_url = wp_get_attachment_image_url( $attachment_id, 'large' );
					if ( ! $full_url ) {
						continue;
					}
					?>
					<a class="amz-gallery-item" href="<?php echo esc_url( $full_url ); ?>" data-amz-lightbox
					   aria-label="<?php echo esc_attr( sprintf( __( 'Ver foto de %s en grande', 'amazonia-theme' ), $store_name ) ); ?>">
						<?php
						echo wp_get_attachment_image(
							$attachment_id,
							'medium',
							false,
							array(
								'loading' => 'lazy',
								'alt'     => esc_attr( $store_name ),
							)
						);
						?>
					</a>
				<?php endforeach; ?>
			</div>
		</section>
	<?php endif; ?>

	<!-- ── Envío y devoluciones (solo si hay políticas escritas) ──── -->
	<?php if ( $policies ) : ?>
		<section class="amz-band">
			<div class="amz-shell amz-section" style="max-width:1000px">
				<div class="amz-h2row">
					<h2 class="amz-h2"><?php esc_html_e( 'Envío y devoluciones', 'amazonia-theme' ); ?></h2>
				</div>
				<div class="amz-policies">
					<?php $first = true; foreach ( $policies as $title => $body ) : ?>
						<details class="amz-policy"<?php echo $first ? ' open' : ''; ?>>
							<summary><?php echo esc_html( $title ); ?></summary>
							<p><?php echo wp_kses_post( $body ); ?></p>
						</details>
					<?php $first = false; endforeach; ?>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<!-- ── Ubicación y contacto ──────────────────────────────────── -->
	<?php if ( $address || $email || $phone ) : ?>
		<section class="amz-shell amz-section">
			<div class="amz-contact-grid">
				<?php if ( $address ) : ?>
					<div class="amz-card">
						<div class="amz-map"<?php echo $has_map_coords ? ' data-lat="' . esc_attr( $store_lat ) . '" data-lng="' . esc_attr( $store_lng ) . '" data-name="' . esc_attr( $store_name ) . '"' : ''; ?>>
							<span class="material-symbols-outlined" aria-hidden="true">location_on</span>
						</div>
						<div class="amz-card__pad">
							<h3><?php esc_html_e( 'Dónde estamos', 'amazonia-theme' ); ?></h3>
							<p><?php echo esc_html( $address ); ?></p>
						</div>
					</div>
				<?php endif; ?>

				<?php if ( $email || $phone ) : ?>
					<div class="amz-card">
						<div class="amz-card__pad">
							<h3><?php esc_html_e( 'Escríbele al taller', 'amazonia-theme' ); ?></h3>
							<p><?php esc_html_e( 'Resuelve tus dudas sobre una pieza, un encargo a medida o los tiempos de entrega.', 'amazonia-theme' ); ?></p>
							<div class="amz-contact-actions">
								<?php if ( $phone ) : ?>
									<a class="amz-contact-btn amz-contact-btn--wa" target="_blank" rel="noopener noreferrer"
									   href="https://wa.me/<?php echo esc_attr( preg_replace( '/[^0-9]/', '', $phone ) ); ?>">
										<span class="material-symbols-outlined" aria-hidden="true">chat</span>
										<?php esc_html_e( 'WhatsApp', 'amazonia-theme' ); ?>
									</a>
								<?php endif; ?>
								<?php if ( $email ) : ?>
									<a class="amz-contact-btn" href="mailto:<?php echo esc_attr( $email ); ?>">
										<span class="material-symbols-outlined" aria-hidden="true">mail</span>
										<?php esc_html_e( 'Enviar correo', 'amazonia-theme' ); ?>
									</a>
								<?php endif; ?>
								<?php if ( $phone ) : ?>
									<a class="amz-contact-btn" href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $phone ) ); ?>">
										<span class="material-symbols-outlined" aria-hidden="true">call</span>
										<?php echo esc_html( $phone ); ?>
									</a>
								<?php endif; ?>
							</div>
						</div>
					</div>
				<?php endif; ?>
			</div>
		</section>
	<?php endif; ?>

	<!-- ── Reseñas ───────────────────────────────────────────────── -->
	<section class="amz-shell amz-section" style="max-width:1000px">
		<div class="amz-h2row">
			<h2 class="amz-h2"><?php esc_html_e( 'Reseñas', 'amazonia-theme' ); ?></h2>
		</div>

		<?php if ( $reviews ) : ?>
			<div class="amz-reviews">
				<?php
				foreach ( $reviews as $review ) :
					$score  = get_comment_meta( $review->comment_ID, 'rating', true );
					$author = $review->comment_author ? $review->comment_author : __( 'Anónimo', 'amazonia-theme' );
					?>
					<article class="amz-review">
						<div class="amz-review__head">
							<div class="amz-review__av" aria-hidden="true"><?php echo esc_html( mb_strtoupper( mb_substr( $author, 0, 1 ) ) ); ?></div>
							<div class="amz-review__who">
								<div class="amz-review__name"><?php echo esc_html( $author ); ?></div>
								<div class="amz-review__date">
									<?php
									printf(
										/* translators: 1: fecha, 2: nombre del producto */
										esc_html__( '%1$s · sobre %2$s', 'amazonia-theme' ),
										esc_html( date_i18n( get_option( 'date_format' ), strtotime( $review->comment_date ) ) ),
										esc_html( get_the_title( $review->comment_post_ID ) )
									);
									?>
								</div>
							</div>
							<?php if ( $score ) : ?>
								<span class="amz-review__score">
									<span class="material-symbols-outlined" aria-hidden="true">star</span>
									<?php echo esc_html( number_format_i18n( (float) $score, 1 ) ); ?>
								</span>
							<?php endif; ?>
						</div>
						<p><?php echo esc_html( $review->comment_content ); ?></p>
					</article>
				<?php endforeach; ?>
			</div>
		<?php else : ?>
			<div class="amz-empty--dashed">
				<div class="amz-empty__ico"><span class="material-symbols-outlined" aria-hidden="true">chat_bubble</span></div>
				<h3><?php esc_html_e( 'Todavía sin reseñas', 'amazonia-theme' ); ?></h3>
				<p><?php esc_html_e( 'Sé la primera persona en compartir tu experiencia con este taller.', 'amazonia-theme' ); ?></p>
			</div>
		<?php endif; ?>
	</section>

	<!-- ── Cierre: vuelta a la comunidad + redes ─────────────────── -->
	<footer class="amz-close amz-close--store">
		<div class="amz-shell amz-close__inner">
			<div>
				<div class="amz-close__name"><?php echo esc_html( $store_name ); ?></div>
				<?php if ( $community ) : ?>
					<a class="amz-close__back" href="<?php echo esc_url( $community['url'] ); ?>">
						<?php
						printf(
							/* translators: %s: nombre de la comunidad */
							esc_html__( '← Volver a la %s', 'amazonia-theme' ),
							esc_html( $community['nombre'] )
						);
						?>
					</a>
				<?php endif; ?>
			</div>

			<?php if ( $store_user->has_social() ) : ?>
				<div class="amz-socials">
					<?php
					foreach ( $social_map as $key => $meta ) :
						if ( empty( $socials[ $key ] ) ) {
							continue;
						}
						?>
						<a class="amz-btn amz-btn--ghost" href="<?php echo esc_url( $socials[ $key ] ); ?>"
						   target="_blank" rel="noopener noreferrer">
							<span class="material-symbols-outlined" style="font-size:18px" aria-hidden="true"><?php echo esc_html( $meta['icon'] ); ?></span>
							<?php echo esc_html( $meta['label'] ); ?>
						</a>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>
	</footer>

</div>

<?php get_footer( 'shop' ); ?>
