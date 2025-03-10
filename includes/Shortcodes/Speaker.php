<?php

namespace RRZE\Events\Shortcodes;

use RRZE\Events\Settings;
use RRZE\Events\Utils;

class Speaker {
    public function __construct() {
        add_shortcode('speaker', [$this, 'shortcodeOutput']);
        add_shortcode('rrze-speaker', [$this, 'shortcodeOutput']);
    }

    public static function shortcodeOutput($atts, $content = "") {
        global $post;
        $speakerSettings = Settings::getOption('rrze-events-speaker-settings');

        extract(shortcode_atts(array(
            'cat' => '',
            'category' => '',
            'num' => -1,
            'number' => -1,
            'id' => '',
            'format' => '',
            'showautor' => 1,
            'orderby' => 'lastname',
        ), $atts));
        $single = 0;
        $category = ('' != $category) ? sanitize_text_field($category) : sanitize_text_field($cat);
        $number = ('-1' != $num) ? sanitize_text_field($num) : sanitize_text_field($number);
        $idsRaw = explode(',', $id);
        $ids = array_map('intval', $idsRaw);
        $format = sanitize_text_field($format);
        $orderby = ($orderby == 'lastname' ? 'lastname' : 'firstname');
        $settings = Settings::getOption('rrze-events-settings');
        $accentColor = $settings['accent-color'];

        $args = [
            'post_type' => 'speaker',
            'post_status' => 'publish',
            'posts_per_page' => '-1',
        ];
        if (is_numeric($number)) {
            $args['posts_per_page'] = $number;
        }
        if ((isset($id)) && ( strlen(trim($id)) > 0)) {
            $args ['post__in'] = $ids;
            $single = 1;
        } elseif ((isset($category)) && ( strlen(trim($category)) > 0)) {
            $args['tax_query'][] = array(
                'taxonomy' => 'speaker_category',
                'field' => 'slug',
                'terms' => explode(',', $category),
                'operator' => 'AND',
            );
        }

        $speakers = new \WP_Query($args);
        $out = '';

        if ($speakers->have_posts()) {
            $i = 0;
            if ($format == 'list') {
                $out .= '<ul class="rrze-events speaker-list">';
            } else {
                $out .= '<div class="rrze-events speaker-grid">';
            }

            foreach ($speakers->posts as $_s => $speaker) {
                $s_name = $speaker->post_title;
                $titles = ['MdB', 'BSc', 'B.Sc.', 'MSc', 'M.Sc.', 'BA', 'B.A.', 'MA', 'M.A.', 'PhD', 'LL.B.', 'LL.M.', 'B.Eng.', 'M.Eng.', 'B.F.A.', 'M.F.A.', 'B.Mus.', 'M.Mus.', 'Dr.', 'Prof.', 'em.', 'PD', 'PD.', 'P.D.', 'AG', 'GmbH', '& Co.', 'KG', 'Ltd.', ' von ', ' van '];
                $completename = trim(str_replace($titles, '', $s_name));
                $nameparts = explode(' ', $s_name);
                $lastname = end($nameparts);
                $speakers->posts[$_s]->lastname = $lastname;
                $speakers->posts[$_s]->completename = $completename;
            }
            if ($orderby == 'lastname') {
                usort($speakers->posts, function ($a, $b) {
                    return strcasecmp($a->lastname, $b->lastname);
                });
            } elseif ($orderby == 'firstname') {
                usort($speakers->posts, function ($a, $b) {
                    return strcasecmp($a->completename, $b->completename);
                });
            }

            $max = ($number == '-1' ? 999999 : $number);
            while ($speakers->have_posts() && ($i < $max)) {
                $speakers->the_post();
                $url = esc_url(get_permalink());
                $title = get_the_title();
                $i++;

                if ($format == 'list') {
                    $out .= '<li>';
                    $out .= '<a href="' . $url . '" rel="bookmark">' . $title . '</a>';
                    $out .= '</li>';
                } else {
                    $out .= '<article id="post-' . $post->ID . '" class="' . implode(' ', get_post_class()) . '">';
                    $out .= '<header class="entry-header">';
                    if ('' !== get_the_post_thumbnail() && !post_password_required()) :
                        $out .=     '<div class="speaker-thumbnail">' . '<a href="' . $url . '" rel="bookmark">';
                        $out .=			get_the_post_thumbnail($post->ID, 'large');
                        $out .=		'</a></div>';       // end .entry-thumbnail
                    endif;

                    $out .= '<h2 class="entry-title">' .
                        '<a href="' . $url . '" rel="bookmark">' . $title . '</a>' .
                        '</h2>' .
                        '</header>';    // end .entry-header

                    $out .=         '<div class="entry-summary">' .
                        wp_strip_all_tags(get_the_excerpt()) . do_shortcode('[icon icon="solid angles-right" color="' . $accentColor . '"]') .
                        '</div>';      // end .entry-main

                    if (isset($speakerSettings['show-categories']) && $speakerSettings['show-categories'] == true && get_the_terms($post->ID, 'speaker_category') !== false) {
                        $out .= '<div class="entry-cats">' . get_the_term_list( $post->ID, 'speaker_category', null,' | ') . '</div>';
                    }

                    $out .= '</article>';
                }
            }
            if ($format == 'list') {
                $out .= '</ul>';
            } else {
                $out .= '</div>';
            }
        }
        wp_reset_postdata();
        wp_enqueue_style('rrze-events');
        return $out;
    }
}

