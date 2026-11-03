<?php
/*
 * get sibling post id in default language
 */
if ( ! function_exists( 'ct_get_default_language_post_id' ) ) {
	function ct_get_default_language_post_id( $id, $post_type = 'post' ) {
		if ( class_exists('SitePress') ) {
			global $sitepress;

			$default_language = $sitepress->get_default_language();
			return icl_object_id($id, $post_type, true, $default_language);
		} else {
			return $id;
		}
	}
}

/*
 * get sibling post id in current language
 */
if ( ! function_exists( 'ct_get_current_language_post_id' ) ) {
	function ct_get_current_language_post_id( $id, $post_type = 'post' ) {
		if ( class_exists('SitePress') ) {
			return icl_object_id( $id, $post_type, true );
		} else {
			return $id;
		}
	}
}

/*
 * get sibling hotel id in original language
 */
if ( ! function_exists( 'ct_hotel_org_id' ) ) {
	function ct_hotel_org_id( $id ) {
		return ct_get_default_language_post_id( $id, 'hotel' );
	}
}

/*
 * get sibling hotel id in current language
 */
if ( ! function_exists( 'ct_hotel_clang_id' ) ) {
	function ct_hotel_clang_id( $id ) {
		return ct_get_current_language_post_id( $id, 'hotel' );
	}
}


/*
 * get sibling room id in original language
 */
if ( ! function_exists( 'ct_room_org_id' ) ) {
	function ct_room_org_id( $id ) {
		return ct_get_default_language_post_id( $id, 'room_type' );
	}
}

/*
 * get sibling room id in current language
 */
if ( ! function_exists( 'ct_room_clang_id' ) ) {
	function ct_room_clang_id( $id ) {
		return ct_get_current_language_post_id( $id, 'room_type' );
	}
}

/*
 * get sibling tour id in original language
 */
if ( ! function_exists( 'ct_tour_org_id' ) ) {
	function ct_tour_org_id( $id ) {
		return ct_get_default_language_post_id( $id, 'tour' );
	}
}

/*
 * get sibling tour id in current language
 */
if ( ! function_exists( 'ct_tour_clang_id' ) ) {
	function ct_tour_clang_id( $id ) {
		return ct_get_current_language_post_id( $id, 'tour' );
	}
}

/*
 * get sibling post id in original language
 */
if ( ! function_exists( 'ct_post_org_id' ) ) {
	function ct_post_org_id( $id ) {
		return ct_get_default_language_post_id( $id, get_post_type( $id ) );
	}
}

/*
 * get sibling post id in current language
 */
if ( ! function_exists( 'ct_post_clang_id' ) ) {
	function ct_post_clang_id( $id ) {
		return ct_get_current_language_post_id( $id, get_post_type( $id ) );
	}
}

/*
 * get default language
 */
if ( ! function_exists( 'ct_get_default_language' ) ) {
	function ct_get_default_language() {
		global $sitepress;

		if ( $sitepress ) {
			return $sitepress->get_default_language();
		} elseif ( defined(WPLANG) ) {
			return WPLANG;
		}
		
		return "en";
	}
}

/*
 * get default language
 */
if ( ! function_exists( 'ct_get_permalink_clang' ) ) {
	function ct_get_permalink_clang( $post_id )
	{
		$url = "";
		if ( class_exists('SitePress') ) {
			$language = ICL_LANGUAGE_CODE;

			$lang_post_id = icl_object_id( $post_id , 'page', true, $language );

			if( 0 != $lang_post_id ) {
				$url = get_permalink( $lang_post_id );
			} else {
				// No page found, it's most likely the homepage
				global $sitepress;
				$url = $sitepress->language_url( $language );
			}
		} else {
			$url = get_permalink( $post_id );
		}

		return esc_url( $url );
	}
}

/*
 * get language count
 */
if ( ! function_exists( 'ct_get_lang_count' ) ) {
	function ct_get_lang_count() {
		$language_count = 1;
		// wpml variables
		if ( defined('ICL_LANGUAGE_CODE') ) {
			$languages = icl_get_languages('skip_missing=1');
			$language_count = count( $languages );
		}
		return $language_count;
	}
}

/*
 * get language code for page
 */
if ( ! function_exists( 'ct_get_lang_code_for_page' ) ) { 
	function ct_get_lang_code_for_page( $post_id ) { 
		$lang_code = "";

		if ( class_exists('SitePress') ) {
			$language = ICL_LANGUAGE_CODE;

			$lang_post_id = icl_object_id( $post_id , 'page', true, $language );

			if( 0 != $lang_post_id ) {
				$lang_code = ICL_LANGUAGE_CODE;
			}
		}

		return $lang_code;
	}
}