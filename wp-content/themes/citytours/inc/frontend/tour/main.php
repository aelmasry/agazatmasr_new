<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

require_once( CT_INC_DIR . '/frontend/tour/functions.php');
require_once( CT_INC_DIR . '/frontend/tour/templates.php');
require_once( CT_INC_DIR . '/frontend/tour/ajax.php');

add_action( 'ct_tour_booking_wrong_data', 'ct_redirect_home' );
add_action( 'ct_tour_thankyou_wrong_data', 'ct_redirect_home' );
add_action( 'wp_ajax_ct_tour_update_cart', 'ct_tour_update_cart' );
add_action( 'wp_ajax_nopriv_ct_tour_update_cart', 'ct_tour_update_cart' );
add_action( 'wp_ajax_ct_tour_submit_booking', 'ct_tour_submit_booking' );
add_action( 'wp_ajax_nopriv_ct_tour_submit_booking', 'ct_tour_submit_booking' );

add_action( 'wp_ajax_ct_add_tour_to_woo_cart', 'ct_add_tour_to_woo_cart' );
add_action( 'wp_ajax_nopriv_ct_add_tour_to_woo_cart', 'ct_add_tour_to_woo_cart' );

add_action( 'wp_ajax_ct_get_tour_list',        'ct_get_tour_list' );
add_action( 'wp_ajax_nopriv_ct_get_tour_list', 'ct_get_tour_list' );