<?php
// if (!defined('ABSPATH'))

if (!function_exists('ct_hotel_ajax_search'))
{
    function ct_hotel_ajax_search()
    {
        global $wpdb;
        $district = ( isset($_POST['districts']) ) ? ( is_array($_POST['districts']) ? $_POST['districts'] : array($_POST['districts']) ) : array();
        $country = ( isset($_POST['country']) ) ? ( is_array($_POST['country']) ? $_POST['country'] : $_POST['country'] ) : array();
        
        if (empty($district[0]) && !empty($country))
        {
            $district[0] = $country;
        }

        $sql = "SELECT DISTINCT posts.post_title FROM " . $wpdb->posts . " posts ";
        $where = " WHERE posts.post_type='hotel' AND posts.post_status ='publish' AND posts.post_title LIKE '%" . stripslashes($_POST['search']) . "%'";

        // district filter
        if (!empty($district) && trim(implode('', $district)) != "")
        {
            $sql .= " INNER JOIN " . $wpdb->prefix . "term_relationships AS tr ON tr.object_id = posts.ID
                    INNER JOIN " . $wpdb->prefix . "term_taxonomy AS tt ON tt.term_taxonomy_id = tr.term_taxonomy_id";
            $where .= " AND tt.taxonomy = 'district' AND tt.term_id IN (" . esc_sql(implode(',', $district)) . ") OR tt.parent IN (" . esc_sql(implode(',', $district)) . ")";
        }

        $sql = $sql . $where . ' limit 20';

        $results = $wpdb->get_results($sql);

        $items = array();

        if (!empty($results))
        {
            foreach ($results as $result)
            {
                $items[] = $result->post_title;
            }
        }

        wp_send_json_success($items);
    }
}

if (!function_exists('ct_get_hotel_cart_page'))
{

    function ct_get_hotel_cart_page()
    {
        global $ct_options;
        if (!empty($ct_options['hotel_cart_page']))
        {
            return ct_get_permalink_clang($ct_options['hotel_cart_page']);
        }
        return false;
    }

}

if (!function_exists('ct_get_hotel_checkout_page'))
{

    function ct_get_hotel_checkout_page()
    {
        global $ct_options;
        if (!empty($ct_options['hotel_checkout_page']))
        {
            return ct_get_permalink_clang($ct_options['hotel_checkout_page']);
        }
        return false;
    }

}
if (!function_exists('ct_get_hotel_thankyou_page'))
{

    function ct_get_hotel_thankyou_page()
    {
        global $ct_options;
        if (!empty($ct_options['hotel_thankyou_page']))
        {
            return ct_get_permalink_clang($ct_options['hotel_thankyou_page']);
        }
        return false;
    }

}

/*
 * Return matched hotels to given data. It is used for check availability function
 */
if (!function_exists('ct_hotel_get_available_rooms'))
{

    function ct_hotel_get_available_rooms($hotel_id, $from_date, $to_date, $adults = 1, $kids = 0)
    {

        // validation
        if (empty($hotel_id) || 'hotel' != get_post_type($hotel_id))
            return esc_html__('Invalide Hotel ID.', 'citytours'); //invalid data
        $hotel_id = esc_sql(ct_hotel_org_id($hotel_id));
        if (!ct_strtotime($from_date) || !ct_strtotime($to_date) || ( ct_strtotime($from_date) >= ct_strtotime($to_date) ))
        {
            return esc_html__('Invalid date. Please check your booking date again.', 'citytours'); //invalid data
        }

        $minimum_stay = get_post_meta($hotel_id, '_hotel_minimum_stay', true);
        if (!empty($minimum_stay) && ( ct_strtotime($from_date . ' + ' . $minimum_stay . ' days') > ct_strtotime($to_date) ))
        {
            return sprintf(esc_html__('Minimum stay for this hotel is %d nights. Have another look at your dates and try again.', 'citytours'), $minimum_stay);
        }

        // initiate variables
        global $wpdb;

        $sql = "SELECT pm0.post_id FROM " . $wpdb->postmeta . " as pm0 INNER JOIN " . $wpdb->posts . " AS room ON (pm0.post_id = room.ID) AND (room.post_status = 'publish') AND (room.post_type = 'room_type') WHERE meta_key = '_room_hotel_id' AND meta_value = " . esc_sql($hotel_id);
        $bookable_room_ids = $wpdb->get_col($sql);
        if (empty($bookable_room_ids))
        {
            if (is_user_logged_in())
            {
                return esc_html__('This hotel does not have any rooms. Please create and set rooms in admin panel.', 'citytours');
            }
            else
            {
                return esc_html__('No Rooms Available In This Hotel.', 'citytours');
            }
        }

        if(!empty($from_date))
            $from_date_obj = new DateTime('@'.ct_strtotime($from_date));
        else
            $from_date_obj = new DateTime(ct_strtotime($from_date));

        if(!empty($to_date))
            $to_date_obj = new DateTime('@'.ct_strtotime($to_date));
        else
            $to_date_obj = new DateTime(ct_strtotime($to_date));

        $date_interval = DateInterval::createFromDateString('1 day');
        $period = new DatePeriod($from_date_obj, $date_interval, $to_date_obj);
        $sql_check_date_parts = array();
        foreach ($period as $dt)
        {
            $sql_check_date_parts[] = "SELECT '" . esc_sql($dt->format("Y-m-d")) . "' AS check_date";
        }
        $days = count($sql_check_date_parts);
        if ($days < 1)
        {
            return esc_html__('Invalid date. Please check your booking date again.', 'citytours');
        }

        $room_ids = array();
        foreach ($bookable_room_ids as $room_id)
        {
            $room_id = (int) $room_id;
            if ($room_id > 0)
            {
                $room_ids[] = $room_id;
            }
        }
        $room_ids = array_values(array_unique($room_ids));
        if (empty($room_ids))
        {
            return esc_html__('No Rooms Available In This Hotel.', 'citytours');
        }

        $stay_from = esc_sql($from_date_obj->format('Y-m-d'));
        $stay_to = esc_sql($to_date_obj->format('Y-m-d'));
        $sql_check_date = implode(' UNION ', $sql_check_date_parts);
        $sql = "SELECT daily.room_type_id, MIN(daily.available_rooms) AS available_rooms
				FROM (
					SELECT vacancies.id, vacancies.room_type_id, check_dates.check_date,
						vacancies.rooms - IFNULL(SUM(bookings.rooms), 0) AS available_rooms
					FROM ({$sql_check_date}) AS check_dates
					INNER JOIN " . CT_HOTEL_VACANCIES_TABLE . " AS vacancies
						ON vacancies.hotel_id = '" . $hotel_id . "'
						AND vacancies.room_type_id IN (" . implode(',', $room_ids) . ")
						AND vacancies.date_from <= check_dates.check_date
						AND vacancies.date_to >= check_dates.check_date
					LEFT JOIN (
						SELECT hotel_booking.room_type_id, hotel_booking.rooms, hotel_order.date_from, hotel_order.date_to
						FROM " . CT_HOTEL_BOOKINGS_TABLE . " AS hotel_booking
						INNER JOIN " . CT_ORDER_TABLE . " AS hotel_order ON hotel_order.id = hotel_booking.order_id
						WHERE hotel_order.status != 'cancelled'
							AND hotel_order.post_id = '" . $hotel_id . "'
							AND hotel_order.date_from < '" . $stay_to . "'
							AND hotel_order.date_to > '" . $stay_from . "'
					) AS bookings
						ON bookings.room_type_id = vacancies.room_type_id
						AND bookings.date_from <= check_dates.check_date
						AND bookings.date_to > check_dates.check_date
					GROUP BY vacancies.id, vacancies.room_type_id, check_dates.check_date, vacancies.rooms
					HAVING available_rooms >= 1
				) AS daily
				GROUP BY daily.room_type_id
				HAVING COUNT(DISTINCT daily.check_date) = " . (int) $days;

        $results = $wpdb->get_results($sql);
        if (empty($results))
        {
            return esc_html__('No Rooms Available. Please have another look at booking date.', 'citytours');
        }

        $return_value = array();
        foreach ($results as $result)
        {
            $return_value[$result->room_type_id] = $result->available_rooms;
        }
        return $return_value;
    }

}

/*
 * Calculate the price of selected hotel room and return price array data
 */
