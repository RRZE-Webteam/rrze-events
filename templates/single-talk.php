<?php
/**
 * The template for displaying single posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#single-post
 *
 * @package RRZE_2019
 */

use function RRZE\Events\plugin;

get_header();

include plugin()->getPath('templates/content/') . 'content-single-talk.php';

get_footer();
