<?php
/**
 * Single Product Image
 *
 * @see         https://docs.woocommerce.com/document/template-structure/
 * @author      WooThemes
 * @package     WooCommerce/Templates
 * @version     3.3.2
 */

defined( 'ABSPATH' ) || exit;

global $woocommerce, $post, $product;

$attachment_ids = array();
if ( version_compare( $woocommerce->version, '3.0', ">=" ) ) {
    $attachment_ids = $product->get_gallery_image_ids();
} else {
    $attachment_ids = $product->get_gallery_attachment_ids();
}
?>

<div class="product-images images">
    <?php

    $html = "<div class='product-images-slider magnific-gallery owl-carousel'>";
    
    if ( has_post_thumbnail() ) {
        $attachment_count = count( $attachment_ids );
        $props            = wc_get_product_attachment_props( get_post_thumbnail_id(), $post );
        $image            = get_the_post_thumbnail( $post->ID, apply_filters( 'single_product_large_thumbnail_size', 'shop_single' ), array(
            'title'  => $props['title'],
            'alt'    => $props['alt'],
        ) );
        $html .= sprintf(
            '<a href="%s" itemprop="image" class="woocommerce-main-image " title="%s">%s</a>',
            esc_url( $props['url'] ),
            esc_attr( $props['caption'] ),
            $image
        );
    } else {
        echo apply_filters( 'woocommerce_single_product_image_html', sprintf( '<a href="%s" title="%s" class="placeholder-img"><img src="%s" alt="%s" /></a>', esc_url( wc_placeholder_img_src() ), __( 'Awaiting product image', 'woocommerce' ), esc_url( wc_placeholder_img_src() ), __( 'Awaiting product image', 'woocommerce' ) ), $post->ID );
    }

    if ( $attachment_ids ) { 
        foreach ( $attachment_ids as $attachment_id ) {
            $props = wc_get_product_attachment_props( $attachment_id, $post );
            $image = wp_get_attachment_image( $attachment_id, apply_filters( 'single_product_large_thumbnail_size', 'shop_single' ), 0, $props );

            $html .= sprintf(
                '<a href="%s" title="%s">%s</a>',
                esc_url( $props['url'] ),
                esc_attr( $props['caption'] ),
                $image
            );
        }
    }

    $html .= '</div>';

    echo apply_filters( 'woocommerce_single_product_image_html', $html, $post->ID );

    ?>

    <?php 
    if ( count( $attachment_ids ) ) { 
        do_action( 'woocommerce_product_thumbnails' );
    }
    ?>
</div>
