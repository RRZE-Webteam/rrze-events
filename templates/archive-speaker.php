<?php

use function RRZE\Events\plugin;

get_header();

include plugin()->getPath('templates/content/') . 'content-archive-speaker.php';

get_footer();
