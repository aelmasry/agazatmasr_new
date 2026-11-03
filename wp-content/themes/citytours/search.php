<?php
if ( ! defined( 'ABSPATH' ) ) { 
    exit; 
}

get_header();

global $ct_options;

$header_img_scr = ct_get_header_image_src('blog');
if ( ! empty( $header_img_scr ) ) {
	$sidebar_position = ct_get_sidebar_position('blog');
	$content_class = '';
	if ( 'no' != $sidebar_position ) $content_class = 'col-md-9';
	$header_content = ct_get_header_content( 'blog' );
	$header_img_height = ct_get_header_image_height('blog');
	?>

	<section class="parallax-window" data-parallax="scroll" data-image-src="<?php echo esc_url( $header_img_scr ) ?>" data-natural-width="1400" data-natural-height="<?php echo esc_attr( $header_img_height ); ?>">
		<div class="parallax-content-1">
			<div class="animated fadeInDown">
				<h1><?php printf( esc_html__( 'Search Results for: %s', 'citytours' ), '<span>' . get_search_query() . '</span>' ); ?></h1>
			</div>
		</div>
	</section><!-- End section -->
	<div id="position">

<?php } else { ?>
	<div id="position" class="blank-parallax">
<?php } ?>

	<div class="container"><?php ct_breadcrumbs(); ?></div>
</div><!-- End Position -->

<section id="content">
	<div class="container margin_60">
		<div class="row">
			<?php if ( 'left' == $sidebar_position ) : ?>
				<aside class="col-md-3 add_bottom_30">
					<?php generated_dynamic_sidebar(); ?>
				</aside><!-- End aside -->
			<?php endif; ?>

			<?php if ( have_posts() ) : ?>
				<div class="<?php echo esc_attr( $content_class ); ?>">
					<div class="box_style_1">
						<?php $index = 0;
						while(have_posts()): the_post();
							if ( 0 != $index ) echo '<hr>';
							$index++;
							ct_get_template( 'loop-blog.php', '/templates' );
						endwhile; ?>
					</div><!-- end box_style_1 -->

					<hr>

					<div class="text-center">
						<?php echo paginate_links( array( 'type' => 'list','prev_text' => esc_html__( 'Prev', 'citytours' ), 'next_text' => esc_html__('Next', 'citytours'), ) ); ?>
					</div>

				</div><!-- End col-md-9-->

				<?php wp_link_pages('before=<div class="page-links">&after=</div>'); ?>

			<?php else: ?>
				<div class="<?php echo esc_attr( $content_class ); ?>">
					<div class="box_style_1">
						<h2><?php echo esc_html__( "Nothing Found", 'citytours'); ?></h2>
						<p><?php echo esc_html__( "Sorry, no posts matched your criteria. Please try another search.", 'citytours' ); ?><br /><?php echo esc_html__( "You might want to consider some of our suggestions to get better results:", 'citytours' ); ?></p>
						<ul class="triangle">
							<li><?php echo esc_html__( "Check your spelling.", 'citytours' ); ?></li>
							<li><?php echo esc_html__( "Try a similar keyword.", 'citytours' ); ?></li>
							<li><?php echo esc_html__( "Try using more than one keyword.", 'citytours' ); ?></li>
							<li><?php echo esc_html__( "See frequently asked questions.", 'citytours' ); ?></li>
							<li><?php echo esc_html__( "Contact the support center.", 'citytours' ); ?></li>
						</ul>
					</div>
				</div>
			<?php endif; ?>

			<?php if ( 'right' == $sidebar_position ) : ?>
				<aside class="col-md-3 add_bottom_30">
					<?php generated_dynamic_sidebar(); ?>
				</aside><!-- End aside -->
			<?php endif; ?>
		</div>
	</div>
</section>

<?php 

get_footer();