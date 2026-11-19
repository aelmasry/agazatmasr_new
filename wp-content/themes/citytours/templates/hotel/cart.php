<?php
/* Hotel Cart Template */
if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;

// validation
$required_params = array('hotel_id', 'date_from', 'date_to');
foreach ($required_params as $param) {
    if (!isset($_REQUEST[$param])) {
        do_action('ct_hotel_booking_wrong_data'); // ct_redirect_home() - if data is not valid return to home
        exit;
    }
}

// init variables
$hotel_id = $_REQUEST['hotel_id'];
$date_from = $_REQUEST['date_from'];
$date_to = $_REQUEST['date_to'];
$room_ids = ct_hotel_get_available_rooms($hotel_id, $date_from, $date_to);

$deposit_rate = get_post_meta($hotel_id, '_hotel_security_deposit', true);
$deposit_rate = empty($deposit_rate) ? 0 : $deposit_rate;
$add_services = ct_get_add_services_by_postid($hotel_id);

$uid = $hotel_id . $date_from . $date_to;
$cart = new CT_Hotel_Cart();

$uri = parse_url($_SERVER['HTTP_REFERER'], PHP_URL_PATH);
$uri = explode('/', $uri);
$uri = array_filter($uri);
$uri = array_values($uri);

if (!empty($_REQUEST['rooms']) && !in_array('hotel-cart-page', $uri)) {
    CT_Hotel_Cart::_unset($uid);
    $data = array();
    $data['total_price'] = 0;
    $data['total_adults'] = 0;
    foreach ($_REQUEST['rooms'] as $key => $value) {
        if ($value >= 1) {
            $data['total_adults'] += $_REQUEST['adults'][$key] * $_REQUEST['rooms'][$key];
            for ($k = 1; $k <= $value; $k++) {
                $price = (($_REQUEST['price'][$key] > 1)) ? $_REQUEST['price'][$key] : 0;
                $data['room'][$key][$k] = array('rooms' => 1, 'total' => $price);
                $data['total_price'] += $price;
            }
        }
    }

    $data['hotel_id'] = $_REQUEST['hotel_id'];
    $data['date_from'] = $_REQUEST['date_from'];
    $data['date_to'] = $_REQUEST['date_to'];

    CT_Hotel_Cart::set($uid, $data);
}

$cart_service = $cart->get_field($uid, 'add_service');
$ct_hotel_checkout_page_url = apply_filters('ct_get_woocommerce_cart_url', ct_get_hotel_checkout_page());

