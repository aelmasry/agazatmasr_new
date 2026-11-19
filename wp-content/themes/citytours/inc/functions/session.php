<?php
/**
 * Lazy PHP session start (AGZ-020 / AGZ-035).
 *
 * The theme used to call session_start() on every request. PHP then sent
 * Set-Cookie: PHPSESSID plus the "nocache" cache limiter headers
 * (Cache-Control: no-store, Expires: 1981), which made every page
 * uncacheable at the edge and blocked bfcache.
 *
 * A session is now started only where something reads or writes $_SESSION
 * across requests:
 * - the booking cart (CT_Hotel_Cart) via its AJAX actions and the
 *   cart / checkout / thank-you / invoice pages,
 * - the captcha on the contact page and the login / registration flow,
 * - currency switching (?selected_currency=),
 * - any front-end form POST, logged-in users, and visitors who already
 *   hold a session cookie (so an in-progress booking is never lost).
 *
 * Anonymous GET pages therefore send no cookie and no no-store header.
 * Code that touches $_SESSION without a session still works: it is a
 * plain per-request array and nothing is persisted.
 *
 * @package citytours
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * AJAX actions that read or write the booking cart in $_SESSION.
 *
 * Other AJAX calls (search, city lists, reviews) must not start a session,
 * otherwise every visitor using the homepage search would get a cookie.
 *
 * @return string[]
 */
function ct_session_ajax_actions()
{
    return apply_filters('ct_session_ajax_actions', array(
        'ct_hotel_update_cart',
        'ct_hotel_submit_booking',
        'ct_add_hotel_to_woo_cart',
        'ct_tour_update_cart',
        'ct_tour_submit_booking',
        'ct_add_tour_to_woo_cart',
    ));
}

/**
 * Theme option keys of pages that use the session (cart, booking, login).
 *
 * @return string[]
 */
function ct_session_page_option_keys()
{
    return array(
        'hotel_cart_page',
        'hotel_checkout_page',
        'hotel_thankyou_page',
        'hotel_invoice_page',
        'tour_cart_page',
        'tour_checkout_page',
        'tour_thankyou_page',
        'tour_invoice_page',
        'login_page',
    );
}

/**
 * Start the session once, if it is not running and headers can still be sent.
 */
function ct_session_start()
{
    if (PHP_SESSION_NONE !== session_status() || headers_sent()) {
        return;
    }

    session_start();
}

/**
 * Request-level reasons to start a session (no query conditionals needed).
 *
 * @return bool
 */
function ct_request_needs_session()
{
    // Keep an existing session (e.g. a cart started on a previous request).
    if (isset($_COOKIE[session_name()])) {
        return true;
    }

    if (is_user_logged_in()) {
        return true;
    }

    // AJAX is decided by action only: most admin-ajax calls are anonymous POSTs.
    if (wp_doing_ajax()) {
        $action = isset($_REQUEST['action']) ? sanitize_key(wp_unslash($_REQUEST['action'])) : '';
        return in_array($action, ct_session_ajax_actions(), true);
    }

    if (isset($_SERVER['REQUEST_METHOD']) && 'POST' === $_SERVER['REQUEST_METHOD']) {
        return true;
    }

    if (!empty($_GET['selected_currency'])) {
        return true;
    }

    return false;
}

/**
 * Whether the current front-end page needs a session.
 *
 * @return bool
 */
function ct_page_needs_session()
{
    global $ct_options;

    $page_ids = array();
    foreach (ct_session_page_option_keys() as $key) {
        if (!empty($ct_options[$key])) {
            $page_ids[] = (int) $ct_options[$key];
        }
    }

    if ($page_ids && is_page($page_ids)) {
        return true;
    }

    return is_page_template(array('contact.php', 'templates/template-login.php'));
}

/**
 * Front end: decide after the main query, before any output.
 */
function ct_maybe_start_frontend_session()
{
    if (ct_request_needs_session() || ct_page_needs_session()) {
        ct_session_start();
    }
}
add_action('template_redirect', 'ct_maybe_start_frontend_session', 0);

/**
 * admin-ajax.php, wp-admin and wp-login.php (registration captcha).
 */
function ct_maybe_start_backend_session()
{
    if (ct_request_needs_session()) {
        ct_session_start();
    }
}
add_action('admin_init', 'ct_maybe_start_backend_session', 0);
add_action('login_init', 'ct_maybe_start_backend_session', 0);
