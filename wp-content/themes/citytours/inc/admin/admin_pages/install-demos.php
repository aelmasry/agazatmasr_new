<?php
$theme = wp_get_theme();
if ( $theme->parent_theme ) {
    $template_dir =  basename( get_template_directory() );
    $theme = wp_get_theme( $template_dir );
}

$tgmpa             = TGM_Plugin_Activation::$instance;
$plugins           = TGM_Plugin_Activation::$instance->plugins;

$installed_plugins = get_plugins();

$required_plugins = array();
$view_totals = array(
    'all'      => array(), // Meaning: all plugins which still have open actions.
    'install'  => array(),
    'update'   => array(),
    'activate' => array(),
);

foreach ( $plugins as $slug => $plugin ) {
    if ( $tgmpa->is_plugin_active( $slug ) && false === $tgmpa->does_plugin_have_update( $slug ) ) {
        // No need to display plugins if they are installed, up-to-date and active.
        continue;
    } else {
        $view_totals['all'][ $slug ] = $plugin;

        if ( ! $tgmpa->is_plugin_installed( $slug ) ) {
            $view_totals['install'][ $slug ] = $plugin;
        } else {
            if ( false !== $tgmpa->does_plugin_have_update( $slug ) ) {
                $view_totals['update'][ $slug ] = $plugin;
            }

            if ( $tgmpa->can_plugin_activate( $slug ) ) {
                $view_totals['activate'][ $slug ] = $plugin;
            }
        }
    }
}

$install_index = $update_index = $activate_index = 0;

foreach ( $view_totals as $type => $count ) {
    $size = sizeof( $count );
    if ( $size < 1 ) {
        continue;
    }

    switch ( $type ) {
        case 'install':
            $install_index = $size;
            break;
        case 'update':
            $update_index = $size;
            break;
        case 'activate':
            $activate_index = $size;
            break;
        default:
            break;
    }
}

$is_ready_demo = true;
$plugins_required = true;

$system_status = array();

// Server Memory limit
$system_status['memory_limit']['title'] = __( 'Server Memory Limit:', 'citytours' );

$memory = intval( substr( ini_get('memory_limit'), 0, -1 ) );

if ( $memory < 256 ) {
    $system_status['memory_limit']['note'] = '<mark class="error"><span class="dashicons dashicons-warning"></span>' . sprintf( __( '%s - We recommend setting memory to at least <strong>128MB</strong>. <br /> Please define memory limit in <strong>wp-config.php</strong> file. To learn how, see: <a href="%s" target="_blank">Increasing memory allocated to PHP.</a>', 'citytours' ), ini_get('memory_limit'), 'http://codex.wordpress.org/Editing_wp-config.php#Increasing_memory_allocated_to_PHP' ) . '</mark>';
    $is_ready_demo = false;
} else {
    $system_status['memory_limit']['note'] = '<mark class="yes"><span class="dashicons dashicons-yes"></span>' . ini_get("memory_limit") . '</mark>';
}

// PHP Time Limit
$system_status['time_limit']['title'] = __( 'PHP Time Limit:', 'citytours' );

$time_limit = ini_get('max_execution_time');

if ( $time_limit < 180 ) {
    $system_status['time_limit']['note'] = '<mark class="error"><span class="dashicons dashicons-warning"></span>' . sprintf( __( '%s - We recommend setting max execution time to at least 180. <br /> To import demo content, <strong>300</strong> seconds of max execution time is required.<br />See: <a href="%s" target="_blank">Increasing max execution to PHP</a>', 'citytours' ), $time_limit, 'http://codex.wordpress.org/Common_WordPress_Errors#Maximum_execution_time_exceeded' ) . '</mark>';
    $is_ready_demo = false;
} else {
    $system_status['time_limit']['note'] = '<mark class="yes"><span class="dashicons dashicons-yes"></span>' . $time_limit . '</mark>';
}

// PHP Time Limit
$system_status['upload_size']['title'] = __( 'Max Upload Size:', 'citytours' );

$upload_size = intval( substr( size_format( wp_max_upload_size() ), 0, -1 ) );

