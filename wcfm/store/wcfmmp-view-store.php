<?php
/**
 * Custom Template for displaying store.
 * Overrides WCFM Marketplace default template
 */
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $WCFM, $WCFMmp;

$wcfm_store_url    = wcfm_get_option( 'wcfm_store_url', 'store' );
$wcfm_store_name   = apply_filters( 'wcfmmp_store_query_var', get_query_var( $wcfm_store_url ) );
if ( empty( $wcfm_store_name ) ) return;
$seller_info       = get_user_by( 'slug', $wcfm_store_name );
if( !$seller_info ) return;

$store_user        = wcfmmp_get_store( $seller_info->ID );
$store_info        = $store_user->get_shop_info();

// Extract needed data
$store_name = isset($store_info['store_name']) ? esc_html($store_info['store_name']) : '';
$store_description = isset($store_info['shop_description']) ? wp_kses_post($store_info['shop_description']) : '';
$banner_url = $store_user->get_banner();
if ( ! $banner_url ) {
	$banner_url = !empty( $WCFMmp->wcfmmp_marketplace_options['store_default_banner'] ) ? wcfm_get_attachment_url($WCFMmp->wcfmmp_marketplace_options['store_default_banner']) : esc_url($WCFMmp->plugin_url . 'assets/images/default_banner.jpg');
}
$avatar_url = $store_user->get_avatar();
$email = $store_user->get_email();
$phone = $store_user->get_phone();

// Location processing to get just City/Country if possible
$city = isset($store_info['address']['city']) ? $store_info['address']['city'] : '';
$country = isset($store_info['address']['country']) ? $store_info['address']['country'] : '';
$location_parts = array_filter( array( $city, $country ) );
$short_location = ! empty( $location_parts ) ? implode( ', ', $location_parts ) : 'Ubicación no especificada';

$address = $store_user->get_address_string(); // Full address if needed

// Get Vendor Category (Badge)
$badge_text = 'Productor Local'; // Fallback
$vendor_categories = wp_get_object_terms( $seller_info->ID, 'wcfm_vendor_category' );
if ( ! is_wp_error( $vendor_categories ) && ! empty( $vendor_categories ) ) {
    $badge_text = $vendor_categories[0]->name;
}

// Custom stats (fallback to dummy data for now)
$reforested_area = get_user_meta($seller_info->ID, 'reforested_area', true) ?: '120 Hectáreas';
$families_supported = get_user_meta($seller_info->ID, 'families_supported', true) ?: '45 Familias';
$traditions_preserved = get_user_meta($seller_info->ID, 'traditions_preserved', true) ?: '12 Artesanías';

// Add store url logic for View All link
$store_url = function_exists('wcfmmp_get_store_url') ? wcfmmp_get_store_url( $seller_info->ID ) : get_author_posts_url( $seller_info->ID );

get_header( 'shop' );
?>

