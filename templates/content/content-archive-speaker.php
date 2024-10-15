<?php

use RRZE\Events\Shortcodes\Speaker;
use RRZE\Events\Utils;

global $wp_query;

$queryVars = $wp_query->query_vars;
$atts = [
    'format' => 'grid',
];
if (isset($queryVars['speaker_category']) && $queryVars['speaker_category'] != '') {
    $atts['category'] = esc_html($queryVars['speaker_category']);
}

echo wp_kses(Speaker::shortcodeOutput($atts), Utils::getKsesExtendedRuleset());
