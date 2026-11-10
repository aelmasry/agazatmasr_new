<?php 
require_once CT_INC_DIR . '/lib/payment/paypal.php';

/*
 * check if any payment is enabled
 */
if ( ! function_exists( 'ct_is_payment_enabled' ) ) {
	function ct_is_payment_enabled() {
		return apply_filters( 'ct_is_payment_enabled', false );
	}
}

/*
 * process payment
 */
if ( ! function_exists( 'ct_process_payment' ) ) {
	function ct_process_payment( $payment_data ) { 
		// $payment_data = array( 'item_name', 'item_number', 'item_desc', 'item_qty', 'item_price', 'item_total_price', 'grand_total', 'status', 'return_url', 'cancel_url', 'deposit_rate' )

		global $ct_options;

		if ( ct_is_paypal_enabled() ) {
			// validation
			if ( empty( $ct_options['paypal_api_username'] ) || empty( $ct_options['paypal_api_password'] ) || empty( $ct_options['paypal_api_signature'] ) ) {
				echo '<h5 class="alert alert-warning">Please check site paypal setting. <a href="' . admin_url( 'admin.php?page=theme_options' ) . '">' . admin_url( 'admin.php?page=theme_options' ) . '</a><span class="close"></span></h5>';

				return false;
			}

			$PayPalApiUsername 	= $ct_options['paypal_api_username'];
			$PayPalApiPassword 	= $ct_options['paypal_api_password'];
			$PayPalApiSignature = $ct_options['paypal_api_signature'];
			$PayPalMode 		= empty( $ct_options['paypal_sandbox'] )? 'live' : 'sandbox';

			// SetExpressCheckOut
			if ( ! isset( $_GET["token"] ) || ! isset( $_GET["PayerID"] ) ) {
				$padata = 	'&METHOD=SetExpressCheckout'.
							'&RETURNURL='.urlencode($payment_data['return_url'] ).
							'&CANCELURL='.urlencode($payment_data['cancel_url']).
							'&PAYMENTREQUEST_0_PAYMENTACTION='.urlencode("SALE").
							'&L_PAYMENTREQUEST_0_NAME0='.urlencode($payment_data['item_name']).
							'&L_PAYMENTREQUEST_0_NUMBER0='.urlencode($payment_data['item_number']).
							'&L_PAYMENTREQUEST_0_DESC0='.urlencode($payment_data['item_desc']).
							'&L_PAYMENTREQUEST_0_AMT0='.urlencode($payment_data['item_price']).
							'&L_PAYMENTREQUEST_0_QTY0='. urlencode($payment_data['item_qty']).
							'&NOSHIPPING=1'.
							'&SOLUTIONTYPE=Sole'.
							'&PAYMENTREQUEST_0_ITEMAMT='.urlencode($payment_data['item_total_price']).
							'&PAYMENTREQUEST_0_AMT='.urlencode($payment_data['grand_total']).
							'&PAYMENTREQUEST_0_CURRENCYCODE='.urlencode( $payment_data['currency'] ) .
							'&LOCALECODE=US'.
							'&LOGOIMG=' . ct_logo_url() .
							'&CARTBORDERCOLOR=FFFFFF'.
							'&ALLOWNOTE=1';
				
				//We need to execute the "SetExpressCheckOut" method to obtain paypal token
				$paypal= new CT_PayPal();
				$httpParsedResponseAr = $paypal->PPHttpPost( 'SetExpressCheckout', $padata, $PayPalApiUsername, $PayPalApiPassword, $PayPalApiSignature, $PayPalMode );

				//Respond according to message we receive from Paypal
				if ( "SUCCESS" == strtoupper( $httpParsedResponseAr["ACK"] ) || "SUCCESSWITHWARNING" == strtoupper( $httpParsedResponseAr["ACK"] ) ) {
					//Redirect user to PayPal store with Token received.
					$paypalmode = ( $PayPalMode == 'sandbox' )? '.sandbox' : '';
					$paypalurl 	='https://www' . $paypalmode . '.paypal.com/cgi-bin/webscr?cmd=_express-checkout&token=' . $httpParsedResponseAr["TOKEN"] . '';

					header( 'Location: ' . $paypalurl );
					exit;
				} else {
					//Show error message
					echo '<div class="alert alert-warning"><b>Error : </b>' . urldecode( $httpParsedResponseAr["L_LONGMESSAGE0"] ) . '<span class="close"></span></div>';
					echo '<pre>';
					print_r( $httpParsedResponseAr );
					echo '</pre>';

					exit;
				}
			}

			// DoExpressCheckOut
			if ( isset( $_GET["token"] ) && isset( $_GET["PayerID"] ) ) {
				$token = $_GET["token"];
				$payer_id = $_GET["PayerID"];

				$padata = 	'&TOKEN='.urlencode($token).
							'&PAYERID='.urlencode($payer_id).
							'&PAYMENTREQUEST_0_PAYMENTACTION='.urlencode("SALE").
							'&L_PAYMENTREQUEST_0_NAME0='.urlencode($payment_data['item_name']).
							'&L_PAYMENTREQUEST_0_NUMBER0='.urlencode($payment_data['item_number']).
							'&L_PAYMENTREQUEST_0_DESC0='.urlencode($payment_data['item_desc']).
							'&L_PAYMENTREQUEST_0_AMT0='.urlencode($payment_data['item_price']).
							'&L_PAYMENTREQUEST_0_QTY0='. urlencode($payment_data['item_qty']).
							'&PAYMENTREQUEST_0_ITEMAMT='.urlencode($payment_data['item_total_price']).
							'&PAYMENTREQUEST_0_AMT='.urlencode($payment_data['grand_total']).
							'&PAYMENTREQUEST_0_CURRENCYCODE='.urlencode($payment_data['currency']);

				// execute the "DoExpressCheckoutPayment" at this point to Receive payment from user.
				$paypal = new CT_PayPal();
				$httpParsedResponseAr = $paypal->PPHttpPost( 'DoExpressCheckoutPayment', $padata, $PayPalApiUsername, $PayPalApiPassword, $PayPalApiSignature, $PayPalMode );

				// Check if everything went ok..
				if ( "SUCCESS" == strtoupper( $httpParsedResponseAr["ACK"] ) || "SUCCESSWITHWARNING" == strtoupper( $httpParsedResponseAr["ACK"] ) ) {
					$transation_id = urldecode( $httpParsedResponseAr["PAYMENTINFO_0_TRANSACTIONID"] );

					echo '<div class="alert alert-success">' . __( 'Payment Received Successfully! Your Transaction ID : ', 'citytours' ) . $transation_id . '<span class="close"></span></div>';

					// GetTransactionDetails requires a Transaction ID, and GetExpressCheckoutDetails requires Token returned by SetExpressCheckOut
					$padata = '&TOKEN=' . urlencode($token);
					$paypal= new CT_PayPal();
					$httpParsedResponseAr = $paypal->PPHttpPost( 'GetExpressCheckoutDetails', $padata, $PayPalApiUsername, $PayPalApiPassword, $PayPalApiSignature, $PayPalMode );

					if ( "SUCCESS" == strtoupper( $httpParsedResponseAr["ACK"] ) || "SUCCESSWITHWARNING" == strtoupper( $httpParsedResponseAr["ACK"] ) ) {
						return array( 
							'success'		 => 1, 
							'method'		 => 'paypal', 
							'transaction_id' => $transation_id 
						);
					} else  {
						echo '<div class="alert alert-warning"><b>GetTransactionDetails failed:</b>' . urldecode( $httpParsedResponseAr["L_LONGMESSAGE0"] ) . '<span class="close"></span></div>';
						echo '<pre>';
						print_r( $httpParsedResponseAr );
						echo '</pre>';

						exit;
					}
				} else {
					echo '<div class="alert alert-warning"><b>Error : </b>' . urldecode( $httpParsedResponseAr["L_LONGMESSAGE0"] ) . '<span class="close"></span></div>';
					echo '<pre>';
					print_r( $httpParsedResponseAr );
					echo '</pre>';

					exit;
				}
			}
		}

		return false;
	}
}

