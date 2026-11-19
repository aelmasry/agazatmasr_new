<?php
// ! File Security Check
if ( ! defined( 'ABSPATH' ) ) { 
    exit; 
}

$extra_class = array(
    'type'          => 'textfield',
    'heading'       => esc_html__( 'Extra class name', 'citytours' ),
    'param_name'    => 'class',
    'description'   => esc_html__( 'If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.', 'citytours' ),
    'save_always'   => true,
    'admin_label'   => true,
);
$content_area = array(
    'type'          => 'textarea_html',
    'heading'       => esc_html__( 'Content', 'citytours' ),
    'param_name'    => 'content',
    'description'   => esc_html__( 'Enter your content.', 'citytours' ),
    'save_always'   => true,
    'admin_label'   => true,
);

$districts = array( 
    esc_html__( 'All', 'citytours' ) => '', 
);

$hotel_districts = get_terms( 'district', array( 
    'hide_empty' => false 
) );
if ( ! is_wp_error( $hotel_districts ) ) {
    foreach ( $hotel_districts as $term ) {
        $districts[$term->name] = $term->term_id;
    }
}

$tour_types = array( 
    esc_html__( 'All', 'citytours' ) => '' 
);

$tour_type_terms = get_terms( 'tour_type', array( 
    'hide_empty' => false 
) );
if ( ! is_wp_error( $tour_type_terms ) ) {
    foreach ( $tour_type_terms as $term ) {
        $tour_types[$term->name] = $term->term_id;
    }
}

if ( is_admin() ) { 
    wp_enqueue_style( 'ct_vc_custom_css', CT_TEMPLATE_DIRECTORY_URI . '/css/admin/js_composer.css' );
}

// ! Removing unwanted shortcodes
vc_remove_element( 'vc_widget_sidebar' );
vc_remove_element( 'vc_wp_search' );
vc_remove_element( 'vc_wp_meta' );
vc_remove_element( 'vc_wp_recentcomments' );
vc_remove_element( 'vc_wp_calendar' );
vc_remove_element( 'vc_wp_pages' );
vc_remove_element( 'vc_wp_tagcloud' );
vc_remove_element( 'vc_wp_custommenu' );
vc_remove_element( 'vc_wp_text' );
vc_remove_element( 'vc_wp_posts' );
vc_remove_element( 'vc_wp_links' );
vc_remove_element( 'vc_wp_categories' );
vc_remove_element( 'vc_wp_archives' );
vc_remove_element( 'vc_wp_rss' );
vc_remove_element( 'vc_gallery' );
vc_remove_element( 'vc_teaser_grid' );
// vc_remove_element( 'vc_btn' );
vc_remove_element( 'vc_cta_button' );
vc_remove_element( 'vc_posts_grid' );
vc_remove_element( 'vc_images_carousel' );
vc_remove_element( 'vc_posts_slider' );
vc_remove_element( 'vc_carousel' );
vc_remove_element( 'vc_message' );
vc_remove_element( 'vc_progress_bar' );
vc_remove_element( 'vc_tour' );

vc_add_param( 'vc_row', array(
    'type'          => 'checkbox',
    'class'         => '',
    'heading'       => esc_html__('Is Container', 'citytours'),
    'param_name'    => 'is_container',
    'value'         => array( 
        esc_html__( 'yes', 'citytours' ) => 'yes' 
    ),
    'description'   => __('This option will add container class to this row. Please check bootstrap container class for more detail.', 'citytours'),
    'def'           => '',
));

/* Blockquote Shortcode */
vc_map( array(
    'name'      => esc_html__('Container', 'citytours'),
    'base'      => 'container',
    'icon'      => 'citytours-js-composer',
    'class'     => '',
    'category'  => esc_html__('by SoapTheme', 'citytours'),
    'params'    => array(
        $content_area,
        $extra_class
    ),
    'js_view'   => 'VcColumnView'
) );

