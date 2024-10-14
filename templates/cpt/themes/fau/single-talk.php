<?php

/**
 * The template for displaying a single post.
 *
 *
 * @package WordPress
 * @subpackage FAU
 * @since FAU 1.0
 */

use RRZE\Events\Settings;
use RRZE\Events\Utils;

get_header();

$id = get_the_ID();
$meta = get_post_meta($id);
$talkCategories = get_the_terms($post->ID, 'talk_category');
$talkTags = get_the_terms($post->ID, 'talk_tag');

while (have_posts()) : the_post(); ?>

    <div id="content">
        <div class="content-container">
            <div class="content-row">
                <main>
                    <article class="rrze-talk" itemscope itemtype="https://schema.org/Event">
                        <h1 id="maintop" class="mobiletitle" itemprop="name">
                            <?php the_title(); ?>
                        </h1>

                        <?php
                        $speakers = Utils::getMeta($meta, 'talk_speakers');
                        if ($speakers != '') {
                            echo '<div class="talk-speaker">';
                            echo '<h2>' . esc_html(_n('Speaker', 'Speakers', count($speakers), 'rrze-events')) . '</h2>';
                            foreach ($speakers as $speakerID) {
                                if (!has_post_thumbnail($speakerID))
                                    continue;
                                $organisation = get_post_meta($speakerID, 'speaker_organisation', TRUE);
                                echo '<div class="talk-speaker-item"><a href="' . get_permalink($speakerID) . '">';
                                echo get_the_post_thumbnail($speakerID, 'medium', ['class' => 'talk-speaker-thumbnail']);
                                echo '<span class="speaker-name">' . get_the_title($speakerID) . '</span>';
                                echo ($organisation != '' ? '<span class="speaker-organisation">' . $organisation . '</span>' : '');
                                echo '</a></div>';
                            }
                            echo '</div>';
                        }
                        ?>

                        <div class="talk-main">
                            <div class="talk-details">
                                <h2><?php esc_html_e('Infos', 'rrze-eventsdisplay: flex;');?></h2>
                                <?php
                                // Thumbnail
                                if ( 1 == 2 && has_post_thumbnail() && !post_password_required()) {
                                    $post_thumbnail_id = get_post_thumbnail_id();
                                    if ($post_thumbnail_id && !metadata_exists('post', $post->ID, 'vidpod_url')) {
                                        $value = get_post_meta( $post->ID, '_hide_featured_image', true );
                                        $imgdata = fau_get_image_attributs($post_thumbnail_id);
                                        $full_image_attributes = wp_get_attachment_image_src($post_thumbnail_id, 'full');
                                        if ($full_image_attributes) {
                                            $altattr = trim(wp_strip_all_tags($imgdata['alt']));
                                            if ((fau_empty($altattr)) && (get_theme_mod("advanced_display_postthumb_alt-from-desc"))) {
                                                $altattr = trim(wp_strip_all_tags($imgdata['description']));
                                            }
                                            if (fau_empty($altattr)) {
                                                // falls es noch immer leer ist, geben wir an, dass dieses Bild ein Symbolbild ist und
                                                // der Klick das Bild größer macht.
                                                $altattr = __('Symbolbild zum Artikel. Der Link öffnet das Bild in einer großen Anzeige.', 'fau');
                                            }
                                            if ($value) {
                                                $post_thumbnail_id = get_post_thumbnail_id($post->ID);
                                                $post_thumbnail_url = wp_get_attachment_image_src($post_thumbnail_id, 'full')[0];
                                                echo '<style type="text/css">.post-image { display: none; }</style>';
                                                echo '<img src="' . esc_url($post_thumbnail_url) . '" class="post-image" />';
                                            }
                                            echo '<div class="post-image">';
                                            echo '<figure>';
                                            echo '<a class="lightbox" href="' . esc_url($full_image_attributes[0]) . '">';
                                            echo wp_kses_post(fau_get_image_htmlcode($post_thumbnail_id, 'rwd-480-3-2', $altattr));
                                            echo '</a>';

                                            /*$bildunterschrift = get_post_meta($post->ID, 'fauval_overwrite_thumbdesc', true);
                                            if (isset($bildunterschrift) && strlen($bildunterschrift) > 1) {
                                                $imgdata['fauval_overwrite_thumbdesc'] = $bildunterschrift;
                                            }
                                            echo fau_get_image_figcaption($imgdata);*/
                                            echo '</figure>';
                                            echo '</div>';
                                        }
                                    }
                                }

                                $talkMeta = Utils::talkFields($id, ['organisation', 'speaker']);
                                if ($talkMeta != '') {
                                    echo wp_kses($talkMeta, Utils::getKsesExtendedRuleset());
                                }
                                ?>

                            </div>

                            <div class="talk-description">
                                <?php the_content(); ?>
                            </div>

                            <?php
                            if ($talkCategories && $talkTags) {
                                echo '<div class="talk-taxonomies">'
                                    . '<h2>' . __('More about&hellip;', 'rrze-events') . '</h2>';
                                if ($talkCategories) {
                                    echo '<div class="talk-categories">';
                                    echo get_the_term_list( $post->ID, 'talk_category', '<ul><li>','</li><li>', '</li></ul>');
                                    echo '</div>';
                                }

                                if ($talkTags) {
                                    $settings = Settings::getOption('rrze-events-settings');
                                    $accentColor = $settings['accent-color'];
                                    echo '<div class="talk-tags">';
                                    echo do_shortcode('[icon icon="solid tag" color="' . $accentColor . '"]')
                                        . '<span class="sr-only">' . esc_html('Tags', 'rrze-events') . ': </span>'
                                        . get_the_term_list( $post->ID, 'talk_tag', '<ul><li>','</li><li>', '</li></ul>');
                                    echo '</div>';
                                }
                                echo '</div>';
                            }
                            ?>
                        </div>

                    </article>

                </main>
            </div>
        </div>
    </div>
<?php endwhile;

get_footer();
