<?php
/**
 * The front page template file for the Amazonia Theme.
 * Featuring a minimalist, modern, and visually stunning storytelling design.
 *
 * @package Amazonia_Theme
 */

get_header();

// Setup DB Queries
$communities_query = new WP_Query( array(
    'post_type'      => 'comunidad',
    'post_status'    => 'publish',
    'posts_per_page' => 3,
    'orderby'        => 'date',
    'order'          => 'DESC',
) );

$products_query = null;
if ( class_exists( 'WooCommerce' ) ) {
    $products_query = new WP_Query( array(
        'post_type'      => 'product',
        'post_status'    => 'publish',
        'posts_per_page' => 4,
        'orderby'        => 'date',
        'order'          => 'DESC',
    ) );
}
?>

<main id="primary" class="site-main bg-background-light dark:bg-background-dark min-h-screen transition-colors duration-300">

    <!-- 1. Immersive Hero Section -->
    <section class="relative max-w-[1440px] mx-auto w-full px-4 md:px-10 lg:px-20 py-8">
        <div class="relative min-h-[550px] lg:min-h-[650px] rounded-[2rem] overflow-hidden bg-[#0a2e0a] flex items-center shadow-2xl">
            <!-- Background Image & Gradient overlay -->
            <div class="absolute inset-0 z-0">
                <img alt="Selva amazónica mística rodeada de niebla y vegetación densa" 
                     class="w-full h-full object-cover opacity-60 mix-blend-multiply transition-all duration-700 hover:scale-105" 
                     src="https://images.unsplash.com/photo-1516026672322-bc52d61a55d5?q=80&w=1600&auto=format&fit=crop" 
                     fetchpriority="high" />
                <div class="absolute inset-0 bg-gradient-to-tr from-[#0a2e0a] via-[#0a2e0a]/80 to-transparent"></div>
                <!-- Ambient decorative glow -->
                <div class="absolute -top-40 -left-40 w-96 h-96 bg-primary/20 rounded-full blur-[120px] pointer-events-none"></div>
            </div>

            <!-- Content Area -->
            <div class="relative z-10 p-8 md:p-16 lg:p-20 max-w-3xl">
                <span class="inline-flex items-center gap-2 px-4 py-1.5 bg-primary/20 border border-primary/30 backdrop-blur-md text-[#4ade80] text-xs font-bold rounded-full mb-6 uppercase tracking-widest shadow-sm">
                    <span class="material-symbols-outlined text-[14px]">eco</span>
                    <?php esc_html_e( 'Saberes Ancestrales & Comercio Justo', 'amazonia-theme' ); ?>
                </span>
                
                <h1 class="text-white text-4xl md:text-6xl font-black leading-tight mb-6 tracking-tight">
                    <?php esc_html_e( 'El Latido de la Selva en Cada Creación', 'amazonia-theme' ); ?>
                </h1>
                
                <p class="text-green-100/95 text-lg md:text-xl mb-10 leading-relaxed font-light">
                    <?php esc_html_e( 'Cada producto cuenta una historia sagrada. Conecta directamente con comunidades indígenas y afro-amazónicas, y apoya la preservación de su territorio y cultura.', 'amazonia-theme' ); ?>
                </p>
                
                <div class="flex flex-col sm:flex-row gap-4">
                    <a href="<?php echo class_exists('WooCommerce') ? esc_url( wc_get_page_permalink( 'shop' ) ) : '#'; ?>" 
                       class="bg-primary hover:bg-[#15803d] text-white font-bold py-4 px-10 rounded-full transition-all duration-300 transform hover:-translate-y-1 shadow-lg hover:shadow-primary/20 text-center no-underline flex items-center justify-center gap-2">
                        <span class="material-symbols-outlined">shopping_bag</span>
                        <?php esc_html_e( 'Explorar Tienda', 'amazonia-theme' ); ?>
                    </a>
                    
                    <a href="#comunidades" 
                       class="bg-white/10 hover:bg-white/20 text-white border border-white/20 hover:border-white/30 backdrop-blur-md font-bold py-4 px-10 rounded-full transition-all duration-300 transform hover:-translate-y-1 text-center no-underline flex items-center justify-center gap-2">
                        <span class="material-symbols-outlined">diversity_3</span>
                        <?php esc_html_e( 'Ver Comunidades', 'amazonia-theme' ); ?>
                    </a>
                </div>
            </div>
        </div>
    </section>


    <!-- 2. The 3 Pillars of Amazonian Storytelling -->
    <section class="max-w-[1440px] mx-auto px-4 md:px-10 lg:px-20 py-16">
        <div class="text-center max-w-3xl mx-auto mb-16">
            <h2 class="text-3xl md:text-4xl font-black text-slate-900 dark:text-white mb-4">
                <?php esc_html_e( 'El Valor de la Historia', 'amazonia-theme' ); ?>
            </h2>
            <p class="text-slate-600 dark:text-slate-400 text-lg">
                <?php esc_html_e( 'Creemos en un comercio transparente que va más allá del intercambio de objetos. Cada pieza porta la esencia de tres elementos vitales:', 'amazonia-theme' ); ?>
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- Pillar 1: El Territorio -->
            <div class="bg-white dark:bg-slate-800 rounded-2xl p-8 border border-primary/5 hover:border-primary/20 transition-all duration-300 hover:shadow-xl group">
                <div class="inline-flex p-4 rounded-xl bg-green-500/10 text-primary dark:text-[#4ade80] mb-6 group-hover:scale-110 transition-transform">
                    <span class="material-symbols-outlined text-3xl">public</span>
                </div>
                <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-3"><?php esc_html_e( 'El Territorio', 'amazonia-theme' ); ?></h3>
                <p class="text-slate-600 dark:text-slate-400 leading-relaxed text-sm">
                    <?php esc_html_e( 'La selva no es solo un recurso; es el hogar sagrado de donde nacen las materias primas (cumare, inchi, arcillas) recolectadas respetando los ciclos biológicos de la Amazonía.', 'amazonia-theme' ); ?>
                </p>
            </div>

            <!-- Pillar 2: El Fabricante -->
            <div class="bg-white dark:bg-slate-800 rounded-2xl p-8 border border-primary/5 hover:border-primary/20 transition-all duration-300 hover:shadow-xl group">
                <div class="inline-flex p-4 rounded-xl bg-green-500/10 text-primary dark:text-[#4ade80] mb-6 group-hover:scale-110 transition-transform">
                    <span class="material-symbols-outlined text-3xl">handyman</span>
                </div>
                <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-3"><?php esc_html_e( 'El Hacedor', 'amazonia-theme' ); ?></h3>
                <p class="text-slate-600 dark:text-slate-400 leading-relaxed text-sm">
                    <?php esc_html_e( 'Cada producto es elaborado por artesanos, familias y cooperativas locales. La compra directa empodera a estas familias y preserva sus técnicas de generación en generación.', 'amazonia-theme' ); ?>
                </p>
            </div>

            <!-- Pillar 3: La Historia -->
            <div class="bg-white dark:bg-slate-800 rounded-2xl p-8 border border-primary/5 hover:border-primary/20 transition-all duration-300 hover:shadow-xl group">
                <div class="inline-flex p-4 rounded-xl bg-green-500/10 text-primary dark:text-[#4ade80] mb-6 group-hover:scale-110 transition-transform">
                    <span class="material-symbols-outlined text-3xl">auto_stories</span>
                </div>
                <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-3"><?php esc_html_e( 'La Historia', 'amazonia-theme' ); ?></h3>
                <p class="text-slate-600 dark:text-slate-400 leading-relaxed text-sm">
                    <?php esc_html_e( 'No adquieres solo un producto; adquieres un relato cosmogónico, un pedazo de mitología y la explicación cultural de los patrones tejidos o tallados.', 'amazonia-theme' ); ?>
                </p>
            </div>
        </div>
    </section>


    <!-- 3. Featured Communities Section -->
    <section id="comunidades" class="bg-[#f0fdf4]/50 dark:bg-slate-900/30 py-20 border-y border-primary/5">
        <div class="max-w-[1440px] mx-auto px-4 md:px-10 lg:px-20">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-end mb-12 gap-4">
                <div>
                    <span class="text-primary font-bold text-xs tracking-widest uppercase mb-2 block"><?php esc_html_e( 'Vínculos de Sangre y Tierra', 'amazonia-theme' ); ?></span>
                    <h2 class="text-3xl md:text-4xl font-black text-slate-900 dark:text-white m-0">
                        <?php esc_html_e( 'Comunidades Ancestrales', 'amazonia-theme' ); ?>
                    </h2>
                </div>
                <a href="<?php echo esc_url( home_url( '/?page_id=53/' ) ); ?>" 
                   class="text-primary hover:text-[#15803d] font-bold flex items-center gap-1 hover:underline transition-colors shrink-0">
                    <?php esc_html_e( 'Ver todas las comunidades', 'amazonia-theme' ); ?>
                    <span class="material-symbols-outlined text-sm">arrow_forward</span>
                </a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <?php
                if ( $communities_query && $communities_query->have_posts() ) :
                    while ( $communities_query->have_posts() ) :
                        $communities_query->the_post();
                        $c_id = get_the_ID();
                        $data = amazonia_get_community_data( $c_id );
                        $num_stores = count( amazonia_get_community_vendors( $c_id ) );

                        $nombre      = $data['nombre'];
                        $logo_url    = $data['logo'];
                        $desc        = $data['descripcion'] ? wp_trim_words( $data['descripcion'], 20, '...' ) : __( 'Comunidad amazónica con saberes únicos.', 'amazonia-theme' );
                        $categoria   = $data['categoria'] ?: __( 'Comunidad', 'amazonia-theme' );
                        $location    = implode( ', ', array_filter( [ $data['municipio'], $data['departamento'], $data['pais'] ] ) );
                        $url         = get_permalink( $c_id );
                        ?>
                        <!-- Community Card -->
                        <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 border border-slate-100 dark:border-slate-800 shadow-sm flex flex-col justify-between hover:shadow-xl hover:-translate-y-1 transition-all duration-300 group">
                            <div>
                                <div class="flex items-center gap-4 mb-5">
                                    <div class="w-16 h-16 rounded-full border-2 border-primary/20 overflow-hidden bg-slate-50 flex items-center justify-center shrink-0">
                                        <?php if ( $logo_url ) : ?>
                                            <img src="<?php echo esc_url( $logo_url ); ?>" alt="<?php echo esc_attr( $nombre ); ?>" class="w-full h-full object-cover">
                                        <?php else : ?>
                                            <span class="material-symbols-outlined text-primary text-3xl">groups</span>
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <span class="text-primary font-bold text-[10px] uppercase tracking-wider block mb-0.5"><?php echo esc_html( $categoria ); ?></span>
                                        <h3 class="text-lg font-bold text-slate-900 dark:text-white m-0 group-hover:text-primary transition-colors"><?php echo esc_html( $nombre ); ?></h3>
                                        <?php if ( $location ) : ?>
                                            <span class="text-xs text-slate-500 dark:text-slate-400 flex items-center gap-1 mt-0.5">
                                                <span class="material-symbols-outlined text-[12px] text-primary">location_on</span>
                                                <?php echo esc_html( $location ); ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <p class="text-slate-600 dark:text-slate-300 text-sm leading-relaxed italic">
                                    "<?php echo esc_html( $desc ); ?>"
                                </p>
                            </div>
                            <div class="mt-6 pt-4 border-t border-slate-50 dark:border-slate-700/50 flex justify-between items-center">
                                <span class="text-xs text-slate-400 flex items-center gap-1 font-medium">
                                    <span class="material-symbols-outlined text-[14px]">storefront</span>
                                    <?php echo esc_html( sprintf( _n( '%d tienda', '%d tiendas', $num_stores, 'amazonia-theme' ), $num_stores ) ); ?>
                                </span>
                                <a href="<?php echo esc_url( $url ); ?>" class="text-primary hover:text-[#15803d] font-bold text-sm flex items-center gap-0.5 group/link">
                                    <?php esc_html_e( 'Ver Comunidad', 'amazonia-theme' ); ?>
                                    <span class="material-symbols-outlined text-sm transform group-hover/link:translate-x-1 transition-transform">arrow_forward</span>
                                </a>
                            </div>
                        </div>
                        <?php
                    endwhile;
                    wp_reset_postdata();
                else :
                    // --- MOCK FALLBACK COMMUNITIES ---
                    $mock_communities = array(
                        array(
                            'nombre' => 'Comunidad Tikuna',
                            'categoria' => 'Artesanías & Tejidos',
                            'location' => 'Leticia, Amazonas',
                            'desc' => 'Artesanas expertas en tejer la fibra de la palmera de cumare y teñirla con tintes naturales de semillas y cortezas de la selva profunda.',
                            'logo' => 'https://images.unsplash.com/photo-1504618223053-559bdef9dd5a?q=80&w=200&auto=format&fit=crop',
                            'stores' => 4,
                        ),
                        array(
                            'nombre' => 'Asociación Kamentsá',
                            'categoria' => 'Talla en Madera',
                            'location' => 'Valle de Sibundoy, Putumayo',
                            'desc' => 'Talladores tradicionales de máscaras sagradas de madera que narran el origen del viento, el maíz y los cantos de sanación ancestral.',
                            'logo' => 'https://images.unsplash.com/photo-1596436889106-be35e843f974?q=80&w=200&auto=format&fit=crop',
                            'stores' => 2,
                        ),
                        array(
                            'nombre' => 'Cooperativa Siona',
                            'categoria' => 'Aceites & Botánica',
                            'location' => 'Puerto Asís, Putumayo',
                            'desc' => 'Productores de aceites esenciales ecológicos, resinas de copal sagrado y plantas medicinales recolectadas bajo criterios de conservación.',
                            'logo' => 'https://images.unsplash.com/photo-1546842931-886c185b4c8c?q=80&w=200&auto=format&fit=crop',
                            'stores' => 3,
                        ),
                    );

                    foreach ( $mock_communities as $mock ) :
                        ?>
                        <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 border border-slate-100 dark:border-slate-800 shadow-sm flex flex-col justify-between hover:shadow-xl hover:-translate-y-1 transition-all duration-300 group">
                            <div>
                                <div class="flex items-center gap-4 mb-5">
                                    <div class="w-16 h-16 rounded-full border-2 border-primary/20 overflow-hidden bg-slate-50 flex items-center justify-center shrink-0">
                                        <img src="<?php echo esc_url( $mock['logo'] ); ?>" alt="<?php echo esc_attr( $mock['nombre'] ); ?>" class="w-full h-full object-cover">
                                    </div>
                                    <div>
                                        <span class="text-primary font-bold text-[10px] uppercase tracking-wider block mb-0.5"><?php echo esc_html( $mock['categoria'] ); ?></span>
                                        <h3 class="text-lg font-bold text-slate-900 dark:text-white m-0 group-hover:text-primary transition-colors"><?php echo esc_html( $mock['nombre'] ); ?></h3>
                                        <span class="text-xs text-slate-500 dark:text-slate-400 flex items-center gap-1 mt-0.5">
                                            <span class="material-symbols-outlined text-[12px] text-primary">location_on</span>
                                            <?php echo esc_html( $mock['location'] ); ?>
                                        </span>
                                    </div>
                                </div>
                                <p class="text-slate-600 dark:text-slate-300 text-sm leading-relaxed italic">
                                    "<?php echo esc_html( $mock['desc'] ); ?>"
                                </p>
                            </div>
                            <div class="mt-6 pt-4 border-t border-slate-50 dark:border-slate-700/50 flex justify-between items-center">
                                <span class="text-xs text-slate-400 flex items-center gap-1 font-medium">
                                    <span class="material-symbols-outlined text-[14px]">storefront</span>
                                    <?php echo esc_html( sprintf( _n( '%d tienda', '%d tiendas', $mock['stores'], 'amazonia-theme' ), $mock['stores'] ) ); ?>
                                </span>
                                <a href="#" class="text-primary hover:text-[#15803d] font-bold text-sm flex items-center gap-0.5 group/link">
                                    <?php esc_html_e( 'Ver Comunidad', 'amazonia-theme' ); ?>
                                    <span class="material-symbols-outlined text-sm transform group-hover/link:translate-x-1 transition-transform">arrow_forward</span>
                                </a>
                            </div>
                        </div>
                        <?php
                    endforeach;
                endif;
                ?>
            </div>
        </div>
    </section>


    <!-- 4. Featured Products with Storytelling Section -->
    <section class="max-w-[1440px] mx-auto px-4 md:px-10 lg:px-20 py-20">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-end mb-12 gap-4">
            <div>
                <span class="text-primary font-bold text-xs tracking-widest uppercase mb-2 block"><?php esc_html_e( 'Tesoros con Alma', 'amazonia-theme' ); ?></span>
                <h2 class="text-3xl md:text-4xl font-black text-slate-900 dark:text-white m-0">
                    <?php esc_html_e( 'Productos con Historia', 'amazonia-theme' ); ?>
                </h2>
            </div>
            <a href="<?php echo class_exists('WooCommerce') ? esc_url( wc_get_page_permalink( 'shop' ) ) : '#'; ?>" 
               class="text-primary hover:text-[#15803d] font-bold flex items-center gap-1 hover:underline transition-colors shrink-0">
                <?php esc_html_e( 'Ver todo el catálogo', 'amazonia-theme' ); ?>
                <span class="material-symbols-outlined text-sm">arrow_forward</span>
            </a>
        </div>

        <?php if ( $products_query && $products_query->have_posts() ) : ?>
            <ul class="products grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8 list-none p-0 m-0">
                <?php
                while ( $products_query->have_posts() ) :
                    $products_query->the_post();
                    wc_get_template_part( 'content', 'product' );
                endwhile;
                wp_reset_postdata();
                ?>
            </ul>
        <?php else : ?>
            <div class="w-full py-16 text-center text-slate-500 dark:text-slate-400 bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-800 p-8 shadow-sm">
                <span class="material-symbols-outlined text-5xl mb-4 text-primary opacity-40">inventory_2</span>
                <h3 class="text-lg font-bold text-slate-700 dark:text-slate-300 m-0 mb-1"><?php esc_html_e( 'No hay productos disponibles en este momento', 'amazonia-theme' ); ?></h3>
                <p class="text-sm m-0"><?php esc_html_e( 'Vuelve pronto para conocer los tesoros y las historias de nuestra selva.', 'amazonia-theme' ); ?></p>
            </div>
        <?php endif; ?>
    </section>

    <!-- 6. Impact & Heritage Details -->
    <section class="max-w-[1440px] mx-auto px-4 md:px-10 lg:px-20 py-12">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">
            <!-- Left image collage -->
            <div class="relative rounded-[2.5rem] overflow-hidden p-4 bg-primary/5 border border-primary/10">
                <div class="rounded-2xl overflow-hidden aspect-video shadow-lg relative group">
                    <img class="w-full h-full object-cover filter brightness-[0.9] group-hover:scale-105 transition-transform duration-700" 
                         alt="Manos artesanas trenzando fibras vegetales sostenibles" 
                         src="https://images.unsplash.com/photo-1504618223053-559bdef9dd5a?q=80&w=1200&auto=format&fit=crop"/>
                    <div class="absolute inset-0 bg-gradient-to-t from-slate-900/40 to-transparent"></div>
                </div>
                
                <div class="grid grid-cols-2 gap-6 mt-8">
                    <div class="p-6 bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-800/80 shadow-sm">
                        <span class="text-3xl font-black text-primary block mb-2">100%</span>
                        <span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest block"><?php esc_html_e( 'Comercio Directo', 'amazonia-theme' ); ?></span>
                        <p class="text-slate-600 dark:text-slate-300 text-xs mt-2 m-0"><?php esc_html_e( 'Sin intermediarios comerciales. El valor total llega a la comunidad.', 'amazonia-theme' ); ?></p>
                    </div>
                    <div class="p-6 bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-800/80 shadow-sm">
                        <span class="text-3xl font-black text-primary block mb-2">Eco</span>
                        <span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest block"><?php esc_html_e( 'Cosecha Consciente', 'amazonia-theme' ); ?></span>
                        <p class="text-slate-600 dark:text-slate-300 text-xs mt-2 m-0"><?php esc_html_e( 'Recolección silvestre controlada para proteger la biodiversidad.', 'amazonia-theme' ); ?></p>
                    </div>
                </div>
            </div>

            <!-- Right content detail -->
            <div class="space-y-8">
                <span class="text-primary font-bold text-xs tracking-widest uppercase block"><?php esc_html_e( 'Impacto Directo en la Selva', 'amazonia-theme' ); ?></span>
                <h2 class="text-3xl md:text-4xl font-black text-slate-900 dark:text-white leading-tight">
                    <?php esc_html_e( 'Preservación Cultural a través del Sustento Sostenible', 'amazonia-theme' ); ?>
                </h2>
                
                <div class="space-y-6 text-slate-600 dark:text-slate-400 font-light leading-relaxed">
                    <p>
                        <?php esc_html_e( 'En las culturas amazónicas, los objetos cotidianos no son meras mercancías. Son portales a su cosmovisión. El diseño de una mochila o el tallado de una máscara relata la historia de espíritus guardianes, ríos celestes y árboles sagrados.', 'amazonia-theme' ); ?>
                    </p>
                    <p>
                        <?php esc_html_e( 'Al adquirir estos productos, contribuyes a crear una economía regenerativa que compite de frente contra la deforestación y la minería ilegal. Provees a los jóvenes de las comunidades una alternativa económica digna basada en el orgullo de sus propios saberes.', 'amazonia-theme' ); ?>
                    </p>
                </div>

                <div class="pt-4 flex flex-wrap gap-6 border-t border-slate-200/60 dark:border-slate-700/60">
                    <div class="flex items-center gap-3">
                        <span class="material-symbols-outlined text-primary text-2xl">verified_user</span>
                        <span class="text-sm font-bold text-slate-800 dark:text-white"><?php esc_html_e( 'Origen Auténtico', 'amazonia-theme' ); ?></span>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="material-symbols-outlined text-primary text-2xl">favorite</span>
                        <span class="text-sm font-bold text-slate-800 dark:text-white"><?php esc_html_e( 'Fondo Solidario', 'amazonia-theme' ); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </section>

</main>

<?php
get_footer();
