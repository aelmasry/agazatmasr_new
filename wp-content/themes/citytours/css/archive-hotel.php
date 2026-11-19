<?php
/* Hotel List Page Template */
if (!defined('ABSPATH')) {
    exit;
}

global $ct_options, $post_list, $current_view;

get_header();

$district = get_query_var('district');
$districts = array();
if (!empty($district)) {
    $district = get_term_by('slug', $district, 'district');
    $districts[0] = $district->term_id;
    $country = $district->parent;
}

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

// set date by default
$date_from = isset($_REQUEST['date_from']) ? ct_sanitize_date($_REQUEST['date_from']) : date('Y-m-d');
$date_to = isset($_REQUEST['date_to']) ? ct_sanitize_date($_REQUEST['date_to']) : date("Y-m-d", strtotime($date_from . ' + 1day'));


if (ct_strtotime($date_from) >= ct_strtotime($date_to)) {
    $date_from = '';
    $date_to = '';
}

$rooms = (isset($_REQUEST['rooms']) && is_numeric($_REQUEST['rooms'])) ? sanitize_text_field($_REQUEST['rooms']) : 1;
$adults = (isset($_REQUEST['adults']) && is_numeric($_REQUEST['adults'])) ? sanitize_text_field($_REQUEST['adults']) : 1;
$kids = (isset($_REQUEST['kids']) && is_numeric($_REQUEST['kids'])) ? sanitize_text_field($_REQUEST['kids']) : 0;
$order_by = (isset($_REQUEST['order_by']) && array_key_exists($_REQUEST['order_by'], $order_by_array)) ? sanitize_text_field($_REQUEST['order_by']) : '';
$order = (isset($_REQUEST['order']) && in_array($_REQUEST['order'], $order_array)) ? sanitize_text_field($_REQUEST['order']) : 'ASC';
$districts = (isset($_REQUEST['districts'])) ? (is_array($_REQUEST['districts']) ? $_REQUEST['districts'] : array($_REQUEST['districts'])) : $districts ?? [];
$price_filter = (isset($_REQUEST['price_filter']) && is_array($_REQUEST['price_filter'])) ? $_REQUEST['price_filter'] : array();
$star_filter = (isset($_REQUEST['star_filter']) && is_array($_REQUEST['star_filter'])) ? $_REQUEST['star_filter'] : array();
$rating_filter = (isset($_REQUEST['rating_filter']) && is_array($_REQUEST['rating_filter'])) ? $_REQUEST['rating_filter'] : array();
$facility_filter = (isset($_REQUEST['facilities']) && is_array($_REQUEST['facilities'])) ? $_REQUEST['facilities'] : array();
$country = (isset($_REQUEST['country'])) ? (is_array($_REQUEST['country']) ? $_REQUEST['country'] : $_REQUEST['country']) : $country ?? [];

if (isset($_REQUEST['view']) && !empty($_REQUEST['view'])) {
    $current_view = sanitize_text_field($_REQUEST['view']);
} else if (isset($ct_options['hotel_list_default_view']) && !empty($ct_options['hotel_list_default_view'])) {
    $current_view = $ct_options['hotel_list_default_view'];
} else {
    $current_view = 'list';
}

// $page = (isset($_REQUEST['page']) && (is_numeric($_REQUEST['page'])) && ($_REQUEST['page'] >= 1)) ? sanitize_text_field($_REQUEST['page']) : get_query_var('paged') ?? 1;
$page = (get_query_var('paged')) ? get_query_var('paged') : 1;
$per_page = (isset($ct_options['hotel_posts']) && is_numeric($ct_options['hotel_posts'])) ? $ct_options['hotel_posts'] : 6;

if (empty($districts[0]) && !empty($country)) {
    $districts[0] = $country;
}

$search_result = ct_hotel_get_search_result(array('s' => $s, 'date_from' => $date_from, 'date_to' => $date_to, 'rooms' => $rooms, 'adults' => $adults, 'kids' => $kids, 'star_filter' => $star_filter, 'district' => $districts, 'price_filter' => $price_filter, 'rating_filter' => $rating_filter, 'facility_filter' => $facility_filter, 'order_by' => $order_by_array[$order_by], 'order' => $order, 'last_no' => ($page - 1) * $per_page, 'per_page' => $per_page));

