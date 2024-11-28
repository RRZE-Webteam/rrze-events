<?php
namespace RRZE\Events;

defined('ABSPATH') || exit;

class Utils {

    /**
     * FAU & RRZE Themes
     * @var array
     */
    protected static $themes = [
        'fau' => [
            'FAU-Einrichtungen',
            'FAU-Einrichtungen-BETA',
            'FAU-Medfak',
            'FAU-RWFak',
            'FAU-Philfak',
            'FAU-Techfak',
            'FAU-Natfak'
        ],
        'rrze' => [
            'rrze-2019'
        ],
        'vendor' => [
            'Francesca-Child'
        ]
    ];

    public static function getTemplatePath(): string {
        $currentTheme = wp_get_theme();
        foreach (self::$themes as $slug => $theme) {
            if (in_array(strtolower($currentTheme->stylesheet), array_map('strtolower', $theme))) {
                return plugin()->getPath('templates/themes/') . $slug . '/';
            }
        }
        return plugin()->getPath('templates/');
    }

    public static function getMeta($meta, $key)
    {
        if (!isset($meta[$key][0]))
            return '';
        if (str_starts_with($meta[$key][0], 'a:')) {
            return unserialize($meta[$key][0]);
        } else {
            return $meta[$key][0];
        }
    }

    /**
     * Display a speaker's talks
     */
    public static function talksBySpeaker($speakerID, $orderBy='date', $heading = 'h2'): string {
        $args = array(
            'post_type' => 'talk',
            'numberposts' => -1,
            'meta_query' => [
                'relation' => 'AND',
                [
                'key' => 'talk_speakers',
                'value' => '"' . $speakerID . '"',
                'compare' => 'LIKE',
                ]]
        );
        if ($orderBy == 'date') {
            $args['orderby'] = 'meta_value';
            $args['meta_key'] = 'talk_date';
            $args['order'] = 'ASC';
        } else {
            $args['orderby'] = 'title';
            $args['order'] = 'ASC';
        }

        $talks = get_posts($args);
        $output = '';

        if (!empty($talks)) {
            $labels = Settings::getOption('rrze-events-label-settings');
            $output .= "<$heading>" . $labels['label-talk-plural'] . "</$heading>";
            $output .= "<ul>";
            foreach ($talks as $talk) {
                $output .= "<li><a href='" . get_post_permalink($talk->ID) . "'>";
                $output .= apply_filters('the_title', $talk->post_title);
                $output .= "</a> ";
                $output .= get_the_term_list($talk->ID, 'talk_category', '<div class="speaker-categories inline">', ' ', '</div>');
                //$output .= get_post_meta($talk->ID, 'talk_date', true);
            }
            $output .= "</ul>";
        }
        return $output;
    }

