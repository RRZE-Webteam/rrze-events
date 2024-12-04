<?php

namespace RRZE\Events;

use WPCF7_Submission;

defined('ABSPATH') || exit;

class CF7 {

    public function __construct() {
        if (is_plugin_active('contact-form-7/wp-contact-form-7.php')) {
            add_filter('wpcf7_form_tag', [$this, 'addPluginListToContactForm'], 10, 2);
            add_action( 'wpcf7_before_send_mail', [$this, 'actionWpcf7BeforeSendMail'], 10, 3 );
        }
    }

    public function addPluginListToContactForm($tag, $unused) {
        if ($tag['name'] != 'talk-list')
            return $tag;

        $args = array('post_type' => 'talk',
            'orderby' => 'title',
            'order' => 'ASC',
            'numberposts' => -1);
        $talks = get_posts($args);

        if (!$talks)
            return $tag;

        foreach ($talks as $talk) {
            $available = get_post_meta($talk->ID, 'talk_available', true);
            $short = get_post_meta($talk->ID, 'talk_shortname', true);
            $value = $talk->ID;
            if ($short == '') {
                $short = ' (' . sanitize_title($short) . ')';
            }
            $date = get_post_meta($talk->ID, 'talk_date', true);
            $dateOut = date_i18n(_x('Y-m-d', 'Date format', 'rrze-events'), strtotime($date));
            $start = get_post_meta($talk->ID, 'talk_start', true);
            if ($start != '')
                $dateOut .= ', ' . $start;
            if ($dateOut != '')
                $dateOut .= ': ';
            $waitinglist = get_post_meta($talk->ID, 'talk_waitinglist', true);
            if (((isset($available)) && ($available > 0)) || $available == '') {
                $tag['raw_values'][] = $value . "_" . $talk->post_name;
                $tag['values'][] = $value . "_" . $talk->post_name;
                $tag['labels'][] = $dateOut . $talk->post_title . " (" . $short . ")";
            } elseif ((isset($available)) && ($available == 0) && $waitinglist == "on") {
                $tag['raw_values'][] = $value . "_" . $talk->post_name . "_waitinglist";
                $tag['values'][] = $value . "_" . $talk->post_name . "_waitinglist";
                $tag['labels'][] = $dateOut . $talk->post_title . " (" . $short . ") &ndash; " . _x('WAITING LIST', 'Addition to select list option on registration form', 'rrze-events');
            }
        }

        return $tag;
    }

    public function actionWpcf7BeforeSendMail($contact_form, $abort, $submission) {
        $cf7Integration = Settings::getOption('rrze-events-cfp-settings');

        if (!isset($cf7Integration['cfp-form-id']) || $cf7Integration['cfp-form-id'] == '')
            return $contact_form;

        if ($contact_form->id() != $cf7Integration['cfp-form-id'])
            return $contact_form;

        if (!$submission)
            return $contact_form;

        // Add Speaker
        $args_speaker = array(
            'post_type' => 'speaker',
            'post_status' => 'draft'
        );
        $speaker = [];
        if ($cf7Integration['cfp-speaker-title'] != '')
            $speaker[] = sanitize_text_field($submission->get_posted_data($cf7Integration['cfp-speaker-title']));
        if ($cf7Integration['cfp-speaker-firstname'] != '')
            $speaker[] = sanitize_text_field($submission->get_posted_data($cf7Integration['cfp-speaker-firstname']));
        if ($cf7Integration['cfp-speaker-lastname'] != '')
            $speaker[] = sanitize_text_field($submission->get_posted_data($cf7Integration['cfp-speaker-lastname']));
        if (!empty($speaker))
            $args_speaker['post_title'] = implode(' ', $speaker);
        if ('' != $cf7Integration['cfp-speaker-cv'])
            $args_speaker['post_content'] = sanitize_textarea_field($submission->get_posted_data($cf7Integration['cfp-speaker-cv']));
        if ('' != $cf7Integration['cfp-speaker-organisation'])
            $args_speaker['meta_input']['speaker_organisation'] = sanitize_text_field($submission->get_posted_data($cf7Integration['cfp-speaker-organisation']));
        if ('' != $cf7Integration['cfp-speaker-email'])
            $args_speaker['meta_input']['speaker_email'] = sanitize_email($submission->get_posted_data($cf7Integration['cfp-speaker-email']));
        if ('' != $cf7Integration['cfp-speaker-website'])
            $args_speaker['meta_input']['speaker_website'] = sanitize_url($submission->get_posted_data($cf7Integration['cfp-speaker-website']));

        $speakerID = wp_insert_post($args_speaker, true);

        // Add Talk
        $args_talk = array(
            'post_type' => 'talk',
            'post_status' => 'draft',
        );

        if ('' != $cf7Integration['cfp-talk-title'])
            $args_talk['post_title'] = sanitize_text_field($submission->get_posted_data($cf7Integration['cfp-talk-title']));
        if ('' != $cf7Integration['cfp-talk-description'])
            $args_talk['post_content'] = sanitize_text_field($submission->get_posted_data($cf7Integration['cfp-talk-description']));
        if ('' != $cf7Integration['cfp-talk-excerpt'])
            $args_talk['post_excerpt'] = sanitize_text_field($submission->get_posted_data($cf7Integration['cfp-talk-excerpt']));
        if (!is_wp_error($speakerID))
            $args_talk['meta_input']['talk-speakers'] = $speakerID;
        $talkID = wp_insert_post($args_talk);

        return $contact_form;
    }

}