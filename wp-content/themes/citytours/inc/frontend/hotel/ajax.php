<?php

if (!defined('ABSPATH'))
    exit; // Exit if accessed directly

/*
 * Calculate the price of selected hotel room and return price array data
 */
if (!function_exists('ct_hotel_update_cart'))
{
    function ct_hotel_update_cart()
    {
        // validation
        if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'hotel_update_cart'))
        {
            print esc_html__('Sorry, your nonce did not verify.', 'citytours');
            exit;
        }

        // init variables
        $hotel_id = $_POST['hotel_id'];
        $date_from = $_POST['date_from'];
        $date_to = $_POST['date_to'];
        $room_ids = $_POST['room_type_id'];
        $uid = $hotel_id . $date_from . $date_to;
        $cart_data = array();
        $total_adults = 0;
        $total_kids = 0;
        $total_price = 0;

        // function
        foreach ($room_ids as $room_id => $value)
        {
            $arrayCount = max(array_keys($_POST['rooms'][$room_id]));

            for ($i = 0; $i <= $arrayCount; $i++)
            {
                if (!empty($_POST['rooms'][$room_id][$i]) && $_POST['adults'][$room_id][$i] >= 1)
                {
                    $rooms = (!empty($_POST['rooms'][$room_id][$i])) ? $_POST['rooms'][$room_id][$i] : 0;
                    $adults = (!empty($_POST['adults'][$room_id][$i]) ) ? $_POST['adults'][$room_id][$i] : 0;
                    $kids = (!empty($_POST['kids'][$room_id][$i]) ) ? $_POST['kids'][$room_id][$i] : 0;
                    $kidsAge = (!empty($_POST['kidsAge'][$room_id][$i]) ) ? $_POST['kidsAge'][$room_id][$i] : 0;

                    if ($rooms >= 1 && $adults >= 1)
                    {
                        $price_data = ct_hotel_calc_room_price($hotel_id, $room_id, $date_from, $date_to, $rooms, $adults, $kids, $kidsAge);
                    }
                    
                   // echo "<pre>"; print_r($price_data); exit;
                    
                    
                    if ($price_data && is_array($price_data))
                    {
                        $cart_room_data = array();
                        $cart_room_data['rooms'] = $rooms;
                        $cart_room_data['adults'] = $adults;
                        $cart_room_data['kids'] = $kids;
                        $cart_room_data['kidsAge'] = $kidsAge;
                        $cart_room_data['total'] = $price_data['total_price'];

                        $cart_data['room'][$room_id][$i] = $cart_room_data;
                        $total_adults += $adults;
                        $total_kids += $kids;
                        $total_price = round($total_price + $price_data['total_price']);

                    } elseif ($price_data === 0)
                    {
                        wp_send_json(array(
                            'success' => 0,
                            'message' => $price_data
                        ));
                    } else
                    {
                        wp_send_json(array(
                            'success' => 0,
                            'message' => __('Some validation error is occurred while calculate price.', 'citytours')
                        ));
                    }
                }
            }
        }

        if (!empty($_POST['add_service']))
        {
            global $wpdb;

            foreach ($_POST['add_service'] as $i => $value)
            {
                $services = $wpdb->get_row($wpdb->prepare('SELECT * FROM ' . CT_ADD_SERVICES_TABLE . ' WHERE id=%d AND post_id=%d', $i, $hotel_id));

                if (!empty($services))
                {
                    $cart_add_service_data = array();
                    $cart_add_service_data['title'] = $services->title;
                    $cart_add_service_data['price'] = $services->price;
                    $qty = 1;
                    $qty = ( isset($_POST['add_service_' . $i]) ) ? $_POST['add_service_' . $i] : 1;
                    $cart_add_service_data['qty'] = $qty;
                    $cart_add_service_data['total'] = $cart_add_service_data['price'] * $qty;

                    $cart_data['add_service'][$i] = $cart_add_service_data;
                    $total_price += $cart_add_service_data['total'];
                }
            }
        }

        $cart_data['total_price'] = $total_price;
        $cart_data['total_adults'] = $total_adults;
        $cart_data['total_kids'] = $total_kids;
        $cart_data['hotel_id'] = $hotel_id;
        $cart_data['date_from'] = $date_from;
        $cart_data['date_to'] = $date_to;

        CT_Hotel_Cart::_unset($uid);

        CT_Hotel_Cart::set($uid, $cart_data);

        wp_send_json(array(
            'success' => 1,
            'message' => 'success',
            'cart_data' => json_encode($cart_data)
        ));
    }

}

/*
 * Handle submit booking ajax request
 */
