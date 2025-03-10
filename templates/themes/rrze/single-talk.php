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

	<div id="primary" class="content-area">
		<main id="main" class="site-main">

            <?php include plugin()->getPath('templates/content/') . 'content-single-talk.php'; ?>

		</main><!-- #main -->
	</div><!-- #primary -->

<?php
get_footer();
