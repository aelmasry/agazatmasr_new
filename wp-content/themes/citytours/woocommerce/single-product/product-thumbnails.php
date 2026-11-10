<?php
/**
 * Single Product Thumbnails
 *
 * @see         https://docs.woocommerce.com/document/template-structure/
 * @author      WooThemes
 * @package     WooCommerce/Templates
 * @version     3.3.2
 */

defined( 'ABSPATH' ) || exit;

global $post, $product;

// $attachment_ids = $product->get_gallery_attachment_ids();
$attachment_ids = $product->get_gallery_image_ids();

if ( $attachment_ids ) {
    $loop       = 0;
    $columns    = apply_filters( 'woocommerce_product_thumbnails_columns', 3 );

    ?>
    <div class="product-thumbs-slider <?php echo 'columns-' . $columns ?> owl-carousel">

        <?php
        if ( has_post_thumbnail() ) { 
            $props  = wc_get_product_attachment_props( get_post_thumbnail_id(), $post );
            echo get_the_post_thumbnail( $post->ID, apply_filters( 'single_product_small_thumbnail_size', 'shop_thumbnail' ), array(
                'title'  => $props['title'],
                'alt'    => $props['alt'],
            ) );
        } else { 
            echo sprintf( '<img src="%s" alt="%s" />', wc_placeholder_img_src(), __( 'Placeholder', 'woocommerce' ) );
        }

        foreach ( $attachment_ids as $attachment_id ) {

            $image_class = '';
            $props       = wc_get_product_attachment_props( $attachment_id, $post );

            if ( ! $props['url'] ) {
                continue;
            }

            echo apply_filters(
                'woocommerce_single_product_image_thumbnail_html',
                wp_get_attachment_image( $attachment_id, apply_filters( 'single_product_small_thumbnail_size', 'shop_thumbnail' ), 0, $props ),
                $attachment_id,
                $post->ID,
                esc_attr( $image_class )
            );

            $loop++;
        }
        ?>

    </div>
    <?php
}
