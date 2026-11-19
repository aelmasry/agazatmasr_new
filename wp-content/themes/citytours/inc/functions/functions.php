<?php
if (!defined('ABSPATH'))
    exit; // Exit if accessed directly

    /*
      ct_get_date_format
      ct_sanitize_date
      ct_strtotime
      ct_get_phptime
     */

if (!function_exists('ct_get_date_format'))
{


    function ct_get_date_format($language = '')
    {
        global $ct_options;
        if (isset($ct_options['date_format']))
        {
            if ($language == 'php')
            {
                switch ($ct_options['date_format'])
                {
                    case 'dd/mm/yyyy':
                        return 'd/m/Y';
                        break;
                    case 'yyyy-mm-dd':
                        return 'Y-m-d';
                        break;
                    case 'mm/dd/yyyy':
                    default:
                        return 'm/d/Y';
                        break;
                }
            }
            else
            {
                return $ct_options['date_format'];
            }
        }
        else
        {
            if ($language == 'php')
            {
                return 'm/d/Y';
            }
            else
            {
                return 'mm/dd/yyyy';
            }
        }
    }

}

function ct_site_date_format()
{
    return apply_filters('ct_site_date_format', get_option('date_format'));
}

/*
 * get site date format
 */
if (!function_exists('ct_sanitize_date'))
{


    function ct_sanitize_date($input_date)
    {
        $date_obj = date_create_from_format(ct_get_date_format('php'), $input_date);
        if (!$date_obj)
        {
            return '';
        }
        return sanitize_text_field($input_date);
    }

}

/*
 * function to make it enable d/m/Y strtotime
 */
if (!function_exists('ct_strtotime'))
{
    function ct_strtotime($input_date)
    {
        if (ct_get_date_format('php') == 'd/m/Y')
        {
            $input_date = str_replace('/', '-', $input_date);
        }
        return strtotime($input_date);
    }
}

/*
 * function to make it enable d/m/Y strtotime
 */
if (!function_exists('ct_get_phptime'))
{
    function ct_get_phptime($input_date)
    {
        if (!ct_strtotime($input_date))
        {
            return '';
        }
        $return_value = date(ct_get_date_format('php'), ct_strtotime($input_date));
        return $return_value;
    }

}

/*
 * get day interval
 */
if (!function_exists('ct_get_day_interval'))
{
    function ct_get_day_interval($date_from, $date_to)
    {
        if (!empty($date_from)) {
            $date_from = new DateTime('@' . ct_strtotime($date_from));
        } else {
            $date_from = new DateTime(ct_strtotime($date_from));
        }
        
        if (!empty($date_to)) {
            $date_to = new DateTime('@' . ct_strtotime($date_to));
        } else {
            $date_to = new DateTime(ct_strtotime($date_to));
        }
        
        $interval = $date_from->diff($date_to);
        
        return $interval->d;
    }
}

/*
 * is hotel module enabled
 */
if (!function_exists('ct_is_hotel_enabled'))
{
    function ct_is_hotel_enabled()
    {
        return apply_filters('ct_is_hotel_enabled', in_array('hotel', ct_get_available_modules()));
    }
}

/*
 * is tour module enabled
 */
if (!function_exists('ct_is_tour_enabled'))
{
    function ct_is_tour_enabled()
    {
        return apply_filters('ct_is_tour_enabled', in_array('tour', ct_get_available_modules()));
    }
}

/*
 * one click install main pages
 */
if (!function_exists('ct_one_click_install_main_pages'))
{
    function ct_one_click_install_main_pages()
    {
        if (!empty($_GET['install_ct_pages']))
        {
            global $ct_options;

            $installed = get_option('install_ct_pages');

            if (empty($installed))
            {
                update_option('install_ct_pages', 1);

                if (empty($ct_options['wishlist']))
                {
                    $postarr = array(
                        'post_title' => 'Wishlist',
                        'post_type' => 'page',
                        'post_content' => '',
                        'post_status' => 'publish'
                    );
                    $ct_options['wishlist'] = '' . wp_insert_post($postarr);
                }
                if (empty($ct_options['hotel_cart_page']))
                {
                    $postarr = array(
                        'post_title' => 'Hotel Cart Page',
                        'post_type' => 'page',
                        'post_content' => '[hotel_cart]',
                        'post_status' => 'publish'
                    );
                    $ct_options['hotel_cart_page'] = '' . wp_insert_post($postarr);
                }
                if (empty($ct_options['hotel_checkout_page']))
                {
                    $postarr = array(
                        'post_title' => 'Hotel Checkout Page',
                        'post_type' => 'page',
                        'post_content' => '[hotel_checkout]',
                        'post_status' => 'publish'
                    );
                    $ct_options['hotel_checkout_page'] = '' . wp_insert_post($postarr);
                }
                if (empty($ct_options['hotel_thankyou_page']))
                {
                    $postarr = array(
                        'post_title' => 'Hotel Booking Confirmation Page',
                        'post_type' => 'page',
                        'post_content' => '[hotel_booking_confirmation]',
                        'post_status' => 'publish'
                    );
                    $ct_options['hotel_thankyou_page'] = '' . wp_insert_post($postarr);
                }
                if (empty($ct_options['tour_cart_page']))
                {
                    $postarr = array(
                        'post_title' => 'Tour Cart Page',
                        'post_type' => 'page',
                        'post_content' => '[tour_cart]',
                        'post_status' => 'publish'
                    );
                    $ct_options['tour_cart_page'] = '' . wp_insert_post($postarr);
                }
                if (empty($ct_options['tour_checkout_page']))
                {
                    $postarr = array(
                        'post_title' => 'Tour Checkout Page',
                        'post_type' => 'page',
                        'post_content' => '[tour_checkout]',
                        'post_status' => 'publish'
                    );
                    $ct_options['tour_checkout_page'] = '' . wp_insert_post($postarr);
                }
                if (empty($ct_options['tour_thankyou_page']))
                {
                    $postarr = array(
                        'post_title' => 'Tour Booking Confirmation Page',
                        'post_type' => 'page',
                        'post_content' => '[tour_booking_confirmation]',
                        'post_status' => 'publish'
                    );
                    $ct_options['tour_thankyou_page'] = '' . wp_insert_post($postarr);
                }

                update_option('citytours', $ct_options);

                wp_redirect(admin_url('admin.php?page=theme_options'));
                exit;
            }
        }

        // dismiss the notice if skip setup button is clicked
        if (!empty($_GET['skip_ct_pages']))
        {
            update_option('install_ct_pages', 1);
        }
    }

}

/*
 * get site additional services
 */
