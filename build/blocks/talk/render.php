<?php

// Compatibility with Shortcode
$attributes['number'] = $attributes['numTalks'] ?? '';
$attributes['category'] = isset($attributes['selectedCategories']) ? implode(',',$attributes['selectedCategories']) : '';
$attributes['id'] = isset($attributes['selectedTalks']) ? implode(',',$attributes['selectedTalks']) : '';
$attributes['orderby'] = $attributes['orderBy'] ?? 'date';
$attributes['format'] = $attributes['layout'] ?? 'grid';

echo \RRZE\Events\Shortcodes\Talk::shortcodeOutput($attributes);