<?php
/* Hotel Thank-you Page Template */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! isset( $_REQUEST['booking_no'] ) || ! isset( $_REQUEST['pin_code'] ) ) {
	do_action('ct_hotel_thankyou_wrong_data');
	exit;
}

global $wpdb, $ct_options;

$order = new CT_Hotel_Order( $_REQUEST['booking_no'], $_REQUEST['pin_code'] );

if ( ! $order_data = $order->get_order_info() ) {
	do_action('ct_hotel_thankyou_wrong_data');
	exit;
}

$deposit_rate = get_post_meta( $order_data['post_id'], '_hotel_security_deposit', true );
$deposit_rate = empty( $deposit_rate ) ? 0 : $deposit_rate;

if ( empty( $order_data['deposit_paid'] ) )
{
	// init payment variables
	if ( $deposit_rate < 100 ) {
		$ItemName = sprintf( esc_html__( 'Deposit(%d%%) for your order %d', 'citytours' ), $deposit_rate, $order_data['id'] );
	} else {
		$ItemName = sprintf( esc_html__( 'Deposit for your order %d', 'citytours' ), $order_data['id'] );
	}

	$payment_data = array();
	$payment_data['item_name'] = $ItemName;
	$payment_data['item_number'] = $order_data['id'];
	$payment_data['item_desc'] = esc_html__( 'Check In', 'citytours' ) . ' ' . $order_data['date_from'] . ' ' . esc_html__( 'Check Out', 'citytours' ) . ' ' . $order_data['date_to'];
	$payment_data['item_qty'] = 1;
	$payment_data['item_price'] = $order_data['deposit_price'];
	$payment_data['item_total_price'] = $payment_data['item_qty'] * $payment_data['item_price'];
	$payment_data['grand_total'] = $payment_data['item_total_price'];
	$payment_data['currency'] = strtoupper( $order_data['currency_code'] );
	$payment_data['return_url'] = ct_get_current_page_url() . '?booking_no=' . $order_data['booking_no'] . '&pin_code=' . $order_data['pin_code'] . '&payment=success';
	$payment_data['cancel_url'] = ct_get_current_page_url() . '?booking_no=' . $order_data['booking_no'] . '&pin_code=' . $order_data['pin_code'] . '&payment=failed';

	$payment_result = ct_process_payment( $payment_data );

    // after payment
	if ( $payment_result ) {
		if ( ! empty( $payment_result['success'] ) && ( $payment_result['method'] == 'paypal' ) ) {
			$other_booking_data = array();
			if ( ! empty( $order_data['other'] ) ) {
				$other_booking_data = unserialize( $order_data['other'] );
			}
			$other_booking_data['pp_transaction_id'] = $payment_result['transaction_id'];
			$order_data['deposit_paid'] = 1;
			$update_status = $wpdb->update( CT_ORDER_TABLE, array( 'deposit_paid' => $order_data['deposit_paid'], 'other' => serialize( $other_booking_data ), 'status' => 'new' ), array( 'booking_no' => $order_data['booking_no'], 'pin_code' => $order_data['pin_code'] ) );
			if ( $update_status === false ) {
				do_action( 'ct_payment_update_booking_error' );
			} elseif ( empty( $update_status ) ) {
				do_action( 'ct_payment_update_booking_no_row' );
			} else {
				do_action( 'ct_payment_update_booking_success' );
			}
		}
	}
}

if ( empty( $order_data['deposit_paid'] ) ) {
	do_action('ct_order_deposit_payment_not_paid', $order_data ); // deposit payment not paid
}

if ( empty( $order_data['mail_sent'] ) ) {
	do_action('ct_order_conf_mail_not_sent', $order_data); // mail is not sent
}

$order_rooms = $order->get_rooms();

$order_services = $order->get_services();

?>

<div class="row">
	<div class="col-md-8">

		<div class="form_title">
			<h3><strong><i class="icon-ok"></i></strong><?php echo esc_html__( 'Thank you!', 'citytours' ); ?></h3>
			<p><?php echo sprintf(esc_html__( 'Your Booking Order is Now Confirmed %s.', 'citytours' ), $order_data['pin_code']) ?></p>
		</div>

		<div class="step">
			<?php if ( ! empty( $ct_options['hotel_thankyou_text_1'] ) ) : ?>
			<p><?php echo esc_html__( $ct_options['hotel_thankyou_text_1'] , 'citytours' ) ?></p>
			<?php endif; ?>
		</div><!--End step -->

		<div class="form_title">
			<h3><strong><i class="icon-tag-1"></i></strong><?php echo esc_html__( 'Booking summary', 'citytours' ); ?></h3>
