<?php

namespace RRZE\Events;

use RRZE\Events\CPT\Speaker;
use RRZE\Events\CPT\Talk;
use RRZE\Events\Shortcodes\Speaker as SC_Speaker;
use RRZE\Events\Shortcodes\Talk as SC_Talk;

defined('ABSPATH') || exit;



class Main
{
    /**
     * __construct
     */
    public function __construct()
    {
        add_filter('plugin_action_links_' . plugin()->getBaseName(), [$this, 'settingsLink']);

        add_action('admin_enqueue_scripts', [$this, 'adminEnqueueScripts']);
        add_action('wp_enqueue_scripts', [$this, 'wpEnqueueScripts']);

        settings()->loaded();

        Speaker::init();
        Talk::init();

        new SC_Speaker;
        new SC_Talk;
    }

    /**
     * Add the settings link to the list of plugins.
     *
     * @param array $links
     * @return array
     */
    public function settingsLink($links)
    {
        $settingsLink = sprintf(
            '<a href="%s">%s</a>',
            admin_url('options-general.php?page=rrze-events'),
            __('Settings', 'rrze-events')
        );
        array_unshift($links, $settingsLink);
        return $links;
    }

    public function adminEnqueueScripts() {

    }

    public function wpEnqueueScripts()
    {
        wp_register_style(
            'rrze-events',
            plugins_url('assets/css/rrze-events.css', plugin()->getBasename()),
            [],
            plugin()->getVersion(true)
        );
    }
}
