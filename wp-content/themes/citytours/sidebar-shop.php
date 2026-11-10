<?php 
/* Tour List Page Template */
if ( ! defined( 'ABSPATH' ) ) { 
    exit; 
}

if ( is_archive() ) { 
    $product_id = 'shop';
} else { 
    $product_id = get_the_ID();
}

$sidebar_position = ct_get_sidebar_position( $product_id );

if ( $sidebar_position != 'no' ) { 
    echo '<div class="col-md-3">';

    generated_dynamic_sidebar();

    echo '</div>';
}
