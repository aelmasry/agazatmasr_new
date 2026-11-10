<?php
/* Tour Cart Page Template */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// validation
$required_params = array( 'tour_id' );
foreach ( $required_params as $param ) {
    if ( ! isset( $_REQUEST[ $param ] ) ) {
        do_action( 'ct_tour_booking_wrong_data' ); // ct_redirect_home() - if data is not valid return to home
        exit;
    }
}

// init variables
$tour_id      = $_REQUEST['tour_id'];
$is_repeated  = get_post_meta( $tour_id, '_tour_repeated', true );
$tour_date    = get_post_meta( $tour_id, '_tour_date', true );
$charge_child = get_post_meta( $tour_id, '_tour_charge_child', true );
$deposit_rate = get_post_meta( $tour_id, '_tour_security_deposit', true );
$deposit_rate = empty( $deposit_rate ) ? 0 : $deposit_rate;
$add_services = ct_get_add_services_by_postid( $tour_id );

$date = '';
if ( ! empty( $is_repeated ) ) {
    if ( empty( $_REQUEST['date'] ) ) {
        do_action( 'ct_tour_booking_wrong_data' ); // ct_redirect_home() - if data is not valid return to home
        exit;
    }
    $date = $_REQUEST['date'];
} else if ( ! empty( $tour_date ) ) {
    $date = $tour_date;
}

$uid = $tour_id . $date;

if ( $cart_data = CT_Hotel_Cart::get( $uid ) ) {
    // init booking info if cart is not empty
    $adults      = $cart_data['tour']['adults'];
    $kids        = $cart_data['tour']['kids'];
    $total_price = $cart_data['tour']['total'];

    if(is_array($cart_data['tour']['adults'])) {
        $cart_data['total_adults'] = array_sum($cart_data['tour']['adults']);
    }else {
        $cart_data['total_adults'] = $cart_data['tour']['adults'];
    }

    if(is_array($cart_data['tour']['kids'])) {
        $cart_data['total_kids'] = array_sum($cart_data['tour']['kids']);
    }else {
        $cart_data['total_kids'] = $cart_data['tour']['kids'];
    }

} else {
    // init cart if it is empty
    $adults      = ( isset( $_REQUEST['adults'] ) ) ? $_REQUEST['adults'] : 0;
    $kids        = ( isset( $_REQUEST['kids'] ) ) ? $_REQUEST['kids'] : 0;
    $total_price = ct_tour_calc_tour_price( $tour_id, $date, $adults, $kids );
    $cart_data   = array(
        'tour'          => array(
            'adults'    => $adults,
            'kids'      => $kids,
            'total'     => $total_price
        ),
        'tour_id'       => $tour_id,
        'date'          => $date,
        'total_adults'  => $adults,
        'total_kids'    => $kids,
        'total_price'   => $total_price,
    );

    CT_Hotel_Cart::set( $uid, $cart_data );
}

$cart = new CT_Hotel_Cart();
$cart_service = $cart->get_field( $uid, 'add_service' );
$ct_tour_checkout_page_url = apply_filters( 'ct_get_woocommerce_cart_url', ct_get_tour_checkout_page() );

