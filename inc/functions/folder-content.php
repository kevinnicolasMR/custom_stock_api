<?php
require_once plugin_dir_path(__FILE__) . '../api-connection.php';

function get_folder_content() {
    // Verificar que se proporciona un ID de carpeta
    if (!isset($_POST['folder_id'])) {
        wp_send_json_error('No se proporcionó un ID de carpeta.', 400); // Error 400: Solicitud incorrecta
        wp_die();
    }

    // Sanitizar el ID de la carpeta
    $folderId = sanitize_text_field($_POST['folder_id']);
    
    // Conectar a Google Drive
    try {
        $driveService = connect_to_google_drive();
    } catch (Exception $e) {
        wp_send_json_error('Error al conectar con Google Drive: ' . esc_html($e->getMessage()), 500); // Error 500: Error del servidor
        wp_die();
    }

    try {
    // Consulta para obtener SOLO archivos multimedia dentro de la carpeta (imágenes/videos)
    $query = sprintf("'%s' in parents and (mimeType contains 'image/' or mimeType contains 'video/')", $folderId);

    // Listar los archivos dentro de la carpeta (solo multimedia)
    $results = $driveService->files->listFiles(array('q' => $query, 'fields' => 'files(id, name, mimeType, thumbnailLink, webViewLink)'));

    // Comprobar si hay archivos
    if (count($results->files) == 0) {
        // Mensaje cuando no hay contenido en la carpeta
        $output = '<div class="media-preview"><p>No se encontraron archivos multimedia en esta carpeta.</p></div>';
        wp_send_json_success($output); // Enviar respuesta exitosa con el mensaje
        wp_die();
    } else {
        $output = '<div class="media-preview">';  // Contenedor general para todos los archivos
        foreach ($results->files as $file) {
            $output .= '<div class="media-item" style="display:inline-block; margin:10px;">';

            // Si el archivo es una imagen
            if (strpos($file->mimeType, 'image') !== false) {
                // Comprobar si thumbnailLink está disponible
                if (!empty($file->thumbnailLink)) {
                    $output .= '<img src="' . esc_url($file->thumbnailLink) . '" style="max-width: 300px; max-height: 300px;" alt="' . esc_attr($file->name) . '" />';
                } else {
                    $output .= '<p>No se pudo mostrar la imagen: ' . esc_html($file->name) . '</p>';
                }
            } 
            // Si el archivo es un video
            elseif (strpos($file->mimeType, 'video') !== false) {
                // Comprobar si webViewLink está disponible
                if (!empty($file->webViewLink)) {
                    $output .= '<video controls style="max-width: 300px; max-height: 300px;">
                                    <source src="' . esc_url($file->webViewLink) . '" type="' . esc_attr($file->mimeType) . '">
                                    Tu navegador no soporta la previsualización de este video.
                                </video>';
                } else {
                    $output .= '<p>No se pudo reproducir el video: ' . esc_html($file->name) . '</p>';
                }
            }

            // Cerrar el contenedor del archivo individual
            $output .= '</div>';
        }
        $output .= '</div>';  // Cerrar el contenedor general de todos los archivos

        // Enviar la respuesta con éxito
        wp_send_json_success($output);
    }
} catch (Exception $e) {
    // Capturar y enviar cualquier error que ocurra al intentar obtener los archivos
    wp_send_json_error('Error al obtener archivos multimedia: ' . esc_html($e->getMessage()), 500);
}


    wp_die(); // Termina la ejecución correctamente después de la respuesta
}

// Registrar la función para manejar solicitudes AJAX autenticadas y no autenticadas
add_action('wp_ajax_get_folder_content', 'get_folder_content');
add_action('wp_ajax_nopriv_get_folder_content', 'get_folder_content');
