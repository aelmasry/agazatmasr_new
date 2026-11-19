<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! function_exists( 'ct_tour_get_schedule_html' ) ) {
	function ct_tour_get_schedule_html( $tour_id ) {
		$schedules = ct_tour_get_schedules( $tour_id );
		$has_multi_schedules = get_post_meta( $tour_id, '_has_multi_schedules', true );
		if ( ! empty( $schedules ) ) :
			$schedule_id = $schedules[0]['schedule_id'];
			foreach ( $schedules as $key => $schedule ) :
				if ( $schedule_id != $schedule['schedule_id'] ) {
					echo '</tbody></table></div>';
				}

				if ( ( $key == 0 ) || ( $schedule_id != $schedule['schedule_id'] ) ) { ?>

					<div class="table-responsive">
						<table class="table table-striped">
						<thead>
							<?php if ( ! empty( $has_multi_schedules ) ) : ?>
							<tr>
								<th colspan="2">
									<?php if ( ! empty( $schedule['from'] ) && $schedule['from'] != '0000-00-00' ) echo date( 'jS F', strtotime( $schedule['from'] ) );
									if ( ! empty( $schedule['to'] ) && $schedule['to'] != '0000-00-00' ) echo ' ' . esc_html__( 'to', 'citytours' ) . ' ' . date( 'jS F', strtotime( $schedule['to'] ) ); ?>
								</th>
							</tr>
							<?php endif; ?>
						</thead>
						<tbody>

				<?php }
				echo '<tr> <td> ' . date('l', strtotime("Monday +" . $schedule['day'] . " days")) . ' </td> <td> ';
				if ( ! empty( $schedule['is_closed'] ) ) {
					echo '<span class="label label-danger">' . esc_html__( 'Closed', 'citytours' ) . '</span>';
				} else {
					echo esc_html( $schedule['open_time'] . ' - ' . $schedule['close_time'] ) ;
				}
				echo ' </td> </tr>';
				$schedule_id = $schedule['schedule_id'];
			endforeach;
			echo '</tbody></table></div>';
		endif;
	}
}