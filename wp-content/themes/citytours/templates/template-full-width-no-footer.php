<?php
 /*
 Template Name: Full Page Map Template
 */

if ( ! defined( 'ABSPATH' ) ) { 
    exit; 
}

get_header();

if ( have_posts() ) {
	while ( have_posts() ) : the_post();
		$post_id = get_the_ID();
		$content_class = 'post-content';
		$content_class = apply_filters( 'ct_add_custom_content_class', $content_class ); 
		?>

		<div class="<?php echo esc_attr( $content_class ); ?>">

			<div class="post nopadding clearfix">

				<?php the_content(); ?>

			</div><!-- end post -->

		</div>

		<?php 
	endwhile;
}

get_footer();