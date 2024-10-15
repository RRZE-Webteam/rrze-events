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

?>

<?php if ( !is_front_page() ) { ?>
    <div id="sidebar" class="sidebar">
        <?php get_sidebar('page'); ?>
    </div><!-- .sidebar -->
<?php } ?>

    <div id="primary" class="content-area">
        <main id="main" class="site-main">

            <?php include plugin()->getPath('templates/content/') . 'content-single-speaker.php'; ?>

        </main><!-- #main -->
    </div><!-- #primary -->

<?php
get_footer();
