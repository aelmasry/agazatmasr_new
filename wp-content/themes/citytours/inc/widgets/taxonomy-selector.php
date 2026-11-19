<?php
/**
 * Widget Name: Taxonomy Selection Widget
 * Description: Displays selected items from multiple taxonomies as a block on desktop and slideshow on mobile
 * Version: 1.0.0
 * Author: Ali Salem
 */

// Register the widget
function register_taxonomy_selection_widget() {
    register_widget('Taxonomy_Selection_Widget');
}
add_action('widgets_init', 'register_taxonomy_selection_widget');

// Enqueue necessary scripts and styles
function taxonomy_selection_widget_scripts() {
    wp_enqueue_style('taxonomy-selection-style', get_template_directory_uri() . '/css/taxonomy_selection.css');
    wp_enqueue_script('slick-js', 'https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js', array('jquery'), '1.8.1', true);
    wp_enqueue_style('slick-css', 'https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css');
    wp_enqueue_script('taxonomy-selection-js', get_template_directory_uri() . '/js/taxonomy_selection.js', array('jquery', 'slick-js'), '1.0.0', true);
}
add_action('wp_enqueue_scripts', 'taxonomy_selection_widget_scripts');

class Taxonomy_Selection_Widget extends WP_Widget {
    
    // Widget setup
    public function __construct() {
        parent::__construct(
            'taxonomy_selection_widget',
            'Taxonomy Selection Widget',
            array('description' => 'Display selected items from multiple taxonomies')
        );
    }
    
    // Front-end display
    public function widget($args, $instance) {
        echo $args['before_widget'];
        
        if (!empty($instance['title'])) {
            echo $args['before_title'] . apply_filters('widget_title', $instance['title']) . $args['after_title'];
        }
        
        // Get selected taxonomies and their terms
        $taxonomies = get_taxonomies(array('public' => true), 'objects');
        $selected_terms = array();
        
        foreach ($taxonomies as $taxonomy) {
            $tax_id = $taxonomy->name;
            if (!empty($instance['taxonomy_' . $tax_id]) && !empty($instance['terms_' . $tax_id])) {
                $term_ids = explode(',', $instance['terms_' . $tax_id]);
                if (!empty($term_ids)) {
                    foreach ($term_ids as $term_id) {
                        $term = get_term($term_id);
                        if ($term && !is_wp_error($term)) {
                            $selected_terms[] = array(
                                'id' => $term->term_id,
                                'name' => $term->name,
                                'slug' => $term->slug,
                                'taxonomy' => $tax_id,
                                'taxonomy_label' => $taxonomy->label,
                                'link' => get_term_link($term)
                            );
                        }
                    }
                }
            }
        }
        // Display selected terms
        if (!empty($selected_terms)) {
            ?>
            <div class="taxonomy-selection-container">
                <div class="taxonomy-selection-items">
                    <?php foreach ($selected_terms as $term) : 
                        $thumbnail_id = get_term_meta($term['id'], $term['taxonomy'].'_image', true);
                        $image_url = $thumbnail_id ? wp_get_attachment_image_url($thumbnail_id, 'medium') : '';
                    ?>
                        
                        <div class="taxonomy-item">
                            <div class="taxonomy-item-inner">
                                <a href="<?php echo esc_url($term['link']); ?>">
                                    <div class="destination-card">
                                        <img src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr($term['name']); ?>">
                                        <div class="overlay">
                                            <h3><?php echo esc_attr($term['name']); ?></h3>
                                            <div class="desc d-flex align-items-center justify-content-center flex-wrap multi">
                                                <?php echo esc_attr($term['count']); ?>
                                            </div>
                                        </div>
                                    </div>
                                </a>

                                
                                <?php
                                // Get recent posts from this term
                                $posts = get_posts(array(
                                    'numberposts' => 3,
                                    'tax_query' => array(
                                        array(
                                            'taxonomy' => $term['taxonomy'],
                                            'field' => 'id',
                                            'terms' => $term['id']
                                        )
                                    )
                                ));
                                
                                if ($posts) {
                                    echo '<ul class="term-posts">';
                                    foreach ($posts as $post) {
                                        printf(
                                            '<li><a href="%s">%s</a></li>',
                                            esc_url(get_permalink($post->ID)),
                                            esc_html($post->post_title)
                                        );
                                    }
                                    echo '</ul>';
                                }
                                ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php
        }
        
        echo $args['after_widget'];
    }
    
