<?php
/**
 * Login form
 *
 * @see         https://docs.woocommerce.com/document/template-structure/
 * @author      WooThemes
 * @package     WooCommerce/Templates
 * @version     3.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if ( is_user_logged_in() ) {
    return;
}

?>
<form method="post" class="checkout-login login row" <?php echo ( $hidden ) ? 'style="display:none;"' : ''; ?>>

    <?php do_action( 'woocommerce_login_form_start' ); ?>

    <div class="form-group col-sm-12"> 
        <?php echo ( $message ) ? wpautop( wptexturize( $message ) ) : ''; // @codingStandardsIgnoreLine ?>
    </div>

    <div class="form-row form-row-first col-sm-6 form-group">
        <label for="username"><?php esc_html_e( 'Username or email', 'woocommerce' ); ?> <span class="required">*</span></label>
        <input type="text" class="input-text form-control" name="username" id="username" />
    </div>
    <div class="form-row form-row-last col-sm-6 form-group">
        <label for="password"><?php esc_html_e( 'Password', 'woocommerce' ); ?> <span class="required">*</span></label>
        <input class="input-text form-control" type="password" name="password" id="password" />
    </div>
    <div class="clear"></div>

    <?php do_action( 'woocommerce_login_form' ); ?>

    <div class="form-row col-sm-6">
        <?php wp_nonce_field( 'woocommerce-login', 'woocommerce-login-nonce' ); ?>
        <input type="submit" class="button btn_1" name="login" value="<?php esc_attr_e( 'Login', 'woocommerce' ); ?>" />
        <input type="hidden" name="redirect" value="<?php echo esc_url( $redirect ) ?>" />

        <label for="rememberme" class="woocommerce-form__label woocommerce-form__label-for-checkbox inline remember-user">
            <input class="woocommerce-form__input woocommerce-form__input-checkbox" name="rememberme" type="checkbox" id="rememberme" value="forever" /> <?php _e( 'Remember me', 'woocommerce' ); ?>
        </label>

        <p class="lost_password" style="display: inline-block">
            <a href="<?php echo esc_url( wp_lostpassword_url() ); ?>"><?php esc_html_e( 'Lost your password?', 'woocommerce' ); ?></a>
        </p>
    </div>

    <div class="clear"></div>

    <?php do_action( 'woocommerce_login_form_end' ); ?>

</form>
