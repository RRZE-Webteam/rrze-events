<?php

namespace RRZE\Events\Shortcodes;

use RRZE\Events\Utils;

class Talk {
    public function __construct() {
        //add_action('admin_enqueue_scripts', [$this, 'enqueueGutenberg']);
        //add_action('init', [$this, 'initGutenberg']);
        add_shortcode('talk', [$this, 'shortcodeOutput']);
    }

    public static function shortcodeOutput($atts, $content = "") {
        global $post;
        extract(shortcode_atts(array(
            'cat' => '',
            'category' => '',
            'tag' => '',
            'num' => -1,
            'number' => -1,
            'id' => '',
            'format' => '',
            'showimage' => 0,
            'showorganisation' => 1,
            'date' => '',
            'columns' => 'date, duration, title, speaker',
            'orderby' => 'date,ASC,duration,ASC',//,title,ASC
        ), $atts));
        $single = 0;
        $category = ('' != $category) ? sanitize_text_field($category) : sanitize_text_field($cat);
        $tag = sanitize_text_field($tag);
        $number = ('-1' != $num) ? sanitize_text_field($num) : sanitize_text_field($number);
        $id = sanitize_text_field($id);
        $format = sanitize_text_field($format);

        $sorting = explode(',', $orderby);
        $sort = array();
        $sort[1]['key'] = trim($sorting[0]);
        $sort[1]['order'] = $sorting[1] == 'DESC' ? SORT_DESC : SORT_ASC;
        $sort[2]['key'] = (isset($sorting[2])) ? trim($sorting[2]) : NULL;
        $sort[2]['order'] = (isset($sorting[3]) && $sorting[3] == 'DESC') ? SORT_DESC : SORT_ASC;
        $sort[3]['key'] = (isset($sorting[4])) ? trim($sorting[4]) : NULL;
        $sort[3]['order'] = (isset($sorting[5]) && $sorting[5] == 'DESC') ? SORT_DESC : SORT_ASC;

        $args = [
            'post_type' => 'talk',
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
            $cats = explode(',', $category);
            $cats = array_map('trim',$cats);
            $args = array(
                'post_type' => 'talk',
                'relation' => 'AND',
            );
            foreach ($cats as $_c) {
                $args['tax_query'][] = array(
                    'taxonomy' => 'talk_category',
                    'field' => 'slug',
                    'terms' => $_c,
                );
            }
        }

        if (strpos($date, '.')) {
            $dateparts = explode('.', $date);
            $dateparts['year'] = $dateparts[2];
            $dateparts['month'] = $dateparts[1];
            $dateparts['day'] = $dateparts[0];
        } else if (strpos($date, '-')) {
            $dateparts = explode('-', $date);
            $dateparts['year'] = $dateparts[0];
            $dateparts['month'] = $dateparts[1];
            $dateparts['day'] = $dateparts[2];
        } else {
            $date = '';
        }
        if ($date !== '') {
            $args['meta_key'] = 'talk_date';
            $args['meta_value'] = $dateparts['year'] . '-' . $dateparts['month']  . '-' . $dateparts['day'];
        }

        $headers = array(
            'date' => __('Date', 'fau-events'),
            'title' => get_theme_mod('label-talk'),
            'talk' => get_theme_mod('label-talk'),
            'start' => __('Start', 'fau-events'),
            'end' => __('End', 'fau-events'),
            'duration' => __('Time', 'fau-events'),
            'location' => __('Location', 'fau-events'),
            'speaker' => get_theme_mod('label-speaker'),
            'participants' => __('Participants', 'fau-events'),
            'available' => __('Available', 'fau-events'),
            'short' => get_theme_mod('label-short')
        );

        $talks = new \WP_Query($args);
        if ($talks->have_posts()) {
            $i = 0;
            $out = '';

            if (isset($format) && ($format == 'table') && ($single == 0)) {
                $columns = explode(',', $columns);
                $columns = array_map('trim',$columns);

                $out .= '<table class="talk-table">
			<thead>
                            <tr>';
                foreach ($columns as $column) {
                    $out .= '<th scope="col" class="'.$column.'">' . $headers[$column] . '</th>';
                }
                $out .= '</tr>
                </thead>
            <tbody>';
            }

            $posts = array();

            $max = ($number == '-1' ? 999999 : $number);
            while ($talks->have_posts() && ($i < $max)) {
                $talks->the_post();
                $i++;
                $post_id = $post->ID;
                $posts[$post_id]['ID'] = $post_id;
                $posts[$post_id]['title'] = get_the_title();
                $posts[$post_id]['talk'] = get_the_title();
                $posts[$post_id]['excerpt'] = get_the_excerpt();
                $posts[$post_id]['content'] = get_the_content();
                $posts[$post_id]['link'] = get_permalink();
                $meta = get_post_meta($post_id);

                $talk_speaker_ids = (array) Utils::getMeta($meta, 'talk_speakers');
                $posts[$post_id]['speaker_links'] = array();
                foreach ($talk_speaker_ids as $talk_speaker_id) {
                    if (get_post_type($talk_speaker_id) == 'speaker') {
                        $talk_speaker = get_post($talk_speaker_id);
                        $name = $talk_speaker->post_title;
                        $image = $showimage ? get_the_post_thumbnail($talk_speaker->ID) . "<br />" : '';
                        $link = get_post_permalink($talk_speaker->ID);
                        $speaker_organisation = get_post_meta($talk_speaker->ID, 'speaker_organisation', true);
                        $orga = ($showorganisation == 1 && !empty($speaker_organisation)) ? ' (' . get_post_meta($talk_speaker->ID, 'speaker_organisation', true). ')' : '';
                        $posts[$post_id]['speaker_links'][] = '<a href="' . $link . '" title="' . $name . '">' . $image . $name . '</a>' . $orga;
                        $posts[$post_id]['speaker_names'][] = $image . $name;
                    } else {
                        $posts[$post_id]['speaker_links'] = array();
                        $posts[$post_id]['speaker_names'] = array();
                    }
                }
                $posts[$post_id]['shortname'] = Utils::getMeta($meta, 'talk_shortname');
                $talk_meta = strtotime(Utils::getMeta($meta, 'talk_date'));
                if ($talk_meta !== FALSE) {
                    $talk_date = date_i18n( get_option('date_format'), strtotime(Utils::getMeta($meta, 'talk_date')));
                } else {
                    $talk_date = '';
                }
                $posts[$post_id]['printdate'] = $talk_date;
                $posts[$post_id]['date'] = $talk_meta;
                $talk_start = Utils::getMeta($meta, 'talk_start');
                $posts[$post_id]['start'] = $talk_start;
                $posts[$post_id]['dtstamp_start'] = date('Ymd', strtotime($talk_date)) . "T" . date('Hi', strtotime($talk_start));
                $talk_end = Utils::getMeta($meta, 'talk_end');
                $posts[$post_id]['end'] = $talk_end;
                $posts[$post_id]['dtstamp_end'] = date('Ymd', strtotime($talk_date)) . "T" . date('Hi', strtotime($posts[$post_id]['end']));
                $posts[$post_id]['duration'] = $talk_end != '' ? $talk_start . ' - ' . $talk_end : $talk_start;
                $posts[$post_id]['room'] = Utils::getMeta($meta, 'talk_room');
                $posts[$post_id]['max_participants'] = Utils::getMeta($meta, 'talk_max_participants');
                $posts[$post_id]['available'] = Utils::getMeta($meta, 'talk_available');
                $posts[$post_id]['video'] = Utils::getMeta($meta, 'talk_video');
                $posts[$post_id]['slides'] = Utils::getMeta($meta, 'talk_slides');
            }

            foreach ($posts as $key => $row) {
                $sort1[$key] = $row[$sort[1]['key']];
                if ($sort[2]['key']) {
                    $sort2[$key] = $row[$sort[2]['key']] ?? '';
                }
                if ($sort[3]['key']) {
                    $sort3[$key] = $row[$sort[3]['key']] ?? '';
                }
            }

            if ($sort[3]['key']) {
                array_multisort($sort1, $sort[1]['order'], $sort2, $sort[2]['order'], $sort3, $sort[3]['order'], $posts);
            } elseif ($sort[2]['key']) {
                array_multisort($sort1, $sort[1]['order'], $sort2, $sort[2]['order'], $posts);
            } else {
                array_multisort($sort1, $sort[1]['order'], $posts);
            }

            foreach ($posts as $post) {
                if (isset($id) && isset($format) && ($format == 'short')) {
                    // format short
                    $out .= '<p>'
                        . '<a href="' . $post['link'] . '">' . '<span class="titel">'
                        . $post['title']
                        . '</span></a>';
                    if (!empty($post['speaker_links'])) {
                        $out .= ' &ndash; '
                            . '<span class="speaker">'
                            . implode('</span>, <span class="speaker">', $post['speaker_links'])
                            . '</span>';
                    }
                    $out .= '</p>';
                } elseif (isset($format) && ($format == 'table') && ($single == 0)) {
                    // format table
                    // Datum
                    $out .= "<tr class=\"talk\">\n";
                    foreach ($columns as $column) {
                        $out .= '<td>';
                        switch ($column) {
                            case 'date':
                                $out .= $post['printdate'];
                                break;
                            case 'title':
                            case 'talk':
                                $out .= '<a href="' . $post['link'] . '" title="' . $post['title'] . '">' . $post['title'] . '</a>';
                                break;
                            case 'start':
                                $out .= $post['start'];
                                break;
                            case 'end':
                                $out .= $post['end'];
                                break;
                            case 'duration':
                                $out .= $post['duration'];
                                break;
                            case 'location':
                                $out .= $post['room'];
                                break;
                            case 'speaker':
                                $out .= '<span class="speaker">' . implode('</span><br /><span class="speaker">', $post['speaker_links']) . '</span>';
                                break;
                            case 'participants':
                                $out .= $post['max_participants'];
                                break;
                            case 'available':
                                $out .= $post['available'];
                                break;
                            case 'short':
                                $out .= $post['shortname'];
                                break;
                        }
                        $out .= '</td>';
                    }
                } else {
                    // format other
                    $out .= '<article class="shortcode talk" id="post-' . $post['ID'] . '" >';
                    $out .= "\n";
                    $out .= '<header class="titel">';
                    // Titel
                    $out .= '<h3 class="summary"><a href="' . $post['link'] . '">'
                        . $post['title']
                        . '</a></h3>';
                    // Referent
                    $hide = ['media'];
                    if ($showorganisation == 0)
                        $hide[] = 'organisation';
                    //var_dump($hide);
                    //$out .= fau_events_talk_fields($post['ID'], $hide);

                    $out .= '</header>';
                    $out .= "\n";
                    $out .= '<div class="talk_daten">';
                    $out .= "\n";
                    $out .= '<article class="post-entry">';
                    $out .= "\n";

                    if (isset($format) && ($format == 'medium')) {
                        $out .= '<p class="short-description">';
                        $out .= $post['excerpt'];
                        $out .= '</p>';
                    } else {
                        $out .= '<p class="long-description">';
                        $out .= $post['content'];
                        $out .= '</p>';
                    }

                    $out .= "</article>\n";

                    if (isset($format) && ($format != 'medium')) {
                        if ((strlen(trim($post['video'])) > 0) || (strlen(trim($post['slides'])) > 0)) {
                            $out .= '<footer>';
                            $out .= '<ul class="medien">';
                            if (isset($post['video']) && (strlen(trim($post['video'])) > 0)) {
                                $out .= '<li class="video"><a href="' . $post['video'] . '">Videoaufzeichnung</a></li>';
                            }
                            if (isset($post['slides']) && (strlen(trim($post['slides'])) > 0)) {
                                $out .= '<li class="folien"><a href="' . $post['slides'] . '">Vortragsfolien</a></li>';
                            }
                            $out .= '</ul>';
                            $out .= '</footer>';
                        }
                    }
                    $out .= "</div>\n";
                    $out .= "</article>\n";
                }
            }
            if (isset($format) && ($format == 'table') && ($single == 0)) {
                $out .= '</table>';
            }
        } else {
            $out = '<section class="shortcode talk"><p>';
            $out .= __('No talk information found.', 'fau-events');
            $out .= "</p></section>\n";
        }
        wp_reset_postdata();
        return $out;
    }

    protected function talkList($talks) {
        $output = '<ul class="rrze-events talk-list">';
        foreach ($talks as $talk) {
            $output .= '<li>'
                . '<a href="' . $talk['link'] . '">' . '<span class="titel">'
                . $talk['title']
                . '</span></a>';
            if ( ! empty($talk['speaker_links'])) {
                $output .= ' &ndash; '
                    . '<span class="speaker">'
                    . implode('</span>, <span class="speaker">', $talk['speaker_links'])
                    . '</span>';
            }
            $output .= '</li>';
        }
        $output .= '</ul>';
        return $output;
    }
}