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
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 mb-24">
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

                <div class="custom-add-to-cart-wrapper mb-8">
                    <style>
                        .custom-add-to-cart-wrapper form.cart {
                            display: flex;
                            flex-wrap: wrap;
                            gap: 1rem;
                            align-items: center;
                        }
                        .custom-add-to-cart-wrapper .quantity {
                            display: flex;
                            align-items: center;
                        }
                        .custom-add-to-cart-wrapper .quantity input {
                            border-radius: 0.5rem;
                            border: 2px solid #e2e8f0;
                            padding: 0.85rem 1rem;
                            width: 80px;
                            text-align: center;
                            background: transparent;
                        }
                        .custom-add-to-cart-wrapper button.single_add_to_cart_button {
                            flex: 1;
                            min-width: 200px;
                            background-color: #11d411;
                            color: #000000;
                            font-weight: 700;
                            padding: 1rem 2rem;
                            border-radius: 0.5rem;
                            box-shadow: 0 4px 6px -1px rgba(17, 212, 17, 0.2);
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            gap: 0.5rem;
                            transition: all 0.3s;
                            cursor: pointer;
                            border: none;
                        }
                        .custom-add-to-cart-wrapper button.single_add_to_cart_button:hover {
                            background-color: #0ea60e;
                        }
                        .custom-add-to-cart-wrapper button.single_add_to_cart_button::before {
                            content: 'local_mall';
                            font-family: 'Material Symbols Outlined';
                            font-size: 20px;
                        }
                    </style>
                    <?php woocommerce_template_single_add_to_cart(); ?>
                </div>

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

                <?php do_action( 'woocommerce_single_product_summary' ); ?>
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

        <!-- Estilos compartidos: descripción + storytelling -->
        <style>
            .amz-glass-card {
                background: rgba(244, 252, 240, 0.6);
                backdrop-filter: blur(8px);
                -webkit-backdrop-filter: blur(8px);
            }
            .amz-gradient-border-left {
                border-left-width: 3px;
                border-image: linear-gradient(to bottom, #006b2c, #7ffc97) 1;
            }
            .amz-glow-hover:hover {
                box-shadow: 0 0 30px rgba(0, 107, 44, 0.15);
            }
            .amz-fade-up {
                opacity: 0;
                transform: translateY(30px);
                transition: all 0.8s cubic-bezier(0.22, 1, 0.36, 1);
            }
            .amz-fade-up.visible {
                opacity: 1;
                transform: translateY(0);
            }
            /* Restaurar estilos de contenido del editor WordPress */
            .amz-wp-content p { margin-bottom: 1em; }
            .amz-wp-content img.alignleft,
            .amz-wp-content .alignleft  { float: left;  margin: 0 1.5em 1em 0; display: inline; }
            .amz-wp-content img.alignright,
            .amz-wp-content .alignright { float: right; margin: 0 0 1em 1.5em; display: inline; }
            .amz-wp-content img.aligncenter,
            .amz-wp-content .aligncenter { display: block; margin-left: auto; margin-right: auto; margin-bottom: 1em; }
            .amz-wp-content img.alignnone { margin-bottom: 1em; }
            .amz-wp-content .wp-caption { max-width: 100%; }
            .amz-wp-content .wp-caption-text { font-size: 0.875em; text-align: center; color: #64748b; margin-top: 0.25em; }
            .amz-wp-content::after { content: ''; display: table; clear: both; }
            .amz-wp-content ul { list-style: disc; padding-left: 1.5em; margin-bottom: 1em; }
            .amz-wp-content ol { list-style: decimal; padding-left: 1.5em; margin-bottom: 1em; }
            .amz-wp-content li { margin-bottom: 0.25em; }
            .amz-wp-content h2,
            .amz-wp-content h3,
            .amz-wp-content h4 { font-weight: bold; margin-bottom: 0.5em; margin-top: 1em; }
            .amz-wp-content blockquote { border-left: 3px solid #006b2c; padding-left: 1em; margin: 1em 0; font-style: italic; }
            .amz-wp-content a { color: #006b2c; text-decoration: underline; }
        </style>

        <?php $long_description = $product->get_description(); ?>
        <?php if ( $long_description ) : ?>
        <section class="max-w-[1200px] mx-auto py-16 px-4 overflow-hidden">
            <div class="flex flex-col items-start mb-10 amz-fade-up" style="transition-delay: 0s;">
                <span class="text-xs font-semibold text-[#006b2c] tracking-widest uppercase mb-3" style="font-family:'Work Sans',sans-serif; letter-spacing:0.05em;">DESCRIPCIÓN</span>
                <h2 class="text-3xl lg:text-4xl font-bold text-slate-900 mb-3 leading-tight" style="font-family:'Outfit',sans-serif;">Sobre Nuestro Producto</h2>
                <div class="h-1 w-24 rounded-full" style="background:linear-gradient(to right,#006b2c,#7ffc97);"></div>
            </div>
            <div class="amz-fade-up amz-glass-card border border-slate-100 rounded-[24px] overflow-hidden amz-gradient-border-left amz-glow-hover p-8 md:p-12" style="transition-delay:0.1s;">
                <div class="amz-wp-content text-base lg:text-lg text-slate-600 leading-relaxed"
                     style="font-family:'Inter',sans-serif;"><?php echo wp_kses_post( $long_description ); ?></div>
            </div>
        </section>
        <?php endif; ?>

        <?php if ( $community_data ) : ?>
        <section class="max-w-[1200px] mx-auto py-20 overflow-hidden">

            <!-- Section Header -->
            <div class="flex flex-col items-start mb-12 amz-fade-up" style="transition-delay: 0s;">
                <span class="text-xs font-semibold text-[#006b2c] tracking-widest uppercase mb-3" style="font-family: 'Work Sans', sans-serif; letter-spacing: 0.05em;">ORIGEN &amp; PROPÓSITO</span>
                <h2 class="text-3xl lg:text-4xl font-bold text-slate-900 mb-3 max-w-2xl leading-tight" style="font-family: 'Outfit', sans-serif;">Cada producto tiene una historia</h2>
                <div class="h-1 w-24 rounded-full" style="background: linear-gradient(to right, #006b2c, #7ffc97);"></div>
            </div>

            <div class="flex flex-col gap-12">

                <!-- Card 1: La Comunidad — texto izquierda, imagen derecha -->
                <div class="amz-fade-up group flex flex-col md:flex-row items-stretch amz-glass-card border border-slate-100 rounded-[24px] overflow-hidden amz-gradient-border-left hover:-translate-y-1 transition-all duration-500 amz-glow-hover" style="transition-delay: 0.15s;">
                    <div class="w-full md:w-1/2 p-8 md:p-12 flex flex-col justify-center order-2 md:order-1">
                        <?php if ( $community_data['categoria'] ) : ?>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold w-fit mb-3 uppercase tracking-wider" style="background: rgba(127,252,151,0.3); color: #005320; font-family: 'Work Sans', sans-serif;">
                            <?php echo esc_html( $community_data['categoria'] ); ?>
                        </span>
                        <?php endif; ?>
                        <h3 class="text-2xl lg:text-3xl font-bold text-slate-900 mb-3 leading-tight" style="font-family: 'Outfit', sans-serif;"><?php echo esc_html( $community_data['nombre'] ); ?></h3>
                        <?php if ( $community_data['historia'] ) : ?>
                        <p class="text-base lg:text-lg text-slate-600 mb-6 leading-relaxed" style="font-family: 'Inter', sans-serif;">
                            <?php echo esc_html( wp_trim_words( $community_data['historia'], 40, '…' ) ); ?>
                        </p>
                        <?php endif; ?>
                        <a class="text-sm font-semibold text-[#006b2c] flex items-center gap-2 hover:translate-x-2 transition-transform duration-300 w-fit" style="font-family: 'Work Sans', sans-serif;" href="<?php echo esc_url( $community_data['url'] ); ?>">
                            Conocer la comunidad
                            <span class="material-symbols-outlined" style="font-size: 18px;">arrow_forward</span>
                        </a>
                    </div>
                    <div class="w-full md:w-1/2 h-[300px] md:h-auto overflow-hidden order-1 md:order-2">
                        <?php if ( $gallery_img_1 ) : ?>
                        <img alt="<?php echo esc_attr( $community_data['nombre'] ); ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700" src="<?php echo esc_url( $gallery_img_1 ); ?>" loading="lazy" />
                        <?php else : ?>
                        <div class="w-full h-full bg-slate-100 min-h-[300px]"></div>
                        <?php endif; ?>
                    </div>
                </div>

                <?php
                $valores = $community_data['valores'];
                $bullets = array_slice( $valores, 0, 3 );
                ?>

                <!-- Card 2: Tradición & Cultura — imagen izquierda, texto derecha -->
                <?php if ( ! empty( $bullets ) ) : ?>
                <div class="amz-fade-up group flex flex-col md:flex-row items-stretch amz-glass-card border border-slate-100 rounded-[24px] overflow-hidden amz-gradient-border-left hover:-translate-y-1 transition-all duration-500 amz-glow-hover" style="transition-delay: 0.3s;">
                    <div class="w-full md:w-1/2 h-[300px] md:h-auto overflow-hidden">
                        <?php if ( $gallery_img_2 ) : ?>
                        <img alt="Tradición artesanal" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700" src="<?php echo esc_url( $gallery_img_2 ); ?>" loading="lazy" />
                        <?php else : ?>
                        <div class="w-full h-full bg-slate-100 min-h-[300px]"></div>
                        <?php endif; ?>
                    </div>
                    <div class="w-full md:w-1/2 p-8 md:p-12 flex flex-col justify-center">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold w-fit mb-3 uppercase tracking-wider" style="background: rgba(195,238,184,0.4); color: #2b4f27; font-family: 'Work Sans', sans-serif;">
                            TRADICIÓN &amp; CULTURA
                        </span>
                        <h3 class="text-2xl lg:text-3xl font-bold text-slate-900 mb-4 leading-tight" style="font-family: 'Outfit', sans-serif;">Saberes que se tejen de generación en generación</h3>
                        <ul class="space-y-3">
                            <?php foreach ( $bullets as $valor ) : ?>
                            <li class="flex items-center gap-3 text-base text-slate-600" style="font-family: 'Inter', sans-serif;">
                                <?php if ( ! empty( $valor['icono'] ) ) : ?>
                                <span class="material-symbols-outlined text-[#006d36] shrink-0" style="font-size: 18px;"><?php echo esc_html( $valor['icono'] ); ?></span>
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
                <div class="amz-fade-up group flex flex-col md:flex-row items-stretch amz-glass-card border border-slate-100 rounded-[24px] overflow-hidden amz-gradient-border-left hover:-translate-y-1 transition-all duration-500 amz-glow-hover" style="transition-delay: 0.45s;">
                    <div class="w-full md:w-1/2 p-8 md:p-12 flex flex-col justify-center order-2 md:order-1">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold w-fit mb-3 uppercase tracking-wider" style="background: #00873a; color: #f7fff2; font-family: 'Work Sans', sans-serif;">
                            VALORES
                        </span>
                        <h3 class="text-2xl lg:text-3xl font-bold text-slate-900 mb-6 leading-tight" style="font-family: 'Outfit', sans-serif;">Los principios que guían cada pieza</h3>
                        <div class="grid grid-cols-2 gap-4">
                            <?php foreach ( $valores as $valor ) : ?>
                            <div class="flex items-start gap-3">
                                <?php if ( ! empty( $valor['icono'] ) ) : ?>
                                <span class="material-symbols-outlined text-[#006b2c] shrink-0 mt-0.5" style="font-size: 24px;"><?php echo esc_html( $valor['icono'] ); ?></span>
                                <?php endif; ?>
                                <span class="text-base text-slate-600" style="font-family: 'Inter', sans-serif;"><?php echo esc_html( $valor['texto'] ); ?></span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="w-full md:w-1/2 h-[300px] md:h-auto overflow-hidden order-1 md:order-2">
                        <?php if ( $gallery_img_3 ) : ?>
                        <img alt="Valores artesanales" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700" src="<?php echo esc_url( $gallery_img_3 ); ?>" loading="lazy" />
                        <?php else : ?>
                        <div class="w-full h-full bg-slate-100 min-h-[300px]"></div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>

            </div>
        </section>

        <script>
        (function() {
            document.addEventListener('DOMContentLoaded', function() {
                var observer = new IntersectionObserver(function(entries) {
                    entries.forEach(function(entry) {
                        if (entry.isIntersecting) {
                            entry.target.classList.add('visible');
                        }
                    });
                }, { threshold: 0.1, rootMargin: '0px 0px -50px 0px' });
                document.querySelectorAll('.amz-fade-up').forEach(function(el) {
                    observer.observe(el);
                });
            });
        })();
        </script>
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

<?php do_action( 'woocommerce_after_single_product' ); ?>
