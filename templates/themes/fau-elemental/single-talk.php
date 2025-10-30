<?php

use function RRZE\Events\plugin;

get_header();

?>

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
