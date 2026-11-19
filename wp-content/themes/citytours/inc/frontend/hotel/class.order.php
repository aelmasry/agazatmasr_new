<?php

if (!defined('ABSPATH'))
    exit; // Exit if accessed directly

if (!class_exists('CT_Hotel_Order'))
{

    class CT_Hotel_Order
    {

        public $order_id = '';
        public $service_data;

        public function __construct()
        {
            $a = func_get_args();
            $i = func_num_args();
            if (method_exists($this, $f = '__construct' . $i))
            {
                call_user_func_array(array($this, $f), $a);
            }
        }

        public function __construct1($order_id)
        {
            $this->order_id = $order_id;
        }

        public function __construct2($booking_no, $pin_code)
        {
            $this->order_id = $this->get_order_id($booking_no, $pin_code);
        }

        public static function get_order_id($booking_no, $pin_code)
        {
            global $wpdb;

            $sql = "SELECT ct_order.id FROM " . CT_ORDER_TABLE . " AS ct_order WHERE ct_order.booking_no='" . esc_sql($booking_no) . "' AND ct_order.pin_code='" . esc_sql($pin_code) . "'";
            $order_id = $wpdb->get_var($sql);

            if (empty($order_id))
            {
                return false;
            }

            return $order_id;
        }

        public function get_order_info()
        {
            global $wpdb;

            if (empty($this->order_id))
            {
                return false;
            }

            $sql = "SELECT ct_order.* FROM " . CT_ORDER_TABLE . " AS ct_order WHERE ct_order.id='" . esc_sql($this->order_id) . "'";
            $order_data = $wpdb->get_row($sql, ARRAY_A);

            if (empty($order_data))
            {
                return false;
            }

            return $order_data;
        }

        public function get_rooms()
        {
            global $wpdb;

            if (empty($this->order_id))
            {
                return false;
            }

            $hotel_data = $wpdb->get_results("SELECT ct_bookings.* FROM " . CT_ORDER_TABLE . " AS ct_order
											INNER JOIN " . CT_HOTEL_BOOKINGS_TABLE . " AS ct_bookings ON ct_bookings.order_id = ct_order.id
											WHERE ct_order.id='" . esc_sql($this->order_id) . "'", ARRAY_A);
            if (empty($hotel_data))
            {
                return false;
            }

            return $hotel_data;
        }

        public function get_tours()
        {
            global $wpdb;

            if (empty($this->order_id))
            {
                return false;
            }

            $tour_data = $wpdb->get_row("SELECT ct_bookings.* FROM " . CT_ORDER_TABLE . " AS ct_order
				INNER JOIN " . CT_TOUR_BOOKINGS_TABLE . " AS ct_bookings ON ct_bookings.order_id = ct_order.id
				WHERE ct_order.id='" . esc_sql($this->order_id) . "'", ARRAY_A);
            if (empty($tour_data))
            {
                return false;
            }

            return $tour_data;
        }

        public function get_services()
        {
            global $wpdb;

            if (empty($this->order_id))
            {
                return false;
            }

			$add_service_data = $wpdb->get_results("SELECT ct_add_bookings.*, ct_add_services.title FROM " . CT_ORDER_TABLE . " AS ct_order
				INNER JOIN " . CT_ADD_SERVICES_BOOKINGS_TABLE . " AS ct_add_bookings ON ct_add_bookings.order_id = ct_order.id
				INNER JOIN " . CT_ADD_SERVICES_TABLE . " AS ct_add_services ON ct_add_bookings.add_service_id = ct_add_services.id
				WHERE ct_order.id='" . esc_sql($this->order_id) . "'", ARRAY_A);
            if (empty($add_service_data))
            {
                return false;
            }

            return $add_service_data;
        }

    }

}
