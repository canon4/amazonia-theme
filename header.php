<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="profile" href="https://gmpg.org/xfn/11">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="dns-prefetch" href="https://lh3.googleusercontent.com">
    <?php wp_head(); ?>
</head>
<body <?php body_class('bg-background-light dark:bg-background-dark text-slate-900 dark:text-slate-100'); ?>>
<?php wp_body_open(); ?>
<div id="page" class="site">

    <header id="masthead" class="site-header sticky top-0 z-50 bg-white/80 dark:bg-background-dark/80 backdrop-blur-md border-b border-primary/10 px-4 md:px-10 lg:px-20 py-3">
        <div class="max-w-[1440px] mx-auto flex items-center justify-between gap-8">
            <div class="flex items-center gap-8">
                <a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home" class="flex items-center gap-2 text-primary no-underline">
                    <span class="material-symbols-outlined text-3xl font-bold">eco</span>
                    <h2 class="text-slate-900 dark:text-slate-100 text-xl font-black leading-tight tracking-tight m-0"><?php bloginfo( 'name' ); ?></h2>
                </a>
                
                <nav id="site-navigation" class="hidden lg:flex items-center gap-8 flex-wrap">
                    <?php
                    // Display primary menu if it exists, otherwise show fallback Tailwind links
                    if ( has_nav_menu( 'menu-1' ) ) {
                        wp_nav_menu(
                            array(
                                'theme_location' => 'menu-1',
                                'menu_id'        => 'primary-menu',
                                'container'      => false,
                                'menu_class'     => 'flex items-center gap-8 list-none m-0 p-0',
                                'fallback_cb'    => false,
                            )
                        );
                    } else {
                        $shop_url = class_exists( 'WooCommerce' ) ? esc_url( wc_get_page_permalink( 'shop' ) ) : '#';
                        echo '<a class="text-slate-600 dark:text-slate-400 text-sm font-medium hover:text-primary transition-colors no-underline" href="' . $shop_url . '">Tienda</a>
                              <a class="text-slate-600 dark:text-slate-400 text-sm font-medium hover:text-primary transition-colors no-underline" href="#">Categorías</a>
                              <a class="text-slate-600 dark:text-slate-400 text-sm font-medium hover:text-primary transition-colors no-underline" href="' . esc_url( home_url( '/?page_id=53/' ) ) . '">Comunidades</a>
                              <a class="text-slate-600 dark:text-slate-400 text-sm font-medium hover:text-primary transition-colors no-underline" href="#">Impacto</a>';
                    }
                    ?>
                </nav>
            </div>
            
            <div class="flex flex-1 justify-end items-center gap-4">
                <label class="hidden md:flex flex-col min-w-40 h-10 max-w-md w-full m-0">
                    <div class="flex w-full flex-1 items-stretch rounded-full h-full bg-primary/5 border border-primary/10 overflow-hidden">
                        <div class="text-primary/60 flex items-center justify-center pl-4">
                            <span class="material-symbols-outlined">search</span>
                        </div>
                        <form role="search" method="get" class="search-form w-full flex items-center m-0" action="<?php echo esc_url( home_url( '/' ) ); ?>">
                            <input type="search" class="w-full bg-transparent border-none focus:ring-0 text-sm placeholder:text-primary/40 px-3 outline-none" placeholder="Buscar aceites, semillas, artesanías..." value="<?php echo get_search_query(); ?>" name="s" />
                        </form>
                    </div>
                </label>
                
                <div class="flex items-center gap-2">
                    <button id="mobile-search-btn" class="md:hidden flex items-center justify-center rounded-full h-10 w-10 bg-primary/10 text-slate-900 dark:text-slate-100 hover:bg-primary hover:text-white transition-all border-none cursor-pointer">
                        <span class="material-symbols-outlined text-[20px]">search</span>
                    </button>

                    <a href="<?php echo esc_url( get_permalink( 56 ) ); ?>" class="flex items-center justify-center rounded-full h-10 w-10 bg-primary/10 text-slate-900 dark:text-slate-100 hover:bg-primary hover:text-white transition-all border-none cursor-pointer no-underline">
                        <span class="material-symbols-outlined text-[20px]">favorite</span>
                    </a>
                    
                    <a id="side-cart-toggle" href="<?php echo class_exists('WooCommerce') ? esc_url( wc_get_cart_url() ) : '#'; ?>" class="flex items-center justify-center rounded-full h-10 w-10 bg-primary/10 text-slate-900 dark:text-slate-100 hover:bg-primary hover:text-white transition-all relative no-underline">
                        <span class="material-symbols-outlined text-[20px]">shopping_cart</span>
                        <span class="cart-count absolute -top-1 -right-1 bg-primary text-[10px] font-bold text-white h-4 w-4 rounded-full flex items-center justify-center">
                            <?php echo class_exists('WooCommerce') && WC()->cart ? wp_kses_data( WC()->cart->get_cart_contents_count() ) : '0'; ?>
                        </span>
                    </a>
                    
                    <?php 
                    $account_url = '#';
                    if ( class_exists('WooCommerce') ) {
                        $account_url = esc_url( wc_get_page_permalink('myaccount') );
                        if ( is_user_logged_in() && function_exists('wcfm_is_vendor') && wcfm_is_vendor() ) {
                            $account_url = esc_url( apply_filters( 'wcfm_dashboard_home', get_wcfm_page() ) );
                        }
                    }
                    ?>
                    <a href="<?php echo $account_url; ?>" class="flex items-center justify-center rounded-full h-10 w-10 bg-primary/10 text-slate-900 dark:text-slate-100 hover:bg-primary hover:text-white transition-all no-underline">
                        <span class="material-symbols-outlined text-[24px]">account_circle</span>
                    </a>

                    <button id="mobile-menu-btn" class="lg:hidden flex items-center justify-center rounded-full h-10 w-10 bg-primary/10 text-slate-900 dark:text-slate-100 hover:bg-primary hover:text-white transition-all border-none cursor-pointer">
                        <span class="material-symbols-outlined text-[24px]">menu</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Search Dropdown -->
        <div id="mobile-search-container" class="hidden md:hidden absolute top-full left-0 w-full bg-white dark:bg-background-dark border-b border-primary/10 p-4 shadow-lg z-40">
            <div class="flex items-center gap-2">
                <form role="search" method="get" class="search-form w-full flex items-center bg-primary/5 rounded-full px-4 h-10" action="<?php echo esc_url( home_url( '/' ) ); ?>">
                    <span class="material-symbols-outlined text-primary/60 text-[20px] mr-2">search</span>
                    <input type="search" class="w-full bg-transparent border-none focus:ring-0 text-sm placeholder:text-primary/40 outline-none" placeholder="Buscar aceites, semillas, artesanías..." value="<?php echo get_search_query(); ?>" name="s" />
                </form>
                <button id="mobile-search-close" class="p-2 text-slate-500 hover:text-primary transition-colors">
                    <span class="material-symbols-outlined text-[20px]">close</span>
                </button>
            </div>
        </div>
    </header>

    <!-- Mobile Off-Canvas Menu -->
    <div id="mobile-menu" class="fixed inset-y-0 right-0 w-80 bg-white dark:bg-background-dark shadow-2xl z-[60] transform translate-x-full transition-transform duration-300 ease-in-out lg:hidden flex flex-col">
        <div class="flex items-center justify-between p-4 border-b border-primary/10">
            <span class="font-black text-primary uppercase tracking-wider">Menú</span>
            <button id="mobile-menu-close" class="p-2 text-slate-500 hover:text-primary transition-colors rounded-full hover:bg-primary/10">
                <span class="material-symbols-outlined text-[24px]">close</span>
            </button>
        </div>
        <div class="p-4 overflow-y-auto flex-1">
            <nav class="flex flex-col gap-6">
                <?php
                if ( has_nav_menu( 'menu-1' ) ) {
                    wp_nav_menu(
                        array(
                            'theme_location' => 'menu-1',
                            'menu_id'        => 'mobile-primary-menu',
                            'container'      => false,
                            'menu_class'     => 'flex flex-col gap-4 list-none m-0 p-0 text-lg font-medium',
                            'fallback_cb'    => false,
                        )
                    );
                } else {
                    $shop_url = class_exists( 'WooCommerce' ) ? esc_url( wc_get_page_permalink( 'shop' ) ) : '#';
                    echo '<a class="text-slate-800 dark:text-slate-200 text-lg font-medium hover:text-primary transition-colors no-underline block" href="' . $shop_url . '">Tienda</a>
                          <a class="text-slate-800 dark:text-slate-200 text-lg font-medium hover:text-primary transition-colors no-underline block" href="#">Categorías</a>
                          <a class="text-slate-800 dark:text-slate-200 text-lg font-medium hover:text-primary transition-colors no-underline block" href="' . esc_url( home_url( '/?page_id=53/' ) ) . '">Comunidades</a>
                          <a class="text-slate-800 dark:text-slate-200 text-lg font-medium hover:text-primary transition-colors no-underline block" href="#">Impacto</a>';
                }
                ?>
            </nav>
        </div>
        <div class="p-4 border-t border-primary/10 bg-slate-50 dark:bg-slate-900/50">
            <div class="flex flex-col gap-4">
                <a href="<?php echo class_exists('WooCommerce') ? esc_url( wc_get_page_permalink('myaccount') ) : '#'; ?>" class="flex items-center gap-3 text-slate-700 dark:text-slate-300 font-medium no-underline">
                    <span class="material-symbols-outlined text-primary">account_circle</span>
                    Mi Cuenta
                </a>
                <a href="<?php echo class_exists('WooCommerce') ? esc_url( wc_get_cart_url() ) : '#'; ?>" class="flex items-center gap-3 text-slate-700 dark:text-slate-300 font-medium no-underline">
                    <span class="material-symbols-outlined text-primary">shopping_cart</span>
                    Mi Carrito
                </a>
            </div>
        </div>
    </div>
    <div id="content" class="site-content">