if (!function_exists('ct_get_add_services_by_postid'))
{


    function ct_get_add_services_by_postid($post_id = 0, $service_id = 0)
    {
        global $wpdb;

        $where = '1=1';
        if (!empty($service_id))
        {
            $where .= ' AND id=' . esc_sql($service_id);
        }
        if (!empty($post_id))
        {
            $where .= ' AND post_id=' . esc_sql($post_id);
        }

        $services = $wpdb->get_results('SELECT * FROM ' . CT_ADD_SERVICES_TABLE . ' WHERE ' . $where);

        return $services;
    }

}

/*
 * get site additional services
 */
if (!function_exists('ct_get_add_service'))
{


    function ct_get_add_service($service_id)
    {
        global $wpdb;

        $services = $wpdb->get_row($wpdb->prepare('SELECT * FROM ' . CT_ADD_SERVICES_TABLE . ' WHERE id=%d', $service_id));

        return $services;
    }

}

/*
 * set cart
 */
if (!function_exists('ct_set_cart'))
{


    function ct_set_cart($uid, $data)
    {
        if (empty($_SESSION['cart']))
            $_SESSION['cart'] = array();
        $_SESSION['cart'][$uid] = $data;
    }

}

/*
 * get cart
 */
if (!function_exists('ct_get_cart'))
{


    function ct_get_cart($uid)
    {
        if (empty($_SESSION['cart']))
            $_SESSION['cart'] = array();
        if (!empty($_SESSION['cart'][$uid]))
            return $_SESSION['cart'][$uid];
        return false;
    }

}

/*
 * get cart room fields
 */
if (!function_exists('ct_get_cart_room_field'))
{


    function ct_get_cart_room_field($uid, $room_id, $field = 'quantity')
    {
        $cart = ct_get_cart($uid);
        if ($cart && !empty($cart['room']) && !empty($cart['room'][$room_id]) && !empty($cart['room'][$room_id][$field]))
            return $cart['room'][$room_id][$field];
        return 0;
    }

}

/*
 * get site date format
 */
if (!function_exists('ct_get_cart_field'))
{


    function ct_get_cart_field($uid, $field = 'total_price')
    {
        $cart = ct_get_cart($uid);
        if ($cart && !empty($cart[$field]))
            return $cart[$field];
        return 0;
    }

}

/*
 * get all countries
 */
