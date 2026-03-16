<?php
/**
 * The template for displaying product content within loops
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/content-product.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 9.4.0
 */

defined( 'ABSPATH' ) || exit;

global $product;

// Check if the product is a valid WooCommerce product and ensure its visibility before proceeding.
if ( ! is_a( $product, WC_Product::class ) || ! $product->is_visible() ) {
	return;
}
?>
<li <?php wc_product_class( 'group bg-white dark:bg-slate-800 rounded-xl overflow-hidden border border-primary/5 hover:border-primary/20 transition-all hover:shadow-xl hover:shadow-primary/5 flex flex-col !w-full !float-none !m-0', $product ); ?>>
    <div class="aspect-square relative overflow-hidden bg-background-light">
        <a href="<?php echo esc_url( $product->get_permalink() ); ?>" class="block w-full h-full">
            <?php 
            echo $product->get_image( 'woocommerce_thumbnail', array( 'class' => 'w-full h-full object-cover group-hover:scale-110 transition-transform duration-500' ) ); 
            ?>
        </a>
        <?php if ( $product->is_on_sale() ) : ?>
            <div class="absolute top-3 left-3 bg-primary text-white text-[10px] font-black uppercase px-2 py-1 rounded-full shadow-lg">
                <?php echo apply_filters( 'woocommerce_sale_flash', esc_html__( 'Oferta', 'woocommerce' ), $product ); ?>
            </div>
        <?php endif; ?>
        
        <button class="absolute top-3 right-3 h-8 w-8 bg-white/90 rounded-full flex items-center justify-center text-slate-400 hover:text-red-500 transition-colors">
            <span class="material-symbols-outlined text-[18px]">favorite</span>
        </button>
    </div>

    <div class="p-4 flex flex-col flex-1">
        <?php
        $rating_count = $product->get_rating_count();
        $review_count = $product->get_review_count();
        $average      = $product->get_average_rating();

        if ( $rating_count > 0 ) {
            echo '<div class="flex items-center gap-1 mb-2">';
            
            // Loop de estrellas simples para simplificar en Tailwind
            echo '<div class="flex text-yellow-400 text-[16px]">';
            for ($i = 1; $i <= 5; $i++) {
                if ($i <= $average) {
                    echo '<span class="material-symbols-outlined text-[16px] fill-current">star</span>';
                } else {
                    echo '<span class="material-symbols-outlined text-[16px] text-slate-300">star</span>';
                }
            }
            echo '</div>';

            echo '<span class="text-[10px] text-slate-400 font-medium">(' . esc_html( $review_count ) . ')</span>';
            echo '</div>';
        } else {
            echo '<div class="flex items-center gap-1 mb-2 h-5"></div>';
        }
        ?>

        <a href="<?php echo esc_url( $product->get_permalink() ); ?>">
            <h3 class="font-bold text-slate-800 dark:text-slate-100 mb-1 line-clamp-2"><?php echo $product->get_title(); ?></h3>
        </a>
        
        <?php 
        $store_name = 'Comunidad Amazonia';
        if ( function_exists( 'wcfm_get_vendor_store_name' ) ) {
            $vendor_id = wcfm_get_vendor_id_by_post( $product->get_id() );
            if ( $vendor_id ) {
                $store_name = wcfm_get_vendor_store_name( $vendor_id );
            }
        }
        echo '<p class="text-xs text-primary font-medium mb-3">' . esc_html( $store_name ) . '</p>';
        ?>

        <div class="mt-auto pt-4 flex items-center justify-between gap-1">
            <span class="text-xl font-black text-slate-900 dark:text-slate-100 flex-1">
                <?php echo $product->get_price_html(); ?>
            </span>
            
            <?php
            echo apply_filters( 'woocommerce_loop_add_to_cart_link', 
                sprintf( '<a href="%s" data-quantity="%s" class="%s bg-primary hover:bg-primary/90 text-white text-[10px] sm:text-xs font-bold px-3 py-2 rounded-full transition-colors text-center whitespace-nowrap" %s>%s</a>',
                    esc_url( $product->add_to_cart_url() ),
                    esc_attr( isset( $args['quantity'] ) ? $args['quantity'] : 1 ),
                    $product->is_purchasable() && $product->is_in_stock() ? 'add_to_cart_button ajax_add_to_cart' : '',
                    isset( $args['attributes'] ) ? wc_implode_html_attributes( $args['attributes'] ) : '',
                    esc_html( $product->add_to_cart_text() )
                ),
            $product, $args );
            ?>
        </div>
    </div>
</li>
