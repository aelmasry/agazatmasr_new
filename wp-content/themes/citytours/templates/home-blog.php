<?php
/*
  Blog Page Content
 */
if (!defined('ABSPATH')) {
    exit;
}

global $title, $post_count, $order, $paged;

$home_query_args = array(
    'post_type' => 'post',
    'post_status' => 'publish',
    'posts_per_page' => max(1, (int) $post_count),
    'orderby' => 'date',
    'order' => ($order === 'ASC') ? 'ASC' : 'DESC',
    'no_found_rows' => true,
);
if (!empty($cat)) {
    $home_query_args['cat'] = (int) $cat;
}
$home_posts = new WP_Query($home_query_args);

?>
<div class="">
    <div class="">
        <div class="blog_sec_home">
        <div class="main_title" style="margin-bottom:25px !important">
            <h2><?php echo $title ?></h2>
        </div>
            <div class="items">
                <?php
                if ($home_posts->have_posts()) :
                    $index = 0;

                    while ($home_posts->have_posts()): $home_posts->the_post();
//                        if (0 != $index) {
//                        
//                        }
//
//                        $index++;
                        ?>
                        <div class="item">
                        
                            <div class="pic" style="margin-bottom:15px;">
                                <a href="<?php echo the_permalink() ?>" ><?php the_post_thumbnail(array(380,240), array('class' => 'img-responsive')) ?></a>
                            </div>
                            <h3> <a href="<?php echo the_permalink() ?>" > <?php echo the_title() ?> </a> </h3>
                            <div class="text">
                                <ul>
                                    <li>
                                        <i class="icon-comment"></i>
                                        (<?php comments_number(); ?>) التعليقات
                                    </li>
                                    <li>
                                    <i class="icon-calendar"></i>
                                    <?php echo get_the_date(); ?>
                                    </li>
                                </ul> 
                                <p><?php echo get_the_excerpt(); ?></p>
                                <a href="<?php echo the_permalink() ?>" > أقرا المزيد</a>
                            </div>

                        </div>

                        <?php
                    endwhile;
                else :
                    esc_html_e('No posts found', 'citytours');
                endif;
                ?>
            </div><!-- end box_style_1 -->
        </div>

    </div>
</div>
<?php
wp_reset_postdata();