<!--			 <p><?php // echo esc_html__( 'Followings are Summary of Your Booking.', 'citytours' ) ?></p> -->
		</div>

		<div class="step">
			<table class="table confirm">
                <tr>
                    <th colspan="2">
                        <?php echo get_the_title( $order_data['post_id'] ) ?>
                    </th>
                </tr>
				<tbody>
					<tr>
						<td><strong><?php echo esc_html__( 'Name', 'citytours' ) ?></strong></td>
						<td><?php echo esc_html( $order_data['first_name'] . ' ' . $order_data['last_name'] ) ?></td>
					</tr>
					<tr>
						<td><strong><?php echo esc_html__( 'Check in', 'citytours' ) ?></strong></td>
						<td><?php echo date_i18n( 'j F Y', strtotime( $order_data['date_from'] ) ) ?></td>
					</tr>
					<tr>
						<td><strong><?php echo esc_html__( 'Check out', 'citytours' ) ?></strong></td>
						<td><?php echo date_i18n( 'j F Y', strtotime( $order_data['date_to'] ) ) ?></td>
					</tr>
					<tr>
						<td><strong><?php echo esc_html__( 'Hotel name', 'citytours' ) ?></strong></td>
						<td><?php echo get_the_title( $order_data['post_id'] ) ?></td>
					</tr>
					<tr>
						<td><strong><?php echo esc_html__( 'Rooms', 'citytours' ) ?></strong></td>
						<td>
							<?php if ( ! empty( $order_rooms ) ) {
								foreach ($order_rooms as $room ) {
									echo esc_html( $room['rooms'] . ' ' . get_the_title( $room['room_type_id'] ) ) . '<br>';
								}
							}?>
						</td>
					</tr>
					<tr>
						<td><strong><?php echo esc_html__( 'Nights', 'citytours' ) ?></strong></td>
						<td><?php echo ct_get_day_interval( $order_data['date_from'], $order_data['date_to'] ) ?></td>
					</tr>
					<tr>
						<td><strong><?php echo esc_html__( 'Adults', 'citytours' ) ?></strong></td>
						<td><?php echo esc_html( $order_data['total_adults'] ) ?></td>
					</tr>
					<tr>
						<td><strong><?php echo esc_html__( 'Children', 'citytours' ) ?></strong></td>
						<td><?php echo esc_html( $order_data['total_kids'] ) ?></td>
					</tr>
					<?php if(!empty($order_services)) :
						foreach($order_services as $services) : ?>
						<tr>
							<td><strong><?php echo esc_html($services['title']) ?></strong></td>
							<td><strong><?php echo ct_price($services['total_price']) ?></strong></td>
						</tr>
					<?php endforeach;
					endif; ?>
					<?php if ( ! empty( $deposit_rate ) && $deposit_rate < 100 ) : ?>
						<tr>
							<td><strong><?php echo sprintf( esc_html__( 'Security Deposit(%d%%)', 'citytours' ), $deposit_rate ) ?></strong></td>
							<td ><?php echo ct_price( $order_data['deposit_price'], "", $order_data['currency_code'], 0 ) ?></td>
						</tr>
					<?php endif; ?>
					<tr>
						<td><strong><?php echo esc_html__( 'TOTAL COST', 'citytours' ) ?></strong></td>
						<td ><strong><?php echo ct_price( $order_data['total_price'] ) ?></strong></td>
					</tr>
				</tbody>
			</table>
		</div><!--End step -->

	</div><!--End col-md-8 -->

	<aside class="col-md-4">
		<div class="box_style_1">
			<h3 class="inner"><?php echo esc_html__( 'Thank you!', 'citytours' ) ?></h3>

			<!-- <p><?php echo esc_html__( 'Nihil inimicus ex nam, in ipsum dignissim duo. Tale principes interpretaris vim ei, has posidonium definitiones ut. Duis harum fuisset ut his, duo an dolor epicuri appareat.', 'citytours' ) ?></p> -->

			<?php if ( ! empty( $ct_options['hotel_thankyou_text_2'] ) ) : ?>
			<p><?php echo esc_html__( $ct_options['hotel_thankyou_text_2'], 'citytours' ) ?></p>
			<?php endif; ?>

		</div>

        <?php dynamic_sidebar('sidebar-contact-info'); ?>

    </aside>

</div><!--End row -->
