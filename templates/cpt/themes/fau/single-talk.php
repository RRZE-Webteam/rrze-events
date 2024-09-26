<?php

/**
 * The template for displaying a single post.
 *
 *
 * @package WordPress
 * @subpackage FAU
 * @since FAU 1.0
 */

use RRZE\Events\Utils;

get_header();

$id = get_the_ID();
$meta = get_post_meta($id);

while (have_posts()) : the_post(); ?>

    <div id="content">
        <div class="content-container">
            <div class="content-row">
                <main>
                    <article class="rrze-talk" itemscope itemtype="https://schema.org/Event">
                        <h1 id="maintop" class="mobiletitle" itemprop="name">
                            <?php the_title();
                            $organisation = get_post_meta($id, 'talk_organisation', true);
                            if ($organisation != '') {
                                echo '<br /><span class="talk-organisation">' . $organisation . '</span>';
                            }
                            ?>
                        </h1>

                        <div class="talk-details">

                            <?php
                            // Thumbnail
                            if (has_post_thumbnail() && !post_password_required()) {
                                $post_thumbnail_id = get_post_thumbnail_id();
                                if ($post_thumbnail_id && !metadata_exists('post', $post->ID, 'vidpod_url')) {
                                    $value = get_post_meta( $post->ID, '_hide_featured_image', true );
                                    $imgdata = fau_get_image_attributs($post_thumbnail_id);
                                    $full_image_attributes = wp_get_attachment_image_src($post_thumbnail_id, 'full');
                                    if ($full_image_attributes) {
                                        $altattr = trim(strip_tags($imgdata['alt']));
                                        if ((fau_empty($altattr)) && (get_theme_mod("advanced_display_postthumb_alt-from-desc"))) {
                                            $altattr = trim(strip_tags($imgdata['description']));
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
                                        echo '<a class="lightbox" href="' . fau_esc_url($full_image_attributes[0]) . '">';
                                        echo fau_get_image_htmlcode($post_thumbnail_id, 'rwd-480-3-2', $altattr);
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

                            // Speaker(s)
                            $speakers = get_post_meta($id, 'talk_speakers', true);
                            if ($speakers != '') {
                                $speakerLinks = [];
                                foreach ($speakers as $speakerID) {
                                    $organisation = Utils::getMeta($meta, 'speaker_organisation');
                                    $speakerLinks[] = '<a href="' . get_permalink($speakerID) . '">' . get_the_title($speakerID) . '</a>'
                                        . ($organisation != '' ? ' (' . $organisation . ')' : '');
                                }
                                echo '<div class="talk-speaker"><span class="label">' . _n('Speaker', 'Speakers', count($speakers), 'rrze-events') . '</span>:<br />';
                                echo implode('<br />', $speakerLinks);
                                echo '</div>';
                            }

                            // Date/Time
                            $date = Utils::getMeta($meta, 'talk_date');
                            $start = Utils::getMeta($meta, 'talk_start');
                            $end = Utils::getMeta($meta, 'talk_end');
                            if ($date . $start . $end != '') {
                                echo '<div class="talk-datetime">'
                                    . do_shortcode('<span class="date">[icon icon="regular calendar"] '
                                        . '<span class="sr-only">' . __('Date', 'rrze-events') . ': </span>'
                                        . $date
                                        . '</span><span class="time">'
                                        . '[icon icon="regular clock"] '
                                        . '<span class="sr-only">' . __('Time', 'rrze-events') . ': </span>'
                                        . $start
                                        . ($end != '' ? ' - ' . $end : ''))
                                         . '</span>';
                                echo '</div>';
                            }


                            //$links = Utils::talkLinks($id, 'icons');
                            //if ($links != '') {
                            //    echo '<div class="talk-links">' . $links . '</div>';
                            //}
                            ?>

                            <?php if (/*get_my_theme_mod('show_talk_categories') == true && */false !== get_the_terms($post->ID, 'talk_category')) : ?>
                                <div class="talk-categories">
                                    <?php print get_the_term_list( $post->ID, 'talk_category', '<ul><li>','</li><li>', '</li></ul>'); ?>
                                </div><!-- end .entry-cats -->
                            <?php endif; ?>


                        </div>

                        <div class="talk-main">
                            <div class="talk-description">
                                <?php the_content(); ?>
                            </div>
                            <?php
                            $talks = Utils::talksBySpeaker($id);
                            if ($talks != '') {
                                echo '<div class="talk-talks">' . $talks . '</div>';
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
