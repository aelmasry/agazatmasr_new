<?php
/* Default Archive Page Template */
if (!defined('ABSPATH')) {
    exit;
}

get_header();

global $ct_options;

$category = (isset($_REQUEST['category'])) ? (is_array($_REQUEST['category']) ? $_REQUEST['category'] : $_REQUEST['category']) : array();
$country = (isset($_REQUEST['country'])) ? (is_array($_REQUEST['country']) ? $_REQUEST['country'] : $_REQUEST['country']) : array();

$header_img_scr = ct_get_header_image_src('visa');
if (!empty($header_img_scr)) {
    $sidebar_position = ct_get_sidebar_position('visa');

    $content_class = '';
    if ('no' != $sidebar_position) $content_class = 'col-md-9';

    $desc = '';
    if (is_tag()) {
        $desc = tag_description();
    }
    if (is_category()) {
        $desc = category_description();
    }
?>
    <style>
        .img-responsive {
            width: 100% !important;
        }
    </style>
    <section class="parallax-window" data-parallax="scroll" data-image-src="<?php echo esc_url($header_img_scr) ?>" data-natural-width="1400" data-natural-height="470">
        <div class="parallax-content-1">
            <div class="animated fadeInDown">
                <h1>
                    <?php if (is_category()) {
                        single_cat_title();
                    } elseif (is_tag()) {
                        single_tag_title();
                    } ?>
                </h1>
                <p><?php echo ($desc) ?></p>
            </div>
        </div>
    </section><!-- End section -->
    <div id="position">

    <?php } else { ?>
        <div id="position" class="blank-parallax">
        <?php } ?>

        <div class="container"><?php ct_breadcrumbs(); ?></div>
        </div><!-- End Position -->
        
        <div class="container margin_60">
            <div class="row">

                <aside class="col-md-3 add_bottom_30">

                    <div id="modify_search">
                        <a data-toggle="collapse" href="#collapseModify_search" aria-expanded="true" aria-controls="collapseModify_search" id="modify_col_bt">
                            <i class="icon_set_1_icon-78"></i><?php esc_html_e('Modify Search', 'citytours') ?> <i class="icon-plus-1 pull-right"></i>
                        </a>

                        <div class="collapse show" id="collapseModify_search">
                            <div class="modify_search_wp">
                                <form role="search" method="get" id="search-tour-form" action="<?php echo esc_url(home_url('/')); ?>">
                                    <input type="hidden" name="post_type" value="visa">

                                    <div class="form-group">
                                        <label>اسم القارة</label>
                                        <?php $categories = get_terms('visa-category', array('hide_empty' => false, 'parent' => 0));
                                        if (!empty($categories)) :
                                        ?>
                                            <select class="form-control" name="category" id="category">
                                                <option value="" selected><?php esc_html_e('All Continents', 'citytours') ?></option>
                                                <?php
                                                foreach ($categories as $cat) {
                                                    $term_id = $cat->term_id;
                                                    $icon_class = get_tax_meta($term_id, 'ct_tax_icon_class');
                                                ?>
                                                    <option value="<?php echo esc_attr($term_id) ?>" <?php echo (!empty($category) && $category[0] == $term_id) ? "selected='selected'" : ""; ?>><?php echo esc_html($cat->name) ?></option>
                                                <?php } ?>
                                            </select>
                                        <?php endif; ?>
                                    </div>
                                    <?php if (!isset($category) &&  is_array($category)) :
                                        $countries = get_terms('visa-category', array('hide_empty' => false, 'parent' => $category[0]));
                                    ?>
                                    <div class="form-group">
                                        <label>
                                            <?php esc_html_e('tour search terms', 'citytours') ?>
                                        </label>
                                        
                                        <select class="form-control" name="country" id="countries" <?php echo ((!isset($category) &&  is_array($category))) ? "" : "disabled"; ?>>
                                            <option value="" selected><?php esc_html_e('Select country', 'citytours') ?></option>
                                            <?php
                                                foreach ($countries as $country) {
                                                    $term_id = $country->term_id;
                                                    $icon_class = get_tax_meta($term_id, 'ct_tax_icon_class');
                                            ?>
                                                    <option value="<?php echo esc_attr($term_id) ?>" <?php echo ($country[0] == $term_id) ? "selected='selected'" : ""; ?>><?php echo esc_html($country->name) ?></option>
                                            <?php }
                                            ?>
                                        </select>
                                    </div>
                                    <?php endif; ?>
                                    <div class="form-group">
                                        <label>للبحـث</label>
                                        <input type="text" class="form-control" id="search_terms" name="s" placeholder="<?php echo esc_html_e('Type your search terms', 'citytours') ?>" value="<?php echo esc_attr($s) ?>">
                                    </div>

                                    <button class="btn_1 green"><?php esc_html_e('Search again', 'citytours') ?></button>
                                </form>
                            </div>
                        </div>
                        <!--End collapse -->
                    </div>
                    
                    <?php dynamic_sidebar('sidebar-visa'); ?>

                </aside><!-- End aside -->

                <?php //endif; ?>
                
                <div class="<?php echo esc_attr($content_class); ?>">
                    <div class="blog1 box_style_1">
                    <?php
                        $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
                        
                        $per_page = 7;
                        $args = [
                            'posts_per_page' => $per_page,
                            'post_type' => array('visa'),
                            'post_status' => array('publish'),
                            'paged' => $paged
                        ];
                        
                        if (!empty($category)) {
                            $args['meta_query'] = array(
                                'relation' => 'AND',
                                array(
                                    'key' => '_visa_category',
                                    'value' => $category,
                                    'compare' => 'LIKE'
                                )
                            );
                        }

                        if (!empty($country)) {
                            $args['meta_query'] = array(
                                'relation' => 'AND',
                                array(
                                    'key' => '_visa_city',
                                    'value' => $country,
                                    'compare' => 'LIKE'
                                )
                            );
                        }

                        // The Query
                        $the_query = new WP_Query($args);
                        
                        if ($the_query->have_posts()) :
                            $index = 0;

                            while ($the_query->have_posts()) : $the_query->the_post();
                                if (0 != $index) {
                        ?>
                              
                        <?php
                                }

                                $index++;

                                ct_get_template('loop-visa.php', '/templates');
                            endwhile;
                        // wp_pagenavi(array('query' => $the_query));
                        else :
                            esc_html_e('No posts found', 'citytours');
                        endif;
                        ?>

                    </div><!-- end box_style_1 -->

                    <hr>

                    <div class="text-center">
                    <?php
                        $current = max(1, get_query_var('paged'));
                        $per_page=$per_page?? 7;
                        // echo $pagination;
                        $request = explode("/", $wp->request);
                        
                        unset($_GET['page']);
                        
                        $pagenum_link =  home_url($request[0]) . '%_%';
                        $total = $the_query->max_num_pages?? 0;
                        $args = array(
                            'base' => $pagenum_link, // http://example.com/all_posts.php%_% : %_% is replaced by format (below)
                            'total' => $total,
                            'format' => '/page/%#%',
                            'current' => $current,
                            'show_all' => false,
                            'prev_next' => true,
                            'prev_text' => esc_html__('Previous', 'citytours'),
                            'next_text' => esc_html__('Next', 'citytours'),
                            'end_size' => 1,
                            'mid_size' => 5,
                            'type' => 'list',
                            'add_args' => $_GET,
                        );

                        $pagination = paginate_links($args) ?? '';
                        $pagination = str_replace("<ul class='page-numbers'>", '<ul class="pagination">', $pagination);
                        $pagination = str_replace('page-numbers', '', $pagination);

                        // echo $pagination;
                        echo wp_kses_post($pagination);

                        wp_reset_postdata();
                    ?>
                    </div>

                </div><!-- End col-md-9-->

                <?php wp_link_pages('before=<div class="page-links">&after=</div>'); ?>

                <?php if ('right' == $sidebar_position && is_active_sidebar('sidebar-post')) : ?>

                    <aside class="col-md-3 add_bottom_30">

                        <?php dynamic_sidebar('sidebar-post'); ?>

                    </aside><!-- End aside -->

                <?php endif; ?>

            </div><!-- End row-->
        </div><!-- End container -->

        <?php


        get_footer();
        ?>

        <script type="text/javascript">
            jQuery.noConflict();
            jQuery(document).ready(function($) {

                const queryString = window.location.search;
                const urlParams = new URLSearchParams(queryString);
                const country = urlParams.get('country')

                // load continents
                $('#category').change(function() {
                    var category = $(this).val();
                    get_countries(category);
                })

                function get_countries(category) {
                    $.ajax({
                        type: "POST",
                        dataType: "json",
                        url: ajaxurl,
                        data: {
                            action: "get_visa_cities",
                            'category': category
                        },
                        success: function(response) {
                            var options = '<option value="" selected><?php esc_html_e('Select country', 'citytours') ?></option>';
                            $.each(response.cities, function(index, item) {
                                if (country == item['term_id']) {
                                    options += '<option value="' + item['term_id'] + '" selected>' + item['name'] + '</option>';
                                } else {
                                    options += '<option value="' + item['term_id'] + '">' + item['name'] + '</option>';
                                }
                            });

                            $('#countries').html(options);
                            $('#countries').prop("disabled", false);
                        }
                    });
                }
            });
        </script>
