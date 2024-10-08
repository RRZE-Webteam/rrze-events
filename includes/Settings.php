<?php

namespace RRZE\Events;

defined('ABSPATH') || exit;

class Settings
{
    const OPTION_NAME = 'rrze_events';

    protected $settings;

    public function __construct()
    {
        add_action('cmb2_admin_init', [$this, 'registerSettings']);
        if (!has_action('cmb2_render_toggle')) {
            add_action('cmb2_render_toggle', [$this, 'renderToggle' ], 10, 5);
            add_action('admin_head', [$this, 'addStyle' ]);
        }
    }

    public static function getOption($option)
    {
        $settings = get_option($option);
        if (!$settings) {
            $settings = Config\getDefaults($option);
        }
        return $settings;
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
                throw new \Exception(esc_html($message));
            }
        }
    }

    public function registerSettings() {
        $defaults = Config\getDefaults();
        $speaker_options = new_cmb2_box([
            'id' => 'rrze-events-speaker-settings',
            'title' => esc_html__('Events Settings', 'rrze-events'),
            'object_types' => ['options-page'],
            'option_key' => 'rrze-events-speaker-settings', // The option key and admin menu page slug.
            'menu_title'      => esc_html__( 'RRZE Events', 'rrze-events' ), // Falls back to 'title' (above).
            'parent_slug'     => 'options-general.php',
            'tab_group'    => 'rrze-events-speaker-settings',
            'tab_title'    => esc_html__('Speaker Page Options', 'rrze-events'),
            'display_cb' => [$this, 'options_display_with_tabs']
        ]);
        $speaker_options->add_field([
            'name' => esc_html__('Speaker image format', 'rrze-events'),
            //'desc' => esc_html__('', 'rrze-events'),
            'type' => 'radio_inline',
            'id'   => 'image-format',
            'options' => [
                'rounded' => __('rounded', 'rrze-events'),
                'rectangle' => __('rectangle', 'rrze-events'),
            ],
            'default' => $defaults['rrze-events-speaker-settings']['image-format'],
        ]);
        $speaker_options->add_field([
            'name' => esc_html__('Show link icons in speaker single view', 'rrze-events'),
            //'desc' => esc_html__('', 'rrze-events'),
            'type' => 'toggle',
            'id'   => 'show-link-icons',
            'default' => $defaults['rrze-events-speaker-settings']['show-link-icons'],
        ]);
        $speaker_options->add_field([
            'name' => esc_html__(' Show categories in speaker single view', 'rrze-events'),
            //'desc' => esc_html__('', 'rrze-events'),
            'type' => 'toggle',
            'id'   => 'show-categories',
            'default' => $defaults['rrze-events-speaker-settings']['show-categories'],
        ]);
        $speaker_options->add_field([
            'name' => esc_html__(' Show talk list in speaker single view', 'rrze-events'),
            //'desc' => esc_html__('', 'rrze-events'),
            'type' => 'toggle',
            'id'   => 'show-talk-list',
            'default' => $defaults['rrze-events-speaker-settings']['show-talk-list'],
        ]);
        $speaker_options->add_field([
            'name' => esc_html__('Talk Order', 'rrze-events'),
            'desc' => esc_html__('Order of the talks listed on a speakers single page.', 'rrze-events'),
            'type' => 'radio_inline',
            'id'   => 'talk-order',
            'options' => [
                'by-title' => __('By title', 'rrze-events'),
                'by-date' => __('By date', 'rrze-events'),
            ],
            'default' => $defaults['rrze-events-speaker-settings']['talk-order'],
        ]);

        // Labels
        $label_options = new_cmb2_box([
            'id'           => 'rrze-events-label-settings',
            'title'        => esc_html__('Labels', 'rrze-events'),
            'object_types' => ['options-page'],
            'option_key'   => 'rrze-events-label-settings',
            'parent_slug'  => 'rrze-events-speaker-settings',
            'tab_group'    => 'rrze-events-speaker-settings',
            'tab_title'    => esc_html__('Labels', 'rrze-events'),
            'display_cb' => [$this, 'options_display_with_tabs']
        ]);
        $label_options->add_field([
            'name' => esc_html__('"Talk": Singular', 'rrze-events'),
            'desc' => esc_html__('(e.g. Workshop, Course, Conference etc.)', 'rrze-events'),
            'type' => 'text',
            'id'   => 'label-talk',
            'default' => __('Talk', 'rrze-events'),
        ]);
        $label_options->add_field([
            'name' => esc_html__('"Talk": Plural', 'rrze-events'),
            //'desc' => esc_html__('(e.g. Workshops, Courses, Conferences etc.)', 'rrze-events'),
            'type' => 'text',
            'id'   => 'label-talk-plural',
            'default' => __('Talks', 'rrze-events'),
        ]);
        $label_options->add_field([
            'name' => esc_html__('"Speaker": Singular', 'rrze-events'),
            'desc' => esc_html__('(e.g. Lecturer, Speaker, Contributor etc.)', 'rrze-events'),
            'type' => 'text',
            'id'   => 'label-speaker',
            'default' => __('Speaker', 'rrze-events'),
        ]);
        $label_options->add_field([
            'name' => esc_html__('"Speaker": Plural', 'rrze-events'),
            //'desc' => esc_html__('(e.g. Workshops, Courses, Conferences etc.)', 'rrze-events'),
            'type' => 'text',
            'id'   => 'label-speaker-plural',
            'default' => __('Speakers', 'rrze-events'),
        ]);
        $label_options->add_field([
            'name' => esc_html__('Code', 'rrze-events'),
            'desc' => esc_html__('(e.g. Course Nr, ID etc.)', 'rrze-events'),
            'type' => 'text',
            'id'   => 'label-short',
            'default' => __('Course Nr', 'rrze-events'),
        ]);

        // Call For Papers
        $cfp_options = new_cmb2_box([
            'id'           => 'rrze-events-cfp-settings',
            'title'        => esc_html__('Call For Papers', 'rrze-events'),
            'object_types' => ['options-page'],
            'option_key'   => 'rrze-events-cfp-settings',
            'parent_slug'  => 'rrze-events-speaker-settings',
            'tab_group'    => 'rrze-events-speaker-settings',
            'tab_title'    => esc_html__('Call For Papers', 'rrze-events'),
            'display_cb' => [$this, 'options_display_with_tabs']
        ]);
        if (is_plugin_active( 'contact-form-7/wp-contact-form-7.php' ) ) {
            $cfp_options->add_field([
                'id'    => 'cfp-form-id',
                'name'  => esc_html__('Form ID', 'rrze-events'),
                'desc'  => esc_html__('ID of the Contact Form 7 form containing the Call for Papers', 'rrze-events'),
                'type'  => 'select',
                'options_cb' => [$this, 'getCf7Forms'],
                'show_option_none' => '-- ' . __('None', 'rrze-events') . ' --',
                'default' => $defaults['rrze-events-cfp-settings']['cfp-form-id'],
            ]); //getCf7Fields
            $cfpFields = [
                'cfp-talk-title' => esc_html__('Talk Title', 'rrze-events'),
                'cfp-talk-excerpt' => esc_html__('Talk Excerpt', 'rrze-events'),
                'cfp-talk-description' => esc_html__('Talk Description', 'rrze-events'),
                'cfp-speaker-title' => esc_html__('Speaker Academic Title', 'rrze-events'),
                'cfp-speaker-firstname' => esc_html__('Speaker First Name', 'rrze-events'),
                'cfp-speaker-lastname' => esc_html__('Speaker Last Name', 'rrze-events'),
                'cfp-speaker-cv' => esc_html__('Speaker Short Biography', 'rrze-events'),
                'cfp-speaker-organisation' => esc_html__('Speaker Organisation', 'rrze-events'),
                'cfp-speaker-email' => esc_html__('Speaker Email Address', 'rrze-events'),
                'cfp-speaker-website' => esc_html__('Speaker Website', 'rrze-events'),
            ];
            foreach ($cfpFields as $key => $label) {
                $cfp_options->add_field([
                    'id'    => $key,
                    'name'  => $label,
                    'type'  => 'select',
                    'options_cb' => [$this, 'getCf7Fields'],
                    'show_option_none' => '-- ' . __('None', 'rrze-events') . ' --',
                    'default'   => $defaults['rrze-events-cfp-settings'][$key],
                ]);
            }
        } else {
            $cfp_options->add_field([
                'name' => __('Please activate Contact Form 7 Plugin to use this option','rrze-events'),
                'desc' => '<a href="' . get_site_url(null, '/wp-admin/plugins.php?s=contact%20form%207') . '">&rarr; ' . __('Go to Plugin page','rrze-events') . '</a>',
                'type' => 'title',
                'id'   => 'cfp-error',
            ]);
        }

    }

    /*
     * CMB2 Toggle
     * Source: https://kittygiraudel.com/2021/04/05/an-accessible-toggle/
     */
    public function renderToggle( $field, $escaped_value, $object_id, $object_type, $field_type_object ) {
        $field_name = $field->_name();

        $return_value = 'on';

        if ( $field->args( 'return_value' ) && ! empty( $field->args( 'return_value' ) ) ) {
            $return_value = $field->args( 'return_value' );
        }

        $args = array(
            'type'  => 'checkbox',
            'id'    => $field_name,
            'name'  => $field_name,
            'desc'  => '',
            'value' => $return_value,
        );

        echo '<label class="cmb2-toggle" for="' . esc_attr( $args['id'] ) . '">
  <input type="checkbox" name="' . esc_attr( $args['name'] ) . '" id="' . esc_attr( $args['id'] ) . '" value="' . esc_attr( $return_value ) . '" class="Toggle__input" ' . checked( $escaped_value, $return_value, false ) . ' />

  <span class="Toggle__display" hidden>
    <svg
      aria-hidden="true"
      focusable="false"
      class="Toggle__icon Toggle__icon--checkmark"
      width="18"
      height="14"
      viewBox="0 0 18 14"
      fill="none"
      xmlns="http://www.w3.org/2000/svg"
    >
      <path
        d="M6.08471 10.6237L2.29164 6.83059L1 8.11313L6.08471 13.1978L17 2.28255L15.7175 1L6.08471 10.6237Z"
        fill="currentcolor"
        stroke="currentcolor"
      />
    </svg>
    <svg
      aria-hidden="true"
      focusable="false"
      class="Toggle__icon Toggle__icon--cross"
      width="13"
      height="13"
      viewBox="0 0 13 13"
      fill="none"
      xmlns="http://www.w3.org/2000/svg"
    >
      <path
        d="M11.167 0L6.5 4.667L1.833 0L0 1.833L4.667 6.5L0 11.167L1.833 13L6.5 8.333L11.167 13L13 11.167L8.333 6.5L13 1.833L11.167 0Z"
        fill="currentcolor"
      />
    </svg>
  </span>

  <span class="screen-reader-text"> ' . esc_html($field->args['name']) . '</span>
</label>';

        $field_type_object->_desc( true, true );
    }

    public function addStyle() {
        ?>
        <style>
            .cmb2-toggle {
                display: inline-flex; /* 1 */
                align-items: center; /* 1 */
                flex-wrap: wrap; /* 2 */
                position: relative; /* 3 */
                gap: 1ch; /* 4 */
            }
            .Toggle__display {
                --offset: 0.25em;
                --diameter: 1.8em;

                display: inline-flex; /* 1 */
                align-items: center; /* 1 */
                justify-content: space-around; /* 1 */

                width: calc(var(--diameter) * 2 + var(--offset) * 2); /* 2 */
                height: calc(var(--diameter) + var(--offset) * 2); /* 2 */
                box-sizing: content-box; /* 2 */

                border: 0.1em solid rgb(0 0 0 / 0.2); /* 3 */

                position: relative; /* 4 */
                border-radius: 100vw; /* 5 */
                background-color: #fbe4e2; /* 6 */

                transition: 250ms;
                cursor: pointer;
            }
            .Toggle__display::before {
                content: '';

                width: var(--diameter); /* 1 */
                height: var(--diameter); /* 1 */
                border-radius: 50%; /* 1 */

                box-sizing: border-box; /* 2 */
                border: 0.1px solid rgb(0 0 0 / 0.2); /* 2 */

                position: absolute; /* 3 */
                z-index: 2; /* 3 */
                top: 50%; /* 3 */
                left: var(--offset); /* 3 */
                transform: translate(0, -50%); /* 3 */

                background-color: #fff; /* 4 */
                transition: inherit;
            }
            @media (prefers-reduced-motion: reduce) {
                .Toggle__display {
                    transition-duration: 0ms;
                }
            }
            .Toggle__input {
                position: absolute;
                opacity: 0;
                width: 100%;
                height: 100%;
            }
            .Toggle__input:focus + .Toggle__display {
                outline: 1px dotted #212121; /* 1 */
                outline: 1px auto -webkit-focus-ring-color; /* 1 */
            }
            .Toggle__input:focus:not(:focus-visible) + .Toggle__display {
                outline: 0; /* 1 */
            }
            .Toggle__input:checked + .Toggle__display {
                background-color: #e3f5eb; /* 1 */
            }
            .Toggle__input:checked + .Toggle__display::before {
                transform: translate(100%, -50%); /* 1 */
            }
            .Toggle__input:disabled + .Toggle__display {
                opacity: 0.6; /* 1 */
                filter: grayscale(40%); /* 1 */
                cursor: not-allowed; /* 1 */
            }
            [dir='rtl'] .Toggle__display::before {
                left: auto; /* 1 */
                right: var(--offset); /* 1 */
            }
            [dir='rtl'] .Toggle__input:checked + .Toggle__display::before {
                transform: translate(-100%, -50%); /* 1 */
            }
            .Toggle__icon {
                display: inline-block;
                width: 1em;
                height: 1em;
                color: inherit;
                fill: currentcolor;
                vertical-align: middle;
            }
            .Toggle__icon--cross {
                color: #e74c3c;
                font-size: 85%; /* 1 */
            }

            .Toggle__icon--checkmark {
                color: #1fb978;
            }
        </style>
        <?php
    }

    /**
     * A CMB2 options-page display callback override which adds tab navigation among
     * CMB2 options pages which share this same display callback.
     *
     * @param CMB2_Options_Hookup $cmb_options The CMB2_Options_Hookup object.
     */
    public function options_display_with_tabs( $cmb_options ): void {
        $tabs = self::options_page_tabs( $cmb_options );
        ?>
        <div class="wrap cmb2-options-page option-<?php echo esc_html($cmb_options->option_key); ?>">
            <?php if ( get_admin_page_title() ) : ?>
                <h2><?php echo wp_kses_post( get_admin_page_title() ); ?></h2>
            <?php endif; ?>
            <h2 class="nav-tab-wrapper">
                <?php foreach ( $tabs as $option_key => $tab_title ) : ?>
                    <a class="nav-tab<?php if ( isset( $_GET['page'] ) && $option_key === $_GET['page'] ) : ?> nav-tab-active<?php endif; ?>" href="<?php menu_page_url( $option_key ); ?>"><?php echo wp_kses_post( $tab_title ); ?></a>
                <?php endforeach; ?>
            </h2>
            <form class="cmb-form" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="POST" id="<?php echo esc_html($cmb_options->cmb->cmb_id); ?>" enctype="multipart/form-data" encoding="multipart/form-data">
                <input type="hidden" name="action" value="<?php echo esc_attr( $cmb_options->option_key ); ?>">
                <?php $cmb_options->options_page_metabox(); ?>
                <?php submit_button( esc_attr( $cmb_options->cmb->prop( 'save_button' ) ), 'primary', 'submit-cmb' ); ?>
            </form>
        </div>
        <?php
    }

    /**
     * Gets navigation tabs array for CMB2 options pages which share the given
     * display_cb param.
     *
     * @param CMB2_Options_Hookup $cmb_options The CMB2_Options_Hookup object.
     *
     * @return array Array of tab information.
     */
    public function options_page_tabs( $cmb_options ): array {
        $tab_group = $cmb_options->cmb->prop( 'tab_group' );
        $tabs      = array();

        foreach ( \CMB2_Boxes::get_all() as $cmb_id => $cmb ) {
            if ( $tab_group === $cmb->prop( 'tab_group' ) ) {
                $tabs[ $cmb->options_page_keys()[0] ] = $cmb->prop( 'tab_title' )
                    ? $cmb->prop( 'tab_title' )
                    : $cmb->prop( 'title' );
            }
        }

        return $tabs;
    }

    public function getCf7Forms(): array {
        $cf7_forms = array();
        $cf7_args = array('post_type' => 'wpcf7_contact_form', 'posts_per_page' => -1, 'orderby' => 'title', 'order' => 'ASC');
        $cf7_forms_obj = get_posts( $cf7_args );
        //$cf7_forms[''] = __('&mdash; None &mdash;', 'rrze-events');
        foreach ($cf7_forms_obj as $form) {
            $cf7_forms[$form->ID] = $form->post_title . ' (ID ' . $form->ID . ')';
        }
        return $cf7_forms;
    }

    // Get CF7 Form Fields (Choices)
    public function getCf7Fields(): array {
        $cfpOptions = get_option('rrze-events-cfp-settings');
        $cfpID = isset($cfpOptions['cfp-form-id']) && $cfpOptions['cfp-form-id'] != '' ? $cfpOptions['cfp-form-id'] : false;
        $cf7_fields = array();

        if ($cfpID) {
            $cf7_form = get_post($cfpID);
            $manager = \WPCF7_FormTagsManager::get_instance();
            $tags = $manager->scan($cf7_form->post_content);
            $cond = array();
            $filter_result = $manager->filter($tags, $cond);
            foreach ($filter_result as $key => $value) {
                if ($value->type != 'submit') {
                    $cf7_fields[$value->name] = $value->name;
                }
            }
        }

        return $cf7_fields;
    }
}
