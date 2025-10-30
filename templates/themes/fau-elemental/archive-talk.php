<?php

use RRZE\Events\Settings;

use function RRZE\Events\plugin;

$labels = Settings::getOption('rrze-events-label-settings');

get_header();

?>

    <main id="main" class="site-main" role="main">

        <section id="post-<?php echo esc_attr(get_the_ID()); ?>" <?php post_class(); ?>>

            <h1><?php echo esc_html($labels['label-talk-plural'])?></h1>
            <?php include plugin()->getPath('templates/content/') . 'content-archive-talk.php'; ?>

        </section>

    </main>

<?php
get_footer();