if (!function_exists('ct_hotel_calc_room_price'))
{

    function ct_hotel_calc_room_price($hotel_id, $room_type_id, $from_date, $to_date, $rooms, $adults, $kids, $kidsAge = array())
    {
        global $wpdb;

        $hotel_id = ct_hotel_org_id($hotel_id);
        $room_type_id = ct_room_org_id($room_type_id);
        //validation
        $room_hotel_id = get_post_meta($room_type_id, '_room_hotel_id', true);
        if ($room_hotel_id != $hotel_id)
            return esc_html__('Room Type Id is not matched.', 'citytours');

        $max_adults = (int) get_post_meta($room_type_id, '_room_max_adults', true);
        $max_kids = (int) get_post_meta($room_type_id, '_room_max_kids', true);
        $avg_adults = ceil($adults / $rooms);
        $avg_kids = ceil($kids / $rooms);

        if (( $avg_adults > $max_adults ) || ( ( $avg_adults + $avg_kids ) > ( $max_adults + $max_kids ) ))
            return esc_html__('Exceeds Max Guests.', 'citytours');

        if (( time() - ( 60 * 60 * 24 ) ) > ct_strtotime($from_date))
            return esc_html__('Wrong Check In date. Please check again.', 'citytours');
        $minimum_stay = get_post_meta($hotel_id, '_hotel_minimum_stay', true);
        $minimum_stay = is_numeric($minimum_stay) ? $minimum_stay : 0;
        if (!ct_strtotime($from_date) || !ct_strtotime($to_date) || ( ct_strtotime($from_date) >= ct_strtotime($to_date) ) || ( ct_strtotime($from_date . ' + ' . $minimum_stay . ' days') > ct_strtotime($to_date) ))
            return esc_html__('Wrong Booking Date. Please check again.', 'citytours');

        if(!empty($from_date))
            $from_date_obj = new DateTime('@'.ct_strtotime($from_date));
        else
            $from_date_obj = new DateTime(ct_strtotime($from_date));

        if(!empty($to_date))
            $to_date_obj = new DateTime('@'.ct_strtotime($to_date));
        else
            $to_date_obj = new DateTime(ct_strtotime($to_date));

        $date_interval = DateInterval::createFromDateString('1 day');
        // $endDateInt = new DateInterval( "P1D" );
        // $to_date_obj->add( $endDateInt );
        $period = new DatePeriod($from_date_obj, $date_interval, $to_date_obj);

        $price_data = array();
        $total_price = 0;
        $check_dates = array();

        $hotel_id = esc_sql($hotel_id);
        $room_type_id = esc_sql($room_type_id);
        $adults = esc_sql($adults);
        $kids = esc_sql($kids);
        $rooms_needed = (int) $rooms;

        $night_dates = array();
        foreach ($period as $dt)
        {
            $night_dates[] = $dt->format("Y-m-d");
        }
        $sql_check_date_parts = array();
        foreach ($night_dates as $night_date)
        {
            $sql_check_date_parts[] = "SELECT '" . esc_sql($night_date) . "' AS check_date";
        }
        if (empty($sql_check_date_parts))
        {
            return esc_html__('Wrong Booking Date. Please check again.', 'citytours');
        }
        $stay_from = esc_sql($from_date_obj->format('Y-m-d'));
        $stay_to = esc_sql($to_date_obj->format('Y-m-d'));
        $except_sql = ( empty($except_booking_no) || empty($pin_code) ) ? "" : ( " AND NOT ( hotel_order.booking_no = '" . $except_booking_no . "' AND hotel_order.pin_code = '" . $pin_code . "' )" );
        $sql = "SELECT check_dates.check_date, vacancies.price_per_room, vacancies.price_per_person, vacancies.price_per_child, vacancies.price_per_child2, vacancies.active, vacancies.promotion
				FROM (" . implode(' UNION ', $sql_check_date_parts) . ") AS check_dates
				INNER JOIN " . CT_HOTEL_VACANCIES_TABLE . " AS vacancies
					ON vacancies.hotel_id = '" . $hotel_id . "'
					AND vacancies.room_type_id = '" . $room_type_id . "'
					AND vacancies.date_from <= check_dates.check_date
					AND vacancies.date_to >= check_dates.check_date
					AND vacancies.active = 1
				LEFT JOIN (
					SELECT hotel_booking.rooms, hotel_order.date_from, hotel_order.date_to
					FROM " . CT_HOTEL_BOOKINGS_TABLE . " AS hotel_booking
					INNER JOIN " . CT_ORDER_TABLE . " AS hotel_order ON hotel_order.id = hotel_booking.order_id
					WHERE hotel_order.status != 'cancelled'
						AND hotel_order.post_id = '" . $hotel_id . "'
						AND hotel_booking.room_type_id = '" . $room_type_id . "'
						AND hotel_order.date_from < '" . $stay_to . "'
						AND hotel_order.date_to > '" . $stay_from . "'" . $except_sql . "
				) AS bookings
					ON bookings.date_from <= check_dates.check_date
					AND bookings.date_to > check_dates.check_date
				GROUP BY vacancies.id, check_dates.check_date, vacancies.rooms, vacancies.price_per_room, vacancies.price_per_person, vacancies.price_per_child, vacancies.price_per_child2, vacancies.active, vacancies.promotion
				HAVING vacancies.rooms - IFNULL(SUM(bookings.rooms), 0) >= " . $rooms_needed;
        $rows_by_date = array();
        foreach ($wpdb->get_results($sql) as $row)
        {
            $rows_by_date[$row->check_date][] = $row;
        }

        foreach ($night_dates as $check_date)
        {
            $check_date = esc_sql($check_date);
            $check_dates[] = $check_date;
            $result = isset($rows_by_date[$check_date]) ? $rows_by_date[$check_date] : array();
//   echo "<pre>";
//                     print_r($result);exit;
            if (empty($result))
            { //if no available rooms on selected date
                return esc_html__('Sorry, The room you are booking now is just taken by another customer. Please have another look.', 'citytours');
            }
            else
            {

                if (is_array($result))
                {
                    $price_per_room = 0;
                    $promotion = 0;
                    foreach ($result as $row)
                    {
                        if ($row->promotion == 1)
                        {
                            $result = $row;
                            $promotion = $row->promotion;
                            $price_per_room = $row->price_per_room;
                        }
                        elseif ($row->price_per_room > $price_per_room && $promotion == 0)
                        {
                            $result = $row;
                            $price_per_room = $row->price_per_room;
                        }
                    }
                }

                $price_per_room = (float) $result->price_per_room;
                $price_per_person = (float) $result->price_per_person;
                $price_per_child = json_decode($result->price_per_child, true);
                $price_per_child2 = json_decode($result->price_per_child2, true);
                


                $kid1 = $kidsAge[0] ?? [];
                $kid2 = $kidsAge[1] ?? [];
                
                // kid 1
                $kid1Price = 0;
                if ($kid1 && $price_per_child) {
                    $kid1Price = $price_per_room;  // Default price if none of the age ranges match
                    
                    foreach ($price_per_child as $ageRange) {
                        if ($kid1 >= $ageRange['from'] && $kid1 <= $ageRange['to']) {
                            $kid1Price = $ageRange['price'];
                            break;  // Break out of the loop as we found a matching age range
                        }
                    }
                }
                
                $kid2Price = 0;
                if ($kid2 && $price_per_child2) {
                    $kid2Price = $price_per_room;  // Default price if none of the age ranges match
                    
                    foreach ($price_per_child2 as $ageRange) {
                        if ($kid2 >= $ageRange['from'] && $kid2 <= $ageRange['to']) {
                            $kid2Price = $ageRange['price'];
                            break;  // Break out of the loop as we found a matching age range
                        }
                    }
                }

                $kid1Price= intval($kid1Price);
                $kid2Price= intval($kid2Price);
                $day_price = $price_per_room +  ($kid1Price + $kid2Price);

                $price_data[$check_date] = array(
                    'ppr' => $price_per_room,
                    'ppp' => $price_per_person,
                    'ppc' => $kid1Price + $kid2Price,
                    'total' => $day_price
                );

                $total_price += $day_price;
            }
        }
        
        // print_r($price_data);exit;

        $return_value = array(
            'check_dates' => $check_dates,
            'prices' => $price_data,
            'total_price' => $total_price
        );

        return $return_value;
    }

}

/*
 * send booking confirmation email function
 */
