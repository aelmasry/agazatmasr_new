<?php
/**
 * Loop Add to Cart
 *
 * @see         https://docs.woocommerce.com/document/template-structure/
 * @author      WooThemes
 * @package     WooCommerce/Templates
 * @version     3.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

global $product;

$html_start = '<span class="icon-basket"></span><div class="tool-tip">';
$html_end = '</div>';

if ( isset( $class ) ) { 
    $class_array = explode( ' ', $class );

    $key = array_search( 'button' , $class_array );
    $class_array[$key] = 'btn_shop';

    $class = join( ' ', $class_array );
}

echo apply_filters( 'woocommerce_loop_add_to_cart_link',
    sprintf( '<a href="%s" data-quantity="%s" class="%s" %s>%s</a>',
        esc_url( $product->add_to_cart_url() ),
        esc_attr( isset( $quantity ) ? $quantity : 1 ),
        esc_attr( isset( $args['class'] ) ? $args['class'] : 'btn_shop' ),
        isset( $args['attributes'] ) ? wc_implode_html_attributes( $args['attributes'] ) : '',
        $html_start . esc_html( $product->add_to_cart_text() ) . $html_end
    ),
$product, $args );
