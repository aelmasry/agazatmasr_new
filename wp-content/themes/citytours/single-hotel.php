<?php
/* Page Template for Single Hotel */
if (!defined('ABSPATH')) {
    exit;
}

get_header();

if (have_posts()) {

    while (have_posts()) : the_post();
        $post_id = get_the_ID();
        
        $titele = esc_html(get_the_title($post_id));
    
        $address = get_post_meta($post_id, '_hotel_address', true);
//        $person_price = get_post_meta($post_id, '_hotel_price', true);
//        if (empty($person_price))
//            $person_price = 0;

        $slider = get_post_meta($post_id, '_hotel_slider', true);
        $star_rating = get_post_meta($post_id, '_hotel_star', true);
        $minimum_stay = get_post_meta($post_id, '_hotel_minimum_stay', true);

        $hotel_pos = get_post_meta($post_id, '_hotel_loc', true);
        $related_ht = get_post_meta($post_id, '_hotel_related');

        $hotel_setting = get_post_meta($post_id, '_hotel_booking_type', true);
        $hotel_setting = empty($hotel_setting) ? 'default' : $hotel_setting;
        $inquiry_form = get_post_meta($post_id, '_hotel_inquiry_form', true);
        $external_link = get_post_meta($post_id, '_hotel_external_link', true);
        $external_link = empty($external_link) ? '#' : $external_link;

        $is_fixed_sidebar = get_post_meta($post_id, '_hotel_fixed_sidebar', true);

        $date_from = (!empty($_GET['date_from']) ) ? ct_get_phptime($_GET['date_from']) : date("Y-m-d", strtotime('+1 day'));
        $date_to = (!empty($_GET['date_to']) ) ? ct_get_phptime($_GET['date_to']) : date("Y-m-d", strtotime($date_from . ' + 1day'));
        // var_dump($date_from);
//        $date_to = (!empty($_GET['date_to']) ) ? ct_get_phptime($_GET['date_to']) : date("Y-m-d", strtotime($date_from . ' +' . $minimum_stay . 'day'));

        $days = ct_get_day_interval($date_from, $date_to);

        $person_price = ct_hotel_average_price($post_id, $date_from, $date_to);
        if (empty($person_price))
            $person_price = 0;

        if (!empty($hotel_pos)) {
            $hotel_pos = explode(',', $hotel_pos);
        }

        $available_rooms = ct_hotel_get_available_rooms($post_id, $date_from, $date_to);

        if (is_array($available_rooms) && !empty($available_rooms)) {
            $keys = array_keys($available_rooms);
            $sql = "SELECT * FROM " . $wpdb->posts . " posts
                    INNER JOIN " . $wpdb->postmeta . " postmeta ON postmeta.post_id = posts.ID
                    WHERE posts.ID IN (" . implode(',', $keys) . ") group by posts.ID";
            $room_types = $wpdb->get_results($sql);
        }

        $header_img_scr = ct_get_header_image_src($post_id);
        if (!empty($header_img_scr)) {
            $header_img_height = ct_get_header_image_height($post_id);
            ?>

            <section class="parallax-window" data-parallax="scroll" data-image-src="<?php echo esc_url($header_img_scr) ?>" data-natural-width="1400" data-natural-height="200" >
                <?php /*
                <div class="parallax-content-2">
                    <div class="container">
                        <div class="row">
                            <div class="col-md-8 col-sm-8">
                                <span class="rating">
                                    <?php ct_rating_smiles($star_rating, 'icon-star-empty', 'icon-star voted'); ?>
                                </span>
                                <h1><?php the_title() ?></h1>
                                <span><?php echo esc_html($address, 'citytours'); ?></span>
                            </div>
                            <div class="col-md-4 col-sm-4">
                                <div id="price_single_main">
                                    <?php echo esc_html__('from/per night', 'citytours') ?> <?php echo ct_price($person_price, "special") ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                */ ?>
            </section><!-- End section -->
            <div id="position">

            <?php } else { ?>
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
                            <span class="rating">
                                <?php ct_rating_smiles($star_rating, 'icon-star-empty', 'icon-star voted'); ?>
                            </span>
                            <h1><?php the_title() ?></h1>
                            <span><?php echo esc_html($address, 'citytours'); ?></span>
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-4">
                        <div id="price_single_main">
                            <?php echo esc_html__('from/per night', 'citytours') ?> <?php echo ct_price($person_price, "special") ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="container margin_60 bottom-sec-sing-hotel">
                <div class="row">
                    <div class="col-md-8" id="single_hotel_desc">

                        <p class="visible-sm visible-xs"><a class="btn_map" data-toggle="collapse" href="#collapseMap" aria-expanded="false" aria-controls="collapseMap"><?php echo __('View on map', 'citytours') ?></a></p>

                        <?php if (!empty($slider)) : ?>
                            <?php echo do_shortcode($slider); ?>
                            <hr>
                        <?php endif; ?>


                        <div id="single_tour_feat">
                            <ul>
                                <?php
                                require_once(get_template_directory() . '/inc/lib/tax-meta-class/Tax-meta-class.php');

                                $hotel_facilities = wp_get_post_terms($post_id, 'hotel_facility');

                                if (!$hotel_facilities || is_wp_error($hotel_facilities))
                                    $hotel_facilities = array();

                                foreach ($hotel_facilities as $hotel_term) :
                                    $term_id = $hotel_term->term_id;
                                    $icon_class = get_tax_meta($term_id, 'ct_tax_icon_class', true);
                                    echo '<li>';
                                    if (!empty($icon_class))
                                        echo '<i class="' . esc_attr($icon_class) . '"></i>';
                                    echo esc_html($hotel_term->name);
                                    echo '</li>';
                                endforeach;
                                ?>
                            </ul>
                        </div>

                        <div class="row single-hotel-content">
                            <div class="col-md-12">
                                <?php the_content(); ?>
                            </div>
                        </div>


                        <hr>

                        <div class="row" id="booking_box_2">
                            <form method="get" id="booking-form" action="<?php echo esc_url(add_query_arg(array(), get_permalink($post_id))); ?>/#booking_box_2">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label><i class="icon-calendar-7"></i> <?php echo esc_html__('Check in', 'citytours') ?></label>
                                            <input class="date-pick form-control" placeholder="<?php echo ct_get_date_format('html'); ?>" data-date-format="<?php echo ct_get_date_format('html'); ?>" type="date" placeholder="yyyy-mm-dd" name="date_from" value="<?php echo $date_from; ?>" autocomplete="off">
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label><i class="icon-calendar-7"></i> <?php echo esc_html__('Check out', 'citytours') ?></label>
                                            <input class="date-pick form-control" placeholder="<?php echo ct_get_date_format('html'); ?>" data-date-format="<?php echo ct_get_date_format('html'); ?>" type="date" placeholder="yyyy-mm-dd" name="date_to" value="<?php echo $date_to; ?>" autocomplete="off">
                                        </div>
                                    </div>

                                </div>
                            </form>
                            <br />
                            <?php if (is_array($available_rooms) && !empty($available_rooms)) : ?>
                            <form method="post" id="hotel-cart" action="<?php echo add_query_arg(array('hotel_id' => $post_id, 'date_from' => $date_from, 'date_to' => $date_to), ct_get_hotel_cart_page()); ?>">
                                <input type="hidden" name="hotel_id" value="<?php echo esc_attr($post_id) ?>">
                                <input type="hidden" name="date_from" value="<?php echo esc_attr($date_from) ?>">
                                <input type="hidden" name="date_to" value="<?php echo esc_attr($date_to) ?>">
                                <table class="table table-striped cart-list hotel add_bottom_30 single-hotel" align="right">
                                    <thead class="thead-light">
                                        <tr>
                                            <th class=""><?php echo esc_html__('Rooms available and their specifications', 'citytours') ?></th>
                                            <th class="gap"></th>
                                            <th class=""><?php echo esc_html__('Maximum number of individuals in the room', 'citytours') ?></th>
                                            <th class="nights"><?php printf(esc_html__('Price per room for %s nights', 'citytours'), $days); ?></th>
                                            <th class=""><?php echo esc_html__('Select the number of rooms', 'citytours') ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $is_first = true;
                                        $price = 0;
                                        foreach ($room_types as $key => $post) : setup_postdata($post);
                                            $availableRooms = ($available_rooms[$post->ID] < 9 ) ? $available_rooms[$post->ID] : 9;
                                            $max_adults = get_post_meta($post->ID, '_room_max_adults', true);
                                            $max_kids = get_post_meta($post->ID, '_room_max_kids', true);
                                            $price = ct_hotel_calc_price_per_room($post_id, $post->ID, $date_from, $date_to);

                                        ?>
                                        <input type="hidden" name="room_type_id[]" value="<?php echo esc_attr($post->ID) ?>">
                                        <tr style="border-top:solid 1px;" id="<?php echo esc_attr($post->ID) ?>">
                                            <td>
                                                <h4><?php the_title() ?> </h4>
                                                <p style="overflow-wrap:break-word;"> <?php the_excerpt() ?> </p>
                                                <?php
                                                $hotel_facilities = wp_get_post_terms($post->ID, 'hotel_facility');
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
                                            </td>
                                            <td class="gap"></td>
                                            <td>
                                                <input type="hidden" name="adults[<?php echo esc_attr($post->ID) ?>]" value="<?php echo esc_attr($max_adults) ?>">
                                                <?php for ($a = 0; $a < $max_adults; $a++): ?> <i class="icon-adult"></i> <?php endfor; ?>
                                                <i class="icon-plus-1"></i>
                                                <?php for ($c = 0; $c < $max_kids; $c++): ?> <i class="icon-child"></i> <?php endfor; ?>
                                            </td>
                                            <td>
                                                <p class="nightClass"> <?php printf(esc_html__('Price per room for %s nights', 'citytours'), $days); ?> </p>
                                                <input type="hidden" name="price[<?php echo esc_attr($post->ID) ?>]" value="<?php echo esc_attr($price) ?>">
                                                <div class="room-price" style="overflow-wrap:break-word;"><?php echo ct_price($price, 'special') ?></div>
                                            </td>
                                            <td>
                                                <p class="roomsClass"> عدد الغرف </p>
                                                <select class="form-control" name="rooms[<?php echo esc_attr($post->ID) ?>]">
                                                    <?php for ($x = 0; $x <= $availableRooms; $x++): ?>
                                                        <option value="<?php echo $x ?>"><?php echo $x ?></option>
                                                    <?php endfor; ?>
                                                </select>
                                            </td>
                                        </tr>
                                        <?php wp_reset_postdata(); ?>
                                    <?php endforeach ?>
                                    </tbody>
                                </table>

                                <button type="submit" class="btn_full book-now"><?php echo esc_html__('Check now', 'citytours') ?></button>
                            </form>

                                <?php
                            else:
                                if($minimum_stay >= 1)
                                    echo '<p>'.sprintf(esc_html__('Minimum stay for this hotel is %d nights. Have another look at your dates and try again.', 'citytours'), $minimum_stay).'</p>';

                                echo '<p>'.sprintf(esc_html__('No Rooms Available In This Hotel between from %s to %s please have another look at booking date.', 'citytours'), $date_from, $date_to).'</p>';
                            endif;
                            ?>

                        </div><!-- End row  -->
                        <hr>

                        <?php include 'contact_info.php'; ?>

                        <div class="row related-sec">
                            <div class="col-md-12">
                                <h3><?php echo esc_html__('Related Hotel', 'citytours') ?></h3>
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
                                        'date_from' => $date_from,
                                        'date_to' => $date_to
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
                                                    <div class="rating">
                                                        <?php
                                                        $rating = get_post_meta($post->ID, '_hotel_star', true);
                                                        for ($r = 1; $r <= $rating; $r++) :
                                                            ?>
                                                            <i class="icon-star voted"></i>
                                                        <?php endfor; ?>
                                                    </div>
                                                    <h3><strong> <?php echo esc_html(get_the_title($post->ID)); ?> </strong></h3>
                                                    <div class="wbooking">
                                                        <a href="<?php echo esc_url(add_query_arg(array(), get_permalink($post->ID))); ?>" class="btn_1 btn-sm" > <?php esc_html_e('Watch and Book', 'citytours') ?> </a>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- End box tour -->
                                        </div>
                                        <!-- End col-md-6 -->
                                    <?php 
                                        endforeach; 
                                        wp_reset_postdata();
?>
                                                    
                                </div>
                                <!-- End row -->
                            </div>
                        </div>

                        <hr>

                        <?php
                        global $ct_options;

                        if (!empty($ct_options['hotel_review'])) :
                            $review_fields = !empty($ct_options['hotel_review_fields']) ? explode(",", $ct_options['hotel_review_fields']) : array(__("Position", "citytours"), __("Comfort", "citytours"), __("Price", "citytours"), __("Quality", "citytours"));
                            $review = get_post_meta(ct_hotel_org_id($post_id), '_review', true);

                            $review = round((!empty($review) ) ? (float) $review : 0, 1);
                            $doubled_review = number_format(round($review * 2, 1), 1);

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

                            $review_detail = get_post_meta(ct_hotel_org_id($post_id), '_review_detail', true);
                            if (!empty($review_detail)) {
                                $review_detail = is_array($review_detail) ? $review_detail : unserialize($review_detail);
                            } else {
                                $review_detail = array_fill(0, count($review_fields), 0);
                            }
                            ?>


                            <div class="row">
                                <div class="col-sm-3 col-xs-12">
                                    <h3><?php echo esc_html__('Reviews', 'citytours') ?></h3>
                                    <a href="#" class="btn rate-btn  add_bottom_15" data-toggle="modal" data-target="#myReview"><?php echo esc_html__('Leave a review', 'citytours') ?></a>
                                </div>

                                <div class="col-sm-9 col-xs-12">
                                    <div id="score_detail">
                                        <span><?php echo esc_html($doubled_review) ?></span><?php echo esc_html($review_content) ?> <small><?php echo sprintf(esc_html__('(Based on %d reviews)', 'citytours'), ct_get_review_count($post_id)) ?></small>
                                    </div>

                                    <div class="row" id="rating_summary">
                                        <div class="col-md-6">
                                            <ul>
                                                <?php for ($i = 0; $i < ( count($review_fields) / 2 ); $i++) { ?>
                                                    <li><?php echo $review_fields[$i] ?>
                                                        <div class="rating"><?php echo ct_rating_smiles($review_detail[$i]) ?></div>
                                                    </li>
                                                <?php } ?>
                                            </ul>
                                        </div>

                                        <div class="col-md-6">
                                            <ul>
                                                <?php for ($i = $i; $i < count($review_fields); $i++) { ?>
                                                    <li><?php echo $review_fields[$i] ?>
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

                                            <a href="#" class="btn_1 more-review" data-post_id="<?php echo esc_attr($post_id) ?>"><?php echo esc_html__('Load More Reviews', 'citytours') ?></a>

                                            <?php
                                        }
                                        ?>
                                    </div>
                                </div>
                            </div>

                        <?php endif; ?>

                    </div><!--End  single_hotel_desc-->

                    <aside class="col-md-4" <?php if ($is_fixed_sidebar) echo 'id="sidebar"'; ?>>

                        <p class="hidden-sm hidden-xs">
                            <a class="btn_map" data-toggle="collapse" href="#collapseMap" aria-expanded="false" aria-controls="collapseMap"><?php echo __('View on map', 'citytours') ?></a>
                        </p>

                        <?php if ($is_fixed_sidebar) : ?>
                            <div class="theiaStickySidebar">
                            <?php endif; ?>

                            <div class="box_style_1 expose" id="booking_box">
                                <h3 class="inner"><?php echo esc_html__('Research in hotels', 'citytours') ?></h3>
                                <?php if ('external' == $hotel_setting) : ?>
                                    <a href="<?php echo esc_url($external_link) ?>" class="btn_full"><?php echo esc_html__('Check now', 'citytours') ?></a>
                                <?php
                                elseif ('inquiry' == $hotel_setting) :
                                    echo do_shortcode($inquiry_form);
                                endif;
                                ?>
                                <form role="search" method="get" id="search-hotel-form" action="<?php echo esc_url(home_url('hotel/')); ?>">
                                    <input type="hidden" name="post_type" value="hotel">

                                    <div class="filter_type">
                                        <div class="form-group">
                                            <label><?php esc_html_e('Country', 'citytours'); ?></label>
                                            <?php
                                            $all_districts = get_terms('district', array('hide_empty' => false, 'parent' => 0));
                                            if (!empty($all_districts)) :
                                                ?>

                                                <div class="styled-select-filters">
                                                    <select name="country" id="countries">
                                                        <option value="" ><?php esc_html_e('Select a country', 'citytours'); ?> </option>
                                                        <?php
                                                        foreach ($all_districts as $district) {
                                                            $term_id = $district->term_id;
                                                            ?>
                                                            <option value="<?php echo esc_attr($term_id) ?>" ><?php echo esc_html($district->name) ?></option>
                                                        <?php } ?>
                                                    </select>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <div class="filter_type" <?php if (empty($country)) echo 'style="display:none;"'; ?>>
                                        <div class="form-group">
                                            <label><?php esc_html_e('City', 'citytours'); ?></label>
                                            <div class="styled-select-filters">
                                                <select class="form-control" name="districts" id="cities">
                                                    <?php if (empty($country)) : ?>
                                                        <option value="" selected><?php esc_html_e('Select city', 'citytours'); ?></option>
                                                        <?php
                                                    else:
                                                        $all_districts = get_terms('district', array('hide_empty' => false, 'parent' => $country));
                                                        if (!empty($all_districts)) :
                                                            ?>
                                                            <option value="" selected><?php esc_html_e('Select city', 'citytours'); ?></option>
                                                            <?php
                                                            foreach ($all_districts as $district) {
                                                                $term_id = $district->term_id;
                                                                ?>
                                                                <option value="<?php echo esc_attr($term_id) ?>" <?php if (!empty($districts[0]) && $districts[0] == $term_id) echo 'selected'; ?> ><?php echo esc_html($district->name) ?></option>
                                                                <?php
                                                            }
                                                        endif;
                                                    endif;
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label><?php echo esc_html_e('Hotel name', 'citytours') ?></label>
                                        <input type="text" class="form-control" id="hotel_name" name="s" placeholder="<?php esc_attr_e('Optionally type hotel name', 'citytours') ?>" value="<?php echo esc_attr($s) ?>">
                                    </div>

                                    <button class="btn_1 green"><?php esc_html_e('Search again', 'citytours'); ?></button>
                                </form>

                                <?php
                                if (!empty($ct_options['wishlist'])) :
                                    if (is_user_logged_in()) {
                                        $user_id = get_current_user_id();
                                        $wishlist = get_user_meta($user_id, 'wishlist', true);

                                        if (empty($wishlist)) {
                                            $wishlist = array();
                                        }
                                        ?>

                                        <a class="btn_full_outline btn-add-wishlist" href="#" data-post-id="<?php echo esc_attr($post_id) ?>"<?php echo ( in_array(ct_hotel_org_id($post_id), $wishlist) ) ? ' style="display:none;"' : '' ?>><i class=" icon-heart"></i> <?php echo esc_html__('Add to wishlist', 'citytours') ?></a>
                                        <a class="btn_full_outline btn-remove-wishlist" href="#" data-post-id="<?php echo esc_attr($post_id) ?>"<?php echo (!in_array(ct_hotel_org_id($post_id), $wishlist) ) ? ' style="display:none;"' : '' ?>><i class=" icon-heart"></i> <?php esc_html_e('Remove from wishlist', 'citytours'); ?></a>

                                        <?php
                                    } else {
                                        ?>

                                        <div><?php esc_html_e('To save your wishlist please login.', 'citytours'); ?></div>

                                        <?php if (empty($ct_options['login_page'])) { ?>

                                            <a href="#" class="btn_full_outline"><?php esc_html_e('login', 'citytours'); ?></a>

                                        <?php } else { ?>

                                            <a href="<?php echo esc_url(ct_get_permalink_clang($ct_options['login_page'])); ?>" class="btn_full_outline"><?php esc_html_e('login', 'citytours'); ?></a>

                                        <?php } ?>

                                        <?php
                                    }

                                endif;
                                ?>

                            </div><!--/box_style_1 -->

                            <?php if (is_active_sidebar('sidebar-hotel')) : ?>
                                <?php dynamic_sidebar('sidebar-hotel'); ?>
                            <?php endif; ?>
                            <?php if ($is_fixed_sidebar) : ?>
                            </div>
                        <?php endif; ?>
                    </aside>
                </div><!--End row -->
            </div><!--End container -->

            <?php if (!empty($ct_options['hotel_review'])) : ?>

                <div class="modal fade" id="myReview" tabindex="-1" role="dialog" aria-labelledby="myReviewLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">x</span></button>
                                <h4 class="modal-title" id="myReviewLabel"><?php echo esc_html__('Write your review', 'citytours') ?></h4>
                            </div>

                            <div class="modal-body">
                                <form method="get" action="<?php echo esc_url(admin_url('admin-ajax.php')) ?>" name="review" id="review-form">
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
                                    <!-- End row -->

                                    <hr>

                                    <div class="row">
                                        <?php for ($i = 0; $i < ( count($review_fields) ); $i++) { ?>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label><?php echo $review_fields[$i] ?></label>
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
                                    <!-- End row -->

                                    <div class="form-group">
                                        <textarea name="review_text" id="review_text" class="form-control" style="height:100px" placeholder="<?php esc_html_e("Write your review", 'citytours'); ?>"></textarea>
                                    </div>

                                    <input type="submit" value="ارسـل" class="btn_1" id="submit-review">
                                </form>

                                <div id="message-review" class="alert alert-warning"></div>
                            </div>
                        </div>
                    </div>
                </div>

            <?php endif; ?>
            <script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyC8ODJCdUgA8i2kcDOSL_SP3lMMSCwTtIk&callback=initMap"></script>
            <script type="text/javascript">

                $ = jQuery.noConflict();
                var lang = '<?php echo get_locale() ?>';
                lang = lang.replace('_', '-');

                function initMap() {
                    var lati = <?php echo $hotel_pos[0] ?>;
                    var long = <?php echo $hotel_pos[1] ?>;
                            
                    const map = new google.maps.Map(document.getElementById('map'), {
                        center: {lat: lati, lng: long},
                        zoom: 17,
                        mapId: 'DEMO_MAP_ID',
                    });

                    const marker = new google.maps.marker.AdvancedMarkerElement({
                        map,
                        position: {lat: lati, lng: long},
                    });

                    marker.addListener('click', ({domEvent, latLng}) => {
                        const {target} = domEvent;
                        // Handle the click event.
                        // ...
                    });
                }

                $(document).ready(function () {

                    $('#search-hotel-form input').val('');
                    $('input.date-pick').datepicker({
                        startDate: "today",
                        language: lang
                    });

                    //$('input[name="date_from"]').datepicker( 'setDate', 'today' );
                    //$('input[name="date_to"]').datepicker( 'setDate', '+1d' );

                    $('#booking-form').submit(function () {
                        var minimum_stay = 0,
                                date_from,
                                date_to,
                                one_day;

        <?php
        if (!empty($minimum_stay)) {
            echo 'minimum_stay=' . $minimum_stay . ';';
        }
        ?>

                        date_from = $('input[name="date_from"]').datepicker('getDate').getTime();
                        date_to = $('input[name="date_to"]').datepicker('getDate').getTime();
                        one_day = 1000 * 60 * 60 * 24;

                        if (date_from + one_day * minimum_stay > date_to) {
                            alert("<?php echo esc_js(sprintf(__('Minimum stay for this hotel is %d nights. Have another look at your dates and try again.', 'citytours'), $minimum_stay)) ?>");

                            return false;
                        }
                    });

                    $('input').iCheck({
                        checkboxClass: 'icheckbox_square-grey',
                        radioClass: 'iradio_square-grey'
                    });

                    $(".carousel").owlCarousel({
                        items: 4,
                        itemsDesktop: [1199, 3],
                        itemsDesktopSmall: [979, 3]
                    });

                    $('#sidebar').theiaStickySidebar({
                        additionalMarginTop: 80
                    });

                    update_cart = function () {
                        $('#overlay').fadeIn();
                        date_from = $('input[name="date_from"]').val()
                        date_to = $('input[name="date_to"]').val()
                        hotel_id = $('input[name="hotel_id"]').val()
                        $('input[name="room_type_id[]"]').each(function() {
                            var room_type_id = $(this).val();
                            $.ajax({
                                url: ajaxurl,
                                type: "POST",
                                data: {
                                    'action': 'ct_hotel_calc_price_per_room_ajax',
                                    hotel_id, room_type_id, date_from, date_to
                                },
                                success: function (response) {
                                    if (response.success == 1) {
                                        var day_start = new Date(date_from)
                                        var day_end = new Date(date_to)
                                        var total_days = parseInt((day_end - day_start) / (1000 * 60 * 60 * 24))
                                        $('.nights').text("سعر الغرفة لـ "+total_days+" ليلة");
                                        $('.nightClass').text("سعر الغرفة لـ "+total_days+" ليلة");
                                        $('input[name="price['+room_type_id+']"]').val(response.data['price'])
                                        $("tr[id='"+room_type_id+"']").find(".room-price").html(response.data['price_format']);
                                    } else {
                                        alert(response.message);
                                    }
                                }
                            });
                        })

                        $('#overlay').fadeOut();
                    }

//                    $('.update-cart-btn').click(funtion() {
//                        update_cart()
//                    });

                    $('input[name="date_from"]').change(function() {
                        $('#hotel-cart input[name="date_from"]').val($(this).val())
                        update_cart()
                    })

                    $('input[name="date_to"]').change(function() {
                        $('#hotel-cart input[name="date_to"]').val($(this).val())
                        update_cart()
                    })

                    $('.book-now').click(function (e) {
                        e.preventDefault();
                        valid = false
                        $("select[name*='rooms']").each(function ()
                        {
                            if ($(this).val() >= 1)
                            {
                                valid = true
                            }
                        });

                        if (valid == false)
                        {
                            alert("<?php esc_html_e("Please select the number of rooms", 'citytours'); ?>");
                            $('html, body').animate({
                                scrollTop: $("#booking_box_2").offset().top
                            }, 2000);
                            return false;
                        } else
                        {
                            $('#hotel-cart').submit();
                        }
                    });

                    /* Show Map on "View on Map" button click */
                    $('#collapseMap').on('shown.bs.collapse', function (e) {
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
                ?>
                                    '<?php echo $each_ht ?>': [{
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
        if (!empty($hotel_pos)) {
            $description = str_replace("'", "\'", wp_trim_words(strip_shortcodes(get_post_field("post_content", $post_id)), 20, '...'));
            ?>
                                'Center': [
                                    {
                                        name: '<?php echo $titele;  ?>',
                                                type: 'Hotels',
                                        location_latitude: <?php echo $hotel_pos[0] ?>,
                                                location_longitude: <?php echo $hotel_pos[1] ?>,
                                        map_image_url: '<?php echo ct_get_header_image_src($post_id, "ct-map-thumb") ?>',
                                                name_point: '<?php echo $titele; ?>',
                                        description_point: '<?php echo $description ?>',
                                                url_point: '<?php echo get_permalink($post_id) ?>'
                                    },
                                ]
        <?php }
        ?>
                        };
        <?php
        if (empty($hotel_pos)) {
            foreach ($related_ht as $each_ht) {
                if (get_post_type($each_ht) == 'hotel') {
                    $each_pos = get_post_meta($each_ht, '_hotel_loc', true);
                } else {
                    $each_pos = get_post_meta($each_ht, '_tour_loc', true);
                }

                if (!empty($each_pos)) {
                    $hotel_pos = explode(',', $each_pos);
                    break;
                }
            }
        }

        if (!empty($hotel_pos)) {
            ?>
                            var lati = <?php echo $hotel_pos[0] ?>;
                            var long = <?php echo $hotel_pos[1] ?>;
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
    ?>
    <?php
    wp_enqueue_script('validate', CT_TEMPLATE_DIRECTORY_URI . '/assets/validate.js');
    wp_enqueue_script('icheck', CT_TEMPLATE_DIRECTORY_URI . '/js/icheck.js');
    get_footer();
