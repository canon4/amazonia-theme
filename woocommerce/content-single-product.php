<?php
/**
 * The template for displaying product content in the single-product.php template
 */

defined( 'ABSPATH' ) || exit;

global $product;

/**
 * Hook: woocommerce_before_single_product.
 *
 * @hooked woocommerce_output_all_notices - 10
 */
do_action( 'woocommerce_before_single_product' );

if ( post_password_required() ) {
	echo get_the_password_form(); // WPCS: XSS ok.
	return;
}
?>

<main class="max-w-7xl mx-auto px-6 lg:px-20 py-10 tracking-normal antialiased">
    <div id="product-<?php the_ID(); ?>" <?php wc_product_class( '', $product ); ?>>

        <!-- Hero Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 mb-16">
            <?php do_action( 'woocommerce_before_single_product_summary' ); ?>

            <!-- Product Image -->
            <div class="relative group">
                <style>
                    /* Make sure the WooCommerce gallery matches the theme's aesthetic */
                    .product-gallery-wrapper .woocommerce-product-gallery {
                        opacity: 1 !important;
                        position: relative;
                        width: 100% !important;
                        max-width: none !important;
                        float: none !important;
                        margin: 0 !important;
                    }
                    .product-gallery-wrapper .woocommerce-product-gallery__wrapper {
                        margin: 0 !important;
                    }
                    .product-gallery-wrapper .woocommerce-product-gallery__image img {
                        width: 100% !important;
                        height: auto !important;
                        aspect-ratio: 1 / 1;
                        object-fit: cover;
                        border-radius: 0.75rem;
                    }
                    .product-gallery-wrapper .flex-control-thumbs {
                        display: flex !important;
                        flex-wrap: nowrap !important;
                        gap: 10px !important;
                        margin-top: 10px !important;
                        padding: 0 !important;
                        list-style: none !important;
                        overflow: visible !important;
                    }
                    .product-gallery-wrapper .flex-control-thumbs li {
                        float: none !important;
                        clear: none !important;
                        width: calc(25% - 7.5px) !important;
                        flex-shrink: 0 !important;
                        cursor: pointer !important;
                        margin: 0 !important;
                    }
                    .product-gallery-wrapper .flex-control-thumbs li img {
                        width: 100%;
                        height: auto;
                        aspect-ratio: 1 / 1;
                        object-fit: cover;
                        border-radius: 0.5rem;
                        opacity: 0.6;
                        transition: all 0.3s ease;
                    }
                    .product-gallery-wrapper .flex-control-thumbs li img:hover,
                    .product-gallery-wrapper .flex-control-thumbs li img.flex-active {
                        opacity: 1;
                        box-shadow: 0 0 0 2px #11d411;
                    }
                    /* Hide WooCommerce's built-in magnifier icon to keep it clean */
                    .product-gallery-wrapper .woocommerce-product-gallery__trigger {
                        position: absolute;
                        top: 1rem;
                        right: 4rem; /* keep away from favorite button */
                        z-index: 9;
                        background: white;
                        border-radius: 50%;
                        width: 2.5rem;
                        height: 2.5rem;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        text-indent: -9999px;
                        overflow: hidden;
                        box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
                    }
                    .product-gallery-wrapper .woocommerce-product-gallery__trigger::before {
                        content: "zoom_in";
                        font-family: "Material Symbols Outlined";
                        text-indent: 0;
                        color: #94a3b8;
                        font-size: 1.25rem;
                        position: absolute;
                    }
                </style>
                <div class="product-gallery-wrapper w-full">
                    <?php woocommerce_show_product_images(); ?>
                </div>

                <?php if ( $product->is_on_sale() ) : ?>
                    <div class="absolute top-4 left-4 bg-[#11d411] text-white px-4 py-1 rounded-full text-xs font-bold uppercase tracking-widest z-10">
                        Sale
                    </div>
                <?php endif; ?>

                <button class="amazonia-favorite-btn absolute top-4 right-4 h-10 w-10 bg-white/90 rounded-full flex items-center justify-center text-slate-400 hover:text-red-500 transition-colors z-10 shadow-lg" data-product-id="<?php echo esc_attr( $product->get_id() ); ?>">
                    <span class="material-symbols-outlined text-xl">favorite</span>
                </button>
            </div>

            <!-- Product Details -->
            <div class="flex flex-col justify-center">

                <?php
                if ( wc_review_ratings_enabled() ) {
                    $rating_count = $product->get_rating_count();
                    $review_count = $product->get_review_count();
                    $average      = $product->get_average_rating();

                    if ( $rating_count > 0 ) : ?>
                        <div class="mb-4 flex items-center gap-2">
                            <div class="flex text-[#11d411]">
                                <?php
                                    $rating = round($average);
                                    for ($i=0; $i<5; $i++) {
                                        echo '<span class="material-symbols-outlined text-sm">' . ($i < $rating ? 'star' : 'star_border') . '</span>';
                                    }
                                ?>
                            </div>
                            <span class="text-xs font-medium text-slate-400">(<?php echo esc_html( $review_count ); ?> Reviews)</span>
                        </div>
                    <?php endif;
                }
                ?>

                <h1 class="text-4xl lg:text-5xl font-black text-slate-900 dark:text-slate-100 mb-2 leading-tight">
                    <?php the_title(); ?>
                </h1>

                <div class="mb-2 product-price-wrapper">
                    <style>
                        .product-price-wrapper .price {
                            display: flex;
                            align-items: baseline;
                            gap: 0.5rem;
                            margin-bottom: 0;
                        }
                        .product-price-wrapper .price ins {
                            text-decoration: none;
                        }
                        .product-price-wrapper .price ins .amount,
                        .product-price-wrapper .price > .amount {
                            font-size: 1.5rem;
                            font-weight: 700;
                            color: #475569;
                        }
                        .product-price-wrapper .price del .amount {
                            color: #94a3b8;
                            text-decoration: line-through;
                            font-size: 1.25rem;
                        }
                    </style>
                    <?php echo $product->get_price_html(); ?>
                </div>

                <!-- Tags -->
                <?php
                $terms = get_the_terms( $product->get_id(), 'product_tag' );
                if ( $terms && ! is_wp_error( $terms ) ) :
                    $tag_links = array();
                    foreach ( $terms as $term ) {
                        $tag_links[] = '<a href="' . esc_url( get_term_link( $term->term_id, 'product_tag' ) ) . '" class="hover:underline">' . esc_html( $term->name ) . '</a>';
                    }
                ?>
                    <div class="mb-6 flex items-center gap-2 text-xs font-bold text-[#11d411] uppercase tracking-wider">
                        <span class="material-symbols-outlined text-sm">sell</span>
                        <?php echo join( ', ', $tag_links ); ?>
                    </div>
                <?php endif; ?>

                <div class="text-base text-slate-600 dark:text-slate-400 mb-8 leading-relaxed">
                    <?php echo apply_filters( 'woocommerce_short_description', $post->post_excerpt ); ?>
                </div>

                <div class="custom-add-to-cart-wrapper mb-4">
                    <?php woocommerce_template_single_add_to_cart(); ?>
                </div>

                <!-- WhatsApp contact button -->
                <div class="mb-8">
                    <a href="#" aria-label="Contactar al vendedor por WhatsApp"
                       class="inline-flex w-full items-center justify-center gap-2.5 rounded-xl border border-slate-200 bg-white px-7 py-3.5 font-['Outfit'] text-sm font-semibold tracking-wide !text-[#128C7E] !no-underline transition-all duration-200 hover:border-[#25D366] hover:bg-[#25D366] hover:!text-white active:scale-[0.98]">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" class="shrink-0">
                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                        </svg>
                        Contactar al vendedor
                    </a>
                </div>

                <!-- Vendido por -->
                <?php
                $amz_store_vendor_id = function_exists( 'wcfm_get_vendor_id_by_post' ) ? wcfm_get_vendor_id_by_post( $product->get_id() ) : 0;
                if ( $amz_store_vendor_id ) :
                    $amz_store_name = function_exists( 'wcfm_get_vendor_store_name' ) ? wcfm_get_vendor_store_name( $amz_store_vendor_id ) : '';
                    $amz_store_url  = function_exists( 'wcfmmp_get_store_url' ) ? wcfmmp_get_store_url( $amz_store_vendor_id ) : get_author_posts_url( $amz_store_vendor_id );
                    $amz_store_logo = function_exists( 'wcfm_get_vendor_store_logo_by_vendor' ) ? wcfm_get_vendor_store_logo_by_vendor( $amz_store_vendor_id ) : '';
                    if ( ! $amz_store_name ) {
                        $amz_store_user = get_userdata( $amz_store_vendor_id );
                        $amz_store_name = $amz_store_user ? $amz_store_user->display_name : '';
                    }
                ?>
                <?php
                // material-symbols.css se carga después de tailwind.css y fija font-size: 24px
                // con la misma especificidad, así que los iconos necesitan `!text-[..]`.
                $amz_store_card_class = 'group flex items-center gap-4 rounded-2xl border border-slate-200 bg-white px-5 py-4 !no-underline transition-all duration-200 hover:-translate-y-0.5 hover:border-primary/40 hover:shadow-[0_12px_28px_-14px_rgba(0,107,44,0.25)] dark:border-slate-700 dark:bg-slate-900/40';
                $amz_store_ring_class = 'h-11 w-11 shrink-0 rounded-full ring-1 ring-slate-200 transition-all duration-200 group-hover:ring-primary/50 dark:ring-slate-700';
                ?>
                <div class="mb-6">
                    <?php if ( $amz_store_url ) : ?>
                    <a href="<?php echo esc_url( $amz_store_url ); ?>" class="<?php echo esc_attr( $amz_store_card_class ); ?>">
                    <?php else : ?>
                    <div class="<?php echo esc_attr( $amz_store_card_class ); ?>">
                    <?php endif; ?>

                        <?php if ( $amz_store_logo ) : ?>
                            <img src="<?php echo esc_url( $amz_store_logo ); ?>"
                                 alt="<?php echo esc_attr( $amz_store_name ); ?>"
                                 class="<?php echo esc_attr( $amz_store_ring_class ); ?> object-cover" />
                        <?php else : ?>
                            <span class="<?php echo esc_attr( $amz_store_ring_class ); ?> flex items-center justify-center bg-primary/10 text-primary">
                                <span class="material-symbols-outlined !text-[20px]" aria-hidden="true">storefront</span>
                            </span>
                        <?php endif; ?>

                        <div class="min-w-0 flex-1">
                            <p class="mb-1 text-[0.7rem] font-semibold uppercase leading-none tracking-[0.14em] text-slate-400">Vendido por</p>
                            <p class="truncate font-['Outfit'] text-base font-semibold leading-snug text-slate-800 transition-colors duration-200 group-hover:text-primary dark:text-slate-100">
                                <?php echo esc_html( $amz_store_name ); ?>
                            </p>
                        </div>

                        <?php if ( $amz_store_url ) : ?>
                        <span class="material-symbols-outlined !text-[18px] shrink-0 text-slate-300 transition-all duration-200 group-hover:translate-x-1 group-hover:text-primary dark:text-slate-600" aria-hidden="true">arrow_forward</span>
                        <?php endif; ?>

                    <?php echo $amz_store_url ? '</a>' : '</div>'; ?>
                </div>
                <?php endif; ?>

                <?php
                $attributes = $product->get_attributes();
                if ( ! empty( $attributes ) ) : ?>
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <?php
                        $attr_count = 0;
                        foreach ( $attributes as $attribute ) :
                            if ( ! $attribute->get_visible() ) continue;
                            if ( $attr_count >= 2 ) break; // Limit

                            $name = wc_attribute_label( $attribute->get_name() );
                            $options = $attribute->get_options();

                            if ( $attribute->is_taxonomy() ) {
                                $value = wc_get_product_terms( $product->get_id(), $attribute->get_name(), array( 'fields' => 'names' ) );
                                $value = implode(', ', $value);
                            } else {
                                $value = implode(', ', $options);
                            }
                            ?>
                            <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-800/50 flex items-center gap-3">
                                <div class="text-[#11d411] hidden md:block">
                                    <span class="material-symbols-outlined"><?php echo $attr_count === 0 ? 'public' : 'handyman'; ?></span>
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-sm font-bold text-slate-900 dark:text-white"><?php echo esc_html( $name ); ?></span>
                                    <span class="text-xs text-slate-500 dark:text-slate-400 capitalize"><?php echo esc_html( $value ); ?></span>
                                </div>
                            </div>
                            <?php
                            $attr_count++;
                        endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php
                
                global $wp_filter;
                if ( isset( $wp_filter['woocommerce_single_product_summary'] ) ) {
                    foreach ( $wp_filter['woocommerce_single_product_summary']->callbacks as $priority => $callbacks ) {
                        foreach ( $callbacks as $key => $callback ) {
                            if ( is_array( $callback['function'] )
                                && is_object( $callback['function'][0] )
                                && 'wcfm_enquiry_button' === $callback['function'][1] ) {
                                unset( $wp_filter['woocommerce_single_product_summary']->callbacks[ $priority ][ $key ] );
                            }
                        }
                    }
                }
                do_action( 'woocommerce_single_product_summary' );
                ?>
            </div>
        </div>

        <?php
        // Imágenes de storytelling — se configuran en el panel de la comunidad
        $vendor_id      = function_exists( 'wcfm_get_vendor_id_by_post' ) ? wcfm_get_vendor_id_by_post( $product->get_id() ) : 0;
        $community_data = null;

        if ( $vendor_id ) {
            $community_id = get_user_meta( $vendor_id, 'community_id', true );
            if ( $community_id && function_exists( 'amazonia_get_community_data' ) ) {
                $community_data = amazonia_get_community_data( (int) $community_id );
            }
        }

        $gallery_img_1 = $community_data['storytelling_img_1'] ?? ( $community_data['banner'] ?? '' );
        $gallery_img_2 = $community_data['storytelling_img_2'] ?? '';
        $gallery_img_3 = $community_data['storytelling_img_3'] ?? '';

        // Ocultar la pestaña "Descripción" de WooCommerce para no duplicar
        add_filter( 'woocommerce_product_tabs', function( $tabs ) {
            unset( $tabs['description'] );
            return $tabs;
        } );
        ?>

        <?php $long_description = $product->get_description(); ?>
        <?php if ( $long_description ) : ?>
        <section class="max-w-[900px] mx-auto py-14 px-6 overflow-hidden">
            <div data-amz-fade class="flex flex-col items-center text-center mb-10 opacity-0 translate-y-8 transition-all duration-700 ease-out">
                <span class="mb-4 inline-flex items-center gap-2 font-display text-[0.7rem] font-bold uppercase tracking-[0.14em] text-primary before:h-0.5 before:w-7 before:rounded-sm before:bg-gradient-to-r before:from-primary before:to-green-400 before:content-['']">Descripción</span>
                <h2 class="text-3xl lg:text-[2.5rem] font-bold text-slate-900 leading-tight tracking-tight font-['Outfit']">Sobre Nuestro Producto</h2>
            </div>
            <div data-amz-fade class="rounded-2xl border border-slate-200/70 bg-white p-8 md:p-11 shadow-sm hover:shadow-[0_24px_48px_-18px_rgba(0,107,44,0.18)] opacity-0 translate-y-8 transition-all duration-500 ease-out delay-100">
                <div class="amz-wp-content text-base lg:text-[1.0625rem] text-slate-600 leading-[1.85] font-['Inter']"><?php echo wp_kses_post( $long_description ); ?></div>
            </div>
        </section>
        <?php endif; ?>

        <?php if ( $community_data ) :
            $community_img = $community_data['logo'] ?: ( $gallery_img_1 ?: $community_data['banner'] );
        ?>
        <section class="max-w-[1200px] mx-auto py-14 px-6 overflow-hidden">

            <!-- Section Header -->
            <div data-amz-fade class="flex flex-col items-center text-center mb-12 opacity-0 translate-y-8 transition-all duration-700 ease-out">
                <span class="mb-4 inline-flex items-center gap-2 font-display text-[0.7rem] font-bold uppercase tracking-[0.14em] text-primary before:h-0.5 before:w-7 before:rounded-sm before:bg-gradient-to-r before:from-primary before:to-green-400 before:content-['']">Origen &amp; Propósito</span>
                <h2 class="text-3xl lg:text-[2.5rem] font-bold text-slate-900 mb-3 max-w-2xl leading-tight tracking-tight font-['Outfit']">Cada producto tiene una historia</h2>
                <p class="text-base text-slate-500 font-['Inter']">
                    Elaborado por <span class="font-semibold text-[#006b2c]"><?php echo esc_html( $community_data['nombre'] ); ?></span>
                </p>
            </div>

            <div class="flex flex-col gap-10">

                <!-- Card 1: La Comunidad — texto izquierda, imagen derecha -->
                <div data-amz-fade class="group flex flex-col md:flex-row items-stretch overflow-hidden rounded-[24px] border border-slate-100 bg-white shadow-sm opacity-0 translate-y-8 transition-all duration-500 ease-out delay-150 hover:-translate-y-1 hover:shadow-[0_24px_48px_-18px_rgba(0,107,44,0.18)]">
                    <div class="w-full md:w-3/4 p-8 md:p-12 flex flex-col justify-center order-2 md:order-1">
                        <?php if ( $community_data['categoria'] ) : ?>
                        <span class="mb-3 inline-flex w-fit items-center rounded-full bg-[rgba(127,252,151,0.3)] px-3 py-1 text-xs font-semibold uppercase tracking-wider text-[#005320] font-display">
                            <?php echo esc_html( $community_data['categoria'] ); ?>
                        </span>
                        <?php endif; ?>
                        <h3 class="text-2xl lg:text-3xl font-bold text-slate-900 mb-3 leading-tight font-['Outfit']"><?php echo esc_html( $community_data['nombre'] ); ?></h3>
                        <?php if ( $community_data['historia'] ) : ?>
                        <p class="text-base lg:text-lg text-slate-600 mb-6 leading-relaxed font-['Inter']">
                            <?php echo esc_html( wp_trim_words( $community_data['historia'], 40, '…' ) ); ?>
                        </p>
                        <?php endif; ?>
                        <a class="flex w-fit items-center gap-2 text-sm font-semibold text-[#006b2c] font-display transition-transform duration-300 hover:translate-x-2" href="<?php echo esc_url( $community_data['url'] ); ?>">
                            Conocer la comunidad
                            <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
                        </a>
                    </div>
                    <div class="w-full md:w-1/4 h-[300px] md:h-auto overflow-hidden order-1 md:order-2">
                        <?php $card1_img = $gallery_img_1 ?: $community_img; ?>
                        <?php if ( $card1_img ) : ?>
                        <img alt="<?php echo esc_attr( $community_data['nombre'] ); ?>" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105" src="<?php echo esc_url( $card1_img ); ?>" loading="lazy" />
                        <?php else : ?>
                        <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-[#f0fdf4] to-[#dcfce7]">
                            <span class="material-symbols-outlined text-[#86efac] text-[72px]">groups</span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <?php
                $valores = $community_data['valores'];
                $bullets = array_slice( $valores, 0, 3 );
                ?>

                <!-- Card 2: Tradición & Cultura — imagen izquierda, texto derecha -->
                <?php if ( ! empty( $bullets ) ) : ?>
                <div data-amz-fade class="group flex flex-col md:flex-row items-stretch overflow-hidden rounded-[24px] border border-slate-100 bg-white shadow-sm opacity-0 translate-y-8 transition-all duration-500 ease-out delay-300 hover:-translate-y-1 hover:shadow-[0_24px_48px_-18px_rgba(0,107,44,0.18)]">
                    <div class="w-full md:w-1/2 h-[300px] md:h-auto overflow-hidden">
                        <?php if ( $gallery_img_2 ) : ?>
                        <img alt="Tradición artesanal" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105" src="<?php echo esc_url( $gallery_img_2 ); ?>" loading="lazy" />
                        <?php else : ?>
                        <div class="w-full h-full bg-slate-100 min-h-[300px]"></div>
                        <?php endif; ?>
                    </div>
                    <div class="w-full md:w-1/2 p-8 md:p-12 flex flex-col justify-center">
                        <span class="mb-3 inline-flex w-fit items-center rounded-full bg-[rgba(195,238,184,0.4)] px-3 py-1 text-xs font-semibold uppercase tracking-wider text-[#2b4f27] font-display">
                            TRADICIÓN &amp; CULTURA
                        </span>
                        <h3 class="text-2xl lg:text-3xl font-bold text-slate-900 mb-4 leading-tight font-['Outfit']">Saberes que se tejen de generación en generación</h3>
                        <ul class="space-y-3">
                            <?php foreach ( $bullets as $valor ) : ?>
                            <li class="flex items-center gap-3 text-base text-slate-600 font-['Inter']">
                                <?php if ( ! empty( $valor['icono'] ) ) : ?>
                                <span class="material-symbols-outlined text-[#006d36] shrink-0 text-[18px]"><?php echo esc_html( $valor['icono'] ); ?></span>
                                <?php else : ?>
                                <span class="w-2 h-2 rounded-full bg-[#006d36] shrink-0 inline-block"></span>
                                <?php endif; ?>
                                <?php echo esc_html( $valor['texto'] ); ?>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Card 3: Valores de la Comunidad — texto izquierda, imagen derecha -->
                <?php if ( ! empty( $valores ) ) : ?>
                <div data-amz-fade class="group flex flex-col md:flex-row items-stretch overflow-hidden rounded-[24px] border border-slate-100 bg-white shadow-sm opacity-0 translate-y-8 transition-all duration-500 ease-out delay-[450ms] hover:-translate-y-1 hover:shadow-[0_24px_48px_-18px_rgba(0,107,44,0.18)]">
                    <div class="w-full md:w-1/2 p-8 md:p-12 flex flex-col justify-center order-2 md:order-1">
                        <span class="mb-3 inline-flex w-fit items-center rounded-full bg-[#00873a] px-3 py-1 text-xs font-semibold uppercase tracking-wider text-[#f7fff2] font-display">
                            VALORES
                        </span>
                        <h3 class="text-2xl lg:text-3xl font-bold text-slate-900 mb-6 leading-tight font-['Outfit']">Los principios que guían cada pieza</h3>
                        <div class="grid grid-cols-2 gap-4">
                            <?php foreach ( $valores as $valor ) : ?>
                            <div class="flex items-start gap-3">
                                <?php if ( ! empty( $valor['icono'] ) ) : ?>
                                <span class="material-symbols-outlined text-[#006b2c] shrink-0 mt-0.5 text-[24px]"><?php echo esc_html( $valor['icono'] ); ?></span>
                                <?php endif; ?>
                                <span class="text-base text-slate-600 font-['Inter']"><?php echo esc_html( $valor['texto'] ); ?></span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="w-full md:w-1/2 h-[300px] md:h-auto overflow-hidden order-1 md:order-2">
                        <?php if ( $gallery_img_3 ) : ?>
                        <img alt="Valores artesanales" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105" src="<?php echo esc_url( $gallery_img_3 ); ?>" loading="lazy" />
                        <?php else : ?>
                        <div class="w-full h-full bg-slate-100 min-h-[300px]"></div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>

            </div>
        </section>
        <?php endif; ?>

        <?php
        // ── Productos relacionados ──────────────────────────────────
        $related_ids = wc_get_related_products( $product->get_id(), 4 );
        if ( ! empty( $related_ids ) ) :
            $main_product = $product; // preservar el producto principal
        ?>
        <section class="max-w-[1200px] mx-auto py-14 border-t border-slate-100">
            <div data-amz-fade class="flex flex-col items-center text-center mb-8 px-6 opacity-0 translate-y-8 transition-all duration-700 ease-out">
                <span class="mb-3 inline-flex items-center gap-2 font-display text-[0.7rem] font-bold uppercase tracking-[0.14em] text-primary before:h-0.5 before:w-7 before:rounded-sm before:bg-gradient-to-r before:from-primary before:to-green-400 before:content-['']">También te puede gustar</span>
                <h2 class="text-2xl lg:text-[2rem] font-bold text-slate-900 leading-tight tracking-tight font-['Outfit']">Productos relacionados</h2>
            </div>

            <!-- Mobile: scroll horizontal snap | Desktop: grid 4 columnas -->
            <ul data-amz-fade class="amz-related-list products list-none !p-0 !m-0 flex gap-4 overflow-x-auto px-6 pb-4 snap-x snap-mandatory scroll-smooth [scrollbar-width:none] [&::-webkit-scrollbar]:hidden lg:grid lg:grid-cols-4 lg:gap-6 lg:overflow-visible lg:px-6 lg:pb-0 opacity-0 translate-y-8 transition-all duration-700 ease-out delay-100">
                <?php
                global $post;
                foreach ( $related_ids as $related_id ) {
                    $post    = get_post( $related_id );
                    $product = wc_get_product( $related_id );
                    setup_postdata( $post );
                    wc_get_template_part( 'content', 'product' );
                }
                wp_reset_postdata();
                $product = $main_product;
                ?>
            </ul>
        </section>
        <?php endif; ?>

        <?php
        // ── Más de esta tienda ──────────────────────────────────────
        $store_product_ids = array();
        if ( $vendor_id ) {
            $store_products_query = new WP_Query( array(
                'post_type'      => 'product',
                'post_status'    => 'publish',
                'posts_per_page' => 5,
                'author'         => $vendor_id,
                'post__not_in'   => array( $product->get_id() ),
                'orderby'        => 'rand',
                'fields'         => 'ids',
            ) );
            $store_product_ids = $store_products_query->posts;
        }

        if ( ! empty( $store_product_ids ) ) :
            $main_product = $product; // preservar el producto principal
        ?>
        <section class="max-w-[1200px] mx-auto py-14 border-t border-slate-100">
            <div data-amz-fade class="flex flex-col items-center text-center mb-8 px-6 opacity-0 translate-y-8 transition-all duration-700 ease-out">
                <span class="mb-3 inline-flex items-center gap-2 font-display text-[0.7rem] font-bold uppercase tracking-[0.14em] text-primary before:h-0.5 before:w-7 before:rounded-sm before:bg-gradient-to-r before:from-primary before:to-green-400 before:content-['']">Misma tienda</span>
                <h2 class="text-2xl lg:text-[2rem] font-bold text-slate-900 leading-tight tracking-tight font-['Outfit']">Más de esta tienda</h2>
            </div>

            <!-- Carrusel horizontal con cards de ancho fijo (móvil y escritorio) -->
            <ul data-amz-fade class="amz-store-list products list-none !p-0 !m-0 flex gap-4 overflow-x-auto px-6 pb-4 snap-x snap-mandatory scroll-smooth [scrollbar-width:none] [&::-webkit-scrollbar]:hidden lg:gap-6 opacity-0 translate-y-8 transition-all duration-700 ease-out delay-100">
                <?php
                global $post;
                foreach ( $store_product_ids as $store_product_id ) {
                    $post    = get_post( $store_product_id );
                    $product = wc_get_product( $store_product_id );
                    setup_postdata( $post );
                    wc_get_template_part( 'content', 'product' );
                }
                wp_reset_postdata();
                $product = $main_product;
                ?>
            </ul>
        </section>
        <?php endif; ?>

        <?php
        remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20 );
        remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_upsell_display', 15 );
        // Re-add tabs (description tab already removed above via filter; only Reviews will show).
        add_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_product_data_tabs', 10 );
        do_action( 'woocommerce_after_single_product_summary' );
        ?>

    </div>
</main>

<script>
(function () {
    document.addEventListener('DOMContentLoaded', function () {
        var observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.remove('opacity-0', 'translate-y-8');
                    entry.target.classList.add('opacity-100', 'translate-y-0');
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.1, rootMargin: '0px 0px -50px 0px' });
        document.querySelectorAll('[data-amz-fade]').forEach(function (el) {
            observer.observe(el);
        });
    });
})();
</script>

<?php do_action( 'woocommerce_after_single_product' ); ?>
