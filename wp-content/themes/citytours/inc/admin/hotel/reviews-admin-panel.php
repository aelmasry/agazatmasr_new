<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

/**
 * functions to manage reviews
 */
if ( ! class_exists( 'CT_Review_List_Table') ) :
class CT_Review_List_Table extends WP_List_Table {

	function __construct() {
		global $status, $page;
		parent::__construct( array(
			'singular'  => 'review',     //singular name of the listed records
			'plural'    => 'reviews',    //plural name of the listed records
			'ajax'      => false        //does this table support ajax?
		) );
	}

	function column_default( $item, $column_name ) {
		switch( $column_name ) {
			case 'date':
			//case 'post_title':
				return $item[ $column_name ];
			default:
				return print_r( $item, true ); //Show the whole array for troubleshooting purposes
		}
	}

	function column_author_info( $item ) {
		//Build row actions
		$default = '';
		$photo = ct_get_avatar( array( 'id' => $item['user_id'], 'email' => $item['reviewer_email'], 'size' => 32 ) );
		$str = '';
		$str = $photo;
		$str .= '<span class="author-detail">' . $item['reviewer_name'] . '<br />';
		$str .= '<a href="' . esc_url( 'mailto:' . sanitize_email( $item['reviewer_email'] ) ) . '">' . esc_html( $item['reviewer_email'] ) . '</a><br />';
		$str .= '<a href="' . esc_url( 'admin.php?page=reviews&amp;reviewer_ip=' . $item['reviewer_ip'] ) . '">' . esc_html( $item['reviewer_ip'] ) . '</a></span>';
		return $str;
	}

	function column_review( $item ) {
		$str = '';
		$str .= '<a href="' . esc_url( 'admin.php?page=reviews&amp;action=edit&amp;review_id=' . $item['id'] ) .'"><div class="five-stars-container"><span class="five-stars" style="width:' . $item['review_rating']/5*100 . '%" title="' . $item['review_rating'] . '"></span></div></a>';
		$str .= '<div>' . esc_html( substr( stripslashes( $item['review_text'] ), 0, 150 ) ) . '...</div>';
		$link_pattern = 'admin.php?page=%1$s&action=%2$s&review_id=%3$s';

		$actions = array();
		if ( $item['status'] == 'pending' ) {
			$actions = array(
				'review_approve'  => '<a href="' . esc_url( sprintf( $link_pattern, 'reviews', 'approve', $item['id'] ) ) . '">' . esc_html__( 'Approve', 'citytours' ) . '</a>',
				'edit'  => '<a href="' . esc_url( sprintf( $link_pattern, 'reviews', 'edit', $item['id'] ) ) . '">' . esc_html__( 'Edit', 'citytours' ) . '</a>',
				'trash'    => '<a href="' . esc_url( sprintf( $link_pattern, 'reviews', 'trash', $item['id'] ) ) . '">' . esc_html__( 'Trash', 'citytours' ) . '</a>',
			);
		} else if ( $item['status'] == 'approved' ) {
			$actions = array(
				'review_unapprove'  => '<a href="' . esc_url( sprintf( $link_pattern, 'reviews', 'unapprove', $item['id'] ) ) . '">' . esc_html__( 'Unapprove', 'citytours' ) . '</a>',
				'edit'  => '<a href="' . esc_url( sprintf( $link_pattern, 'reviews', 'edit', $item['id'] ) ) . '">' . esc_html__( 'Edit', 'citytours' ) . '</a>',
				'trash'    => '<a href="' . esc_url( sprintf( $link_pattern, 'reviews', 'trash', $item['id'] ) ) . '">' . esc_html__( 'Trash', 'citytours' ) . '</a>',
			);
		} else if ( $item['status'] == 'trashed' ) {
			$actions = array(
				'untrash'    => '<a href="' . esc_url( sprintf( $link_pattern, 'reviews', 'untrash', $item['id'] ) ) . '">' . esc_html__( 'Untrash', 'citytours' ) . '</a>',
				'delete'    => '<a href="' . esc_url( sprintf( $link_pattern, 'reviews', 'delete', $item['id'] ) ) . '">' . esc_html__( 'Delete Permanently', 'citytours' ) . '</a>',
			);
		}

		$str .= $this->row_actions( $actions );

		return $str;
	}

