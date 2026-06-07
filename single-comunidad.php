<?php
/**
 * Template para página individual de Comunidad (CPT: comunidad)
 * URL: /comunidad/{slug}/
 *
 * @package Amazonia_Theme
 */

get_header();

$community_id = get_the_ID();
$data         = amazonia_get_community_data( $community_id );
$vendors      = amazonia_get_community_vendors( $community_id );
$embed_url    = amazonia_get_video_embed_url( $data['video_url'] );
?>

<main id="primary" class="site-main bg-[#f6f8f6] min-h-screen">

    <!-- ── Hero ─────────────────────────────────────────────────── -->
    <section class="relative overflow-hidden" style="background:#0f172a;">

        <!-- Fondo: banner propio o imagen destacada al 20% -->
        <?php if ( $data['banner'] ) : ?>
            <div class="absolute inset-0 z-0"
                 style="background:url('<?php echo esc_url( $data['banner'] ); ?>') center/cover no-repeat;opacity:.45;"></div>
        <?php elseif ( has_post_thumbnail() ) : ?>
            <div class="absolute inset-0 z-0 opacity-20">
                <?php the_post_thumbnail( 'full', [ 'class' => 'w-full h-full object-cover' ] ); ?>
            </div>
        <?php endif; ?>

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
                         loading="eager" width="96" height="96" />
                <?php else : ?>
                    <div class="w-24 h-24 rounded-full border-4 border-primary/30 bg-primary/10 flex items-center justify-center flex-shrink-0">
                        <span class="material-symbols-outlined text-primary text-5xl">groups</span>
                    </div>
                <?php endif; ?>

                <div>
                    <!-- Categoría + certificaciones -->
                    <div class="flex flex-wrap items-center gap-2 mb-3">
                        <?php if ( $data['categoria'] ) : ?>
                            <span class="inline-block px-3 py-1 bg-primary text-white text-xs font-bold rounded-full uppercase tracking-wider">
                                <?php echo esc_html( $data['categoria'] ); ?>
                            </span>
                        <?php endif; ?>
                        <?php if ( $data['certificaciones'] ) : ?>
                            <?php foreach ( explode( '·', $data['certificaciones'] ) as $cert ) :
                                $cert = trim( $cert );
                                if ( ! $cert ) continue;
                            ?>
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 bg-white/15 text-white/90 text-xs font-semibold rounded-full border border-white/20">
                                    <span class="material-symbols-outlined text-[12px] text-primary">verified</span>
                                    <?php echo esc_html( $cert ); ?>
                                </span>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

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

                    <!-- Redes sociales -->
                    <?php if ( $data['instagram'] || $data['facebook'] ) : ?>
                        <div class="flex items-center gap-3 mt-3">
                            <?php if ( $data['instagram'] ) : ?>
                                <a href="<?php echo esc_url( $data['instagram'] ); ?>" target="_blank" rel="noopener noreferrer"
                                   class="flex items-center gap-1.5 text-slate-300 hover:text-primary transition-colors text-sm">
                                    <span class="material-symbols-outlined text-[18px]">photo_camera</span>
                                    Instagram
                                </a>
                            <?php endif; ?>
                            <?php if ( $data['facebook'] ) : ?>
                                <a href="<?php echo esc_url( $data['facebook'] ); ?>" target="_blank" rel="noopener noreferrer"
                                   class="flex items-center gap-1.5 text-slate-300 hover:text-primary transition-colors text-sm">
                                    <span class="material-symbols-outlined text-[18px]">thumb_up</span>
                                    Facebook
                                </a>
                            <?php endif; ?>
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
            <div class="flex flex-wrap gap-8 mt-8">
                <div class="text-center">
                    <div class="text-3xl font-black text-primary"><?php echo count( $vendors ); ?></div>
                    <div class="text-slate-400 text-sm"><?php esc_html_e( 'Tiendas', 'amazonia-theme' ); ?></div>
                </div>
                <?php if ( $data['num_familias'] ) : ?>
                    <div class="text-center">
                        <div class="text-3xl font-black text-primary"><?php echo esc_html( $data['num_familias'] ); ?></div>
                        <div class="text-slate-400 text-sm"><?php esc_html_e( 'Familias', 'amazonia-theme' ); ?></div>
                    </div>
                <?php endif; ?>
                <?php if ( $data['fundacion'] ) : ?>
                    <div class="text-center">
                        <div class="text-3xl font-black text-primary"><?php echo esc_html( $data['fundacion'] ); ?></div>
                        <div class="text-slate-400 text-sm"><?php esc_html_e( 'Fundación', 'amazonia-theme' ); ?></div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <div class="max-w-5xl mx-auto px-6 py-12 space-y-12">

        <!-- ── Valores ───────────────────────────────────────────── -->
        <?php if ( ! empty( $data['valores'] ) ) : ?>
            <section>
                <div class="flex flex-wrap gap-3">
                    <?php foreach ( $data['valores'] as $valor ) : ?>
                        <div class="inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-slate-100 rounded-2xl shadow-sm text-sm font-semibold text-slate-700">
                            <span class="material-symbols-outlined text-primary text-[20px]"><?php echo esc_html( $valor['icono'] ?? 'eco' ); ?></span>
                            <?php echo esc_html( $valor['texto'] ); ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>

        <!-- ── Historia ──────────────────────────────────────────── -->
        <?php if ( $data['historia'] ) : ?>
            <section class="bg-white rounded-2xl border border-slate-100 p-8 shadow-sm">
                <h2 class="text-2xl font-black text-slate-900 mb-4 flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary">auto_stories</span>
                    <?php esc_html_e( 'Nuestra historia', 'amazonia-theme' ); ?>
                </h2>
                <div class="text-slate-600 leading-relaxed whitespace-pre-line">
                    <?php echo esc_html( $data['historia'] ); ?>
                </div>
            </section>
        <?php endif; ?>

        <!-- ── Galería ───────────────────────────────────────────── -->
        <?php if ( ! empty( $data['galeria_ids'] ) ) :
            $gallery_images = [];
            foreach ( $data['galeria_ids'] as $att_id ) {
                $large  = wp_get_attachment_image_url( $att_id, 'large' );
                $medium = wp_get_attachment_image_url( $att_id, 'medium' );
                if ( $large ) $gallery_images[] = [ 'large' => $large, 'medium' => $medium ?: $large, 'alt' => get_post_field( 'post_excerpt', $att_id ) ?: $data['nombre'] ];
            }
        ?>
            <?php if ( ! empty( $gallery_images ) ) : ?>
            <section>
                <h2 class="text-2xl font-black text-slate-900 mb-6 flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary">photo_library</span>
                    <?php esc_html_e( 'Galería', 'amazonia-theme' ); ?>
                </h2>
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
                    <?php foreach ( $gallery_images as $img ) : ?>
                        <button class="ca-gallery-photo block aspect-square rounded-xl overflow-hidden border border-slate-100 hover:border-primary/30 transition-all shadow-sm hover:shadow-md focus:outline-none focus:ring-2 focus:ring-primary"
                                data-full="<?php echo esc_attr( $img['large'] ); ?>">
                            <img src="<?php echo esc_url( $img['medium'] ); ?>"
                                 alt="<?php echo esc_attr( $img['alt'] ); ?>"
                                 class="w-full h-full object-cover transition-transform hover:scale-105"
                                 loading="lazy" />
                        </button>
                    <?php endforeach; ?>
                </div>
            </section>

            <!-- Lightbox -->
            <dialog id="ca-lightbox" style="padding:0;background:transparent;border:none;max-width:90vw;max-height:90vh;">
                <div style="position:relative;display:inline-block;">
                    <img id="ca-lightbox-img" src="" alt="" style="max-width:90vw;max-height:85vh;display:block;border-radius:12px;box-shadow:0 25px 50px rgba(0,0,0,.5);" />
                    <button onclick="document.getElementById('ca-lightbox').close()"
                            style="position:absolute;top:8px;right:8px;background:rgba(15,23,42,.7);border:none;border-radius:50%;width:36px;height:36px;cursor:pointer;display:flex;align-items:center;justify-content:center;color:#fff;">
                        <span class="material-symbols-outlined" style="font-size:20px;line-height:1;">close</span>
                    </button>
                </div>
            </dialog>
            <script>
            (function(){
                document.querySelectorAll('.ca-gallery-photo').forEach(function(btn){
                    btn.addEventListener('click', function(){
                        document.getElementById('ca-lightbox-img').src = this.dataset.full;
                        document.getElementById('ca-lightbox').showModal();
                    });
                });
                var lb = document.getElementById('ca-lightbox');
                if(lb) lb.addEventListener('click', function(e){ if(e.target===this) this.close(); });
                document.addEventListener('keydown', function(e){ if(e.key==='Escape' && lb) lb.close(); });
            })();
            </script>
            <?php endif; ?>
        <?php endif; ?>

        <!-- ── Video ─────────────────────────────────────────────── -->
        <?php if ( $embed_url ) : ?>
            <section>
                <h2 class="text-2xl font-black text-slate-900 mb-6 flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary">play_circle</span>
                    <?php esc_html_e( 'Video de la comunidad', 'amazonia-theme' ); ?>
                </h2>
                <div class="rounded-2xl overflow-hidden shadow-lg" style="aspect-ratio:16/9;background:#0f172a;">
                    <iframe src="<?php echo esc_url( $embed_url ); ?>"
                            style="width:100%;height:100%;display:block;"
                            frameborder="0"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                            allowfullscreen
                            loading="lazy"
                            title="<?php echo esc_attr( $data['nombre'] ); ?>">
                    </iframe>
                </div>
            </section>
        <?php endif; ?>

        <!-- ── Tiendas ───────────────────────────────────────────── -->
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