if (!function_exists('ct_get_all_countries'))
{


    function ct_get_all_countries()
    {
        $countries = array(
            array("code" => "US", "name" => "United States", "d_code" => "+1"),
            array("code" => "GB", "name" => "United Kingdom", "d_code" => "+44"),
            array("code" => "CA", "name" => "Canada", "d_code" => "+1"),
            array("code" => "AF", "name" => "Afghanistan", "d_code" => "+93"),
            array("code" => "AL", "name" => "Albania", "d_code" => "+355"),
            array("code" => "DZ", "name" => "Algeria", "d_code" => "+213"),
            array("code" => "AS", "name" => "American Samoa", "d_code" => "+1"),
            array("code" => "AD", "name" => "Andorra", "d_code" => "+376"),
            array("code" => "AO", "name" => "Angola", "d_code" => "+244"),
            array("code" => "AI", "name" => "Anguilla", "d_code" => "+1"),
            array("code" => "AG", "name" => "Antigua", "d_code" => "+1"),
            array("code" => "AR", "name" => "Argentina", "d_code" => "+54"),
            array("code" => "AM", "name" => "Armenia", "d_code" => "+374"),
            array("code" => "AW", "name" => "Aruba", "d_code" => "+297"),
            array("code" => "AU", "name" => "Australia", "d_code" => "+61"),
            array("code" => "AT", "name" => "Austria", "d_code" => "+43"),
            array("code" => "AZ", "name" => "Azerbaijan", "d_code" => "+994"),
            array("code" => "BH", "name" => "Bahrain", "d_code" => "+973"),
            array("code" => "BD", "name" => "Bangladesh", "d_code" => "+880"),
            array("code" => "BB", "name" => "Barbados", "d_code" => "+1"),
            array("code" => "BY", "name" => "Belarus", "d_code" => "+375"),
            array("code" => "BE", "name" => "Belgium", "d_code" => "+32"),
            array("code" => "BZ", "name" => "Belize", "d_code" => "+501"),
            array("code" => "BJ", "name" => "Benin", "d_code" => "+229"),
            array("code" => "BM", "name" => "Bermuda", "d_code" => "+1"),
            array("code" => "BT", "name" => "Bhutan", "d_code" => "+975"),
            array("code" => "BO", "name" => "Bolivia", "d_code" => "+591"),
            array("code" => "BA", "name" => "Bosnia and Herzegovina", "d_code" => "+387"),
            array("code" => "BW", "name" => "Botswana", "d_code" => "+267"),
            array("code" => "BR", "name" => "Brazil", "d_code" => "+55"),
            array("code" => "IO", "name" => "British Indian Ocean Territory", "d_code" => "+246"),
            array("code" => "VG", "name" => "British Virgin Islands", "d_code" => "+1"),
            array("code" => "BN", "name" => "Brunei", "d_code" => "+673"),
            array("code" => "BG", "name" => "Bulgaria", "d_code" => "+359"),
            array("code" => "BF", "name" => "Burkina Faso", "d_code" => "+226"),
            array("code" => "MM", "name" => "Burma Myanmar", "d_code" => "+95"),
            array("code" => "BI", "name" => "Burundi", "d_code" => "+257"),
            array("code" => "KH", "name" => "Cambodia", "d_code" => "+855"),
            array("code" => "CM", "name" => "Cameroon", "d_code" => "+237"),
            array("code" => "CV", "name" => "Cape Verde", "d_code" => "+238"),
            array("code" => "KY", "name" => "Cayman Islands", "d_code" => "+1"),
            array("code" => "CF", "name" => "Central African Republic", "d_code" => "+236"),
            array("code" => "TD", "name" => "Chad", "d_code" => "+235"),
            array("code" => "CL", "name" => "Chile", "d_code" => "+56"),
            array("code" => "CN", "name" => "China", "d_code" => "+86"),
            array("code" => "CO", "name" => "Colombia", "d_code" => "+57"),
            array("code" => "KM", "name" => "Comoros", "d_code" => "+269"),
            array("code" => "CK", "name" => "Cook Islands", "d_code" => "+682"),
            array("code" => "CR", "name" => "Costa Rica", "d_code" => "+506"),
            array("code" => "CI", "name" => "Cote d'Ivoire", "d_code" => "+225"),
            array("code" => "HR", "name" => "Croatia", "d_code" => "+385"),
            array("code" => "CU", "name" => "Cuba", "d_code" => "+53"),
            array("code" => "CY", "name" => "Cyprus", "d_code" => "+357"),
            array("code" => "CZ", "name" => "Czech Republic", "d_code" => "+420"),
            array("code" => "CD", "name" => "Democratic Republic of Congo", "d_code" => "+243"),
            array("code" => "DK", "name" => "Denmark", "d_code" => "+45"),
            array("code" => "DJ", "name" => "Djibouti", "d_code" => "+253"),
            array("code" => "DM", "name" => "Dominica", "d_code" => "+1"),
            array("code" => "DO", "name" => "Dominican Republic", "d_code" => "+1"),
            array("code" => "EC", "name" => "Ecuador", "d_code" => "+593"),
            array("code" => "EG", "name" => "Egypt", "d_code" => "+20"),
            array("code" => "SV", "name" => "El Salvador", "d_code" => "+503"),
            array("code" => "GQ", "name" => "Equatorial Guinea", "d_code" => "+240"),
            array("code" => "ER", "name" => "Eritrea", "d_code" => "+291"),
            array("code" => "EE", "name" => "Estonia", "d_code" => "+372"),
            array("code" => "ET", "name" => "Ethiopia", "d_code" => "+251"),
            array("code" => "FK", "name" => "Falkland Islands", "d_code" => "+500"),
            array("code" => "FO", "name" => "Faroe Islands", "d_code" => "+298"),
            array("code" => "FM", "name" => "Federated States of Micronesia", "d_code" => "+691"),
            array("code" => "FJ", "name" => "Fiji", "d_code" => "+679"),
            array("code" => "FI", "name" => "Finland", "d_code" => "+358"),
            array("code" => "FR", "name" => "France", "d_code" => "+33"),
            array("code" => "GF", "name" => "French Guiana", "d_code" => "+594"),
            array("code" => "PF", "name" => "French Polynesia", "d_code" => "+689"),
            array("code" => "GA", "name" => "Gabon", "d_code" => "+241"),
            array("code" => "GE", "name" => "Georgia", "d_code" => "+995"),
            array("code" => "DE", "name" => "Germany", "d_code" => "+49"),
            array("code" => "GH", "name" => "Ghana", "d_code" => "+233"),
            array("code" => "GI", "name" => "Gibraltar", "d_code" => "+350"),
            array("code" => "GR", "name" => "Greece", "d_code" => "+30"),
            array("code" => "GL", "name" => "Greenland", "d_code" => "+299"),
            array("code" => "GD", "name" => "Grenada", "d_code" => "+1"),
            array("code" => "GP", "name" => "Guadeloupe", "d_code" => "+590"),
            array("code" => "GU", "name" => "Guam", "d_code" => "+1"),
            array("code" => "GT", "name" => "Guatemala", "d_code" => "+502"),
            array("code" => "GN", "name" => "Guinea", "d_code" => "+224"),
            array("code" => "GW", "name" => "Guinea-Bissau", "d_code" => "+245"),
            array("code" => "GY", "name" => "Guyana", "d_code" => "+592"),
            array("code" => "HT", "name" => "Haiti", "d_code" => "+509"),
            array("code" => "HN", "name" => "Honduras", "d_code" => "+504"),
            array("code" => "HK", "name" => "Hong Kong", "d_code" => "+852"),
            array("code" => "HU", "name" => "Hungary", "d_code" => "+36"),
            array("code" => "IS", "name" => "Iceland", "d_code" => "+354"),
            array("code" => "IN", "name" => "India", "d_code" => "+91"),
            array("code" => "ID", "name" => "Indonesia", "d_code" => "+62"),
            array("code" => "IR", "name" => "Iran", "d_code" => "+98"),
            array("code" => "IQ", "name" => "Iraq", "d_code" => "+964"),
            array("code" => "IE", "name" => "Ireland", "d_code" => "+353"),
            array("code" => "IL", "name" => "Israel", "d_code" => "+972"),
            array("code" => "IT", "name" => "Italy", "d_code" => "+39"),
            array("code" => "JM", "name" => "Jamaica", "d_code" => "+1"),
            array("code" => "JP", "name" => "Japan", "d_code" => "+81"),
            array("code" => "JO", "name" => "Jordan", "d_code" => "+962"),
            array("code" => "KZ", "name" => "Kazakhstan", "d_code" => "+7"),
            array("code" => "KE", "name" => "Kenya", "d_code" => "+254"),
            array("code" => "KI", "name" => "Kiribati", "d_code" => "+686"),
            array("code" => "XK", "name" => "Kosovo", "d_code" => "+381"),
            array("code" => "KW", "name" => "Kuwait", "d_code" => "+965"),
            array("code" => "KG", "name" => "Kyrgyzstan", "d_code" => "+996"),
            array("code" => "LA", "name" => "Laos", "d_code" => "+856"),
            array("code" => "LV", "name" => "Latvia", "d_code" => "+371"),
            array("code" => "LB", "name" => "Lebanon", "d_code" => "+961"),
            array("code" => "LS", "name" => "Lesotho", "d_code" => "+266"),
            array("code" => "LR", "name" => "Liberia", "d_code" => "+231"),
            array("code" => "LY", "name" => "Libya", "d_code" => "+218"),
            array("code" => "LI", "name" => "Liechtenstein", "d_code" => "+423"),
            array("code" => "LT", "name" => "Lithuania", "d_code" => "+370"),
            array("code" => "LU", "name" => "Luxembourg", "d_code" => "+352"),
            array("code" => "MO", "name" => "Macau", "d_code" => "+853"),
            array("code" => "MK", "name" => "Macedonia", "d_code" => "+389"),
            array("code" => "MG", "name" => "Madagascar", "d_code" => "+261"),
            array("code" => "MW", "name" => "Malawi", "d_code" => "+265"),
            array("code" => "MY", "name" => "Malaysia", "d_code" => "+60"),
            array("code" => "MV", "name" => "Maldives", "d_code" => "+960"),
            array("code" => "ML", "name" => "Mali", "d_code" => "+223"),
            array("code" => "MT", "name" => "Malta", "d_code" => "+356"),
            array("code" => "MH", "name" => "Marshall Islands", "d_code" => "+692"),
            array("code" => "MQ", "name" => "Martinique", "d_code" => "+596"),
            array("code" => "MR", "name" => "Mauritania", "d_code" => "+222"),
            array("code" => "MU", "name" => "Mauritius", "d_code" => "+230"),
            array("code" => "YT", "name" => "Mayotte", "d_code" => "+262"),
            array("code" => "MX", "name" => "Mexico", "d_code" => "+52"),
            array("code" => "MD", "name" => "Moldova", "d_code" => "+373"),
            array("code" => "MC", "name" => "Monaco", "d_code" => "+377"),
            array("code" => "MN", "name" => "Mongolia", "d_code" => "+976"),
            array("code" => "ME", "name" => "Montenegro", "d_code" => "+382"),
            array("code" => "MS", "name" => "Montserrat", "d_code" => "+1"),
            array("code" => "MA", "name" => "Morocco", "d_code" => "+212"),
            array("code" => "MZ", "name" => "Mozambique", "d_code" => "+258"),
            array("code" => "NA", "name" => "Namibia", "d_code" => "+264"),
            array("code" => "NR", "name" => "Nauru", "d_code" => "+674"),
            array("code" => "NP", "name" => "Nepal", "d_code" => "+977"),
            array("code" => "NL", "name" => "Netherlands", "d_code" => "+31"),
            array("code" => "AN", "name" => "Netherlands Antilles", "d_code" => "+599"),
            array("code" => "NC", "name" => "New Caledonia", "d_code" => "+687"),
            array("code" => "NZ", "name" => "New Zealand", "d_code" => "+64"),
            array("code" => "NI", "name" => "Nicaragua", "d_code" => "+505"),
            array("code" => "NE", "name" => "Niger", "d_code" => "+227"),
            array("code" => "NG", "name" => "Nigeria", "d_code" => "+234"),
            array("code" => "NU", "name" => "Niue", "d_code" => "+683"),
            array("code" => "NF", "name" => "Norfolk Island", "d_code" => "+672"),
            array("code" => "KP", "name" => "North Korea", "d_code" => "+850"),
            array("code" => "MP", "name" => "Northern Mariana Islands", "d_code" => "+1"),
            array("code" => "NO", "name" => "Norway", "d_code" => "+47"),
            array("code" => "OM", "name" => "Oman", "d_code" => "+968"),
            array("code" => "PK", "name" => "Pakistan", "d_code" => "+92"),
            array("code" => "PW", "name" => "Palau", "d_code" => "+680"),
            array("code" => "PS", "name" => "Palestine", "d_code" => "+970"),
            array("code" => "PA", "name" => "Panama", "d_code" => "+507"),
            array("code" => "PG", "name" => "Papua New Guinea", "d_code" => "+675"),
            array("code" => "PY", "name" => "Paraguay", "d_code" => "+595"),
            array("code" => "PE", "name" => "Peru", "d_code" => "+51"),
            array("code" => "PH", "name" => "Philippines", "d_code" => "+63"),
            array("code" => "PL", "name" => "Poland", "d_code" => "+48"),
            array("code" => "PT", "name" => "Portugal", "d_code" => "+351"),
            array("code" => "PR", "name" => "Puerto Rico", "d_code" => "+1"),
            array("code" => "QA", "name" => "Qatar", "d_code" => "+974"),
            array("code" => "CG", "name" => "Republic of the Congo", "d_code" => "+242"),
            array("code" => "RE", "name" => "Reunion", "d_code" => "+262"),
            array("code" => "RO", "name" => "Romania", "d_code" => "+40"),
            array("code" => "RU", "name" => "Russia", "d_code" => "+7"),
            array("code" => "RW", "name" => "Rwanda", "d_code" => "+250"),
            array("code" => "BL", "name" => "Saint Barthelemy", "d_code" => "+590"),
            array("code" => "SH", "name" => "Saint Helena", "d_code" => "+290"),
            array("code" => "KN", "name" => "Saint Kitts and Nevis", "d_code" => "+1"),
            array("code" => "MF", "name" => "Saint Martin", "d_code" => "+590"),
            array("code" => "PM", "name" => "Saint Pierre and Miquelon", "d_code" => "+508"),
            array("code" => "VC", "name" => "Saint Vincent and the Grenadines", "d_code" => "+1"),
            array("code" => "WS", "name" => "Samoa", "d_code" => "+685"),
            array("code" => "SM", "name" => "San Marino", "d_code" => "+378"),
            array("code" => "ST", "name" => "Sao Tome and Principe", "d_code" => "+239"),
            array("code" => "SA", "name" => "Saudi Arabia", "d_code" => "+966"),
            array("code" => "SN", "name" => "Senegal", "d_code" => "+221"),
            array("code" => "RS", "name" => "Serbia", "d_code" => "+381"),
            array("code" => "SC", "name" => "Seychelles", "d_code" => "+248"),
            array("code" => "SL", "name" => "Sierra Leone", "d_code" => "+232"),
            array("code" => "SG", "name" => "Singapore", "d_code" => "+65"),
            array("code" => "SK", "name" => "Slovakia", "d_code" => "+421"),
            array("code" => "SI", "name" => "Slovenia", "d_code" => "+386"),
            array("code" => "SB", "name" => "Solomon Islands", "d_code" => "+677"),
            array("code" => "SO", "name" => "Somalia", "d_code" => "+252"),
            array("code" => "ZA", "name" => "South Africa", "d_code" => "+27"),
            array("code" => "KR", "name" => "South Korea", "d_code" => "+82"),
            array("code" => "ES", "name" => "Spain", "d_code" => "+34"),
            array("code" => "LK", "name" => "Sri Lanka", "d_code" => "+94"),
            array("code" => "LC", "name" => "St. Lucia", "d_code" => "+1"),
            array("code" => "SD", "name" => "Sudan", "d_code" => "+249"),
            array("code" => "SR", "name" => "Suriname", "d_code" => "+597"),
            array("code" => "SZ", "name" => "Swaziland", "d_code" => "+268"),
            array("code" => "SE", "name" => "Sweden", "d_code" => "+46"),
            array("code" => "CH", "name" => "Switzerland", "d_code" => "+41"),
            array("code" => "SY", "name" => "Syria", "d_code" => "+963"),
            array("code" => "TW", "name" => "Taiwan", "d_code" => "+886"),
            array("code" => "TJ", "name" => "Tajikistan", "d_code" => "+992"),
            array("code" => "TZ", "name" => "Tanzania", "d_code" => "+255"),
            array("code" => "TH", "name" => "Thailand", "d_code" => "+66"),
            array("code" => "BS", "name" => "The Bahamas", "d_code" => "+1"),
            array("code" => "GM", "name" => "The Gambia", "d_code" => "+220"),
            array("code" => "TL", "name" => "Timor-Leste", "d_code" => "+670"),
            array("code" => "TG", "name" => "Togo", "d_code" => "+228"),
            array("code" => "TK", "name" => "Tokelau", "d_code" => "+690"),
            array("code" => "TO", "name" => "Tonga", "d_code" => "+676"),
            array("code" => "TT", "name" => "Trinidad and Tobago", "d_code" => "+1"),
            array("code" => "TN", "name" => "Tunisia", "d_code" => "+216"),
            array("code" => "TR", "name" => "Turkey", "d_code" => "+90"),
            array("code" => "TM", "name" => "Turkmenistan", "d_code" => "+993"),
            array("code" => "TC", "name" => "Turks and Caicos Islands", "d_code" => "+1"),
            array("code" => "TV", "name" => "Tuvalu", "d_code" => "+688"),
            array("code" => "UG", "name" => "Uganda", "d_code" => "+256"),
            array("code" => "UA", "name" => "Ukraine", "d_code" => "+380"),
            array("code" => "AE", "name" => "United Arab Emirates", "d_code" => "+971"),
            array("code" => "UY", "name" => "Uruguay", "d_code" => "+598"),
            array("code" => "VI", "name" => "US Virgin Islands", "d_code" => "+1"),
            array("code" => "UZ", "name" => "Uzbekistan", "d_code" => "+998"),
            array("code" => "VU", "name" => "Vanuatu", "d_code" => "+678"),
            array("code" => "VA", "name" => "Vatican City", "d_code" => "+39"),
            array("code" => "VE", "name" => "Venezuela", "d_code" => "+58"),
            array("code" => "VN", "name" => "Vietnam", "d_code" => "+84"),
            array("code" => "WF", "name" => "Wallis and Futuna", "d_code" => "+681"),
            array("code" => "YE", "name" => "Yemen", "d_code" => "+967"),
            array("code" => "ZM", "name" => "Zambia", "d_code" => "+260"),
            array("code" => "ZW", "name" => "Zimbabwe", "d_code" => "+263"),
        );
        return $countries;
    }

}

