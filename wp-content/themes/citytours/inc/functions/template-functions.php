<?php
if (!defined('ABSPATH'))
    exit; // Exit if accessed directly

    /*
      ct_logo_url
      ct_logo_sticky_url
      ct_favicon_url
      ct_login_url
      ct_signup_url
      ct_start_page_url
      ct_wishlist_page_url
      ct_get_header_image_src
      ct_get_header_image_height
      ct_get_header_content
      ct_get_sidebar_position
      ct_get_current_page_url
      ct_get_template
      ct_redirect_home

      ct_init
      ct_body_class
      ct_inline_script
      ct_get_custom_css
      ct_enqueue_scripts
      ct_remove_redux_menu
      ct_register_required_plugins
      ct_wp_title_old
      ct_wp_title
      ct_register_sidebar
      ct_breadcrumbs
      ct_enqueue_comment_reply
      ct_comment
      ct_get_avatar
      ct_get_default_avatar
      ct_show404

      ct_user_register
      ct_login_failed
      ct_authenticate
     */

/*
 * get logo url function
 */
if (!function_exists('ct_logo_url'))
{


    function ct_logo_url()
    {
        global $ct_options;
        $url = '';
        if (!empty($ct_options['logo']) && !empty($ct_options['logo']['url']))
        {
            $url = $ct_options['logo']['url'];
        } else
        {
            $url = CT_IMAGE_URL . '/logo.png';
        }
        return $url;
    }

}

/*
 * get logo url function
 */
if (!function_exists('ct_logo_sticky_url'))
{


    function ct_logo_sticky_url()
    {
        global $ct_options;
        if (!empty($ct_options['logo_sticky']) && !empty($ct_options['logo_sticky']['url']))
        {
            $url = $ct_options['logo_sticky']['url'];
        } else
        {
            $url = CT_IMAGE_URL . '/logo.png';
        }
        return $url;
    }

}

/*
 * get logo url function
 */
if (!function_exists('ct_favicon_url'))
{


    function ct_favicon_url()
    {
        global $ct_options;

        if (!empty($ct_options['favicon']) && !empty($ct_options['favicon']['url']))
        {
            $url = $ct_options['favicon']['url'];
        } else
        {
            $url = CT_IMAGE_URL . '/favicon.ico';
        }

        return $url;
    }

}

/*
 * get login page url function
 */
if (!function_exists('ct_login_url'))
{


    function ct_login_url()
    {
        global $ct_options;

        $login_url = '';
        if (!empty($ct_options['modal_login']))
        {
            $login_url = '#';
        } else
        {
            if (!empty($ct_options['login_page']))
            {
                $login_url = ct_get_permalink_clang($ct_options['login_page']);
            } else
            {
                $login_url = wp_login_url(ct_redirect_url());
            }
        }

        return $login_url;
    }

}

/*
 * get login page url function
 */
if (!function_exists('ct_redirect_url'))
{


    function ct_redirect_url()
    {
        global $ct_options;
        if (!empty($ct_options['redirect_page']))
        {
            return ct_get_permalink_clang($ct_options['redirect_page']);
        } else
        {
            return ct_get_current_page_url();
        }
    }

}

/*
 * get signup page url function
 */
if (!function_exists('ct_signup_url'))
{


    function ct_signup_url()
    {
        global $ct_options;
        $signup_url = '';
        if (!get_option('users_can_register'))
        {
            return $signup_url;
        }
        if (!empty($ct_options['modal_login']))
        {
            $signup_url = '#ct-signup';
        } else
        {
            if (!empty($ct_options['login_page']))
            {
                $signup_url = add_query_arg('action', 'register', ct_get_permalink_clang($ct_options['login_page']));
            } else
            {
                $signup_url = wp_registration_url();
            }
        }
        return $signup_url;
    }

}

/*
 * get redirect page after login page url function
 */
if (!function_exists('ct_start_page_url'))
{


    function ct_start_page_url()
    {
        global $ct_options;
        $start_page_url = '';
        if (!empty($ct_options['redirect_page']))
        {
            $start_page_url = ct_get_permalink_clang($ct_options['redirect_page']);
        } else
        {
            $start_page_url = esc_url(home_url('/'));
        }
        return $start_page_url;
    }

}

/*
 * get wishlist page url function
 */
if (!function_exists('ct_wishlist_page_url'))
{


    function ct_wishlist_page_url()
    {
        global $ct_options;

        $wishlist_page = '';
        if (!empty($ct_options['wishlist']))
        {
            $wishlist_page = ct_get_permalink_clang($ct_options['wishlist']);
        }

        return $wishlist_page;
    }

}

/*
 * get header image src function
 */
if (!function_exists('ct_get_header_image_src'))
{
    function ct_get_header_image_src($post_id = 0, $size = 'full')
    {
        global $ct_options;

        if (empty($post_id))
        {
            global $post;
            $post_id = $post->ID;
        }

        $header_img_ids = get_post_meta($post_id, '_header_image', true);
        if (!empty($header_img_ids))
        {
            $header_img = wp_get_attachment_image_src($header_img_ids, $size);
            return $header_img[0];
        }

        if ('hotel' == get_post_type($post_id))
        {
            if (!empty($ct_options['hotel_header_img']))
                return $ct_options['hotel_header_img']['url'];
        } elseif ('tour' == get_post_type($post_id))
        {
            if (!empty($ct_options['tour_header_img']))
                return $ct_options['tour_header_img']['url'];
        } elseif ('product' == get_post_type($post_id))
        {
            if (!empty($ct_options['product_header_img']))
                return $ct_options['product_header_img']['url'];
        } elseif ('post' == get_post_type($post_id))
        {
            if (!empty($ct_options['post_header_img']))
                return $ct_options['post_header_img']['url'];
        } elseif ('page' == get_post_type($post_id))
        {
            if (!empty($ct_options['page_header_img']))
                return $ct_options['page_header_img']['url'];
        }

        if (is_post_type_archive('tour'))
        {
            if (!empty($ct_options['tour_header_img']))
                return $ct_options['tour_header_img']['url'];
        } elseif (is_post_type_archive('hotel'))
        {
            if (!empty($ct_options['hotel_header_img']))
                return $ct_options['hotel_header_img']['url'];
        }
        elseif (class_exists('WooCommerce') && is_shop())
        {
            if (!empty($ct_options['shop_header_img']))
                return $ct_options['shop_header_img']['url'];
        } elseif (is_archive())
        {
            if (!empty($ct_options['blog_header_img']))
                return $ct_options['blog_header_img']['url'];
        }

        if (!empty($ct_options['header_img']))
        {
            return $ct_options['header_img']['url'];
        }

        return '';
    }

}

/*
 * return extra body classes after check if there's header image
 */
