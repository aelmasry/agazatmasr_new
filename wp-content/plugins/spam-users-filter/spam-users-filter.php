<?php
/*
Plugin Name: Spam User Scanner
Description: Scans users for spammy display names and moves them to a custom role with restore and cleanup options.
Version: 1.1
Author: Mohannad & Copilot
*/

// 🔧 Add custom role on activation
register_activation_hook(__FILE__, function() {
    add_role('spam_candidate', 'Spam Candidate', []);
});

// 🧭 Add admin page
add_action('admin_menu', function() {
    add_users_page('Spam Scanner', 'Spam Scanner', 'manage_options', 'spam-scanner', 'render_spam_scanner_page');
});

// 🖥️ Render admin page
function render_spam_scanner_page() {
    $total_users = count_users()['total_users'];
    $last_offset = get_option('spam_scan_last_offset', 0);
    ?>
    <div class="wrap">
        <h1>Spam Scanner</h1>
        <button id="start-scan" class="button button-primary">Start Scan</button>
        <button id="restore-roles" class="button">Restore Original Roles</button>
        <button id="reset-scan" class="button">Reset Scan Progress</button>
        <div id="progress-container" style="margin-top:20px; width:100%; background:#eee;">
            <div id="progress-bar" style="width:0%;height:20px;background:#0073aa;"></div>
        </div>
        <div id="scan-log" style="margin-top:10px;"></div>
    </div>
    <script>
    let offset = <?php echo intval($last_offset); ?>;
    const total = <?php echo $total_users; ?>;
    const log = document.getElementById('scan-log');
    const bar = document.getElementById('progress-bar');

    document.getElementById('start-scan').onclick = function() {
        log.innerHTML = '';
        scanNext();
    };

    function scanNext() {
        fetch(ajaxurl + '?action=scan_user_batch&offset=' + offset)
            .then(res => res.json())
            .then(data => {
                log.innerHTML += `<p>${data.message}</p>`;
                offset++;
                bar.style.width = ((offset / total) * 100).toFixed(2) + '%';
                if (!data.done) scanNext();
            });
    }

    document.getElementById('restore-roles').onclick = function() {
        fetch(ajaxurl + '?action=restore_original_roles')
            .then(res => res.json())
            .then(data => {
                log.innerHTML += `<p>${data.message}</p>`;
            });
    };

    document.getElementById('reset-scan').onclick = function() {
        fetch(ajaxurl + '?action=reset_scan_progress')
            .then(res => res.json())
            .then(data => {
                log.innerHTML += `<p>${data.message}</p>`;
                offset = 0;
                bar.style.width = '0%';
            });
    };
    </script>
    <?php
}

// 🔍 AJAX: Scan one user at a time
add_action('wp_ajax_scan_user_batch', function() {
    $offset = intval($_GET['offset']);
    $users = get_users([
        'number' => 1,
        'offset' => $offset,
        'fields' => ['ID', 'display_name', 'roles']
    ]);

    if (empty($users)) {
        wp_send_json(['done' => true, 'message' => '✅ Scan complete.']);
    }

    $user = $users[0];
    $name = $user->display_name;

    $is_spam = false;
    if (strpos($name, '-') !== false) {
        $words = preg_split('/[\s\-]+/', $name);
        if (count($words) > 3) {
            $is_spam = true;
        }
    }

    if ($is_spam) {
        update_user_meta($user->ID, '_original_role', $user->roles[0]);
        wp_update_user(['ID' => $user->ID, 'role' => 'spam_candidate']);
        $msg = "🚩 User '{$name}' flagged and moved to spam_candidate.";
    } else {
        $msg = "✅ User '{$name}' skipped.";
    }

    update_option('spam_scan_last_offset', $offset);
    wp_send_json(['done' => false, 'message' => $msg]);
});

// 🔄 AJAX: Restore original roles
add_action('wp_ajax_restore_original_roles', function() {
    $users = get_users(['role' => 'spam_candidate']);
    $restored = 0;

    foreach ($users as $user) {
        $original = get_user_meta($user->ID, '_original_role', true);
        if ($original) {
            wp_update_user(['ID' => $user->ID, 'role' => $original]);
            delete_user_meta($user->ID, '_original_role');
            $restored++;
        }
    }

    wp_send_json(['message' => "🔄 Restored {$restored} users to original roles."]);
});

// 🔁 AJAX: Reset scan progress
add_action('wp_ajax_reset_scan_progress', function() {
    delete_option('spam_scan_last_offset');
    wp_send_json(['message' => '🔄 Scan progress reset to start.']);
});

// 🧹 Cleanup user meta when spam user is deleted
add_action('delete_user', function($user_id) {
    $user = get_userdata($user_id);
    if (in_array('spam_candidate', $user->roles)) {
        global $wpdb;
        $wpdb->delete($wpdb->usermeta, ['user_id' => $user_id]);
    }
});

// 🖼️ Optional: Rename role label in admin dropdown
add_filter('editable_roles', function($roles) {
    if (isset($roles['spam_candidate'])) {
        $roles['spam_candidate']['name'] = '🚩 Spam Candidate';
    }
    return $roles;
});


/* Registration date column to users page in admin */

add_filter( 'manage_users_columns', 'add_registration_date_column' );
function add_registration_date_column( $columns ) {
    $columns['registration_date'] = 'Registration Date';
    return $columns;
}

add_filter( 'manage_users_custom_column', 'display_registration_date_column_data', 10, 3 );
function display_registration_date_column_data( $output, $column_name, $user_id ) {
    if ( 'registration_date' === $column_name ) {
        $user_info = get_userdata( $user_id );
        return '<span style="display:inline-block;direction:ltr;">'.date( 'Y-m-d H:i:s', strtotime( $user_info->user_registered ) ).'</span>';
    }
    return $output;
}

add_filter( 'manage_users_sortable_columns', 'make_registration_date_column_sortable' );
function make_registration_date_column_sortable( $columns ) {
    $columns['registration_date'] = 'user_registered';
    return $columns;
}