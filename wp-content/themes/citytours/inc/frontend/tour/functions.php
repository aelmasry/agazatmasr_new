<?php
if (!defined('ABSPATH'))
    exit; // Exit if accessed directly

if (!function_exists('ct_tour_get_schedules'))
{

    function ct_tour_get_schedules($tour_id)
    {
        // validation
        // init variables
        global $wpdb;
        $tour_id = ct_tour_org_id($tour_id);

        $sql = "SELECT schedules.from, schedules.to, schedule_meta.* FROM " . CT_TOUR_SCHEDULES_TABLE . " AS schedules
				LEFT JOIN " . CT_TOUR_SCHEDULE_META_TABLE . " AS schedule_meta
					ON schedules.id = schedule_meta.schedule_id
				WHERE schedules.tour_id = %d
				ORDER BY schedules.ts_id ASC, schedule_meta.day ASC";

        $schedules = $wpdb->get_results($wpdb->prepare($sql, $tour_id), ARRAY_A);

        return $schedules;
    }

}

if (!function_exists('ct_get_tour_cart_page'))
{

    function ct_get_tour_cart_page()
    {
        global $ct_options;
        if (!empty($ct_options['tour_cart_page']))
        {
            return ct_get_permalink_clang($ct_options['tour_cart_page']);
        }
        return false;
    }

}
if (!function_exists('ct_get_tour_checkout_page'))
{

    function ct_get_tour_checkout_page()
    {
        global $ct_options;
        if (!empty($ct_options['tour_checkout_page']))
        {
            return ct_get_permalink_clang($ct_options['tour_checkout_page']);
        }
        return false;
    }

}
if (!function_exists('ct_get_tour_thankyou_page'))
{

    function ct_get_tour_thankyou_page()
    {
        global $ct_options;
        if (!empty($ct_options['tour_thankyou_page']))
        {
            return ct_get_permalink_clang($ct_options['tour_thankyou_page']);
        }
        return false;
    }

}

if (!function_exists('ct_tour_check_availability'))
{

    function ct_tour_check_availability($post_id, $date = '', $adults = 1, $kids = 0)
    {

        // validation
        if (empty($post_id) || 'tour' != get_post_type($post_id))
            return esc_html__('Invalide Tour ID.', 'citytours'); //invalid data
        $max_people = get_post_meta($post_id, '_tour_max_people', true);
        if (empty($max_people))
            return true; // max people empty means no limit
        $is_repeated = get_post_meta($post_id, '_tour_repeated', true);

        $post_id = esc_sql(ct_tour_org_id($post_id));
        if (!empty($is_repeated) && !ct_strtotime($date))
        {
            return esc_html__('Invalid date. Please check your booking date again.', 'citytours'); //invalid data
        }

        global $wpdb;
        $where = "1=1";
        $where .= " AND tour_id=" . $post_id;
        if (!empty($is_repeated))
            $where .= " AND tour_date='" . esc_sql(date('Y-m-d', ct_strtotime($date))) . "'";
        $sql = "SELECT SUM(adults) FROM " . CT_TOUR_BOOKINGS_TABLE . " WHERE $where";
        $booked_count = $wpdb->get_var($sql);
        if ($booked_count + $adults <= $max_people)
        {
            return true;
        }
        elseif ($booked_count >= $max_people)
        {
            return esc_html__('Sold Out', 'citytours');
        }
        else
        {
            return sprintf(esc_html__('Exceed Persons. Only %d persons available', 'citytours'), $max_people - $booked_count);
        }
    }

}

