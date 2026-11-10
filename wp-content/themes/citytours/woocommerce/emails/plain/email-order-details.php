<?php
/**
 * Order details table shown in emails.
 *
 * @see         https://docs.woocommerce.com/document/template-structure/
 * @package     WooCommerce/Templates/Emails
 * @version     3.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

do_action( 'woocommerce_email_before_order_table', $order, $sent_to_admin, $plain_text, $email );

/* translators: %s: Order ID. */
echo wp_kses_post( strtoupper( sprintf( __( 'Order number: %s', 'woocommerce' ), $order->get_order_number() ) ) ) . "\n";
echo wc_format_datetime( $order->get_date_created() ) . "\n";  // WPCS: XSS ok.
echo "\n" . wc_get_email_order_items( $order, array( // WPCS: XSS ok.
    'show_sku'    => $sent_to_admin,
    'show_image'  => false,
    'image_size'  => array( 32, 32 ),
    'plain_text'  => true,
    'sent_to_admin' => $sent_to_admin,
) );

echo "==========\n\n";

$totals = $order->get_order_item_totals();

if ( $totals ) {
    foreach ( $totals as $total ) {
        echo wp_kses_post( $total['label'] . "\t " . $total['value'] ) . "\n";
    }
}

if ( $order->get_customer_note() ) {
    echo esc_html__( 'Note:', 'woocommerce' ) . "\t " . wp_kses_post( wptexturize( $order->get_customer_note() ) ) . "\n";
}

if ( $sent_to_admin ) {
    /* translators: %s: Order link. */
    echo "\n" . sprintf( esc_html__( 'View order: %s', 'woocommerce' ), esc_url( $order->get_edit_order_url() ) ) . "\n";
}

if ( ! $sent_to_admin ) { 
    global $wpdb;

    echo "\n\n" . __( 'Additional Information', 'citytours' ) . "\n\n";

    foreach ( $order->get_items() as $order_item ) {

        $product_id = $order_item['product_id'];
        $hotel_tour_id = get_post_meta( $product_id, '_ct_post_id', true );

        if ( $hotel_tour_id ) { 
            $booking_info = $wpdb->get_row( 'SELECT booking_no, pin_code FROM ' . CT_ORDER_TABLE . ' WHERE other = ' . $order->get_order_number(), ARRAY_A );

            echo ' --- ' $order_item['name'] . " --- \n" ;
            echo __( 'Booking No : ', 'citytours' ) . $booking_info['booking_no'] . "\n";
            echo __( 'Pin Code : ', 'citytours' ) . $booking_info['pin_code'] . "\n";
        }
    }
}

do_action( 'woocommerce_email_after_order_table', $order, $sent_to_admin, $plain_text, $email );
