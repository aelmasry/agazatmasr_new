<?php

if ( ! class_exists( 'Redux' ) ) {
    return;
}

$options_pages = array();
$options_pages_obj = get_pages('sort_column=post_parent,menu_order');
$options_pages[''] = 'Select a page:';
foreach ($options_pages_obj as $_page) {
    $options_pages[$_page->ID] = $_page->post_title;
}

$opt_name = "citytours";
$theme = wp_get_theme(); // For use with some settings. Not necessary.

$args = array(
    'opt_name'             => $opt_name,
    'disable_tracking'     => true,
    'display_name'         => $theme->get( 'Name' ),
    'display_version'      => $theme->get( 'Version' ),
    'menu_type'            => 'submenu',
    'allow_sub_menu'       => false,
    'menu_title'           => __( 'Theme Options', 'citytours' ),
    'page_title'           => __( 'CityTours Theme Options', 'citytours' ),
    'google_api_key'       => '',
    'google_update_weekly' => false,
    'async_typography'     => true,
    //'disable_google_fonts_link' => true,                    // Disable this in case you want to create your own google fonts loader
    'admin_bar'            => false,
    'admin_bar_icon'       => 'dashicons-portfolio',
    'admin_bar_priority'   => 50,
    'global_variable'      => 'ct_options',
    'dev_mode'             => false,
    'update_notice'        => false,
    'customizer'           => true,
    //'open_expanded'     => true,                    // Allow you to start the panel in an expanded way initially.
    //'disable_save_warn' => true,                    // Disable the save warning when a user changes a field
    'page_priority'        => null,
    'page_parent'          => 'citytours',
    'page_permissions'     => 'manage_options',
    'menu_icon'            => '',
    'last_tab'             => '',
    'page_icon'            => 'icon-themes',
    'page_slug'            => 'theme_options',
    'save_defaults'        => true,
    'default_show'         => false,
    'default_mark'         => '',
    'show_import_export'   => true,
    'transient_time'       => 60 * MINUTE_IN_SECONDS,
    'output'               => true,
    'output_tag'           => true,
    // 'footer_credit'     => '',                   // Disable the footer credit of Redux. Please leave if you can help it.
    'database'             => '',
    'system_info'          => false,
    //'compiler'             => true,
    'hints'                => array(
        'icon'          => 'el el-question-sign',
        'icon_position' => 'right',
        'icon_color'    => 'lightgray',
        'icon_size'     => 'normal',
        'tip_style'     => array(
            'color'   => 'red',
            'shadow'  => true,
            'rounded' => false,
            'style'   => '',
        ),
        'tip_position'  => array(
            'my' => 'top left',
            'at' => 'bottom right',
        ),
        'tip_effect'    => array(
            'show' => array(
                'effect'   => 'slide',
                'duration' => '500',
                'event'    => 'mouseover',
            ),
            'hide' => array(
                'effect'   => 'slide',
                'duration' => '500',
                'event'    => 'click mouseleave',
            ),
        ),
    )
);

$args['share_icons'][] = array(
    'url'   => 'http://twitter.com/soaptheme',
    'title' => 'Follow us on Twitter',
    'icon'  => 'el el-twitter'
);

$args['intro_text'] = '';
$args['footer_text'] = '&copy; 2015 CityTours';

Redux::setArgs( $opt_name, $args );

$tabs = array(
    array(
        'id'      => 'redux-help-tab-1',
        'title'   => __( 'Theme Information', 'citytours' ),
        'content' => __( '<p>If you have any question please check documentation <a href="http://soaptheme.net/document/citytours-wp/">Documentation</a>. And that are beyond the scope of documentation, please feel free to contact us.</p>', 'citytours' )
    ),
);
Redux::setHelpTab( $opt_name, $tabs );

// Set the help sidebar
$content = __( '<p></p>', 'citytours' );
Redux::setHelpSidebar( $opt_name, $content );

Redux::setSection( $opt_name, array(
    'title'     => __( 'Basic Settings', 'citytours' ),
    'id'        => 'basic-settings',
    'icon'      => 'el el-home',
    'fields'    => array(
        array(
            'id'         => 'skin',
            'type'       => 'image_select',
            'title'      => __( 'Site Skin', 'citytours' ), 
            'subtitle'   => __( 'Select a Site Skin', 'citytours' ),
            'options'    => array(
                'red'      => array(
                    'alt'   => 'red',
                    'title' => 'red',
                    'img'   => CT_IMAGE_URL . '/admin/skin/red.jpg'
                ),
                'aqua'      => array(
                    'alt'   => 'aqua',
                    'title' => 'aqua',
                    'img'   => CT_IMAGE_URL . '/admin/skin/aqua.jpg'
                ),
                'green'      => array(
                    'alt'   => 'green',
                    'title' => 'green',
                    'img'   => CT_IMAGE_URL . '/admin/skin/green.jpg'
                ),
                'orange'      => array(
                    'alt'   => 'orange',
                    'title' => 'orange',
                    'img'   => CT_IMAGE_URL . '/admin/skin/orange.jpg'
                ),
            ),
            'default'    => 'red'
        ),
        array(
            'id'       => 'copyright',
            'type'     => 'text',
            'title'    => __( 'Copyright Text', 'citytours' ),
            'subtitle' => __( 'Set copyright text in footer', 'citytours' ),
            'desc'     => __( 'A year written in this text, such as 2026, is replaced with the current year.', 'citytours' ),
            'default'  => 'Citytours 2015',
        ),
        array(
            'id'       => 'email',
            'type'     => 'text',
            'title'    => __('E-Mail of admin', 'citytours'),
            'subtitle' => __( 'Set email address', 'citytours' ),
            'default'  => '',
            'validate' => 'email'
        ),
        array(
            'id'       => 'inquiry_email',
            'type'     => 'text',
            'title'    => __('E-Mail of inquiry', 'citytours'),
            'subtitle' => __( 'Set email address', 'citytours' ),
            'default'  => '',
            'validate' => 'email'
        ),
        array(
            'id'       => 'phone_no',
            'type'     => 'text',
            'title'    => __('Phone Number', 'citytours'),
            'subtitle' => __( 'Set phone number', 'citytours' ),
            'desc'     => __('Leave blank to hide phone number field', 'citytours'),
            'default'  => '',
        ),
        array(
            'id'       => 'work_time',
            'type'     => 'text',
            'title'    => __('Work time', 'citytours'),
            'subtitle' => __( 'Set work time', 'citytours' ),
            'desc'     => __('Leave blank to hide work time field', 'citytours'),
            'default'  => '',
        ),
        array(
            'id'       => 'map_api_key',
            'type'     => 'text',
            'title'    => __('Google Map API Key', 'trav'),
            'subtitle' => __( 'Input API key to show Google Maps', 'trav' ),
            'desc'     => __('If you don\'t have Map API key, you can get from <a href="https://developers.google.com/maps/documentation/javascript/get-api-key#get-an-api-key">Here</a>', 'trav'),
            'default'  => '',
        ),
        array(
            'id'       => 'modal_login',
            'type'     => 'switch',
            'title'    => __('Modal Login/Sign Up', 'citytours'),
            'subtitle' => __('Enable modal login and modal signup.', 'citytours'),
            'default'  => true,
        ),
        /*
        array(
            'id'       => 'sticky_menu',
            'type'     => 'switch',
            'title'    => __( 'Sticky Menu', 'citytours' ),
            'subtitle' => __( 'Enable Sticky Menu', 'citytours' ),
            'default'  => true,
        ),*/
        array(
            'id'       => 'preload',
            'type'     => 'switch',
            'title'    => __( 'Page Preloader', 'citytours' ),
            'subtitle' => __( 'Enable Page Preloader', 'citytours' ),
            'default'  => true,
        ),
        array(
            'id'         => 'info',
            'type'       => 'info',
            'raw'        => '<h3 style="margin: 10px 0; color: red;">' . __( 'Define Custom Styles', 'citytours' ) . '</h3>'
        ),
        array(
            'id'         => 'enable-custom-styles',
            'type'       => 'switch',
            'title'      => __( 'Enable Custom Styles', 'citytours' ),
            'desc'       => __( 'Enable this Option If you want to define custom styles like font, color, etc...', 'citytours' ), 
            'default'    => false,
        ),
        array(
            'id'         => 'body-font',
            'type'       => 'typography',
            'title'      => __( 'Body Font', 'citytours' ),
            'google'     => true,
            'subsets'    => false,
            'text-align' => false,
            'default'    => array(
                'color'         => "#565a5c",
                'google'        => true,
                'font-weight'   => '400',
                'font-family'   => 'Montserrat',
                'font-size'     => '12px',
                'line-height'   => '20px'
            ),
            'preview'    => array(
                'text'          => __( '"CityTours" is Awesome!!!', 'citytours' )
            ),
            'required'   => array( 'enable-custom-styles', '=', '1' )
        ),
        array(
            'id'    => 'body-background',
            'type'  => 'background',
            'title' => __( 'Body Background', 'citytours' ),
            'required'   => array( 'enable-custom-styles', '=', '1' )
        ),
        array(
            'id'        => 'header-bg',
            'type'      => 'color',
            'title'     => __( 'Header Background Color', 'citytours' ),
            'default'   => 'transparent',
            'validate'  => 'color',
            'required'   => array( 'enable-custom-styles', '=', '1' )
        ),
        array(
            'id'        => 'header-sticky-bg',
            'type'      => 'color',
            'title'     => __( 'Sticky Header Background Color', 'citytours' ),
            'default'   => '#fff',
            'validate'  => 'color',
            'required'   => array( 'enable-custom-styles', '=', '1' )
        ),
        array(
            'id'         => 'menu-font',
            'type'       => 'typography',
            'title'      => __( 'Menu Font', 'citytours' ),
            'google'     => true,
            'subsets'    => false,
            'text-align' => false,
            'color'      => false,
            'default'    => array(
                'google'        => true,
                'font-weight'   => '400',
                'font-family'   => 'Montserrat',
                'font-size'     => '13px',
                'line-height'   => '20px'
            ),
            'preview'    => array(
                'text'          => __( '"CityTours" is Awesome!!!', 'citytours' )
            ),
            'required'   => array( 'enable-custom-styles', '=', '1' )
        ),
        array(
            'id'        => 'menu-font-color',
            'type'      => 'link_color',
            'active'    => false,
            'title'     => __( 'Menu Font Color', 'citytours' ),
            'default'   => array(
                'regular'   => '#ffffff',
                'hover'     => '#e04f67',
            ),
            'required'   => array( 'enable-custom-styles', '=', '1' )
        ),
        array(
            'id'         => 'menu-popup-font',
            'type'       => 'typography',
            'title'      => __( 'Menu Popup Font', 'citytours' ),
            'google'     => true,
            'subsets'    => false,
            'text-align' => false,
            'color'      => false,
            'default'    => array(
                'google'        => true,
                'font-weight'   => '400',
                'font-family'   => 'Montserrat',
                'font-size'     => '13px',
                'line-height'   => '20px'
            ),
            'preview'    => array(
                'text'          => __( '"CityTours" is Awesome!!!', 'citytours' )
            ),
            'required'   => array( 'enable-custom-styles', '=', '1' )
        ),
        array(
            'id'        => 'menu-popup-font-color',
            'type'      => 'link_color',
            'active'    => false,
            'title'     => __( 'Menu Popup Font Color', 'citytours' ),
            'default'   => array(
                'regular'   => '#666666',
                'hover'     => '#e04f67',
            ),
            'required'   => array( 'enable-custom-styles', '=', '1' )
        ),
        array(
            'id'         => 'menu-sticky-font',
            'type'       => 'typography',
            'title'      => __( 'Menu Font in Sticky Header', 'citytours' ),
            'subtitle'   => __( 'This Setting will be used on Mobile menu', 'citytours' ),
            'google'     => true,
            'subsets'    => false,
            'text-align' => false,
            'color'      => false,
            'default'    => array(
                'google'        => true,
                'font-weight'   => '400',
                'font-family'   => 'Montserrat',
                'font-size'     => '13px',
                'line-height'   => '20px'
            ),
            'preview'    => array(
                'text'          => __( '"CityTours" is Awesome!!!', 'citytours' )
            ),
            'required'   => array( 'enable-custom-styles', '=', '1' )
        ),
        array(
            'id'        => 'menu-sticky-font-color',
            'type'      => 'link_color',
            'active'    => false,
            'title'     => __( 'Menu Font Color in Sticky Header', 'citytours' ),
            'default'   => array(
                'regular'   => '#333333',
                'hover'     => '#e04f67',
            ),
            'required'   => array( 'enable-custom-styles', '=', '1' )
        ),
    )
) );

