<?php
/**
 * Custom Template for displaying a single store (vendor) page.
 * Overrides the WCFM Marketplace default template.
 *
 * Diseño minimalista de página de vendedor para ecommerce:
 *  - Cabecera con banner, avatar, valoración y datos clave del vendedor.
 *  - Barra de estadísticas con información real de la base de datos.
 *  - Carruseles de productos (propios del vendedor y relacionados).
 *
 * Todos los datos provienen del objeto de tienda de WCFM y de WooCommerce.
 */
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $WCFM, $WCFMmp;

$wcfm_store_url  = wcfm_get_option( 'wcfm_store_url', 'store' );
$wcfm_store_name = apply_filters( 'wcfmmp_store_query_var', get_query_var( $wcfm_store_url ) );
if ( empty( $wcfm_store_name ) ) return;
$seller_info = get_user_by( 'slug', $wcfm_store_name );
if ( ! $seller_info ) return;

$store_user = wcfmmp_get_store( $seller_info->ID );
$store_info = $store_user->get_shop_info();

/* ---------------------------------------------------------------------------
 * Datos base del vendedor (reales, desde la BD)
 * ------------------------------------------------------------------------ */
$store_name        = isset( $store_info['store_name'] ) && $store_info['store_name'] !== '' ? esc_html( $store_info['store_name'] ) : esc_html( $seller_info->display_name );
$store_description = isset( $store_info['shop_description'] ) ? wp_kses_post( $store_info['shop_description'] ) : '';

$banner_url = $store_user->get_banner();
if ( ! $banner_url ) {
	$banner_url = ! empty( $WCFMmp->wcfmmp_marketplace_options['store_default_banner'] )
		? wcfm_get_attachment_url( $WCFMmp->wcfmmp_marketplace_options['store_default_banner'] )
		: esc_url( $WCFMmp->plugin_url . 'assets/images/default_banner.jpg' );
}
$avatar_url = $store_user->get_avatar();
$email      = $store_user->get_email();
$phone      = $store_user->get_phone();
$address    = $store_user->get_address_string();

/* Ubicación corta: Ciudad, País */
$city    = isset( $store_info['address']['city'] ) ? $store_info['address']['city'] : '';
$country = isset( $store_info['address']['country'] ) ? $store_info['address']['country'] : '';
if ( $country && function_exists( 'WC' ) ) {
	$countries = WC()->countries->get_countries();
	if ( isset( $countries[ $country ] ) ) {
		$country = $countries[ $country ];
	}
}
$location_parts = array_filter( array( $city, $country ) );
$short_location = ! empty( $location_parts ) ? implode( ', ', $location_parts ) : '';

/* Categoría del vendedor (badge) */
$badge_text        = '';
$vendor_categories = wp_get_object_terms( $seller_info->ID, 'wcfm_vendor_category' );
if ( ! is_wp_error( $vendor_categories ) && ! empty( $vendor_categories ) ) {
	$badge_text = $vendor_categories[0]->name;
}
if ( ! $badge_text ) {
	$badge_text = __( 'Productor Local', 'amazonia-theme' );
}

/* Métricas reales del vendedor */
$avg_rating     = (float) $store_user->get_avg_review_rating();
$review_count   = (int) $store_user->get_total_review_count();
$follower_count = (int) $store_user->get_total_follower_count();

$register_date = $store_user->get_register_date();
$member_since  = $register_date ? date_i18n( 'M Y', strtotime( $register_date ) ) : '';

/* Redes sociales (solo se muestran las que existan) */
$socials     = $store_user->get_social_profiles();
$social_map  = array(
	'fb'        => array( 'label' => 'Facebook',  'icon' => 'thumb_up' ),
	'instagram' => array( 'label' => 'Instagram', 'icon' => 'photo_camera' ),
	'twitter'   => array( 'label' => 'Twitter',   'icon' => 'tag' ),
	'youtube'   => array( 'label' => 'YouTube',   'icon' => 'play_circle' ),
	'linkedin'  => array( 'label' => 'LinkedIn',  'icon' => 'work' ),
	'pinterest' => array( 'label' => 'Pinterest', 'icon' => 'push_pin' ),
);

