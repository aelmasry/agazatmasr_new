<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

/*
 * Blog search form widget
 */
if (!class_exists('BlogSearchWidget')) :
    class BlogSearchWidget extends WP_Widget
    {

        public function __construct()
        {
            $widget_ops = array(
                'classname' => 'blog_search',
                'description' => __('Display blog search form.', 'citytours'),
            );

            parent::__construct('blog_search_form', __('Blog Search Form', 'citytours'), $widget_ops);
        }

        public function widget($args, $instance)
        {
            // add custom class contact box
            // global $ct_options;

            extract($args);

            if (strpos($before_widget, 'class') === false) {
                $before_widget = str_replace('>', 'class="' . 'btn_map' . '"', $before_widget);
            } else {
                $before_widget = str_replace('class="', 'class="' . 'btn_map' . ' ', $before_widget);
            }

            echo wp_kses_post($before_widget);

            if ( ! empty( $instance['title'] ) ) {
                echo wp_kses_post( $before_title . apply_filters( 'widget_title', $instance['title'] ) . $after_title );
            }

            include('blog_search_form.php'); 

            echo wp_kses_post($after_widget);
        }

        public function update($new_instance, $old_instance)
        {
            // Save widget options
            $instance = $old_instance;
            $instance['title'] = (!empty($new_instance['title'])) ? strip_tags($new_instance['title']) : '';
            return $instance;
        }

        public function form($instance)
        {
            // Output admin widget options form
            $defaults = array('title' => __('Blog Search Form', 'citytours'));
            $instance = wp_parse_args((array) $instance, $defaults); ?>

            <p>
                <label for="<?php echo esc_attr( $this->get_field_id('title') ); ?>"><?php _e( 'Title', 'citytours' ) ?>:</label>
                <input type="text" class="widefat" id="<?php echo esc_attr( $this->get_field_id('title') ); ?>" name="<?php echo esc_attr( $this->get_field_name('title') ); ?>" value="<?php echo esc_attr( $instance['title'] ) ?>" />
            </p>
        <?php
        }
    }
endif;


function registerBlogSearchWidget()
{
    register_widget('BlogSearchWidget');
}

add_action('widgets_init', 'registerBlogSearchWidget');
