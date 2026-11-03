<?php
/**
 * Single Product Up-Sells
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

if ( ! $upsells = $product->get_upsell_ids() ) {
    return;
}

if ( ! $ct_options['product_show_upsells'] ) { 
    return;
}

$posts_per_page = $ct_options['product_upsell_count'];
$columns = $ct_options['product_upsell_columns'];

$args = array(
    'post_type'           => 'product',
    'ignore_sticky_posts' => 1,
    'no_found_rows'       => 1,
    'posts_per_page'      => $posts_per_page,
    'orderby'             => $orderby,
    'post__in'            => $upsells,
    'post__not_in'        => array( $product->get_id() ),
    'meta_query'          => WC()->query->get_meta_query()
);

$products                           = new WP_Query( $args );

$woocommerce_loop['name']           = 'up-sells';
$woocommerce_loop['columns']        = apply_filters( 'woocommerce_up_sells_columns', $columns );
$woocommerce_loop['total_products'] = $posts_per_page;

if ( $products->have_posts() ) : ?>

    <div class="related-products up-sells upsells products">

        <h2><?php esc_html_e( 'You may also like&hellip;', 'woocommerce' ) ?></h2>

        <?php woocommerce_product_loop_start(); ?>

            <?php while ( $products->have_posts() ) : $products->the_post(); ?>

                <?php wc_get_template_part( 'content', 'product' ); ?>

            <?php endwhile; // end of the loop. ?>

        <?php woocommerce_product_loop_end(); ?>

    </div>

<?php endif;

wp_reset_postdata();
