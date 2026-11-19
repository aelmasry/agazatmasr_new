<?php
/**
 * Product quantity inputs
 *
 * @see 	    https://docs.woocommerce.com/document/template-structure/
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     3.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( $max_value && $min_value === $max_value ) {
	?>

	<div class="quantity hidden">
		<input type="hidden" id="<?php echo esc_attr( $input_id ); ?>" class="qty" name="<?php echo esc_attr( $input_name ); ?>" value="<?php echo esc_attr( $min_value ); ?>" />
	</div>
	
	<?php
} else {
	?>

	<div class="quantity">
		<?php if ( isset( $is_custom ) && $is_custom ) { ?>
			<div class="numbers-row" data-min="1" data-max="1">
		<?php } else { ?>
			<div class="numbers-row" data-min="<?php echo esc_attr( $min_value ) ?>" data-max="<?php echo esc_attr( $max_value ) ?>">
		<?php } ?>

				<input type="text" id="<?php echo esc_attr( $input_id ); ?>" class="input-text qty qty2 form-control text" step="<?php echo esc_attr( $step ); ?>" min="<?php echo esc_attr( $min_value ); ?>" max="<?php echo esc_attr( 0 < $max_value ? $max_value : '' ); ?>" name="<?php echo esc_attr( $input_name ); ?>" value="<?php echo esc_attr( $input_value ); ?>" title="<?php echo esc_attr_x( 'Qty', 'Product quantity input tooltip', 'woocommerce' ) ?>" size="4" pattern="<?php echo esc_attr( $pattern ); ?>" inputmode="<?php echo esc_attr( $inputmode ); ?>" aria-labelledby="<?php echo ! empty( $args['product_name'] ) ? sprintf( esc_attr__( '%s quantity', 'woocommerce' ), $args['product_name'] ) : ''; ?>" />
				
				<div class="inc button_inc">+</div>
				<div class="dec button_inc">-</div>
			</div>
	</div>

	<?php
}
