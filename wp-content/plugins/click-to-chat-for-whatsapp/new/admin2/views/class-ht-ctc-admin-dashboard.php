<?php
/**
 * Admin Dashboard View (Layout)
 *
 * @package Click_To_Chat
 * @subpackage Administration
 * @since 4.41
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'HT_CTC_Admin_Dashboard' ) ) {

	/**
	 * Admin Dashboard Class.
	 *
	 * Coordinates the Admin 2026 workspace layout: loads the left navigation,
	 * the settings panels and the right sidebar.
	 */
	class HT_CTC_Admin_Dashboard {

		/**
		 * Display the admin dashboard layout.
		 */
		public static function display() {

			// Left Sidebar (Navigation)
			HT_CTC_Utils::load_file( 'new/admin2/views/class-ht-ctc-admin-nav.php' );
			if ( class_exists( 'HT_CTC_Admin_Nav' ) ) {
				HT_CTC_Admin_Nav::display();
			}

			// Main Content (Settings Panels)
			HT_CTC_Utils::load_file( 'new/admin2/views/class-ht-ctc-admin-panels.php' );
			if ( class_exists( 'HT_CTC_Admin_Panels' ) ) {
				HT_CTC_Admin_Panels::display();
			}

			// Right Sidebar (Support, Feedback, Preview)
			HT_CTC_Utils::load_file( 'new/admin2/views/class-ht-ctc-admin-right-sidebar.php' );
			if ( class_exists( 'HT_CTC_Admin_Right_Sidebar' ) ) {
				HT_CTC_Admin_Right_Sidebar::display();
			}
		}
	}
}
