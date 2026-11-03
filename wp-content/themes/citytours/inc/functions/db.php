<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/*
 * Create More Tables
 */
if ( ! function_exists( 'ct_create_extra_tables' ) ) {
	function ct_create_extra_tables() {
		global $wpdb;
		$installed_db_ver = get_option( "ct_db_version" );

		if ( $installed_db_ver != CT_DB_VERSION ) {
			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
			$sql = "CREATE TABLE " . CT_HOTEL_VACANCIES_TABLE . " (
						id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
						date_from date DEFAULT '0000-00-00',
						date_to date DEFAULT '9999-12-31',
						hotel_id bigint(20) unsigned DEFAULT NULL,
						room_type_id bigint(20) unsigned DEFAULT NULL,
						rooms tinyint(11) unsigned DEFAULT NULL,
						price_per_room decimal(16,2) DEFAULT '0.00',
						price_per_person decimal(16,2) DEFAULT '0.00',
						price_per_child decimal(16,2) DEFAULT '0.00',
						other text,
						PRIMARY KEY  (id)
					) DEFAULT CHARSET=utf8;";
			dbDelta($sql);

			$sql = "CREATE TABLE " . CT_HOTEL_BOOKINGS_TABLE . " (
						id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
						hotel_id bigint(20) unsigned DEFAULT NULL,
						room_type_id bigint(20) unsigned DEFAULT NULL,
						rooms tinyint(1) unsigned DEFAULT '0',
						adults tinyint(1) unsigned DEFAULT '0',
						kids tinyint(1) unsigned DEFAULT '0',
						room_price decimal(16,2) DEFAULT '0.00',
						tax decimal(16,2) DEFAULT '0.00',
						total_price decimal(16,2) DEFAULT '0.00',
						order_id bigint(20) DEFAULT NULL,
						PRIMARY KEY  (id)
					) DEFAULT CHARSET=utf8;";
			dbDelta($sql);

			$sql = "CREATE TABLE " . CT_TOUR_SCHEDULES_TABLE . " (
						id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
						tour_id bigint(20) NOT NULL,
						ts_id bigint(20) DEFAULT NULL,
						`from` date DEFAULT NULL,
						`to` date DEFAULT NULL,
						PRIMARY KEY  (id)
					) DEFAULT CHARSET=utf8;";
			dbDelta($sql);

			$sql = "CREATE TABLE " . CT_TOUR_SCHEDULE_META_TABLE . " (
						id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
						schedule_id bigint(20) DEFAULT NULL,
						day tinyint(1) DEFAULT NULL,
						is_closed tinyint(1) DEFAULT NULL,
						open_time varchar(255) DEFAULT NULL,
						close_time varchar(255) DEFAULT NULL,
						PRIMARY KEY  (id)
					) DEFAULT CHARSET=utf8;";
			dbDelta($sql);

			$sql = "CREATE TABLE " . CT_TOUR_BOOKINGS_TABLE . " (
						id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
						tour_id bigint(20) unsigned DEFAULT NULL,
						st_id tinyint(1) DEFAULT '0',
						tour_date date DEFAULT '0000-00-00',
						adults tinyint(1) unsigned DEFAULT '0',
						kids tinyint(1) unsigned DEFAULT '0',
						total_price decimal(16,2) DEFAULT '0.00',
						order_id bigint(20) unsigned DEFAULT NULL,
						PRIMARY KEY  (id)
					) DEFAULT CHARSET=utf8;";
			dbDelta($sql);

			$sql = "CREATE TABLE " . CT_ADD_SERVICES_TABLE . " (
						id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
						title varchar(255) DEFAULT NULL,
						price bigint(20) DEFAULT NULL,
						per_person tinyint(1) DEFAULT '0',
						inc_child tinyint(1) DEFAULT '0',
						icon_class varchar(255) DEFAULT NULL,
						post_id bigint(20) DEFAULT NULL,
						PRIMARY KEY  (id)
					) DEFAULT CHARSET=utf8;";
			dbDelta($sql);

			$sql = "CREATE TABLE " . CT_ADD_SERVICES_BOOKINGS_TABLE . " (
						id bigint(20) NOT NULL AUTO_INCREMENT,
						order_id bigint(20) DEFAULT NULL,
						add_service_id bigint(20) DEFAULT NULL,
						qty bigint(20) DEFAULT '0',
						total_price decimal(16,2) DEFAULT NULL,
						PRIMARY KEY  (id)
					) DEFAULT CHARSET=utf8;";
			dbDelta($sql);

			$sql = "CREATE TABLE " . CT_REVIEWS_TABLE . " (
						id int(11) NOT NULL AUTO_INCREMENT,
						date datetime NOT NULL,
						reviewer_name varchar(150) DEFAULT NULL,
						reviewer_email varchar(150) DEFAULT NULL,
						reviewer_ip varchar(15) DEFAULT NULL,
						review_text text,
						review_rating decimal(2,1) DEFAULT '0.0',
						review_rating_detail varchar(150) DEFAULT '0',
						post_id int(11) NOT NULL DEFAULT '0',
						status varchar(20) DEFAULT '0',
						other text,
						booking_no int(9) DEFAULT NULL,
						pin_code int(5) DEFAULT NULL,
						user_id bigint(20) unsigned DEFAULT NULL,
						PRIMARY KEY  (id)
					) DEFAULT CHARSET=utf8;";
			dbDelta($sql);

			$sql = "CREATE TABLE " . CT_CURRENCIES_TABLE . " (
						id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
						currency_code varchar(10) NOT NULL,
						currency_label varchar(255) NOT NULL,
						currency_symbol varchar(10) DEFAULT NULL,
						exchange_rate decimal(16,8) DEFAULT '1',
						other text,
						PRIMARY KEY  (id)
					) DEFAULT CHARSET=utf8;";
			dbDelta($sql);

			$sql = "CREATE TABLE " . CT_ORDER_TABLE . " (
						id bigint(20) NOT NULL AUTO_INCREMENT,
						first_name varchar(255) DEFAULT NULL,
						last_name varchar(255) DEFAULT NULL,
						email varchar(255) DEFAULT NULL,
						phone varchar(255) DEFAULT NULL,
						country varchar(255) DEFAULT NULL,
						address1 varchar(255) DEFAULT NULL,
						address2 varchar(255) DEFAULT NULL,
						city varchar(255) DEFAULT NULL,
						state varchar(255) DEFAULT NULL,
						zip varchar(255) DEFAULT NULL,
						special_requirements text CHARACTER SET latin1,
						total_price decimal(16,2) DEFAULT '0.00',
						total_adults int(5) DEFAULT '0',
						total_kids int(5) DEFAULT '0',
						date_from date DEFAULT NULL,
						date_to date DEFAULT NULL,
						post_id bigint(20) DEFAULT NULL,
						booking_no bigint(20) DEFAULT NULL,
						pin_code int(5) DEFAULT NULL,
						status varchar(20) DEFAULT 'new',
						deposit_paid tinyint(1) DEFAULT '0',
						deposit_price decimal(16,2) DEFAULT '0.00',
						currency_code varchar(8) DEFAULT NULL,
						exchange_rate decimal(16,8) DEFAULT NULL,
						other text CHARACTER SET latin1,
						created datetime DEFAULT NULL,
						mail_sent tinyint(1) DEFAULT '0',
						updated datetime DEFAULT NULL,
						post_type varchar(20) DEFAULT NULL,
						PRIMARY KEY  (id)
					) DEFAULT CHARSET=utf8;";
			dbDelta($sql);

			update_option( "ct_db_version", CT_DB_VERSION );
		}

		$installed_theme_ver = get_option( "ct_theme_version" );
		if ( $installed_theme_ver != CT_VERSION ) {
			update_option( "ct_theme_version", CT_VERSION );
		}
	}
}

add_action("after_switch_theme", "ct_create_extra_tables");
?>