/* Categorías de producto de la tienda */
$store_cats = $store_user->get_store_taxonomies( 'product_cat' );

/* URL de la tienda para "ver todo" */
$store_url = function_exists( 'wcfmmp_get_store_url' ) ? wcfmmp_get_store_url( $seller_info->ID ) : get_author_posts_url( $seller_info->ID );

/* ---------------------------------------------------------------------------
 * Consultas de productos
 * ------------------------------------------------------------------------ */
$vendor_products = new WP_Query( array(
	'post_type'      => 'product',
	'post_status'    => 'publish',
	'author'         => $seller_info->ID,
	'posts_per_page' => 12,
	'orderby'        => 'date',
	'order'          => 'DESC',
) );
$product_count = (int) $vendor_products->found_posts;

/* Categorías de los productos del vendedor -> productos relacionados */
$cat_ids = array();
if ( ! empty( $vendor_products->posts ) ) {
	foreach ( $vendor_products->posts as $vp ) {
		$terms = wp_get_post_terms( $vp->ID, 'product_cat', array( 'fields' => 'ids' ) );
		if ( ! is_wp_error( $terms ) ) {
			$cat_ids = array_merge( $cat_ids, $terms );
		}
	}
	$cat_ids = array_values( array_unique( $cat_ids ) );
}

$related_args = array(
	'post_type'      => 'product',
	'post_status'    => 'publish',
	'posts_per_page' => 12,
	'orderby'        => 'date',
	'order'          => 'DESC',
	'author__not_in' => array( $seller_info->ID ),
);
if ( ! empty( $cat_ids ) ) {
	$related_args['tax_query'] = array(
		array(
			'taxonomy' => 'product_cat',
			'field'    => 'term_id',
			'terms'    => $cat_ids,
		),
	);
}
$related_products = new WP_Query( $related_args );

/* ---------------------------------------------------------------------------
 * Helpers de render
 * ------------------------------------------------------------------------ */
if ( ! function_exists( 'amazonia_store_rating_stars' ) ) {
	/** Devuelve el HTML de estrellas para una valoración media (0-5). */
	function amazonia_store_rating_stars( $rating, $size = 18 ) {
		$html = '<div class="flex items-center text-amber-400" aria-hidden="true">';
		for ( $i = 1; $i <= 5; $i++ ) {
			$icon = ( $i <= round( $rating ) ) ? 'star' : 'star';
			$cls  = ( $i <= round( $rating ) ) ? 'text-amber-400' : 'text-slate-300';
			$html .= '<span class="material-symbols-outlined ' . $cls . '" style="font-size:' . intval( $size ) . 'px">star</span>';
		}
		$html .= '</div>';
		return $html;
	}
}

if ( ! function_exists( 'amazonia_render_product_carousel' ) ) {
	/**
	 * Renderiza un carrusel horizontal de productos a partir de un WP_Query.
	 * Usa la plantilla content-product.php del tema para cada tarjeta.
	 */
	function amazonia_render_product_carousel( $query, $carousel_id, $empty_message = '' ) {
		if ( ! $query->have_posts() ) {
			if ( $empty_message ) {
				echo '<p class="text-slate-500">' . esc_html( $empty_message ) . '</p>';
			}
			return;
		}
		?>
		<div class="relative group/carousel" data-carousel>
			<!-- Flecha anterior -->
			<button type="button" data-carousel-prev
				class="hidden sm:flex absolute -left-4 top-1/2 -translate-y-1/2 z-20 h-11 w-11 items-center justify-center rounded-full bg-white shadow-lg shadow-black/10 text-slate-700 hover:text-primary hover:scale-105 transition-all opacity-0 pointer-events-none"
				aria-label="<?php esc_attr_e( 'Anterior', 'amazonia-theme' ); ?>">
				<span class="material-symbols-outlined">chevron_left</span>
			</button>

			<div id="<?php echo esc_attr( $carousel_id ); ?>" data-carousel-track
				class="flex gap-5 overflow-x-auto snap-x snap-mandatory scroll-smooth pb-3 amazonia-no-scrollbar">
				<?php
				wc_setup_loop( array( 'is_paginated' => false ) );
				$i = 0;
				while ( $query->have_posts() ) {
					$query->the_post();
					wc_set_loop_prop( 'loop', ++$i );
					echo '<div class="snap-start shrink-0 w-[52%] sm:w-[38%] md:w-[220px] lg:w-[210px] flex">';
					wc_get_template_part( 'content', 'product' );
					echo '</div>';
				}
				wc_reset_loop();
				wp_reset_postdata();
				?>
			</div>

			<!-- Flecha siguiente -->
			<button type="button" data-carousel-next
				class="hidden sm:flex absolute -right-4 top-1/2 -translate-y-1/2 z-20 h-11 w-11 items-center justify-center rounded-full bg-white shadow-lg shadow-black/10 text-slate-700 hover:text-primary hover:scale-105 transition-all"
				aria-label="<?php esc_attr_e( 'Siguiente', 'amazonia-theme' ); ?>">
				<span class="material-symbols-outlined">chevron_right</span>
			</button>
		</div>
		<?php
	}
}