/*
 * Get current user info
 */
if (!function_exists('ct_get_current_user_info'))
{


    function ct_get_current_user_info()
    {
        $user_info = array(
            'display_name' => '',
            'first_name' => '',
            'last_name' => '',
            'email' => '',
            'country_code' => '',
            'phone' => '',
            'birthday' => '',
            'address1' => '',
            'address2' => '',
            'city' => '',
            'state' => '',
            'zip' => '',
            'country' => '',
            'photo_url' => '',
        );
        if (is_user_logged_in())
        {
            $current_user = wp_get_current_user();
            $user_id = $current_user->ID;
            $user_info['display_name'] = $current_user->user_firstname;
            $user_info['login'] = $current_user->user_login;
            $user_info['first_name'] = $current_user->user_firstname;
            $user_info['last_name'] = $current_user->user_lastname;
            $user_info['email'] = $current_user->user_email;
            $user_info['description'] = $current_user->description;
            $user_info['country_code'] = get_user_meta($user_id, 'country_code', true);
            $user_info['phone'] = get_user_meta($user_id, 'phone', true);
            $user_info['birthday'] = get_user_meta($user_id, 'birthday', true);
            $user_info['address1'] = get_user_meta($user_id, 'address1', true);
            $user_info['address2'] = get_user_meta($user_id, 'address2', true);
            $user_info['city'] = get_user_meta($user_id, 'city', true);
            $user_info['state'] = get_user_meta($user_id, 'state', true);
            $user_info['zip'] = get_user_meta($user_id, 'zip', true);
            $user_info['country'] = get_user_meta($user_id, 'country', true);
            $user_info['photo_url'] = ( isset($current_user->photo_url) && !empty($current_user->photo_url) ) ? $current_user->photo_url : '';
        }
        return $user_info;
    }

}