Redux::setSection( $opt_name, array(
    'title'     => __( 'Header Style', 'citytours' ),
    'id'        => 'header-settings',
    'fields'    => array(
        array(
            'id'       => 'header_style',
            'type'     => 'image_select',
            'title'    => __('Header Style', 'citytours'), 
            'subtitle' => __('Select header style', 'citytours'),
            'options'  => array(
                'transparent'   => array(
                    'alt'       => 'transparent', 
                    'img'       => CT_IMAGE_URL . '/admin/header/transparent.jpg'
                ),
                'plain'         => array(
                    'alt'       => 'plain', 
                    'img'       => CT_IMAGE_URL . '/admin/header/plain.jpg'
                ),
            ),
            'default' => 'transparent'
        ),
        array(
            'id'        => 'header_sticky',
            'type'      => 'switch',
            'title'     => __('Sticky Header', 'citytours'),
            'desc'      => __('You can enable/disable Sticky header here.', 'citytours'),
            'default'   => true,
            'on'        => __('Enable', 'citytours'),
            'off'       => __('Disable', 'citytours'),
        ),
        array(
            'id'       => 'header_img',
            'type'     => 'media',
            'url'      => true,
            'title'    => __( 'Default Header Image', 'citytours' ),
            'subtitle' => __( 'Set a default image file for your header.', 'citytours' ),
            'desc'           => __( 'You can override this setting by using Header Image Settings metabox in post edit panel.', 'citytours' ),
            'default'  => array( 'url' => CT_IMAGE_URL . "/header-img.jpg" ),
        ),
        array(
            'id'             => 'header_img_height',
            'type'           => 'dimensions',
            'units'          => 'px',    // You can specify a unit value. Possible: px, em, %
            'units_extended' => 'false',  // Allow users to select any type of unit
            'title'          => __( 'Default Header Image Height', 'citytours' ),
            'subtitle'  => __( 'Set default height of header image', 'citytours' ),
            'desc'           => __( 'You can override this setting by using Header Image Settings metabox in post edit panel.', 'citytours' ),
            'width'         => false,
            'default'        => array( 'height' => '500')
        ),
        array(
            'id'        => 'header_breadcrumb',
            'type'      => 'switch',
            'title'     => __('Header Breadcrumb', 'citytours'),
            'desc'      => __('You can enable/disable breadcrumb here.', 'citytours'),
            'default'   => true,
            'on'        => __('Show', 'citytours'),
            'off'       => __('Hide', 'citytours'),
        ),
    )
) );

Redux::setSection( $opt_name, array(
    'title'      => __( 'Logo & Favicon', 'citytours' ),
    'icon'       => 'el el-angle-right',
    'subsection' => true,
    'fields'     => array(
        array(
            'id'       => 'favicon',
            'type'     => 'media',
            'url'      => true,
            'title'    => __( 'Favicon', 'citytours' ),
            'desc'     => '',
            'subtitle' => __( 'Set a 16x16 ico image for your favicon', 'citytours' ),
            'default'  => array( 'url' => CT_IMAGE_URL . "/favicon.ico" ),
        ),
        array(
            'id'       => 'logo',
            'type'     => 'media',
            'url'      => true,
            'title'    => __( 'Logo Image', 'citytours' ),
            'desc'     => '',
            'subtitle' => __( 'Set an image file for your logo', 'citytours' ),
            'default'  => array( 'url' => CT_IMAGE_URL . "/logo.png" ),
        ),
        array(
            'id'       => 'logo_sticky',
            'type'     => 'media',
            'url'      => true,
            'title'    => __( 'Logo Image In Sticky Menu Bar', 'citytours' ),
            'desc'     => '',
            'subtitle' => __( 'Set an image file for your sticky logo', 'citytours' ),
            'default'  => array( 'url' => CT_IMAGE_URL . "/logo_sticky.png" ),
        ),
        array(
            'id'             => 'logo_size_header',
            'type'           => 'dimensions',
            'units'          => 'px',    // You can specify a unit value. Possible: px, em, %
            'units_extended' => 'false',  // Allow users to select any type of unit
            'title'          => __( 'Header Logo Size', 'citytours' ),
            'subtitle'  => __( 'Set width and height of logo in header', 'citytours' ),
            'desc'           => __( 'Leave blank to use default value that supported by each header style', 'citytours' ),
            'default'        => array()
        ),
    )
) );

