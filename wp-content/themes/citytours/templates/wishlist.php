<?php
/* Wishlist Page Template */
if ( ! defined( 'ABSPATH' ) ) { 
    exit; 
}

get_header();

global $ct_options;

$order_array = array( 'ASC', 'DESC' );
$order_by_array = array(
	'' => '',
	'price' => 'price',
	'rating' => 'rating'
);
$order_defaults = array(
	'price' => 'ASC',
	'rating' => 'DESC'
);

$post_type_filter = ( isset( $_REQUEST['post_types'] ) && is_array( $_REQUEST['post_types'] ) ) ? $_REQUEST['post_types'] : array();
$current_view = isset( $_REQUEST['view'] ) ? sanitize_text_field( $_REQUEST['view'] ) : 'list';

$wishlist = array();
if ( is_user_logged_in() ) {
	$user_id = get_current_user_id();
	$wishlist = get_user_meta( $user_id, 'wishlist', true );
}

// filter by post type
if ( ! empty( $wishlist ) && ! empty( $post_type_filter ) && is_array( $post_type_filter ) ) {
	foreach ( $wishlist as $key => $post_id) {
		$post_type = get_post_type( $post_id );
		if ( ! in_array( $post_type , $post_type_filter ) ) { unset( $wishlist[$key] ); }
	}
}

if (is_array($wishlist) || $wishlist instanceof Countable) {
    $count = count($wishlist);
} else {
    // Handle the case where $items is not countable
    // For example, you might want to set a default count or log an error
    $count = 0; // Default count
    error_log('Error: $items is not countable in wishlist.php');
}

$header_img_scr = ct_get_header_image_src('tour');
$header_img_height = ct_get_header_image_height( $post_id );
if ( ! empty( $header_img_scr ) ) {
	$header_content = '';
	if ( ! empty( $ct_options['tour_header_content'] ) ) $header_content = $ct_options['tour_header_content'];
	?>

	<section class="parallax-window" data-parallax="scroll" data-image-src="<?php echo esc_url( $header_img_scr ) ?>" data-natural-width="1400" data-natural-height="<?php echo esc_attr( $header_img_height ); ?>">
		<div class="parallax-content-1">
			<div class="animated fadeInDown">
				<h1><?php esc_html_e( 'Your wishlist', 'citytours' ); ?></h1>
			</div>
		</div>
	</section><!-- End section -->

	<div id="position">

<?php } else { ?>
	<div id="position" class="blank-parallax">
<?php } ?>

	<div class="container"><?php ct_breadcrumbs(); ?></div>
</div><!-- End Position -->

<div class="container margin_60">
	<div class="row">
		<aside class="col-lg-3 col-md-3">

			<div id="filters_col">
				<a data-toggle="collapse" href="#collapseFilters" aria-expanded="false" aria-controls="collapseFilters" id="filters_col_bt"><i class="icon_set_1_icon-65"></i><?php echo esc_html__( 'Filters', 'citytours' ) ?> <i class="icon-plus-1 pull-right"></i></a>

				<div class="collapse" id="collapseFilters">

					<div class="filter_type">
						<h6><?php echo esc_html__( 'Type', 'citytours' ) ?></h6>

						<ul class="list-filter post-type-filter" data-base-url="<?php echo esc_url( remove_query_arg( array( 'post_types', 'page' ) ) ); ?>" data-arg="post_types">
							<?php
							if ( ct_is_hotel_enabled() ) {
								$checked = ( in_array( 'hotel', $post_type_filter ) ) ? ' checked="checked"' : '';
								echo '<li><label><input type="checkbox" name="post_types[]" value="hotel"' . $checked . '>' . esc_html__( 'Hotels', 'citytours' ) . '</label></li>';
							}
							if ( ct_is_tour_enabled() ) {
								$checked = ( in_array( 'tour', $post_type_filter ) ) ? ' checked="checked"' : '';
								echo '<li><label><input type="checkbox" name="post_types[]" value="tour"' . $checked . '>' . esc_html__( 'Tours', 'citytours' ) . '</label></li>';
							}
							?>
						</ul>
					</div>

				</div><!--End collapse -->
			</div><!--End filters col-->

		</aside><!--End aside -->

		<div class="col-lg-9 col-md-8">
			<div id="tools">
				<div class="row">
					<div class="col-md-6 col-sm-6 col-xs-12">
					</div>

					<div class="col-md-6 col-sm-6 hidden-xs text-right">
						<a href="<?php echo esc_url( add_query_arg( array( 'view' => 'grid' ) ) ) ?>" class="bt_filters" title="<?php esc_html_e(  'Grid View', 'citytours' ) ?>"><i class="icon-th"></i></a>
						<a href="<?php echo esc_url( add_query_arg( array( 'view' => 'list' ) ) ) ?>" class="bt_filters" title="<?php esc_html_e(  'List View', 'citytours' ) ?>"><i class="icon-list"></i></a>
					</div>
				</div>
			</div><!--End tools -->

			<div class="tour-list <?php if ( $current_view == 'grid' ) echo 'row' ?>">
				<?php
				if ( empty( $wishlist ) ) :
					if ( is_user_logged_in() ) {
						echo '<h5 class="empty-list">' . esc_html__( 'Your wishlist is empty.', 'citytours' ) . '</h5>';
					} else {
						echo '<h5 class="empty-list">' . esc_html__( 'You need to login to check your wishlist', 'citytours' ) . '</h5>';
					}
				else :
					foreach( $wishlist as $post_id ) {
						global $post_id;
						$post_type = get_post_type( $post_id );
						if ( ! empty( $post_type ) ) :
							ct_get_template( 'loop-' . $current_view . '.php', '/templates/' . get_post_type( $post_id ) . '/');
						endif;
					}
				endif;
				?>
			</div><!-- hotel-list -->

			<hr>

		</div><!-- End col lg 9 -->
	</div><!-- End row -->
</div><!-- End container -->

<script type="text/javascript">
	jQuery(document).ready(function(){
		jQuery('input').iCheck({
		   checkboxClass: 'icheckbox_square-grey',
		   radioClass: 'iradio_square-grey'
		});
	});
</script>

<?php 

get_footer();