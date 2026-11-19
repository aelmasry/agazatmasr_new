<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Filters For autocomplete param:
// For suggestion: vc_autocomplete_[shortcode_name]_[param_name]_callback

// "Category" field in "FAQs" Visual Composer element
add_filter( 'vc_autocomplete_faqs_category_callback', 'ct_faqs_autocomplete_suggestor', 10, 3 );
add_filter( 'vc_autocomplete_faqs_category_render', 'ct_faqs_autocomplete_render', 10 );

// "Tour IDs" field in "Tours" Visual Composer element
add_filter( 'vc_autocomplete_tours_post_ids_callback', 'ct_tours_autocomplete_suggestor', 10, 3 );
add_filter( 'vc_autocomplete_tours_post_ids_render', 'ct_tours_autocomplete_render', 10 );

// "Tour IDs" field in "Hotels" Visual Composer element
add_filter( 'vc_autocomplete_hotels_post_ids_callback', 'ct_hotels_autocomplete_suggestor', 10, 3 );
add_filter( 'vc_autocomplete_hotels_post_ids_render', 'ct_hotels_autocomplete_render', 10 );

// "Around Hotels or Tours" field in "CityTours Map" Visual Composer element
add_filter( 'vc_autocomplete_map_related_callback', 'ct_map_related_autocomplete_suggestor', 10, 3 );
add_filter( 'vc_autocomplete_map_related_render', 'ct_map_related_autocomplete_render', 10 );

/*
 * Suggestor for "Category" field in "FAQs" shortcode
 */