Redux::setSection( $opt_name, array(
    'title'      => __( 'Top Bar', 'citytours' ),
    'icon'       => 'el el-angle-right',
    'subsection' => true,
    'fields'     => array(
        array(
            'id'        => 'header_top_bar_login',
            'type'      => 'switch',
            'title'     => __('Login/Logout Button', 'citytours'),
            'desc'      => __('If you want to hide Login button, Disable this option', 'citytours'),
            'default'   => true,
            'on'        => __('Show', 'citytours'),
            'off'       => __('Hide', 'citytours'),
        ),
        array(
            'id'        => 'header_top_bar_wishlist',
            'type'      => 'switch',
            'title'     => __('Wishlist Button', 'citytours'),
            'desc'      => __('If you want to hide Wishlist button, Disable this option', 'citytours'),
            'default'   => true,
            'on'        => __('Show', 'citytours'),
            'off'       => __('Hide', 'citytours'),
        ),
        array(
            'id'        => 'header_search_bar',
            'type'      => 'switch',
            'title'     => __('Search bar', 'citytours'),
            'desc'      => __('If you want to hide Search Bar, Disable this option', 'citytours'),
            'default'   => true,
            'on'        => __('Show', 'citytours'),
            'off'       => __('Hide', 'citytours'),
        ),
        array(
            'id'        => 'header_top_bar_enable_custom',
            'type'      => 'switch',
            'title'     => __('Custom Link', 'citytours'), 
            'subtitle'  => __('Custom HTML Allowed', 'citytours'),
            'default'   => true,
            'on'        => __('Show', 'citytours'),
            'off'       => __('Hide', 'citytours'),
        ),
        array(
            'id'        => 'header_top_bar_custom',
            'type'      => 'textarea',
            //'validate'  => 'html_custom',
            'desc'      => 'You add custom link into Top navigation bar. ( <b>Example : </b>&lt;a href="#"&gt;Today Sale!!!&lt;/a&gt; )',
            /*'allowed_html'  => array(
                'a' => array(
                    'href' => array(),
                    'title' => array()
                ),
                'span'  => array(),
                'div'   => array(),
                'br' => array(),
                'em' => array(),
                'strong' => array(),
            ),*/
            'required'  => array( 'header_top_bar_enable_custom', '=', '1' ),
        ),
    )
) );

Redux::setSection( $opt_name, array(
    'title'      => __( 'Page/Post Settings', 'citytours' ),
    'id'         => 'main-page-post-settings',
));
Redux::setSection( $opt_name, array(
    'title'      => __( 'Single Page Settings', 'citytours' ),
    'id'         => 'main-page-settings',
    'subsection' => true,
    'fields'     => array(
        array(
            'id'       => 'def_page_layout',
            'type'     => 'image_select',
            'title'    => __('Default Single Page Layout', 'citytours'), 
            'subtitle' => __('Select Default Single Page Layout', 'citytours'),
            'options'  => array(
                'left'      => array(
                    'alt'   => 'left-sidbar', 
                    'img'   => ReduxFramework::$_url.'assets/img/2cl.png'
                ),
                'right'      => array(
                    'alt'   => 'right-sidbar', 
                    'img'   => ReduxFramework::$_url.'assets/img/2cr.png'
                ),
                'no'      => array(
                    'alt'   => 'no-sidbar', 
                    'img'   => ReduxFramework::$_url.'assets/img/1col.png'
                ),
            ),
            'default' => 'left'
        ),
        array(
            'id'       => 'page_header_img',
            'type'     => 'media',
            'url'      => true,
            'title'    => __( 'Page Header Image', 'citytours' ),
            'desc'     => '',
            'subtitle' => __( 'Set a image file for your page header.', 'citytours' ),
            'default'  => array( 'url' => CT_IMAGE_URL . "/header-img.jpg" ),
        ),
        array(
            'id'             => 'page_header_img_height',
            'type'           => 'dimensions',
            'units'          => 'px',    // You can specify a unit value. Possible: px, em, %
            'units_extended' => 'false',  // Allow users to select any type of unit
            'title'          => __( 'Page Header Image Height', 'citytours' ),
            'subtitle'  => __( 'Set height of page header image', 'citytours' ),
            'width'         => false,
            'default'        => array( 'height' => '500')
        ),
        array(
            'id' => 'page_header_content',
            'title' => __('Page Header Content', 'citytours'),
            'subtitle' => __( 'Set blog page header content.', 'citytours' ),
            'type' => 'editor'
        ),
    )
));

Redux::setSection( $opt_name, array(
    'title'      => __( 'Single Post Settings', 'citytours' ),
    'id'         => 'main-post-settings',
    'subsection' => true,
    'fields'     => array(
        array(
            'id'       => 'def_post_layout',
            'type'     => 'image_select',
            'title'    => __('Default Single Post Layout', 'citytours'), 
            'subtitle' => __('Select Default Single Post Layout', 'citytours'),
            'options'  => array(
                'left'      => array(
                    'alt'   => 'left-sidbar', 
                    'img'   => ReduxFramework::$_url.'assets/img/2cl.png'
                ),
                'right'      => array(
                    'alt'   => 'right-sidbar', 
                    'img'   => ReduxFramework::$_url.'assets/img/2cr.png'
                ),
                'no'      => array(
                    'alt'   => 'no-sidbar', 
                    'img'   => ReduxFramework::$_url.'assets/img/1col.png'
                ),
            ),
            'default' => 'left'
        ),
        array(
            'id'       => 'post_header_img',
            'type'     => 'media',
            'url'      => true,
            'title'    => __( 'Post Header Image', 'citytours' ),
            'desc'     => '',
            'subtitle' => __( 'Set a image file for your post header.', 'citytours' ),
            'default'  => array( 'url' => CT_IMAGE_URL . "/header-img.jpg" ),
        ),
        array(
            'id'             => 'post_header_img_height',
            'type'           => 'dimensions',
            'units'          => 'px',    // You can specify a unit value. Possible: px, em, %
            'units_extended' => 'false',  // Allow users to select any type of unit
            'title'          => __( 'Post Header Image Height', 'citytours' ),
            'subtitle'  => __( 'Set height of post header image', 'citytours' ),
            'width'         => false,
            'default'        => array( 'height' => '500')
        ),
        array(
            'id' => 'post_header_content',
            'title' => __('Post Header Content', 'citytours'),
            'subtitle' => __( 'Set post header content.', 'citytours' ),
            'type' => 'editor'
        ),        
    )
));

Redux::setSection( $opt_name, array(
    'title'      => __( 'Blog Page Settings', 'citytours' ),
    'id'         => 'main-blog-page-settings',
    'subsection' => true,
    'fields'     => array(
        array(
            'id'       => 'blog_page_layout',
            'type'     => 'image_select',
            'title'    => __('Blog Page Layout', 'citytours'), 
            'subtitle' => __('Select Default Single Post Layout', 'citytours'),
            'options'  => array(
                'left'      => array(
                    'alt'   => 'left-sidbar', 
                    'img'   => ReduxFramework::$_url.'assets/img/2cl.png'
                ),
                'right'      => array(
                    'alt'   => 'right-sidbar', 
                    'img'   => ReduxFramework::$_url.'assets/img/2cr.png'
                ),
                'no'      => array(
                    'alt'   => 'no-sidbar', 
                    'img'   => ReduxFramework::$_url.'assets/img/1col.png'
                ),
            ),
            'default' => 'left'
        ),
        array(
            'id'       => 'blog_header_img',
            'type'     => 'media',
            'url'      => true,
            'title'    => __( 'Blog Page Header Image', 'citytours' ),
            'desc'     => '',
            'subtitle' => __( 'Set a image file for your blog page header.', 'citytours' ),
            'default'  => array( 'url' => CT_IMAGE_URL . "/header-img.jpg" ),
        ),
        array(
            'id'             => 'blog_header_img_height',
            'type'           => 'dimensions',
            'units'          => 'px',    // You can specify a unit value. Possible: px, em, %
            'units_extended' => 'false',  // Allow users to select any type of unit
            'title'          => __( 'Blog Page Header Image Height', 'citytours' ),
            'subtitle'  => __( 'Set height of blog page header image', 'citytours' ),
            'width'         => false,
            'default'        => array( 'height' => '500')
        ),
        array(
            'id' => 'blog_header_content',
            'title' => __('Blog Page Header Content', 'citytours'),
            'subtitle' => __( 'Set blog page header content.', 'citytours' ),
            'type' => 'editor'
        ),
    )
));

Redux::setSection( $opt_name, array(
    'title'      => __( 'Special Page Settings', 'citytours' ),
    'subsection' => true,
    'fields'     => array(
        array(
            'id'       => 'login_page',
            'type'     => 'select',
            'title'    => __('Login Page', 'citytours'),
            'subtitle' => __('You can leave this field blank if you don\'t need Custom Login Page', 'citytours'),
            'desc'     => __('If you set wrong page you\'re unable to login. In that case you can login with /wp-login.php?no_redirect=1', 'citytours'),
            'options'  => $options_pages,
            'default'  => ''
                ),
        array(
            'id'       => 'login_page_bg_image',
            'type'     => 'media',
            'url'      => true,
            'title'    => __( 'Login/SignUp Page Background Image', 'citytours' ),
            'desc'     => __( 'If leave this field empty, default image will be used.', 'citytours' ),
            'default'  => array( 'url' => CT_IMAGE_URL . "/slide_hero.jpg" ),
                ),
        array(
            'id'       => 'redirect_page',
            'type'     => 'select',
            'title'    => __('Page to Redirect to on login', 'citytours'),
            'subtitle' => __('Select a Page to Redirect to on login.', 'citytours'),
            'options'  => $options_pages,
            'default'  => ''
                ),
        array(
            'id'       => '404_page',
            'type'     => 'select',
            'title'    => __('404 Page', 'citytours'),
            'subtitle' => __('You can leave this field blank if you don\'t need Custom 404 Page', 'citytours'),
            'options'  => $options_pages,
            'default'  => ''
        ),
    )
) );