if (!function_exists('ct_hotel_generate_conf_mail'))
{

    function ct_hotel_generate_conf_mail($order, $type = 'new')
    {
        global $wpdb, $ct_options;
        
        $order_data = $order->get_order_info();
        if (!empty($order_data))
        {
            // server variables
            $admin_email = get_option('admin_email');
            $inquiry_email = $ct_options['inquiry_email'];
            $home_url = esc_url(home_url('/'));
            $site_name = $_SERVER['SERVER_NAME'];
            $logo_url = esc_url(ct_logo_url());
            $order_data['hotel_id'] = ct_hotel_clang_id($order_data['post_id']);

            // hotel info
            $hotel_name = get_the_title($order_data['hotel_id']);
            $hotel_url = esc_url(ct_get_permalink_clang($order_data['hotel_id']));
            $hotel_thumbnail = get_the_post_thumbnail($order_data['hotel_id'], 'medium');
            $hotel_address = get_post_meta($order_data['hotel_id'], '_hotel_address', true);
            $hotel_email = get_post_meta($order_data['hotel_id'], '_hotel_email', true);
            $hotel_phone = get_post_meta($order_data['hotel_id'], '_hotel_phone', true);

            // room info
            $booking_rooms = '<table><tbody><tr><th>' . esc_html__('Room Name', 'citytours') . '</th><th>' . esc_html__('Rooms', 'citytours') . '</th><th>' . esc_html__('Adults', 'citytours') . '</th><th>' . esc_html__('Kids', 'citytours') . '</th><th>' . esc_html__('Total Price', 'citytours') . '</th></tr>';
            $rooms_booking_data = $order->get_rooms();
            if (!empty($rooms_booking_data))
            {
                foreach ($rooms_booking_data as $key => $room_data)
                {
                    $room_type_id = ct_room_clang_id($room_data['room_type_id']);
                    $room_type_title = esc_html(get_the_title($room_type_id));
                    $booking_rooms .= '<tr><td>' . $room_type_title . '</td><td>' . $room_data['rooms'] . '</td><td>' . $room_data['adults'] . '</td><td>' . $room_data['kids'] . '</td><td>' . $room_data['total_price'] . '</td></tr>';
                }
            }
            $booking_rooms .= '</tbody></table>';

            // services info
            $booking_services = '<table><tbody><tr><th>' . esc_html__('Service Name', 'citytours') . '</th><th>' . esc_html__('Quantity', 'citytours') . '</th><th>' . esc_html__('Total Price', 'citytours') . '</th></tr>';
            $services_booking_data = $order->get_services();
            if (!empty($services_booking_data))
            {
                foreach ($services_booking_data as $key => $service_booking_data)
                {
                    $service_data = ct_get_add_service($service_booking_data['add_service_id']);
                    $service_quantity = $service_booking_data['qty'];
                    $service_total_price = $service_booking_data['total_price'];
                    $booking_services .= '<tr><td>' . $service_data->title . '</td><td>' . $service_quantity . '</td><td>' . $service_total_price . '</td></tr>';
                }
            }
            $booking_services .= '</tbody></table>';

            // booking info
            $date_from = new DateTime($order_data['date_from']);
            $date_to = new DateTime($order_data['date_to']);
            $number1 = $date_from->format('U');
            $number2 = $date_to->format('U');
            $booking_nights = ($number2 - $number1) / (3600 * 24);
            $booking_from_date = date_i18n('j F Y', strtotime($order_data['date_from']));
            $booking_to_date = date_i18n('j F Y', strtotime($order_data['date_to']));
            $booking_adults = $order_data['total_adults'];
            $booking_kids = $order_data['total_kids'];
            $booking_total_price = esc_html(ct_price($order_data['total_price'] * $order_data['exchange_rate'], "", $order_data['currency_code'], 0));
            $booking_deposit_price = esc_html($order_data['deposit_price'] . $order_data['currency_code']);
            $booking_deposit_paid = esc_html(empty($order_data['deposit_paid']) ? 'No' : 'Yes');
            $booking_no = $order_data['booking_no'];
            $booking_pincode = $order_data['pin_code'];

            // customer info
            $customer_first_name = $order_data['first_name'];
            $customer_last_name = $order_data['last_name'];
            $customer_email = $order_data['email'];
//            $customer_country_code = $order_data['country'];
            $customer_phone = $order_data['phone'];
//            $customer_address1 = $order_data['address1'];
//            $customer_address2 = $order_data['address2'];
//            $customer_city = $order_data['city'];
//            $customer_zip = $order_data['zip'];
//            $customer_country = $order_data['country'];
            $customer_special_requirements = $order_data['special_requirements'];

            $variables = array(
                'home_url',
                'site_name',
                'logo_url',
                'hotel_name',
                'hotel_url',
                'hotel_thumbnail',
                'hotel_address',
                'hotel_email',
                'hotel_phone',
                'booking_rooms',
                'booking_services',
                'booking_no',
                'booking_pincode',
                'booking_from_date',
                'booking_to_date',
                'booking_nights',
                'booking_adults',
                'booking_kids',
                'booking_total_price',
                'booking_deposit_paid',
                'booking_deposit_price',
                'customer_first_name',
                'customer_last_name',
                'customer_email',
                'customer_country_code',
                'customer_phone',
                'customer_address1',
                'customer_address2',
                'customer_city',
                'customer_zip',
                'customer_country',
                'customer_special_requirements',
            );

            if (empty($subject))
            {
                $subject = empty($ct_options['hotel_confirm_email_subject']) ? 'Booking Confirmation Email Subject' : $ct_options['hotel_confirm_email_subject'];
            }

            if (empty($description))
            {
                $description = empty($ct_options['hotel_confirm_email_description']) ? 'Booking Confirmation Email Description' : $ct_options['hotel_confirm_email_description'];
            }

            foreach ($variables as $variable)
            {
                $subject = str_replace("[" . $variable . "]", $$variable, $subject);
                $description = str_replace("[" . $variable . "]", $$variable, $description);
            }

            $mail_sent = ct_send_mail($site_name, $admin_email, $customer_email, $subject, $description);

            /* mailing function to admin */
            if (!empty($ct_options['hotel_booked_notify_admin']))
            {
                $subject = empty($ct_options['hotel_admin_email_subject']) ? 'You received a booking' : $ct_options['hotel_admin_email_subject'];
                $description = empty($ct_options['hotel_admin_email_description']) ? 'Booking Details' : $ct_options['hotel_admin_email_description'];

                foreach ($variables as $variable)
                {
                    $subject = str_replace("[" . $variable . "]", $$variable, $subject);
                    $description = str_replace("[" . $variable . "]", $$variable, $description);
                }

                ct_send_mail($site_name, $inquiry_email, $inquiry_email, $subject, $description);
                ct_send_mail($site_name, $inquiry_email, $admin_email, $subject, $description);
            }
            return true;
        }
        return false;
    }

}


if (!function_exists('ct_hotel_get_search_result_count'))
{
    function ct_hotel_get_search_result_count($args)
    {
        global $ct_options, $wpdb;
        $district = array();
        $price_filter = array();
        $star_filter = array();
        $rating_filter = array();
        $facility_filter = array();
        extract($args);
        $tbl_posts = esc_sql($wpdb->posts);
        $tbl_postmeta = esc_sql($wpdb->postmeta);
        $tbl_terms = esc_sql($wpdb->prefix . 'terms');
        $tbl_term_taxonomy = esc_sql($wpdb->prefix . 'term_taxonomy');
        $tbl_term_relationships = esc_sql($wpdb->prefix . 'term_relationships');
        $temp_tbl_name = ct_get_temp_table_name();
        $result = array();

        if ($by == 'star')
        {

            $sql = "SELECT IFNULL( meta_star.meta_value, 0 ) as star, COUNT(*) AS counts FROM {$temp_tbl_name} as t1
					INNER JOIN {$tbl_posts} post_s1 ON (t1.hotel_id = post_s1.ID) AND (post_s1.post_status = 'publish') AND (post_s1.post_type = 'hotel')
					LEFT JOIN {$tbl_postmeta} AS meta_star ON t1.hotel_id = meta_star.post_id AND meta_star.meta_key = '_hotel_star'";
            $where = ' 1=1';
            // district filter
            if (!empty($district) && trim(implode('', $district)) != "")
            {
                $sql .= " INNER JOIN {$tbl_term_relationships} AS tr ON tr.object_id = t1.hotel_id
						INNER JOIN {$tbl_term_taxonomy} AS tt ON tt.term_taxonomy_id = tr.term_taxonomy_id";
                $where .= " AND tt.taxonomy = 'district' AND (tt.term_id IN (" . esc_sql(implode(',', $district)) . ") OR tt.parent IN (" . esc_sql(implode(',', $district)) . "))";
            }
            
            // price filter
            if (!empty($price_filter) && trim(implode('', $price_filter)) != "")
            {
                $sql .= " LEFT JOIN {$tbl_postmeta} AS meta_price ON t1.hotel_id = meta_price.post_id AND meta_price.meta_key = '_hotel_price'";
                $price_where = array();
                $price_steps = empty($ct_options['hotel_price_filter_steps']) ? '50,80,100' : $ct_options['hotel_price_filter_steps'];
                $step_arr = explode(',', $price_steps);
                array_unshift($step_arr, 0);
                foreach ($price_filter as $index)
                {
                    if ($index < count($step_arr) - 1)
                    {
                        // 80 ~ 100 case
                        $price_where[] = "( cast(meta_price.meta_value as unsigned) BETWEEN " . esc_sql($step_arr[$index]) . " AND " . esc_sql($step_arr[$index + 1]) . " )";
                    }
                    else
                    {
                        // 200+ case
                        $price_where[] = "( cast(meta_price.meta_value as unsigned) >= " . esc_sql($step_arr[$index]) . " )";
                    }
                }
                $where .= " AND ( " . implode(' OR ', $price_where) . " )";
            }

            // hotel star filter
            // review filter
            if (!empty($rating_filter) && trim(implode('', $rating_filter)) != "")
            {
                $sql .= " LEFT JOIN {$tbl_postmeta} AS meta_rating ON t1.hotel_id = meta_rating.post_id AND meta_rating.meta_key = '_review'";
                $where .= " AND round( cast( IFNULL( meta_rating.meta_value, 0 ) AS decimal(2,1) ) ) IN ( " . esc_sql(implode(',', $rating_filter)) . " )";
            }

            // facility filter
            if (!empty($facility_filter) && trim(implode('', $facility_filter)) != "")
            {
                $where .= " AND (( SELECT COUNT(1) FROM {$tbl_term_relationships} AS tr1
						INNER JOIN {$tbl_term_taxonomy} AS tt1 ON ( tr1.term_taxonomy_id= tt1.term_taxonomy_id )
						WHERE tt1.taxonomy = 'hotel_facility' AND tt1.term_id IN (" . esc_sql(implode(',', $facility_filter)) . ") AND tr1.object_id = t1.hotel_id ) = " . count($facility_filter) . ")";
            }

            $sql .= " WHERE {$where} GROUP BY meta_star.meta_value";

            $result = $wpdb->get_results($sql, ARRAY_A);
            $keys = array_map(function($a) {
                return $a['star'];
            }, $result);
            $values = array_map(function($a) {
                return $a['counts'];
            }, $result);
            $result = array_combine($keys, $values);
        }
        return $result;
    }

}

