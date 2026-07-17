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
        'per_page' => 50,
    ), $atts, 'amazonia_communities' );

    ob_start();

    $query = new WP_Query( array(
        'post_type'      => 'comunidad',
        'post_status'    => 'publish',
        'posts_per_page' => min( intval( $atts['per_page'] ), 50 ),
        'orderby'        => 'title',
        'order'          => 'ASC',
        'no_found_rows'  => true,
    ) );

    if ( ! $query->have_posts() ) {
        echo '<div class="flex flex-col items-center justify-center text-center py-20 my-8">';
        echo '<span class="material-symbols-outlined text-green-300 text-6xl mb-4">groups</span>';
        echo '<p class="text-slate-500 text-lg">' . esc_html__( 'No se encontraron comunidades en este momento.', 'amazonia-theme' ) . '</p>';
        echo '</div>';
        return ob_get_clean();
    }

    echo '<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mt-10 mb-8">';

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
        <div class="group flex flex-col bg-white rounded-2xl border border-slate-100 p-6 hover:border-primary/30 hover:shadow-lg hover:shadow-primary/5 transition-all duration-300">
            <!-- Encabezado: logo + nombre -->
            <div class="flex items-center gap-4">
                <div class="w-16 h-16 shrink-0 rounded-2xl border border-green-100 overflow-hidden flex items-center justify-center bg-green-50">
                    <?php if ( $logo_url ) : ?>
                        <img src="<?php echo esc_url( $logo_url ); ?>" alt="<?php echo esc_attr( $nombre ); ?>" class="w-full h-full object-cover m-0" loading="lazy" width="64" height="64">
                    <?php else : ?>
                        <span class="material-symbols-outlined text-green-400 text-3xl">groups</span>
                    <?php endif; ?>
                </div>
                <div class="min-w-0">
                    <span class="block text-primary font-bold text-[10px] tracking-widest uppercase mb-0.5">
                        <?php echo esc_html( $categoria ); ?>
                    </span>
                    <h3 class="text-lg sm:text-xl font-bold text-forest-green leading-tight truncate m-0">
                        <?php echo esc_html( $nombre ); ?>
                    </h3>
                    <?php if ( $location ) : ?>
                    <div class="flex items-center text-slate-400 text-xs gap-1 mt-1">
                        <span class="material-symbols-outlined text-[14px]">location_on</span>
                        <span class="capitalize truncate"><?php echo esc_html( strtolower( $location ) ); ?></span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Descripción -->
            <p class="text-slate-500 mt-5 text-sm leading-relaxed flex-1 m-0">
                <?php echo esc_html( $descripcion ); ?>
            </p>

            <!-- Pie: tiendas + enlace -->
            <div class="flex items-center justify-between mt-6 pt-4 border-t border-slate-100">
                <span class="inline-flex items-center gap-1.5 text-xs text-slate-400 font-medium">
                    <span class="material-symbols-outlined text-[16px] text-green-400">storefront</span>
                    <?php echo esc_html( sprintf(
                        _n( '%d tienda', '%d tiendas', $num_stores, 'amazonia-theme' ),
                        $num_stores
                    ) ); ?>
                </span>
                <a href="<?php echo esc_url( $url ); ?>" class="inline-flex items-center gap-1 font-bold text-sm text-primary hover:text-green-700 group-hover:gap-2 transition-all">
                    <?php esc_html_e( 'Ver Comunidad', 'amazonia-theme' ); ?>
                    <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
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
                    <h1 class="text-4xl lg:text-5xl font-black text-[#032e21] mb-6">Mis Favoritos</h1>
                    <p class="text-slate-600 md:text-lg leading-relaxed mx-auto max-w-2xl">
                        Aquí se muestran los productos que has marcado como favoritos. Guárdalos para volver a ellos cuando quieras y apoya con tu compra el saber ancestral de las comunidades del bosque.
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