/*
 * send mail with icalendar functions
 */
if (!function_exists('ct_send_ical_event'))
{


    function ct_send_ical_event($from_name, $from_address, $to_name, $to_address, $startTime, $endTime, $subject, $description, $location)
    {
        $domain = $from_name;
        //Create Email Headers
        $mime_boundary = "----Meeting Booking----" . MD5(TIME());

        $headers = "From: " . $from_name . " <" . $from_address . ">\n";
        $headers .= "Reply-To: " . $from_name . " <" . $from_address . ">\n";
        $headers .= "MIME-Version: 1.0\n";
        $headers .= "Content-Type: multipart/alternative; boundary=\"$mime_boundary\"\n";
        $headers .= "Content-class: urn:content-classes:calendarmessage\n";

        //Create Email Body (HTML)
        $message = "--$mime_boundary\r\n";
        $message .= "Content-Type: text/html; charset=UTF-8\n";
        $message .= "Content-Transfer-Encoding: 8bit\n\n";
        $message .= "<html>\n";
        $message .= "<body>\n";
        $message .= $description;
        $message .= "</body>\n";
        $message .= "</html>\n";
        $message .= "--$mime_boundary\r\n";

        $ical = 'BEGIN:VCALENDAR' . "\r\n" .
                'PRODID:-//Microsoft Corporation//Outlook 10.0 MIMEDIR//EN' . "\r\n" .
                'VERSION:2.0' . "\r\n" .
                'METHOD:REQUEST' . "\r\n" .
                'BEGIN:VEVENT' . "\r\n" .
                'ORGANIZER;CN="' . $from_name . '":MAILTO:' . $from_address . "\r\n" .
                'ATTENDEE;CN="' . $to_name . '";ROLE=REQ-PARTICIPANT;RSVP=TRUE:MAILTO:' . $to_address . "\r\n" .
                'LAST-MODIFIED:' . date("Ymd\TGis") . "\r\n" .
                'UID:' . date("Ymd\TGis", ct_strtotime($startTime)) . rand() . "@" . $domain . "\r\n" .
                'DTSTAMP:' . date("Ymd\TGis") . "\r\n" .
                'DTSTART;TZID="Eastern Time":' . date("Ymd\THis", ct_strtotime($startTime)) . "\r\n" .
                'DTEND;TZID="Eastern Time":' . date("Ymd\THis", ct_strtotime($endTime)) . "\r\n" .
                'TRANSP:OPAQUE' . "\r\n" .
                'SEQUENCE:1' . "\r\n" .
                'SUMMARY:' . $subject . "\r\n" .
                'LOCATION:' . $location . "\r\n" .
                'CLASS:PUBLIC' . "\r\n" .
                'PRIORITY:5' . "\r\n" .
                'BEGIN:VALARM' . "\r\n" .
                'TRIGGER:-PT15M' . "\r\n" .
                'ACTION:DISPLAY' . "\r\n" .
                'DESCRIPTION:Reminder' . "\r\n" .
                'END:VALARM' . "\r\n" .
                'END:VEVENT' . "\r\n" .
                'END:VCALENDAR' . "\r\n";
        $message .= 'Content-Type: text/calendar;name="meeting.ics";method=REQUEST\n';
        $message .= "Content-Transfer-Encoding: 8bit\n\n";
        $message .= $ical;

        $mailsent = wp_mail($to_address, $subject, $message, $headers);
        return ($mailsent) ? (true) : (false);
    }

}

/*
 * send mail functions
 */
if (!function_exists('ct_send_mail'))
{


    function ct_send_mail($from_name, $from_address, $to_address, $subject, $description)
    {
        try {
            //Create Email Headers
            $headers = "MIME-Version: 1.0" . "\r\n";
            $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
            $headers .= "From: " . $from_name . " <" . $from_address . ">\n";
            $headers .= "Reply-To: " . $from_name . " <" . $from_address . ">\n";
            $message = "<html>\n";
            $message .= "<body>\n";
            $message .= $description;
            $message .= "</body>\n";
            $message .= "</html>\n";
            $mailsent = wp_mail($to_address, $subject, $message, $headers);
            
            if (!$mailsent) {
                error_log("Failed to send email to $to_address");
            }
    
            return true;
        } catch (Exception $e) {
            error_log("Error in ct_send_mail: " . $e->getMessage());
            return false;
        }
    }

}

