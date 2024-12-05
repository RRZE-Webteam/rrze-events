<?php
/**
 * The template for displaying archive pages
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package RRZE_2019
 */

use RRZE\Events\Settings;

use function RRZE\Events\plugin;

if (isset($_GET['format']) && $_GET['format'] == 'embedded') {
    get_template_part('template-parts/archive', 'embedded');
    return;
}

$labels = Settings::getOption('rrze-events-label-settings');

get_header();
?>

    <div id="primary" class="content-area">
		<main id="main" class="site-main">

            <h1><?php echo esc_html($labels['label-speaker-plural'])?></h1>

		    <?php include plugin()->getPath('templates/content/') . 'content-archive-speaker.php'; ?>

		</main><!-- #main -->
	</div><!-- #primary -->

<?php
get_footer();