    public static function speakerLinks($speakerID, $type = 'list', $heading = 'h2'): string {

        $output = '';
        $url_fields = array(
            'speaker_email' => __('Email', 'rrze-events'),
            'speaker_website' => __('Website', 'rrze-events'),
            'speaker_blog' => __('Blog', 'rrze-events'),
            'speaker_social_media' => __('Social Media', 'rrze-events'),
        );
        $links = [];
        $settings = Settings::getOption('rrze-events-settings');
        $accentColor = $settings['accent-color'];
        foreach ($url_fields as $url_field => $label) {
            $linkData = [];
            if ('speaker_social_media' == $url_field) {
                $value = get_post_meta($speakerID, $url_field, true);
                if ($value == '')
                    continue;
                foreach ($value as $url) {
                    if ($url == '')
                        continue;
                    $linkData['url'] = $url;
                    if (str_contains($url, 'twitter.com')) {
                        $linkData['icon'] = 'brands x-twitter';
                        $linkData['label'] = 'Twitter';
                    } elseif (str_contains($url, 'bsky.app')) {
                        $linkData['icon'] = 'brands bluesky';
                        $linkData['label'] = 'Bluesky';
                    } elseif (str_contains($url, 'xing.com')) {
                        $linkData['icon'] = 'brands xing';
                        $linkData['label'] = 'Xing';
                    } elseif (str_contains($url, 'linkedin.com')) {
                        $linkData['icon'] = 'brands linkedin';
                        $linkData['label'] = 'LinkedIn';
                    } elseif (str_contains($url, 'github.com')) {
                        $linkData['icon'] = 'brands github';
                        $linkData['label'] = 'GitHub';
                    } elseif (str_contains($url, 'gitlab')) {
                        $linkData['icon'] = 'brands gitlab';
                        $linkData['label'] = 'Gitlab';
                    } elseif (str_contains($url, 'facebook')) {
                        $linkData['icon'] = 'brands facebook';
                        $linkData['label'] = 'Facebook';
                    } elseif (str_contains($url, 'instagram')) {
                        $linkData['icon'] = 'brands instagram';
                        $linkData['label'] = 'Instagram';
                    } elseif (str_contains($url, 'pinterest')) {
                        $linkData['icon'] = 'brands pinterest';
                        $linkData['label'] = 'Pinterest';
                    } elseif (str_contains($url, 'youtube')) {
                        $linkData['icon'] = 'brands youtube';
                        $linkData['label'] = 'Youtube';
                    } elseif (str_contains($url, 'orcid.org')) {
                        $linkData['icon'] = 'brands orcid';
                        $linkData['label'] = 'ORCID';
                    } else {
                        $linkData['icon'] = 'solid link';
                        $linkData['label'] = 'Profile';
                    }
                    $links[] = $linkData;
                }
            } else {
                $url = get_post_meta($speakerID, $url_field, TRUE);
                if ($url == '')
                    continue;
                switch ($url_field) {
                    case 'speaker_email':
                        $linkData['url'] = 'mailto:' . $url;
                        $linkData['icon'] = 'regular envelope';
                        break;
                    case 'speaker_website':
                        $linkData['url'] = $url;
                        $linkData['icon'] = 'solid link';
                        break;
                    case 'speaker_blog':
                        $linkData['url'] = $url;
                        $linkData['icon'] = 'solid blog';
                        break;
                }
                $linkData['label'] = $label;
                $links[] = $linkData;
            }
        }

        //print "<pre>"; var_dump($links);print "</pre>";

        if (empty($links))
            return '';

        if ($type == 'list') {
            $output .= "<$heading style=\"clear:both;\">" . __('Contact', 'rrze-events') . "</$heading>";
        }
        $output .= "<ul class=\"speaker-social-$type\">";
        foreach ($links as $link) {
            $output .= '<li>';
            if ($type == 'list') {
                $output .= "<span class=''>" . $link['label'] . "</span>: ";
            }
            $output .= '<a href="' . $link['url'] . '" title="' . $link['label'] . '">'
                . '[icon icon="' . $link['icon'] . '" alt="' . $link['label'] . '" style="2x" color="' . $accentColor . '"]'
                .  '<span class="sr-only">' . $link['label'] . '</span>'
            . "</a>";
            $output .= '</li>';

        }

        $output .= "</ul>";

        return do_shortcode($output);
    }

