<?php
if (!defined('ABSPATH')) {
    exit;
}

global $post_list, $current_view, $post_id, $price, $order_direction;

if (empty($post_list)) :
    echo '<h5 class="empty-list">' . esc_html__('No available hotels', 'citytours') . '</h5>';
else :
    $date_from = isset($_REQUEST['date_from']) ? ct_sanitize_date($_REQUEST['date_from']) : date('Y-m-d');
    $date_to = isset($_REQUEST['date_to']) ? ct_sanitize_date($_REQUEST['date_to']) : date("Y-m-d", strtotime($date_from . ' + 1day'));
    // Extract prices and hotel IDs into separate arrays
    // $prices = array_map(function ($post) use ($date_from, $date_to) {
    //     return ct_hotel_average_price($post['hotel_id'], $date_from, $date_to);
    // }, $post_list);

    // // Define the order direction (e.g., 'asc' or 'desc')
    // $order_direction = 'asc';  // Set it based on your requirements

    // // Sort prices and post list accordingly
    // array_multisort($prices, ($order_direction === 'desc') ? SORT_ASC : SORT_DESC, $post_list);

    // Loop through the sorted array
    foreach ($post_list as $post_obj) :
        $post_id = $post_obj['hotel_id'];
        $price = $post_obj['price'];
        // $price = ct_hotel_average_price($post_obj['hotel_id'], $date_from, $date_to);

        ct_get_template('loop-' . $current_view . '.php', '/templates/hotel/');

    endforeach;

endif;
?>
