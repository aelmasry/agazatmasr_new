<?php
if ( ! defined( 'ABSPATH' ) )
	exit; // Exit if accessed directly

add_action('init', 'register_new_post_types', 0);
add_action('widgets_init', 'visa_register_sidebar');
add_filter('rwmb_meta_boxes', 'ct_visa_register_meta_boxes');

if ( ! function_exists( 'register_new_post_types' ) )
{
    function register_new_post_types()
    {
        register_visa_post_type();

        register_visa_category_taxonomy();

        register_countries_taxonomy();

        // register_visa_district_taxonomy();
    }
}
/*
 * Post Types for Visa
 * Register Visa Post Type
 */
if ( ! function_exists( 'register_visa_post_type' ) )
{
    function register_visa_post_type()
    {
        global $ct_options;

        $labels = array(
                'name' => _x('Visas', 'Post Type General Name', 'citytours'),
                'singular_name' => _x('Visa', 'Post Type Singular Name', 'citytours'),
                'menu_name' => __('Visas', 'citytours'),
                'all_items' => __('All Posts', 'citytours'),
                'view_item' => __('View Post', 'citytours'),
                'add_new_item' => __('Add New Post', 'citytours'),
                'add_new' => __('New Post', 'citytours'),
                'edit_item' => __('Edit Post', 'citytours'),
                'update_item' => __('Update Post', 'citytours'),
                'search_items' => __('Search Post', 'citytours'),
                'not_found' => __('No Visas found', 'citytours'),
                'not_found_in_trash' => __('No Visas found in Trash', 'citytours'),
        );

        $args = array(
            'label' => __('Visa', 'citytours'),
            'description' => __('Visa information pages', 'citytours'),
            'labels' => $labels,
            'supports' => array('title', 'editor', 'thumbnail', 'author','visa-category'),
            'taxonomies' => array(),
            'hierarchical' => true,
            'public' => true,
            'show_ui' => true,
            'rewrite'            =>  true,
            'query_var'          =>  true,
            'rewrite' => array("slug" => "visa"),
            'can_export' => true,
            'has_archive' => true,
            'exclude_from_search' => false,
            'publicly_queryable' => true,
        );

        if (!empty($ct_options['visa_permalink']))
        {
                $visa_permalink = $ct_options['visa_permalink'];
                $visa_permalink = str_replace(' ', '-', $visa_permalink);
                $visa_permalink = str_replace('_', '-', $visa_permalink);
                $visa_permalink = str_replace('/', '', $visa_permalink);

                if (!preg_match('/[^a-zA-Z0-9\/-]+/', $visa_permalink, $matches))
                {
                        $args['rewrite'] = array('slug' => $visa_permalink);
                }
        }

        register_post_type('visa', $args);
        flush_rewrite_rules(false);
    }
}

/*
 * Register Category taxonomy
 */
function register_visa_category_taxonomy()
{
    $labels = array(
            'name' => _x('Visa category', 'taxonomy general name', 'citytours'),
            'singular_name' => _x('Category', 'taxonomy singular name', 'citytours'),
            'menu_name' => __('Category', 'citytours'),
            'all_items' => __('All Visa category', 'citytours'),
            'parent_item' => __( 'Parent', 'textdomain' ),
            'parent_item_colon' => __( 'Parent:', 'textdomain' ),
            'new_item_name' => __('New category', 'citytours'),
            'add_new_item' => __('Add New category', 'citytours'),
            'edit_item' => __('Edit category', 'citytours'),
            'update_item' => __('Update category', 'citytours'),
            'separate_items_with_commas' => __('Separate category with commas', 'citytours'),
            'search_items' => __('Search category', 'citytours'),
            'add_or_remove_items' => __('Add or remove category', 'citytours'),
            'choose_from_most_used' => __('Choose from the most used category', 'citytours'),
            'not_found' => __('No category found.', 'citytours'),
    );

    $args = array(
            'labels' => $labels,
            'show_ui' => true,
            'show_admin_column' => true,
            'public' => true,
            'hierarchical' => true,
            'has_archive' => true,
            'rewrite' => array('slug' => 'visa-category'),
            'meta_box_cb' => false,
    );

    register_taxonomy('visa-category', array('visa'), $args);
}

