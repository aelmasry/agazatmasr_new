<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// location taxonomy fields
require_once(get_template_directory() . '/inc/lib/tax-meta-class/Tax-meta-class.php');

if (is_admin()){
	$prefix = 'ct_';
    
	$config = array(
		'id' => 'ct_info',
		'title' => 'Custom Icon Class',
		'pages' => array('tour_type', 'tour_facility', 'hotel_type', 'hotel_facility'),
		'context' => 'normal',
		'fields' => array(),
		'local_images' => false,
		'use_with_theme' => true
	);

	$my_meta =  new Tax_Meta_Class($config);
	$my_meta->addText($prefix.'tax_icon_class',array('name'=> esc_html__('Custom Icon Class','citytours'),'desc' => 'You can check <a href="http://www.soaptheme.net/wordpress/citytours/icon-pack-1/">Icon Pack1</a> and <a href="http://www.soaptheme.net/wordpress/citytours/icon-pack-2/">Icon Pack2</a> for class detail'));

	$my_meta->Finish();
}