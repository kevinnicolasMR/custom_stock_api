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
    wp_enqueue_script('jquery'); // Asegúrate de que jQuery se esté cargando

    // Cargar tus scripts personalizados
    wp_enqueue_script('custom-script', plugin_dir_url(__FILE__) . 'functions/js/menu.js', array('jquery'), null, true);
    wp_enqueue_script('custom-file-script', plugin_dir_url(__FILE__) . 'functions/js/custom-file.js', array('jquery'), null, true);
    wp_enqueue_script('image-popup-script', plugin_dir_url(__FILE__) . 'functions/js/popup-preview.js', array('jquery'), null, true);

    // Cargar tus estilos personalizados
    wp_enqueue_style('custom-style', plugin_dir_url(__FILE__) . 'custom_css/menu.css');

    // Cargar Font Awesome desde su CDN
    wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css', array(), null);

    // Localiza el script y pasa la URL de admin-ajax.php a JavaScript
    wp_localize_script('custom-script', 'ajax_object', array(
        'ajax_url' => admin_url('admin-ajax.php')
    ));
}
add_action('wp_enqueue_scripts', 'enqueue_custom_assets');

add_shortcode('drive_folders', 'display_drive_folders_menu');
