<?php
/**
 * Empty cart page
 *
 * @see         https://docs.woocommerce.com/document/template-structure/
 * @author  WooThemes
 * @package WooCommerce/Templates
 * @version 3.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}
?>

<div class="cart-section"> 
    <?php wc_print_notices(); ?>

    <?php do_action( 'woocommerce_cart_is_empty' ); ?>

    <?php if ( wc_get_page_id( 'shop' ) > 0 ) : ?>
        <p class="return-to-shop">
            <a class="button wc-backward btn_cart_outine" href="<?php echo esc_url( apply_filters( 'woocommerce_return_to_shop_redirect', wc_get_page_permalink( 'shop' ) ) ); ?>">
                <?php _e( 'Return to Shop', 'woocommerce' ) ?>
            </a>
        </p>
    <?php endif; ?>

</div>