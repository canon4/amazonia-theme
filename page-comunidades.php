<?php
/**
 * Plantilla para la página "Comunidades" (slug: comunidades).
 *
 * Diseño minimalista y responsivo para el listado de comunidades amazónicas.
 * El contenido (shortcode [amazonia_communities]) se renderiza vía the_content().
 */

get_header();

// Conteo de comunidades publicadas para mostrarlo en el hero.
$comunidad_counts = wp_count_posts( 'comunidad' );
$comunidad_total  = $comunidad_counts ? (int) $comunidad_counts->publish : 0;
?>

<main id="primary" class="site-main bg-background-light dark:bg-background-dark pb-24">

    <!-- Hero -->
    <section class="relative overflow-hidden bg-forest-green">
        <!-- Glows decorativos -->
        <div class="absolute -top-24 -right-20 w-72 h-72 bg-primary/30 rounded-full blur-3xl pointer-events-none" aria-hidden="true"></div>
        <div class="absolute -bottom-32 -left-24 w-80 h-80 bg-green-400/20 rounded-full blur-3xl pointer-events-none" aria-hidden="true"></div>

        <div class="relative z-10 max-w-7xl mx-auto px-6 lg:px-20 py-16 md:py-20">
            <?php
            if ( function_exists( 'woocommerce_breadcrumb' ) ) {
                woocommerce_breadcrumb( array(
                    'delimiter'   => ' <span class="material-symbols-outlined text-[16px] text-green-200/60 align-middle">chevron_right</span> ',
                    'wrap_before' => '<nav class="woocommerce-breadcrumb flex items-center gap-1 text-green-200/80 font-medium text-sm mb-6">',
                    'wrap_after'  => '</nav>',
                ) );
            }
            ?>

            <span class="inline-flex items-center gap-2 px-4 py-1.5 bg-white/10 backdrop-blur-sm text-green-50 text-xs font-bold rounded-full mb-6 uppercase tracking-widest border border-white/15">
                <span class="material-symbols-outlined text-[16px]">groups</span>
                <?php esc_html_e( 'Comercio justo', 'amazonia-theme' ); ?>
            </span>

            <h1 class="text-white text-4xl md:text-6xl font-black leading-tight mb-5 max-w-3xl">
                <?php esc_html_e( 'Nuestras Comunidades', 'amazonia-theme' ); ?>
            </h1>

            <p class="text-green-100/90 text-lg md:text-xl max-w-2xl leading-relaxed font-light mb-0">
                <?php esc_html_e( 'Conoce a los pueblos que dan vida a cada producto. Detrás de cada artesanía hay una historia de tradición, territorio y respeto por la selva.', 'amazonia-theme' ); ?>
            </p>

            <?php if ( $comunidad_total > 0 ) : ?>
            <div class="mt-8 inline-flex items-center gap-2 text-green-200/80 text-sm font-medium">
                <span class="material-symbols-outlined text-[18px] text-green-300">location_on</span>
                <?php echo esc_html( sprintf(
                    /* translators: %d: número de comunidades */
                    _n( '%d comunidad aliada', '%d comunidades aliadas', $comunidad_total, 'amazonia-theme' ),
                    $comunidad_total
                ) ); ?>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Listado de comunidades -->
    <section class="max-w-7xl mx-auto px-6 lg:px-20 relative z-20 -mt-6">
        <div class="entry-content">
            <?php
            while ( have_posts() ) :
                the_post();
                the_content();
            endwhile;
            ?>
        </div>
    </section>

</main>

<?php
get_footer();