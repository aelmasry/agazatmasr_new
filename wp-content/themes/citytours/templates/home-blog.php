<?php
/*
  Blog Page Content
 */
if (!defined('ABSPATH')) {
    exit;
}

global $title, $post_count, $order, $paged;

$posts_per_page = $post_count;
$arg_str = 'post_type=post&post_status=publish&posts_per_page=' . $posts_per_page . '&paged=' . $paged;
// Initialize variables if they are not already defined

if (!empty($cat)) {
    $arg_str .= '&cat=' . $cat;
}
if (!empty($order)) {
    $arg_str .= '&order=' . $order;
}

query_posts($arg_str);

?>
<div class="">
    <div class="">
        <div class="blog_sec_home">
        <div class="main_title" style="margin-bottom:25px !important">
            <h2><?php echo $title ?></h2>
        </div>
            <div class="items">
                <?php
                if (have_posts()) :
                    $index = 0;

                    while (have_posts()): the_post();
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
wp_reset_query();
