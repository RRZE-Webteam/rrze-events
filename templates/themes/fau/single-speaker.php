<?php

/**
 * The template for displaying a single post.
 *
 *
 * @package WordPress
 * @subpackage FAU
 * @since FAU 1.0
 */

use function RRZE\Events\plugin;

get_header();

while (have_posts()) : the_post(); ?>

    <div id="content">
        <div class="content-container">
            <div class="content-row">
                <main>

                    <?php include plugin()->getPath('templates/content/') . 'content-single-speaker.php'; ?>

                </main>
            </div>
        </div>
    </div>
<?php endwhile;

get_footer();