/* Button */
vc_map( array(
    "name"      => esc_html__("Button", 'citytours'),
    "base"      => "button",
    "icon"      => "citytours-js-composer",
    "category"  => esc_html__('by SoapTheme', 'citytours'),
    "params"    => array(
        array(
            "type"          => "textfield",
            "heading"       => esc_html__("Link", 'citytours'),
            "param_name"    => "link",
            'save_always'   => true,
            'admin_label'   => true,
            "value"         => "#"
        ),
        array(
            "type"          => "dropdown",
            "heading"       => esc_html__( "Button Size", 'citytours' ),
            "param_name"    => "size",
            "value"         => array(
                __( "Default", "citytours" )    => "",
                __( "Medium", "citytours" )     => "medium",
                __( "Full", "citytours" )       => "full",
            ),
            "std"           => "",
            'save_always'   => true,
            'admin_label'   => true,
        ),
        array(
            "type"          => "dropdown",
            "heading"       => esc_html__("Style", 'citytours'),
            'save_always'   => true,
            'admin_label'   => true,
            "param_name"    => "style",
            "value"         => array(
                __( "Default", "citytours" ) => "",
                __( "Outline", "citytours" ) => "outline",
                __( "White", "citytours" ) => "white",
                __( "Green", "citytours" ) => "green",
            ),
            "std"           => "",
            "description"   => ""
        ),
        array(
            "type"          => "dropdown",
            "heading"       => esc_html__("Target", 'citytours'),
            "param_name"    => "target",
            "value"         => array(
                "_self"         => "_self",
                "_blank"        => "_blank",
                "_top"          => "_top",
                "_parent"       => "_parent"
            ),
            "std"           => "",
            "save_always"   => true,
            "admin_label"   => true,
            "description"   => ""
        ),
        $content_area,
        $extra_class
    )
) );

/* Blockquote Shortcode */
vc_map( array(
    "name"      => esc_html__("Blockquote", 'citytours'),
    "base"      => "blockquote",
    "icon"      => "citytours-js-composer",
    "class"     => "",
    "category"  => esc_html__('by SoapTheme', 'citytours'),
    "params"    => array(
        $content_area,
        $extra_class
    )
) );

/* Banner Shortcode */
vc_map( array(
    "name"      => esc_html__("Banner", 'citytours'),
    "base"      => "banner",
    "icon"      => "citytours-js-composer",
    "class"     => "",
    "category"  => esc_html__('by SoapTheme', 'citytours'),
    "params"    => array(
        array(
            "type"          => "dropdown",
            "heading"       => esc_html__( 'Type', 'citytours' ),
            "save_always"   => true,
            "admin_label"   => true,
            "param_name"    => 'type',
            "value"         => array(
                __( 'Default', 'citytours' )    => 'default',
                __( 'Custom', 'citytours' )     => 'custom',
            ),
            "std"           => '',
            "description"   => '',
        ),
        array(
            "type"          => "dropdown",
            "heading"       => esc_html__( 'Style', 'citytours' ),
            "save_always"   => true,
            "admin_label"   => true,
            "param_name"    => "style",
            "value"         => array(
                __( "Default", "citytours" ) => "",
                __( "Colored", "citytours" ) => "colored",
            ),
            "std"           => '',
            "description"   => "",
            "dependency"    => array(
                "element"   => 'type',
                "value"     => 'default',
            ),
        ),
        array(
            "type"          => "attach_image",
            "heading"       => esc_html__( 'Background Image', 'citytours' ),
            "save_always"   => true,
            "admin_label"   => true,
            "param_name"    => "bg_image",
            "dependency"    => array(
                "element"   => "type",
                "value"     => "custom",
            ),
        ),
        array(
            "type"          => "colorpicker",
            "heading"       => esc_html__( 'Background Color', 'citytours' ),
            "save_always"   => true,
            "admin_label"   => true,
            "param_name"    => "bg_color",
            "dependency"    => array(
                "element"   => "type",
                "value"     => "custom",
            ),
        ),
        $content_area,
        $extra_class
    )
) );

/* CheckList Shortcode */
vc_map( array(
    "name"      => esc_html__("Checklist", 'citytours'),
    "base"      => "checklist",
    "icon"      => "citytours-js-composer",
    "class"     => "",
    "category"  => esc_html__('by SoapTheme', 'citytours'),
    "params"    => array(
        $content_area,
        $extra_class
    )
) );

/* Icon Box Shortcode */
vc_map( array(
    "name"      => esc_html__("Icon Box", 'citytours'),
    "base"      => "icon_box",
    "icon"      => "citytours-js-composer",
    "class"     => "",
    "category"  => esc_html__('by SoapTheme', 'citytours'),
    "params"    => array(
        array(
            "type"          => "dropdown",
            "heading"       => esc_html__("Style", 'citytours'),
            "save_always"   => true,
            "admin_label"   => true,
            "param_name"    => "style",
            "value"         => array(
                __( "Default", "citytours" )    => "",
                __( "Style2", "citytours" )     => "style2",
                __( "Style3", "citytours" )     => "style3",
            ),
            "std"           => "",
            "description"   => ""
        ),
        array(
            'type'          => 'textfield',
            'heading'       => esc_html__( 'Icon Class', 'citytours' ),
            'param_name'    => 'icon_class',
            'save_always'   => true,
            'admin_label'   => true,
        ),
        $content_area,
        $extra_class
    )
) );

