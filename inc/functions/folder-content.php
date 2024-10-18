<?php
require_once plugin_dir_path(__FILE__) . '../api-connection.php';

// Agrega esta función para manejar la solicitud AJAX
function get_folder_content() {
    // Verifica si se proporciona el ID de la carpeta
    if (isset($_POST['folder_id'])) {
        $folderId = sanitize_text_field($_POST['folder_id']); // Sanitiza el ID de la carpeta

        // Conecta a Google Drive
        $driveService = connect_to_google_drive();

        try {
            // Realiza la consulta para obtener archivos en la carpeta
            $query = sprintf("'%s' in parents", $folderId); // Obtiene archivos dentro de la carpeta
            $files = $driveService->files->listFiles(array('q' => $query, 'fields' => 'files(id, name, mimeType, thumbnailLink)'));

            // Comienza a construir el contenido a mostrar
            $output = ''; // Asegúrate de inicializar $output
            if (count($files->files) > 0) {
                foreach ($files->files as $file) {
                    // Verifica el tipo de archivo y genera el HTML correspondiente
                    $mimeType = $file->mimeType;
                    $output .= '<div class="file-item">';
                    
                    if (strpos($mimeType, 'image/') === 0) {
                        $output .= '<img src="' . esc_url($file->thumbnailLink) . '" alt="' . esc_attr($file->name) . '" class="image-item" data-image-url="' . esc_url($file->thumbnailLink) . '" data-file-id="' . esc_attr($file->id) . '" style="width: 300px; height: 300px;">';

                    
                    } elseif (strpos($mimeType, 'video/') === 0) {
                        // Para videos, muestra la miniatura
                        $output .= '<img src="' . esc_url($file->thumbnailLink) . '" alt="' . esc_attr($file->name) . '" style="max-width: 100%; height: auto;">';
                        $output .= '<p><a href="https://drive.google.com/file/d/' . esc_attr($file->id) . '/view" target="_blank">Ver video</a></p>';
                    } elseif (strpos($mimeType, 'audio/') === 0) {
    // Crea un div con un botón "X" que cargará el audio dinámicamente
    $audioUrl = 'https://drive.google.com/file/d/' . esc_attr($file->id) . '/preview'; // URL para previsualizar y reproducir
    
    $output .= '<div class="audio-container" data-audio-url="' . esc_url($audioUrl) . '">';
    $output .= '<p>' . esc_html($file->name) . '</p>';
    $output .= '<button class="load-audio">Cargar audio</button>'; // Botón para cargar el audio
    $output .= '<div class="audio-content"></div>'; // Contenedor vacío donde se insertará el iframe
    $output .= '</div>';
                    } elseif ($mimeType === 'application/pdf') {
                        // Para PDFs
                        $output .= '<p>' . esc_html($file->name) . ' <a href="https://drive.google.com/file/d/' . esc_attr($file->id) . '/view" target="_blank">Ver PDF</a></p>';
                    }
                    
                    $output .= '</div>'; // Cierra el div del archivo
                }
            } else {
                $output .= '<p>No se encontraron archivos en esta carpeta.</p>'; // Mensaje si no hay archivos
            }

            // Retorna la respuesta exitosa
            wp_send_json_success($output);
        } catch (Exception $e) {
            // Maneja errores
            wp_send_json_error('Error al obtener el contenido de la carpeta: ' . esc_html($e->getMessage()));
        }
    } else {
        // Responde si no se proporciona un ID
        wp_send_json_error('No se ha proporcionado un ID de carpeta.');
    }
}

// Agrega la acción AJAX para usuarios registrados y no registrados
add_action('wp_ajax_get_folder_content', 'get_folder_content');
add_action('wp_ajax_nopriv_get_folder_content', 'get_folder_content');