if (!$ct_hotel_checkout_page_url) {
?>
    <h5 class="alert alert-warning"><?php echo esc_html__('Please set checkout page in theme options panel.', 'citytours') ?></h5>

    <?php
} else {
    // function
    if (!empty($room_ids) && is_array($room_ids)) :
    ?>

        <form id="hotel-cart" action="<?php echo esc_url(add_query_arg(array('uid' => $uid), $ct_hotel_checkout_page_url)); ?>">
            <div class="row">
                <div class="col-md-8">
                    <?php do_action('hotel_cart_main_before'); ?>

                    <div class="alert alert-info" role="alert"><?php echo __('<strong>Rooms available</strong> for the selected dates.<br>PLEASE SELECT YOUR QUANTITY.', 'citytours') ?></div>

                    <table class="table table-striped cart-list cart hotel add_bottom_30">
                        <thead>
                            <tr>
                                <th><?php echo esc_html__('Room Type', 'citytours') ?></th>
                                <th><?php echo esc_html__('Adults', 'citytours') ?></th>
                                <th colspan="2"><?php echo esc_html__('Children', 'citytours') ?></th>
                                <th><?php echo esc_html__('Total', 'citytours') ?></th>
                                <th></th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php
                            foreach ($room_ids as $room_id => $available_rooms) :
                                $max_adults = get_post_meta($room_id, '_room_max_adults', true);
                                $max_kids = get_post_meta($room_id, '_room_max_kids', true);
                                if (empty($max_adults) || !is_numeric($max_adults))
                                    $max_adults = 0;
                                if (empty($max_kids) || !is_numeric($max_kids))
                                    $max_kids = 0;

                                if (!empty($cart->get_room_fields($uid, $room_id))) :
                                    $i = 0;
                                    foreach ($cart->get_room_fields($uid, $room_id) as $key => $value) : ?>
                                        <tr number-row="<?php echo $key; ?>" id="<?php echo $room_id . $key; ?>">
                                            <td class="rooms" data-title="<?php esc_attr_e('Room Type', 'citytours') ?>">
                                                <div class="thumb_cart">
                                                    <a href="#" data-toggle="modal" data-target="#room-<?php echo esc_attr($room_id) ?>"><?php echo get_the_post_thumbnail($room_id, 'thumbnail'); ?></a>
                                                </div>
                                                <span class="item_cart">
                                                    <a href="#" data-toggle="modal" data-target="#room-<?php echo esc_attr($room_id) ?>"><?php echo esc_html(get_the_title($room_id)); ?></a>
                                                </span>
                                                <input type="hidden" name="room_type_id[<?php echo esc_attr($room_id) ?>][<?php echo $key ?>]" value="<?php echo esc_attr($room_id) ?>">
                                                <input type="hidden" class="qty2 form-control room-quantity" name="rooms[<?php echo esc_attr($room_id) ?>][<?php echo $key ?>]" value="<?php echo esc_attr($value['rooms']) ?>">
                                            </td>

                                            <td class="adults-count" data-title="<?php esc_attr_e('Adults', 'citytours') ?>">
                                                <div class="numbers-row input-group" data-min="0" <?php if (!empty($max_adults)) echo 'data-max="' . esc_attr($max_adults) . '" data-per-room="' . esc_attr($max_adults) . '"'; ?>>
                                                    <input type="text" class="qty2 form-control input-number room-adults" name="adults[<?php echo esc_attr($room_id) ?>][<?php echo $key ?>]" value="<?php echo (!empty($value['adults'])) ? esc_attr($value['adults']) : esc_attr($max_adults) ?>">
                                                    
                                                    <div class="inc button_inc">+</div>
                                                    
                                                    <div class="dec button_inc">-</div>
                                                </div>
                                            </td>

                                            <td class="children-count" data-title="<?php esc_attr_e('Children', 'citytours') ?>">
                                                <?php if (!empty($max_kids)) : ?>
                                                    <div class="numbers-row input-group kids" data-min="0" data-max="<?php echo esc_attr($max_kids) ?>" data-per-room="<?php echo esc_attr($max_kids) ?>">
                                                        <input type="text" class="qty2 form-control input-number room-kids" name="kids[<?php echo esc_attr($room_id) ?>][<?php echo $key ?>]" value="<?php echo (!empty($value['kids'])) ? esc_attr($value['adults']) : 0 ?>">

                                                        <div class="inc button_inc">+</div>
                                                        <div class="dec button_inc">-</div>
                                                    </div>
                                                <?php endif; ?>
                                            </td>

                                            <td class="tdkidsage" style="display:none;">
                                                <?php
                                                if (!empty($value['kidsAge'])) :
                                                    foreach ($value['kidsAge'] as $k => $age) :
                                                ?>
                                                        <div class="divKidsAge">
                                                            <?php echo ($k >= 1 ) ? '<span> عمر الطفل الثانى </span>' : '<span> عمر الطفل الاول </span>'; ?>
                                                            <select name='kidsAge[<?php echo $room_id ?>][<?php echo $key ?>][]' style='width:60%' class='form-control kidsAge'>
                                                                <?php
                                                                for ($x = 1; $x < 13; $x++) :
                                                                    echo "<option value='" . $x . "' " . (($x == $age) ? 'Selected' : '') . ">" . $x . "</option>";
                                                                endfor;
                                                                ?>
                                                            </select>
                                                        </div>
                                                <?php
                                                    endforeach;
                                                endif;
                                                ?>
                                            </td>

                                            <td class="total" data-title="Total">
                                                <?php
                                                $sql = "SELECT room_type_id, rooms, price_per_room, price_per_person, price_per_child, price_per_child2 FROM " . CT_HOTEL_VACANCIES_TABLE . "
                                                                WHERE 1=1 AND hotel_id=" . $hotel_id . " AND room_type_id=" . $room_id . "
                                                                AND date_from <= '" . date("Y-m-d", ct_strtotime($date_from)) . "' AND date_to >= '" . date("Y-m-d", ct_strtotime($date_to)) . "'
                                                                AND active = 1 limit 1";

                                                $row = $wpdb->get_row($sql);
                                                
                                                if($row) {
                                                    $pricePerChild = json_decode($row->price_per_child, true);
                                                    $pricePerChild2 = json_decode($row->price_per_child2, true);
                                                    
                                                    $child_labels = [
                                                        'طفل الاول',
                                                        'الطفل الثاني'
                                                    ];

                                                    $lines = "<button type='button' class='close' aria-hidden='true'>&times;</button>";
                                                    $lines .= 'سعر الغرفـة ' . ct_price($row->price_per_room) . ' لليلة<br/> <br />';
                                                    
                                                    $lines .= '&nbsp;' . $child_labels[0];
                                                    $child1Price = [];
                                                    foreach($pricePerChild as $index => $clind){
                                                        $child1Price[] = '(' . $clind['from'] . '-' . $clind['to'] . ')' . '&nbsp;&nbsp;' . ct_price($clind['price']) ;
                                                    }
                                                    $lines .= implode('<br><br>اطفل الاول', $child1Price);
                                                    $lines .= '&nbsp; لليلة <br/><br/>';
                                                    $lines .= 'و';
                                                    
                                                    $lines .= '&nbsp;' . $child_labels[1];
                                                    $child1Price2 = [];
                                                    foreach($pricePerChild2 as $index => $clind){
                                                        $child1Price2[] = '(' . $clind['from'] . '-' . $clind['to'] . ')' . '&nbsp;&nbsp;' . ct_price($clind['price']) ;
                                                    }
                                                    
                                                    $lines .= implode('<br><br>الطفل الثاني', $child1Price2);
                                                    $lines .= '&nbsp; لليلة <br />';
                                                }
                                                ?>
                                                <strong>
                                                    <?php
                                                    $total = $value['total'];
                                                    if (!empty($total))
                                                        echo ct_price($value['total'])
                                                    ?>
                                                </strong>
                                            </td>

                                            <td class="new-tooltip">
                                                <span><i data-toggle="tooltip" data-placement="top" title="<?php echo $lines ?? ""; ?>" class="fa fa-question-circle" aria-hidden="true"> ؟ </i></span>
                                            </td>

                                        </tr>
                            <?php
                                        $i++;
                                    endforeach;
                                endif;
                            endforeach;
                            ?>
                        </tbody>
                    </table>

                    <span class="mobile-txt">رحلات اختياريه و خدمات أخري</span>

                    <?php if (!empty($add_services)) : ?>
                        <table class="table table-striped options_cart">
                            <thead>
                                <tr>
                                    <th colspan="4"><?php echo esc_html__('Add options / Services', 'citytours') ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($add_services as $service) : ?>
                                    <tr>
                                        <td>
                                            <i class="<?php echo esc_attr($service->icon_class); ?>"></i>
                                        </td>
                                        <td>
                                            <?php echo esc_attr($service->title); ?>
                                            <strong>+<?php echo ct_price($service->price); ?></strong>
                                        </td>
                                        <td>
                                            <?php
                                            $field_name = 'add_service_' . esc_attr($service->id);

                                            if (!empty($cart_service) && !empty($cart_service[$service->id])) {
                                                $temp_value = isset($cart_service[$service->id]['qty']) ? $cart_service[$service->id]['qty'] : 1;
                                            } else {
                                                $temp_value = isset($_REQUEST[$field_name]) ? $_REQUEST[$field_name] : 1;
                                            }
                                            ?>

                                            <div class="numbers-row post-right <?php if (empty($cart_service) || empty($cart_service[$service->id])) echo 'hide-row'; ?>" data-min="1">
                                                <input type="text" class="qty2 form-control <?php echo $field_name ?>" name="<?php echo $field_name ?>" value="<?php echo $temp_value ?>">

                                                <div class="inc button_inc">+</div>
                                                <div class="dec button_inc">-</div>
                                            </div>
                                        </td>
                                        <td>
                                            <label class="switch-light switch-ios pull-right">
                                                <input type="checkbox" name="add_service[<?php echo esc_attr($service->id); ?>]" value="1" <?php if (!empty($cart_service) && !empty($cart_service[$service->id])) echo ' checked="checked"' ?>>
                                                <span>
                                                    <span><?php echo esc_html__('No', 'citytours') ?></span>
                                                    <span><?php echo esc_html__('Yes', 'citytours') ?></span>
                                                </span>
                                                <a></a>
                                            </label>
                                        </td>
                                    </tr>
                                <?php endforeach ?>
                            </tbody>
                        </table>
                    <?php endif; ?>

                    <?php do_action('hotel_cart_main_after'); ?>
                </div><!-- End col-md-8 -->

                <aside class="col-md-4">
                    <?php do_action('hotel_cart_sidebar_before'); ?>
                    <div class="box_style_1">
                        <h3 class="inner"><?php echo esc_html__('- Summary -', 'citytours') ?></h3>
                        <table class="table table_summary">
                            <tbody>
                                <tr>
                                    <td><?php echo esc_html__('Check in', 'citytours') ?></td>
                                    <td class="text-right"><?php echo date_i18n('j F Y', ct_strtotime($date_from)); ?></td>
                                </tr>
                                <tr>
                                    <td><?php echo esc_html__('Check out', 'citytours') ?></td>
                                    <td class="text-right"><?php echo date_i18n('j F Y', ct_strtotime($date_to)); ?></td>
                                </tr>
                                <tr>
                                    <td><?php echo esc_html__('Rooms', 'citytours') ?></td>
                                    <td class="text-right" data-title="Rooms">
                                        <?php
                                        $cart_rooms = $cart->get_field($uid, 'room');
                                        if (!empty($cart_rooms)) {
                                            foreach ($cart_rooms as $room_id => $room_data) {
                                                if (isset($room_data['rooms'])) {
                                                    echo '<span style="font-weight:bold">' . count($room_data) . '</span> &nbsp; ' . esc_html($room_data['rooms'] . ' ' . get_the_title($room_id)) . '<br>';
                                                }
                                            }
                                        }
                                        ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <?php echo esc_html__('Nights', 'citytours') ?>
                                    </td>
                                    <td class="text-right">
                                        <?php echo ct_get_day_interval($date_from, $date_to) ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td><?php echo esc_html__('Adults', 'citytours') ?></td>
                                    <td class="text-right" data-title="total_adults"><?php echo esc_html($cart->get_field($uid, 'total_adults')) ?></td>
                                </tr>
                                <tr class="total_kids">
                                    <td><?php echo esc_html__('Children', 'citytours') ?></td>
                                    <td class="text-right" data-title="total_kids"><?php echo esc_html($cart->get_field($uid, 'total_kids')) ?></td>
                                </tr>
                                <?php
                                if (!empty($cart_service)) {
                                    foreach ($cart_service as $key => $service) {
                                ?>
                                        <tr class='services_summary'>
                                            <td><?php echo esc_html($service['title']) ?></td>
                                            <td class="text-right"><?php echo ct_price($service['total']); ?></td>
                                        </tr>
                                <?php
                                    }
                                }
                                ?>
                                <tr class="total">
                                    <td><?php echo esc_html__('Total cost', 'citytours') ?></td>
                                    <td class="text-right" data-title="total_price"><?php
                                                                                    $total_price = $cart->get_field($uid, 'total_price');
                                                                                    if (!empty($total_price))
                                                                                        echo ct_price($total_price)
                                                                                    ?></td>
                                </tr>
                                <tr>
                                    <td><?php echo sprintf(esc_html__('Security Deposit (%d%%)', 'citytours'), $deposit_rate) ?></td>
                                    <td class="text-right"><?php if (!empty($total_price)) echo ct_price($total_price * $deposit_rate / 100) ?></td>
                                </tr>
                            </tbody>
                        </table>
                        <a class="btn_full book-now-btn" href="#"><?php echo esc_html__('Book now', 'citytours') ?></a>
                        <!-- <a class="btn_full update-cart-btn" href="#"><?php // echo esc_html__('Update price', 'citytours')
                                                                            ?></a> -->
                        <a class="btn_full_outline" href="<?php echo esc_url(get_permalink($hotel_id)) ?>"><i class="icon-right"></i> <?php echo esc_html__('Modify your search', 'citytours') ?></a>
                        <input type="hidden" name="action" value="ct_hotel_book">
                        <input type="hidden" name="hotel_id" value="<?php echo esc_attr($hotel_id) ?>">
                        <input type="hidden" name="date_from" value="<?php echo esc_attr($date_from) ?>">
                        <input type="hidden" name="date_to" value="<?php echo esc_attr($date_to) ?>">
                        <?php wp_nonce_field('hotel_update_cart'); ?>
                    </div>
                    <?php do_action('hotel_cart_sidebar_after'); ?>
                    <?php ct_get_template('contact_info.php', '/'); ?>
                </aside><!-- End aside -->
            </div>
            <!--End row -->
        </form>

        <script type="text/javascript">
            var ajaxurl = '<?php echo esc_js(admin_url('admin-ajax.php')) ?>';
            var is_woocommerce_enabled = '<?php
                                            if (ct_is_woocommerce_integration_enabled())
                                                echo "true";
                                            else
                                                echo "false"
                                            ?>';

            $ = jQuery.noConflict();

            $(document).ready(function() {

                $('[data-toggle="tooltip"]').tooltip({
                    html: true,
                });

                updateCart = function() {
                    $('input[name="action"]').val('ct_hotel_update_cart');
                    $('#overlay').fadeIn();
                    $.ajax({
                        url: ajaxurl,
                        type: "POST",
                        data: $('#hotel-cart').serialize(),
                        success: function(response) {

                            if (response.success == 1) {
                                var obj = JSON.parse(response.cart_data)
                                $.each(obj['room'], function(index, valObj) {
                                    if (typeof valObj === 'object') {
                                        $.each(valObj, function(key, value) {
                                            var thisObj = $('#' + index + key);
                                            thisObj.find('td[data-title="Total"]').html("<strong data-toggle='tooltip' data-placement='top' title='Tooltip on top'> " + value['total'] + "&nbsp; ج   . م  <button type='button' class='close' aria-hidden='true'>&times;</button></strong>");
                                        })
                                    }
                                })

                                $('td[data-title="total_adults"]').text(obj['total_adults']);
                                $('td[data-title="total_kids"]').text(obj['total_kids']);

                                $('.services_summary').remove()

                                if (typeof obj['add_service'] === 'object') {
                                    $.each(obj['add_service'], function(index, value) {
                                        $('.total_kids').closest("tr").after("<tr class='services_summary'><td>" + value['title'] + "</td><td class='text-right'>" + value['total'] + "&nbsp; ج   . م </td></tr>");
                                    })
                                }

                                $('td[data-title="total_price"]').html(obj['total_price'] + "&nbsp; ج   . م ");
                            } else {
                                alert(response.message);
                            }
                        }
                    });

                    $('#overlay').fadeOut();
                }

                $('.options_cart input').change(function() {
                    updateCart()
                })

                $('table').delegate('.room-quantity', 'change', function() {
                    let $adults = $(this).closest('tr').find('.room-adults');
                    let $kids = $(this).closest('tr').find('.room-kids');
                    let max_adults = 0,
                        max_kids = 0;
                    if ($adults.parent('.numbers-row').attr('data-per-room'))
                        max_adults = $adults.parent('.numbers-row').data('per-room');
                    if ($kids.parent('.numbers-row').attr('data-per-room'))
                        max_kids = $kids.parent('.numbers-row').data('per-room');
                        let rooms = parseInt($(this).val(), 10);
                    if (max_adults > 0 && (max_adults * rooms < parseInt($adults.val(), 10)))
                        $adults.val(max_adults * rooms);
                    if (max_kids > 0 && (max_kids * rooms < parseInt($kids.val(), 10)))
                        $kids.val(max_kids * rooms);

                    updateCart()
                });

                // number of adults
                $('table').delegate('.room-adults', 'change', function() {
                    var $quantity = $(this).closest('tr').find('.room-quantity');
                    var adults = parseInt($(this).val(), 10);
                    var max_adults = 0;
                    if ($(this).parent('.numbers-row').attr('data-per-room')) {
                        max_adults = $(this).parent('.numbers-row').data('per-room');
                        if ((max_adults * $quantity.val() < adults))
                            $quantity.val(Math.ceil(adults / max_adults));
                        if (adults == 0)
                            $quantity.val(0);
                    }

                    updateCart()
                });

                // number of kids
                $('table').delegate('.room-kids', 'change', function() {
                    var $quantity = $(this).closest('tr').find('.room-quantity');
                    var roomId = $(this).closest('tr').find("input[name*='room_type_id']").val()
                    var roomKids = $(this).closest('tr').find('.room-kids').val()
                    var n = parseInt($(this).closest('tr').find(".divKidsAge").length)
                    var num = $(this).closest('tr').attr('number-row');
                    var max_kids = 0
                    if ($(this).parent('.numbers-row').attr('data-per-room')) {
                        max_kids = $(this).parent('.numbers-row').data('per-room');
                        if (n < max_kids && n < roomKids) {
                            let kidsNstring = (n == 0) ? '<span>عمر الطفل الأول</span>' : '<span>عمر الطفل الثاني</span>';
                            let ageSelectHtml ='<div class="divKidsAge">'+kidsNstring
                                ageSelectHtml += '<select name="kidsAge[' + roomId + '][' + num + '][' + n + ']" style="width:60%" class="form-control kidsAge">';
                                ageSelectHtml += '<option value="1">1</option>';
                                ageSelectHtml += '<option value="2">2</option>';
                                ageSelectHtml += '<option value="3">3</option>';
                                ageSelectHtml += '<option value="4">4</option>';
                                ageSelectHtml += '<option value="5">5</option>';
                                ageSelectHtml += '<option value="6">6</option>';
                                ageSelectHtml += '<option value="7">7</option>';
                                ageSelectHtml += '<option value="8">8</option>';
                                ageSelectHtml += '<option value="9">9</option>';
                                ageSelectHtml += '<option value="10">10</option>';
                                ageSelectHtml += '<option value="11">11</option>';
                                ageSelectHtml += '<option value="12">12</option>';
                                ageSelectHtml += '</select>';

                            $(this).closest('tr').find('.tdkidsage').css('display', 'block');    
                            $(this).closest('tr').find('.tdkidsage').append(ageSelectHtml);
                            
                        } else if (n > roomKids) {
                            len = parseInt(n - 1);
                            if (len > 0)
                                $(this).closest('tr').find(".divKidsAge").eq(len).remove();
                            else
                                $(this).closest('tr').find(".divKidsAge").remove();
                        }
                    }

                    updateCart()
                });

                // number of kids age selection
                $('table').delegate('.kidsAge', 'change', function() {
                    updateCart()
                })

                $('.update-cart-btn').click(function(e) {
                    e.preventDefault();
                    updateCart()

                    return false;
                });

                $('.book-now-btn').click(function(e) {
                    e.preventDefault();
                    if (is_woocommerce_enabled == "true") {
                        $('#overlay').fadeIn();
                        $('input[name="action"]').val('ct_add_hotel_to_woo_cart');

                        $.ajax({
                            url: ajaxurl,
                            type: "POST",
                            data: $('#hotel-cart').serialize(),
                            success: function(response) {
                                $('#overlay').fadeOut();
                                if (response.success == 1) {
                                    document.location.href = $("#hotel-cart").attr('action');
                                    // alert(response.message);
                                } else {
                                    alert(response.message);
                                }
                            }
                        });
                    } else {
                        document.location.href = $("#hotel-cart").attr('action');
                    }
                });

                $('.options_cart input[type="checkbox"]').change(function() {
                    var qty_display = $(this).parent().parent().parent().find('.numbers-row');

                    if (qty_display.hasClass("hide-row")) {
                        qty_display.removeClass("hide-row");
                    } else {
                        qty_display.addClass("hide-row");
                    }
                });
            });
        </script>

    <?php else : ?>
        <h5 class="alert alert-warning"><?php echo esc_html($room_ids); ?></h5>
<?php
    endif;
}
?>