// main function
if ( ! $ct_tour_checkout_page_url ) {
    ?>

    <h5 class="alert alert-warning"><?php echo esc_html__( 'Please set checkout page in theme options panel.', 'citytours' ) ?></h5>

    <?php
} else {
    // function
    $is_available = ct_tour_check_availability( $tour_id, $date, $adults, $kids );
    if ( true === $is_available ) : ?>
    
    <form id="tour-cart" action="<?php echo esc_url( add_query_arg( array('uid'=> $uid), $ct_tour_checkout_page_url ) ); ?>">

        <div class="row">

            <div class="col-md-8">

                <?php do_action( 'tour_cart_main_before' ); ?>

                <div class="alert alert-info" role="alert">من فضلك أختار عدد الأفراد  ونوع الغرفة المطلوب حجزها</div>

                <table class="table table-striped cart-list tour add_bottom_30">
                    <thead>
                        <tr>
                            <th><span class="item_cart"><a href="#" data-toggle="modal" data-target="#tour-<?php echo esc_attr( $tour_id ) ?>"><?php echo esc_html( get_the_title( $tour_id ) ); ?></a></span></th>
                            <th><?php echo esc_html__( 'Number of Persons', 'citytours' ) ?></th>
                            <th><?php echo esc_html__( 'Price', 'citytours' ) ?></th>
                            <th><?php echo esc_html__( 'Total', 'citytours' ) ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            $price_per_person  = get_post_meta( $tour_id, '_tour_price', true );
                            if(!empty($price_per_person)) :
                        ?>
                        <tr data-title="person">
                            <td data-title="<?php esc_attr_e( 'Item', 'citytours' ) ?>">
                                <?php esc_html_e( 'Price Per Person', 'citytours' ) ?>
                            </td>
                            <td data-title="<?php esc_attr_e( 'Number of Persons', 'citytours' ) ?>">
                                <div class="numbers-row" data-min="0">
                                    <?php if(is_array($adults)) { ?>
                                    <input type="text" class="qty2 form-control tour-adults" name="adults[person]" value="<?php echo (!empty($adults['person'])) ? esc_attr($adults['person']) : 0 ?>">
                                    <?php } else { ?>
                                    <input type="text" class="qty2 form-control tour-adults" name="adults[person]" value="<?php echo (!empty($adults)) ? esc_attr($adults) : 0 ?>">
                                    <?php } ?>
                                    <div class="inc button_inc">+</div>
                                    <div class="dec button_inc">-</div>
                                </div>
                            </td>
                            <td data-title="Price">
                                <?php echo ct_price($price_per_person); ?>
                            </td>
                            <td data-title="Total">
                                <?php if(!isset($adults['person'])) { ?>
                                <strong><?php echo ct_price($price_per_person * $adults); ?></strong>
                                <?php } else { ?>
                                <strong><?php echo ct_price($price_per_person * $adults['person']); ?></strong>
                                <?php } ?>
                            </td>
                        </tr>
                        <?php endif;
                            $price_in_double_room  = get_post_meta( $tour_id, '_tour_price_in_double_room', true );
                            if(!empty($price_in_double_room)) :
                        ?>
                        <tr data-title="double">
                            <td data-title="<?php esc_attr_e( 'Item', 'citytours' ) ?>">
                                <?php  esc_html_e( 'Price Per Person in Double Room', 'citytours' ) ?>
                            </td>
                            <td data-title="<?php esc_attr_e( 'Number of Persons', 'citytours' ) ?>">
                                <div class="numbers-row" data-min="0">
                                    <input type="text" class="qty2 form-control tour-adults" name="adults[double]" value="<?php echo (!empty($adults['double'])) ? esc_attr($adults['double']) : 0 ?>">

                                    <div class="inc button_inc">+</div>
                                    <div class="dec button_inc">-</div>
                                </div>
                            </td>
                            <td data-title="Price">
                                <?php echo ct_price($price_in_double_room); ?>
                            </td>
                            <td data-title="Total">
                                <strong><?php echo (isset($adults['double'])) ? ct_price($adults['double'] * $price_in_double_room) : 0;  ?></strong>
                            </td>
                        </tr>
                        <?php endif;
                        $price_in_triple_room  = get_post_meta( $tour_id, '_tour_price_in_triple_room', true );
                        if(!empty($price_in_triple_room)) :
                        ?>
                        <tr data-title="triple">
                            <td data-title="<?php esc_attr_e( 'Item', 'citytours' ) ?>">
                                <?php  esc_html_e( 'Price Per Person in Triple Room', 'citytours' ) ?>
                            </td>
                            <td data-title="<?php esc_attr_e( 'Number of Persons', 'citytours' ) ?>">
                                <div class="numbers-row" data-min="0">
                                    <input type="text" class="qty2 form-control tour-adults" name="adults[triple]" value="<?php echo (!empty($adults['triple'])) ? esc_attr($adults['triple']) : 0 ?>">

                                    <div class="inc button_inc">+</div>
                                    <div class="dec button_inc">-</div>
                                </div>
                            </td>
                            <td data-title="Price">
                                <?php echo ct_price($price_in_triple_room); ?>
                            </td>
                            <td data-title="Total">
                                <strong><?php echo (isset($adults['triple'])) ? ct_price($adults['triple'] * $price_in_triple_room) : 0;  ?></strong>
                            </td>
                        </tr>
                        <?php endif;
                        $price_in_single_room = get_post_meta( $tour_id, '_tour_price_in_single_room', true );
                        if(!empty($price_in_single_room)) :
                        ?>
                        <tr data-title="single">
                            <td data-title="<?php esc_attr_e( 'Item', 'citytours' ) ?>">
                                <?php esc_html_e( 'Price Per Person in single Room', 'citytours' ) ?>
                            </td>
                            <td data-title="<?php esc_attr_e( 'Number of Persons', 'citytours' ) ?>">
                                <div class="numbers-row" data-min="0">
                                    <input type="text" class="qty2 form-control tour-adults" name="adults[single]" value="<?php echo (!empty($adults['single'])) ? esc_attr($adults['single']) : 0 ?>">

                                    <div class="inc button_inc">+</div>
                                    <div class="dec button_inc">-</div>
                                </div>
                            </td>
                            <td data-title="Price">
                                <?php echo ct_price($price_in_single_room); ?>
                            </td>
                            <td data-title="Total">
                                <strong><?php echo (isset($adults['single'])) ? ct_price($adults['single'] * $price_in_single_room) : 0;  ?></strong>
                            </td>
                        </tr>
                        <?php endif;
                        $price_in_quad_room = get_post_meta( $tour_id, '_tour_price_in_quad_room', true );
                        if(!empty($price_in_quad_room)) :
                        ?>
                        <tr data-title="quad">
                            <td data-title="<?php esc_attr_e( 'Item', 'citytours' ) ?>">
                                <?php esc_html_e( 'Price Per Person in Quad Room', 'citytours' ) ?>
                            </td>
                            <td data-title="<?php esc_attr_e( 'Number of Persons', 'citytours' ) ?>">
                                <div class="numbers-row" data-min="0">
                                    <input type="text" class="qty2 form-control tour-adults" name="adults[quad]" value="<?php echo (!empty($adults['quad'])) ? esc_attr($adults['quad']) : 0 ?>">

                                    <div class="inc button_inc">+</div>
                                    <div class="dec button_inc">-</div>
                                </div>
                            </td>
                            <td data-title="Price">
                                <?php echo ct_price($price_in_quad_room); ?>
                            </td>
                            <td data-title="Total">
                                <strong><?php echo (isset($adults['quad'])) ? ct_price($adults['quad'] * $price_in_quad_room) : 0;  ?></strong>
                            </td>
                        </tr>
                        <?php endif;
                        if($charge_child) :
                        $price_child = get_post_meta( $tour_id, '_tour_price_child', true );
                        if(!empty($price_child)) :
                        ?>
                        <tr data-title="kids_0">
                            <td data-title="<?php esc_attr_e( 'Item', 'citytours' ) ?>">
                                <?php esc_html_e( 'Price Per Child', 'citytours' ) ?>
                            </td>
                            <td data-title="<?php esc_attr_e( 'Number of Persons', 'citytours' ) ?>">
                                <div class="numbers-row" data-min="0">
                                    <input type="text" class="qty2 form-control tour-adults" name="kids[0]" value="<?php echo (!empty($kids['0'])) ? esc_attr($kids['0']) : 0 ?>">

                                    <div class="inc button_inc">+</div>
                                    <div class="dec button_inc">-</div>
                                </div>
                            </td>
                            <td data-title="Price">
                                <?php echo ct_price($price_child); ?>
                            </td>
                            <td data-title="Total">
                                <strong><?php echo (isset($kids[0])) ? ct_price($kids[0] * $price_child) : 0;  ?></strong>
                            </td>
                        </tr>
                        <?php endif;
                        $price_child_0_2 = get_post_meta( $tour_id, '_tour_price_child_0_2', true );
                        if(!empty($price_child_0_2)) :
                        ?>
                        <tr data-title="kids_2">
                            <td data-title="<?php esc_attr_e( 'Item', 'citytours' ) ?>">
                                <?php esc_html_e( 'Price child less than 2 years', 'citytours' ) ?>
                            </td>
                            <td data-title="<?php esc_attr_e( 'Number of Persons', 'citytours' ) ?>">
                                <div class="numbers-row" data-min="0">
                                    <input type="text" class="qty2 form-control tour-adults" name="kids[2]" value="<?php echo (!empty($kids['2'])) ? esc_attr($kids['2']) : 0 ?>">

                                    <div class="inc button_inc">+</div>
                                    <div class="dec button_inc">-</div>
                                </div>
                            </td>
                            <td data-title="Price">
                                <?php echo ct_price($price_child_0_2); ?>
                            </td>
                            <td data-title="Total">
                                <strong><?php echo (isset($kids[0])) ? ct_price($kids[0] * $price_child_0_2) : 0;  ?></strong>
                            </td>
                        </tr>
                        <?php endif;
                        $price_child_2_6 = get_post_meta( $tour_id, '_tour_price_child_2_6', true );
                        if(!empty($price_child_2_6)) :
                        ?>
                        <tr data-title="kids_6">
                            <td data-title="<?php esc_attr_e( 'Item', 'citytours' ) ?>">
                                <?php esc_html_e( 'Price child from 2 to 6', 'citytours' ) ?>
                            </td>
                            <td data-title="<?php esc_attr_e( 'Number of Persons', 'citytours' ) ?>">
                                <div class="numbers-row" data-min="0">
                                    <input type="text" class="qty2 form-control tour-adults" name="kids[6]" value="<?php echo (!empty($kids['6'])) ? esc_attr($kids['6']) : 0 ?>">

                                    <div class="inc button_inc">+</div>
                                    <div class="dec button_inc">-</div>
                                </div>
                            </td>
                            <td data-title="Price">
                                <?php echo ct_price($price_child_2_6); ?>
                            </td>
                            <td data-title="Total">
                                <strong><?php echo (isset($kids[0])) ? ct_price($kids[0] * $price_child_2_6) : 0;  ?></strong>
                            </td>
                        </tr>
                        <?php endif;
                        $price_child_6_11 = get_post_meta( $tour_id, '_tour_price_child_6_11', true );
                        if(!empty($price_child_6_11)) :
                        ?>
                        <tr data-title="kids_11">
                            <td data-title="<?php esc_attr_e( 'Item', 'citytours' ) ?>">
                                <?php  esc_html_e( 'Price child from 6 to 11', 'citytours' ) ?>
                            </td>
                            <td data-title="<?php esc_attr_e( 'Number of Persons', 'citytours' ) ?>">
                                <div class="numbers-row" data-min="0">
                                    <input type="text" class="qty2 form-control tour-adults" name="kids[11]" value="<?php echo (!empty($kids['11'])) ? esc_attr($kids['11']) : 0 ?>">

                                    <div class="inc button_inc">+</div>
                                    <div class="dec button_inc">-</div>
                                </div>
                            </td>
                            <td data-title="Price">
                                <?php echo ct_price($price_child_6_11); ?>
                            </td>
                            <td data-title="Total">
                                <strong><?php echo (isset($kids[11])) ? ct_price($kids[11] * $price_child_6_11) : 0;  ?></strong>
                            </td>
                        </tr>
                        <?php endif;
                        endif;
                        ?>

                    </tbody>

                </table>

                    <?php if ( ! empty( $add_services ) ) : ?>
                    <table class="table table-striped options_cart">
                        <thead>
                            <tr>
                                <th colspan="4"><?php echo esc_html__( 'Add options / Services', 'citytours' ) ?></th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php foreach ( $add_services as $service ) : ?>
                                <tr>
                                    <td>
                                        <i class="<?php echo esc_attr( $service->icon_class ); ?>"></i>
                                    </td>
                                    <td>
                                        <?php echo esc_attr( $service->title ); ?>
                                        <strong>+<?php echo ct_price( $service->price ); ?></strong>
                                    </td>
                                    <td>
                                        <?php
                                        $field_name = 'add_service_' . esc_attr( $service->id );

                                        if ( ! empty( $cart_service ) && ! empty( $cart_service[ $service->id ] ) ) {
                                            $temp_value = isset( $cart_service[$service->id]['qty'] ) ? $cart_service[$service->id]['qty'] : 1;
                                        } else {
                                            $temp_value = isset( $_REQUEST[ $field_name ] ) ? $_REQUEST[ $field_name ] : 1;
                                        }
                                        ?>

                                        <div class="numbers-row post-right <?php if ( empty( $cart_service ) || empty( $cart_service[ $service->id ] ) ) echo 'hide-row';  ?>" data-min="0">
                                            <input type="text" class="qty2 form-control <?php echo $field_name ?>" name="<?php echo $field_name ?>" value="<?php echo $temp_value ?>">

                                            <div class="inc button_inc">+</div>
                                            <div class="dec button_inc">-</div>
                                        </div>
                                    </td>
                                    <td>
                                        <label class="switch-light switch-ios pull-right">
                                        <input type="checkbox" name="add_service[<?php echo esc_attr( $service->id ); ?>]" value="1"<?php if ( ! empty( $cart_service ) && ! empty( $cart_service[ $service->id ] ) ) echo ' checked="checked"' ?>>
                                        <span>
                                        <span><?php echo esc_html__( 'No', 'citytours' ) ?></span>
                                        <span><?php echo esc_html__( 'Yes', 'citytours' ) ?></span>
                                        </span>
                                        <a></a>
                                        </label>
                                    </td>
                                </tr>
                            <?php endforeach ?>
                        </tbody>
                    </table>
                <?php endif; ?>

                <?php do_action( 'tour_cart_main_after' ); ?>

            </div><!-- End col-md-8 -->

            <aside class="col-md-4">

                <?php do_action( 'tour_cart_sidebar_before' ); ?>

                <div class="box_style_1">
                    <h3 class="inner"><?php echo esc_html__( '- Summary -', 'citytours' ) ?></h3>

                    <table class="table table_summary">
                        <tbody>
                            <?php if ( ! empty( $date ) ) : ?>
                            <tr>
                                <td><?php echo esc_html__( 'Date', 'citytours' ) ?></td>
                                <td class="text-right"><?php echo date_i18n( 'j F Y', ct_strtotime( $date ) ); ?></td>
                            </tr>
                            <?php endif; ?>
                            <tr>
                                <td>عـدد البالغين</td>
                                <td class="text-right" data-title="total_adults"><?php echo esc_html($cart_data['total_adults']) ?></td>
                            </tr>
                            <tr class="total_kids">
                                <td>عـدد الاطفال</td>
                                <td class="text-right" data-title="total_kids"><?php echo esc_html($cart_data['total_kids']) ?></td>
                            </tr>
                            <?php if ( ! empty( $cart_service ) ) {
                                foreach ( $cart_service as $key => $service ) { ?>
                                    <tr class='services_summary'>
                                        <td><?php echo esc_html( $service['title'] ) ?></td>
                                        <td class="text-right"><?php echo ct_price( $service['total'] ); ?></td>
                                    </tr>
                            <?php }} ?>
                            <tr class="total">
                                <td><?php echo esc_html__( 'Total cost', 'citytours' ) ?></td>
                                <td class="text-right" data-title="total_price"><?php $total_price = $cart->get_field( $uid, 'total_price' ); if ( ! empty( $total_price ) ) echo ct_price( $total_price ) ?></td>
                            </tr>
                            <tr>
                                <td><?php echo sprintf( esc_html__( 'Security Deposit (%d%%)', 'citytours' ), $deposit_rate ) ?></td>
                                <td class="text-right"><?php if ( ! empty( $total_price ) ) echo ct_price( $total_price * $deposit_rate / 100 ) ?></td>
                            </tr>
                        </tbody>
                    </table>

                    <a class="btn_full book-now-btn" href="#"><?php echo esc_html__( 'Book now', 'citytours' ) ?></a>
                    <a class="btn_full_outline" href="<?php echo esc_url( get_permalink( $tour_id ) ) ?>"><i class="icon-right"></i> <?php echo esc_html__( 'Modify your search', 'citytours' ) ?></a>

                    <input type="hidden" name="action" value="ct_tour_book">
                    <input type="hidden" name="tour_id" value="<?php echo esc_attr( $tour_id ) ?>">
                    <input type="hidden" name="date" value="<?php echo esc_attr( $date ) ?>">
                    <?php wp_nonce_field( 'tour_update_cart' ); ?>
                </div>

                <?php do_action( 'tour_cart_sidebar_after' ); ?>
                <?php ct_get_template('contact_info.php', '/'); ?>
            </aside><!-- End aside -->

        </div><!--End row -->

    </form>

    <script>
        var ajaxurl = '<?php echo esc_js( admin_url( 'admin-ajax.php' ) ) ?>';
        var is_woocommerce_enabled = '<?php if ( ct_is_woocommerce_integration_enabled() ) echo "true"; else echo "false" ?>';

        jQuery(document).ready( function($){

            updateCart = function()
            {
                $('input[name="action"]').val('ct_tour_update_cart');
                $('#overlay').fadeIn();

                $.ajax({
                    url: ajaxurl,
                    type: "POST",
                    data: $('#tour-cart').serialize(),
                    success: function(response){
                        if (response.success == 1) {
                            var obj = JSON.parse(response.cart_data)
                            var total_adults = 0;
                            var total_kids = 0;
                            $.each(obj['tour']['adults'], function(key, value) {
                                if(value != '') {
//                                    console.log(key)
                                    total_adults += parseInt(value)
                                    var thisObj = $('tr[data-title="'+key+'"]');
                                    price = parseInt(thisObj.find('td[data-title="Price"]').text());
                                    total = parseFloat(price * value)
                                    thisObj.find('td[data-title="Total"]').html("<strong>" +total+"ج   . م <strong>");
                                }
                            })

                            $.each(obj['tour']['kids'], function(key, value) {
                                if(value != '') {
                                    total_kids += parseInt(value)
                                    var thisObj = $('tr[data-title="kids_'+key+'"]');
                                    price = parseInt(thisObj.find('td[data-title="Price"]').text());
                                    total = parseFloat(price * value)
                                    thisObj.find('td[data-title="Total"]').html("<strong>" +total+"&nbsp; ج . م <strong>");
                                }
                            })

                            $('td[data-title="total_adults"]').text(total_adults);
                            $('td[data-title="total_kids"]').text(total_kids);

							$('.services_summary').remove()

							if (typeof obj['add_service'] === 'object') {
								$.each(obj['add_service'], function (index, value) {
									$('.total_kids').closest( "tr" ).after("<tr class='services_summary'><td>"+value['title']+"</td><td class='text-right'>"+value['total']+"&nbsp; ج   . م </td></tr>");
								})
							}

							$('td[data-title="total_price"]').html("<strong>" +obj['total_price']+"&nbsp; ج. م<strong>");

						} else {
                            alert(response.message);
                        }

                        $('#overlay').fadeOut();
                    }
                });
            }

            $('table').delegate('.tour-adults', 'change', function () {
                updateCart()
            })

            $('.update-cart-btn').click(function(e){
                e.preventDefault();
                updateCart();

                return false;
            });

            $('.options_cart input').change(function(){
                updateCart()
            })

            $('.options_cart input[type="checkbox"]').change(function(){
                var qty_display = $(this).parent().parent().parent().find('.numbers-row');

                if ( qty_display.hasClass("hide-row") ) {
                    qty_display.removeClass("hide-row");
                } else {
                    qty_display.addClass("hide-row");
                }

                updateCart();
            });

            $('.book-now-btn').click(function(e){
                e.preventDefault();

                if ( is_woocommerce_enabled == "true" ) {
                    $('#overlay').fadeIn();
                    $('input[name="action"]').val('ct_add_tour_to_woo_cart');

                    $.ajax({
                        url: ajaxurl,
                        type: "POST",
                        data: $('#tour-cart').serialize(),
                        success: function(response){
                            $('#overlay').fadeOut();
                            if (response.success == 1) {
                                document.location.href=$("#tour-cart").attr('action');
                            } else {
                                alert(response.message);
                            }
                        }
                    });
                } else {
                    document.location.href=$("#tour-cart").attr('action');
                }
            })
        } );
    </script>

    <?php else : ?>
        <h5 class="alert alert-warning"><?php echo esc_html( $is_available ); ?></h5>
    <?php endif;
}