if ( ! function_exists( 'ct_faqs_autocomplete_suggestor' ) ) { 
	function ct_faqs_autocomplete_suggestor( $query, $tag, $param_name ) { 
		global $wpdb;

		$cat_id = (int) $query;
		$query = trim( $query );

		$sql = $wpdb->prepare( "SELECT a.term_id AS id, b.name as name, b.slug AS slug
			FROM {$wpdb->term_taxonomy} AS a
			INNER JOIN {$wpdb->terms} AS b ON b.term_id = a.term_id
			WHERE a.taxonomy = 'faq_cat' AND (a.term_id = '%d' OR b.slug LIKE '%%%s%%' OR b.name LIKE '%%%s%%' )", $cat_id > 0 ? $cat_id : - 1, stripslashes( $query ), stripslashes( $query ) );

		$post_meta_infos = $wpdb->get_results( $sql, ARRAY_A );

		$result = array();
		if ( is_array( $post_meta_infos ) && ! empty( $post_meta_infos ) ) {
			foreach ( $post_meta_infos as $value ) {
				$data = array();
				$data['value'] = $slug ? $value['slug'] : $value['id'];
				$data['label'] = __( 'Id', 'citytours' ) . ': ' . $value['id'] . ( ( strlen( $value['name'] ) > 0 ) ? ' - ' . __( 'Name', 'citytours' ) . ': ' . $value['name'] : '' ) . ( ( strlen( $value['slug'] ) > 0 ) ? ' - ' . __( 'Slug', 'citytours' ) . ': ' . $value['slug'] : '' );
				$result[] = $data;
			}
		}

		return $result;
	}
}

/*
 * Renderer for "Category" field in "FAQs" shortcode
 */
if ( ! function_exists( 'ct_faqs_autocomplete_render' ) ) { 
	function ct_faqs_autocomplete_render( $query ) { 
		$query = $query['value'];
		$cat_id = (int) $query;
		$term = get_term( $cat_id, 'faq_cat' );

		if ( ! is_wp_error( $term ) ) { 
			$term_slug = $term->slug;
			$term_title = $term->name;
			$term_id = $term->term_id;

			$term_slug_display = '';
			if ( ! empty( $term_slug ) ) {
				$term_slug_display = ' - ' . __( 'Sku', 'js_composer' ) . ': ' . $term_slug;
			}

			$term_title_display = '';
			if ( ! empty( $term_title ) ) {
				$term_title_display = ' - ' . __( 'Title', 'js_composer' ) . ': ' . $term_title;
			}

			$term_id_display = __( 'Id', 'js_composer' ) . ': ' . $term_id;

			$data = array();
			$data['value'] = $term_id;
			$data['label'] = $term_id_display . $term_title_display . $term_slug_display;

			return $data;
		}

		return false;
	}
}

/*
 * Suggestor for "Tour IDs" field in "Tours" shortcode
 */
if ( ! function_exists( 'ct_tours_autocomplete_suggestor' ) ) { 
	function ct_tours_autocomplete_suggestor( $query, $tag, $param_name ) { 
		global $wpdb;

		$tour_id = (int) $query;
		$tour_infos = $wpdb->get_results( $wpdb->prepare( "SELECT a.ID AS id, a.post_title AS title
			FROM {$wpdb->posts} AS a
			WHERE a.post_type = 'tour' AND ( a.ID = '%d' OR a.post_title LIKE '%%%s%%' )", $tour_id > 0 ? $tour_id : - 1, stripslashes( $query ) ), ARRAY_A );

		$results = array();
		if ( is_array( $tour_infos ) && ! empty( $tour_infos ) ) {
			foreach ( $tour_infos as $value ) {
				$data = array();

				$data['value'] = $value['id'];
				$data['label'] = __( 'Id', 'js_composer' ) . ': ' . $value['id'] . ( ( strlen( $value['title'] ) > 0 ) ? ' - ' . __( 'Title', 'js_composer' ) . ': ' . $value['title'] : '' );

				$results[] = $data;
			}
		}

		return $results;
	}
}

/*
 * Renderer for "Tour IDs" field in "Tours" shortcode
 */
if ( ! function_exists( 'ct_tours_autocomplete_render' ) ) { 
	function ct_tours_autocomplete_render( $query ) { 
		$query = trim( $query['value'] ); // get value from requested

		if ( ! empty( $query ) ) {
			// get tour
			$tour_object = get_post( (int) $query );

			if ( is_object( $tour_object ) ) {
				$tour_title = $tour_object->post_title;
				$tour_id = $tour_object->ID;

				$tour_title_display = '';
				if ( ! empty( $tour_title ) ) {
					$tour_title_display = ' - ' . __( 'Title', 'js_composer' ) . ': ' . $tour_title;
				}

				$tour_id_display = __( 'Id', 'js_composer' ) . ': ' . $tour_id;

				$data = array();
				$data['value'] = $tour_id;
				$data['label'] = $tour_id_display . $tour_title_display;

				return $data;
			}

			return false;
		}

		return false;
	}
}

/*
 * Suggestor for "Hotel IDs" field in "Hotels" shortcode
 */
if ( ! function_exists( 'ct_hotels_autocomplete_suggestor' ) ) { 
	function ct_hotels_autocomplete_suggestor( $query, $tag, $param_name ) { 
		global $wpdb;

		$hotel_id = (int) $query;
		$hotel_infos = $wpdb->get_results( $wpdb->prepare( "SELECT a.ID AS id, a.post_title AS title 
			FROM {$wpdb->posts} AS a 
			WHERE a.post_type = 'hotel' AND ( a.ID = '%d' OR a.post_title LIKE '%%%s%%' )", $hotel_id > 0 ? $hotel_id : - 1, stripslashes( $query ) ), ARRAY_A );

		$results = array();
		if ( is_array( $hotel_infos ) && ! empty( $hotel_infos ) ) {
			foreach ( $hotel_infos as $value ) {
				$data = array();

				$data['value'] = $value['id'];
				$data['label'] = __( 'Id', 'js_composer' ) . ': ' . $value['id'] . ( ( strlen( $value['title'] ) > 0 ) ? ' - ' . __( 'Title', 'js_composer' ) . ': ' . $value['title'] : '' );

				$results[] = $data;
			}
		}

		return $results;
	}
}

/*
 * Renderer for "Hotel IDs" field in "Hotels" shortcode
 */
if ( ! function_exists( 'ct_hotels_autocomplete_render' ) ) { 
	function ct_hotels_autocomplete_render( $query ) { 
		$query = trim( $query['value'] ); // get value from requested

		if ( ! empty( $query ) ) {
			// get tour
			$hotel_object = get_post( (int) $query );

			if ( is_object( $hotel_object ) ) {
				$hotel_title = $hotel_object->post_title;
				$hotel_id = $hotel_object->ID;

				$hotel_title_display = '';
				if ( ! empty( $hotel_title ) ) {
					$hotel_title_display = ' - ' . __( 'Title', 'js_composer' ) . ': ' . $hotel_title;
				}

				$hotel_id_display = __( 'Id', 'js_composer' ) . ': ' . $hotel_id;

				$data = array();
				$data['value'] = $hotel_id;
				$data['label'] = $hotel_id_display . $hotel_title_display;

				return $data;
			}

			return false;
		}

		return false;
	}
}

/*
 * Suggestor for "Around Hotels or Tours" field in "CityTours Map" shortcode
 */
if ( ! function_exists( 'ct_map_related_autocomplete_suggestor' ) ) { 
	function ct_map_related_autocomplete_suggestor( $query, $tag, $param_name ) { 
		global $wpdb;

		$object_id = (int) $query;

		$object_info = $wpdb->get_results( $wpdb->prepare( "SELECT a.ID AS id, a.post_title AS title, a.post_type AS post_type 
			FROM {$wpdb->posts} AS a 
			WHERE a.post_status = 'publish' AND a.post_type IN ('hotel', 'tour') AND ( a.ID = '%d' OR a.post_title LIKE '%%%s%%' )", $object_id > 0 ? $object_id : - 1, stripslashes( $query ) ), ARRAY_A );

		$results = array();
		if ( is_array( $object_info ) && ! empty( $object_info ) ) {
			foreach ( $object_info as $value ) {
				$data = array();

				$data['value'] = $value['id'];

				$label = '';
				if ( 'hotel' == $value['post_type'] ) { 
					$label .= __('Hotel', 'citytours') . '  -  ';
				} else if ( 'tour' == $value['post_type'] ) { 
					$label .= __('Tour', 'citytours') . '  -  ';
				}
				$label .= __( 'Id', 'js_composer' ) . ': ' . $value['id'] . ( ( strlen( $value['title'] ) > 0 ) ? ' - ' . __( 'Title', 'js_composer' ) . ': ' . $value['title'] : '' );

				$data['label'] = $label;

				$results[] = $data;
			}
		}

		return $results;
	}
}

/*
 * Renderer for "Around Hotels or Tours" field in "CityTours Map" shortcode
 */
if ( ! function_exists( 'ct_map_related_autocomplete_render' ) ) { 
	function ct_map_related_autocomplete_render( $query ) { 
		$query = trim( $query['value'] ); // get value from requested

		if ( ! empty( $query ) ) {
			// get tour
			$post_object = get_post( (int) $query );

			if ( is_object( $post_object ) ) {
				$post_title_display = '';
				if ( ! empty( $post_object->post_title ) ) {
					$post_title_display = ' - ' . __( 'Title', 'js_composer' ) . ': ' . $post_object->post_title;
				}

				$post_id_display = __( 'Id', 'js_composer' ) . ': ' . $post_object->ID;

				$post_type_display = '';
				if ( 'hotel' == $post_object->post_type ) { 
					$post_type_display .= __('Hotel', 'citytours') . '  -  ';
				} else if ( 'tour' == $post_object->post_type ) { 
					$post_type_display .= __('Tour', 'citytours') . '  -  ';
				}

				$data = array();
				$data['value'] = $post_object->ID;
				$data['label'] = $post_type_display . $post_id_display . $post_title_display;

				return $data;
			}

			return false;
		}

		return false;
	}
}