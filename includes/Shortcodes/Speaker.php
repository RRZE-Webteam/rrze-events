<?php

namespace RRZE\Events\Shortcodes;

use RRZE\Events\Utils;

class Speaker {
    public function __construct() {
        //add_action('admin_enqueue_scripts', [$this, 'enqueueGutenberg']);
        //add_action('init', [$this, 'initGutenberg']);
        add_shortcode('speaker', [$this, 'shortcodeOutput']);
    }

    public static function shortcodeOutput($atts, $content = "") {
        global $post;
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
        $id = sanitize_text_field($id);
        $format = sanitize_text_field($format);
        $orderby = ($orderby == 'lastname' ? 'lastname' : 'firstname');

        $args = [
            'post_type' => 'speaker',
            'post_status' => 'publish',
            'posts_per_page' => '-1',
        ];
        if (is_numeric($number)) {
            $args['posts_per_page'] = $number;
        }
        if ((isset($id)) && ( strlen(trim($id)) > 0)) {
            $args ['p'] = $id;
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
                    $out .= '<a href="' . $url . '" rel="bookmark" class="entry-main">' .
                        '<header class="entry-header">';
                    if (get_theme_mod('show_speaker_categories') == true && get_the_terms($post->ID, 'speaker_category') !== false) {
                        $out .= '<div class="entry-cats">' . get_the_term_list( $post->ID, 'speaker_category', null,' | ') . '</div>';
                    }

                    if ('' !== get_the_post_thumbnail() && !post_password_required()) :
                        $out .=     '<div class="entry-thumbnail">';
                        $out .=				'<figure class="thumb-wrap">';
                        $out .=					get_the_post_thumbnail($post->ID, 'large');
                        $out .=				'</figure>';
                        $out .=		'</div>';       // end .entry-thumbnail
                    endif;

                    $out .= '<h2 class="entry-title">' .
                        $title .
                        '</h2>' .
                        '</header>';    // end .entry-header

                    $out .=         '<div class="entry-summary">' .
                        wp_strip_all_tags(get_the_excerpt()) . do_shortcode('[icon icon="solid angles-right"]') .
                        '</div>' .      // end .entry-summary
                        '</a>';      // end .entry-main

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

