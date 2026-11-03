<?php
if (!session_id()) {
    @ob_start();
    @session_start();
}
global $wpdb;
//constants
define('CT_VERSION', '2.5.4');
define('CT_DB_VERSION', '1.1');
define('CT_TEMPLATE_DIRECTORY_URI', get_template_directory_uri());
define('CT_IMAGE_URL', CT_TEMPLATE_DIRECTORY_URI . '/img');
define('CT_INC_DIR', get_template_directory() . '/inc');
define('CT_TAX_META_DIR_URL', CT_TEMPLATE_DIRECTORY_URI . '/inc/lib/tax-meta-class/');

define('CT_HOTEL_VACANCIES_TABLE', $wpdb->prefix . 'ct_hotel_vacancies');
define('CT_HOTEL_BOOKINGS_TABLE', $wpdb->prefix . 'ct_hotel_bookings');
define('CT_REVIEWS_TABLE', $wpdb->prefix . 'ct_reviews');
define('CT_ADD_SERVICES_TABLE', $wpdb->prefix . 'ct_add_services');
define('CT_ADD_SERVICES_BOOKINGS_TABLE', $wpdb->prefix . 'ct_add_service_bookings');
define('CT_TOUR_SCHEDULES_TABLE', $wpdb->prefix . 'ct_tour_schedules');
define('CT_TOUR_SCHEDULE_META_TABLE', $wpdb->prefix . 'ct_tour_schedule_meta');
define('CT_TOUR_BOOKINGS_TABLE', $wpdb->prefix . 'ct_tour_bookings');
define('CT_CURRENCIES_TABLE', $wpdb->prefix . 'ct_currencies');
define('CT_ORDER_TABLE', $wpdb->prefix . 'ct_order');
define('CT_MODE', 'dev');

if (!class_exists('ReduxFramework')) {
    require_once(CT_INC_DIR . '/lib/redux-framework/ReduxCore/framework.php');
}
if (!isset($redux_demo)) {
    require_once(CT_INC_DIR . '/lib/redux-framework/config.php');
}

// WooCommerce Integration
require_once(CT_INC_DIR . '/woocommerce/woocommerce.php');

//require files
if (!defined('RWMB_URL')) {
    define('RWMB_URL', CT_TEMPLATE_DIRECTORY_URI . '/inc/lib/meta-box/');
    require_once(CT_INC_DIR . '/lib/meta-box/meta-box.php');
}

require_once(CT_INC_DIR . '/admin/main.php');
require_once(CT_INC_DIR . '/lib/multiple_sidebars.php');
require_once(CT_INC_DIR . '/functions/main.php');
require_once(CT_INC_DIR . '/shortcode/init.php');
require_once(CT_INC_DIR . '/js_composer/init.php');
require_once(CT_INC_DIR . '/frontend/main.php');
require_once(CT_INC_DIR . '/widgets/main.php');
require_once(CT_INC_DIR .'/shortcode/search-container/search_container.php');
require_once(CT_INC_DIR .'/shortcode/featured_districts.php');
require_once(CT_INC_DIR .'/shortcode/featured_tour_type.php');
// require_once(CT_INC_DIR . '/core/model.php');
require_once('utility-functions.php');
// Translation
load_theme_textdomain('citytours', get_template_directory() . '/languages');

//theme supports
add_theme_support('automatic-feed-links');
add_theme_support('post-thumbnails');
add_theme_support('woocommerce');

// date_default_timezone_set("Africa/Cairo");

global $wp_version;

if (version_compare($wp_version, '4.1', '>=')) {
    add_theme_support('title-tag');
    add_filter('wp_title', 'ct_wp_title', 10, 2);
} else {
    add_filter('wp_title', 'ct_wp_title_old', 10, 2);
}

if (!isset($content_width))
    $content_width = 900;

/* Add custom Image sizes */
add_image_size('ct-room-gallery', 200, 133, true);
add_image_size('ct-list-thumb', 400, 267, true);
add_image_size('ct-map-thumb', 280, 140, true);
add_image_size('ct-thumbnails', 270, 150, true);

//actions
add_action('init', 'ct_init');
add_action('wp_enqueue_scripts', 'ct_enqueue_scripts');
add_action('admin_enqueue_scripts', 'ct_admin_scripts');
add_action('wp_head', 'ct_load_custom_styles', 99);
add_action('wp_footer', 'ct_inline_script');
add_action('tgmpa_register', 'ct_register_required_plugins');
add_action('admin_menu', 'ct_remove_redux_menu', 12);
add_action('widgets_init', 'ct_register_sidebar');
// add_action( 'user_register', 'ct_user_register' );
add_action('wp_login_failed', 'ct_login_failed');
add_action('lost_password', 'ct_lost_password');
add_action('comment_form_before', 'ct_enqueue_comment_reply');
add_filter('404_template', 'ct_show404');
add_filter('authenticate', 'ct_authenticate', 1, 3);
add_filter('get_default_comment_status', 'ct_open_comments_for_myposttype', 10, 3);

