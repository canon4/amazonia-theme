<?php
/**
 * Template Name: WCFM Dashboard Minimal
 * Description: Plantilla vacía para que el panel de WCFM cargue solo, sin el diseño de la tienda principal.
 */
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php 
    // Esto es muy importante, carga todos los CSS y scripts de los plugins (incluido WCFM)
    wp_head(); 
    ?>
    <style>
        /* Reseteamos los márgenes por defecto para que WCFM ocupe la pantalla completa */
        body, html { 
            margin: 0; 
            padding: 0; 
            min-height: 100vh; 
            background: #f0f0f1; /* Color de fondo base sutil */
        }
        .wcfm_dashboard_wrapper { 
            padding: 0; 
            min-height: 100vh; 
        }
    </style>
</head>
<body <?php body_class(); ?>>

<div class="wcfm_dashboard_wrapper">
    <?php
    // Aquí es donde WordPress va a inyectar el código "shortcode" de WCFM
    while (have_posts()) :
        the_post();
        the_content();
    endwhile;
    ?>
</div>

<?php 
// Esto carga los scripts del footer (vital para que los botones de WCFM funcionen)
wp_footer(); 
?>
</body>
</html>