if (!function_exists('ct_get_extra_body_class'))
{


    function ct_get_extra_body_class()
    {
        global $post, $ct_options;

        $body_class = '';

        if (is_single())
        {
            if (is_singular('product'))
            {
                $enable_header_img = get_post_meta($post->ID, '_show_header_image', true);

                if ($enable_header_img == 'show' || empty($enable_header_img))
                {
                    $header_img_scr = ct_get_header_image_src($post->ID);

                    if (empty($header_img_scr))
                    {
                        $body_class = 'no-header-image';
                    }
                } else
                {
                    $body_class = 'no-header-image';
                }
            } else
            {
                $header_img_scr = ct_get_header_image_src($post->ID);

                if (empty($header_img_scr))
                {
                    $body_class = 'no-header-image';
                }
            }
        } else if (is_archive() || is_tax())
        {
            $post_type = '';

            if (is_post_type_archive('post') || is_category())
            {
                $post_type = 'blog';
            } else if (is_post_type_archive('hotel') || is_tax(array('district', 'hotel_facility')))
            {
                $post_type = 'hotel';
            } else if (is_post_type_archive('tour') || is_tax(array('tour_type', 'tour_facility')))
            {
                $post_type = 'tour';
            } else if (is_post_type_archive('product') || is_tax('product_cat'))
            {
                $post_type = 'shop';
            }
            $header_img_scr = ct_get_header_image_src($post_type);

            if (empty($header_img_scr))
            {
                $body_class = 'no-header-image';
            }
        }

        if (isset($ct_options['header_sticky']) && empty($ct_options['header_sticky']))
        {
            $body_class .= ' no-sticky-header';
        }

        return $body_class;
    }

}

/*
 * get header image height function
 */
if (!function_exists('ct_get_header_image_height'))
{


    function ct_get_header_image_height($post_id = 0)
    {
        global $ct_options;

        if (empty($post_id))
        {
            global $post;
            $post_id = $post->ID;
        }

        $post_header_img_height = get_post_meta($post_id, '_header_image_height', true);
        if (!empty($post_header_img_height) && is_numeric($post_header_img_height))
        {
            return $post_header_img_height;
        }

        if ('hotel' == get_post_type($post_id))
        {
            if (!empty($ct_options['hotel_header_img_height']))
                return intval($ct_options['hotel_header_img_height']['height']);
        } elseif ('tour' == get_post_type($post_id))
        {
            if (!empty($ct_options['tour_header_img_height']))
                return intval($ct_options['tour_header_img_height']['height']);
        } elseif ('product' == get_post_type($post_id))
        {
            if (!empty($ct_options['product_header_img_height']))
                return intval($ct_options['product_header_img_height']['height']);
        } elseif ('post' == get_post_type($post_id))
        {
            if (!empty($ct_options['post_header_img_height']))
                return intval($ct_options['post_header_img_height']['height']);
        } elseif ('page' == get_post_type($post_id))
        {
            if (!empty($ct_options['page_header_img_height']))
                return intval($ct_options['page_header_img_height']['height']);
        }

        if (is_post_type_archive('tour'))
        {
            if (!empty($ct_options['tour_header_img_height']))
                return intval($ct_options['tour_header_img_height']['height']);
        } elseif (is_post_type_archive('hotel'))
        {
            if (!empty($ct_options['hotel_header_img_height']))
                return intval($ct_options['hotel_header_img_height']['height']);
        } elseif (class_exists('WooCommerce') && is_shop())
        {
            if (!empty($ct_options['shop_header_img_height']))
                return intval($ct_options['shop_header_img_height']['height']);
        } elseif (is_archive())
        {
            if (!empty($ct_options['blog_header_img_height']))
                return intval($ct_options['blog_header_img_height']['height']);
        }

        if (!empty($ct_options['header_img_height']))
        {
            return intval($ct_options['header_img_height']['height']);
        }

        return 200;
    }

}

/*
 * get header content
 */
if (!function_exists('ct_get_header_content'))
{


    function ct_get_header_content($post_id = 0)
    {
        global $ct_options;

        if (empty($post_id))
        {
            global $post;
            $post_id = $post->ID;
        }

        $post_header_content = get_post_meta($post_id, '_header_content', true);

        if (!empty($post_header_content))
        {
            return $post_header_content;
        }


        if ('hotel' == get_post_type($post_id))
        {
            if (!empty($ct_options['hotel_header_content']))
                return $ct_options['hotel_header_content'];
        } elseif ('tour' == get_post_type($post_id))
        {
            if (!empty($ct_options['tour_header_content']))
                return $ct_options['tour_header_content'];
        } elseif ('product' == get_post_type($post_id))
        {
            if (!empty($ct_options['product_header_content']))
                return $ct_options['product_header_content'];
        } elseif ('post' == get_post_type($post_id))
        {
            if (!empty($ct_options['post_header_content']))
                return $ct_options['post_header_content'];
        } elseif ('page' == get_post_type($post_id))
        {
            if (!empty($ct_options['page_header_content']))
                return $ct_options['page_header_content'];
        }

        if (is_post_type_archive('tour'))
        {
            if (!empty($ct_options['tour_header_content']))
                return $ct_options['tour_header_content'];
        } elseif (is_post_type_archive('hotel'))
        {
            if (!empty($ct_options['hotel_header_content']))
                return $ct_options['hotel_header_content'];
        } elseif (class_exists('WooCommerce') && is_shop())
        {
            if (!empty($ct_options['shop_header_content']))
                return $ct_options['shop_header_content'];
        } elseif (is_archive())
        {
            if (!empty($ct_options['blog_header_conetent']))
                return $ct_options['blog_header_conetent'];
        }

        if ($post_id == 'blog')
        {
            return $ct_options['blog_header_content'];
        }

        if (!empty($ct_options['header_content']))
        {
            return $ct_options['header_content'];
        }

        return '';
    }

}

if (!function_exists('ct_get_header_logo_height'))
{


    function ct_get_header_logo_height()
    {
        global $ct_options;
        $height = 34;
        if (!empty($ct_options['logo_size_header']) && is_array($ct_options['logo_size_header']) && isset($ct_options['logo_size_header']['height']))
        {
            $height = intval($ct_options['logo_size_header']['height']);
            if (empty($height))
                $height = 34;
        }
        return $height;
    }

}

if (!function_exists('ct_get_header_logo_width'))
{


    function ct_get_header_logo_width()
    {
        global $ct_options;
        $width = 160;
        if (!empty($ct_options['logo_size_header']) && is_array($ct_options['logo_size_header']) && isset($ct_options['logo_size_header']['width']))
        {
            $width = intval($ct_options['logo_size_header']['width']);
            if (empty($width))
                $width = 160;
        }
        return $width;
    }

}


/*
 * get header image height function
 */
