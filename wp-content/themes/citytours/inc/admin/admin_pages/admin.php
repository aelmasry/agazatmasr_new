<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

require_once( CT_INC_DIR . '/admin/importer/importer.php' );

class CT_Admin_Page
{
    public function __construct()
    {
        add_action('admin_init', array($this, 'admin_init'));
        add_action('wp_before_admin_bar_render', array($this, 'add_wp_toolbar_menu'));
        add_action('admin_menu', array($this, 'admin_menu'));
        add_action('after_switch_theme', array($this, 'activation_redirect'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
    }


    public function add_wp_toolbar_menu()
    {
        if (current_user_can('edit_theme_options')) {

            $ct_parent_menu_title = '<span class="ab-icon"></span><span class="ab-label">CityTours</span>';

            $this->add_wp_toolbar_menu_item($ct_parent_menu_title, false, admin_url('admin.php?page=citytours'), array('class' => 'citytours-menu'), 'citytours');
            $this->add_wp_toolbar_menu_item(__('Tools', 'citytours'), 'citytours', admin_url('admin.php?page=citytours-demos'));
            $this->add_wp_toolbar_menu_item(__('Theme Options', 'citytours'), 'citytours', admin_url('admin.php?page=theme_options'));
        }
    }


    public function add_wp_toolbar_menu_item($title, $parent = false, $href = '', $custom_meta = array(), $custom_id = '')
    {
        global $wp_admin_bar;

        if (current_user_can('edit_theme_options')) {
            if (!is_super_admin() || !is_admin_bar_showing()) {
                return;
            }

            // Set custom ID
            if ($custom_id) {
                $id = $custom_id;
            } else { // Generate ID based on $title
                $id = strtolower(str_replace(' ', '-', $title));
            }

            // links from the current host will open in the current window
            $meta = strpos($href, site_url()) !== false ? array() : array('target' => '_blank'); // external links open in new tab/window
            $meta = array_merge($meta, $custom_meta);

            $wp_admin_bar->add_node(array(
                'parent' => $parent,
                'id' => $id,
                'title' => $title,
                'href' => $href,
                'meta' => $meta,
            ));
        }
    }


    public function activation_redirect()
    {
        if (current_user_can('edit_theme_options')) {
            header('Location:' . admin_url() . 'admin.php?page=citytours');
        }
    }


    public function admin_init()
    {
        if (current_user_can('edit_theme_options')) {
            if (isset($_GET['citytours-deactivate']) && 'deactivate-plugin' == $_GET['citytours-deactivate']) {
                check_admin_referer('citytours-deactivate', 'citytours-deactivate-nonce');

                $plugins = TGM_Plugin_Activation::$instance->plugins;

                foreach ($plugins as $plugin) {
                    if ($plugin['slug'] == $_GET['plugin']) {
                        deactivate_plugins($plugin['file_path']);
                    }
                }
            }

            if (isset($_GET['citytours-activate']) && 'activate-plugin' == $_GET['citytours-activate']) {
                check_admin_referer('citytours-activate', 'citytours-activate-nonce');

                $plugins = TGM_Plugin_Activation::$instance->plugins;

                foreach ($plugins as $plugin) {
                    if (isset($_GET['plugin']) && $plugin['slug'] == $_GET['plugin']) {
                        activate_plugin($plugin['file_path']);

                        wp_redirect(admin_url('admin.php?page=citytours'));
                        exit;
                    }
                }
            }
        }
    }


    public function admin_menu()
    {

        if (current_user_can('edit_theme_options')) {
            $welcome_screen = add_menu_page('CityTours', 'CityTours', 'administrator', 'citytours', array($this, 'welcome_screen'), 'dashicons-citytours-logo', 3);

            $welcome = add_submenu_page('citytours', __('Welcome', 'citytours'), __('Welcome', 'citytours'), 'administrator', 'citytours', array($this, 'welcome_screen'));
            $demos = add_submenu_page('citytours', __('Tools', 'citytours'), __('Tools', 'citytours'), 'administrator', 'citytours-demos', array($this, 'demos_tab'));
        }
    }


    public function welcome_screen()
    {
        require_once( 'welcome.php' );
    }


    public function demos_tab()
    {
        require_once( 'install-demos.php' );
    }


    public function enqueue_scripts()
    {
        wp_enqueue_script('ct_demo_importer_js', CT_TEMPLATE_DIRECTORY_URI . '/js/admin/demo_importer.js');
        wp_localize_script('ct_demo_importer_js', 'ajaxurl', admin_url('admin-ajax.php'));
    }


    public function plugin_link($item)
    {
        $installed_plugins = get_plugins();

        $item['sanitized_plugin'] = $item['name'];

        $actions = array();

        // We have a repo plugin
        if (!$item['version']) {
            $item['version'] = TGM_Plugin_Activation::$instance->does_plugin_have_update($item['slug']);
        }

        /** We need to display the 'Install' hover link */
        if (!isset($installed_plugins[$item['file_path']])) {
            $actions = array(
                'install' => sprintf(
                        '<a href="%1$s" class="button button-primary" title="Install %2$s">Install</a>', esc_url(wp_nonce_url(
                                        add_query_arg(
                                                array(
                    'page' => urlencode(TGM_Plugin_Activation::$instance->menu),
                    'plugin' => urlencode($item['slug']),
                    'plugin_name' => urlencode($item['sanitized_plugin']),
                    'plugin_source' => urlencode($item['source']),
                    'tgmpa-install' => 'install-plugin',
                    'return_url' => 'citytours-demos',
                                                ), TGM_Plugin_Activation::$instance->get_tgmpa_url()
                                        ), 'tgmpa-install', 'tgmpa-nonce'
                        )), $item['sanitized_plugin']
                ),
            );
        }
        /** We need to display the 'Activate' hover link */ elseif (is_plugin_inactive($item['file_path'])) {
            $actions = array(
                'activate' => sprintf(
                        '<a href="%1$s" class="button button-primary" title="Activate %2$s">Activate</a>', esc_url(add_query_arg(
                                        array(
                    'plugin' => urlencode($item['slug']),
                    'plugin_name' => urlencode($item['sanitized_plugin']),
                    'plugin_source' => urlencode($item['source']),
                    'citytours-activate' => 'activate-plugin',
                    'citytours-activate-nonce' => wp_create_nonce('citytours-activate'),
                                        ), admin_url('admin.php?page=citytours-demos')
                        )), $item['sanitized_plugin']
                ),
            );
        }
        /* We need to display the 'Update' hover link  */ elseif (version_compare($installed_plugins[$item['file_path']]['Version'], $item['version'], '<')) {
            $actions = array(
                'update' => sprintf(
                        '<a href="%1$s" class="button button-primary" title="Install %2$s">Update</a>', wp_nonce_url(
                                add_query_arg(
                                        array(
                    'page' => urlencode(TGM_Plugin_Activation::$instance->menu),
                    'plugin' => urlencode($item['slug']),
                    'tgmpa-update' => 'update-plugin',
                    'plugin_source' => urlencode($item['source']),
                    'version' => urlencode($item['version']),
                    'return_url' => 'citytours-demos',
                                        ), TGM_Plugin_Activation::$instance->get_tgmpa_url()
                                ), 'tgmpa-update', 'tgmpa-nonce'
                        ), $item['sanitized_plugin']
                ),
            );
        } elseif (is_plugin_active($item['file_path'])) {
            $actions = array(
                'deactivate' => sprintf(
                        '<a href="%1$s" class="button button-primary" title="Deactivate %2$s">Deactivate</a>', esc_url(add_query_arg(
                                        array(
                    'plugin' => urlencode($item['slug']),
                    'plugin_name' => urlencode($item['sanitized_plugin']),
                    'plugin_source' => urlencode($item['source']),
                    'citytours-deactivate' => 'deactivate-plugin',
                    'citytours-deactivate-nonce' => wp_create_nonce('citytours-deactivate'),
                                        ), admin_url('admin.php?page=citytours-demos')
                        )), $item['sanitized_plugin']
                ),
            );
        }

        return $actions;
    }


    public function let_to_num($size)
    {
        $l = substr($size, -1);
        $ret = substr($size, 0, -1);
        switch (strtoupper($l)) {
            case 'P':
                $ret *= 1024;
            case 'T':
                $ret *= 1024;
            case 'G':
                $ret *= 1024;
            case 'M':
                $ret *= 1024;
            case 'K':
                $ret *= 1024;
        }
        return $ret;
    }

}

new CT_Admin_Page();