if (!function_exists('ct_hotel_submit_booking'))
{

    function ct_hotel_submit_booking()
    {
        global $wpdb, $ct_options;

        // validation
        $result_json = array(
            'success' => 0,
            'result' => '',
            'order_id' => 0
        );

        if (isset($_POST['order_id']) && empty($_POST['order_id']))
        {
            if (!isset($_POST['uid']) || !CT_Hotel_Cart::get($_POST['uid']))
            {
                $result_json['result'] = esc_html__('Sorry, some error occurred on input data validation.', 'citytours');

                wp_send_json($result_json);
            }

            if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'checkout'))
            {
                $result_json['result'] = esc_html__('Sorry, your nonce did not verify.', 'citytours');

                wp_send_json($result_json);
            }

            if (isset($_POST['payment_info']) && 'cc' == $_POST['payment_info'])
            {
                if (!ct_is_valid_card_number($_POST['billing_credircard']))
                {
                    $result_json['result'] = esc_html__('Credit card number you entered is invalid.', 'citytours');

                    wp_send_json($result_json);
                }

                if (!ct_is_valid_card_type($_POST['billing_cardtype']))
                {
                    $result_json['result'] = esc_html__('Card type is not valid.', 'citytours');

                    wp_send_json($result_json);
                }

                if (!ct_is_valid_expiry($_POST['billing_expdatemonth'], $_POST['billing_expdateyear']))
                {
                    $result_json['result'] = esc_html__('Card expiration date is not valid.', 'citytours');

                    wp_send_json($result_json);
                }

                if (!ct_is_valid_cvv_number($_POST['billing_ccvnumber']))
                {
                    $result_json['result'] = esc_html__('Card verification number (CVV) is not valid. You can find this number on your credit card.', 'citytours');

                    wp_send_json($result_json);
                }
            }

            // init variables
            $uid = $_POST['uid'];
            
            $post_fields = array(
                'first_name',
                'last_name',
                'email',
                'phone',
                'country',
                'address1',
                'address2',
                'city',
                'state',
                'zip',
                'payment_method',
                'location',
                'location_desc',
                'paid' => 0,
                'special_requirements'
            );

            $order_info = ct_order_default_order_data('new');

            foreach ($post_fields as $post_field)
            {
                if (!empty($_POST[$post_field]))
                {
                    $order_info[$post_field] = sanitize_text_field($_POST[$post_field]);
                }
            }

            $latest_order_id = $wpdb->get_var('SELECT id FROM ' . CT_ORDER_TABLE . ' ORDER BY id DESC LIMIT 1');
            $booking_no = mt_rand(1000, 9999);
            $booking_no .= $latest_order_id;
            $pin_code = mt_rand(1000, 9999);

            $cart_data = CT_Hotel_Cart::get($uid);

            $order_info['total_price'] = $cart_data['total_price'];
            $order_info['total_adults'] = $cart_data['total_adults'];
            $order_info['total_kids'] = $cart_data['total_kids'];
            $order_info['status'] = 'new';
            $order_info['deposit_paid'] = 1;
            $order_info['mail_sent'] = 0;
            $order_info['post_id'] = $cart_data['hotel_id'];
            $order_info['date_from'] = date('Y-m-d', ct_strtotime($cart_data['date_from']));
            $order_info['date_to'] = date('Y-m-d', ct_strtotime($cart_data['date_to']));
            $order_info['booking_no'] = $booking_no;
            $order_info['pin_code'] = $pin_code;
            $order_info['post_type'] = 'hotel';
            $order_info['created'] = date('Y-m-d H:i:s');

            // calculate deposit payment
            $deposit_rate = get_post_meta($cart_data['hotel_id'], '_hotel_security_deposit', true);

            // if woocommerce enabled change currency_code and exchange rate as default
            if (!empty($deposit_rate) && ct_is_woocommerce_integration_enabled())
            {
                $order_info['currency_code'] = ct_get_def_currency();
                $order_info['exchange_rate'] = 1;
            } else
            {
                if (!isset($_SESSION['exchange_rate']))
                {
                    ct_init_currency();
                }
                $order_info['exchange_rate'] = $_SESSION['exchange_rate'];
                $order_info['currency_code'] = ct_get_user_currency();
            }

            // if payment enabled set deposit price field
            if (!empty($deposit_rate) && ct_is_payment_enabled())
            {
                $decimal_prec = isset($ct_options['decimal_prec']) ? $ct_options['decimal_prec'] : 2;
                $order_info['deposit_price'] = round($deposit_rate / 100 * $order_info['total_price'] * $order_info['exchange_rate'], $decimal_prec);
                $order_info['deposit_paid'] = 0; // set unpaid if payment enabled
                $order_info['status'] = 'pending';
            }

            if ($wpdb->insert(CT_ORDER_TABLE, $order_info))
            {
                CT_Hotel_Cart::_unset($uid);

                $order_id = $wpdb->insert_id;

                if (!empty($cart_data['room']))
                {
                    foreach ($cart_data['room'] as $room_id => $rooms_data)
                    {
                        $cart_data['room'][$room_id] = array();
                        foreach ($rooms_data as $data)
                        {
                            $cart_data['room'][$room_id]['rooms'] += $data['rooms'];
                            $cart_data['room'][$room_id]['adults'] += $data['adults'];
                            $cart_data['room'][$room_id]['kids'] += $data['kids'];
                            $cart_data['room'][$room_id]['total'] += $data['total'];
                        }
                    }
                    
                    foreach ($cart_data['room'] as $room_id => $room_data) 
                    {
                        $room_booking_info = array();
                        $room_booking_info['order_id'] = $order_id;
                        $room_booking_info['hotel_id'] = $cart_data['hotel_id'];
                        $room_booking_info['room_type_id'] = $room_id;
                        $room_booking_info['rooms'] = $room_data['rooms'];
                        $room_booking_info['adults'] = $room_data['adults'];
                        $room_booking_info['kids'] = $room_data['kids'];
                        $room_booking_info['total_price'] = $room_data['total'];

                        $wpdb->insert(CT_HOTEL_BOOKINGS_TABLE, $room_booking_info);
                    }
                }

                if (!empty($cart_data['add_service']))
                {
                    foreach ($cart_data['add_service'] as $service_id => $service_data)
                    {
                        $service_booking_info = array();
                        $service_booking_info['order_id'] = $order_id;
                        $service_booking_info['add_service_id'] = $service_id;
                        $service_booking_info['qty'] = $service_data['qty'];
                        $service_booking_info['total_price'] = $service_data['total'];

                        $wpdb->insert(CT_ADD_SERVICES_BOOKINGS_TABLE, $service_booking_info);
                    }
                }

                if (( isset($_POST['payment_info']) && $_POST['payment_info'] == 'paypal' ) || (!isset($_POST['payment_info']) ))
                {
                    $result_json['success'] = 1;

                    $result_json['result'] = array();
                    $result_json['result']['order_id'] = $order_id;
                    $result_json['result']['booking_no'] = $booking_no;
                    $result_json['result']['pin_code'] = $pin_code;
                } else if (isset($_POST['payment_info']) && 'cc' == $_POST['payment_info'])
                {
                    $payment_process_result = ct_credit_card_paypal_process_payment($order_info);

                    if (1 == $payment_process_result['success'])
                    {
                        $result_json['success'] = 1;

                        $result_json['result'] = array();
                        $result_json['result']['order_id'] = $order_id;
                        $result_json['result']['booking_no'] = $booking_no;
                        $result_json['result']['pin_code'] = $pin_code;
                    } else
                    {
                        $result_json['success'] = 0;

                        $result_json['result'] = $payment_process_result['errormsg'];
                        $result_json['order_id'] = $order_id;
                    }
                }
            } else
            {
                $result_json['success'] = 0;
                $result_json['result'] = esc_html__('Sorry, An error occurred while add your order.', 'citytours');
            }
        } else if (isset($_POST['order_id']) && !empty($_POST['order_id']) && isset($_POST['payment_info']) && 'cc' == $_POST['payment_info'])
        {
            $order = new CT_Hotel_Order($_POST['order_id']);

            $order_info = $order->get_order_info();
            $payment_process_result = ct_credit_card_paypal_process_payment($order_info);

            if (1 == $payment_process_result['success'])
            {
                $result_json['success'] = 1;

                $result_json['result'] = array();
                $result_json['result']['order_id'] = $order->order_id;
                $result_json['result']['booking_no'] = $booking_no;
                $result_json['result']['pin_code'] = $pin_code;
            } else
            {
                $result_json['success'] = 0;

                $result_json['result'] = $payment_process_result['errormsg'];
                $result_json['order_id'] = $order->order_id;
            }
        }

        wp_send_json($result_json);
    }

}

