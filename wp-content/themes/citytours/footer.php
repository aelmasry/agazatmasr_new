<?php
/* Footer Template */
if (!defined('ABSPATH')) {
    exit;
}

global $ct_options;
?>
</main>
<?php
$page_template = basename(get_page_template());

if ($page_template != "template-full-width-no-footer.php") :
    global $epm_options, $current_user;

    wp_get_current_user();
    $epm_default_email_value = null;
    if (is_user_logged_in()) {
        $epm_default_email_value = $current_user->user_email;
    }
?>
    <style>
        @media (max-width: 440px) {
  /* Define the styling for the selected state */
  .sp-image {
        width: 100% !important;
    height: auto !important;
    /* margin-left: 0px; */
    /* margin-top: -59.8062px; */
    display: flex !important;
    justify-content: center !important;
    align-items: center !important;
    position: absolute !important;
    z-index: 1;
  }
    </style>
    <div id="newsletter_container">
        <div class="container margin_30">
            <div class="row">
                <div class="col-md-10 col-md-offset-1 text-center">
                    <h3>اشترك في النشرة الإخبارية للحصول على أحدث الأخبار</h3>
                    <div id="message-newsletter_2"></div>
                    <form method="post" action="<?php echo esc_url(get_template_directory_uri() . '/assets/newsletter.php'); ?>" name="epm-sign-up-form" id="newsletter_2" class="epm-sign-up-form">
                        <input name="email_newsletter_2" id="email_newsletter_2" type="email" value="<?php echo $epm_default_email_value ?>" placeholder="البريد الخاص بك" class="form-control email">
                        <input type="hidden" name="epm_submit" id="epm_submit" value="true">
                        <input type="hidden" name="epm_list_id" id="epm_list_id" value="<?php echo $list ?? ''; ?>">
                        <input type="submit" name="epm-submit-chimp" value="اشترك" data-wait-text="انتظر من فضلك..." class="newsletter-btn btn epm-sign-up-button epm-submit-chimp">
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- End newsletter_container -->
    <footer id="footer_3">
        <div class="container">

            <div class="row footer-links">

                <div class="col-md-3 col-sm-3 col-xs-12 block">
                    <p>
                        <img src="<?php echo esc_url(CT_TEMPLATE_DIRECTORY_URI . '/imgs/logo.png') ?>" width="160" height="56" alt="Agazat Masr" data-retina="true" id="logo">
                    </p>

                    <ul class="social-links">
                        <?php
                        $social_links = array(
                            'facebook' => 'Facebook',
                            'twitter' => 'Twitter',
                            'google' => 'Google',
                            'instagram' => 'Instagram',
                            'pinterest' => 'Pinterest',
                            'vimeo' => 'Vimeo',
                            'youtube-play' => 'YouTube',
                            'linkedin' => 'LinkedIn',
                        );

                        foreach ($social_links as $social_link => $social_name) :
                            if (!empty($ct_options[$social_link])) :
                        ?>
                                <li>
                                    <a href="<?php echo esc_url($ct_options[$social_link]) ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php echo esc_attr($social_name); ?>"><span class="fa fa-<?php echo esc_attr($social_link) ?>" aria-hidden="true"></span></a>
                                </li>
                        <?php
                            endif;
                        endforeach;
                        ?>
                       
                        <li>
                            <a href="https://api.whatsapp.com/send/?phone=201225993443&text&type=phone_number&app_absent=0" target="_blank" rel="noopener noreferrer" aria-label="WhatsApp"><span class="fa fa-whatsapp" aria-hidden="true"></span></a>
                        </li>
                        <li>
                            <a style="background-color: #19cb77f5" href="tel:+201225993443" aria-label="<?php esc_attr_e('اتصل بنا', 'citytours'); ?>"><span class="fa fa-phone" aria-hidden="true"></span></a>
                        </li>

                    </ul>
                    <?php ct_get_template('contact_info.php', '/'); ?>
                </div>

                <div class="col-md-3 col-sm-3 col-xs-12 block">
                    <?php if (is_active_sidebar('sidebar-footer-2')) : ?>
                        <?php dynamic_sidebar('sidebar-footer-2'); ?>
                    <?php endif; ?>
                </div>

                <div class="col-md-3 col-sm-3 col-xs-12 block">
                    <?php if (is_active_sidebar('sidebar-footer-3')) : ?>
                        <?php dynamic_sidebar('sidebar-footer-3'); ?>
                    <?php endif; ?>
                </div>

                <div class="col-md-3 col-sm-3 col-xs-12 block">
                    <?php if (is_active_sidebar('sidebar-footer-4')) : ?>
                        <?php dynamic_sidebar('sidebar-footer-4'); ?>
                    <?php endif; ?>
					
                </div>
                
            </div>
            <!-- End row -->

            <div class="row">
                <div class="col-md-12">
                    <div id="social_footer" style="padding-bottom:20px;">

                        <div class="payment-" style="margin-bottom:15px;">
                            <img src="<?php echo esc_url(CT_TEMPLATE_DIRECTORY_URI . '/imgs/payment-methods-min.png') ?>" width="180" height="35" alt="طرق الدفع المتاحة" id="paymentmethods" loading="lazy" decoding="async">
                        </div>

                        <div style="padding-bottom:20px; width: 100% !important; text-align: center !important;">
                            <?php if (!empty($ct_options['copyright'])) {
                                $copyright = (string) $ct_options['copyright'];
                                $year = date_i18n('Y');
                                $copyright = str_replace('{year}', $year, $copyright);
                                $copyright = preg_replace('/(?<!\d)20\d{2}(?!\d)/', $year, $copyright, 1);
                                echo esc_html($copyright) . ' - ';
                            } ?>
                            <span dir="ltr">Powered by <a style="text-decoration: none;" href="https://alisalem.me/" target="_blank" rel="noopener noreferrer">Ali Salem</a></span>
                        </div>
                    </div>
                </div>
            </div><!-- End row -->

        </div><!-- End container -->
    </footer><!-- End footer -->

<?php endif; ?>

<div id="toTop"></div><!-- Back to top button -->
<div id="overlay"><i class="icon-spin3 animate-spin"></i></div>

<?php
wp_enqueue_style(
    'jquery-auto-complete',
    'https://cdnjs.cloudflare.com/ajax/libs/jquery-autocomplete/1.0.7/jquery.auto-complete.css',
    array(),
    '1.0.7'
);

wp_enqueue_script(
    'jquery-auto-complete',
    'https://cdnjs.cloudflare.com/ajax/libs/jquery-autocomplete/1.0.7/jquery.auto-complete.min.js',
    array('jquery'),
    '1.0.7',
    true
);


// Search forms, validation and date pickers. Enqueued (instead of a hard-coded <script>)
// so it gets a cache-busting version and loads after the libraries it calls.
wp_enqueue_script(
    'ct_footer_script',
    CT_TEMPLATE_DIRECTORY_URI . '/js/footer.min.js',
    array('jquery', 'ct_script_jquery_validate', 'ct_script_datepicker', 'jquery-auto-complete', 'ct_theme_script'),
    filemtime(get_template_directory() . '/js/footer.min.js'),
    true
);

if (is_tag() || is_category() || is_archive()) {
    wp_enqueue_style('blog', CT_TEMPLATE_DIRECTORY_URI . '/css/blog.css');
}
wp_footer();
?>
<script type="text/javascript">
    (function(c,l,a,r,i,t,y){
        c[a]=c[a]||function(){(c[a].q=c[a].q||[]).push(arguments)};
        t=l.createElement(r);t.async=1;t.src="https://www.clarity.ms/tag/"+i;
        y=l.getElementsByTagName(r)[0];y.parentNode.insertBefore(t,y);
    })(window, document, "clarity", "script", "o5p6vaxi11");
</script>
<?php /* AGZ-006: retired UA-49084486-1 removed; GA4 is loaded via GTM-54SHNR28 only. */ ?>
</body>

</html>