    public static function talkFields($post_id = null, $hide = []) {

        if (!$post_id) {
            global $post;
            $post_id = $post->ID;
        }

        $output = '';
        $meta = get_post_meta($post_id);
        $talk_short = self::getMeta($meta, 'talk_shortname');
        $talk_date = self::getMeta($meta, 'talk_date');
        $talk_start = self::getMeta($meta, 'talk_start');
        $dtstamp_beginn = gmdate('Ymd', strtotime($talk_date)) . "T" . gmdate('Hi', strtotime($talk_start));
        $talk_end = self::getMeta($meta, 'talk_end');
        $dtstamp_ende = gmdate('Ymd', strtotime($talk_date)) . "T" . gmdate('Hi', strtotime($talk_end));
        $talk_room = self::getMeta($meta, 'talk_room');
        $talk_room_url = self::getMeta($meta, 'talk_room_url');
        $talk_max_participants = self::getMeta($meta, 'talk_max_participants');
        $talk_available = self::getMeta($meta, 'talk_available');
        $talk_video = self::getMeta($meta, 'talk_video');
        $talk_slides = self::getMeta($meta, 'talk_slides');
        $settings = Settings::getOption('rrze-events-settings');
        $accentColor = $settings['accent-color'];

        // Speaker(s)
        if (!in_array('speaker', $hide)) {
            $speakers = self::getMeta($meta, 'talk_speakers');
            if ($speakers != '') {
                $speakerLinks = [];
                foreach ($speakers as $speakerID) {
                    if ( ! in_array('organisation', $hide)) {
                        $organisation = get_post_meta($speakerID, 'speaker_organisation', TRUE);
                    } else {
                        $organisation = '';
                    }
                    $speakerLinks[] = '<a href="' . get_permalink($speakerID) . '">' . get_the_title($speakerID) . '</a>'
                        . ($organisation != '' ? ' (' . $organisation . ')' : '');
                }
                $output .= '<div class="talk-speaker" title="' . __('Speaker', 'rrze-events') . '">[icon icon="solid user" color="' . $accentColor . '"]<span class="sr-only">' . __('Speaker', 'rrze-events') . ': </span>';
                $output .= implode(', ', $speakerLinks);
                $output .= '</div>';
            }
        }

        // Date/Time
        $date = Utils::getMeta($meta, 'talk_date');
        $start = Utils::getMeta($meta, 'talk_start');
        $tsStart = strtotime($date . ' ' . $start);
        $tsStartUTC = get_gmt_from_date(gmdate('Y-m-d H:i', $tsStart), 'U');
        $end = Utils::getMeta($meta, 'talk_end');
        $metaStart = '<meta itemprop="startDate" content="'. gmdate('c', $tsStartUTC) . '" />';
        if ($end != '') {
            $tsEnd = strtotime($date . ' ' . $end);
            $tsEndUTC = get_gmt_from_date(gmdate('Y-m-d H:i', $tsEnd), 'U');
            $metaEnd = '<meta itemprop="endDate" content="'. gmdate('c', $tsEndUTC) . '" />';
        } else {
            $metaEnd = '';
        }
        if ($date . $start . $end != '') {
            $output .= '<div class="talk-datetime">'
                . ($date != '' ? '<span class="date" title="' . __('Date', 'rrze-events') . '">[icon icon="regular calendar" color="' . $accentColor . '"] '
                    . '<span class="sr-only">' . __('Date', 'rrze-events') . ': </span>'
                    . date_i18n('d.m.Y', $tsStart) . '</span>' : '')
                . ($start .$end != '' ? '<span class="time" title="' . __('Time', 'rrze-events') . '">'
                    . '[icon icon="regular clock" color="' . $accentColor . '"] '
                    . '<span class="sr-only">' . __('Time', 'rrze-events') . ': </span>'
                    . $start
                    . ($end != '' ? ' - ' . $end : '')
                    . '</span>' : '')
                . $metaStart . $metaEnd;
            $output .= '</div>';
        }

        // Location
        if ($talk_room != '') {
            $output .= '<div class="talk-location" title="' . __('Location', 'rrze-events') . '">'
                . '[icon icon="solid location-dot" color="' . $accentColor . '"]'
                . '<span class="sr-only">' . __('Location', 'rrze-events') . ': </span>'
                . ($talk_room_url != '' ? '<a href="' . $talk_room_url . '">' : '')
                . $talk_room
                . ($talk_room_url != '' ? '</a>' : '')
                . '<meta itemprop="location" content="' . wp_strip_all_tags($talk_room) . '">'
                . '</div>';
        }

        // Participants
        if ($talk_max_participants != '' || $talk_available != '') {
            $output .= '<div class="talk-participants" title="' . __('Participants', 'rrze-events') . '">'
                . '[icon icon="solid users" color="' . $accentColor . '"]'
                . '<span class="sr-only">' . __('Participants', 'rrze-events') . ': </span>';
            if ($talk_max_participants != '') {
                $output .= $talk_max_participants . ' '. __('Participants', 'rrze-events');
            }
            if ($talk_available != '') {
                $output .= ' (' . $talk_available . '&nbsp;' . __('available', 'rrze-events') . ')';
            }
            $output .= '</div>';
        }

        if (!in_array('media', $hide) && ('' != $talk_video || '' != $talk_slides)) {
            $output .= '<div class="talk-media"><ul>';
            if ('' != $talk_video) {
                $output .= '<li class="video" title="' . __('Participants', 'rrze-events') . '">[icon icon="solid video" color="' . $accentColor . '"] <a href="' . $talk_video . '">' . __('Video','rrze-events') . '</a></li>';
            }
            if ('' != $talk_slides) {
                $output .= '<li class="folien" title="' . __('Participants', 'rrze-events') . '">[icon icon="regular file-powerpoint" color="' . $accentColor . '"] <a href="' . $talk_slides . '">' . __('Slides','rrze-events') . '</a></li>';
            }
            $output .= '</ul></div>';
        }


        return do_shortcode($output);

    }

    public static function getDaysOfWeek($format = null)
    {
        $days = [];
        $daysOfWeek = self::daysOfWeek($format);
        $startOfWeek = get_option('start_of_week', 0);
        for ($i = 0; $i < 7; $i++) {
            $weekDay = ($i + $startOfWeek) % 7;
            $days[$weekDay] = $daysOfWeek[$weekDay];
        }
        return $days;
    }

