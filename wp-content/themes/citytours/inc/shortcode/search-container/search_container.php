<?php 
// Add this to your theme's functions.php or a custom plugin file
function search_container_shortcode($atts) {
    // Extract style attribute if passed
    $atts = shortcode_atts(array(
        'style' => ''
    ), $atts);
    
    // Start output buffering
    ob_start();
    // [search_container style="your-style-here"]
    // Initialize active class
    $active_class = 'active';
    ?>
    <section id="search_container_elementor" class="search_container_elementor">
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
                        <?php $active_class = ''; ?>
                    <?php endif; ?>
                    
                    <?php if (ct_is_tour_enabled()) : ?>
                        <li>
                            <a href="#tours" data-toggle="tab">
                                <img src="<?php echo esc_url(get_template_directory_uri() . '/imgs/eiffel-icon-white.png'); ?>" class="wh-effile" alt="image">
                                <img src="<?php echo esc_url(get_template_directory_uri() . '/imgs/eiffel-icon-black.png'); ?>" class="bl-effile" alt="image" style="display:none;">
                                <?php esc_html_e('Tours', 'citytours') ?>
                            </a>
                        </li>
                        <?php $active_class = ''; ?>
                    <?php endif; ?>
                    
                    <li>
                        <a href="https://tourico.amadeusonlinesuite.com/flights?lc=AR" target="_blank">
                            <span class="fa icon-flight"></span>
                            <?php esc_html_e('flights', 'citytours') ?>
                        </a>
                    </li>
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
    // Get the buffered content and clean the buffer
    return ob_get_clean();
}
add_shortcode('search_container', 'search_container_shortcode');