remove_action('admin_enqueue_scripts', 'wp_auth_check_load');

register_nav_menus(array(
    'header' => __('Main Menu', 'header-menu'),
    'footer' => __('Footer Menu', 'footer-menu'),
    //    'tour menu' => __('Tour Menu', 'tour-hotel'),
    //    'hotel menu' => __('Hotel Menu', 'hotel-menu'),
));


function add_async_forscript($url)
{
    if (strpos($url, '#asyncload') === false)
        return $url;
    else if (is_admin())
        return str_replace('#asyncload', '', $url);
    else
        return str_replace('#asyncload', '', $url) . "' async defer ";
}

add_filter('clean_url', 'add_async_forscript', 11, 1);

add_filter('pre_get_document_title', function () {
}, 999, 1);

/* Function which remove update notifications */
function remove_update_notifications($value)
{
    unset($value->response['akismet/akismet.php']);
    unset($value->response['sliderpro/sliderpro.php']);
    unset($value->response['js_composer/js_composer.php']);
    return $value;
}

add_filter('site_transient_update_plugins', 'remove_update_notifications');

add_action('init', 'mysqlfix', -1);

function mysqlfix()
{
    global $wpdb;
    // WordPress strips ANSI and ONLY_FULL_GROUP_BY. Putting ANSI back makes the hotel search GROUP BY fail.
    $wpdb->set_sql_mode();
}

function custom_login_redirect($redirect_to, $request, $user)
{
    // Modify the redirection logic based on your requirements
    if (is_wp_error($user)) {
        return $redirect_to; // If login fails, stay on the same page
    }

    if (user_can($user, 'administrator')) {
        // Redirect admin users to a specific page
        $redirect_to = home_url('/dashboard'); // Change '/dashboard' to your desired URL
    } else {
        // Redirect other users to a different page
        $redirect_to = home_url(); // Change to your preferred URL
    }

    return $redirect_to;
}

add_filter('login_redirect', 'custom_login_redirect', 10, 3);

function enqueue_hotel_js()
{
    if (is_archive() && is_post_type_archive('hotel'))
    {
        wp_register_script('hotel_archive_js', get_template_directory_uri() . '/js/hotel/archive.js', array('jquery'), null, true);

        // Pass parameters (like ajaxurl) from PHP to JavaScript
        wp_localize_script('hotel_archive_js', 'hotel_archive', array(
            'lang' => get_locale(),
            'ajaxurl' => admin_url('admin-ajax.php'),
            'is_woocommerce_enabled' => ct_is_woocommerce_integration_enabled() ? 'true' : 'false',
        ));
    }

    $pagename = get_query_var('pagename');
    
    if ($pagename !== 'hotel-cart-page') {
        // Register the script with WordPress
        wp_register_script('hotel_cart_js', get_template_directory_uri() . '/js/hotel/cart.js', array('jquery'), null, true);

        // Pass parameters (like ajaxurl) from PHP to JavaScript
        wp_localize_script('hotel_cart_js', 'hotel_cart_data', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'is_woocommerce_enabled' => ct_is_woocommerce_integration_enabled() ? 'true' : 'false',
        ));
    }
}

// add_action('wp_enqueue_scripts', 'enqueue_hotel_js');


function _action_fw_flash_message_backend_prepare() {
    // Start output buffering before starting the session
    ob_start();

    // Check if session has been started
    if (!session_id()) {
        // Start a new session
        session_start();    
    }
}
add_action( 'plugins_loaded', '_action_fw_flash_message_backend_prepare' );
function add_noindex_to_all_pagination_pages() {
    if (is_paged()) { 
        echo '<meta name="robots" content="noindex, follow">' . "\n";
    }
}
add_action('wp_head', 'add_noindex_to_all_pagination_pages', 999);

function add_lazy_loading_to_post_thumbnail($attr, $attachment, $size) {
    // Check if it's the post thumbnail
    if (is_singular() && isset($attr['class']) && strpos($attr['class'], 'wp-post-image') !== false) {
        $attr['loading'] = 'lazy'; // Add lazy loading attribute
    }
    return $attr;
}

add_filter('wp_get_attachment_image_attributes', 'add_lazy_loading_to_post_thumbnail', 10, 3);