/* Icon List Shortcode */
vc_map( array(
    "name"      => esc_html__("Icon List", 'citytours'),
    "base"      => "icon_list",
    "icon"      => "citytours-js-composer",
    "class"     => "",
    "category"  => esc_html__('by SoapTheme', 'citytours'),
    "params"    => array(
        $content_area,
        $extra_class
    )
) );

/* Tooltip Shortcode */
vc_map( array(
    'name'      => esc_html__('Tooltip', 'citytours'),
    'base'      => 'tooltip',
    'icon'      => 'citytours-js-composer',
    'class'     => '',
    'category'  => esc_html__('by SoapTheme', 'citytours'),
    'params'    => array(
        array(
            'type'          => 'textfield',
            'heading'       => esc_html__( 'Title', 'citytours' ),
            'param_name'    => 'title',
            "save_always"   => true,
            "admin_label"   => true,
        ),
        array(
            "type"          => "dropdown",
            "heading"       => esc_html__("Style", 'citytours'),
            "save_always"   => true,
            "admin_label"   => true,
            "param_name"    => "style",
            "value"         => array(
                __( "Simple", "citytours" )     => "",
                __( "Advanced", "citytours" )   => "advanced",
            ),
            "std"           => '',
            "description"   => ""
        ),
        array(
            "type"          => "dropdown",
            "heading"       => esc_html__("Tooltip Position", 'citytours'),
            "save_always"   => true,
            "admin_label"   => true,
            "param_name"    => "position",
            "value"         => array(
                __( "Top", "citytours" )    => "top",
                __( "Bottom", "citytours" ) => "bottom",
                __( "Left", "citytours" )   => "left",
                __( "Right", "citytours" )  => "right",
            ),
            "std"           => "top",
            "dependency"    => array(
                "element"       => "style",
                "value"         => array("")
            ),
        ),
        array(
            "type"          => "dropdown",
            "heading"       => esc_html__("Tooltip Effect", 'citytours'),
            "save_always"   => true,
            "admin_label"   => true,
            "param_name"    => "effect",
            "value"         => array(
                __( "fadeInDown", "citytours" ) => "1",
                __( "flipInX", "citytours" )    => "2",
                __( "twistUp", "citytours" )    => "3",
                __( "zoomIn", "citytours" )     => "4",
            ),
            "std"           => "top",
            "description"   => "",
            "dependency"    => array(
                "element"       => "style",
                "value"         => array("advanced")
            ),
        ),
        $content_area,
        $extra_class
    )
) );