if (!function_exists('ct_tour_calc_tour_price'))
{

    function ct_tour_calc_tour_price($post_id, $date = '', $adults = 1, $kids = 0)
    {
        //        if ( empty( $person_price ) ) $person_price = 0;
//		$charge_child = get_post_meta( $post_id, '_tour_charge_child', true );
//		$child_price = get_post_meta( $post_id, '_tour_price_child', true );
//		if ( empty( $charge_child ) || empty( $child_price ) ) $child_price = 0;
//		$total = $person_price * $adults + $child_price * $kids;

        $priceOfAdults = 0;
        if (!is_array($adults))
        {
            $priceOfAdults += $adults * get_post_meta($post_id, '_tour_price', true);
        }

        if (is_array($adults) && isset($adults['person']))
        {
            $priceOfAdults += $adults['person'] * get_post_meta($post_id, '_tour_price', true);
        }

        if (isset($adults['double']))
        {
            $priceOfAdults += $adults['double'] * get_post_meta($post_id, '_tour_price_in_double_room', true);
        }

        if (isset($adults['triple']))
        {
            $priceOfAdults += $adults['triple'] * get_post_meta($post_id, '_tour_price_in_triple_room', true);
        }

        if (isset($adults['single']))
        {
            $priceOfAdults += $adults['single'] * get_post_meta($post_id, '_tour_price_in_single_room', true);
        }

        if (isset($adults['quad']))
        {
            $priceOfAdults += $adults['quad'] * get_post_meta($post_id, '_tour_price_in_quad_room', true);
        }

        $charge_child = get_post_meta($post_id, '_tour_charge_child', true);
        $priceOfKids = 0;
        if ($charge_child)
        {
            if (!is_array($kids))
            {
                $priceOfKids += $kids * (int) get_post_meta($post_id, '_tour_price_child', true);
            }

            if (isset($kids['0']))
            {
                $priceOfKids += $kids['0'] * get_post_meta($post_id, '_tour_price_child', true);
            }

            if (isset($kids['2']))
            {
                $priceOfKids += $kids['2'] * get_post_meta($post_id, '_tour_price_child_0_2', true);
            }

            if (isset($kids['6']))
            {
                $priceOfKids += $kids['6'] * get_post_meta($post_id, '_tour_price_child_2_6', true);
            }

            if (isset($kids['11']))
            {
                $priceOfKids += $kids['11'] * get_post_meta($post_id, '_tour_price_child_6_11', true);
            }
        }

        $total = $priceOfAdults + $priceOfKids;
        return $total;
    }

}

/*
 * send booking confirmation email function
 */
