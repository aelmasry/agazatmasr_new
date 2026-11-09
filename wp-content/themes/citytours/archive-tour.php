<?php
/* Tour List Page Template */
if (!defined('ABSPATH')) {
    exit;
}

get_header();

global $ct_options, $post_list, $current_view;

$tourtype = get_query_var('type', get_query_var('tour_type', null));

$tour_type = array();
if (!empty($tourtype) && is_string($tourtype)) {
    $tourtype_term = get_term_by('slug', $tourtype, 'tour_type');
    if ($tourtype_term && !is_wp_error($tourtype_term)) {
        $tour_type[0] = (int) $tourtype_term->term_id;
    }
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
$date = isset($_REQUEST['date']) ? sanitize_text_field($_REQUEST['date']) : '';
$adults = isset($_REQUEST['adults']) ? sanitize_text_field($_REQUEST['adults']) : '';
$kids = isset($_REQUEST['kids']) ? sanitize_text_field($_REQUEST['kids']) : '';

$order_by = (isset($_REQUEST['order_by']) && array_key_exists($_REQUEST['order_by'], $order_by_array)) ? sanitize_text_field($_REQUEST['order_by']) : '';
$order = (isset($_REQUEST['order']) && in_array($_REQUEST['order'], $order_array)) ? sanitize_text_field($_REQUEST['order']) : 'ASC';
if (isset($_REQUEST['type']) && $_REQUEST['type'] !== '') {
    $requested_type = is_array($_REQUEST['type']) ? $_REQUEST['type'] : array($_REQUEST['type']);
    $requested_ids = array_values(array_filter(array_map('absint', $requested_type)));
    if ($requested_ids) {
        $tour_type = $requested_ids;
    }
}
$price_filter = (isset($_REQUEST['price_filter']) && is_array($_REQUEST['price_filter'])) ? $_REQUEST['price_filter'] : array();
$rating_filter = (isset($_REQUEST['rating_filter']) && is_array($_REQUEST['rating_filter'])) ? $_REQUEST['rating_filter'] : array();
if (isset($_REQUEST['facility_filter']) && is_array($_REQUEST['facility_filter'])) {
    $facility_filter = array_values(array_filter(array_map('absint', $_REQUEST['facility_filter'])));
} elseif (isset($_REQUEST['facilities']) && is_array($_REQUEST['facilities'])) {
    $facility_filter = array_values(array_filter(array_map('absint', $_REQUEST['facilities'])));
} else {
    $facility_filter = array();
}
$tour_city = array();
if (isset($_REQUEST['city']) && $_REQUEST['city'] !== '') {
    $requested_city = is_array($_REQUEST['city']) ? $_REQUEST['city'] : array($_REQUEST['city']);
    $tour_city = array_values(array_filter(array_map('absint', $requested_city)));
}

if (isset($_REQUEST['view']) && !empty($_REQUEST['view'])) {
    $current_view = sanitize_text_field($_REQUEST['view']);
} else if (isset($ct_options['tour_list_default_view']) && !empty($ct_options['tour_list_default_view'])) {
    $current_view = $ct_options['tour_list_default_view'];
} else {
    $current_view = 'list';
}

$page = (isset($_REQUEST['page']) && (is_numeric($_REQUEST['page'])) && ($_REQUEST['page'] >= 1)) ? sanitize_text_field($_REQUEST['page']) : 1;
$per_page = (isset($ct_options['tour_posts']) && is_numeric($ct_options['tour_posts'])) ? $ct_options['tour_posts'] : 6;
$search_result = ct_tour_get_search_result(array('s' => $s, 'date' => $date, 'adults' => $adults, 'kids' => $kids, 'tour_type' => $tour_type, 'tour_city' => $tour_city, 'price_filter' => $price_filter, 'rating_filter' => $rating_filter, 'facility_filter' => $facility_filter, 'order_by' => $order_by_array[$order_by], 'order' => $order, 'last_no' => ($page - 1) * $per_page, 'per_page' => $per_page));
$post_list = $search_result['ids'];
$count = $search_result['count']; // total_count

$map_zoom = empty($ct_options['tour_list_zoom']) ? 14 : $ct_options['tour_list_zoom'];

$pageTitle = esc_html__('All tours', 'citytours');

if (!empty($tour_type[0])) {
    $tour_term = get_term((int) $tour_type[0], 'tour_type');
    if ($tour_term && !is_wp_error($tour_term)) {
        $pageTitle = $tour_term->name;
    }
}

$header_img_scr = ct_get_header_image_src('tour');
if (!empty($header_img_scr)) {
    $header_content = ct_get_header_content('tour');
    $header_img_height = ct_get_header_image_height('tour');
?>

    <section class="parallax-window" data-parallax="scroll" data-image-src="<?php echo esc_url($header_img_scr) ?>" data-natural-width="1400" data-natural-height="<?php echo $header_img_height; ?>">
        <div class="parallax-content-1">
            <div class="animated fadeInDown">
                <h1 class="page-title"><?php echo $pageTitle ?></h1>
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
                        <a class="btn_map" data-toggle="collapse" href="#collapseMap" aria-expanded="false" aria-controls="collapseMap"><?php esc_html_e('View on map', 'citytours') ?></a>
                    </p>

                    <?php if (!empty($_REQUEST)) : ?>
                        <div id="search_results"><?php echo sprintf(esc_html__('%d Results found', 'citytours'), $count) ?></div>
                    <?php endif; ?>

                    <div id="modify_search">
                        <a data-toggle="collapse" href="#collapseModify_search" aria-expanded="true" aria-controls="collapseModify_search" id="modify_col_bt">
                            <i class="icon_set_1_icon-78"></i><?php esc_html_e('Modify Search', 'citytours') ?> <i class="icon-plus-1 pull-right"></i>
                        </a>

                        <div class="collapse show" id="collapseModify_search">
                            <div class="modify_search_wp">
                                <form role="search" method="get" id="search-tour-form" action="<?php echo esc_url(home_url('/')); ?>">
                                    <input type="hidden" name="post_type" value="tour">

                                    <div class="form-group">
                                        <label for="tour_types"> <?php esc_html_e('tour search terms', 'citytours') ?></label>
                                        <?php
                                        $city_temp_array = array();
                                        $all_tour_types = ct_unique_tour_types_by_name(get_terms('tour_type', array('hide_empty' => false, 'parent' => 0)));
                                        if (!is_wp_error($all_tour_types) && !empty($all_tour_types)) :
                                        ?>
                                            <select class="form-control" name="type" id="tour_types">
                                                <option value="" selected><?php esc_html_e('Select a country', 'citytours') ?></option>
                                                <?php
                                                $i = 0;
                                                foreach ($all_tour_types as $each_tour_type) {
                                                    $term_id = $each_tour_type->term_id;
                                                    $icon_class = get_tax_meta($term_id, 'ct_tax_icon_class');
                                                    $city_temp_array[$i] = $term_id;
                                                    $i++;
                                            ?>
                                                    <option value="<?php echo esc_attr($term_id) ?>" <?php echo (!empty($tour_type) && $tour_type[0] == $term_id) ? "selected='selected'" : ""; ?>><?php echo esc_html($each_tour_type->name) ?></option>
                                                <?php } ?>
                                            </select>
                                        <?php endif; ?>
                                    </div>
                                    <div class="form-group">
                                        <label for="tour_city"><?php esc_html_e('Things to do', 'citytours') ?></label>
                                        <?php
                                        $city_terms = array();
                                        if (count($tour_type) >= 1 && !empty($tour_type[0])) {
                                            $child_terms = get_terms('tour_type', array('hide_empty' => false, 'parent' => (int) $tour_type[0]));
                                            if (!is_wp_error($child_terms)) {
                                                $city_terms = $child_terms;
                                            }
                                        } else {
                                            foreach ($city_temp_array as $parent_id) {
                                                $child_terms = get_terms('tour_type', array('hide_empty' => false, 'parent' => (int) $parent_id));
                                                if (!is_wp_error($child_terms)) {
                                                    $city_terms = array_merge($city_terms, $child_terms);
                                                }
                                            }
                                        }
                                        $city_terms = ct_unique_tour_types_by_name($city_terms);
                                        $selected_city_keys = array();
                                        foreach ((array) $tour_city as $selected_city_id) {
                                            $selected_city = get_term((int) $selected_city_id, 'tour_type');
                                            if ($selected_city && !is_wp_error($selected_city)) {
                                                $selected_city_keys[ct_tour_type_name_key($selected_city->name)] = true;
                                            }
                                        }
                                        ?>
                                        <select class="form-control" name="city" id="tour_city" <?php //echo (count($tour_type) >= 1) ? "" : "disabled"; ?>>
                                            <option value="" selected><?php esc_html_e('All tours', 'citytours') ?></option>
                                            <?php
                                            foreach ($city_terms as $each_city) {
                                                $term_id = $each_city->term_id;
                                                $is_selected = isset($selected_city_keys[ct_tour_type_name_key($each_city->name)]);
                                            ?>
                                                    <option value="<?php echo esc_attr($term_id) ?>" <?php echo $is_selected ? "selected='selected'" : ""; ?>><?php echo esc_html($each_city->name) ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label for="search_terms">للبحـث</label>
                                        <input type="text" class="form-control" id="search_terms" name="s" placeholder="<?php esc_attr_e('Type your search terms', 'citytours') ?>" value="<?php echo esc_attr($s) ?>">
                                    </div>

                                    <div class="form-group">
                                        <label><i class="icon-calendar-7"></i> <?php esc_html_e('Date', 'citytours') ?></label>
                                        <input class="date-pick form-control" data-date-format="<?php echo ct_get_date_format('html'); ?>" type="date" placeholder="yyyy-mm-dd" name="date" value="<?php echo esc_attr($date) ?>" autocomplete="off">
                                    </div>

                                    <button class="btn_1 green"><?php esc_html_e('Search again', 'citytours') ?></button>
                                </form>
                            </div>
                        </div>
                        <!--End collapse -->
                    </div>

                    <div id="filters_col">
                        <a data-toggle="collapse" href="#collapseFilters" aria-expanded="false" aria-controls="collapseFilters" id="filters_col_bt">
                            <i class="icon_set_1_icon-65"></i><?php echo esc_html__('Filters', 'citytours') ?> <i class="icon-plus-1 pull-right"></i>
                        </a>

                        <div class="collapse" id="collapseFilters">
                            <br />
                            <?php
                            if (!empty($ct_options['tour_price_filter'])) :
                                $price_steps = empty($ct_options['tour_price_filter_steps']) ? '50,80,100' : $ct_options['tour_price_filter_steps'];
                                $step_arr = explode(',', $price_steps);
                                array_unshift($step_arr, 0);
                            ?>

                                <div class="filter_type">
                                    <h6><?php echo esc_html__('Price', 'citytours') ?></h6>

                                    <ul class="list-filter price-filter" data-arg="price_filter">
                                        <?php
                                        for ($i = 0; $i < count($step_arr); $i++) {
                                            $checked = (in_array($i, $price_filter)) ? ' checked="checked"' : '';
                                            if ($i < count($step_arr) - 1) {
                                        ?>
                                                <li><label><input type="checkbox" name="price_filter[]" value="<?php echo esc_attr($i) ?>" <?php echo ($checked) ?>><?php echo ct_price($step_arr[$i]) ?> - <?php echo ct_price($step_arr[$i + 1]) ?></label></li>
                                            <?php } else { ?>
                                                <li><label><input type="checkbox" name="price_filter[]" value="<?php echo esc_attr($i) ?>" <?php echo ($checked) ?>><?php echo ct_price($step_arr[$i]) ?> +</label></li>
                                            <?php } ?>
                                        <?php } ?>
                                    </ul>
                                </div>

                            <?php
                            endif;

                            if (!empty($ct_options['tour_rating_filter'])) :
                            ?>
                                <div class="filter_type">
                                    <h6><?php echo esc_html__('Rating', 'citytours') ?></h6>

                                    <ul class="list-filter rating-filter" data-base-url="<?php echo esc_url(remove_query_arg(array('rating_filter', 'page'))); ?>" data-arg="rating_filter">
                                        <?php for ($i = 5; $i > 0; $i--) {
                                            $checked = (in_array($i, $rating_filter)) ? ' checked="checked"' : '';
                                        ?>
                                            <li>
                                                <label><input type="checkbox" name="rating_filter[]" value="<?php echo esc_attr($i) ?>" <?php echo ($checked) ?>><span class="rating"><?php ct_rating_smiles($i); ?></span></label>
                                            </li>

                                        <?php } ?>
                                    </ul>
                                </div>
                            <?php
                            endif;

                            if (!empty($ct_options['tour_facility_filter'])) :
                            ?>
                                <div class="filter_type">
                                    <h6><?php echo esc_html__('Facility', 'citytours') ?></h6>

                                    <ul class="list-filter facility-filter" data-base-url="<?php echo esc_url(remove_query_arg(array('facilities', 'page'))); ?>" data-arg="facilities">
                                        <?php
                                        $all_facilities = get_terms('tour_facility', array('hide_empty' => 0));

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
                                    <select name="sort_price" id="sort_price" data-base-url="<?php echo esc_url(remove_query_arg(array('order', 'order_by', 'page'))); ?>">
                                        <option value="" <?php if ($order_by != 'price') echo 'selected' ?>><?php echo esc_html__('Sort by price', 'citytours') ?></option>
                                        <option value="lower" <?php if ($order_by == 'price' && $order == 'ASC') echo 'selected' ?>><?php echo esc_html__('Lowest price', 'citytours') ?></option>
                                        <option value="higher" <?php if ($order_by == 'price' && $order == 'DESC') echo 'selected' ?>><?php echo esc_html__('Highest price', 'citytours') ?></option>
                                    </select>
                                </div>
                            </div>

                            <?php /*
                            <div class="col-md-3 col-sm-3 col-xs-6">
                                <div class="styled-select-filters">
                                    <select name="sort_rating" id="sort_rating" data-base-url="<?php echo esc_url(remove_query_arg(array('order', 'order_by', 'page'))); ?>">
                                        <option value="" <?php if ($order_by != 'rating') echo 'selected' ?>><?php echo esc_html__('Sort by rating', 'citytours') ?></option>
                                        <option value="lower" <?php if ($order_by == 'rating' && $order == 'ASC') echo 'selected' ?>><?php echo esc_html__('Lowest rating', 'citytours') ?></option>
                                        <option value="higher" <?php if ($order_by == 'rating' && $order == 'DESC') echo 'selected' ?>><?php echo esc_html__('Highest rating', 'citytours') ?></option>
                                    </select>
                                </div>
                            </div>
                            */ ?>

                        </div>
                    </div>
                    <!--End tools -->

                    <div class="tour-list <?php if ($current_view == 'list') echo 'row add-clearfix' ?>">
                        <?php ct_get_template('tour-list.php', '/templates/tour/'); ?>

                        <div class="text-center">
                            <?php
                            // unset($_GET['page']);

                            $pagenum_link = strtok($_SERVER["REQUEST_URI"], '?') . '%_%';
                            $total = ceil($count / $per_page);
                            $args = array(
                                'base' => $pagenum_link, // http://example.com/all_posts.php%_% : %_% is replaced by format (below)
                                'total' => $total,
                                'format' => '?page=%#%'  ,
                                'current' => $page,
                                'show_all' => false,
                                'prev_next' => true,
                                'prev_text' => esc_html__('Previous', 'citytours'),
                                'next_text' => esc_html__('Next', 'citytours'),
                                'end_size' => 1,
                                'mid_size' => 2,
                                'type' => 'list',
                                'add_args' => $_GET,
                            );

                            $pagination = paginate_links($args);
                            $pagination = str_replace("<ul class='page-numbers'>", '<ul class="pagination">', $pagination);
                            $pagination = str_replace('page-numbers', '', $pagination);

                            echo $pagination;
                            ?>
                        </div><!-- end pagination-->
                    </div><!-- End row -->

                    <?php include 'contact_info.php'; ?>

                </div><!-- End col lg 9 -->
            </div><!-- End row -->
        </div><!-- End container -->

        <script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyC8ODJCdUgA8i2kcDOSL_SP3lMMSCwTtIk"></script>
        <script type="text/javascript">
            jQuery(document).ready(function($) {
                var lang = '<?php echo get_locale() ?>';
                lang = lang.replace('_', '-');

                $('input').iCheck({
                    checkboxClass: 'icheckbox_square-grey',
                    radioClass: 'iradio_square-grey',
                    cursor: true
                }).on('ifToggled', function(e) {
                    if ($('#allFilter').is(':checked')) {
                        $('input[name="tour_types[]"]').prop('checked', false).iCheck('update')
                    }

                    if ($(this).attr('id') != '#allFilter') {
                        $('#allFilter').prop('checked', false).iCheck('update')
                    }

                    getTourList()
                    return false;
                })

                if ($('input.date-pick').length) {
                    if (lang.substring(0, 2) != 'fa') {
                        $('input.date-pick').datepicker({
                            startDate: "today",
                            language: lang
                        });
                    } else {
                        var date_format = $('input.date-pick').data('date-format');
                        $('input.date-pick').persianDatepicker({
                            observer: true,
                            format: date_format.toUpperCase(),
                        });
                    }
                }

                $('#sort_price').change(function() {
                    getTourList()
                    return false;
                })

                $('#sort_rating').change(function() {
                    getTourList()
                    return false;
                })

                $('div').delegate('.pagination a', 'click', function() {
                    var href = $(this).attr('href');
                    getTourList(href)
                    return false;
                })

                $('input[name^="facility_filter[]"]').on('change', function() {
                    getTourList()
                    return false;
                });

                getTourList = function(href) {
                    if (href) {
                        window.location = href;
                        return;
                    }

                    var params = new URLSearchParams();
                    params.set('post_type', 'tour');

                    var type = $('#tour_types').val();
                    var city = $('#tour_city').val();
                    var keyword = $('#search_terms').val();
                    var date = $('input[name="date"]').val();

                    if (type) params.set('type', type);
                    if (city) params.set('city', city);
                    if (keyword) params.set('s', keyword);
                    if (date) params.set('date', date);

                    if ($('#sort_price').val() === 'lower') {
                        params.set('order_by', 'price');
                        params.set('order', 'ASC');
                    } else if ($('#sort_price').val() === 'higher') {
                        params.set('order_by', 'price');
                        params.set('order', 'DESC');
                    } else if ($('#sort_rating').val() === 'lower') {
                        params.set('order_by', 'rating');
                        params.set('order', 'ASC');
                    } else if ($('#sort_rating').val() === 'higher') {
                        params.set('order_by', 'rating');
                        params.set('order', 'DESC');
                    }

                    ['price_filter[]', 'rating_filter[]', 'facility_filter[]'].forEach(function(name) {
                        $('input[name="' + name + '"]:checked').each(function() {
                            params.append(name, $(this).val());
                        });
                    });

                    window.location = <?php echo wp_json_encode(home_url('/')); ?> + '?' + params.toString();
                }

                jQuery('#collapseMap').on('shown.bs.collapse', function(e) {
                    var zoom = <?php echo $map_zoom ?>;
                    var markersData = {
                        <?php
                        foreach ($post_list as $tour_obj) {
                            $tour_pos = get_post_meta($tour_obj['tour_id'], '_tour_loc', true);

                            if (!empty($tour_pos)) {
                                $tour_pos = explode(',', $tour_pos);
                                $description = str_replace("'", "\'", wp_trim_words(strip_shortcodes(get_post_field("post_content", $tour_obj['tour_id'])), 20, '...'));
                        ?> '<?php echo $tour_obj['tour_id'] ?>': [{
                                    name: '<?php echo get_the_title($tour_obj['tour_id']) ?>',
                                    type: 'Tours',
                                    location_latitude: <?php echo $tour_pos[0] ?>,
                                    location_longitude: <?php echo $tour_pos[1] ?>,
                                    map_image_url: '<?php echo ct_get_header_image_src($tour_obj['tour_id'], "ct-map-thumb") ?>',
                                    name_point: '<?php echo get_the_title($tour_obj['tour_id']) ?>',
                                    description_point: '<?php echo $description ?>',
                                    url_point: '<?php echo get_permalink($tour_obj['tour_id']) ?>'
                                }],
                        <?php
                            }
                        }
                        ?>
                    };

                    <?php
                    $tour_pos = array();
                    if (!empty($post_list)) {
                        foreach ($post_list as $tour_obj) {
                            $tour_pos = get_post_meta($tour_obj['tour_id'], '_tour_loc', true);

                            if (!empty($tour_pos)) {
                                $tour_pos = explode(',', $tour_pos);
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
                        renderMap(_center, markersData, zoom, google.maps.MapTypeId.ROADMAP, false);
                    <?php } ?>
                });
            });
        </script>

        <?php
        get_footer();