/*
 * template chooser
 */
if (!function_exists('ct_template_chooser'))
{


    function ct_template_chooser($template)
    {
        global $post, $ct_options;

        if ((!empty($ct_options['hotel_invoice_page']) && (!empty($post) ) && ( $ct_options['hotel_invoice_page'] == $post->ID || $ct_options['hotel_invoice_page'] == ct_post_org_id($post->ID) ) ) ||
                (!empty($ct_options['tour_invoice_page']) && (!empty($post) ) && ( $ct_options['tour_invoice_page'] == $post->ID || $ct_options['tour_invoice_page'] == ct_post_org_id($post->ID) ) ))
        {
            $new_template = locate_template(array('templates/invoice.php'));

            if ('' != $new_template)
            {
                return $new_template;
            }
        }

        if (!empty($ct_options['wishlist']) && (!empty($post) ) && ( $ct_options['wishlist'] == $post->ID || $ct_options['wishlist'] == ct_post_org_id($post->ID) ))
        {
            $new_template = locate_template(array('templates/wishlist.php'));

            if ('' != $new_template)
            {
                return $new_template;
            }
        }

        $post_type = get_query_var('post_type');
        if (is_search() && $post_type == 'hotel')
        {
            // return locate_template( 'templates/accommodation/search-accommodation.php' );
            return locate_template('archive-hotel.php');
        }
        elseif (is_search() && $post_type == 'tour')
        {
            // return locate_template( 'templates/tour/search-tour.php' );
            return locate_template('archive-tour.php');
        }

        return $template;
    }

}

/*
 * Same trip type is stored once under each country, so the search list repeats the name.
 */
if (!function_exists('ct_tour_type_name_key')) {
    function ct_tour_type_name_key($name) {
        $name = trim(preg_replace('/\s+/u', ' ', wp_strip_all_tags((string) $name)));
        if ($name === '') {
            return '';
        }
        $parts = explode(' ', $name);
        foreach ($parts as $index => $part) {
            $parts[$index] = preg_replace('/^ال/u', '', $part);
        }
        return implode(' ', $parts);
    }
}

