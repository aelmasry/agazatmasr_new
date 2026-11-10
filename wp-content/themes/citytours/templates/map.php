<?php 
if ( ! defined( 'ABSPATH' ) ) { 
    exit; 
}

global $container_id, $center, $related, $zoom, $maptypecontrol, $maptype;

$map_id = 'map-' . mt_rand( 10000, 99999 );

if ( ! empty( $container_id ) ) { ?>
	<div class="collapse" id="collapseMap<?php echo $container_id ?>">
<?php } ?>

<div id="<?php echo $map_id ?>" class="citytours-map"></div>

<?php if ( ! empty( $container_id ) ) { ?>
	</div>
<?php } ?>

<script type="text/javascript">
	jQuery(document).ready(function($){
		<?php if ( ! empty( $container_id ) ) {  ?>
		$('#collapseMap').on('shown.bs.collapse', function(e){
		<?php } ?>
			var mapId = '<?php echo $map_id ?>';
			var zoom = <?php echo $zoom ?>;
			var mapType = <?php echo $maptype ?>;
			var mapTypeControl = <?php echo $maptypecontrol ?>;
			var markersData = {
				<?php foreach ( $related as $each_ht ) {
					if ( get_post_type( $each_ht ) == 'hotel' ) {
						$each_pos = get_post_meta( $each_ht, '_hotel_loc', true );
						$post_type = 'Hotels';
					} else { 
						$each_pos = get_post_meta( $each_ht, '_tour_loc', true );
						$post_type = 'Tours';
					}
					if ( ! empty( $each_pos ) ) {
						$each_pos = explode( ',', $each_pos );
						$description = str_replace( "'", "\'", wp_trim_words( strip_shortcodes(get_post_field("post_content", $each_ht)), 20, '...' ) );
						 ?>
							'<?php echo $each_ht ?>' :  [{
								name: '<?php echo get_the_title( $each_ht ) ?>',
								type: '<?php echo $post_type ?>',
								location_latitude: <?php echo $each_pos[0] ?>,
								location_longitude: <?php echo $each_pos[1] ?>,
								map_image_url: '<?php echo ct_get_header_image_src( $each_ht, "ct-map-thumb" ) ?>',
								name_point: '<?php echo get_the_title( $each_ht ) ?>',
								description_point: '<?php echo $description ?>',
								url_point: '<?php echo get_permalink( $each_ht ) ?>'
							}],
						<?php
					}
				} ?>
			};
			<?php 
			if ( ! empty( $center ) ) {
				$center_pos = explode( ',', $center );
			} else if ( empty( $center ) && ! empty( $related[0] ) ) {
				if ( get_post_type( $related[0] ) == 'hotel' ) {
					$center_pos = get_post_meta( $related[0], '_hotel_loc', true );
				} else { 
					$center_pos = get_post_meta( $related[0], '_tour_loc', true );
				}
				$center_pos = explode( ',', $center_pos );
			}
			 ?>

			var lati = <?php echo trim($center_pos[0]) ?>;
			var long = <?php echo trim($center_pos[1]) ?>;
			// var _center = [48.865633, 2.321236];
			var _center = [lati, long];
			renderMap( _center, markersData, zoom, mapType, mapTypeControl, mapId );

		<?php if ( !empty( $container_id ) ) {  ?>
		});
		<?php } ?>
	});
</script>