<?php
/**
 * The template for displaying archive pages
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package RRZE_2019
 */

use function RRZE\Events\plugin;

if (isset($_GET['format']) && $_GET['format'] == 'embedded') {
    get_template_part('template-parts/archive', 'embedded');
    return;
}

get_header();
?>

    <?php if ( !is_front_page() ) { ?>
        <div id="sidebar" class="sidebar">
            <?php get_sidebar('page'); ?>
        </div><!-- .sidebar -->
    <?php } ?>

    <div id="primary" class="content-area">
		<main id="main" class="site-main">

		<?php include plugin()->getPath('templates/content/') . 'content-archive-speaker.php'; ?>

		</main><!-- #main -->
	</div><!-- #primary -->

<?php
get_footer();