$post_list = $search_result['ids'];
$count = $search_result['count']; // total_count

$map_zoom = empty($ct_options['hotel_list_zoom']) ? 14 : $ct_options['hotel_list_zoom'];

$pageTitle = esc_html__('All Hotels', 'citytours');
if (!empty($districts[0])) {
    $district = get_term($districts[0]);
    $pageTitle = sprintf(esc_html__('Hotels of %s', 'citytours'), $district->name);
}

$header_img_scr = ct_get_header_image_src('hotel');
if (!empty($header_img_scr)) {
    $header_content = ct_get_header_content('hotel');
    $header_img_height = ct_get_header_image_height('hotel');
?>

    <section class="parallax-window" data-parallax="scroll" data-image-src="<?php echo esc_url($header_img_scr) ?>" data-natural-width="1400" data-natural-height="<?php echo $header_img_height; ?>">
        <div class="parallax-content-1">
            <div class="animated fadeInDown">
                <h1 class="page-title" style="color:#000;"><?php echo $pageTitle ?></h1>
            </div>
        </div>
    </section><!-- End section -->

    <div id="position">

    <?php } else { ?>
        <div id="position" class="blank-parallax">
        <?php } ?>

        <div class="container"><?php ct_breadcrumbs(); ?></div>
        </div><!-- End Position -->

        <div class="collapse" id="collapseMap">
            <div id="map" class="map"></div>
        </div><!-- End Map -->

        <div class="container margin_60">
            <div class="row">
                <aside class="col-lg-3 col-md-3">
                    <p>
                        <a class="btn_map" data-toggle="collapse" href="#collapseMap" aria-expanded="false" aria-controls="collapseMap"><?php echo __('View on map', 'citytours') ?></a>
                    </p>

                    <?php if (!empty($_REQUEST)) : ?>
                        <div id="search_results"><?php echo sprintf(esc_html__('%d Results found', 'citytours'), $count); ?></div>
                    <?php endif; ?>

                    <div id="modify_search">

                        <a data-toggle="collapse" href="#collapseModify_search" aria-expanded="true" aria-controls="collapseModify_search" id="filters_col_bt" class="">
                            <i class="icon_set_1_icon-78"></i><?php _e('Modify Search', 'citytours') ?> <i class="icon-plus-1 pull-right"></i>
                        </a>

                        <div class="collapse show" id="collapseModify_search">
                            <div class="modify_search_wp">
                                <form role="search" method="get" id="search-hotel-form" action="<?php echo esc_url(home_url('/')); ?>">
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
                                                        <option value="" <?php if (empty($country)) echo 'selected'; ?>><?php esc_html_e('Select a country', 'citytours'); ?> </option>
                                                        <?php
                                                        foreach ($all_districts as $district) {
                                                            $term_id = $district->term_id;
                                                        ?>
                                                            <option value="<?php echo esc_attr($term_id) ?>" <?php if (!empty($country) && $country == $term_id) echo 'selected'; ?>><?php echo esc_html($district->name) ?></option>
                                                        <?php } ?>
                                                    </select>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="filter_type" <?php if (empty($country) && empty($districts)) echo 'style="display:none;"'; ?>>
                                        <div class="form-group">
                                            <label><?php esc_html_e('City', 'citytours'); ?></label>
                                            <div class="styled-select-filters">
                                                <select class="form-control" name="districts" id="cities">
                                                    <?php if (empty($country) && empty($district)) : ?>
                                                        <option value="" selected><?php esc_html_e('Select city', 'citytours'); ?></option>
                                                        <?php
                                                        else :
                                                        if(is_array($country)) {
                                                            $country = implode(', ', $country);
                                                        }
                                                        $cities = $wpdb->get_results(" SELECT t.term_id, t.name, tt.taxonomy FROM " . $wpdb->prefix . "terms AS t INNER JOIN " . $wpdb->prefix . "term_taxonomy AS tt ON t.term_id = tt.term_id WHERE tt.taxonomy IN ('district') AND tt.parent = '".$country."' ORDER BY t.name ASC ");
                                                        if (!empty($cities)) :
                                                        ?>
                                                            <option value="" selected><?php esc_html_e('Select city', 'citytours'); ?></option>
                                                            <?php
                                                            foreach ($cities as $district) {
                                                                $term_id = $district->term_id;
                                                            ?>
                                                                <option value="<?php echo esc_attr($term_id) ?>" <?php if (!empty($districts[0]) && $districts[0] == $term_id) echo 'selected'; ?>><?php echo esc_html($district->name) ?></option>
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

                                    <div class="form-group">
                                        <label><i class="icon-calendar-7"></i> <?php esc_html_e('Check in', 'citytours') ?></label>
                                        <input class="date-pick form-control" data-date-format="<?php echo ct_get_date_format('html'); ?>" type="date" placeholder="yyyy-mm-dd" name="date_from" value="<?php echo esc_attr($date_from) ?>" autocomplete="off">
                                    </div>

                                    <div class="form-group">
                                        <label><i class="icon-calendar-7"></i> <?php esc_html_e('Check out', 'citytours') ?></label>
                                        <input class="date-pick form-control" data-date-format="<?php echo ct_get_date_format('html'); ?>" type="date" placeholder="yyyy-mm-dd" name="date_to" value="<?php echo esc_attr($date_to) ?>" autocomplete="off">
                                    </div>



                                    <button class="btn_1 green"><?php esc_html_e('Search again', 'citytours'); ?></button>
                                </form>
                            </div>
                            <!--End modify_search_wp -->
                        </div>
                        <!--End collapse -->
                    </div>

                    <div id="filters_col">
                        <a data-toggle="collapse" href="#collapseFilters" aria-expanded="false" aria-controls="collapseFilters" id="filters_col_bt">
                            <i class="icon_set_1_icon-65"></i><?php echo esc_html__('Filters', 'citytours') ?> <i class="icon-plus-1 pull-right"></i>
                        </a>

                        <div class="collapse" id="collapseFilters">
                            <?php
                            if (!empty($ct_options['hotel_price_filter'])) :
                                $price_steps = empty($ct_options['hotel_price_filter_steps']) ? '50,80,100' : $ct_options['hotel_price_filter_steps'];
                                $step_arr = explode(',', $price_steps);
                                array_unshift($step_arr, 0);
                            ?>

                                <div class="filter_type">
                                    <h6><?php echo esc_html__('Price', 'citytours') ?></h6>
                                    <input type="text" id="range" name="range" value="">
                                </div>

                            <?php
                            endif;

                            if (!empty($ct_options['hotel_star_filter'])) :
                                $counts_by_star = ct_hotel_get_search_result_count(array('by' => 'star', 'price_filter' => $price_filter, 'rating_filter' => $rating_filter, 'facility_filter' => $facility_filter, 'district' => $districts));
                            ?>

                                <div class="filter_type">
                                    <h6><?php echo esc_html__('Hotel level', 'citytours') ?></h6>
                                    <ul class="list-filter star-filter" data-arg="star_filter">
                                        <li><label><input type="checkbox" name="star_filter[]" value="-1" id="allFilter"><?php echo esc_html__('All Hotels', 'citytours') . ' (' . esc_html(array_sum($counts_by_star)) . ')' ?></label></li>

                                        <?php
                                        for ($i = 5; $i > 0; $i--) {
                                            $checked = (in_array($i, $star_filter)) ? ' checked="checked"' : '';
                                        ?>

                                            <li><label><input type="checkbox" name="star_filter[]" value="<?php echo esc_attr($i) ?>" <?php echo ($checked) ?>><span class="rating">
                                                        <?php ct_rating_smiles($i, 'icon_set_1_icon-81'); ?></span><?php echo '(' . esc_html((empty($counts_by_star[$i]) ? 0 : $counts_by_star[$i])) . ')' ?></label></li>
                                        <?php } ?>
                                    </ul>
                                </div>

                            <?php
                            endif;

                            if (!empty($ct_options['hotel_rating_filter'])) :
                            ?>

                                <div class="filter_type">
                                    <h6><?php echo esc_html__('Review Score', 'citytours') ?></h6>

                                    <ul class="list-filter rating-filter" data-arg="rating_filter">
                                        <?php
                                        for ($i = 5; $i > 0; $i--) {
                                            $checked = (in_array($i, $rating_filter)) ? ' checked="checked"' : '';
                                        ?>

                                            <li>
                                                <label><input type="checkbox" name="rating_filter[]" value="<?php echo esc_attr($i) ?>" <?php echo ($checked) ?>><span class="rating">
                                                        <?php ct_rating_smiles($i); ?></span></label>
                                            </li>

                                        <?php } ?>
                                    </ul>
                                </div>

                            <?php
                            endif;

                            if (!empty($ct_options['hotel_facility_filter'])) :
                            ?>
                                <div class="filter_type">
                                    <h6><?php echo esc_html__('features', 'citytours') ?></h6>

                                    <!--<ul class="list-filter facility-filter" data-base-url="<?php echo esc_url(remove_query_arg(array('facilities', 'page'))); ?>" data-arg="facilities">-->
                                    <ul class="list-filter facility-filter" data-arg="facilities">

                                        <?php
                                        $all_facilities = get_terms('hotel_facility', array('hide_empty' => 0));

                                        if (!empty($all_facilities)) :
                                            foreach ($all_facilities as $facility) {
                                                $term_id = $facility->term_id;
                                                $checked = (in_array($term_id, $facility_filter)) ? ' checked="checked"' : '';

                                                echo '<li><label><input type="checkbox" name="facility_filter[]" value="' . esc_attr($term_id) . '"' . $checked . '>' . esc_html($facility->name) . '</label></li>';
                                            }
                                        endif;
                                        ?>

                                    </ul>
                                </div>

                            <?php
                            endif;
                            ?>



                        </div>
                        <!--End collapse -->

                    </div>
                    <!--End filters col-->

                    <?php if (is_active_sidebar('sidebar-hotel')) : ?>
                        <?php dynamic_sidebar('sidebar-hotel'); ?>
                    <?php endif; ?>

                </aside>
                <!--End aside -->

                <div class="col-lg-9 col-md-8">
                    <div id="tools">
                        <div class="row">
                            <div class="col-md-3 col-sm-3 col-xs-6">
                                <div class="styled-select-filters">
                                    <select name="sort_price" id="sort_price">
                                        <option value="" <?php if ($order_by != 'price') echo 'selected' ?>><?php echo esc_html__('Sort by price', 'citytours') ?></option>
                                        <option value="lower" <?php if ($order_by == 'price' && $order == 'ASC') echo 'selected' ?>><?php echo esc_html__('Lowest price', 'citytours') ?></option>
                                        <option value="higher" <?php if ($order_by == 'price' && $order == 'DESC') echo 'selected' ?>><?php echo esc_html__('Highest price', 'citytours') ?></option>
                                    </select>
                                </div>
                            </div>

                            <?php 
                            /*
                            <div class="col-md-3 col-sm-3 col-xs-6">
                                <div class="styled-select-filters">
                                    <select name="sort_rating" id="sort_rating">
                                        <option value="" <?php if ($order_by != 'rating') echo 'selected' ?>><?php echo esc_html__('Sort by rating', 'citytours') ?></option>
                                        <option value="lower" <?php if ($order_by == 'rating' && $order == 'ASC') echo 'selected' ?>><?php echo esc_html__('Lowest rating', 'citytours') ?></option>
                                        <option value="higher" <?php if ($order_by == 'rating' && $order == 'DESC') echo 'selected' ?>><?php echo esc_html__('Highest rating', 'citytours') ?></option>
                                    </select>
                                </div>
                            </div>
                            */ 
                            ?>
                            

                        </div>
                    </div>
                    <!--End tools -->

                    <div class="hotel-list <?php if ($current_view == 'list') echo 'row add-clearfix' ?>">
                        <?php ct_get_template('hotel-list.php', '/templates/hotel/'); ?>

                        <div class="text-center">
                        <?php
                            unset($_GET['page']);
                            $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
                            $pagenum_link = strtok($_SERVER["REQUEST_URI"], '?') . '%_%';
                            $total = ceil($count / $per_page);
                            $args = array(
                                // 'base' => $pagenum_link, // http://example.com/all_posts.php%_% : %_% is replaced by format (below)
                                'total' => $total,
                                // 'format' => '?page=%#%',
                                'current' => $paged,
                                'show_all' => true,
                                'prev_next' => true,
                                'prev_text' => esc_html__('Previous', 'citytours'),
                                'next_text' => esc_html__('Next', 'citytours'),
                                'end_size' => 1,
                                'mid_size' => 5,
                                'type' => 'list',
                                'add_args' => $_GET,
                            );

                            $pagination = paginate_links($args) ?? '';
                            $pagination = str_replace("<ul class='page-numbers'>", '<ul class="pagination">', $pagination);
                            $pagination = str_replace('page-numbers', '', $pagination);

                            echo $pagination;
                        ?>
                        </div><!-- end pagination-->
                    </div><!-- hotel-list -->

                    <div class="box_style_2" style="display:none;">
                        <i class="icon_set_1_icon-57"></i>
                        <h4>تحتاج <span>مساعدة ؟</span></h4>
                        <a href="tel:01225993443" class="phone">01225993443</a>
                        <small>السبت الى الجمعة: 9.00am - 6.00pm</small>
                    </div>


                </div><!-- End col lg 9 -->
            </div><!-- End row -->
        </div><!-- End container -->
        <?php
        wp_enqueue_script('ct_script_icheck');
        // wp_enqueue_script('icheck', CT_TEMPLATE_DIRECTORY_URI . '/js/icheck.js');
        ?>
        <?php ct_google_maps_script(array('callback' => 'initMap')); ?>
        <script type="text/javascript">
            jQuery(function($) {
                var lang = '<?php echo get_locale() ?>';
                lang = lang.replace('_', '-');

                $('input').iCheck({
                    checkboxClass: 'icheckbox_square-grey',
                    radioClass: 'iradio_square-grey',
                    cursor: true
                }).on('ifToggled', function(e) {

                    if ($('#allFilter').is(':checked')) {
                        $('input[name="star_filter[]"]').prop('checked', false).iCheck('update')
                    }

                    if ($(this).attr('id') != '#allFilter') {
                        $('#allFilter').prop('checked', false).iCheck('update')
                    }

                    getHotelList()
                    return false;
                })

                $('#sort_price').change(function() {
                    getHotelList()
                    return false;
                })

                $('#sort_rating').change(function() {
                    getHotelList()
                    return false;
                })

                $('div').delegate('.pagination a', 'click', function() {
                    var href = $(this).attr('href');
                    getHotelList(href)
                    return false;
                })

                getHotelList = function(href) {
                    
                    let data = {
                        action: 'ct_get_hotel_list'
                    }

                    <?php if(!empty($districts[0]) && $districts[0] != '') : ?>
                    data['districts'] = '<?php echo $districts[0]  ?>';
                    <?php endif;?>

                    if ($('#sort_price').val() == "lower") {
                        data['order_by'] = 'price'
                        data['order'] = 'ASC'
                    } else if ($('#sort_price').val() == "higher") {
                        data['order_by'] = 'price'
                        data['order'] = 'DESC'
                    }

                    if ($('#sort_rating').val() == "lower") {
                        data['order_by'] = 'rating'
                        data['order'] = 'ASC'
                    } else if ($('#sort_rating').val() == "higher") {
                        data['order_by'] = 'rating'
                        data['order'] = 'DESC'
                    }

                    if ($('#countries').val() != '') {
                        data['country'] = $('#countries').val()
                    }

                    if ($('#cities').val() != '') {
                        data['districts'] = $('#cities').val()
                    }

                    if ($('input[name^="date_from"]').val() != '') {
                        data['date_from'] = $('input[name^="date_from"]').val()
                    }

                    if ($('input[name^="date_to"]').val() != '') {
                        data['date_to'] = $('input[name^="date_to"]').val()
                    }

                    if ($('input[name^="star_filter[]"]').is(':checked')) {
                        data['star_filter'] = []
                        $('input[name^="star_filter[]"]').each(function() {
                            if ($(this).is(':checked')) {
                                data['star_filter'].push($(this).val())
                            }
                        });
                    }

                    if ($('input[name^="price_filter[]"]').is(':checked')) {
                        data['price_filter'] = []
                        $('input[name^="price_filter[]"]').each(function() {
                            if ($(this).is(':checked')) {
                                data['price_filter'].push($(this).val())
                            }
                        });
                    }

                    if ($('input[name^="rating_filter[]"]').is(':checked')) {
                        data['rating_filter'] = []
                        $('input[name^="rating_filter[]"]').each(function() {
                            if ($(this).is(':checked')) {
                                data['rating_filter'].push($(this).val())
                            }
                        });
                    }

                    if ($('input[name^="facility_filter[]"]').is(':checked')) {
                        data['facility_filter'] = []
                        $('input[name^="facility_filter[]"]').each(function() {
                            if ($(this).is(':checked')) {
                                data['facility_filter'].push($(this).val())
                            }
                        });
                    }

                    if (href != '' && href != undefined) {
                        let page = 1;
                        let queryString = href.split('?')[1];
                        if(queryString) {
                            const pageParam = queryString.split('&').find(param => param.startsWith('page='));
                            page = decodeURIComponent(pageParam.split('=')[1]);
                        }else {
                            let queryString = href.split('page/')[1];
                            page = decodeURIComponent(queryString.split('/')[0]);
                        }
                        // console.log("@page ", page);
                        data['page'] = page;
                        for (var i = 1; i < page.length; i++) {
                            params = page[i].split('=');
                            data['' + params[0] + ''] = params[1];
                        }
                    }

                    $.ajax({
                            type: "POST",
                            dataType: "html",
                            url: ajaxurl,
                            data: data
                        })
                        .done(function(resp) {
                            $('html, body').animate({
                                scrollTop: $('.hotel-list').offset().top
                            }, 2000);
                            resp = resp.substring(0, resp.length - 1);
                            $(".hotel-list").empty().html(resp);
                        })
                        .fail(function(jqXHR, ajaxOptions, thrownError) {
                            alert('No response from server');
                        });
                }
                if ($('input.date-pick').length) {  
                    $('input.date-pick').datepicker({
                        startDate: "today",
                        language: lang
                    });
                }

                $('#collapseMap').on('shown.bs.collapse', function(e) {
                    var zoom = <?php echo $map_zoom ?>;
                    var markersData = {
                        <?php
                        foreach ($post_list as $hotel_obj) {
                            $hotel_pos = get_post_meta($hotel_obj['hotel_id'], '_hotel_loc', true);

                            if (!empty($hotel_pos)) {
                                $hotel_pos = explode(',', $hotel_pos);
                                $description = str_replace("'", "\'", wp_trim_words(strip_shortcodes(get_post_field("post_content", $hotel_obj['hotel_id'])), 20, '...'));
                        ?> '<?php echo $hotel_obj['hotel_id'] ?>': [{
                                    name: '<?php echo get_the_title($hotel_obj['hotel_id']) ?>',
                                    type: 'Hotels',
                                    location_latitude: <?php echo $hotel_pos[0] ?>,
                                    location_longitude: <?php echo $hotel_pos[1] ?>,
                                    map_image_url: '<?php echo ct_get_header_image_src($hotel_obj['hotel_id'], "ct-map-thumb") ?>',
                                    name_point: '<?php echo get_the_title($hotel_obj['hotel_id']) ?>',
                                    description_point: '<?php echo $description ?>',
                                    url_point: '<?php echo get_permalink($hotel_obj['hotel_id']) ?>'
                                }],
                        <?php
                            }
                        }
                        ?>
                    }

                    <?php
                    $hotel_pos = array();
                    if (!empty($post_list)) {
                        foreach ($post_list as $hotel_obj) {
                            $hotel_pos = get_post_meta($hotel_obj['hotel_id'], '_hotel_loc', true);

                            if (!empty($hotel_pos)) {
                                $hotel_pos = explode(',', $hotel_pos);
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
                        renderMap(_center, markersData, zoom, google.maps.MapTypeId.ROADMAP, false);
                    <?php } ?>
                });

                /* Input range slider */
                <?php if (!empty($ct_options['hotel_price_filter'])) : ?>

                    $(function() {
                        'use strict';
                        $("#range").ionRangeSlider({
                            hide_min_max: true,
                            keyboard: true,
                            min: <?php echo min($step_arr) ?>,
                            max: <?php echo max($step_arr) ?>,
                            from: 0,
                            to: <?php echo max($step_arr) ?>,
                            type: 'double',
                            step: 1,
                            // prefix: "$",
                            grid: true
                        });
                    });

                <?php endif; ?>


            });
        </script>

        <?php
        wp_enqueue_style('rangeSlider', CT_TEMPLATE_DIRECTORY_URI . '/css/ion.rangeSlider.css');
        wp_enqueue_style('rangeSlider_skinFlat', CT_TEMPLATE_DIRECTORY_URI . '/css/ion.rangeSlider.skinFlat.css');
        get_footer();
        ?>
