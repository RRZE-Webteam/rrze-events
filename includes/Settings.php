<?php

namespace RRZE\Events;

defined('ABSPATH') || exit;

use RRZE\WP\Settings\Settings as OptionsSettings;

class Settings
{
    const OPTION_NAME = 'rrze_events';

    protected $settings;

    public function __construct()
    {
        add_action('rrze_wp_settings_after_update_option', [$this, 'flushRewriteRules']);
    }

    public function loaded()
    {
        $this->settings = new OptionsSettings(__('Events Settings', 'rrze-events'), 'rrze-events');


    }

    public function flushRewriteRules($optionName)
    {
        if ($optionName === self::OPTION_NAME) {
            flush_rewrite_rules(false);
        }
    }

    public function validateEndpointSlug($value)
    {
        if (mb_strlen(sanitize_title($value)) < 4) {
            return false;
        }
        return true;
    }

    public function getOption($option)
    {
        return $this->settings->getOption($option);
    }

    public function getOptions()
    {
        return $this->settings->getOptions();
    }

    /**
     * __call method
     * Method overloading.
     */
    public function __call(string $name, array $arguments)
    {
        if (!method_exists($this, $name)) {
            $message = sprintf('Call to undefined method %1$s::%2$s', __CLASS__, $name);
            do_action(
                'rrze.log.error',
                $message,
                [
                    'class' => __CLASS__,
                    'method' => $name,
                    'arguments' => $arguments
                ]
            );
            if (defined('WP_DEBUG') && WP_DEBUG) {
                throw new \Exception($message);
            }
        }
    }
}
