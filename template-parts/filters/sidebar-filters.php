<?php
$shop_url          = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/' );
$currency_symbol   = function_exists( 'get_woocommerce_currency_symbol' ) ? get_woocommerce_currency_symbol() : '$';
$current_min_price = isset( $_GET['min_price'] ) ? wc_clean( wp_unslash( $_GET['min_price'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$current_max_price = isset( $_GET['max_price'] ) ? wc_clean( wp_unslash( $_GET['max_price'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$has_price_filter  = ( '' !== $current_min_price || '' !== $current_max_price );

// "Limpiar" quita todos los filtros pero conserva el término de búsqueda.
$clear_url = $shop_url;
if ( get_search_query() ) {
    $clear_url = add_query_arg( 's', get_search_query(), $clear_url );
}
?>
<aside class="w-full lg:w-64 flex-shrink-0 space-y-8">
    <div>
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-bold text-lg flex items-center gap-2">
                <span class="material-symbols-outlined text-primary">filter_list</span> Filtros
            </h3>
            <a href="<?php echo esc_url( $clear_url ); ?>" class="text-xs text-primary font-semibold hover:underline no-underline">Limpiar</a>
        </div>
        <div class="space-y-6">

            <!-- Category Filter -->
            <div class="border-b border-primary/10 pb-4">
                <h4 class="font-semibold text-sm mb-3 flex items-center gap-2">
                    <span class="material-symbols-outlined text-sm">category</span> Categoría
                </h4>
                <div class="space-y-2">
                    <?php
                    $product_categories = get_terms( array(
                        'taxonomy'   => 'product_cat',
                        'hide_empty' => true,
                    ) );

                    if ( ! empty( $product_categories ) && ! is_wp_error( $product_categories ) ) :
                        foreach ( $product_categories as $category ) :
                            $term_link = get_term_link( $category );
                            $is_active = is_product_category( $category->slug );
                            ?>
                            <a href="<?php echo esc_url( $term_link ); ?>" class="flex items-center gap-2 cursor-pointer group">
                                <input <?php echo $is_active ? 'checked' : ''; ?> class="rounded text-primary focus:ring-primary h-4 w-4 bg-primary/5 border-primary/20" type="checkbox" onclick="window.location.href=this.parentElement.href;return false;" />
                                <span class="text-sm group-hover:text-primary transition-colors <?php echo $is_active ? 'text-primary font-medium' : ''; ?>">
                                    <?php echo esc_html( $category->name ); ?>
                                </span>
                            </a>
                            <?php
                        endforeach;
                    else :
                        echo '<p class="text-sm text-slate-500">No hay categorías disponibles.</p>';
                    endif;
                    ?>
                </div>
            </div>

            <!-- Price Filter -->
            <div class="border-b border-primary/10 pb-4">
                <h4 class="font-semibold text-sm mb-3 flex items-center gap-2">
                    <span class="material-symbols-outlined text-sm">payments</span> Precio
                </h4>
                <form method="get" action="<?php echo esc_url( $shop_url ); ?>" class="space-y-3">
                    <?php
                    // Preserva el término de búsqueda y el ordenamiento al aplicar el precio.
                    if ( function_exists( 'wc_query_string_form_fields' ) ) {
                        wc_query_string_form_fields( null, array( 'min_price', 'max_price', 'paged', 'submit' ) );
                    }
                    ?>
                    <div class="flex items-center gap-2">
                        <div class="relative flex-1">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs text-slate-400 pointer-events-none"><?php echo esc_html( $currency_symbol ); ?></span>
                            <input
                                type="number" name="min_price"
                                value="<?php echo esc_attr( $current_min_price ); ?>"
                                min="0" step="any" inputmode="decimal" placeholder="Mín"
                                aria-label="Precio mínimo"
                                class="w-full pl-6 pr-2 py-2 text-sm rounded-full bg-primary/5 border border-primary/20 text-slate-800 dark:text-slate-100 placeholder:text-slate-400 focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-colors"
                            />
                        </div>
                        <span class="text-slate-400 flex-shrink-0">—</span>
                        <div class="relative flex-1">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs text-slate-400 pointer-events-none"><?php echo esc_html( $currency_symbol ); ?></span>
                            <input
                                type="number" name="max_price"
                                value="<?php echo esc_attr( $current_max_price ); ?>"
                                min="0" step="any" inputmode="decimal" placeholder="Máx"
                                aria-label="Precio máximo"
                                class="w-full pl-6 pr-2 py-2 text-sm rounded-full bg-primary/5 border border-primary/20 text-slate-800 dark:text-slate-100 placeholder:text-slate-400 focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-colors"
                            />
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="submit" class="flex-1 bg-primary hover:bg-primary/90 text-white text-xs font-bold py-2 rounded-full transition-colors border-none cursor-pointer">
                            Aplicar
                        </button>
                        <?php if ( $has_price_filter ) : ?>
                            <a href="<?php echo esc_url( remove_query_arg( array( 'min_price', 'max_price', 'paged' ) ) ); ?>" class="text-xs text-slate-500 hover:text-primary font-semibold px-2 no-underline whitespace-nowrap">Quitar</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <!-- Community Filter -->
            <div class="border-b border-primary/10 pb-4">
                <h4 class="font-semibold text-sm mb-3 flex items-center gap-2">
                    <span class="material-symbols-outlined text-sm">groups</span> Comunidad
                </h4>
                <div class="space-y-2">
                    <?php
                    $comunidad_terms = get_terms( array(
                        'taxonomy'   => 'pa_comunidad',
                        'hide_empty' => true,
                    ) );

                    if ( ! empty( $comunidad_terms ) && ! is_wp_error( $comunidad_terms ) ) :
                        foreach ( $comunidad_terms as $term ) :
                            $term_link = get_term_link( $term );
                            $is_active = is_tax( 'pa_comunidad', $term->slug );
                            ?>
                            <a href="<?php echo esc_url( $term_link ); ?>" class="flex items-center gap-2 cursor-pointer group">
                                <input <?php echo $is_active ? 'checked' : ''; ?> class="rounded text-primary focus:ring-primary h-4 w-4 bg-primary/5 border-primary/20" type="checkbox" onclick="window.location.href=this.parentElement.href;return false;" />
                                <span class="text-sm group-hover:text-primary transition-colors <?php echo $is_active ? 'text-primary font-medium' : ''; ?>">
                                    <?php echo esc_html( $term->name ); ?>
                                </span>
                            </a>
                            <?php
                        endforeach;
                    else :
                        echo '<p class="text-sm text-slate-500">No hay comunidades configuradas.</p>';
                    endif;
                    ?>
                </div>
            </div>

            <!-- Atributos Filter -->
            <div class="pb-4">
                <h4 class="font-semibold text-sm mb-3 flex items-center gap-2">
                    <span class="material-symbols-outlined text-sm">verified</span> Atributos
                </h4>
                <div class="space-y-2">
                    <?php
                    $atributos_terms = get_terms( array(
                        'taxonomy'   => 'pa_atributos',
                        'hide_empty' => true,
                    ) );

                    if ( ! empty( $atributos_terms ) && ! is_wp_error( $atributos_terms ) ) :
                        foreach ( $atributos_terms as $term ) :
                            $term_link = get_term_link( $term );
                            $is_active = is_tax( 'pa_atributos', $term->slug );
                            $icon      = 'verified';
                            if ( in_array( strtolower( $term->name ), array( 'orgánico', 'organico' ), true ) ) {
                                $icon = 'energy_savings_leaf';
                            } elseif ( 'artesanal' === strtolower( $term->name ) ) {
                                $icon = 'front_hand';
                            }
                            ?>
                            <a href="<?php echo esc_url( $term_link ); ?>" class="flex items-center gap-2 cursor-pointer group">
                                <input <?php echo $is_active ? 'checked' : ''; ?> class="rounded text-primary focus:ring-primary h-4 w-4 bg-primary/5 border-primary/20" type="checkbox" onclick="window.location.href=this.parentElement.href;return false;" />
                                <div class="flex items-center gap-1">
                                    <span class="material-symbols-outlined text-[16px] text-primary"><?php echo esc_attr( $icon ); ?></span>
                                    <span class="text-sm group-hover:text-primary transition-colors <?php echo $is_active ? 'text-primary font-medium' : ''; ?>">
                                        <?php echo esc_html( $term->name ); ?>
                                    </span>
                                </div>
                            </a>
                            <?php
                        endforeach;
                    else :
                        echo '<p class="text-sm text-slate-500">No hay atributos configurados.</p>';
                    endif;
                    ?>
                </div>
            </div>

        </div>
    </div>
</aside>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const sidebar = document.querySelector('aside.lg\\:w-64');
    const wcfmMap = document.getElementById('wcfmmp-store-list-map');
    const searchForm = document.querySelector('form.wcfmmp-store-search-form');

    if (sidebar && wcfmMap) {
        if (searchForm) searchForm.style.display = 'none';

        const mapCard = document.createElement('div');
        mapCard.className = "mt-8";
        mapCard.innerHTML = `
            <div class="border-b border-primary/10 pb-4 mb-4">
                <h4 class="font-bold text-sm mb-3 text-slate-900 dark:text-white flex items-center gap-2">
                    <span class="material-symbols-outlined text-sm">location_on</span> Community Location
                </h4>
            </div>
            <div class="relative rounded-2xl overflow-hidden shadow-sm h-[200px] w-full group">
                <div id="sidebar-map-container" class="absolute inset-0 w-full h-full bg-slate-200"></div>
                <div class="absolute inset-0 pointer-events-none" style="box-shadow:inset 0 0 20px rgba(0,0,0,0.05)"></div>
                <style>
                    #sidebar-map-container .wcfmmp-store-list-map { height:100%!important; filter:grayscale(1) opacity(.8) contrast(1.2); transition:filter .3s }
                    #sidebar-map-container:hover .wcfmmp-store-list-map { filter:grayscale(.5) opacity(1) contrast(1) }
                    #sidebar-map-container .leaflet-control-container,
                    #sidebar-map-container .leaflet-top,
                    #sidebar-map-container .leaflet-bottom { display:none!important }
                </style>
            </div>
        `;
        sidebar.appendChild(mapCard);
        document.getElementById('sidebar-map-container').appendChild(wcfmMap);
        setTimeout(function() {
            window.dispatchEvent(new Event('resize'));
            if (window.wcfmmp_store_map) {
                try { window.wcfmmp_store_map.invalidateSize(); } catch(e) {}
            }
        }, 500);
    }
});
</script>