get_header( 'shop' );
?>

<style>
	.amazonia-no-scrollbar::-webkit-scrollbar { display: none; }
	.amazonia-no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>

<div class="wcfmmp-single-store-holder w-full min-h-screen bg-background-light dark:bg-background-dark pb-24 font-display text-slate-800 dark:text-slate-100">

	<!-- ==================== HERO ==================== -->
	<section class="relative w-full h-[300px] md:h-[380px] bg-cover bg-center" style="background-image:url('<?php echo esc_url( $banner_url ); ?>');">
		<div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/40 to-black/20"></div>
		<div class="absolute inset-0 max-w-[1400px] mx-auto px-4 sm:px-8 flex flex-col justify-end pb-24 md:pb-28">
			<div class="flex flex-wrap items-center gap-3 mb-3">
				<span class="bg-primary text-white text-[11px] font-bold uppercase tracking-wider py-1.5 px-3 rounded-full shadow-lg">
					<?php echo esc_html( mb_strtoupper( $badge_text ) ); ?>
				</span>
				<?php if ( $short_location ) : ?>
					<span class="text-white/90 text-sm flex items-center gap-1 font-medium">
						<span class="material-symbols-outlined text-[18px]">location_on</span>
						<span class="capitalize"><?php echo esc_html( mb_strtolower( $short_location ) ); ?></span>
					</span>
				<?php endif; ?>
			</div>
			<h1 class="text-4xl md:text-5xl font-black text-white tracking-tight drop-shadow-sm"><?php echo $store_name; ?></h1>
		</div>
	</section>

	<!-- ==================== TARJETA DE PERFIL (superpuesta) ==================== -->
	<div class="max-w-[1400px] mx-auto px-4 sm:px-8 -mt-16 relative z-10">
		<div class="bg-white dark:bg-slate-800 rounded-2xl shadow-xl shadow-black/5 border border-slate-100 dark:border-slate-700 p-5 sm:p-7">
			<div class="flex flex-col md:flex-row md:items-center gap-6">

				<!-- Avatar + nombre + valoración -->
				<div class="flex items-center gap-4 flex-1 min-w-0">
					<img src="<?php echo esc_url( $avatar_url ); ?>" alt="<?php echo esc_attr( $store_name ); ?>"
						class="w-20 h-20 sm:w-24 sm:h-24 rounded-2xl object-cover ring-4 ring-white dark:ring-slate-800 shadow-md shrink-0"
						width="96" height="96" loading="lazy">
					<div class="min-w-0">
						<h2 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white truncate"><?php echo $store_name; ?></h2>
						<div class="flex items-center gap-2 mt-1.5 flex-wrap">
							<?php if ( $review_count > 0 ) : ?>
								<?php echo amazonia_store_rating_stars( $avg_rating, 18 ); ?>
								<span class="text-sm font-semibold text-slate-700 dark:text-slate-200"><?php echo esc_html( number_format( $avg_rating, 1 ) ); ?></span>
								<span class="text-sm text-slate-400">(<?php echo esc_html( $review_count ); ?> <?php esc_html_e( 'reseñas', 'amazonia-theme' ); ?>)</span>
							<?php else : ?>
								<span class="inline-flex items-center gap-1 text-xs font-semibold text-primary bg-primary/10 px-2.5 py-1 rounded-full">
									<span class="material-symbols-outlined text-[15px]">verified</span>
									<?php esc_html_e( 'Nuevo en Amazonia', 'amazonia-theme' ); ?>
								</span>
							<?php endif; ?>
						</div>
					</div>
				</div>

				<!-- CTAs -->
				<div class="flex items-center gap-3 shrink-0">
					<?php if ( $phone ) : ?>
						<a href="https://wa.me/<?php echo preg_replace( '/[^0-9]/', '', $phone ); ?>" target="_blank" rel="noopener"
							class="flex-1 md:flex-none bg-[#25D366] hover:bg-[#1ebe57] text-white font-semibold py-3 px-5 rounded-xl flex items-center justify-center gap-2 transition-colors">
							<span class="material-symbols-outlined text-xl">chat</span>
							<span class="hidden sm:inline"><?php esc_html_e( 'WhatsApp', 'amazonia-theme' ); ?></span>
						</a>
					<?php endif; ?>
					<?php if ( $email ) : ?>
						<a href="mailto:<?php echo esc_attr( $email ); ?>"
							class="flex-1 md:flex-none bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-800 dark:text-slate-100 font-semibold py-3 px-5 rounded-xl flex items-center justify-center gap-2 transition-colors">
							<span class="material-symbols-outlined text-xl">mail</span>
							<span class="hidden sm:inline"><?php esc_html_e( 'Contactar', 'amazonia-theme' ); ?></span>
						</a>
					<?php endif; ?>
				</div>
			</div>

			<!-- Barra de estadísticas reales -->
			<div class="grid grid-cols-2 md:grid-cols-4 gap-3 mt-6 pt-6 border-t border-slate-100 dark:border-slate-700">
				<div class="text-center">
					<p class="text-2xl font-black text-slate-900 dark:text-white"><?php echo esc_html( $product_count ); ?></p>
					<p class="text-xs text-slate-400 font-medium uppercase tracking-wider mt-0.5"><?php esc_html_e( 'Productos', 'amazonia-theme' ); ?></p>
				</div>
				<div class="text-center">
					<p class="text-2xl font-black text-slate-900 dark:text-white">
						<?php echo $review_count > 0 ? esc_html( number_format( $avg_rating, 1 ) ) : '—'; ?>
					</p>
					<p class="text-xs text-slate-400 font-medium uppercase tracking-wider mt-0.5"><?php esc_html_e( 'Valoración', 'amazonia-theme' ); ?></p>
				</div>
				<div class="text-center">
					<p class="text-2xl font-black text-slate-900 dark:text-white"><?php echo esc_html( $follower_count ); ?></p>
					<p class="text-xs text-slate-400 font-medium uppercase tracking-wider mt-0.5"><?php esc_html_e( 'Seguidores', 'amazonia-theme' ); ?></p>
				</div>
				<div class="text-center">
					<p class="text-2xl font-black text-slate-900 dark:text-white capitalize"><?php echo $member_since ? esc_html( $member_since ) : '—'; ?></p>
					<p class="text-xs text-slate-400 font-medium uppercase tracking-wider mt-0.5"><?php esc_html_e( 'Miembro desde', 'amazonia-theme' ); ?></p>
				</div>
			</div>
		</div>
	</div>

	<!-- ==================== CUERPO ==================== -->
	<div class="max-w-[1400px] mx-auto px-4 sm:px-8 mt-8">
		<div class="flex flex-col lg:flex-row gap-8">

			<!-- Sidebar: información del vendedor -->
			<aside class="lg:w-1/3 xl:w-[30%] flex flex-col gap-6 lg:sticky lg:top-24 lg:self-start">

				<!-- Sobre la tienda -->
				<?php if ( $store_description ) : ?>
					<div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 p-6">
						<h3 class="text-lg font-bold text-slate-900 dark:text-white mb-3 flex items-center gap-2">
							<span class="material-symbols-outlined text-primary">storefront</span>
							<?php esc_html_e( 'Sobre la tienda', 'amazonia-theme' ); ?>
						</h3>
						<div class="prose prose-sm max-w-none text-slate-600 dark:text-slate-300 leading-relaxed">
							<?php echo $store_description; ?>
						</div>
					</div>
				<?php endif; ?>

				<!-- Información de contacto -->
				<div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 p-6">
					<h3 class="text-lg font-bold text-slate-900 dark:text-white mb-4 flex items-center gap-2">
						<span class="material-symbols-outlined text-primary">contact_page</span>
						<?php esc_html_e( 'Información', 'amazonia-theme' ); ?>
					</h3>
					<ul class="flex flex-col gap-4 text-sm">
						<?php if ( $address ) : ?>
							<li class="flex items-start gap-3">
								<span class="material-symbols-outlined text-slate-400 text-[20px] mt-0.5">location_on</span>
								<span class="text-slate-600 dark:text-slate-300"><?php echo esc_html( $address ); ?></span>
							</li>
						<?php endif; ?>
						<?php if ( $phone ) : ?>
							<li class="flex items-start gap-3">
								<span class="material-symbols-outlined text-slate-400 text-[20px] mt-0.5">call</span>
								<a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $phone ) ); ?>" class="text-slate-600 dark:text-slate-300 hover:text-primary transition-colors"><?php echo esc_html( $phone ); ?></a>
							</li>
						<?php endif; ?>
						<?php if ( $email ) : ?>
							<li class="flex items-start gap-3">
								<span class="material-symbols-outlined text-slate-400 text-[20px] mt-0.5">mail</span>
								<a href="mailto:<?php echo esc_attr( $email ); ?>" class="text-slate-600 dark:text-slate-300 hover:text-primary transition-colors break-all"><?php echo esc_html( $email ); ?></a>
							</li>
						<?php endif; ?>
					</ul>

					<?php if ( $store_user->has_social() ) : ?>
						<div class="flex flex-wrap gap-2 mt-5 pt-5 border-t border-slate-100 dark:border-slate-700">
							<?php foreach ( $social_map as $key => $meta ) :
								if ( empty( $socials[ $key ] ) ) continue; ?>
								<a href="<?php echo esc_url( $socials[ $key ] ); ?>" target="_blank" rel="noopener"
									class="h-9 w-9 flex items-center justify-center rounded-full bg-slate-100 dark:bg-slate-700 text-slate-500 hover:bg-primary hover:text-white transition-colors"
									aria-label="<?php echo esc_attr( $meta['label'] ); ?>">
									<span class="material-symbols-outlined text-[18px]"><?php echo esc_html( $meta['icon'] ); ?></span>
								</a>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>
				</div>

				<!-- Categorías de la tienda -->
				<?php if ( ! is_wp_error( $store_cats ) && ! empty( $store_cats ) ) : ?>
					<div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 p-6">
						<h3 class="text-lg font-bold text-slate-900 dark:text-white mb-4 flex items-center gap-2">
							<span class="material-symbols-outlined text-primary">sell</span>
							<?php esc_html_e( 'Categorías', 'amazonia-theme' ); ?>
						</h3>
						<div class="flex flex-wrap gap-2">
							<?php foreach ( $store_cats as $cat ) :
								$cat_link = add_query_arg( 'product_cat', $cat->slug, $store_url ); ?>
								<a href="<?php echo esc_url( $cat_link ); ?>"
									class="text-xs font-medium text-slate-600 dark:text-slate-300 bg-slate-100 dark:bg-slate-700 hover:bg-primary/10 hover:text-primary px-3 py-1.5 rounded-full transition-colors">
									<?php echo esc_html( $cat->name ); ?>
								</a>
							<?php endforeach; ?>
						</div>
					</div>
				<?php endif; ?>

				<!-- Valores / sellos de confianza -->
				<div class="bg-primary/5 rounded-2xl border border-primary/10 p-6">
					<div class="flex flex-col gap-3">
						<div class="flex items-center gap-3">
							<span class="material-symbols-outlined text-primary bg-white rounded-full shadow-sm p-1 text-[20px]">handshake</span>
							<span class="text-sm font-medium text-slate-700 dark:text-slate-200"><?php esc_html_e( 'Comercio justo y directo', 'amazonia-theme' ); ?></span>
						</div>
						<div class="flex items-center gap-3">
							<span class="material-symbols-outlined text-primary bg-white rounded-full shadow-sm p-1 text-[20px]">volunteer_activism</span>
							<span class="text-sm font-medium text-slate-700 dark:text-slate-200"><?php esc_html_e( 'Hecho a mano por la comunidad', 'amazonia-theme' ); ?></span>
						</div>
						<div class="flex items-center gap-3">
							<span class="material-symbols-outlined text-primary bg-white rounded-full shadow-sm p-1 text-[20px]">forest</span>
							<span class="text-sm font-medium text-slate-700 dark:text-slate-200"><?php esc_html_e( 'Producción sostenible', 'amazonia-theme' ); ?></span>
						</div>
					</div>
				</div>
			</aside>

			<!-- Contenido: carruseles de productos -->
			<div class="lg:w-2/3 xl:w-[70%] flex flex-col gap-12">

				<!-- Carrusel de productos del vendedor -->
				<section>
					<div class="flex justify-between items-end mb-6">
						<div>
							<h2 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white"><?php esc_html_e( 'Nuestros productos', 'amazonia-theme' ); ?></h2>
							<p class="text-slate-500 dark:text-slate-400 mt-1"><?php esc_html_e( 'Piezas auténticas directamente de la comunidad', 'amazonia-theme' ); ?></p>
						</div>
						<?php if ( $product_count > 0 ) : ?>
							<a href="<?php echo esc_url( $store_url ); ?>" class="text-primary font-semibold hover:text-primary/80 flex items-center gap-1 group shrink-0 pb-1">
								<span class="hidden sm:inline"><?php esc_html_e( 'Ver todo', 'amazonia-theme' ); ?></span>
								<span class="material-symbols-outlined text-lg group-hover:translate-x-1 transition-transform">arrow_forward</span>
							</a>
						<?php endif; ?>
					</div>
					<?php amazonia_render_product_carousel( $vendor_products, 'store-products-carousel', __( 'Esta tienda aún no tiene productos publicados.', 'amazonia-theme' ) ); ?>
				</section>

				<!-- Carrusel de productos relacionados -->
				<?php if ( $related_products->have_posts() ) : ?>
					<section>
						<div class="flex justify-between items-end mb-6">
							<div>
								<h2 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white"><?php esc_html_e( 'También te puede interesar', 'amazonia-theme' ); ?></h2>
								<p class="text-slate-500 dark:text-slate-400 mt-1"><?php esc_html_e( 'Productos similares de otras comunidades', 'amazonia-theme' ); ?></p>
							</div>
						</div>
						<?php amazonia_render_product_carousel( $related_products, 'related-products-carousel' ); ?>
					</section>
				<?php endif; ?>

			</div>
		</div>
	</div>
