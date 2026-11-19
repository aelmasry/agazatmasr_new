<?php

namespace Ali;

if (!class_exists('Helper')) {
    class Helper
    {
        public static function slugify(string $text): string
        {
            $text = preg_replace('~[^\pL\d]+~u', '-', $text);
            $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
            $text = preg_replace('~[^-\w]+~', '', $text);
            $text = trim($text, '-');
            $text = preg_replace('~-+~', '-', $text);
            return empty($text) ? 'n-a' : strtolower($text);
        }

        public static function dump($data): void
        {
            echo "<pre>";
            if (is_array($data) || is_object($data)) {
                print_r($data);
            } else {
                var_dump($data);
            }
            echo "</pre>";
        }

        public static function dd($data): void
        {
            self::dump($data);
            die;
        }

        public static function isWPMLEnabled(): bool
        {
            return function_exists('icl_object_id');
        }

        public static function getCurrentUrl(): string
        {
            global $wp;
            return home_url($wp->request);
        }

        public static function isBlogPage(): bool
        {
            return is_home() || is_singular('post');
        }

        public static function parseYoutubeURL(string $url, string $output = 'iframe'): string
        {
            $match = [];
            $videoId = false;

            if (preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $url, $match)) {
                $videoId = $match[1];
            }

            return $videoId ? 'https://www.youtube.com/embed/' . $videoId : $url;
        }

        public static function encodeURI(string $uri): string
        {
            return preg_replace_callback("{[^0-9a-z_.!~*'();,/?:@&=+$#-]}i", function ($m) {
                return sprintf('%%%02X', ord($m[0]));
            }, $uri);
        }

        public static function getUserIP(): string
        {
            $ip = $_SERVER['HTTP_CF_CONNECTING_IP'] ?? $_SERVER['REMOTE_ADDR'];
            if (filter_var($ip, FILTER_VALIDATE_IP)) {
                return $ip;
            }
            return '0.0.0.0';
        }

        public static function Pagination($query = null): void
        {
            global $wp_query;
            $paged = max(1, get_query_var('paged'));
            $total_pages = $query ? $query->max_num_pages : $wp_query->max_num_pages;

            if ($total_pages > 1) {
                $args = [
                    'type' => 'list',
                    'current' => $paged,
                    'total' => $total_pages,
                    'prev_text' => esc_html__('Prev', 'robin'),
                    'next_text' => esc_html__('Next', 'robin'),
                    'end_size' => 1,
                    'mid_size' => 2,
                ];

                echo str_replace("<ul class='page-numbers'>", '<ul class="pagination">', paginate_links($args));
            }

            wp_reset_postdata();
        }

        public static function sanitizeInput(string $input): string
        {
            return filter_var(trim($input), FILTER_SANITIZE_STRING);
        }

        public static function generateRandomString(int $length = 10): string
        {
            return bin2hex(random_bytes($length / 2));
        }

        public static function formatDate($date, string $format = 'Y-m-d H:i:s'): string
        {
            return date($format, strtotime($date));
        }

        public static function isUserLoggedIn(): bool
        {
            return is_user_logged_in();
        }

        public static function redirectTo(string $url): void
        {
            wp_redirect($url);
            exit;
        }
    }
}
