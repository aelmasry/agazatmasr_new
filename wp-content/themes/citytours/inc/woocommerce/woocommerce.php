<?php 
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/*
 * Functions for WooCommerce Integration
 */

if ( is_admin() ) { 
    add_action( 'admin_init', 'ct_compatible_with_woocommerce' );
} else { 
    add_action( 'init', 'ct_compatible_with_woocommerce' );
}

/*
 * Check if Woocommerce Integration is enabled
 */

if ( ! function_exists( 'ct_is_woocommerce_integration_enabled' ) ) {
    function ct_is_woocommerce_integration_enabled() {
        global $ct_options;

        if ( ! empty( $ct_options['enable_woocommerce_integration'] ) && class_exists( 'WooCommerce' ) ) {
            return true;
        } else { 
            return false;
        }
    }
}

if ( class_exists( 'WooCommerce' ) ) {

    remove_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20 );
    remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_rating', 10 );
    remove_action( 'woocommerce_before_shop_loop_item', 'woocommerce_template_loop_product_link_open', 10 );
    remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_product_link_close', 5 );
    remove_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_template_loop_product_thumbnail', 10 );
    remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_rating', 5 );
    remove_action( 'woocommerce_shop_loop_item_title', 'woocommerce_template_loop_product_title', 10 );
    remove_action( 'woocommerce_widget_shopping_cart_buttons', 'woocommerce_widget_shopping_cart_button_view_cart', 10 );
    remove_action( 'woocommerce_widget_shopping_cart_buttons', 'woocommerce_widget_shopping_cart_proceed_to_checkout', 20 );

    add_action( 'init', 'ct_woocommerce_init' );
    add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_rating', 15 );
    add_action( 'woocommerce_before_shop_loop_item_title', 'ct_woocommerce_template_loop_product_thumbnail', 15 );
    add_action( 'woocommerce_shop_loop_item_title', 'woocommerce_template_loop_rating', 5 );
    add_action( 'woocommerce_shop_loop_item_title', 'ct_woocommerce_template_loop_product_title', 5 );
    add_action( 'woocommerce_after_shop_loop_item', 'ct_woocommerce_template_loop_links_open', 1 );
    add_action( 'woocommerce_after_shop_loop_item', 'ct_woocommerce_template_loop_quick_view', 99 );
    add_action( 'woocommerce_after_shop_loop_item', 'ct_woocommerce_template_loop_links_close', 100 );
    add_action( 'wp_enqueue_scripts', 'ct_custom_woocommerce_scripts', 90 );

    add_action( 'pre_get_posts','ct_shop_filter_certain_cat' );

    add_action( 'wp_ajax_ct_product_quickview', 'ct_product_quickview' );
    add_action( 'wp_ajax_nopriv_ct_product_quickview', 'ct_product_quickview' );

    add_action( 'wp_ajax_ct_ajax_mini_cart', 'ct_ajax_mini_cart' );
    add_action( 'wp_ajax_nopriv_ct_ajax_mini_cart', 'ct_ajax_mini_cart' );

    add_filter( 'ct_get_woocommerce_cart_url', 'ct_get_woocommerce_cart_url' );
    add_filter( 'ct_add_custom_content_class', 'ct_add_custom_content_class' );
    add_filter( 'rwmb_meta_boxes', 'ct_theme_register_product_meta_boxes', 15 );
    add_filter( 'woocommerce_enqueue_styles', 'ct_remove_woocommerce_styles', 99 );
    add_filter( 'comment_form_fields', 'ct_reset_fields_order' );
    add_filter( 'woocommerce_show_page_title', 'ct_woocommerce_show_page_title' );
    add_filter( 'loop_shop_per_page', 'ct_loop_shop_per_page', 20 );
    add_filter( 'woocommerce_form_field_args', 'ct_woocommerce_form_field_args', 10, 3 );
    add_filter( 'woocommerce_output_related_products_args', 'ct_woocommerce_output_related_products_args', 10 );
    add_filter( 'woocommerce_get_item_data', 'ct_woocommerce_extra_booking_info', 10, 2 );
}

// Add actions/filters to make Citytours compatible with WooCommerce
if ( ! function_exists( 'ct_compatible_with_woocommerce' ) ) {
    function ct_compatible_with_woocommerce() { 

        if ( ct_is_woocommerce_integration_enabled() ) { 

            add_action( 'plugins_loaded', 'ct_register_simple_hotel_tour_product_type' );
            add_action( 'admin_enqueue_scripts', 'ct_enqueue_custom_admin_scripts', 100 );
            add_action( 'woocommerce_product_data_panels', 'ct_hotel_options_product_tab_content' );
            add_action( 'product_type_options', 'ct_enable_product_type_options');

            /* ADD Order when customer checkout with WooCommerce */
            add_action( 'woocommerce_order_status_completed', 'ct_change_order_status_as_completed', 11, 1 );
            add_action( 'woocommerce_checkout_order_processed', 'ct_add_new_booking_order', 15 );
            add_action( 'woocommerce_checkout_order_processed', 'ct_clear_cart_info', 20 );

            add_filter( 'product_type_selector', 'ct_add_custom_product_type' );
            add_filter( 'woocommerce_product_data_tabs', 'ct_add_custom_product_tabs' );
            add_filter( 'woocommerce_product_data_tabs', 'ct_add_custom_css_to_panels' );

            add_filter( 'woocommerce_form_field_args', 'ct_add_class_woocommerce_form_field', 1, 3 );
            add_filter( 'ct_def_currency', 'ct_return_woocommerce_currency' );

        }
    }
}

