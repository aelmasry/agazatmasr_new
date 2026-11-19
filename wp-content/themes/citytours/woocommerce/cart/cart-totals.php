<?php
/**
 * Cart totals
 *
 * @see         https://docs.woocommerce.com/document/template-structure/
 * @author      WooThemes
 * @package     WooCommerce/Templates
 * @version     2.3.6
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

global $ct_options;

$classes = '';
$crosssells = WC()->cart->get_cross_sells();

if ( !$crosssells || !$ct_options['cart_show_cross_sells'] ) {
    $classes .= 'col-md-offset-8 col-sm-offset-6';
}

$classes .= ' col-md-4 col-sm-6';

if ( WC()->customer->has_calculated_shipping() ) {
    $classes .= ' calculated_shipping';
}

?>
<div class="cart_totals <?php echo $classes ?>">

    <?php do_action( 'woocommerce_before_cart_totals' ); ?>

    <!-- <h2><?php _e( 'Cart Totals', 'woocommerce' ); ?></h2> -->

    <table cellspacing="0" class="shop_table shop_table_responsive totals-table">

        <tr class="cart-subtotal clearfix">
            <th class="col"><?php _e( 'Subtotal', 'woocommerce' ); ?></th>
            <td class="col" data-title="<?php esc_attr_e( 'Subtotal', 'woocommerce' ); ?>"><?php wc_cart_totals_subtotal_html(); ?></td>
        </tr>

        <?php foreach ( WC()->cart->get_coupons() as $code => $coupon ) : ?>
            <tr class="cart-discount coupon-<?php echo esc_attr( sanitize_title( $code ) ); ?> clearfix">
                <th class="col"><?php wc_cart_totals_coupon_label( $coupon ); ?></th>
                <td class="col" data-title="<?php echo esc_attr( wc_cart_totals_coupon_label( $coupon, false ) ); ?>"><?php wc_cart_totals_coupon_html( $coupon ); ?></td>
            </tr>
        <?php endforeach; ?>

        <?php if ( WC()->cart->needs_shipping() && WC()->cart->show_shipping() ) : ?>

            <?php do_action( 'woocommerce_cart_totals_before_shipping' ); ?>

            <?php wc_cart_totals_shipping_html(); ?>

            <?php do_action( 'woocommerce_cart_totals_after_shipping' ); ?>

        <?php elseif ( WC()->cart->needs_shipping() && 'yes' === get_option( 'woocommerce_enable_shipping_calc' ) ) : ?>

            <tr class="shipping clearfix">
                <th class="col"><?php _e( 'Shipping', 'woocommerce' ); ?></th>
                <td class="col" data-title="<?php esc_attr_e( 'Shipping', 'woocommerce' ); ?>"><?php woocommerce_shipping_calculator(); ?></td>
            </tr>

        <?php endif; ?>

        <?php foreach ( WC()->cart->get_fees() as $fee ) : ?>
            <tr class="fee clearfix">
                <th class="col"><?php echo esc_html( $fee->name ); ?></th>
                <td class="col" data-title="<?php echo esc_attr( $fee->name ); ?>"><?php wc_cart_totals_fee_html( $fee ); ?></td>
            </tr>
        <?php endforeach; ?>

        <?php if ( wc_tax_enabled() && 'excl' === WC()->cart->tax_display_cart ) :
            $taxable_address = WC()->customer->get_taxable_address();
            $estimated_text  = WC()->customer->is_customer_outside_base() && ! WC()->customer->has_calculated_shipping()
                    ? sprintf( ' <small>(' . __( 'estimated for %s', 'woocommerce' ) . ')</small>', WC()->countries->estimated_for_prefix( $taxable_address[0] ) . WC()->countries->countries[ $taxable_address[0] ] )
                    : '';

            if ( 'itemized' === get_option( 'woocommerce_tax_total_display' ) ) : ?>
                <?php foreach ( WC()->cart->get_tax_totals() as $code => $tax ) : ?>
                    <tr class="tax-rate tax-rate-<?php echo sanitize_title( $code ); ?> clearfix">
                        <th class="col"><?php echo esc_html( $tax->label ) . $estimated_text; ?></th>
                        <td class="col" data-title="<?php echo esc_attr( $tax->label ); ?>"><?php echo wp_kses_post( $tax->formatted_amount ); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr class="tax-total clearfix">
                    <th class="col"><?php echo esc_html( WC()->countries->tax_or_vat() ) . $estimated_text; ?></th>
                    <td class="col" data-title="<?php echo esc_attr( WC()->countries->tax_or_vat() ); ?>"><?php wc_cart_totals_taxes_total_html(); ?></td>
                </tr>
            <?php endif; ?>
        <?php endif; ?>

        <?php do_action( 'woocommerce_cart_totals_before_order_total' ); ?>

        <tr class="order-total total">
            <th class="col"><?php _e( 'Total', 'woocommerce' ); ?></th>
            <td class="col" data-title="<?php esc_attr_e( 'Total', 'woocommerce' ); ?>"><?php wc_cart_totals_order_total_html(); ?></td>
        </tr>

        <?php do_action( 'woocommerce_cart_totals_after_order_total' ); ?>

    </table>

    <div class="wc-proceed-to-checkout">
        <?php do_action( 'woocommerce_proceed_to_checkout' ); ?>
    </div>

    <?php do_action( 'woocommerce_after_cart_totals' ); ?>

</div>