if (!function_exists('ct_tour_generate_conf_mail'))
{

    function ct_tour_generate_conf_mail($order, $type = 'new')
    {
        global $wpdb, $ct_options;
        $order_data = $order->get_order_info();
        if (!empty($order_data))
        {
            // server variables
            // $admin_email = get_option('admin_email');
            $admin_email = $ct_options['inquiry_email'];
            $home_url = esc_url(home_url('/'));
            $site_name = $_SERVER['SERVER_NAME'];
            $logo_url = esc_url(ct_logo_url());
            $order_data['tour_id'] = ct_tour_clang_id($order_data['post_id']);

            // tour info
            $tour_name = get_the_title($order_data['tour_id']);
            if (!empty($order_data['date_from']) && '0000-00-00' != $order_data['date_from'])
                $tour_name .= ' - ' . date_i18n('j F Y', strtotime($order_data['date_from']));
            $tour_url = esc_url(ct_get_permalink_clang($order_data['tour_id']));
            $tour_thumbnail = get_the_post_thumbnail($order_data['tour_id'], 'medium');
            $tour_address = get_post_meta($order_data['tour_id'], '_tour_address', true);
            $tour_email = get_post_meta($order_data['tour_id'], '_tour_email', true);
            $tour_phone = get_post_meta($order_data['tour_id'], '_tour_phone', true);

            // services info
            $booking_services = '<table><tbody><tr><th>' . esc_html__('Service Name', 'citytours') . '</th><th>' . esc_html__('Quantity', 'citytours') . '</th><th>' . esc_html__('Total Price', 'citytours') . '</th></tr>';
            $services_booking_data = $order->get_services();
            if (!empty($services_booking_data))
            {
                foreach ($services_booking_data as $key => $service_booking_data)
                {
                    $service_data = ct_get_add_service($service_booking_data['add_service_id']);
                    $service_qty = $service_booking_data['qty'];
                    $service_total_price = $service_booking_data['total_price'];
                    $booking_services .= '<tr><td>' . $service_data->title . '</td><td>' . $service_qty . '</td><td>' . $service_total_price . '</td></tr>';
                }
            }
            $booking_services .= '</tbody></table>';

            // booking info
            $booking_date = date_i18n('j F Y', strtotime($order_data['date_from']));
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
            $customer_country_code = $order_data['country'];
            $customer_phone = $order_data['phone'];
            $customer_address1 = $order_data['address1'];
            $customer_address2 = $order_data['address2'];
            $customer_city = $order_data['city'];
            $customer_zip = $order_data['zip'];
            $customer_country = $order_data['country'];
            $customer_special_requirements = $order_data['special_requirements'];

            $variables = array('home_url',
                'site_name',
                'logo_url',
                'tour_name',
                'tour_url',
                'tour_thumbnail',
                'tour_address',
                'tour_email',
                'tour_phone',
                'booking_services',
                'booking_no',
                'booking_pincode',
                'booking_date',
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
                $subject = empty($ct_options['tour_confirm_email_subject']) ? 'Booking Confirmation Email Subject' : $ct_options['tour_confirm_email_subject'];
            }

            if (empty($description))
            {
                $description = empty($ct_options['tour_confirm_email_description']) ? 'Booking Confirmation Email Description' : $ct_options['tour_confirm_email_description'];
            }

            foreach ($variables as $variable)
            {
                $subject = str_replace("[" . $variable . "]", $$variable, $subject);
                $description = str_replace("[" . $variable . "]", $$variable, $description);
            }

            $mail_sent = ct_send_mail($site_name, $admin_email, $customer_email, $subject, $description);

            /* mailing function to admin */
            if (!empty($ct_options['tour_booked_notify_admin']))
            {
                $subject = empty($ct_options['tour_admin_email_subject']) ? 'You received a booking' : $ct_options['tour_admin_email_subject'];
                $description = empty($ct_options['tour_admin_email_description']) ? 'Booking Details' : $ct_options['tour_admin_email_description'];

                foreach ($variables as $variable)
                {
                    $subject = str_replace("[" . $variable . "]", $$variable, $subject);
                    $description = str_replace("[" . $variable . "]", $$variable, $description);
                }

                ct_send_mail($site_name, $admin_email, $admin_email, $subject, $description);
            }
            return true;
        }
        return false;
    }

}

