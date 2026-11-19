<?php
if (!defined('ABSPATH'))
    exit; // Exit if accessed directly

if (!class_exists('WP_List_Table'))
{
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

/**
 * functions to manage order
 */
if (!class_exists('CT_Tour_Order_List_Table')) :

    class CT_Tour_Order_List_Table extends WP_List_Table
    {

        function __construct()
        {
            global $status, $page;
            parent::__construct(array(
                'singular' => '_order', //singular name of the listed records
                'plural' => 'tour_orders', //plural name of the listed records
                'ajax' => false        //does this table support ajax?
            ));
        }

        function column_default($item, $column_name)
        {
            switch ($column_name)
            {
                case 'id':
                case 'created':
                case 'total_price':
                    return $item[$column_name];
                default:
                    return print_r($item, true); //Show the whole array for troubleshooting purposes
            }
        }

        
        function column_operator($item)
        {
            return esc_html($item['operatorName']);
        }

        function column_date($item)
        {
            if (empty($item['date_from']) || '0000-00-00' == $item['date_from'])
                return '';
            return $item['date_from'];
        }

        function column_customer_name($item)
        {
            //Build row actions
            $link_pattern = 'edit.php?post_type=%1s&page=%2$s&action=%3$s&order_id=%4$s';
            $actions = array(
                'edit' => '<a href="' . esc_url(sprintf($link_pattern, sanitize_text_field($_REQUEST['post_type']), 'tour_orders', 'edit', $item['id'])) . '">Edit</a>',
                'delete' => '<a href="' . esc_url(sprintf($link_pattern, sanitize_text_field($_REQUEST['post_type']), 'tour_orders', 'delete', $item['id'] . '&_wpnonce=' . wp_create_nonce('order_delete'))) . '">Delete</a>',
            );
            $content = '<a href="' . esc_url(sprintf($link_pattern, sanitize_text_field($_REQUEST['post_type']), 'tour_orders', 'edit', $item['id'])) . '">' . esc_html($item['first_name'] . ' ' . $item['last_name']) . '</a>';
            //Return the title contents
            return sprintf('%1$s %2$s', $content, $this->row_actions($actions));
        }

        function column_tour_name($item)
        {
            return '<a href="' . esc_url(get_edit_post_link($item['post_id'])) . '">' . esc_html($item['tour_name']) . '</a>';
        }

        function column_status($item)
        {
            switch ($item['status']) {
                case 'pending':
					return '<h3><span class="label label-warning">'.esc_html__('Pending', 'citytours').'</span></h3>';
                case 'new':
					return '<h3><span class="label label-primary">'.esc_html__('New', 'citytours').'</span></h3>';
                case 'confirmed':
                    return '<h3><span class="label label-success">'.esc_html__('Confirmed', 'citytours').'</span></h3>';
                case 'cancelled':
                    return '<h3><span class="label label-danger">'.esc_html__('Cancelled', 'citytours').'</span></h3>';
            }
            return $item['status'];
        }

        function column_cb($item)
        {
            return sprintf('<input type="checkbox" name="%1$s[]" value="%2$s" />', $this->_args['singular'], $item['id']);
        }

        function get_columns()
        {
            $columns = array(
                'cb' => '<input type="checkbox" />', //Render a checkbox instead of text
                'id' => esc_html__('ID', 'citytours'),
                'customer_name' => esc_html__('Customer Name', 'citytours'),
                'date' => esc_html__('Date', 'citytours'),
                'tour_name' => esc_html__('Tour Name', 'citytours'),
                'total_price' => esc_html__('Price', 'citytours'),
                'created' => esc_html__('Created Date', 'citytours'),
                'operator' => esc_html__('Operator', 'citytours'),
                'status' => esc_html__('Status', 'citytours'),
            );
            return $columns;
        }

        function get_sortable_columns()
        {
            $sortable_columns = array(
                'id' => array('id', false),
                'date' => array('date', false),
                'tour_name' => array('tour_name', false),
                'operator' => array('operator', false),
                'status' => array('status', false),
            );
            return $sortable_columns;
        }

        function get_bulk_actions()
        {
            $actions = array(
                'bulk_delete' => 'Delete',
                'bulk_mark_new' => 'Mark as New',
                'bulk_mark_confirmed' => 'Mark as Confirmed',
                'bulk_mark_cancelled' => 'Mark as Cancelled',
            );
            return $actions;
        }

        function process_bulk_action()
        {
            global $wpdb;
            //Detect when a bulk action is being triggered...

            if (isset($_POST['_wpnonce']) && !empty($_POST['_wpnonce']))
            {

                $nonce = filter_input(INPUT_POST, '_wpnonce', FILTER_SANITIZE_STRING);
                $action = 'bulk-' . $this->_args['plural'];

                if (!wp_verify_nonce($nonce, $action))
                    wp_die('Sorry, your nonce did not verify');
            }

            if ('bulk_delete' === $this->current_action())
            {
                $selected_ids = $_GET[$this->_args['singular']];
                $how_many = count($selected_ids);
                $placeholders = array_fill(0, $how_many, '%d');
                $format = implode(', ', $placeholders);

                $current_user_id = get_current_user_id();
                $post_table_name = esc_sql($wpdb->prefix . 'posts');
                $sql = '';

                if (current_user_can('ct_tour_order'))
                {
                    $sql = sprintf('DELETE ct_order, ct_bookings, ct_services FROM %1$s AS ct_order
				LEFT JOIN %2$s AS ct_bookings ON ct_order.id = ct_bookings.order_id
				LEFT JOIN %3$s AS ct_services ON ct_order.id = ct_services.order_id
				WHERE ct_order.id IN (%4$s)', CT_ORDER_TABLE, CT_TOUR_BOOKINGS_TABLE, CT_ADD_SERVICES_BOOKINGS_TABLE, "$format");
                } else
                {
                    $sql = sprintf('DELETE ct_order, ct_bookings, ct_services FROM %1$s AS ct_order
				LEFT JOIN %2$s AS ct_bookings ON ct_order.id = ct_bookings.order_id
				LEFT JOIN %3$s AS ct_services ON ct_order.id = ct_services.order_id
				INNER JOIN %5$s as tour ON ct_order.post_id=tour.ID
				WHERE ct_order.id IN (%4$s) AND tour.post_author = %6$d', CT_ORDER_TABLE, CT_TOUR_BOOKINGS_TABLE, CT_ADD_SERVICES_BOOKINGS_TABLE, "$format", $post_table_name, $current_user_id);
                }

                $wpdb->query($wpdb->prepare($sql, $selected_ids));
                // wp_redirect( admin_url( 'edit.php?post_type=tour&page=tour_orders&bulk_delete=true&items=' . $how_many) );
            } elseif ('bulk_mark_new' === $this->current_action() || 'bulk_mark_confirmed' === $this->current_action() || 'bulk_mark_cancelled' === $this->current_action())
            {
                $selected_ids = $_GET[$this->_args['singular']];
                $how_many = count($selected_ids);
                $placeholders = array_fill(0, $how_many, '%d');
                $format = implode(', ', $placeholders);
                $current_user_id = get_current_user_id();
                $post_table_name = esc_sql($wpdb->prefix . 'posts');
                $sql = '';
                switch ($this->current_action())
                {
                    case 'bulk_mark_new':
                        $status = 'new';
                        break;
                    case 'bulk_mark_confirmed':
                        $status = 'confirmed';
                        break;
                    case 'bulk_mark_cancelled':
                        $status = 'cancelled';
                        break;
                }
                if (current_user_can('ct_tour_order'))
                {
                    $sql = sprintf('UPDATE %1$s AS ct_order
				SET ct_order.status="%2$s"
				WHERE ct_order.id IN (%3$s)', CT_ORDER_TABLE, $status, "$format");
                } else
                {
                    $sql = sprintf('UPDATE %1$s AS ct_order
				INNER JOIN %4$s as tour ON ct_order.post_id=tour.ID
				SET ct_order.status="%2$s"
				WHERE ct_order.id IN (%3$s) AND tour.post_author = %5$d', CT_ORDER_TABLE, $status, "$format", $post_table_name, $current_user_id);
                }
                $wpdb->query($wpdb->prepare($sql, $selected_ids));
                wp_redirect(admin_url('edit.php?post_type=tour&page=tour_orders&bulk_update=true&items=' . $how_many));
            }
        }

        function prepare_items()
        {
            global $wpdb;

            $per_page = 10;
            $columns = $this->get_columns();
            $hidden = array();
            $sortable = $this->get_sortable_columns();

            $this->_column_headers = array($columns, $hidden, $sortable);
            $this->process_bulk_action();

            $orderby = (!empty($_REQUEST['orderby']) ) ? sanitize_sql_orderby($_REQUEST['orderby']) : 'id'; //If no sort, default to title
            $order = (!empty($_REQUEST['order']) ) ? sanitize_text_field($_REQUEST['order']) : 'desc'; //If no order, default to desc
            $current_page = $this->get_pagenum();
            $post_table_name = esc_sql($wpdb->prefix . 'posts');
			$user_table_name = esc_sql($wpdb->prefix . 'users');
			
            $where = "1=1";
            $where .= " AND CT_Orders.post_type='tour'";
            if (!empty($_REQUEST['post_id']))
                $where .= " AND CT_Orders.post_id = '" . esc_sql(ct_tour_org_id($_REQUEST['post_id'])) . "'";
            if (!empty($_REQUEST['date']))
                $where .= " AND CT_Orders.date_from = '" . esc_sql($_REQUEST['date']) . "'";
            if (!empty($_REQUEST['booking_no']))
                $where .= " AND CT_Orders.booking_no = '" . esc_sql($_REQUEST['booking_no']) . "'";
            if (isset($_REQUEST['status']))
                $where .= " AND CT_Orders.status = '" . esc_sql($_REQUEST['status']) . "'";
            if (!current_user_can('ct_tour_order'))
            {
                $where .= " AND tour.post_author = '" . get_current_user_id() . "' ";
            }
            if(!current_user_can('administrator') && current_user_can('operator')) {
                $where .= " AND CT_Orders.operator_id = '" . get_current_user_id() . "' ";
            }
			
			if (current_user_can('administrator') && !empty($_REQUEST['userid'])) {
                $where .= " AND CT_Orders.operator_id = '" . esc_sql($_REQUEST['userid']) . "' ";
            }
			
            $sql = sprintf('SELECT CT_Orders.*, tour.ID as post_id, tour.post_title as tour_name, operator.display_name AS operatorName FROM %1$s as CT_Orders
			INNER JOIN %2$s as tour ON CT_Orders.post_id=tour.ID
			LEFT JOIN %3$s AS operator ON CT_Orders.operator_id = operator.ID
			WHERE ' . $where . ' ORDER BY %4$s %5$s
			LIMIT %6$s, %7$s', CT_ORDER_TABLE, $post_table_name, $user_table_name, $orderby, $order, $per_page * ( $current_page - 1 ), $per_page);
            
            $data = $wpdb->get_results($sql, ARRAY_A);
            
            $sql = sprintf('SELECT COUNT(*) FROM %1$s as CT_Orders INNER JOIN %2$s as tour ON CT_Orders.post_id=tour.ID WHERE %3$s', CT_ORDER_TABLE, $post_table_name, $where);
            
            $total_items = $wpdb->get_var($sql);

            $this->items = $data;
            $this->set_pagination_args(array(
                'total_items' => $total_items, //WE have to calculate the total number of items
                'per_page' => $per_page, //WE have to determine how many items to show on a page
                'total_pages' => ceil($total_items / $per_page)   //WE have to calculate the total number of pages
            ));
        }

    }

    endif;

/*
 * add order list page to menu
 */
if (!function_exists('ct_tour_order_add_menu_items'))
{

    function ct_tour_order_add_menu_items()
    {
        //add tour orders list page
        $page = add_submenu_page('edit.php?post_type=tour', 'Tour Orders', 'Orders', 'ct_tour_order', 'tour_orders', 'ct_tour_order_render_pages');
        add_action('admin_print_scripts-' . $page, 'ct_tour_order_admin_enqueue_scripts');
    }

}

/*
 * order admin main actions
 */
if (!function_exists('ct_tour_order_render_pages'))
{

    function ct_tour_order_render_pages()
    {
        if ((!empty($_REQUEST['action']) ) && ( ( 'add' == $_REQUEST['action'] ) || ( 'edit' == $_REQUEST['action'] ) ))
        {
            ct_tour_order_render_manage_page();
        } elseif ((!empty($_REQUEST['action']) ) && ( 'delete' == $_REQUEST['action'] ))
        {
            ct_tour_order_delete_action();
        } else
        {
            ct_tour_order_render_list_page();
        }
    }

}

/*
 * render order list page
 */
if (!function_exists('ct_tour_order_render_list_page'))
{

    function ct_tour_order_render_list_page()
    {
        global $wpdb;

        $ctOrderTable = new CT_Tour_Order_List_Table();
        $ctOrderTable->prepare_items();
        ?>

        <div class="wrap">

            <h2>Tour Orders <a href="edit.php?post_type=tour&amp;page=tour_orders&amp;action=add" class="add-new-h2">Add New</a></h2>

                <?php
                if (isset($_REQUEST['bulk_delete']) && isset($_REQUEST['items']))
                {
                    echo '<div id="message" class="updated below-h2"><p>' . esc_html(sprintf(esc_html__('%d orders deleted', 'citytours'), $_REQUEST['items'])) . '</p></div>';
                }

                if (isset($_REQUEST['bulk_update']) && isset($_REQUEST['items']))
                {
                    echo '<div id="message" class="updated below-h2"><p>' . esc_html(sprintf(esc_html__('%d orders updated', 'citytours'), $_REQUEST['items'])) . '</p></div>';
                }
                ?>

            <select id="tour_filter">
                <option></option>
        <?php
        $args = array(
            'post_type' => 'tour',
            'posts_per_page' => -1,
            'orderby' => 'title',
            'order' => 'ASC'
        );
        if (!current_user_can('manage_options') ) {
            $args['author'] = get_current_user_id();
        }
//        if (current_user_has_role('operator') ) {
//            $args['operator_id'] = get_current_user_id();
//        }
        
        $tour_query = new WP_Query($args);

        if ($tour_query->have_posts())
        {
            while ($tour_query->have_posts())
            {
                $tour_query->the_post();

                $selected = '';
                $id = $tour_query->post->ID;
                if (!empty($_REQUEST['post_id']) && ( $_REQUEST['post_id'] == $id ))
                {
                    $selected = ' selected ';
                }

                echo '<option ' . esc_attr($selected) . 'value="' . esc_attr($id) . '">' . wp_kses_post(get_the_title($id)) . '</option>';
            }
        } else
        {
            // no posts found
        }
        /* Restore original Post Data */
        wp_reset_postdata();
        ?>
            </select>
            <input type="text" id="date_filter" name="date" placeholder="<?php echo esc_html__('Filter by Date', 'citytours') ?>" value="<?php if (!empty($_REQUEST['date'])) echo esc_attr($_REQUEST['date']); ?>">
            <input type="text" id="booking_no_filter" name="booking_no" placeholder="<?php echo esc_html__('Filter by Booking No', 'citytours') ?>" value="<?php if (!empty($_REQUEST['booking_no'])) echo esc_attr($_REQUEST['booking_no']); ?>">
            <select name="status" id="status_filter">
                <option value=""><?php echo esc_html__('Select Status', 'citytours') ?></option>
        <?php
        $statuses = array(
            'new' => esc_html__('New', 'citytours'),
            'confirmed' => esc_html__('Confirmed', 'citytours'),
            'cancelled' => esc_html__('Cancelled', 'citytours'),
            'pending' => esc_html__('Pending', 'citytours')
        );

        foreach ($statuses as $key => $status)
        {
            ?>
                    <option value="<?php echo esc_attr($key) ?>" <?php selected($key, isset($_REQUEST['status']) ? esc_attr($_REQUEST['status']) : '' ); ?>>
            <?php echo esc_attr($status) ?>
                    </option>
            <?php
        }
        ?>
            </select>
			<?php 
            if(current_user_can('administrator')) { 
                $args = array(
//                    'role' => 'operator',
                    'orderby' => 'user_nicename',
                    'order' => 'ASC'
                );

                $users = get_users($args);
                ?>
			<select id="user_filter" name="userid">
			<option></option>
			<?php
			foreach ( $users as $user ) {
					$selected = '';
					if (!empty($_REQUEST['userid']) && ( $_REQUEST['userid'] == $user->ID ))
						$selected = ' selected ';
					echo '<option ' . esc_attr($selected) . 'value="' . esc_attr($user->ID) . '">' . esc_html( $user->display_name ) . '</option>';
				}
			?>
			</select>
			<?php } ?>
            <input type="button" name="order_filter" id="tour-order-filter" class="button" value="Filter">
            <a href="edit.php?post_type=tour&amp;page=tour_orders" class="button-secondary"><?php echo esc_html__('Show All', 'citytours') ?></a>

            <form id="tours-orders-filter" method="get">
                <input type="hidden" name="post_type" value="<?php echo esc_attr($_REQUEST['post_type']) ?>" />
                <input type="hidden" name="page" value="<?php echo esc_attr($_REQUEST['page']) ?>" />
        <?php $ctOrderTable->display() ?>
            </form>

        </div>
        <style>#date_filter, #date_to_filter {width:150px;}</style>
        <?php
    }

}

/*
 * render order detail page
 */
if (!function_exists('ct_tour_order_render_manage_page'))
{

    function ct_tour_order_render_manage_page()
    {
        global $wpdb, $ct_options;

        if (!empty($_POST['save']))
        {
            ct_tour_order_save_action();
            return;
        }

        $order_data = array();
        $tour_data = array();
        $service_data = array();

        if ('edit' == $_REQUEST['action'])
        {
            
            if (empty($_REQUEST['order_id']))
            {
                echo "<h2>" . esc_html__("You attempted to edit an item that doesn't exist. Perhaps it was deleted?", "ct") . "</h2>";
                return;
            }

            $order_id = $_REQUEST['order_id'];
            $post_table_name = $wpdb->prefix . 'posts';

            $order = new CT_Hotel_Order($order_id);
            $order_data = $order->get_order_info();
            $tour_data = $order->get_tours();
            $service_data = $order->get_services();

            if (empty($order_data))
            {
                echo "<h2>" . esc_html__("You attempted to edit an item that doesn't exist. Perhaps it was deleted?", "ct") . "</h2>";
                return;
            }
            
            if(!current_user_can('administrator') && !current_user_can('operator')) {
                echo "<h2>" . esc_html__("You don't have permission to edit this item", "ct") . "</h2>";
                return;
            }
            
            if(current_user_can('operator') && $order_data['operator_id'] != get_current_user_id()) {
                echo "<h2>" . esc_html__("You don't have permission to edit this item", "ct") . "</h2>";
                return;
            }
        }

        $default_order_data = ct_order_default_order_data();
        $order_data = array_replace($default_order_data, $order_data);
        $site_currency_symbol = ct_get_site_currency_symbol();
        ?>

        <div class="wrap">
                                        <?php $page_title = ( 'edit' == $_REQUEST['action'] ) ? 'Edit Tour Order<a href="edit.php?post_type=tour&amp;page=tour_orders&amp;action=add" class="add-new-h2">Add New</a>' : 'Add New Tour Order'; ?>

            <h2><?php echo wp_kses_post($page_title); ?></h2>

        <?php if (isset($_REQUEST['updated'])) echo '<div id="message" class="updated below-h2"><p>Order saved</p></div>' ?>

            <form method="post" id="order-form" class="tour-order-form" onsubmit="return manage_order_validateForm();" data-message="<?php echo esc_attr(esc_html__('Please select a tour', 'citytours')) ?>">

                <input type="hidden" name="id" value="<?php echo esc_attr($order_data['id']); ?>">

                <div class="row postbox">
                    <div class="one-half">
                        <h3><?php echo esc_html__('Order Detail', 'citytours') ?></h3>

                        <table class="ct_admin_table ct_order_manage_table">
                            <tr>
                                <th><?php echo esc_html__('Tour', 'citytours') ?></th>
                                <td>
                                    <select name="post_id" id="post_id">
                                        <option></option>
                            <?php
                            $args = array(
                                'post_type' => 'tour',
                                'posts_per_page' => -1,
                                'orderby' => 'title',
                                'order' => 'ASC'
                            );
                            if (!current_user_can('ct_tour_order'))
                            {
                                $args['author'] = get_current_user_id();
                            }
                            $tour_query = new WP_Query($args);

                            if ($tour_query->have_posts())
                            {
                                while ($tour_query->have_posts())
                                {
                                    $tour_query->the_post();
                                    $selected = '';
                                    $id = $tour_query->post->ID;
                                    if ($order_data['post_id'] == $id)
                                        $selected = ' selected ';
                                    echo '<option ' . esc_attr($selected) . 'value="' . esc_attr($id) . '">' . wp_kses_post(get_the_title($id)) . '</option>';
                                }
                            }
                            wp_reset_postdata();
                            ?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th><?php echo esc_html__('Date', 'citytours') ?></th>
                                <td><input type="text" name="date_from" id="date" value="<?php echo esc_attr($order_data['date_from']) ?>"></td>
                            </tr>
                            <tr>
                                <th><?php echo esc_html__('Total Adults', 'citytours') ?></th>
                                <td><input type="number" name="total_adults" value="<?php echo esc_attr($order_data['total_adults']) ?>"></td>
                            </tr>
                            <tr>
                                <th><?php echo esc_html__('Total Children', 'citytours') ?></th>
                                <td><input type="number" name="total_kids" value="<?php echo esc_attr($order_data['total_kids']) ?>"></td>
                            </tr>
                            <tr>
                                <th><?php echo esc_html__('Total Price (including Service)', 'citytours') ?></th>
                                <td><input type="text" name="total_price" value="<?php echo esc_attr($order_data['total_price']) ?>"> <?php echo esc_html($site_currency_symbol) ?></td>
                            </tr>
        <?php if (ct_is_multi_currency())
        { ?>
                                <tr>
                                    <th><?php echo esc_html__('User Currency', 'citytours') ?></th>
                                    <td>
                                        <select name="currency_code">
            <?php foreach (array_filter($ct_options['site_currencies']) as $key => $content)
            { ?>
                                                <option value="<?php echo esc_attr($key) ?>" <?php selected($key, $order_data['currency_code']); ?>><?php echo esc_html($key) ?></option>
            <?php } ?>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <th><?php echo esc_html__('Exchange Rate', 'citytours') ?></th>
                                    <td><input type="text" name="exchange_rate" value="<?php echo esc_attr($order_data['exchange_rate']) ?>"></td>
                                </tr>
                                <tr>
                                    <th><?php echo esc_html__('Total Price in User Currency', 'citytours') ?></th>
                                    <td><label> <?php if (!empty($order_data['total_price']) && !empty($order_data['exchange_rate'])) echo esc_html($order_data['total_price'] * $order_data['exchange_rate']) . esc_html(ct_get_currency_symbol($order_data['currency_code'])) ?></td>
                                </tr>
        <?php } ?>
                            <tr>
                                <th><?php echo esc_html__('Deposit Amount', 'citytours') ?></th>
                                <td><input type="text" name="deposit_price" value="<?php echo esc_attr($order_data['deposit_price']) ?>"> <?php echo esc_html(ct_get_currency_symbol($order_data['currency_code'])) ?></td>
                            </tr>
        <?php if ('add' == $_REQUEST['action'] || (!empty($order_data['deposit_price']) && !( $order_data['deposit_price'] == 0 ) ))
        { ?>
                                <tr>
                                    <th><?php echo esc_html__('Deposit Paid', 'citytours') ?></th>
                                    <td>
                                        <select name="deposit_paid">
            <?php
            $deposit_paid = array(
                '1' => esc_html__('Yes', 'citytours'),
                '0' => esc_html__('No', 'citytours')
            );
            ?>
            <?php foreach ($deposit_paid as $key => $content)
            { ?>
                                                <option value="<?php echo esc_attr($key) ?>" <?php selected($key, $order_data['deposit_paid']); ?>><?php echo esc_html($content) ?></option>
            <?php } ?>
                                        </select>
                                    </td>
                                </tr>
            <?php
            if (!empty($order_data['deposit_paid']))
            {
                $other_data = $order_data['other'];
                if (!empty($other_data['pp_transaction_id']))
                {
                    ?>
                                        <tr>
                                            <th><?php echo esc_html__('Paypal Payment Transaction ID', 'citytours') ?></th>
                                            <td><label><?php echo esc_html($other_data['pp_transaction_id']) ?></label></td>
                                        </tr>
                <?php }
            } ?>
        <?php } else
        { ?>
                                <input type="hidden" name="deposit_paid" value="1">
        <?php } ?>
                            <tr>
                                <th><?php echo esc_html__('Status', 'citytours') ?></th>
                                <td>
                                    <select name="status">
        <?php
        $statuses = array('new' => esc_html__('New', 'citytours'), 'confirmed' => esc_html__('Confirmed', 'citytours'), 'cancelled' => esc_html__('Cancelled', 'citytours'), 'pending' => esc_html__('Pending', 'citytours'));
        if (!isset($order_data['status']))
        {
            $order_data['status'] = 'new';
        }
        ?>
        <?php foreach ($statuses as $key => $content)
        { ?>
                                            <option value="<?php echo esc_attr($key) ?>" <?php selected($key, $order_data['status']); ?>><?php echo esc_html($content) ?></option>
        <?php } ?>
                                    </select>
                                </td>
                            </tr>
                                <?php
                                if (!empty($order_data['other'])) :
                                    $edit_url = esc_url(sprintf('post.php?post=%d&action=edit', $order_data['other']));
                                    ?>
                                <tr>
                                    <th colspan="2" style="padding-top:15px;">
                                        <a href="<?php echo $edit_url ?>"><?php _e('Related WooCommerce Order', 'citytours') ?></a>
                                    </th>
                                </tr>
        <?php endif; ?>
                                
                                <tr>
                                    <th><?php echo esc_html__('Payment way', 'citytours') ?></th>
                                    <td>
                                        <input type="hidden" name="payment_method" value="<?php echo esc_attr($order_data['payment_method']) ?>">
                                        <?php if($order_data['payment_method'] == 'bank_transfer') : ?>
                                        <input disabled="off" type="text" name="payment_method" value="<?php echo esc_attr('الدفع عن طريق تحوبل بنكي') ?>">
                                        <?php elseif($order_data['payment_method'] == 'cash_office') : ?>
                                        <input disabled="off" type="text" name="payment_method" value="<?php echo esc_attr('الدفع كاش عن طريق المكتب في القاهرة') ?>">
                                        <?php elseif($order_data['payment_method'] == 'cash_delegate') : ?>
                                        <input disabled="off" type="text" name="payment_method" value="<?php echo esc_attr('الدفع كاش عن طريق أحد مندوبينا') ?>">
                                        <?php elseif($order_data['payment_method'] == 'visa_card') : ?>
                                        <input disabled="off" type="text" name="payment_method" value="<?php echo esc_attr('الدفع ببطافة الإئتمان') ?>">
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                
                                <tr>
                                <th><?php echo esc_html__('Assign Operator', 'citytours') ?></th>
                                <td>
                                    <select name="operator_id" id="operator_id">
                                    <option></option>
                                    <?php
                                    $users = get_users();
                                    foreach ($users as $user) {
                                        $selected = '';
                                        if ($user->id == $order_data['operator_id'])
                                                $selected = ' selected ';

                                        echo '<option' . esc_attr($selected) . ' value="' . esc_attr($user->id) . '">' . $user->display_name . '</option>';
                                    }
                                    ?>
                                    </select>
                                </td>
                            </tr>
                            
                        </table>
                    </div>

                    <div class="one-half">
                        <h3><?php echo esc_html__('Customer Infomation', 'citytours') ?></h3>
                        <table  class="ct_admin_table ct_order_manage_table">
                            <tr>
                                <th><?php echo esc_html__('First Name', 'citytours') ?></th>
                                <td><input type="text" name="first_name" value="<?php echo esc_attr($order_data['first_name']) ?>"></td>
                            </tr>
                            <tr>
                                <th><?php echo esc_html__('Last Name', 'citytours') ?></th>
                                <td><input type="text" name="last_name" value="<?php echo esc_attr($order_data['last_name']) ?>"></td>
                            </tr>
                            <tr>
                                <th><?php echo esc_html__('Email', 'citytours') ?></th>
                                <td><input type="email" name="email" value="<?php echo esc_attr($order_data['email']) ?>"></td>
                            </tr>
                            <tr>
                                <th><?php echo esc_html__('Phone', 'citytours') ?></th>
                                <td><input type="text" name="phone" value="<?php echo esc_attr($order_data['phone']) ?>"></td>
                            </tr>
                            <tr>
                                <th><?php echo esc_html__('Street line 1', 'citytours') ?></th>
                                <td><input type="text" name="address1" value="<?php echo esc_attr($order_data['address1']) ?>"></td>
                            </tr>
                            <tr>
                                <th><?php echo esc_html__('Street line 2', 'citytours') ?></th>
                                <td><input type="text" name="address2" value="<?php echo esc_attr($order_data['address2']) ?>"></td>
                            </tr>
                            <tr>
                                <th><?php echo esc_html__('City', 'citytours') ?></th>
                                <td><input type="text" name="city" value="<?php echo esc_attr($order_data['city']) ?>"></td>
                            </tr>
                            <tr>
                                <th><?php echo esc_html__('State', 'citytours') ?></th>
                                <td><input type="text" name="state" value="<?php echo esc_attr($order_data['state']) ?>"></td>
                            </tr>
                            <tr>
                                <th><?php echo esc_html__('Postal Code', 'citytours') ?></th>
                                <td><input type="text" name="zip" value="<?php echo esc_attr($order_data['zip']) ?>"></td>
                            </tr>
                            <tr>
                                <th><?php echo esc_html__('Country', 'citytours') ?></th>
                                <td><input type="text" name="country" value="<?php echo esc_attr($order_data['country']) ?>"></td>
                            </tr>
                            <tr>
                                <th><?php echo esc_html__('Special Requirements', 'citytours') ?></th>
                                <td><textarea name="special_requirements"><?php echo esc_textarea(stripslashes($order_data['special_requirements'])) ?></textarea></td>
                            </tr>
                            <tr>
                                <th><?php echo esc_html__('Booking No', 'citytours') ?></th>
                                <td><input type="text" name="booking_no" value="<?php echo esc_attr($order_data['booking_no']) ?>"></td>
                            </tr>
                            <tr>
                                <th><?php echo esc_html__('Pin Code', 'citytours') ?></th>
                                <td><input type="text" name="pin_code" value="<?php echo esc_attr($order_data['pin_code']) ?>"></td>
                            </tr>
                        </table>
                    </div>
                </div>

                <input type="hidden" name="tour_booking_id" value="<?php echo esc_attr(( empty($tour_data) || empty($tour_data['id']) ) ? '' : $tour_data['id'] ); ?>">
                
                <div class="row postbox ct-order-rooms">
                    <h3><?php echo esc_html__('Order Tour Detail', 'citytours') ?></h3>
                    <?php 
                        $details = json_decode($tour_data['details'], TRUE);
                    ?>
                    <div class="rooms-wrapper" style="line-height:30px;">
                        <table class="rooms-table">
                            <thead class="clone-wrapper">
                                <tr class="clone-field">
                                    <th style="text-align:left"><?php echo esc_html__('Room Type', 'citytours') ?></th>
                                    <th>C o u n t </th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($details['adults'] as $key=>$value) : ?>
                                    <?php if($key == 'person' && $value >= 1 ) : ?>
                                    <tr class="clone-field">
                                        <td style="text-align:left"><strong> شخص واحد </strong></td>
                                        <td style="text-align:right"><?php echo esc_html( $value ) ?></td>
                                    </tr>
                                    <?php endif; ?>
                                    <?php if($key == 'double' && $value >= 1) : ?>
                                    <tr class="clone-field">
                                        <td style="text-align:left"><strong> غرفة مزدوجة </strong></td>
                                        <td style="text-align:right"><?php echo esc_html( $value ) ?></td>
                                    </tr>
                                    <?php endif; ?>
                                    <?php if($key == 'triple' && $value >= 1) : ?>
                                    <tr class="clone-field">
                                        <td style="text-align:left"><strong> غرفة ثلاثية  </strong></td>
                                        <td style="text-align:right"><?php echo esc_html( $value ) ?></td>
                                    </tr>
                                    <?php endif; ?>
                                    <?php if($key == 'single' && $value >= 1) : ?>
                                    <tr class="clone-field">
                                        <td style="text-align:left"><strong> غرفة منفردة  </strong></td>
                                        <td style="text-align:right"><?php echo esc_html( $value ) ?></td>
                                    </tr>
                                    <?php endif; ?>
                                    <?php if($key == 'quad' && $value >= 1) : ?>
                                    <tr class="clone-field">
                                        <td style="text-align:left"><strong> غرفة رباعية  </strong></td>
                                        <td style="text-align:right"><?php echo esc_html( $value ) ?></td>
                                    </tr>
                                    <?php endif; ?>
                                    <?php endforeach; ?>
                                    <?php foreach($details['kids'] as $key=>$value) : ?>
                                    <?php if($key == 0 && $value >= 1 ) : ?>
                                    <tr class="clone-field">
                                        <td style="text-align:left"><strong>طفل واحد</strong></td>
                                        <td style="text-align:right"><?php echo esc_html( $value ) ?></td>
                                    </tr>
                                    <?php endif; ?>
                                    <?php if($key == 2 && $value >= 1) : ?>
                                    <tr class="clone-field">
                                        <td style="text-align:left"><strong>طفل اقل من سنتين</strong></td>
                                        <td style="text-align:right"><?php echo esc_html( $value ) ?></td>
                                    </tr>
                                    <?php endif; ?>
                                    <?php if($key == 6 && $value >= 1) : ?>
                                    <tr class="clone-field">
                                        <td style="text-align:left"><strong>طفل من 2 الى 6 سنوات</strong></td>
                                        <td style="text-align:right"><?php echo esc_html( $value ) ?></td>
                                    </tr>
                                    <?php endif; ?>
                                    <?php if($key == 11 && $value >= 1) : ?>
                                    <tr class="clone-field">
                                        <td style="text-align:left"><strong>طفل من 6 الى 11 سنوات</strong></td>
                                        <td style="text-align:right"><?php echo esc_html( $value ) ?></td>
                                    </tr>
                                    <?php endif; ?>

                                    <?php endforeach; ?>
                            </tbody>
                        </table>
                    
                    </div>
                </div>
                
                <div class="row postbox ct-order-services">
                    <h3><?php echo esc_html__('Order Services Detail', 'citytours') ?></h3>
                    <div class="services-wrapper">
                        <table class="services-table"><tbody class="clone-wrapper">
                                <tr class="rwmb-field">
                                    <th><?php echo esc_html__('Title', 'citytours') ?></th>
                                    <th><?php echo esc_html__('Qty', 'citytours') ?></th>
                                    <th><?php echo esc_html__('Total Price', 'citytours') ?></th>
                                    <th>&nbsp;</th>
                                </tr>

        <?php if (empty($service_data))
        { ?>
                                    <tr class="clone-field">
                                        <td>
                                            <input type="hidden" name="service_booking_id[0]" value="">
                                            <select name="service_id[0]" class="service_id_select">
            <?php
            echo ct_get_service_list($order_data['post_id']);
            ?>
                                            </select>
                                        </td>
                                        <td><input type="text" name="service_qty[0]"></td>
                                        <td><input type="text" name="service_price[0]"></td>
                                        <td><a href="#" class="rwmb-button button remove-clone" style="display: none;">-</a></td>
                                    </tr>
        <?php } else
        { ?>
            <?php foreach ($service_data as $key => $service) : ?>
                                        <tr class="clone-field">
                                            <td>
                                                <input type="hidden" name="service_booking_id[<?php echo esc_attr($key) ?>]" value="<?php echo esc_attr($service['id']) ?>">
                                                <select name="service_id[<?php echo esc_attr($key) ?>]" class="service_id_select">
                <?php
                echo ct_get_service_list($order_data['post_id'], $service['add_service_id']);
                ?>
                                                </select>
                                            </td>
                                            <td><input type="text" name="service_qty[<?php echo esc_attr($key) ?>]" value="<?php echo esc_attr($service['qty']) ?>"></td>
                                            <td><input type="text" name="service_price[<?php echo esc_attr($key) ?>]" value="<?php echo esc_attr($service['total_price']) ?>"></td>
                                            <td><a href="#" class="rwmb-button button remove-clone">-</a></td>
                                        </tr>
            <?php endforeach; ?>
        <?php } ?>
                                <tr><td colspan="5"><a href="#" class="rwmb-button button-primary add-clone">+</a></td></tr>
                            </tbody></table>
                    </div>
                </div>

                <input type="submit" class="button-primary button_save_order" name="save" value="Save order">

                <a href="edit.php?post_type=tour&amp;page=tour_orders" class="button-secondary">Cancel</a>
        <?php wp_nonce_field('ct_manage_orders', 'order_save'); ?>
            </form>
        </div>

        <?php
        if (!empty($ct_options['vld_credit_card']) && !empty($ct_options['cc_off_charge']) && !empty($order_data['other']))
        {
            $cc_fields = array('cc_type' => 'CREDIT CARD TYPE', 'cc_holder_name' => 'CARD HOLDER NAME', 'cc_number' => 'CARD NUMBER', 'cc_cid' => 'CARD IDENTIFICATION NUMBER', 'cc_exp_year' => 'EXPIRATION YEAR', 'cc_exp_month' => 'EXPIRATION MONTH');
            $cc_infos = unserialize($order_data['other']);

            echo '<style>.cc_table{background:#fff;margin-top:30px;}.cc_table td{padding:10px;}.cc_table,.cc_table tr,.cc_table td{border:1px solid #000; border-collapse: collapse;}</style>';
            echo '<div style="clear:both"></div><h3>Credit Card Info</h3>';

            echo '<table class="cc_table"><tbody>';
            foreach ($cc_fields as $key => $label)
            {
                if (!empty($cc_infos[$key]))
                {
                    echo '<tr><td><label>' . $label . '</label></td><td>' . $cc_infos[$key] . '</td></tr>';
                }
            }
            echo '</tbody></table>';
        }
    }

}

/*
 * order delete action
 */
if (!function_exists('ct_tour_order_delete_action'))
{

    function ct_tour_order_delete_action()
    {
        global $wpdb;
        // data validation
        if (empty($_REQUEST['order_id']))
        {
            print esc_html__('Sorry, you tried to remove nothing.', 'citytours');
            exit;
        }

        // nonce check
        if (!isset($_GET['_wpnonce']) || !wp_verify_nonce($_GET['_wpnonce'], 'order_delete'))
        {
            print esc_html__('Sorry, your nonce did not verify.', 'citytours');
            exit;
        }

        // check ownership if user is not admin
        if (!current_user_can('ct_tour_order'))
        {
            $sql = $wpdb->prepare('SELECT CT_Orders.post_id FROM %1$s as CT_Orders WHERE CT_Orders.id = %2$d', CT_ORDER_TABLE, $_REQUEST['order_id']);
            $post_id = $wpdb->get_var($sql);
            $post_author_id = get_post_field('post_author', $post_id);
            if (get_current_user_id() != $post_author_id)
            {
                print esc_html__('You don\'t have permission to remove other\'s item.', 'citytours');
                exit;
            }
        }

        // do action
        $sql = sprintf('DELETE ct_order, ct_bookings, ct_services FROM %1$s AS ct_order
		LEFT JOIN %2$s AS ct_bookings ON ct_order.id = ct_bookings.order_id
		LEFT JOIN %3$s AS ct_services ON ct_order.id = ct_services.order_id
		WHERE ct_order.id = %4$s', CT_ORDER_TABLE, CT_TOUR_BOOKINGS_TABLE, CT_ADD_SERVICES_BOOKINGS_TABLE, '%d');
        $wpdb->query($wpdb->prepare($sql, $_REQUEST['order_id']));
        wp_redirect(admin_url('edit.php?post_type=tour&page=tour_orders'));
        exit;
    }

}

/*
 * order save action
 */
if (!function_exists('ct_tour_order_save_action'))
{

    function ct_tour_order_save_action()
    {
        //validation
        if (!isset($_POST['order_save']) || !wp_verify_nonce($_POST['order_save'], 'ct_manage_orders'))
        {
            print esc_html__('Sorry, your nonce did not verify.', 'citytours');
            exit;
        }

        if (empty($_POST['post_id']) || 'tour' != get_post_type($_POST['post_id']))
        {
            print esc_html__('Invalide Tour ID.', 'citytours');
            exit;
        }

        global $wpdb;
        $default_order_data = ct_order_default_order_data('update');
        $order_data = array();
        foreach ($default_order_data as $table_field => $def_value)
        {
            if (isset($_POST[$table_field]))
            {
                $order_data[$table_field] = $_POST[$table_field];
                if (!is_array($_POST[$table_field]))
                {
                    $order_data[$table_field] = sanitize_text_field($order_data[$table_field]);
                } else
                {
                    $order_data[$table_field] = serialize($order_data[$table_field]);
                }
            }
        }

        $order_data = array_replace($default_order_data, $order_data);
        $order_data['post_id'] = ct_tour_org_id($order_data['post_id']);
        if (empty($_POST['id']))
        {
            //insert
            $order_data['created'] = date('Y-m-d H:i:s');
            $order_data['post_type'] = 'tour';
            $wpdb->insert(CT_ORDER_TABLE, $order_data);
            $order_id = $wpdb->insert_id;
        } else
        {
            //update
            $wpdb->update(CT_ORDER_TABLE, $order_data, array('id' => sanitize_text_field($_POST['id'])));
            $order_id = sanitize_text_field($_POST['id']);
        }

        $tour_data = array(
            'tour_id' => $order_data['post_id'],
            'tour_date' => $order_data['date_from'],
            'adults' => $order_data['total_adults'],
            'kids' => $order_data['total_kids'],
            'total_price' => $order_data['total_price'],
            'order_id' => $order_id,
        );

        // update tour booking table
        $sql = 'DELETE FROM ' . CT_TOUR_BOOKINGS_TABLE . ' WHERE order_id=%d';
        $wpdb->query($wpdb->prepare($sql, $order_id));
        $format = array('%d', '%s', '%d', '%d', '%f', '%d');
        if (!empty($_POST['tour_booking_id']))
        {
            $tour_data['id'] = $_POST['tour_booking_id'];
            $format[] = '%d';
        }
        $wpdb->insert(CT_TOUR_BOOKINGS_TABLE, $tour_data, $format); // add additional services
        // update service table
        if (!empty($_POST['service_id']))
        {
            $service_id_list = $_POST['service_id'];
            $service_qty_list = $_POST['service_qty'];
            $service_price_list = $_POST['service_price'];
            $service_booking_id_list = $_POST['service_booking_id'];
            $sql = 'DELETE FROM ' . CT_ADD_SERVICES_BOOKINGS_TABLE . ' WHERE order_id=%d';
            $wpdb->query($wpdb->prepare($sql, $order_id));

            for ($index = 0; $index < count($service_id_list); $index++)
            {
                $service_data = array(
                    'add_service_id' => $service_id_list[$index],
                    'qty' => $service_qty_list[$index],
                    'total_price' => $service_price_list[$index],
                    'order_id' => $order_id,
                );

                $format = array('%d', '%d', '%f', '%d');
                if (!empty($service_booking_id_list[$index]))
                {
                    $service_data['id'] = $service_booking_id_list[$index];
                    $format[] = '%d';
                }
                $wpdb->insert(CT_ADD_SERVICES_BOOKINGS_TABLE, $service_data, $format); // add additional services
            }
        }

        wp_redirect(admin_url('edit.php?post_type=tour&page=tour_orders&action=edit&order_id=' . $order_id . '&updated=true'));
        exit;
    }

}

/*
 * order admin enqueue script action
 */
if (!function_exists('ct_tour_order_admin_enqueue_scripts'))
{

    function ct_tour_order_admin_enqueue_scripts()
    {

        // support select2
        wp_enqueue_style('rwmb_select2', RWMB_URL . 'css/select2/select2.css', array(), '3.2');
        wp_enqueue_script('rwmb_select2', RWMB_URL . 'js/select2/select2.min.js', array(), '3.2', true);


        // datepicker
        $url = RWMB_URL . 'css/jqueryui';
        wp_register_style('jquery-ui-core', "{$url}/jquery.ui.core.css", array(), '1.8.17');
        wp_register_style('jquery-ui-theme', "{$url}/jquery.ui.theme.css", array(), '1.8.17');
        wp_enqueue_style('jquery-ui-datepicker', "{$url}/jquery.ui.datepicker.css", array('jquery-ui-core', 'jquery-ui-theme'), '1.8.17');

        // Load localized scripts
        $locale = str_replace('_', '-', get_locale());
        $file_path = 'jqueryui/datepicker-i18n/jquery.ui.datepicker-' . $locale . '.js';
        $deps = array('jquery-ui-datepicker');
        if (file_exists(RWMB_DIR . 'js/' . $file_path))
        {
            wp_register_script('jquery-ui-datepicker-i18n', RWMB_URL . 'js/' . $file_path, $deps, '1.8.17', true);
            $deps[] = 'jquery-ui-datepicker-i18n';
        }

        wp_enqueue_script('rwmb-date', RWMB_URL . 'js/' . 'date.js', $deps, RWMB_VER, true);
        wp_localize_script('rwmb-date', 'RWMB_Date', array('lang' => $locale));
        wp_enqueue_script('jquery-ui-sortable');

        // custom style and js
        // wp_enqueue_style( 'ct_admin_tour_style' , CT_TEMPLATE_DIRECTORY_URI . '/css/admin/admin.css' ); 
        wp_enqueue_script('ct_admin_tour_script', CT_TEMPLATE_DIRECTORY_URI . '/js/admin/order.js', array('jquery'), '1.0', true);
    }

}

add_action('admin_menu', 'ct_tour_order_add_menu_items');
