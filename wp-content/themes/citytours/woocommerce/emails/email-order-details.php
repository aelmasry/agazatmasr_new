<?php
/**
 * Order details table shown in emails.
 *
 * @see         https://docs.woocommerce.com/document/template-structure/
 * @package     WooCommerce/Templates/Emails
 * @version     3.3.1
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$text_align = is_rtl() ? 'right' : 'left';

do_action( 'woocommerce_email_before_order_table', $order, $sent_to_admin, $plain_text, $email ); ?>

<h2>
    <?php
    if ( $sent_to_admin ) {
        $before = '<a class="link" href="' . esc_url( $order->get_edit_order_url() ) . '">';
        $after  = '</a>';
    } else {
        $before = '';
        $after  = '';
    }
    /* translators: %s: Order ID. */
    echo wp_kses_post( $before . sprintf( __( 'Order #%s', 'woocommerce' ) . $after . ' (<time datetime="%s">%s</time>)', $order->get_order_number(), $order->get_date_created()->format( 'c' ), wc_format_datetime( $order->get_date_created() ) ) );
    ?>
</h2>

<table class="td" cellspacing="0" cellpadding="6" style="width: 100%; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;" border="1">
    <thead>
        <tr>
            <th class="td" scope="col" style="text-align:<?php echo esc_attr( $text_align ); ?>;"><?php esc_html_e( 'Product', 'woocommerce' ); ?></th>
            <th class="td" scope="col" style="text-align:<?php echo esc_attr( $text_align ); ?>;"><?php esc_html_e( 'Quantity', 'woocommerce' ); ?></th>
            <th class="td" scope="col" style="text-align:<?php echo esc_attr( $text_align ); ?>;"><?php esc_html_e( 'Price', 'woocommerce' ); ?></th>
        </tr>
    </thead>
    <tbody>
        <?php 
        echo wc_get_email_order_items( $order, array( // WPCS: XSS ok.
            'show_sku'      => $sent_to_admin,
            'show_image'    => false,
            'image_size'    => array( 32, 32 ),
            'plain_text'    => $plain_text,
            'sent_to_admin' => $sent_to_admin,
        ) ); 
        ?>
    </tbody>
    <tfoot>
        <?php
            $totals = $order->get_order_item_totals();

            if ( $totals ) {
                $i = 0;
                foreach ( $totals as $total ) {
                    $i++;
                    ?>
                    <tr>
                        <th class="td" scope="row" colspan="2" style="text-align:<?php echo esc_attr( $text_align ); ?>; <?php echo ( 1 === $i ) ? 'border-top-width: 4px;' : ''; ?>"><?php echo wp_kses_post( $total['label'] ); ?></th>
                        <td class="td" style="text-align:<?php echo esc_attr( $text_align ); ?>; <?php echo ( 1 === $i ) ? 'border-top-width: 4px;' : ''; ?>"><?php echo wp_kses_post( $total['value'] ); ?></td>
                    </tr>
                    <?php
                }
            }
            if ( $order->get_customer_note() ) {
                ?><tr>
                    <th class="td" scope="row" colspan="2" style="text-align:<?php echo esc_attr( $text_align ); ?>;"><?php esc_html_e( 'Note:', 'woocommerce' ); ?></th>
                    <td class="td" style="text-align:<?php echo esc_attr( $text_align ); ?>;"><?php echo wp_kses_post( wptexturize( $order->get_customer_note() ) ); ?></td>
                </tr><?php
            }
        ?>
    </tfoot>
</table>

<?php if ( ! $sent_to_admin ) : ?>

    <h3 style="margin: 20px 0;"><?php _e( 'Additional Information', 'citytours' ) ?></h3>

    <table class="td" cellspacing="0" cellpadding="6" style="width: 100%; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;" border="1">
        <thead>
            <tr>
                <th class="td" scope="col" style="text-align:<?php echo esc_attr( $text_align ); ?>;"><?php _e( 'Product', 'woocommerce' ) ?></th>
                <th class="td" scope="col" style="text-align:<?php echo esc_attr( $text_align ); ?>;"><?php echo esc_html__( 'Booking No', 'citytours' ) ?></th>
                <th class="td" scope="col" style="text-align:<?php echo esc_attr( $text_align ); ?>;"><?php echo esc_html__( 'Pin Code', 'citytours' ) ?></th>
            </tr>
        </thead>
        <tbody>
            <?php 
            global $wpdb;

            $items = $order->get_items(); 

            foreach ( $items as $item ) {
                $product_id = $item['product_id'];
                $hotel_tour_id = get_post_meta( $product_id, '_ct_post_id', true );

                if ( $hotel_tour_id ) { 
                    $booking_info = $wpdb->get_row( 'SELECT booking_no, pin_code FROM ' . CT_ORDER_TABLE . ' WHERE other = ' . $order->get_order_number(), ARRAY_A );
                    ?>
                    <tr>
                        <th class="td" scope="col" style="text-align:<?php echo esc_attr( $text_align ); ?>;"><?php echo $item['name'] ?></th>
                        <td class="td" scope="col" style="text-align:<?php echo esc_attr( $text_align ); ?>;"><?php echo $booking_info['booking_no'] ?></td>
                        <td class="td" scope="col" style="text-align:<?php echo esc_attr( $text_align ); ?>;"><?php echo $booking_info['pin_code'] ?></td>
                    </tr>
                    <?php
                }
            }
            ?>
        </tbody>
    </table>

<?php endif; ?>

<?php do_action( 'woocommerce_email_after_order_table', $order, $sent_to_admin, $plain_text, $email ); ?>
