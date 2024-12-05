<?php
/**
 * The template for displaying all single posts.
 *
 * @link  https://developer.wordpress.org/themes/basics/template-hierarchy/#single-post
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

while ( have_posts() ) :
	the_post();

    do_action( 'tha_entry_before' );

    ?>

    <article data-id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

        <?php do_action( 'tha_entry_top' ); ?>

        <div class="<?php echo esc_attr( Entry\Component::get_entry_class( 'content' ) ); ?>"><?php

            do_action( 'tha_entry_content_before' );

            include plugin()->getPath('templates/content/') . 'content-single-speaker.php';

            do_action( 'tha_entry_content_after' );

            ?></div>

        <?php do_action( 'tha_entry_bottom' ); ?>

    </article>

    <?php

    do_action( 'tha_entry_after' );

endwhile;
