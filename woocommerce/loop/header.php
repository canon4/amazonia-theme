<?php
/**
 * Product taxonomy archive header
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/loop/header.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 8.6.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<header class="woocommerce-products-header">
	<?php
	$search_term = get_search_query();
	if ( apply_filters( 'woocommerce_show_page_title', true ) ) :
		if ( '' !== $search_term ) :
			global $wp_query;
			$found = (int) $wp_query->found_posts;
			?>
			<h1 class="woocommerce-products-header__title page-title">
				<?php
				printf(
					/* translators: %s: término buscado */
					esc_html__( 'Resultados para %s', 'amazonia-theme' ),
					'<span class="text-primary">&ldquo;' . esc_html( $search_term ) . '&rdquo;</span>'
				);
				?>
			</h1>
			<p class="woocommerce-result-count" role="alert">
				<?php
				printf(
					esc_html( _n( '%d producto encontrado', '%d productos encontrados', $found, 'amazonia-theme' ) ),
					$found
				);
				?>
			</p>
		<?php else : ?>
			<h1 class="woocommerce-products-header__title page-title"><?php woocommerce_page_title(); ?></h1>
		<?php endif; ?>
	<?php endif; ?>

	<?php do_action( 'woocommerce_archive_description' ); ?>
</header>
