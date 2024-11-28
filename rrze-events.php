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
add_action('init', __NAMESPACE__ . '\init');

/* TODO:
    * Call for Papers
    * Select-/Radio-/Checkbox-Auswahl "talk-list" für CF7
*/

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

    // Import existing theme mods and social media profiles from FAU-Events Theme
    importThemeMods();
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

    // Flush rewrite rules
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

    add_action('init', __NAMESPACE__ . '\createBlocks');
    add_filter('block_categories_all', __NAMESPACE__ . '\rrze_block_category', 10, 2);

}

function init() {
    loadTextdomain();
}

function createBlocks(): void {
    register_block_type( __DIR__ . '/build/blocks/speaker' );
    register_block_type( __DIR__ . '/build/blocks/talk' );
}

function rrze_block_category($categories, $post) {
    $custom_category = [
        'slug'  => 'rrze',
        'title' => __('RRZE Plugins', 'rrze-plugins'),
        'icon'  => 'layout',
    ];

    array_unshift($categories, $custom_category);

    return $categories;
}
add_filter('block_categories_all', __NAMESPACE__ . '\rrze_block_category', 10, 2);

function importThemeMods() {
    $themeMods = [
        'speaker-image-format' => 'speaker|image-format',
        'speaker_link_icons' => 'speaker|show-link-icons',
        'speaker_talk_list' => 'speaker|show-talk-list',
        'show_speaker_categories' => 'speaker|show-categories',
        'talk_order' => 'speaker|talk-order',
        'label-talk' => 'label|label-talk',
        'label-talk-pl' => 'label|label-talk-plural',
        'label-speaker' => 'label|label-speaker',
        'label-speaker-pl' => 'label|label-speaker-plural',
        'label-short' => 'label|label-short',
        'cfp-form-id' => 'cfp|form-id',
        'cfp-talk_title' => 'cfp|talk-title',
        'cfp-talk_excerpt' => 'cfp|talk-excerpt',
        'cfp-talk_description' => 'cfp|talk-description',
        'cfp-speaker-title' => 'cfp|speaker-title',
        'cfp-speaker-firstname' => 'cfp|speaker-firstname',
        'cfp-speaker-lastname' => 'cfp|speaker-lastname',
        'cfp-speaker-cv' => 'cfp|speaker-cv',
        'cfp-speaker-organisation' => 'cfp|speaker-organisation',
        'cfp-speaker-email' => 'cfp|speaker-email',
        'cfp-speaker-website' => 'cfp|speaker-website',
    ];
    foreach ($themeMods as $key => $settings) {
        $mod = get_theme_mod($key);
        if ($mod === false) {
            continue;
        }
        if ($key == 'talk_order') {
            $mod = 'by-' . $mod; // renamed because of CMB2 problems with value 'date'
        }
        $settingsParts = explode('|', $settings);
        $settingsCat = $settingsParts[0];
        $settingsKey = $settingsParts[1];
        $settingsNew = Settings::getOption('rrze-events-' . $settingsCat . '-settings');
        $settingsNew[$settingsKey] = $mod;
        update_option('rrze-events-' . $settingsCat . '-settings', $settingsNew);
    }
}
