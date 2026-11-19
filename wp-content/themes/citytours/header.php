<?php
/* Main Header Template */
if (!defined('ABSPATH')) {
    exit;
}
global $ct_options;
?>
<!DOCTYPE html>
<!--[if IE 7 ]>    <html class="ie7 oldie" <?php language_attributes(); ?>> <![endif]-->
<!--[if IE 8 ]>    <html class="ie8 oldie" <?php language_attributes(); ?>> <![endif]-->
<!--[if IE   ]>    <html class="ie" <?php language_attributes(); ?>> <![endif]-->
<!--[if lt IE 9]><script src="//html5shim.googlecode.com/svn/trunk/html5.js"></script><![endif]-->
<html <?php language_attributes(); ?>>

<head>
    <!-- Meta Tags -->
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta http-equiv="X-UA-Compatible" content="IE=Edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="<?php echo esc_url(CT_IMAGE_URL . '/favicon.ico'); ?>">
    <link rel="shortcut icon" href="<?php echo esc_url(CT_IMAGE_URL . '/favicon.ico'); ?>" type="image/x-icon">
    <link rel="apple-touch-icon" sizes="57x57" href="<?php echo esc_url(CT_IMAGE_URL . '/apple-icon-57x57.png'); ?>">
    <link rel="apple-touch-icon" sizes="60x60" href="<?php echo esc_url(CT_IMAGE_URL . '/apple-icon-60x60.png'); ?>">
    <link rel="apple-touch-icon" sizes="72x72" href="<?php echo esc_url(CT_IMAGE_URL . '/apple-icon-72x72.png'); ?>">
    <link rel="apple-touch-icon" sizes="76x76" href="<?php echo esc_url(CT_IMAGE_URL . '/apple-icon-76x76.png'); ?>">
    <link rel="apple-touch-icon" sizes="114x114" href="<?php echo esc_url(CT_IMAGE_URL . '/apple-icon-114x114.png'); ?>">
    <link rel="apple-touch-icon" sizes="120x120" href="<?php echo esc_url(CT_IMAGE_URL . '/apple-icon-120x120.png'); ?>">
    <link rel="apple-touch-icon" sizes="144x144" href="<?php echo esc_url(CT_IMAGE_URL . '/apple-icon-144x144.png'); ?>">
    <link rel="apple-touch-icon" sizes="152x152" href="<?php echo esc_url(CT_IMAGE_URL . '/apple-icon-152x152.png'); ?>">
    <link rel="apple-touch-icon" sizes="180x180" href="<?php echo esc_url(CT_IMAGE_URL . '/apple-icon-180x180.png'); ?>">
    <link rel="icon" type="image/png" sizes="192x192"  href="<?php echo esc_url(CT_IMAGE_URL . '/android-icon-192x192.png'); ?>">
    <link rel="icon" type="image/png" sizes="32x32" href="<?php echo esc_url(CT_IMAGE_URL . '/favicon-32x32.png'); ?>">
    <link rel="icon" type="image/png" sizes="32x32" href="<?php echo esc_url(CT_IMAGE_URL . '/favicon-48x48.png'); ?>">
    <link rel="icon" type="image/png" sizes="96x96" href="<?php echo esc_url(CT_IMAGE_URL . '/favicon-96x96.png'); ?>">
    <link rel="icon" type="image/png" sizes="16x16" href="<?php echo esc_url(CT_IMAGE_URL . '/favicon-16x16.png'); ?>">
    <link rel="manifest" href="<?php echo esc_url(CT_IMAGE_URL . '/manifest.json'); ?>">
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="msapplication-TileImage" content="<?php echo esc_url(CT_IMAGE_URL . '/ms-icon-144x144.png'); ?>">
    <meta name="theme-color" content="#ffffff">
    <meta name="google-site-verification" content="BPAUEH2UUSIzUPTMxkTdK2ErhZwqUqi-X4x9gYsko_E" />
    <?php wp_head(); ?>
    <!-- Google Analytics 4 -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-61H36E3748"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
    
        // ضبط Google Analytics
        gtag('config', 'G-61H36E3748');
    </script>
    <!-- End Google Analytics 4 -->
</head>

<?php $body_class = ct_get_extra_body_class(); ?>

