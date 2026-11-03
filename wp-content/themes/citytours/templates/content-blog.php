<?php
 /*
 Blog Page Content
 */
if ( ! defined( 'ABSPATH' ) ) { 
    exit; 
}

global $cat;

$posts_per_page = get_option( 'posts_per_page' );
$arg_str = 'post_type=post&post_status=publish&posts_per_page=' . $posts_per_page . '&paged='. get_query_var('paged');

if ( ! empty( $cat ) ) {
	$arg_str .= '&cat=' . $cat;
}

query_posts( $arg_str );
?>

<div class="box_style_1">
	<?php 
	if ( have_posts() ) :
		$index = 0; 

		while ( have_posts() ): the_post();
			if ( 0 != $index ) { 
				?>

				<hr>

				<?php 
			} 

			$index++;

			ct_get_template( 'loop-blog.php', '/templates' );
		endwhile;
	else : 
		esc_html_e( 'No posts found', 'citytours' );
	endif; 
	?>
</div><!-- end box_style_1 -->

<hr>

<div class="text-center">
	<?php echo paginate_links( array( 'type' => 'list','prev_text' => esc_html__( 'Prev', 'citytours' ), 'next_text' => esc_html__('Next', 'citytours'), ) ); ?>
</div>

<?php 

wp_reset_query();