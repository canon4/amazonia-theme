<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="profile" href="https://gmpg.org/xfn/11">

    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<div id="page" class="site">

    <header id="masthead" class="site-header custom-amazonia-header">
        <div class="header-container">
            <div class="site-branding">
                <a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#2ecc71" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M11 20A7 7 0 0 1 9.8 6.1C15.5 5 17 4.48 19 2c1 2 2 4.18 1 8.3C19.2 15.6 15 20 11 20z" fill="rgba(46,204,113,0.2)"/>
                        <line x1="11" y1="20" x2="15" y2="10"/>
                    </svg>
                    <span class="logo-text"><?php bloginfo( 'name' ); ?></span>
                </a>
            </div>
            
            <nav id="site-navigation" class="main-navigation">
                <?php
                if ( has_nav_menu( 'menu-1' ) ) {
                    wp_nav_menu(
                        array(
                            'theme_location' => 'menu-1',
                            'menu_id'        => 'primary-menu',
                            'container'      => false,
                            'fallback_cb'    => false,
                        )
                    );
                } else {
                    echo '<ul>
                            <li><a href="#">Shop All</a></li>
                            <li><a href="#">Categories</a></li>
                            <li><a href="#">Green Living</a></li>
                            <li><a href="#">Artisanal</a></li>
                            <li><a href="#">Wellness</a></li>
                            <li><a href="#" style="color: #f97316;">Sale</a></li>
                            <li><a href="#">about</a></li>
                          </ul>';
                }
                ?>
            </nav>

            <div class="header-right-actions">
                <button class="icon-btn search-btn" aria-label="Search">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                </button>
                <button class="icon-btn user-btn" aria-label="User Account">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                </button>
                
                <?php if ( class_exists( 'WooCommerce' ) ) : ?>
                    <a class="header-cart-btn" href="<?php echo esc_url( wc_get_cart_url() ); ?>">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>
                        <span>Cart [<?php echo WC()->cart ? wp_kses_data( WC()->cart->get_cart_contents_count() ) : '0'; ?>]</span>
                    </a>
                <?php else: ?>
                    <a class="header-cart-btn" href="#">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>
                        <span>Cart [4]</span>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <div id="content" class="site-content">
