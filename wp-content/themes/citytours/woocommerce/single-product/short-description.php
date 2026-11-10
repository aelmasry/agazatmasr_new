<?php
/**
 * Single product short description
 *
 * @see 	    https://docs.woocommerce.com/document/template-structure/
 * @author 		Automattic
 * @package 	WooCommerce/Templates
 * @version     3.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $post;

$short_description = apply_filters( 'woocommerce_short_description', $post->post_excerpt );

if ( ! $short_description ) {
	return;
}

?>
<div class="woocommerce-product-details__short-description description">
	<?php echo $short_description; ?>
</div>