    public static function daysOfWeek($format = null)
    {
        global $wp_locale;
        $daysOfWeek = [];
        switch ($format) {
            case 'rrule':
                $daysOfWeek = [
                    'monday' => 'MO',
                    'tuesday' => 'TU',
                    'wednesday' => 'WE',
                    'thursday' => 'TH',
                    'friday' => 'FR',
                    'saturday' => 'SA',
                    'sunday' => 'SU',
                ];
                break;
            case 'min':
                $daysOfWeek = [
                    0 => $wp_locale->get_weekday_initial($wp_locale->get_weekday(0)),
                    1 => $wp_locale->get_weekday_initial($wp_locale->get_weekday(1)),
                    2 => $wp_locale->get_weekday_initial($wp_locale->get_weekday(2)),
                    3 => $wp_locale->get_weekday_initial($wp_locale->get_weekday(3)),
                    4 => $wp_locale->get_weekday_initial($wp_locale->get_weekday(4)),
                    5 => $wp_locale->get_weekday_initial($wp_locale->get_weekday(5)),
                    6 => $wp_locale->get_weekday_initial($wp_locale->get_weekday(6)),
                ];
                break;
            case 'short':
                $daysOfWeek = [
                    0 => $wp_locale->get_weekday_abbrev($wp_locale->get_weekday(0)),
                    1 => $wp_locale->get_weekday_abbrev($wp_locale->get_weekday(1)),
                    2 => $wp_locale->get_weekday_abbrev($wp_locale->get_weekday(2)),
                    3 => $wp_locale->get_weekday_abbrev($wp_locale->get_weekday(3)),
                    4 => $wp_locale->get_weekday_abbrev($wp_locale->get_weekday(4)),
                    5 => $wp_locale->get_weekday_abbrev($wp_locale->get_weekday(5)),
                    6 => $wp_locale->get_weekday_abbrev($wp_locale->get_weekday(6)),
                ];
                break;
            case 'full':
            default:
                $daysOfWeek = [
                    0 => $wp_locale->get_weekday(0),
                    1 => $wp_locale->get_weekday(1),
                    2 => $wp_locale->get_weekday(2),
                    3 => $wp_locale->get_weekday(3),
                    4 => $wp_locale->get_weekday(4),
                    5 => $wp_locale->get_weekday(5),
                    6 => $wp_locale->get_weekday(6),
                ];
                break;
        }
        return $daysOfWeek;
    }

    public static function getMonthNames($format = null)
    {
        global $wp_locale;
        $monthNames = [];
        switch ($format) {
            case 'rrule':
                $monthNames = [
                    'jan' => 1,
                    'feb' => 2,
                    'mar' => 3,
                    'apr' => 4,
                    'may' => 5,
                    'jun' => 6,
                    'jul' => 7,
                    'aug' => 8,
                    'sep' => 9,
                    'oct' => 10,
                    'nov' => 11,
                    'dec' => 12,
                ];
                break;
            case 'short':
                $monthNames = [
                    0 => $wp_locale->get_month_abbrev($wp_locale->get_month('01')),
                    1 => $wp_locale->get_month_abbrev($wp_locale->get_month('02')),
                    2 => $wp_locale->get_month_abbrev($wp_locale->get_month('03')),
                    3 => $wp_locale->get_month_abbrev($wp_locale->get_month('04')),
                    4 => $wp_locale->get_month_abbrev($wp_locale->get_month('05')),
                    5 => $wp_locale->get_month_abbrev($wp_locale->get_month('06')),
                    6 => $wp_locale->get_month_abbrev($wp_locale->get_month('07')),
                    7 => $wp_locale->get_month_abbrev($wp_locale->get_month('08')),
                    8 => $wp_locale->get_month_abbrev($wp_locale->get_month('09')),
                    9 => $wp_locale->get_month_abbrev($wp_locale->get_month('10')),
                    10 => $wp_locale->get_month_abbrev($wp_locale->get_month('11')),
                    11 => $wp_locale->get_month_abbrev($wp_locale->get_month('12')),
                    12 => $wp_locale->get_month_abbrev($wp_locale->get_month('12')),
                ];
                break;
            case 'full':
            default:
                $monthNames = [
                    0 => $wp_locale->get_month('01'),
                    1 => $wp_locale->get_month('02'),
                    2 => $wp_locale->get_month('03'),
                    3 => $wp_locale->get_month('04'),
                    4 => $wp_locale->get_month('05'),
                    5 => $wp_locale->get_month('06'),
                    6 => $wp_locale->get_month('07'),
                    7 => $wp_locale->get_month('08'),
                    8 => $wp_locale->get_month('09'),
                    9 => $wp_locale->get_month('10'),
                    10 => $wp_locale->get_month('11'),
                    11 => $wp_locale->get_month('12'),
                    12 => $wp_locale->get_month('12'),
                ];
                break;
        }
        return $monthNames;
    }

    public static function getKsesExtendedRuleset() {
        $kses_defaults = wp_kses_allowed_html('post');
        $svg_args = [
            'svg' => [
                'class' => TRUE,
                'aria-hidden' => TRUE,
                'aria-labelledby' => TRUE,
                'role' => TRUE,
                'xmlns' => TRUE,
                'width' => TRUE,
                'height' => TRUE,
                'viewbox' => TRUE, // <= Must be lower case!
                'style' => [
                    'fill' => TRUE,
                    'font-size' => TRUE,
                ],
                'alt' => TRUE,
            ],
            'g' => ['fill' => TRUE],
            'title' => ['title' => TRUE],
            'path' => [
                'd' => TRUE,
                'fill' => TRUE,
            ],
        ];
        return array_merge($kses_defaults, $svg_args);
    }

}