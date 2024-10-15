<?php

/*
Plugin Name:     RRZE Events
Plugin URI:      https://github.com/RRZE-Webteam/rrze-events
Description:     Manage and display talks and speakers
Version:         1.0.0
Author:          RRZE Webteam
Author URI:      https://blogs.fau.de/webworking/
License:         GNU General Public License v3.0
License URI:     https://www.gnu.org/licenses/gpl-3.0.en.html
Domain Path:     /languages
Text Domain:     rrze-events
*/

namespace RRZE\Events;

defined('ABSPATH') || exit;

use RRZE\Events\CPT\Speaker;
use RRZE\Events\CPT\Talk;
use RRZE\WP\Plugin\Plugin;


const RRZE_PHP_VERSION = '8.2';
const RRZE_WP_VERSION = '6.6';

require_once 'config/defaults.php';

// Autoloader
require_once 'vendor/autoload.php';

register_activation_hook(__FILE__, __NAMESPACE__ . '\activation');
register_deactivation_hook(__FILE__, __NAMESPACE__ . '\deactivation');

add_action('plugins_loaded', __NAMESPACE__ . '\loaded');

/**
 * loadTextdomain
 */
function loadTextdomain()
{
    load_plugin_textdomain(
        'rrze-events',
        false,
        sprintf('%s/languages/', dirname(plugin_basename(__FILE__)))
    );
}

/**
 * System requirements verification.
 * @return string Return an error message.
 */
function systemRequirements(): string
{
    global $wp_version;
    // Strip off any -alpha, -RC, -beta, -src suffixes.
    [$wpVersion] = explode('-', $wp_version);
    $phpVersion = phpversion();
    $theme = wp_get_theme();
    $error = '';
    if (!is_php_version_compatible(RRZE_PHP_VERSION)) {
        $error = sprintf(
        /* translators: 1: Server PHP version number, 2: Required PHP version number. */
            __('The server is running PHP version %1$s. The Plugin requires at least PHP version %2$s.', 'rrze-events'),
            $phpVersion,
            RRZE_PHP_VERSION
        );
    } elseif (!is_wp_version_compatible(RRZE_WP_VERSION)) {
        $error = sprintf(
        /* translators: 1: Server WordPress version number, 2: Required WordPress version number. */
            __('The server is running WordPress version %1$s. The Plugin requires at least WordPress version %2$s.', 'rrze-events'),
            $wpVersion,
            RRZE_WP_VERSION
        );
    } elseif ( 'FAU Events' == $theme->name || 'FAU Events' == $theme->parent_theme ) {
        $error = __('RRZE Events plugin does not work with FAU Events Theme as the event features are already included in the theme. Please activate another theme before activating RRZE Events. The plugin will preserve your existing speakers, talks and shortcodes.', 'rrze-events');
    }
    return $error;
}

/**
 * Activation callback function.
 */
function activation()
{
    loadTextdomain();
    if ($error = systemRequirements()) {
        deactivate_plugins(plugin_basename(__FILE__));
        wp_die(
            esc_html(sprintf(
            /* translators: 1: The plugin name, 2: The error string. */
                __('Plugins: %1$s: %2$s', 'rrze-events'),
                plugin_basename(__FILE__),
                $error
            ))
        );
    }

    add_action(
        'init',
        function () {
            Speaker::registerPostType();
            Talk::registerPostType();
            flush_rewrite_rules(false);
        }
    );

    // Import existing social media profiles from FAU-Events Theme
    $speakers = get_posts([
        'post_type' => 'speaker',
        'posts_per_page' => -1,
    ]);
    $urlFields = array(
        'speaker_blog',
        'speaker_twitter',
        'speaker_xing',
        'speaker_linkedin',
        'speaker_facebook',
        'speaker_other_profile',
    );
    foreach ($speakers as $speaker) {
        $socialMediaURLs = [];
        foreach ($urlFields as $field) {
            $value = get_post_meta($speaker->ID, $field, true);
            if ($value != '') {
                $socialMediaURLs[] = ($field == 'speaker_twitter' ? 'https://twitter.com/' . $value : $value);
            }
        }
        if (!empty($socialMediaURLs)) {
            update_post_meta($speaker->ID, 'speaker_social_media', $socialMediaURLs);
        }
    }

    flush_rewrite_rules(false);
}

/**
 * Deactivation callback function.
 */
function deactivation()
{
    flush_rewrite_rules(false);
}

/**
 * Instantiate Plugin class.
 * @return object Plugin
 */
function plugin()
{
    static $instance;
    if (null === $instance) {
        $instance = new Plugin(__FILE__);
    }
    return $instance;
}

/**
 * Instantiate Settings class.
 * @return object Settings
 */
function settings()
{
    static $instance;
    if (null === $instance) {
        $instance = new Settings();
    }
    return $instance;
}

/**
 * Execute on 'plugins_loaded' API/action.
 * @return void
 */
function loaded()
{
    loadTextdomain();
    plugin()->loaded();
    if ($error = systemRequirements()) {
        add_action('admin_init', function () use ($error) {
            if (current_user_can('activate_plugins')) {
                $pluginData = get_plugin_data(plugin()->getFile());
                $pluginName = $pluginData['Name'];
                $tag = is_plugin_active_for_network(plugin()->getBaseName()) ? 'network_admin_notices' : 'admin_notices';
                add_action($tag, function () use ($pluginName, $error) {
                    printf(
                        '<div class="notice notice-error"><p>' .
                        /* translators: 1: The plugin name, 2: The error string. */
                        esc_html(__('Plugins: %1$s: %2$s', 'rrze-events') .
                        '</p></div>'),
                        esc_html($pluginName),
                        esc_html($error)
                    );
                });
            }
        });
        return;
    }
    new Main;
}