if ( ! function_exists( 'ct_woocommerce_init' ) ) {
    function ct_woocommerce_init() {
        global $ct_options;

        if ( ! empty( $ct_options['enable_woocommerce_integration'] ) ) {
            // create necessary product category terms
            $product_cats = array(
                    'hotel' => __('Hotels', 'citytours'),
                    'tour' => __('Tours', 'citytours'),
                );
            foreach ( $product_cats as $slug => $name ) {
                if ( ! term_exists( $slug , 'product_cat' ) ) {
                    ct_woo_create_product_category( $slug, $name );
                }
            }

            add_filter( 'post_type_link', 'ct_woo_update_product_link', 10, 4  );
        }
        // add_filter( 'template_include', 'ct_woo_disable_template_access' );
        // add_filter( 'woocommerce_return_to_shop_redirect', 'ct_woo_return_to_shop_redirect', 10, 4  );
    }
}

/*
 * Create Woocommerce product category terms
 */
if ( ! function_exists( 'ct_woo_create_product_category' ) ) {
    function ct_woo_create_product_category( $term_slug, $term_name ) {
        wp_insert_term(
            $term_name,
            'product_cat', // the taxonomy
            array(
                'description'=> 'Custom Category for WooCommerce integration',
                'slug' => $term_slug,
            )
        );
    }
}

/*
 * Return WooCommerce Cart Page URL
 */
if ( ! function_exists( 'ct_get_woocommerce_cart_url' ) ) { 
    function ct_get_woocommerce_cart_url( $ct_default_cart_url ) { 
        if ( ct_is_woocommerce_integration_enabled() ) { 
            return wc_get_cart_url();
        } else { 
            return $ct_default_cart_url;
        }
    }
}

/*
 * Disable direct access to product page templates.
 */
if ( ! function_exists( 'ct_woo_disable_template_access' ) ) {
    function ct_woo_disable_template_access( $template ) {
        if ( ( is_single() && get_post_type() == 'product' ) // product detail page
                || is_tax( 'product_cat' ) // product category archive page
                || is_tax( 'product_tag' ) // product tag archive page
                || is_post_type_archive( 'product' ) // product post type archive page
                || ( function_exists( 'wc_get_page_id' ) && is_page( wc_get_page_id( 'shop' ) ) ) ) // shop page
        {
            return locate_template( '404.php' );
        }
        return $template;
    }
}

/*
 * Disable generated product link and set it property link
 */
if ( ! function_exists( 'ct_woo_update_product_link' ) ) {
    function ct_woo_update_product_link( $post_link, $post ) {
        if ( $post->post_type === 'product' ) {
            $ct_post_id = get_post_meta( $post->ID, '_ct_post_id', true );
            if ( ! empty( $ct_post_id ) ) {
                $post_link = get_permalink( $ct_post_id );
            }
        }
        return $post_link;
    }
}

/*
 * Redirect to shop
 */
if ( ! function_exists( 'ct_woo_return_to_shop_redirect' ) ) {
    function ct_woo_return_to_shop_redirect() {
        return esc_url( home_url() );
    }
}

/**
 * Register the Hotel product type after init
 */
if ( ! function_exists( 'ct_register_simple_hotel_tour_product_type' ) ) { 
    function ct_register_simple_hotel_tour_product_type() {

        class WC_Product_Hotel extends WC_Product {

            public function __construct( $product ) {
                $this->product_type = 'simple_hotel';
                parent::__construct( $product );
            }

        }

        class WC_Product_Tour extends WC_Product {

            public function __construct( $product ) {
                $this->product_type = 'simple_tour';
                parent::__construct( $product );
            }

        }

    }
}

/**
 * Add to product type drop down.
 */
if ( ! function_exists( 'ct_add_custom_product_type' ) ) { 
    function ct_add_custom_product_type( $types ){
        $types[ 'simple_hotel' ] = __( 'Hotel' );
        $types[ 'simple_tour' ] = __( 'Tour' );
        return $types;
    }
}

/**
 * Show tabs and fields for simple_hotel product type.
 */
if ( ! function_exists( 'ct_enqueue_custom_admin_scripts' ) ) { 
    function ct_enqueue_custom_admin_scripts() {

        if ( 'product' != get_post_type() ) :
            return;
        endif;

        wp_enqueue_style( 'ct_style_icon_set_1', CT_TEMPLATE_DIRECTORY_URI . '/css/fontello/css/icon_set_1.css' );
        wp_enqueue_style( 'ct_style_icon_set_2', CT_TEMPLATE_DIRECTORY_URI . '/css/fontello/css/icon_set_2.css' );
        wp_enqueue_style( 'ct_style_fontello', CT_TEMPLATE_DIRECTORY_URI . '/css/fontello/css/fontello.css' );
        wp_enqueue_style( 'ct_woo_hotel_tour_css', CT_TEMPLATE_DIRECTORY_URI . '/css/admin/admin_wc.css' );

        wp_enqueue_script( 'ct_woo_hotel_tour_js', CT_TEMPLATE_DIRECTORY_URI . '/js/admin/woocommerce.js', array(), false, true );
    }
}

/**
 * Add custom CSS classes to each data panel.
 */
if ( ! function_exists( 'ct_add_custom_css_to_panels' ) ) { 
    function ct_add_custom_css_to_panels( $tabs ) {

        $tabs['inventory']['class'][] = 'show_if_simple_hotel show_if_simple_tour';
        $tabs['attribute']['class'][] = 'hide_if_simple_hotel hide_if_simple_tour';
        $tabs['advanced']['class'][] = 'hide_if_simple_hotel hide_if_simple_tour';

        return $tabs;
    }
}

/**
 * Add a Hotel product tab.
 */
if ( ! function_exists( 'ct_add_custom_product_tabs' ) ) { 
    function ct_add_custom_product_tabs( $tabs ) {

        $tabs['hotel'] = array(
            'label'     => __( 'Hotel', 'citytours' ),
            'target'    => 'hotel_options',
            'class'     => array( 'show_if_simple_hotel' ),
        );

        $tabs['tour'] = array(
            'label'     => __( 'Tour', 'citytours' ),
            'target'    => 'tour_options',
            'class'     => array( 'show_if_simple_tour' ),
        );

        return $tabs;
    }
}

