<?php
/* Page Template for Single Tour */
if (!defined('ABSPATH')) {
    exit;
}

get_header();

if (have_posts()) {
    while (have_posts()) : the_post();

        //init variables
        $post_id = get_the_ID();
        
        $titele = esc_html(get_the_title($post_id));

        $is_repeated = get_post_meta($post_id, '_tour_repeated', true);
        $tour_start_date = get_post_meta($post_id, '_tour_start_date', true);
        $tour_end_date = get_post_meta($post_id, '_tour_end_date', true);
        $tour_available_days = get_post_meta($post_id, '_tour_available_days');

        $person_price = get_post_meta($post_id, '_tour_price', true);
        if (empty($person_price))
            $person_price = 0;

        $charge_child = get_post_meta($post_id, '_tour_charge_child', true);
        $child_price = get_post_meta($post_id, '_tour_price_child', true);

        $slider = get_post_meta($post_id, '_tour_slider', true);
        $schedule_info = get_post_meta($post_id, '_tour_schedule_info', true);

        $review = get_post_meta($post_id, '_review', true);
        $review = (!empty($review)) ? round($review, 1) : 0;

        $is_fixed_sidebar = get_post_meta($post_id, '_tour_fixed_sidebar', true);

        $tour_setting = get_post_meta($post_id, '_tour_booking_type', true);
        $tour_setting = empty($tour_setting) ? 'default' : $tour_setting;
        $inquiry_form = get_post_meta($post_id, '_tour_inquiry_form', true);
        $external_link = get_post_meta($post_id, '_tour_external_link', true);
        $external_link = empty($external_link) ? '#' : $external_link;

        $address = get_post_meta($post_id, '_tour_address', true);
        $tour_pos = get_post_meta($post_id, '_tour_loc', true);
        if (!empty($tour_pos))
            $tour_pos = explode(',', $tour_pos);

        $related_ht = get_post_meta($post_id, '_tour_related');

        $header_img_scr = ct_get_header_image_src($post_id);
        if (!empty($header_img_scr)) {
            $header_img_height = ct_get_header_image_height($post_id);
?>

            <section class="parallax-window" data-parallax="scroll" data-image-src="<?php echo esc_url($header_img_scr) ?>" data-natural-width="1400" data-natural-height="200">
            <?php /* 
            <div class="parallax-content-2">
                <div class="container">
                    <div class="row">
                        <div class="col-md-8 col-sm-8">
                            <span class="rating"><?php ct_rating_smiles($review) ?><small>(<?php echo esc_html(ct_get_review_count($post_id)) ?>)</small></span>
                            <h1><?php the_title() ?></h1>
                            <span><?php echo esc_html($address, 'citytours'); ?></span>
                        </div>
                        <div class="col-md-4 col-sm-4">
                            <div id="price_single_main">
                                <?php echo esc_html__('from/per person', 'citytours') ?> <?php echo ct_price($person_price, "special") ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            */ ?>
            </section><!-- End section -->

            <div id="position">

            <?php
        } else {
            ?>
            
            
            
                <div id="position" class="blank-parallax">
                <?php } ?>

                <div class="container"><?php ct_breadcrumbs(); ?></div>
                </div><!-- End Position -->

                <div class="collapse" id="collapseMap">
                    <div id="map" class="map"></div>
                </div>

                <div class="container margin_30">
                    <div class="row top-parent">
                        <div class="col-md-8 col-sm-8">
                            <div class="single-hotel-heading">
                                <?php $review_count = (int) ct_get_review_count($post_id); ?>
                                <?php if ($review_count > 0) : ?>
                                <span class="rating">
                                    <?php ct_rating_smiles($review) ?><small>(<?php echo esc_html($review_count) ?>)</small>
                                </span>
                                <?php endif; ?>
                                <h1><?php the_title() ?></h1>
                                <span><?php echo esc_html($address, 'citytours'); ?></span>
                            </div>
                        </div>
                        <div class="col-md-4 col-sm-4">
                            <div id="price_single_main">
                                <?php echo esc_html__('from/per person', 'citytours') ?> <?php echo ct_price($person_price, "special") ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="container margin_60 bottom-sec-sing-hotel">

                    <div class="row">
                        <div class="col-md-8" id="single_tour_desc">



                            <p class="visible-sm visible-xs"><a class="btn_map" data-toggle="collapse" href="#collapseMap" aria-expanded="false" aria-controls="collapseMap"><?php echo __('View on map', 'citytours') ?></a></p>

                            <?php if (!empty($slider)) : ?>
                                <?php echo do_shortcode($slider); ?>
                                <hr>
                            <?php endif; ?>

                            <div id="single_tour_feat">
                                <ul>
                                    <?php
                                    require_once(get_template_directory() . '/inc/lib/tax-meta-class/Tax-meta-class.php');

                                    //                                $tour_types = wp_get_post_terms($post_id, 'tour_type');
                                    $tour_facilities = wp_get_post_terms($post_id, 'tour_facility');

                                    if (!isset($tour_types) || is_wp_error($tour_types))
                                        $tour_types = array();
                                    if (!$tour_facilities || is_wp_error($tour_facilities))
                                        $tour_facilities = array();

                                    $tour_terms = array_merge($tour_types, $tour_facilities);
                                    foreach ($tour_terms as $tour_term) :
                                        $term_id = $tour_term->term_id;
                                        $icon_class = get_tax_meta($term_id, 'ct_tax_icon_class', true);
                                        echo '<li>';
                                        if (!empty($icon_class))
                                            echo '<i class="' . esc_attr($icon_class) . '"></i>';
                                        echo esc_html($tour_term->name);
                                        echo '</li>';
                                    endforeach;
                                    ?>
                                </ul>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <?php the_content(); ?>
                                </div>
                            </div>

                            <hr>





                            <?php if (!empty($schedule_info)) : ?>
                                <div class="row">
                                    <div class="col-md-12">
                                        <h3><?php echo esc_html__('Schedule', 'citytours') ?></h3>
                                    </div>

                                    <div class="col-md-12">
                                        <?php echo do_shortcode($schedule_info); ?>
                                    </div>
                                </div>
                                <hr>
                            <?php endif; ?>

                            <div class="row">
                                <form method="get" id="booking-form" action="<?php echo esc_url(ct_get_tour_cart_page()); ?>">
                                    <input type="hidden" name="tour_id" value="<?php echo esc_attr($post_id) ?>">
                                    <?php if (!empty($is_repeated)) : ?>
                                        <div class="col-md-6 col-sm-6">
                                            <div class="form-group">
                                                <label><i class="icon-calendar-7"></i> <?php echo esc_html__('Select a date', 'citytours') ?></label>
                                                <input class="date-pick form-control" data-date-format="<?php echo ct_get_date_format('html'); ?>" type="text" name="date" autocomplete="off">
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                    <div class="col-md-12">
                                        <h3><?php echo esc_html__('Tour prices', 'citytours') ?></h3>
                                    </div>
                                    <div class="col-md-6 col-sm-6">
                                        <table class="table table-striped cart-list tour">
                                            <tbody>
                                                <?php
                                                $price_per_person = get_post_meta($post_id, '_tour_price', true);
                                                if (!empty($price_per_person)) :
                                                ?>
                                                    <tr>
                                                        <td data-title="<?php esc_attr_e('Item', 'citytours') ?>">
                                                            <?php esc_html_e('Price Per Person', 'citytours') ?>
                                                        </td>
                                                        <td data-title="<?php esc_attr_e('Price', 'citytours') ?>">
                                                            <?php echo $price_per_person; ?>
                                                        </td>
                                                    </tr>
                                                <?php
                                                endif;
                                                $price_in_double_room = get_post_meta($post_id, '_tour_price_in_double_room', true);
                                                if (!empty($price_in_double_room)) :
                                                ?>
                                                    <tr>
                                                        <td data-title="<?php esc_attr_e('Item', 'citytours') ?>">
                                                            <?php esc_html_e('Price Per Person in Double Room', 'citytours') ?>
                                                        </td>
                                                        <td data-title="<?php esc_attr_e('Price', 'citytours') ?>">
                                                            <?php echo $price_in_double_room; ?>
                                                        </td>
                                                    </tr>
                                                <?php
                                                endif;
                                                $price_in_triple_room = get_post_meta($post_id, '_tour_price_in_triple_room', true);
                                                if (!empty($price_in_triple_room)) :
                                                ?>
                                                    <tr>
                                                        <td data-title="<?php esc_attr_e('Item', 'citytours') ?>">
                                                            <?php esc_html_e('Price Per Person in Triple Room', 'citytours') ?>
                                                        </td>
                                                        <td data-title="<?php esc_attr_e('Price', 'citytours') ?>">
                                                            <?php echo $price_in_triple_room; ?>
                                                        </td>
                                                    </tr>
                                                <?php
                                                endif;
                                                $price_in_single_room = get_post_meta($post_id, '_tour_price_in_single_room', true);
                                                if (!empty($price_in_single_room)) :
                                                ?>
                                                    <tr>
                                                        <td data-title="<?php esc_attr_e('Item', 'citytours') ?>">
                                                            <?php esc_html_e('Price Per Person in single Room', 'citytours') ?>
                                                        </td>
                                                        <td data-title="<?php esc_attr_e('Price', 'citytours') ?>">
                                                            <?php echo $price_in_single_room; ?>
                                                        </td>
                                                    </tr>
                                                <?php
                                                endif;
                                                $price_in_quad_room = get_post_meta($post_id, '_tour_price_in_quad_room', true);
                                                if (!empty($price_in_quad_room)) :
                                                ?>
                                                    <tr>
                                                        <td data-title="<?php esc_attr_e('Item', 'citytours') ?>">
                                                            <?php esc_html_e('Price Per Person in Quad Room', 'citytours') ?>
                                                        </td>
                                                        <td data-title="<?php esc_attr_e('Price', 'citytours') ?>">
                                                            <?php echo $price_in_quad_room; ?>
                                                        </td>
                                                    </tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="col-md-6">

                                        <table class="table table-striped cart-list tour">
                                            <tbody>
                                                <?php
                                                if ($charge_child) :
                                                    $price_child = get_post_meta($post_id, '_tour_price_child', true);
                                                    if (!empty($price_child)) :
                                                ?>
                                                        <tr>
                                                            <td data-title="<?php esc_attr_e('Item', 'citytours') ?>">
                                                                <?php esc_html_e('Price Per Child', 'citytours') ?>
                                                            </td>
                                                            <td data-title="<?php esc_attr_e('Price', 'citytours') ?>">
                                                                <?php echo $price_child; ?>
                                                            </td>
                                                        </tr>
                                                    <?php
                                                    endif;
                                                    $price_child_0_2 = get_post_meta($post_id, '_tour_price_child_0_2', true);
                                                    if (!empty($price_child_0_2)) :
                                                    ?>
                                                        <tr>
                                                            <td data-title="<?php esc_attr_e('Item', 'citytours') ?>">
                                                                <?php esc_html_e('Price child less than 2 years', 'citytours') ?>
                                                            </td>
                                                            <td data-title="<?php esc_attr_e('Price', 'citytours') ?>">
                                                                <?php echo $price_child_0_2; ?>
                                                            </td>
                                                        </tr>
                                                    <?php
                                                    endif;
                                                    $price_child_2_6 = get_post_meta($post_id, '_tour_price_child_2_6', true);
                                                    if (!empty($price_child_2_6)) :
                                                    ?>
                                                        <tr>
                                                            <td data-title="<?php esc_attr_e('Item', 'citytours') ?>">
                                                                <?php esc_html_e('Price child from 2 to 6', 'citytours') ?>
                                                            </td>
                                                            <td data-title="<?php esc_attr_e('Price', 'citytours') ?>">
                                                                <?php echo $price_child_2_6; ?>
                                                            </td>
                                                        </tr>
                                                    <?php
                                                    endif;
                                                    $price_child_6_11 = get_post_meta($post_id, '_tour_price_child_6_11', true);
                                                    if (!empty($price_child_6_11)) :
                                                    ?>
                                                        <tr>
                                                            <td data-title="<?php esc_attr_e('Item', 'citytours') ?>">
                                                                <?php esc_html_e('Price child from 6 to 11', 'citytours') ?>
                                                            </td>
                                                            <td data-title="<?php esc_attr_e('Price', 'citytours') ?>">
                                                                <?php echo $price_child_6_11; ?>
                                                            </td>
                                                        </tr>
                                                <?php
                                                    endif;
                                                endif;
                                                ?>
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="col-md-12">
                                        <button type="submit" class="btn_full book-now"><?php echo esc_html__('Book now', 'citytours') ?></button>
                                    </div>

                                </form>
                            </div>

                            <hr>

                            <div class="row related-sec">
                                <div class="col-md-12">
                                    <h3><?php echo esc_html__('Related Tours', 'citytours') ?></h3>
                                </div>
                                <div class="col-md-12">

                                    <div class="row">
                                        <?php
                                        $args = array(
                                            'post_type' => array('hotel', 'tour'),
                                            'numberposts' => 4,
                                            'post__in' => $related_ht
                                        );
                                        $posts = get_posts($args);

                                        $query_args = array(
                                            'date_from' => $date_from ?? '',
                                            'date_to' => $date_to ?? ''
                                        );
                                        foreach ($posts as $post) :
                                        ?>
                                            <div class="col-sm-6">
                                                <div class="tour_container">
                                                    <div class="img_container">
                                                        <a href="<?php echo esc_url(add_query_arg(array(), get_permalink($post->ID))); ?>">
                                                            <?php echo get_the_post_thumbnail($post->ID, array(365, 235), array('class' => 'img-responsive')); ?>
                                                        </a>
                                                    </div>
                                                    <div class="tour_title">
                                                        <h3><strong> <?php echo esc_html(get_the_title($post->ID)); ?> </strong></h3>
                                                        <div class="wbooking">
                                                            <a href="<?php echo esc_url(add_query_arg(array(), get_permalink($post->ID))); ?>" class="btn_1 btn-sm"> <?php esc_html_e('Watch and Book', 'citytours') ?> </a>
                                                        </div>
                                                    </div>
                                                </div>
                                                <!-- End box tour -->
                                            </div>
                                            <!-- End col-md-6 -->
                                        <?php endforeach; 
                                        
                                        wp_reset_postdata();

?>

                                    </div>
                                    <!-- End row -->
                                </div>
                            </div>

                            <hr>

                            <?php
                            global $ct_options;

                            if (!empty($ct_options['tour_review'])) :
                                $review_fields = !empty($ct_options['tour_review_fields']) ? explode(",", $ct_options['tour_review_fields']) : array("Position", "Comfort", "Price", "Quality");
                                $review = get_post_meta(ct_tour_org_id($post_id), '_review', true);
                                $review = round((!empty($review)) ? (float) $review : 0, 1);
                                $review_detail = get_post_meta(ct_tour_org_id($post_id), '_review_detail', true);

                                if (!empty($review_detail)) {
                                    $review_detail = is_array($review_detail) ? $review_detail : unserialize($review_detail);
                                } else {
                                    $review_detail = array_fill(0, count($review_fields), 0);
                                }
                            ?>

                                <div class="row">
                                    <div class="col-md-3">
                                        <h3><?php echo esc_html__('Reviews', 'citytours') ?></h3>

                                        <a href="#" class="btn rate-btn add_bottom_15" data-toggle="modal" data-target="#myReview"><?php echo esc_html__('Leave a review', 'citytours') ?></a>
                                    </div>

                                    <div class="col-md-9">
                                        <?php $review_count = isset($review_count) ? (int) $review_count : (int) ct_get_review_count($post_id); ?>
                                        <div id="general_rating"><?php if ($review_count > 0) { echo sprintf(esc_html__('%d Reviews', 'citytours'), $review_count); } ?>
                                            <?php if ($review_count > 0) : ?><div class="rating"><?php echo ct_rating_smiles($review) ?></div><?php endif; ?>
                                        </div>

                                        <div class="row" id="rating_summary">
                                            <div class="col-md-6">
                                                <ul>
                                                    <?php
                                                    for ($i = 0; $i < (count($review_fields) / 2); $i++) {
                                                    ?>
                                                        <li><?php echo esc_html($review_fields[$i], 'citytours'); ?>
                                                            <div class="rating"><?php echo ct_rating_smiles($review_detail[$i]) ?></div>
                                                        </li>
                                                    <?php } ?>
                                                </ul>
                                            </div>
                                            <div class="col-md-6">
                                                <ul>
                                                    <?php
                                                    for ($i = $i; $i < count($review_fields); $i++) {
                                                    ?>
                                                        <li><?php echo esc_html($review_fields[$i], 'citytours'); ?>
                                                            <div class="rating"><?php echo ct_rating_smiles($review_detail[$i]) ?></div>
                                                        </li>
                                                    <?php } ?>
                                                </ul>
                                            </div>
                                        </div><!-- End row -->

                                        <hr>

                                        <div class="guest-reviews">
                                            <?php
                                            $per_page = 10;
                                            $review_html = ct_get_review_html($post_id, 0, $per_page);

                                            echo $review_html['html'];

                                            if ($review_html['count'] >= $per_page) {
                                            ?>

                                                <a href="#" class="btn more-review" data-post_id="<?php echo esc_attr($post_id) ?>"><?php echo esc_html__('Load More Reviews', 'citytours') ?></a>

                                            <?php
                                            }
                                            ?>
                                        </div>
                                    </div>
                                </div>

                            <?php
                            endif;
                            ?>

                        </div>
                        <!--End  single_tour_desc-->

                        <aside class="col-md-4" <?php if ($is_fixed_sidebar) echo 'id="sidebar"'; ?>>

                            <p class="hidden-sm hidden-xs">
                                <a class="btn_map" data-toggle="collapse" href="#collapseMap" aria-expanded="false" aria-controls="collapseMap"><?php echo __('View on map', 'citytours') ?></a>
                            </p>

                            <?php if ($is_fixed_sidebar) : ?>
                                <div class="theiaStickySidebar">
                                <?php endif; ?>

                                <?php if ('empty' != $tour_setting) : ?>

                                    <div class="box_style_1 expose">
                                        <h3 class="inner">- <?php echo esc_html__('Modify search in tours', 'citytours') ?> -</h3>

                                        <?php if ('external' == $tour_setting) : ?>
                                            <a href="<?php echo esc_url($external_link) ?>" class="btn_full"><?php echo esc_html__('Check now', 'citytours') ?></a>
                                        <?php elseif ('default' == $tour_setting) : ?>

                                            <form role="search" method="get" id="search-tour-form" action="<?php echo esc_url(home_url('/')); ?>">
                                                <input type="hidden" name="post_type" value="tour">

                                                <div class="form-group">
                                                    <label><?php esc_html_e('Things to do', 'citytours') ?></label>
                                                    <?php $all_tour_types = ct_unique_tour_types_by_name(get_terms('tour_type', array('hide_empty' => false, 'parent' => 0)));
                                                    if (!empty($all_tour_types)) :
                                                    ?>
                                                        <select class="form-control" name="type" id="tour_types">
                                                            <option value="" selected><?php esc_html_e('All tours', 'citytours') ?></option>
                                                            <?php
                                                            foreach ($all_tour_types as $each_tour_type) {
                                                                $term_id = $each_tour_type->term_id;
                                                                $icon_class = get_tax_meta($term_id, 'ct_tax_icon_class');
                                                            ?>
                                                                <option value="<?php echo esc_attr($term_id) ?>" <?php echo (!empty($tour_type) && $term_id == $tour_type) ? "selected='selected'" : ""; ?>><?php echo esc_html($each_tour_type->name) ?></option>
                                                            <?php } ?>
                                                        </select>
                                                    <?php endif; ?>
                                                </div>

                                                <div class="form-group">
                                                    <label>
                                                        <?php esc_html_e('tour search terms', 'citytours') ?>
                                                    </label>
                                                    <select class="form-control" name="city" id="tour_city" >
                                                        <option value="" selected><?php esc_html_e('Select city', 'citytours') ?></option>
                                                    </select>
                                                </div>

                                                <button class="btn_1 green"><?php esc_html_e('Search again', 'citytours') ?></button>
                                            </form>
                                            

                                        <?php elseif ('inquiry' == $tour_setting) :
                                            echo do_shortcode($inquiry_form);
                                        endif;
                                        ?>

                                        <hr>

                                        <?php
                                        if (!empty($ct_options['wishlist'])) :
                                            if (is_user_logged_in()) {
                                                $user_id = get_current_user_id();
                                                $wishlist = get_user_meta($user_id, 'wishlist', true);
                                                if (empty($wishlist))
                                                    $wishlist = array();
                                        ?>

                                                <a class="btn_full_outline btn-add-wishlist" href="#" data-label-add="<?php esc_html_e('Add to wishlist', 'citytours'); ?>" data-label-remove="<?php esc_html_e('Remove from wishlist', 'citytours'); ?>" data-post-id="<?php echo esc_attr($post_id) ?>" <?php echo (in_array(ct_tour_org_id($post_id), $wishlist)) ? ' style="display:none;"' : '' ?>><i class=" icon-heart"></i> <?php echo esc_html__('Add to wishlist', 'citytours') ?></a>
                                                <a class="btn_full_outline btn-remove-wishlist" href="#" data-label-add="<?php esc_html_e('Add to wishlist', 'citytours'); ?>" data-label-remove="<?php esc_html_e('Remove from wishlist', 'citytours'); ?>" data-post-id="<?php echo esc_attr($post_id) ?>" <?php echo (!in_array(ct_tour_org_id($post_id), $wishlist)) ? ' style="display:none;"' : '' ?>><i class=" icon-heart"></i> <?php esc_html_e('Remove from wishlist', 'citytours'); ?></a>

                                            <?php
                                            } else {
                                            ?>

                                                <div><?php esc_html_e('To save your wishlist please login.', 'citytours'); ?></div>

                                                <?php
                                                if (empty($ct_options['login_page'])) {
                                                ?>
                                                    <a href="#" class="btn_full_outline"><?php esc_html_e('login', 'citytours'); ?></a>
                                                <?php
                                                } else {
                                                ?>
                                                    <a href="<?php echo esc_url(ct_get_permalink_clang($ct_options['login_page'])); ?>" class="btn_full_outline"><?php esc_html_e('login', 'citytours'); ?></a>
                                                <?php } ?>

                                        <?php
                                            }
                                        endif;
                                        ?>

                                    </div>
                                    <!--/box_style_1 -->

                                <?php endif; ?>

                                <?php if (is_active_sidebar('sidebar-tour')) : ?>
                                    <?php dynamic_sidebar('sidebar-tour'); ?>
                                <?php endif; ?>

                                <?php if ($is_fixed_sidebar) : ?>
                                </div>
                            <?php endif; ?>

                        </aside>

                    </div>
                    <!--End row -->

                </div>
                <!--End container -->

                <?php
                if (!empty($ct_options['tour_review'])) :
                ?>

                    <div class="modal fade" id="myReview" tabindex="-1" role="dialog" aria-labelledby="myReviewLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">x</span></button>

                                    <h4 class="modal-title" id="myReviewLabel"><?php echo esc_html__('Write your review', 'citytours') ?></h4>
                                </div>

                                <div class="modal-body">
                                    <form method="post" action="<?php echo esc_url(admin_url('admin-ajax.php')) ?>" name="review" id="review-form">
                                        <?php wp_nonce_field('post-' . $post_id, '_wpnonce', false); ?>
                                        <input type="hidden" name="post_id" value="<?php echo esc_attr($post_id); ?>">
                                        <input type="hidden" name="action" value="submit_review">

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <input name="booking_no" id="booking_no" type="text" placeholder="<?php echo esc_html__('Booking No', 'citytours') ?>" class="form-control">
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <input name="pin_code" id="pin_code" type="text" placeholder="<?php echo esc_html__('Pin Code', 'citytours') ?>" class="form-control">
                                                </div>
                                            </div>
                                        </div>

                                        <hr>

                                        <div class="row">
                                            <?php
                                            for ($i = 0; $i < (count($review_fields)); $i++) {
                                            ?>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label><?php echo esc_html($review_fields[$i], 'citytours'); ?></label>
                                                        <select class="form-control" name="review_rating_detail[<?php echo esc_attr($i) ?>]">
                                                            <option value="0"><?php esc_html_e("Please review", 'citytours'); ?></option>
                                                            <option value="1"><?php esc_html_e("Low", 'citytours'); ?></option>
                                                            <option value="2"><?php esc_html_e("Sufficient", 'citytours'); ?></option>
                                                            <option value="3"><?php esc_html_e("Good", 'citytours'); ?></option>
                                                            <option value="4"><?php esc_html_e("Excellent", 'citytours'); ?></option>
                                                            <option value="5"><?php esc_html_e("Super", 'citytours'); ?></option>
                                                        </select>
                                                    </div>
                                                </div>
                                            <?php } ?>
                                        </div>

                                        <div class="form-group">
                                            <textarea name="review_text" id="review_text" class="form-control" style="height:100px" placeholder="<?php esc_html_e("Write your review", 'citytours'); ?>"></textarea>
                                        </div>

                                        <input type="submit" value="ارسـل" class="btn_1" id="submit-review">
                                    </form>

                                    <div id="message-review" class="alert alert-warning">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                <?php
                endif;
                ?>

                <?php
                $tour_start_date_milli_sec = 0;
                if (!empty($tour_start_date)) {
                    $tour_start_date_milli_sec = strtotime($tour_start_date) * 1000;
                }

                $tour_end_date_milli_sec = 9999 * 365 * 24 * 60 * 60 * 1000;
                if (!empty($tour_end_date)) {
                    $tour_end_date_milli_sec = strtotime($tour_end_date) * 1000;
                }
                ?>
                <?php ct_google_maps_script(); ?>
                <script type="text/javascript">
                    $ = jQuery.noConflict();
                    $('#ssearch-tour-form input').val('');
                    var price_per_person = 0,
                        price_per_child = 0,
                        exchange_rate = 1;
                    <?php if (!empty($person_price)) : ?>
                        price_per_person = <?php echo esc_js($person_price); ?>;
                    <?php endif; ?>
                    <?php if (!empty($child_price)) : ?>
                        price_per_child = <?php echo esc_js($child_price); ?>;
                    <?php endif; ?>
                    <?php if (!empty($_SESSION['exchange_rate'])) : ?>
                        exchange_rate = <?php echo esc_js($_SESSION['exchange_rate']); ?>;
                    <?php endif; ?>

                    function update_tour_price() {
                        var adults = $('input#adults').val();
                        var children = price = total_price = 0;
                        if ($('input#children').length) {
                            children = $('input#children').val();
                        }

                        price = +((adults * price_per_person + children * price_per_child) * exchange_rate).toFixed(2);
                        $('.child-amount').toggleClass('hide', children < 1);
                        total_price = $('.total-cost').text().replace(/[\d\.\,]+/g, price);
                        $('.total-cost').text(total_price);
                    }

                    $(document).ready(function() {
                        var available_days = <?php echo json_encode($tour_available_days); ?>;
                        var today = new Date();
                        var tour_start_date = new Date(<?php echo $tour_start_date_milli_sec; ?>);
                        var tour_end_date = new Date(<?php echo $tour_end_date_milli_sec; ?>);
                        var available_first_date = tour_end_date;
                        var lang = '<?php echo get_locale() ?>';
                        lang = lang.replace('_', '-');
                        today.setHours(0, 0, 0, 0);
                        tour_start_date.setHours(0, 0, 0, 0);
                        tour_end_date.setHours(0, 0, 0, 0);
                        if (today > tour_start_date) {
                            tour_start_date = today;
                        }

                        function DisableDays(date) {
                            var day = date.getDay();
                            if (available_days.length == 0) {
                                if (available_first_date >= date && date >= tour_start_date) {
                                    available_first_date = date;
                                }
                                return true;
                            }

                            if ($.inArray(day.toString(), available_days) >= 0) {
                                if (available_first_date >= date && date >= tour_start_date) {
                                    available_first_date = date;
                                }
                                return true;
                            } else {
                                return false;
                            }
                        }

                        if ($('input.date-pick').length) {
                            if (lang.substring(0, 2) != 'fa') {
                                $('input.date-pick').datepicker({
                                    startDate: tour_start_date,
                                    <?php if ($tour_end_date_milli_sec > 0) : ?>
                                        endDate: tour_end_date,
                                    <?php endif; ?>
                                    beforeShowDay: DisableDays,
                                    language: lang
                                });
                                $('input[name="date"]').datepicker('setDate', available_first_date);
                            } else {
                                var date_format = $('input.date-pick').data('date-format');
                                $('input.date-pick').persianDatepicker({
                                    observer: true,
                                    format: date_format.toUpperCase(),
                                });
                            }
                        }

                        // if ( $('input.time-pick').length ) {
                        //  $('input.time-pick').timepicker({
                        //      minuteStep: 15,
                        //      showInpunts: false
                        //  });
                        // }

                        $('input#adults').on('change', function() {
                            $('.adults-number').html($(this).val());
                            update_tour_price();
                        });
                        $('input#children').on('change', function() {
                            $('.children-number').html($(this).val());
                            update_tour_price();
                        });
                        var validation_rules = {};
                        if ($('input.date-pick').length) {
                            validation_rules.date = {
                                required: true
                            };
                        }
                        //validation form
                        $('#booking-form').validate({
                            rules: validation_rules
                        });
                        $('#sidebar').theiaStickySidebar({
                            additionalMarginTop: 80
                        });
                        $('#collapseMap').on('shown.bs.collapse', function(e) {
                            var markersData = {
                                <?php
                                foreach ($related_ht as $each_ht) {
                                    if (get_post_type($each_ht) == 'hotel') {
                                        $each_pos = get_post_meta($each_ht, '_hotel_loc', true);
                                        $post_type = 'Hotels';
                                    } else {
                                        $each_pos = get_post_meta($each_ht, '_tour_loc', true);
                                        $post_type = 'Tours';
                                    }

                                    if (!empty($each_pos)) {
                                        $each_pos = explode(',', $each_pos);
                                        $description = str_replace("'", "\'", wp_trim_words(strip_shortcodes(get_post_field("post_content", $each_ht)), 20, '...'));
                                ?> '<?php echo $each_ht ?>': [{
                                            name: '<?php echo get_the_title($each_ht) ?>',
                                            type: '<?php echo $post_type ?>',
                                            location_latitude: <?php echo $each_pos[0] ?>,
                                            location_longitude: <?php echo $each_pos[1] ?>,
                                            map_image_url: '<?php echo ct_get_header_image_src($each_ht, "ct-map-thumb") ?>',
                                            name_point: '<?php echo get_the_title($each_ht) ?>',
                                            description_point: '<?php echo $description ?>',
                                            url_point: '<?php echo get_permalink($each_ht) ?>'
                                        }],
                                    <?php
                                    }
                                }
                                if (!empty($tour_pos)) {
                                    $description = str_replace("'", "\'", wp_trim_words(strip_shortcodes(get_post_field("post_content", $post_id)), 20, '...'));
                                    ?> 'Center': [{
                                        name: '<?php echo $titele;  ?>',
                                        type: 'Tours',
                                        location_latitude: <?php echo $tour_pos[0] ?>,
                                        location_longitude: <?php echo $tour_pos[1] ?>,
                                        map_image_url: '<?php echo ct_get_header_image_src($post_id, "ct-map-thumb") ?>',
                                        name_point: '<?php echo $titele;  ?>',
                                        description_point: '<?php echo $description ?>',
                                        url_point: '<?php echo get_permalink($post_id) ?>'
                                    }, ]
                                <?php }
                                ?>
                            };
                            <?php
                            if (empty($tour_pos)) {
                                foreach ($related_ht as $each_ht) {
                                    if (get_post_type($each_ht) == 'hotel') {
                                        $each_pos = get_post_meta($each_ht, '_hotel_loc', true);
                                    } else {
                                        $each_pos = get_post_meta($each_ht, '_tour_loc', true);
                                    }

                                    if (!empty($each_pos)) {
                                        $tour_pos = explode(',', $each_pos);
                                        break;
                                    }
                                }
                            }

                            if (!empty($tour_pos)) {
                            ?>
                                var lati = <?php echo $tour_pos[0] ?>;
                                var long = <?php echo $tour_pos[1] ?>;
                                // var _center = [48.865633, 2.321236];
                                var _center = [lati, long];
                                renderMap(_center, markersData, 14, google.maps.MapTypeId.ROADMAP, false);
                            <?php } ?>
                        });
                    });
                </script>

        <?php
    endwhile;
}

get_footer();
