<?php

use RRZE\Events\Settings;
use RRZE\Events\Utils;


$id = get_the_ID();
$meta = get_post_meta($id);
$talkCategories = get_the_terms($id, 'talk_category');
$talkTags = get_the_terms($id, 'talk_tag');
$speakerSettings = Settings::getOption('rrze-events-speaker-settings');
$labelSettings = Settings::getOption('rrze-events-label-settings');
?>

<div class="rrze-talk" itemscope itemtype="https://schema.org/Event">
    <h1 id="maintop" class="mobiletitle entry-title" itemprop="name">
        <?php the_title(); ?>
    </h1>

    <?php
    $speakers = Utils::getMeta($meta, 'talk_speakers');
    if ($speakers != '') {
        if (count($speakers) > 1) {
            $title_speakers = !empty($labelSettings['label-speaker-plural']) ? $labelSettings['label-speaker-plural'] : __('Speakers', 'rrze-events');
        } else {
            $title_speakers = !empty($labelSettings['label-speaker']) ? $labelSettings['label-speaker'] : __('Speaker', 'rrze-events');
        }
        echo '<div class="talk-speaker">';
        echo '<h2>' . (esc_html($title_speakers)) . '</h2>';
        foreach ($speakers as $speakerID) {
            $organisation = get_post_meta($speakerID, 'speaker_organisation', TRUE);
            $cssClass = 'talk-speaker-thumbnail';
            if (isset($speakerSettings['image-format']) && $speakerSettings['image-format'] == 'rounded') {
                $cssClass .= ' format-rounded';
            }
            echo '<div class="talk-speaker-item"><a href="' . esc_html(get_permalink($speakerID)) . '">';
            if (has_post_thumbnail($speakerID)) {
                echo get_the_post_thumbnail($speakerID, 'medium', ['class' => $cssClass]);
            }
            echo '<span class="speaker-name">' . esc_html(get_the_title($speakerID)) . '</span>';
            echo ($organisation != '' ? '<span class="speaker-organisation">' . esc_html($organisation) . '</span>' : '');
            echo '</a></div>';
        }
        echo '</div>';
    }
    ?>

    <div class="talk-main">
        <div class="talk-details">
            <h2><?php esc_html_e('Infos', 'rrze-events');?></h2>
            <?php
            $talkMeta = Utils::talkFields($id, ['organisation', 'speaker']);
            if ($talkMeta != '') {
                echo wp_kses($talkMeta, Utils::getKsesExtendedRuleset());
            }
            ?>
        </div>

        <?php
        // Thumbnail
        if (has_post_thumbnail() && !post_password_required()) {
            $cssClass = 'talk-thumbnail';
            echo '<div class="post-image">'
                . get_the_post_thumbnail($id, 'medium', ['class' => $cssClass])
                . '</div>';
        }
        ?>

        <div class="talk-description">
            <?php the_content(); ?>
        </div>

        <?php
        if ($talkCategories || $talkTags) {
            echo '<div class="talk-taxonomies">';
            if ($talkCategories) {
                echo '<div class="talk-categories">';
                echo get_the_term_list( $id, 'talk_category', '<ul><li>','</li><li>', '</li></ul>');
                echo '</div>';
            }

            if ($talkTags) {
                $settings = Settings::getOption('rrze-events-settings');
                $accentColor = $settings['accent-color'];
                echo '<div class="talk-tags">';
                echo do_shortcode('[icon icon="solid tag" color="' . $accentColor . '"]')
                    . '<span class="sr-only">' . esc_html__('Tags', 'rrze-events') . ': </span>'
                    . get_the_term_list( $id, 'talk_tag', '<ul><li>','</li><li>', '</li></ul>');
                echo '</div>';
            }
            echo '</div>';
        }
        ?>
    </div>

</div>

<?php