/**
 * Contents of the Hotel options product tab.
 */
if ( ! function_exists( 'ct_hotel_options_product_tab_content' ) ) { 
    function ct_hotel_options_product_tab_content() {

        global $post;
        $hotel_tour_id = get_post_meta( $post->ID, '_ct_post_id', true );

        ?>
        <div id='hotel_options' class='panel woocommerce_options_panel'>
            <?php if ( get_post_type( $hotel_tour_id ) == 'hotel' ) : ?>
            <div class='options_group'>
                <?php
                    $date_from = get_post_meta( $post->ID, '_ct_booking_date_from', true );
                    $date_to = get_post_meta( $post->ID, '_ct_booking_date_to', true );

                    $booking_info = get_post_meta( $post->ID, '_ct_booking_info' );
                    $add_service = get_post_meta( $post->ID, '_ct_add_service' );
                ?>
                <div class="booking-date-container">
                    <div class="booking-date">
                        <label for="booking_date_from"><?php _e('Check In : ', 'citytours') ?></label>
                        <input type="text" id="booking_date_from" value="<?php echo $date_from ?>">
                    </div>
                    <div class="booking-date">
                        <label for="booking_date_to"><?php _e('Check Out : ', 'citytours') ?></label>
                        <input type="text" id="booking_date_to" value="<?php echo $date_to ?>">
                    </div>
                </div>
                <table class="rooms-container">
                    <thead><tr>
                        <th><?php _e('Room Type', 'citytours') ?></th>
                        <th><?php _e('Quantity', 'citytours') ?></th>
                        <th><?php _e('Adults', 'citytours') ?></th>
                        <th><?php _e('Children', 'citytours') ?></th>
                        <th><?php _e('Total', 'citytours') ?></th>
                    </tr></thead>
                    <tbody>
                        <?php
                        foreach ( $booking_info[0] as $room_info ) { ?>
                        <tr>
                            <td>
                                <div class="thumb_cart">
                                    <a href="<?php echo get_edit_post_link( $room_info['room_id'] ) ?>" target="_blank"><?php echo get_the_post_thumbnail( $room_info['room_id'], 'thumbnail' ); ?></a>
                                </div>
                                 <span class="item_cart"><a href="<?php echo get_edit_post_link( $room_info['room_id'] ) ?>" target="_blank"><?php echo esc_html( get_the_title( $room_info['room_id'] ) ); ?></a></span>
                            </td>
                            <td>
                                <input type="number" name="room_qty" value="<?php echo $room_info['room_qty'] ?>" disabled>
                            </td>
                            <td>
                                <input type="number" name="room_adults" value="<?php echo $room_info['adults'] ?>" disabled>
                            </td>
                            <td>
                                <input type="number" name="room_kids" value="<?php echo $room_info['kids'] ?>" disabled>
                            </td>
                            <td>
                                <input type="text" name="room_total" value="<?php echo ct_price($room_info['total']) ?>" disabled>
                            </td>
                        </tr>
                        <?php }
                        ?>
                    </tbody>
                </table>
                <table class="service-container">
                    <thead><tr>
                        <th colspan="4"><?php _e('Added Services', 'citytours') ?></th>
                    </tr></thead>
                    <tbody>
                        <?php 
                        foreach ( $add_service[0] as $service_info ) {
                            $org_service_info = ct_get_add_services_by_postid( 0, $service_info['service_id'] );
                        ?>
                        <tr> 
                            <td>
                                <i class="<?php echo esc_attr( $org_service_info[0]->icon_class ); ?>"></i>
                            </td>
                            <td>
                                <?php echo esc_attr( $service_info['title'] ); ?> 
                                <strong>+<?php echo ct_price( $service_info['price'] ); ?></strong>
                            </td>
                            <td>
                                <label for="service_qty">Quantity : </label>
                                <input type="number" name="service_qty" id="service_qty" value="<?php echo $service_info['qty'] ?>" disabled>
                            </td>
                            <td>
                                <label for="service_total">Total : </label>
                                <input type="text" name="service_total" id="service_total" value="<?php echo ct_price( $service_info['price'] * $service_info['qty'] ) ?>" disabled>
                            </td>
                        </tr>
                        <?php }
                        ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>

        <div id='tour_options' class='panel woocommerce_options_panel'>
            <?php if ( get_post_type( $hotel_tour_id ) == 'tour' ) : ?>
            <div class='options_group'>
                <?php
                    $tour_date = get_post_meta( $post->ID, '_ct_booking_date', true );

                    $booking_info = get_post_meta( $post->ID, '_ct_booking_info' );
                    $add_service = get_post_meta( $post->ID, '_ct_add_service' );
                ?>
                <div class="booking-date-container">
                    <div class="booking-date">
                        <label for="booking_date"><?php _e('Book Date : ', 'citytours') ?></label>
                        <input type="text" id="booking_date" value="<?php echo $tour_date ?>">
                    </div>
                </div>
                <table class="tour-container">
                    <thead><tr>
                        <th><?php _e('Item', 'citytours') ?></th>
                        <th><?php _e('Adults', 'citytours') ?></th>
                        <th><?php _e('Children', 'citytours') ?></th>
                        <th><?php _e('Total', 'citytours') ?></th>
                    </tr></thead>
                    <tbody>
                        <tr>
                            <td>
                                <div class="thumb_cart">
                                    <a href="<?php echo get_edit_post_link( $booking_info[0]['tour_id'] ) ?>" target="_blank"><?php echo get_the_post_thumbnail( $booking_info[0]['tour_id'], 'thumbnail' ); ?></a>
                                </div>
                                 <span class="item_cart"><a href="<?php echo get_edit_post_link( $booking_info[0]['tour_id'] ) ?>" target="_blank"><?php echo esc_html( get_the_title( $booking_info[0]['tour_id'] ) ); ?></a></span>
                            </td>
                            <td>
                                <input type="number" name="tour_adults" value="<?php echo $booking_info[0]['adults'] ?>" disabled>
                            </td>
                            <td>
                                <input type="number" name="tour_kids" value="<?php echo $booking_info[0]['kids'] ?>" disabled>
                            </td>
                            <td>
                                <input type="text" name="tour_total" value="<?php echo ct_price($booking_info[0]['total']) ?>" disabled>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <table class="service-container">
                    <thead><tr>
                        <th colspan="4"><?php _e('Added Services', 'citytours') ?></th>
                    </tr></thead>
                    <tbody>
                        <?php 
                        foreach ( $add_service[0] as $service_info ) {
                            $org_service_info = ct_get_add_services_by_postid( 0, $service_info['service_id'] );
                        ?>
                        <tr> 
                            <td>
                                <i class="<?php echo esc_attr( $org_service_info[0]->icon_class ); ?>"></i>
                            </td>
                            <td>
                                <?php echo esc_attr( $service_info['title'] ); ?> 
                                <strong>+<?php echo ct_price( $service_info['price'] ); ?></strong>
                            </td>
                            <td>
                                <label for="service_qty">Quantity : </label>
                                <input type="number" name="service_qty" id="service_qty" value="<?php echo $service_info['qty'] ?>" disabled>
                            </td>
                            <td>
                                <label for="service_total">Total : </label>
                                <input type="text" name="service_total" id="service_total" value="<?php echo ct_price( $service_info['price'] * $service_info['qty'] ) ?>" disabled>
                            </td>
                        </tr>
                        <?php }
                        ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
        <?php
    }
}

