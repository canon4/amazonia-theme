<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="profile" href="https://gmpg.org/xfn/11">
    <?php wp_head(); ?>
    <style>
        body { font-family: 'Inter', sans-serif; margin: 0; padding: 0; background-color: #f4f4f4; color: #333; }
        .site-header { background-color: #1a202c; color: #fff; padding: 30px 20px; text-align: center; border-bottom: 4px solid #48bb78; }
        .site-header h1 a { color: #fff; text-decoration: none; font-size: 2.5rem; font-weight: bold; }
        .site-description { color: #a0aec0; margin-top: 10px; font-size: 1.1rem; }
        .site-content { padding: 40px 20px; max-width: 1200px; margin: 40px auto; background: #fff; min-height: 50vh; box-shadow: 0 4px 6px rgba(0,0,0,0.1); border-radius: 8px;}
        .site-footer { background-color: #2d3748; color: #e2e8f0; padding: 30px 20px; text-align: center; margin-top: 40px; }
        nav ul { list-style: none; padding: 0; display: flex; justify-content: center; gap: 20px; margin-top: 20px; }
        nav ul a { color: #edf2f7; text-decoration: none; font-weight: 500; }
        nav ul a:hover { color: #48bb78; }
        .entry-title a { color: #2d3748; text-decoration: none; }
        .entry-title a:hover { color: #48bb78; }
    </style>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<div id="page" class="site">

    <header id="masthead" class="site-header">
        <div class="site-branding">
            <h1 class="site-title"><a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a></h1>
            <p class="site-description"><?php bloginfo( 'description' ); ?></p>
        </div>
        
        <nav id="site-navigation" class="main-navigation">
            <?php
            wp_nav_menu(
                array(
                    'theme_location' => 'menu-1',
                    'menu_id'        => 'primary-menu',
                    'fallback_cb'    => false,
                )
            );
            ?>
        </nav>
    </header>

    <div id="content" class="site-content">