if ( $upload_size > 12 ) { 
    $system_status['upload_size']['note'] = '<mark class="yes"><span class="dashicons dashicons-yes"></span>' . size_format( wp_max_upload_size() ) . '</mark>';
} else { 
    $system_status['upload_size']['note'] = '<mark class="error"><span class="dashicons dashicons-warning"></span>  <span>' . size_format( wp_max_upload_size() ) . '</span>.' . __('The recommended value is 12M.') . '</mark>';
    $is_ready_demo = false;
}

// GZip Archive
$system_status['gzip']['title'] = __( 'GZip:', 'citytours' );

if ( class_exists( 'ZipArchive' ) ) {
    $system_status['gzip']['note'] = '<mark class="yes"><span class="dashicons dashicons-yes"></span></mark>';
} else {
    $system_status['gzip']['note'] = '<mark class="error"><span class="dashicons dashicons-warning"></span></mark>';
    $is_ready_demo = false;
}

// WP Remote Post
$system_status['wp_remote_post']['title'] = __( 'WP Remote Post:', 'citytours' );

$response = wp_safe_remote_post( 'https://www.paypal.com/cgi-bin/webscr', array(
    'timeout'     => 60,
    'user-agent'  => 'WooCommerce/2.6',
    'httpversion' => '1.1',
    'body'        => array(
        'cmd'    => '_notify-validate'
    )
) );

if ( ! is_wp_error( $response ) && $response['response']['code'] >= 200 && $response['response']['code'] < 300 ) {
    $system_status['wp_remote_post']['note'] = '<mark class="yes"><span class="dashicons dashicons-yes"></span></mark>';
} else {
    $system_status['wp_remote_post']['note'] = '<mark class="error"><span class="dashicons dashicons-warning"></span>  <span>' . __('wp_remote_post() failed. Some theme features may not work. Please contact your hosting provider.', 'citytours') . '</span></mark>';
    $is_ready_demo = false;
}

?>

