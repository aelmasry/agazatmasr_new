<?php
if (!defined('ABSPATH')) {
	exit;
}

$post_id = get_the_ID();

?>
<div id="post-<?php echo esc_attr($post_id); ?>" <?php post_class('post'); ?>>
<a href="<?php echo the_permalink() ?>" >
	<?php
	if (has_post_thumbnail()) {
		the_post_thumbnail('full', array('class' => 'img-responsive'));
	}
	?>
</a>
<a href="<?php echo the_permalink() ?>" >
	<h2><?php the_title(); ?></h2>
</a>
	<p><?php the_excerpt(); ?></p>

	<a href="<?php the_permalink(); ?>" class="btn_1"><?php echo esc_html__('Read more', 'citytours') ?></a>

</div><!-- end post -->
