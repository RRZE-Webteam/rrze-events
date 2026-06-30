<?php

use RRZE\Events\Settings;
use RRZE\Events\Utils;

$id = get_the_ID();
$meta = get_post_meta($id);
$speakerSettings = Settings::getOption('rrze-events-speaker-settings');
$speakerCategories = get_the_terms($id, 'speaker_category');
$speakerTags = get_the_terms($id, 'speaker_tag');
?>

<div class="rrze-speaker" itemscope itemtype="https://schema.org/Person">
    <h1 id="maintop" class="mobiletitle entry-title" itemprop="name">
        <?php the_title();
        $organisation = get_post_meta($id, 'speaker_organisation', true);
        if ($organisation != '') {
            echo '<br /><span class="speaker-organisation">' . esc_html($organisation) . '</span>';
        }
        ?>
    </h1>

    <div class="speaker-info">

        <?php
        // Thumbnail
        if (has_post_thumbnail() && !post_password_required()) {
            $cssClass = 'speaker-thumbnail';
            if (isset($speakerSettings['image-format']) && $speakerSettings['image-format'] == 'rounded') {
                $cssClass .= ' format-rounded';
            }
            echo '<div class="post-image">'
                . get_the_post_thumbnail($id, 'large', ['class' => $cssClass])
                . '</div>';
        } ?>

        <div class="speaker-details">

            <?php echo '<div class="speaker-name">' . esc_html(get_the_title()) . '</div>'; ?>

            <?php
            $organisation = get_post_meta($id, 'speaker_organisation', true);
            if ($organisation != '') {
                echo '<div class="speaker-organisation">' . esc_html($organisation) . '</div>';
            }
            ?>

            <?php if (isset($speakerSettings['show-link-icons']) && $speakerSettings['show-link-icons'] == 'on') {
                $links = Utils::speakerLinks($id, 'icons');
                if ($links != '') {
                    echo '<div class="speaker-links">' . wp_kses($links, Utils::getKsesExtendedRuleset()) . '</div>';
                }
            } ?>

            <?php if (isset($speakerSettings['show-categories']) && $speakerSettings['show-categories'] == 'on') :
                if ($speakerCategories) { ?>
                    <div class="speaker-categories">
                        <?php print get_the_term_list( $id, 'speaker_category', '<ul><li>','</li><li>', '</li></ul>'); ?>
                    </div><!-- end .entry-cats -->
                <?php } ?>
                <?php if ($speakerTags) {
                    $settings = Settings::getOption('rrze-events-settings');
                    $accentColor = $settings['accent-color']; ?>
                    <div class="speaker-tags">
                    <?php print do_shortcode('[icon icon="solid tag" color="' . $accentColor . '"]')
                        . '<span class="sr-only">' . esc_html__('Tags', 'rrze-events') . ': </span>'
                        . get_the_term_list( $id, 'speaker_tag', '<ul><li>','</li><li>', '</li></ul>'); ?>
                    </div><!-- end .entry-tags -->
                <?php } ?>
            <?php endif; ?>

        </div>

    </div>

    <div class="speaker-main">
        <div class="speaker-description">
            <?php the_content(); ?>
        </div>
        <?php
        $orderby = $speakerSettings['talk-order'] == 'by-date' ? 'date' : 'title';
        $talks = Utils::talksBySpeaker($id, $orderby);
        if ($talks != '') {
            echo '<div class="speaker-talks">' . wp_kses_post($talks) . '</div>';
        }
        ?>
    </div>

</div>

<?php