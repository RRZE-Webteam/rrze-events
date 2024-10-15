<?php

use RRZE\Events\Shortcodes\Talk;
use RRZE\Events\Utils;

global $wp_query;

$queryVars = $wp_query->query_vars;

$atts = [
    'format' => 'grid',
    'showorganisation' => '0',
];
if (isset($queryVars['talk_category']) && $queryVars['talk_category'] != '') {
    $atts['category'] = esc_html($queryVars['talk_category']);
}
if (isset($queryVars['talk_tag']) && $queryVars['talk_tag'] != '') {
    $atts['tag'] = esc_html($queryVars['talk_tag']);
}

echo wp_kses(Talk::shortcodeOutput($atts), Utils::getKsesExtendedRuleset());