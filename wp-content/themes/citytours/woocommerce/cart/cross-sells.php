<?php
/**
 * Cross-sells
 *
 * @see         https://docs.woocommerce.com/document/template-structure/
 * @author      WooThemes
 * @package     WooCommerce/Templates
 * @version     3.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

global $product, $woocommerce_loop, $ct_options;

if ( ! $crosssells = WC()->cart->get_cross_sells() ) {
    return;
}

if ( ! $ct_options['cart_show_cross_sells'] ) { 
    return;
}

$columns = isset( $ct_options['cart_cross_sells_columns'] )? $ct_options['cart_cross_sells_columns'] : 3;
$posts_per_page = isset( $ct_options['cart_cross_sells_count'] )? $ct_options['cart_cross_sells_count'] : 3;

$args = array(
    'post_type'           => 'product',
    'ignore_sticky_posts' => 1,
    'no_found_rows'       => 1,
    'posts_per_page'      => apply_filters( 'woocommerce_cross_sells_total', $posts_per_page ),
    'orderby'             => $orderby,
    'post__in'            => $crosssells,
    'meta_query'          => WC()->query->get_meta_query()
);

$products                           = new WP_Query( $args );
$woocommerce_loop['name']           = 'cross-sells';
$woocommerce_loop['columns']        = apply_filters( 'woocommerce_cross_sells_columns', $columns );
$woocommerce_loop['total_products'] = $posts_per_page;

if ( $products->have_posts() ) : ?>

    <div class="cross-sells related-products col-md-8 col-sm-6">

        <h2><?php _e( 'You may be interested in&hellip;', 'woocommerce' ) ?></h2>

        <?php woocommerce_product_loop_start(); ?>

            <?php while ( $products->have_posts() ) : $products->the_post(); ?>

                <?php wc_get_template_part( 'content', 'product' ); ?>

            <?php endwhile; // end of the loop. ?>

        <?php woocommerce_product_loop_end(); ?>

    </div>

<?php endif;

wp_reset_postdata();
