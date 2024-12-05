<?php
/**
 * The template for displaying archive pages.
 *
 * @link  https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package    Francesca
 * @copyright  WebMan Design, Oliver Juhas
 *
 * @since  1.0.0
 */

namespace WebManDesign\Francesca;

use function RRZE\Events\plugin;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( have_posts() ) :

	get_template_part( 'templates/parts/component/page-header', 'archive' );
    do_action( 'francesca/postslist/before' );

    ?>

    <div id="posts" class="posts posts-list">

        <?php

        do_action( 'tha_content_while_before' );

        include plugin()->getPath('templates/content/') . 'content-archive-speaker.php';

        do_action( 'tha_content_while_after' );

        ?>

    </div>

    <?php

    /**
     * Fires after posts list container closing tag.
     *
     * @since  1.0.0
     */
    do_action( 'francesca/postslist/after' );

else :

	get_template_part( 'templates/parts/content/content', 'none' );

endif;
