<?php
// Create the shortcode
function featured_districts_shortcode($atts)
{
    // Parse attributes
    $atts = shortcode_atts(array(
        // 'title' => 'Featured Districts',
        'posts_per_page' => $atts['posts_per_page'] ?? 4,
        'columns' => $atts['columns'] ?? 4,
    ), $atts);

    // Generate a unique cache key based on the attributes
    $cache_key = 'featured_districts_' . md5(serialize($atts));
    
    $districts = get_transient($cache_key);
    
    if (false === $districts) {
        $districts = get_terms(array(
            'taxonomy' => 'district',
            'hide_empty' => false,
            'number' => $atts['posts_per_page'],  // Limit to 4 districts
            'offset' => 0,  // Start from first result
            'meta_query' => array(      // Filter by custom field
                array(
                    'key'   => 'featured', // Custom field key
                    'value' => '1',       // Custom field value
                ),
            ),
        ));

        // Cache the results for 1 hour if they are successfully fetched
        if (!is_wp_error($districts) && !empty($districts)) {
            set_transient($cache_key, $districts, HOUR_IN_SECONDS);
        }
    }

    if (is_wp_error($districts) || empty($districts)) {
        return '<p>No districts found.</p>';
    }

    // Start output buffering
    ob_start();
?>


    <div class="destinations">
        <!-- <h2>Top destinations</h2> -->
        <div class="destination-grid">
            <?php foreach ($districts as $district) :
                // Get district thumbnail
                $thumbnail_id = get_term_meta($district->term_id, 'district_image', true);
                // $image_url = $thumbnail_id ? wp_get_attachment_image_url($thumbnail_id, 'medium') : '';
                $image_url = $thumbnail_id ? wp_get_attachment_image_url($thumbnail_id, 'large') : '';
                $district_link = get_term_link($district);
            ?>

                <a href="<?php echo esc_url($district_link); ?>">

                    <div class="destination-card">
                        <img src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr($district->name); ?>">
                        <div class="overlay">
                            <h3><?php echo esc_attr($district->name); ?></h3>
                            <div class="desc d-flex align-items-center justify-content-center flex-wrap multi">
                                <?php echo esc_attr($district->count); ?> &nbsp; <?php esc_html_e('Hotel', 'citytours') ?>
                            </div>
                            
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>

    </div>

<?php
    return ob_get_clean();
}
add_shortcode('featured_districts', 'featured_districts_shortcode');

// Add meta box for district taxonomy
add_action('district_add_form_fields', 'district_custom_meta_box_add');
add_action('district_edit_form_fields', 'district_custom_meta_box_edit');
add_action('edited_district', 'save_district_custom_meta');
add_action('created_district', 'save_district_custom_meta');

function district_custom_meta_box_add()
{
    echo '<div class="form-field">';
    echo '<label for="featured">Featured</label>';
    echo '<input type="checkbox" name="featured" id="featured" value="1">';
    echo '<p class="description">Mark this district as featured.</p>';

    echo '<label for="district_image">District Image</label>';
    echo '<input type="hidden" id="district_image" name="district_image" class="custom_upload_image">';
    echo '<button class="upload_image_button button">Upload Image</button>';
    echo '<div class="image-preview"></div>';
    echo '<p class="description">Upload an image for this district.</p>';
    echo '</div>';
}

function district_custom_meta_box_edit($term)
{
    $featured = get_term_meta($term->term_id, 'featured', true);
    $image_id = get_term_meta($term->term_id, 'district_image', true);

    echo '<tr class="form-field">';
    echo '<th scope="row"><label for="featured">Featured District</label></th>';
    echo '<td>';
    echo '<input type="checkbox" name="featured" id="featured" value="1"' . checked($featured, 1, false) . '> ';
    echo '<p class="description">Mark this district as featured.</p>';
    echo '</td>';
    echo '</tr>';

    echo '<tr class="form-field">';
    echo '<th scope="row"><label for="district_image">District Image</label></th>';
    echo '<td>';
    echo '<input type="hidden" id="district_image" name="district_image" class="custom_upload_image" value="' . esc_attr($image_id) . '">';
    echo '<button class="upload_image_button button">Upload Image</button>';
    echo '<div class="image-preview">';
    if (!empty($image_id)) {
        echo wp_get_attachment_image($image_id, 'thumbnail');
    }
    echo '</div>';
    echo '<p class="description">Upload an image for this district.</p>';
    echo '</td>';
    echo '</tr>';
}

function save_district_custom_meta($term_id)
{
    // Featured status
    $is_featured = isset($_POST['featured']) ? 1 : 0;
    update_term_meta($term_id, 'featured', $is_featured);

    // Image upload
    if (isset($_POST['district_image'])) {
        $image_id = absint($_POST['district_image']);
        update_term_meta($term_id, 'district_image', $image_id);
    }
}

function enqueue_media_uploader_district_image()
{
    // Enqueue WordPress media scripts
    wp_enqueue_media();

    // Enqueue custom script
    wp_enqueue_script(
        'district-meta-box', // Handle
        get_template_directory_uri() . '/js/district-meta.js', // Script URL
        array('jquery'), // Dependencies
        '1.0', // Version
        true // Load in footer
    );
}
add_action('admin_enqueue_scripts', 'enqueue_media_uploader_district_image');