/*
 * Register district taxonomy
 */
// function register_visa_district_taxonomy()
// {
//     $labels = array(
//         'name' => _x('Districts', 'taxonomy general name', 'citytours'),
//         'singular_name' => _x('Districts', 'taxonomy singular name', 'citytours'),
//         'menu_name' => __('Districts', 'citytours'),
//         'all_items' => __('All Visa district', 'citytours'),
//         'parent_item' => __('Parent', 'textdomain'),
//         'parent_item_colon' => __('Parent:', 'textdomain'),
//         'new_item_name' => __('New district', 'citytours'),
//         'add_new_item' => __('Add New district', 'citytours'),
//         'edit_item' => __('Edit district', 'citytours'),
//         'update_item' => __('Update district', 'citytours'),
//         'separate_items_with_commas' => __('Separate district with commas', 'citytours'),
//         'search_items' => __('Search district', 'citytours'),
//         'add_or_remove_items' => __('Add or remove district', 'citytours'),
//         'choose_from_most_used' => __('Choose from the most used district', 'citytours'),
//         'not_found' => __('No district found.', 'citytours'),
//     );

//     $args = array(
//         'labels' => $labels,
//         'show_ui' => true,
//         'show_admin_column' => true,
//         'public' => true,
//         'hierarchical' => true,
//         'has_archive' => true,
//         'rewrite' => array('slug' => 'visa_district'),
//         'meta_box_cb' => false,
//     );

//     register_taxonomy('visa_district', array('visa'), $args);
// }

/*
 * visa metabox registration
 */
if (!function_exists('ct_visa_register_meta_boxes'))
{

    function ct_visa_register_meta_boxes()
    {
        $meta_boxes = array();

        $prefix = '_visa_';

        // Details for 'Default Hotel Setting'.
        $meta_boxes[] = array(
            'id' => 'visa_default_settings',
            'title' => esc_html__('Default Visa Settings', 'citytours'),
            'pages' => array('visa'),
            'context' => 'normal',
            'priority' => 'high',
            'fields' => array(
                array(
                    'name' => esc_html__('categories', 'citytours'),
                    'id' => "{$prefix}category",
                    'desc' => esc_html__('Select an visa category', 'citytours'),
                    'placeholder' => esc_html__('Select an visa category', 'citytours'),
                    'type' => 'select_advanced',
                    'options' => ct_get_visa_category(),
                ),
                array(
                    'name' => esc_html__('city', 'citytours'),
                    'id' => "{$prefix}city",
                    'desc' => esc_html__('Select city', 'citytours'),
                    'placeholder' => esc_html__('Select an city', 'citytours'),
                    'type' => 'select',
                    'options' => ct_get_city_visa(),
                ),

                array(
                    'name' => esc_html__('Slider Content', 'citytours'),
                    'id' => "{$prefix}slider",
                    'desc' => esc_html__('Please write slider shortcode here.', 'citytours'),
                    'type' => 'text',
                ),

                array(
                    'name' => esc_html__('Related Hotels and Tours', 'citytours'),
                    'id' => "{$prefix}related",
                    'desc' => esc_html__('Select Hotels or Tours related to this Hotel', 'citytours'),
                    'placeholder' => esc_html__('Please type a name of Hotel or Tours', 'citytours'),
                    'type' => 'post',
                    'post_type' => array('visa', 'tour'),
                    'field_type' => 'select_advanced',
                    'multiple' => true,
                ),
            )
        );

        $meta_boxes[] = array(
            'id' => 'is_fixed_sidebar',
            'title' => esc_html__('Sidebar Setting', 'citytours'),
            'pages' => array('visa'),
            'context' => 'normal',
            'priority' => 'high',
            'fields' => array(
                array(
                    'name' => esc_html__('Fixed Sidebar', 'citytours'),
                    'id' => "{$prefix}fixed_sidebar",
                    'type' => 'checkbox',
                    'desc' => esc_html__('Is fixed sidebar?', 'citytours'),
                    'std' => 0,
                ),
            )
        );

        $meta_boxes = apply_filters('ct_visa_register_meta_boxes', $meta_boxes);

        return $meta_boxes;
    }

}

