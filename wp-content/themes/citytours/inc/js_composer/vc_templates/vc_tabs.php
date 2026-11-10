<?php
$variables = array( 'style'=>'', 'active_tab_index' => '1', 'el_class'=>'', 'class'=>'' );
extract( shortcode_atts( $variables, $atts ) );

$result = '';

preg_match_all( '/\[vc_tab(.*?)]/i', $content, $matches, PREG_OFFSET_CAPTURE );
$tab_titles = array();

if ( isset( $matches[0] ) ) {
	$tab_titles = $matches[0];
}
if ( count( $tab_titles ) ) {

	$classes = array( 'tab-container', 'clearfix', $style );
	if ( $el_class != '' ) {
		$classes[] = $el_class;
	}

	$result .= sprintf( '<div class="%s"><ul class="nav nav-tabs">', esc_attr( $class ) );
	$uid = uniqid();
	foreach ( $tab_titles as $i => $tab ) {
		preg_match( '/title="([^\"]+)"/i', $tab[0], $tab_matches, PREG_OFFSET_CAPTURE );
		if ( isset( $tab_matches[1][0] ) ) {
			$active_class = '';
			$active_attr = '';
			if ( $active_tab_index - 1 == $i ) {
				$active_class = ' class="active"';
				$active_attr = ' active="true"';
			}

			preg_match( '/ tab_id="([^\"]+)"/i', $tab[0], $tab_id_matches, PREG_OFFSET_CAPTURE );
			$tid = '';
			if ( !empty( $tab_id_matches[1][0] ) ) {
				$tid = esc_attr( $tab_id_matches[1][0] );
			}

			$result .= '<li '. $active_class . '><a href="#' . $tid . '" data-toggle="tab">' . esc_html( $tab_matches[1][0] ) . '</a></li>';
			$before_content = substr($content, 0, $tab[1]);
			$current_content = substr($content, $tab[1]);
			$current_content = preg_replace('/\[vc_tab/', '[vc_tab' . $active_attr, $current_content, 1);
			$content = $before_content . $current_content;
		}
	}
	$result .= '</ul>';
	$result .= '<div class="tab-content">';
	$result .= wpb_js_remove_wpautop( $content );
	$result .= '</div>';
	$result .= '</div>';
} else {
	$result .= wpb_js_remove_wpautop( $content );
}

echo ( $output );