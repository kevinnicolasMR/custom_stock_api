<?php
// Asegúrate de incluir la conexión con Google API si es necesario
require_once plugin_dir_path(__FILE__) . '../api-connection.php';

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
