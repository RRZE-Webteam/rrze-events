<?php
/**
 * The main template file.
 *
 * @package WordPress
 * @subpackage FAU
 * @since FAU 1.0
 */

use RRZE\Events\Shortcodes\Talk;
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
                        <h1 id="maintop"  class="screen-reader-text"><?php esc_html_e('Talks', 'rrze-events'); ?></h1>
                    <?php } else { ?>
                        <h1 id="maintop" ><?php esc_html_e('Talks', 'rrze-events');; ?></h1>
                    <?php }

                    $queryVars = $wp_query->query_vars;
                    //var_dump($queryVars);
                    $atts = [
                        'format' => 'grid',
                    ];
                    if (isset($queryVars['talk_category']) && $queryVars['talk_category'] != '') {
                        $atts['category'] = esc_html($queryVars['talk_category']);
                    }
                    if (isset($queryVars['talk_tag']) && $queryVars['talk_tag'] != '') {
                        $atts['tag'] = esc_html($queryVars['talk_tag']);
                    }

                    echo wp_kses(Talk::shortcodeOutput($atts), Utils::get_kses_extended_ruleset());
                    ?>

                </main>
            </div>
        </div>

    </div>
<?php
get_footer(); 

