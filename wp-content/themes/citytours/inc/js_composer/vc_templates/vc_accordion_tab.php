<?php
$output = $title = $parent_id = $active = '';

extract(shortcode_atts(array(
	'title' => esc_html__("Section", 'citytours'),
	'parent_id' => '',
	'active' => '',
	'class' => '',
), $atts));

$class = 'panel panel-default' . (empty( $class ) ? '': ( ' ' . $class ));
$class_in = ( $active === 'yes') ? ' in':'';
$class_collapsed = ( $active === 'yes') ? '' : ' collapsed';
$class_icon = ( $active === 'yes') ? 'icon-minus' : 'icon-plus';

$accordion_attrs = "";
if ( !empty( $parent_id ) ) {
	$accordion_attrs = ' data-parent="#' . $parent_id . '"';
}
$uid = uniqid("ct-tg");
$css_class = apply_filters( VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, $class, $this->settings['base'], $atts );
$output .= "\n\t\t\t" . '<div class="' . esc_attr( $css_class ) . '">';
$output .= "\n\t\t\t\t" . '<div class="panel-heading"><h4 class="panel-title"><a class="accordion-toggle' . $class_collapsed . '" href="#' . $uid . '" data-toggle="collapse"' . $accordion_attrs . '>' . esc_html( $title ) . '<i class="indicator pull-right ' . $class_icon . '"></i></a></h4></div>';
$output .= "\n\t\t\t\t" . '<div id="' . $uid . '" class="panel-collapse collapse' . $class_in . '">';
$output .= "\n\t\t\t\t\t" . '<div class="panel-body"><p>';
$output .= ($content=='' || $content==' ') ? esc_html__("Empty section. Edit page to add content here.", 'citytours') : "\n\t\t\t\t" . wpb_js_remove_wpautop($content);
$output .= "\n\t\t\t\t\t" . '</p></div>';
$output .= "\n\t\t\t\t" . '</div>';
$output .= "\n\t\t\t" . '</div> ' . "\n";

echo ( $output );