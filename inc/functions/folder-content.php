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

            // Inicializa los contenedores para diferentes tipos de archivos
            $imageOutput = '';
            $videoOutput = '';
            $audioOutput = '';
            $pdfOutput = '';
            
            if (count($files->files) > 0) {
                foreach ($files->files as $file) {
                    $mimeType = $file->mimeType;
                    
                    // Excluir las carpetas (application/vnd.google-apps.folder)
                    if ($mimeType === 'application/vnd.google-apps.folder') {
                        continue; // Saltar si es una carpeta
                    }

                    // Genera el HTML dependiendo del tipo de archivo
                    if (strpos($mimeType, 'image/') === 0) {
                        // Para imágenes
                        $imageOutput .= '<div class="file-item file-item-img">';
                        $imageOutput .= '<img src="' . esc_url($file->thumbnailLink) . '" alt="' . esc_attr($file->name) . '" class="image-item" data-image-url="' . esc_url($file->thumbnailLink) . '" data-file-id="' . esc_attr($file->id) . '">';
                        $imageOutput .= '</div>';
                    
                    } elseif (strpos($mimeType, 'video/') === 0) {
                        // Para videos
                        $videoOutput .= '<div class="file-item file-item-video">';
                        $videoOutput .= '<img src="' . esc_url($file->thumbnailLink) . '" alt="' . esc_attr($file->name) . '" class="video-item" data-video-url="https://drive.google.com/file/d/' . esc_attr($file->id) . '/preview" style="max-width: 100%; height: auto;">';
                        $videoOutput .= '</div>';
                    
                    } elseif (strpos($mimeType, 'audio/') === 0) {
                        // Para audios
                        $audioUrl = 'https://drive.google.com/file/d/' . esc_attr($file->id) . '/preview'; // URL para previsualizar y reproducir
                        $downloadUrl = 'https://drive.google.com/uc?export=download&id=' . esc_attr($file->id); // URL para descargar el archivo
                    
                        // Estructura HTML para el audio
                        $audioOutput .= '<div class="file-item file-item-audio">';  // Div principal
                    
                        // Primer bloque: Botón de reproducir y título
                        $audioOutput .= '<div class="audio-info-container">';  // Div para el botón y el título
                        $audioOutput .= '  <div class="audio-play-btn">';
                        $audioOutput .= '    <button class="play-audio-button">Reproducir</button>'; // Botón de reproducir
                        $audioOutput .= '  </div>';
                        $audioOutput .= '  <div class="audio-title">';
                        $audioOutput .= '    <p>' . esc_html($file->name) . '</p>'; // Título del audio
                        $audioOutput .= '  </div>';
                        $audioOutput .= '</div>';
                    
                        // Segundo bloque: Texto cualquiera
                        $audioOutput .= '<div class="audio-description">';
                        $audioOutput .= '  <p>Texto de ejemplo aquí</p>'; // Texto cualquiera
                        $audioOutput .= '</div>';
                    
                        // Tercer bloque: Botón de descarga
                        $audioOutput .= '<div class="audio-download">';
                        $audioOutput .= '  <a href="' . esc_url($downloadUrl) . '" class="download-audio-button" download>Descargar</a>'; // Botón de descarga
                        $audioOutput .= '</div>';
                    
                        $audioOutput .= '</div>'; // Cierre del div principal
                   
                
                    
                    } elseif ($mimeType === 'application/pdf') {
                        // Para PDFs
                        $pdfOutput .= '<div class="file-item file-item-pdf">';
                        $pdfOutput .= '<p>' . esc_html($file->name) . ' <a href="https://drive.google.com/file/d/' . esc_attr($file->id) . '/view" target="_blank">Ver PDF</a></p>';
                        $pdfOutput .= '</div>';
                    }
                }
            } else {
                $imageOutput = '<p>No se encontraron archivos en esta carpeta.</p>'; // Mensaje si no hay archivos
            }

            // Combina los resultados, pero solo incluye contenedores que tengan contenido
            $output = '';
            if (!empty($imageOutput)) {
                $output .= '<div class="file-item-container file-item-container-img">' . $imageOutput . '</div>';
            }
            if (!empty($videoOutput)) {
                $output .= '<div class="file-item-container file-item-container-video">' . $videoOutput . '</div>';
            }
            if (!empty($audioOutput)) {
                $output .= '<div class="file-item-container file-item-container-audio">' . $audioOutput . '</div>';
            }
            if (!empty($pdfOutput)) {
                $output .= '<div class="file-item-container file-item-container-pdf">' . $pdfOutput . '</div>';
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