<body <?php body_class($body_class); ?>>

    <!--[if lte IE 8]>
            <p class="chromeframe">You are using an <strong>outdated</strong> browser. Please <a href="//browsehappy.com/">upgrade your browser</a>.</p>
        <![endif]-->

    <?php if (!empty($ct_options['preload'])) : ?>
        <div id="preloader">
            <div class="sk-spinner sk-spinner-wave">
                <div class="sk-rect1"></div>
                <div class="sk-rect2"></div>
                <div class="sk-rect3"></div>
                <div class="sk-rect4"></div>
                <div class="sk-rect5"></div>
            </div>
        </div>
        <!-- End Preload -->
    <?php endif; ?>

    <div class="layer"></div>
    <!-- Mobile menu overlay mask -->

    <!-- Header Plain:  add the class plain to header and change logo.png to logo_sticky.png ======================= -->
    <?php
    $header_class = '';
    $page_template = basename(get_page_template());
    if ((!empty($ct_options['header_style']) && ($ct_options['header_style'] == 'plain')) || ($page_template == "template-full-width-no-footer.php")) {
        $header_class = 'plain';
    }
    ?>

    <header class="<?php echo esc_attr($header_class); ?>">
        <div id="top_line">
            <div class="container">
                <div class="row">
                    <div class="col-md-6 col-sm-6 col-xs-6">
                        <?php if (!empty($ct_options['phone_no'])) { ?><strong><?php echo esc_html($ct_options['phone_no']) ?></strong><i class="icon-phone"></i><?php } ?>
                        <span id="opening">السبت الى الخميس: 9.00am - 5.00pm <i class="icon-clock-1"></i></span>
                    </div>

                    <div class="col-md-6 col-sm-6 col-xs-6">
                        <ul id="top_links">
                            <li>
                                <ul>
                                    <li class="">
                                        <a href="<?php echo esc_url(home_url('/about-us')); ?>">عن الشركه</a>
                                    </li>
                                    <li class="">
                                        <a href="<?php echo esc_url(home_url('/blog')); ?>">مدونه</a>
                                    </li>
                                    <li class="">
                                        <a href="<?php echo esc_url(home_url('/faqs')); ?>">اسئلة واجوبة</a>
                                    </li>
                                </ul>
                            </li>
                            <?php if (!is_user_logged_in()) : ?>
                                <li>
                                    <div class="dropdown dropdown-access">
                                        <a href="#" class="dropdown-toggle" data-toggle="dropdown" id="access_link">تسجيل الدخول</a>
                                        <div class="dropdown-menu">
                                            <div class="row">
                                                <div class="col-xs-12">
                                                    <?php do_action('facebook_login_button'); ?>
                                                </div>
                                            </div>
                                            <div class="login-or">
                                                <hr class="hr-or">
                                                <span class="span-or">أو</span>
                                            </div>
                                            <form action="<?php echo esc_url(home_url('/wp-login.php')); ?>" method="post">
                                                <div class="form-group">
                                                    <input type="text" name="log" id="user_login" aria-describedby="login_error" class="form-control" id="inputUsernameEmail" placeholder="البريد الإلكتروني">
                                                </div>
                                                <div class="form-group">
                                                    <input type="password" name="pwd" id="user_pass" aria-describedby="login_error" class="form-control" id="inputPassword" placeholder="كلمه السر">
                                                </div>
                                                <a id="forgot_pw" href="<?php echo esc_url(home_url('/login/?action=lostpassword')); ?>">هل نسيت كلمة المرور؟</a>
                                                <input type="submit" name="Sign_in" value="تسجيل الدخول" id="Sign_in" class="button_drop">
                                                <a href="<?php echo esc_url(home_url('/login/?action=register')); ?>" class="button_drop outline">
                                                    انشاء حساب
                                                </a>
                                                <!-- <input type="button" name="Sign_up" value="انشاء حساب" id="Sign_up" class="button_drop outline" > -->
                                            </form>
                                        </div>
                                    </div>
                                    <!-- End Dropdown access -->
                                </li>
                            <?php else : ?>
                                
                                <li><a href="<?php echo esc_url(home_url('/wishlist')); ?>" id="wishlist_link">المفضلة</a> </li>
                                <li><a href="<?php echo wp_logout_url(home_url()); ?>">تسجيل الخروج</a></li>
                                <?php endif; ?>
                                </li>
                        </ul>
                    </div>
                </div><!-- End row -->
            </div><!-- End container-->
        </div><!-- End top line-->

        <div class="container">
            <div class="row header-links">
                <div class="col-md-3 col-sm-3 col-xs-12 mobile-block">
                    <div id="logo_home">
                        <h1><a href="<?php echo esc_url(home_url('/')); ?>" title="اجازات مصر">اجازات مصر</a></h1>
                    </div>
                </div>

                <nav class="col-md-9 col-sm-9 col-xs-12 mobile-block">
                    <button type="button" class="cmn-toggle-switch cmn-toggle-switch__htx open_close" aria-expanded="false" aria-controls="mobile-menu" aria-label="<?php esc_attr_e('Menu mobile', 'citytours'); ?>"><span><?php _e('Menu mobile', 'citytours') ?></span></button>

                    <div class="main-menu" id="mobile-menu">
                        <div id="header_menu">
                            <img src="<?php echo esc_url(ct_logo_sticky_url()) ?>" width="<?php echo esc_attr(ct_get_header_logo_width()) ?>" height="<?php echo esc_attr(ct_get_header_logo_height()) ?>" alt="<?php echo esc_attr(get_bloginfo('name')); ?>">
                        </div>

                        <button type="button" class="open_close" id="close_in" aria-label="<?php esc_attr_e('إغلاق', 'citytours'); ?>"><i class="icon_set_1_icon-77" aria-hidden="true"></i></button>
                        <?php
                        if (has_nav_menu('header-menu')) {
                            wp_nav_menu(array('theme_location' => 'header-menu', 'container' => '', 'menu_class' => '', 'menu_id' => ''));
                        } else {
                        ?>
                            <div>
                                <span class="fa fa-camera-retro fa-3x"></span>
                                <ul>
                                    <li class="menu-item"><a href="<?php echo esc_url(home_url('/')); ?>"><?php esc_html_e('Home', "ct"); ?></a></li>
                                    <li class="menu-item"><a href="<?php echo esc_url(admin_url('nav-menus.php')); ?>"></span><?php esc_html_e('Configure', "ct"); ?></a></li>
                                </ul>
                            </div>
                        <?php } ?>
                    </div><!-- End main-menu -->
                            
                    <ul id="top_tools">
                        <?php if ((isset($ct_options['header_search_bar']) && !empty($ct_options['header_search_bar'])) || !isset($ct_options['header_search_bar'])) : ?>
                            <li>
                                <div class="dropdown dropdown-search">
                                    <a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="icon-search"></i></a>
                                    <div class="dropdown-menu">
                                        <?php get_search_form(); ?>
                                    </div>
                                </div>
                            </li>
                        <?php endif; ?>

                        <?php if (class_exists('WooCommerce') && $ct_options['cart_show_mini_cart']) { ?>
                            <li>
                                <div class="dropdown dropdown-cart">
                                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                                        <i class=" icon-basket-1"></i><?php _e('Cart', 'woocommerce') ?>
                                        (<span class="cart-item-qty"><?php echo WC()->cart->cart_contents_count; ?></span>)
                                    </a>
                                    <ul class="dropdown-menu" id="cart_items">
                                        <?php woocommerce_mini_cart(array('type' => 'header-mini-cart')); ?>
                                    </ul>
                                </div>
                            </li>
                        <?php } ?>
                    </ul>
                </nav>

                <div class="mobile-block" style="display:none">
                    <span>
                        <i class="fa fa-user"></i>
                        <?php if (!is_user_logged_in()) : ?>
                            <a href="<?php echo esc_url(home_url('/login')); ?>">
                                حسابي
                            </a>
                        <?php else : ?>
                            <a href="<?php echo wp_logout_url(home_url()); ?>">تسجيل الخروج</a>
                            <a href="<?php echo esc_url(home_url('/wishlist')); ?>" id="wishlist_link">المفضلة</a>
                        <?php endif ?>
                    </span>
                </div>


            </div>
        </div><!-- container -->
    </header><!-- End Header -->
<main id="main">