if (!function_exists('ct_hotel_get_search_result'))
{
    function ct_hotel_get_search_result($args)
    {
        global $ct_options, $wpdb;

        $s = '';
        $date_from = '';
        $date_to = '';
        $rooms = 1;
        $adults = 1;
        $kids = 0;
        $district = array();
        $price_filter = array();
        $star_filter = array();
        $rating_filter = array();
        $facility_filter = array();
        $order_by = '';
        $order = '';
        $last_no = 0;
        $per_page = ( isset($ct_options['hotel_posts']) && is_numeric($ct_options['hotel_posts']) ) ? $ct_options['hotel_posts'] : 6;
        extract($args);

        $order_array = array('ASC', 'DESC');
        $order_by_array = array(
            '' => '',
            'price' => 'price',
            'rating' => 'meta_rating.meta_value'
        );

        if (!array_key_exists($order_by, $order_by_array))
            $order_by = '';
        if (!in_array($order, $order_array))
            $order = 'ASC';

        $tbl_posts = esc_sql($wpdb->posts);
        $tbl_postmeta = esc_sql($wpdb->postmeta);
        $tbl_terms = esc_sql($wpdb->prefix . 'terms');
        $tbl_term_taxonomy = esc_sql($wpdb->prefix . 'term_taxonomy');
        $tbl_term_relationships = esc_sql($wpdb->prefix . 'term_relationships');
//        $tbl_icl_translations = esc_sql($wpdb->prefix . 'icl_translations');

        $temp_tbl_name = ct_get_temp_table_name();
        $language_count = ct_get_lang_count();
        
        if(empty($date_from)) {
            $date_from = date('Y-m-d');
        }

        if(empty($date_to)) {
            $date_to = date("Y-m-d", strtotime($date_from . ' + 1day'));
        }
        
        $from_date_obj = date_create_from_format(ct_get_date_format('php'), $date_from);
        $to_date_obj = date_create_from_format(ct_get_date_format('php'), $date_to);

        $s_query = ''; // sql for search keyword
        $c_query = ''; // sql for conditions ( review, avg_price, user_rating )
        $v_query = ''; // sql for vacancy check

        $s_query = "SELECT post_s1.ID AS hotel_id FROM {$tbl_posts} AS post_s1
					WHERE (post_s1.post_status = 'publish') AND (post_s1.post_type = 'hotel')";
        
        if (!empty($s))
        {
            //mysql escape sting and like escape
            if (floatval(get_bloginfo('version')) >= 4.0)
            {
                $s = esc_sql($wpdb->esc_like($s));
            }
            else
            {
                $s = esc_sql(like_escape($s));
            }
            $s_query .= " AND ((post_s1.post_title LIKE '%{$s}%') OR (post_s1.post_content LIKE '%{$s}%') )";
        }

        $district_ids = array();
        if (!empty($district) && is_array($district)) {
            foreach ($district as $district_id) {
                $district_id = (int) $district_id;
                if ($district_id > 0) {
                    $district_ids[] = $district_id;
                }
            }
            $district_ids = array_values(array_unique($district_ids));
        }
        if ($district_ids) {
            $id_list = implode(',', $district_ids);
            $s_query .= " AND post_s1.ID IN (
				SELECT tr_d.object_id
				FROM {$tbl_term_relationships} AS tr_d
				INNER JOIN {$tbl_term_taxonomy} AS tt_d ON tt_d.term_taxonomy_id = tr_d.term_taxonomy_id
				WHERE tt_d.taxonomy = 'district'
				  AND (tt_d.term_id IN ({$id_list}) OR tt_d.parent IN ({$id_list}))
			)";
        }

        // if wpml is enabled do search by default language post
//        if (defined('ICL_LANGUAGE_CODE') && ( $language_count > 1 ))
//        {
//            $s_query = "SELECT DISTINCT it2.element_id AS hotel_id FROM ({$s_query}) AS t0
//						INNER JOIN {$tbl_icl_translations} it1 ON (it1.element_type = 'post_hotel') AND it1.element_id = t0.hotel_id
//						INNER JOIN {$tbl_icl_translations} it2 ON (it2.element_type = 'post_hotel') AND it2.language_code='" . ct_get_default_language() . "' AND it2.trid = it1.trid ";
//        }

        $c_query = "SELECT t1.*, meta_c1.post_id AS room_id, meta_c2.meta_value AS max_adults, meta_c3.meta_value AS max_kids, meta_c4.meta_value AS minimum_stay
					FROM ( {$s_query} ) AS t1
					INNER JOIN {$tbl_postmeta} AS meta_c1 ON (meta_c1.meta_key = '_room_hotel_id') AND (t1.hotel_id = meta_c1.meta_value)
					INNER JOIN {$tbl_postmeta} AS meta_c2 ON (meta_c1.post_id = meta_c2.post_id) AND (meta_c2.meta_key='_room_max_adults')
					LEFT JOIN {$tbl_postmeta} AS meta_c3 ON (meta_c1.post_id = meta_c3.post_id) AND (meta_c3.meta_key='_room_max_kids')
					LEFT JOIN {$tbl_postmeta} AS meta_c4 ON (t1.hotel_id = meta_c4.post_id) AND (meta_c4.meta_key='_hotel_minimum_stay')";

        // if this searh has specified date then check vacancy and booking data, but if it doesn't have specified date then only check other search factors
        if ($from_date_obj && $to_date_obj)
        {
            // has specified date
            $date_interval = DateInterval::createFromDateString('1 day');
            $period = new DatePeriod($from_date_obj, $date_interval, $to_date_obj);
            $sql_check_date_parts = array();
            $days = 0;
            foreach ($period as $dt)
            {
                $check_date = $dt->format("Y-m-d");
                $sql_check_date_parts[] = "SELECT '{$check_date}' AS check_date";
                $days++;
            }
            
            $sql_check_date = implode(' UNION ', $sql_check_date_parts);
            $stay_from = esc_sql($from_date_obj->format('Y-m-d'));
            $stay_to = esc_sql($to_date_obj->format('Y-m-d'));
            // and vacancies.date_from >= check_dates.check_date AND vacancies.date_to <= check_dates.check_date
            // GROUP BY t3.room_id, t3.hotel_id
            $v_query = "SELECT t3.hotel_id, t3.room_id, t3.max_adults, t3.max_kids, t3.minimum_stay, rooms, MIN(price) AS price FROM (
							SELECT t2.*, (IFNULL(vacancies.rooms,0) - IFNULL(SUM(bookings.rooms),0)) AS rooms, check_dates.check_date, (IFNULL(vacancies.price_per_room,0)) AS price, vacancies.active
							FROM ({$c_query}) AS t2
							JOIN ( {$sql_check_date} ) AS check_dates
							LEFT JOIN " . CT_HOTEL_VACANCIES_TABLE . " AS vacancies ON (vacancies.room_type_id = t2.room_id) AND (vacancies.date_from <= check_dates.check_date AND vacancies.date_to >= check_dates.check_date)
							LEFT JOIN ( SELECT hotel_booking.*, hotel_order.date_from, hotel_order.date_to FROM " . CT_HOTEL_BOOKINGS_TABLE . " AS hotel_booking INNER JOIN " . CT_ORDER_TABLE . " as hotel_order ON hotel_order.id = hotel_booking.order_id AND hotel_order.status!='cancelled' AND hotel_order.date_from < '{$stay_to}' AND hotel_order.date_to > '{$stay_from}' ) AS bookings ON (bookings.room_type_id = t2.room_id) AND (bookings.date_from <= check_dates.check_date AND bookings.date_to > check_dates.check_date)
                            GROUP BY t2.room_id, check_dates.check_date
						  ) AS t3
                            WHERE active = 1
						  GROUP BY t3.hotel_id";

            // if rooms == 1 do specific search and if rooms > 1 do overal search for vacancies
            if ($rooms == 1)
            {
                $sql = "SELECT t4.hotel_id, SUM(t4.rooms) AS rooms, price FROM ({$v_query}) AS t4
					WHERE ((t4.minimum_stay IS NULL) OR (t4.minimum_stay <= {$days}))
					  AND (t4.max_adults >= {$adults})
					  AND (t4.max_adults + IFNULL(t4.max_kids,0) >= {$adults} + {$kids})
					GROUP BY t4.hotel_id
					HAVING rooms >= {$rooms}";
            }
            else
            {
                $sql = "SELECT t4.hotel_id, SUM(t4.rooms) AS rooms, SUM(IFNULL(t4.max_adults,0) * t4.rooms) as hotel_max_adults, SUM(IFNULL(t4.max_kids,0) * t4.min_rooms) as hotel_max_kids, price FROM ({$v_query}) AS t4
					WHERE ((t4.minimum_stay IS NULL) OR (t4.minimum_stay <= {$days}))
					GROUP BY t4.hotel_id
					HAVING rooms >= {$rooms} AND hotel_max_adults >= {$adults} AND hotel_max_kids >= {$kids}";
            }
            

        }
        else
        {
            $days = 1;

            $v_query = "SELECT t3.hotel_id, t3.room_id, t3.max_adults, t3.max_kids, t3.minimum_stay, rooms, MIN(price) AS price FROM (
							SELECT t2.*, (IFNULL(vacancies.rooms,0) - IFNULL(SUM(bookings.rooms),0)) AS rooms, (IFNULL(vacancies.price_per_room,0)) AS price, vacancies.active
							FROM ({$c_query}) AS t2
							LEFT JOIN " . CT_HOTEL_VACANCIES_TABLE . " AS vacancies ON (vacancies.room_type_id = t2.room_id)
							LEFT JOIN ( SELECT hotel_booking.*, hotel_order.date_from, hotel_order.date_to FROM " . CT_HOTEL_BOOKINGS_TABLE . " AS hotel_booking INNER JOIN " . CT_ORDER_TABLE . " as hotel_order ON hotel_order.id = hotel_booking.order_id AND hotel_order.status!='cancelled' ) AS bookings ON (bookings.room_type_id = t2.room_id)
							WHERE active = 1 
                            GROUP BY t2.room_id, t2.hotel_id 
						  ) AS t3
						  GROUP BY t3.hotel_id ";

            $sql = "SELECT t4.hotel_id, rooms, price FROM ({$v_query}) AS t4
					WHERE (t4.max_adults >= {$adults})
					  AND (t4.max_adults + IFNULL(t4.max_kids,0) >= {$adults} + {$kids})
					GROUP BY t4.hotel_id";

        }

        // if wpml is enabled return current language posts