if (!function_exists('ct_unique_tour_types_by_name')) {
    function ct_unique_tour_types_by_name($terms) {
        if (is_wp_error($terms) || empty($terms) || !is_array($terms)) {
            return array();
        }
        $seen = array();
        $unique = array();
        foreach ($terms as $term) {
            if (!is_object($term) || empty($term->name)) {
                continue;
            }
            $key = ct_tour_type_name_key($term->name);
            if ($key === '' || isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;
            $unique[] = $term;
        }
        return $unique;
    }
}

if (!function_exists('ct_expand_tour_type_ids')) {
    function ct_expand_tour_type_ids($term_ids, $parent_ids = array()) {
        $parent_ids = array_values(array_filter(array_map('absint', (array) $parent_ids)));
        $terms = get_terms(array(
            'taxonomy' => 'tour_type',
            'hide_empty' => false,
        ));
        if (is_wp_error($terms) || empty($terms)) {
            return array_values(array_filter(array_map('absint', (array) $term_ids)));
        }

        $expanded = array();
        foreach ((array) $term_ids as $term_id) {
            $term_id = absint($term_id);
            if (!$term_id) {
                continue;
            }
            $term = get_term($term_id, 'tour_type');
            if (!$term || is_wp_error($term)) {
                $expanded[] = $term_id;
                continue;
            }
            $key = ct_tour_type_name_key($term->name);
            foreach ($terms as $candidate) {
                if (ct_tour_type_name_key($candidate->name) !== $key) {
                    continue;
                }
                if ($parent_ids && !in_array((int) $candidate->parent, $parent_ids, true)) {
                    continue;
                }
                $expanded[] = (int) $candidate->term_id;
            }
        }

        $expanded = array_values(array_unique(array_filter($expanded)));
        return $expanded ? $expanded : array_values(array_filter(array_map('absint', (array) $term_ids)));
    }
}

/*
 * get review count for hotel and tour
 */
if (!function_exists('ct_get_review_count'))
{


    function ct_get_review_count($post_id)
    {
        global $wpdb;

        $post_id = ct_post_org_id($post_id);
        $sql = "SELECT count(*) FROM " . CT_REVIEWS_TABLE . " WHERE post_id='" . esc_sql($post_id) . "' AND status='approved'";
        $result = $wpdb->get_var($sql);

        return $result;
    }

}

/*
 * echo review rating smiles
 */
if (!function_exists('ct_rating_smiles'))
{


    function ct_rating_smiles($review, $unvoted_class = "icon-smile", $voted_class = "")
    {
        $review = round($review);
        $voted_class = empty($voted_class) ? $unvoted_class . ' voted' : $voted_class;
        for ($i = 1; $i <= 5; $i++)
        {
            $class = ( $i <= $review ) ? $voted_class : $unvoted_class;
            echo '<i class="' . $class . '"></i>';
        }
    }

}

/*
 * get post reviews from post_id
 */
if (!function_exists('ct_get_reviews'))
{


    function ct_get_reviews($post_id, $start_num = 0, $per_page = 10)
    {
        global $wpdb;
        $post_id = ct_post_org_id($post_id);
        $sql = "SELECT * FROM " . CT_REVIEWS_TABLE . " WHERE post_id='" . esc_sql($post_id) . "' AND status='approved' ORDER BY id DESC LIMIT " . esc_sql($start_num) . ", " . esc_sql($per_page);
        $results = $wpdb->get_results($sql, ARRAY_A);
        return $results;
    }

}

/*
 * get post review html from post_id
 */
if (!function_exists('ct_get_review_html'))
{


    function ct_get_review_html($post_id, $start_num = 0, $per_page = 10)
    {
        $reviews = ct_get_reviews($post_id, $start_num, $per_page);
        $html = '';

        if (!empty($reviews))
        {
            ob_start();

            foreach ($reviews as $review)
            {
                $photo = ct_get_avatar(array(
                    'id' => $review['user_id'],
                    'email' => $review['reviewer_email'],
                    'size' => 76,
                    'class' => 'img-circle'
                ));
                ?>

                <div class="review_strip_single guest-review">
                    <?php echo ct_get_avatar(array('id' => $review['user_id'], 'email' => $review['reviewer_email'], 'size' => 76, 'class' => 'img-circle')); ?>
                    <small> - <?php echo date("M, d, Y", ct_strtotime($review['date'])); ?> -</small>
                    <h4><?php echo esc_html($review['reviewer_name']); ?></h4>
                    <p><?php echo esc_html(stripslashes($review['review_text'])) ?></p>
                    <div class="rating"><?php ct_rating_smiles($review['review_rating']) ?></div>
                </div>

                <?php
            }

            $html = ob_get_clean();
        }

        return array('html' => $html, 'count' => count($reviews));
    }

}

/*
 * Handle get more reviews ajax request
 */
if (!function_exists('ct_ajax_get_more_reviews'))
{


    function ct_ajax_get_more_reviews()
    {
        $result = array(
            'success' => 0,
            'html' => '',
            'more_reviews' => 0,
            'notice' => __('No More Reviews', 'citytours')
        );

        if (empty($_POST['post_id']) || empty($_POST['last_no']))
        {
            wp_send_json($result);
            exit;
        }

        $post_id = sanitize_text_field($_POST['post_id']);
        $last_no = sanitize_text_field($_POST['last_no']);
        $per_page = 10;
        $review_html = ct_get_review_html($post_id, $last_no, $per_page);

        if (!empty($review_html['html']))
        {
            $result['success'] = 1;
            $result['html'] = $review_html['html'];

            if ($review_html['count'] == $per_page)
            {
                $result['more_reviews'] = 1;
            }
        }

        wp_send_json($result);
        exit;
    }

}

/*
 * Handle submit reviews ajax request
 */
if (!function_exists('ct_ajax_submit_review'))
{


    function ct_ajax_submit_review()
    {
        global $wpdb;
        $result_json = array('success' => 0, 'result' => '', 'title' => '');
        if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'post-' . $_POST['post_id']))
        {
            $result_json['success'] = 0;
            $result_json['result'] = esc_html__('Sorry, your nonce did not verify.', 'citytours');
            wp_send_json($result_json);
        }

        $fields = array('post_id', 'booking_no', 'pin_code', 'review_text', 'review_rating');

        //validation
        $data = array();
        foreach ($fields as $field)
        {
            $data[$field] = ( isset($_POST[$field]) ) ? sanitize_text_field($_POST[$field]) : '';
        }

        $order = new CT_Hotel_Order($data['booking_no'], $data['pin_code']);
        if (!$order_data = $order->get_order_info())
        {
            $result_json['success'] = 0;
            $result_json['result'] = esc_html__('Wrong Booking Number and Pin Code.', 'citytours');
            wp_send_json($result_json);
        }

        if (!is_array($order_data) || $order_data['status'] == 'cancelled')
        {
            $result_json['success'] = 0;
            $result_json['title'] = esc_html__('Sorry, You cannot leave a rating.', 'citytours');
            $result_json['result'] = esc_html__('You cancelled your booking, so cannot leave a rating.', 'citytours');
            wp_send_json($result_json);
        }

        if (( empty($order_data['date_to']) && ct_strtotime($order_data['date_from']) > ct_strtotime(date("Y-m-d")) ) || ( ct_strtotime($order_data['date_to']) > ct_strtotime(date("Y-m-d")) ))
        {
            $result_json['success'] = 0;
            $result_json['title'] = esc_html__('Sorry, You cannot leave a rating before travel.', 'citytours');
            $result_json['result'] = esc_html__('You can leave a review after travel.', 'citytours');
            wp_send_json($result_json);
        }

        $data['post_id'] = $order_data['post_id'];
        $data['reviewer_name'] = $order_data['first_name'] . ' ' . $order_data['last_name'];
        $data['reviewer_email'] = $order_data['email'];
        $data['reviewer_ip'] = $_SERVER['REMOTE_ADDR'];
        $data['review_rating_detail'] = serialize($_POST['review_rating_detail']);
        $data['review_rating'] = array_sum($_POST['review_rating_detail']) / count($_POST['review_rating_detail']);
        $data['date'] = date('Y-m-d H:i:s');
        $data['status'] = 'pending';
        if (is_user_logged_in())
            $data['user_id'] = get_current_user_id();
        if (!$review_data = $wpdb->get_row($wpdb->prepare('SELECT * FROM ' . CT_REVIEWS_TABLE . ' WHERE booking_no=%d AND pin_code=%d', $data['booking_no'], $data['pin_code']), ARRAY_A))
        {
            if ($wpdb->insert(CT_REVIEWS_TABLE, $data))
            {
                $result_json['success'] = 1;
                $result_json['title'] = esc_html__('Thank you! Your review has been submitted successfully.', 'citytours');
                $result_json['result'] = esc_html__('Your review has been submitted.', 'citytours');
            }
            else
            {
                $result_json['success'] = 0;
                $result_json['title'] = esc_html__('Sorry, An error occurred while add review.', 'citytours');
                $result_json['result'] = esc_html__('Please try again after a while.', 'citytours');
            }
        }
        else
        {
            if ($wpdb->update(CT_REVIEWS_TABLE, $data, array('booking_no' => $data['booking_no'], 'pin_code' => $data['pin_code'])))
            {
                $result_json['success'] = 1;
                $result_json['title'] = esc_html__('Thank you! Your review has been submitted successfully.', 'citytours');
                $result_json['result'] = esc_html__('You can change your review anytime.', 'citytours');
                ct_review_calculate_rating($data['post_id']);
            }
            else
            {
                $result_json['success'] = 0;
                $result_json['title'] = esc_html__('Sorry, An error occurred while add review.', 'citytours');
                $result_json['result'] = esc_html__('Please try again after a while.', 'citytours');
            }
        }
        wp_send_json($result_json);
    }

}

/* function to get all modules that enabled */
if (!function_exists('ct_get_available_modules'))
{


    function ct_get_available_modules()
    {
        global $ct_options;
        $modules = array();
        if (empty($ct_options['disable_hotel']))
            $modules[] = 'hotel';
        if (empty($ct_options['disable_tour']))
            $modules[] = 'tour';
        return $modules;
    }

}

/*
 * functions when theme activation
 */
if (!function_exists('ct_after_switch_theme'))
{


    function ct_after_switch_theme()
    {
        if (!wp_next_scheduled('ct_hourly_cron'))
        {
            wp_schedule_event(time(), 'hourly', 'ct_hourly_cron');
        }
    }

}

/*
 * functions when theme deactivation
 */
if (!function_exists('ct_switch_theme'))
{


    function ct_switch_theme()
    {
        wp_clear_scheduled_hook('ct_hourly_cron');
    }

}

/*
 * get additional service select list
 */
if (!function_exists('ct_get_service_list'))
{


    function ct_get_service_list($post_id = 0, $def_service_id = 0)
    {
        $services = ct_get_add_services_by_postid($post_id);

        echo '<option></option>';
        if (!empty($services))
        {
            foreach ($services as $service)
            {
                $selected = '';
                $id = $service->id;
                if ($def_service_id == $id)
                {
                    $selected = ' selected ';
                }

                echo '<option ' . esc_attr($selected) . 'value="' . esc_attr($id) . '">' . wp_kses_post($service->title) . '</option>';
            }
        }
    }

}

