    </div><!-- #content -->

    <footer class="bg-forest-green text-white py-8 mt-8">
        <div class="max-w-7xl mx-auto px-6 lg:px-20 grid grid-cols-1 md:grid-cols-4 gap-8">
            <div class="col-span-1 md:col-span-2">
                <div class="flex items-center gap-3 mb-4">
                    <span class="material-symbols-outlined text-primary text-4xl">eco</span>
                    <h2 class="text-2xl font-black tracking-tight uppercase"><?php bloginfo('name'); ?></h2>
                </div>
                <p class="text-slate-400 max-w-md leading-relaxed mb-4">
                    Conectamos la sabiduría ancestral de las comunidades amazónicas con el bienestar global a través de productos biológicos de impacto positivo.
                </p>
                <div class="flex gap-4">
                    <a class="size-10 bg-white/10 rounded-full flex items-center justify-center hover:bg-primary transition-colors" href="javascript:void(0)">
                        <span class="material-symbols-outlined text-sm">public</span>
                    </a>
                    <a class="size-10 bg-white/10 rounded-full flex items-center justify-center hover:bg-primary transition-colors" href="javascript:void(0)">
                        <span class="material-symbols-outlined text-sm">mail</span>
                    </a>
                </div>
            </div>
            <div>
                <h6 class="font-bold mb-3 uppercase text-xs tracking-widest text-primary">Enlaces rápidos</h6>
                <ul class="space-y-2 text-slate-400 text-sm">
                    <li><a class="hover:text-white transition-colors" href="<?php echo esc_url( site_url( '/about-us' ) ); ?>">Nosotros</a></li>
                    <li><a class="hover:text-white transition-colors" href="javascript:void(0)">Informes de sostenibilidad</a></li>
                    <li><a class="hover:text-white transition-colors" href="javascript:void(0)">Mayoreo</a></li>
                    <li><a class="hover:text-white transition-colors" href="javascript:void(0)">Política de envíos</a></li>
                </ul>
            </div>
            <div>
                <h6 class="font-bold mb-3 uppercase text-xs tracking-widest text-primary">Boletín</h6>
                <p class="text-xs text-slate-400 mb-3">Recibe historias directamente desde la selva.</p>
                <div class="flex">
                    <input class="bg-white/5 border-white/10 rounded-l-full focus:ring-primary focus:border-primary text-sm w-full" placeholder="Email" type="email"/>
                    <button class="bg-primary px-4 rounded-r-full hover:bg-primary/90">
                        <span class="material-symbols-outlined">send</span>
                    </button>
                </div>
            </div>
        </div>
        <div class="max-w-7xl mx-auto px-6 lg:px-20 mt-6 pt-6 border-t border-white/5 text-center text-xs text-slate-500">
            &copy; <?php echo date('Y'); ?> <?php bloginfo('name'); ?>. Preservando el corazón del mundo.
        </div>
    </footer>
</div><!-- #page -->

<!-- Side Cart UI -->
<div id="side-cart" class="fixed inset-y-0 right-0 w-full md:w-[450px] bg-white/95 dark:bg-background-dark/95 backdrop-blur-3xl shadow-[0_0_60px_rgba(0,0,0,0.15)] z-[100] transform translate-x-full transition-transform duration-700 ease-[cubic-bezier(0.32,0.72,0,1)] flex flex-col border-l border-white/20">
    <div class="flex items-center justify-between p-8 border-b border-primary/10 bg-white/50 dark:bg-black/50">
        <div class="flex items-center gap-3">
            <span class="material-symbols-outlined text-primary text-[28px]">shopping_cart</span>
            <h3 class="text-lg font-black text-slate-900 dark:text-slate-100 m-0 uppercase tracking-widest">Tu Selección</h3>
        </div>
        <button id="side-cart-close" class="rounded-full w-10 h-10 flex items-center justify-center bg-primary/5 hover:bg-primary hover:text-white text-slate-500 transition-all group">
            <span class="material-symbols-outlined text-[20px] group-hover:rotate-90 transition-transform duration-500">close</span>
        </button>
    </div>
    
    <div class="side-cart-content flex-1 overflow-y-auto p-8 relative scrollbar-hide">
        <div class="widget_shopping_cart_content h-full">
            <?php if ( function_exists('woocommerce_mini_cart') ) woocommerce_mini_cart(); ?>
        </div>
    </div>
</div>

<div id="side-cart-overlay" class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-[90] opacity-0 pointer-events-none transition-opacity duration-700 ease-[cubic-bezier(0.32,0.72,0,1)]"></div>

<?php wp_footer(); ?>

</body>
</html>
