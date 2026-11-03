<?php
/**
 * Plugin Name: Social Media Orders
 * Description: A lead collection plugin with form builder, 2-step submission, reCAPTCHA, Email Notifications, and Data Export.
 * Version: 2.1.4
 * Author: Mohannad Qamara
 * Author URI: https://mohannad.online
 * Text Domain: social-media-orders
 * Domain Path: /languages
 * Copyright 2025 Mohannad Qamara
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * 1. CONFIGURATION
 * --------------------------------------
 */
define( 'SMO_PATH', plugin_dir_path( __FILE__ ) );
define( 'SMO_URL', plugin_dir_url( __FILE__ ) );

define( 'SMO_RECAPTCHA_SITE_KEY', '6Lcc1RQsAAAAAKhfgBoxAeHwlWXrf3ksiCbmtMR4' );
define( 'SMO_RECAPTCHA_SECRET_KEY', '6Lcc1RQsAAAAAHJcuqMnsDIcNZuoFY5zbOh3Lh8n' );

/**
 * 2. INIT & POST TYPES
 * --------------------------------------
 */
add_action( 'plugins_loaded', 'smo_load_textdomain' );
function smo_load_textdomain() {
    load_plugin_textdomain( 'social-media-orders', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}

add_action( 'init', 'smo_register_post_types' );
function smo_register_post_types() {
    register_post_type( 'smo_form', array(
        'labels' => array(
            'name' => __( 'SMO Forms', 'social-media-orders' ),
            'singular_name' => __( 'Form', 'social-media-orders' ),
            'add_new_item' => __( 'Add New Form', 'social-media-orders' ),
            'edit_item' => __( 'Edit Form', 'social-media-orders' ),
        ),
        'public' => false,
        'show_ui' => true,
        'menu_icon' => 'dashicons-clipboard',
        'supports' => array( 'title' ),
    ));

    register_post_type( 'smo_entry', array(
        'labels' => array( 'name' => __( 'Inquiries', 'social-media-orders' ), 'singular_name' => __( 'Inquiry', 'social-media-orders' ) ),
        'public' => false,
        'show_ui' => true,
        'show_in_menu' => 'edit.php?post_type=smo_form',
        'capabilities' => array( 'create_posts' => false ),
        'map_meta_cap' => true,
        'supports' => array( 'title' ),
    ));
}

/**
 * 3. ADMIN MENUS & PAGES (Settings & Export)
 * --------------------------------------
 */
add_action( 'admin_menu', 'smo_add_admin_pages' );
function smo_add_admin_pages() {
    add_submenu_page( 'edit.php?post_type=smo_form', __( 'Settings & Notifications', 'social-media-orders' ), __( 'Settings', 'social-media-orders' ), 'manage_options', 'smo_settings', 'smo_render_settings_page' );
    add_submenu_page( 'edit.php?post_type=smo_form', __( 'Export Data', 'social-media-orders' ), __( 'Export', 'social-media-orders' ), 'manage_options', 'smo_export', 'smo_render_export_page' );
}

function smo_render_settings_page() {
    if ( isset( $_POST['smo_save_settings'] ) && check_admin_referer( 'smo_settings_nonce' ) ) {
        $settings = array(
            'email_enabled' => isset($_POST['email_enabled']) ? 1 : 0,
            'recipient'     => sanitize_text_field( $_POST['recipient'] ),
            'subject'       => sanitize_text_field( $_POST['subject'] ),
            'body'          => wp_kses_post( $_POST['body'] ),
        );
        update_option( 'smo_settings', $settings );
        echo '<div class="notice notice-success"><p>' . __( 'Settings Saved.', 'social-media-orders' ) . '</p></div>';
    }

    $opt = get_option( 'smo_settings', array( 'email_enabled'=>0, 'recipient'=>get_option('admin_email'), 'subject'=>'New Order: {form_title}', 'body'=>'New lead from {name}.' ) );
    ?>
    <div class="wrap">
        <h1><?php _e( 'Email Notification Settings', 'social-media-orders' ); ?></h1>
        <form method="post">
            <table class="form-table">
                <tr>
                    <th scope="row"><?php _e( 'Enable Notifications', 'social-media-orders' ); ?></th>
                    <td><input type="checkbox" name="email_enabled" value="1" <?php checked( $opt['email_enabled'], 1 ); ?>></td>
                </tr>
                <tr>
                    <th scope="row"><?php _e( 'Recipient Email', 'social-media-orders' ); ?></th>
                    <td><input type="text" name="recipient" value="<?php echo esc_attr( $opt['recipient'] ); ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th scope="row"><?php _e( 'Email Subject', 'social-media-orders' ); ?></th>
                    <td><input type="text" name="subject" value="<?php echo esc_attr( $opt['subject'] ); ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th scope="row"><?php _e( 'Email Body', 'social-media-orders' ); ?></th>
                    <td>
                        <?php wp_editor( $opt['body'], 'body', array( 'textarea_rows' => 10 ) ); ?>
                        <p class="description">
                            <?php _e( 'Available Placeholders:', 'social-media-orders' ); ?> <br>
                            <code>{name}</code>, <code>{email}</code>, <code>{mobile}</code>, <code>{individuals}</code>, <code>{source_url}</code>, <code>{page_title}</code>, <code>{form_title}</code>, <code>{all_answers}</code>
                        </p>
                    </td>
                </tr>
            </table>
            <?php wp_nonce_field( 'smo_settings_nonce' ); ?>
            <input type="submit" name="smo_save_settings" class="button button-primary" value="<?php _e( 'Save Settings', 'social-media-orders' ); ?>">
        </form>
    </div>
    <?php
}

function smo_render_export_page() {
    $forms = get_posts( array( 'post_type' => 'smo_form', 'numberposts' => -1 ) );
    ?>
    <div class="wrap">
        <h1><?php _e( 'Export Inquiries', 'social-media-orders' ); ?></h1>
        <div class="card" style="max-width: 600px; padding: 20px;">
            <form method="post" action="<?php echo admin_url( 'admin-post.php' ); ?>">
                <input type="hidden" name="action" value="smo_export_data">
                <?php wp_nonce_field( 'smo_export_nonce' ); ?>
                
                <p>
                    <label><strong><?php _e( 'Select Form:', 'social-media-orders' ); ?></strong></label><br>
                    <select name="form_id" class="widefat">
                        <option value="all"><?php _e( 'All Forms', 'social-media-orders' ); ?></option>
                        <?php foreach ( $forms as $f ) echo '<option value="'.$f->ID.'">'.esc_html($f->post_title).'</option>'; ?>
                    </select>
                </p>
                <p>
                    <label><strong><?php _e( 'Format:', 'social-media-orders' ); ?></strong></label><br>
                    <label><input type="radio" name="format" value="csv" checked><?php printf( esc_html__( 'File %s', 'social-media-orders' ), 'CSV' ); ?></label> &nbsp;
                    <label><input type="radio" name="format" value="xls"><?php printf( esc_html__( 'File %s', 'social-media-orders' ), 'Excel (.xls)' ); ?></label> &nbsp;
                    <label><input type="radio" name="format" value="xml"><?php printf( esc_html__( 'File %s', 'social-media-orders' ), 'XML' ); ?></label>
                </p>
                <p>
                    <input type="submit" class="button button-primary" value="<?php _e( 'Download Export', 'social-media-orders' ); ?>">
                </p>
            </form>
        </div>
    </div>
    <?php
}

add_action( 'admin_post_smo_export_data', 'smo_handle_export_request' );
function smo_handle_export_request() {
    check_admin_referer( 'smo_export_nonce' );
    if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Unauthorized' );

    $form_id = sanitize_text_field( $_POST['form_id'] );
    $format = sanitize_text_field( $_POST['format'] );

    $args = array( 'post_type' => 'smo_entry', 'numberposts' => -1, 'post_status' => 'publish' );
    if ( $form_id !== 'all' ) {
        $args['meta_key'] = '_smo_form_id';
        $args['meta_value'] = $form_id;
    }
    $entries = get_posts( $args );

    $dynamic_headers = array();
    if ( $form_id !== 'all' ) {
        $questions = get_post_meta( $form_id, '_smo_questions', true );
        if ( $questions ) {
            foreach ( $questions as $q ) $dynamic_headers[] = $q['title'];
        }
    }

    $data_rows = array();
    foreach ( $entries as $entry ) {
        $client = get_post_meta( $entry->ID, '_smo_client_data', true );
        $answers = get_post_meta( $entry->ID, '_smo_answers', true );
        $f_id = get_post_meta( $entry->ID, '_smo_form_id', true );
        
        $page_title = isset($client['source_title']) ? $client['source_title'] : '';
        $form_title = str_replace('{page_title}', $page_title, get_the_title($f_id));

        $row = array(
            'Date'        => get_the_date( 'Y-m-d H:i', $entry->ID ),
            'Form'        => $form_title,
            'Name'        => isset( $client['name'] ) ? $client['name'] : '',
            'Mobile'      => isset( $client['mobile'] ) ? $client['mobile'] : '',
            'Email'       => isset( $client['email'] ) ? $client['email'] : '',
            'Individuals' => isset( $client['individuals'] ) ? $client['individuals'] : '',
            'Source URL'  => isset( $client['source_url'] ) ? $client['source_url'] : '',
            'Source Page' => $page_title,
        );

        $ans_map = array();
        $ans_str = '';
        if ( $answers && is_array( $answers ) ) {
            foreach ( $answers as $a ) {
                $ans_map[ $a['question'] ] = $a['answer'];
                $ans_str .= $a['question'] . ': ' . $a['answer'] . " | ";
            }
        }

        if ( $form_id !== 'all' ) {
            foreach ( $dynamic_headers as $head ) {
                $row[ $head ] = isset( $ans_map[ $head ] ) ? $ans_map[ $head ] : '';
            }
        } else {
            $row['Details'] = trim( $ans_str, ' | ' );
        }
        $data_rows[] = $row;
    }

    $date = date( 'Y-m-d' );
    
    if ( $format == 'csv' ) {
        $filename = "smo_export_{$date}.csv";
        header( 'Content-Type: text/csv; charset=utf-8' );
        header( 'Content-Disposition: attachment; filename=' . $filename );
        $output = fopen( 'php://output', 'w' );
        fputs( $output, "\xEF\xBB\xBF" );
        if ( ! empty( $data_rows ) ) {
            fputcsv( $output, array_keys( $data_rows[0] ) );
            foreach ( $data_rows as $r ) fputcsv( $output, $r );
        }
        fclose( $output );
        exit;
    } 
    elseif ( $format == 'xls' ) {
        $filename = "smo_export_{$date}.xls";
        header("Content-Type: application/vnd.ms-excel; charset=UTF-8");
        header("Content-Disposition: attachment; filename=$filename");
        
        echo '<?xml version="1.0" encoding="UTF-8"?>';
        echo '<?mso-application progid="Excel.Sheet"?>';
        echo '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet" xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet" xmlns:html="http://www.w3.org/TR/REC-html40">';
        echo '<Worksheet ss:Name="Inquiries"><Table>';
        
        if ( ! empty( $data_rows ) ) {
            echo '<Row>';
            foreach ( array_keys( $data_rows[0] ) as $h ) {
                echo '<Cell><Data ss:Type="String">' . htmlspecialchars( $h ) . '</Data></Cell>';
            }
            echo '</Row>';
            
            foreach ( $data_rows as $r ) {
                echo '<Row>';
                foreach ( $r as $cell ) {
                    echo '<Cell><Data ss:Type="String">' . htmlspecialchars( $cell ) . '</Data></Cell>';
                }
                echo '</Row>';
            }
        }
        echo '</Table></Worksheet></Workbook>';
        exit;
    } 
    elseif ( $format == 'xml' ) {
        $filename = "smo_export_{$date}.xml";
        header( 'Content-Type: text/xml' );
        header( 'Content-Disposition: attachment; filename=' . $filename );
        echo '<?xml version="1.0" encoding="UTF-8"?>';
        echo '<inquiries>';
        foreach ( $data_rows as $r ) {
            echo '<entry>';
            foreach ( $r as $k => $v ) {
                $slug = sanitize_title( $k );
                if(!$slug) $slug = 'field';
                echo "<$slug>" . htmlspecialchars( $v ) . "</$slug>";
            }
            echo '</entry>';
        }
        echo '</inquiries>';
        exit;
    }
}


/**
 * 4. ADMIN: BUILDER & COLUMNS
 * --------------------------------------
 */
add_action( 'add_meta_boxes', 'smo_add_meta_boxes' );
function smo_add_meta_boxes() {
    add_meta_box( 'smo_form_builder', __( 'Form Builder Configuration', 'social-media-orders' ), 'smo_render_form_builder', 'smo_form', 'normal', 'high' );
    add_meta_box( 'smo_entry_data', __( 'Submitted Data', 'social-media-orders' ), 'smo_render_entry_view', 'smo_entry', 'normal', 'high' );
}

function smo_render_form_builder( $post ) {
    $form_type = get_post_meta( $post->ID, '_smo_form_type', true ) ?: 'steps';
    $questions = get_post_meta( $post->ID, '_smo_questions', true );
    $youtube_url = get_post_meta( $post->ID, '_smo_youtube_url', true );
    $pre_step_1_content = get_post_meta( $post->ID, '_smo_pre_step_1_content', true );
    wp_nonce_field( 'smo_save_form', 'smo_form_nonce' );
    ?>
    <div class="smo-field-group" style="padding: 10px; border: 1px solid #ccd0d4; margin-bottom: 15px; background-color: #fff;">
        <label for="smo_form_type"><strong><?php _e( 'Form Type (نوع الفورم)', 'social-media-orders' ); ?></strong></label>
        <select name="smo_form_type" id="smo_form_type" style="width:100%; margin-top:5px;">
            <option value="steps" <?php selected($form_type, 'steps'); ?>><?php _e( 'Steps (خطوات)', 'social-media-orders' ); ?></option>
            <option value="fixed" <?php selected($form_type, 'fixed'); ?>><?php _e( 'Fixed Data (بيانات ثابته)', 'social-media-orders' ); ?></option>
        </select>
        <p class="description"><?php _e( 'Fixed Data form collects: Name, Mobile, Individuals, and Source URL without Step 2.', 'social-media-orders' ); ?></p>
    </div>

    <div class="smo-field-group smo-builder-steps-only" style="padding: 10px; border: 1px solid #ccd0d4; margin-bottom: 15px; background-color: #fff;">
        <label for="smo_pre_step_1_content"><strong><?php _e( 'Content Before Step 1 (Optional)', 'social-media-orders' ); ?></strong></label>
        <?php wp_editor( $pre_step_1_content, 'smo_pre_step_1_content', array( 'textarea_name' => 'smo_pre_step_1_content', 'media_buttons' => false, 'textarea_rows' => 5 ) ); ?>
        <p class="description"><?php _e( 'This content will be displayed above the first step of the form.', 'social-media-orders' ); ?></p>
    </div>

    <div class="smo-field-group smo-builder-steps-only" style="padding: 10px; border: 1px solid #ccd0d4; margin-bottom: 15px; background-color: #fff;">
        <label for="smo_youtube_url"><strong><?php _e( 'YouTube Video URL (Optional)', 'social-media-orders' ); ?></strong></label>
        <input type="url" id="smo_youtube_url" name="smo_youtube_url" value="<?php echo esc_url( $youtube_url ); ?>" style="width:100%" placeholder="e.g. https://www.youtube.com/watch?v=dQw4w9WgXcQ">
        <p class="description"><?php _e( 'Enter a YouTube video URL to display it after step 1 on the frontend.', 'social-media-orders' ); ?></p>
    </div>

    <div id="smo-builder-wrapper" class="smo-builder-steps-only">
        <div id="smo-questions-list" class="ui-sortable" style="margin-top: 15px;">
            <?php if ( ! empty( $questions ) && is_array( $questions ) ) {
                foreach ( $questions as $index => $q ) { smo_render_question_row( $index, $q ); }
            } ?>
        </div>
        <button type="button" class="button button-primary" id="smo-add-question" style="margin-top:15px;"><?php esc_html_e( '+ Add Question', 'social-media-orders' ); ?></button>
    </div>
    <script type="text/template" id="smo-row-template"><?php smo_render_question_row( 'INDEX', array() ); ?></script>
    <style>
        .smo-question-row { border: 1px solid #ccc; background: #f9f9f9; padding: 15px; margin-bottom: 10px; cursor: move; }
        .smo-row-header { font-weight: bold; margin-bottom: 10px; display: flex; justify-content: space-between; }
        .smo-field-group { margin-bottom: 10px; }
        .remove-row { color: red; cursor: pointer; }
    </style>
    <script>
    jQuery(document).ready(function($) {
        function toggleBuilderOptions() {
            if ( $('#smo_form_type').val() === 'fixed' ) {
                $('.smo-builder-steps-only').hide();
            } else {
                $('.smo-builder-steps-only').show();
            }
        }
        $('#smo_form_type').on('change', toggleBuilderOptions);
        toggleBuilderOptions();

        $('#smo-questions-list').sortable();
        $('#smo-add-question').on('click', function() {
            var index = $('#smo-questions-list .smo-question-row').length;
            $('#smo-questions-list').append($('#smo-row-template').html().replace(/INDEX/g, index));
        });
        $(document).on('click', '.remove-row', function() { $(this).closest('.smo-question-row').remove(); });
    });
    </script>
    <?php
}

function smo_render_question_row( $index, $data ) {
    $title = isset($data['title']) ? esc_attr($data['title']) : '';
    $type = isset($data['type']) ? esc_attr($data['type']) : 'text';
    $options = isset($data['options']) ? esc_textarea($data['options']) : '';
    ?>
    <div class="smo-question-row">
        <div class="smo-row-header"><span><?php esc_html_e('Question Field', 'social-media-orders'); ?></span><span class="remove-row"><?php esc_html_e('Remove', 'social-media-orders'); ?></span></div>
        <div class="smo-field-group"><label><?php _e( 'Title', 'social-media-orders' ); ?></label><input type="text" style="width:100%" name="smo_questions[<?php echo $index; ?>][title]" value="<?php echo $title; ?>"></div>
        <div class="smo-field-group"><label><?php _e( 'Type', 'social-media-orders' ); ?></label>
            <select name="smo_questions[<?php echo $index; ?>][type]" style="width:100%">
                <option value="text" <?php selected($type, 'text'); ?>><?php _e( 'Text', 'social-media-orders' ); ?></option>
                <option value="number" <?php selected($type, 'number'); ?>><?php _e( 'Number', 'social-media-orders' ); ?></option>
                <option value="date" <?php selected($type, 'date'); ?>><?php _e( 'Date', 'social-media-orders' ); ?></option>
                <option value="textarea" <?php selected($type, 'textarea'); ?>><?php _e( 'Text Area', 'social-media-orders' ); ?></option>
                <option value="select" <?php selected($type, 'select'); ?>><?php _e( 'Select Dropdown', 'social-media-orders' ); ?></option>
                <option value="radio" <?php selected($type, 'radio'); ?>><?php _e( 'Radio Buttons', 'social-media-orders' ); ?></option>
            </select>
        </div>
        <div class="smo-field-group"><label><?php _e( 'Options (One per line)', 'social-media-orders' ); ?></label><textarea style="width:100%" name="smo_questions[<?php echo $index; ?>][options]" rows="3"><?php echo $options; ?></textarea></div>
    </div>
    <?php
}

add_action( 'save_post_smo_form', 'smo_save_form_meta' );
function smo_save_form_meta( $post_id ) {
    if ( ! isset( $_POST['smo_form_nonce'] ) || ! wp_verify_nonce( $_POST['smo_form_nonce'], 'smo_save_form' ) ) return;
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;

    if ( isset( $_POST['smo_form_type'] ) ) update_post_meta( $post_id, '_smo_form_type', sanitize_text_field( $_POST['smo_form_type'] ) );

    if ( isset( $_POST['smo_questions'] ) ) update_post_meta( $post_id, '_smo_questions', array_values( $_POST['smo_questions'] ) );
    else delete_post_meta( $post_id, '_smo_questions' );

    if ( isset( $_POST['smo_youtube_url'] ) ) update_post_meta( $post_id, '_smo_youtube_url', esc_url_raw( $_POST['smo_youtube_url'] ) );
    else delete_post_meta( $post_id, '_smo_youtube_url' );

    if ( isset( $_POST['smo_pre_step_1_content'] ) ) update_post_meta( $post_id, '_smo_pre_step_1_content', wp_kses_post( $_POST['smo_pre_step_1_content'] ) );
    else delete_post_meta( $post_id, '_smo_pre_step_1_content' );
}

add_filter('manage_smo_form_posts_columns', function($columns) {
    $new = array(); foreach($columns as $key => $val) { $new[$key] = $val; if($key == 'title') $new['smo_shortcode'] = 'Shortcode'; } return $new;
});
add_action('manage_smo_form_posts_custom_column', function($column, $post_id) {
    if ($column == 'smo_shortcode') echo '<code style="user-select:all; background:#eee; padding:2px 5px;">[smo_form id="' . $post_id . '" title=""]</code>';
}, 10, 2);

function smo_render_entry_view( $post ) {
    $client_data = get_post_meta( $post->ID, '_smo_client_data', true );
    $answers = get_post_meta( $post->ID, '_smo_answers', true );
    $form_id = get_post_meta( $post->ID, '_smo_form_id', true );
    
    $page_title = isset($client_data['source_title']) ? $client_data['source_title'] : '';
    $form_title = str_replace('{page_title}', $page_title, get_the_title($form_id));
    
    echo '<h3>Source: <a href="'.get_edit_post_link($form_id).'">'.esc_html($form_title).'</a></h3>';
    echo '<table class="widefat striped"><thead><tr><th colspan="2">' . esc_html__( 'Client Data', 'social-media-orders' ) . '</th></tr></thead><tbody>';
    
    $labels_map = array('name' => 'Name', 'email' => 'Email', 'mobile' => 'Mobile', 'individuals' => 'Individuals', 'source_url' => 'Source Page URL', 'source_title' => 'Source Page Title');
    if($client_data) {
        foreach($client_data as $k => $v) {
            $label = isset($labels_map[$k]) ? $labels_map[$k] : ucfirst($k);
            if ($k === 'source_url') {
                $v = '<a href="'.esc_url($v).'" target="_blank">'.esc_html($v).'</a>';
                echo '<tr><td>'.esc_html( $label ).'</td><td>'.$v.'</td></tr>';
            } else {
                echo '<tr><td>'.esc_html( $label ).'</td><td>'.esc_html($v).'</td></tr>';
            }
        }
    }
    echo '</tbody></table><br>';

    if($answers || !isset($client_data['individuals'])) {
        echo '<table class="widefat striped"><thead><tr><th colspan="2">' . esc_html__( 'Questions', 'social-media-orders' ) . '</th></tr></thead><tbody>';
        if($answers) foreach($answers as $a) echo '<tr><td>'.esc_html($a['question']).'</td><td>'.nl2br(esc_html($a['answer'])).'</td></tr>';
        else echo '<tr><td colspan="2">No step 2 data.</td></tr>';
        echo '</tbody></table>';
    }
}

function smo_filter_entries_by_form() {
    global $typenow;
    if ( $typenow == 'smo_entry' ) {
        $forms = get_posts(array('post_type' => 'smo_form', 'numberposts' => -1, 'post_status' => 'publish'));
        $current_selection = isset($_GET['filter_form']) ? $_GET['filter_form'] : '';
        echo '<select name="filter_form"><option value="">' . __( 'All Forms', 'social-media-orders' ) . '</option>';
        foreach($forms as $form) echo '<option value="'.esc_attr($form->ID).'" '.selected($current_selection, $form->ID, false).'>'.esc_html($form->post_title).'</option>';
        echo '</select>';
    }
}
add_action( 'restrict_manage_posts', 'smo_filter_entries_by_form' );

function smo_filter_query( $query ) {
    global $pagenow;
    if ( is_admin() && $pagenow == 'edit.php' && ! empty( $_GET['filter_form'] ) && isset($query->query['post_type']) && $query->query['post_type'] == 'smo_entry' ) {
        $query->set( 'meta_key', '_smo_form_id' ); $query->set( 'meta_value', $_GET['filter_form'] );
    }
}
add_filter( 'parse_query', 'smo_filter_query' );

add_filter( 'manage_smo_entry_posts_columns', function( $columns ) {
    $columns['smo_form_source'] = __( 'Form Source', 'social-media-orders' );
    $columns['smo_mobile'] = __( 'Mobile', 'social-media-orders' );
    return $columns;
});

add_action( 'manage_smo_entry_posts_custom_column', function( $column, $post_id ) {
    if ( $column == 'smo_form_source' ) {
        $form_id = get_post_meta( $post_id, '_smo_form_id', true );
        $client_data = get_post_meta( $post_id, '_smo_client_data', true );
        $page_title = isset($client_data['source_title']) ? $client_data['source_title'] : '';
        
        if ($form_id && get_post_status($form_id)) {
            $form_title = str_replace('{page_title}', $page_title, get_the_title($form_id));
            echo '<a href="' . get_edit_post_link( $form_id ) . '"><strong>' . esc_html( $form_title ) . '</strong></a>';
        } else {
            echo '<span style="color:#aaa;">' . __( '(Form Deleted)', 'social-media-orders' ) . '</span>';
        }
    }
    if ( $column == 'smo_mobile' ) {
        $data = get_post_meta( $post_id, '_smo_client_data', true );
        echo isset( $data['mobile'] ) ? esc_html( $data['mobile'] ) : '-';
    }
}, 10, 2 );

/**
 * 5. FRONTEND: SHORTCODE & SCRIPTS
 * --------------------------------------
 */
add_shortcode( 'smo_form', 'smo_shortcode_handler' );

function smo_shortcode_handler( $atts ) {
    $a = shortcode_atts( array( 
        'id'    => 0,
        'title' => ''
    ), $atts );
    
    if ( ! $a['id'] ) return 'ID Missing';
    
    $form_id = intval( $a['id'] );
    $current_page_title = get_the_title();
    
    $shortcode_title = sanitize_text_field( $a['title'] );
    $shortcode_title = str_replace( '{page_title}', $current_page_title, $shortcode_title );

    $form_type = get_post_meta( $form_id, '_smo_form_type', true ) ?: 'steps';
    $questions = get_post_meta( $form_id, '_smo_questions', true );
    $pre_step_1_content = get_post_meta( $form_id, '_smo_pre_step_1_content', true );
    $youtube_url = get_post_meta( $form_id, '_smo_youtube_url', true );
    $wrapper_id = 'smo-wrapper-' . $form_id;

    $current_url = esc_url( (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]" );

    wp_enqueue_script( 'jquery' );
    wp_enqueue_script( 'google-recaptcha', 'https://www.google.com/recaptcha/api.js?render=' . SMO_RECAPTCHA_SITE_KEY, array(), null, true );
    add_action( 'wp_footer', 'smo_print_frontend_js', 100 );

    $pre_content_html = '';
    if ( ! empty( $pre_step_1_content ) ) {
        $pre_content_html = '<div class="smo-pre-content" style="margin-bottom: 20px;">' . wpautop( $pre_step_1_content ) . '</div>';
    }

    $embed_url = '';
    if ( ! empty( $youtube_url ) && preg_match( '%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $youtube_url, $match ) ) {
        $video_id = $match[1];
        $embed_url = 'https://www.youtube.com/embed/' . $video_id;
    }

    ob_start();
    ?>
    <div class="smo-form-wrapper" id="<?php echo $wrapper_id; ?>">
        
        <?php if ( $form_type === 'fixed' ) : ?>
            
            <form class="smo-fixed-form smo-fixed-layout" method="POST" data-id="<?php echo $form_id; ?>">
                <?php if ( ! empty( $shortcode_title ) ) : ?>
                    <h3 class="smo-form-heading"><?php echo esc_html( $shortcode_title ); ?></h3>
                <?php endif; ?>

                <div class="smo-fixed-grid">
                    <div class="smo-group name-group">
                        <label><?php esc_html_e('Name', 'social-media-orders'); ?></label>
                        <input type="text" name="c_name" required>
                    </div>
                    <div class="smo-group mobile-group">
                        <label><?php esc_html_e('Mobile', 'social-media-orders'); ?></label>
                        <input type="tel" name="c_mobile" required>
                    </div>
                    <div class="smo-group individuals-group">
                        <label><?php esc_html_e('Individuals', 'social-media-orders'); ?></label>
                        <input type="number" name="c_individuals" required min="1">
                    </div>
                    <div class="smo-group btn-group">
                        <button type="submit" class="smo-btn"><?php esc_html_e( 'Send', 'social-media-orders' ); ?></button>
                    </div>
                </div>
                
                <input type="hidden" name="source_url" value="<?php echo $current_url; ?>">
                <input type="hidden" name="source_title" value="<?php echo esc_attr( $current_page_title ); ?>">
                <input type="hidden" name="action" value="smo_submit_fixed">
                <input type="hidden" name="form_id" value="<?php echo $form_id; ?>">
                <input type="hidden" name="recaptcha_token" class="smo-recaptcha-token">
                <?php wp_nonce_field( 'smo_frontend_nonce', 'security' ); ?>
                <div class="smo-msg"></div>
            </form>
            <div class="smo-final-message" style="display:none; color:green; text-align:center; font-weight:bold; padding:20px; border:1px solid green; background:#e7f7e7; border-radius:5px;">
                <?php esc_html_e( 'Great, will be in touch with you shortly.', 'social-media-orders' ); ?>
            </div>

        <?php else : ?>

            <form class="smo-step-form smo-step-1" method="POST" data-id="<?php echo $form_id; ?>">
                <?php echo $pre_content_html; ?>
                <h3 class="smo-form-heading"><?php echo ! empty( $shortcode_title ) ? esc_html( $shortcode_title ) : esc_html__( 'Step 1: Info', 'social-media-orders' ); ?></h3>
                <div class="smo-group"><label><?php esc_html_e('Name', 'social-media-orders'); ?></label><input type="text" name="c_name" required></div>
                <div class="smo-group"><label><?php esc_html_e('Mobile', 'social-media-orders'); ?></label><input type="tel" name="c_mobile" required></div>
                <div class="smo-group"><label><?php esc_html_e('Email', 'social-media-orders'); ?></label><input type="email" name="c_email" required></div>
                
                <input type="hidden" name="source_url" value="<?php echo $current_url; ?>">
                <input type="hidden" name="source_title" value="<?php echo esc_attr( $current_page_title ); ?>">
                <input type="hidden" name="action" value="smo_submit_step_1">
                <input type="hidden" name="form_id" value="<?php echo $form_id; ?>">
                <input type="hidden" name="recaptcha_token" class="smo-recaptcha-token">
                <?php wp_nonce_field( 'smo_frontend_nonce', 'security' ); ?>
                <button type="submit" class="smo-btn"><?php esc_html_e( 'Send', 'social-media-orders' ); ?></button>
                <div class="smo-msg"></div>
            </form>

            <div class="smo-intermediate-message" style="display:none; margin-bottom:20px;">
                <div class="smo-alert smo-alert-success"><strong><?php esc_html_e( 'All is Done, we will be in contact soon.', 'social-media-orders' ); ?></strong></div>
                <div class="smo-alert smo-alert-info"><?php esc_html_e( 'If possible please provide us with some more details in order to arrange A very special offer designed for you.', 'social-media-orders' ); ?></div>
            </div>

            <?php if ( $embed_url ) : ?>
            <div class="smo-video-wrapper" style="display:none; position: relative; padding-bottom: 56.25%; height: 0; overflow: hidden; max-width: 100%; margin-bottom: 20px;">
                <iframe src="<?php echo esc_url( $embed_url ); ?>" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%;" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
            </div>
            <?php endif; ?>

            <form class="smo-step-form smo-step-2" method="POST" data-id="<?php echo $form_id; ?>" style="display:none;">
                <h3><?php esc_html_e( 'Step 2: Details', 'social-media-orders' ); ?></h3>
                <?php if($questions): foreach($questions as $idx => $q): $field = 'q_' . $idx; ?>
                    <div class="smo-group">
                        <label><?php echo esc_html($q['title']); ?></label>
                        <?php if($q['type'] == 'textarea'): ?> <textarea name="<?php echo $field; ?>"></textarea>
                        <?php elseif($q['type'] == 'select'): echo '<select name="'.$field.'">'; foreach(explode("\n", $q['options']) as $opt) echo '<option value="'.esc_attr(trim($opt)).'">'.esc_html(trim($opt)).'</option>'; echo '</select>'; ?>
                        <?php elseif($q['type'] == 'radio'): foreach(explode("\n", $q['options']) as $opt) echo '<label style="display:block;"><input type="radio" name="'.$field.'" value="'.esc_attr(trim($opt)).'"> '.esc_html(trim($opt)).'</label>'; ?>
                        <?php else: ?> <input type="<?php echo esc_attr($q['type']); ?>" name="<?php echo $field; ?>"> <?php endif; ?>
                        <input type="hidden" name="q_label_<?php echo $idx; ?>" value="<?php echo esc_attr($q['title']); ?>">
                    </div>
                <?php endforeach; endif; ?>
                <input type="hidden" name="action" value="smo_submit_step_2">
                <input type="hidden" name="entry_id" class="smo-entry-id-input">
                <?php wp_nonce_field( 'smo_frontend_nonce', 'security' ); ?>
                <button type="submit" class="smo-btn"><?php esc_html_e( 'Submit', 'social-media-orders' ); ?></button>
                <div class="smo-msg"></div>
            </form>
            
            <div class="smo-final-message" style="display:none; color:green; text-align:center; font-weight:bold; padding:20px; border:1px solid green; background:#e7f7e7; border-radius:5px;">
                <?php esc_html_e( 'Great, now we understand your preferences better, will be in touch with you shortly.', 'social-media-orders' ); ?>
            </div>
        <?php endif; ?>
    </div>
    <style>
        .smo-form-wrapper { margin:20px auto; padding:20px; border:1px solid #ddd; border-radius:5px; }
        .smo-form-heading { margin-top: 0; margin-bottom: 15px; }
        .smo-group { margin-bottom:15px; }
        .smo-group label { display:block; font-weight:bold; margin-bottom:5px; }
        .smo-group input:not([type=radio]), .smo-group select, .smo-group textarea { width:100%; padding:8px; border:1px solid #ccc; box-sizing:border-box; }
        .smo-btn { width:100%; padding:10px; background:#0073aa; color:#fff; border:none; cursor:pointer; box-sizing:border-box; }
        .smo-btn:disabled { opacity:0.6; cursor:not-allowed; }
        .smo-msg { margin-top:10px; }
        .smo-alert { padding: 10px 15px; margin-bottom: 10px; border-radius: 4px; border-left: 4px solid; }
        .smo-alert-success { background-color: #e7f7e7; border-color: #28a745; color: #155724; }
        .smo-alert-info { background-color: #e7f3fe; border-color: #17a2b8; color: #0c5460; }
        
        .smo-fixed-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; align-items: end; }
        .smo-fixed-grid .name-group { grid-column: 1 / -1; margin-bottom: 0; }
        .smo-fixed-grid .mobile-group { grid-column: 1 / 2; margin-bottom: 0; }
        .smo-fixed-grid .individuals-group { grid-column: 2 / 3; margin-bottom: 0; }
        .smo-fixed-grid .btn-group { grid-column: 1 / -1; margin-bottom: 0; }
        
        @media (min-width: 768px) {
            .smo-fixed-grid { grid-template-columns: repeat(4, 1fr); }
            .smo-fixed-grid .name-group, .smo-fixed-grid .mobile-group, .smo-fixed-grid .individuals-group, .smo-fixed-grid .btn-group { grid-column: span 1; }
        }
    </style>
    <?php
    return ob_get_clean();
}

function smo_print_frontend_js() {
    static $printed = false; if ( $printed ) return; $printed = true;
    ?>
    <script>
    document.addEventListener("DOMContentLoaded", function() {
        if (typeof jQuery === 'undefined') { console.error('SMO: jQuery missing.'); return; }
        jQuery(document).ready(function($) {
            if( typeof grecaptcha !== 'undefined' ) {
                grecaptcha.ready(function() {
                    grecaptcha.execute('<?php echo SMO_RECAPTCHA_SITE_KEY; ?>', {action: 'submit'}).then(function(t) { $('.smo-recaptcha-token').val(t); });
                });
            }

            $(document).on('submit', '.smo-fixed-form', function(e) {
                e.preventDefault(); var f = $(this), w = f.closest('.smo-form-wrapper'), b = f.find('button'); b.prop('disabled', true).text('...');
                $.post('<?php echo admin_url('admin-ajax.php'); ?>', f.serialize(), function(r) {
                    if(r.success) { 
                        f.slideUp(); 
                        w.find('.smo-final-message').slideDown(); 
                        if (typeof dataLayer !== 'undefined') {
                            dataLayer.push({
                                event: "booking_completed",
                                user: {
                                    phone: f.find('input[name="c_mobile"]').val(),
                                    first_name: f.find('input[name="c_name"]').val()
                                }
                            });
                        }
                    } 
                    else { f.find('.smo-msg').html('<span style="color:red">'+r.data+'</span>'); b.prop('disabled', false).text('<?php echo esc_js( __( 'Send', 'social-media-orders' ) ); ?>'); }
                }).fail(function(){ alert('Error'); b.prop('disabled', false); });
            });

            $(document).on('submit', '.smo-step-1', function(e) {
                e.preventDefault(); var f = $(this), w = f.closest('.smo-form-wrapper'), b = f.find('button'); b.prop('disabled', true).text('...');
                $.post('<?php echo admin_url('admin-ajax.php'); ?>', f.serialize(), function(r) {
                    if(r.success) { 
                        w.find('.smo-entry-id-input').val(r.data.entry_id); 
                        f.slideUp(); 
                        w.find('.smo-intermediate-message, .smo-video-wrapper, .smo-step-2').slideDown(); 

                        if (typeof dataLayer !== 'undefined') {
                            dataLayer.push({
                                event: "booking_completed",
                                user: {
                                    email: f.find('input[name="c_email"]').val(),
                                    phone: f.find('input[name="c_mobile"]').val(),
                                    first_name: f.find('input[name="c_name"]').val(),
                                    last_name: "",
                                }
                            });
                        }
                    } 
                    else { f.find('.smo-msg').html('<span style="color:red">'+r.data+'</span>'); b.prop('disabled', false).text('<?php echo esc_js( __( 'Send', 'social-media-orders' ) ); ?>'); }
                }).fail(function(){ alert('Error'); b.prop('disabled', false); });
            });
            $(document).on('submit', '.smo-step-2', function(e) {
                e.preventDefault(); var f = $(this), w = f.closest('.smo-form-wrapper'), b = f.find('button'); b.prop('disabled', true).text('...');
                $.post('<?php echo admin_url('admin-ajax.php'); ?>', f.serialize(), function(r) {
                    if(r.success) { 
                        w.find('.smo-intermediate-message').slideUp(); 
                        f.slideUp(); 
                        w.find('.smo-final-message').slideDown(); 
                    } 
                    else { f.find('.smo-msg').html('<span style="color:red">'+r.data+'</span>'); b.prop('disabled', false).text('<?php echo esc_js( __( 'Submit', 'social-media-orders' ) ); ?>'); }
                }).fail(function(){ alert('Error'); b.prop('disabled', false); });
            });
        });
    });
    </script>
    <?php
}

/**
 * 6. AJAX & NOTIFICATIONS
 * --------------------------------------
 */
function smo_verify_recaptcha($token) {
    if( empty($token) ) return false;
    $response = wp_remote_post('https://www.google.com/recaptcha/api/siteverify', array('body' => array( 'secret' => SMO_RECAPTCHA_SECRET_KEY, 'response' => $token )));
    $result = json_decode( wp_remote_retrieve_body($response) );
    return ( isset($result->success) && $result->success && $result->score >= 0.5 );
}

function smo_send_notification( $entry_id ) {
    $opt = get_option( 'smo_settings' );
    if ( ! isset( $opt['email_enabled'] ) || $opt['email_enabled'] != 1 ) return;

    $client = get_post_meta( $entry_id, '_smo_client_data', true );
    $answers = get_post_meta( $entry_id, '_smo_answers', true );
    $form_id = get_post_meta( $entry_id, '_smo_form_id', true );
    
    $page_title = isset($client['source_title']) ? wp_specialchars_decode( $client['source_title'], ENT_QUOTES ) : '';
    $raw_form_title = wp_specialchars_decode( get_the_title($form_id), ENT_QUOTES );
    $form_title = str_replace('{page_title}', $page_title, $raw_form_title);

    $to = ! empty( $opt['recipient'] ) ? $opt['recipient'] : get_option( 'admin_email' );
    
    $answers_html = '<ul>';
    if ( $answers && is_array( $answers ) ) {
        foreach ( $answers as $a ) {
            $answers_html .= '<li><strong>' . esc_html( $a['question'] ) . ':</strong> ' . nl2br( esc_html( $a['answer'] ) ) . '</li>';
        }
    }
    $answers_html .= '</ul>';

    $subject = str_replace( 
        array( '{name}', '{mobile}', '{form_title}', '{page_title}' ), 
        array( isset($client['name']) ? $client['name'] : '', isset($client['mobile']) ? $client['mobile'] : '', $form_title, $page_title ), 
        $opt['subject'] 
    );
    
    $subject = html_entity_decode( $subject, ENT_QUOTES, 'UTF-8' );

    $body = str_replace( 
        array( '{name}', '{mobile}', '{email}', '{individuals}', '{source_url}', '{form_title}', '{all_answers}', '{page_title}' ), 
        array( 
            isset($client['name']) ? $client['name'] : '', 
            isset($client['mobile']) ? $client['mobile'] : '', 
            isset($client['email']) ? $client['email'] : '', 
            isset($client['individuals']) ? $client['individuals'] : '', 
            isset($client['source_url']) ? $client['source_url'] : '', 
            $form_title, 
            $answers_html,
            $page_title
        ), 
        $opt['body'] 
    );

    $site_name = get_bloginfo( 'name' );
    $headers = array( 
        'Content-Type: text/html; charset=UTF-8',
        'From: ' . $site_name . ' <info@agazatmasr.com>'
    );
    $body = wpautop( $body );
    wp_mail( $to, $subject, $body, $headers );
}

add_action( 'wp_ajax_smo_submit_fixed', 'smo_ajax_fixed' );
add_action( 'wp_ajax_nopriv_smo_submit_fixed', 'smo_ajax_fixed' );
function smo_ajax_fixed() {
    check_ajax_referer( 'smo_frontend_nonce', 'security' );
    if ( SMO_RECAPTCHA_SECRET_KEY !== 'YOUR_SECRET_KEY_HERE' ) {
        if ( ! smo_verify_recaptcha( $_POST['recaptcha_token'] ) ) wp_send_json_error( __( 'Spam Protected', 'social-media-orders' ) );
    }
    $name = sanitize_text_field($_POST['c_name']);
    $entry_id = wp_insert_post(array('post_type' => 'smo_entry', 'post_title' => $name, 'post_status' => 'publish'));
    if( is_wp_error($entry_id) ) wp_send_json_error( 'DB Error' );

    update_post_meta($entry_id, '_smo_form_id', intval($_POST['form_id']));
    update_post_meta($entry_id, '_smo_client_data', array(
        'name'         => $name,
        'mobile'       => sanitize_text_field($_POST['c_mobile']),
        'individuals'  => sanitize_text_field($_POST['c_individuals']),
        'source_url'   => esc_url_raw($_POST['source_url']),
        'source_title' => sanitize_text_field($_POST['source_title'])
    ));

    smo_send_notification( $entry_id );
    wp_send_json_success();
}

add_action( 'wp_ajax_smo_submit_step_1', 'smo_ajax_step_1' );
add_action( 'wp_ajax_nopriv_smo_submit_step_1', 'smo_ajax_step_1' );
function smo_ajax_step_1() {
    check_ajax_referer( 'smo_frontend_nonce', 'security' );
    if ( SMO_RECAPTCHA_SECRET_KEY !== 'YOUR_SECRET_KEY_HERE' ) {
        if ( ! smo_verify_recaptcha( $_POST['recaptcha_token'] ) ) wp_send_json_error( __( 'Spam Protected', 'social-media-orders' ) );
    }
    $name = sanitize_text_field($_POST['c_name']);
    $entry_id = wp_insert_post(array('post_type' => 'smo_entry', 'post_title' => $name, 'post_status' => 'publish'));
    if( is_wp_error($entry_id) ) wp_send_json_error( 'DB Error' );

    update_post_meta($entry_id, '_smo_form_id', intval($_POST['form_id']));
    update_post_meta($entry_id, '_smo_client_data', array(
        'name'         => $name, 
        'email'        => sanitize_email($_POST['c_email']), 
        'mobile'       => sanitize_text_field($_POST['c_mobile']),
        'source_url'   => esc_url_raw($_POST['source_url']),
        'source_title' => sanitize_text_field($_POST['source_title'])
    ));
    wp_send_json_success( array( 'entry_id' => $entry_id ) );
}

add_action( 'wp_ajax_smo_submit_step_2', 'smo_ajax_step_2' );
add_action( 'wp_ajax_nopriv_smo_submit_step_2', 'smo_ajax_step_2' );
function smo_ajax_step_2() {
    check_ajax_referer( 'smo_frontend_nonce', 'security' );
    $entry_id = intval($_POST['entry_id']);
    $answers = array();
    foreach($_POST as $key => $val) {
        if(strpos($key, 'q_') === 0 && strpos($key, 'q_label_') === false) {
            $idx = str_replace('q_', '', $key);
            $label = isset($_POST['q_label_'.$idx]) ? sanitize_text_field($_POST['q_label_'.$idx]) : 'Question';
            $answers[] = array( 'question' => $label, 'answer' => is_array($val) ? implode(', ', map_deep($val, 'sanitize_text_field')) : sanitize_textarea_field($val) );
        }
    }
    update_post_meta($entry_id, '_smo_answers', $answers);
    
    smo_send_notification( $entry_id );

    wp_send_json_success();
}