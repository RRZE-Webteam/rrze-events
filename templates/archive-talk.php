<?php

use function RRZE\Events\plugin;

get_header();

include plugin()->getPath('templates/content/') . 'content-archive-talk.php';

get_footer();
