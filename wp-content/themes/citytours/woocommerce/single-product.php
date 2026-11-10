<?php
/**
 * The Template for displaying all single products
 *
 * @see         https://docs.woocommerce.com/document/template-structure/
 * @author      WooThemes
 * @package     WooCommerce/Templates
 * @version     1.6.4
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

get_header( 'shop' ); ?>

    <?php 
    $product_id = get_the_ID();
    $enable_header_img = get_post_meta( $product_id, '_show_header_image', true );

    if ( $enable_header_img == 'show' || empty($enable_header_img) ) { 
        $header_img_scr = ct_get_header_image_src( $product_id );

        if ( ! empty( $header_img_scr ) ) { 
            $header_content = get_header_content( $product_id );
            $header_img_height = ct_get_header_image_height( $product_id );
            ?>

            <section class="parallax-window" data-parallax="scroll" data-image-src="<?php echo esc_url( $header_img_scr ) ?>" data-natural-width="1400" data-natural-height="<?php echo esc_attr( $header_img_height ); ?>">
                <div class="parallax-content-1">
                    <?php echo balancetags( $header_content ); ?>
                </div>
            </section><!-- End section -->

        <?php } 
    }
    ?>

    <div id="position">
        <div class="container"><?php ct_breadcrumbs(); ?></div>
    </div><!-- End Position -->

    <?php
        /**
         * woocommerce_before_main_content hook.
         *
         * @hooked woocommerce_output_content_wrapper - 10 (outputs opening divs for the content)
         * @hooked woocommerce_breadcrumb - 20 : removed
         */
        do_action( 'woocommerce_before_main_content' );
    ?>

        <?php while ( have_posts() ) : the_post(); ?>

            <?php wc_get_template_part( 'content', 'single-product' ); ?>

        <?php endwhile; // end of the loop. ?>

    <?php
        /**
         * woocommerce_sidebar hook.
         *
         * @hooked woocommerce_get_sidebar - 10
         */
        do_action( 'woocommerce_sidebar' );
    ?>

    <?php
        /**
         * woocommerce_after_main_content hook.
         *
         * @hooked woocommerce_output_content_wrapper_end - 10 (outputs closing divs for the content)
         */
        do_action( 'woocommerce_after_main_content' );
    ?>

<?php get_footer( 'shop' ); ?>