if ( ! function_exists( 'ct_credit_card_paypal_process_payment' ) ) {
	function ct_credit_card_paypal_process_payment( $order_data ) { 
		global $wpdb, $ct_options;

		$PayPalApiUsername 	= $ct_options['paypal_api_username'];
		$PayPalApiPassword 	= $ct_options['paypal_api_password'];
		$PayPalApiSignature = $ct_options['paypal_api_signature'];
		$paypalmode 		= empty( $ct_options['paypal_sandbox'] ) ? '' : '.sandbox';

		$gatewayRequestData = array(
			'PAYMENTACTION' => 'Sale',
			'VERSION' => '84.0',
			'METHOD' => 'DoDirectPayment',
			'USER' => $PayPalApiUsername,
			'PWD' => $PayPalApiPassword,
			'SIGNATURE' => $PayPalApiSignature,
			//'AMT' => round($order_data['deposit_price'], 2),
			'AMT' => $order_data['deposit_price'],
			'FIRSTNAME' => $order_data['first_name'],
			'LASTNAME' => $order_data['last_name'],
			'CITY' => $order_data['city'],
			'STATE' => $order_data['state'],
			'ZIP' => $order_data['zip'],
			'IPADDRESS' => $_SERVER['REMOTE_ADDR'],
			'CREDITCARDTYPE' => $_POST['billing_cardtype'],
			'ACCT' => $_POST['billing_credircard'],
			'CVV2' => $_POST['billing_ccvnumber'],
			'EXPDATE' => sprintf( '%s%s', $_POST['billing_expdatemonth'], $_POST['billing_expdateyear'] ),
			'STREET' => sprintf( '%s, %s', $order_data['address1'], $order_data['address2'] ),
			'CURRENCYCODE' => urlencode(strtoupper( $order_data['currency_code'] ) ),
			'BUTTONSOURCE' => 'TipsandTricks_SP',
		);

		$result = array();

		$erroMessage = "";
		$api_url = "https://api-3t" . $paypalmode . ".paypal.com/nvp";
		$request = array(
			'method' 		=> 'POST',
			'httpversion' 	=> '1.1',
			'timeout' 		=> 100,
			'blocking' 		=> true,
			'sslverify' 	=> empty( $ct_options['paypal_sandbox'] ) ? true : false,
			'body' 			=> $gatewayRequestData
		);

		$response = wp_remote_post( $api_url, $request );

		if ( ! is_wp_error( $response ) ) {
			$parsedResponse = ct_parse_paypal_response( $response );

			if ( array_key_exists( 'ACK', $parsedResponse ) ) {
				switch ($parsedResponse['ACK']) {
					case 'Success':
					case 'SuccessWithWarning':
						$other_booking_data = array();

						if ( ! empty( $order_data['other'] ) ) {
							$other_booking_data = unserialize( $order_data['other'] );
						}
						
						$other_booking_data['pp_transaction_id'] = $parsedResponse['TRANSACTIONID'];
						$order_data['deposit_paid'] = 1;

						$update_status = $wpdb->update( 
							CT_ORDER_TABLE, 
							array( 
								'deposit_paid' 	=> $order_data['deposit_paid'], 
								'other' 		=> serialize( $other_booking_data ), 
								'status' 		=> 'new' 
							), 
							array( 
								'booking_no' 	=> $order_data['booking_no'], 
								'pin_code' 		=> $order_data['pin_code'] 
							) 
						);
						
						if ( $update_status === false ) {
							$result['success'] = 0;
							$result['errormsg'] = esc_html__( 'Sorry, An error occurred while add your order.', 'citytours' );

							do_action( 'ct_payment_update_booking_error' );
						} elseif ( empty( $update_status ) ) {
							$result['success'] = 0;
							$result['errormsg'] = esc_html__( 'Sorry, An error occurred because no rows are matched in database.', 'citytours' );

							do_action( 'ct_payment_update_booking_no_row' );
						} else {
							$result['success'] = 1;

							do_action( 'ct_payment_update_booking_success' );
						}
						break;

					default:
						$result['success'] = 0;
						$result['errormsg'] = $parsedResponse['L_LONGMESSAGE0'];
						break;
				}
			}
		} else {
			// Uncomment to view the http error
			//$result['errormsg'] = print_r($response->errors, true);
			$result['success'] = 0;
			$result['errormsg'] = esc_html__( 'Something went wrong while performing your request. Please contact website administrator to report this problem.', 'citytours' );
		}

		return $result;
	}
}

