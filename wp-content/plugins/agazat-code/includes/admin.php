<?php
// Add admin menu
add_action('admin_menu', 'agazat_code_admin_menu');

function agazat_code_admin_menu() {
    add_menu_page(
        'Agazat Code Settings',
        'Agazat Code',
        'manage_options',
        'agazat-code',
        'agazat_code_admin_page',
        'dashicons-editor-code',
        80
    );
}

// Register settings
add_action('admin_init', 'agazat_code_register_settings');

function agazat_code_register_settings() {
    register_setting('agazat_code_settings', 'agazat_code_head');
    register_setting('agazat_code_settings', 'agazat_code_after_body');
    register_setting('agazat_code_settings', 'agazat_code_before_body_close');
}

// Admin page content
function agazat_code_admin_page() {
    if (!current_user_can('manage_options')) {
        return;
    }
    ?>
    <div class="wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
        
        <form method="post" action="options.php">
            <?php
            settings_fields('agazat_code_settings');
            do_settings_sections('agazat_code_settings');
            ?>
            
            <h2>Head Section</h2>
            <p>Add code that will be inserted before the closing &lt;/head&gt; tag.</p>
            <textarea name="agazat_code_head" rows="10" cols="100" class="widefat"><?php echo esc_textarea(get_option('agazat_code_head')); ?></textarea>
            
            <h2>After Body Tag</h2>
            <p>Add code that will be inserted right after the opening &lt;body&gt; tag.</p>
            <textarea name="agazat_code_after_body" rows="10" cols="100" class="widefat"><?php echo esc_textarea(get_option('agazat_code_after_body')); ?></textarea>
            
            <h2>Before Body Close</h2>
            <p>Add code that will be inserted before the closing &lt;/body&gt; tag.</p>
            <textarea name="agazat_code_before_body_close" rows="10" cols="100" class="widefat"><?php echo esc_textarea(get_option('agazat_code_before_body_close')); ?></textarea>
            
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

// Add settings link to plugin page
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'agazat_code_add_settings_link');

function agazat_code_add_settings_link($links) {
    $settings_link = '<a href="' . admin_url('admin.php?page=agazat-code') . '">' . __('Settings') . '</a>';
    array_unshift($links, $settings_link);
    return $links;
}