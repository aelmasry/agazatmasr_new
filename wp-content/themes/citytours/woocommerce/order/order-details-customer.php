<?php
/**
 * Order Customer Details
 *
 * @see 	https://docs.woocommerce.com/document/template-structure/
 * @author  WooThemes
 * @package WooCommerce/Templates
 * @version 3.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$show_shipping = ! wc_ship_to_billing_address_only() && $order->needs_shipping_address();
?>

<div><h2><?php _e( 'Customer Details', 'woocommerce' ); ?></h2></div>

<table class="shop_table customer_details">

	<?php if ( $order->get_customer_note() ) : ?>
		<tr>
			<th><?php _e( 'Note:', 'woocommerce' ); ?></th>
			<td><?php echo wptexturize( $order->get_customer_note() ); ?></td>
		</tr>
	<?php endif; ?>

	<?php if ( $order->get_billing_email() ) : ?>
		<tr>
			<th><?php _e( 'Email:', 'woocommerce' ); ?></th>
			<td><?php echo esc_html( $order->get_billing_email() ); ?></td>
		</tr>
	<?php endif; ?>

	<?php if ( $order->get_billing_phone() ) : ?>
		<tr>
			<th><?php _e( 'Phone:', 'woocommerce' ); ?></th>
			<td><?php echo esc_html( $order->get_billing_phone() ); ?></td>
		</tr>
	<?php endif; ?>

	<?php do_action( 'woocommerce_order_details_after_customer_details', $order ); ?>
</table>

<?php if ( $show_shipping ) : ?>

<div class="col2-set addresses clearfix">
	<div class="col-1">

<?php endif; ?>

<div class="woocommerce-column__title title">
	<h3><?php _e( 'Billing Address', 'woocommerce' ); ?></h3>
</div>
<address>
	<?php echo wp_kses_post( $order->get_formatted_billing_address( __( 'N/A', 'woocommerce' ) ) ); ?>
</address>

<?php if ( $show_shipping ) : ?>

	</div><!-- /.col-1 -->
	
	<div class="col-2">
		<div class="woocommerce-column__title title">
			<h3><?php _e( 'Shipping Address', 'woocommerce' ); ?></h3>
		</div>
		<address>
			<?php echo wp_kses_post( $order->get_formatted_shipping_address( __( 'N/A', 'woocommerce' ) ) ); ?>
		</address>
	</div><!-- /.col-2 -->
</div><!-- /.col2-set -->

<?php endif; ?>
