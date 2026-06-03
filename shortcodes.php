<?php
/**
 * Custom Shortcodes for Amazonia Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * Shortcode to display WCFM communities (vendors)
 * Usage: [amazonia_communities per_page="12"]
 */
function amazonia_communities_shortcode( $atts ) {
    $atts = shortcode_atts( array(
        'per_page' => -1,
    ), $atts, 'amazonia_communities' );

    ob_start();

    // Ensure WCFM functions exist
    if ( ! function_exists( 'wcfm_get_vendor_store_name' ) ) {
        echo '<p class="text-red-500">WCFM is not active or correctly configured.</p>';
        return ob_get_clean();
    }

    $vendors = get_users( array(
        'role'    => 'wcfm_vendor',
        'orderby' => 'registered',
        'order'   => 'DESC',
        'number'  => intval( $atts['per_page'] ),
    ) );

    if ( empty( $vendors ) ) {
        echo '<p class="text-gray-500">No se encontraron comunidades en este momento.</p>';
        return ob_get_clean();
    }

    echo '<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 my-8">';

    foreach ( $vendors as $vendor ) {
        $vendor_id = $vendor->ID;
        $store_name = wcfm_get_vendor_store_name( $vendor_id );
        $logo_url   = wcfm_get_vendor_store_logo_by_vendor( $vendor_id );
        
        if ( ! $logo_url ) {
            // Fallback placeholder
            $logo_url = function_exists('wc_placeholder_img_src') ? wc_placeholder_img_src() : '';
        }
        
        $store_info = get_user_meta( $vendor_id, 'wcfmmp_profile_settings', true );
        
        // Location processing
        $city = isset($store_info['address']['city']) ? $store_info['address']['city'] : '';
        $country = isset($store_info['address']['country']) ? $store_info['address']['country'] : '';
        $state = isset($store_info['address']['state']) ? $store_info['address']['state'] : '';
        
        $location_parts = array_filter( array( $city, $country ) );
        $location = ! empty( $location_parts ) ? implode( ', ', $location_parts ) : 'Ubicación no especificada';
        
        // Description
        $description = isset($store_info['shop_description']) ? wp_strip_all_tags($store_info['shop_description']) : '';
        if ( empty($description) ) {
            $description = 'Es una tienda de Artesanías de nuestra comunidad.';
        } else {
            // Trim description if it's too long
            $description = wp_trim_words( $description, 18, '...' );
        }
        
        $store_url = function_exists('wcfmmp_get_store_url') ? wcfmmp_get_store_url( $vendor_id ) : get_author_posts_url( $vendor_id );
        
        // Get Vendor Category (Badge)
        $badge_text = 'Productor Local'; // Fallback
        $vendor_categories = wp_get_object_terms( $vendor_id, 'wcfm_vendor_category' );
        if ( ! is_wp_error( $vendor_categories ) && ! empty( $vendor_categories ) ) {
            $badge_text = $vendor_categories[0]->name;
        }

        ?>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 flex flex-col justify-between hover:shadow-md transition-shadow duration-300">
            <div>
                <div class="flex items-start gap-4">
                    <!-- Logo -->
                    <div class="w-20 h-20 shrink-0 rounded-full border-2 border-green-200 overflow-hidden flex items-center justify-center bg-gray-50">
                        <?php if ( $logo_url ) : ?>
                            <img src="<?php echo esc_url( $logo_url ); ?>" alt="<?php echo esc_attr( $store_name ); ?>" class="w-full h-full object-cover m-0">
                        <?php endif; ?>
                    </div>
                    <!-- Info -->
                    <div class="flex flex-col">
                        <span class="text-green-500 font-bold text-[10px] sm:text-xs tracking-widest uppercase mb-1"><?php echo esc_html( $badge_text ); ?></span>
                        <h3 class="text-xl sm:text-2xl font-bold text-[#0A2640] leading-tight mb-1 m-0"><?php echo esc_html( $store_name ); ?></h3>
                        <div class="flex items-center text-gray-500 text-sm gap-1">
                            <span class="material-symbols-outlined text-[1rem]">location_on</span>
                            <span class="capitalize"><?php echo esc_html( strtolower($location) ); ?></span>
                        </div>
                    </div>
                </div>
                
                <div class="text-[#333333] italic mt-5 text-sm md:text-base leading-relaxed">
                    "<?php echo esc_html( $description ); ?>"
                </div>
            </div>
            
            <div class="mt-6 pt-4 border-t border-gray-50">
                <a href="<?php echo esc_url( $store_url ); ?>" class="inline-flex items-center font-bold text-green-500 hover:text-green-600 transition-colors group">
                    Ver Comunidad 
                    <span class="material-symbols-outlined text-sm ml-1 transform group-hover:translate-x-1 transition-transform">arrow_forward</span>
                </a>
            </div>
        </div>
        <?php
    }

    echo '</div>'; // End grid

    return ob_get_clean();
}
add_shortcode( 'amazonia_communities', 'amazonia_communities_shortcode' );

/**
 * Shortcode to display Favorites
 * Usage: [amazonia_favorites]
 */
function amazonia_favorites_shortcode( $atts ) {
    ob_start();
    ?>
    <div class="bg-[#fafaf5] min-h-screen font-sans">
        <div class="max-w-[1440px] mx-auto flex flex-col pt-10 pb-20 px-6">
            
            <!-- Main Content -->
            <main class="w-full">
                <!-- Header -->
                <div class="max-w-4xl mb-12 mx-auto text-center">
                    <h1 class="text-4xl lg:text-5xl font-black text-[#032e21] mb-6">My Favorites</h1>
                    <p class="text-slate-600 md:text-lg leading-relaxed mx-auto max-w-2xl">
                        A curated sanctuary of Amazonian treasures. These artifacts support the ancestral knowledge and sustainable livelihoods of forest communities.
                    </p>
                </div>

                <!-- Products Grid -->
                <div id="amazonia-favorites-grid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                    <!-- Javascript will inject products here via AJAX -->
                    <div class="col-span-full h-64 flex flex-col items-center justify-center">
                         <!-- Fallback spinner until JS takes over -->
                         <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-[#11d411] mb-4"></div>
                         <p class="text-slate-500">Cargando favoritos...</p>
                    </div>
                </div>
            </main>

        </div>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode( 'amazonia_favorites', 'amazonia_favorites_shortcode' );
