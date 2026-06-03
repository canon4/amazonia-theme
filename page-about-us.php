<?php
/**
 * Template Name: About Us
 * Description: Plantilla para la página de "Sobre Nosotros".
 */

get_header();
?>

<main id="primary" class="site-main bg-slate-50 dark:bg-slate-900 pb-20">

    <!-- Hero Section -->
    <section class="max-w-[1440px] mx-auto w-full px-4 md:px-10 lg:px-20 py-6">
        <div class="mb-16 rounded-2xl overflow-hidden bg-slate-900 relative min-h-[500px] flex items-end shadow-2xl group">
            <div class="absolute inset-0 z-0">
                <img alt="The lush Amazon rainforest at golden hour" class="w-full h-full object-cover opacity-75 group-hover:opacity-90 transition-opacity duration-700 ease-in-out group-hover:scale-105 transform" src="<?php echo get_template_directory_uri(); ?>/assets/img/amazon_rainforest_hero.png" />
                <div class="absolute inset-0 bg-gradient-to-t from-slate-900 via-slate-900/60 to-transparent"></div>
            </div>
            <div class="relative z-10 p-8 md:p-16 max-w-4xl">
                <span class="inline-block px-4 py-1.5 bg-primary text-white text-xs font-bold rounded-full mb-6 uppercase tracking-widest shadow-lg">Nuestra Esencia</span>
                <h1 class="text-white text-5xl md:text-7xl font-black leading-tight mb-6">El corazón de la Amazonía en cada detalle.</h1>
                <p class="text-slate-200 text-lg md:text-xl md:w-3/4 mb-0 leading-relaxed font-light">Somos un puente entre la sabiduría ancestral de nuestras comunidades y el resto del mundo, protegiendo nuestro ecosistema a través del comercio justo.</p>
            </div>
        </div>
    </section>

    <!-- Story & Mission Section -->
    <section class="max-w-7xl mx-auto px-6 lg:px-20 py-16 mb-10">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">
            <div class="relative rounded-2xl overflow-hidden shadow-2xl h-full min-h-[400px] lg:min-h-[600px] group">
                <img src="<?php echo get_template_directory_uri(); ?>/assets/img/indigenous_crafts_community.png" class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-transform duration-700 ease-in-out" alt="Artesana indígena tejiendo canasta">
                <div class="absolute inset-0 bg-forest-green/20"></div>
                <div class="absolute bottom-6 left-6 right-6 bg-white/90 backdrop-blur-sm p-6 rounded-xl border border-white/20">
                    <p class="text-slate-800 italic font-medium">"Tejemos nuestras historias y el respeto por nuestra madre selva en cada fibra."</p>
                </div>
            </div>
            
            <div>
                <h2 class="text-sm font-bold text-primary uppercase tracking-[0.2em] mb-4">Nuestra Historia</h2>
                <h3 class="text-3xl md:text-4xl font-extrabold text-forest-green dark:text-white mb-8 leading-tight">Preservando un Legado Milenario</h3>
                <div class="space-y-6 text-slate-600 dark:text-slate-400 text-lg leading-relaxed">
                    <p>Nacimos en el corazón de la cuenca amazónica con un propósito claro: revalorizar los conocimientos de los pueblos originarios y las comunidades locales.</p>
                    <p>Cada producto que encuentras en nuestra plataforma no es solo un objeto, es una obra de arte que representa días de dedicación, materiales 100% naturales y prácticas completamente sostenibles que protegen la biodiversidad.</p>
                </div>
                
                <div class="mt-12 grid grid-cols-1 sm:grid-cols-2 gap-8">
                    <div class="p-6 bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 transition hover:shadow-md">
                        <span class="material-symbols-outlined text-primary text-4xl mb-4">compost</span>
                        <h4 class="text-xl font-bold text-forest-green dark:text-white mb-2">100% Sostenible</h4>
                        <p class="text-slate-500 dark:text-slate-400 text-sm">Respetamos los ciclos naturales de recolección.</p>
                    </div>
                    <div class="p-6 bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 transition hover:shadow-md">
                        <span class="material-symbols-outlined text-primary text-4xl mb-4">handshake</span>
                        <h4 class="text-xl font-bold text-forest-green dark:text-white mb-2">Comercio Justo</h4>
                        <p class="text-slate-500 dark:text-slate-400 text-sm">Impacto directo en más de 20 comunidades aliadas.</p>
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
                <div class="pt-8 md:pt-0">
                    <div class="text-6xl font-black text-white mb-2">+50</div>
                    <div class="text-xs uppercase font-bold tracking-widest text-green-200">Familias Beneficiadas</div>
                </div>
                <div class="pt-8 md:pt-0">
                    <div class="text-6xl font-black text-white mb-2">100%</div>
                    <div class="text-xs uppercase font-bold tracking-widest text-green-200">Materiales Orgánicos</div>
                </div>
                <div class="pt-8 md:pt-0">
                    <div class="text-6xl font-black text-white mb-2">+10K</div>
                    <div class="text-xs uppercase font-bold tracking-widest text-green-200">Hectáreas Protegidas</div>
                </div>
            </div>
        </div>
    </section>

</main>

<?php
get_footer();
