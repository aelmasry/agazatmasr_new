<?php
/*
 * Shortcodes Class
 */
if (!class_exists('CTShortcodes')) :

    class CTShortcodes
    {


        public $shortcodes = array(
            "container",
            "row",
            "column",
            "one_half",
            "one_third",
            "one_fourth",
            "two_third",
            "three_fourth",
            "blockquote",
            "button",
            "banner",
            "checklist",
            "icon_box",
            "tabs",
            "tab",
            "tooltip",
            "toggles",
            "toggle",
            "review",
            "icon_list",
            "pricing_table",
            "parallax_block",
            "hotel_cart",
            "hotel_checkout",
            "hotel_booking_confirmation",
            "tour_cart",
            "tour_checkout",
            "tour_booking_confirmation",
            "hotels",
            "tours",
            "timeline_container",
            "timeline",
            "blog",
            "map",
            "faqs",
            "search_form",
        );


        function __construct()
        {
            add_action('init', array($this, 'add_shortcodes'));
            add_filter('the_content', array($this, 'filter_eliminate_autop'));
            add_filter('widget_text', array($this, 'filter_eliminate_autop'));
        }

        /*         * **************************************************************
         * **************** Remove AutoP tags *****************************
         * **************************************************************** */


        function filter_eliminate_autop($content)
        {
            $block = join("|", $this->shortcodes);

            // replace opening tag
            $content = preg_replace("/(<p>)?\[($block)(\s[^\]]+)?\](<\/p>|<br \/>)?/", "[$2$3]", $content);

            // replace closing tag
            $content = preg_replace("/(<p>)?\[\/($block)](<\/p>|<br \/>)/", "[/$2]", $content);
            return $content;
        }

        /*         * **************************************************************
         * **************** Add Shortcodes ********************************
         * **************************************************************** */


        function add_shortcodes()
        {
            foreach ($this->shortcodes as $shortcode) {
                $function_name = 'shortcode_' . $shortcode;
                add_shortcode($shortcode, array($this, $function_name));
            }
        }

        /*         * **************************************************************
         * *************** Grid System ************************************
         * **************************************************************** */


        //shortcode container
        function shortcode_container($atts, $content = null)
        {
            extract(shortcode_atts(array(
                'class' => ''
                            ), $atts));

            $class = empty($class) ? '' : ( ' ' . $class );
            $result = '<div class="container' . esc_attr($class) . '">';
            $result .= do_shortcode($content);
            $result .= '</div>';
            return $result;
        }


        //shortcode row
        function shortcode_row($atts, $content = null)
        {
            extract(shortcode_atts(array(
                'class' => ''
                            ), $atts));

            $class = empty($class) ? '' : ( ' ' . $class );
            $result = '<div class="row' . esc_attr($class) . '">';
            $result .= do_shortcode($content);
            $result .= '</div>';
            return $result;
        }


        //shortcode column
        function shortcode_column($atts, $content = null)
        {
            extract(shortcode_atts(array(
                'lg' => '',
                'md' => '',
                'sms' => '',
                'sm' => '',
                'xs' => '',
                'lgoff' => '',
                'mdoff' => '',
                'smsoff' => '',
                'smoff' => '',
                'xsoff' => '',
                'lghide' => '',
                'mdhide' => '',
                'smshide' => '',
                'smhide' => '',
                'xshide' => '',
                'lgclear' => '',
                'mdclear' => '',
                'smsclear' => '',
                'smclear' => '',
                'xsclear' => '',
                'class' => ''
                            ), $atts));

            $devices = array('lg', 'md', 'sm', 'sms', 'xs');
            $classes = array();
            foreach ($devices as $device) {

                //grid column class
                if (${$device} != '')
                    $classes[] = 'col-' . $device . '-' . ${$device};

                //grid offset class
                $device_off = $device . 'off';
                if (${$device_off} != '')
                    $classes[] = 'col-' . $device . '-offset-' . ${$device_off};

                //grid hide class
                $device_hide = $device . 'hide';
                if (${$device_hide} == 'yes')
                    $classes[] = 'hidden-' . $device;

                //grid clear class
                $device_clear = $device . 'clear';
                if (${$device_clear} == 'yes')
                    $classes[] = 'clear-' . $device;
            }
            if (!empty($class))
                $classes[] = $class;

            $result = '<div class="' . esc_attr(implode(' ', $classes)) . '">';
            $result .= do_shortcode($content);
            $result .= '</div>';

            return $result;
        }


        //shortcode one_half
        function shortcode_one_half($atts, $content = null)
        {
            extract(shortcode_atts(array(
                'class' => '',
                'offset' => 0,
                            ), $atts));

            $class = empty($class) ? '' : ( ' ' . $class );
            if ($offset != 0)
                $class .= ' col-sm-offset-' . $offset;

            $result = '<div class="col-sm-6' . esc_attr($class) . ' one-half">';
            $result .= do_shortcode($content);
            $result .= '</div>';

            return $result;
        }


        //shortcode one_third
        function shortcode_one_third($atts, $content = null)
        {
            extract(shortcode_atts(array(
                'class' => '',
                'offset' => 0,
                            ), $atts));

            $class = empty($class) ? '' : ( ' ' . $class );
            if ($offset != 0)
                $class .= ' col-sm-offset-' . $offset;

            $result = '<div class="col-sm-4' . esc_attr($class) . ' one-third">';
            $result .= do_shortcode($content);
            $result .= '</div>';

            return $result;
        }


        //shortcode two_third
        function shortcode_two_third($atts, $content = null)
        {
            extract(shortcode_atts(array(
                'class' => '',
                'offset' => 0,
                            ), $atts));

            $class = empty($class) ? '' : ( ' ' . $class );
            if ($offset != 0)
                $class .= ' col-sm-offset-' . $offset;

            $result = '<div class="col-sm-8' . esc_attr($class) . ' two-third">';
            $result .= do_shortcode($content);
            $result .= '</div>';

            return $result;
        }


        //shortcode one_fourth
        function shortcode_one_fourth($atts, $content = null)
        {
            extract(shortcode_atts(array(
                'class' => '',
                'offset' => 0,
                            ), $atts));

            $class = empty($class) ? '' : ( ' ' . $class );
            if ($offset != 0)
                $class .= ' col-sm-offset-' . $offset;

            $result = '<div class="col-sm-3 ' . esc_attr($class) . ' one-fourth">';
            $result .= do_shortcode($content);
            $result .= '</div>';

            return $result;
        }


        //shortcode three_fourth
        function shortcode_three_fourth($atts, $content = null)
        {
            extract(shortcode_atts(array(
                'class' => '',
                'offset' => 0,
                            ), $atts));

            $class = empty($class) ? '' : ( ' ' . $class );
            if ($offset != 0)
                $class .= ' col-sm-offset-' . $offset;

            $result = '<div class="col-sm-9 ' . esc_attr($class) . ' three-fourth">';
            $result .= do_shortcode($content);
            $result .= '</div>';

            return $result;
        }

        /*         * **************************************************************
         * **************** Blockquote Shortcode **************************
         * **************************************************************** */


        function shortcode_blockquote($atts, $content = null)
        {
            extract(shortcode_atts(array(
                'class' => ''
                            ), $atts));

            $class = empty($class) ? '' : ( ' ' . $class );
            $result = '';
            $result .= '<blockquote class="' . esc_attr('styled' . $class) . '">';
            $result .= do_shortcode($content);
            $result .= '</blockquote>';

            return $result;
        }

        /*         * **************************************************************
         * **************** Button Shortcode **************************
         * **************************************************************** */


        function shortcode_button($atts, $content = null)
        {
            extract(shortcode_atts(array(
                'class' => '',
                'style' => '',
                'size' => '',
                'target' => '_self', //available values 5 ( _blank|_self|_parent|_top|framename )
                'link' => '#',
                            ), $atts));

            $class = empty($class) ? '' : ( ' ' . $class );
            $styles = array('outline', 'white', 'green');
            $sizes = array('medium', 'full');
            if (!in_array($style, $styles))
                $style = '';
            if (!in_array($size, $sizes))
                $size = '';
            if ($size == 'full')
                $size = 'btn-full';
            $classes = array('btn_1');
            if (!empty($style))
                $classes[] = $style;
            if (!empty($size))
                $classes[] = $size;
            if (!empty($class))
                $classes[] = $class;

            $result = '';
            $result .= '<a href="' . esc_url($link) . '" class="' . esc_attr(implode(' ', $classes)) . '" target="' . esc_attr($target) . '">';
            $result .= do_shortcode($content);
            $result .= '</a>';

            return $result;
        }

        /*         * **************************************************************
         * **************** Banner Shortcode **************************
         * **************************************************************** */


        function shortcode_banner($atts, $content = null)
        {
            extract(shortcode_atts(array(
                'class' => '',
                'type' => '',
                'style' => '',
                'bg_image' => '',
                'bg_color' => '',
                            ), $atts));

            $class = empty($class) ? '' : ( ' ' . $class );
            $styles = array('colored');
            if (!in_array($style, $styles))
                $style = '';
            $types = array('default', 'custom');
            if (!in_array($type, $types))
                $type = 'default';

            $result = '';
            if ('default' == $type) {
                $result .= '<div class="banner ' . esc_attr($style . $class) . ' ">';
            } else if ('custom' == $type) {
                $result .= '<div class="banner custom_banner">';

                $custom_style = '<style type="text/css">.banner.custom_banner{';
                if (!empty($bg_image)) {
                    $bg_image_url = wp_get_attachment_url($bg_image);
                    $custom_style .= 'background: #fff url(' . $bg_image_url . ') no-repeat center bottom;';
                }
                if (!empty($bg_color)) {
                    $custom_style .= 'background-color:' . $bg_color;
                }
                $custom_style .= '}</style>';

                $result .= $custom_style;
            }
            $result .= do_shortcode($content);
            $result .= '</div>';

            return $result;
        }

        /*         * **************************************************************
         * **************** Check List Shortcode *****************************
         * **************************************************************** */


        function shortcode_checklist($atts, $content = null)
        {

            extract(shortcode_atts(array(
                'class' => '',
                            ), $atts));

            $class = empty($class) ? '' : ( ' ' . $class );
            $class = 'list_ok' . $class;
            $result = str_replace('<ul>', '<ul class="' . esc_attr($class) . '">', $content);
            $result = str_replace('<li>', '<li>', $result);
            $result = do_shortcode($result);

            return $result;
        }

        /*         * **************************************************************
         * **************** Icon Box Shortcode *****************************
         * **************************************************************** */


        function shortcode_icon_box($atts, $content = null)
        {

            extract(shortcode_atts(array(
                'class' => '',
                'icon_class' => '',
                'style' => '',
                            ), $atts));

            $styles = array('style2', 'style3');
            if (!in_array($style, $styles))
                $style = '';
            $class = empty($class) ? '' : ( ' ' . $class );
            $class = 'ct-icon-box ' . $style . $class;
            $result = '';
            $result .= '<div class="' . esc_attr($class) . '">';
            if (!empty($icon_class)) :
                $result .= '<i class="' . esc_attr($icon_class) . '"></i>';
            endif;
            $result .= do_shortcode($content);
            $result .= '</div>';

            return $result;
        }

        /*         * **************************************************************
         * **************** Tabs Shortcode ********************************
         * **************************************************************** */


        function shortcode_tabs($atts, $content = null)
        {
            $variables = array('active_tab_index' => '1', 'class' => '');
            extract(shortcode_atts($variables, $atts));

            $result = '';

            preg_match_all('/\[tab(.*?)]/i', $content, $matches, PREG_OFFSET_CAPTURE);
            $tab_titles = array();

            if (isset($matches[0])) {
                $tab_titles = $matches[0];
            }
            if (count($tab_titles)) {

                $result .= sprintf('<div class="%s"><ul class="nav nav-tabs">', esc_attr($class));
                $uid = uniqid();
                foreach ($tab_titles as $i => $tab) {
                    preg_match('/title="([^\"]+)"/i', $tab[0], $tab_matches, PREG_OFFSET_CAPTURE);
                    if (isset($tab_matches[1][0])) {
                        $active_class = '';
                        $active_attr = '';
                        if ($active_tab_index - 1 == $i) {
                            $active_class = ' class="active"';
                            $active_attr = ' active="true"';
                        }

                        $result .= '<li ' . $active_class . '><a href="' . esc_url('#' . $uid . $i) . '" data-toggle="tab">' . esc_html($tab_matches[1][0]) . '</a></li>';

                        $before_content = substr($content, 0, $tab[1]);
                        $current_content = substr($content, $tab[1]);
                        $current_content = preg_replace('/\[tab/', '[tab id="' . $uid . $i . '"' . $active_attr, $current_content, 1);
                        $content = $before_content . $current_content;
                    }
                }
                $result .= '</ul>';
                $result .= '<div class="tab-content">';
                $result .= do_shortcode($content);
                $result .= '</div>';
                $result .= '</div>';
            } else {
                $result .= do_shortcode($content);
            }

            return $result;
        }

        /*         * **************************************************************
         * **************** Tab Shortcode ********************************
         * **************************************************************** */


        function shortcode_tab($atts, $content = null)
        {
            extract(shortcode_atts(array(
                'title' => '',
                'id' => '',
                'active' => '',
                'class' => ''
                            ), $atts));

            $classes = array('tab-pane');
            if ($active == 'true' || $active == 'yes') {
                $classes[] = 'active';
            }
            if ($class != '') {
                $classes[] = $class;
            }
            return sprintf('<div id="%s" class="%s">%s</div>', esc_attr($id), esc_attr(implode(' ', $classes)), do_shortcode($content)
            );
        }

        /*         * **************************************************************
         * **************** ToolTip Shortcode *****************************
         * **************************************************************** */


        function shortcode_tooltip($atts, $content = null)
        {
            extract(shortcode_atts(array(
                'title' => '',
                'style' => '',
                'effect' => 1,
                'position' => 'top',
                'class' => ''
                            ), $atts));

            if ($style == 'advanced') {
                $effects = array(1, 2, 3, 4);
                if (!in_array($effect, $effects))
                    $effect = 1;
                $classes = array('tooltip_styled', 'tooltip-effect-' . esc_attr($effect));
                if ($class != '') {
                    $classes[] = $class;
                }
                $result = sprintf('<div class="%s"><span class="tooltip-item">%s</span><div class="tooltip-content">', esc_attr(implode(' ', $classes)), do_shortcode($content));
                $result .= esc_html($title);
                $result .= '</div></div>';
            } else {
                $classes = array('tooltip-1');
                if ($class != '') {
                    $classes[] = $class;
                }
                $positions = array('top', 'bottom', 'left', 'right');
                if (!in_array($position, $positions))
                    $position = 'top';
                $result = '';
                $result .= sprintf('<a href="#" class="%s" data-placement="%s" title="%s">', esc_attr(implode(' ', $classes)), esc_attr($position), esc_attr($title));
                $result .= do_shortcode($content);
                $result .= '</a>';
            }

            return $result;
        }


        // toggles
        public $toggles_index = 1; //to generate unique accordion id
        public $toggles_type = 'toggle'; //toggle type ( accordion|toggle )


        /*         * **************************************************************
         * **************** toggles Shortcode *****************************
         * **************************************************************** */


        function shortcode_toggles($atts, $content = null)
        {

            extract(shortcode_atts(array(
                'toggle_type' => 'accordion',
                'class' => ''
                            ), $atts));

            $this->toggles_type = $toggle_type;
            $classes = array('panel-group');
            if ($class != '') {
                $classes[] = $class;
            }
            $result = '<div class="' . esc_attr(implode(' ', $classes)) . '" id="toggles-' . $this->toggles_index . '">';
            $result .= do_shortcode($content);
            $result .= "</div>";
            $this->toggles_index++;
            return $result;
        }

        /*         * **************************************************************
         * **************** toggle Shortcode ******************************
         * **************************************************************** */


        function shortcode_toggle($atts, $content = null)
        {
            extract(shortcode_atts(array(
                'title' => '',
                'active' => 'no',
                'class' => ''
                            ), $atts));

            static $toggle_id = 1;

            $data_parent = '';
            if ($this->toggles_type == "accordion") {
                $data_parent = ' data-parent="#toggles-' . $this->toggles_index . '"';
            }

            $result = '';
            $class = 'panel panel-default' . (empty($class) ? '' : ( ' ' . $class ));
            $class_in = ( $active === 'yes') ? ' in' : '';
            $class_collapsed = ( $active === 'yes') ? '' : ' collapsed';
            $class_icon = ( $active === 'yes') ? 'icon-minus' : 'icon-plus';

            $result .= '<div class="' . esc_attr($class) . '"><div class="panel-heading">';
            $result .= '<h4 class="panel-title"><a class="accordion-toggle' . $class_collapsed . '" href="#toggle-' . $toggle_id . '" data-toggle="collapse"' . $data_parent . '>';
            $result .= esc_html($title) . '<i class="indicator pull-right ' . $class_icon . '"></i></a></h4></div>';
            $result .= '<div class="panel-collapse collapse' . $class_in . '" id="toggle-' . $toggle_id . '"><div class="panel-body"><p>';
            $result .= do_shortcode($content);
            $result .= '</p></div></div></div>';

            $toggle_id++;

            return $result;
        }

        /*         * **************************************************************
         * **************** Review Shortcode *****************************
         * **************************************************************** */


        function shortcode_review($atts, $content = null)
        {
            extract(shortcode_atts(array(
                'name' => '',
                'rating' => 5,
                'img_url' => '',
                'class' => ''
                            ), $atts));

            $classes = array('review_strip');
            if ($class != '') {
                $classes[] = $class;
            }

            $result = '';
            $result .= '<div class="' . esc_attr(implode(' ', $classes)) . '">';
            if (!empty($img_url))
                $result .= '<img src="' . esc_url($img_url) . '" alt="" class="img-circle">';
            $result .= '<h4>' . esc_html($name) . '</h4>';
            $result .= do_shortcode($content);
            if (!empty($rating) && is_numeric($rating)) {
                $result .= '<div class="rating">';
                for ($i = 1; $i <= 5; $i++) {
                    if ($rating >= $i) {
                        $icon_class = 'icon-star voted';
                    } else {
                        $icon_class = 'icon-star-empty';
                    }
                    $result .= '<i class="' . $icon_class . '"></i>';
                }
                $result .= '</div>';
            }
            $result .= '</div><!-- End review strip -->';
            return $result;
        }

        /*         * **************************************************************
         * **************** Icon List Shortcode *****************************
         * **************************************************************** */


        function shortcode_icon_list($atts, $content = null)
        {
            extract(shortcode_atts(array(
                'class' => ''
                            ), $atts));

            $classes = array('general_icons');
            if ($class != '') {
                $classes[] = $class;
            }
            $result = '';
            $result .= '<div class="' . esc_attr(implode(' ', $classes)) . '">';
            $result .= do_shortcode($content);
            $result .= '</div>';
            return $result;
        }

        /*         * **************************************************************
         * ******************** Pricing Table Shortcode *******************
         * **************************************************************** */


        function shortcode_pricing_table($atts, $content = null)
        {
            $variables = array('class' => '',
                'style' => '',
                'price' => '',
                'title' => '',
                'btn_title' => 'Buy Now!',
                'btn_url' => '',
                'btn_target' => '_blank',
                'btn_color' => '',
                'btn_class' => '',
                'ribbon_img_url' => '',
                'is_featured' => '',
            );
            extract(shortcode_atts($variables, $atts));
            $result = '';

            if (empty($style)) {

                $classes = array('plan');
                if (!empty($is_featured)) {
                    $classes[] = 'plan-tall';
                }
                if ($class != '') {
                    $classes[] = $class;
                }
                $result .= '<div class="' . esc_attr(implode(' ', $classes)) . '">';
                if (!empty($ribbon_img_url))
                    $result .= '<span class="ribbon_table" style="background:url(' . esc_url($ribbon_img_url) . ') no-repeat 0 0"></span>';
                $result .= '<h2 class="plan-title">' . esc_html($title) . '</h2>';
                $result .= '<p class="plan-price">' . balancetags($price) . '</p>';
                $content = preg_replace('/<ul>/', '<ul class="plan-features">', $content, 1);
                $result .= do_shortcode($content);
                $result .= '<p><a href="' . esc_url($btn_url) . '" class="btn_1 ' . esc_attr($btn_color . ' ' . $btn_class) . '" target="' . esc_html($btn_target) . '">' . esc_html($btn_title) . '</a></p>';
                $result .= '</div>';
            } else {
                $classes = array('pricing-table');
                $classes[] = (!empty($is_featured) && ( $is_featured != 'no' ) ) ? 'green' : 'black';
                if ($class != '') {
                    $classes[] = $class;
                }
                $result .= '<div class="' . esc_attr(implode(' ', $classes)) . '">';
                $result .= '<span class="ribbon_2" style="background:url(' . esc_url($ribbon_img_url) . ') no-repeat 0 0"></span>';
                $result .= '<div class="pricing-table-header">
                            <span class="heading">' . esc_html($title) . '</span>
                            <div class="price-value">' . balancetags($price) . '</div>
                        </div>';
                $result .= '<div class="pricing-table-features">' . do_shortcode($content) . '</div>';
                $result .= '<div class="pricing-table-sign-up">';
                $result .= '<a href="' . esc_url($btn_url) . '" class="btn_1 ' . esc_attr($btn_color . ' ' . $btn_class) . '" target="' . esc_html($btn_target) . '">' . esc_html($btn_title) . '</a>';
                $result .= '</div></div><!-- End pricing-table-->';
            }
            return $result;
        }

        /*         * **************************************************************
         * **************** Parallax Section Shortcode ********************
         * **************************************************************** */


        function shortcode_parallax_block($atts, $content = null)
        {
            extract(shortcode_atts(array(
                'bg_image' => '',
                'width' => '1400',
                'height' => '470',
                'class' => '',
                            ), $atts));

            $result = '';
            $result .= '<section class="parallax-window" data-parallax="scroll" data-image-src="' . esc_url($bg_image) . '" data-natural-width="' . esc_attr($width) . '" data-natural-height="' . esc_attr($height) . '">';
            $result .= '<div class="parallax-content-1 magnific">';
            $result .= do_shortcode($content);
            $result .= '</div>';
            $result .= '</section>';
            return $result;
        }

        /*         * **************************************************************
         * **************** Hotel Booking Page Shortcode **********
         * **************************************************************** */


        function shortcode_hotel_cart($atts, $content = null)
        {
            ob_start();
            ct_get_template('cart.php', '/templates/hotel');
            $output = ob_get_contents();
            ob_end_clean();
            return $output;
        }

        /*         * **************************************************************
         * **************** Hotel CheckOut Page Shortcode **********
         * **************************************************************** */


        function shortcode_hotel_checkout($atts, $content = null)
        {
            ob_start();
            ct_get_template('checkout.php', '/templates/hotel');
            $output = ob_get_contents();
            ob_end_clean();
            return $output;
        }

        /*         * **************************************************************
         * **************** Hotel Booking Confirm Page Shortcode **********
         * **************************************************************** */


        function shortcode_hotel_booking_confirmation($atts, $content = null)
        {
            ob_start();
            ct_get_template('thankyou.php', '/templates/hotel');
            $output = ob_get_contents();
            ob_end_clean();
            return $output;
        }

        /*         * **************************************************************
         * **************** Tour Booking Page Shortcode **********
         * **************************************************************** */


        function shortcode_tour_cart($atts, $content = null)
        {
            ob_start();
            ct_get_template('cart.php', '/templates/tour');
            $output = ob_get_contents();
            ob_end_clean();
            return $output;
        }

        /*         * **************************************************************
         * **************** Tour CheckOut Page Shortcode **********
         * **************************************************************** */


        function shortcode_tour_checkout($atts, $content = null)
        {
            ob_start();
            ct_get_template('checkout.php', '/templates/tour');
            $output = ob_get_contents();
            ob_end_clean();
            return $output;
        }

        /*         * **************************************************************
         * **************** Tour Booking Confirm Page Shortcode **********
         * **************************************************************** */


        function shortcode_tour_booking_confirmation($atts, $content = null)
        {
            ob_start();
            ct_get_template('thankyou.php', '/templates/tour');
            $output = ob_get_contents();
            ob_end_clean();
            return $output;
        }

        /*         * **************************************************************
         * ****************** Hotels List Shortcode ****************
         * **************************************************************** */


        function shortcode_hotels($atts)
        {
            extract(shortcode_atts(array(
                'title' => '',
                'type' => 'latest',
                'style' => 'advanced',
                'map' => '',
                'count' => 6,
                'count_per_row' => 3,
                'district' => '',
                'post_ids' => '',
                'class' => '',
                            ), $atts));
            if (!ct_is_hotel_enabled())
                return '';
            $styles = array('advanced', 'simple');
            $types = array('latest', 'featured', 'popular', 'hot', 'selected');
            if (!in_array($style, $styles))
                $style = 'advanced';
            if (!in_array($type, $types))
                $type = 'latest';
            $map = (!empty($map) && $style == 'advanced' ) ? true : false;
            $post_ids = explode(',', $post_ids);
            $district = (!empty($district) ) ? explode(',', $district) : array();
            $count = is_numeric($count) ? $count : 6;
            $count_per_row = is_numeric($count_per_row) ? $count_per_row : 3;
            $output = '';

            // Generate a unique cache key based on the parameters
            $cache_key = 'ct_hotels_' . md5(serialize(array(
                'type' => $type,
                'count' => $count,
                'district' => $district,
                'post_ids' => $post_ids
            )));
            
            $cache_key .= '_' . date('Y-m-d');
            $cached_hotel_ids = get_transient($cache_key);
            $hotels = array();
            if (is_array($cached_hotel_ids)) {
                foreach ($cached_hotel_ids as $cached_hotel_id) {
                    $cached_hotel = get_post((int) $cached_hotel_id);
                    if ($cached_hotel) {
                        $hotels[] = $cached_hotel;
                    }
                }
            } else {
                if ($type == 'selected') {
                    $hotels = ct_hotel_get_hotels_from_id($post_ids);
                } elseif ($type == 'hot') {
                    $hotels = ct_hotel_get_hot_hotels($count, array(), $district);
                } else {
                    $hotels = ct_hotel_get_special_hotels($type, $count, array(), $district);
                }
                if (!is_array($hotels)) {
                    $hotels = array();
                }
                $hotel_ids = array();
                foreach ($hotels as $hotel_post) {
                    if (!empty($hotel_post->ID)) {
                        $hotel_ids[] = (int) $hotel_post->ID;
                    }
                }
                set_transient($cache_key, $hotel_ids, HOUR_IN_SECONDS);
            }

            if ($style == 'simple') {

                $output = '<div class="other_tours"><ul>';

                foreach ($hotels as $post_obj) {
                    if ($post_obj) {
                        $post_id = $post_obj->ID;
                        $price = get_post_meta($post_id, '_hotel_price', true);
                        $output .= '<li><a href="' . esc_url(get_permalink($post_id)) . '">' . get_the_title($post_id) . '<span class="other_tours_price">' . ct_price($price) . '</span></a></li>';
                    }
                }

                $output .= '</ul></div>';
            } else {

                ob_start();

                global $before_list, $post_id, $show_map;
                $show_map = $map;
                $before_list = '';
                if (( 2 == $count_per_row)) {
                    $before_list = '<div class="col-md-6 col-sm-6 zoomIn" data-wow-delay="0.1s">';
                } elseif (4 == $count_per_row) {
                    $before_list = '<div class="col-md-3 col-sm-6 zoomIn" data-wow-delay="0.1s">';
                } else {
                    $before_list = '<div class="col-md-4 col-sm-6 zoomIn" data-wow-delay="0.1s">';
                }

                if ($map) {
                    echo '<div class="container-fluid">';
                    echo '<div class="row">';
                    echo '<div class="col-md-7 content-left">';

                    if (!empty($title)) {
                        echo '<h2>' . esc_html($title) . '</h2>';
                    }

                    echo '<div class="hotel-list row add-clearfix' . esc_attr($class) . '">';

                    foreach ($hotels as $post_obj) {
                        if ($post_obj) {
                            $post_id = $post_obj->ID;
                            ct_get_template('loop-grid.php', '/templates/hotel/');
                        }
                    }

                    echo '</div>';
                    echo '</div>';
                    echo '<div class="col-md-5 map-right hidden-sm hidden-xs"><div class="map" id="map"></div></div>';
                    $map_zoom = empty($ct_options['hotel_list_zoom']) ? 14 : $ct_options['hotel_list_zoom'];
                    ?>
                    <script type="text/javascript">
                        jQuery(document).ready(function(){
                        var lang = '<?php echo get_locale() ?>';
                        lang = lang.replace('_', '-');
                        var zoom = <?php echo $map_zoom ?>;
                        var markersData = {
                    <?php
                    foreach ($hotels as $hotel_obj) {
                        $hotel_pos = get_post_meta($hotel_obj->ID, '_hotel_loc', true);

                        if (!empty($hotel_pos)) {
                            $hotel_pos = explode(',', $hotel_pos);
                            $description = str_replace("'", "\'", wp_trim_words(strip_shortcodes(get_post_field("post_content", $hotel_obj->ID)), 20, '...'));
                            ?>
                                '<?php echo $hotel_obj->ID ?>' :  [{
                                name: '<?php echo get_the_title($hotel_obj->ID) ?>',
                                        type: 'Hotels',
                                        location_latitude: <?php echo $hotel_pos[0] ?>,
                                        location_longitude: <?php echo $hotel_pos[1] ?>,
                                        map_image_url: '<?php echo ct_get_header_image_src($hotel_obj->ID, "ct-map-thumb") ?>',
                                        name_point: '<?php echo get_the_title($hotel_obj->ID) ?>',
                                        description_point: '<?php echo $description ?>',
                                        url_point: '<?php echo get_permalink($hotel_obj->ID) ?>'
                                }],
                            <?php
                        }
                    }
                    ?>
                        };
                    <?php
                    $hotel_pos = array();
                    if (!empty($hotels)) {
                        foreach ($hotels as $hotel_obj) {
                            $hotel_pos = get_post_meta($hotel_obj->ID, '_hotel_loc', true);

                            if (!empty($hotel_pos)) {
                                $hotel_pos = explode(',', $hotel_pos);
                                break;
                            }
                        }
                    }

                    if (!empty($hotel_pos)) {
                        ?>
                            var lati = <?php echo $hotel_pos[0] ?>;
                            var long = <?php echo $hotel_pos[1] ?>;
                            // var _center = [48.865633, 2.321236];
                            var _center = [lati, long];
                            renderMap(_center, markersData, zoom, google.maps.MapTypeId.ROADMAP, false);
                    <?php } ?>

                        });
                    </script>
                    <?php
                    echo '</div>';
                    echo '</div>';
                } else {
                    if (!empty($title)) {
                        echo '<h2>' . esc_html($title) . '</h2>';
                    }

                    echo '<div class="hotel-list row add-clearfix' . esc_attr($class) . '">';

                    foreach ($hotels as $post_obj) {
                        if ($post_obj) {
                            $post_id = $post_obj->ID;
                            ct_get_template('loop-grid.php', '/templates/hotel/');
                        }
                    }

                    echo '</div>';
                }

                $output = ob_get_contents();

                ob_end_clean();
            }

            return $output;
        }

        /*         * **************************************************************
         * ****************** Tour List Shortcode ****************
         * **************************************************************** */


        function shortcode_tours($atts)
        {
            extract(shortcode_atts(array(
                'title' => '',
                'type' => 'latest',
                'style' => 'advanced',
                'map' => '',
                'count' => 6,
                'count_per_row' => 3,
                'tour_type' => '',
                'post_ids' => '',
                'class' => '',
                            ), $atts));
            if (!ct_is_tour_enabled())
                return '';
            $styles = array('advanced', 'simple', 'list', 'simple2');
            $types = array('latest', 'featured', 'popular', 'hot', 'selected');
            if (!in_array($style, $styles))
                $style = 'advanced';
            if (!in_array($type, $types))
                $type = 'latest';
            $map = (!empty($map) && $style == 'advanced' ) ? true : false;
            $post_ids = explode(',', $post_ids);
            $count = is_numeric($count) ? $count : 6;
            $count_per_row = is_numeric($count_per_row) ? $count_per_row : 3;
            $tour_type = (!empty($tour_type) ) ? explode(',', $tour_type) : array();

            global $before_list, $post_id, $show_map;
            $show_map = $map;

            $before_list = '';
            if (( 2 == $count_per_row)) {
                $before_list = '<div class="col-md-6 col-sm-6 zoomIn" data-wow-delay="0.1s">';
            } elseif (4 == $count_per_row) {
                $before_list = '<div class="col-md-3 col-sm-6 zoomIn" data-wow-delay="0.1s">';
            } else {
                $before_list = '<div class="col-md-4 col-sm-6 zoomIn" data-wow-delay="0.1s">';
            }

            $tours = array();
            
            // Generate a unique cache key based on the parameters
            $cache_key = 'ct_tours_' . md5(serialize(array(
                'type' => $type,
                'count' => $count,
                'tour_type' => $tour_type,
                'post_ids' => $post_ids
            )));
            
            $cache_key .= '_' . date('Y-m-d');
            $cached_tour_ids = get_transient($cache_key);
            $tours = array();
            if (is_array($cached_tour_ids)) {
                foreach ($cached_tour_ids as $cached_tour_id) {
                    $cached_tour = get_post((int) $cached_tour_id);
                    if ($cached_tour) {
                        $tours[] = $cached_tour;
                    }
                }
            } else {
                if ($type == 'selected') {
                    $tours = ct_tour_get_tours_from_id($post_ids);
                } elseif ($type == 'hot') {
                    $tours = ct_tour_get_hot_tours($count, array(), $tour_type);
                } else {
                    $tours = ct_tour_get_special_tours($type, $count, array(), $tour_type);
                }
                if (!is_array($tours)) {
                    $tours = array();
                }
                $tour_ids = array();
                foreach ($tours as $tour_post) {
                    if (!empty($tour_post->ID)) {
                        $tour_ids[] = (int) $tour_post->ID;
                    }
                }
                set_transient($cache_key, $tour_ids, MINUTE_IN_SECONDS);
            }

            if ($style == 'simple') {
                $output = '<div class="other_tours"><ul>';

                foreach ($tours as $post_obj) {
                    if ($post_obj) {
                        $post_id = $post_obj->ID;
                        $price = get_post_meta($post_id, '_tour_price', true);
                        $tour_type = wp_get_post_terms($post_id, 'tour_type');

                        $output .= '<li><a href="' . esc_url(get_permalink($post_id)) . '">';

                        if (!empty($tour_type)) {
                            $icon_class = get_tax_meta($tour_type[0]->term_id, 'ct_tax_icon_class', true);
                            $output .= '<i class="' . esc_attr($icon_class) . '"></i>';
                        }

                        $output .= get_the_title($post_id) . '<span class="other_tours_price">' . ct_price($price) . '</span></a></li>';
                    }
                }

                $output .= '</ul></div>';
            } else if ($style == 'simple2') {
                $output = '<div class="tour_list simple"><ul>';

                foreach ($tours as $post_obj) {
                    if ($post_obj) {
                        $post_id = $post_obj->ID;
                        $price = get_post_meta($post_id, '_tour_price', true);
                        $tour_type = wp_get_post_terms($post_id, 'tour_type');

                        $output .= '<li><div><a href="' . esc_url(get_permalink($post_id)) . '">';

                        if (has_post_thumbnail($post_id)) {
                            $output .= '<figure>' . get_the_post_thumbnail($post_id, array(45, 45), array('class' => 'img-rounded')) . '</figure>';
                        } else {
                            $placeholder = CT_TEMPLATE_DIRECTORY_URI . '/img/placeholder1.png';
                            $output .= '<figure>' . '<img src="' . $placeholder . '" alt="' . __("Placeholder", "citytours") . '" class="img-rounded"/>' . '</figure>';
                        }

                        $output .= '<h3><strong>' . get_the_title($post_id) . '</strong> ' . __('Tour', 'citytours') . '</h3>';

                        $output .= '<small>' . __('From ', 'citytours') . ct_price($price) . '</small></a></div></li>';
                    }
                }

                $output .= '</ul></div>';
            } else if ($style == 'list') {
                
                ob_start();

                if (!empty($title)) {
                    echo '<h2>' . esc_html($title) . '</h2>';
                }

                echo '<div class="tour-list row add-clearfix' . esc_attr($class) . '">';

                foreach ($tours as $post_obj) {
                    if ($post_obj) {
                        $post_id = $post_obj->ID;
                        ct_get_template('loop-list.php', '/templates/tour/');
                    }
                }

                echo '</div>';

                $output = ob_get_contents();
                ob_end_clean();
            } else {
                ob_start();

                if ($map) {
                    echo '<div class="container-fluid">';
                    echo '<div class="row">';
                    echo '<div class="col-md-7 content-left">';
                    if (!empty($title)) {
                        echo '<h2>' . esc_html($title) . '</h2>';
                    }

                    echo '<div class="tour-list row add-clearfix' . esc_attr($class) . '">';

                    foreach ($tours as $post_obj) {
                        if ($post_obj) {
                            $post_id = $post_obj->ID;
                            ct_get_template('loop-grid.php', '/templates/tour/');
                        }
                    }

                    echo '</div>';
                    echo '</div>';
                    echo '<div class="col-md-5 map-right hidden-sm hidden-xs"><div class="map" id="map"></div></div>';
                    $map_zoom = empty($ct_options['tour_list_zoom']) ? 14 : $ct_options['tour_list_zoom'];
                    ?>
                    <script type="text/javascript">
                        jQuery(document).ready(function(){
                        var lang = '<?php echo get_locale() ?>';
                        lang = lang.replace('_', '-');
                        var zoom = <?php echo $map_zoom ?>;
                        var markersData = {
                    <?php
                    foreach ($tours as $tour_obj) {
                        $tour_pos = get_post_meta($tour_obj->ID, '_tour_loc', true);

                        if (!empty($tour_pos)) {
                            $tour_pos = explode(',', $tour_pos);
                            $description = str_replace("'", "\'", wp_trim_words(strip_shortcodes(get_post_field("post_content", $tour_obj->ID)), 20, '...'));
                            ?>
                                '<?php echo $tour_obj->ID ?>' :  [{
                                name: '<?php echo get_the_title($tour_obj->ID) ?>',
                                        type: 'Tours',
                                        location_latitude: <?php echo $tour_pos[0] ?>,
                                        location_longitude: <?php echo $tour_pos[1] ?>,
                                        map_image_url: '<?php echo ct_get_header_image_src($tour_obj->ID, "ct-map-thumb") ?>',
                                        name_point: '<?php echo get_the_title($tour_obj->ID) ?>',
                                        description_point: '<?php echo $description ?>',
                                        url_point: '<?php echo get_permalink($tour_obj->ID) ?>'
                                }],
                            <?php
                        }
                    }
                    ?>
                        };
                    <?php
                    $tour_pos = array();
                    if (!empty($tours)) {
                        foreach ($tours as $tour_obj) {
                            $tour_pos = get_post_meta($tour_obj->ID, '_tour_loc', true);

                            if (!empty($tour_pos)) {
                                $tour_pos = explode(',', $tour_pos);
                                break;
                            }
                        }
                    }

                    if (!empty($tour_pos)) {
                        ?>
                            var lati = <?php echo $tour_pos[0] ?>;
                            var long = <?php echo $tour_pos[1] ?>;
                            // var _center = [48.865633, 2.321236];
                            var _center = [lati, long];
                            renderMap(_center, markersData, zoom, google.maps.MapTypeId.ROADMAP, false);
                    <?php } ?>

                        });
                    </script>
                    <?php
                    echo '</div>';
                    echo '</div>';
                } else {
                    if (!empty($title)) {
                        echo '<h2>' . esc_html($title) . '</h2>';
                    }

                    echo '<div class="tour-list row add-clearfix' . esc_attr($class) . '">';

                    foreach ($tours as $post_obj) {
                        if ($post_obj) {
                            $post_id = $post_obj->ID;
                            ct_get_template('loop-grid.php', '/templates/tour/');
                        }
                    }

                    echo '</div>';
                }
                $output = ob_get_contents();
                ob_end_clean();
            }

            return $output;
        }

        /*         * **************************************************************
         * ****************** shortcode_search_form Shortcode ****************
         * **************************************************************** */
        function shortcode_search_form($atts, $content = null)
        {
            extract(shortcode_atts(array(
                'class' => '',
                            ), $atts));

                ob_start(); // Start output buffering

                $style = esc_attr($atts['style']);
                $active_class = 'active'; // Default active class

                ?>
                <section id="search_container" style="<?php echo $style; ?>">
                    <div id="search">
                        <div class="search_con">
                            <ul class="nav nav-tabs">
                                <?php if (ct_is_hotel_enabled()) : ?>
                                    <li class="<?php echo esc_attr($active_class) ?>">
                                        <a href="#hotels" data-toggle="tab">
                                            <span class="icon-hospital"></span>
                                            <?php esc_html_e('Hotels', 'citytours') ?>
                                        </a>
                                    </li>
                                <?php
                                    $active_class = '';
                                endif;
                                ?>
                                <?php if (ct_is_tour_enabled()) : ?>
                                    <li>
                                        <a href="#tours" data-toggle="tab">
                                            <img src="<?php echo esc_url(get_template_directory_uri() . '/imgs/eiffel-icon-white.png'); ?>" class="wh-effile" alt="image">
                                            <img src="<?php echo esc_url(get_template_directory_uri() . '/imgs/eiffel-icon-black.png'); ?>" class="bl-effile" alt="image" style="display:none;">
                                            <?php esc_html_e('Tours', 'citytours') ?>
                                        </a>
                                    </li>
                                <?php
                                    $active_class = '';
                                endif;
                                ?>
                                <li>
                                    <a href="https://tourico.amadeusonlinesuite.com/flights?lc=AR" target="_blank">
                                        <span class="fa icon-flight"></span>
                                        <?php esc_html_e('flights', 'citytours') ?>
                                    </a>
                                </li>
                                <?php $active_class = ''; ?>
                            </ul>

                            <?php $active_class = 'active'; ?>
                            <div class="tab-content">
                                <?php
                                if (ct_is_hotel_enabled()) :
                                    include_once 'home_hotels_search_form.php';
                                    $active_class = '';
                                endif;
                                if (ct_is_tour_enabled()) :
                                    include_once 'home_tours_search_form.php';
                                    $active_class = '';
                                endif;
                                    include_once 'home_flights_search_form.php';
                                ?>
                            </div>
                        </div>
                    </div>
                </section>
                <?php

                return ob_get_clean(); // Return the buffered content
        }
        
        /*         * **************************************************************
         * **************** Timeline container Shortcode ******************
         * **************************************************************** */
        function shortcode_timeline_container($atts, $content = null)
        {
            extract(shortcode_atts(array(
                'class' => '',
                            ), $atts));

            $result = '';
            $result .= '<ul class="cbp_tmtimeline ' . esc_attr($class) . '">';
            $result .= do_shortcode($content);
            $result .= '</ul>';
            return $result;
        }

        /*         * **************************************************************
         * ******************** Tiemline Shortcode ************************
         * **************************************************************** */


        function shortcode_timeline($atts, $content = null)
        {
            extract(shortcode_atts(array(
                'class' => '',
                'time' => '',
                'duration' => '',
                'icon_class' => '',
                            ), $atts));

            $result = '';
            $result .= '<li class="' . esc_attr($class) . '">';
            $result .= '<time class="cbp_tmtime" datetime="' . esc_attr($time) . '"><span>' . esc_html($duration) . '</span> <span>' . esc_attr($time) . '</span></time>';
            $result .= '<div class="cbp_tmicon">' . '<i class="' . esc_attr($icon_class) . '"></i>' . '</div>';
            $result .= '<div class="cbp_tmlabel">';
            $result .= do_shortcode($content);
            $result .= '</div></li>';

            return $result;
        }

        /*         * **************************************************************
         * ************************* Blog Shortcode ***********************
         * **************************************************************** */


        function shortcode_blog($atts, $content = null)
        {
            global $title, $post_count, $order, $paged;
            
            $variables = array('style' => 'simple', 'title' => esc_html__( 'our blog', 'citytours' ), 'post_count' => 5, 'order' => 'ASC', 'paged' => false);
            extract(shortcode_atts($variables, $atts));
            
            $styles = array( 'home', 'simple' );
            if ( ! in_array( $style, $styles ) ) $style = 'simple';
                        
            ob_start();
            if($style === 'home')
                ct_get_template('home-blog.php', '/templates');
            else
                ct_get_template('content-blog.php', '/templates');
            
            $output = ob_get_contents();
            ob_end_clean();

            return $output;
        }

        /*         * **************************************************************
         * ************************* Map Shortcode ************************
         * **************************************************************** */


        function shortcode_map($atts, $content = null)
        {
            global $container_id, $center, $related, $zoom, $maptypecontrol, $maptype;

            $variables = array(
                'class' => '',
                'center' => '',
                'related' => '',
                'zoom' => '14',
                'maptype' => 'RoadMap',
                'maptypecontrol' => '',
                'streetviewcontrol' => 'true',
                'scrollwheel' => 'true',
                'draggable' => 'true',
                'width' => '100%',
                'height' => '300px',
                'container_id' => ''
            );
            extract(shortcode_atts($variables, $atts));

            if (!empty($related)) {
                $related = explode(',', $related);
            } else {
                $related = array();
            }

            if (( $maptypecontrol == 'yes' ) || ( $maptypecontrol == 'true' )) {
                $maptypecontrol = 'true';
            } else {
                $maptypecontrol = 'false';
            }
            // if ( ( $nav_control == 'yes' ) || ( $nav_control == 'true' ) ) { $nav_control = 'navigationControl: true,'; } else { $nav_control = 'navigationControl: false,'; }

            if (( $scrollwheel == 'yes' ) || ( $scrollwheel == 'true' )) {
                $scrollwheel = 'true';
            } else {
                $scrollwheel = 'false';
            }

            if (( $streetviewcontrol == 'yes' ) || ( $streetviewcontrol == 'true' )) {
                $streetviewcontrol = 'true';
            } else {
                $streetviewcontrol = 'false';
            }
            // if ( ( $draggable == 'yes' ) || ( $draggable == 'true' ) ) { $draggable = 'draggable: true,'; } else { $draggable = 'draggable: false,'; }

            $map_types = array('ROADMAP', 'SATELLITE', 'HYBRID', 'TERRAIN');
            $maptype = strtoupper($maptype);
            if (empty($maptype) || !in_array($maptype, $map_types))
                $maptype = 'ROADMAP';
            $maptype = 'google.maps.MapTypeId.' . $maptype;

            static $map_id = 1;
            $class = empty($class) ? '' : ' ' . esc_attr($class);

            $result = '';

            ob_start();
            ct_get_template('map.php', '/templates');
            $result = ob_get_contents();
            ob_end_clean();

            $map_id++;

            return $result;
        }

        /*         * **************************************************************
         * ************************* FAQs Shortcode ***********************
         * **************************************************************** */


        function shortcode_faqs($atts, $content = null)
        {
            extract(shortcode_atts(array(
                'show_cat_title' => 'true',
                'show_filter' => false,
                'post_count' => 12,
                'category' => '',
                'orderby' => 'title',
                'order' => 'ASC',
                'class' => '',
                            ), $atts));

            if ($show_cat_title == 'yes' || $show_cat_title == 'true') {
                $show_cat_title = 'true';
            }

            $args = array(
                'post_type' => 'faq',
                'posts_per_page' => $post_count,
                'orderby' => $orderby,
                'order' => $order,
            );

            if (isset($category) && !empty($category)) {
                $args['tax_query'] = array(
                    array(
                        'taxonomy' => 'faq_cat',
                        'field' => 'term_id',
                        'terms' => $category,
                    ),
                );
            }

            $query = new WP_Query($args);

            $result = '';
            $used_cats = array();
            $available_cats = get_terms(array('taxonomy' => 'faq_cat', 'hide_empty' => true, 'parent' => 0));

            if ($query->have_posts()) {

                $cat_html = array();
                foreach ($available_cats as $each_cat) {
                    $cat_html[$each_cat->term_id] = '';

                    if ($show_cat_title == 'true') {
                        $term_info = get_term_by('id', $each_cat->term_id, 'faq_cat', ARRAY_A);

                        $cat_html[$each_cat->term_id] .= '<h3 class="nomargin_top" id="faq-' . $term_info['slug'] . '">' . $term_info['name'] . '</h3>';
                    }

                    $cat_html[$each_cat->term_id] .= '<div class="panel-group">';
                }

                while ($query->have_posts()) {
                    $query->the_post();
                    $toggle_index = 'toggle-' . get_the_ID();
                    $post_cats = wp_get_post_terms(get_the_ID(), 'faq_cat');

                    if (!in_array($post_cats[0]->term_id, $used_cats)) {
                        $used_cats[] = $post_cats[0]->term_id;
                    }

                    $cat_html[$post_cats[0]->term_id] .= '<div class="panel panel-default ' . $class . '">';

                    // title
                    $cat_html[$post_cats[0]->term_id] .= '<div class="panel-heading"><h4 class="panel-title">';
                    $cat_html[$post_cats[0]->term_id] .= '<a class="accordion-toggle collapsed" href="#' . $toggle_index . '" data-toggle="collapse">' . esc_html(get_the_title()) . '<i class="indicator pull-right icon-plus"></i></a>';
                    $cat_html[$post_cats[0]->term_id] .= '</h4></div>';
                    // content
                    $cat_html[$post_cats[0]->term_id] .= '<div class="panel-collapse collapse" id="' . $toggle_index . '"><div class="panel-body">';
                    $cat_html[$post_cats[0]->term_id] .= get_the_content();
                    $cat_html[$post_cats[0]->term_id] .= "</div></div>";

                    $cat_html[$post_cats[0]->term_id] .= '</div>';
                }

                foreach ($available_cats as $each_cat) {
                    $cat_html[$each_cat->term_id] .= '</div>';
                }

                if ($show_filter) {
                    $result .= '<div class="row">';
                    $result .= '<div class="col-md-3 hidden-xs hidden-sm" id="sidebar">';

                    $result .= '<div class="theiaStickySidebar">';
                    $result .= '<div class="box_style_cat" id="faq_box">';
                    $result .= '<ul id="cat_nav">';
                    foreach ($used_cats as $cat_id) {
                        $term_info = get_term_by('id', $cat_id, 'faq_cat', ARRAY_A);
                        $result .= '<li><a href="#faq-' . $term_info['slug'] . '"><i class="icon_set_1_icon-95"></i>' . $term_info['name'] . '</a></li>';
                    }
                    $result .= '</ul>';
                    $result .= '</div>';
                    $result .= '</div>';

                    $result .= '</div>';
                    $result .= '<div class="col-md-9">';
                }

                foreach ($used_cats as $cat_id) {
                    $result .= $cat_html[$cat_id];
                }

                if ($show_filter) {
                    $result .= '</div></div>';
                }

                /* Restore original Post Data */
                wp_reset_postdata();
            } else {
                // no posts found
            }

            return $result;
        }

        

    }

    
endif;