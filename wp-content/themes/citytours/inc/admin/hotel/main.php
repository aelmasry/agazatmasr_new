<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

require_once( CT_INC_DIR . '/admin/hotel/vacancies-admin-panel.php' );
require_once( CT_INC_DIR . '/admin/hotel/orders-admin-panel.php' );
require_once( CT_INC_DIR . '/admin/hotel/reviews-admin-panel.php' );

/*
 * get hotel room list from hotel id
 */
if ( ! function_exists( 'ct_ajax_hotel_order_postid_change' ) ) {
	function ct_ajax_hotel_order_postid_change() {
		$post_id = ! empty( $_POST['post_id'] ) ? $_POST['post_id'] : 0;
		ob_start();
		ct_hotel_get_room_list( $post_id );
		$room_list = ob_get_contents();
		ob_end_clean();
		ob_start();
		ct_get_service_list( $post_id );
		$service_list = ob_get_contents();
		ob_end_clean();
		$result_json = array(
				'success' => 1,
				'room_list' => $room_list,
				'service_list' => $service_list,
			);
		wp_send_json( $result_json );
	}
}

/*
 * add hotel filter to admin/room_type list
 */
if ( ! function_exists('ct_hotel_table_filtering') ) 
{
	function ct_hotel_table_filtering() 
	{
		global $wpdb;
		if ( isset( $_GET['post_type'] ) && 'room_type' == $_GET['post_type'] ) {
			$accs = get_posts( array( 'post_type'=>'hotel', 'posts_per_page'=>-1, 'orderby'=>'post_title', 'order'=>'ASC', 'suppress_filters'=>0 ) );
			echo '<select name="hotel_id" id="hotel_filter">';
			echo '<option value="">' . esc_html__( 'All Hotels', 'citytours' ) . '</option>';
			foreach( $accs as $acc ) {
				$selected = ( ! empty( $_GET['hotel_id'] ) AND $_GET['hotel_id'] == $acc->ID ) ? 'selected="selected"' : '';
				echo '<option value="' . esc_attr( $acc->ID ) . '" ' . esc_attr( $selected ) . '>' . esc_html( $acc->post_title ) . '</option>';
			}
			echo '</select>';
		}
		
	}
}

/*
 * do filter by hotel id in admin/room_type list
 */
if ( ! function_exists('ct_admin_filter_room_type') ) {
	function ct_admin_filter_room_type( $query ) {
		global $pagenow;
		$qv = &$query->query_vars;
		if ( $pagenow=='edit.php' && isset($qv['post_type']) && $qv['post_type']=='room_type' && !empty($_GET['hotel_id']) && is_numeric($_GET['hotel_id']) ) {
			$qv['meta_key'] = '_room_hotel_id';
			$qv['meta_value'] = $_GET['hotel_id'];
		}
	}
}

/*
 * remove or add columns on admin/Hotel list
 */
if ( ! function_exists('ct_hotel_set_columns') ) {
	function ct_hotel_set_columns( $columns ) {
		$author = $columns['author'];
		$date = $columns['date'];
		$facilities = $columns['taxonomy-hotel_facility'];
		unset($columns['taxonomy-hotel_facility']);
		unset($columns['comments']);
		unset($columns['author']);
		unset($columns['date']);

		$columns['taxonomy-hotel_facility'] = $facilities;
		$columns['author'] = $author;
		$columns['date'] = $date;
		return $columns;
	}
}

/*
 * Modify columns on admin/room_type list
 */
if ( ! function_exists('ct_room_type_custom_columns') ) {
	function ct_room_type_custom_columns( $column, $post_id ) {
		switch ( $column ) {

			case 'hotel' :
				$hotel_id = get_post_meta( $post_id, '_room_hotel_id', true );
				if ( ! empty( $hotel_id ) ) {
					edit_post_link( get_the_title( $hotel_id ), '', '', $hotel_id );
				} else {
					echo esc_html__( 'Not Set', 'citytours' );
				}
				break;
			case 'max_adults' :
				$max_adults = get_post_meta( $post_id, '_room_max_adults', true );
				echo esc_html( $max_adults );
				break;
			case 'max_kids' :
				$max_adults = get_post_meta( $post_id, '_room_max_kids', true );
				echo esc_html( $max_adults );
				break;
		}
	}
}

/*
 * remove or add columns on admin/room_type list
 */
