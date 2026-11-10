<?php 
if ( ! defined( 'ABSPATH' ) ) { 
    exit; 
}

$post_id = get_the_ID(); 
?>

<div id="post-<?php echo esc_attr( $post_id ); ?>" <?php post_class(); ?>>
	<a href="<?php echo the_permalink() ?>" >
		<?php
		if ( has_post_thumbnail() ) { 
			the_post_thumbnail( 'full', array('class' => 'img-responsive') ); 
		} 
		?>
	</a>

	<div class="post_info clearfix">
		<div class="post-left">
			<ul>
				<li><i class="icon-calendar-empty"></i><?php echo "أضافه المحتوى" ?> <span><?php echo get_the_date(); ?></span></li>
				<li><i class="icon-inbox-alt"></i><?php echo esc_html__( 'In', 'citytours' ) ?> <?php the_category( ' ' ); ?></li>
				<li><i class="icon-tags"></i><?php echo "الوجهه"?> </li>
			</ul>
		</div>
		
		<div class="post-right"><i class="icon-comment"></i><?php comments_number(); ?></div>
	</div>

	<h2> <a href="<?php echo the_permalink() ?>" > <?php echo the_title() ?> </a> </h2>

	<p><?php the_excerpt(); ?></p>

	<a href="<?php the_permalink(); ?>" class="btn_1"><?php echo esc_html__( 'Read more', 'citytours' ) ?></a>

</div><!-- end post -->