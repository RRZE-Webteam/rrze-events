<?php

namespace RRZE\Events\CPT;

use RRZE\Events\Utils;

class Speaker {
    const POST_TYPE = 'speaker';

    const TAX_CATEGORY = 'speaker_category';

    const TAX_TAG = 'speaker_tag';

    public static function init() {
        // Register Post Type.
        add_action('init', [__CLASS__, 'registerPostType']);
        // Register Taxonomies.
        add_action('init', [__CLASS__, 'registerCategory']);
        add_action('init', [__CLASS__, 'registerTag']);
        // CMB2 Fields
        add_action('cmb2_admin_init', [__CLASS__, 'addFields']);
        // Templates
        add_filter('single_template', [__CLASS__, 'includeSingleTemplate']);
        add_filter('archive_template', [__CLASS__, 'includeArchiveTemplate']);

    }

    public static function registerPostType() {
        $labels = array(
            'name' => _x('Speakers', 'Post Type General Name', 'rrze-events'),
            'singular_name' => _x('Speaker', 'Post Type Singular Name', 'rrze-events'),
            'menu_name' => __('Speakers', 'rrze-events'),
            'parent_item_colon' => __('Parent Item:', 'rrze-events'),
            'all_items' => __('All Speakers', 'rrze-events'),
            'view_item' => __('View Speaker', 'rrze-events'),
            'add_new_item' => __('New Speaker', 'rrze-events'),
            'add_new' => __('New', 'rrze-events'),
            'edit_item' => __('Edit', 'rrze-events'),
            'update_item' => __('Update', 'rrze-events'),
            'search_items' => __('Search Speaker', 'rrze-events'),
            'not_found' => __('Speaker not found', 'rrze-events'),
            'not_found_in_trash' => __('Speaker not found in recycle bin', 'rrze-events'),
        );
        $args = array(
            'label' => __('Speaker', 'rrze-events'),
            //'description' => __('Add and edit speaker information', 'rrze-events'),
            'labels' => $labels,
            'supports' => array('title', 'editor', 'excerpt', 'author', 'thumbnail', 'comments', 'revisions', 'custom-fields', 'page-attributes'),
            'hierarchical' => true,
            'public' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'show_in_nav_menus' => true,
            'show_in_admin_bar' => true,
            'menu_position' => 6,
            'menu_icon' => 'dashicons-businessman',
            'can_export' => true,
            'has_archive' => true,
            'exclude_from_search' => false,
            'publicly_queryable' => true,
            'rewrite' => array('slug' => 'speakers', 'with_front' => FALSE),
            'capability_type' => 'page',
            'show_in_rest' => true,
        );
        register_post_type('speaker', $args);
    }

    public static function registerCategory() {
        $labels = [
            'name'              => _x('Speaker Categories', 'Taxonomy general name', 'rrze-events'),
            'singular_name'     => _x('Speaker Category', 'Taxonomy singular name', 'rrze-events'),
        ];
        $args = [
            'labels'            => $labels,
            'public'            => true,
            'hierarchical'      => true,
            'show_admin_column' => true,
            'show_in_rest'      => true,
            'rewrite'           => ['slug' => 'speaker-category', 'with_front' => false],
        ];
        register_taxonomy(self::TAX_CATEGORY, self::POST_TYPE, $args);
    }

    public static function registerTag() {
        $labels = [
            'name'              => _x('Speaker Tags', 'Taxonomy general name', 'rrze-events'),
            'singular_name'     => _x('Speaker Tag', 'Taxonomy singular name', 'rrze-events'),
        ];
        $args = [
            'labels'            => $labels,
            'public'            => true,
            'hierarchical'      => true,
            'show_admin_column' => true,
            'show_in_rest'      => true,
            'rewrite'           => ['slug' => 'speaker-tags', 'with_front' => false],
        ];
        register_taxonomy(self::TAX_TAG, self::POST_TYPE, $args);
    }

    public static function addFields() {
        $cmb_info = new_cmb2_box([
            'id' => 'speaker_metabox',
            'title' => __('Speaker Information', 'rrze-events'),
            'object_types' => [self::POST_TYPE],
            'context' => 'normal',
            'priority' => 'high',
            'show_names' => true,
        ]);
        $cmb_info->add_field(array(
            'name' => esc_html__('Firma / Organisation', 'rrze-events'),
            //'desc' => esc_html__( '', 'rrze-events' ),
            'id'   => 'speaker_organisation',
            'type' => 'text',
        ));
        $cmb_info->add_field(array(
            'name' => esc_html__('Email', 'rrze-events'),
            //'desc' => esc_html__( '', 'rrze-events' ),
            'id'   => 'speaker_email',
            'type' => 'text_email',
        ));
        $cmb_info->add_field(array(
            'name' => esc_html__('Website', 'rrze-events'),
            //'desc' => esc_html__( '', 'rrze-events' ),
            'id'   => 'speaker_website',
            'type' => 'text_url',
        ));
        $cmb_info->add_field(array(
            'name' => esc_html__('Blog', 'rrze-events'),
            //'desc' => esc_html__( '', 'rrze-events' ),
            'id'   => 'speaker_blog',
            'type' => 'text_url',
        ));
        $cmb_info->add_field(array(
            'name' => esc_html__('Social Media', 'rrze-events'),
            //'desc' => esc_html__( '', 'rrze-events' ),
            'id'   => 'speaker_social_media',
            'type' => 'text_url',
            'repeatable' => true,
            'attributes' => [
                'placeholder' => __('Enter URL...', 'rrze-events'),
            ],
            'text' => [
                'add_row_text' => __('Add URL', 'rrze-events'),
            ],
        ));
    }

    public static function includeSingleTemplate($singleTemplate)
    {
        global $post;
        if (!$post || $post->post_type != 'speaker')
            return $singleTemplate;

        wp_enqueue_style('rrze-events');
        return Utils::getTemplatePath() . 'single-speaker.php';
    }

    public static function includeArchiveTemplate($archiveTemplate)
    {
        global $post;
        if (!$post || $post->post_type != 'speaker')
            return $archiveTemplate;

        return Utils::getTemplatePath() . 'archive-speaker.php';
    }


}