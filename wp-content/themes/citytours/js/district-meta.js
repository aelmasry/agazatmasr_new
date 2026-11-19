jQuery(document).ready(function($) {
    // Check if wp.media is available
    if (typeof wp === 'undefined' || !wp.media) {
        console.error('wp.media is not available.');
        return;
    }

    // Handle image upload button click
    $('.upload_image_button').on('click', function(e) {
        e.preventDefault();

        var button = $(this);
        var custom_uploader;

        // Create a new media frame
        custom_uploader = wp.media({
            title: 'Choose Image',
            library: {
                type: 'image' // Only allow images
            },
            button: {
                text: 'Select Image' // Button text
            },
            multiple: false // Allow only one image to be selected
        });

        // When an image is selected, run a callback
    //     custom_uploader.on('select', function() {
    //         var attachment = custom_uploader.state().get('selection').first().toJSON();
    //         var field = button.siblings('.custom_upload_image');
    //         var preview = button.siblings('.image-preview');

    //         // Update the hidden input field with the attachment ID
    //         field.val(attachment.id);

    //         // Display the selected image in the preview area
    //         preview.html('<img src="' + attachment.url + '" alt="" style="max-width:150px;" />');
    //     });

    //     // Open the media frame
    //     custom_uploader.open();
    // });
});