<?php

defined( 'ABSPATH' ) || exit;

use function RRZE\Events\plugin;

get_header(); ?>

<main id="content">
    <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>

        <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
            <?php include plugin()->getPath('templates/content/') . 'content-single-speaker.php'; ?>
        </article>

        <?php comments_template(); ?>

    <?php endwhile; endif; ?>
</main>

<?php get_footer(); ?>
