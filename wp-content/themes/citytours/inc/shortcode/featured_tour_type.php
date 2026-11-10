<?php
// Create the shortcode
function featured_tour_type_shortcode($atts) 
{
    // Parse attributes
    $atts = shortcode_atts(array(
        // 'title' => 'Featured tour_type',
        'posts_per_page' => $atts['posts_per_page'] ?? 4,
    ), $atts);

    $cache_key = 'featured_tour_types_' . md5(serialize($atts));
    $tourTypes = get_transient($cache_key);
    if (false === $tourTypes) {
    $tourTypes = get_terms(array(
        'taxonomy' => 'tour_type',
        'hide_empty' => false,
        'number' => $atts['posts_per_page'],  // Limit to 4 tour_type
        'offset' => 0,  // Start from first result
        'meta_query' => array(      // Filter by custom field
            array(
                'key'   => 'featured', // Custom field key
                'value' => '1',       // Custom field value
            ),
        ),
    ));
        if (!is_wp_error($tourTypes) && !empty($tourTypes)) {
            set_transient($cache_key, $tourTypes, HOUR_IN_SECONDS);
        }
    }

    if (is_wp_error($tourTypes) || empty($tourTypes)) {
        return '<p>No tour_type found.</p>';
    }
    // Start output buffering
    ob_start();
    ?>
    <div class="destinations">
        <!-- <h2>Top destinations</h2> -->
        <div class="destination-grid">
        <?php foreach ($tourTypes as $tourType) : 
            // Get district thumbnail
            $thumbnail_id = get_term_meta($tourType->term_id, 'tour_type_image', true);
            // $image_url = $thumbnail_id ? wp_get_attachment_image_url($thumbnail_id, 'medium') : '';
            $image_url = $thumbnail_id ? wp_get_attachment_image_url($thumbnail_id, 'large') : '';
            $district_link = get_term_link($tourType);
        ?>
        
        <a href="<?php echo esc_url($district_link); ?>">
            
                <div class="destination-card">
                <img src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr($tourType->name); ?>">
                <div class="overlay">
                    <h3><?php echo esc_attr($tourType->name); ?></h3>
                    <div class="desc d-flex align-items-center justify-content-center flex-wrap multi">
                        <?php echo esc_attr($tourType->count); ?> &nbsp; <?php esc_html_e('Tour', 'citytours') ?>
                    </div>
                </div>
                </div>
        </a>
        <?php endforeach; ?>
    </div>
    <?php
    return ob_get_clean();
}

add_shortcode('featured_tour_type', 'featured_tour_type_shortcode');

// Add meta box for tour_type taxonomy
add_action('tour_type_add_form_fields', 'tour_type_custom_meta_box_add');
add_action('tour_type_edit_form_fields', 'tour_type_custom_meta_box_edit');
add_action('edited_tour_type', 'save_tour_type_custom_meta');
add_action('created_tour_type', 'save_tour_type_custom_meta');

function tour_type_custom_meta_box_add($taxonomy) {
    echo '<div class="form-field">';
    echo '<label for="featured">Featured</label>';
    echo '<input type="checkbox" name="featured" id="featured" value="1">';
    echo '<p class="description">Mark this tour type as featured.</p>';

    echo '<label for="image">Image</label>';
    echo '<input type="hidden" id="tour_type_image" name="tour_type_image" class="custom_upload_image">';
    echo '<button class="upload_image_button button">Upload Image</button>';
    echo '<div class="image-preview"></div>';
    echo '<p class="description">Upload an image for this tour_type.</p>';
    echo '</div>';
}

function tour_type_custom_meta_box_edit($term) {
    $featured = get_term_meta($term->term_id, 'featured', true);
    $image_id = get_term_meta($term->term_id, 'tour_type_image', true);
    
    echo '<tr class="form-field">';
    echo '<th scope="row"><label for="featured">Featured</label></th>';
    echo '<td>';
    echo '<input type="checkbox" name="featured" id="featured" value="1"' . checked($featured, 1, false) . '> ';
    echo '<p class="description">Mark this tour_type as featured.</p>';
    echo '</td>';
    echo '</tr>';

    echo '<tr class="form-field">';
    echo '<th scope="row"><label for="image">Image</label></th>';
    echo '<td>';
    echo '<input type="hidden" id="tour_type_image" name="tour_type_image" class="custom_upload_image" value="' . esc_attr($image_id) . '">';
    echo '<button class="upload_image_button button">Upload Image</button>';
    echo '<div class="image-preview">';
    if (!empty($image_id)) {
        echo wp_get_attachment_image($image_id, 'thumbnail');
    }
    echo '</div>';
    echo '<p class="description">Upload an image for this tour_type.</p>';
    echo '</td>';
    echo '</tr>';
}

function save_tour_type_custom_meta($term_id) {
    // Featured status
    $is_featured = isset($_POST['featured']) ? 1 : 0;
    update_term_meta($term_id, 'featured', $is_featured);

    // Image upload
    if (isset($_POST['tour_type_image'])) {
        $image_id = absint($_POST['tour_type_image']);
        update_term_meta($term_id, 'tour_type_image', $image_id);
    }
}

function enqueue_media_uploader_tour_type_scripts() {
    // Enqueue WordPress media scripts
    wp_enqueue_media();

    // Enqueue custom script
    wp_enqueue_script(
        'tour_type-meta-box', // Handle
        get_template_directory_uri() . '/js/tour_type_meta.js', // Script URL
        array('jquery'), // Dependencies
        '1.0', // Version
        true // Load in footer
    );
}
add_action('admin_enqueue_scripts', 'enqueue_media_uploader_tour_type_scripts');