<!DOCTYPE html>
<!--[if IE 8]><html class="ie ie8"> <![endif]-->
<!--[if IE 9]><html class="ie ie9"> <![endif]-->
<!--[if gt IE 9]><!--> <!--<![endif]-->
<html <?php language_attributes(); ?>>

<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<?php if ( ! function_exists( 'has_site_icon' ) || ! has_site_icon() ) { ?>
	<link rel="shortcut icon" href="<?php echo esc_url( ct_favicon_url() ); ?>" type="image/x-icon" />
	<?php } ?>

	<style>
	.invoice-title h2, .invoice-title h3 {display: inline-block;}
	.table > tbody > tr > .no-line {border-top: none;}
	.table > thead > tr > .no-line {border-bottom: none;}
	.table > tbody > tr > .thick-line {border-top: 2px solid;}
	</style>

	<?php wp_head(); ?>
</head>

<body>

<div class="container">
<?php if ( ! empty( $_REQUEST['booking_no'] ) && ! empty( $_REQUEST['pin_code'] ) ) {
	$order = new CT_Hotel_Order( $_REQUEST['booking_no'], $_REQUEST['pin_code'] );
	if ( $order_info = $order->get_order_info() ) {
		$post_type = get_post_type( $order_info['post_id'] );
		$deposit_rate = 0;
		if ( 'tour' == $post_type ) {
			$deposit_rate = get_post_meta( $order_info['post_id'], '_tour_security_deposit', true );
		} elseif( 'hotel' == $post_type ) {
			$deposit_rate = get_post_meta( $order_info['post_id'], '_hotel_security_deposit', true );
		}
		?>

	<div class="row">
		<div class="col-xs-12">
			<div class="invoice-title">
				<h2><?php echo esc_html__( 'Invoice', 'citytours' ) ?></h2><h3 class="pull-right"><?php echo sprintf( esc_html__( 'Order # %s', 'citytours' ), $order_info['id'] ) ?></h3>
			</div>

			<hr>

			<div class="row">
				<div class="col-xs-6">
					<address>
					<strong><?php echo esc_html__( 'Billed To', 'citytours' ) ?>:</strong><br>
						<?php echo esc_html( $order_info['first_name'] . ' ' . $order_info['last_name'] ) ?><br>
						<?php if ( ! empty( $order_info['address1'] ) ) echo esc_html( $order_info['address1'] ) . '<br>' ?>
						<?php if ( ! empty( $order_info['address2'] ) ) echo esc_html( $order_info['address2'] ) . '<br>' ?>
						<?php echo esc_html( $order_info['state'] ) ?>, <?php echo esc_html( $order_info['city'] ) ?> <?php echo esc_html( $order_info['zip'] ) ?><br>
						<?php echo esc_html( $order_info['country'] ) ?><br>
					</address>
				</div>

				<div class="col-xs-6 text-right">
					<address>
						<strong><?php echo esc_html__( 'Order Date', 'citytours' ) ?>:</strong><br>
						<?php echo esc_html( date_i18n( ct_site_date_format(), strtotime( $order_info['created'] ) ) ) ?><br><br>
					</address>
				</div>
			</div>
		</div>
	</div>

	<div class="row">
		<div class="col-md-12">
			<div class="panel panel-default">
				<div class="panel-heading">
					<h3 class="panel-title"><strong><?php echo esc_html__( 'Order summary', 'citytours' ) ?></strong></h3>
				</div>

				<div class="panel-body">
					<div class="table-responsive">
						<table class="table table-condensed">
							<thead>
								<tr>
									<td><strong><?php echo esc_html__( 'Item ID', 'citytours' ) ?></strong></td>
									<td class="text-center"><strong><?php echo esc_html__( 'Description', 'citytours' ) ?></strong></td>
									<td class="text-right"><strong><?php echo esc_html__( 'Totals', 'citytours' ) ?></strong></td>
								</tr>
							</thead>

							<tbody>

								<?php
								if ( 'hotel' == $post_type ) {
									$order_rooms = $order->get_rooms();

									if ( ! empty( $order_rooms ) ) :
										foreach ( $order_rooms as $order_room ) : 
											?>

											<tr>
												<td class=""><?php echo esc_html( $order_room['room_type_id'] ) ?></td>
												<td class="text-center"><?php echo esc_html( get_the_title( $order_room['room_type_id'] ) ) . ' ' . date_i18n( ct_site_date_format(), strtotime( $order_info['date_from'] ) ) . ' - ' . date_i18n( ct_site_date_format(), strtotime( $order_info['date_to'] ) ) ?></td>
												<td class="text-right"><?php echo ct_price( $order_room['total_price'] ) ?></td>
											</tr>

											<?php 
										endforeach;
									endif;
								} elseif ( 'tour' == $post_type ) {
									$tour_data = $order->get_tours();

									if ( ! empty( $tour_data ) ) : 
										?>

										<tr>
											<td class=""><?php echo esc_html( $tour_data['tour_id'] ) ?></td>
											<td class="text-center"><?php echo esc_html( get_the_title( $tour_data['tour_id'] ) );
											if ( ! empty( $order_info['date_from'] ) && '0000-00-00' != $order_info['date_from'] ) echo ' - ' . date_i18n( ct_site_date_format(), strtotime( $order_info['date_from'] ) ) ?></td>
											<td class="text-right"><?php echo ct_price( $tour_data['total_price'] ) ?></td>
										</tr>

										<?php 
									endif;
								}

								$order_services = $order->get_services();

								if ( ! empty( $order_services ) ) :
								?>
								<?php foreach ( $order_services as $order_service ) : ?>
								<?php $service_data = ct_get_add_service( $order_service['add_service_id'] ); ?>
								<tr>
									<td class=""><?php echo esc_html( $order_service['add_service_id'] ) ?></td>
									<td class="text-center"><?php echo esc_html( $service_data->title ) ?></td>
									<td class="text-right"><?php echo ct_price( $order_service['total_price'] ) ?></td>
								</tr>
								<?php endforeach; ?>
								<?php endif; ?>

								<tr>
									<td class="thick-line"></td>
									<td class="thick-line text-center"><strong><?php echo esc_html__( 'Total', 'citytours' ) ?></strong></td>
									<td class="thick-line text-right"><?php echo ct_price( $order_info['total_price'] ) ?></td>
								</tr>

								<?php if ( ! empty( $deposit_rate ) && $deposit_rate < 100 ) : ?>
									<tr>
										<td class="no-line"></td>
										<td class="no-line text-center"><strong><?php echo sprintf( esc_html__( 'Security Deposit (%d%%)', 'citytours' ), $deposit_rate ) ?></strong></td>
										<td class="no-line text-right"><?php echo ct_price( $order_info['deposit_price'], "", $order_info['currency_code'], 0 ) ?></td>
									</tr>
								<?php endif; ?>
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>

	<?php } else { ?>
		<div class="alert-info"><?php echo esc_html__( 'Wrong Booking Number or Pin Code.', 'citytours' ); ?></div>
	<?php } ?>
<?php } else {
	echo esc_html__( 'You can not access to this page directly.', 'citytours' );
} 
?>

</div>

<?php wp_footer(); ?>

</body>
</html>