if (!function_exists('ct_get_sidebar_position'))
{


    function ct_get_sidebar_position($post_id = 0)
    {
        global $ct_options;
        if (empty($post_id))
        {
            global $post;
            $post_id = $post->ID;
        }
        if ('blog' == $post_id)
        {
            return (!empty($ct_options['blog_page_layout']) ) ? $ct_options['blog_page_layout'] : 'left';
        } else if ('shop' == $post_id)
        {
            return (!empty($ct_options['shop_page_layout']) ) ? $ct_options['shop_page_layout'] : 'left';
        } else
        {
            $sidebar_position = get_post_meta($post_id, '_ct_sidebar_position', true);
            if (!empty($sidebar_position) && ( 'default' != $sidebar_position ))
                return $sidebar_position;
            if ('post' == get_post_type($post_id))
            {
                return (!empty($ct_options['def_post_layout']) ) ? $ct_options['def_post_layout'] : 'left';
            } else if ('page' == get_post_type($post_id))
            {
                return (!empty($ct_options['def_page_layout']) ) ? $ct_options['def_page_layout'] : 'left';
            } else if ('product' == get_post_type($post_id))
            {
                return (!empty($ct_options['product_page_layout']) ) ? $ct_options['product_page_layout'] : 'right';
            }
        }
        return 'left';
    }

}

/*
 * get current page url
 */
if (!function_exists('ct_get_current_page_url'))
{


    function ct_get_current_page_url()
    {
        global $wp;
        return esc_url(home_url(add_query_arg(array(), $wp->request)));
    }

}

/*
 * template locate and include function
 */
if (!function_exists('ct_get_template'))
{


    function ct_get_template($template_name, $template_path = '')
    {
        $template = locate_template(
                array(
                    trailingslashit($template_path) . $template_name,
                    $template_name
                )
        );
        include( $template );
    }

}

/*
 * redirect home function
 */
if (!function_exists('ct_redirect_home'))
{


    function ct_redirect_home()
    {
        wp_redirect(esc_url(home_url('/')));
        exit;
    }

}

/*
 * redirect home function
 */
if (!function_exists('ct_redirect_hotels'))
{

    function ct_redirect_hotels()
    {
        wp_redirect(esc_url(home_url('/hotel')));
        exit;
    }

}

/*
 * init function
 */
if (!function_exists('ct_init'))
{


    function ct_init()
    {
        ob_start();
        // register header nav menu location
        register_nav_menu('header-menu', 'Header Menu');

        add_action('redux/citytours/panel/before', 'ct_one_click_install_main_pages');
    }

}

/*
 * body_class filter
 */
if (!function_exists('ct_body_class'))
{


    function ct_body_class($classes)
    {
        return $classes;
    }

}

/*
 * body_class filter
 */
if (!function_exists('ct_inline_script'))
{


    function ct_inline_script()
    {
        global $ct_options;
        if (!empty($ct_options['custom_js']))
        {
            echo '<script>' . $ct_options['custom_js'] . '</script>';
        }
    }

}

/*
 * get custom css to add inline css
 */
if (!function_exists('ct_get_custom_css'))
{


    function ct_get_custom_css()
    {
        global $ct_options;
        $custom_css = "";
        // site custom css
        if (!empty($ct_options['custom_css']))
        {
            $custom_css .= $ct_options['custom_css'];
        }
        // post custom css
        $custom_css .= get_post_meta(get_queried_object_id(), '_custom_css', true);
        return $custom_css;
    }

}

/*
 * enqueue script function
 */
