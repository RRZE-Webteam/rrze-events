<?php

// Compatibility with Shortcode
$attributes['number'] = $attributes['numSpeakers'] ?? '';
$attributes['category'] = isset($attributes['selectedCategories']) ? implode(',',$attributes['selectedCategories']) : '';
$attributes['id'] = isset($attributes['selectedSpeakers']) ? implode(',',$attributes['selectedSpeakers']) : '';
$attributes['orderby'] = $attributes['orderBy'] ?? 'lastname';
$attributes['format'] = $attributes['layout'] ?? 'grid';

echo wp_kses_post(\RRZE\Events\Shortcodes\Speaker::shortcodeOutput($attributes));