Redux::setSection( $opt_name, array(
    'title'      => __( 'Flight Page Settings', 'citytours' ),
    'id'         => 'main-flight-settings',
    'subsection' => true,
    'fields'     => array(
        array(
            'id'       => 'def_flight_layout',
            'type'     => 'image_select',
            'title'    => __('Default Flight Page Layout', 'citytours'), 
            'subtitle' => __('Select Default Flight Page Layout', 'citytours'),
            'options'  => array(
                'left'      => array(
                    'alt'   => 'left-sidbar', 
                    'img'   => ReduxFramework::$_url.'assets/img/2cl.png'
                ),
                'right'      => array(
                    'alt'   => 'right-sidbar', 
                    'img'   => ReduxFramework::$_url.'assets/img/2cr.png'
                ),
                'no'      => array(
                    'alt'   => 'no-sidbar', 
                    'img'   => ReduxFramework::$_url.'assets/img/1col.png'
                ),
            ),
            'default' => 'left'
        ),
        array(
            'id'       => 'flight_header_img',
            'type'     => 'media',
            'url'      => true,
            'title'    => __( 'Flight Header Image', 'citytours' ),
            'desc'     => '',
            'subtitle' => __( 'Set a image file for your flight header.', 'citytours' ),
            'default'  => array( 'url' => CT_IMAGE_URL . "/header-img.jpg" ),
        ),
        array(
            'id'             => 'flight_header_img_height',
            'type'           => 'dimensions',
            'units'          => 'px',    // You can specify a unit value. Possible: px, em, %
            'units_extended' => 'false',  // Allow users to select any type of unit
            'title'          => __( 'Flight Header Image Height', 'citytours' ),
            'subtitle'  => __( 'Set height of flight header image', 'citytours' ),
            'width'         => false,
            'default'        => array( 'height' => '500')
        ),
        array(
            'id' => 'flight_header_content',
            'title' => __('Flight Header Content', 'citytours'),
            'subtitle' => __( 'Set flight header content.', 'citytours' ),
            'type' => 'editor'
        ),        
    )
));

include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
if ( is_plugin_active( 'ct-booking/ct-booking.php' ) ) :

Redux::setSection( $opt_name, array(
    'title'     => __( 'Booking Settings', 'citytours' ),
    'id'        => 'booking-settings',
    'fields'    => array(
        array(
            'id'       => 'date_format',
            'type'     => 'select',
            'title'    => __('Date Format', 'citytours'),
            'subtitle' => __('Please select a date format for datepicker.', 'citytours'),
            'options'  => array(
                            'mm/dd/yyyy' => 'mm/dd/yyyy',
                            'dd/mm/yyyy' => 'dd/mm/yyyy',
                            'yyyy-mm-dd' => 'yyyy-mm-dd',
                          ),
            'default'  => 'mm/dd/yyyy'
        ),
        array(
            'id'       => 'wishlist',
            'type'     => 'select',
            'title'    => __('Wishlist Page', 'citytours'),
            'subtitle' => __('Please create a blank page and set it.', 'citytours'),
            'desc'     => '',
            'options'  => $options_pages,
            'default'  => ''
        ),
    )
) );


// Include Currency functions
require_once CT_INC_DIR . '/functions/currency.php';

Redux::setSection( $opt_name, array(
    'title'      => __( 'Currency Settings', 'citytours' ),
    'id'         => 'currency-settings',
    'icon'       => 'el el-usd',
    'fields'     => array(
        array(
            'id'       => 'def_currency',
            'type'     => 'select',
            'title'    => __( 'Default Currency', 'citytours' ),
            'subtitle' => __( 'Select default currency', 'citytours' ),
            'desc'     => apply_filters( 'ct_options_def_currency_desc', __('All price fields in admin panel will be considered in this currency', 'citytours') ),
            //Must provide key => value pairs for select options
            'options'  => ct_get_all_available_currencies(),
            'default'  => 'usd'
        ),
        array(
            'id'       => 'site_currencies',
            'type'     => 'checkbox',
            'title'    => __('Available Currencies', 'citytours'),
            'subtitle' => __('You can select currencies that this site support. You can manage currency list <a href="admin.php?page=currencies">here</a>', 'citytours'),
            'desc'     => '',
            'options'  => ct_get_all_available_currencies(),
            'default'  => ct_get_default_available_currencies()
        ),
        array(
            'id'       => 'cs_pos',
            'type'     => 'button_set',
            'title'    => __( 'Currency Symbol Position', 'citytours' ),
            'subtitle' => __( "Select a Curency Symbol Position for Frontend", 'citytours' ),
            'desc'     => '',
            'options'  => array(
                'left' => __( 'Left ($99.99)', 'citytours' ),
                'right' => __( 'Right (99.99$)', 'citytours' ),
                'left_space' => __( 'Left with space ($ 99.99)', 'citytours' ),
                'right_space' => __( 'Right with space (99.99 $)', 'citytours' )
            ),
            'default'  => 'before'
        ),
        array(
            'id'       => 'decimal_prec',
            'type'     => 'select',
            'title'    => __( 'Decimal Precision', 'citytours' ),
            'subtitle' => __( 'Please choose desimal precision', 'citytours' ),
            'desc'     => '',
            'options'  => array(
                            '0' => '0',
                            '1' => '1',
                            '2' => '2',
                            '3' => '3',
                            ),
            'default'  => '2'
        ),
        array(
            'id'       => 'thousands_sep',
            'type'     => 'text',
            'title'    => __( 'Thousand Separate', 'citytours' ),
            'subtitle' => __( 'This sets the thousand separator of displayed prices.', 'citytours' ),
            'default'  => ',',
        ),
        array(
            'id'       => 'decimal_sep',
            'type'     => 'text',
            'title'    => __( 'Decimal Separate', 'citytours' ),
            'subtitle' => __( 'This sets the decimal separator of displayed prices.', 'citytours' ),
            'default'  => '.',
        ),
    )
) );

Redux::setSection( $opt_name, array(
    'title'      => __( 'Hotel', 'citytours' ),
    'id'         => 'hotel-settings',
) );

Redux::setSection( $opt_name, array(
    'title'      => __( 'Hotel Main Settings', 'citytours' ),
    'id'         => 'hotel-main-settings',
    'icon'       => 'el el-angle-right',
    'subsection' => true,
    'fields'     => array(
        array(
            'id'       => 'disable_hotel',
            'type'     => 'button_set',
            'title'    => __('Enable/Disable hotel feature.', 'citytours'),
            'default'  => 0,
            'options'  => array(
                '0' => __( 'Enable', 'citytours' ),
                '1' => __( 'Disable', 'citytours' )
            ),
        ),
        array(
            'id'       => 'hotel_cart_page',
            'type'     => 'select',
            'required' => array( 'disable_hotel', '=', '0' ),
            'title'    => __('Hotel Cart Page', 'citytours'),
            'subtitle' => __('This sets the base page of your hotel booking. Please add [hotel_cart] shortcode in the page content.', 'citytours'),
            'options'  => $options_pages,
        ),
        array(
            'id'       => 'hotel_checkout_page',
            'type'     => 'select',
            'required' => array( 'disable_hotel', '=', '0' ),
            'title'    => __('Hotel Checkout Page', 'citytours'),
            'subtitle' => __('This sets the hotel Checkout Page. Please add [hotel_checkout] shortcode in the page content.', 'citytours'),
            'options'  => $options_pages,
        ),
        array(
            'id'       => 'hotel_thankyou_page',
            'type'     => 'select',
            'required' => array( 'disable_hotel', '=', '0' ),
            'title'    => __('Hotel Booking Confirmation Page', 'citytours'),
            'subtitle' => __('This sets the hotel booking confirmation Page. Please add [hotel_booking_confirmation] shortcode in the page content.', 'citytours'),
            'options'  => $options_pages,
        ),
        array(
            'id'       => 'hotel_thankyou_text_1',
            'type'     => 'text',
            'required' => array( 'disable_hotel', '=', '0' ),
            'title'    => __('Hotel Booking Confirmation Text 1', 'citytours'),
            'subtitle' => __('This sets confirmation text1.', 'citytours'),
            'default'  => 'Lorem ipsum dolor sit amet, nostrud nominati vis ex, essent conceptam eam ad. Cu etiam comprehensam nec. Cibo delicata mei an, eum porro legere no. Te usu decore omnium, quem brute vis at, ius esse officiis legendos cu. Dicunt voluptatum at cum. Vel et facete equidem deterruisset, mei graeco cetero labores et. Accusamus inciderint eu mea.',
        ),
        array(
            'id'       => 'hotel_thankyou_text_2',
            'type'     => 'text',
            'required' => array( 'disable_hotel', '=', '0' ),
            'title'    => __('Hotel Booking Confirmation Text 2', 'citytours'),
            'subtitle' => __('This sets confirmation text2.', 'citytours'),
            'default'  => 'Nihil inimicus ex nam, in ipsum dignissim duo. Tale principes interpretaris vim ei, has posidonium definitiones ut. Duis harum fuisset ut his, duo an dolor epicuri appareat.',
        ),
        array(
            'id'       => 'hotel_invoice_page',
            'type'     => 'select',
            'required' => array( 'disable_hotel', '=', '0' ),
            'title'    => __('Invoice Page', 'citytours'),
            'subtitle' => __('You can create a blank page for invoice page. After that please set the page here.', 'citytours'),
            'options'  => $options_pages,
        ),
        array(
            'id'       => 'hotel_terms_page',
            'type'     => 'select',
            'required' => array( 'disable_hotel', '=', '0' ),
            'title'    => __('Terms & Conditions Page', 'citytours'),
            'subtitle' => __('Booking Terms and Conditions Page.', 'citytours'),
            'options'  => $options_pages,
        ),
        array(
            'id'       => 'hotel_review',
            'type'     => 'switch',
            'required' => array( 'disable_hotel', '=', '0' ),
            'title'    => __('Hotel Review On/Off', 'citytours'),
            'default'  => true,
        ),
        array(
            'id'       => 'hotel_review_fields',
            'type'     => 'text',
            'required' => array( 'disable_hotel', '=', '0' ),
            'title'    => __('Hotel Review Fields', 'citytours'),
            'subtitle'    => __('Set review fields separated by comma.', 'citytours'),
            'default'  => 'Position,Comfort,Price,Quality',
        ),
        array(
            'id'       => 'hotel_map_icon',
            'type'     => 'text',
            'required' => array( 'disable_hotel', '=', '0' ),
            'title'    => __('Hotel Map Icon URL', 'citytours'),
            'subtitle'    => __('Set Map Icon URL. <br> Please leave this blank if you want to use default Hotel Map Icon.', 'citytours'),
            'default'  => '',
        ),
    ),
) );