/*
 * Enable original product type options for Hotel product type
 */
if ( ! function_exists( 'ct_enable_product_type_options' ) ) { 
    function ct_enable_product_type_options( $options ) { 
        $options['virtual']['wrapper_class'] = 'show_if_simple show_if_simple_hotel show_if_simple_tour';
        return $options;
    }
}

/*
 * Add custom class 'container' into Cart and Checkout page container.
 */
if ( ! function_exists( 'ct_add_custom_content_class' ) ) { 
    function ct_add_custom_content_class( $content_class ) { 
        if ( is_cart() || is_checkout() ) { 
            $content_class .= ' container';
        }

        return $content_class;
    }
}


/**
 * Add custom CSS class into WooCommerce form fields
 */
if ( ! function_exists( 'ct_add_class_woocommerce_form_field' ) ) { 
    function ct_add_class_woocommerce_form_field( $args, $key, $value ) { 
        if ( $args['type'] == 'radio' ) { 
            $args['input_class'][] = 'form-radio-control';
        } else { 
            $args['input_class'][] = 'form-control';
        }
        return $args;
    }
}

/**
 * Change Hotel/Tour order status to "Confirmed" after WooCommerce Order status is changed as "Completed"
 */
if ( ! function_exists( 'ct_change_order_status_as_completed' ) ) { 
    function ct_change_order_status_as_completed( $order_id ) { 
        global $wpdb, $ct_options;

        $order = new WC_Order( $order_id );
        $items = $order->get_items();

        foreach ( $items as $item ) {
            $product_id = $item['product_id'];
            $hotel_tour_id = get_post_meta( $product_id, '_ct_post_id', true );

            if ( $hotel_tour_id ) { 
                // $result = $wpdb->get_var( 'SELECT id FROM ' . CT_ORDER_TABLE . ' WHERE other = ' . $order_id, ARRAY_A );
                $wpdb->update( CT_ORDER_TABLE, array( 'status' => 'confirmed' ), array( 'other' => $order_id ), array( '%s' ), array( '%d' ) );
            }
        }
    }
}

/*
 * Add Hotel/Tour Booking Order when complete payment
 */
