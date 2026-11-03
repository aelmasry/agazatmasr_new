<?php
/**
 * The template for displaying product content in the single-product.php template
 *
 * @see         https://docs.woocommerce.com/document/template-structure/
 * @author      WooThemes
 * @package     WooCommerce/Templates
 * @version     3.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

$product_id = get_the_ID();
$sidebar_position = ct_get_sidebar_position( $product_id );

if ( $sidebar_position == 'no' ) { 
    $container_class = 'col-md-12';
} else if ( $sidebar_position == 'right' ) { 
    $container_class = 'col-md-9';
} else { 
    $container_class = 'col-md-9 left-sidebar';
}

$post_class = join( ' ', get_post_class());
$post_class .= ' product-details';
?>

<div class="col-sm-12">

    <?php
        /**
         * woocommerce_before_single_product hook.
         *
         * @hooked wc_print_notices - 10
         */
         do_action( 'woocommerce_before_single_product' );

         if ( post_password_required() ) {
            echo get_the_password_form();
            return;
         }
    ?>

</div>
<div class="<?php echo $container_class ?>">

    <div id="product-<?php the_ID(); ?>" class="<?php echo $post_class ?>">

        <div class="basic-details">
            <div class="row"> 
                <div class="image-column col-sm-6 col-xs-12"> 
                    <?php
                        /**
                         * woocommerce_before_single_product_summary hook.
                         *
                         * @hooked woocommerce_show_product_sale_flash - 10
                         * @hooked woocommerce_show_product_images - 20
                         */
                        do_action( 'woocommerce_before_single_product_summary' );
                    ?>
                </div>

                <div class="info-column col-sm-6 col-xs-12 summary entry-summary">

                    <?php
                        /**
                         * woocommerce_single_product_summary hook.
                         *
                         * @hooked woocommerce_template_single_title - 5
                         * @hooked woocommerce_template_single_price - 10
                         * @hooked woocommerce_template_single_rating - 15
                         * @hooked woocommerce_template_single_excerpt - 20
                         * @hooked woocommerce_template_single_add_to_cart - 30
                         * @hooked woocommerce_template_single_meta - 40
                         * @hooked woocommerce_template_single_sharing - 50
                         * @hooked WC_Structured_Data::generate_product_data() - 60
                         */
                        do_action( 'woocommerce_single_product_summary' );
                    ?>

                </div><!-- .summary -->
            </div>
        </div>

        <?php
            /**
             * woocommerce_after_single_product_summary hook.
             *
             * @hooked woocommerce_output_product_data_tabs - 10
             * @hooked woocommerce_upsell_display - 15
             * @hooked woocommerce_output_related_products - 20
             */
            do_action( 'woocommerce_after_single_product_summary' );
        ?>

    </div><!-- #product-<?php the_ID(); ?> -->

    <?php do_action( 'woocommerce_after_single_product' ); ?>
</div>