if (!function_exists('ct_tour_get_search_result_count'))
{

    function ct_tour_get_search_result_count($args)
    {
        global $ct_options, $wpdb;

        $tour_type = array();
        $price_filter = array();
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

        if ($by == 'tour_type')
        {

            $sql = "SELECT tt.term_id as tour_type, COUNT(*) AS counts FROM {$temp_tbl_name} AS t1
					INNER JOIN {$tbl_posts} post_s1 ON (t1.tour_id = post_s1.ID) AND (post_s1.post_status = 'publish') AND (post_s1.post_type = 'tour')
					INNER JOIN {$tbl_term_relationships} AS tr ON tr.object_id = t1.tour_id
					INNER JOIN {$tbl_term_taxonomy} AS tt ON tt.term_taxonomy_id = tr.term_taxonomy_id";
            $where = " WHERE 1=1 AND tt.taxonomy = 'tour_type'";

            if (!empty($price_filter) && trim(implode('', $price_filter)) != "")
            {
                $sql .= " LEFT JOIN {$tbl_postmeta} AS meta_price ON t1.tour_id = meta_price.post_id AND meta_price.meta_key = '_tour_price'";
                $price_where = array();
                $price_steps = empty($ct_options['tour_price_filter_steps']) ? '50,80,100' : $ct_options['tour_price_filter_steps'];
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

            if (!empty($rating_filter) && trim(implode('', $rating_filter)) != "")
            {
                $sql .= " LEFT JOIN {$tbl_postmeta} AS meta_rating ON t1.tour_id = meta_rating.post_id AND meta_rating.meta_key = '_review'";
                $where .= " AND round( cast( IFNULL( meta_rating.meta_value, 0 ) AS decimal(2,1) ) ) IN ( " . esc_sql(implode(',', $rating_filter)) . " )";
            }

            if (!empty($facility_filter) && trim(implode('', $facility_filter)) != "")
            {
                $where .= " AND (( SELECT COUNT(1) FROM {$tbl_term_relationships} AS tr1
						INNER JOIN {$tbl_term_taxonomy} AS tt1 ON ( tr1.term_taxonomy_id= tt1.term_taxonomy_id )
						WHERE tt1.taxonomy = 'tour_facility' AND tt1.term_id IN (" . esc_sql(implode(',', $facility_filter)) . ") AND tr1.object_id = t1.tour_id ) = " . count($facility_filter) . ")";
            }

            $sql .= $where . " GROUP BY tt.term_id";
            $result = $wpdb->get_results($sql, ARRAY_A);
            $keys = array_map(function($a) {
                return $a['tour_type'];
            }, $result);
            $values = array_map(function($a) {
                return $a['counts'];
            }, $result);
            $result = array_combine($keys, $values);
        }
        return $result;
    }

}