	function column_post_title( $item ) {
		return '<a href="' . esc_url( get_edit_post_link( $item['post_id'] ) ) . '">' . $item['post_title'] . '</a>';
	}

	function column_cb( $item ) {
		return sprintf( '<input type="checkbox" name="%1$s[]" value="%2$s" />', $this->_args['singular'], $item['id'] );
	}

	function get_columns() {
		$columns = array(
			'cb'			=> '<input type="checkbox" />', //Render a checkbox instead of text
			'author_info'	=> esc_html__( 'Author', 'citytours' ),
			'review'		=> esc_html__( 'Review', 'citytours' ),
			'post_title'	=> esc_html__( 'Post Title', 'citytours' ),
			'date'			=> esc_html__( 'Review Date (UTC)', 'citytours' )
		);

		return $columns;
	}

	function get_sortable_columns() {
		$sortable_columns = array(
			'date'       => array( 'date', false ),
			'post_title' => array( 'post_title', false ),
		);

		return $sortable_columns;
	}

	function get_bulk_actions() {
		$actions = array();
		$status = isset( $_GET['status'] ) ? $_GET['status'] : 'pending';

		if ( $status == 'all' ) {
			$actions = array(
				'bulk_movetrash'    => esc_html__( 'Move to Trash', 'citytours' )
			);
		} elseif ( $status == 'approved' ) {
			$actions = array(
				'bulk_unapprove'    => esc_html__( 'Unapprove', 'citytours' ),
				'bulk_movetrash'    => esc_html__( 'Move to Trash', 'citytours' )
			);
		} elseif ( $status == 'trashed' ) {
			$actions = array(
				'bulk_untrash'    => esc_html__( 'Restore', 'citytours' ),
				'bulk_delete'     => esc_html__( 'Delete Permanently', 'citytours' )
			);
		} else {
			$actions = array(
				'bulk_approve'     => esc_html__( 'Approve', 'citytours' ),
				'bulk_movetrash'   => esc_html__( 'Move to Trash', 'citytours' )
			);
		}
		return $actions;
	}

	function process_bulk_action() {
		global $wpdb;
		//Detect when a bulk action is being triggered...

		if ( isset( $_POST['_wpnonce'] ) && ! empty( $_POST['_wpnonce'] ) ) {

			$nonce  = filter_input( INPUT_POST, '_wpnonce', FILTER_SANITIZE_STRING );
			$action = 'bulk-' . $this->_args['plural'];

			if ( ! wp_verify_nonce( $nonce, $action ) )
				wp_die( 'Sorry, your nonce did not verify' );

		}

		$sql = '';
		$status = 'pending';
		switch( $this->current_action() ) {
				//wp_redirect( admin_url( 'admin.php?page=reviews&bulk_delete=true') );
			case 'bulk_movetrash': //status will be 2
				$status = 'trashed';
			case 'bulk_approve': //status will be 1
				$status = 'approved';
			case 'bulk_unapprove':
			case 'bulk_untrash': //status will be 0
				$status = 'pending';
			case 'bulk_delete':
				$selected_ids = $_GET[ $this->_args['singular'] ];
				$how_many = count($selected_ids);
				$placeholders = array_fill(0, $how_many, '%d');
				$format = implode(', ', $placeholders);
				if ( $this->current_action() == "bulk_delete" ) {
					$sql = sprintf('DELETE FROM %1$s WHERE id IN (%2$s)', CT_REVIEWS_TABLE, "$format" );
				} else {
					$sql = sprintf('UPDATE %1$s SET status="%2$s" WHERE id IN (%3$s)', CT_REVIEWS_TABLE, esc_sql( $status ), "$format" );
				}
				$wpdb->query( $wpdb->prepare( $sql, $selected_ids ) );

				/* calculate post rating */
				$sql = sprintf('SELECT post_id FROM %1$s WHERE id IN (%2$s)', CT_REVIEWS_TABLE, "$format" );
				$post_ids = $wpdb->get_col( $wpdb->prepare( $sql, $selected_ids ) );

				foreach ( $post_ids as $post_id ) {
					ct_review_calculate_rating( $post_id );
				}
				wp_redirect( $_SERVER[HTTP_REFERER] );
		}
	}