/* PriceTable Shortcode */
vc_map( array(
    "name"      => esc_html__("Pricing Table", 'citytours'),
    "base"      => "pricing_table",
    "icon"      => "citytours-js-composer",
    "class"     => "",
    "category"  => esc_html__('by SoapTheme', 'citytours'),
    "params"    => array(
        array(
            "type"          => "dropdown",
            "heading"       => esc_html__("Style", 'citytours'),
            "save_always"   => true,
            "admin_label"   => true,
            "param_name"    => "style",
            "value"         => array(
                __( "Default", "citytours" ) => "",
                __( "Style2", "citytours" ) => "style2",
            ),
            "std"           => "",
            "description"   => ""
        ),
        array(
            "type"          => "dropdown",
            "heading"       => esc_html__("Is Featured", 'citytours'),
            "param_name"    => "is_featured",
            "value"         => array(
                __( "No", "citytours" )     => "",
                __( "Yes", "citytours" )    => "true",
            ),
            "std"           => "",
            "save_always"   => true,
            "admin_label"   => true,
        ),
        array(
            'type'          => 'textfield',
            'heading'       => esc_html__( 'Title', 'citytours' ),
            'param_name'    => 'title',
            "save_always"   => true,
            "admin_label"   => true,
        ),
        array(
            'type'          => 'textfield',
            'heading'       => esc_html__( 'Price', 'citytours' ),
            'param_name'    => 'price',
            'save_always'   => true,
            'admin_label'   => true,
        ),
        array(
            'type'          => 'textfield',
            'heading'       => esc_html__( 'Button Title', 'citytours' ),
            'param_name'    => 'btn_title',
            'save_always'   => true,
            'admin_label'   => true,
        ),
        array(
            'type'          => 'textfield',
            'heading'       => esc_html__( 'Button Link', 'citytours' ),
            'param_name'    => 'btn_url',
            'save_always'   => true,
            'admin_label'   => true,
        ),
        array(
            "type"          => "dropdown",
            "heading"       => esc_html__("Button Target", 'citytours'),
            "param_name"    => "btn_target",
            "value"         => array(
                "_self"         => "_self",
                "_blank"        => "_blank",
                "_top"          => "_top",
                "_parent"       => "_parent"
            ),
            "std"           => "_self",
            "save_always"   => true,
            "admin_label"   => true,
        ),
        array(
            "type"          => "dropdown",
            "heading"       => esc_html__("Button Color", 'citytours'),
            "param_name"    => "btn_color",
            "value"         => array(
                __( "Default", "citytours" ) => "",
                __( "Outline", "citytours" ) => "outline",
                __( "White", "citytours" ) => "white",
                __( "Green", "citytours" ) => "green",
            ),
            "std"           => "",
            "save_always"   => true,
            "admin_label"   => true,
        ),
        array(
            'type'          => 'textfield',
            'heading'       => esc_html__( 'Button Class', 'citytours' ),
            'param_name'    => 'btn_class',
            'save_always'   => true,
            'admin_label'   => true,
        ),
        array(
            'type'          => 'textfield',
            'heading'       => esc_html__( 'Ribbon Image Url', 'citytours' ),
            'param_name'    => 'ribbon_img_url',
            'save_always'   => true,
            'admin_label'   => true,
        ),
        $content_area,
        $extra_class
    )
) );

/* Review Shortcode */
vc_map( array(
    'name'      => esc_html__( 'Review', 'citytours' ),
    'base'      => 'review',
    'icon'      => 'citytours-js-composer',
    'class'     => '',
    'category'  => esc_html__( 'by SoapTheme', 'citytours' ),
    'params'    => array(
        array(
            'type'          => 'textfield',
            'heading'       => esc_html__( 'Reviewer Name', 'citytours' ),
            'param_name'    => 'name',
            'save_always'   => true,
            'admin_label'   => true,
        ),
        array(
            'type'          => 'textfield',
            'heading'       => esc_html__( 'Rating', 'citytours' ),
            'param_name'    => 'rating',
            'save_always'   => true,
            'admin_label'   => true,
        ),
        array(
            'type'          => 'textfield',
            'heading'       => esc_html__( 'Reviewer Image Url', 'citytours' ),
            'param_name'    => 'img_url',
            'save_always'   => true,
            'admin_label'   => true,
        ),
        $content_area,
        $extra_class
    )
) );

/* Parallax Block */
vc_map( array(
    'name'          => esc_html__( 'Parallax Block', 'citytours' ),
    'base'          => 'parallax_block',
    'icon'          => 'citytours-js-composer',
    'class'         => '',
    'is_container'  => true,
    'category'      => esc_html__( 'by SoapTheme', 'citytours' ),
    'params'        => array(
        array(
            'type'          => 'textfield',
            'heading'       => esc_html__( 'Background Image Url', 'citytours' ),
            'param_name'    => 'bg_image',
            'save_always'   => true,
            'admin_label'   => true,
        ),
        array(
            'type'          => 'textfield',
            'heading'       => esc_html__( 'Parallax Block Height', 'citytours' ),
            'param_name'    => 'height',
            'value'         => 470,
            'save_always'   => true,
            'admin_label'   => true,
        ),
        $extra_class
    ),
    'js_view' => 'VcColumnView'
) );