//        if (defined('ICL_LANGUAGE_CODE') && ( ct_get_lang_count() > 1 ) && ( ct_get_default_language() != ICL_LANGUAGE_CODE ))
//        {
//            $sql = "SELECT it4.element_id AS hotel_id FROM ({$sql}) AS t5
//					INNER JOIN {$tbl_icl_translations} it3 ON (it3.element_type = 'post_hotel') AND it3.element_id = t5.hotel_id
//					INNER JOIN {$tbl_icl_translations} it4 ON (it4.element_type = 'post_hotel') AND it4.language_code='" . ICL_LANGUAGE_CODE . "' AND it4.trid = it3.trid";
//        }

        if (!empty($order_by))
        {
            $sql .= " ORDER BY " . $order_by_array[$order_by] . " " . $order;
        }

        // die($sql) ;

        $wpdb->query("DROP TEMPORARY TABLE IF EXISTS {$temp_tbl_name}");
        $sql = "CREATE TEMPORARY TABLE {$temp_tbl_name} AS " . $sql;
       
        $wpdb->query($sql);

        $sql = " FROM {$temp_tbl_name} as t1
				INNER JOIN {$tbl_posts} post_s1 ON (t1.hotel_id = post_s1.ID) AND (post_s1.post_status = 'publish') AND (post_s1.post_type = 'hotel')";
        $where = ' 1=1';

        // district filter
        if (!empty($district) && trim(implode('', $district)) != "")
        {
            $sql .= " INNER JOIN {$tbl_term_relationships} AS tr ON tr.object_id = post_s1.ID
					INNER JOIN {$tbl_term_taxonomy} AS tt ON tt.term_taxonomy_id = tr.term_taxonomy_id";
            $where .= " AND tt.taxonomy = 'district' AND (tt.term_id IN (" . esc_sql(implode(',', $district)) . ") OR tt.parent IN (" . esc_sql(implode(',', $district)) . "))";
        }

        // price filter
        $sql .= " LEFT JOIN {$tbl_postmeta} AS meta_price ON post_s1.ID = meta_price.post_id AND meta_price.meta_key = '_hotel_price'";
        if (!empty($price_filter) && trim(implode('', $price_filter)) != "")
        {
            $price_where = array();
            $price_steps = empty($ct_options['hotel_price_filter_steps']) ? '50,80,100' : $ct_options['hotel_price_filter_steps'];
            $step_arr = explode(',', $price_steps);
            array_unshift($step_arr, 0);
            foreach ($price_filter as $index)
            {
                if ($index < count($step_arr) - 1)
                {
                    // 80 ~ 100 case
                    $price_where[] = "( cast(meta_price.meta_value as unsigned) BETWEEN " . esc_sql($step_arr[$index]) . " AND " . esc_sql($step_arr[$index + 1]) . " )";
                }
                else
                {
                    // 200+ case
                    $price_where[] = "( cast(meta_price.meta_value as unsigned) >= " . esc_sql($step_arr[$index]) . " )";
                }
            }
            $where .= " AND ( " . implode(' OR ', $price_where) . " )";
        }

        // hotel star filter
        if (!empty($star_filter) && trim(implode('', $star_filter)) != "")
        {
            $sql .= " LEFT JOIN {$tbl_postmeta} AS meta_star ON post_s1.ID = meta_star.post_id AND meta_star.meta_key = '_hotel_star'";
            $where .= " AND IFNULL( meta_star.meta_value, 0 ) IN ( " . esc_sql(implode(',', $star_filter)) . " )";
        }

        // review filter
        $sql .= " LEFT JOIN {$tbl_postmeta} AS meta_rating ON post_s1.ID = meta_rating.post_id AND meta_rating.meta_key = '_review'";
        if (!empty($rating_filter) && trim(implode('', $rating_filter)) != "")
        {
            $where .= " AND round( cast( IFNULL( meta_rating.meta_value, 0 ) AS decimal(2,1) ) ) IN ( " . esc_sql(implode(',', $rating_filter)) . " )";
        }

        // facility filter
        if (!empty($facility_filter) && trim(implode('', $facility_filter)) != "")
        {
            $where .= " AND (( SELECT COUNT(1) FROM {$tbl_term_relationships} AS tr1
					INNER JOIN {$tbl_term_taxonomy} AS tt1 ON ( tr1.term_taxonomy_id= tt1.term_taxonomy_id )
					WHERE tt1.taxonomy = 'hotel_facility' AND tt1.term_id IN (" . esc_sql(implode(',', $facility_filter)) . ") AND tr1.object_id = post_s1.ID ) = " . count($facility_filter) . ")";
        }
        
        $sql .= " WHERE {$where}";

        $count_sql = "SELECT COUNT(DISTINCT t1.hotel_id)" . $sql;
        $count = $wpdb->get_var($count_sql);

        $sql .= " LIMIT {$last_no}, {$per_page};";
        
        $main_sql = "SELECT DISTINCT t1.hotel_id AS hotel_id, price" . $sql;
        
        $ids = $wpdb->get_results($main_sql, ARRAY_A);

        return array('count' => $count, 'ids' => $ids);
    }

}

/*
 * Get hotels from ids
 */
if (!function_exists('ct_hotel_get_hotels_from_id'))
{

    function ct_hotel_get_hotels_from_id($ids)
    {
        if (!is_array($ids))
            return false;

        $results = array();
        foreach ($ids as $id)
        {
            $result = get_post($id);
            if (!empty($result) && !is_wp_error($result))
            {
                if ($result->post_type == 'hotel')
                    $results[] = $result;
            }
        }
        return $results;
    }

}


/*
 * Get special( latest or featured ) hotels and return data
 */
