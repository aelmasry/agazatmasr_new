<?php
/**
  Plugin Name: CTBooking
  Plugin URI: http://www.soaptheme.net/ctbooking/
  Description: A booking system
  Version: 1.2
  Author: Soaptheme
  Author URI: http://www.soaptheme.net
  Text Domain: ct-booking
  Domain Path: /languages/
 */
if (!defined('ABSPATH'))
{
    exit;
}

class CT_Booking
{

    // Construction
    function __construct()
    {
        // load plugin text domain
        add_action('plugins_loaded', array($this, 'loadTextDomain'), 1);

        // Register post types
        add_action('init', array($this, 'addContentTypes'), 2);

        // add_action('admin_menu', array($this, 'hideNewHotelMenu'));
    }

    // Load plugin textdomain.
    function loadTextDomain()
    {
        load_plugin_textdomain('ct-booking', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }

    // hide Add Hotel Submenu on sidebar
    function hideNewHotelMenu()
    {
        if (current_user_can('manage_options'))
        {
            global $submenu;

			unset($submenu['edit.php?post_type=hotel'][10]);
        }
    }

    // Post Types for Hotel
    /*
     * Register Hotel Post Type
     */
    public function register_hotel_post_type()
    {
        global $ct_options;

        $labels = array(
            'name' => _x('Hotels', 'Post Type General Name', 'ct-booking'),
            'singular_name' => _x('Hotel', 'Post Type Singular Name', 'ct-booking'),
            'menu_name' => __('Hotels', 'ct-booking'),
            'all_items' => __('All Hotels', 'ct-booking'),
            'view_item' => __('View Hotel', 'ct-booking'),
            'add_new_item' => __('Add New Hotel', 'ct-booking'),
            'add_new' => __('New Hotel', 'ct-booking'),
            'edit_item' => __('Edit Hotels', 'ct-booking'),
            'update_item' => __('Update Hotels', 'ct-booking'),
            'search_items' => __('Search Hotels', 'ct-booking'),
            'not_found' => __('No Hotels found', 'ct-booking'),
            'not_found_in_trash' => __('No Hotels found in Trash', 'ct-booking'),
        );
        $args = array(
            'label' => __('hotel', 'ct-booking'),
            'description' => __('Hotel information pages', 'ct-booking'),
            'labels' => $labels,
            'supports' => array('title', 'editor', 'thumbnail', 'author'),
            'taxonomies' => array(),
            'hierarchical' => false,
            'public' => true,
            'can_export' => true,
            'has_archive' => true,
            'exclude_from_search' => false,
            'publicly_queryable' => true,
        );

        if (!empty($ct_options['hotel_permalink']))
        {
            $hotel_permalink = $ct_options['hotel_permalink'];
            $hotel_permalink = str_replace(' ', '-', $hotel_permalink);
            $hotel_permalink = str_replace('_', '-', $hotel_permalink);
            $hotel_permalink = str_replace('/', '', $hotel_permalink);

            if (!preg_match('/[^a-zA-Z0-9\/-]+/', $hotel_permalink, $matches))
            {
                $args['rewrite'] = array('slug' => $hotel_permalink);
            }
        }

        register_post_type('hotel', $args);
        flush_rewrite_rules(false);
    }

    /*
     * Register Room Post Type
     */
    public function register_room_type_post_type()
    {
        $labels = array(
            'name' => _x('Room Types', 'Post Type Name', 'ct-booking'),
            'singular_name' => _x('Room Type', 'Post Type Singular Name', 'ct-booking'),
            'menu_name' => __('Room Types', 'ct-booking'),
            'all_items' => __('All Room Types', 'ct-booking'),
            'view_item' => __('View Room Type', 'ct-booking'),
            'add_new_item' => __('Add New Room', 'ct-booking'),
            'add_new' => __('New Room Types', 'ct-booking'),
            'edit_item' => __('Edit Room Types', 'ct-booking'),
            'update_item' => __('Update Room Types', 'ct-booking'),
            'search_items' => __('Search Room Types', 'ct-booking'),
            'not_found' => __('No Room Types found', 'ct-booking'),
            'not_found_in_trash' => __('No Room Types found in Trash', 'ct-booking'),
        );
        $args = array(
            'label' => __('room types', 'ct-booking'),
            'description' => __('Room Type information pages', 'ct-booking'),
            'labels' => $labels,
            'supports' => array('title', 'editor', 'thumbnail', 'author'),
            'taxonomies' => array(),
            'hierarchical' => false,
            'public' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'show_in_nav_menus' => true,
            'show_in_admin_bar' => true,
            'can_export' => true,
            'has_archive' => false,
            'exclude_from_search' => false,
            'publicly_queryable' => true,
            'rewrite' => array('slug' => 'room-type', 'with_front' => true)
        );

        if (current_user_can('manage_options'))
        {
            $args['show_in_menu'] = 'edit.php?post_type=hotel';
        }

        register_post_type('room_type', $args);
    }

    /*
     * Register District taxonomy
     */

    public function register_hotel_district_taxonomy()
    {
        $labels = array(
            'name' => _x('Districts', 'taxonomy general name', 'ct-booking'),
            'singular_name' => _x('District', 'taxonomy singular name', 'ct-booking'),
            'menu_name' => __('Districts', 'ct-booking'),
            'all_items' => __('All Districts', 'ct-booking'),
            'parent_item' => null,
            'parent_item_colon' => null,
            'new_item_name' => __('New District', 'ct-booking'),
            'add_new_item' => __('Add New District', 'ct-booking'),
            'edit_item' => __('Edit District', 'ct-booking'),
            'update_item' => __('Update District', 'ct-booking'),
            'separate_items_with_commas' => __('Separate Districts with commas', 'ct-booking'),
            'search_items' => __('Search Districts', 'ct-booking'),
            'add_or_remove_items' => __('Add or remove Districts', 'ct-booking'),
            'choose_from_most_used' => __('Choose from the most used Districts', 'ct-booking'),
            'not_found' => __('No Districts found.', 'ct-booking'),
        );

        $args = array(
            'labels' => $labels,
            'show_ui' => true,
            'show_admin_column' => true,
            'public' => true,
            'hierarchical' => true,
            'has_archive' => true,
            'rewrite' => array('slug' => 'district'),
            'meta_box_cb' => false,
        );

        register_taxonomy('district', array('hotel'), $args);
    }

    /*
     * Register Hotel Facility taxonomy
     */

    public function register_hotel_facility_taxonomy()
    {
        $labels = array(
            'name' => _x('Hotel Facilities', 'taxonomy general name', 'ct-booking'),
            'singular_name' => _x('Hotel Facility', 'taxonomy singular name', 'ct-booking'),
            'menu_name' => __('Hotel Facilities', 'ct-booking'),
            'all_items' => __('All Hotel Facilities', 'ct-booking'),
            'parent_item' => null,
            'parent_item_colon' => null,
            'new_item_name' => __('New Hotel Facility', 'ct-booking'),
            'add_new_item' => __('Add New Hotel Facility', 'ct-booking'),
            'edit_item' => __('Edit Hotel Facility', 'ct-booking'),
            'update_item' => __('Update Hotel Facility', 'ct-booking'),
            'separate_items_with_commas' => __('Separate hotel facilities with commas', 'ct-booking'),
            'search_items' => __('Search Hotel Facilities', 'ct-booking'),
            'add_or_remove_items' => __('Add or remove hotel facilities', 'ct-booking'),
            'choose_from_most_used' => __('Choose from the most used hotel facilities', 'ct-booking'),
            'not_found' => __('No hotel facilities found.', 'ct-booking'),
        );
        $args = array(
            'labels' => $labels,
            'hierarchical' => false,
            'show_ui' => true,
            'show_admin_column' => true,
            'meta_box_cb' => false
        );

        register_taxonomy('hotel_facility', array('room_type', 'hotel'), $args);
    }

    // Post Types for Tour
    /*
     * Register Tour Post Type
     */
    public function register_tour_post_type()
    {
        global $ct_options;

        $labels = array(
            'name' => _x('Tours', 'Post Type General Name', 'ct-booking'),
            'singular_name' => _x('Tour', 'Post Type Singular Name', 'ct-booking'),
            'menu_name' => __('Tours', 'ct-booking'),
            'all_items' => __('All Tours', 'ct-booking'),
            'view_item' => __('View Tour', 'ct-booking'),
            'add_new_item' => __('Add New Tour', 'ct-booking'),
            'add_new' => __('New Tour', 'ct-booking'),
            'edit_item' => __('Edit Tours', 'ct-booking'),
            'update_item' => __('Update Tours', 'ct-booking'),
            'search_items' => __('Search Tours', 'ct-booking'),
            'not_found' => __('No Tours found', 'ct-booking'),
            'not_found_in_trash' => __('No Tours found in Trash', 'ct-booking'),
        );
        $args = array(
            'label' => __('tour', 'ct-booking'),
            'description' => __('Tour information pages', 'ct-booking'),
            'labels' => $labels,
            'supports' => array('title', 'editor', 'thumbnail', 'author'),
            'taxonomies' => array(),
            'hierarchical' => false,
            'show_in_menu' => true,
            'public' => true,
            'show_in_nav_menus' => true,
            'can_export' => true,
            'has_archive' => true,
            'exclude_from_search' => false,
            'publicly_queryable' => true,
        );

        if (!empty($ct_options['tour_permalink']))
        {
            $tour_permalink = $ct_options['tour_permalink'];
            $tour_permalink = str_replace(' ', '-', $tour_permalink);
            $tour_permalink = str_replace('_', '-', $tour_permalink);
            $tour_permalink = str_replace('/', '', $tour_permalink);

            if (!preg_match('/[^a-zA-Z0-9\/-]+/', $tour_permalink, $matches))
            {
                $args['rewrite'] = array('slug' => $tour_permalink);
            }
        }

        register_post_type('tour', $args);
        flush_rewrite_rules(false);
    }

    /*
     * Register Tour Type taxonomy
     */

    public function register_tour_type_taxonomy()
    {
        $labels = array(
            'name' => _x('Tour Types', 'taxonomy general name', 'ct-booking'),
            'singular_name' => _x('Tour Type', 'taxonomy singular name', 'ct-booking'),
            'menu_name' => __('Tour Types', 'ct-booking'),
            'all_items' => __('All Tour Types', 'ct-booking'),
            'parent_item' => null,
            'parent_item_colon' => null,
            'new_item_name' => __('New Tour Type', 'ct-booking'),
            'add_new_item' => __('Add New Tour Type', 'ct-booking'),
            'edit_item' => __('Edit Tour Type', 'ct-booking'),
            'update_item' => __('Update Tour Type', 'ct-booking'),
            'separate_items_with_commas' => __('Separate tour types with commas', 'ct-booking'),
            'search_items' => __('Search Tour Types', 'ct-booking'),
            'add_or_remove_items' => __('Add or remove tour types', 'ct-booking'),
            'choose_from_most_used' => __('Choose from the most used tour types', 'ct-booking'),
            'not_found' => __('No tour types found.', 'ct-booking'),
        );
        $args = array(
            'labels' => $labels,
            'hierarchical' => true,
            'show_in_menu' => true,
            // 'show_in_nav_menus' => true,
            'show_ui' => true,
            'public' => true,
            'has_archive' => true,
            'show_admin_column' => true,
            'meta_box_cb' => false,
            'rewrite' => array('slug' => 'tour-type', 'with_front' => true)
        );

        register_taxonomy('tour_type', array('tour'), $args);
    }


    /*
     * Register District taxonomy
     */
    public function register_tour_district_taxonomy()
    {
        $labels = array(
            'name' => _x('Districts', 'taxonomy general name', 'ct-booking'),
            'singular_name' => _x('District', 'taxonomy singular name', 'ct-booking'),
            'menu_name' => __('Districts', 'ct-booking'),
            'all_items' => __('All Districts', 'ct-booking'),
            'parent_item' => null,
            'parent_item_colon' => null,
            'new_item_name' => __('New District', 'ct-booking'),
            'add_new_item' => __('Add New District', 'ct-booking'),
            'edit_item' => __('Edit District', 'ct-booking'),
            'update_item' => __('Update District', 'ct-booking'),
            'separate_items_with_commas' => __('Separate Districts with commas', 'ct-booking'),
            'search_items' => __('Search Districts', 'ct-booking'),
            'add_or_remove_items' => __('Add or remove Districts', 'ct-booking'),
            'choose_from_most_used' => __('Choose from the most used Districts', 'ct-booking'),
            'not_found' => __('No Districts found.', 'ct-booking'),
        );

        $args = array (
                'labels' => $labels,
                'show_ui' => true,
                'show_admin_column' => true,
                'public' => true,
                'hierarchical' => true,
                'has_archive' => true,
                'rewrite' => array('slug' => 'tour_district'),
                'meta_box_cb' => false,
            );

        register_taxonomy('tour_district', array('tour'), $args);
    }

    /*
     * Register Tour Facility taxonomy
     */

    public function register_tour_facility_taxonomy()
    {
        $labels = array(
            'name' => _x('Tour Facilities', 'taxonomy general name', 'ct-booking'),
            'singular_name' => _x('Tour Facility', 'taxonomy singular name', 'ct-booking'),
            'menu_name' => __('Tour Facilities', 'ct-booking'),
            'all_items' => __('All Tour Facilities', 'ct-booking'),
            'parent_item' => null,
            'parent_item_colon' => null,
            'new_item_name' => __('New Tour Facility', 'ct-booking'),
            'add_new_item' => __('Add New Tour Facility', 'ct-booking'),
            'edit_item' => __('Edit Tour Facility', 'ct-booking'),
            'update_item' => __('Update Tour Facility', 'ct-booking'),
            'separate_items_with_commas' => __('Separate tour facilities with commas', 'ct-booking'),
            'search_items' => __('Search Tour Facilities', 'ct-booking'),
            'add_or_remove_items' => __('Add or remove tour facilities', 'ct-booking'),
            'choose_from_most_used' => __('Choose from the most used tour facilities', 'ct-booking'),
            'not_found' => __('No tour facilities found.', 'ct-booking'),
        );
        $args = array(
            'labels' => $labels,
            'hierarchical' => false,
            'show_ui' => true,
            'show_admin_column' => true,
            'meta_box_cb' => false
        );

        register_taxonomy('tour_facility', array('tour'), $args);
    }

    // Custom Post Type for FAQ
    /*
     * register FAQ Post Type
     */
    public function register_faq_post_type()
    {
        $labels = array(
            'name' => _x('FAQs', 'Post Type General Name', 'ct-booking'),
            'singular_name' => _x('FAQ', 'Post Type Singular Name', 'ct-booking'),
            'menu_name' => __('FAQs', 'ct-booking'),
            'all_items' => __('All FAQs', 'ct-booking'),
            'view_item' => __('View FAQ', 'ct-booking'),
            'add_new_item' => __('Add New FAQ', 'ct-booking'),
            'add_new' => __('New FAQ', 'ct-booking'),
            'edit_item' => __('Edit FAQs', 'ct-booking'),
            'update_item' => __('Update FAQs', 'ct-booking'),
            'search_items' => __('Search FAQs', 'ct-booking'),
            'not_found' => __('No FAQs found', 'ct-booking'),
            'not_found_in_trash' => __('No FAQs found in Trash', 'ct-booking'),
            'attributes' => __('FAQ Attributes', 'ct-booking'),
        );
        $args = array(
            'labels' => $labels,
            'description' => __('Tour information pages', 'ct-booking'),
            'supports' => array('title', 'editor', 'page-attributes'),
            'public' => true,
            'can_export' => true,
            'has_archive' => true,
            'publicly_queryable' => true,
        );

        register_post_type('faq', $args);
    }

    /*
     * Register FAQ Category taxonomy
     */
    public function register_faq_cat_taxonomy()
    {
        $labels = array(
            'name' => _x('FAQ Categories', 'taxonomy general name', 'ct-booking'),
            'singular_name' => _x('FAQ Category', 'taxonomy singular name', 'ct-booking'),
            'menu_name' => __('FAQ Categories', 'ct-booking'),
            'all_items' => __('All FAQ Categories', 'ct-booking'),
            'parent_item' => null,
            'parent_item_colon' => null,
            'new_item_name' => __('New FAQ Category', 'ct-booking'),
            'add_new_item' => __('Add New FAQ Category', 'ct-booking'),
            'edit_item' => __('Edit FAQ Category', 'ct-booking'),
            'update_item' => __('Update FAQ Category', 'ct-booking'),
            'separate_items_with_commas' => __('Separate FAQ Categories with commas', 'ct-booking'),
            'search_items' => __('Search FAQ Categories', 'ct-booking'),
            'add_or_remove_items' => __('Add or remove FAQ Categories', 'ct-booking'),
            'choose_from_most_used' => __('Choose from the most used FAQ Categories', 'ct-booking'),
            'not_found' => __('No FAQ Categories found.', 'ct-booking'),
        );
        $args = array(
            'labels' => $labels,
            'hierarchical' => true,
            'show_ui' => true,
            'show_admin_column' => true,
            'rewrite' => array('slug' => 'faq-cat', 'with_front' => true)
        );

        register_taxonomy('faq_cat', array('faq'), $args);
    }

    // Init Custom Post Types
    function addContentTypes()
    {
        global $ct_options;

        $this->register_faq_post_type();
        $this->register_faq_cat_taxonomy();

        if (empty($ct_options['disable_hotel']))
        {
            $this->register_hotel_post_type();
            $this->register_room_type_post_type();
            $this->register_hotel_district_taxonomy();
            $this->register_hotel_facility_taxonomy();
        }

        if (empty($ct_options['disable_tour']))
        {
            $this->register_tour_post_type();
            $this->register_tour_type_taxonomy();
            // $this->register_tour_district_taxonomy();
            $this->register_tour_facility_taxonomy();
        }
    }




}

// Initilize CT_Booking plugin
new CT_Booking();