/*
 * Update Room list based on hotel_id
 */
if (!function_exists('ct_ajax_hotel_get_hotel_room_list'))
{

    function ct_ajax_hotel_get_hotel_room_list()
    {
        $hotel_id = (!empty($_POST['hotel_id']) ) ? $_POST['hotel_id'] : 0;

        ct_hotel_get_room_list($hotel_id);
    }

}

/*
 * Add Hotel product to WooCommerce Cart
 */
if (!function_exists('ct_add_hotel_to_woo_cart'))
{

    function ct_add_hotel_to_woo_cart()
    {
        if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'hotel_update_cart'))
        {
            print esc_html__('Sorry, your nonce did not verify.', 'citytours');
            exit;
        }

        // init variables
        $hotel_id = $_POST['hotel_id'];
        $date_from = $_POST['date_from'];
        $date_to = $_POST['date_to'];
        $uid = $hotel_id . $date_from . $date_to;
        $cart_data = array();

        $hotel_product_id = ct_create_hotel_product($hotel_id, $uid);

        if (!$hotel_product_id || is_wp_error($hotel_product_id))
        {
            wp_send_json(array(
                'success' => 0,
                'message' => 'Can not add Hotel to Cart. Please try again later'
            ));
        }

        $cart = WC()->cart->get_cart();
        $in_cart = false;

        // check if product already in cart
        if (count($cart) > 0)
        {
            foreach ($cart as $cart_item_key => $values)
            {
                $_product = $values['data'];

                if ($_product->id == $hotel_product_id)
                {
                    $in_cart = true;
                    break;
                }
            }
        }

        if (!$in_cart)
        {
            WC()->cart->add_to_cart($hotel_product_id);
        }

        wp_send_json(array(
            'success' => 1,
            'message' => 'success'
        ));
    }

}

