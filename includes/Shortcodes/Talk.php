<?php

namespace RRZE\Events\Shortcodes;

use RRZE\Events\Settings;
use RRZE\Events\Utils;

class Talk {
    public function __construct() {
        //add_action('admin_enqueue_scripts', [$this, 'enqueueGutenberg']);
        //add_action('init', [$this, 'initGutenberg']);
        add_shortcode('talk', [$this, 'shortcodeOutput']);
    }

    public static function shortcodeOutput($atts, $content = "") {
        global $post;
        $atts = self::sanitizeAtts($atts);

        $single = 0;

        $sorting = explode(',', $atts['orderby']);
        $sort = array();
        $sort[1]['key'] = trim($sorting[0]);
        $sort[1]['order'] = (isset($sorting[1]) && $sorting[1] == 'DESC') ? SORT_DESC : SORT_ASC;
        $sort[2]['key'] = (isset($sorting[2])) ? trim($sorting[2]) : NULL;
        $sort[2]['order'] = (isset($sorting[3]) && $sorting[3] == 'DESC') ? SORT_DESC : SORT_ASC;
        $sort[3]['key'] = (isset($sorting[4])) ? trim($sorting[4]) : NULL;
        $sort[3]['order'] = (isset($sorting[5]) && $sorting[5] == 'DESC') ? SORT_DESC : SORT_ASC;

        $args = [
            'post_type' => 'talk',
            'post_status' => 'publish',
            'posts_per_page' => '-1',
        ];

        if ((isset($atts['id'])) && ( strlen(trim($atts['id'])) > 0)) {
            $args ['p'] = $atts['id'];
            $single = 1;
        } elseif ((isset($atts['category'])) && ( strlen(trim($atts['category'])) > 0)) {
            $cats = explode(',', $atts['category']);
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

        if ((isset($atts['tag'])) && ( strlen(trim($atts['tag'])) > 0)) {
            $tags = explode(',', $atts['tag']);
            $tags = array_map('trim',$tags);
            $args = array(
                'post_type' => 'talk',
                'relation' => 'AND',
            );
            foreach ($tags as $_t) {
                $args['tax_query'][] = array(
                    'taxonomy' => 'talk_tag',
                    'field' => 'slug',
                    'terms' => $_t,
                );
            }
        }

        if (strpos($atts['date'], '.')) {
            $dateparts = explode('.', $atts['date']);
            $dateparts['year'] = $dateparts[2];
            $dateparts['month'] = $dateparts[1];
            $dateparts['day'] = $dateparts[0];
        } else if (strpos($atts['date'], '-')) {
            $dateparts = explode('-', $atts['date']);
            $dateparts['year'] = $dateparts[0];
            $dateparts['month'] = $dateparts[1];
            $dateparts['day'] = $dateparts[2];
        } else {
            $atts['date'] = '';
        }
        if ($atts['date'] !== '') {
            $args['meta_key'] = 'talk_date';
            $args['meta_value'] = $dateparts['year'] . '-' . $dateparts['month']  . '-' . $dateparts['day'];
        }

        $talks = new \WP_Query($args);

        if ($talks->have_posts()) {

            $talkData = array();

            while ($talks->have_posts()) {
                $talks->the_post();

                $post_id = $post->ID;
                $talkData[$post_id]['ID'] = $post_id;
                $talkData[$post_id]['title'] = get_the_title();
                $talkData[$post_id]['talk'] = get_the_title();
                $talkData[$post_id]['excerpt'] = get_the_excerpt();
                $talkData[$post_id]['content'] = get_the_content();
                $talkData[$post_id]['link'] = get_permalink();
                $meta = get_post_meta($post_id);

                $talk_speaker_ids = (array) Utils::getMeta($meta, 'talk_speakers');
                $talkData[$post_id]['speaker_links'] = array();
                foreach ($talk_speaker_ids as $talk_speaker_id) {
                    if (get_post_type($talk_speaker_id) == 'speaker') {
                        $talk_speaker = get_post($talk_speaker_id);
                        $name = $talk_speaker->post_title;
                        $image = $atts['showimage'] ? get_the_post_thumbnail($talk_speaker->ID) . "<br />" : '';
                        $link = get_post_permalink($talk_speaker->ID);
                        $speaker_organisation = get_post_meta($talk_speaker->ID, 'speaker_organisation', true);
                        $orga = ($atts['showorganisation'] == 1 && !empty($speaker_organisation)) ? ' (' . get_post_meta($talk_speaker->ID, 'speaker_organisation', true). ')' : '';
                        $talkData[$post_id]['speaker_links'][] = '<a href="' . $link . '" title="' . $name . '">' . $image . $name . '</a>' . $orga;
                        $talkData[$post_id]['speaker_names'][] = $image . $name;
                    } else {
                        $talkData[$post_id]['speaker_links'] = array();
                        $talkData[$post_id]['speaker_names'] = array();
                    }
                }
                $talkData[$post_id]['shortname'] = Utils::getMeta($meta, 'talk_shortname');
                $talk_meta = strtotime(Utils::getMeta($meta, 'talk_date'));
                if ($talk_meta !== FALSE) {
                    $talk_date = date_i18n( get_option('date_format'), strtotime(Utils::getMeta($meta, 'talk_date')));
                } else {
                    $talk_date = '';
                }
                $talkData[$post_id]['printdate'] = $talk_date;
                $talkData[$post_id]['date'] = $talk_meta;
                $talk_start = Utils::getMeta($meta, 'talk_start');
                $talkData[$post_id]['start'] = $talk_start;
                $talkData[$post_id]['dtstamp_start'] = gmdate('Ymd', strtotime($talk_date)) . "T" . gmdate('Hi', strtotime($talk_start));
                $talk_end = Utils::getMeta($meta, 'talk_end');
                $talkData[$post_id]['end'] = $talk_end;
                $talkData[$post_id]['dtstamp_end'] = gmdate('Ymd', strtotime($talk_date)) . "T" . gmdate('Hi', strtotime($talkData[$post_id]['end']));
                $talkData[$post_id]['duration'] = $talk_end != '' ? $talk_start . ' - ' . $talk_end : $talk_start;
                $talkData[$post_id]['room'] = Utils::getMeta($meta, 'talk_room');
                $talkData[$post_id]['max_participants'] = Utils::getMeta($meta, 'talk_max_participants');
                $talkData[$post_id]['available'] = Utils::getMeta($meta, 'talk_available');
            }

            foreach ($talkData as $key => $row) {
                $sort1[$key] = $row[$sort[1]['key']];
                if ($sort[2]['key']) {
                    $sort2[$key] = $row[$sort[2]['key']] ?? '';
                }
                if ($sort[3]['key']) {
                    $sort3[$key] = $row[$sort[3]['key']] ?? '';
                }
            }

            if ($sort[3]['key']) {
                array_multisort($sort1, $sort[1]['order'], $sort2, $sort[2]['order'], $sort3, $sort[3]['order'], $talkData);
            } elseif ($sort[2]['key']) {
                array_multisort($sort1, $sort[1]['order'], $sort2, $sort[2]['order'], $talkData);
            } else {
                array_multisort($sort1, $sort[1]['order'], $talkData);
            }

            if (isset($atts['format']) && ($atts['format'] == 'short')) {
                $output = self::talkList($talkData, $atts);
            } elseif (isset($atts['format']) && ($atts['format'] == 'table')) {
                $output = self::talkTable($talkData, $atts);
            } else {
                $output = self::talkGrid($talkData, $atts);
            }

        } else {
            $output = '<section class="shortcode talk"><p>';
            $output .= __('No talk information found.', 'rrze-events');
            $output .= "</p></section>\n";
        }
        wp_reset_postdata();
        wp_enqueue_style('rrze-events');
        return $output;
    }

    protected static function talkList($talks, $atts) {

        $max = ($atts['number'] == '-1' ? 999999 : $atts['number']);
        $i = 0;

        $output = '<ul class="rrze-events talk-list">';
        foreach ($talks as $talk) {
            $output .= '<li>'
                . '<a href="' . $talk['link'] . '">' . '<span class="title">'
                . $talk['title']
                . '</span></a>';
            if ( ! empty($talk['speaker_links'])) {
                $output .= ' &ndash; '
                    . '<span class="speaker">'
                    . implode('</span>, <span class="speaker">', $talk['speaker_links'])
                    . '</span>';
            }
            $output .= '</li>';

            $i ++;
            if ($i >= $max)
                break;
        }
        $output .= '</ul>';
        return $output;
    }

    protected static function talkTable ($talkData, $atts) {
        $headers = array(
            'date' => __('Date', 'rrze-events'),
            'title' => get_theme_mod('label-talk'),
            'talk' => get_theme_mod('label-talk'),
            'start' => __('Start', 'rrze-events'),
            'end' => __('End', 'rrze-events'),
            'duration' => __('Time', 'rrze-events'),
            'location' => __('Location', 'rrze-events'),
            'speaker' => get_theme_mod('label-speaker'),
            'participants' => __('Participants', 'rrze-events'),
            'available' => __('Available', 'rrze-events'),
            'short' => get_theme_mod('label-short'),
        );

        $columns = explode(',', $atts['columns']);
        $columns = array_map('trim',$columns);

        $max = ($atts['number'] == '-1' ? 999999 : $atts['number']);
        $i = 0;

        $output = '<table class="talk-table">
			<thead>
                <tr>';
        foreach ($columns as $column) {
            $output .= '<th scope="col" class="'.$column.'">' . $headers[$column] . '</th>';
        }
        $output .= '</tr>
                </thead>
            <tbody>';
        foreach ($talkData as $talk) {
            $output .= "<tr class=\"talk\">\n";
            foreach ($columns as $column) {
                $output .= '<td>';
                switch ($column) {
                    case 'date':
                        $output .= $talk['printdate'];
                        break;
                    case 'title':
                    case 'talk':
                        $output .= '<a href="' . $talk['link'] . '" title="' . $talk['title'] . '">' . $talk['title'] . '</a>';
                        break;
                    case 'start':
                        $output .= $talk['start'];
                        break;
                    case 'end':
                        $output .= $talk['end'];
                        break;
                    case 'duration':
                        $output .= $talk['duration'];
                        break;
                    case 'location':
                        $output .= $talk['room'];
                        break;
                    case 'speaker':
                        $output .= '<span class="speaker">' . implode('</span><br /><span class="speaker">', $talk['speaker_links']) . '</span>';
                        break;
                    case 'participants':
                        $output .= $talk['max_participants'];
                        break;
                    case 'available':
                        $output .= $talk['available'];
                        break;
                    case 'short':
                        $output .= $talk['shortname'];
                        break;
                }
                $output .= '</td>';
            }
            $output .= '</tr>';

            $i ++;
            if ($i >= $max)
                break;
        }

        $output .= '</tbody>
                </table>';

        return $output;
    }

    private static function talkGrid($talkData, $atts) {
        $settings = Settings::getOption('rrze-events-settings');
        $accentColor = $settings['accent-color'];
        $max = ($atts['number'] == '-1' ? 999999 : $atts['number']);
        $i = 0;

        $output = '<div class="rrze-events talk-' . $atts['format'] . '">';
        foreach ($talkData as $talk) {
            $talk['video'] = get_post_meta($talk['ID'], 'talk_video', true);
            $talk['slides'] = get_post_meta($talk['ID'], 'talk_slides', true);
            $output .= '<article class="shortcode talk" id="post-' . $talk['ID'] . '" class="' . implode(' ', get_post_class('', $talk['ID'])) . '">';
            $output .= '<header class="titel">';
            // Titel
            $output .= '<h3 class="summary"><a href="' . $talk['link'] . '" rel="bookmark">' . $talk['title'] . '</a></h3>';
            $output .= '</header>';

            $output .= '<div class="talk-data">';
            $hide = ['media'];
            if ($atts['showorganisation'] == 0) {
                $hide[] = 'organisation';
            }
            $output .= Utils::talkFields($talk['ID'], $hide);
            $output .= "</div>";

            $output .= '<div class="post-entry">';

            $output .= '<p class="short-description">';
            $output .= wp_strip_all_tags($talk['excerpt']) . '<a href="' . $talk['link'] . '" rel="bookmark" class="read-more">' . do_shortcode('[icon icon="solid angles-right" alt="' . __('Read more about', 'rrze-events') . '" color="' . $accentColor . '"]') . '</a>';
            $output .= '</p>';

            $output .= "</div>";
            $output .= "</article>";

            $i ++;
            if ($i >= $max)
                break;
        }
        $output .= '</div>';
        return $output;
    }

    private static function sanitizeAtts($atts) {
        $defaults = [
            'cat' => '',
            'category' => '',
            'tag' => '',
            'num' => -1,
            'number' => -1,
            'id' => '',
            'format' => 'grid',
            'showimage' => 0,
            'showorganisation' => 0,
            'date' => '',
            'columns' => 'date, duration, title, speaker',
            'orderby' => 'date,ASC,duration,ASC',//,title,ASC
        ];
        $args = shortcode_atts($defaults, $atts);
        $args['number'] = ($args['num'] != '-1') ? $args['num'] : $args['number'];
        $args['category'] = ($args['cat'] != '') ? $args['cat'] : $args['category'];
        array_walk($args, 'sanitize_text_field');

        return $args;
    }
}