<?php
/**
 * Template para página individual de Comunidad (CPT: comunidad)
 * URL: /comunidad/{slug}/
 * Muestra la info de la comunidad y lista sus tiendas vinculadas.
 *
 * @package Amazonia_Theme
 */

get_header();

$community_id = get_the_ID();
$data         = amazonia_get_community_data( $community_id );
$vendors      = amazonia_get_community_vendors( $community_id );
?>

<main id="primary" class="site-main bg-[#f6f8f6] min-h-screen">

    <!-- Hero de la comunidad -->
    <section class="bg-slate-900 relative overflow-hidden">
        <div class="absolute inset-0 z-0 opacity-20">
            <?php if ( has_post_thumbnail() ) : ?>
                <?php the_post_thumbnail( 'full', [ 'class' => 'w-full h-full object-cover' ] ); ?>
            <?php endif; ?>
        </div>
        <div class="relative z-10 max-w-5xl mx-auto px-6 py-16 lg:py-24">

            <a href="<?php echo esc_url( home_url( '/' ) ); ?>"
               class="inline-flex items-center gap-1.5 text-primary text-sm font-semibold mb-8 hover:text-white transition-colors">
                <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                <?php esc_html_e( 'Volver a comunidades', 'amazonia-theme' ); ?>
            </a>

            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-6">
                <!-- Logo -->
                <?php if ( $data['logo'] ) : ?>
                    <img src="<?php echo esc_url( $data['logo'] ); ?>"
                         alt="<?php echo esc_attr( $data['nombre'] ); ?>"
                         class="w-24 h-24 rounded-full border-4 border-primary/40 object-cover flex-shrink-0"
                         loading="lazy" width="96" height="96" />
                <?php else : ?>
                    <div class="w-24 h-24 rounded-full border-4 border-primary/30 bg-primary/10 flex items-center justify-center flex-shrink-0">
                        <span class="material-symbols-outlined text-primary text-5xl">groups</span>
                    </div>
                <?php endif; ?>

                <div>
                    <?php if ( $data['categoria'] ) : ?>
                        <span class="inline-block px-3 py-1 bg-primary text-white text-xs font-bold rounded-full mb-3 uppercase tracking-wider">
                            <?php echo esc_html( $data['categoria'] ); ?>
                        </span>
                    <?php endif; ?>
                    <h1 class="text-white text-3xl lg:text-5xl font-black leading-tight m-0">
                        <?php echo esc_html( $data['nombre'] ); ?>
                    </h1>
                    <?php $location = implode( ', ', array_filter( [ $data['municipio'], $data['departamento'], $data['pais'] ] ) ); ?>
                    <?php if ( $location ) : ?>
                        <div class="flex items-center gap-1.5 text-slate-300 mt-2 text-sm">
                            <span class="material-symbols-outlined text-[16px] text-primary">location_on</span>
                            <?php echo esc_html( $location ); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <?php if ( $data['descripcion'] ) : ?>
                <p class="text-slate-200 text-lg leading-relaxed mt-8 max-w-2xl">
                    <?php echo esc_html( $data['descripcion'] ); ?>
                </p>
            <?php endif; ?>

            <!-- Métricas rápidas -->
            <div class="flex gap-8 mt-8">
                <div class="text-center">
                    <div class="text-3xl font-black text-primary"><?php echo count( $vendors ); ?></div>
                    <div class="text-slate-400 text-sm"><?php esc_html_e( 'Tiendas', 'amazonia-theme' ); ?></div>
                </div>
            </div>
        </div>
    </section>

    <div class="max-w-5xl mx-auto px-6 py-12">

        <!-- Historia -->
        <?php if ( $data['historia'] ) : ?>
            <section class="bg-white rounded-2xl border border-slate-100 p-8 mb-12 shadow-sm">
                <h2 class="text-2xl font-black text-slate-900 mb-4 flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary">auto_stories</span>
                    <?php esc_html_e( 'Nuestra historia', 'amazonia-theme' ); ?>
                </h2>
                <div class="text-slate-600 leading-relaxed whitespace-pre-line">
                    <?php echo esc_html( $data['historia'] ); ?>
                </div>
            </section>
        <?php endif; ?>

        <!-- Tiendas de la comunidad -->
        <section>
            <h2 class="text-2xl font-black text-slate-900 mb-6 flex items-center gap-2">
                <span class="material-symbols-outlined text-primary">storefront</span>
                <?php esc_html_e( 'Tiendas de la comunidad', 'amazonia-theme' ); ?>
            </h2>

            <?php if ( empty( $vendors ) ) : ?>
                <div class="bg-white rounded-2xl border border-slate-100 p-12 text-center text-slate-400">
                    <span class="material-symbols-outlined text-5xl block mb-3 opacity-40">store</span>
                    <p><?php esc_html_e( 'Esta comunidad aún no tiene tiendas registradas.', 'amazonia-theme' ); ?></p>
                </div>
            <?php else : ?>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php foreach ( $vendors as $vendor ) :
                        $store_name = wcfm_get_vendor_store_name( $vendor->ID );
                        $logo       = function_exists( 'wcfm_get_vendor_store_logo_by_vendor' ) ? wcfm_get_vendor_store_logo_by_vendor( $vendor->ID ) : '';
                        $store_url  = function_exists( 'wcfmmp_get_store_url' ) ? wcfmmp_get_store_url( $vendor->ID ) : get_author_posts_url( $vendor->ID );
                        $profile    = get_user_meta( $vendor->ID, 'wcfmmp_profile_settings', true );
                        $shop_desc  = isset( $profile['shop_description'] ) ? wp_trim_words( wp_strip_all_tags( $profile['shop_description'] ), 14, '...' ) : '';
                    ?>
                        <a href="<?php echo esc_url( $store_url ); ?>"
                           class="bg-white rounded-2xl border border-slate-100 p-6 flex flex-col gap-4 hover:shadow-lg hover:border-primary/20 transition-all group no-underline">

                            <!-- Logo de tienda -->
                            <div class="flex items-center gap-3">
                                <?php if ( $logo ) : ?>
                                    <img src="<?php echo esc_url( $logo ); ?>"
                                         alt="<?php echo esc_attr( $store_name ); ?>"
                                         class="w-14 h-14 rounded-xl object-cover border border-slate-100 flex-shrink-0"
                                         loading="lazy" width="56" height="56" />
                                <?php else : ?>
                                    <div class="w-14 h-14 rounded-xl bg-primary/10 flex items-center justify-center flex-shrink-0">
                                        <span class="material-symbols-outlined text-primary text-2xl">storefront</span>
                                    </div>
                                <?php endif; ?>
                                <h3 class="text-base font-bold text-slate-900 leading-tight m-0 group-hover:text-primary transition-colors">
                                    <?php echo esc_html( $store_name ?: $vendor->display_name ); ?>
                                </h3>
                            </div>

                            <?php if ( $shop_desc ) : ?>
                                <p class="text-sm text-slate-500 leading-relaxed m-0">
                                    <?php echo esc_html( $shop_desc ); ?>
                                </p>
                            <?php endif; ?>

                            <div class="mt-auto flex items-center gap-1 text-primary text-sm font-bold">
                                <?php esc_html_e( 'Ver tienda', 'amazonia-theme' ); ?>
                                <span class="material-symbols-outlined text-[16px] group-hover:translate-x-1 transition-transform">arrow_forward</span>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

    </div>
</main>

<?php get_footer(); ?>
