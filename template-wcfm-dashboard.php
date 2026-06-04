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
    // Carga todos los CSS y scripts de WordPress, tema y plugins (incluido WCFM).
    // Los estilos personalizados del dashboard se cargan desde:
    // assets/css/wcfm-dashboard.css — encolado en functions.php
    wp_head();
    ?>
</head>
<body <?php body_class('bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-slate-100 antialiased selection:bg-primary/30'); ?>>

<div class="wcfm_dashboard_wrapper">
    <?php
    // Aquí es donde WordPress va a inyectar el código "shortcode" de WCFM
    while (have_posts()) :
        the_post();
        the_content();
    endwhile;
    ?>
</div>

<?php wp_footer(); ?>
</body>
</html>
