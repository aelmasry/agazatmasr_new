<?php

if (!defined('ABSPATH'))
    exit; // Exit if accessed directly

if (!class_exists('CT_Hotel_Cart')) {

    class CT_Hotel_Cart
    {

        public function __construct()
        {
            if (empty($_SESSION['cart'])) {
                $_SESSION['cart'] = array();
            }
        }


        public static function set($uid, $data)
        {
            if (!empty($uid)) {
                $_SESSION['cart'][$uid] = $data;
            }
        }


        public static function get($uid = 0)
        {
            if (!empty($_SESSION['cart']) && !empty($_SESSION['cart'][$uid])) {
                return $_SESSION['cart'][$uid];
            }

            return false;
        }


        public static function _unset($uid = 0)
        {
            if (!empty($_SESSION['cart']) && !empty($_SESSION['cart'][$uid])) {
                unset($_SESSION['cart'][$uid]);
            }
        }


        public function get_field($uid = 0, $field = 'total_price')
        {
            $cart = $this->get($uid);

            if ($cart && !empty($cart[$field])) {
                return $cart[$field];
            }

            return 0;
        }


        public function get_room_field($uid, $room_id, $field = 'quantity')
        {
            $cart = $this->get($uid);

            if ($cart && !empty($cart['room']) && !empty($cart['room'][$room_id]) && !empty($cart['room'][$room_id][$field])) {
                return $cart['room'][$room_id][$field];
            }

            return 0;
        }
        
        
        public function get_room_fields($uid, $room_id)
        {
            $cart = $this->get($uid);

            if ($cart && !empty($cart['room']) && !empty($cart['room'][$room_id])) {
                return $cart['room'][$room_id];
            }

            return 0;
        }

    }

}