/* hotels Shortcode */
vc_map( array(
    'name'      => esc_html__( 'Hotels', 'citytours' ),
    'base'      => 'hotels',
    'icon'      => 'citytours-js-composer',
    'class'     => '',
    'category'  => esc_html__( 'by SoapTheme', 'citytours' ),
    'params'    => array(
        array(
            'type' => 'textfield',
            'heading' => esc_html__( 'Title', 'citytours' ),
            'param_name' => 'title',
            'save_always'   => true,
            'admin_label'   => true,
        ),
        array(
            "type" => "dropdown",
            "heading" => esc_html__("Type", 'citytours'),
            "param_name" => "type",
            "value" => array(
                __( "Latest Hotels", 'citytours' ) => "latest",
                __( "Popular Hotels", 'citytours' ) => "popular",
                __( "Featured Hotels", 'citytours' ) => "featured",
                __( "Hot Hotels", 'citytours' ) => "hot",
                __( "Selected Hotels", 'citytours' ) => "selected",
            ),
            "std" => '',
            "description" => "",
            'save_always'   => true,
            'admin_label'   => true,
        ),
        array(
            "type" => "dropdown",
            "heading" => esc_html__("Style", 'citytours'),
            "param_name" => "style",
            "value" => array(
                __( "Advanced", 'citytours' ) => "advanced",
                __( "Simple", 'citytours' ) => "simple"
            ),
            "std" => 'advanced',
            "description" => "",
            'save_always'   => true,
            'admin_label'   => true,
        ),
        array(
            "type" => "checkbox",
            "heading" => __("Show Map", 'trav'),
            "param_name" => "map",
            "value" => array(
                __( "Yes", "trav" )=> "true",
            ),
            "dependency" => array(
                "element" => "style",
                "value" => "advanced",
            ),
            "std" => '',
        ),
        array(
            'type' => 'textfield',
            'heading' => esc_html__( 'Count', 'citytours' ),
            'param_name' => 'count',
            'save_always'   => true,
            'admin_label'   => true,
        ),
        array(
            'type' => 'dropdown',
            'heading' => esc_html__( 'Count Per Row', 'citytours' ),
            'param_name' => 'count_per_row',
            "value" => array(
                __( "2", 'citytours' ) => 2,
                __( "3", 'citytours' ) => 3,
                __( "4", 'citytours' ) => 4,
            ),
            'save_always'   => true,
            'admin_label'   => true,
            "std" => '3',
        ),
        array(
            'type' => 'dropdown',
            'heading' => esc_html__( 'Districts', 'citytours' ),
            'param_name' => 'district',
            "dependency" => array(
                "element" => "type",
                "value" => array( "latest", "popular", "featured" )
            ),
            'save_always'   => true,
            'admin_label'   => true,
            "value" => $districts
        ),
        array(
            'type'          => 'autocomplete',
            'heading'       => esc_html__( 'Hotel IDs', 'citytours' ),
            'param_name'    => 'post_ids',
            'settings'      => array(
                'multiple' => true,
                'sortable' => true,
            ),
            'save_always'   => true,
            'admin_label'   => true,
            'dependency'    => array(
                'element' => 'type',
                'value'   => array( 'selected' )
            ),
        ),
        $extra_class
    )
) );

/* tours Shortcode */
vc_map( array(
    'name'      => esc_html__( 'Tours', 'citytours' ),
    'base'      => 'tours',
    'icon'      => 'citytours-js-composer',
    'class'     => '',
    'category'  => esc_html__( 'by SoapTheme', 'citytours' ),
    'params'    => array(
        array(
            'type' => 'textfield',
            'heading' => esc_html__( 'Title', 'citytours' ),
            'param_name' => 'title',
            'admin_label' => true
        ),
        array(
            "type" => "dropdown",
            "heading" => esc_html__("Style", 'citytours'),
            "param_name" => "style",
            "value" => array(
                __( "Advanced", 'citytours' ) => "advanced",
                __( "Simple", 'citytours' ) => "simple",
                __( "Simple2", 'citytours' ) => "simple2",
                __( "List", 'citytours' ) => "list",
            ),
            "std" => 'advanced',
            "description" => "",
            'save_always'   => true,
            'admin_label'   => true,
        ),
        array(
            "type" => "checkbox",
            "heading" => __("Show Map", 'trav'),
            "param_name" => "map",
            "value" => array(
                __( "Yes", "trav" )=> "true",
            ),
            "dependency" => array(
                "element" => "style",
                "value" => "advanced",
            ),
            "std" => '',
        ),
        array(
            "type" => "dropdown",
            "heading" => esc_html__("Type", 'citytours'),
            "param_name" => "type",
            "value" => array(
                __( "Latest Tours", 'citytours' ) => "latest",
                __( "Popular Tours", 'citytours' ) => "popular",
                __( "Featured Tours", 'citytours' ) => "featured",
                __( "Hot Tours", 'citytours' ) => "hot",
                __( "Selected Tours", 'citytours' ) => "selected",
            ),
            "std" => '',
            "description" => "",
            'admin_label' => true
        ),
        array(
            "type" => "dropdown",
            "heading" => esc_html__("Style", 'citytours'),
            "param_name" => "style",
            "value" => array(
                __( "Simple 1", 'citytours' ) => "simple",
                __( "Simple 2", 'citytours' ) => "simple2",
                __( "List", 'citytours' ) => "list",
                __( "Grid", 'citytours' ) => "advanced",
            ),
            "std" => 'advanced',
            'admin_label' => true
        ),
        array(
            'type' => 'textfield',
            'heading' => esc_html__( 'Count', 'citytours' ),
            'param_name' => 'count',
        ),
        array(
            'type' => 'dropdown',
            'heading' => esc_html__( 'Count Per Row', 'citytours' ),
            'param_name' => 'count_per_row',
            "value" => array(
                __( "2", 'citytours' ) => 2,
                __( "3", 'citytours' ) => 3,
                __( "4", 'citytours' ) => 4,
            ),
            "std" => '3',
        ),
        array(
            'type' => 'dropdown',
            'heading' => esc_html__( 'Tour Type', 'citytours' ),
            'param_name' => 'tour_type',
            "dependency" => array(
                "element" => "type",
                "value" => array( "latest", "popular", "featured" )
            ),
            "value" => $tour_types
        ),
        array(
            'type'          => 'autocomplete',
            'heading'       => esc_html__( 'Tour IDs', 'citytours' ),
            'param_name'    => 'post_ids',
            'settings'      => array(
                'multiple' => true,
                'sortable' => true,
            ),
            'save_always'   => true,
            'admin_label'   => true,
            "dependency"    => array(
                "element"   => "type",
                "value"     => array( "selected" )
            ),
        ),
        $extra_class
    )
) );

