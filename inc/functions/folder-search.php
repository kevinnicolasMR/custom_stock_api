<?php
require_once plugin_dir_path(__FILE__) . '../api-connection.php';

define('PARENT_FOLDER_ID', '1VEnaLmB6_EYRKYj5552rXB7shcjesrgM');

function display_drive_folders_menu($atts) {
    // Shortcode attributes
    $atts = shortcode_atts(array('ids' => ''), $atts, 'drive_folders');
    if (empty($atts['ids'])) {
        return '<p>No se han proporcionado carpetas para mostrar.</p>';
    }

    // Output container for the Drive folders
    $output = '<div id="drive-folders-container">';
    
    // Añadir un botón arriba del menú
    $output .= '<div id="top-menu-button">';
    $output .= '<p>Menu de recursos disponibles</p>';
    $output .= '<button id="menu-toggle"><i class="fas fa-bars"></i></button>';  // Este botón tiene un id de "menu-toggle"
    $output .= '</div>';

    // Menú de carpetas
    $output .= '<div id="folder-menu"><p>Cargando menú de carpetas...</p></div>';
    
    // Mueve el div de carga fuera de #folder-content
    $output .= '<div id="loading-message" style="text-align: center; display: none;">
    <div id="loading-spinner"></div>
    <p>Cargando contenido de Google Drive...</p></div>';
    
    $output .= '<div id="folder-content"></div>';
    $output .= '</div>';

    return $output;
}



function enqueue_drive_folder_scripts() {
    // Encola el archivo JavaScript externo
    wp_enqueue_script('drive-folders', plugin_dir_url(__FILE__) . 'js/drive-folders.js', array('jquery'), null, true);

    wp_localize_script('drive-folders', 'ajax_object', array(
        'ajax_url' => admin_url('admin-ajax.php')
    ));
}

add_action('wp_enqueue_scripts', 'enqueue_drive_folder_scripts');

function get_folder_menu() {
    $folderId = isset($_POST['folder_id']) ? sanitize_text_field($_POST['folder_id']) : PARENT_FOLDER_ID;
    $level = isset($_POST['level']) ? intval($_POST['level']) : 0;
    $driveService = connect_to_google_drive();

    try {
        // Iniciar el contenedor adecuado según el nivel
        if ($level === 0) {
            $output = '<div class="level-0-wrapper">';
        } elseif ($level === 1) {
            $output = '<div class="level-1-wrapper">';
        } else {
            $output = '<div class="level-2-wrapper">'; // Nuevo contenedor para level-2
        }

        // Query para obtener carpetas de nivel específico
        $query = sprintf("'%s' in parents and mimeType = 'application/vnd.google-apps.folder'", $folderId);
        $folders = $driveService->files->listFiles(array('q' => $query, 'fields' => 'files(id, name)'));

        foreach ($folders->files as $folder) {
            $folderLevelClass = 'level-' . $level;
            $output .= '<div class="subfolder ' . $folderLevelClass . ' clickable-folder" data-folder-id="' . esc_attr($folder->id) . '">';
            $output .= '<p>' . esc_html($folder->name) . '</p>';
            $output .= '</div>';
        }

        $output .= '</div>'; // Cerrar el contenedor del nivel actual
        wp_send_json_success($output);

    } catch (Exception $e) {
        wp_send_json_error('Error al obtener el menú de carpetas: ' . esc_html($e->getMessage()));
    }
}

add_action('wp_ajax_get_folder_menu', 'get_folder_menu');
add_action('wp_ajax_nopriv_get_folder_menu', 'get_folder_menu');
?>
