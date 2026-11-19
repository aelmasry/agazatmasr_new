<?php

if (!defined('ABSPATH'))
    exit; // Exit if accessed directly

require_once( CT_INC_DIR . '/frontend/hotel/main.php');
require_once( CT_INC_DIR . '/frontend/tour/main.php');
require_once( CT_INC_DIR . '/frontend/custom_style.php');

add_action('ct_order_conf_mail_not_sent', 'ct_order_conf_send_mail');
add_action('ct_order_deposit_payment_not_paid', 'ct_order_deposit_payment_not_paid');
add_action('wp_ajax_add_to_wishlist', 'ct_ajax_add_to_wishlist');
add_action('wp_ajax_nopriv_add_to_wishlist', 'ct_ajax_add_to_wishlist');

/*
 * get order default values
 */
if (!function_exists('ct_order_default_order_data'))
{
    function ct_order_default_order_data($type = 'new')
    {
        $default_order_data = array(
            'first_name' => '',
            'last_name' => '',
            'email' => '',
            'phone' => '',
            'address1' => '',
            'address2' => '',
            'city' => '',
            'state' => '',
            'zip' => '',
            'country' => '',
            'special_requirements' => '',
            'post_id' => '',
            'total_adults' => '',
            'total_kids' => '',
            'total_price' => '',
            'deposit_price' => 0,
            'currency_code' => '',
            'exchange_rate' => 1,
            'deposit_price' => 0,
            'deposit_paid' => 1,
            'date_from' => '',
            'date_to' => '',
            'booking_no' => '',
            'pin_code' => '',
            'status' => 'new',
            'updated' => date('Y-m-d H:i:s'),
            'payment_method' => '',
            'location' => '',
            'location_desc' => '',
            'paid' => 0,
            'operator_id' => 0,
        );

        if ($type == 'new')
        {
            $temp = array(
                'created' => date('Y-m-d H:i:s'),
                'mail_sent' => '',
                'other' => '',
                'id' => ''
            );

            $default_order_data = array_merge($default_order_data, $temp);
        }

        return $default_order_data;
    }

}

/*
 * echo deposit payment not paid notice on confirmation page
 */
if (!function_exists('ct_order_deposit_payment_not_paid'))
{

    function ct_order_deposit_payment_not_paid($order_data)
    {
        echo '<div class="alert alert-warning">' . esc_html__('Deposit payment is not paid.', 'citytours') . '<span class="close"></span></div>';
    }

}


/*
 * send confirmation email
 */
if (!function_exists('ct_order_conf_send_mail'))
{

    function ct_order_conf_send_mail($order_data)
    {
        global $wpdb;
        $mail_sent = 0;
        if (ct_order_send_email($order_data['booking_no'], $order_data['pin_code'], 'new'))
        {
            $mail_sent = 1;
            $wpdb->update(CT_ORDER_TABLE, array('mail_sent' => $mail_sent), array('booking_no' => $order_data['booking_no'], 'pin_code' => $order_data['pin_code']), array('%d'), array('%d', '%d'));
        }
    }

}

/*
 * send booking confirmation email function
 */
if (!function_exists('ct_order_send_email'))
{
    function ct_order_send_email($booking_no, $booking_pincode, $type = 'new', $subject = '', $description = '')
    {
        $order = new CT_Hotel_Order($booking_no, $booking_pincode);
        $order_data = $order->get_order_info();
        if (!empty($order_data))
        {
            $post_type = get_post_type($order_data['post_id']);
            if ('hotel' == $post_type)
            {
                return ct_hotel_generate_conf_mail($order, $type);
            } elseif ('tour' == $post_type)
            {
                return ct_tour_generate_conf_mail($order, $type);
            }
        }
        return false;
    }

}

/*
 * Handle Add to Wishlist Action on Detail Page
 */
if (!function_exists('ct_ajax_add_to_wishlist'))
{
	function ct_ajax_add_to_wishlist()
    {
        $result_json = array(
            'success' => 0,
            'result' => ''
        );

        if (!is_user_logged_in())
        {
            $result_json['success'] = 0;
            $result_json['result'] = esc_html__('Please login to update your wishlist.', 'citytours');

            wp_send_json($result_json);
        }

        $user_id = get_current_user_id();
        $new_item_id = sanitize_text_field(ct_post_org_id($_POST['post_id']));
        $wishlist = get_user_meta($user_id, 'wishlist', true);

        if (isset($_POST['remove']))
        {
            //remove
            $wishlist = array_diff($wishlist, array($new_item_id));

            if (update_user_meta($user_id, 'wishlist', $wishlist))
            {
                $result_json['success'] = 1;
                $result_json['result'] = esc_html__('This post has removed from your wishlist successfully.', 'citytours');
            } else
            {
                $result_json['success'] = 0;
                $result_json['result'] = esc_html__('Sorry, An error occurred while update wishlist.', 'citytours');
            }
        } else
        {
            //add
            if (empty($wishlist))
            {
                $wishlist = array();
            }

            if (!in_array($new_item_id, $wishlist))
            {
                array_push($wishlist, $new_item_id);

                if (update_user_meta($user_id, 'wishlist', $wishlist))
                {
                    $result_json['success'] = 1;
                    $result_json['result'] = esc_html__('This post has added to your wishlist successfully.', 'citytours');
                } else
                {
                    $result_json['success'] = 0;
                    $result_json['result'] = esc_html__('Sorry, An error occurred while update wishlist.', 'citytours');
                }
            } else
            {
                $result_json['success'] = 1;
                $result_json['result'] = esc_html__('Already exists in your wishlist.', 'citytours');
            }
        }

        wp_send_json($result_json);
        exit;
    }

}