<div class="wrap about-wrap citytours-wrap">
    <h1><?php _e( 'Welcome to CityTours!', 'citytours' ); ?></h1>

    <div class="about-text"><?php echo esc_html__( 'CityTours is now installed and ready to use! Read below for additional information. We hope you\'ll enjoy it!', 'citytours' ); ?></div>

    <h2 class="nav-tab-wrapper">
        <?php
        printf( '<a href="%s" class="nav-tab">%s</a>', admin_url( 'admin.php?page=citytours' ), __( 'Welcome', 'citytours' ) );
        printf( '<a href="#" class="nav-tab nav-tab-active">%s</a>', __( 'Tools', 'citytours' ) );
        printf( '<a href="%s" class="nav-tab">%s</a>', admin_url( 'admin.php?page=theme_options' ), __( 'Theme Options', 'citytours' ) );
        ?>
    </h2>

    <div class="citytours-section">

        <div class="citytours-install-plugins">
            <div class="header-section">
                <h2><?php echo __('Recommended Plugins', 'citytours') ?></h2>

                <div class="clear"></div>

                <?php if ($install_index > 1 || $update_index > 1 || $activate_index > 1) : ?>
                    <p class="about-description">
                        <?php
                        if ($install_index > 1) {
                            printf( '<a href="%s" class="button-primary">%s</a>', admin_url( 'themes.php?page=install-required-plugins&plugin_status=install' ), __( "Click here to install plugins all together.", 'citytours' ) );
                        }

                        if ($activate_index > 1) {
                            printf( '<a href="%s" class="button-primary">%s</a>', admin_url( 'themes.php?page=install-required-plugins&plugin_status=activate' ), __( "Click here to activate plugins all together.", 'citytours' ) );
                        }

                        if ($update_index > 1) {
                            printf( '<a href="%s" class="button-primary">%s</a>', admin_url( 'themes.php?page=install-required-plugins&plugin_status=update' ), __( "Click here to update plugins all together.", 'citytours' ) );
                        }
                        ?>

                        <br><br>
                    </p>
                <?php endif; ?>
            </div>

            <div class="feature-section theme-browser rendered">
                <?php 
                foreach ( $plugins as $plugin ) :
                    $class = '';
                    $plugin_status = '';
                    $file_path = $plugin['file_path'];
                    $plugin_action = $this->plugin_link( $plugin );

                    if ( $plugin['required'] ) { 
                        $required_plugins[] = $plugin;
                    }

                    if ( is_plugin_active( $file_path ) ) {
                        $plugin_status = 'active';
                        $class = 'active';
                    } else { 
                        if ( $plugin['required'] ) { 
                            $is_ready_demo = false;
                            $plugins_required = false;
                        }
                    }
                    ?>
                    
                    <div class="theme <?php echo $class; ?>">
                        <div class="theme-wrapper">
                            <div class="theme-screenshot">
                                <img src="<?php echo $plugin['image_url']; ?>" alt="" />

                                <div class="plugin-info">
                                    <?php if ( isset( $installed_plugins[ $plugin['file_path'] ] ) ) : ?>
                                        <?php printf( __( 'Version: %1s', 'citytours' ), $installed_plugins[ $plugin['file_path'] ]['Version'] ); ?>
                                    <?php elseif ( 'bundled' == $plugin['source_type'] ) : ?>
                                        <?php printf( esc_attr__( 'Available Version: %s', 'citytours' ), $plugin['version'] ); ?>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <h3 class="theme-name">
                                <?php if ( 'active' == $plugin_status ) : ?>
                                    <span><?php printf( __( 'Active: %s', 'citytours' ), $plugin['name'] ); ?></span>
                                <?php else : ?>
                                    <?php echo $plugin['name']; ?>
                                <?php endif; ?>
                            </h3>

                            <div class="theme-actions">
                                <?php foreach ( $plugin_action as $action ) { echo $action; } ?>
                            </div>

                            <?php if ( isset( $plugin_action['update'] ) && $plugin_action['update'] ) : ?>
                                <div class="plugin-update">
                                    <span class="dashicons dashicons-update"></span> <?php printf( __( 'Update Available: Version %s', 'citytours' ), $plugin['version'] ); ?>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ( isset( $plugin['required'] ) && $plugin['required'] ) : ?>
                                <div class="plugin-required">
                                    <?php esc_html_e( 'Required', 'citytours' ); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php 
                endforeach; 
                ?>
            </div>
        </div>

        <div class="citytours-theme-demo">
            <div class="header-section">
                <h2><?php echo __('Demo Import', 'citytours') ?></h2>

                <a href="#demo_tab" class="demo-tab-switch active" id="demo_toggle"><?php _e('Importer', 'citytours') ?></a>
                <a href="#status_tab" class="demo-tab-switch <?php if(!$is_ready_demo) echo 'error' ?>" id="status_toggle">
                    <?php 
                    if ( ! $is_ready_demo ) { 
                        echo '<span class="dashicons dashicons-warning"></span>';
                    }
                    ?>
                    <?php _e('System Status', 'citytours') ?>
                </a>
                <div class="clear"></div>
            </div>

            <div id="citytours-install-options" style="display: none;">
                <h3>
                    <span class="theme-name"></span> <?php _e('Install Options', 'citytours') ?>
                </h3>

                <input type="hidden" id="citytours-install-demo-type" value="default"/>

                <label for="citytours-import-options">
                    <input type="checkbox" id="citytours-import-options" value="1" checked="checked"/> <?php _e('Import theme options', 'citytours') ?>
                </label>
                <label for="citytours-reset-menus">
                    <input type="checkbox" id="citytours-reset-menus" value="1" checked="checked"/> <?php _e('Reset menus', 'citytours') ?>
                </label>
                <label for="citytours-reset-widgets">
                    <input type="checkbox" id="citytours-reset-widgets" value="1" checked="checked"/> <?php _e('Reset widgets', 'citytours') ?>
                </label>
                <label for="citytours-import-dummy">
                    <input type="checkbox" id="citytours-import-dummy" value="1" checked="checked"/> <?php _e('Import dummy content', 'citytours') ?>
                </label>
                <label for="citytours-import-widgets">
                    <input type="checkbox" id="citytours-import-widgets" value="1" checked="checked"/> <?php _e('Import widgets', 'citytours') ?>
                </label>

                <p><?php _e('Do you want to install demo? It can also take a minute to complete.', 'citytours') ?></p>

                <button class="button button-primary" id="citytours-import-yes"><?php _e('Yes', 'citytours') ?></button>
                <button class="button" id="citytours-import-no"><?php _e('No', 'citytours') ?></button>
            </div>

            <div id="import-status"></div>
            
            <div id="demo_tab" class="demo-tab theme-browser rendered">
                <div class="import-success importer-notice" style="display: none">
                    <p>
                        <?php echo __('The demo content has been imported successfully.', 'citytours') ?>
                        <a href="<?php echo site_url(); ?>"><?php _e('View Site', 'citytours') ?></a>
                    </p>
                </div>

                <div class="import-error import-failed" style="display: none">
                    <p>
                        <span class="dashicons dashicons-warning"></span>&nbsp;&nbsp;<?php _e('The demo importing process failed. Please check System Status. It will help you understand why you failed.', 'citytours') ?>
                    </p>
                </div>

                <?php if ( ! $is_ready_demo ) : ?>
                    <div class="import-error">
                        <p><span class="dashicons dashicons-warning error"></span>&nbsp;&nbsp;<?php _e('Please check the <a href="javascript:void(0)" class="status_toggle2 error">System Status</a> before importing the demo content to make sure the importing process won’t fail', 'citytours') ?></p>
                    </div>
                <?php endif; ?>

                <div class="demo">
                    <div class="demo-screenshot">
                        <img src="<?php echo CT_TEMPLATE_DIRECTORY_URI . '/img/admin/theme-preview.jpg' ?>" alt="demo preview image">

                        <div class="demo-info">
                            <?php _e('Citytours - Theme Demo', 'citytours') ?>
                        </div>

                        <div class="demo-actions">
                            <a href="#" class="button citytours-install-demo-button" data-demo-id="default"><?php _e('Import Now', 'citytours') ?></a>
                            <a href="http://www.soaptheme.net/wordpress/citytours/" class="button" target="_blank"><?php _e('Preview', 'citytours') ?></a>
                        </div>
                    </div>

                    <?php if ( ! $plugins_required ) : ?>
                    <div class="demo-disabled">
                        <?php echo __('Install the above <strong>Required Plugins</strong><br/> before importing the demo content.'); ?>
                    </div>
                    <?php endif; ?>

                    <div class="demo-importer-loader preview-all"></div>

                    <div class="demo-importer-loader preview-default"><i class="dashicons dashicons-admin-generic"></i></div>
                </div>

                <div class="clear"></div>
            </div>

            <div id="status_tab" class="demo-tab status-holder" style="display: none">
                <table class="widefat" cellspacing="0">
                    <thead>
                        <tr>
                            <th colspan="2"><?php _e( 'CityTours', 'citytours' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><?php _e( 'Current Version:', 'citytours' ); ?></td>
                            <td><?php echo CT_VERSION; ?></td>
                        </tr>
                    </tbody>
                </table>

                <table class="widefat" cellspacing="0">
                    <thead>
                        <tr>
                            <th colspan="2"><?php _e('Required Plugins', 'citytours') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        foreach ( $required_plugins as $plugin) {
                            $file_path = $plugin['file_path'];
                            $active = is_plugin_active( $file_path );
                            ?>

                            <tr>
                                <td><?php echo $plugin['name'] ?></td>
                                <td>
                                    <?php if ( $active ) { ?>
                                        <mark class="yes"><span class="dashicons dashicons-yes"></span></mark>
                                    <?php } else { ?>
                                        <mark class="error"><span class="dashicons dashicons-warning"></span> <span><?php _e('Not Installed/Activated.') ?></span></mark>
                                    <?php } ?>
                                </td>
                            </tr>

                            <?php
                        } 
                        ?>
                    </tbody>
                </table>

                <table class="widefat" cellspacing="0">
                    <thead>
                        <tr>
                            <th colspan="2"><?php _e( 'Server Environment', 'citytours' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><?php _e( 'Server Info:', 'citytours' ); ?></td>
                            <td><?php echo esc_html( $_SERVER['SERVER_SOFTWARE'] ); ?></td>
                        </tr>

                        <?php foreach ( $system_status as $conf_value ) {?>
                            <tr>
                                <td><?php echo $conf_value['title'] ?></td>
                                <td><?php echo $conf_value['note'] ?></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

</div>