    // Back-end widget form
    public function form($instance) {
        $title = !empty($instance['title']) ? $instance['title'] : '';
        ?>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('title')); ?>">Title:</label>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>" name="<?php echo esc_attr($this->get_field_name('title')); ?>" type="text" value="<?php echo esc_attr($title); ?>">
        </p>
        
        <p>Select taxonomies and terms to display:</p>
        
        <?php
        $taxonomies = get_taxonomies(array('public' => true), 'objects');
        
        foreach ($taxonomies as $taxonomy) {
            $tax_id = $taxonomy->name;
            $is_selected = !empty($instance['taxonomy_' . $tax_id]);
            $selected_terms = !empty($instance['terms_' . $tax_id]) ? $instance['terms_' . $tax_id] : '';
            ?>
            
            <div class="taxonomy-selection">
                <p>
                    <input type="checkbox" id="<?php echo esc_attr($this->get_field_id('taxonomy_' . $tax_id)); ?>" name="<?php echo esc_attr($this->get_field_name('taxonomy_' . $tax_id)); ?>" value="1" <?php checked($is_selected); ?>>
                    <label for="<?php echo esc_attr($this->get_field_id('taxonomy_' . $tax_id)); ?>">
                        <strong><?php echo esc_html($taxonomy->label); ?></strong>
                    </label>
                </p>
                
                <?php if ($is_selected) : ?>
                    <div class="taxonomy-terms-selection">
                        <?php
                        $terms = get_terms(array('taxonomy' => $tax_id, 'hide_empty' => false));
                        $selected_term_ids = explode(',', $selected_terms);
                        
                        if (!empty($terms)) {
                            echo '<select multiple class="widefat taxonomy-terms-select" id="' . esc_attr($this->get_field_id('terms_select_' . $tax_id)) . '" data-target="' . esc_attr($this->get_field_id('terms_' . $tax_id)) . '">';
                            
                            foreach ($terms as $term) {
                                $selected = in_array($term->term_id, $selected_term_ids) ? 'selected="selected"' : '';
                                echo '<option value="' . esc_attr($term->term_id) . '" ' . $selected . '>' . esc_html($term->name) . '</option>';
                            }
                            
                            echo '</select>';
                            echo '<p><small>Hold Ctrl (or Cmd) to select multiple terms</small></p>';
                        } else {
                            echo '<p>No terms found for this taxonomy.</p>';
                        }
                        ?>
                        <input type="hidden" id="<?php echo esc_attr($this->get_field_id('terms_' . $tax_id)); ?>" name="<?php echo esc_attr($this->get_field_name('terms_' . $tax_id)); ?>" value="<?php echo esc_attr($selected_terms); ?>">
                    </div>
                <?php endif; ?>
            </div>
            
            <?php
        }
        ?>
        
        <script>
            jQuery(document).ready(function($) {
                // Update hidden field when multi-select changes
                $('.taxonomy-terms-select').on('change', function() {
                    var selectedValues = $(this).val() || [];
                    $('#' + $(this).data('target')).val(selectedValues.join(','));
                });
                
                // Show/hide term selection based on checkbox
                $('input[id^="widget-taxonomy_selection_widget"][id$="taxonomy_"]').on('change', function() {
                    var taxonomyTerms = $(this).closest('.taxonomy-selection').find('.taxonomy-terms-selection');
                    if ($(this).is(':checked')) {
                        taxonomyTerms.show();
                    } else {
                        taxonomyTerms.hide();
                    }
                });
            });
        </script>
        
        <style>
            .taxonomy-selection {
                margin-bottom: 15px;
                padding-bottom: 15px;
                border-bottom: 1px solid #eee;
            }
            .taxonomy-terms-selection {
                margin-left: 25px;
            }
        </style>
        <?php
    }
    
    // Sanitize widget form values as they are saved
    public function update($new_instance, $old_instance) {
        $instance = array();
        $instance['title'] = (!empty($new_instance['title'])) ? sanitize_text_field($new_instance['title']) : '';
        
        $taxonomies = get_taxonomies(array('public' => true));
        foreach ($taxonomies as $tax_id) {
            $instance['taxonomy_' . $tax_id] = !empty($new_instance['taxonomy_' . $tax_id]) ? 1 : 0;
            $instance['terms_' . $tax_id] = !empty($new_instance['terms_' . $tax_id]) ? sanitize_text_field($new_instance['terms_' . $tax_id]) : '';
        }
        
        return $instance;
    }
}