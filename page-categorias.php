<?php
/**
 * Template Name: Categorías de Productos
 *
 * Plantilla para la página "Categorías" (slug sugerido: categorias).
 *
 * Muestra en una cuadrícula minimalista las categorías de producto de la
 * tienda (taxonomía `product_cat`). Cada tarjeta usa la imagen de la
 * categoría (term meta `thumbnail_id`), su nombre y el número de productos,
 * y enlaza al archivo de la categoría.
 *
 * @package Amazonia_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

// Categorías de producto de nivel superior, ocultando las vacías y la "sin categoría".
$product_categories = array();

if ( taxonomy_exists( 'product_cat' ) ) {
	$product_categories = get_terms( array(
		'taxonomy'   => 'product_cat',
		'hide_empty' => true,
		'parent'     => 0,
		'orderby'    => 'name',
		'order'      => 'ASC',
		'exclude'    => array( (int) get_option( 'default_product_cat', 0 ) ),
	) );

	if ( is_wp_error( $product_categories ) ) {
		$product_categories = array();
	}
}

$total_categorias = count( $product_categories );
$shop_url         = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/' );
?>

<main id="primary" class="site-main bg-background-light dark:bg-background-dark pb-24">

	<!-- Hero -->
	<section class="relative overflow-hidden bg-slate-900">
		<!-- Imagen de fondo -->
		<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/img/amazonia-hero-selva.jpg' ); ?>"
			 alt=""
			 aria-hidden="true"
			 fetchpriority="high"
			 class="absolute inset-0 w-full h-full object-cover object-center opacity-55" />
		<!-- Degradado oscuro sobre la imagen para legibilidad del texto -->
		<div class="absolute inset-0 bg-gradient-to-r from-slate-900/80 via-slate-900/50 to-slate-900/20"></div>
		<div class="absolute inset-0 bg-gradient-to-t from-slate-900/60 to-transparent"></div>

		<div class="relative z-10 max-w-7xl mx-auto px-6 lg:px-20 py-20 md:py-28">
			<?php
			if ( function_exists( 'woocommerce_breadcrumb' ) ) {
				woocommerce_breadcrumb( array(
					'delimiter'   => ' <span class="material-symbols-outlined text-[16px] text-white/40 align-middle">chevron_right</span> ',
					'wrap_before' => '<nav class="woocommerce-breadcrumb flex items-center gap-1 text-white/60 font-medium text-sm mb-6">',
					'wrap_after'  => '</nav>',
				) );
			}
			?>

			<span class="inline-flex items-center gap-2 px-4 py-1.5 bg-primary/80 backdrop-blur-sm text-white text-xs font-bold rounded-full mb-6 uppercase tracking-widest border border-primary/40">
				<span class="material-symbols-outlined text-[16px]">category</span>
				<?php esc_html_e( 'Explora la tienda', 'amazonia-theme' ); ?>
			</span>

			<h1 class="text-white text-4xl md:text-6xl font-black leading-tight mb-5 max-w-3xl drop-shadow-lg">
				<?php esc_html_e( 'Categorías de Productos', 'amazonia-theme' ); ?>
			</h1>

			<p class="text-slate-200 text-lg md:text-xl max-w-2xl leading-relaxed font-light mb-0">
				<?php esc_html_e( 'Recorre nuestras categorías y descubre bioproductos sostenibles nacidos en el corazón de la Amazonía.', 'amazonia-theme' ); ?>
			</p>

			<?php if ( $total_categorias > 0 ) : ?>
			<div class="mt-8 inline-flex items-center gap-2 text-white/70 text-sm font-medium">
				<span class="material-symbols-outlined text-[18px] text-primary">grid_view</span>
				<?php echo esc_html( sprintf(
					/* translators: %d: número de categorías */
					_n( '%d categoría disponible', '%d categorías disponibles', $total_categorias, 'amazonia-theme' ),
					$total_categorias
				) ); ?>
			</div>
			<?php endif; ?>
		</div>
	</section>

	<!-- Listado de categorías -->
	<section class="max-w-7xl mx-auto px-6 lg:px-20 pt-12 pb-4">

		<?php if ( $total_categorias > 0 ) : ?>

		<div class="grid grid-cols-2 md:grid-cols-3 gap-4 md:gap-6">
			<?php
			foreach ( $product_categories as $category ) :
				$term_link    = get_term_link( $category );
				$thumbnail_id = get_term_meta( $category->term_id, 'thumbnail_id', true );

				if ( $thumbnail_id ) {
					$image_url = wp_get_attachment_image_url( $thumbnail_id, 'medium_large' );
				} elseif ( function_exists( 'wc_placeholder_img_src' ) ) {
					$image_url = wc_placeholder_img_src( 'medium_large' );
				} else {
					$image_url = '';
				}

				if ( is_wp_error( $term_link ) ) {
					continue;
				}
				?>
				<a href="<?php echo esc_url( $term_link ); ?>"
				   class="group flex flex-col overflow-hidden rounded-2xl bg-white dark:bg-slate-800 shadow-sm hover:shadow-xl ring-1 ring-slate-200 dark:ring-slate-700 hover:ring-primary/40 transition-all duration-300 hover:-translate-y-1 no-underline">

					<!-- Imagen -->
					<div class="relative overflow-hidden aspect-[3/2]">
						<?php if ( $image_url ) : ?>
							<img src="<?php echo esc_url( $image_url ); ?>"
								 alt="<?php echo esc_attr( $category->name ); ?>"
								 loading="lazy"
								 class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105" />
						<?php else : ?>
							<div class="w-full h-full bg-gradient-to-br from-primary/10 via-green-50 to-forest-green/20 flex items-center justify-center">
								<span class="material-symbols-outlined text-6xl text-primary/30">eco</span>
							</div>
						<?php endif; ?>

						<!-- Pill del nº de productos sobre la imagen (esquina superior derecha) -->
						<span class="absolute top-3 right-3 inline-flex items-center gap-1 px-2.5 py-1 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 text-xs font-bold rounded-full shadow-sm ring-1 ring-slate-200 dark:ring-slate-600">
							<span class="material-symbols-outlined text-[13px] text-primary">inventory_2</span>
							<?php echo esc_html( number_format_i18n( (int) $category->count ) ); ?>
						</span>
					</div>

					<!-- Panel de texto — fondo sólido, contraste garantizado -->
					<div class="flex items-center justify-between gap-3 px-4 py-4 border-t border-slate-100 dark:border-slate-700">
						<div class="min-w-0">
							<h2 class="text-slate-900 dark:text-white text-sm md:text-base font-bold leading-snug line-clamp-1 m-0">
								<?php echo esc_html( $category->name ); ?>
							</h2>
							<span class="block mt-0.5 text-primary text-xs font-semibold">
								<?php echo esc_html( sprintf(
									/* translators: %d: número de productos */
									_n( '%d producto', '%d productos', (int) $category->count, 'amazonia-theme' ),
									(int) $category->count
								) ); ?>
							</span>
						</div>
						<span class="flex-shrink-0 flex items-center justify-center h-8 w-8 rounded-full bg-slate-100 dark:bg-slate-700 text-slate-500 dark:text-slate-300 group-hover:bg-primary group-hover:text-white transition-all duration-300">
							<span class="material-symbols-outlined text-[18px] transition-transform duration-300 group-hover:translate-x-0.5">arrow_forward</span>
						</span>
					</div>
				</a>
				<?php
			endforeach;
			?>
		</div>

		<?php else : ?>

		<!-- Estado vacío -->
		<div class="bg-white dark:bg-slate-800 rounded-2xl ring-1 ring-slate-200 dark:ring-slate-700 py-24 px-6 text-center">
			<span class="material-symbols-outlined text-6xl text-slate-300 dark:text-slate-600 block mb-4">category</span>
			<h2 class="text-xl font-bold text-slate-900 dark:text-white mb-2">
				<?php esc_html_e( 'Aún no hay categorías', 'amazonia-theme' ); ?>
			</h2>
			<p class="text-slate-500 dark:text-slate-400 mb-8 max-w-sm mx-auto text-sm">
				<?php esc_html_e( 'Pronto encontrarás aquí todas las categorías de productos de la tienda.', 'amazonia-theme' ); ?>
			</p>
			<a href="<?php echo esc_url( $shop_url ); ?>"
			   class="inline-flex items-center gap-2 px-6 py-3 rounded-full bg-primary text-white font-bold text-sm hover:bg-primary/90 transition-colors no-underline">
				<span class="material-symbols-outlined text-[18px]">storefront</span>
				<?php esc_html_e( 'Ir a la tienda', 'amazonia-theme' ); ?>
			</a>
		</div>

		<?php endif; ?>

	</section>

</main>

<?php
get_footer();
