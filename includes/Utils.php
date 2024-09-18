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
            $str .= "<$heading style='clear:both;'>" . get_theme_mod('label-talk-pl', __('Talks', 'rrze-events')) . "</$heading>";
            $str .= "<ul>";
            foreach ($talks as $talk) {
                $str .= "<li><a href='" . get_post_permalink($talk->ID) . "'>";
                $str .= apply_filters('the_title', $talk->post_title);
                $str .= "</a> ";
                $str .= get_the_term_list($talk->ID, 'talk_category', ' &ndash; <div class="entry-cats inline">', ' ', '</div>');
            }
            $str .= "</ul>";
        }
        return $str;
    }

    public static function speakerLinks($speakerID, $type = 'list', $heading = 'h2'): string {

        $str = '';
        if ($type == 'list') {
            $str = "<$heading style=\"clear:both;\">" . __('Contact', 'rrze-events') . "</$heading>";
        }
        $url_fields = array(
            'speaker_email' => __('Email', 'fau-events'),
            'speaker_website' => __('Website', 'fau-events'),
            'speaker_blog' => __('Blog', 'fau-events'),
            'speaker_social_media' => __('Social Media', 'fau-events'),
        );

        $str .= "<ul class=\"speaker-social-$type\">";
        foreach ($url_fields as $url_field => $label) {
            $value = get_post_meta($speakerID, $url_field, true);
            if ($value != '') {
                $str .= "<li>";
                if ($type == 'list') {
                    $str .= "<span class=''>" . $label . "</span>: ";
                }
                if ('speaker_social_media' == $url_field) {
                    foreach ($value as $item) {
                        $str .= "<span class=''><a href=\"$item\">$item</a>";
                    }
                } elseif ('speaker_email' == $url_field) {
                    $str .= "<a href='mailto:".$value."'>" . $value . "</a>";
                } elseif ('speaker_blog' == $url_field) {
                    $str .= "<a href='".$value."' class='blog'>" . $value . "</a>";
                } else {
                    $str .= "<a href='".$value."'>" . $value . "</a>";
                }
                $str .= "</li>";
            }
        }
        $str .= "</ul>";
        return $str;
    }
}