<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

require_once( CT_INC_DIR . '/admin/admin_pages/admin.php' );
require_once( CT_INC_DIR . '/admin/hotel/main.php' );
require_once( CT_INC_DIR . '/admin/tour/main.php' );
require_once( CT_INC_DIR . '/admin/currencies-admin-panel.php' );

/*
 * admin notice hook function
 */
if ( ! function_exists('ct_admin_notice') ) {
	function ct_admin_notice() {
		$installed = get_option( 'install_ct_pages' );

		if ( empty( $installed ) && ( empty( $_GET['install_ct_pages'] ) && empty( $_GET['skip_ct_pages'] ) ) ) {
			echo '<div class="updated"><p>' . esc_html__( 'Welcome to CityTours - You\'re almost ready to launch.', 'citytours' ) . '</p><p><a class="button-primary" href="' . esc_url( admin_url( 'admin.php?page=theme_options&install_ct_pages=true' ) ) . '">' . esc_html__( 'Install Main Pages', 'citytours' ) . '</a> <a href="' . esc_url( admin_url( 'admin.php?page=theme_options&skip_ct_pages=true' ) ) . '" class="skip-setup">' . esc_html__( 'Skip setup', 'citytours' ) . '</a></p></div>';
		}
		
		if ( ! get_option('permalink_structure') ) {
			// echo '<div class="updated"><p>' . esc_html__( 'Please change your permalink setting to Post name. We strongly recommended that.', 'citytours' ) . '</p><p><a class="button-primary" href="' . esc_url( admin_url( 'options-permalink.php' ) ) . '">' . esc_html__( 'Edit Permalink Settings', 'citytours' ) . '</a></p></div>';
		}
	}
}

/*
 * remove pending booking if payment is not finished in 30 mins
 */
if ( ! function_exists( 'ct_order_remove_pending_order' ) ) {
	function ct_order_remove_pending_order( ) {
		global $wpdb;
		// set to cancelled if someone did not finish booking in 30 mins
		$check_time = date('Y-m-d H:i:s', strtotime('-30 minutes'));
		$wpdb->query( "UPDATE " . CT_ORDER_TABLE . " SET status = 'cancelled' WHERE status = 'pending' AND deposit_paid = 0 AND deposit_price > 0 AND created < '" . $check_time . "'" );
	}
}

add_action( 'admin_notices', 'ct_admin_notice' );
add_action( 'wp_ajax_get_review_rating_fields', 'ct_ajax_get_review_rating_fields' );
add_action( 'ct_hourly_cron', 'ct_order_remove_pending_order' );