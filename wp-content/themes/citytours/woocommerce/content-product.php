<?php
/**
 * The template for displaying product content within loops
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @author  WooThemes
 * @package WooCommerce/Templates
 * @version 3.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

global $product, $ct_options;

// Ensure visibility
if ( empty( $product ) || ! $product->is_visible() ) {
    return;
}

$post_class = join( ' ', get_post_class() );
$post_class .= ' shop-item';

if ( is_archive() ) { 
    switch ( $ct_options['shop_product_columns'] ) {
        case '2':
            $post_class .= ' col-sm-6';
            break;
        case '3':
            $post_class .= ' col-lg-4 col-md-4 col-sm-6';
            break;
        case '4':
            $post_class .= ' col-lg-3 col-sm-6';
            break;
        case '5':
            $post_class .= ' col-lg-24 col-sm-6';
            break;
        case '6':
            $post_class .= ' col-lg-2 col-md-4 col-sm-6';
            break;
        default:
            $post_class .= ' col-lg-4 col-md-4 col-sm-6';
            break;
    }
}

?>
<li class="<?php echo $post_class ?>">
    <div class="inner-box">
        <?php
        /**
         * woocommerce_before_shop_loop_item hook.
         *
         * @hooked woocommerce_template_loop_product_link_open - 10 : removed
         */
        do_action( 'woocommerce_before_shop_loop_item' );
        ?>

        <div class="image-box"> 
            <?php
            /**
             * woocommerce_before_shop_loop_item_title hook.
             *
             * @hooked woocommerce_show_product_loop_sale_flash - 10
             * @hooked woocommerce_template_loop_product_thumbnail - 10 : removed
             */
            do_action( 'woocommerce_before_shop_loop_item_title' );

            /**
             * woocommerce_after_shop_loop_item hook.
             *
             * @hooked woocommerce_template_loop_product_link_close - 5 : removed
             * @hooked woocommerce_template_loop_add_to_cart - 10
             */
            do_action( 'woocommerce_after_shop_loop_item' );
            ?>
        </div>

        <div class="product-description"> 
            <?php
            /**
             * woocommerce_shop_loop_item_title hook.
             *
             * @hooked woocommerce_template_loop_rating - 5
             * @hooked woocommerce_template_loop_product_title - 10 : removed
             */
            do_action( 'woocommerce_shop_loop_item_title' );

            /**
             * woocommerce_after_shop_loop_item_title hook.
             *
             * @hooked woocommerce_template_loop_rating - 5 : removed
             * @hooked woocommerce_template_loop_price - 10
             */
            do_action( 'woocommerce_after_shop_loop_item_title' );
            ?>
        </div>
    </div>
</li>
