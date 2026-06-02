<?php

use RRZE\Events\Settings;
use function RRZE\Events\plugin;
$labels = Settings::getOption('rrze-events-label-settings');

get_header(); ?>

<main id="content">
    <header class="archive-header">
        <h1><?php echo esc_html($labels['label-speaker-plural'])?></h1>
    </header>

    <?php include plugin()->getPath('templates/content/') . 'content-archive-speaker.php'; ?>

</main>

<?php get_footer(); ?>