if (!function_exists('ct_tour_get_search_result'))
{

    function ct_tour_get_search_result($args)
    {
        global $ct_options, $wpdb;
        $s = '';
        $date = '';
        $adults = 1;
        $kids = 0;
        $tour_type = array();
        $tour_city = null;
        $price_filter = array();
        $rating_filter = array();
        $facility_filter = array();
        $order_by = '';
        $order = '';
        $last_no = 0;
        $per_page = ( isset($ct_options['tour_posts']) && is_numeric($ct_options['tour_posts']) ) ? $ct_options['tour_posts'] : 6;
        extract($args);

        if (!empty($tour_type) && function_exists('ct_expand_tour_type_ids')) {
            $tour_type = ct_expand_tour_type_ids($tour_type);
        }
        if (!empty($tour_city) && function_exists('ct_expand_tour_type_ids')) {
            $tour_city = ct_expand_tour_type_ids($tour_city, $tour_type);
        }

        $order_array = array('ASC', 'DESC');
        $order_by_array = array(
            '' => '',
            'price' => 'convert(meta_price.meta_value, decimal)',
            'rating' => 'meta_rating.meta_value'
        );
        if (!array_key_exists($order_by, $order_by_array))
            $order_by = '';
        if (!in_array($order, $order_array))
            $order = 'ASC';
        $adults = max(0, (int) $adults);
        $kids = max(0, (int) $kids);
        $last_no = max(0, (int) $last_no);
        $per_page = max(1, (int) $per_page);

        $tbl_posts = esc_sql($wpdb->posts);
        $tbl_postmeta = esc_sql($wpdb->postmeta);
        $tbl_terms = esc_sql($wpdb->prefix . 'terms');
        $tbl_term_taxonomy = esc_sql($wpdb->prefix . 'term_taxonomy');
        $tbl_term_relationships = esc_sql($wpdb->prefix . 'term_relationships');
        $tbl_icl_translations = esc_sql($wpdb->prefix . 'icl_translations');
        $temp_tbl_name = ct_get_temp_table_name();

        $s_query = "SELECT DISTINCT post_s1.ID AS tour_id FROM {$tbl_posts} AS post_s1 WHERE (post_s1.post_status = 'publish') AND (post_s1.post_type = 'tour')";

        // search filter
        if (!empty($s))
        {
            $s = esc_sql($wpdb->esc_like($s));
            $s_query .= " AND ((post_s1.post_title LIKE '%{$s}%') OR (post_s1.post_content LIKE '%{$s}%') )";
        }

        // if wpml is enabled do search by default language post
//        if (defined('ICL_LANGUAGE_CODE') && ( ct_get_lang_count() > 1 ))
//        {
//            $s_query = "SELECT DISTINCT it2.element_id AS tour_id FROM ({$s_query}) AS t0
//						INNER JOIN {$tbl_icl_translations} it1 ON (it1.element_type = 'post_tour') AND it1.element_id = t0.tour_id
//						INNER JOIN {$tbl_icl_translations} it2 ON (it2.element_type = 'post_tour') AND it2.language_code='" . ct_get_default_language() . "' AND it2.trid = it1.trid ";
//        }

        $sql = "SELECT t1.* FROM ( {$s_query} ) AS t1 ";

        if (!empty($date))
        {
            $date = esc_sql(date('Y-m-d', ct_strtotime($date)));
            $day_of_week = esc_sql(date('w', ct_strtotime($date)));

            $sql .= " LEFT JOIN {$tbl_postmeta} AS meta_c1 ON (meta_c1.meta_key = '_tour_max_people') AND (t1.tour_id = meta_c1.post_id)";
            $sql .= " LEFT JOIN {$tbl_postmeta} AS meta_c2 ON (meta_c2.meta_key = '_tour_repeated') AND (t1.tour_id = meta_c2.post_id)";
            $sql .= " LEFT JOIN {$tbl_postmeta} AS meta_c3 ON (meta_c3.meta_key = '_tour_date') AND (t1.tour_id = meta_c3.post_id)";
            $sql .= " LEFT JOIN {$tbl_postmeta} AS meta_c4 ON (meta_c4.meta_key = '_tour_start_date') AND (t1.tour_id = meta_c4.post_id)";
            $sql .= " LEFT JOIN {$tbl_postmeta} AS meta_c5 ON (meta_c5.meta_key = '_tour_end_date') AND (t1.tour_id = meta_c5.post_id)";
            $sql .= " LEFT JOIN {$tbl_postmeta} AS meta_c6 ON (meta_c6.meta_key = '_tour_available_days') AND (t1.tour_id = meta_c6.post_id)";
            $sql .= " LEFT JOIN ( SELECT tour_booking.tour_id, SUM( tour_booking.adults ) as adults, SUM( tour_booking.kids ) as kids FROM " . CT_TOUR_BOOKINGS_TABLE . " AS tour_booking
						INNER JOIN " . CT_ORDER_TABLE . " as tour_order
						ON tour_order.id = tour_booking.order_id AND tour_order.status!='cancelled'
						WHERE tour_order.date_from = '{$date}'
						GROUP BY tour_booking.tour_id ) AS booking_info ON booking_info.tour_id = t1.tour_id";
            $sql .= " WHERE ((( meta_c2.meta_value=1 ) AND ( IFNULL(meta_c4.meta_value, '0000-00-00') < '{$date}' ) AND ( IFNULL(meta_c5.meta_value, '9999-12-31') > '{$date}' ) AND ( IFNULL(meta_c6.meta_value, '{$day_of_week}') = '{$day_of_week}' )) OR ( meta_c2.meta_value=0 AND meta_c3.meta_value='{$date}' ))
						AND ((meta_c1.meta_value IS NULL) OR (meta_c1.meta_value='') OR (meta_c1.meta_value-IFNULL(booking_info.adults, 0) > {$adults}) )";
        }

        // if wpml is enabled return current language posts
        if (defined('ICL_LANGUAGE_CODE') && ( ct_get_lang_count() > 1 ) && ( ct_get_default_language() != ICL_LANGUAGE_CODE ))
        {
            $sql = "SELECT it4.element_id AS tour_id FROM ({$sql}) AS t5
					INNER JOIN {$tbl_icl_translations} it3 ON (it3.element_type = 'post_tour') AND it3.element_id = t5.tour_id
					INNER JOIN {$tbl_icl_translations} it4 ON (it4.element_type = 'post_tour') AND it4.language_code='" . ICL_LANGUAGE_CODE . "' AND it4.trid = it3.trid";
        }

        // echo($sql);
        $wpdb->query("DROP TEMPORARY TABLE IF EXISTS {$temp_tbl_name}");
        $sql = "CREATE TEMPORARY TABLE {$temp_tbl_name} AS " . $sql;
        
        $wpdb->query($sql);

        $sql = " FROM {$temp_tbl_name} as t1
				INNER JOIN {$tbl_posts} post_s1 ON (t1.tour_id = post_s1.ID) AND (post_s1.post_status = 'publish') AND (post_s1.post_type = 'tour')";
        $where = ' WHERE 1=1';

        // tour_type filter
        if (!empty($tour_type) && trim(implode('', $tour_type)) != "")
        {
            $sql .= " INNER JOIN {$tbl_term_relationships} AS tr ON tr.object_id = post_s1.ID
					INNER JOIN {$tbl_term_taxonomy} AS tt ON tt.term_taxonomy_id = tr.term_taxonomy_id";
            $where .= " AND tt.taxonomy = 'tour_type' AND tt.term_id IN (" . esc_sql(implode(',', $tour_type)) . ")";
        }

        // tour_city filter
        if (!empty($tour_city) && trim(implode('', $tour_city)) != "")
        {
            $sql .= " INNER JOIN {$tbl_postmeta} AS postmeta ON post_s1.ID = postmeta.post_id AND (postmeta.meta_key ='_tour_city' AND postmeta.meta_value IN (" . esc_sql(implode(',', $tour_city)) . ") )";
            // $sql .= " INNER JOIN {$tbl_term_relationships} AS tr ON tr.object_id = post_s1.ID
			// 		INNER JOIN {$tbl_term_taxonomy} AS tt ON tt.term_taxonomy_id = tr.term_taxonomy_id";
            // $where .= " AND tt.taxonomy = 'tour_district' AND tt.term_id IN (" . esc_sql(implode(',', $tour_city)) . ")";
        }

        // price filter
        $sql .= " LEFT JOIN {$tbl_postmeta} AS meta_price ON post_s1.ID = meta_price.post_id AND meta_price.meta_key = '_tour_price'";
        if (!empty($price_filter) && trim(implode('', $price_filter)) != "")
        {
            $price_where = array();
            $price_steps = empty($ct_options['tour_price_filter_steps']) ? '50,80,100' : $ct_options['tour_price_filter_steps'];
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
					WHERE tt1.taxonomy = 'tour_facility' AND tt1.term_id IN (" . esc_sql(implode(',', $facility_filter)) . ") AND tr1.object_id = post_s1.ID ) = " . count($facility_filter) . ")";
        }

        $sql .= $where;
        $count_sql = "SELECT COUNT(DISTINCT t1.tour_id)" . $sql;
        $count = $wpdb->get_var($count_sql);

        if (!empty($order_by))
        {
            $sql .= " ORDER BY " . $order_by_array[$order_by] . " " . $order;
        }
        
        $sql .= " LIMIT {$last_no}, {$per_page};";
        $main_sql = "SELECT DISTINCT t1.tour_id AS tour_id" . $sql;
        
        $ids = $wpdb->get_results($main_sql, ARRAY_A);

        return array('count' => $count, 'ids' => $ids);
    }

}

