<aside class="w-full lg:w-64 flex-shrink-0 space-y-8">
    <div>
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-bold text-lg flex items-center gap-2">
                <span class="material-symbols-outlined text-primary">filter_list</span> Filtros
            </h3>
            <button class="text-xs text-primary font-semibold hover:underline">Limpiar</button>
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
                        'hide_empty' => true, // Ocultar las que no tienen productos
                    ) );

                    if ( ! empty( $product_categories ) && ! is_wp_error( $product_categories ) ) :
                        foreach ( $product_categories as $category ) :
                            $term_link = get_term_link( $category );
                            $is_active = is_product_category( $category->slug );
                            ?>
                            <a href="<?php echo esc_url( $term_link ); ?>" class="flex items-center gap-2 cursor-pointer group">
                                <input <?php echo $is_active ? 'checked' : ''; ?> class="rounded text-primary focus:ring-primary h-4 w-4 bg-primary/5 border-primary/20" type="checkbox" onclick="window.location.href='<?php echo esc_url( $term_link ); ?>'"/>
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
                <div class="space-y-2">
                    <label class="flex items-center gap-2 cursor-pointer group">
                        <input class="text-primary focus:ring-primary h-4 w-4 bg-primary/5 border-primary/20" name="price" type="radio"/>
                        <span class="text-sm group-hover:text-primary transition-colors">Menos de $50</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer group">
                        <input class="text-primary focus:ring-primary h-4 w-4 bg-primary/5 border-primary/20" name="price" type="radio"/>
                        <span class="text-sm group-hover:text-primary transition-colors">$50 - $150</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer group">
                        <input class="text-primary focus:ring-primary h-4 w-4 bg-primary/5 border-primary/20" name="price" type="radio"/>
                        <span class="text-sm group-hover:text-primary transition-colors">Más de $150</span>
                    </label>
                </div>
            </div>
            <!-- Community Filter -->
            <div class="border-b border-primary/10 pb-4">
                <h4 class="font-semibold text-sm mb-3 flex items-center gap-2">
                    <span class="material-symbols-outlined text-sm">groups</span> Comunidad
                </h4>
                <div class="space-y-2">
                    <?php
                    // Asumimos que has creado un atributo en WooCommerce llamado "Comunidad" (slug: pa_comunidad)
                    $comunidad_terms = get_terms( array(
                        'taxonomy'   => 'pa_comunidad',
                        'hide_empty' => true,
                    ) );

                    if ( ! empty( $comunidad_terms ) && ! is_wp_error( $comunidad_terms ) ) :
                        foreach ( $comunidad_terms as $term ) :
                            $term_link = get_term_link( $term );
                            // Revisar si este término está activo en la URL (útil si hay navegación por atributos)
                            $is_active = is_tax( 'pa_comunidad', $term->slug );
                            ?>
                            <a href="<?php echo esc_url( $term_link ); ?>" class="flex items-center gap-2 cursor-pointer group">
                                <input <?php echo $is_active ? 'checked' : ''; ?> class="rounded text-primary focus:ring-primary h-4 w-4 bg-primary/5 border-primary/20" type="checkbox" onclick="window.location.href='<?php echo esc_url( $term_link ); ?>'"/>
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
            <!-- Values Filter -->
            <div class="pb-4">
                <h4 class="font-semibold text-sm mb-3 flex items-center gap-2">
                    <span class="material-symbols-outlined text-sm">verified</span> Atributos
                </h4>
                <div class="space-y-2">
                    <?php
                    // Asumimos que has creado un atributo en WooCommerce llamado "Atributos" o "Valores" (slug: pa_atributos)
                    $atributos_terms = get_terms( array(
                        'taxonomy'   => 'pa_atributos',
                        'hide_empty' => true,
                    ) );

                    if ( ! empty( $atributos_terms ) && ! is_wp_error( $atributos_terms ) ) :
                        foreach ( $atributos_terms as $term ) :
                            $term_link = get_term_link( $term );
                            $is_active = is_tax( 'pa_atributos', $term->slug );
                            
                            // Determinamos un ícono básico dependiedo del nombre o uno por defecto
                            $icon = 'verified';
                            if ( strtolower($term->name) == 'orgánico' || strtolower($term->name) == 'organico' ) {
                                $icon = 'energy_savings_leaf';
                            } elseif ( strtolower($term->name) == 'artesanal' ) {
                                $icon = 'front_hand';
                            }
                            ?>
                            <a href="<?php echo esc_url( $term_link ); ?>" class="flex items-center gap-2 cursor-pointer group">
                                <input <?php echo $is_active ? 'checked' : ''; ?> class="rounded text-primary focus:ring-primary h-4 w-4 bg-primary/5 border-primary/20" type="checkbox" onclick="window.location.href='<?php echo esc_url( $term_link ); ?>'"/>
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