if (!function_exists('ct_hotel_get_special_hotels'))
{

    function ct_hotel_get_special_hotels($type = 'latest', $count = 6, $exclude_ids=array(), $district = array())
    {
        $args = array(
            'post_type' => 'hotel',
            'suppress_filters' => 0,
            'posts_per_page' => $count,
            'post_status' => 'publish',
        );

        if (!empty($exclude_ids))
        {
            $args['post__not_in'] = $exclude_ids;
        }

        if (!empty($district))
        {
            if (is_numeric($district[0]))
            {
                $args['tax_query'] = array(
                    array(
                        'taxonomy' => 'district',
                        'field' => 'term_id',
                        'terms' => $district
                    )
                );
            }
            else
            {
                $args['tax_query'] = array(
                    array(
                        'taxonomy' => 'district',
                        'field' => 'name',
                        'terms' => $district
                    )
                );
            }
        }

        if ($type == 'featured')
        {
            $args = array_merge($args, array(
                'orderby' => 'modified',
                'order' => 'DESC',
                'meta_key' => '_hotel_featured',
                'meta_value' => '1',
            ));
            return get_posts($args);
        }
        elseif ($type == 'latest')
        {
            $args = array_merge($args, array(
                'orderby' => 'post_date',
                'order' => 'DESC',
            ));
            return get_posts($args);
        }
        elseif ($type == 'popular')
        {
            global $wpdb;
            $tbl_postmeta = esc_sql($wpdb->prefix . 'postmeta');
            $tbl_terms = esc_sql($wpdb->prefix . 'terms');
            $tbl_term_taxonomy = esc_sql($wpdb->prefix . 'term_taxonomy');
            $tbl_term_relationships = esc_sql($wpdb->prefix . 'term_relationships');

            $date = date('Y-m-d', strtotime('-30 days'));
            $sql = 'SELECT hotel_id, COUNT(*) AS booking_count FROM ' . CT_HOTEL_BOOKINGS_TABLE . ' AS booking
			INNER JOIN ' . CT_ORDER_TABLE . ' as _order ON _order.id = booking.order_id';
            $where = ' WHERE (_order.status <> "cancelled") AND (_order.created > %s)';
            if (!empty($district))
            {
                $sql .= " INNER JOIN {$tbl_term_relationships} AS tr ON tr.object_id = t1.hotel_id
						INNER JOIN {$tbl_term_taxonomy} AS tt ON tt.term_taxonomy_id = tr.term_taxonomy_id";
                $where .= " AND tt.taxonomy = 'district' AND tt.term_id =" . esc_sql($district) . ")";
            }
            $sql .= $where . ' GROUP BY booking.hotel_id ORDER BY booking_count desc LIMIT %d';
            $popular_hotel = $wpdb->get_results($wpdb->prepare($sql, $date, $count));
            $result = array();
            if (!empty($popular_hotel))
            {
                foreach ($popular_hotel as $hotel)
                {
                    $temp = get_post(ct_hotel_clang_id($hotel->hotel_id));
                    if (!empty($temp))
                    {
                        $result[] = $temp;
                    }
                }
            }
            // if booked room number in last month is smaller than count then add latest hotel
            if (count($popular_hotel) < $count)
            {
                foreach ($popular_hotel as $hotel)
                {
                    $exclude_ids[] = ct_hotel_clang_id($hotel->hotel_id);
                }
                $result = array_merge($result, ct_hotel_get_special_hotels('latest', $count - count($result), $exclude_ids, $district));
            }
            return $result;
        }
    }

}

/*
 * Calculate the price of selected hotel room and return price array data
 */
if (!function_exists('ct_hotel_calc_price_per_room'))
{

    function ct_hotel_calc_price_per_room($hotel_id, $room_type_id, $from_date, $to_date, $rooms = 1)
    {
        global $wpdb;

        $hotel_id = ct_hotel_org_id($hotel_id);
        $room_type_id = ct_room_org_id($room_type_id);

        $total_price = 0;

        if(!empty($from_date))
            $from_date_obj = new DateTime('@'.ct_strtotime($from_date));
        else
            $from_date_obj = new DateTime(ct_strtotime($from_date));

        if(!empty($to_date))
            $to_date_obj = new DateTime('@'.ct_strtotime($to_date));
        else
            $to_date_obj = new DateTime(ct_strtotime($to_date));

        $date_interval = DateInterval::createFromDateString('1 day');

        $period = new DatePeriod($from_date_obj, $date_interval, $to_date_obj);

        $price_data = array();

        $hotel_id = esc_sql($hotel_id);
        $room_type_id = esc_sql($room_type_id);
        $rooms_needed = (int) $rooms;
        $check_dates = array();
        $night_dates = array();
        foreach ($period as $dt)
        {
            $night_dates[] = $dt->format("Y-m-d");
        }
        $sql_check_date_parts = array();
        foreach ($night_dates as $night_date)
        {
            $sql_check_date_parts[] = "SELECT '" . esc_sql($night_date) . "' AS check_date";
        }
        $stay_from = esc_sql($from_date_obj->format('Y-m-d'));
        $stay_to = esc_sql($to_date_obj->format('Y-m-d'));
        $except_sql = ( empty($except_booking_no) || empty($pin_code) ) ? "" : ( " AND NOT ( hotel_order.booking_no = '" . $except_booking_no . "' AND hotel_order.pin_code = '" . $pin_code . "' )" );
        $rows_by_date = array();
        if (!empty($sql_check_date_parts))
        {
            $sql = "SELECT check_dates.check_date, vacancies.price_per_room, vacancies.price_per_person, vacancies.price_per_child, vacancies.promotion
					FROM (" . implode(' UNION ', $sql_check_date_parts) . ") AS check_dates
					INNER JOIN " . CT_HOTEL_VACANCIES_TABLE . " AS vacancies
						ON vacancies.hotel_id = '" . $hotel_id . "'
						AND vacancies.room_type_id = '" . $room_type_id . "'
						AND vacancies.date_from <= check_dates.check_date
						AND vacancies.date_to >= check_dates.check_date
						AND vacancies.active = 1
					LEFT JOIN (
						SELECT hotel_booking.rooms, hotel_order.date_from, hotel_order.date_to
						FROM " . CT_HOTEL_BOOKINGS_TABLE . " AS hotel_booking
						INNER JOIN " . CT_ORDER_TABLE . " AS hotel_order ON hotel_order.id = hotel_booking.order_id
						WHERE hotel_order.status != 'cancelled'
							AND hotel_order.post_id = '" . $hotel_id . "'
							AND hotel_booking.room_type_id = '" . $room_type_id . "'
							AND hotel_order.date_from < '" . $stay_to . "'
							AND hotel_order.date_to > '" . $stay_from . "'" . $except_sql . "
					) AS bookings
						ON bookings.date_from <= check_dates.check_date
						AND bookings.date_to > check_dates.check_date
					GROUP BY vacancies.id, check_dates.check_date, vacancies.rooms, vacancies.price_per_room, vacancies.price_per_person, vacancies.price_per_child, vacancies.promotion
					HAVING vacancies.rooms - IFNULL(SUM(bookings.rooms), 0) >= " . $rooms_needed;
            foreach ($wpdb->get_results($sql) as $row)
            {
                $rows_by_date[$row->check_date][] = $row;
            }
        }

        foreach ($night_dates as $check_date)
        {
            $check_date = esc_sql($check_date);
            $check_dates[] = $check_date;
            $result = isset($rows_by_date[$check_date]) ? $rows_by_date[$check_date] : array();

            if (empty($result))
            { //if no available rooms on selected date
                return esc_html__('Sorry, The room you are booking now is just taken by another customer. Please have another look.', 'citytours');
            }
            else
            {

                if (is_array($result))
                {
                    $price_per_room = 0;
                    $promotion = 0;
                    foreach ($result as $row)
                    {
                        if ($row->promotion == 1)
                        {
                            $result = $row;
                            $promotion = $row->promotion;
                            $price_per_room = $row->price_per_room;
                        }
                        elseif ($row->price_per_room > $price_per_room && $promotion == 0)
                        {
                            $result = $row;
                            $price_per_room = $row->price_per_room;
                        }
                    }
                }


                $price_per_room = (float) $result->price_per_room;

                $total_price += $price_per_room;
            }
        }

        return $total_price;
    }

}

/*
 * Stay totals for every listed room in one query.
 * Each night uses the same choice as ct_hotel_calc_price_per_room:
 * a promotion row wins, otherwise the highest price_per_room, then the nights are summed.
 */