Redux::setSection( $opt_name, array(
    'title'      => __( 'Hotel List Page Settings', 'citytours' ),
    'id'         => 'hotel-list-settings',
    'icon'       => 'el el-angle-right',
    'subsection' => true,
    'fields'     => array(
        array(
            'id'       => 'hotel_header_img',
            'type'     => 'media',
            'url'      => true,
            'title'    => __( 'Header Image', 'citytours' ),
            'desc'     => '',
            'subtitle' => __( 'Set a image file for your hotel list page header.', 'citytours' ),
            'default'  => array( 'url' => CT_IMAGE_URL . "/header-img.jpg" ),
        ),
        array(
            'id'             => 'hotel_header_img_height',
            'type'           => 'dimensions',
            'units'          => 'px',    // You can specify a unit value. Possible: px, em, %
            'units_extended' => 'false',  // Allow users to select any type of unit
            'title'          => __( 'Header Image Height', 'citytours' ),
            'subtitle'  => __( 'Set height of hotel list page header image', 'citytours' ),
            'width'         => false,
            'default'        => array( 'height' => '500')
        ),
        array(
            'id'        => 'hotel_header_content',
            'title'     => __( 'Page Header Content', 'citytours' ),
            'subtitle'  => __( 'Set hotel list page header content.', 'citytours' ),
            'type'      => 'editor'
        ),
        array(
            'id'       => 'hotel_list_default_view',
            'type'     => 'button_set',
            'title'    => __('Default View', 'citytours'),
            'subtitle' => __('Set default view in Hotel List Page', 'citytours'),
            'default'  => 'list',
            'options'  => array(
                'list' => __( 'List', 'citytours' ),
                'grid' => __( 'Grid', 'citytours' )
            ),
        ),
        array(
            'title'     => __('Enable Star Rating Filter', 'citytours'),
            'subtitle'  => __('Add star rating filter to hotel list page.', 'citytours'),
            'id'        => 'hotel_star_filter',
            'default'   => true,
            'type'      => 'switch'
        ),
        array(
            'title'     => __('Enable Price Filter', 'citytours'),
            'subtitle'  => __('Add price filter to hotel list page.', 'citytours'),
            'id'        => 'hotel_price_filter',
            'default'   => true,
            'type'      => 'switch'
        ),
        array(
            'id'        => 'hotel_price_filter_steps',
            'required'  => array( 'hotel_price_filter', '=', true ),
            'type'      => 'text',
            'title'     => __( 'Price Filter Steps', 'citytours' ),
            'subtitle'  => __( 'This field is for price filter steps. For example you can set 50,80,100 to make 4 steps - 0~50, 50~80, 80~100, 100+.', 'citytours' ),
            'default'   => '50,80,100',
        ),
        array(
            'title'     => __('Enable Review Rating Filter', 'citytours'),
            'subtitle'  => __('Add review rating filter to hotel list page.', 'citytours'),
            'id'        => 'hotel_rating_filter',
            'default'   => true,
            'type'      => 'switch'
        ),
        array(
            'title'     => __('Enable Facility Filter', 'citytours'),
            'subtitle'  => __('Add facility filter to hotel list page.', 'citytours'),
            'id'        => 'hotel_facility_filter',
            'default'   => true,
            'type'      => 'switch'
        ),
        array(
            'title'     => __('Enable District Filter', 'citytours'),
            'subtitle'  => __('Add district filter to hotel list page.', 'citytours'),
            'id'        => 'hotel_district_filter',
            'default'   => true,
            'type'      => 'switch'
        ),
        array(
            'id'        => 'hotel_posts',
            'type'      => 'text',
            'title'     => __( 'Hotels per page', 'citytours' ),
            'subtitle'  => __( 'Select a number of hotels to show on Hotel List Page', 'citytours' ),
            'default'   => '12',
        ),
        array(
            'id'        => 'hotel_list_zoom',
            'type'      => 'text',
            'title'     => __( 'Map zoom value', 'citytours' ),
            'subtitle'  => __( 'Select a zoom value for Map in List page.', 'citytours' ),
            'default'   => '14',
        ),
    ),
) );

Redux::setSection( $opt_name, array(
    'title'      => __( 'Hotel Email Settings', 'citytours' ),
    'id'         => 'hotel-email-settings',
    'icon'       => 'el el-angle-right',
    'subsection' => true,
    'fields'     => array(
        array(
            'id'        => 'hotel_confirm_email_start',
            'type'      => 'section',
            'title'     => __( 'Customer Email Setting', 'citytours' ),
            // 'subtitle'   => __( '', 'citytours' ),
            'indent'    => true,
        ),
        /*array(
            'title'     => __('Enable Icalendar', 'citytours'),
            'subtitle'  => __('Send icalendar with booking confirmation email.', 'citytours'),
            'id'        => 'hotel_confirm_email_ical',
            'default'   => true,
            'type'      => 'switch'
        ),*/
        array(
            'title'     => __('Booking Confirmation Email Subject', 'citytours'),
            'subtitle'  => __( 'Hotel booking confirmation email subject.', 'citytours' ),
            'id'        => 'hotel_confirm_email_subject',
            'default'   => 'Your booking at [hotel_name]',
            'type'      => 'text'
        ),
        array(
            'title'     => __('Booking Confirmation Email Description', 'citytours'),
            'subtitle'  => __( 'Hotel booking confirmation email description.', 'citytours' ),
            'id'        => 'hotel_confirm_email_description',
            'default'   => file_get_contents( dirname( __FILE__ ) . '/templates/hotel_confirm_email_description.htm' ),
            'type'      => 'editor'
        ),
        array(
            'id'        => 'hotel_confirm_email_end',
            'type'      => 'section',
            'indent'    => false,
        ),
        array(
            'id'        => 'hotel_admin_email_start',
            'type'      => 'section',
            'title'     => __( 'Admin Notification Setting', 'citytours' ),
            // 'subtitle'   => __( '', 'citytours' ),
            'indent'    => true,
        ),
        array(
            'title'     => __('Administrator Notification', 'citytours'),
            'subtitle'  => __('enable individual booked email notification to site administrator.', 'citytours'),
            'id'        => 'hotel_booked_notify_admin',
            'default'   => 'true',
            'type'      => 'switch'
        ),
        array(
            'title'     => __('Administrator Booking Notification Email Subject', 'citytours'),
            'subtitle'  => __( 'Administrator Notification Email Subject for Hotel Booking.', 'citytours' ),
            'id'        => 'hotel_admin_email_subject',
            'default'   => 'Received a booking at [hotel_name]',
            'required'  => array( 'hotel_booked_notify_admin', '=', '1' ),
            'type'      => 'text'
        ),
        array(
            'title'     => __('Administrator Booking Notification Email Description', 'citytours'),
            'subtitle'  => __( 'Administrator Notification Email Description for Hotel Booking.', 'citytours' ),
            'id'        => 'hotel_admin_email_description',
            'default'   => file_get_contents( dirname( __FILE__ ) . '/templates/hotel_admin_email_description.htm' ),
            'required'  => array( 'hotel_booked_notify_admin', '=', '1' ),
            'type'      => 'editor'
        ),
        array(
            'id'        => 'hotel_admin_email_end',
            'type'      => 'section',
            'indent'    => false,
        ),
    ),
) );

