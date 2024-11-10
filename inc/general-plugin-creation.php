<?php
require_once plugin_dir_path(__FILE__) . 'api-connection.php';
require_once plugin_dir_path(__FILE__) . 'functions/click-handlers.php';
require_once plugin_dir_path(__FILE__) . 'functions/folder-content.php';
require_once plugin_dir_path(__FILE__) . 'functions/folder-search.php';

function generate_shortcode($folderIds) {
    if (!is_array($folderIds) || empty($folderIds)) {
        return ''; 
    }
    $ids = implode(',', array_map('esc_attr', $folderIds));
    return '[drive_folders ids="' . esc_attr($ids) . '"]';
}

function enqueue_custom_assets() {
    wp_enqueue_script('jquery'); 

    // Scripts del plugin cargados al final para no afectar carga inicial de la página
    wp_enqueue_script('custom-script', plugin_dir_url(__FILE__) . 'functions/js/menu.js', array('jquery'), null, true);
    wp_enqueue_script('audio-iframe-script', plugin_dir_url(__FILE__) . 'functions/js/audio-iframe.js', array('jquery'), null, true);
    wp_enqueue_script('image-popup-script', plugin_dir_url(__FILE__) . 'functions/js/popup-preview.js', array('jquery'), null, true);
    wp_enqueue_script('clickable-folder-script', plugin_dir_url(__FILE__) . 'functions/js/clickable-folder.js', array('jquery'), null, true);
    wp_enqueue_script('filter-search-content', plugin_dir_url(__FILE__) . 'functions/js/filter-search-content.js', array('jquery'), null, true);
    wp_enqueue_script('button-load-scripts', plugin_dir_url(__FILE__) . 'functions/js/button-load.js', array('jquery'), null, true);
    wp_enqueue_script('breadcrumb-navigation-scripts', plugin_dir_url(__FILE__) . 'functions/js/breadcrumb-navigation.js', array('jquery'), null, true);

    // Estilos cargados como de costumbre
    wp_enqueue_style('custom-style', plugin_dir_url(__FILE__) . 'custom_css/menu.css');
    wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css', array(), null);

    wp_localize_script('custom-script', 'ajax_object', array(
        'ajax_url' => admin_url('admin-ajax.php')
    ));
}


add_action('wp_enqueue_scripts', 'enqueue_custom_assets');

// Shortcode para mostrar las carpetas de Google Drive
add_shortcode('drive_folders', 'display_drive_folders_menu');
?>
