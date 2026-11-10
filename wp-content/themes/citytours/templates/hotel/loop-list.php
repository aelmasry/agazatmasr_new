<?php
if (!defined('ABSPATH')) {
    exit;
}

global $post_id, $price;

$wishlist = array();
if (is_user_logged_in()) {
    $user_id = get_current_user_id();
    $wishlist = get_user_meta($user_id, 'wishlist', true);
}

if (!is_array($wishlist))
    $wishlist = array();

$date_from = isset($_REQUEST['date_from']) ? ct_sanitize_date($_REQUEST['date_from']) : date('Y-m-d');
$date_to = isset($_REQUEST['date_to']) ? ct_sanitize_date($_REQUEST['date_to']) : date("Y-m-d", strtotime($date_from . ' + 1day'));
// if (ct_strtotime($date_from) >= ct_strtotime($date_to)) {
//     $date_from = '';
//     $date_to = '';
// }

// ct_hotel_average_price
if (empty($price)) {
    $price = ct_hotel_average_price($post_id, $date_from, $date_to);
}

$brief = get_post_meta($post_id, '_hotel_brief', true);
if (empty($brief)) {
    $brief = apply_filters('the_content', get_post_field('post_content', $post_id));
}
$brief = wp_trim_words($brief, 55, '');
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
if (!empty($date_from)) {
    $url = esc_url(add_query_arg($query_args, get_permalink($post_id)));
} else {
    $url = esc_url(get_permalink($post_id));
}
?>
<?php if($price > 0) { ?>
<div class="strip_all_tour_list wow fadeIn" data-wow-delay="0.1s">

    <div class="row">

        <div class="col-lg-4 col-md-4 col-sm-4">
            <?php if (!empty($featured)) { ?>
                <div class="ribbon_3"><span><?php _e('Featured', 'citytours'); ?></span></div>
            <?php } elseif (!empty($hot)) { ?>
                <div class="ribbon_3 popular"><span><?php _e('Hot', 'citytours'); ?></span></div>
            <?php } ?>
            <?php if (!empty($wishlist_link)) : ?>
                <div class="wishlist">
                    <a class="tooltip_flip tooltip-effect-1 btn-add-wishlist" href="#" data-post-id="<?php echo esc_attr($post_id) ?>" <?php echo (in_array(ct_hotel_org_id($post_id), $wishlist) ? ' style="display:none;"' : '') ?>><span class="wishlist-sign">+</span><span class="tooltip-content-flip"><span class="tooltip-back"><?php esc_html_e('Add to wishlist', 'citytours'); ?></span></span></a>
                    <a class="tooltip_flip tooltip-effect-1 btn-remove-wishlist" href="#" data-post-id="<?php echo esc_attr($post_id) ?>" <?php echo (!in_array(ct_hotel_org_id($post_id), $wishlist) ? ' style="display:none;"' : '') ?>><span class="wishlist-sign">-</span><span class="tooltip-content-flip"><span class="tooltip-back"><?php esc_html_e('Remove from wishlist', 'citytours'); ?></span></span></a>
                </div>
            <?php endif; ?>

            <div class="img_list">
                <a href="<?php echo $url; ?>">
                    <!-- <div class="ribbon popular" ></div> -->
                    <?php echo get_the_post_thumbnail($post_id, array(330, 220)); ?>
                    <?php if (!empty($discount_rate)) { ?>
                        <div class="badge_save"><?php _e('Discount', 'citytours'); ?><strong><?php echo esc_html($discount_rate . '%'); ?></strong></div>
                    <?php } ?>
                </a>
            </div>
        </div>

        <!-- <div class="clearfix visible-xs-block"></div> -->
        <div class="col-lg-6 col-md-6 col-sm-6">
            <div class="tour_list_desc">
                <?php /* if (!empty($review)) : ?>
                    <div class="score"><?php echo esc_html($review_content) ?>
                        <span><?php echo esc_html($doubled_review) ?></span>
                    </div>
                <?php endif; */ ?>

                <div class="rating">
                    <?php ct_rating_smiles($star, 'icon-star-empty', 'icon-star voted') ?>
                </div>

                <h3><a href="<?php echo $url; ?>"><?php echo esc_html(get_the_title($post_id)); ?></a></h3>

                <p><?php echo wp_kses_post($brief); ?></p>
                <?php
                $hotel_facilities = wp_get_post_terms($post_id, 'hotel_facility');
                ?>
                <ul class="add_info">
                    <?php
                    foreach ($hotel_facilities as $hotel_term) :
                        $term_id = $hotel_term->term_id;
                        $icon_class = get_tax_meta($term_id, 'ct_tax_icon_class', true);
                        echo '<li><a href="javascript:void(0);" class="tooltip-1" data-placement="top" title="' . esc_html($hotel_term->name) . '">';
                        if (!empty($icon_class))
                            echo '<i class="' . esc_attr($icon_class) . '"></i>';
                        echo '</a></li>';
                    endforeach;
                    ?>

                </ul>
            </div>

        </div>

        <div class="col-lg-2 col-md-2 col-sm-2">
            <div class="price_list">
                <div>
                    <?php echo ct_price($price, 'special') ?><small><?php echo esc_html__('سعر الغرفة / الليلة', 'citytours') ?></small>
                    <p><a href="<?php echo $url; ?>" class="btn_1"><?php echo esc_html__('أتفرج وأحجز', 'citytours') ?></a></p>
                </div>
            </div>
        </div>

    </div>

</div>
<?php } ?>
<!--End strip -->
