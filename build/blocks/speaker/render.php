<?php

// Compatibility with Shortcode
$attributes['number'] = $attributes['numSpeakers'] ?? '';
$attributes['category'] = isset($attributes['selectedCategories']) ? implode(',',$attributes['selectedCategories']) : '';
$attributes['id'] = isset($attributes['selectedSpeakers']) ? implode(',',$attributes['selectedSpeakers']) : '';
$attributes['orderby'] = $attributes['orderBy'] ?? 'lastname';
$attributes['format'] = $attributes['layout'] ?? 'grid';

echo esc_html(\RRZE\Events\Shortcodes\Speaker::shortcodeOutput($attributes));