if ( ! function_exists( 'ct_add_new_booking_order' ) ) { 
    function ct_add_new_booking_order( $order_id ) { 
        global $wpdb, $ct_options;

        $order = new WC_Order( $order_id );
        $items = $order->get_items();
        $customer_id = $order->get_user_id(); 

        $order_info = array();
        $order_info['deposit_paid'] = 1;
        $order_info['first_name'] = $order->get_billing_first_name();
        $order_info['last_name'] = $order->get_billing_last_name();
        $order_info['email'] = $order->get_billing_email();
        $order_info['phone'] = $order->get_billing_phone();
        $order_info['country'] = $order->get_billing_country();
        $order_info['address1'] = $order->get_billing_address_1();
        if ( $order->get_billing_address_2() ) { 
            $order_info['address2'] = $order->get_billing_address_2();
        } else { 
            $order_info['address2'] = '';
        }
        $order_info['city'] = $order->get_billing_city();
        $order_info['state'] = $order->get_billing_state();
        $order_info['zip'] = $order->get_billing_postcode();

        $order_info['other'] = $order_id;

        foreach ( $items as $item ) {
            $flag_custom = false;
            $total_price = 0;
            $total_adults = 0;
            $total_kids = 0;
            
            $product_id = $item['product_id'];
            $hotel_tour_id = get_post_meta( $product_id, '_ct_post_id', true );
            
            $total = get_post_meta( $product_id, '_ct_total_price', true );
            if ( $total && !empty($total) ) { 
                $total_price += $total;
            }

            $booking_info = get_post_meta( $product_id, '_ct_booking_info' );

            if ( $hotel_tour_id ) { 
                if ( get_post_type( $hotel_tour_id ) == 'tour' ) { 
                    $flag_custom = true;

                    $deposit_rate = get_post_meta( $hotel_tour_id, '_tour_security_deposit', true );
                    $booking_date = get_post_meta( $product_id, '_ct_booking_date', true );
                    $order_info['date_from'] = date( 'Y-m-d', ct_strtotime( $booking_date ) );

                    $total_adults += $booking_info[0]['adults'];
                    $total_kids += $booking_info[0]['kids'];
                } else if ( get_post_type( $hotel_tour_id ) == 'hotel' ) { 
                    $flag_custom = true;

                    $deposit_rate = get_post_meta( $hotel_tour_id, '_hotel_security_deposit', true );
                    $booking_date_from = get_post_meta( $product_id, '_ct_booking_date_from', true );
                    $booking_date_to = get_post_meta( $product_id, '_ct_booking_date_to', true );

                    $order_info['date_from'] = date( 'Y-m-d', ct_strtotime( $booking_date_from ) );
                    $order_info['date_to'] = date( 'Y-m-d', ct_strtotime( $booking_date_to ) );

                    foreach ( $booking_info[0] as $room_info ) {
                        $total_adults += $room_info['adults'];
                        $total_kids += $room_info['kids'];
                    }
                }
            }

            $result = $wpdb->get_var( 'SELECT id FROM ' . CT_ORDER_TABLE . ' WHERE other = ' . $order_id );

            if ( $flag_custom && empty( $result ) ) { 

                $order_info['total_price'] = $total_price;
                $order_info['total_adults'] = $total_adults;
                $order_info['total_kids'] = $total_kids;
                $order_info['status'] = 'new';
                $order_info['deposit_paid'] = 1;

                $decimal_prec = isset( $ct_options['decimal_prec'] ) ? $ct_options['decimal_prec'] : 2;
                $order_info['deposit_price'] = round( $deposit_rate / 100 * $total_price, $decimal_prec );

                $order_info['mail_sent'] = 1;
                $order_info['post_id'] = $hotel_tour_id;

                // Insert Booking Order into CT_ORDER_TABLE
                $latest_order_id = $wpdb->get_var( 'SELECT id FROM ' . CT_ORDER_TABLE . ' ORDER BY id DESC LIMIT 1' );
                $booking_no = mt_rand( 1000, 9999 );
                $booking_no .= $latest_order_id;
                $pin_code = mt_rand( 1000, 9999 );

                $order_info['booking_no'] = $booking_no;
                $order_info['pin_code'] = $pin_code;
                $order_info['currency_code'] = get_woocommerce_currency();
                $order_info['exchange_rate'] = 1;
                $order_info['created'] = date( 'Y-m-d H:i:s' );
                $order_info['post_type'] = get_post_type( $hotel_tour_id );

                if ( $wpdb->insert( CT_ORDER_TABLE, $order_info ) ) {

                    $ct_order_id = $wpdb->insert_id;

                    if ( ! empty( $booking_info[0] ) ) {
                        if ( get_post_type( $hotel_tour_id ) == 'tour' ) { 
                            $tour_booking_info = array();
                            $tour_booking_info['order_id'] = $ct_order_id;
                            $tour_booking_info['tour_id'] = $hotel_tour_id;
                            $tour_booking_info['tour_date'] = $booking_date;
                            $tour_booking_info['adults'] = $booking_info[0]['adults'];
                            $tour_booking_info['kids'] = $booking_info[0]['kids'];
                            $tour_booking_info['total_price'] = $booking_info[0]['total'];

                            $wpdb->insert( CT_TOUR_BOOKINGS_TABLE, $tour_booking_info );
                        } else { 
                            foreach ( $booking_info[0] as $$room_info ) {
                                $room_booking_info = array();
                                $room_booking_info['order_id'] = $ct_order_id;
                                $room_booking_info['hotel_id'] = $hotel_tour_id;
                                $room_booking_info['room_type_id'] = $room_info['room_id'];
                                $room_booking_info['rooms'] = $room_info['room_qty'];
                                $room_booking_info['adults'] = $room_info['adults'];
                                $room_booking_info['kids'] = $room_info['kids'];
                                $room_booking_info['total_price'] = $room_info['total'];

                                $wpdb->insert( CT_HOTEL_BOOKINGS_TABLE, $room_booking_info );
                            }
                        }
                    }

                    $add_services = get_post_meta( $product_id, '_ct_add_service' );
                    if ( ! empty( $add_services[0] ) ) {
                        foreach ( $add_services[0] as $service_data ) {
                            $service_booking_info = array();
                            $service_booking_info['order_id'] = $ct_order_id;
                            $service_booking_info['add_service_id'] = $service_data['service_id'];
                            $service_booking_info['qty'] = $service_data['qty'];
                            $service_booking_info['total_price'] = $service_data['price'] * $service_data['qty'];

                            $wpdb->insert( CT_ADD_SERVICES_BOOKINGS_TABLE, $service_booking_info );
                        }
                    }
                }

            }
        }

        // Get Order Payment Method
        // $payment_method = get_post_meta( $order_id, '_payment_method', true );
    }
}

/*
 *
 */
if ( ! function_exists( 'ct_clear_cart_info' ) ) { 
    function ct_clear_cart_info( $order_id ) { 
        $order = new WC_Order( $order_id );
        $items = $order->get_items();

        foreach ( $items as $item ) {
            $post_id = get_post_meta( $item['product_id'], '_ct_post_id', true );
            $uid = '';

            if ( $post_id && !empty( $post_id ) ) { 
                if ( get_post_type($post_id) == 'tour' ) { 
                    $booking_date = get_post_meta( $item['product_id'], '_ct_booking_date', true );
                    $uid = $post_id . $booking_date;
                } else { 
                    $booking_date_from = get_post_meta( $item['product_id'], '_ct_booking_date_from', true );
                    $booking_date_to = get_post_meta( $item['product_id'], '_ct_booking_date_to', true );
                    $uid = $post_id . $booking_date_from . $booking_date_to;
                }

                if ( $cart_data = CT_Hotel_Cart::get( $uid ) ) {
                    CT_Hotel_Cart::_unset( $uid );
                }
            }
        }
    }
}

/*
 * Return WooCommerce Currency 
 */
if ( ! function_exists( 'ct_return_woocommerce_currency' ) ) { 
    function ct_return_woocommerce_currency( $currency ) { 
        return get_woocommerce_currency();
    }
}

