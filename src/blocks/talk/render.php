<?php

// Compatibility with Shortcode
$attributes['number'] = $attributes['numTalks'] ?? '';
$attributes['category'] = isset($attributes['selectedCategories']) ? implode(',',$attributes['selectedCategories']) : '';
$attributes['tag'] = isset($attributes['selectedTags']) ? implode(',',$attributes['selectedTags']) : '';
$attributes['id'] = isset($attributes['selectedTalks']) ? implode(',',$attributes['selectedTalks']) : '';
if (isset($attributes['orderBy']) && $attributes['orderBy'] == 'date') {
    $attributes['orderby'] = $attributes['orderBy'] .','  . ($attributes['orderType'] ?? 'ASC') . 'start' .','  . ($attributes['orderType'] ?? 'ASC');
} else {
    $attributes['orderby'] = ($attributes['orderBy'] ?? 'date') . ',' . ($attributes['orderType'] ?? 'ASC');
}
$attributes['format'] = $attributes['layout'] ?? 'grid';
$attributes['date'] = $attributes['talkDate'] ?? '';
$attributes['showimage'] = $attributes['showImage'] ?? '';
$attributes['showorganisation'] = $attributes['showOrganisation'] ?? '';
$attributes['columns'] = implode(',', $attributes['tableColumns']);

echo esc_html(\RRZE\Events\Shortcodes\Talk::shortcodeOutput($attributes));