if (!function_exists('ct_enqueue_scripts'))
{

    function ct_enqueue_scripts()
    {
        global $ct_options;

        $custom_css = ct_get_custom_css();

        $depends = array();
        if (class_exists('Vc_Manager', false))
        {
            $depends[] = 'js_composer_front';
        }

        $suffix = defined('WP_DEBUG') && WP_DEBUG ? '' : '.min';

        $current_locale = get_locale();
        $current_locale = str_replace('_', '-', $current_locale);

        // wp_register_style('ct_style_main', CT_TEMPLATE_DIRECTORY_URI . '/css/base.css');
        // wp_register_style('ct_style_main', CT_TEMPLATE_DIRECTORY_URI . '/css/mq_base.css');
        wp_register_style('ct_style_main', CT_TEMPLATE_DIRECTORY_URI . '/css/mq_base.css', array(), null, 'print');
        /* MQ Css Code */
        wp_register_style('mq_bootstrap_min', CT_TEMPLATE_DIRECTORY_URI . '/css/bootstrap.min.css');
        wp_register_style('mq_style_min', CT_TEMPLATE_DIRECTORY_URI . '/css/style.min.css');
        wp_register_style('mq_menu_min', CT_TEMPLATE_DIRECTORY_URI . '/css/menu.min.css');
        wp_register_style('mq_custom', CT_TEMPLATE_DIRECTORY_URI . '/css/custom.css');
        /* HELLO */
        wp_register_style('ct_style_cus', CT_TEMPLATE_DIRECTORY_URI . '/css/cus.css');
        wp_register_style('ct_style_lightslider', CT_TEMPLATE_DIRECTORY_URI . '/css/lightslider.css');
        wp_register_style('ct_style_grey', CT_TEMPLATE_DIRECTORY_URI . '/css/skins/square/grey.css');

        wp_register_style('ct_style_date_time_picker', CT_TEMPLATE_DIRECTORY_URI . '/css/date_time_picker' . $suffix . '.css');
        wp_register_style('ct_style_owl_carousel', CT_TEMPLATE_DIRECTORY_URI . '/css/owl.carousel' . $suffix . '.css', array(), null, 'print');
        wp_register_style('ct_style_owl_theme', CT_TEMPLATE_DIRECTORY_URI . '/css/owl.theme' . $suffix . '.css');
//        wp_register_style('ct_style_timeline', CT_TEMPLATE_DIRECTORY_URI . '/css/timeline' . $suffix . '.css');
        wp_register_style('ct_style_jquery_switch', CT_TEMPLATE_DIRECTORY_URI . '/css/jquery.switch' . $suffix . '.css', array(), null, 'print');
        wp_register_style('ct_style_jquery_persian', CT_TEMPLATE_DIRECTORY_URI . '/css/persian-datepicker-0.4.5.min.css', array(), null, 'print');

        wp_register_style('ct_style_fontawesome', CT_TEMPLATE_DIRECTORY_URI . '/css/font-awesome/css/font-awesome' . $suffix . '.css');

        if (current_user_can('edit_theme_options'))
        {
            wp_register_style('ct_admin_bar', CT_TEMPLATE_DIRECTORY_URI . '/css/admin/admin_bar.css');
        }

        wp_register_style('ct_gootle_fonts', ct_theme_slug_fonts_url());
        wp_register_style('ct_child_theme_css', get_stylesheet_directory_uri() . '/style.css'); //register default style.css file. only include in childthemes. has no purpose in main theme

        wp_enqueue_style('ct_style_main');
        add_filter('style_loader_tag', function($html, $handle) {
            if ($handle === 'ct_style_main') {
                return str_replace("media='print'", "media='print' onload=\"this.media='all'\"", $html);
            }
            return $html;
        }, 10, 2);
        wp_enqueue_style('mq_bootstrap_min');
        wp_enqueue_style('mq_style_min');
        wp_enqueue_style('mq_menu_min');
        wp_enqueue_style('mq_custom');
        wp_enqueue_style('ct_style_cus');
        wp_enqueue_style('ct_style_lightslider');
        wp_enqueue_style('ct_style_grey');

        wp_enqueue_style('ct_style_date_time_picker');
//        wp_enqueue_style('ct_style_timeline');
        wp_enqueue_style('ct_style_jquery_switch');
        add_filter('style_loader_tag', function($html, $handle) {
            if ($handle === 'ct_style_jquery_switch') {
                return str_replace("media='print'", "media='print' onload=\"this.media='all'\"", $html);
            }
            return $html;
        }, 10, 2);
        wp_enqueue_style('ct_style_owl_carousel');
        add_filter('style_loader_tag', function($html, $handle) {
            if ($handle === 'ct_style_owl_carousel') {
                return str_replace("media='print'", "media='print' onload=\"this.media='all'\"", $html);
            }
            return $html;
        }, 10, 2);
        wp_enqueue_style('ct_style_owl_theme');

        wp_enqueue_style('ct_gootle_fonts');

        if (current_user_can('edit_theme_options'))
        {
            wp_enqueue_style('ct_admin_bar');
        }

//         rtl css
        $is_rtl = 'false';
        if (is_rtl())
        {
            $is_rtl = 'true';
            wp_enqueue_style('ct_rtl_bootstrap', CT_TEMPLATE_DIRECTORY_URI . '/css/rtl/bootstrap-rtl' . $suffix . '.css');
            // wp_enqueue_style( 'ct_rtl_jqueryui',  CT_TEMPLATE_DIRECTORY_URI . "/css/rtl/jquery-no-theme-rtl.css" );
            wp_enqueue_style('ct_rtl', CT_TEMPLATE_DIRECTORY_URI . '/css/rtl/rtl' . $suffix . '.css');

            if (substr($current_locale, 0, 2) == 'fa')
            {
                wp_enqueue_style('ct_style_jquery_persian');
            }
        }

        // child theme css
        if (get_stylesheet_directory_uri() != CT_TEMPLATE_DIRECTORY_URI)
        {
            wp_enqueue_style('ct_child_theme_css');
        }

        if (!empty($ct_options['skin']) && $ct_options['skin'] != 'red')
        {
            wp_enqueue_style('ct_style_skin', CT_TEMPLATE_DIRECTORY_URI . '/css/color-' . $ct_options['skin'] . $suffix . '.css');
        }

        // custom css
        wp_add_inline_style('ct_style_main', $custom_css);
        wp_enqueue_style('ct_style_fontawesome');
        // main js
        wp_register_script('ct_script_common', CT_TEMPLATE_DIRECTORY_URI . '/js/common_scripts' . $suffix . '.js', array('jquery'), '', true);
        wp_register_script('ct_theme_script', CT_TEMPLATE_DIRECTORY_URI . '/js/functions' . $suffix . '.js', array('jquery'), '2.2.4', true);
        wp_localize_script('ct_theme_script', 'ajaxurl', array(admin_url('admin-ajax.php')));
        wp_localize_script('ct_theme_script', 'is_rtl', array($is_rtl));

        // date and time picker
        wp_register_script('ct_script_datepicker', CT_TEMPLATE_DIRECTORY_URI . '/js/bootstrap-datepicker' . $suffix . '.js', array('jquery'), '', true);
        wp_register_script('ct_script_timepicker', CT_TEMPLATE_DIRECTORY_URI . '/js/bootstrap-timepicker' . $suffix . '.js', array('jquery'), '', true);

        wp_localize_script('ct_script_datepicker', 'is_rtl', array($is_rtl));

        if ($current_locale != 'en')
        {
            $file_name = '/js/locales/bootstrap-datepicker.' . $current_locale . '.min.js';
            $file_name2 = '/js/locales/bootstrap-datepicker.' . substr($current_locale, 0, 2) . '.min.js';
            if ($file_exists = file_exists(get_template_directory() . $file_name))
            {
                wp_register_script('ct_script_localization', CT_TEMPLATE_DIRECTORY_URI . $file_name, array('jquery'), '', true);
            } else if ($file_exists = file_exists(get_template_directory() . $file_name2))
            {
                wp_register_script('ct_script_localization', CT_TEMPLATE_DIRECTORY_URI . $file_name2, array('jquery'), '', true);
            } else
            {
                if (substr($current_locale, 0, 2) == 'fa')
                {
                    wp_register_script('ct_script_localization1', CT_TEMPLATE_DIRECTORY_URI . '/js/persian-date' . $suffix . '.js', array('jquery'), '', true);
                    wp_register_script('ct_script_localization2', CT_TEMPLATE_DIRECTORY_URI . '/js/persian-datepicker-0.4.5.min.js', array('jquery'), '', true);
                }
            }
        }

        // minor js
        wp_register_script('ct_script_icheck', CT_TEMPLATE_DIRECTORY_URI . '/js/icheck' . $suffix . '.js', array(), '', true);
        wp_register_script('ct_script_jquery_validate', CT_TEMPLATE_DIRECTORY_URI . '/js/jquery.validate.min.js', array('jquery'), '', true);

        // fixed sidebar js
        wp_register_script('ct_script_fixed_sidebar', CT_TEMPLATE_DIRECTORY_URI . '/js/theia-sticky-sidebar' . $suffix . '.js', array(), '', true);

        // map
        if (!empty($ct_options['map_api_key']))
        {
            wp_register_script('ct_script_google_map', 'https://maps.googleapis.com/maps/api/js?key=' . $ct_options['map_api_key'], array(), '', true);
        } else
        {
            wp_register_script('ct_script_google_map', 'https://maps.googleapis.com/maps/api/js', array(), '', true);
        }
        
        wp_register_script('ct_script_infobox', CT_TEMPLATE_DIRECTORY_URI . '/js/infobox' . $suffix . '.js', array(), '', true);
        wp_register_script('ct_script_map', CT_TEMPLATE_DIRECTORY_URI . '/js/map.min.js', array(), '', true);
        wp_localize_script('ct_script_map', 'theme_url', array(CT_TEMPLATE_DIRECTORY_URI));
        wp_localize_script('ct_script_map', 'button_text', array(__('Details', 'citytours')));

        if (!empty($ct_options['tour_map_icon']))
        {
            wp_localize_script('ct_script_map', 'tour_icon', $ct_options['tour_map_icon']);
        }
        if (!empty($ct_options['hotel_map_icon']))
        {
            wp_localize_script('ct_script_map', 'hotel_icon', $ct_options['hotel_map_icon']);
        }

        wp_register_script('ct_script_modernizr', CT_TEMPLATE_DIRECTORY_URI . '/js/modernizr' . $suffix . '.js', array(), '', true);
        wp_register_script('ct_script_owl', CT_TEMPLATE_DIRECTORY_URI . '/js/owl.carousel.min.js', array(), '', true);
        wp_register_script('ct_cat_nav_mobile', CT_TEMPLATE_DIRECTORY_URI . '/js/cat_nav_mobile' . $suffix . '.js', array(), '', true);

        wp_enqueue_script('ct_script_common');
        wp_enqueue_script('ct_script_jquery_validate');
        wp_enqueue_script('ct_script_datepicker');

        if ($current_locale != 'en' && $file_exists)
        {
            wp_enqueue_script('ct_script_localization');
        } else if (substr($current_locale, 0, 2) == 'fa')
        {
            wp_enqueue_script('ct_script_localization1');
            wp_enqueue_script('ct_script_localization2');
        }

        wp_enqueue_script('ct_script_owl');
        $needs_map = is_singular(array('hotel', 'tour')) || is_post_type_archive(array('hotel', 'tour')) || is_page_template('contact.php');
        if ($needs_map) {
            wp_enqueue_script('ct_script_map');
            wp_enqueue_script('ct_script_infobox');
        }

        if (is_singular(array('tour', 'hotel')))
        {
            // wp_enqueue_script( 'ct_script_timepicker' );
        }
        if (is_post_type_archive('tour'))
        {
            wp_enqueue_script('ct_cat_nav_mobile');
        }

        wp_enqueue_script('ct_script_fixed_sidebar');
        wp_enqueue_script('ct_script_icheck');

        wp_enqueue_script('ct_theme_script');
    }

}