/*
 * Create product for selected Hotel
 */
if (!function_exists('ct_create_hotel_product'))
{
   function ct_create_hotel_product($hotel_id, $uid)
    {

        $deposit_rate = get_post_meta($hotel_id, '_hotel_security_deposit', true);
        $deposit_rate = empty($deposit_rate) ? 0 : $deposit_rate;

        $hotel_product = array(
            'post_title' => get_the_title($hotel_id),
            'post_content' => '',
            'post_status' => 'publish',
            'post_type' => 'product',
            'comment_status' => 'closed'
        );

        //Create Hotel Product
        $hotel_product_id = wp_insert_post($hotel_product);

        if ($hotel_product_id)
        {
            $cart = new CT_Hotel_Cart();
            $cart_info = $cart->get($uid);
            $attach_id = get_post_meta($hotel_id, "_thumbnail_id", true);

            add_post_meta($hotel_product_id, '_thumbnail_id', $attach_id);

            wp_set_object_terms($hotel_product_id, 'hotel', 'product_cat');
            wp_set_object_terms($hotel_product_id, 'simple_hotel', 'product_type');

            $default_attributes = array();
            update_post_meta($hotel_product_id, '_sku', 'sku' . $uid);
            update_post_meta($hotel_product_id, '_stock_status', 'instock');
            update_post_meta($hotel_product_id, '_visibility', 'visible');
            update_post_meta($hotel_product_id, '_virtual', 'yes');
            update_post_meta($hotel_product_id, '_default_attributes', $default_attributes);
            update_post_meta($hotel_product_id, '_manage_stock', 'no');
            update_post_meta($hotel_product_id, '_backorders', 'no');

            $booking_price = $cart_info['total_price'] * $deposit_rate / 100;
            update_post_meta($hotel_product_id, '_regular_price', $booking_price);
            update_post_meta($hotel_product_id, '_sale_price', $booking_price);
            update_post_meta($hotel_product_id, '_price', $booking_price);

            update_post_meta($hotel_product_id, '_ct_post_id', $hotel_id);
            update_post_meta($hotel_product_id, '_ct_booking_date_from', $cart_info['date_from']);
            update_post_meta($hotel_product_id, '_ct_booking_date_to', $cart_info['date_to']);
            update_post_meta($hotel_product_id, '_ct_total_price', $cart_info['total_price']);

            $booking_info = array();
            $temp = array();

            foreach ($cart_info['room'] as $room_id => $room_info)
            {
                $temp['room_id'] = $room_id;
                $temp['room_qty'] = $room_info['rooms'];
                $temp['adults'] = $room_info['adults'];
                $temp['kids'] = $room_info['kids'];
                $temp['total'] = $room_info['total'];

                $booking_info[] = $temp;

                unset($temp);
            }

            update_post_meta($hotel_product_id, '_ct_booking_info', $booking_info);

            $add_service = array();
            foreach ($cart_info['add_service'] as $service_id => $service_info)
            {
                $temp['service_id'] = $service_id;
                $temp['title'] = $service_info['title'];
                $temp['price'] = $service_info['price'];
                $temp['qty'] = $service_info['qty'];

                $add_service[] = $temp;

                unset($temp);
            }

            update_post_meta($hotel_product_id, '_ct_add_service', $add_service);
        }

        return $hotel_product_id;
    }

}