if (!function_exists('ct_hotel_calc_stay_prices'))
{
    function ct_hotel_calc_stay_prices($hotel_id, $room_type_ids, $from_date, $to_date, $rooms = 1)
    {
        global $wpdb;

        $requested_ids = array();
        foreach ((array) $room_type_ids as $room_type_id)
        {
            $room_type_id = (int) $room_type_id;
            if ($room_type_id > 0)
            {
                $requested_ids[] = $room_type_id;
            }
        }
        $requested_ids = array_values(array_unique($requested_ids));

        $prices = array();
        $empty = array(
            'nights' => 0,
            'prices' => $prices,
        );
        if (empty($requested_ids))
        {
            return $empty;
        }

        $hotel_id = ct_hotel_org_id($hotel_id);
        $hotel_id = esc_sql($hotel_id);
        $rooms_needed = (int) $rooms;

        if (!empty($from_date))
            $from_date_obj = new DateTime('@' . ct_strtotime($from_date));
        else
            $from_date_obj = new DateTime(ct_strtotime($from_date));

        if (!empty($to_date))
            $to_date_obj = new DateTime('@' . ct_strtotime($to_date));
        else
            $to_date_obj = new DateTime(ct_strtotime($to_date));

        $date_interval = DateInterval::createFromDateString('1 day');
        $period = new DatePeriod($from_date_obj, $date_interval, $to_date_obj);
        $night_dates = array();
        foreach ($period as $dt)
        {
            $night_dates[] = $dt->format('Y-m-d');
        }

        $org_ids = array();
        $org_by_requested = array();
        foreach ($requested_ids as $requested_id)
        {
            $org_id = (int) ct_room_org_id($requested_id);
            $org_by_requested[$requested_id] = $org_id;
            if ($org_id > 0)
            {
                $org_ids[] = $org_id;
            }
        }
        $org_ids = array_values(array_unique($org_ids));

        if (empty($night_dates) || empty($org_ids))
        {
            foreach ($requested_ids as $requested_id)
            {
                $prices[$requested_id] = 0;
            }
            return array(
                'nights' => count($night_dates),
                'prices' => $prices,
            );
        }

        $stay_from = esc_sql($from_date_obj->format('Y-m-d'));
        $stay_to = esc_sql($to_date_obj->format('Y-m-d'));
        $sql_check_date_parts = array();
        foreach ($night_dates as $night_date)
        {
            $sql_check_date_parts[] = "SELECT '" . esc_sql($night_date) . "' AS check_date";
        }
        $room_list = implode(',', $org_ids);
        $sql = "SELECT check_dates.check_date, vacancies.room_type_id, vacancies.price_per_room, vacancies.price_per_person, vacancies.price_per_child, vacancies.promotion
				FROM (" . implode(' UNION ', $sql_check_date_parts) . ") AS check_dates
				INNER JOIN " . CT_HOTEL_VACANCIES_TABLE . " AS vacancies
					ON vacancies.hotel_id = '" . $hotel_id . "'
					AND vacancies.room_type_id IN (" . $room_list . ")
					AND vacancies.date_from <= check_dates.check_date
					AND vacancies.date_to >= check_dates.check_date
					AND vacancies.active = 1
				LEFT JOIN (
					SELECT hotel_booking.room_type_id, hotel_booking.rooms, hotel_order.date_from, hotel_order.date_to
					FROM " . CT_HOTEL_BOOKINGS_TABLE . " AS hotel_booking
					INNER JOIN " . CT_ORDER_TABLE . " AS hotel_order ON hotel_order.id = hotel_booking.order_id
					WHERE hotel_order.status != 'cancelled'
						AND hotel_order.post_id = '" . $hotel_id . "'
						AND hotel_booking.room_type_id IN (" . $room_list . ")
						AND hotel_order.date_from < '" . $stay_to . "'
						AND hotel_order.date_to > '" . $stay_from . "'
				) AS bookings
					ON bookings.room_type_id = vacancies.room_type_id
					AND bookings.date_from <= check_dates.check_date
					AND bookings.date_to > check_dates.check_date
				GROUP BY vacancies.id, check_dates.check_date, vacancies.room_type_id, vacancies.rooms, vacancies.price_per_room, vacancies.price_per_person, vacancies.price_per_child, vacancies.promotion
				HAVING vacancies.rooms - IFNULL(SUM(bookings.rooms), 0) >= " . $rooms_needed;

        $rows_by_room_date = array();
        foreach ($wpdb->get_results($sql) as $row)
        {
            $rows_by_room_date[(int) $row->room_type_id][$row->check_date][] = $row;
        }

        foreach ($requested_ids as $requested_id)
        {
            $org_id = $org_by_requested[$requested_id];
            $total_price = 0;
            $missing_night = false;
            foreach ($night_dates as $check_date)
            {
                $result = isset($rows_by_room_date[$org_id][$check_date]) ? $rows_by_room_date[$org_id][$check_date] : array();
                if (empty($result))
                {
                    $missing_night = true;
                    break;
                }

                $price_per_room = 0;
                $promotion = 0;
                $selected = null;
                foreach ($result as $row)
                {
                    if ($row->promotion == 1)
                    {
                        $selected = $row;
                        $promotion = $row->promotion;
                        $price_per_room = $row->price_per_room;
                    }
                    elseif ($row->price_per_room > $price_per_room && $promotion == 0)
                    {
                        $selected = $row;
                        $price_per_room = $row->price_per_room;
                    }
                }

                $total_price += ($selected === null) ? 0 : (float) $selected->price_per_room;
            }

            if ($missing_night)
            {
                $prices[$requested_id] = esc_html__('Sorry, The room you are booking now is just taken by another customer. Please have another look.', 'citytours');
            }
            else
            {
                $prices[$requested_id] = $total_price;
            }
        }

        return array(
            'nights' => count($night_dates),
            'prices' => $prices,
        );
    }
}

/*
 * Calculate the price of selected hotel room and return price array data
 */
if (!function_exists('ct_hotel_calc_price_per_room_ajax'))
{
    function ct_hotel_calc_price_per_room_ajax()
    {
        $hotel_id = $_POST['hotel_id'];
        $room_type_id = $_POST['room_type_id'];
        $from_date = ct_get_phptime($_POST['date_from']);
        $to_date = ct_get_phptime($_POST['date_to']);

        $data['price'] = ct_hotel_calc_price_per_room($hotel_id, $room_type_id, $from_date, $to_date);
        if (!is_numeric($data['price']))
        {
            wp_send_json_error(array('message' => wp_strip_all_tags($data['price'])));
        }

        $data['price_format'] = ct_price($data['price'], "special");

        wp_send_json_success($data);
    }

}

/*
 * One response for every room on the hotel page, so the table updates once.
 */
if (!function_exists('ct_hotel_calc_room_prices_ajax'))
{
    function ct_hotel_calc_room_prices_ajax()
    {
        $hotel_id = isset($_POST['hotel_id']) ? absint($_POST['hotel_id']) : 0;
        $from_date = ct_get_phptime(isset($_POST['date_from']) ? sanitize_text_field(wp_unslash($_POST['date_from'])) : '');
        $to_date = ct_get_phptime(isset($_POST['date_to']) ? sanitize_text_field(wp_unslash($_POST['date_to'])) : '');
        $room_type_ids = isset($_POST['room_type_ids']) ? (array) $_POST['room_type_ids'] : array();
        $room_type_ids = array_slice($room_type_ids, 0, 50);

        $stay = ct_hotel_calc_stay_prices($hotel_id, $room_type_ids, $from_date, $to_date);
        $prices = array();
        $min_total = null;
        foreach ($stay['prices'] as $room_id => $raw_price)
        {
            if (is_numeric($raw_price) && (float) $raw_price > 0)
            {
                $prices[$room_id] = array(
                    'available' => true,
                    'price' => $raw_price,
                    'price_format' => ct_price($raw_price, 'special'),
                );
                if ($min_total === null || $raw_price < $min_total)
                {
                    $min_total = $raw_price;
                }
            }
            else
            {
                $prices[$room_id] = array(
                    'available' => false,
                    'price' => '',
                    'message' => 'غير متوفر',
                );
            }
        }

        $nights = (int) $stay['nights'];
        $from_per_night = ($nights > 0 && $min_total !== null) ? $min_total / $nights : 0;

        wp_send_json_success(array(
            'prices' => $prices,
            'nights' => $nights,
            'from_per_night_format' => ct_price($from_per_night, 'special'),
        ));
    }
}


