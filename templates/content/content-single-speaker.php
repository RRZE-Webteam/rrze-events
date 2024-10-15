<?php

use RRZE\Events\Settings;
use RRZE\Events\Utils;

$id = get_the_ID();
$meta = get_post_meta($id);
$speakerSettings = Settings::getOption('rrze-events-speaker-settings');
?>

<article class="rrze-speaker" itemscope itemtype="https://schema.org/Person">
    <h1 id="maintop" class="mobiletitle entry-title" itemprop="name">
        <?php the_title();
        $organisation = get_post_meta($id, 'speaker_organisation', true);
        if ($organisation != '') {
            echo '<br /><span class="speaker-organisation">' . esc_html($organisation) . '</span>';
        }
        ?>
    </h1>

    <div class="speaker-details">

        <?php
        // Thumbnail
        if (has_post_thumbnail() && !post_password_required()) {
            echo '<div class="post-image">'
                . get_the_post_thumbnail($post->ID, 'large', ['class' => 'speaker-thumbnail'])
                . '</div>';
        } ?>

        <?php echo '<div class="speaker-name">' . esc_html(get_the_title()) . '</div>'; ?>

        <?php
        $organisation = get_post_meta($id, 'speaker_organisation', true);
        if ($organisation != '') {
            echo '<div class="speaker-organisation">' . esc_html($organisation) . '</div>';
        }
        ?>

        <?php if ($speakerSettings['show-link-icons'] == 'on') {
            $links = Utils::speakerLinks($id, 'icons');
            if ($links != '') {
                echo '<div class="speaker-links">' . wp_kses($links, Utils::getKsesExtendedRuleset()) . '</div>';
            }
        } ?>

        <?php if ($speakerSettings['show-categories'] == 'on' && get_the_terms($post->ID, 'speaker_category') !== false) : ?>
            <div class="speaker-categories">
                <?php print get_the_term_list( $post->ID, 'speaker_category', '<ul><li>','</li><li>', '</li></ul>'); ?>
            </div><!-- end .entry-cats -->
        <?php endif; ?>

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

</article>

<?php