// add-on compatibility
$hotel_add_on_settings = apply_filters( 'ct_options_hotel_addon_settings', array() );
if ( ! empty( $hotel_add_on_settings ) ) {
    Redux::setSection( $opt_name, array(
        'title'      => __( 'Hotel Add-On Settings', 'citytours' ),
        'id'         => 'hotel-add-settings',
        'subsection' => true,
        'fields'     => $hotel_add_on_settings
    ) );
}


Redux::setSection( $opt_name, array(
    'title'      => __( 'Tour', 'citytours' ),
    'id'         => 'tour-settings',
) );

Redux::setSection( $opt_name, array(
    'title'      => __( 'Tour Main Settings', 'citytours' ),
    'id'         => 'tour-main-settings',
    'icon'       => 'el el-angle-right',
    'subsection' => true,
    'fields'     => array(
        array(
            'id'       => 'disable_tour',
            'type'     => 'button_set',
            'title'    => __('Enable/Disable tour feature.', 'citytours'),
            'default'  => 0,
            'options'  => array(
                '0' => __( 'Enable', 'citytours' ),
                '1' => __( 'Disable', 'citytours' )
            ),
        ),
        array(
            'id'       => 'tour_cart_page',
            'type'     => 'select',
            'required' => array( 'disable_tour', '=', '0' ),
            'title'    => __('Tour Cart Page', 'citytours'),
            'subtitle' => __('This sets the base page of your tour booking. Please add [tour_cart] shortcode in the page content.', 'citytours'),
            'options'  => $options_pages,
        ),
        array(
            'id'       => 'tour_checkout_page',
            'type'     => 'select',
            'required' => array( 'disable_tour', '=', '0' ),
            'title'    => __('Tour Checkout Page', 'citytours'),
            'subtitle' => __('This sets the tour Checkout Page. Please add [tour_checkout] shortcode in the page content.', 'citytours'),
            'options'  => $options_pages,
        ),
        array(
            'id'       => 'tour_thankyou_page',
            'type'     => 'select',
            'required' => array( 'disable_tour', '=', '0' ),
            'title'    => __('Tour Booking Confirmation Page', 'citytours'),
            'subtitle' => __('This sets the tour booking confirmation Page. Please add [tour_booking_confirmation] shortcode in the page content.', 'citytours'),
            'options'  => $options_pages,
        ),

        array(
            'id'       => 'tour_thankyou_text_1',
            'type'     => 'text',
            'required' => array( 'disable_tour', '=', '0' ),
            'title'    => __('Tour Booking Confirmation Text 1', 'citytours'),
            'subtitle' => __('This sets confirmation text1.', 'citytours'),
            'default'  => 'Lorem ipsum dolor sit amet, nostrud nominati vis ex, essent conceptam eam ad. Cu etiam comprehensam nec. Cibo delicata mei an, eum porro legere no. Te usu decore omnium, quem brute vis at, ius esse officiis legendos cu. Dicunt voluptatum at cum. Vel et facete equidem deterruisset, mei graeco cetero labores et. Accusamus inciderint eu mea.',
        ),
        array(
            'id'       => 'tour_thankyou_text_2',
            'type'     => 'text',
            'required' => array( 'disable_tour', '=', '0' ),
            'title'    => __('Tour Booking Confirmation Text 2', 'citytours'),
            'subtitle' => __('This sets confirmation text2.', 'citytours'),
            'default'  => 'Nihil inimicus ex nam, in ipsum dignissim duo. Tale principes interpretaris vim ei, has posidonium definitiones ut. Duis harum fuisset ut his, duo an dolor epicuri appareat.',
        ),
        array(
            'id'       => 'tour_invoice_page',
            'type'     => 'select',
            'required' => array( 'disable_tour', '=', '0' ),
            'title'    => __('Invoice Page', 'citytours'),
            'subtitle' => __('You can create a blank page for invoice page. After that please set the page here.', 'citytours'),
            'options'  => $options_pages,
        ),
        array(
            'id'       => 'tour_terms_page',
            'type'     => 'select',
            'required' => array( 'disable_tour', '=', '0' ),
            'title'    => __('Terms & Conditions Page', 'citytours'),
            'subtitle' => __('Booking Terms and Conditions Page.', 'citytours'),
            'options'  => $options_pages,
        ),
        array(
            'id'       => 'tour_review',
            'type'     => 'switch',
            'required' => array( 'disable_tour', '=', '0' ),
            'title'    => __('Tour Review On/Off', 'citytours'),
            'default'  => true,
        ),
        array(
            'id'       => 'tour_review_fields',
            'type'     => 'text',
            'required' => array( 'disable_tour', '=', '0' ),
            'title'    => __('Tour Review Fields', 'citytours'),
            'subtitle'    => __('Set review fields separated by comma.', 'citytours'),
            'default'  => 'Position,Tourist guide,Price,Quality',
        ),
        array(
            'id'       => 'tour_map_icon',
            'type'     => 'text',
            'required' => array( 'disable_tour', '=', '0' ),
            'title'    => __('Tour Map Icon URL', 'citytours'),
            'subtitle'    => __('Set Map Icon URL. <br> Please leave this blank if you want to use default Tour Map Icon.', 'citytours'),
            'default'  => '',
        ),
    ),
) );

Redux::setSection( $opt_name, array(
    'title'      => __( 'Tour List Page Settings', 'citytours' ),
    'id'         => 'tour-list-settings',
    'icon'       => 'el el-angle-right',
    'subsection' => true,
    'fields'     => array(
        array(
            'id'       => 'tour_header_img',
            'type'     => 'media',
            'url'      => true,
            'title'    => __( 'Header Image', 'citytours' ),
            'desc'     => '',
            'subtitle' => __( 'Set a image file for your tour list page header.', 'citytours' ),
            'default'  => array( 'url' => CT_IMAGE_URL . "/header-img.jpg" ),
        ),
        array(
            'id'             => 'tour_header_img_height',
            'type'           => 'dimensions',
            'units'          => 'px',    // You can specify a unit value. Possible: px, em, %
            'units_extended' => 'false',  // Allow users to select any type of unit
            'title'          => __( 'Header Image Height', 'citytours' ),
            'subtitle'       => __( 'Set height of tour list page header image', 'citytours' ),
            'width'          => false,
            'default'        => array( 'height' => '500')
        ),
        array(
            'id'        => 'tour_header_content',
            'title'     => __( 'Page Header Content', 'citytours' ),
            'subtitle'  => __( 'Set tour list page header content.', 'citytours' ),
            'type'      => 'editor'
        ),
        array(
            'id'       => 'tour_list_default_view',
            'type'     => 'button_set',
            'title'    => __('Default View', 'citytours'),
            'subtitle' => __('Set default view in Tour List Page', 'citytours'),
            'default'  => 'list',
            'options'  => array(
                'list' => __( 'List', 'citytours' ),
                'grid' => __( 'Grid', 'citytours' )
            ),
        ),
        array(
            'title'     => __('Enable Price Filter', 'citytours'),
            'subtitle'  => __('Add price filter to tour list page.', 'citytours'),
            'id'        => 'tour_price_filter',
            'default'   => true,
            'type'      => 'switch'),
        array(
            'id'       => 'tour_price_filter_steps',
            'required' => array( 'tour_price_filter', '=', true ),
            'type'     => 'text',
            'title'    => __( 'Price Filter Steps', 'citytours' ),
            'subtitle' => __( 'This field is for price filter steps. For example you can set 50,80,100 to make 4 steps - 0~50, 50~80, 80~100, 100+.', 'citytours' ),
            'default'  => '50,80,100',
        ),
        array(
            'title' => __('Enable Rating Filter', 'citytours'),
            'subtitle' => __('Add rating filter to tour list page.', 'citytours'),
            'id' => 'tour_rating_filter',
            'default' => true,
            'type' => 'switch'),
        array(
            'title' => __('Enable Facility Filter', 'citytours'),
            'subtitle' => __('Add facility filter to tour list page.', 'citytours'),
            'id' => 'tour_facility_filter',
            'default' => true,
            'type' => 'switch'),
        array(
            'id'       => 'tour_posts',
            'type'     => 'text',
            'title'    => __( 'Tours per page', 'citytours' ),
            'subtitle' => __( 'Select a number of tours to show on Tour List Page', 'citytours' ),
            'default'  => '12',
        ),
        array(
            'id'       => 'tour_list_zoom',
            'type'     => 'text',
            'title'    => __( 'Map zoom value', 'citytours' ),
            'subtitle' => __( 'Select a zoom value for Map in List page.', 'citytours' ),
            'default'  => '14',
        ),
    ),
) );

