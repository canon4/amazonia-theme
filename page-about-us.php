<?php
/**
 * Template Name: About Us
 * Description: Plantilla para la página de "Sobre Nosotros".
 *
 * Textos e imágenes editables en WP Admin → Amazonia → Contenido del sitio
 * (inc/site-content.php). Si un campo está vacío se usa el original del tema.
 */

get_header();
?>

<main id="primary" class="site-main bg-slate-50 dark:bg-slate-900 pb-20">

    <!-- Hero Section -->
    <section class="max-w-[1440px] mx-auto w-full px-4 md:px-10 lg:px-20 py-6">
        <div class="mb-16 rounded-2xl overflow-hidden bg-slate-900 relative min-h-[500px] flex items-end shadow-2xl group">
            <div class="absolute inset-0 z-0">
                <img <?php echo amazonia_content_img_attrs( 'img_about_hero', 'assets/img/amazon_rainforest_hero.png', 'images.iconos.rainforest' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escapado dentro del helper. ?> alt="The lush Amazon rainforest at golden hour" class="w-full h-full object-cover opacity-75 group-hover:opacity-90 transition-opacity duration-700 ease-in-out group-hover:scale-105 transform" fetchpriority="high" width="1920" height="1080" />
                <div class="absolute inset-0 bg-gradient-to-t from-slate-900 via-slate-900/60 to-transparent"></div>
            </div>
            <div class="relative z-10 p-8 md:p-16 max-w-4xl">
                <span class="inline-block px-4 py-1.5 bg-primary text-white text-xs font-bold rounded-full mb-6 uppercase tracking-widest shadow-lg"><?php echo esc_html( amazonia_content( 'about_badge' ) ); ?></span>
                <h1 class="text-white text-5xl md:text-7xl font-black leading-tight mb-6"><?php echo esc_html( amazonia_content( 'about_title' ) ); ?></h1>
                <p class="text-slate-200 text-lg md:text-xl md:w-3/4 mb-0 leading-relaxed font-light"><?php echo esc_html( amazonia_content( 'about_text' ) ); ?></p>
            </div>
        </div>
    </section>

    <!-- Story & Mission Section -->
    <section class="max-w-7xl mx-auto px-6 lg:px-20 py-16 mb-10">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">
            <div class="relative rounded-2xl overflow-hidden shadow-2xl h-full min-h-[400px] lg:min-h-[600px] group">
                <img <?php echo amazonia_content_img_attrs( 'img_about_historia', 'assets/img/indigenous_crafts_community.png', 'images.iconos.crafts' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escapado dentro del helper. ?> class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-transform duration-700 ease-in-out" alt="Artesana indígena tejiendo canasta" loading="lazy" width="800" height="600">
                <div class="absolute inset-0 bg-forest-green/20"></div>
                <div class="absolute bottom-6 left-6 right-6 bg-white/90 backdrop-blur-sm p-6 rounded-xl border border-white/20">
                    <p class="text-slate-800 italic font-medium">"<?php echo esc_html( amazonia_content( 'about_quote' ) ); ?>"</p>
                </div>
            </div>

            <div>
                <h2 class="text-sm font-bold text-primary uppercase tracking-[0.2em] mb-4"><?php echo esc_html( amazonia_content( 'about_story_eyebrow' ) ); ?></h2>
                <h3 class="text-3xl md:text-4xl font-extrabold text-forest-green dark:text-white mb-8 leading-tight"><?php echo esc_html( amazonia_content( 'about_story_title' ) ); ?></h3>
                <div class="space-y-6 text-slate-600 dark:text-slate-400 text-lg leading-relaxed">
                    <p><?php echo esc_html( amazonia_content( 'about_story_p1' ) ); ?></p>
                    <p><?php echo esc_html( amazonia_content( 'about_story_p2' ) ); ?></p>
                </div>

                <div class="mt-12 grid grid-cols-1 sm:grid-cols-2 gap-8">
                    <div class="p-6 bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 transition hover:shadow-md">
                        <span class="material-symbols-outlined text-primary text-4xl mb-4">compost</span>
                        <h4 class="text-xl font-bold text-forest-green dark:text-white mb-2"><?php echo esc_html( amazonia_content( 'about_card1_title' ) ); ?></h4>
                        <p class="text-slate-500 dark:text-slate-400 text-sm"><?php echo esc_html( amazonia_content( 'about_card1_text' ) ); ?></p>
                    </div>
                    <div class="p-6 bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 transition hover:shadow-md">
                        <span class="material-symbols-outlined text-primary text-4xl mb-4">handshake</span>
                        <h4 class="text-xl font-bold text-forest-green dark:text-white mb-2"><?php echo esc_html( amazonia_content( 'about_card2_title' ) ); ?></h4>
                        <p class="text-slate-500 dark:text-slate-400 text-sm"><?php echo esc_html( amazonia_content( 'about_card2_text' ) ); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Banner -->
    <section class="bg-green-900 py-20 text-white mt-10 rounded-3xl mx-4 md:mx-10 lg:mx-20 shadow-2xl relative overflow-hidden">
        <div class="absolute -top-24 -right-24 w-64 h-64 bg-white/10 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-24 -left-24 w-64 h-64 bg-white/10 rounded-full blur-3xl"></div>
        <div class="max-w-7xl mx-auto px-6 relative z-10">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-12 text-center divide-y md:divide-y-0 md:divide-x divide-white/20">
                <?php foreach ( array( 1, 2, 3 ) as $n ) : ?>
                <div class="pt-8 md:pt-0">
                    <div class="text-6xl font-black text-white mb-2"><?php echo esc_html( amazonia_content( 'about_stat' . $n . '_n' ) ); ?></div>
                    <div class="text-xs uppercase font-bold tracking-widest text-green-200"><?php echo esc_html( amazonia_content( 'about_stat' . $n . '_l' ) ); ?></div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

</main>

<?php
get_footer();