if ( ! function_exists( 'ct_parse_paypal_response' ) ) { 
	function ct_parse_paypal_response( $response ) {
		$result = array();
		$enteries = explode( '&', $response['body'] );

		foreach ( $enteries as $nvp ) {
			$pair = explode( '=', $nvp );

			if ( count( $pair ) > 1 ) {
				$result[urldecode($pair[0])] = urldecode( $pair[1] );
			}
		}

		return $result;
	}
}

if ( ! function_exists( 'ct_is_valid_card_number' ) ) { 
	function ct_is_valid_card_number( $toCheck ) {
	    if ( ! is_numeric( $toCheck ) )
	        return false;

	    $number = preg_replace('/[^0-9]+/', '', $toCheck);
	    $strlen = strlen($number);
	    $sum 	= 0;

	    if ( $strlen < 13 ) {
	        return false;
	    }

	    for ( $i = 0; $i < $strlen; $i++ ) {
	        $digit = substr( $number, $strlen - $i - 1, 1 );

	        if ( $i % 2 == 1 ) {
	            $sub_total = $digit * 2;

	            if ( $sub_total > 9 ) {
	                $sub_total = 1 + ($sub_total - 10);
	            }
	        } else {
	            $sub_total = $digit;
	        }

	        $sum += $sub_total;
	    }

	    if ( $sum > 0 AND $sum % 10 == 0 ) {
	        return true;
	    }

	    return false;
	}
}