Redux::setSection( $opt_name, array(
    'title'      => __( 'Tour Email Settings', 'citytours' ),
    'id'         => 'tour-email-settings',
    'icon'       => 'el el-angle-right',
    'subsection' => true,
    'fields'     => array(
        array(
            'id'       => 'tour_confirm_email_start',
            'type'     => 'section',
            'title'    => __( 'Customer Email Setting', 'citytours' ),
            // 'subtitle' => __( '', 'citytours' ),
            'indent'   => true,
        ),
        array(
            'title' => __('Booking Confirmation Email Subject', 'citytours'),
            'subtitle' => __( 'Tour booking confirmation email subject.', 'citytours' ),
            'id' => 'tour_confirm_email_subject',
            'default' => 'Your booking at [tour_name]',
            'type' => 'text'),
        array(
            'title' => __('Booking Confirmation Email Description', 'citytours'),
            'subtitle' => __( 'Tour booking confirmation email description.', 'citytours' ),
            'id' => 'tour_confirm_email_description',
            'default' => file_get_contents( dirname( __FILE__ ) . '/templates/tour_confirm_email_description.htm' ),
            'type' => 'editor'),
        array(
            'id'     => 'tour_confirm_email_end',
            'type'   => 'section',
            'indent' => false,
        ),
        array(
            'id'       => 'tour_admin_email_start',
            'type'     => 'section',
            'title'    => __( 'Admin Notification Setting', 'citytours' ),
            // 'subtitle' => __( '', 'citytours' ),
            'indent'   => true,
        ),
        array(
            'title' => __('Administrator Notification', 'citytours'),
            'subtitle' => __('enable individual booked email notification to site administrator.', 'citytours'),
            'id' => 'tour_booked_notify_admin',
            'default' => 'true',
            'type' => 'switch'),
        array(
            'title' => __('Administrator Booking Notification Email Subject', 'citytours'),
            'subtitle' => __( 'Administrator Notification Email Subject for Tour Booking.', 'citytours' ),
            'id' => 'tour_admin_email_subject',
            'default' => 'Received a booking at [tour_name]',
            'required' => array( 'tour_booked_notify_admin', '=', '1' ),
            'type' => 'text'),
        array(
            'title' => __('Administrator Booking Notification Email Description', 'citytours'),
            'subtitle' => __( 'Administrator Notification Email Description for Tour Booking.', 'citytours' ),
            'id' => 'tour_admin_email_description',
            'default' => file_get_contents( dirname( __FILE__ ) . '/templates/tour_admin_email_description.htm' ),
            'required' => array( 'tour_booked_notify_admin', '=', '1' ),
            'type' => 'editor'),
        array(
            'id'     => 'tour_admin_email_end',
            'type'   => 'section',
            'indent' => false,
        ),
    ),
) );

// add-on compatibility
$tour_add_on_settings = apply_filters( 'ct_options_tour_addon_settings', array() );
if ( ! empty( $tour_add_on_settings ) ) {
    Redux::setSection( $opt_name, array(
        'title'      => __( 'Tour Add-On Settings', 'citytours' ),
        'id'         => 'tour-add-settings',
        'subsection' => true,
        'fields'     => $tour_add_on_settings
    ) );
}

Redux::setSection( $opt_name, array(
    'title'     => __( 'Permalinks', 'citytours' ),
    'id'        => 'permalink-settings',
    'fields'    => array( 
        array(
            'id'        => 'hotel_permalink',
            'type'      => 'text',
            'required'  => array( 'disable_hotel', '=', '0' ),
            'title'     => __('Hotel', 'citytours'),
            'default'   => '',
        ),
        array(
            'id'        => 'tour_permalink',
            'type'      => 'text',
            'required'  => array( 'disable_tour', '=', '0' ),
            'title'     => __('Tour', 'citytours'),
            'default'   => '',
        ),
        array( 
            'id'        => 'permalink_setting_desc',
            'type'      => 'info',
            'desc'       => __("If you like, you may enter custom structures for Hotel and Tour URLs here. <b>For example</b>, using 'resort' as Hotel base would make your Hotel links like http://local.citytours.com/resort/abc/. <br> If you leave these blank the defaults will be used.", 'citytours'),
        ),
    ),
) );

Redux::setSection( $opt_name, array(
    'title' => __( 'Payment', 'citytours' ),
    'id'    => 'payment-settings',
) );
Redux::setSection( $opt_name, array(
    'title'      => __( 'Paypal', 'citytours' ),
    'id'         => 'paypal-settings',
    'icon'       => 'el el-angle-right',
    'subsection' => true,
    'fields'     => array(
        array(
            'title' => __('PayPal Integration', 'citytours'),
            'subtitle' => __('Enable payment through PayPal in booking step.', 'citytours'),
            'id' => 'pay_paypal',
            'default' => false,
            'type' => 'switch'),

        array(
            'title' => __('Credit Card Payment', 'citytours'),
            'subtitle' => __('Enable Credit Card payment through PayPal in booking step. Please note your paypal account should be business pro.', 'citytours'),
            'id' => 'credit_card',
            'default' => false,
            'required' => array( 'pay_paypal', '=', '1' ),
            'type' => 'switch'),

        array(
            'title' => __('Sandbox Mode', 'citytours'),
            'subtitle' => __('Enable PayPal sandbox for testing.', 'citytours'),
            'id' => 'paypal_sandbox',
            'default' => false,
            'required' => array( 'pay_paypal', '=', '1' ),
            'type' => 'switch'),

        array(
            'title' => __('PayPal API Username', 'citytours'),
            'subtitle' => __('Your PayPal Account API Username.', 'citytours'),
            'id' => 'paypal_api_username',
            'default' => '',
            'required' => array( 'pay_paypal', '=', '1' ),
            'type' => 'text'),

        array(
            'title' => __('PayPal API Password', 'citytours'),
            'subtitle' => __('Your PayPal Account API Password.', 'citytours'),
            'id' => 'paypal_api_password',
            'default' => '',
            'required' => array( 'pay_paypal', '=', '1' ),
            'type' => 'text'),

        array(
            'title' => __('PayPal API Signature', 'citytours'),
            'subtitle' => __('Your PayPal Account API Signature.', 'citytours'),
            'id' => 'paypal_api_signature',
            'default' => '',
            'required' => array( 'pay_paypal', '=', '1' ),
            'type' => 'text'),
    )
) );

