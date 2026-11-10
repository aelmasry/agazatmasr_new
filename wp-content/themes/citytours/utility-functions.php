<?php 
include  'Ali/Helper.php';


if (!function_exists('customPagination')) {
    function customPagination($query = null) 
    {
        global $wp_query;

        // Use the provided query or the main query
        $paged = get_query_var('paged') ? get_query_var('paged') : 1;
        $current = max(1, $paged);
        $total_pages = ($query ? $query->max_num_pages : $wp_query->max_num_pages) ?? 0;
        
        // Output pagination links
        if ($total_pages > 1) {
            $args = [
                'type' => 'list',
                'current' => $current,
                'show_all' => false,
                'prev_next' => true,
                'total' => $total_pages,
                'prev_text' => esc_html__('Prev', 'robin'),
                'next_text' => esc_html__('Next', 'robin'),
                'end_size' => 1,
                'mid_size' => 2,
                'add_args' => $_GET ?? [], // Optional: pass custom query parameters
                'screen_reader_text' => esc_html__('Pagination', 'robin'),
            ];

            // Generate pagination links
            $pagination = paginate_links($args);
            if ($pagination) {
                // Add custom classes for styling
                $pagination = str_replace("<ul class='page-numbers'>", '<ul class="pagination">', $pagination);
                $pagination = str_replace('page-numbers', '', $pagination);
                echo $pagination;
            }
        }

        // Reset post data
        wp_reset_postdata();
    }
}


function customLog($message) {
    if (defined('WP_DEBUG') && WP_DEBUG) {
        $log_file = plugin_dir_path(__FILE__) . 'custom-log.log'; // Adjust the path as needed
        $current_time = date('Y-m-d H:i:s');
        if(is_array($message) || is_object($message)){
            $message = print_r($message, true);
        }
        
        $message = "$current_time: $message" . PHP_EOL;
        
        // Append the message to the log file
        file_put_contents($log_file, $message, FILE_APPEND);
    }
}

