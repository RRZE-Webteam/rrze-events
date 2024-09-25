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

    public static function getTemplatePath($tpl): string {
        switch ($tpl) {
            case 'cpt':
                $currentTheme = wp_get_theme();
                foreach (self::$themes as $slug => $theme) {
                    if (in_array(strtolower($currentTheme->stylesheet), array_map('strtolower', $theme))) {
                        return plugin()->getPath('templates/cpt/themes/') . $slug . '/';
                    }
                }
                break;
            case 'shortcode':
                return plugin()->getPath('templates/') . 'shortcodes/';
        }
        return false;
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
    public static function talksBySpeaker($speakerID, $heading = 'h2'): string {
        $args = array(
            'post_type' => 'talk',
            'orderby' => 'title',
            'order' => 'ASC',
            'numberposts' => -1,
            'meta_key' => 'talk_speakers',
            'meta_value' => '"' . $speakerID . '"',
            'meta_compare' => 'LIKE',
        );
        if ('date' == get_theme_mod('talk_order')) {
            $args['orderby'] = 'post_date';
            $args['order'] = 'DESC';
        }

        $talks = get_posts($args);
        $str = '';

        if (!empty($talks)) {
            $str .= "<$heading>" . get_theme_mod('label-talk-pl', __('Talks', 'rrze-events')) . "</$heading>";
            $str .= "<ul>";
            foreach ($talks as $talk) {
                $str .= "<li><a href='" . get_post_permalink($talk->ID) . "'>";
                $str .= apply_filters('the_title', $talk->post_title);
                $str .= "</a> ";
                $str .= get_the_term_list($talk->ID, 'talk_category', '<div class="speaker-categories inline">', ' ', '</div>');
            }
            $str .= "</ul>";
        }
        return $str;
    }

    public static function speakerLinks($speakerID, $type = 'list', $heading = 'h2'): string {

        $str = '';
        $url_fields = array(
            'speaker_email' => __('Email', 'fau-events'),
            'speaker_website' => __('Website', 'fau-events'),
            'speaker_blog' => __('Blog', 'fau-events'),
            'speaker_social_media' => __('Social Media', 'fau-events'),
        );
        $links = [];
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
            $str .= "<$heading style=\"clear:both;\">" . __('Contact', 'rrze-events') . "</$heading>";
        }
        $str .= "<ul class=\"speaker-social-$type\">";
        foreach ($links as $link) {
            $str .= '<li>';
            if ($type == 'list') {
                $str .= "<span class=''>" . $link['label'] . "</span>: ";
            }
            $str .= '<a href="' . $link['url'] . '" title="' . $link['label'] . '">'
                . '[icon icon="' . $link['icon'] . '" alt="' . $link['label'] . '" style="2x"]'
                .  '<span class="sr-only">' . $link['label'] . '</span>'
            . "</a>";
            $str .= '</li>';

        }

        $str .= "</ul>";

        return do_shortcode($str);
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
}