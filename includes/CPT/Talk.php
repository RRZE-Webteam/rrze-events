<?php

namespace RRZE\Events\CPT;

use RRZE\Events\Utils;

class Talk {
    const POST_TYPE = 'talk';

    const TAX_CATEGORY = 'talk_category';

    const TAX_TAG = 'talk_tag';

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
            'name' => _x('Talks', 'Post Type General Name', 'rrze-events'),
            'singular_name' => _x('Talk', 'Post Type Singular Name', 'rrze-events'),
            'menu_name' => __('Talks', 'rrze-events'),
            'parent_item_colon' => __('Parent Item:', 'rrze-events'),
            'all_items' => __('All Talks', 'rrze-events'),
            'view_item' => __('View Talk', 'rrze-events'),
            'add_new_item' => __('New Talk', 'rrze-events'),
            'add_new' => __('New', 'rrze-events'),
            'edit_item' => __('Edit', 'rrze-events'),
            'update_item' => __('Update', 'rrze-events'),
            'search_items' => __('Search Talk', 'rrze-events'),
            'not_found' => __('Talk not found', 'rrze-events'),
            'not_found_in_trash' => __('Talk not found in recycle bin', 'rrze-events'),
        );
        $args = array(
            'label' => __('Talk', 'rrze-events'),
            //'description' => __('Add and edit talk information', 'rrze-events'),
            'labels' => $labels,
            'supports' => array('title', 'editor', 'excerpt', 'author', 'thumbnail', 'little-promo-boxes', 'comments', 'revisions', 'custom-fields', 'page-attributes'),
            'hierarchical' => true,
            'public' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'show_in_nav_menus' => true,
            'show_in_admin_bar' => true,
            'menu_position' => 5,
            'menu_icon' => 'dashicons-calendar',
            'can_export' => true,
            'has_archive' => true,
            'exclude_from_search' => false,
            'publicly_queryable' => true,
            'rewrite' => array('slug' => 'talks', 'with_front' => FALSE),
            'capability_type' => 'page',
            'show_in_rest' => true,
        );
        register_post_type('talk', $args);
    }

    public static function registerCategory() {
        $labels = [
            'name'              => _x('Talk Categories', 'Taxonomy general name', 'rrze-events'),
            'singular_name'     => _x('Talk Category', 'Taxonomy singular name', 'rrze-events')
        ];
        $args = [
            'labels'            => $labels,
            'public'            => true,
            'hierarchical'      => true,
            'show_admin_column' => true,
            'show_in_rest'      => true,
            'rewrite'           => ['slug' => 'talk-category', 'with_front' => false]
        ];
        register_taxonomy(self::TAX_CATEGORY, self::POST_TYPE, $args);
    }

    public static function registerTag() {
        $labels = [
            'name'              => _x('Talk Tags', 'Taxonomy general name', 'rrze-events'),
            'singular_name'     => _x('Talk Tag', 'Taxonomy singular name', 'rrze-events')
        ];
        $args = [
            'labels'            => $labels,
            'public'            => true,
            'hierarchical'      => true,
            'show_admin_column' => true,
            'show_in_rest'      => true,
            'rewrite'           => ['slug' => 'talk-tags', 'with_front' => false]
        ];
        register_taxonomy(self::TAX_TAG, self::POST_TYPE, $args);
    }

    public static function addFields() {
        $cmb_info = new_cmb2_box([
            'id' => 'talk_metabox',
            'title' => __('Talk Information', 'rrze-events'),
            'object_types' => [self::POST_TYPE],
            'context' => 'normal',
            'priority' => 'high',
            'show_names' => true,
        ]);
        $cmb_info->add_field(array(
            'name' => esc_html__('Code', 'rrze-events'),
            //'desc' => esc_html__( '', 'rrze-events' ),
            'id'   => 'talk_shortname',
            'type' => 'text_medium',
        ));
        $cmb_info->add_field(array(
            'name' => esc_html__('Date', 'rrze-events'),
            //'desc' => esc_html__( '', 'rrze-events' ),
            'id'   => 'talk_date',
            'type' => 'text_date',
            'date_format' => 'd.m.Y',
            'attributes' => array(
                'data-datepicker' => wp_json_encode( array(
                    'dayNames' => Utils::getDaysOfWeek(),
                    'monthNamesShort' => Utils::getMonthNames('short'),
                    'dateFormat' => 'Y-m-d',
                ) ),
            ),
        ));
        $cmb_info->add_field(array(
            'name' => esc_html__('Start Time', 'rrze-events'),
            //'desc' => esc_html__( '', 'rrze-events' ),
            'id'   => 'talk_start',
            'type' => 'text_time',
            'time_format' => 'H:i',
            'attributes' => array(
                'data-timepicker' => wp_json_encode( array(
                    //'timeOnlyTitle' => __( 'Choose your Time', 'rrze-events' ),
                    'timeFormat' => 'HH:mm',
                    'stepMinute' => 1, // 1 minute increments instead of the default 5
                ) ),
            ),
        ));
        $cmb_info->add_field(array(
            'name' => esc_html__('End Time', 'rrze-events'),
            //'desc' => esc_html__( '', 'rrze-events' ),
            'id'   => 'talk_end',
            'type' => 'text_time',
            'time_format' => 'H:i',
            'attributes' => array(
                'data-timepicker' => wp_json_encode( array(
                    //'timeOnlyTitle' => __( 'Choose your Time', 'rrze-events' ),
                    'timeFormat' => 'HH:mm',
                    'stepMinute' => 1, // 1 minute increments instead of the default 5
                ) ),
            ),
        ));
        $cmb_info->add_field(array(
            'name' => esc_html__('Location', 'rrze-events'),
            //'desc' => esc_html__( '', 'rrze-events' ),
            'id'   => 'talk_room',
            'type' => 'text',
        ));
        $cmb_info->add_field(array(
            'name' => esc_html__('Location URL', 'rrze-events'),
            //'desc' => esc_html__( '', 'rrze-events' ),
            'id'   => 'talk_room_url',
            'type' => 'text_url',
        ));
        $cmb_info->add_field(array(
            'name' => esc_html__('Max. number of participants', 'rrze-events'),
            //'desc' => esc_html__( '', 'rrze-events' ),
            'id'   => 'talk_max_participants',
            'type' => 'text_small',
            'attributes' => [
                'type' => 'number',
                'min' => '1',
            ]
        ));
        $cmb_info->add_field(array(
            'name' => esc_html__('Places available', 'rrze-events'),
            //'desc' => esc_html__( '', 'rrze-events' ),
            'id'   => 'talk_available',
            'type' => 'text_small',
            'attributes' => [
                'type' => 'number',
                'min' => '0',
            ]
        ));
        $cmb_info->add_field(array(
            'name' => esc_html__('Waiting List', 'rrze-events'),
            'desc' => esc_html__( 'Offer waiting list for fully booked talks', 'rrze-events' ),
            'id'   => 'talk_waitinglist',
            'type' => 'checkbox',
        ));
        $cmb_info->add_field(array(
            'name' => esc_html__('Video URL', 'rrze-events'),
            'desc' => esc_html__( 'Enter URL for video (FAU Video portal): &quot;https://...&quot;', 'rrze-events' ),
            'id'   => 'talk_video',
            'type' => 'text_url',
        ));
        $cmb_info->add_field(array(
            'name' => esc_html__('Presentation URL', 'rrze-events'),
            'desc' => esc_html__( 'Enter URL to talk presentation slides: &quot;https://...&quot;', 'rrze-events' ),
            'id'   => 'talk_slides',
            'type' => 'text_url',
        ));
        $cmb_info->add_field( array(
            'name'    => __( 'Speaker(s)', 'rrze-events' ),
            'desc'    => __( 'Drag speakers from the left column to the right column to attach them to this talk.<br />You may rearrange the order of the speakers in the right column by dragging and dropping.', 'rrze-events' ),
            'id'      => 'talk_speakers',
            'type'    => 'custom_attached_posts',
            'column'  => true, // Output in the admin post-listing as a custom column. https://github.com/CMB2/CMB2/wiki/Field-Parameters#column
            'options' => array(
                'show_thumbnails' => true, // Show thumbnails on the left
                'filter_boxes'    => true, // Show a text box for filtering the results
                'query_args'      => array(
                    'posts_per_page' => -1,
                    'post_type'      => 'speaker',
                ), // override the get_posts args
            ),
        ) );
    }

    public static function includeSingleTemplate($singleTemplate)
    {
        global $post;
        if (!$post || $post->post_type != 'talk')
            return $singleTemplate;

        wp_enqueue_style('rrze-events');
        return Utils::getTemplatePath('cpt') . 'single-talk.php';
    }

    public static function includeArchiveTemplate($archiveTemplate)
    {
        global $post;
        if (!$post || $post->post_type != 'talk')
            return $archiveTemplate;

        return Utils::getTemplatePath('cpt') . 'archive-talk.php';
    }

}