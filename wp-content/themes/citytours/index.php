<?php
/**
 * Template Name: Blog Post Page
 */
if (!defined('ABSPATH')) {
    exit;
}

get_header();

// Initialize variables if they are not already defined
$infoReq = isset($_GET['info']) ? $_GET['info'] : '';
$cityReq = isset($_GET['city']) ? $_GET['city'] : '';
$countryReq = isset($_GET['country']) ? $_GET['country'] : '';

// Get the parent term ID and the taxonomy name
$parent_term_id = 1;
$taxonomy = 'state';

// Get all child terms of the parent term
$child_terms = get_term_children($parent_term_id, $taxonomy);

// Add the parent term ID to the list of term IDs
$term_ids = array($parent_term_id);
if (!is_wp_error($child_terms)) {
    $term_ids = array_merge($term_ids, $child_terms);
}

$header_img_scr = ct_get_header_image_src('blog');
if (!empty($header_img_scr)) {
    $sidebar_position = ct_get_sidebar_position('blog');
    $content_class = '';
    if ('no' != $sidebar_position)
        $content_class = 'col-md-9';

    $header_content = ct_get_header_content('blog');

    $header_img_height = ct_get_header_image_height('blog');
?>

    <section class="parallax-wi ndow" data-parallax="scroll" data-image-src="<?php echo esc_url($header_img_scr) ?>" data-natural-width="1400" data-natural-height="<?php echo $header_img_height; ?>">
        <div class="parallax-content-1">
            <div class="animated fadeInDown">
                <?php echo balancetags($header_content); ?>
            </div>
        </div>
    </section><!-- End section -->
<?php } ?>

<?php if (!is_home()) : ?>
    <div id="position" <?php if (empty($header_img_scr)) echo 'class="blank-parallax"' ?>>
        <div class="container"><?php ct_breadcrumbs(); ?></div>
    </div>
    <!-- End Position -->
<?php endif; ?>

<div class="container margin_60">
    <div class="row mob-style">
        <?php if ('left' == $sidebar_position) : ?>
            <aside class="col-md-3 add_bottom_30">
                <?php generated_dynamic_sidebar(); ?>
                <?php ct_get_template('widget.php', '/templates/tour/'); ?>
                <?php include 'contact_info.php'; ?>
            </aside><!-- End aside -->
        <?php endif; ?>

        <div class="<?php echo esc_attr($content_class); ?>">
            <div class="blog1 box_style_1" >
                <?php
                $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
                $per_page = 7;
                // WP_Query arguments
                $args = array(
                    'posts_per_page' => $per_page,
                    'post_type' => 'post', // Change this if you're querying a custom post type
                    'post_status' => array('publish'),
                    'paged' => $paged,
                );
                
                if (!empty($infoReq)) {
                    $args['tax_query'] = array(
                        array(
                            'taxonomy' => $taxonomy,
                            'field'    => 'term_id',
                            'terms'    => $infoReq,
                        )
                    );
                }elseif (!empty($cityReq)) {
                    $args['tax_query'] = array(
                        array(
                            'taxonomy' => $taxonomy,
                            'field'    => 'term_id',
                            'terms'    => $cityReq,
                        )
                    );
                }elseif (!empty($countryReq)) {
                    $args['tax_query'] = array(
                        array(
                            'taxonomy' => $taxonomy,
                            'field'    => 'term_id',
                            'terms'    => $countryReq,
                        )
                    );
                }
                
                // The Query
                $blogQuery = new WP_Query($args);              
                
                if ($blogQuery->have_posts()) :
                    $index = 0;

                    while ($blogQuery->have_posts()) : $blogQuery->the_post();
                        if (0 != $index) {
                ?>
                       
				
				
                <?php
                        }

                        $index++;

                        ct_get_template('loop-blog.php', '/templates');
                    endwhile;
                // wp_pagenavi(array('query' => $blogQuery));
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
                
                $count = $count?? 0;
                $per_page=$per_page?? 0;
                unset($_GET['page']);
                
                $pagenum_link =  home_url($request[0]) . '%_%';
                $total = $blogQuery->max_num_pages?? 0;
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

                echo $pagination;

                wp_reset_postdata();
            ?>
            </div>

        </div><!-- End col-md-9-->

        <?php wp_link_pages('before=<div class="page-links">&after=</div>'); ?>

        <?php if ('right' == $sidebar_position) : ?>
            <aside class="col-md-3 add_bottom_30">
                <?php generated_dynamic_sidebar(); ?>
            </aside><!-- End aside -->
        <?php endif; ?>

    </div><!-- End row-->
</div><!-- End container -->


<?php
wp_enqueue_style('blog', CT_TEMPLATE_DIRECTORY_URI . '/css/blog.css');
get_footer();