if ( ! function_exists('ct_room_type_set_columns') ) {
	function ct_room_type_set_columns( $columns ) {
		$author = $columns['author'];
		$date = $columns['date'];
		$facilities = $columns['taxonomy-hotel_facility'];
		unset($columns['author']);
		unset($columns['date']);
		unset($columns['taxonomy-hotel_facility']);

		$columns['hotel'] = esc_html__( 'Hotel', 'citytours' );
		$columns['max_adults'] = esc_html__( 'Max Adults', 'citytours' );
		$columns['max_kids'] = esc_html__( 'Max Kids', 'citytours' );
		$columns['taxonomy-hotel_facility'] = $facilities;
		$columns['author'] = $author;
		$columns['date'] = $date;
		return $columns;
	}
}

/*
 * declare sortable columns on admin/room_type list
 */
if ( ! function_exists('ct_room_type_table_sorting') ) {
	function ct_room_type_table_sorting( $columns ) {
	  $columns['hotel'] = 'hotel';
	  return $columns;
	}
}

/*
 * make hotel column orderable on admin/room_type list
 */
if ( ! function_exists('ct_room_type_hotel_column_orderby') ) {
	function ct_room_type_hotel_column_orderby( $vars ) {
		if ( isset( $vars['orderby'] ) && 'room_type' == $vars['orderby'] && isset( $vars['orderby'] ) && 'hotel' == $vars['orderby'] ) {
			$vars = array_merge( $vars, array(
				'meta_key' => '_room_hotel_id',
				'orderby' => 'meta_value'
			) );
		}

		return $vars;
	}
}

/*
 * get room select list
 */
if ( ! function_exists( 'ct_hotel_get_room_list' ) ) {
	function ct_hotel_get_room_list( $post_id=0, $def_room_id=0 ) {

		$args = array(
				'post_type'         => 'room_type',
				'posts_per_page'    => -1,
				'post_status'    => 'publish',
				'orderby'           => 'title',
				'order'             => 'ASC',
		);

		if ( ! empty( $post_id ) ) {
			$args['meta_query'] = array(
					array(
						'key'     => '_room_hotel_id',
						'value'   => sanitize_text_field( $post_id ),
					),
				);
		}

		echo '<option></option>';
		$room_type_query = new WP_Query( $args );
		if ( $room_type_query->have_posts() ) {
			while ( $room_type_query->have_posts() ) {
				$room_type_query->the_post();
				$selected = '';
				$id = $room_type_query->post->ID;
				if ( $def_room_id == $id ) $selected = ' selected ';
				echo '<option ' . esc_attr( $selected ) . 'value="' . esc_attr( $id ) .'">' . wp_kses_post( get_the_title( $id ) ) . '</option>';
			}
		}
		wp_reset_postdata();
	}
}

/*
 * get accommodation id from room type id
 */
if ( ! function_exists( 'ct_ajax_hotel_get_room_hotel_id' ) ) {
	function ct_ajax_hotel_get_room_hotel_id() {
		if ( isset( $_POST['room_id'] ) ) {
			$hotel_id = get_post_meta( sanitize_text_field( $_POST['room_id'] ), '_room_hotel_id', true );
			echo esc_js( $hotel_id );
		} else {
			//
		}
		exit();
	}
}

add_action( 'manage_room_type_posts_custom_column' , 'ct_room_type_custom_columns', 10, 2 );
add_filter( 'manage_edit-room_type_sortable_columns', 'ct_room_type_table_sorting' );
add_action( 'restrict_manage_posts', 'ct_hotel_table_filtering', 10, 2 );
add_filter( 'parse_query','ct_admin_filter_room_type' );
add_filter( 'manage_hotel_posts_columns', 'ct_hotel_set_columns' );
add_filter( 'manage_room_type_posts_columns', 'ct_room_type_set_columns' );
add_filter( 'request', 'ct_room_type_hotel_column_orderby' );

/* ajax */
add_action( 'wp_ajax_hotel_order_postid_change', 'ct_ajax_hotel_order_postid_change' );
add_action( 'wp_ajax_hotel_get_hotel_room_list', 'ct_ajax_hotel_get_hotel_room_list' );
add_action( 'wp_ajax_hotel_get_room_hotel_id', 'ct_ajax_hotel_get_room_hotel_id' );
add_action('admin_footer', 'ct_hotel_order_admin_enqueue_scripts');