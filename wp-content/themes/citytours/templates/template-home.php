<?php
/*
  Template Name: Template Home
 */
if (!defined('ABSPATH')) {
    exit;
}

get_header();

if (have_posts()) {
    while (have_posts()) : the_post();
        $post_id = get_the_ID();
        $content_class = 'post-content';
        $header_img_scr = ct_get_header_image_src($post_id);
        $style = "";
        if (!empty($header_img_scr)) {
            $header_img_height = ct_get_header_image_height($post_id);
            $style = "background-image: url('" . $header_img_scr . "');";
            if (!empty($header_img_height)) {
                $style .= " height: " . $header_img_height . "px; ";
            }
        }
        $active_class = 'active';
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
                       
                        <!-- <a href="#flights" data-toggle="tab"> -->    
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
                        <!-- end of container -->
                    </div>

                </div>
            </div>
            </div>
        </section>
        <!-- End hero -->

        <div class="<?php echo esc_attr($content_class); ?>">
            <div class="nopadding">
                <?php
                if (has_post_thumbnail()) {
                    the_post_thumbnail('full', array('class' => 'img-responsive'));
                }

                the_content();

                wp_link_pages('before=<div class="page-links">&after=</div>');

                if (comments_open() || get_comments_number()) {
                    comments_template();
                }
                ?>
            </div>
            <!-- end post -->
        </div>

        <script type="text/javascript">
            $ = jQuery.noConflict();

            $(document).ready(function() {

                var lang = '<?php echo get_locale() ?>';

                lang = lang.replace('_', '-');

                $('input.date-pick').datepicker({
                    startDate: "today",
                    language: lang
                });

                //                $('input[name="date_from"]').datepicker('setDate', 'today');
                //                $('input[name="date_to"]').datepicker('setDate', '+1d');

                $('.way-back').on("click", function() {
                    $('.return-date').removeClass('disable');
                    $('.return-date').attr('required', true)
                });

                $('.one-way').on("click", function() {
                    $('.return-date').addClass('disable');
                    $('.return-date').attr('required', false)
                });

                var amadeusSearchRequest;
                var searchResult = new Array()
                $('#amadeus_air_dep_from').autoComplete({
                    minChars: 2,
                    delay: 0,
                    source: function(term, suggest) {
                        try {
                            amadeusSearchRequest.abort();
                        } catch (e) {}

                        amadeusSearchRequest = $.post(ajaxurl, {
                            'term': term,
                            action: 'airports_search'
                        }, function(res) {
                            suggest(res)
                        })
                    },
                    onSelect: function(event, ui) {
                        event.preventDefault();
                        $('#fromCode').val(ui.split(', ')[1])
                    },
                })

                $('#amadeus_air_dep_to').autoComplete({
                    minChars: 2,
                    delay: 0,
                    source: function(term, suggest) {
                        try {
                            amadeusSearchRequest.abort();
                        } catch (e) {}

                        amadeusSearchRequest = $.post(ajaxurl, {
                            'term': term,
                            action: 'airports_search'
                        }, function(res) {
                            suggest(res)
                        })
                    },
                    onSelect: function(event, ui) {
                        event.preventDefault();
                        $('#toCode').val(ui.split(', ')[1])
                    },
                })

            })
        </script>

<?php
    endwhile;
}

get_footer();