/* Timeline container Block */
vc_map( array(
    'name'              => esc_html__( 'Timeline Container', 'citytours' ),
    'base'              => 'timeline_container',
    'icon'              => 'citytours-js-composer',
    'class'             => '',
    'as_parent'         => array( 
        'only' => 'timeline' 
    ),
    'is_container'      => true,
    'category'          => esc_html__( 'by SoapTheme', 'citytours' ),
    'params'            => array(
        $extra_class
    ),
    'js_view'           => 'VcColumnView',
    'default_content'   => '[timeline][/timeline]'
) );

/* Tiemline Shortcode */
vc_map( array(
    'name'                      => esc_html__( 'Timeline', 'citytours' ),
    'base'                      => 'timeline',
    'icon'                      => 'citytours-js-composer',
    'allowed_container_element' => 'timeline_container',
    'class'                     => '',
    'category'                  => esc_html__( 'by SoapTheme', 'citytours' ),
    'params'                    => array(
        array(
            'type'          => 'textfield',
            'heading'       => esc_html__( 'Time', 'citytours' ),
            'param_name'    => 'time',
            'save_always'   => true,
            'admin_label'   => true,
        ),
        array(
            'type'          => 'textfield',
            'heading'       => esc_html__( 'Duration', 'citytours' ),
            'param_name'    => 'duration',
            'save_always'   => true,
            'admin_label'   => true,
        ),
        array(
            'type'          => 'textfield',
            'heading'       => esc_html__( 'Icon Class', 'citytours' ),
            'param_name'    => 'icon_class',
            'save_always'   => true,
            'admin_label'   => true,
        ),
        $content_area,
        $extra_class
    )
) );

