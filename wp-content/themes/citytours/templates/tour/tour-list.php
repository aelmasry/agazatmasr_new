<?php
if ( ! defined( 'ABSPATH' ) ) { 
    exit; 
} 

global $post_list, $current_view, $post_id;

if ( empty( $post_list ) ) :

	echo '<h5 class="empty-list">' . esc_html__( 'No available tours', 'citytours' ) . '</h5>';

else :

	foreach( $post_list as $post_obj ) :

		$post_id = $post_obj['tour_id'];

		ct_get_template( 'loop-' . $current_view . '.php', '/templates/tour/');

	endforeach;

endif;