/*
 * admin enqueue script function
 */
if (!function_exists('ct_admin_scripts'))
{


    function ct_admin_scripts()
    {

        wp_register_style('ct_admin_bar', CT_TEMPLATE_DIRECTORY_URI . '/css/admin/admin_bar.css');
        wp_register_style('ct_admin_css', CT_TEMPLATE_DIRECTORY_URI . '/css/admin/admin.css');

        wp_enqueue_style('ct_admin_bar');
        wp_enqueue_style('ct_admin_css');
    }

}

/*
 * remove redux tools page
 */
if (!function_exists('ct_remove_redux_menu'))
{


    function ct_remove_redux_menu()
    {
        remove_submenu_page('tools.php', 'redux-about');
    }

}

/*
 * function to register required plugins
 */
if (!function_exists('ct_register_required_plugins'))
{


    function ct_register_required_plugins()
    {
        $plugins = array(
            array(
                'name' => 'CTBooking',
                'slug' => 'ct-booking',
                'source' => CT_INC_DIR . '/plugins/ct-booking.zip',
                'required' => true,
                'version' => '1.2',
                'force_activation' => false,
                'force_deactivation' => false,
                'external_url' => '',
                'image_url' => CT_TEMPLATE_DIRECTORY_URI . '/img/admin/plugins/ct_booking.jpg'
            ),
            array(
                'name' => 'WPBakery Page Builder',
                'slug' => 'js_composer',
                'source' => CT_INC_DIR . '/plugins/js_composer.zip',
                'required' => true,
                'version' => '5.4.7',
                'force_activation' => false,
                'force_deactivation' => false,
                'external_url' => '',
                'image_url' => CT_TEMPLATE_DIRECTORY_URI . '/img/admin/plugins/visual_composer.jpg'
            ),
            array(
                'name' => 'Slider Pro',
                'slug' => 'sliderpro',
                'source' => CT_INC_DIR . '/plugins/slider-pro-responsive-wordpress-slider-plugin.zip',
                'required' => true,
                'version' => '4.5.0',
                'force_activation' => false,
                'force_deactivation' => false,
                'external_url' => '',
                'image_url' => CT_TEMPLATE_DIRECTORY_URI . '/img/admin/plugins/slider_pro.jpg'
            ),
            array(
                'name' => 'Revolution Slider',
                'slug' => 'revslider',
                'source' => CT_INC_DIR . '/plugins/revslider.zip',
                'required' => true,
                'version' => '5.4.7.2',
                'force_activation' => false,
                'force_deactivation' => false,
                'external_url' => '',
                'image_url' => CT_TEMPLATE_DIRECTORY_URI . '/img/admin/plugins/revolution_slider.jpg'
            ),
        );

        $config = array(
            'default_path' => '',
            'menu' => 'install-required-plugins',
            'has_notices' => true,
            'dismissable' => true,
            'dismiss_msg' => '',
            'is_automatic' => false,
            'message' => '',
            'strings' => array(
                'page_title' => esc_html__('Install Required Plugins', 'citytours'),
                'menu_title' => esc_html__('Install Plugins', 'citytours'),
                'installing' => esc_html__('Installing Plugin: %s', 'citytours'), // %s = plugin name.
                'oops' => esc_html__('Something went wrong with the plugin API.', 'citytours'),
                'notice_can_install_required' => _n_noop('This theme requires the following plugin: %1$s.', 'This theme requires the following plugins: %1$s.', 'citytours'), // %1$s = plugin name(s).
                'notice_can_install_recommended' => _n_noop('This theme recommends the following plugin: %1$s.', 'This theme recommends the following plugins: %1$s.', 'citytours'), // %1$s = plugin name(s).
                'notice_cannot_install' => _n_noop('Sorry, but you do not have the correct permissions to install the %s plugin. Contact the administrator of this site for help on getting the plugin installed.', 'Sorry, but you do not have the correct permissions to install the %s plugins. Contact the administrator of this site for help on getting the plugins installed.', 'citytours'),
                'notice_can_activate_required' => _n_noop('The following required plugin is currently inactive: %1$s.', 'The following required plugins are currently inactive: %1$s.', 'citytours'),
                'notice_can_activate_recommended' => _n_noop('The following recommended plugin is currently inactive: %1$s.', 'The following recommended plugins are currently inactive: %1$s.', 'citytours'),
                'notice_cannot_activate' => _n_noop('Sorry, but you do not have the correct permissions to activate the %s plugin. Contact the administrator of this site for help on getting the plugin activated.', 'Sorry, but you do not have the correct permissions to activate the %s plugins. Contact the administrator of this site for help on getting the plugins activated.', 'citytours'),
                'notice_ask_to_update' => _n_noop('The following plugin needs to be updated to its latest version to ensure maximum compatibility with this theme: %1$s.', 'The following plugins need to be updated to their latest version to ensure maximum compatibility with this theme: %1$s.', 'citytours'),
                'notice_cannot_update' => _n_noop('Sorry, but you do not have the correct permissions to update the %s plugin. Contact the administrator of this site for help on getting the plugin updated.', 'Sorry, but you do not have the correct permissions to update the %s plugins. Contact the administrator of this site for help on getting the plugins updated.', 'citytours'),
                'install_link' => _n_noop('Begin installing plugin', 'Begin installing plugins', 'citytours'),
                'activate_link' => _n_noop('Begin activating plugin', 'Begin activating plugins', 'citytours'),
                'return' => esc_html__('Return to Required Plugins Installer', 'citytours'),
                'plugin_activated' => esc_html__('Plugin activated successfully.', 'citytours'),
                'complete' => esc_html__('All plugins installed and activated successfully. %s', 'citytours'),
                'nag_type' => 'updated'
            )
        );

        tgmpa($plugins, $config);
    }

}

/*
 * wp_title filter for old version
 */
