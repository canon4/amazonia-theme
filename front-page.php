<?php
/**
 * The front page template file
 */

get_header();
?>

<main id="primary" class="site-main">

    <div style="text-align: center; margin-bottom: 40px; padding: 40px; background-color: #e6fffa; border-radius: 8px; border: 1px solid #b2f5ea;">
        <h2 style="color: #319795;">¡Bienvenido a la página principal de Amazonia!</h2>
        <p>Estás viendo el archivo <code>front-page.php</code>. Aquí puedes personalizar el inicio de tu tienda y marketplace.</p>
    </div>

    <?php
    if ( have_posts() ) :
        while ( have_posts() ) :
            the_post();
            ?>
            <div class="entry-content" style="margin-top: 15px; line-height: 1.6;">
                <?php the_content(); ?>
            </div>
            <?php
        endwhile;
    endif;
    ?>

</main>

<?php
get_footer();
