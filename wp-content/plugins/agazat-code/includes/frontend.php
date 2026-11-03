<?php
// Add code to head section
add_action('wp_head', 'agazat_code_output_head');

function agazat_code_output_head() {
    $head_code = get_option('agazat_code_head');
    if (!empty($head_code)) {
        echo "\n" . '<!-- Agazat Code Head -->' . "\n";
        echo $head_code;
        echo "\n" . '<!-- End Agazat Code Head -->' . "\n";
    }
}

// Add code after body tag
add_action('wp_body_open', 'agazat_code_output_after_body');

function agazat_code_output_after_body() {
    $after_body_code = get_option('agazat_code_after_body');
    if (!empty($after_body_code)) {
        echo "\n" . '<!-- Agazat Code After Body -->' . "\n";
        echo $after_body_code;
        echo "\n" . '<!-- End Agazat Code After Body -->' . "\n";
    }
}

// Add code before closing body tag
add_action('wp_footer', 'agazat_code_output_before_body_close');

function agazat_code_output_before_body_close() {
    $before_body_close_code = get_option('agazat_code_before_body_close');
    if (!empty($before_body_close_code)) {
        echo "\n" . '<!-- Agazat Code Before Body Close -->' . "\n";
        echo $before_body_close_code;
        echo "\n" . '<!-- End Agazat Code Before Body Close -->' . "\n";
    }
}