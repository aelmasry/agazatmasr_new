<style>
	.tour_list_desc {
		padding: 0 10px 0px 0px;
	}
</style>
<div class="tour_list_widget">
<?php
if (!defined('ABSPATH')) {
	exit;
}

$args = array(
	'post_type' => 'tour',
	'orderby' => 'rand',
	'posts_per_page' => 5,
	'suppress_filters' => 0,
	'post_status' => 'publish',
	'meta_query' => array(
		'relation' => 'AND',
		array(
			'key' => '_tour_featured',
			'value' => '1',
		),
		array(
			'key' => '_tour_discount_rate',
			'value' => array(0, 100),
			'type' => 'numeric',
			'compare' => 'BETWEEN',
		)
	),
);



// The Query
$tourQuery = new WP_Query($args);

if ($tourQuery->have_posts()) :
	$index = 0;

	while ($tourQuery->have_posts()) : $tourQuery->the_post();
		if (0 != $index) {
			echo '<hr>';
		}

		$index++;

		$post_id = get_the_id();
		
		$wishlist = array();
		if (is_user_logged_in()) {
			$user_id = get_current_user_id();
			$wishlist = get_user_meta($user_id, 'wishlist', true);
		}
		if (!is_array($wishlist)) $wishlist = array();
		$price = get_post_meta($post_id, '_tour_price', true) ?? 0;
		
		$tour_type = wp_get_post_terms($post_id, 'tour_type');
		$brief = get_post_meta($post_id, '_tour_brief', true);
		if (empty($brief)) {
			$brief = apply_filters('the_content', get_post_field('post_content', $post_id));
			$brief = wp_trim_words($brief, 20, '');
		}
		$review = get_post_meta($post_id, '_review', true);
		$review = (!empty($review)) ? round($review, 1) : 0;
		$featured = get_post_meta($post_id, '_tour_featured', true);
		$hot = get_post_meta($post_id, '_tour_hot', true);
		$discount_rate = get_post_meta($post_id, '_tour_discount_rate', true);

		$wishlist_link = ct_wishlist_page_url();
?>

		<div class="strip_all_tour_list" data-wow-delay="0.1s">

			<div class="row">

				<div class="col-md-12">
					<?php if (!empty($featured)) { ?>
						<div class="ribbon_3" style="left:15px;"><span><?php _e('Featured', 'citytours'); ?></span></div>
					<?php } elseif (!empty($hot)) { ?>
						<div class="ribbon_3 popular"><span><?php _e('Hot', 'citytours'); ?></span></div>
					<?php } ?>
					<?php if (!empty($wishlist_link)) : ?>
						<div class="wishlist">
							<a class="tooltip_flip tooltip-effect-1 btn-add-wishlist" href="#" data-post-id="<?php echo esc_attr($post_id) ?>" <?php echo (in_array(ct_tour_org_id($post_id), $wishlist) ? ' style="display:none;"' : '') ?>><span class="wishlist-sign">+</span><span class="tooltip-content-flip"><span class="tooltip-back"><?php esc_html_e('Add to wishlist', 'citytours'); ?></span></span></a>
							<a class="tooltip_flip tooltip-effect-1 btn-remove-wishlist" href="#" data-post-id="<?php echo esc_attr($post_id) ?>" <?php echo (!in_array(ct_tour_org_id($post_id), $wishlist) ? ' style="display:none;"' : '') ?>><span class="wishlist-sign">-</span><span class="tooltip-content-flip"><span class="tooltip-back"><?php esc_html_e('Remove from wishlist', 'citytours'); ?></span></span></a>
						</div>
					<?php endif; ?>

					<div class="img_list" style="margin-left:0 !important;">
						<a href="<?php echo esc_url(get_permalink($post_id)); ?>">
							<?php echo get_the_post_thumbnail($post_id, 'large'); ?>
							<?php if (!empty($discount_rate)) { ?>
								<div class="badge_save"><?php _e('Discount', 'citytours'); ?><strong><?php echo esc_html($discount_rate . '%'); ?></strong></div>
							<?php } ?>
							<?php
							if (!empty($tour_type)) {
								$icon_class = get_tax_meta($tour_type[0]->term_id, 'ct_tax_icon_class', true);
								echo '<div class="short_info">' . (empty($icon_class) ? '' : '<i class="' . $icon_class . '"></i>') . $tour_type[0]->name . ' </div>';
							}
							?>
						</a>
					</div>
				</div>

				<div class="clearfix visible-xs-block"></div>

				<div class="col-md-12">
					<div class="tour_list_desc">
						<div class="rating"><?php ct_rating_smiles($review) ?><small>(<?php echo esc_html(ct_get_review_count($post_id)) ?>)</small></div>
						<h3>
							<a href="<?php echo esc_url(get_permalink($post_id)); ?>"> <?php echo esc_html(get_the_title($post_id)); ?></a>
						</h3>

						<div class="price_list" style="height: 100px !important;">
							<div>
								<?php echo ct_price($price, 'special') ?><small><?php echo esc_html__('*Per person', 'citytours') ?></small>
								<p><a href="<?php echo esc_url(get_permalink($post_id)); ?>" class="btn_1"><?php esc_html_e('Watch and Book', 'citytours') ?></a></p>
							</div>
						</div>
					</div>
				</div>
			</div>

		</div>
		<!--End strip -->
<?php
	endwhile;
	wp_reset_postdata();
endif;
?>
</div>