if (!function_exists('ct_wp_title_old'))
{

    function ct_wp_title_old($title, $sep)
    {
        if (is_feed())
        {
            return $title;
        }

        if (is_page_template('templates/template-login.php'))
        {
            if (isset($_GET['action']) && ( $_GET['action'] == 'register' ))
            {
                $title = esc_html__('Registration Form', 'citytours');
            } else if (isset($_GET['action']) && ( $_GET['action'] == 'lostpassword' ))
            {
                $title = esc_html__('Lost Password', 'citytours');
            } else
            {
                $title = esc_html__('Login', 'citytours');
            }
            $title .= ' - ';
        }

        if (get_query_var('paged'))
        {
            $paged = get_query_var('paged');
        } elseif (get_query_var('page'))
        {
            $paged = get_query_var('page');
        } else
        {
            $paged = 1;
        }

        // Add the blog name
        $title .= get_bloginfo('name', 'display');

        // Add the blog description for the home/front page.
        $site_description = get_bloginfo('description', 'display');
        if ($site_description && ( is_home() || is_front_page() ))
        {
            $title .= " $sep $site_description";
        }

        // Add a page number if necessary:
        if (( is_numeric($paged) ) && ( $paged >= 2 ) && !is_404())
        {
            $title .= " $sep " . sprintf(esc_html__('Page %s', 'citytours'), $paged);
        }

        return $title;
    }

}

/*
 * wp_title filter
 */
if (!function_exists('ct_wp_title'))
{

    function ct_wp_title($title, $sep)
    {
        if (is_feed())
        {
            return $title;
        }

        if (is_page_template('templates/template-login.php'))
        {
            if (isset($_GET['action']) && ( $_GET['action'] == 'register' ))
            {
                $title = esc_html__('Registration Form', 'citytours');
            } else if (isset($_GET['action']) && ( $_GET['action'] == 'lostpassword' ))
            {
                $title = esc_html__('Lost Password', 'citytours');
            } else
            {
                $title = esc_html__('Login', 'citytours');
            }
            $title .= ' - ';
            $title .= get_bloginfo('name', 'display');
        }
        else {
            $title = get_bloginfo('name');
        }

        return $title;
    }

}

/*
 * register side bar
 */
if (!function_exists('ct_register_sidebar'))
{
    function ct_register_sidebar()
    {

        $args = array(
            'name' => esc_html__('Blog Sidebar', 'citytours'),
            'id' => 'sidebar-post',
            'description' => '',
            'class' => '',
//            'before_widget' => '<div id="%1$s" class="widget %2$s">',
            'before_widget' => '<div class="widget %2$s">',
            'after_widget' => '</div><hr>',
//            'before_title' => '<h4 class="widgettitle">',
            'before_title' => '<h4>',
            'after_title' => '</h4>');

        register_sidebar($args);

        $args = array(
            'name' => esc_html__('Hotel Sidebar', 'citytours'),
            'id' => 'sidebar-hotel',
            'description' => __('This will be shown on the hotel detail page', 'citytours'),
            'class' => '',
            'before_widget' => '<div id="%1$s" class="widget %2$s">',
            'after_widget' => '</div><hr>',
            'before_title' => '<h4 class="widgettitle">',
            'after_title' => '</h4>');
        register_sidebar($args);

        $args = array(
            'name' => esc_html__('Tour Sidebar', 'citytours'),
            'id' => 'sidebar-tour',
            'description' => __('This will be shown on the tour detail page', 'citytours'),
            'class' => '',
            'before_widget' => '<div id="%1$s" class="widget %2$s">',
            'after_widget' => '</div><hr>',
            'before_title' => '<h4 class="widgettitle">',
            'after_title' => '</h4>');
        register_sidebar($args);

        if (class_exists('WooCommerce'))
        {
            $args = array(
                'name' => esc_html__('WooCommerce Category Sidebar', 'citytours'),
                'id' => 'sidebar-woo-category',
                'description' => __('This will be shown on the WooCommerce Shop/Category page', 'citytours'),
                'class' => '',
                'before_widget' => '<div id="%1$s" class="widget %2$s">',
                'after_widget' => '</div><hr>',
                'before_title' => '<h4 class="widgettitle">',
                'after_title' => '</h4>');
            register_sidebar($args);

            $args = array(
                'name' => esc_html__('WooCommerce Product Sidebar', 'citytours'),
                'id' => 'sidebar-woo-product',
                'description' => __('This will be shown on the WooCommerce Product page', 'citytours'),
                'class' => '',
                'before_widget' => '<div id="%1$s" class="widget %2$s">',
                'after_widget' => '</div><hr>',
                'before_title' => '<h4 class="widgettitle">',
                'after_title' => '</h4>');
            register_sidebar($args);
        }

        $args = array(
            'name' => esc_html__('Footer Widget 1', 'citytours'),
            'id' => 'sidebar-footer-1',
            'description' => '',
            'class' => '',
            'before_widget' => '<div id="%1$s" class="small-box %2$s">',
            'after_widget' => '</div>',
            'before_title' => '<h3 class="widgettitle">',
            'after_title' => '</h3>');
        register_sidebar($args);

        $args = array(
            'name' => esc_html__('Footer Widget 2', 'citytours'),
            'id' => 'sidebar-footer-2',
            'description' => '',
            'class' => '',
            'before_widget' => '<div id="%1$s" class="small-box %2$s">',
            'after_widget' => '</div>',
            'before_title' => '<h3 class="widgettitle">',
            'after_title' => '</h3>');
        register_sidebar($args);

        $args = array(
            'name' => esc_html__('Footer Widget 3', 'citytours'),
            'id' => 'sidebar-footer-3',
            'description' => '',
            'class' => '',
            'before_widget' => '<div id="%1$s" class="small-box %2$s">',
            'after_widget' => '</div>',
            'before_title' => '<h3 class="widgettitle">',
            'after_title' => '</h3>');
        register_sidebar($args);

        $args = array(
            'name' => esc_html__('Footer Widget 4', 'citytours'),
            'id' => 'sidebar-footer-4',
            'description' => '',
            'class' => '',
            'before_widget' => '<div id="%1$s" class="small-box %2$s">',
            'after_widget' => '</div>',
            'before_title' => '<h3 class="widgettitle">',
            'after_title' => '</h3>');
        register_sidebar($args);

        $args = array(
            'name' => esc_html__('FAQ', 'citytours'),
            'id' => 'ct-custom-sidebar-faq',
            'description' => '',
            'class' => '',
            'before_widget' => '<div id="%1$s" class="small-box %2$s">',
            'after_widget' => '</div>',
            'before_title' => '<h3 class="widgettitle">',
            'after_title' => '</h3>');
        register_sidebar($args);

//        $args = array(
//            'name' => esc_html__('Flight Sidebar', 'citytours'),
//            'id' => 'ct-custom-sidebar-flight',
//            'description' => '',
//            'class' => '',
//            'before_widget' => '<div id="%1$s" class="small-box %2$s">',
//            'after_widget' => '</div>',
//            'before_title' => '<h3 class="widgettitle">',
//            'after_title' => '</h3>');
//        register_sidebar($args);
    }

}

/*
 * Breadcrumbs
 */