	function prepare_items() {
		global $wpdb;

		$per_page = 10;
		$columns = $this->get_columns();
		$hidden = array();
		$sortable = $this->get_sortable_columns();
		
		$this->_column_headers = array( $columns, $hidden, $sortable );
		$this->process_bulk_action();
		
		$orderby = ( ! empty( $_REQUEST['orderby'] ) ) ? sanitize_sql_orderby( $_REQUEST['orderby'] ) : 'id'; //If no sort, default to title
		$order = ( ! empty( $_REQUEST['order'] ) ) ? sanitize_text_field( $_REQUEST['order'] ) : 'desc'; //If no order, default to desc
		$current_page = $this->get_pagenum();
		$post_table_name  = $wpdb->prefix . 'posts';

		$where = "1=1";
		if ( ! empty( $_REQUEST['post_id'] ) ) $where .= " AND CT_Reviews.post_id = '" . esc_sql( ct_hotel_org_id( $_REQUEST['post_id'] ) ) . "'";
		if ( ! empty( $_REQUEST['reviewer_ip'] ) ) $where .= " AND CT_Reviews.reviewer_ip = '" . esc_sql( $_REQUEST['reviewer_ip'] ) . "'";
		$status = ( isset( $_REQUEST['status'] ) ) ? esc_sql( $_REQUEST['status'] ) : 'all';
		if ( $status != 'all' ) $where .= " AND CT_Reviews.status = '" . esc_sql( $status ) . "'";

		$sql = sprintf( 'SELECT CT_Reviews.* , hotel.post_title as post_title FROM %1$s as CT_Reviews
						INNER JOIN %2$s as hotel ON CT_Reviews.post_id=hotel.ID
						WHERE ' . $where . ' ORDER BY %4$s %5$s
						LIMIT %6$s, %7$s' , CT_REVIEWS_TABLE, $post_table_name, '', $orderby, $order, ( $per_page * ( $current_page - 1 ) ), $per_page );

		$data = $wpdb->get_results( $sql, ARRAY_A );

		$sql = "SELECT COUNT(*) FROM " . CT_REVIEWS_TABLE . " as CT_Reviews where " . $where;
		$total_items = $wpdb->get_var( $sql );

		$this->items = $data;
		$this->set_pagination_args( array(
			'total_items' => $total_items,                  //WE have to calculate the total number of items
			'per_page'    => $per_page,                     //WE have to determine how many items to show on a page
			'total_pages' => ceil( $total_items/$per_page )   //WE have to calculate the total number of pages
		) );
	}
}
endif;

/*
 * add review list page to menu
 */
if ( ! function_exists( 'ct_review_add_menu_items' ) ) {
	function ct_review_add_menu_items() {
		$page = add_menu_page( 'Reviews', 'Reviews', 'manage_options', 'reviews', 'ct_review_render_pages');
		add_action( 'admin_print_scripts-' . $page, 'ct_review_admin_enqueue_scripts' );
	}
}

/*
 * review admin main actions
 */
if ( ! function_exists( 'ct_review_render_pages' ) ) {
	function ct_review_render_pages() {
		$action = isset( $_REQUEST['action'] ) ? sanitize_text_field( $_REQUEST['action'] ) : '';

		if ( ( 'add' == $action ) || ( 'edit' == $action ) ) {
			ct_review_render_manage_page();
		} elseif ( 'delete' == $action ) {
			ct_review_delete_action();
		} elseif ( 'trash' == $action ) {
			ct_review_change_status_action('trashed');
		} elseif ( 'untrash' == $action ) {
			ct_review_change_status_action('pending');
		} elseif ( 'approve' == $action ) {
			ct_review_change_status_action('approved');
		} elseif ( 'unapprove' == $action ) {
			ct_review_change_status_action('pending');
		} else {
			ct_review_render_list_page();
		}
	}
}

/*
 * render review list page
 */
if ( ! function_exists( 'ct_review_render_list_page' ) ) {
	function ct_review_render_list_page() {

		global $wpdb;
		$ctVancancyTable = new CT_Review_List_Table();
		$ctVancancyTable->prepare_items();
		$page_url = 'admin.php?page=reviews';
		?>

		<div class="wrap">
			
			<h2><?php esc_html_e(  'Reviews', 'citytours' )?><a href="<?php echo esc_url( $page_url ); ?>&amp;action=add" class="add-new-h2">Add New</a></h2>
			<?php if ( isset( $_REQUEST['bulk_delete'] ) ) echo '<div id="message" class="updated below-h2"><p>Reviews deleted</p></div>'?>
			<ul class="subsubsub">
				<?php
					$status_filters = array(
						'all' => esc_html__( 'All', 'citytours' ),
						'pending' => esc_html__( 'Pending', 'citytours' ),
						'approved' => esc_html__( 'Approved', 'citytours' ),
						'trashed' => esc_html__( 'Trash', 'citytours' )
					);
					$status = ( isset( $_REQUEST['status'] ) ) ? sanitize_text_field( $_REQUEST['status'] ) : 'all';
					foreach ( $status_filters as $value => $label ) {

						$where = '1=1';
						if ( $value != 'all' ) $where .= " AND CT_Reviews.status = '" . esc_sql( $value ) . "'";
						$sql = sprintf( 'SELECT COUNT(*) FROM %1$s as CT_Reviews WHERE %2$s', CT_REVIEWS_TABLE, $where );
						$count = $wpdb->get_var( $sql );
						$class = '';
						if ( $status == $value ) $class='current';
						echo '<li><a href="' . esc_url( $page_url . '&amp;status=' . $value ) . '" class="' . esc_attr( $class ) . '">' . esc_html( $label ) . '</a><span class="count">(<span class="pending-count">' . esc_html( $count ) . '</span>)</span></a> |</li>';
					}
				?>
			</ul>
			<div style="float:right;">
				<select id="post_filter">
					<option></option>
					<?php
					$args = array(
						'post_type'         => ct_get_available_modules(),
						'posts_per_page'    => -1,
						'orderby'           => 'title',
						'order'             => 'ASC'
					);
					$hotel_query = new WP_Query( $args );

					if ( $hotel_query->have_posts() ) {
						while ( $hotel_query->have_posts() ) {
							$hotel_query->the_post();
							$selected = '';
							$id = $hotel_query->post->ID;
							if ( ! empty( $_REQUEST['post_id'] ) && ( $_REQUEST['post_id'] == $id ) ) $selected = ' selected ';
							echo '<option ' . esc_attr( $selected ) . 'value="' . esc_attr( $id ) .'">' . wp_kses_post( get_the_title( $id ) ) . '</option>';
						}
					} else {
						// no posts found
					}
					/* Restore original Post Data */
					wp_reset_postdata();
					?>
				</select>
			</div>
			<form id="accomo-reviews-filter" method="get">
				<input type="hidden" name="page" value="<?php echo esc_attr( $_REQUEST['page'] ) ?>" />
				<?php $ctVancancyTable->display() ?>
			</form>
			
		</div>
		<script>
		jQuery(document).ready(function($) {
			$('#post_filter').select2({
				placeholder: "Filter by Post",
				allowClear: true,
				width: "240px"
			});
			$('#post_filter').change(function() {
				var postId = $('#post_filter').val();
				var loc_url = '<?php echo esc_js( $page_url ); ?>';
				var status = '<?php echo esc_js( $status );?>'
				if (postId) loc_url += '&post_id=' + postId + '&status=' + status;
				document.location = loc_url;
			});
			$('.row-actions .delete a').click(function(){
				var r = confirm("It will be deleted permanently. Do you want to delete it?");
				if(r == false) {
					return false;
				}
			});
		});
		</script>
		<?php
	}
}

/*
 * render review detail page
 */
if ( ! function_exists( 'ct_review_render_manage_page' ) ) {
	function ct_review_render_manage_page() {

		global $wpdb, $ct_options;

		if ( ! empty( $_POST['save'] ) ) {
			ct_review_save_action();
			return;
		}

		$default_review_data = array(   
			'post_id'  => '',
			'review_rating' => 0,
			'review_rating_detail' => '',
			'review_text' => '',
			'reviewer_ip' => '127.0.0.1',
			'reviewer_email' => '',
			'reviewer_name' => '',
			'status'        => 'pending',
			'date'        => date( 'Y-m-d H:i:s' ),
			'user_id' => '',
			'booking_no' => '',
			'pin_code' => '',
		);

		$review_data = array();

		if ( 'add' == $_REQUEST['action'] ) {
			$page_title = "Add New Post Review";
		} elseif ( 'edit' == $_REQUEST['action'] ) {
			$page_title = 'Edit Post Review<a href="admin.php?page=reviews&amp;action=add" class="add-new-h2">Add New</a>';
			
			if ( empty( $_REQUEST['review_id'] ) ) {
				echo "<h2>You attempted to edit an item that doesn't exist. Perhaps it was deleted?</h2>";
				return;
			}
			$review_id = sanitize_text_field( $_REQUEST['review_id'] );
			$post_table_name  = $wpdb->prefix . 'posts';

			$sql = sprintf( 'SELECT CT_Reviews.* , hotel.post_title as post_title FROM %1$s as CT_Reviews
							INNER JOIN %2$s as hotel ON CT_Reviews.post_id=hotel.ID
							WHERE CT_Reviews.id = %3$d' , CT_REVIEWS_TABLE, $post_table_name, $review_id );

			$review_data = $wpdb->get_row( $sql, ARRAY_A );
			if ( empty( $review_data ) ) {
				echo "<h2>You attempted to edit an item that doesn't exist. Perhaps it was deleted?</h2>";
				return;
			}
		}

		$review_data = array_replace( $default_review_data, $review_data );
		$review_rating_detail = unserialize( $review_data['review_rating_detail'] );
		?>

		<div class="wrap">
			<h2><?php echo wp_kses_post( $page_title ); ?></h2>
			<?php if ( isset( $_REQUEST['updated'] ) ) echo '<div id="message" class="updated below-h2"><p>Review saved</p></div>'?>
			<form method="post" onsubmit="return manage_review_validateForm1();">
				<input type="hidden" name="id" value="<?php if ( ! empty( $review_data['id'] ) ) echo esc_attr( $review_data['id'] ); ?>">
				<div class="one-half">
					<table class="ct_admin_table ct_review_manage_table">
						<tr>
							<th><h3><?php esc_html_e(  'Review Info', 'citytours' ) ?></h3></th>
						</tr>
						<tr>
							<th><?php esc_html_e(  'Select Post', 'citytours' ); ?></th>
							<td>
								<select name="post_id" id="post_id">
									<option></option>
									<?php
										$args = array(
												'post_type'         => ct_get_available_modules(),
												'posts_per_page'    => -1,
												'orderby'           => 'title',
												'order'             => 'ASC'
										);
										$hotel_query = new WP_Query( $args );

										if ( $hotel_query->have_posts() ) {
											while ( $hotel_query->have_posts() ) {
												$hotel_query->the_post();
												$selected = '';
												$id = $hotel_query->post->ID;
												if ( ( ! empty( $review_data['post_id'] ) ) && ( $review_data['post_id'] == $id ) ) $selected = ' selected ';
												echo '<option ' . esc_attr( $selected ) . 'value="' . esc_attr( $id ) .'">' . wp_kses_post( get_the_title( $id ) ) . '</option>';
											}
										}
										wp_reset_postdata();
									?>
								</select>
							</td>
						</tr>
						<tr>
							<th><?php esc_html_e(  'Reviewer Name', 'citytours' ); ?></th>
							<td><input type="text" name="reviewer_name" value="<?php echo esc_attr( $review_data['reviewer_name'] ); ?>"></td>
						</tr>
						<tr>
							<th><?php esc_html_e(  'Reviewer Email', 'citytours' ); ?></th>
							<td><input type="text" name="reviewer_email" value="<?php echo esc_attr( $review_data['reviewer_email'] ); ?>"></td>
						</tr>
						<tr>
							<th><?php esc_html_e(  'Reviewer IP', 'citytours' ); ?></th>
							<td><input type="text" name="reviewer_ip" value="<?php echo esc_attr( $review_data['reviewer_ip'] ); ?>"></td>
						</tr>
						<tr>
							<th><?php esc_html_e(  'Content', 'citytours' ); ?></th>
							<td><textarea name="review_text"><?php echo esc_textarea( stripslashes( $review_data['review_text'] ) ); ?></textarea></td>
						</tr>
						<tr>
							<th><?php esc_html_e(  'Review Status', 'citytours' ); ?></th>
							<td><select name="status">
								<?php
									$statuses = array(
											'pending' => esc_html__( 'Pending', 'citytours' ),
											'approved' => esc_html__( 'Approved', 'citytours' ),
											'trashed' => esc_html__( 'Trashed', 'citytours' ),
										);

									foreach ( $statuses as $val => $label ) {
										$selected = '';
										if ( $review_data['status'] == $val ) $selected = 'selected';
										echo '<option value="' . esc_attr( $val ) . '" ' . esc_attr( $selected ) . '>' . esc_html( $label ) . '</option>';
									}
								?>
							</select></td>
						</tr>
						<tr>
							<th><?php esc_html_e(  'Review Date', 'citytours' ); ?><br/>(example : 2015-02-20 03:24:16)</th>
							<td><input type="text" name="date" value="<?php echo esc_attr( $review_data['date'] ); ?>"></td>
						</tr>
						<tr>
							<th><?php esc_html_e(  'User ID', 'citytours' ); ?></th>
							<td><input type="text" name="user_id" value="<?php echo esc_attr( $review_data['user_id'] ); ?>"></td>
						</tr>
						<tr>
							<th><?php esc_html_e(  'Booking No', 'citytours' ); ?></th>
							<td><input type="text" name="booking_no" value="<?php echo esc_attr( $review_data['booking_no'] ); ?>"></td>
						</tr>
						<tr>
							<th><?php esc_html_e(  'Pin Code', 'citytours' ); ?></th>
							<td><input type="text" name="pin_code" value="<?php echo esc_attr( $review_data['pin_code'] ); ?>"></td>
						</tr>
					</table>
				</div>
				<div class="one-half">
					<h3><?php esc_html_e(  'Review Rating Details', 'citytours' ) ?></h3>
					<table class="ct_admin_table ct_review_rating_table" style="margin-bottom:20px;">
						<?php
							ct_get_review_rating_fields( $review_data['post_id'], $review_rating_detail );
						?>
					</table>
					<input type="submit" class="button-primary" name="save" value="<?php esc_html_e( 'Save Review', 'citytours' ) ?>">
					<a href="admin.php?page=reviews" class="button-secondary"><?php esc_html_e( 'Cancel', 'citytours' ) ?></a>
				</div>
				<?php wp_nonce_field('ct_review_manage','review_save'); ?>
			</form>
			<script>
				jQuery(document).ready(function($) {
					$('#post_id').select2({
						placeholder: "<?php esc_html_e(  'Select a Post', 'citytours' ) ?>",
						width: "250px"
					});
					$('#post_id').change(function(){
						$.ajax({
							url: ajaxurl,
							type: "POST",
							data: {
								'action': 'get_review_rating_fields',
								'post_id': $(this).val()
							},
							success: function(response){
								if ( response ) {
									$('.ct_review_rating_table').html(response);
								}
							}
						});
					});
				});
			</script>
		</div>
		<?php
	}
}

/*
 * review delete action
 */
if ( ! function_exists( 'ct_review_delete_action' ) ) {
	function ct_review_delete_action() {
		global $wpdb;

		$wpdb->delete( CT_REVIEWS_TABLE, array( 'id' => $_REQUEST['review_id'] ) );
		wp_redirect( admin_url( 'admin.php?page=reviews') );
		exit;
	}
}

/*
 * review change status action
 */
if ( ! function_exists( 'ct_review_change_status_action' ) ) {
	function ct_review_change_status_action( $status = 'pending' ) {
		global $wpdb;

		$wpdb->update( CT_REVIEWS_TABLE, array( 'status' => $status ), array( 'id' => $_REQUEST['review_id'] ) );

		$sql = 'SELECT CT_Reviews.post_id FROM ' . CT_REVIEWS_TABLE . ' AS CT_Reviews WHERE id="' . esc_sql( $_REQUEST['review_id'] ) . '"';
		$post_id = $wpdb->get_var( $sql );
		ct_review_calculate_rating( $post_id );

		wp_redirect( $_SERVER[HTTP_REFERER] );
	}
}

/*
 * review calculate hotel rating action and update it
 */
if ( ! function_exists( 'ct_review_calculate_rating' ) ) {
	function ct_review_calculate_rating( $post_id ) {
		//recalculate hotel rating
		global $wpdb, $ct_options;

		$sql = 'SELECT review_rating, review_rating_detail FROM ' . CT_REVIEWS_TABLE . ' WHERE status="approved" AND post_id="' . esc_sql( $post_id ) . '"';
		$review_datas = $wpdb->get_results( $sql, ARRAY_A );

		$post_type = get_post_type( $post_id );
		$review_fields = array();
		if ( 'hotel' == $post_type ) {
			$review_fields = ( ! empty( $ct_options['hotel_review_fields'] ) ) ? explode( ",", $ct_options['hotel_review_fields'] ) : array( "Position", "Comfort", "Price", "Quality" );
		} elseif ( 'tour' == $post_type ) {
			$review_fields = ( ! empty( $ct_options['tour_review_fields'] ) ) ? explode( ",", $ct_options['tour_review_fields'] ) : array( "Position", "Tourist guide", "Price", "Quality" );
		}

		$rating_detail = array();
		$count_review = count( $review_datas ); 
		$rating = 0;

		if ( ! empty( $review_datas ) ) {
			foreach ( $review_datas as $review_data ) {
				$review_rating_detail = unserialize( $review_data['review_rating_detail'] );
				for( $i = 0; $i < count( $review_fields ); $i++ ) {
					if ( ! isset( $rating_detail[ $i ] ) ) $rating_detail[ $i ] = 0;
					$rating_detail[$i] += (float)$review_rating_detail[$i];
				}
			}

			for( $i = 0; $i < count( $review_fields ); $i++ ) {
				$rating_detail[$i] = round( $rating_detail[$i] / $count_review, 1 );
				$rating += $rating_detail[$i];
			}
		}

		$rating = round( $rating / count( $review_fields ), 1 );

		update_post_meta( $post_id, '_review', $rating );
		update_post_meta( $post_id, '_review_detail', $rating_detail );
	}
}

/*
 * reveiw save action
 */
if ( ! function_exists( 'ct_review_save_action' ) ) {
	function ct_review_save_action() {

		if ( ! isset( $_POST['review_save'] ) || ! wp_verify_nonce( $_POST['review_save'], 'ct_review_manage' ) ) {
			print 'Sorry, your nonce did not verify.';
			exit;
		} else {
			global $wpdb;

			$default_review_data = array(
				'post_id'  => '',
				'review_rating' => 0,
				'review_rating_detail' => '',
				'review_text'   => '',
				'reviewer_ip'   => '127.0.0.1',
				'reviewer_email' => '',
				'reviewer_name' => '',
				'status'        => 'pending',
				'date'        => date( 'Y-m-d H:i:s' ),
				'user_id' => '',
				'booking_no' => '',
				'pin_code' => '',
			);

			$table_fields = array( 'reviewer_name', 'reviewer_email', 'reviewer_ip', 'review_text', 'post_id', 'status', 'date', 'user_id', 'booking_no', 'pin_code' );
			//review_rating, review_rating_detail, date
			$data = array();
			foreach ( $table_fields as $table_field ) {
				if ( ! empty( $_POST[ $table_field ] ) ) {
					$data[ $table_field ] = sanitize_text_field( $_POST[ $table_field ] );
				}
			}

			$data['review_rating_detail'] = serialize( $_POST['review_rating_detail'] );
			$data['review_rating'] = round( array_sum( $_POST['review_rating_detail'] ) / count( $_POST['review_rating_detail'] ), 1 );
			$data = array_replace( $default_review_data, $data );

			$data['post_id'] = ct_post_org_id( $data['post_id'] );
			if ( empty( $_POST['id'] ) ) {
				//insert
				$wpdb->insert( CT_REVIEWS_TABLE, $data );
				$id = $wpdb->insert_id;
			} else {
				//update
				$wpdb->update( CT_REVIEWS_TABLE, $data, array( 'id' => sanitize_text_field( $_POST['id'] ) ) );
				$id = sanitize_text_field( $_POST['id'] );
			}

			ct_review_calculate_rating( $data['post_id'] );

			wp_redirect( admin_url( 'admin.php?page=reviews&action=edit&review_id=' . $id . '&updated=true') );
			exit;
		}
	}
}

/*
 * reveiw admin enqueue script action
 */
if ( ! function_exists( 'ct_review_admin_enqueue_scripts' ) ) {
	function ct_review_admin_enqueue_scripts() {

		// support select2
		wp_enqueue_style( 'rwmb_select2', RWMB_URL . 'css/select2/select2.css', array(), '3.2' );
		wp_enqueue_script( 'rwmb_select2', RWMB_URL . 'js/select2/select2.min.js', array(), '3.2', true );

		// custom style and js
		// wp_enqueue_style( 'ct_admin_hotel_style' , CT_TEMPLATE_DIRECTORY_URI . '/css/admin/admin.css' ); 
	}
}

/*
 * handle ajax request to get reveiw rating fields
 */
if ( ! function_exists( 'ct_ajax_get_review_rating_fields' ) ) {
	function ct_ajax_get_review_rating_fields() {
		$post_id = $_POST['post_id'];
		ct_get_review_rating_fields( $post_id );
	}
}

/*
 * get reveiw rating fields
 */
if ( ! function_exists( 'ct_get_review_rating_fields' ) ) {
	function ct_get_review_rating_fields( $post_id = '', $default_rating = array() ) {
		global $ct_options;

		$post_type = '';
		if ( ! empty( $post_id ) ) {
			$post_type = get_post_type( $post_id );
		} else {
			$available_modules = ct_get_available_modules();
			$post_type = empty( $available_modules ) ? '' : $available_modules[0];
		}

		$review_fields = array();
		if ( 'hotel' == $post_type ) {
			$review_fields = ( ! empty( $ct_options['hotel_review_fields'] ) ) ? explode( ",", $ct_options['hotel_review_fields'] ) : array( "Position", "Comfort", "Price", "Quality" );
		} elseif ( 'tour' == $post_type ) {
			$review_fields = ( ! empty( $ct_options['tour_review_fields'] ) ) ? explode( ",", $ct_options['tour_review_fields'] ) : array( "Position", "Tourist guide", "Price", "Quality" );
		}

		$i = 0;
		foreach ( $review_fields as $review_field ) {
			?>

			<tr>
				<th><?php esc_html_e( $review_field, 'citytours' ) ?></th>
				<td><input type="number" name="review_rating_detail[<?php echo esc_attr( $i ) ?>]" min="1" max="5" value="<?php echo esc_attr( isset( $default_rating[$i] ) ? $default_rating[$i] : 5 ) ?>"></td>
			</tr>

			<?php
			$i++;
		}
	}
}

add_action( 'admin_menu', 'ct_review_add_menu_items' );