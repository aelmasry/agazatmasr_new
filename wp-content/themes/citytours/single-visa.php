<?php
/* Tour List Page Template */
if (!defined('ABSPATH')) {
	exit;
}

get_header();

if (have_posts()) {
	while (have_posts()) : the_post();
		$post_id = get_the_ID();

		$sidebar_position = ct_get_sidebar_position($post_id);
		$content_class = 'post-content';
		if ('no' != $sidebar_position) $content_class .= ' col-md-9';

		$header_img_scr = ct_get_header_image_src($post_id);
		if (!empty($header_img_scr)) {
			$header_content = ct_get_header_content($post_id);
			$header_img_height = ct_get_header_image_height($post_id);
?>

			<section class="parallax-window" data-parallax="scroll" data-image-src="<?php echo esc_url($header_img_scr) ?>" data-natural-width="1400" data-natural-height="<?php echo $header_img_height; ?>">
				<div class="parallax-content-1">
					<?php echo balancetags($header_content); ?>
				</div>
			</section><!-- End section -->
			<div id="position">

			<?php } else { ?>
				<div id="position" class="blank-parallax">
				<?php } ?>

				<div class="container"><?php ct_breadcrumbs(); ?></div>
				</div><!-- End Position -->

				<div class="container margin_60">
					<div class="<?php if ('no' != $sidebar_position) echo 'row' ?>">
						<?php if ('left' == $sidebar_position) : ?>
							<aside class="col-md-3 add_bottom_30">

								<div id="modify_search">
									<a data-toggle="collapse" href="#collapseModify_search" aria-expanded="true" aria-controls="collapseModify_search" id="modify_col_bt">
										<i class="icon_set_1_icon-78"></i><?php esc_html_e('Modify Search', 'citytours') ?> <i class="icon-plus-1 pull-right"></i>
									</a>

									<div class="collapse show" id="collapseModify_search">
										<div class="modify_search_wp">
											<form role="search" method="get" id="search-tour-form" action="<?php echo esc_url(home_url('/')); ?>">
												<input type="hidden" name="post_type" value="visa">

												<div class="form-group">
													<label>اسم القارة</label>
													<?php $categories = get_terms('visa-category', array('hide_empty' => false, 'parent' => 0));
													if (!isset($categories)) :
													?>
														<select class="form-control" name="category" id="category">
															<option value="" selected><?php esc_html_e('All Continents', 'citytours') ?></option>
															<?php
															foreach ($categories as $cat) {
																$term_id = $cat->term_id;
																$icon_class = get_tax_meta($term_id, 'ct_tax_icon_class');
															?>
																<option value="<?php echo esc_attr($term_id) ?>" <?php echo (!isset($category) && $category[0] == $term_id) ? "selected='selected'" : ""; ?>><?php echo esc_html($cat->name) ?></option>
															<?php } ?>
														</select>
													<?php endif; ?>
												</div>
												
												<div class="form-group">
													<label>
														<?php esc_html_e('tour search terms', 'citytours') ?>
													</label>
													<?php if (!empty($category) &&  count($category) >= 1) {
														$countries = get_terms('visa-category', array('hide_empty' => false, 'parent' => $category[0]));
													}
													?>
													<select class="form-control" name="country" id="countries" <?php echo (!isset($category) &&  is_array($category)) ? "" : "disabled"; ?>>
														<option value="" selected><?php esc_html_e('Select country', 'citytours') ?></option>
														<?php
															foreach ($countries as $country) {
																$term_id = $country->term_id;
																$icon_class = get_tax_meta($term_id, 'ct_tax_icon_class');
														?>
																<option value="<?php echo esc_attr($term_id) ?>" <?php echo ($country[0] == $term_id) ? "selected='selected'" : ""; ?>><?php echo esc_html($country->name) ?></option>
														<?php } ?>
													</select>
												</div>
														
												<div class="form-group">
													<label>للبحـث</label>
													<input type="text" class="form-control" id="search_terms" name="s" placeholder="<?php echo esc_html_e('Type your search terms', 'citytours') ?>" value="<?php echo esc_attr($s) ?>">
												</div>

												<button class="btn_1 green"><?php esc_html_e('Search again', 'citytours') ?></button>
											</form>
										</div>
									</div>
									<!--End collapse -->
								</div>
								<?php ct_get_template('widget.php', '/templates/tour/'); ?>

								<?php include 'contact_info.php'; ?>

								<?php dynamic_sidebar('sidebar-visa'); ?>
							</aside><!-- End aside -->
						<?php endif; ?>

						<div class="<?php echo esc_attr($content_class); ?>">
							<div class="box_style_1">
								<div class="post nopadding">
									<?php if (has_post_thumbnail()) {
										the_post_thumbnail('full', array('class' => 'img-responsive'));
									} ?>

									<div class="post_info clearfix">
										<div class="post-left">
											<ul>
												<li><i class="icon-calendar-empty"></i><?php echo esc_html__('On', 'citytours') ?> <span><?php echo get_the_date(); ?></span></li>
												<li><i class="icon-inbox-alt"></i><?php echo esc_html__('In', 'citytours') ?> <?php the_category(' '); ?></li>
												<li><i class="icon-tags"></i><?php echo esc_html__('Tags:', 'citytours') ?> <?php the_tags(''); ?></li>
											</ul>
										</div>

										<div class="post-right">
											<i class="icon-comment"></i><?php comments_number(); ?>
										</div>
									</div>

									<h2><?php the_title(); ?></h2>

									<?php the_content(); ?>

									<?php wp_link_pages('before=<div class="page-links">&after=</div>'); ?>
								</div><!-- end post -->
							</div><!-- end box_style_1 -->

							<?php
							if (comments_open() || get_comments_number()) {
								comments_template();
							}
							?>

						</div><!-- End col-md-9-->

						<?php if ('right' == $sidebar_position) : ?>
							<aside class="col-md-3 add_bottom_30">
								<?php generated_dynamic_sidebar(); ?>
							</aside><!-- End aside -->
						<?php endif; ?>

					</div><!-- End row-->
				</div><!-- End container -->

		<?php
	endwhile;
}
wp_enqueue_style('blog', CT_TEMPLATE_DIRECTORY_URI . '/css/blog.css');
get_footer();