if (!function_exists('ct_breadcrumbs'))
{

    function ct_breadcrumbs()
    {
        global $post;

        if (is_home())
        {

        } else
        {
            echo '<ul>';

            if (!is_front_page())
            {
                echo '<li><a href="' . esc_url(home_url('/')) . '" title="' . esc_attr__('Home', 'citytours') . '">' . esc_html__('Home', 'citytours') . '</a></li>';

                if (is_archive())
                {
                    if ('tour' == $post->post_type)
                    {
                        echo '<li><a href="' . get_post_type_archive_link('tour') . '" title="' . __('Tours', 'citytours') . '">' . __('Tours', 'citytours') . '</a></li>';
                    } elseif ('hotel' == $post->post_type)
                    {
                        echo '<li><a href="' . get_post_type_archive_link('hotel') . '" title="' . __('Hotels', 'citytours') . '">' . __('Hotels', 'citytours') . '</a></li>';
                    } elseif ('visa' == $post->post_type)
                    {
                        echo '<li><a href="' . get_post_type_archive_link('visa') . '" title="' . esc_attr__('Visas', 'citytours') . '">' . esc_html__('Visas', 'citytours') . '</a></li>';
                    }
                }
            }

            if (is_single())
            {
                if (( 'post' == $post->post_type))
                {
                    // default blog post breadcrumb
                    $categories_1 = get_the_category($post->ID);

                    if ($categories_1):
                        foreach ($categories_1 as $cat_1):
                            $cat_1_ids[] = $cat_1->term_id;
                        endforeach;
                        $cat_1_line = implode(',', $cat_1_ids);
                    endif;

                    $categories = get_categories(array(
                        'include' => $cat_1_line,
                        'orderby' => 'id'
                    ));

                    if ($categories) :
                        foreach ($categories as $cat) :
                            $cats[] = '<li><a href="' . esc_url(get_category_link($cat->term_id)) . '" title="' . esc_attr($cat->name) . '">' . $cat->name . '</a></li>';
                        endforeach;
                        echo wp_kses_post(join('', $cats));
                    endif;

                    echo '<li class="active">' . esc_html(get_the_title()) . '</li>';
                } else if ('product' == $post->post_type)
                {
                    // product page breadcrumb
                    global $woocommerce;

                    $shop_page_id = 0;
                    if (version_compare($woocommerce->version, '3.0', ">="))
                    {
                        $shop_page_id = wc_get_page_id('shop');
                    } else
                    {
                        $shop_page_id = woocommerce_get_page_id('shop');
                    }

                    echo '<li><a href="' . get_permalink($shop_page_id) . '" title="' . esc_attr(get_the_title($shop_page_id)) . '">' . __('Shop', 'citytours') . '</a></li>';

                    $categories = get_the_terms($post->ID, 'product_cat');
                    $category = '';

                    if ($categories)
                    {
                        $max_count = 0;

                        foreach ($categories as $cat)
                        {
                            if ($max_count < $cat->count)
                            {
                                $max_count = $cat->count;
                                $category = $cat;
                            }
                        }

                        $cats = array();
                        $cats[] = '<li><a href="' . esc_url(get_term_link($category->term_id)) . '" title="' . esc_attr($category->name) . '">' . $category->name . '</a></li>';

                        $parent_cats = get_ancestors($category->term_id, 'product_cat', 'taxonomy');

                        if ($parent_cats)
                        {
                            foreach ($parent_cats as $key => $parent_cat)
                            {
                                $term = get_term($parent_cat, 'product_cat');

                                array_unshift($cats, '<li><a href="' . esc_url(get_term_link($parent_cat)) . '" title="' . esc_attr($term->name) . '">' . $term->name . '</a></li>');
                            }
                        }

                        echo wp_kses_post(join('', $cats));
                    }

                    echo '<li class="active">' . esc_html(get_the_title()) . '</li>';
                } else if ('tour' == $post->post_type)
                {
                    echo '<li><a href="' . get_post_type_archive_link('tour') . '" title="' . __('Tours', 'citytours') . '">' . __('Tours', 'citytours') . '</a></li>';

                    echo '<li class="active">' . esc_html(get_the_title()) . '</li>';
                } else if ('hotel' == $post->post_type)
                {
                    echo '<li><a href="' . get_post_type_archive_link('hotel') . '" title="' . __('Hotels', 'citytours') . '">' . __('Hotels', 'citytours') . '</a></li>';

                    echo '<li class="active">' . esc_html(get_the_title()) . '</li>';
                } else if ('visa' == $post->post_type)
                {
                    echo '<li><a href="' . get_post_type_archive_link('visa') . '" title="' . __('Visa', 'citytours') . '">' . __('Visas', 'citytours') . '</a></li>';

                    echo '<li class="active">' . esc_html(get_the_title()) . '</li>';
                } else {
                    // other single post breadcrumb
                    echo '<li class="active">' . esc_html(get_the_title()) . '</li>';
                }
            }

            if (is_page() && !is_front_page())
            {
                $parents = array();
                $parent_id = $post->post_parent;
                while ($parent_id) :
                    $_page = get_page($parent_id);
                    $parents[] = '<li><a href="' . esc_url(ct_get_permalink_clang($_page->ID)) . '" title="' . esc_attr(get_the_title($_page->ID)) . '">' . esc_html(get_the_title($_page->ID)) . '</a></li>';
                    $parent_id = $_page->post_parent;
                endwhile;
                $parents = array_reverse($parents);
                echo wp_kses_post(join('', $parents));
                echo '<li class="active">' . esc_html(get_the_title()) . '</li>';
            }

            if (is_category())
            {
                $parents = array();
                $category = get_category(get_query_var('cat'));
                $parent_cat = $category;

                while (!empty($parent_cat->parent))
                {
                    $parent_cat = get_category($parent_cat->parent);
                    $parents[] = '<li><a href="' . esc_url(get_category_link($parent_cat->cat_ID)) . '">' . $parent_cat->cat_name . '</a></li>';
                }

                $parents = array_reverse($parents);
                echo wp_kses_post(join('', $parents));

                echo '<li class="active">' . esc_html($category->cat_name) . '</li>';
            }

            if (is_tax())
            {
                if (is_tax('product_cat'))
                {
                    global $woocommerce;

                    $shop_page_id = 0;
                    if (version_compare($woocommerce->version, '3.0', ">="))
                    {
                        $shop_page_id = wc_get_page_id('shop');
                    } else
                    {
                        $shop_page_id = woocommerce_get_page_id('shop');
                    }

                    echo '<li><a href="' . get_permalink($shop_page_id) . '" title="' . get_the_title($shop_page_id) . '">' . __('Shop', 'citytours') . '</a></li>';
                }

                $taxonomy = get_query_var('taxonomy');
                $term = get_term_by('slug', get_query_var('term'), $taxonomy);

                $parents = array();
                $parent_term = $term;

                while (!empty($parent_term->parent)) :
                    $parent_term = get_term($parent_term->parent, $taxonomy);

                    $parents[] = '<li><a href="' . esc_url(get_term_link($parent_term->term_id, $taxonomy)) . '" title="' . esc_attr($parent_term->name) . '">' . esc_html($parent_term->name) . '</a></li>';
                endwhile;

                $parents = array_reverse($parents);
                echo join('', $parents);

                echo '<li class="active">' . esc_html($term->name) . '</li>';
            }

            if (is_tag())
            {
                echo '<li class="active">' . esc_html(single_tag_title('', FALSE)) . '</li>';
            }

            if (is_404())
            {
                echo '<li class="active">' . esc_html__("404 - Page not Found", 'citytours') . '</li>';
            }

            if (is_search())
            {
                echo '<li class="active">';
                echo esc_html__('Search Results', 'citytours');
                echo "</li>";
            }

            if (is_year())
            {
                echo '<li>' . esc_attr(get_the_time('Y')) . '</li>';
            }

            echo '</ul>';
        }
    }

}

