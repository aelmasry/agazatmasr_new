<?php
/**
 * Mini-cart
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @author  WooThemes
 * @package WooCommerce/Templates
 * @version 3.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

$header_mini_cart = false;
$cart_btn_class = $checkout_btn_class = 'btn_1';
$cart_btn_label = __( 'View Cart', 'woocommerce' );

if ( isset( $args['type'] ) && 'header-mini-cart' == $args['type'] ) { 
    $header_mini_cart   = true;

    $cart_btn_class     = 'button_drop';
    $checkout_btn_class = 'button_drop outline';
    $cart_btn_label     = __( 'Go to Cart', 'citytours' );
}

do_action( 'woocommerce_before_mini_cart' );

if ( $header_mini_cart ): 
    wp_nonce_field( 'ajax_mini_cart', 'ajax_mini_cart' ); 
?>
<li>
<?php endif; ?>

    <ul class="cart_list product_list_widget <?php echo $args['list_class']; ?>">

        <?php 
        if ( ! WC()->cart->is_empty() ) :

            do_action( 'woocommerce_before_mini_cart_contents' );

            foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
                $_product     = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
                $product_id   = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );

                if ( $_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters( 'woocommerce_widget_cart_item_visible', true, $cart_item, $cart_item_key ) ) {
                    $product_name      = apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key );
                    $thumbnail         = apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image(), $cart_item, $cart_item_key );
                    $product_price     = apply_filters( 'woocommerce_cart_item_price', WC()->cart->get_product_price( $_product ), $cart_item, $cart_item_key );
                    $product_permalink = apply_filters( 'woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink( $cart_item ) : '', $cart_item, $cart_item_key );
                    ?>

                    <li class="woocommerce-mini-cart-item <?php echo esc_attr( apply_filters( 'woocommerce_mini_cart_item_class', 'mini_cart_item', $cart_item, $cart_item_key ) ); ?>">
                        <?php
                        echo apply_filters( 'woocommerce_cart_item_remove_link', sprintf(
                            '<a href="%s" class="remove remove_from_cart_button" aria-label="%s" data-product_id="%s" data-cart_item_key="%s" data-product_sku="%s"><i class="icon-trash"></i></a>',
                            esc_url( wc_get_cart_remove_url( $cart_item_key ) ),
                            __( 'Remove this item', 'woocommerce' ),
                            esc_attr( $product_id ),
                            esc_attr( $cart_item_key ),
                            esc_attr( $_product->get_sku() )
                        ), $cart_item_key );
                        ?>
                        <?php if ( ! $_product->is_visible() ) : ?>
                            <?php echo str_replace( array( 'http:', 'https:' ), '', $thumbnail ) . $product_name . '&nbsp;'; ?>
                        <?php else : ?>
                            <a href="<?php echo esc_url( $product_permalink ); ?>" class="image">
                                <?php echo str_replace( array( 'http:', 'https:' ), '', $thumbnail ); ?>
                                <span class="product-title"><?php echo $product_name . '&nbsp;'; ?></span>
                            </a>
                        <?php endif; ?>

                        <div class="item-desc">
                            <?php echo wc_get_formatted_cart_item_data( $cart_item ); ?>

                            <?php echo apply_filters( 'woocommerce_widget_cart_item_quantity', '<span class="quantity">' . sprintf( '%s &times; %s', $cart_item['quantity'], $product_price ) . '</span>', $cart_item, $cart_item_key ); ?>
                        </div>
                    </li>

                    <?php
                }
            }

            do_action( 'woocommerce_mini_cart_contents' );

        else : 
            ?>

            <li class="empty"><?php _e( 'No products in the cart.', 'woocommerce' ); ?></li>

            <?php 
        endif; 
        ?>

    </ul><!-- end product list -->

<?php if ( $header_mini_cart ): ?>
</li>
<?php endif; ?>


<?php if ( ! WC()->cart->is_empty() ) : ?>

    <?php if ( $header_mini_cart ): ?>
    <li class="mini-cart-btn">
    <?php endif; ?>

        <div class="total"><strong><?php _e( 'Subtotal', 'woocommerce' ); ?>:</strong> <?php echo WC()->cart->get_cart_subtotal(); ?></div>

        <?php do_action( 'woocommerce_widget_shopping_cart_before_buttons' ); ?>

        <div class="buttons">
            <a href="<?php echo esc_url( wc_get_cart_url() ); ?>" class="button wc-forward <?php echo $cart_btn_class ?>"><?php echo $cart_btn_label ?></a>
            <a href="<?php echo esc_url( wc_get_checkout_url() ); ?>" class="button checkout wc-forward <?php echo $checkout_btn_class ?>"><?php _e( 'Checkout', 'woocommerce' ); ?></a>

            <?php 
                do_action( 'woocommerce_widget_shopping_cart_buttons' );
            ?>
        </div>

    <?php if ( $header_mini_cart ): ?>
    </li>
    <?php endif; ?>

<?php endif; ?>

<?php do_action( 'woocommerce_after_mini_cart' ); ?>