/* Map Shortcode */
vc_map( array(
    'name'      => esc_html__('CityTours Map', 'citytours'),
    'base'      => 'map',
    'icon'      => 'citytours-js-composer',
    'class'     => '',
    'category'  => esc_html__('by SoapTheme', 'citytours'),
    'params'    => array(
        array(
            'type'          => 'textfield',
            'heading'       => esc_html__("Center Position", 'citytours'),
            'param_name'    => 'center',
            'value'         => '',
            'description'   => __( "Please input position of Hotel or Tour which you want to show on the center of Map. Or Leave this blank", 'citytours' ),
            'save_always'   => true,
            'admin_label'   => true,
        ),
        array(
            'type'          => 'autocomplete',
            'heading'       => esc_html__( 'Around Hotels or Tours', 'citytours' ),
            'param_name'    => 'related',
            'settings'      => array(
                'multiple'      => true,
                'min_length'    => 1,
                'groups'        => true,
                'unique_values' => true,
                'delay'         => 300,
                'auto_focus'    => true,
                'sortable'      => true,
            ),
            'save_always'   => true,
            'admin_label'   => true,
            'description'   => __( "Please choose the Hotels or Tours that you want to show on Map.", 'citytours' ),
        ),
        array(
            'type'          => 'textfield',
            'heading'       => esc_html__( 'Zoom', 'citytours' ),
            'param_name'    => 'zoom',
            'save_always'   => true,
            'admin_label'   => true,
            'value'         => '14'
        ),
        array(
            'type'          => 'dropdown',
            'heading'       => esc_html__( 'Show Map-Type Control Button', 'citytours' ),
            'param_name'    => 'maptypecontrol',
            'value'         => array(
                __( "Yes", 'citytours' ) => 'true',
                __( "No", 'citytours' ) => 'false',
            ),
            'save_always'   => true,
            'admin_label'   => true,
            'std'           => 'false',
        ),
        array(
            'type'          => 'dropdown',
            'heading'       => esc_html__( 'Map Type', 'citytours' ),
            'param_name'    => 'maptype',
            'dependency'    => array(
                "element" => "maptypecontrol",
                "value" => array( "false" )
            ),
            'value'         => array(
                __( "RoadMap", 'citytours' )    => 'RoadMap',
                __( "Satellite", 'citytours' )  => 'Satellite',
                __( "Hybrid", 'citytours' )     => 'Hybrid',
                __( "Terrain", 'citytours' )    => 'Terrain',
            ),
            'save_always'   => true,
            'admin_label'   => true,
            'std'           => 'RoadMap',
        ),
        $extra_class
    )
) );

/* FAQs Shortcode */
vc_map( array(
    "name"      => esc_html__("FAQs Block", 'citytours'),
    "base"      => "faqs",
    "icon"      => "citytours-js-composer",
    "class"     => "",
    "category"  => esc_html__('by SoapTheme', 'citytours'),
    "params"    => array(
        array(
            'type'          => 'dropdown',
            'heading'       => esc_html__( 'Show FAQs by Category', 'citytours' ),
            'param_name'    => 'show_cat_title',
            'value'         => array( 
                esc_html__( 'Yes', 'citytours' ) => 'true',
                esc_html__( 'No', 'citytours' ) => 'false',
            ),
            'save_always'   => true,
            'admin_label'   => true,
        ),
        array(
            'type'          => 'textfield',
            'heading'       => esc_html__( 'FAQs Count to show', 'citytours' ),
            'param_name'    => 'post_count',
            'save_always'   => true,
            'admin_label'   => true,
            'std'           => 12,
        ),
        array(
            'type'          => 'autocomplete',
            'heading'       => __( 'Category', 'citytours' ),
            'param_name'    => 'category',
            'settings'      => array(
                'multiple' => false,
                'sortable' => true,
            ),
            'save_always'   => true,
            'admin_label'   => true,
        ),
        array(
            'type'          => 'dropdown',
            'heading'       => esc_html__( 'OrderBy', 'citytours' ),
            'param_name'    => 'orderby',
            'value'         => array( 
                esc_html__( 'Title', 'citytours' )      => 'title',
                esc_html__( 'ID', 'citytours' )         => 'ID',
                esc_html__( 'Date', 'citytours' )       => 'date',
                esc_html__( 'Menu Order', 'citytours' ) => 'menu_order',
                esc_html__( 'Random', 'citytours' )     => 'rand',
            ),
            'save_always'   => true,
            'admin_label'   => true,
            'std'           => 'title',
        ),
        array(
            'type'          => 'dropdown',
            'heading'       => esc_html__( 'Order', 'citytours' ),
            'param_name'    => 'order',
            'value'         => array( 
                esc_html__( 'Ascending', 'citytours' )  => 'ASC',
                esc_html__( 'Descending', 'citytours' ) => 'DESC',
            ),
            'save_always'   => true,
            'admin_label'   => true,
            'std'           => 'ASC',
        ),
        $extra_class
    )
) );