if ( ! function_exists( 'ct_is_valid_card_type' ) ) { 
	function ct_is_valid_card_type( $toCheck ) {
	    $acceptable_cards = array(
	        "Visa",
	        "MasterCard",
	        "Discover",
	        "Amex"
	    );

	    return $toCheck AND in_array( $toCheck, $acceptable_cards );
	}
}

if ( ! function_exists( 'ct_is_valid_expiry' ) ) { 
	function ct_is_valid_expiry( $month, $year ) {
	    $now 		= time();
	    $thisYear 	= (int) date( 'Y', $now );
	    $thisMonth  = (int) date( 'm', $now );

	    if ( is_numeric( $year ) && is_numeric( $month ) ) {
	        $thisDate 	= mktime( 0, 0, 0, $thisMonth, 1, $thisYear );
	        $expireDate = mktime( 0, 0, 0, $month, 1, $year );

	        return $thisDate <= $expireDate;
	    }

	    return false;
	}
}


if ( ! function_exists( 'ct_is_valid_cvv_number' ) ) { 
	function ct_is_valid_cvv_number( $toCheck ) {
	    $length = strlen( $toCheck );

	    return is_numeric( $toCheck ) AND $length > 2 AND $length < 5;
	}
}

add_filter( 'http_request_timeout', 'ct_extend_http_request_timeout' );

if ( ! function_exists( 'ct_extend_http_request_timeout' ) ) { 
	function ct_extend_http_request_timeout( $time ) {
	    // Default timeout is 5
	    return 200;
	}
}