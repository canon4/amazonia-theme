<?php
/**
 * Custom Shortcodes for Amazonia Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * Shortcode para mostrar comunidades desde el CPT 'comunidad'.
 * Usage: [amazonia_communities per_page="12"]
 */
function amazonia_communities_shortcode( $atts ) {
    $atts = shortcode_atts( array(
        'per_page' => -1,
    ), $atts, 'amazonia_communities' );

    ob_start();

    $query = new WP_Query( array(
        'post_type'      => 'comunidad',
        'post_status'    => 'publish',
        'posts_per_page' => intval( $atts['per_page'] ),
        'orderby'        => 'title',
        'order'          => 'ASC',
    ) );

    if ( ! $query->have_posts() ) {
        echo '<p class="text-gray-500 text-center py-10">No se encontraron comunidades en este momento.</p>';
        return ob_get_clean();
    }

    echo '<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 my-8">';

    while ( $query->have_posts() ) :
        $query->the_post();
        $community_id = get_the_ID();
        $data         = amazonia_get_community_data( $community_id );
        $num_stores   = count( amazonia_get_community_vendors( $community_id ) );

        $nombre      = $data['nombre'];
        $logo_url    = $data['logo'];
        $descripcion = $data['descripcion']
            ? wp_trim_words( $data['descripcion'], 18, '...' )
            : __( 'Comunidad amazónica con productos sostenibles.', 'amazonia-theme' );
        $categoria   = $data['categoria'] ?: __( 'Comunidad', 'amazonia-theme' );
        $location    = implode( ', ', array_filter( [ $data['municipio'], $data['departamento'], $data['pais'] ] ) );
        $url         = get_permalink( $community_id );
        ?>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 flex flex-col justify-between hover:shadow-md transition-shadow duration-300">
            <div>
                <div class="flex items-start gap-4">
                    <!-- Logo -->
                    <div class="w-20 h-20 shrink-0 rounded-full border-2 border-green-200 overflow-hidden flex items-center justify-center bg-gray-50">
                        <?php if ( $logo_url ) : ?>
                            <img src="<?php echo esc_url( $logo_url ); ?>" alt="<?php echo esc_attr( $nombre ); ?>" class="w-full h-full object-cover m-0" loading="lazy" width="80" height="80">
                        <?php else : ?>
                            <span class="material-symbols-outlined text-green-400 text-4xl">groups</span>
                        <?php endif; ?>
                    </div>
                    <!-- Info -->
                    <div class="flex flex-col">
                        <span class="text-green-500 font-bold text-[10px] sm:text-xs tracking-widest uppercase mb-1">
                            <?php echo esc_html( $categoria ); ?>
                        </span>
                        <h3 class="text-xl sm:text-2xl font-bold text-[#0A2640] leading-tight mb-1 m-0">
                            <?php echo esc_html( $nombre ); ?>
                        </h3>
                        <?php if ( $location ) : ?>
                        <div class="flex items-center text-gray-500 text-sm gap-1">
                            <span class="material-symbols-outlined text-[1rem]">location_on</span>
                            <span class="capitalize"><?php echo esc_html( strtolower( $location ) ); ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="text-[#333333] italic mt-5 text-sm md:text-base leading-relaxed">
                    "<?php echo esc_html( $descripcion ); ?>"
                </div>

                <!-- Número de tiendas -->
                <div class="flex items-center gap-1.5 mt-4 text-xs text-gray-400 font-medium">
                    <span class="material-symbols-outlined text-[14px] text-green-400">storefront</span>
                    <?php echo esc_html( sprintf(
                        _n( '%d tienda', '%d tiendas', $num_stores, 'amazonia-theme' ),
                        $num_stores
                    ) ); ?>
                </div>
            </div>

            <div class="mt-6 pt-4 border-t border-gray-50">
                <a href="<?php echo esc_url( $url ); ?>" class="inline-flex items-center font-bold text-green-500 hover:text-green-600 transition-colors group">
                    <?php esc_html_e( 'Ver Comunidad', 'amazonia-theme' ); ?>
                    <span class="material-symbols-outlined text-sm ml-1 transform group-hover:translate-x-1 transition-transform">arrow_forward</span>
                </a>
            </div>
        </div>
        <?php
    endwhile;
    wp_reset_postdata();

    echo '</div>';

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
