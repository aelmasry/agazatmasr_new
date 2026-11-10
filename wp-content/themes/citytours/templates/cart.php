<?php
//session_destroy();
/* Hotel Cart Template */
if (!defined('ABSPATH')) {
    exit;
}
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

                    <table class="table table-striped cart-list hotel add_bottom_30">
                        <thead>
                            <tr>
                                <th><?php echo esc_html__('Room Type', 'citytours') ?></th>
                                <!--<th><?php // echo esc_html__('Quantity', 'citytours')   ?></th>-->
                                <th><?php echo esc_html__('Adults', 'citytours') ?></th>
                                <th><?php echo esc_html__('Children', 'citytours') ?></th>
                                <th colspan="2"><?php echo esc_html__('Total', 'citytours') ?></th>
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
                                
                                if (empty($cart->get_room_fields($uid, $room_id))) :
                            ?>
                                    <tr number-row="0">
                                        <td data-title="<?php esc_attr_e('Room Type', 'citytours') ?>">
                                            <div class="thumb_cart">
                                                <a href="#" data-toggle="modal" data-target="#room-<?php echo esc_attr($room_id) ?>"><?php echo get_the_post_thumbnail($room_id, 'thumbnail'); ?></a>
                                            </div>
                                            <span class="item_cart">
                                                <a href="#" data-toggle="modal" data-target="#room-<?php echo esc_attr($room_id) ?>"><?php echo esc_html(get_the_title($room_id)); ?></a>
                                            </span>
                                            <input type="hidden" name="room_type_id[<?php echo esc_attr($room_id) ?>][]" value="<?php echo esc_attr($room_id) ?>">
                                            <input type="hidden" class="qty2 form-control room-quantity" name="rooms[<?php echo esc_attr($room_id) ?>][]" value="<?php echo esc_attr($cart->get_room_field($uid, $room_id, 'rooms')) ?>">
                                        </td>
                                        <td data-title="<?php esc_attr_e('Adults', 'citytours') ?>">
                                            <div class="numbers-row" data-min="0" <?php if (!empty($max_adults)) echo 'data-max="' . esc_attr($max_adults) . '" data-per-room="' . esc_attr($max_adults) . '"'; ?>>
                                                <input type="text" class="qty2 form-control room-adults" name="adults[<?php echo esc_attr($room_id) ?>][]" value="<?php echo esc_attr($cart->get_room_field($uid, $room_id, 'adults')) ?>">
                                                <div class="inc button_inc">+</div>
                                                <div class="dec button_inc">-</div>
                                            </div>
                                        </td>
                                        <td data-title="<?php esc_attr_e('Children', 'citytours') ?>">
                                            <?php if (!empty($max_kids)) : ?>
                                            <div class="numbers-row" data-min="0" data-max="<?php echo esc_attr($max_kids) ?>" data-per-room="<?php echo esc_attr($max_kids) ?>">
                                                <input type="text" class="qty2 form-control room-kids" name="kids[<?php echo esc_attr($room_id) ?>][]" value="<?php echo esc_attr($cart->get_room_field($uid, $room_id, 'kids')) ?>">

                                                <div class="inc button_inc">+</div>
                                                <div class="dec button_inc">-</div>
                                            </div>
                                            <?php endif; ?>
                                        </td>
                                        <td data-title="<?php esc_attr_e('Total', 'citytours') ?>">
                                            <strong>
                                                <?php
                                                $total = $cart->get_room_field($uid, $room_id, 'total');
                                                if (!empty($total))
                                                    echo ct_price($cart->get_room_field($uid, $room_id, 'total'))
                                                    ?>
                                            </strong>
                                        </td>
                                        <td>
                                            <button class="btn btn-success btn-add" type="button">
                                                <span class="glyphicon glyphicon-plus"></span>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php
                                else:
                                    $i = 0;
                                    foreach ($cart->get_room_fields($uid, $room_id) as $key => $value) : 
                                        ?>
                                        <tr number-row="<?php echo $key ?>">
                                            <td data-title="<?php esc_attr_e('Room Type', 'citytours') ?>">
                                                <div class="thumb_cart">
                                                    <a href="#" data-toggle="modal" data-target="#room-<?php echo esc_attr($room_id) ?>"><?php echo get_the_post_thumbnail($room_id, 'thumbnail'); ?></a>
                                                </div>
                                                <span class="item_cart">
                                                    <a href="#" data-toggle="modal" data-target="#room-<?php echo esc_attr($room_id) ?>"><?php echo esc_html(get_the_title($room_id)); ?></a>
                                                </span>
                                                <input type="hidden" name="room_type_id[<?php echo esc_attr($room_id) ?>][<?php echo $key ?>]" value="<?php echo esc_attr($room_id) ?>">
                                                <input type="hidden" class="qty2 form-control room-quantity" name="rooms[<?php echo esc_attr($room_id) ?>][<?php echo $key ?>]" value="<?php echo esc_attr($value['rooms']) ?>">
                                            </td>
                                            <td data-title="<?php esc_attr_e('Adults', 'citytours') ?>">
                                                <div class="numbers-row" data-min="0" <?php if (!empty($max_adults)) echo 'data-max="' . esc_attr($max_adults) . '" data-per-room="' . esc_attr($max_adults) . '"'; ?>>
                                                    <input type="text" class="qty2 form-control room-adults" name="adults[<?php echo esc_attr($room_id) ?>][<?php echo $key ?>]" value="<?php echo esc_attr($value['adults']) ?>">

                                                    <div class="inc button_inc">+</div>
                                                    <div class="dec button_inc">-</div>
                                                </div>
                                            </td>
                                            <td data-title="<?php esc_attr_e('Children', 'citytours') ?>">
                    <?php if (!empty($max_kids)) : ?>
                                                    <div class="numbers-row" data-min="0" data-max="<?php echo esc_attr($max_kids) ?>" data-per-room="<?php echo esc_attr($max_kids) ?>">
                                                        <input type="text" class="qty2 form-control room-kids" name="kids[<?php echo esc_attr($room_id) ?>][<?php echo $key ?>]" value="<?php echo esc_attr($value['kids']) ?>">

                                                        <div class="inc button_inc">+</div>
                                                        <div class="dec button_inc">-</div>
                                                    </div>
                                                        <?php
                                                        if (!empty($value['kidsAge'])):
                                                            foreach ($value['kidsAge'] as $k => $age):
                                                                ?>
                                                            <select name='kidsAge[<?php echo $room_id ?>][<?php echo $key ?>][]' style='width:60%' class='form-control kidsAge'>
                                                            <?php
                                                            for ($x = 1; $x < 13; $x++):
                                                                echo "<option value='" . $x . "' " . (($x == $age) ? 'Selected' : '') . ">" . $x . "</option>";
                                                            endfor;
                                                            ?>
                                                            </select>
                                                            <?php
                                                        endforeach;
                                                    endif;
                                                    ?>

                                                    <?php endif; ?>
                                            </td>
                                            <td data-title="<?php esc_attr_e('Total', 'citytours') ?>">
                                                <strong><?php
                                $total = $value['total'];
                                if (!empty($total))
                                    echo ct_price($value['total'])
                                    ?></strong>
                                            </td>
                                            <td>
                    <?php if ($i <= 0) : ?>
                                                    <button class="btn btn-success btn-add" type="button">
                                                        <span class="glyphicon glyphicon-plus"></span>
                                                    </button>
                    <?php else: ?>
                                                    <button class="btn btn-remove btn-danger" type="button">
                                                        <span class="glyphicon glyphicon-minus"></span>
                                                    </button>
                                        <?php endif; ?>
                                            </td>
                                        </tr>
                    <?php $i++;
                endforeach;
                ?>
            <?php endif; ?>
        <?php endforeach; ?>
                        </tbody>
                    </table>

        <?php if (!empty($add_services)) : ?>
                        <table class="table table-striped options_cart">
                            <thead><tr><th colspan="4"><?php echo esc_html__('Add options / Services', 'citytours') ?></th></tr></thead>
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
                                                <input type="checkbox" name="add_service[<?php echo esc_attr($service->id); ?>]" value="1"<?php if (!empty($cart_service) && !empty($cart_service[$service->id])) echo ' checked="checked"' ?>>
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
                                    <td class="text-right">
                                        <?php
                                        $cart_rooms = $cart->get_field($uid, 'room');
                                        if (!empty($cart_rooms)) {
                                            foreach ($cart_rooms as $room_id => $room_data) {
                                                echo '<span style="font-weight:bold">'.count($room_data).'</span> &nbsp; '.esc_html($room_data['rooms'] . ' ' . get_the_title($room_id)).'<br>';
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
                                    <td class="text-right"><?php echo esc_html($cart->get_field($uid, 'total_adults')) ?></td>
                                </tr>
                                <tr>
                                    <td><?php echo esc_html__('Children', 'citytours') ?></td>
                                    <td class="text-right"><?php echo esc_html($cart->get_field($uid, 'total_kids')) ?></td>
                                </tr>
        <?php
        if (!empty($cart_service)) {
            foreach ($cart_service as $key => $service) {
                ?>
                                        <tr>
                                            <td><?php echo esc_html($service['title']) ?></td>
                                            <td class="text-right"><?php echo ct_price($service['total']); ?></td>
                                        </tr>
                                                <?php
                                            }
                                        }
                                        ?>
                                <tr class="total">
                                    <td><?php echo esc_html__('Total cost', 'citytours') ?></td>
                                    <td class="text-right"><?php
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
                        <a class="btn_full update-cart-btn" href="#"><?php echo esc_html__('Update Cart', 'citytours') ?></a>
                        <a class="btn_full_outline" href="<?php echo esc_url(get_permalink($hotel_id)) ?>"><i class="icon-right"></i> <?php echo esc_html__('Modify your search', 'citytours') ?></a>
                        <input type="hidden" name="action" value="ct_hotel_book">
                        <input type="hidden" name="hotel_id" value="<?php echo esc_attr($hotel_id) ?>">
                        <input type="hidden" name="date_from" value="<?php echo esc_attr($date_from) ?>">
                        <input type="hidden" name="date_to" value="<?php echo esc_attr($date_to) ?>">
        <?php wp_nonce_field('hotel_update_cart'); ?>
                    </div>
        <?php do_action('hotel_cart_sidebar_after'); ?>
                </aside><!-- End aside -->
            </div><!--End row -->
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

            $(document).ready(function () {

                $('table').delegate('.room-quantity', 'change', function () {
                    var $adults = $(this).closest('tr').find('.room-adults');
                    var $kids = $(this).closest('tr').find('.room-kids');
                    var max_adults = 0, max_kids = 0;
                    if ($adults.parent('.numbers-row').attr('data-per-room'))
                        max_adults = $adults.parent('.numbers-row').data('per-room');
                    if ($kids.parent('.numbers-row').attr('data-per-room'))
                        max_kids = $kids.parent('.numbers-row').data('per-room');
                    var rooms = parseInt($(this).val(), 10);
                    if (max_adults > 0 && (max_adults * rooms < parseInt($adults.val(), 10)))
                        $adults.val(max_adults * rooms);
                    if (max_kids > 0 && (max_kids * rooms < parseInt($kids.val(), 10)))
                        $kids.val(max_kids * rooms);
                });

                $('table').delegate('.room-adults', 'change', function () {
                    var $quantity = $(this).closest('tr').find('.room-quantity');
                    var adults = parseInt($(this).val(), 10);
                    var max_adults = 0;
                    if ($(this).parent('.numbers-row').attr('data-per-room')) {
                        max_adults = $(this).parent('.numbers-row').data('per-room');
                        if ((max_adults * $quantity.val() < adults))
                            $quantity.val(Math.ceil(adults / max_adults));
                        if(adults == 0)
                            $quantity.val(0);
                    }
                });

                $('table').delegate('.room-kids', 'change', function () {
                    var $quantity = $(this).closest('tr').find('.room-quantity');
                    var roomId = $(this).closest('tr').find("input[name*='room_type_id']").val()
                    var roomKids = $(this).closest('tr').find('.room-kids').val()
                    var n = parseInt($(this).closest('td').find("select").length)
                    var num = $(this).closest('tr').attr('number-row');
                    var max_kids = 0
                    if ($(this).parent('.numbers-row').attr('data-per-room')) {
                        max_kids = $(this).parent('.numbers-row').data('per-room');
                        if (n < max_kids && n < roomKids) {
                            $(this).closest('td').append(
                                "<select name='kidsAge[" + roomId + "]["+num+"][]' style='width:60%' class='form-control kidsAge'>\n\
                                    <option value='1'>1</option>\n\
                                    <option value='2'>2</option>\n\
                                    <option value='3'>3</option>\n\
                                    <option value='4'>4</option>\n\
                                    <option value='5'>5</option>\n\
                                    <option value='6'>6</option>\n\
                                    <option value='7'>7</option>\n\
                                    <option value='8'>8</option>\n\
                                    <option value='9'>9</option>\n\
                                    <option value='10'>10</option>\n\
                                    <option value='11'>11</option>\n\
                                    <option value='12'>12</option>\n\
                                </select>"
                            );
                        }
                        else if (n > roomKids) {
                            len = parseInt(n - 1);
                            if(len > 0)
                                $(this).closest('tr').find("select").eq(len).remove();
                            else
                                $(this).closest('tr').find("select").remove();
                        }
                    } 
                });

                $('table').delegate('input,select', 'change', function () {
                    $('.update-cart-btn').css('display', 'inline-block');
                    $('.book-now-btn').hide();
                });

                $('.update-cart-btn').click(function (e) {
                    e.preventDefault();
                    $('input[name="action"]').val('ct_hotel_update_cart');
                    $('#overlay').fadeIn();
                    $.ajax({
                        url: ajaxurl,
                        type: "POST",
                        data: $('#hotel-cart').serialize(),
                        success: function (response) {
                            console.log(response);
                            $('#overlay').fadeOut();
                            if (response.success == 1) {
                                location.reload();
                            } else {
                                alert(response.message);
                            }
                        }
                    });
                    return false;
                });

                $('.book-now-btn').click(function (e) {
                    e.preventDefault();
                    if (is_woocommerce_enabled == "true") {
                        $('#overlay').fadeIn();
                        $('input[name="action"]').val('ct_add_hotel_to_woo_cart');

                        $.ajax({
                            url: ajaxurl,
                            type: "POST",
                            data: $('#hotel-cart').serialize(),
                            success: function (response) {
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

                $('.options_cart input[type="checkbox"]').change(function () {
                    var qty_display = $(this).parent().parent().parent().find('.numbers-row');

                    if (qty_display.hasClass("hide-row")) {
                        qty_display.removeClass("hide-row");
                    } else {
                        qty_display.addClass("hide-row");
                    }
                });
            });


            $('table').delegate('.btn-add', 'click', function () {
		var roomId = $(this).closest('tr').find("input[name*='room_type_id']").val();
                var $inputLast = $("input[value*="+roomId+"]").last();
		var length = parseInt($inputLast.closest('tr').attr('number-row')) +1;
                var thisRow = $(this).closest('tr');
                thisRow = $(thisRow).closest('tr').clone().insertAfter(thisRow)
                thisRow.attr('number-row', length)
		thisRow.find('select').remove()
		thisRow.find('input:text').val(0)
                thisRow.find('input[name*=kids]').attr('name', 'kidsAge['+roomId+']['+length+']')
		thisRow.find('input[name*=room_type_id]').attr('name', 'room_type_id['+roomId+']['+length+']')
		thisRow.find('input[name*=rooms]').attr('name', 'rooms['+roomId+']['+length+']')
		thisRow.find('input[name*=adults]').attr('name', 'adults['+roomId+']['+length+']')
		thisRow.find('input[name*=kids]').attr('name', 'kids['+roomId+']['+length+']')
		thisRow.find('.btn-add').removeClass('btn-add').addClass('btn-remove').removeClass('btn-success').addClass('btn-danger').html('<span class="glyphicon glyphicon-minus"></span>');
		thisRow.find('td[data-title="Total"]').text('')
            })
            
            $('table').delegate('.btn-remove', 'click', function () {
                $(this).closest('tr').remove()
                $('.update-cart-btn').css('display', 'inline-block');
                $('.book-now-btn').hide();
            })

        </script>

    <?php else : ?>
        <h5 class="alert alert-warning"><?php echo esc_html($room_ids); ?></h5>
    <?php
    endif;
}
?>