/* Adding meta field for product */
if ( ! function_exists( 'ct_product_register_meta_boxes' ) ) { 
    function ct_product_register_meta_boxes() { 
        $meta_boxes = array();

        $meta_boxes[] = array(
            'id'        => 'product_header_image_setting',
            'title'     => esc_html__( 'Header Image Setting', 'citytours' ),
            'pages'     => array( 'product' ),
            'context'   => 'normal',
            'priority'  => 'high',
            'fields'    => array(
                array(
                    'name'      => __( 'Show Header Image' ),
                    'id'        => '_show_header_image',
                    'type'      => 'select',
                    'options'   => array( 
                        'show'  => 'Show',
                        'hide'  => 'Hide',
                    ),
                    'desc'      => esc_html__( 'If select "Hide", Header image won\'t be removed.', 'citytours' ),
                    'std'       => 'show'
                ),
                array(
                    'name'  => esc_html__( 'Header Image', 'citytours' ),
                    'id'    => '_header_image',
                    'type'  => 'image_advanced',
                    'desc'  => wp_kses_post( sprintf( __( 'If leave this field empty, default image in <a href="%s" target="_blank">Theme Options panel</a> will work.', 'citytours' ), admin_url( 'admin.php?page=theme_options' ) ) ) ,
                    'max_file_uploads' => 1,
                ),
                array(
                    'name'  => esc_html__( 'Height (px)', 'citytours' ),
                    'id'    => '_header_image_height',
                    'desc'  => wp_kses_post( sprintf( __( 'If leave this field empty, default image height in <a href="%s" target="_blank">Theme Options panel</a> will work.', 'citytours' ), admin_url( 'admin.php?page=theme_options' ) ) ) ,
                    'type'  => 'text',
                ),
                array(
                    'name'  => esc_html__( 'Header Content', 'citytours' ),
                    'id'    => '_header_content',
                    'type'  => 'wysiwyg',
                    'raw'   => true,
                    'options' => array(
                        'textarea_rows' => 4,
                    ),
                ),
            )
        );

        $meta_boxes[] = array(
            'id'        => 'custom_css',
            'title'     => esc_html__( 'Custom CSS', 'citytours' ),
            'pages'     => array( 'product' ),
            'context'   => 'normal',
            'priority'  => 'default',
            'fields'    => array(
                array(
                    'name'  => esc_html__( 'Custom CSS', 'citytours' ),
                    'id'    => "_custom_css",
                    'desc'  => esc_html__( 'Enter custom css code here.', 'citytours' ),
                    'type'  => 'textarea',
                )
            )
        );

        global $wp_registered_sidebars;

        foreach($wp_registered_sidebars as $sidebar) {
            $sidebars[$sidebar['id']] = $sidebar['name'];
        }
        $sidebars['default'] = esc_html__('Default', 'citytours');

        $meta_boxes[] = array(
            'id'        => 'ct-metabox-page-sidebar',
            'title'     => esc_html__( 'Page layout', 'citytours' ),
            'pages'     => array( 'product' ),
            'context'   => 'side',
            'priority'  => 'default',
            'fields'    => array(
                // Sidebar option
                array(
                    'name'  => __( 'Sidebar position:', 'citytours' ),
                    'id'    => '_ct_sidebar_position',
                    'type'  => 'radio',
                    'std'   => 'default',
                    'desc'  => esc_html__( 'If select "Default", the setting on Theme-Options panel will work.', 'citytours' ),
                    'options' => array(
                        'default'   => __( 'Default', 'citytours' ),
                        'no'        => __( 'No Sidebar', 'citytours' ),
                        'left'      => __( 'Left', 'citytours' ),
                        'right'     => __( 'Right', 'citytours' ),
                    )
                ),

                // Sidebar widget area
                array(
                    'name'      => esc_html__( 'Select Sidebar:', 'citytours' ),
                    'id'        => '_ct_sidebar_widget_area',
                    'type'      => 'select',
                    'options'   => $sidebars,
                    'std'       => 'default'
                ),
            ),
        );

        return $meta_boxes;
    }
}

/* Add Custom Meta Fields for Product */
if ( ! function_exists( 'ct_theme_register_product_meta_boxes' ) ) { 
    function ct_theme_register_product_meta_boxes( $meta_boxes ) { 
        $meta_boxes = array_merge( $meta_boxes, ct_product_register_meta_boxes() );
        return $meta_boxes;
    }
}

/* Remove default WooCommerce Styles */
if ( ! function_exists( 'ct_remove_woocommerce_styles' ) ) { 
    function ct_remove_woocommerce_styles( $styles ) { 
        return array();
    }
}

/* Change Comment Form Fields order */
if ( ! function_exists( 'ct_reset_fields_order' ) ) { 
    function ct_reset_fields_order( $comment_fields ) { 
        $comment_field = $comment_fields['comment'];
        unset( $comment_fields['comment'] );
        $comment_fields += array( 'comment' => $comment_field );

        return $comment_fields;
    }
}

/* Return product thumbnails for loop product */
if ( ! function_exists( 'ct_woocommerce_template_loop_product_thumbnail' ) ) { 
    function ct_woocommerce_template_loop_product_thumbnail() { 
        global $post;
        
        $id = get_the_ID();
        $size = 'shop_catalog';
        $image_size = apply_filters( 'single_product_archive_thumbnail_size', $size );

        if ( has_post_thumbnail() ) {
            $props = wc_get_product_attachment_props( get_post_thumbnail_id(), $post );
            $thumb_image = get_the_post_thumbnail( $id, $image_size, array(
                'title'  => $props['title'],
                'alt'    => $props['alt'],
            ) );
        } elseif ( wc_placeholder_img_src() ) {
            $thumb_image = wc_placeholder_img( $image_size );
        }

        echo '<figure class="image"><a href="' . get_the_permalink() . '">';
        echo $thumb_image;
        echo '</a></figure>';
    }
}

