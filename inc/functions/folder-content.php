<?php

require_once plugin_dir_path(__FILE__) . '../api-connection.php';

// Función para manejar la solicitud AJAX
function get_folder_content() {
    // Verifica si se proporciona el ID de la carpeta
    if (isset($_POST['folder_id'])) {
        $folderId = sanitize_text_field($_POST['folder_id']); // Sanitiza el ID de la carpeta

        // Conecta a Google Drive
        $driveService = connect_to_google_drive();

        try {
            // Realiza la consulta para obtener archivos y carpetas en la carpeta
            $query = sprintf("'%s' in parents", $folderId); // Obtiene archivos y carpetas dentro de la carpeta
            $files = $driveService->files->listFiles(array('q' => $query, 'fields' => 'files(id, name, mimeType, thumbnailLink)'));

            // Inicializa una variable para el contenido de salida
            $output = '';
            
            if (count($files->files) > 0) {
                foreach ($files->files as $file) {
                    $mimeType = $file->mimeType;

                    // Genera el HTML dependiendo del tipo de archivo
                    if ($mimeType === 'application/vnd.google-apps.folder') {
                        $output .= '<div class="file-item file-item-folder clickable-folder" data-folder-id="' . esc_attr($file->id) . '" style="width: 300px; height: 200px;">';
                        $output .= '<p class="folder-name">' . esc_html($file->name) . '</p>';
                        $output .= '</div>';
                    
                    } elseif (strpos($mimeType, 'image/') === 0) {
                        // Para imágenes
                        $output .= '<div class="file-item file-item-img">';
                        $output .= '<img src="' . esc_url($file->thumbnailLink) . '" alt="' . esc_attr($file->name) . '" class="image-item" data-image-url="' . esc_url($file->thumbnailLink) . '" data-file-id="' . esc_attr($file->id) . '">';
                        $output .= '</div>';
                    
                    } elseif (strpos($mimeType, 'video/') === 0) {
                        // Para videos
                        $output .= '<div class="file-item file-item-video">';
                        $output .= '<img src="' . esc_url($file->thumbnailLink) . '" alt="' . esc_attr($file->name) . '" class="video-item" data-video-url="https://drive.google.com/file/d/' . esc_attr($file->id) . '/preview" style="max-width: 100%; height: auto;">';
                        $output .= '</div>';
                    
                    } elseif (strpos($mimeType, 'audio/') === 0) {
                        // Para audios
                        $audioUrl = 'https://drive.google.com/file/d/' . esc_attr($file->id) . '/preview';
                        $downloadUrl = 'https://drive.google.com/uc?export=download&id=' . esc_attr($file->id);
                        
                        $output .= '<div class="file-item file-item-audio">';
                        $output .= '<div class="audio-info-container">';
                        $output .= '<div class="audio-container" data-audio-url="' . esc_url($audioUrl) . '">';
                        $output .= '<button class="load-audio"><i class="fas fa-download"></i></button>';
                        $output .= '<div class="audio-content"></div>';
                        $output .= '</div>';
                        $output .= '<div class="audio-title-container"><p class="audio-title">' . esc_html($file->name) . '</p></div>';
                        $output .= '</div>';
                        $output .= '<div class="audio-description"><p>Texto de ejemplo aquí</p></div>';
                        $output .= '<div class="audio-download"><a href="' . esc_url($downloadUrl) . '" class="download-audio-button" target="_blank" download>Descargar</a></div>';
                        $output .= '</div>';
                    
                    } elseif ($mimeType === 'application/pdf') {
                        // Para PDFs
                        $output .= '<div class="file-item file-item-pdf">';
                        $output .= '<p>' . esc_html($file->name) . ' <a href="https://drive.google.com/file/d/' . esc_attr($file->id) . '/view" target="_blank">Ver PDF</a></p>';
                        $output .= '</div>';
                    }
                }
            } else {
                $output = '<p>No se encontraron archivos en esta carpeta.</p>'; // Mensaje si no hay archivos
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