/*
 * Get tours from ids
 */
if (!function_exists('ct_tour_get_tours_from_id'))
{

    function ct_tour_get_tours_from_id($ids)
    {
        if (!is_array($ids))
            return false;
        $results = array();
        foreach ($ids as $id)
        {
            $result = get_post($id);
            if (!empty($result) && !is_wp_error($result))
            {
                if ($result->post_type == 'tour')
                    $results[] = $result;
            }
        }
        return $results;
    }

}

/*
 * Get discounted(hot) tours and return data
 */
if (!function_exists('ct_tour_get_hot_tours'))
{

    function ct_tour_get_hot_tours($count = 10, $exclude_ids= array(), $tour_type = array())
    {
        $args = array(
            'post_type' => 'tour',
            'orderby' => 'modified',
            'order' => 'DESC',
            'posts_per_page' => $count,
            'suppress_filters' => 0,
            'post_status' => 'publish',
            'meta_query' => array(
                'relation' => 'AND',
                array(
                    'key' => '_tour_hot',
                    'value' => '1',
                ),
                array(
                    'key' => '_tour_discount_rate',
                    'value' => array(0, 100),
                    'type' => 'numeric',
                    'compare' => 'BETWEEN',
                )
            ),
        );

        if (!empty($tour_type))
        {
            if (is_numeric($tour_type[0]))
            {
                $args['tax_query'] = array(
                    array(
                        'taxonomy' => 'tour_type',
                        'field' => 'term_id',
                        'terms' => $tour_type
                    )
                );
            }
            else
            {
                $args['tax_query'] = array(
                    array(
                        'taxonomy' => 'tour_type',
                        'field' => 'name',
                        'terms' => $tour_type
                    )
                );
            }
        }
        return get_posts($args);
    }

}