if ( class_exists( 'WooCommerce' ) ) { 

    Redux::setSection( $opt_name, array(
        'title' => __( 'WooCommerce', 'citytours' ),
        'id'    => 'woocommerce-settings',
        'icon'  => 'el el-shopping-cart'
    ) );

    // add-on compatibility
    Redux::setSection( $opt_name, array(
        'title'      => __( 'Woocommerce Integration', 'citytours' ),
        'icon'       => 'el el-angle-right',
        'subsection' => true,
        'fields'     => array(
            array(
                'title'     => __('Woocommerce Integration', 'citytours'),
                'id'        => 'enable_woocommerce_integration',
                'default'   => false,
                'type'      => 'switch',
                'desc'      => __('If enable this option, you can use all WooCommerce features including Payment Gateways, Cart and Checkout system', 'citytours'),
                'on'        => __('Enable', 'citytours'),
                'off'       => __('Disable', 'citytours'),
            )
        )
    ) );
    Redux::setSection( $opt_name, array(
        'title'      => __( 'Shop', 'citytours' ),
        'icon'       => 'el el-angle-right',
        'subsection' => true,
        'fields'     => array(
            array(
                'title'     => __('Page Layout', 'citytours'),
                'id'        => 'shop_page_layout',
                'type'      => 'select',
                'options'   => array(
                    'no'    => __('No Sidebar', 'citytours'),
                    'left'  => __('Left Sidebar', 'citytours'),
                    'right' => __('Right Sidebar', 'citytours')
                ),
                'default'   => 'left'
            ),
            array(
                'title'    => __( 'Header Image', 'citytours' ),
                'id'       => 'shop_header_img',
                'type'     => 'media',
                'url'      => true,
                'desc'     => '',
                'subtitle' => __( 'Set a image file for your Shop page header.', 'citytours' ),
                'default'  => array( 'url' => CT_IMAGE_URL . "/header-img.jpg" ),
            ),
            array(
                'title'          => __( 'Header Image Height', 'citytours' ),
                'id'             => 'shop_header_img_height',
                'type'           => 'dimensions',
                'units'          => 'px',
                'units_extended' => 'false',
                'subtitle'       => __( 'Set height of Shop page header image', 'citytours' ),
                'width'          => false,
                'default'        => array( 'height' => '500')
            ),
            array(
                'id'            => 'shop_header_content',
                'title'         => __( 'Shop Page Header Content', 'citytours' ),
                'subtitle'      => __( 'Set shop page header content.', 'citytours' ),
                'type'          => 'editor'
            ),
            array(
                'title'     => __('Product Columns', 'citytours'),
                'id'        => 'shop_product_columns',
                'type'      => 'button_set',
                'options'   => array( '2' => '2', '3' => '3', '4' => '4', '5' => '5', '6' => '6' ),
                'default'   => '3'
            ),
            array(
                'title'     => __('Products per Page', 'citytours'),
                'id'        => 'shop_product_count',
                'type'      => 'text',
                'default'   => '12'
            ),
            array(
                'title'     => __('Default View Mode', 'citytours'),
                'id'        => 'shop_view_mode',
                'type'      => 'button_set',
                'options'   => array(
                    'grid'  => __('Grid', 'citytours'),
                    'list'  => __('List', 'citytours')
                ),
                'default'   => 'grid'
            ),
        )
    ) );
    Redux::setSection( $opt_name, array(
        'title'      => __( 'Single Product', 'citytours' ),
        'icon'       => 'el el-angle-right',
        'subsection' => true,
        'fields'     => array(
            array(
                'title'     => __('Page Layout', 'citytours'),
                'id'        => 'product_page_layout',
                'type'      => 'select',
                'options'   => array(
                    'no'    => __('No Sidebar', 'citytours'),
                    'left'  => __('Left Sidebar', 'citytours'),
                    'right' => __('Right Sidebar', 'citytours')
                ),
                'default'   => 'right'
            ),
            array(
                'title'    => __( 'Header Image', 'citytours' ),
                'id'       => 'product_header_img',
                'type'     => 'media',
                'url'      => true,
                'desc'     => '',
                'subtitle' => __( 'Set a image file for your Product page header.', 'citytours' ),
                'default'  => array( 'url' => CT_IMAGE_URL . "/header-img.jpg" ),
            ),
            array(
                'title'          => __( 'Header Image Height', 'citytours' ),
                'id'             => 'product_header_img_height',
                'type'           => 'dimensions',
                'units'          => 'px',
                'units_extended' => 'false',
                'subtitle'       => __( 'Set height of Product page header image', 'citytours' ),
                'width'          => false,
                'default'        => array( 'height' => '500')
            ),
            array(
                'title'     => __( 'Show Upsells', 'citytours' ),
                'subtitle'  => __( 'If you want to show up-sell products, please enable this option.', 'citytours' ),
                'id'        => 'product_show_upsells',
                'type'      => 'switch',
                'default'   => false,
            ),
            array(
                'title'     => __( 'Upsell Columns', 'citytours' ),
                'id'        => 'product_upsell_columns',
                'required'  => array( 'product_show_upsells', '=', '1' ),
                'type'      => 'button_set',
                'default'   => '3',
                'options'   => array( '2' => '2', '3' => '3', '4' => '4', '5' => '5', '6' => '6' ),
            ),
            array(
                'title'     => __( 'Upsell Product Count', 'citytours' ),
                'id'        => 'product_upsell_count',
                'required'  => array( 'product_show_upsells', '=', '1' ),
                'type'      => 'text',
                'default'   => '3'
            ),
            array(
                'title'     => __( 'Show Related Products', 'citytours' ),
                'subtitle'  => __( 'If you want to show related products, please enable this option.', 'citytours' ),
                'id'        => 'product_show_related',
                'type'      => 'switch',
                'default'   => true,
            ),
            array(
                'title'     => __( 'Related Product Columns', 'citytours' ),
                'id'        => 'product_related_columns',
                'type'      => 'button_set',
                'required'  => array( 'product_show_related', '=', '1' ),
                'default'   => '3',
                'options'   => array( '2' => '2', '3' => '3', '4' => '4', '5' => '5', '6' => '6' )
            ),
            array(
                'title'     => __( 'Related Product Count', 'citytours' ),
                'id'        => 'product_related_count',
                'type'      => 'text',
                'default'   => '3',
                'required'  => array( 'product_show_related', '=', '1' ),
            )
        )
    ) );
    Redux::setSection( $opt_name, array(
        'title'      => __( 'Cart', 'citytours' ),
        'icon'       => 'el el-angle-right',
        'subsection' => true,
        'fields'     => array(
            array(
                'title'     => __( 'Show Cross Sells', 'citytours' ),
                'subtitle'  => __( 'If you want to show cross-sells in the cart page, please enable this option.', 'citytours' ),
                'id'        => 'cart_show_cross_sells',
                'type'      => 'switch',
                'default'   => false,
            ),
            array(
                'title'     => __( 'Cross-Sell Columns', 'citytours' ),
                'id'        => 'cart_cross_sells_columns',
                'required'  => array( 'cart_show_cross_sells', '=', '1' ),
                'type'      => 'button_set',
                'default'   => '3',
                'options'   => array( '2' => '2', '3' => '3', '4' => '4', '5' => '5', '6' => '6' )
            ),
            array(
                'title'     => __( 'Cross-Sell Products Count', 'citytours' ),
                'id'        => 'cart_cross_sells_count',
                'required'  => array( 'cart_show_cross_sells', '=', '1' ),
                'type'      => 'text',
                'default'   => '3'
            ),
            array(
                'title'     => __( 'Show Mini Cart', 'citytours' ),
                'subtitle'  => __( 'If you want to show mini cart in top bar, please enable this option.', 'citytours' ),
                'id'        => 'cart_show_mini_cart',
                'type'      => 'switch',
                'default'   => false,
            )
        )
    ) );
}

endif;


Redux::setSection( $opt_name, array(
    'title'      => __( 'Social Links', 'citytours' ),
    'id'         => 'social-settings',
    'fields'     => array(
        array(
            'title' => __('Facebook', 'citytours'),
            'desc'  => __( 'Insert your custom link to show the Facebook icon. Leave blank to hide icon.', 'citytours' ),
            'id'    => 'facebook',
            'type'  => 'text',
        ),
        array(
            'title' => __('Twitter', 'citytours'),
            'desc'  => __( 'Insert your custom link to show the Twitter icon. Leave blank to hide icon.', 'citytours' ),
            'id'    => 'twitter',
            'type'  => 'text',
        ),
        array(
            'title' => __('Google+', 'citytours'),
            'desc'  => __( 'Insert your custom link to show the Google+ icon. Leave blank to hide icon.', 'citytours' ),
            'id'    => 'google',
            'type'  => 'text',
        ),
        array(
            'title' => __('Instagram', 'citytours'),
            'desc'  => __( 'Insert your custom link to show the Instagram icon. Leave blank to hide icon.', 'citytours' ),
            'id'    => 'instagram',
            'type'  => 'text',
        ),
        array(
            'title' => __('Pinterest', 'citytours'),
            'desc'  => __( 'Insert your custom link to show the Pinterest icon. Leave blank to hide icon.', 'citytours' ),
            'id'    => 'pinterest',
            'type'  => 'text',
        ),
        array(
            'title' => __('Vimeo', 'citytours'),
            'desc'  => __( 'Insert your custom link to show the Vimeo icon. Leave blank to hide icon.', 'citytours' ),
            'id'    => 'vimeo',
            'type'  => 'text',
        ),
        array(
            'title' => __('YouTube', 'citytours'),
            'desc'  => __( 'Insert your custom link to show the YouTube icon. Leave blank to hide icon.', 'citytours' ),
            'id'    => 'youtube-play',
            'type'  => 'text',
        ),
        array(
            'title' => __('LinkedIn', 'citytours'),
            'desc'  => __( 'Insert your custom link to show the LinkedIn icon. Leave blank to hide icon.', 'citytours' ),
            'id'    => 'linkedin',
            'type'  => 'text',
        ),
        array(
            'title' => __('TripAdvisor', 'citytours'),
            'desc'  => __( 'Insert your custom link to show the TripAdvisor icon. Leave blank to hide icon.', 'citytours' ),
            'id'    => 'tripadvisor',
            'type'  => 'text',
        ),
        array(
            'title' => __('Mixcloud', 'citytours'),
            'desc'  => __( 'Insert your custom link to show the Mixcloud icon. Leave blank to hide icon.', 'citytours' ),
            'id'    => 'mixcloud',
            'type'  => 'text',
        ),
    )
) );

Redux::setSection( $opt_name, array(
    'title'      => __( 'Custom JS & CSS', 'citytours' ),
    'id'         => 'custom-code',
    'fields'     => array(
        array(
            'id'       => 'custom_css',
            'type'     => 'ace_editor',
            'title'    => __( 'Custom CSS Code', 'citytours' ),
            'subtitle' => __( 'Paste your CSS code here.', 'citytours' ),
            'mode'     => 'css',
            'theme'    => 'chrome',
            'default'  => ""
        ),
        array(
            'id'       => 'custom_js',
            'type'     => 'ace_editor',
            'title'    => __( 'Custom Javascript Code', 'citytours' ),
            'subtitle' => __( 'Paste your Javascript code here.', 'citytours' ),
            'mode'     => 'javascript',
            'theme'    => 'chrome',
            'default'  => ""
        ),
    )
) );

if ( file_exists( dirname( __FILE__ ) . '/../README.md' ) ) {
    $section = array(
        'icon'   => 'el el-list-alt',
        'title'  => __( 'Documentation', 'citytours' ),
        'fields' => array(
            array(
                'id'       => '17',
                'type'     => 'raw',
                'markdown' => true,
                'content'  => file_get_contents( dirname( __FILE__ ) . '/../README.md' )
            ),
        ),
    );
    Redux::setSection( $opt_name, $section );
}