<?php
/**
 * The template for displaying all pages
 */

get_header();
?>

<main id="primary" class="site-main">

    <?php
    while ( have_posts() ) :
        the_post();
        ?>
        <article id="post-<?php the_ID(); ?>" <?php post_class( 'pb-12' ); ?>>
            <header class="entry-header bg-slate-50 dark:bg-background-dark py-5 lg:py-6 mb-6 border-b border-primary/10">
                <div class="max-w-7xl mx-auto px-6 lg:px-20">
                    <?php 
                        if ( function_exists( 'woocommerce_breadcrumb' ) ) {
                            woocommerce_breadcrumb( array(
                                'delimiter'   => ' <span class="material-symbols-outlined text-[16px] text-slate-400 align-middle">chevron_right</span> ',
                                'wrap_before' => '<nav class="woocommerce-breadcrumb flex items-center gap-1 text-slate-500 font-medium text-sm mb-4" itemprop="breadcrumb">',
                                'wrap_after'  => '</nav>',
                            ) );
                        }
                    ?>
                    <?php the_title( '<h1 class="text-4xl lg:text-5xl font-black text-forest-green dark:text-slate-100">', '</h1>' ); ?>
                </div>
            </header>
            
            <div class="entry-content">
                <?php the_content(); ?>
            </div>
        </article>
        <?php
    endwhile; // End of the loop.
    ?>

</main>

<?php
get_footer();