/* Open containers for Add-to-cart, Wishlist, QuickView buttons */ 
if ( ! function_exists( 'ct_woocommerce_template_loop_links_open' ) ) { 
    function ct_woocommerce_template_loop_links_open() { 
        echo '<div class="item-options clearfix">';
    }
}

/* Close containers for Add-to-cart, Wishlist, QuickView buttons */
if ( ! function_exists( 'ct_woocommerce_template_loop_links_close' ) ) { 
    function ct_woocommerce_template_loop_links_close() { 
        echo '</div>';
    }
}

/* Show Quick View button on Loop product */
if ( ! function_exists( 'ct_woocommerce_template_loop_quick_view' ) ) { 
    function ct_woocommerce_template_loop_quick_view() { 
        global $product;
        ?>

        <a href="<?php echo get_permalink( $product->get_id() ) ?>" class="quickview btn_shop" data-id="<?php echo $product->get_id() ?>">
            <span class="icon-eye"></span>
            <div class="tool-tip"><?php echo __( 'View', 'citytours' ) ?></div>
        </a>
        
        <?php
    }
}

/* Convert Array to Json */
if ( ! function_exists( 'ct_convert_array_to_json' ) ) { 
    function ct_convert_array_to_json($arr) {
        if(function_exists('json_encode')) return json_encode($arr); //Lastest versions of PHP already has this functionality.
        $parts = array();
        $is_list = false;

        //Find out if the given array is a numerical array
        $keys = array_keys($arr);
        $max_length = count($arr)-1;
        if(($keys[0] == 0) and ($keys[$max_length] == $max_length)) {//See if the first key is 0 and last key is length - 1
            $is_list = true;
            for($i=0; $i<count($keys); $i++) { //See if each key correspondes to its position
                if($i != $keys[$i]) { //A key fails at position check.
                    $is_list = false; //It is an associative array.
                    break;
                }
            }
        }

        foreach($arr as $key=>$value) {
            if(is_array($value)) { //Custom handling for arrays
                if($is_list) $parts[] = ct_convert_array_to_json($value); /* :RECURSION: */
                else $parts[] = '"' . $key . '":' . ct_convert_array_to_json($value); /* :RECURSION: */
            } else {
                $str = '';
                if(!$is_list) $str = '"' . $key . '":';

                //Custom handling for multiple data types
                if(is_numeric($value)) $str .= $value; //Numbers
                elseif($value === false) $str .= 'false'; //The booleans
                elseif($value === true) $str .= 'true';
                else $str .= '"' . addslashes($value) . '"'; //All other things

                $parts[] = $str;
            }
        }
        $json = implode(',',$parts);

        if($is_list) return '[' . $json . ']';//Return numerical JSON
        return '{' . $json . '}';//Return associative JSON
    }
}

/* Return product layout for QuickView */
if ( ! function_exists( 'ct_product_quickview' ) ) { 
    function ct_product_quickview() { 

        global $post, $product;
        $post = get_post( $_POST['pid'] );
        $product = wc_get_product( $post->ID );

        if ( post_password_required() ) {
            echo get_the_password_form();
            wp_send_json(array( 'success' => 0 ));
        }

        ob_start();
        ?>

        <div class="quickview-wrap quickview-wrap-<?php echo $post->ID ?> single-product product-details">
            <div class="basic-details">

                <div class="row">
                    <div class="image-column col-sm-6 col-xs-12 summary-before">
                        <?php
                        do_action( 'woocommerce_before_single_product_summary' );
                        ?>
                    </div>

                    <div class="info-column col-sm-6 col-xs-12 summary entry-summary">
                        <?php
                        do_action( 'woocommerce_single_product_summary' );
                        ?>
                        <script type="text/javascript">
                            <?php
                            $suffix               = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
                            $assets_path          = esc_url(str_replace( array( 'http:', 'https:' ), '', WC()->plugin_url() )) . '/assets/';
                            $frontend_script_path = $assets_path . 'js/frontend/';
                            ?>
                            var wc_add_to_cart_variation_params = <?php echo ct_convert_array_to_json(apply_filters( 'wc_add_to_cart_variation_params', array(
                                'i18n_no_matching_variations_text' => esc_attr__( 'Sorry, no products matched your selection. Please choose a different combination.', 'woocommerce' ),
                        'i18n_make_a_selection_text'       => esc_attr__( 'Select product options before adding this product to your cart.', 'woocommerce' ),
                        'i18n_unavailable_text'            => esc_attr__( 'Sorry, this product is unavailable. Please choose a different combination.', 'woocommerce' ),
                            ) )) ?>;
                            jQuery(document).ready(function($) {
                                $.getScript('<?php echo $frontend_script_path . 'add-to-cart-variation' . $suffix . '.js' ?>');
                            });
                        </script>
                    </div><!-- .summary -->
                </div>
            </div>
        </div>

        <?php
        $output = ob_get_contents();
        ob_end_clean();

        wp_send_json(array( 'success' => 1, 'output' => $output ));
    }
}

/* Return custom title for loop product */
if ( ! function_exists( 'ct_woocommerce_template_loop_product_title' ) ) { 
    function ct_woocommerce_template_loop_product_title() { 
        echo '<h3><a href="' . get_the_permalink() . '">' . get_the_title() . '</a></h3>';
    }
}

/* Disable shop page title */ 
if ( ! function_exists( 'ct_woocommerce_show_page_title' ) ) { 
    function ct_woocommerce_show_page_title( $args ) { 
        return false;
    }
}

/* Return custom products count for Shop/Archive page */
if ( ! function_exists( 'ct_loop_shop_per_page' ) ) { 
    function ct_loop_shop_per_page( $cols ) { 
        global $ct_options;

        return $ct_options['shop_product_count'];
    }
}