<!-- HTML Layout with Tailwind CSS -->
<div class="wcfmmp-single-store-holder w-full min-h-screen bg-gray-50 pb-20 font-sans">

    <!-- Hero Banner -->
    <div class="relative w-full h-[500px] bg-cover bg-center" style="background-image: url('<?php echo esc_url($banner_url); ?>');">
        <!-- Dark overlay -->
        <div class="absolute inset-0 bg-black bg-opacity-60"></div>
        
        <!-- Banner Content -->
        <div class="absolute inset-0 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col justify-center">
            <div class="mt-20">
                <div class="flex items-center gap-3 mb-4">
                    <span class="bg-green-500 text-white text-[11px] font-bold uppercase tracking-wider py-1.5 px-3 rounded-md"><?php echo esc_html(mb_strtoupper($badge_text)); ?></span>
                    <?php if ($short_location !== 'Ubicación no especificada'): ?>
                        <span class="text-gray-200 text-sm flex items-center gap-1 font-medium">
                            <span class="material-symbols-outlined text-[18px]">location_on</span>
                            <span class="capitalize"><?php echo esc_html(strtolower($short_location)); ?></span>
                        </span>
                    <?php endif; ?>
                </div>
                <h1 class="text-5xl md:text-6xl font-bold text-white mb-4 tracking-tight"><?php echo $store_name; ?></h1>
                <?php if ($store_description): ?>
                    <div class="text-gray-200 text-lg md:text-xl max-w-3xl line-clamp-2 md:line-clamp-3 leading-relaxed">
                        <?php echo strip_tags($store_description); ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Main Content Area -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-20 relative z-10">
        <div class="flex flex-col lg:flex-row gap-8">
            
            <!-- Left Sidebar (Vendor Profile Card) -->
            <div class="lg:w-1/3">
                <div class="bg-white rounded-2xl shadow-xl shadow-black/5 p-6 lg:p-8 flex flex-col gap-6">
                    <div class="flex items-center gap-4">
                        <img src="<?php echo esc_url($avatar_url); ?>" alt="Avatar" class="w-16 h-16 rounded-full object-cover shadow-sm">
                        <div>
                            <h3 class="text-lg font-bold text-gray-900"><?php echo esc_html($seller_info->display_name); ?></h3>
                            <p class="text-sm text-gray-500">Representante de la Comunidad</p>
                        </div>
                    </div>
                    
                    <div class="flex flex-col gap-3 mt-2">
                        <?php if ($phone): ?>
                            <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $phone); ?>" target="_blank" class="w-full bg-[#25D366] hover:bg-[#1ebe57] text-white font-semibold py-3.5 px-4 rounded-xl flex items-center justify-center gap-2 transition-colors">
                                <span class="material-symbols-outlined text-xl">chat</span>
                                Mensaje por WhatsApp
                            </a>
                        <?php endif; ?>
                        
                        <?php if ($email): ?>
                            <a href="mailto:<?php echo esc_attr($email); ?>" class="w-full bg-gray-50 hover:bg-gray-100 text-gray-800 font-semibold py-3.5 px-4 rounded-xl flex items-center justify-center gap-2 transition-colors border border-gray-200">
                                <span class="material-symbols-outlined text-xl">mail</span>
                                Enviar Correo a la Comunidad
                            </a>
                        <?php endif; ?>
                    </div>

                    <?php if ($address): ?>
                        <div class="mt-4 pt-6 border-t border-gray-100">
                            <h4 class="text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-4">Ubicación de Origen</h4>
                            <!-- Map Placeholder (Stylized map element matching the design) -->
                            <div class="w-full h-44 bg-[#4a8a96] rounded-xl overflow-hidden relative">
                                <!-- Using a generic svg background or CSS to simulate the map in the user's screenshot -->
                                <div class="absolute inset-0 opacity-40 mix-blend-overlay" style="background-image: url('data:image/svg+xml;utf8,<svg width=\"200\" height=\"200\" xmlns=\"http://www.w3.org/2000/svg\"><path d=\"M10,50 Q40,10 80,40 T150,30 Q180,80 140,120 T60,180 Q20,130 10,50 Z\" fill=\"%23ffffff\"/></svg>'); background-size: cover; background-position: center;"></div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Right Content Area -->
            <div class="lg:w-2/3 flex flex-col gap-8">
                
                <!-- Stats Row -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="bg-green-50/50 rounded-2xl p-6 text-center flex flex-col items-center justify-center transition-all hover:bg-green-50 border border-green-100/50">
                        <span class="material-symbols-outlined text-green-600 mb-2 text-3xl">park</span>
                        <p class="text-xs text-gray-500 mb-1 font-medium uppercase tracking-wider">Área Reforestada</p>
                        <p class="text-xl font-bold text-gray-900"><?php echo esc_html($reforested_area); ?></p>
                    </div>
                    <div class="bg-green-50/50 rounded-2xl p-6 text-center flex flex-col items-center justify-center transition-all hover:bg-green-50 border border-green-100/50">
                        <span class="material-symbols-outlined text-green-600 mb-2 text-3xl">groups</span>
                        <p class="text-xs text-gray-500 mb-1 font-medium uppercase tracking-wider">Familias Apoyadas</p>
                        <p class="text-xl font-bold text-gray-900"><?php echo esc_html($families_supported); ?></p>
                    </div>
                    <div class="bg-green-50/50 rounded-2xl p-6 text-center flex flex-col items-center justify-center transition-all hover:bg-green-50 border border-green-100/50">
                        <span class="material-symbols-outlined text-green-600 mb-2 text-3xl">auto_awesome</span>
                        <p class="text-xs text-gray-500 mb-1 font-medium uppercase tracking-wider">Tradiciones Preservadas</p>
                        <p class="text-xl font-bold text-gray-900"><?php echo esc_html($traditions_preserved); ?></p>
                    </div>
                </div>

                <!-- Mission Section -->
                <div class="bg-white rounded-2xl shadow-xl shadow-black/5 p-8 lg:p-10 border border-gray-100/50">
                    <h2 class="text-3xl font-bold text-gray-900 mb-6">Nuestras Raíces y Misión</h2>
                    <div class="prose max-w-none text-gray-600 mb-10 leading-relaxed text-[17px]">
                        <?php echo $store_description ? $store_description : '<p>Somos una tienda de artesanías y productos locales. Nuestra misión es compartir nuestra herencia cultural a través del comercio sostenible y justo, protegiendo nuestro territorio y nuestras tradiciones.</p>'; ?>
                    </div>
                    
                    <!-- Environmental & Cultural Commitment -->
                    <div class="bg-gray-50/80 rounded-2xl p-6 lg:p-8">
                        <div class="flex items-center gap-3 mb-6">
                            <span class="material-symbols-outlined text-green-500 bg-white rounded-full shadow-sm text-xl p-1">verified</span>
                            <h3 class="text-xl font-bold text-gray-900">Compromiso Ambiental y Cultural</h3>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-y-4 gap-x-6">
                            <div class="flex items-start gap-3">
                                <span class="material-symbols-outlined text-green-500 text-lg mt-0.5">check_circle</span>
                                <p class="text-sm text-gray-700">Métodos de cosecha sin deforestación</p>
                            </div>
                            <div class="flex items-start gap-3">
                                <span class="material-symbols-outlined text-green-500 text-lg mt-0.5">check_circle</span>
                                <p class="text-sm text-gray-700">Beneficio directo a la comunidad (pago justo)</p>
                            </div>
                            <div class="flex items-start gap-3">
                                <span class="material-symbols-outlined text-green-500 text-lg mt-0.5">check_circle</span>
                                <p class="text-sm text-gray-700">Capacitación en preservación de biodiversidad</p>
                            </div>
                            <div class="flex items-start gap-3">
                                <span class="material-symbols-outlined text-green-500 text-lg mt-0.5">check_circle</span>
                                <p class="text-sm text-gray-700">Transferencia intergeneracional de habilidades</p>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- Products Section -->
        <div class="mt-20 mb-8">
            <div class="flex justify-between items-end mb-8">
                <div>
                    <h2 class="text-3xl font-bold text-gray-900">Productos Artesanales</h2>
                    <p class="text-gray-500 mt-2 text-lg">Productos auténticos directamente de la comunidad</p>
                </div>
                <a href="<?php echo esc_url($store_url); ?>" class="text-green-600 font-semibold hover:text-green-700 flex items-center gap-1 group pb-1">
                    Ver Todo <span class="material-symbols-outlined text-sm group-hover:translate-x-1 transition-transform">arrow_forward</span>
                </a>
            </div>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <?php
                $args = array(
                    'post_type'      => 'product',
                    'post_status'    => 'publish',
                    'author'         => $seller_info->ID,
                    'posts_per_page' => 4,
                );
                $products = new WP_Query($args);

                if ($products->have_posts()) :
                    while ($products->have_posts()) : $products->the_post();
                        wc_get_template_part( 'content', 'product' );
                    endwhile;
                    wp_reset_postdata();
                else :
                    echo '<p class="text-gray-500 col-span-full">No se encontraron productos para esta comunidad.</p>';
                endif;
                ?>
            </div>
        </div>
    </div>
</div>

<?php get_footer( 'shop' ); ?>
