<?php
/**
 * The main template file.
 *
 * @package WordPress
 * @subpackage FAU
 * @since FAU 1.0
 */

use function RRZE\Events\plugin;

if (isset($_GET['format']) && $_GET['format'] == 'embedded') {
    get_template_part('template-parts/index', 'embedded');
    return;
}

get_header();

?>

    <div id="content">
        <div class="content-container">
            <div class="post-row">
                <main class="entry-content">

                    <?php if (empty($herotype)) {   ?>
                        <h1 id="maintop"  class="screen-reader-text"><?php esc_html_e('Talks', 'rrze-events'); ?></h1>
                    <?php } else { ?>
                        <h1 id="maintop" ><?php esc_html_e('Talks', 'rrze-events');; ?></h1>
                    <?php }

                    include plugin()->getPath('templates/content/') . 'content-archive-talk.php';

                    ?>

                </main>
            </div>
        </div>

    </div>
<?php
get_footer(); 

