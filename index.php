<?php
/**
 * The main template file
 *
 * This is the most generic template file in a WordPress theme
 * and one of the two required files for a theme (the other being style.css).
 */

get_header();
?>

<main id="primary" class="site-main">

    <div style="text-align: center; margin-bottom: 40px; padding: 40px; background-color: #e6fffa; border-radius: 8px; border: 1px solid #b2f5ea;">
        <h2 style="color: #319795;">¡El Tema Amazonia funciona correctamente!</h2>
        <p>Estás viendo el archivo <code>index.php</code> de tu nuevo tema. Esta es una vista previa del esqueleto básico.</p>
    </div>

    <?php
    if ( have_posts() ) :
        /* Start the Loop */
        while ( have_posts() ) :
            the_post();
            ?>
            <article id="post-<?php the_ID(); ?>" <?php post_class( 'theme-preview-post' ); ?> style="margin-bottom: 30px; padding-bottom: 20px; border-bottom: 1px solid #eee;">
                <header class="entry-header">
                    <?php the_title( '<h2 class="entry-title"><a href="' . esc_url( get_permalink() ) . '" rel="bookmark">', '</a></h2>' ); ?>
                </header>
                
                <div class="entry-content" style="margin-top: 15px; line-height: 1.6;">
                    <?php the_content(); ?>
                </div>
            </article>
            <?php
        endwhile;

        the_posts_navigation();

    else :
        ?>
        <p>No hay entradas para mostrar. ¡Agrega contenido a tu WordPress para verlo aquí!</p>
        <?php
    endif;
    ?>

</main>

<?php
get_footer();
