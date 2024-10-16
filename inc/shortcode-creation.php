<?php
// Asegúrate de incluir la conexión con Google API (api-connection.php)
require_once plugin_dir_path(__FILE__) . 'api-connection.php';

// Incluir el archivo que maneja los clics
require_once plugin_dir_path(__FILE__) . 'functions/click-handlers.php';

// Generar el shortcode
function generate_shortcode($folderIds) {
    if (!is_array($folderIds) || empty($folderIds)) {
        return ''; // Retorna vacío si no es un array o está vacío
    }
    $ids = implode(',', array_map('esc_attr', $folderIds));
    return '[drive_folders ids="' . esc_attr($ids) . '"]';
}

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




// Función para manejar la solicitud AJAX y devolver el contenido de la carpeta seleccionada
function get_folder_content() {
    // Verificar que se proporciona un ID de carpeta
    if (!isset($_POST['folder_id'])) {
        echo 'No se proporcionó un ID de carpeta.';
        wp_die();
    }

    // Sanitizar el ID de la carpeta que viene de la solicitud
    $folderId = sanitize_text_field($_POST['folder_id']);
    
    // Conectar a Google Drive
    $driveService = connect_to_google_drive();

    try {
        // Consulta para obtener SOLO archivos multimedia dentro de la carpeta (imágenes/videos)
        $query = sprintf("'%s' in parents and (mimeType contains 'image/' or mimeType contains 'video/')", $folderId);

        // Listar los archivos dentro de la carpeta (solo multimedia)
        $results = $driveService->files->listFiles(array('q' => $query, 'fields' => 'files(id, name, mimeType, thumbnailLink, webViewLink)'));

        if (count($results->files) == 0) {
            echo '<p>No se encontraron archivos multimedia en esta carpeta.</p>';
        } else {
            echo '<div class="media-preview">';  // Usamos flexbox para asegurar que estén alineadas correctamente
            foreach ($results->files as $file) {
                echo '<div class="media-item" style="display:inline-block; margin:10px;">';

                // Si el archivo es una imagen
                if (strpos($file->mimeType, 'image') !== false) {
                    // Mostrar imagen con tamaño máximo de 300x300
                    echo '<img src="' . esc_url($file->thumbnailLink) . '" style="max-width: 300px; max-height: 300px;" alt="' . esc_attr($file->name) . '" />';
                } elseif (strpos($file->mimeType, 'video') !== false) {
                    // Para videos
                    echo '<video controls style="max-width: 300px; max-height: 300px;">
                            <source src="' . esc_url($file->webViewLink) . '" type="' . esc_attr($file->mimeType) . '">
                            Tu navegador no soporta la previsualización de este video.
                          </video>';
                }

                // Nombre del archivo y enlace para verlo en Drive
                echo '<p>' . esc_html($file->name) . '</p>';
                echo '<a href="' . esc_url($file->webViewLink) . '" target="_blank">Ver en Drive</a>';
                echo '</div>';
            }
            echo '</div>';
        }
    } catch (Exception $e) {
        // Capturar y mostrar cualquier error que ocurra al intentar obtener los archivos
        echo 'Error al obtener archivos multimedia: ' . esc_html($e->getMessage());
    }

    wp_die(); // Termina la ejecución correctamente después de la respuesta
}

// Registrar la función para manejar solicitudes AJAX autenticadas y no autenticadas
add_action('wp_ajax_get_folder_content', 'get_folder_content');
add_action('wp_ajax_nopriv_get_folder_content', 'get_folder_content');


function enqueue_custom_assets() {
    wp_enqueue_style('custom-style', plugin_dir_url(__FILE__) . 'custom_css/menu.css');
    wp_enqueue_script('jquery');
    wp_enqueue_script('custom-script', plugin_dir_url(__FILE__) . 'menu.js', array('jquery'), null, true);

    // Agregar ajaxurl como una variable global en un script en línea
    $ajax_script = 'var ajaxurl = "' . admin_url('admin-ajax.php') . '";';
    wp_add_inline_script('custom-script', $ajax_script);
}
add_action('wp_enqueue_scripts', 'enqueue_custom_assets');



// Registra el shortcode
add_shortcode('drive_folders', 'display_drive_folders_menu');
