<?php
/* Blog Page Template */
if (!defined('ABSPATH')) {
    exit;
}

get_header();

$header_img_scr = ct_get_header_image_src('blog');
if (!empty($header_img_scr)) {
    $sidebar_position = ct_get_sidebar_position('blog');
    $content_class = '';
    if ('no' != $sidebar_position)
        $content_class = 'col-md-9';
    
    $header_content = ct_get_header_content('blog');
    
    $header_img_height = ct_get_header_image_height('blog');
    ?>

    <section class="parallax-window" data-parallax="scroll" data-image-src="<?php echo esc_url($header_img_scr) ?>" data-natural-width="1400" data-natural-height="<?php echo $header_img_height; ?>">
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
    <div class="row">
            <?php if ('left' == $sidebar_position) : ?>
            <aside class="col-md-3 add_bottom_30">
            <?php generated_dynamic_sidebar(); ?>
            </aside><!-- End aside -->
<?php endif; ?>

        <div class="<?php echo esc_attr($content_class); ?>">
            <div class="box_style_1">
                <?php
                if (have_posts()) :
                    $index = 0;

                    while (have_posts()): the_post();
                        if (0 != $index) {
                            ?>
                            <hr>
                            <?php
                        }

                        $index++;

                        ct_get_template('loop-blog.php', '/templates');

                    endwhile;
                else :
                    esc_html_e('No posts found', 'citytours');
                endif;
                ?>
            </div><!-- end box_style_1 -->

            <hr>

            <div class="text-center">
                <?php echo paginate_links(array('type' => 'list', 'prev_text' => esc_html__('Prev', 'citytours'), 'next_text' => esc_html__('Next', 'citytours'),)); ?>
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
if ( is_home() && ! is_front_page() ) :
    wp_enqueue_style('blog', CT_TEMPLATE_DIRECTORY_URI . '/css/blog.css');
endif;
get_footer();