</div>

<script>
( function () {
	function initCarousel( root ) {
		var track = root.querySelector( '[data-carousel-track]' );
		if ( ! track ) return;
		var prev = root.querySelector( '[data-carousel-prev]' );
		var next = root.querySelector( '[data-carousel-next]' );

		function step() { return Math.max( track.clientWidth * 0.85, 260 ); }

		function toggle( btn, hidden ) {
			if ( ! btn ) return;
			btn.classList.toggle( 'opacity-0', hidden );
			btn.classList.toggle( 'pointer-events-none', hidden );
		}

		function update() {
			var max = track.scrollWidth - track.clientWidth - 2;
			var scrollable = max > 2;
			toggle( prev, ! scrollable || track.scrollLeft <= 2 );
			toggle( next, ! scrollable || track.scrollLeft >= max );
		}

		if ( prev ) prev.addEventListener( 'click', function () { track.scrollBy( { left: -step(), behavior: 'smooth' } ); } );
		if ( next ) next.addEventListener( 'click', function () { track.scrollBy( { left: step(), behavior: 'smooth' } ); } );
		track.addEventListener( 'scroll', update, { passive: true } );
		window.addEventListener( 'resize', update );
		update();
	}

	document.querySelectorAll( '[data-carousel]' ).forEach( initCarousel );
} )();
</script>

<?php get_footer( 'shop' ); ?>