/* Add custom classes into billing form fields */
if ( ! function_exists( 'ct_woocommerce_form_field_args' ) ) { 
    function ct_woocommerce_form_field_args( $args, $key, $value ) { 
        $args['class'][] = 'form-group';

        switch ( $key ) {
            case 'billing_first_name':
            case 'billing_last_name':
            case 'billing_email':
            case 'billing_phone':
            case 'billing_city':
            case 'billing_state':
            case 'shipping_first_name':
            case 'shipping_last_name':
            case 'shipping_city':
            case 'shipping_state':
                $args['class'][] = 'col-sm-6';
                break;

            case 'account_password':
                break;

            default:
                $args['class'][] = 'col-sm-12';
                break;
        }

        $args['input_class'][] = 'form-control';

        return $args;
    }
}

/* Remove Select2 js and css from WooCommerce */
if ( ! function_exists( 'ct_custom_woocommerce_scripts' ) ) { 
    function ct_custom_woocommerce_scripts() { 
        wp_dequeue_script( 'select2' );
        wp_dequeue_style( 'select2' );
    }
}

/* Filter shop page query not to show beta products */
if ( ! function_exists( 'ct_shop_filter_certain_cat' ) ) { 
    function ct_shop_filter_certain_cat( $query ) {
        if ( !is_admin() && is_post_type_archive( 'product' ) && $query->is_main_query() ) {
           $query->set('tax_query', array(
                array(
                    'taxonomy'  => 'product_cat',
                    'field'     => 'slug',
                    'terms'     => array( 'hotel', 'tour' ),
                    'operator'  => 'NOT IN'
                    )
                )
           );   
        }
    }
}


/* Return mini-cart contents */
if ( ! function_exists( 'ct_ajax_mini_cart' ) ) { 
    function ct_ajax_mini_cart() { 

        if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'ajax_mini_cart' )  ) { 
            wp_send_json( array( 'success' => false ) );
            die();
        }

        // Get mini cart
        ob_start();

        woocommerce_mini_cart( array( 'type' => 'header-mini-cart' ) );

        $mini_cart = ob_get_clean();

        // Fragments and mini cart are returned
        $data = array(
            'success'       => true, 
            'mini_cart'     => apply_filters( 'woocommerce_add_to_cart_fragments', array( $mini_cart ) ),
            'cart_qty'      => WC()->cart->cart_contents_count
        );

        wp_send_json( $data );
    }
}

if ( ! function_exists( 'ct_woocommerce_output_related_products_args' ) ) { 
    function ct_woocommerce_output_related_products_args( $args ) { 
        global $ct_options;

        $args['posts_per_page'] = $ct_options['product_related_count'];
        $args['columns'] = $ct_options['product_related_columns'];

        return $args;
    }
}

if ( ! function_exists( 'ct_woocommerce_extra_booking_info' ) ) { 
    function ct_woocommerce_extra_booking_info( $item_data, $cart_item ) { 
        $product_id         = $cart_item['product_id'];
        $is_custom_product  = false;
        $post_type          = false;

        $hotel_tour_id      = get_post_meta( $product_id, '_ct_post_id', true );
        if ( ! empty( $hotel_tour_id ) ) { 
            $is_custom_product  = true;
            $post_type          = get_post_type( $hotel_tour_id );
        }

        if ( $is_custom_product && $post_type ) { 
            if ( 'hotel' == $post_type ) { 
                $booking_details = get_post_meta( $product_id, '_ct_booking_info', true );
                foreach ( $booking_details as $room_detail ) {
                    $room_title = get_the_title( $room_detail['room_id'] );

                    $item_data[] = array(
                        'name'  => __('Room', 'citytours'),
                        'value' => $room_title
                    );

                    $item_data[] = array( 
                        'name'  => __('# of Rooms', 'citytours'),
                        'value' => $room_detail['room_qty']
                    );

                    $item_data[] = array( 
                        'name'  => __('Adults', 'citytours'),
                        'value' => $room_detail['adults']
                    );

                    $item_data[] = array( 
                        'name'  => __('Kids', 'citytours'),
                        'value' => $room_detail['kids']
                    );
                }

                $date_from = get_post_meta( $product_id, '_ct_booking_date_from', true );
                $item_data[] = array( 
                    'name'  => __('Check In', 'citytours'),
                    'value' => $date_from
                );

                $date_to = get_post_meta( $product_id, '_ct_booking_date_to', true );
                $item_data[] = array( 
                    'name'  => __('Check Out', 'citytours'),
                    'value' => $date_to
                );

                $additional_services = get_post_meta( $product_id, '_ct_add_service', true );
                // if ( ! empty( $additional_services ) ) { 
                //     $order_info .= '<span style="margin-top: 6px">' . __('Additional Services', 'citytours') . '</span>';

                //     foreach ( $additional_services as $each_service ) {
                //         $order_info .= '<span style="margin-left: 10px">' . __('Service', 'citytours') . ' : ' . $each_service['title'] . '</span>';
                //         $order_info .= '<span style="margin-left: 10px">' . __('Price', 'citytours') . ' : ' . ct_price( $each_service['price'] ) . '</span>';
                //         $order_info .= '<span style="margin-left: 10px">' . __('Qty', 'citytours') . ' : ' . $each_service['qty'] . '</span>';
                //     }
                // }
            }

            if ( 'tour' == $post_type ) { 
                $booking_date = get_post_meta( $product_id, '_ct_booking_date', true );

                $item_data[] = array( 
                    'name'  => __('Date', 'citytours'),
                    'value' => $booking_date
                );

                $booking_details = get_post_meta( $product_id, '_ct_booking_info', true );

                $item_data[] = array( 
                    'name'  => __('Adults', 'citytours'),
                    'value' => $booking_details['adults']
                );

                $item_data[] = array( 
                    'name'  => __('Kids', 'citytours'),
                    'value' => $booking_details['kids']
                );
            }
        }

        return $item_data;
    }
}