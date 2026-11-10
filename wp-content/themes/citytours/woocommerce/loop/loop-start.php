<?php
/**
 * Product Loop Start
 *
 * @see         https://docs.woocommerce.com/document/template-structure/
 * @author      WooThemes
 * @package     WooCommerce/Templates
 * @version     3.3.0
 */
?>

<?php 

global $woocommerce_loop;

$classes = '';
$options = array();

if ( ! is_archive() ) { 
    $options['items'] = $woocommerce_loop['columns'];
    
    if ( isset( $woocommerce_loop['total_products'] ) ) { 
    	$options['slide_count'] = $woocommerce_loop['total_products'];
    }

    $classes .= ' owl-carousel';
} else { 
    $classes .= ' row add-clearfix';
}

$options = 'data-slider="' . esc_attr( json_encode( $options ) ) . '"';

?>

<ul class="products <?php echo $classes ?>" <?php echo $options ?>>
