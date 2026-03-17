<?php
/**
 * The front page template file
 */

get_header();
?>

<main id="primary" class="site-main">

    <!-- Hero Section -->
    <section class="max-w-[1440px] mx-auto w-full px-4 md:px-10 lg:px-20 py-6">
        <div class="mb-10 rounded-2xl overflow-hidden bg-slate-900 relative min-h-[450px] flex items-end shadow-2xl">
            <div class="absolute inset-0 z-0">
                <img alt="Manos indígenas tejiendo fibras naturales en la selva" class="w-full h-full object-cover opacity-80" src="https://lh3.googleusercontent.com/aida-public/AB6AXuAq_j6ErCDkwbEdYp9Q1sGPnpTM3ydBN291StZX9oPUDId6IhpaKQxrBm2MG2ziGJQLy2iwxlJfh8LSgZ-WeJf-h5iA-cd6cphz2ls2ogwXffrZdcggDaQtMwR8QvKYE5ceOs71iYlTuClyoyRgUJqItga_Fc8O0pyzl67oeLFu0LUcWa4Mn2ciQgsWs-nXKDFnpC3os_LNzu6mbhHGTEopssvW6y9T6QhDNtJXdk0gBNWYw1UZt1NXc3Hfh-HupmO4CLnDmhYtKhs"/>
                <div class="absolute inset-0 bg-gradient-to-t from-slate-900/95 via-slate-900/40 to-transparent"></div>
            </div>
            <div class="relative z-10 p-8 md:p-16 max-w-3xl">
                <span class="inline-block px-4 py-1.5 bg-primary text-white text-xs font-bold rounded-full mb-6 uppercase tracking-widest shadow-lg">Saberes Ancestrales</span>
                <h1 class="text-white text-4xl md:text-6xl font-black leading-tight mb-4">Arte y Productos de la Selva</h1>
                <p class="text-slate-200 text-lg md:text-xl mb-8 leading-relaxed font-light">Directo desde las comunidades Indígenas y Afro-Amazónicas. Cada producto cuenta una historia de equilibrio y respeto por la naturaleza.</p>
                
                <div class="flex gap-4">
                    <a href="<?php echo class_exists('WooCommerce') ? esc_url( wc_get_page_permalink( 'shop' ) ) : '#'; ?>" class="bg-primary hover:bg-primary/90 text-white font-bold py-4 px-10 rounded-full transition-all transform hover:scale-105 shadow-xl inline-block no-underline">
                        Ir a la Tienda
                    </a>
                </div>
            </div>
        </div>
    </section>

    <?php
    if ( have_posts() ) :
        while ( have_posts() ) :
            the_post();
            ?>
            <div class="entry-content" style="margin-top: 15px; line-height: 1.6;">
                <?php the_content(); ?>
            </div>
            <?php
        endwhile;
    endif;
    ?>

</main>

<?php
get_footer();
