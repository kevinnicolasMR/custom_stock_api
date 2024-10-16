<?php
require_once plugin_dir_path(__FILE__) . 'api-connection.php';
require_once plugin_dir_path(__FILE__) . 'functions/click-handlers.php';
require_once plugin_dir_path(__FILE__) . 'functions/folder-content.php';
require_once plugin_dir_path(__FILE__) . 'functions/folder-search.php';

function generate_shortcode($folderIds) {
    if (!is_array($folderIds) || empty($folderIds)) {
        return ''; // Retorna vacío si no es un array o está vacío
    }
    $ids = implode(',', array_map('esc_attr', $folderIds));
    return '[drive_folders ids="' . esc_attr($ids) . '"]';
}

function enqueue_custom_assets() {
    wp_enqueue_style('custom-style', plugin_dir_url(__FILE__) . 'custom_css/menu.css');
    wp_enqueue_script('jquery');
    wp_enqueue_script('custom-script', plugin_dir_url(__FILE__) . 'menu.js', array('jquery'), null, true);

    $ajax_script = 'var ajaxurl = "' . admin_url('admin-ajax.php') . '";';
    wp_add_inline_script('custom-script', $ajax_script);
}
add_action('wp_enqueue_scripts', 'enqueue_custom_assets');

add_shortcode('drive_folders', 'display_drive_folders_menu');