if(!function_exists('visa_register_sidebar')) {
    function visa_register_sidebar()
    {
        $args = array(
            'name' => esc_html__('Visa Sidebar', 'citytours'),
            'id' => 'sidebar-visa',
            'description' => __('This will be shown on the visa detail page', 'citytours'),
            'class' => '',
            'before_widget' => '<div id="%1$s" class="widget %2$s">',
            'after_widget' => '</div><hr>',
            'before_title' => '<h4 class="widgettitle">',
            'after_title' => '</h4>'
        );

        register_sidebar($args);
    }
}

if(!function_exists('register_countries_taxonomy')) {

/*
     * Register Countries and Cities taxonomy
     */
    function register_countries_taxonomy()
    {

        $labels = array(
            'name'                       => _x( 'Countries', 'Taxonomy General Name', 'text_domain' ),
            'singular_name'              => _x( 'Country', 'Taxonomy Singular Name', 'text_domain' ),
            'menu_name'                  => __( 'Countries & Cities', 'text_domain' ),
            'all_items'                  => __( 'All Items', 'text_domain' ),
            // 'parent_item'                => __( 'Parent Item', 'text_domain' ),
            // 'parent_item_colon'          => __( 'Parent Item:', 'text_domain' ),
            'new_item_name'              => __( 'New Item Name', 'text_domain' ),
            'add_new_item'               => __( 'Add New Item', 'text_domain' ),
            'edit_item'                  => __( 'Edit Item', 'text_domain' ),
            'update_item'                => __( 'Update Item', 'text_domain' ),
            'view_item'                  => __( 'View Item', 'text_domain' ),
            'separate_items_with_commas' => __( 'Separate items with commas', 'text_domain' ),
            'add_or_remove_items'        => __( 'Add or remove items', 'text_domain' ),
            'choose_from_most_used'      => __( 'Choose from the most used', 'text_domain' ),
            'popular_items'              => __( 'Popular Items', 'text_domain' ),
            'search_items'               => __( 'Search Items', 'text_domain' ),
            'not_found'                  => __( 'Not Found', 'text_domain' ),
            'no_terms'                   => __( 'No items', 'text_domain' ),
            'items_list'                 => __( 'Items list', 'text_domain' ),
            'items_list_navigation'      => __( 'Items list navigation', 'text_domain' ),
        );
        $args = array(
            'labels'                     => $labels,
            'hierarchical'               => true,
            'public'                     => true,
            'show_ui'                    => true,
            'show_admin_column'          => true,
            'show_in_nav_menus'          => true,
            'rewrite' => array('slug' => 'state', 'with_front' => true)
        );

        register_taxonomy( 'state', array( 'post' ), $args );
    }
}
/*
 * Register room types meta box on hotel page
 */
if (!function_exists('ct_get_visa_category'))
{

    function ct_get_visa_category()
    {
        $terms = get_terms('visa-category', array('hide_empty' => 0, 'parent'=> 0));
        foreach($terms as $term) {
            $object_terms[$term->term_id] = $term->name;
        }

        return  $object_terms;
    }

}

/*
 * Register room types meta box on hotel page
 */
if (!function_exists('ct_get_city_visa'))
{
    function ct_get_city_visa()
    {
        // Ensure we have a post ID
        $post_id = isset($_GET['post']) ? $_GET['post'] : (isset($_POST['post_ID']) ? $_POST['post_ID'] : null);

        $tour_type = get_post_meta($post_id,'_visa_category',TRUE);

        $terms = get_terms('visa-category', array('hide_empty' => 0, 'parent' => $tour_type));
        foreach($terms as $term) {
            $object_terms[$term->term_id] = $term->name;
        }

        return  $object_terms;
    }
}