/*
 * Get special( latest or featured ) tours and return data
 */
if (!function_exists('ct_tour_get_special_tours'))
{

    function ct_tour_get_special_tours($type = 'latest', $count = 6, $exclude_ids=array(), $tour_type = array())
    {
        $args = array(
            'post_type' => 'tour',
            'suppress_filters' => 0,
            'posts_per_page' => $count,
            'post_status' => 'publish',
        );

        if (!empty($exclude_ids))
        {
            $args['post__not_in'] = $exclude_ids;
        }

        if (!empty($tour_type))
        {
            if (is_numeric($tour_type[0]))
            {
                $args['tax_query'] = array(
                    array(
                        'taxonomy' => 'tour_type',
                        'field' => 'term_id',
                        'terms' => $tour_type
                    )
                );
            }
            else
            {
                $args['tax_query'] = array(
                    array(
                        'taxonomy' => 'tour_type',
                        'field' => 'name',
                        'terms' => $tour_type
                    )
                );
            }
        }

        if ($type == 'featured')
        {
            $args = array_merge($args, array(
                'orderby' => 'modified',
                'order' => 'DESC',
                'meta_key' => '_tour_featured',
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

            $sql = 'SELECT tour_id, SUM(booking.adults) AS booking_count FROM ' . CT_TOUR_BOOKINGS_TABLE . ' AS booking
					INNER JOIN ' . CT_ORDER_TABLE . ' as _order ON _order.id = booking.order_id';
            $where = ' WHERE (_order.status <> "cancelled") AND (_order.created > %s)';

            $tour_type_id = 0;
            if (!empty($tour_type)) {
                $tour_type_id = (int) (is_array($tour_type) ? reset($tour_type) : $tour_type);
            }
            if ($tour_type_id > 0)
            {
                $sql .= " INNER JOIN {$tbl_term_relationships} AS tr ON tr.object_id = booking.tour_id
						INNER JOIN {$tbl_term_taxonomy} AS tt ON tt.term_taxonomy_id = tr.term_taxonomy_id";
                $where .= " AND tt.taxonomy = 'tour_type' AND tt.term_id = %d";
                $sql .= $where . ' GROUP BY booking.tour_id ORDER BY booking_count DESC LIMIT %d';
                $sql = $wpdb->prepare($sql, $date, $tour_type_id, $count);
            }
            else
            {
                $sql .= $where . ' GROUP BY booking.tour_id ORDER BY booking_count DESC LIMIT %d';
                $sql = $wpdb->prepare($sql, $date, $count);
            }

            $popular_tour = $wpdb->get_results($sql);

            if (!is_array($popular_tour)) {
                $popular_tour = array();
            }
            $result = array();
            if (!empty($popular_tour))
            {
                foreach ($popular_tour as $tour)
                {
                    if ($tour_obj = get_post(ct_tour_clang_id($tour->tour_id)))
                    {
                        $result[] = $tour_obj;
                    }
                }
            }

            // if booked room number in last month is smaller than count then add latest tour
            if (count($result) < $count)
            {
                foreach ($result as $tour)
                {
                    $exclude_ids[] = $tour->ID;
                }
                $result = array_merge($result, ct_tour_get_special_tours('latest', $count - count($result), $exclude_ids, $tour_type));
            }

            return $result;
        }
    }

}


if (!function_exists('ct_get_tour_list'))
{

    function ct_get_tour_list()
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
        $date = isset($_REQUEST['date']) ? sanitize_text_field($_REQUEST['date']) : '';
        $adults = isset($_REQUEST['adults']) ? sanitize_text_field($_REQUEST['adults']) : '';
        $kids = isset($_REQUEST['kids']) ? sanitize_text_field($_REQUEST['kids']) : '';

        $order_by = ( isset($_REQUEST['order_by']) && array_key_exists($_REQUEST['order_by'], $order_by_array) ) ? sanitize_text_field($_REQUEST['order_by']) : '';
        $order = ( isset($_REQUEST['order']) && in_array($_REQUEST['order'], $order_array) ) ? sanitize_text_field($_REQUEST['order']) : 'ASC';
        $tour_type = ( isset($_REQUEST['tour_type']) ) ? ( is_array($_REQUEST['tour_type']) ? $_REQUEST['tour_type'] : array($_REQUEST['tour_type']) ) : array();
        $price_filter = ( isset($_REQUEST['price_filter']) && is_array($_REQUEST['price_filter']) ) ? $_REQUEST['price_filter'] : array();
        $rating_filter = ( isset($_REQUEST['rating_filter']) && is_array($_REQUEST['rating_filter']) ) ? $_REQUEST['rating_filter'] : array();
        $facility_filter = ( isset($_REQUEST['facilities']) && is_array($_REQUEST['facilities']) ) ? $_REQUEST['facilities'] : array();
        $tour_city = ( isset($_REQUEST['city']) ) ? ( is_array($_REQUEST['city']) ? $_REQUEST['city'] : array($_REQUEST['city']) ) : array();

        if (isset($_REQUEST['view']) && !empty($_REQUEST['view']))
        {
            $current_view = sanitize_text_field($_REQUEST['view']);
        }
        else if (isset($ct_options['tour_list_default_view']) && !empty($ct_options['tour_list_default_view']))
        {
            $current_view = $ct_options['tour_list_default_view'];
        }
        else
        {
            $current_view = 'list';
        }

        $page = ( isset($_REQUEST['page']) && ( is_numeric($_REQUEST['page']) ) && ( $_REQUEST['page'] >= 1 ) ) ? sanitize_text_field($_REQUEST['page']) : 1;
        $per_page = ( isset($ct_options['tour_posts']) && is_numeric($ct_options['tour_posts']) ) ? $ct_options['tour_posts'] : 6;
        $search_result = ct_tour_get_search_result(array('s' => $s, 'date' => $date, 'adults' => $adults, 'kids' => $kids, 'tour_type' => $tour_type, 'tour_city' => $tour_city, 'price_filter' => $price_filter, 'rating_filter' => $rating_filter, 'facility_filter' => $facility_filter, 'order_by' => $order_by_array[$order_by], 'order' => $order, 'last_no' => ( $page - 1 ) * $per_page, 'per_page' => $per_page));
        $post_list = $search_result['ids'];
        $count = $search_result['count']; // total_count

        ct_get_template('tour-list.php', '/templates/tour/');
        ?>

        <div class="text-center">

        <?php

        unset($_REQUEST['page']);
        unset($_REQUEST['action']);
        unset($_REQUEST['tour_type']);
        
        $_REQUEST['type'] = $tour_type[0];
        
        $pagenum_link = strtok(home_url('/tour/'), '?') . '%_%';
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