/*
 * enqueue comment js
 */
if (!function_exists('ct_enqueue_comment_reply'))
{


    function ct_enqueue_comment_reply()
    {
        if (get_option('thread_comments'))
        {
            wp_enqueue_script('comment-reply');
        }
    }

}

/*
 * comment template
 */
if (!function_exists('ct_comment'))
{


    function ct_comment($comment, $args, $depth)
    {
        $GLOBALS['comment'] = $comment;
        ?>
        <li <?php comment_class(); ?> id="comment-<?php comment_ID() ?>">
            <div class="comment-content">
                <div class="avatar"><a href="#"><?php echo ct_get_avatar(array('id' => $comment->user_id, 'email' => $comment->comment_author_email, 'size' => 68)); ?></a></div>
                <div class="comment_right clearfix">
                    <div class="comment_info">
        <?php echo esc_html__('Posted by', 'citytours') ?> <?php echo get_comment_author_link() ?><span>|</span> <?php comment_date() ?> <span>|</span><?php comment_reply_link(array_merge($args, array('depth' => $depth))); ?>
                    </div>
        <?php comment_text(); ?>
                </div>
            </div>
        <?php
    }

}

/*
 * get avatar function
 */
if (!function_exists('ct_get_avatar'))
{


    function ct_get_avatar($user_data)
    {
        $size = empty($user_data['size']) ? 96 : $user_data['size'];
        $photo = '';
        $class = empty($user_data['class']) ? '' : $user_data['class'];
        if (!empty($user_data['id']))
        {
            $photo_url = get_user_meta($user_data['id'], 'photo_url', true);
            if (!empty($photo_url))
            {
                $photo = '<img width="' . esc_attr($size) . '" height="' . esc_attr($size) . '" alt="avatar" src="' . esc_attr($photo_url) . '" class="' . esc_attr($class) . '">';
            }
        }
        if (empty($photo))
        {
            $photo = ct_get_default_avatar($user_data['email'], $size, $class);
        }
        return $photo;
    }

}

/*
 * check if gravatar exists function
 */
if (!function_exists('ct_get_default_avatar'))
{


    function ct_get_default_avatar($email, $size = 96, $class = '')
    {
        $hash = md5(strtolower(trim($email)));
        $uri = 'http://www.gravatar.com/avatar/' . $hash . '?d=404';
        $headers = @get_headers($uri);
        if (!preg_match("|200|", $headers[0]))
        {
            $photo = '<img width="' . esc_attr($size) . '" height="' . esc_attr($size) . '" alt="avatar" src="' . esc_url(CT_IMAGE_URL . '/avatar.jpg') . '" class="' . esc_attr($class) . '">';
            return $photo;
        }
        return get_avatar($email, $size, array('class' => $class));
    }

}

/*
 * check if gravatar exists function
 */
if (!function_exists('ct_show404'))
{


    function ct_show404($template)
    {
        global $wp_query, $ct_options;
        $template404 = $template;
        if (!empty($ct_options['404_page']))
        {
            $wp_query = null;
            $wp_query = new WP_Query();
            $wp_query->query('page_id=' . $ct_options['404_page']);
            $wp_query->the_post();
            $template404 = get_page_template();
            rewind_posts();
        }
        return $template404;
    }

}

/*
 * business owner user registration
 */
if (!function_exists('ct_user_register'))
{


    function ct_user_register($user_id, $password = "", $meta = array())
    {
        $userdata = array();
        $userdata['ID'] = $user_id;
        if ($_POST['pwd'] !== '')
        {
            $userdata['user_pass'] = $_POST['pwd'];
        }
        $new_user_id = wp_update_user($userdata);
    }

}

/*
 * login failed function
 */
if (!function_exists('ct_login_failed'))
{


    function ct_login_failed($user)
    {
        global $ct_options;
        if (!empty($ct_options['login_page']))
        {
            wp_redirect(add_query_arg(array('login' => 'failed', 'user' => $user), ct_get_permalink_clang($ct_options['login_page'])));
            exit();
        }
    }

}

/*
 * lost password function
 */
if (!function_exists('ct_lost_password'))
{


    function ct_lost_password()
    {
        global $ct_options;
        if (!empty($ct_options['login_page']) && empty($_GET['no_redirect']))
        {
            wp_redirect(add_query_arg($_GET, ct_get_permalink_clang($ct_options['login_page'])));
            exit;
        }
    }

}

/*
 * Authentication function
 */
if (!function_exists('ct_authenticate'))
{


    function ct_authenticate($user, $username, $password)
    {
        global $ct_options;
        if (!empty($ct_options['login_page']) && ( empty($username) || empty($password) ) && empty($_GET['no_redirect']))
        {
            wp_redirect(add_query_arg($_GET, ct_get_permalink_clang($ct_options['login_page'])));
            exit;
        }
    }

}

if (!function_exists('ct_open_comments_for_myposttype'))
{


    function ct_open_comments_for_myposttype($status, $post_type, $comment_type)
    {
        if ('page' !== $post_type)
        {
            return $status;
        }
        return 'open';
    }

}

if (!function_exists('ct_theme_slug_fonts_url'))
{
    function ct_theme_slug_fonts_url()
    {
        $fonts_url = '';
        $montserrat = _x('on', 'Montserrat font: on or off', 'citytours');
        $gochihand = _x('on', 'Gochi+Hand font: on or off', 'citytours');
        $lato = _x('on', 'Lato font: on or off', 'citytours');

        if ('off' !== $montserrat || 'off' !== $gochihand || 'lato' !== $gochihand)
        {
            $font_families = array();
            if ('off' !== $montserrat)
            {
                $font_families[] = 'Montserrat:400,700';
            }
            if ('off' !== $gochihand)
            {
                $font_families[] = 'Gochi+Hand';
            }
            if ('off' !== $lato)
            {
                $font_families[] = 'Lato:300,400';
            }
            $font_families[] ='Cairo:300,400,600,700,900|Roboto+Condensed:300&amp;subset=arabic';
            $query_args = array(
                'family' => urlencode(implode('|', $font_families)),
                'subset' => urlencode('latin,latin-ext'),
            );
            $fonts_url = add_query_arg($query_args, 'https://fonts.googleapis.com/css');
        }

        return esc_url_raw($fonts_url);
    }
}