/**
 * Add 'defer' attribute to specified JavaScript files.
 * 
 * This function adds the 'defer' attribute to JavaScript files to improve page loading performance
 * by postponing script execution until the HTML document is fully parsed.
 *
 * @param string $tag    The complete <script> tag to be modified
 * @param string $handle The script handle/name registered with wp_register_script()
 * @return string        The modified script tag with defer attribute if applicable
 */
function ct_add_defer_attribute($tag, $handle) {
    // List of scripts that should receive the defer attribute
    $scripts_to_defer = array(
        // 'ct_script_common',
        // 'ct_theme_script',
        // 'ct_script_datepicker',
        // 'ct_script_timepicker',
        'ct_script_localization',
        'ct_script_localization1',
        'ct_script_localization2',
        // 'ct_script_icheck',
        // 'ct_script_jquery_validate',
        'ct_script_fixed_sidebar',
        'ct_script_infobox',
        'ct_script_map',
        'ct_script_modernizr',
        'ct_script_owl',
        // 'ct_cat_nav_mobile'
        // Add other script handles as needed
    );
    
    // Don't defer jQuery to avoid compatibility issues
    // Don't defer scripts that need to be loaded immediately
    
    // Check if the current script handle is in our list of scripts to defer
    if (in_array($handle, $scripts_to_defer)) {
        // Add the defer attribute to the script tag
        return str_replace(' src', ' defer src', $tag);
    }
    
    // Return the original script tag if it's not in our list
    return $tag;
}

// Hook our function to the script_loader_tag filter
add_filter('script_loader_tag', 'ct_add_defer_attribute', 10, 2);

function custom_captcha_register_form() {
    // Always generate new CAPTCHA for security
    include 'simple-php-captcha.php'; 
    
    try {
        $captcha = simple_php_captcha();
        $_SESSION['captcha'] = $captcha;
        
        // Convert file path to URL path
        $image_url = $_SESSION['captcha']['image_src'];
        ?>
        
        <img src="<?php echo esc_url(home_url($image_url)); ?>" alt="CAPTCHA code" style="display: block; margin: 10px 0; border: 1px solid #ddd; border-radius: 4px;" />
        <input type="text" name="captcha" id="captcha" class="form-control" placeholder="<?php esc_html_e('Enter the code above', 'citytours'); ?>" required />
        <?php
    } catch (Exception $e) {
        // Fallback text CAPTCHA if image generation fails
        $simple_captcha = substr(str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZ23456789'), 0, 4);
        $_SESSION['captcha'] = array('code' => $simple_captcha);
        ?>
        <label><?php esc_html_e('Enter CAPTCHA', 'citytours'); ?></label>
        <div style="background: #f0f0f0; padding: 15px; margin: 10px 0; border: 1px solid #ddd; border-radius: 4px; font-weight: bold; text-align: center; font-size: 18px; letter-spacing: 3px;">
            <?php echo $simple_captcha; ?>
        </div>
        <input type="text" name="captcha" id="captcha" class="form-control" placeholder="<?php esc_html_e('Enter the code above', 'citytours'); ?>" required />
        <?php
    }
}
add_action('custom_captcha_register_form', 'custom_captcha_register_form');

function custom_captcha_check($errors, $sanitized_user_login, $user_email) {
    $captcha_error = false;
    
    if (!isset($_POST['captcha']) || !isset($_SESSION['captcha'])) {
        $errors->add('captcha_error', __('<strong>ERROR</strong>: CAPTCHA is required.'));
        $captcha_error = true;
    } else {
        $user_input = trim(strtolower($_POST['captcha']));
        $correct_answer = strtolower($_SESSION['captcha']['code']);

        if ($user_input !== $correct_answer) {
            $errors->add('captcha_error', __('<strong>ERROR</strong>: CAPTCHA is incorrect.'));
            $captcha_error = true;
        }
    }

    // Always reset CAPTCHA after validation (to prevent reuse)
    unset($_SESSION['captcha']);

    // If there's a CAPTCHA error, redirect back to registration page
    if ($captcha_error) {
        // Store error and form data in session to display after redirect
        $_SESSION['captcha_error_message'] = $errors->get_error_message('captcha_error');
        $_SESSION['registration_form_data'] = array(
            'user_login' => $sanitized_user_login,
            'user_email' => $user_email
        );
        
        // Get current login page URL and add registration action
        global $ct_options;
        if (isset($ct_options['login_page'])) {
            $redirect_url = add_query_arg('action', 'register', ct_get_permalink_clang($ct_options['login_page']));
        } else {
            $redirect_url = home_url('/login/?action=register');
        }
        
        // Redirect and exit
        wp_redirect($redirect_url);
        exit;
    }

    return $errors;
}
add_filter('registration_errors', 'custom_captcha_check', 10, 3);