if (!function_exists('ct_get_hotel_list'))
{

    function ct_get_hotel_list()
    {
        global $ct_options, $post_list, $current_view;
        $order_array = array('ASC', 'DESC');
        $order_by_array = array(
            '' => '',
            'price' => 'price',
            'rating' => 'rating'
        );

        $order_defaults = array(
            'price' => 'ASC',
            'rating' => 'DESC'
        );

        $s = isset($_REQUEST['s']) ? sanitize_text_field($_REQUEST['s']) : '';
        $date_from = isset($_REQUEST['date_from']) ? ct_sanitize_date($_REQUEST['date_from']) : '';
        $date_to = isset($_REQUEST['date_to']) ? ct_sanitize_date($_REQUEST['date_to']) : '';
        if (ct_strtotime($date_from) >= ct_strtotime($date_to))
        {
            $date_from = '';
            $date_to = '';
        }

        $rooms = ( isset($_REQUEST['rooms']) && is_numeric($_REQUEST['rooms']) ) ? sanitize_text_field($_REQUEST['rooms']) : 1;
        $adults = ( isset($_REQUEST['adults']) && is_numeric($_REQUEST['adults']) ) ? sanitize_text_field($_REQUEST['adults']) : 1;
        $kids = ( isset($_REQUEST['kids']) && is_numeric($_REQUEST['kids']) ) ? sanitize_text_field($_REQUEST['kids']) : 0;

        $order_by = ( isset($_REQUEST['order_by']) && array_key_exists($_REQUEST['order_by'], $order_by_array) ) ? sanitize_text_field($_REQUEST['order_by']) : '';
        $order = ( isset($_REQUEST['order']) && in_array($_REQUEST['order'], $order_array) ) ? sanitize_text_field($_REQUEST['order']) : 'ASC';
        $districts = ( isset($_REQUEST['districts']) ) ? ( is_array($_REQUEST['districts']) ? $_REQUEST['districts'] : array($_REQUEST['districts']) ) : array();
        $price_filter = ( isset($_REQUEST['price_filter']) && is_array($_REQUEST['price_filter']) ) ? $_REQUEST['price_filter'] : array();
        $star_filter = ( isset($_REQUEST['star_filter']) && is_array($_REQUEST['star_filter']) ) ? $_REQUEST['star_filter'] : array();
        $rating_filter = ( isset($_REQUEST['rating_filter']) && is_array($_REQUEST['rating_filter']) ) ? $_REQUEST['rating_filter'] : array();
        $facility_filter = ( isset($_REQUEST['facility_filter']) && is_array($_REQUEST['facility_filter']) ) ? $_REQUEST['facility_filter'] : array();
        $country = ( isset($_REQUEST['country']) ) ? ( is_array($_REQUEST['country']) ? $_REQUEST['country'] : $_REQUEST['country'] ) : array();

        if (isset($_REQUEST['view']) && !empty($_REQUEST['view']))
        {
            $current_view = sanitize_text_field($_REQUEST['view']);
        }
        else if (isset($ct_options['hotel_list_default_view']) && !empty($ct_options['hotel_list_default_view']))
        {
            $current_view = $ct_options['hotel_list_default_view'];
        }
        else
        {
            $current_view = 'list';
        }

        $page = ( isset( $_REQUEST['page'] ) && ( is_numeric( $_REQUEST['page'] ) ) && ( $_REQUEST['page'] >= 1 ) ) ? sanitize_text_field( $_REQUEST['page'] ):1;
        $per_page = ( isset($ct_options['hotel_posts']) && is_numeric($ct_options['hotel_posts']) ) ? $ct_options['hotel_posts'] : 6;

        $last_no = ( $page - 1 ) * $per_page;

        if (empty($districts[0]) && !empty($country))
        {
            $districts[0] = $country;
        }

        $search_result = ct_hotel_get_search_result(array('s' => $s, 'date_from' => $date_from, 'date_to' => $date_to, 'rooms' => $rooms, 'adults' => $adults, 'kids' => $kids, 'star_filter' => $star_filter, 'district' => $districts, 'price_filter' => $price_filter, 'rating_filter' => $rating_filter, 'facility_filter' => $facility_filter, 'order_by' => $order_by_array[$order_by], 'order' => $order, 'last_no' => $last_no, 'per_page' => $per_page));

        $post_list = $search_result['ids'];
        $count = $search_result['count']; // total_count

        ct_get_template('hotel-list.php', '/templates/hotel/');
        ?>

        <div class="text-center">

        <?php
        unset($_REQUEST['page']);
        unset($_REQUEST['action']);
        $pagenum_link = strtok(home_url('/hotel/'), '?') . '%_%';
        $total = ceil($count / $per_page);
        $args = array(
            'base' => $pagenum_link, // http://example.com/all_posts.php%_% : %_% is replaced by format (below)
            'total' => $total,
            'format' => '?page=%#%',
            'current' => $page,
            'show_all' => true,
            'prev_next' => true,
            'prev_text' => esc_html__('Previous', 'citytours'),
            'next_text' => esc_html__('Next', 'citytours'),
            'end_size' => 1,
            'mid_size' => 5,
            'type' => 'list',
            'add_args' => $_REQUEST,
        );

        $pagination = paginate_links($args);
        $pagination = str_replace("<ul class='page-numbers'>", '<ul class="pagination">', $pagination);
        $pagination = str_replace('page-numbers', '', $pagination);

        echo $pagination;
        ?>
        </div>
        <!-- end pagination-->
        <?php
    }

}

/*
 * Calculate the price of selected hotel room and return price array data
 */
if (!function_exists('ct_hotel_average_price'))
{
    function ct_hotel_average_price($hotel_id, $from_date, $to_date, $rooms = 1)
    {
        global $wpdb;

        // Create cache key
        $cache_key = 'hotel_avg_price_' . $hotel_id . '_' . $from_date . '_' . $to_date . '_' . $rooms;
        
        // Try to get from Redis cache first
        $cached_price = wp_cache_get($cache_key, 'hotel_prices');
        
        
        if ($cached_price !== false) {
            return $cached_price;
        }

        if(!empty($from_date))
            $from_date_obj = new DateTime('@'.ct_strtotime($from_date));
        else
            $from_date_obj = new DateTime(ct_strtotime($from_date));

        if(!empty($to_date))
            $to_date_obj = new DateTime('@'.ct_strtotime($to_date));
        else
            $to_date_obj = new DateTime(ct_strtotime($to_date));

        $hotel_id = ct_hotel_org_id($hotel_id);

        $date_interval =  new DateInterval('P1D');
        $date_interval = DateInterval::createFromDateString('1 day');

        $period = new DatePeriod($from_date_obj, $date_interval, $to_date_obj);

        $price_data = array();

        $hotel_id = esc_sql($hotel_id);
        $rooms_needed = (int) $rooms;
        $price = array();
        $night_dates = array();
        foreach ($period as $dt)
        {
            $night_dates[] = $dt->format('Y-m-d');
        }
        if (empty($night_dates))
        {
            return 0;
        }

        $stay_from = esc_sql($from_date_obj->format('Y-m-d'));
        $stay_to = esc_sql($to_date_obj->format('Y-m-d'));
        $except_sql = ( empty($except_booking_no) || empty($pin_code) ) ? '' : ( " AND NOT ( hotel_order.booking_no = '" . $except_booking_no . "' AND hotel_order.pin_code = '" . $pin_code . "' )" );
        $sql_check_date_parts = array();
        foreach ($night_dates as $night_date)
        {
            $sql_check_date_parts[] = "SELECT '" . esc_sql($night_date) . "' AS check_date";
        }
        $sql = "SELECT check_dates.check_date, vacancies.room_type_id, vacancies.price_per_room, vacancies.price_per_person, vacancies.price_per_child, vacancies.promotion
				FROM (" . implode(' UNION ', $sql_check_date_parts) . ") AS check_dates
				INNER JOIN " . CT_HOTEL_VACANCIES_TABLE . " AS vacancies
					ON vacancies.hotel_id = '" . $hotel_id . "'
					AND vacancies.date_from <= check_dates.check_date
					AND vacancies.date_to >= check_dates.check_date
					AND vacancies.active = 1
				LEFT JOIN (
					SELECT hotel_booking.room_type_id, hotel_booking.rooms, hotel_order.date_from, hotel_order.date_to
					FROM " . CT_HOTEL_BOOKINGS_TABLE . " AS hotel_booking
					INNER JOIN " . CT_ORDER_TABLE . " AS hotel_order ON hotel_order.id = hotel_booking.order_id
					WHERE hotel_order.status != 'cancelled'
						AND hotel_order.post_id = '" . $hotel_id . "'
						AND hotel_order.date_from < '" . $stay_to . "'
						AND hotel_order.date_to > '" . $stay_from . "'" . $except_sql . "
				) AS bookings
					ON bookings.room_type_id = vacancies.room_type_id
					AND bookings.date_from <= check_dates.check_date
					AND bookings.date_to > check_dates.check_date
				GROUP BY vacancies.id, check_dates.check_date, vacancies.room_type_id, vacancies.rooms, vacancies.price_per_room, vacancies.price_per_person, vacancies.price_per_child, vacancies.promotion
				HAVING vacancies.rooms - IFNULL(SUM(bookings.rooms), 0) >= " . $rooms_needed . "
				ORDER BY check_dates.check_date, vacancies.id";
        $rows_by_date = array();
        foreach ($wpdb->get_results($sql) as $row)
        {
            $rows_by_date[$row->check_date][] = $row;
        }

        foreach ($night_dates as $check_date)
        {
            $result = isset($rows_by_date[$check_date]) ? $rows_by_date[$check_date] : array();
            if (empty($result))
            {
                return 0;
            }

            $price_per_room = 0;
            $promotion = 0;
            foreach ($result as $row)
            {
                if ($row->promotion == 1)
                {
                    $promotion = $row->promotion;
                    $price_per_room = $row->price_per_room;
                }
                elseif ($row->price_per_room > $price_per_room && $promotion == 0)
                {
                    $price_per_room = $row->price_per_room;
                }

                $price[$row->room_type_id] = $price_per_room;
            }
        }

        // Calculate final price
        $final_price = 0;
        if (count($price) >= 1) {
            $final_price = min($price);
        }

        // Cache the result for 1 hour
        wp_cache_set($cache_key, $final_price, 'hotel_prices', 3600);

        return $final_price;
    }

}



if (!function_exists('calculateAverage'))
{
    function calculateAverage($array) 
    {
        // Calculate the sum of the array values
        $sum = array_sum($array);

        // Calculate the count of the array values
        $count = count($array);

        // Calculate the average
        $average = $sum / $count;

        // Format the average to 2 decimal places
        $average = number_format($average, 2, '.', '');
        
        return $average;
    }
}