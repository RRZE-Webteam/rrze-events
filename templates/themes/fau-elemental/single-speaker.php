<?php

use function RRZE\Events\plugin;

get_header();

?>

    <section class="hero-page" aria-labelledby="pagetitle">
        <div>
            <h1 id="pagetitle" class="wp-block-post-title">
                <?php
                the_title();
                $organisation = get_post_meta(get_the_ID(), 'speaker_organisation', true);
                if ($organisation != '') {
                    echo '<br /><span class="speaker-organisation">' . esc_html($organisation) . '</span>';
                }?></h1>
        </div>
    </section>

    <main id="main" class="site-main" role="main">
        <?php
        if (have_posts()) :
            while (have_posts()) :
                the_post();
                ?>
                <article id="post-<?php echo esc_attr(get_the_ID()); ?>" <?php post_class(); ?>>
                        <?php include plugin()->getPath('templates/content/') . 'content-single-speaker.php'; ?>

                </article>
            <?php
            endwhile;
        endif;
        ?>
    </main>

<?php
get_footer();
