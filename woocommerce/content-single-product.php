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
                        display: flex;
                        gap: 0.5rem;
                        margin-top: 1rem;
                        padding: 0;
                        list-style: none;
                        overflow-x: auto;
                    }
                    .product-gallery-wrapper .flex-control-thumbs li {
                        width: calc(25% - 0.375rem);
                        flex-shrink: 0;
                        cursor: pointer;
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

        <!-- Storytelling Sections (Content) -->
        <div class="py-16 overflow-hidden flex flex-col items-center max-w-4xl mx-auto space-y-24">

            <?php
            // Setup Images
            $attachment_ids = $product->get_gallery_image_ids();
            $gallery_img_1 = isset($attachment_ids[0]) ? wp_get_attachment_image_src($attachment_ids[0], 'full')[0] : '';
            $gallery_img_2 = isset($attachment_ids[1]) ? wp_get_attachment_image_src($attachment_ids[1], 'full')[0] : '';
            
            // Text Content
            $content = get_the_content();
            if (empty($content)) {
                $content = "Esta es una expresión cultural de las comunidades artesanales. Está elaborado respetando los ciclos naturales y la biodiversidad del territorio. Cada pieza tiene un significado profundo ligado a sus raíces.";
            }

            // Vendor Info
            $vendor_id = function_exists( 'wcfm_get_vendor_id_by_post' ) ? wcfm_get_vendor_id_by_post( $product->get_id() ) : 0;
            $vendor_name = 'Comunidad Local';
            $vendor_logo = wc_placeholder_img_src();
            $vendor_banner = '';
            $shop_desc = 'Artesanos y Productores Locales';
            $location_str = 'Amazonas, Colombia';
            $map_query = 'Amazonas, Colombia';
            $store_url = '#';

            if ( $vendor_id ) {
                $vendor_name = wcfm_get_vendor_store_name( $vendor_id );
                $vendor_logo = wcfm_get_vendor_store_logo_by_vendor( $vendor_id );
                if ( ! $vendor_logo ) $vendor_logo = wc_placeholder_img_src();
                
                $vendor_banner_id = get_user_meta( $vendor_id, 'wcfmmp_banner', true );
                if ( $vendor_banner_id ) {
                    $vendor_banner_src = wp_get_attachment_image_src( $vendor_banner_id, 'full' );
                    $vendor_banner = $vendor_banner_src ? $vendor_banner_src[0] : '';
                }

                $store_info = get_user_meta( $vendor_id, 'wcfmmp_profile_settings', true );
                $vendor_address = isset( $store_info['address'] ) ? $store_info['address'] : array();
                
                $location = array_filter(array(
                    isset($vendor_address['city']) ? $vendor_address['city'] : '',
                    isset($vendor_address['country']) ? $vendor_address['country'] : ''
                ));
                $location_str = !empty($location) ? implode(', ', $location) : 'Amazonas';
                
                $full_address_parts = array_filter(array(
                    isset($vendor_address['street_1']) ? $vendor_address['street_1'] : '',
                    isset($vendor_address['city']) ? $vendor_address['city'] : '',
                    isset($vendor_address['state']) ? $vendor_address['state'] : '',
                    isset($vendor_address['country']) ? $vendor_address['country'] : ''
                ));
                if ( !empty($full_address_parts) ) {
                    $map_query = implode(', ', $full_address_parts);
                } elseif ( !empty($location_str) ) {
                    $map_query = $location_str;
                }

                $shop_desc = isset($store_info['shop_description']) ? wp_strip_all_tags( $store_info['shop_description'] ) : '';
                $store_url = function_exists('wcfmmp_get_store_url') ? wcfmmp_get_store_url($vendor_id) : get_author_posts_url($vendor_id);
            }
            
            // Fallbacks for missing images so the design doesn't break
            if ( ! $gallery_img_1 ) $gallery_img_1 = 'https://images.unsplash.com/photo-1518531933037-91b2f5f229cc?q=80&w=1200&auto=format&fit=crop'; // Cacao/Nature placeholder
            if ( ! $vendor_banner ) $vendor_banner = 'https://images.unsplash.com/photo-1511556820780-d912e42b4980?q=80&w=1200&auto=format&fit=crop'; // Maker placeholder
            if ( ! $gallery_img_2 ) $gallery_img_2 = 'https://images.unsplash.com/photo-1516026672322-bc52d61a55d5?q=80&w=800&auto=format&fit=crop'; // Land placeholder
            ?>

            <!-- Top Intro Image -->
            <div class="w-full rounded-[2rem] overflow-hidden aspect-[2/1] shadow-2xl relative">
                <img src="<?php echo esc_url($gallery_img_1); ?>" alt="Origin Image" class="w-full h-full object-cover" loading="lazy" width="1200" height="600">
                <!-- Fallback gradient to make it look nicer if it's a generic image -->
                <div class="absolute inset-0 bg-gradient-to-t from-black/30 to-transparent"></div>
            </div>

            <!-- The Origin -->
            <div class="w-full flex flex-col items-center">
                <h2 class="text-2xl lg:text-3xl font-bold text-slate-800 dark:text-white mb-8 self-start ml-0 md:ml-8" style="color: #1e3a1e;">The Origin</h2>
                
                <div class="relative w-full">
                    <!-- Background Image -->
                    <div class="w-[90%] md:w-[75%] h-64 md:h-80 rounded-[2rem] overflow-hidden shadow-xl ml-auto relative">
                        <img src="<?php echo esc_url($gallery_img_1); ?>" alt="Background" class="w-full h-full object-cover filter brightness-[0.85]" loading="lazy" width="800" height="400">
                        <div class="absolute inset-0 bg-[#11d411]/10 mix-blend-overlay"></div>
                    </div>
                    
                    <!-- Floating Card -->
                    <div class="absolute top-1/2 -translate-y-1/2 left-0 w-[85%] md:w-[55%] bg-white dark:bg-slate-900 rounded-[1.5rem] p-6 md:p-8 shadow-2xl border-l-[6px] border-[#11d411]">
                        <h3 class="font-bold text-lg md:text-xl text-slate-900 dark:text-white mb-3">El Origen y Proceso:</h3>
                        <div class="text-sm md:text-base text-slate-600 dark:text-slate-400 line-clamp-4 leading-relaxed">
                            <?php echo wp_kses_post( $content ); ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- The Maker -->
            <div class="w-full flex flex-col items-center mt-12 md:mt-20">
                <h2 class="text-2xl lg:text-3xl font-bold text-slate-800 dark:text-white mb-8 self-end mr-0 md:mr-8" style="color: #1e3a1e;">The Maker</h2>
                
                <div class="relative w-full">
                    <!-- Background Image -->
                    <div class="w-[90%] md:w-[75%] h-64 md:h-80 rounded-[2rem] overflow-hidden shadow-xl mr-auto">
                        <img src="<?php echo esc_url($vendor_banner); ?>" alt="The Maker" class="w-full h-full object-cover filter brightness-[0.7]" loading="lazy" width="1200" height="300">
                    </div>
                    
                    <!-- Floating Card -->
                    <div class="absolute top-1/2 -translate-y-1/2 right-0 w-[85%] md:w-[45%] bg-white dark:bg-slate-900 rounded-[1.5rem] p-6 md:p-8 shadow-2xl flex flex-col gap-4">
                        <div class="flex items-center gap-4">
                            <div class="w-16 h-16 md:w-20 md:h-20 rounded-full overflow-hidden border-2 border-slate-100 dark:border-slate-800 shadow-sm shrink-0">
                                <img src="<?php echo esc_url($vendor_logo); ?>" alt="Profile" class="w-full h-full object-cover" loading="lazy" width="80" height="80">
                            </div>
                            <div>
                                <h3 class="font-bold text-lg md:text-xl text-slate-900 dark:text-white leading-tight"><?php echo esc_html($vendor_name); ?></h3>
                                <p class="text-xs text-slate-500 flex items-center gap-1 mt-1">
                                    <span class="material-symbols-outlined text-[14px]">location_on</span>
                                    <?php echo esc_html($location_str); ?>
                                </p>
                            </div>
                        </div>
                        <?php if ( $shop_desc ) : ?>
                            <p class="text-sm md:text-base text-slate-600 dark:text-slate-400 italic">"<?php echo esc_html($shop_desc); ?>"</p>
                        <?php endif; ?>
                        <a href="<?php echo esc_url($store_url); ?>" class="text-[#11d411] text-sm font-bold flex items-center gap-1 hover:underline mt-2 w-fit">
                            Ver Comunidad <span class="material-symbols-outlined text-sm font-bold">add</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- The Land -->
            <div class="w-full flex flex-col items-center mt-12 md:mt-20">
                <h2 class="text-2xl lg:text-3xl font-bold text-slate-800 dark:text-white mb-8 self-start ml-0 md:ml-8" style="color: #1e3a1e;">The Land</h2>
                
                <div class="w-full bg-white dark:bg-slate-900 rounded-[2rem] p-4 shadow-xl border border-slate-100 dark:border-slate-800">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Map -->
                        <div class="aspect-square md:aspect-auto md:h-80 rounded-[1.5rem] overflow-hidden relative group shadow-inner">
                            <div class="absolute top-4 left-4 z-10 bg-white/95 backdrop-blur-sm px-3 py-1.5 rounded-full text-[10px] uppercase tracking-wider font-bold text-slate-700 shadow-sm">
                                <?php echo esc_html($location_str); ?>
                            </div>
                            <iframe 
                                width="100%" 
                                height="100%" 
                                frameborder="0" 
                                scrolling="no" 
                                marginheight="0" 
                                marginwidth="0" 
                                class="grayscale-[20%] contrast-[110%] group-hover:grayscale-0 transition-all duration-700"
                                src="https://maps.google.com/maps?q=<?php echo urlencode($map_query); ?>&t=&z=10&ie=UTF8&iwloc=&output=embed">
                            </iframe>
                        </div>
                        
                        <!-- Landscape Image -->
                        <div class="aspect-square md:aspect-auto md:h-80 rounded-[1.5rem] overflow-hidden relative shadow-inner">
                            <img src="<?php echo esc_url($gallery_img_2); ?>" alt="Territory" class="w-full h-full object-cover" loading="lazy" width="800" height="800">
                            <a href="https://maps.google.com/maps?q=<?php echo urlencode($map_query); ?>" target="_blank" class="absolute bottom-6 left-1/2 -translate-x-1/2 bg-white/95 backdrop-blur-sm px-5 py-2.5 rounded-full text-xs font-bold text-slate-700 shadow-xl flex items-center gap-1.5 cursor-pointer hover:bg-[#11d411] hover:text-white transition-all duration-300">
                                <span class="material-symbols-outlined text-[16px] text-[#11d411] hover:text-white transition-colors">location_on</span> Abrir en Maps
                            </a>
                        </div>
                    </div>
                    <div class="pt-5 px-3 pb-2">
                        <span class="font-bold text-lg text-slate-900 dark:text-white">Su Territorio</span>
                    </div>
                </div>
            </div>

        </div>
            
            <!-- Cultural Importance Section -->
            <section class="max-w-5xl mx-auto grid grid-cols-1 md:grid-cols-2 gap-16 items-center border-t border-slate-200 dark:border-slate-800 pt-20">
                <div class="space-y-8">
                    <div class="inline-flex p-3 rounded-2xl bg-[#11d411]/10 text-[#11d411]">
                        <span class="material-symbols-outlined text-3xl">temple_hindu</span>
                    </div>
                    <h2 class="text-4xl font-black text-slate-900 dark:text-white leading-tight">
                        Preserving Ancestral <br/><span class="text-[#11d411]">Wisdom</span>
                    </h2>
                    <div class="space-y-6 text-slate-600 dark:text-slate-400">
                        <p>
                            In Afro-Amazonian and Indigenous cultures, these objects serve as spiritual anchors.
                        </p>
                        <p>
                            By choosing this product, you are actively participating in the preservation of the language and traditional forest management practices that have kept the Amazon diverse for millennia.
                        </p>
                    </div>
                </div>
                <div class="bg-[#11d411]/5 rounded-[2.5rem] p-4 lg:p-10 border border-[#11d411]/10">
                    <div class="rounded-2xl overflow-hidden aspect-video shadow-lg">
                        <img class="w-full h-full object-cover" alt="Amazon" src="https://lh3.googleusercontent.com/aida-public/AB6AXuA_lRzrkQem4oyVmD_G5i2aCsM8WKKTg5ZYJpRD7JTzleb_715ZJSRzTyiMWa4U0baCbMBtBHLCh30rj_wRQEkxqT0O3PDRAsORoMiTj5yjCBDbU9sTwkxPdP3LuZWRnXpe87dco8PchTJqfSAJtr713_HdLRs-aiq_taRwuNVuwGrKmCHT-90oVtu300qq3FhpxPNC75ofELUO2P_lmvWR_hgM6MlMhDZW1PCnjYvjXAB5IXZoWM_nvAN0w8tUQ9WuSiLDYTMDvR4"
                             loading="lazy" width="1200" height="675" />
                    </div>
                    <div class="mt-8 grid grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <span class="text-2xl font-bold text-slate-900 dark:text-white">Empower</span>
                            <p class="text-xs font-bold text-slate-500 uppercase">Local Artisans</p>
                        </div>
                        <div class="space-y-2">
                            <span class="text-2xl font-bold text-slate-900 dark:text-white">Protect</span>
                            <p class="text-xs font-bold text-slate-500 uppercase">The Rainforest</p>
                        </div>
                    </div>
                </div>
            </section>

            <section class="max-w-4xl mx-auto pt-20">
                <?php
                if ( comments_open() || get_comments_number() ) :
                    comments_template();
                endif;
                ?>
            </section>
        </div>

        <!-- Related / Vendor Products -->
        <?php
        $related_products = array();
        $section_title = 'More to Explore';
        $section_subtitle = 'Discover other treasures';
        $view_all_link = get_permalink( wc_get_page_id( 'shop' ) );

        if ( function_exists( 'wcfm_get_vendor_id_by_post' ) ) {
            $vendor_id = wcfm_get_vendor_id_by_post( $product->get_id() );
            if ( $vendor_id ) {
                $vendor_name = wcfm_get_vendor_store_name( $vendor_id );
                $section_title = 'More from the ' . $vendor_name;
                
                $store_info = get_user_meta( $vendor_id, 'wcfmmp_profile_settings', true );
                $vendor_address = isset( $store_info['address'] ) ? $store_info['address'] : array();
                $location = array_filter(array(
                    isset($vendor_address['city']) ? $vendor_address['city'] : '',
                    isset($vendor_address['state']) ? $vendor_address['state'] : '',
                    isset($vendor_address['country']) ? $vendor_address['country'] : ''
                ));
                if ( !empty($location) ) {
                    $section_subtitle = 'Discover other treasures from ' . implode(', ', $location);
                }
                
                if ( function_exists('wcfmmp_get_store_url') ) {
                    $view_all_link = wcfmmp_get_store_url( $vendor_id );
                }

                // Get products from this vendor
                $args = array(
                    'post_type' => 'product',
                    'post_status' => 'publish',
                    'posts_per_page' => 4,
                    'post__not_in' => array( $product->get_id() ),
                    'author' => $vendor_id,
                );
                $related_products = wc_get_products( $args );
            }
        }
        
        // Fallback to related products by category if no vendor products exist
        if ( empty( $related_products ) ) {
            $related_ids = wc_get_related_products( $product->get_id(), 4 );
            if ( ! empty( $related_ids ) ) {
                $args = array(
                    'post_type' => 'product',
                    'post_status' => 'publish',
                    'posts_per_page' => 4,
                    'include' => $related_ids,
                );
                $related_products = wc_get_products( $args );
            }
        }

        if ( ! empty( $related_products ) ) : ?>
            <div class="py-20 border-t border-slate-200 dark:border-slate-800">
                <style>
                    /* Make related product prices match layout */
                    .related-product-price { display: flex; align-items: baseline; gap: 0.5rem; }
                    .related-product-price del { font-size: 0.75rem; color: #94a3b8; text-decoration: line-through; }
                    .related-product-price ins { text-decoration: none; }
                    .related-product-price ins .amount, .related-product-price > .amount { color: #64748b; font-weight: 500; font-size: 0.875rem; }
                </style>
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-end mb-8 gap-4">
                    <div>
                        <h2 class="text-3xl font-black text-slate-900 dark:text-white mb-1"><?php echo esc_html( $section_title ); ?></h2>
                        <p class="text-slate-500 dark:text-slate-400"><?php echo esc_html( $section_subtitle ); ?></p>
                    </div>
                    <a href="<?php echo esc_url( $view_all_link ); ?>" class="text-[#11d411] font-bold hover:opacity-80 transition-opacity flex items-center gap-1 shrink-0 pb-1">
                        View All <span class="material-symbols-outlined text-sm font-bold">chevron_right</span>
                    </a>
                </div>
                
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-6">
                    <?php foreach ( $related_products as $related_product ) : 
                        $post_object = get_post( $related_product->get_id() );
                        setup_postdata( $GLOBALS['post'] =& $post_object ); // phpcs:ignore
                        ?>
                        <a href="<?php echo esc_url( $related_product->get_permalink() ); ?>" class="group block">
                            <div class="aspect-square rounded-[2rem] overflow-hidden bg-slate-100 dark:bg-slate-800 mb-4 relative shadow-md hover:shadow-xl transition-shadow duration-300">
                                <?php echo $related_product->get_image( 'woocommerce_thumbnail', array( 'class' => 'w-full h-full object-cover group-hover:scale-105 transition-transform duration-500', 'loading' => 'lazy' ) ); ?>
                            </div>
                            <h3 class="font-bold text-slate-900 dark:text-white text-base mb-1 truncate group-hover:text-[#11d411] transition-colors"><?php echo $related_product->get_title(); ?></h3>
                            <div class="related-product-price">
                                <?php echo $related_product->get_price_html(); ?>
                            </div>
                        </a>
                    <?php endforeach; 
                    wp_reset_postdata();
                    ?>
                </div>
            </div>
        <?php endif; ?>

        <?php 
        remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20 );
        remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_upsell_display', 15 );
        do_action( 'woocommerce_after_single_product_summary' ); 
        ?>

    </div>
</main>

<?php do_action( 'woocommerce_after_single_product' ); ?>
