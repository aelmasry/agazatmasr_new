<?php
/* 404 Page Template */
if ( ! defined( 'ABSPATH' ) ) { 
    exit; 
}

get_header();?>

<section id="hero">

	<div class="intro_title error">
		<h1 class="animated fadeInDown">404</h1>

		<p class="animated fadeInDown"><?php echo esc_html__( 'THE PAGE YOU WERE LOOKING FOR COULD NOT BE FOUND.', 'citytours' ) ?></p>

		<?php if ( get_post_type_archive_link('tour') ) : ?>

			<a href="<?php echo esc_url( home_url('/') ) ?>" class="animated fadeInUp button_intro"><?php echo esc_html__( 'Back to home', 'citytours' ) ?></a>

			<a href="<?php echo esc_url( get_post_type_archive_link('tour') ) ?>" class="animated fadeInUp button_intro outline"><?php echo esc_html__( 'View all tours', 'citytours' ) ?></a>

		<?php endif; ?>
	</div>

</section><!-- End hero -->

<?php

get_footer();