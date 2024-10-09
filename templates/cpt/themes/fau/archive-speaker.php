<?php
/**
 * The main template file.
 *
 * @package WordPress
 * @subpackage FAU
 * @since FAU 1.0
 */

use RRZE\Events\Shortcodes\Speaker;
use RRZE\Events\Utils;

if (isset($_GET['format']) && $_GET['format'] == 'embedded') {
    get_template_part('template-parts/index', 'embedded');
    return;
}

get_header();
global $wp_query;

?>

    <div id="content">
        <div class="content-container">
            <div class="post-row">
                <main class="entry-content">

                    <?php if (empty($herotype)) {   ?>
                        <h1 id="maintop"  class="screen-reader-text"><?php esc_html_e('Speakers', 'rrze-events'); ?></h1>
                    <?php } else { ?>
                        <h1 id="maintop" ><?php esc_html_e('Speakers', 'rrze-events');; ?></h1>
                    <?php }

                    $queryVars = $wp_query->query_vars;
                    $atts = [
                        'format' => 'grid',
                    ];
                    if (isset($queryVars['speaker_category']) && $queryVars['speaker_category'] != '') {
                        $atts['category'] = esc_html($queryVars['speaker_category']);
                    }

                    echo wp_kses(Speaker::shortcodeOutput($atts), Utils::getKsesExtendedRuleset());
                    ?>

                </main>
            </div>
        </div>

    </div>
<?php
get_footer(); 