/*
 * get temporary table name
 */
if (!function_exists('ct_get_temp_table_name'))
{


    function ct_get_temp_table_name()
    {
        $temp_tbl_name = str_replace(' ', '', 'Search_' . session_id()); // Replaces all spaces with hyphens.
        return esc_sql(preg_replace('/[^A-Za-z0-9\-]/', '', $temp_tbl_name)); // Removes special chars.
    }

}

/*
 * Return google_map_url with API key
 */
if (!function_exists('ct_google_map_url'))
{


    function ct_google_map_url($map_url)
    {
        global $ct_options;

        $key = ct_google_maps_api_key();
        if ($key !== '')
        {
            $map_url = 'https://maps.googleapis.com/maps/api/js?key=' . rawurlencode($key);
        }
        return $map_url;
    }

}

if (!function_exists('ct_google_maps_api_key')) {
    function ct_google_maps_api_key() {
        global $ct_options;
        $key = '';
        if (defined('CT_GOOGLE_MAPS_API_KEY') && CT_GOOGLE_MAPS_API_KEY) {
            $key = CT_GOOGLE_MAPS_API_KEY;
        } elseif (!empty($ct_options['map_api_key'])) {
            $key = $ct_options['map_api_key'];
        }
        $key = preg_replace('/[^A-Za-z0-9_\-]/', '', (string) $key);
        return $key;
    }
}

if (!function_exists('ct_google_maps_script')) {
    function ct_google_maps_script($args = array()) {
        $key = ct_google_maps_api_key();
        if ($key === '') {
            return;
        }
        $query = array('key' => $key);
        if (!empty($args['libraries'])) {
            $query['libraries'] = $args['libraries'];
        }
        if (!empty($args['callback'])) {
            $query['callback'] = $args['callback'];
        }
        $url = 'https://maps.googleapis.com/maps/api/js?' . http_build_query($query);
        echo '<script async defer src="' . esc_url($url) . '"></script>';
    }
}

/*
 * Redirect single room page to Hotel page
 */
if (!function_exists('ct_update_rooms_url'))
{


    function ct_update_rooms_url($post_link, $post)
    {
        if ($post->post_type === 'room_type')
        {
            $hotel_id = get_post_meta($post->ID, '_room_hotel_id', true);
            $hotel_id = ct_post_org_id($hotel_id);

            if (!empty($hotel_id))
            {
                $post_link = get_permalink($hotel_id);
            }
        }

        return $post_link;
    }

}


/*
 * get post review html from post_id
 */
if (!function_exists('get_districts_byparent'))
{

    function get_districts_byparent()
    {
        global $wpdb;
        $result = array(
            'success' => 0,
            'districts' => '',
        );

        if (empty($_POST['country_id']))
        {
            wp_send_json($result);
            die;
        }

        $country_id = esc_sql($_POST['country_id']);

//		$districts = get_terms('district', array('hide_empty' => 0, 'parent' => $country_id, 'cache_domain' => $country_id));
        $districts = $wpdb->get_results(" SELECT t.term_id, t.name, tt.taxonomy FROM " . $wpdb->prefix . "terms AS t INNER JOIN " . $wpdb->prefix . "term_taxonomy AS tt ON t.term_id = tt.term_id WHERE tt.taxonomy IN ('district') AND tt.parent = {$country_id} ORDER BY t.term_order ASC ");

        if (!empty($districts))
        {
            $result['success'] = 1;
            $result['districts'] = $districts;
        }

        wp_send_json($result);
        die();
    }

}

/*
 * get post review html from post_id
 */
if (!function_exists('filterByCountryId'))
{

    function filterByCountryId()
    {
        global $wpdb;
        $result = array(
            'success' => 0,
            'cities' => '',
        );

        if (empty($_POST['id']))
        {
            wp_send_json($result);
            die;
        }

        $country_id = esc_sql($_POST['id']);

        $cities = $wpdb->get_results(" SELECT t.term_id, t.name, tt.taxonomy FROM " . $wpdb->prefix . "terms AS t INNER JOIN " . $wpdb->prefix . "term_taxonomy AS tt ON t.term_id = tt.term_id WHERE tt.taxonomy IN ('state') AND tt.parent = {$country_id} ORDER BY t.term_order ASC ");

        if (!empty($cities))
        {
            $result['success'] = 1;
            $result['cities'] = $cities;
        }

        wp_send_json($result);
        die();
    }

}

/*
 * get_tour_type_cities
 */
if (!function_exists('get_tour_type_cities'))
{

    function get_tour_type_cities()
    {
        $result = array(
            'success' => 0,
            'cities' => '',
        );

        if (empty($_POST['tour_type']))
        {
            wp_send_json($result);
            die;
        }

        $tour_type = esc_sql($_POST['tour_type']);
        
        $districts = get_terms('tour_type', array('hide_empty' => 0, 'parent' => $tour_type, 'cache_domain' => $tour_type));
        $districts = ct_unique_tour_types_by_name($districts);

        if (!empty($districts))
        {
            $result['success'] = 1;
            $result['cities'] = $districts;
        }

        wp_send_json($result);
        die();
    }

}

/*
 * get_tour_type_cities
 */
if (!function_exists('ct_airports_search'))
{

    function ct_airports_search()
    {
        global $wpdb;

        $term = esc_sql(trim($_POST['term']));

        $term = trim($term);

//        $airports = $wpdb->get_results('SELECT * FROM ' . $wpdb->prefix . 'airports WHERE city REGEXP "' . $term . '" OR airport REGEXP "' . $term . '" order by airport DESC');
        $airports = $wpdb->get_results('SELECT * FROM ' .$wpdb->prefix.'airports WHERE city like "'.$term.'%" OR airport like "%'.$term.'%" OR normalise like "%'.$term.'%" order by airport DESC');

        foreach ($airports as $airport)
        {
            $results[] = $airport->airport . ' , ' . $airport->code . '';
//            $results[] = $airport->airport.' '.$airport->city.' '.$airport->country.' , '.$airport->code.'';
        }

        wp_send_json($results);

        die();
    }

}

/*
 * get_tour_type_cities
 */
if (!function_exists('get_visa_cities'))
{
    function get_visa_cities()
    {
        $result = array(
            'success' => 0,
            'cities' => '',
        );

        if (empty($_POST['category']))
        {
            wp_send_json($result);
            die;
        }

        $parent = esc_sql($_POST['category']);

        $categories = get_terms('visa-category', array('hide_empty' => 0, 'parent' => $parent, 'cache_domain' => $tour_type));

        if (!empty($categories))
        {
            $result['success'] = 1;
            $result['cities'] = $categories;
        }

        wp_send_json($result);
        die();
    }
}