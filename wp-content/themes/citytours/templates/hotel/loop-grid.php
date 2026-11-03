<?php
if (!defined('ABSPATH')) {
    exit;
}

global $post_id, $before_list, $show_map;

$wishlist = array();
if (is_user_logged_in()) {
    $user_id = get_current_user_id();
    $wishlist = get_user_meta($user_id, 'wishlist', false);
}
if (!is_array($wishlist))
    $wishlist = array();


$date_from = isset($_REQUEST['date_from']) ? ct_sanitize_date($_REQUEST['date_from']) : date('Y-m-d');
$date_to = isset($_REQUEST['date_to']) ? ct_sanitize_date($_REQUEST['date_to']) : date("Y-m-d", strtotime($date_from . ' + 3day'));
if (ct_strtotime($date_from) >= ct_strtotime($date_to)) {
    $date_from = '';
    $date_to = '';
}

//$price = get_post_meta($post_id, '_hotel_price', true);
// ct_hotel_average_price

$use_card_price_cache = empty($_REQUEST['date_from']) && empty($_REQUEST['date_to']);
$price_cache_key = 'ct_hotel_card_' . (int) $post_id . '_' . $date_from . '_' . $date_to;
$price = $use_card_price_cache ? get_transient($price_cache_key) : false;
if ($price === false) {
    $price = ct_hotel_average_price($post_id, $date_from, $date_to);
    if ($use_card_price_cache) {
        set_transient($price_cache_key, $price, MINUTE_IN_SECONDS);
    }
}
    
if (empty($price))
    $price = 0;
$brief = get_post_meta($post_id, '_hotel_brief', true);
if (empty($brief)) {
    $brief = apply_filters('the_content', get_post_field('post_content', $post_id));
    $brief = wp_trim_words($brief, 20, '');
}
$star = get_post_meta($post_id, '_hotel_star', true);
$star = (!empty($star)) ? round($star, 1) : 0;
$review = get_post_meta($post_id, '_review', true);
$review = (!empty($review)) ? round($review, 1) : 0;
$doubled_review = number_format(round($review * 2, 1), 1);
$featured = get_post_meta($post_id, '_hotel_featured', true);
$hot = get_post_meta($post_id, '_hotel_hot', true);
$discount_rate = get_post_meta($post_id, '_hotel_discount_rate', true);

$review_content = '';
if ($doubled_review >= 9) {
    $review_content = esc_html__('Superb', 'citytours');
} elseif ($doubled_review >= 8) {
    $review_content = esc_html__('Very good', 'citytours');
} elseif ($doubled_review >= 7) {
    $review_content = esc_html__('Good', 'citytours');
} elseif ($doubled_review >= 6) {
    $review_content = esc_html__('Pleasant', 'citytours');
} else {
    $review_content = esc_html__('Review Rating', 'citytours');
}
$wishlist_link = ct_wishlist_page_url();

$query_args = array(
    'date_from' => $date_from,
    'date_to' => $date_to
);

//$url = esc_url(add_query_arg($query_args, get_permalink($post_id)));
$url = esc_url(get_permalink($post_id));


if (!empty($before_list)) {
    echo ($before_list);
} else {
?>
    <div class="col-md-6 col-sm-6 wow zoomIn" data-wow-delay="0.1s">
    <?php } ?>

    <div class="hotel_container">
        <?php if (!empty($featured)) { ?>
            <div class="ribbon_3"><span><?php _e('Featured', 'citytours'); ?></span></div>
        <?php } elseif (!empty($hot)) { ?>
            <div class="ribbon_3 popular"><span><?php _e('Hot', 'citytours'); ?></span></div>
        <?php } ?>
        <div class="img_container">
            <a href="<?php echo $url; ?>">
                <?php echo get_the_post_thumbnail($post_id, 'ct-list-thumb'); ?>
                <?php if (!empty($discount_rate)) { ?>
                    <div class="badge_save"><?php _e('Discount', 'citytours'); ?><strong><?php echo esc_html($discount_rate . '%'); ?></strong></div>
                <?php } ?>
                <!-- <div class="ribbon top_rated"></div> -->

                <?php if (!empty($review)) : ?>
                    <div class="score"><span><?php echo esc_html($doubled_review) ?></span><?php echo esc_html($review_content) ?></div>
                <?php endif; ?>

                <div class="short_info hotel">
                    <?php echo esc_html__('From/Per night', 'citytours') ?>
                    <span class="price"><?php echo ct_price($price, 'special') ?></span>
                </div>
            </a>

        </div>

        <div class="hotel_title">
            <h3><a href="<?php echo $url; ?>"> <?php echo esc_html(get_the_title($post_id)); ?> </a></h3>

            <div class="rating">
                <?php ct_rating_smiles($star, 'icon-star-empty', 'icon-star voted') ?>
            </div><!-- end rating -->

            <?php if (!empty($wishlist_link)) : ?>
                <div class="wishlist">
                    <a class="tooltip_flip tooltip-effect-1 btn-add-wishlist" href="#" data-post-id="<?php echo esc_attr($post_id) ?>" <?php echo (in_array(ct_hotel_org_id($post_id), $wishlist) ? ' style="display:none;"' : '') ?>><span class="wishlist-sign">+</span><span class="tooltip-content-flip"><span class="tooltip-back"><?php esc_html_e('Add to wishlist', 'citytours'); ?></span></span></a>
                    <a class="tooltip_flip tooltip-effect-1 btn-remove-wishlist" href="#" data-post-id="<?php echo esc_attr($post_id) ?>" <?php echo (!in_array(ct_hotel_org_id($post_id), $wishlist) ? ' style="display:none;"' : '') ?>><span class="wishlist-sign">-</span><span class="tooltip-content-flip"><span class="tooltip-back"><?php esc_html_e('Remove from wishlist', 'citytours'); ?></span></span></a>
                </div><!-- End wish list-->
            <?php endif; ?>
            <?php if (isset($show_map) && $show_map) { ?>
                <div onclick="onHtmlClick('<?php echo $post_id; ?>')" class="view_on_map"><?php _e('View on map', 'citytours'); ?></div>
            <?php } ?>

            <div class="wbooking">
                <a href="<?php echo $url; ?>" class="btn_1 btn-sm"><?php esc_html_e('Watch and Book', 'citytours') ?></a>
            </div>

        </div>

    </div><!-- End box tour -->

    </div><!-- End col-md-6 -->
