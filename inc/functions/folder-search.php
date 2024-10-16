<?php
// Asegúrate de incluir la conexión con Google API si es necesario
require_once plugin_dir_path(__FILE__) . '../api-connection.php';

// Modificación en la función render_subfolders
function render_subfolders($driveService, $folderId, $level = 0) {
    $output = ''; // Inicia la salida vacía
    try {
        $query = sprintf("'%s' in parents and mimeType = 'application/vnd.google-apps.folder'", $folderId);
        $subFolders = $driveService->files->listFiles(array('q' => $query, 'fields' => 'files(id, name)'));
        foreach ($subFolders->files as $subFolder) {
            $output .= '<div class="subfolder level-' . esc_attr($level) . ' hideContentMenu" data-folder-id="' . esc_attr($subFolder->id) . '">';
            $output .= '<p>' . esc_html($subFolder->name) . '</p>';
            if ($level < 2) {
                $output .= render_subfolders($driveService, $subFolder->id, $level + 1); 
            }
            $output .= '</div>'; // Cierra el div de la subcarpeta
        }
    } catch (Exception $e) {
        $output .= '<p>Error al obtener subcarpetas: ' . esc_html($e->getMessage()) . '</p>';
    }
    return $output; // Retorna el contenido generado
}

// Función para mostrar las carpetas de Google Drive
function display_drive_folders_menu($atts) {
    $atts = shortcode_atts(array('ids' => ''), $atts, 'drive_folders');
    if (empty($atts['ids'])) {
        return '<p>No se han proporcionado carpetas para mostrar.</p>';
    }
    $driveService = connect_to_google_drive();
    $folderIds = explode(',', $atts['ids']);
    $output = '<div id="drive-folders-container"><div id="folder-menu">';

    foreach ($folderIds as $folderId) {
        $folderId = trim($folderId);
        try {
            $folder = $driveService->files->get($folderId, array('fields' => 'id, name'));
            $output .= '<div class="subfolder level-0" data-folder-id="' . esc_attr($folderId) . '">';
            $output .= '<p>' . esc_html($folder->name) . '</p>';  
            $output .= render_subfolders($driveService, $folderId, 1);
            $output .= '</div>';  
        } catch (Exception $e) {
            $output .= '<p>Error al obtener carpeta: ' . esc_html($e->getMessage()) . '</p>';
        }
    }
    
    $output .= '</div><div id="folder-content"></div></div>';
    return $output;
}
