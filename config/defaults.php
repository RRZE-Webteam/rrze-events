<?php
namespace RRZE\Events\Config;

defined('ABSPATH') || exit;


function getDefaults($key = ''): array {

    $defaults = [
        'rrze-events-speaker-settings' => [
            'image-format'      => 'rounded',
            'show-link-icons'   => 'on',
            'show-talk-list'    => 'on',
            'show-categories'   => 'on',
            'show-tags'         => 'on',
            'talk-order'        => 'by-date'
        ],
        'rrze-events-label-settings' => [
            'label-talk'            => __('Talk', 'rrze-events'),
            'label-talk-plural'     => __('Talks', 'rrze-events'),
            'label-speaker'         => __('Speaker', 'rrze-events'),
            'label-speaker-plural'  => __('Speakers', 'rrze-events'),
            'label-short'           => __('Course Nr', 'rrze-events'),
        ],
        'rrze-events-cfp-settings' => [
            'cfp-form-id'   => '',
            'cfp-talk-title' => '',
            'cfp-talk-excerpt' => '',
            'cfp-talk-description' => '',
            'cfp-speaker-title' => '',
            'cfp-speaker-firstname' => '',
            'cfp-speaker-lastname' => '',
            'cfp-speaker-cv' => '',
            'cfp-speaker-organisation' => '',
            'cfp-speaker-email' => '',
            'cfp-speaker-website' => '',
        ],
    ];

    if ($key != '' && isset($defaults[$key])) {
        return $defaults[$key];
    }
    return $defaults;
}