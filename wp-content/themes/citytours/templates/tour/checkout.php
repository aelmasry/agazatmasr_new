<?php
/* Tour Cart Page Template */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

global $ct_options;

// validation
$required_params = array( 'uid' );
foreach ( $required_params as $param ) {
	if ( ! isset( $_REQUEST[ $param ] ) ) {
		do_action( 'ct_tour_booking_wrong_data' ); // ct_redirect_home() - if data is not valid return to home
		exit;
	}
}

// init variables
$uid = $_REQUEST['uid'];
if ( ! CT_Hotel_Cart::get( $uid ) ) {
	do_action( 'ct_tour_booking_wrong_data' ); // ct_redirect_home() - if data is not valid return to home
	exit;
}

$cart = new CT_Hotel_Cart();
$tour_id = $cart->get_field( $uid, 'tour_id' );
$date = $cart->get_field( $uid, 'date' );
$cart_tour = $cart->get_field( $uid, 'tour' );
$adults = array_sum($cart_tour['adults']);
$kids = ($cart_tour['kids']) ? array_sum($cart_tour['kids']) : 0;
$cart_service = $cart->get_field( $uid, 'add_service' );
$user_info = ct_get_current_user_info();
$_countries = ct_get_all_countries();
$deposit_rate = get_post_meta( $tour_id, '_tour_security_deposit', true );
$deposit_rate = empty( $deposit_rate ) ? 0 : $deposit_rate;
$payOnline = get_post_meta($tour_id, '_tour_pay_online', true);
// function
if ( ! ct_get_tour_thankyou_page() ) {
	?>

	<h5 class="alert alert-warning">
		<?php echo esc_html__( 'Please set booking confirmation page in theme options panel.', 'citytours' ) ?>
	</h5>

	<?php
} else {
	?>

	<form id="booking-form" action="<?php echo esc_url( ct_get_tour_thankyou_page() ); ?>">

		<div class="row">

			<div class="col-md-8">

				<?php do_action( 'tour_checkout_main_before' ); ?>

				<div class="form_title">
					<h3><strong>1</strong><?php echo esc_html__( 'Order details', 'citytours' ) ?></h3>
					<p><?php echo esc_html__( 'Please fill your detail.', 'citytours' ) ?></p>
				</div>

				<div class="step">

					<div class="row">
						<div class="col-md-6 col-sm-6">
							<div class="form-group">
								<label><?php echo esc_html__( 'First name', 'citytours' ) ?></label>
								<input type="text" class="form-control" name="first_name" value="<?php echo esc_attr( $user_info['first_name'] ) ?>">
							</div>
						</div>

						<div class="col-md-6 col-sm-6">
							<div class="form-group">
								<label><?php echo esc_html__( 'Last name', 'citytours' ) ?></label>
								<input type="text" class="form-control" name="last_name" value="<?php echo esc_attr( $user_info['last_name'] ) ?>">
							</div>
						</div>
					</div>

					<div class="row">
						<div class="col-md-6 col-sm-6">
							<div class="form-group">
								<label><?php echo esc_html__( 'Email', 'citytours' ) ?></label>
								<input type="email" name="email" class="form-control" value="<?php echo esc_attr( $user_info['email'] ) ?>">
							</div>
						</div>

                        <div class="col-md-6 col-sm-6">
							<div class="form-group">
								<label><?php echo esc_html__( 'Telephone', 'citytours' ) ?></label>
								<input type="text" name="phone" class="form-control" value="<?php echo esc_attr( $user_info['phone'] ) ?>">
							</div>
						</div>

                    </div>

				</div><!--End step -->

				<div class="form_title">
                        <h3><strong>2</strong>طريقة الدفع الملائمة لك</h3>
                    </div>

                    <div class="step">
                        <div class="payment-options">
                            <ul>
                                <?php if ($payOnline == 1) : ?>
                                <li>
                                    <div class="radio-option">
                                        <input type="radio" name="payment_method" id="payment-1" value="visa_card">
                                        <label for="payment-1">الدفع ببطافة الإئتمان</label>
                                        <div class="show_box">
                                            <div class="">
                                                <label>الاسم على البطاقة</label>
                                                <input type="text" class="form-control" id="name_card_bookign" name="card_name">
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6 col-sm-6">
                                                    <div class="">
                                                        <label>رقم البطاقة</label>
                                                        <input type="text" id="card_number" name="card_number" class="form-control">
                                                    </div>
                                                </div>
                                                <div class="col-md-6 col-sm-6">
                                                    <img src="<?php echo esc_url(CT_IMAGE_URL . '/cards.png') ?>" width="207" height="43" alt="Cards" class="cards">
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <label>تاريخ إنتهاء الصلاحية</label>
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="">
                                                                <input type="text" id="expire_month" name="expire_month" class="form-control" placeholder="MM">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="">
                                                                <input type="text" id="expire_year" name="expire_year" class="form-control" placeholder="Year">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label>رمز الحماية</label>
                                                        <div class="row">
                                                            <div class="col-md-4">
                                                                <div class="">
                                                                    <input type="text" id="ccv" name="ccv" class="form-control" placeholder="CCV">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-8">
                                                                <img src="<?php echo esc_url(CT_IMAGE_URL . '/icon_ccv.gif') ?>" width="50" height="29" alt="ccv"><small>آخر 3 أرقام</small>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                                <?php endif ?>
                                <li>
                                    <div class="radio-option">
                                        <input type="radio" name="payment_method" id="payment-2" value="bank_transfer">
                                        <label for="payment-2">الدفع عن طريق تحوبل بنكي</label>
                                        <div class="show_box">
                                            <span class="small-text">إيداع قيمة الفاتورة بأحد فروع البنك العربى الأفريقى .حساب 5050 شركة توريكو للسياحة</span>
                                        </div>
                                    </div>
                                </li>
                                <li>
                                    <div class="radio-option">
                                        <input type="radio" name="payment_method" id="payment-3" value="cash_office">
                                        <label for="payment-3">الدفع كاش عن طريق مكتبنا في القاهرة</label>
                                        <div class="show_box">
                                            <span class="small-text">أبراج المروة (2) برج رقم (1) - شارع أحمد تيسير – كلية البنات- مصر الجديدة - القاهرة</span>
                                        </div>
                                    </div>
                                </li>
                                <li>
                                    <div class="radio-option">
                                        <input type="radio" name="payment_method" id="payment-4" value="cash_delegate">
                                        <label for="payment-4">الدفع كاش عن طريق أحد مندوبينا</label>
                                        <div class="show_box">
                                            <span class="small-text">برجاء العلم بأنة سيتم أضافة مبلغ 25 جنية مصاريف تحصيل للفاتورة داخل محافظة القاهرة</span>
                                            <div class="row">
                                                <div class="col-md-6 col-sm-6">
                                                    <div class="form-group">
                                                        <label>المنطقه</label>
                                                        <select class="form-control" name="location" id="country">
                                                            <option value="" selected>اختر المنطقه</option>
                                                            <option value="مصر الجديدة">مصر الجديدة</option>
                                                            <option value="مدينه نصر">مدينه نصر</option>
                                                            <option value="العباسيه">العباسيه</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6 col-sm-6">
                                                    <div class="form-group">
                                                        <label>العنوان </label>
                                                        <textarea name="location_desc" id="" cols="30" rows="3"></textarea>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>


				<div id="policy">

					<?php  //if ( ! empty( $ct_options['tour_terms_page'] ) ) :  ?>

						<h4><?php echo esc_html__( 'Cancellation policy', 'citytours' ) ?></h4>

						<div class="form-group">
							<input name="agree" value="agree" type="checkbox" checked>
							<strong> <?php printf( __('By continuing, you agree to the <a href="%s" target="_blank"><span class="skin-color">Terms and Conditions</span></a>.', 'citytours' ), ct_get_permalink_clang( $ct_options['tour_terms_page'] ) ) ?></strong>
						</div>

						<?php // endif;  ?>

					<button type="submit" class="btn_1 green medium book-now-btn book-now-btn1">تاكيد الحجز</button>
				</div>

				<?php do_action( 'tour_checkout_main_after' ); ?>

			</div>


			<aside class="col-md-4">

				<?php do_action( 'tour_checkout_sidebar_before' ); ?>

				<div class="box_style_1">
					<h3 class="inner"><?php echo esc_html__( '- Summary -', 'citytours' ) ?></h3>

					<table class="table table_summary">
						<tbody>
							<?php if ( ! empty( $date ) ) : ?>
							<tr>
								<td><?php echo esc_html__( 'Date', 'citytours' ) ?></td>
								<td class="text-right"><?php echo date_i18n( 'j F Y', ct_strtotime( $date ) ); ?></td>
							</tr>
							<?php endif; ?>

							<tr>
								<td><?php echo esc_html__( 'Adults', 'citytours' ) ?></td>
								<td class="text-right"><?php echo esc_html( $adults ) ?></td>
							</tr>

							<tr>
								<td><?php echo esc_html__( 'Children', 'citytours' ) ?></td>
								<td class="text-right"><?php echo esc_html( $kids ) ?></td>
							</tr>

							<?php
							if ( ! empty( $cart_service ) ) {
								foreach ( $cart_service as $key => $service ) {
									?>

									<tr>
										<td><?php echo esc_html( $service['title'] ) ?></td>
										<td class="text-right"><?php echo ct_price( $service['total'] ); ?></td>
									</tr>

									<?php
								}
							}
							?>

							<tr class="total">
								<td><?php echo esc_html__( 'Total cost', 'citytours' ) ?></td>
								<td class="text-right"><?php $total_price = $cart->get_field( $uid, 'total_price' ); if ( ! empty( $total_price ) ) echo ct_price( $total_price ) ?></td>
							</tr>

							<?php if ( ! empty( $deposit_rate ) && $deposit_rate < 100 ) : ?>
								<tr>
									<td><?php echo sprintf( esc_html__( 'Security Deposit (%d%%)', 'citytours' ), $deposit_rate ) ?></td>
									<td class="text-right"><?php if ( ! empty( $total_price ) ) echo ct_price( $total_price * $deposit_rate / 100 ) ?></td>
								</tr>
							<?php endif; ?>
						</tbody>
					</table>

					<button type="submit" class="btn_full book-now-btn">تاكيد الحجز</button>

					<a class="btn_full_outline" href="<?php echo esc_url( get_permalink( $tour_id ) ) ?>">
						<i class="icon-right"></i> <?php echo esc_html__( 'Modify your search', 'citytours' ) ?>
					</a>

					<input type="hidden" name="action" value="ct_tour_submit_booking">
					<input type="hidden" name="order_id" id="order_id" value="0">
					<input type="hidden" name="uid" id="uid" value="<?php echo esc_attr( $uid ) ?>">

					<?php wp_nonce_field( 'checkout' ); ?>
				</div>

				<?php do_action( 'tour_checkout_sidebar_after' ); ?>
				<?php ct_get_template('contact_info.php', '/'); ?>
			</aside>

		</div><!--End row -->
	</form>

    <?php wp_enqueue_script('icheck', CT_TEMPLATE_DIRECTORY_URI . '/js/icheck.js'); ?>

	<script type="text/javascript">
		var ajaxurl = '<?php echo esc_js( admin_url( 'admin-ajax.php' ) ) ?>';

		jQuery(document).ready( function($){

            $('input').iCheck({
              labelHover: false,
              checkboxClass: 'icheckbox_square-grey',
              radioClass: 'iradio_square-grey'
            });

			var validation_rules = {
				first_name: {required: true},
                last_name: {required: true},
                email: {required: true, email: true},
                phone: {required: true},
                address1: {required: true},
                city: {required: true},
                zip: {required: true},
                payment_method: {required: true},
			};

			//validation form
			$('#booking-form').validate({
				rules: validation_rules,
				submitHandler: function (form) {
					if ( $('input[name="agree"]').length ) {
						if ( $('input[name="agree"]:checked').length == 0 ) {
							alert("<?php echo esc_html__( 'Agree to terms&conditions is required' ,'citytours' ); ?>");
							return false;
						}
					}

					var booking_data = $('#booking-form').serialize();
					$('#overlay').fadeIn();

					$.ajax({
						type: "POST",
						url: ajaxurl,
						data: booking_data,
						success: function ( response ) {
							if ( response.success == 1 ) {
                                if ( response.result.payment == 'woocommerce' ) {
									<?php if ( function_exists( 'ct_woo_get_cart_page_url' ) && ct_woo_get_cart_page_url() ) { ?>
										window.location.href = '<?php echo esc_js( ct_woo_get_cart_page_url() ); ?>';
									<?php } else { ?>
										alert("<?php echo esc_js( esc_html__( 'Please set woocommerce cart page', 'citytours' ) ); ?>");
									<?php } ?>
									$('#overlay').fadeOut();
								} else {
									
									var confirm_url = $('#booking-form').attr('action');
									if ( confirm_url.indexOf('?') > -1 ) {
										confirm_url = confirm_url + '&';
									} else {
										confirm_url = confirm_url + '?';
									}
									confirm_url = confirm_url + 'booking_no=' + response.result.booking_no + '&pin_code=' + response.result.pin_code;
									if ( response.result.payment_info == 'paypal' ) {
										confirm_url += '&payment_info=paypal';
									}
									$('.book-now-btn').hide();
									window.location.href = confirm_url;
								}
							} else if ( response.success == -1 ) {
								alert( response.result );
								window.location.href = '';
							} else {
								if ( response.order_id != 0 ) {
									$('#order_id').val( response.order_id );
								}
								alert( response.result );
								$('#overlay').fadeOut();
							}
						}
					});

					return false;
				}
			});

			$('.form-radio-control').on('change', function(){
				if ( $(this).val() == 'cc' ) {
					$('#cc-container').show();
					$('#paypal-container').hide();
				} else {
					$('#cc-container').hide();
					$('#paypal-container').show();
				}
			});
		});
	</script>

<?php } ?>
