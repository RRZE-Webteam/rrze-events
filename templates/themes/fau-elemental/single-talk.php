<?php

use function RRZE\Events\plugin;

get_header();

?>

    <section class="hero-page" aria-labelledby="pagetitle">
        <?php
        if (has_post_thumbnail()) { ?>
            <div class="faue-featured-image">
                <?php the_post_thumbnail('large'); ?>
            </div>
        <?php } ?>
        <div>
            <h1 id="pagetitle" class="wp-block-post-title"><?php the_title(); ?></h1>
        </div>

    </section>
    <main id="main" class="site-main" role="main">
        <?php
        if (have_posts()) :
            while (have_posts()) :
                the_post();
                ?>
                <article id="post-<?php echo esc_attr(get_the_ID()); ?>" <?php post_class(); ?>>
                        <?php include plugin()->getPath('templates/content/') . 'content-single-talk.php'; ?>

                </article>
            <?php
            endwhile;
        endif;
        ?>
    </main>

<?php
get_footer();
