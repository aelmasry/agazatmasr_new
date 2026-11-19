<?php
$theme = wp_get_theme();

if ( $theme->parent_theme ) {
    $template_dir =  basename( get_template_directory() );
    $theme = wp_get_theme( $template_dir );
}
?>

<div class="wrap about-wrap citytours-wrap">
    <h1><?php _e( 'Welcome to CityTours!', 'citytours' ); ?></h1>

    <div class="about-text"><?php echo esc_html__( 'CityTours is now installed and ready to use! Read below for additional information. We hope you\'ll enjoy it!', 'citytours' ); ?></div>

    <h2 class="nav-tab-wrapper">
        <?php
        printf( '<a href="#" class="nav-tab nav-tab-active">%s</a>', __( 'Welcome', 'citytours' ) );
        printf( '<a href="%s" class="nav-tab">%s</a>', admin_url( 'admin.php?page=citytours-demos' ), __( 'Tools', 'citytours' ) );
        printf( '<a href="%s" class="nav-tab">%s</a>', admin_url( 'admin.php?page=theme_options' ), __( 'Theme Options', 'citytours' ) );
        ?>
    </h2>

    <div class="citytours-section">
        <p class="about-description">
            <?php printf( __( 'Before you get started, please be sure to always check out <a href="%s" target="_blank">this documentation</a>. We outline all kinds of good information, and provide you with all the details you need to use CityTours.', 'citytours'), 'http://www.soaptheme.net/document/citytours-wp/'); ?>
        </p>
        <p class="about-description">
            <?php printf( __( 'If you are unable to find your answer in our documentation, please contact us via <a href="%s">email</a> directly with your purchase code, site CPanel (or FTP) and admin login info. <br><br>We are very happy to help you and you will get reply from us more faster than you expected.', 'citytours'), 'mailto:soaptheme@gmail.com'); ?>
        </p>
        <p class="about-description">
            <a target="_blank" href="https://themeforest.net/item/citytours-hotel-tour-booking-wordpress-theme/13181652#item-description__changelog" title="<?php _e('Change Logs', 'citytours') ?>"><?php _e('Click here to view change logs.', 'citytours') ?></a>
        </p>


        <p class="about-description">
            Regarding <b>customization services</b> based on CityTours theme or other WordPress projects, please contact us via <a href="mailto:soaptheme@gmail.com" title="Customization Services">soaptheme@gmail.com</a> directly. We have an amazing team to provide customization service who have rich experience and work on reasonable quote.
        </p>
    </div>

    <div class="citytours-thanks">
        <p class="description"><?php _e( 'Thank you for using CityTours! Powered by', 'citytours' ); ?> <a href="https://themeforest.net/user/soaptheme" target="_blank">SoapTheme</a></p>
    </div>
</div>