/* Home blog section Shortcode */
vc_map( array(
    "name"      => esc_html__("Blog Block", 'citytours'),
    "base"      => "blog",
    "icon"      => "citytours-js-composer",
    "class"     => "",
    "category"  => esc_html__('by SoapTheme', 'citytours'),
    "params"    => array(
        array(
            "type" => "dropdown",
            "heading" => esc_html__("Style", 'citytours'),
            "param_name" => "style",
            "value" => array(
                __( "Simple", 'citytours' ) => "simple",
                __( "home", 'citytours' ) => "home",
            ),
            "std" => 'advanced',
            'admin_label' => true
        ),
        array(
            'type'          => 'textfield',
            'heading'       => esc_html__( 'Section title', 'citytours' ),
            'param_name'    => 'title',
            'save_always'   => true,
            'admin_label'   => true,
        ),
        array(
            'type'          => 'textfield',
            'heading'       => esc_html__( 'Post Count to show', 'citytours' ),
            'param_name'    => 'post_count',
            'save_always'   => true,
            'admin_label'   => true,
            'std'           => 12,
        ),
        array(
            'type'          => 'dropdown',
            'heading'       => esc_html__( 'order', 'citytours' ),
            'param_name'    => 'order',
            'value'         => array( 
                esc_html__( 'Ascending', 'citytours' )  => 'ASC',
                esc_html__( 'Descending', 'citytours' ) => 'DESC',
            ),
            'save_always'   => 'ASC',
            'admin_label'   => true,
            'std'           => 'ASC',
        ),
        array(
            'type'          => 'dropdown',
            'heading'       => esc_html__( 'paged', 'citytours' ),
            'param_name'    => 'paged',
            "value"         => array(
                __( "No", "citytours" )     => "",
                __( "Yes", "citytours" )    => "true",
            ),
            'save_always'   => false,
            'admin_label'   => true,
            'std'           => 'ASC',
        ),
        
    ),
    $extra_class
));

/* Accordion Shortcode */
vc_add_param( 'vc_accordion', array(
    'type'          => 'dropdown',
    'class'         => '',
    'heading'       => esc_html__('Toggle Type', 'citytours'),
    'admin_label'   => true,
    'param_name'    => 'toggle_type',
    'value'         => array(
        'Accordion' => 'accordion',
        'Toggle' => 'toggle'
    ),
    'std'           => 'accordion',
    'description'   => '',
));

vc_remove_param( 'vc_accordion', 'interval' );
vc_remove_param( 'vc_accordion', 'collapsible' );
vc_remove_param( 'vc_accordion', 'disable_keyboard' );

vc_map_update( 'vc_accordion', array(
    'is_container'  => false,
    'as_parent'     => array( 
        'only' => 'vc_accordion_tab' 
    ),
) );

/* Tabs */
vc_remove_param( 'vc_tabs', 'interval' );

vc_add_param( 'vc_tabs', array(
    'type'          => 'textfield',
    'heading'       => esc_html__( 'Active Tab Index', 'citytours' ),
    'param_name'    => 'active_tab_index',
    'value'         => '1'
));

if ( class_exists( 'WPBakeryShortCode' ) ) {
    if ( class_exists( 'WPBakeryShortCodesContainer' ) ) {
        class WPBakeryShortCode_Container extends WPBakeryShortCodesContainer {}
        class WPBakeryShortCode_Parallax_Block extends WPBakeryShortCodesContainer {}
        class WPBakeryShortCode_Timeline_Container extends WPBakeryShortCodesContainer {}
    }
    if ( class_exists( 'WPBakeryShortCode' ) ) {
        class WPBakeryShortCode_Timeline extends WPBakeryShortCode {}
    }
}


add_filter( 'vc_shortcodes_css_class', 'ct_vc_shortcode_css_class', 10, 3 );

// Replace rows and columns classes
function ct_vc_shortcode_css_class( $class_string, $tag, $atts ) {
    if ( $tag =='vc_row' || $tag =='vc_row_inner' ) {
        if ( strpos($class_string, 'inner-container') === false ) {
            $class_string = str_replace('vc_row-fluid', 'row', $class_string);
        }
    }
    if ( $tag == 'vc_row_inner' ) {
        if ( !empty( $atts['add_clearfix'] ) ) {
            $class_string .= ' add-clearfix';
        }
    }

    if ( $tag =='vc_column' || $tag =='vc_column_inner' ) {
        if ( !(function_exists('vc_is_inline') && vc_is_inline()) ) {
            if( preg_match('/vc_col-(\w{2})-(\d{1,2})/', $class_string) ) {
                $class_string = str_replace('vc_column_container', '', $class_string);
            }
            $class_string = preg_replace('/vc_col-(\w{2})-(\d{1,2})/', 'col-$1-$2', $class_string);
            $class_string = preg_replace('/vc_hidden-(\w{2})/', 'hidden-$1', $class_string);
        }
    }

    return $class_string;
}
