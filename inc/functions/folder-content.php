<?php
require_once plugin_dir_path(__FILE__) . '../api-connection.php';
require_once plugin_dir_path(__FILE__) . 'templates/folder-template.php';
require_once plugin_dir_path(__FILE__) . 'templates/video-template.php';
require_once plugin_dir_path(__FILE__) . 'templates/image-template.php';
require_once plugin_dir_path(__FILE__) . 'templates/audio-template.php';
require_once plugin_dir_path(__FILE__) . 'templates/pdf-template.php';
require_once plugin_dir_path(__FILE__) . 'templates/fonts-template.php';


// Función para manejar la solicitud AJAX
function get_folder_content() {
    $parentFolderId = '1VEnaLmB6_EYRKYj5552rXB7shcjesrgM';
    $folderId = isset($_POST['folder_id']) ? sanitize_text_field($_POST['folder_id']) : $parentFolderId;

    $driveService = connect_to_google_drive();

    try {
        $query = sprintf("'%s' in parents", $folderId); 
        $files = $driveService->files->listFiles(array('q' => $query, 'fields' => 'files(id, name, mimeType, thumbnailLink)'));

        $folders = [];
        $videos = [];
        $images = [];
        $audios = [];
        $pdfs = [];
        $fonts = []; // Nuevo arreglo para fuentes

        foreach ($files->files as $file) {
            $mimeType = $file->mimeType;

            if ($mimeType === 'application/vnd.google-apps.folder') {
                $folders[] = $file;
            } elseif (strpos($mimeType, 'video/') === 0) {
                $videos[] = $file;
            } elseif (strpos($mimeType, 'image/') === 0) {
                $images[] = $file;
            } elseif (strpos($mimeType, 'audio/') === 0) {
                $audios[] = $file;
            } elseif ($mimeType === 'application/pdf') {
                $pdfs[] = $file;
            } elseif ($mimeType === 'font/otf' || $mimeType === 'font/ttf' || $mimeType === 'application/x-font-ttf' || $mimeType === 'application/x-font-otf') {
                $fonts[] = $file;
            }
        }

        $output = '<div class="search-container">';
        $output .= '<input type="text" id="search-input" placeholder="Escribe el nombre del archivo que estás buscando">';
        $output .= '<button id="search-button">Buscar</button>';
        $output .= '<button id="clear-button" style="display: none;">X</button>';
        $output .= '</div>';

        $output .= '<div class="file-container">';

        foreach ($folders as $folder) {
            $output .= render_folder_template($folder);
        }

        foreach ($videos as $video) {
            $output .= render_video_template($video);
        }

        foreach ($images as $image) {
            $output .= render_image_template($image);
        }

        foreach ($audios as $audio) {
            $output .= render_audio_template($audio);
        }

        foreach ($pdfs as $pdf) {
            $output .= render_pdf_template($pdf);
        }

        foreach ($fonts as $font) {
            $output .= render_font_template($font);
        }

        $output .= '</div>';

        if (empty($files->files)) {
            $output = '<p>No se encontraron archivos en esta carpeta.</p>';
        }

        wp_send_json_success($output);
    } catch (Exception $e) {
        wp_send_json_error('Error al obtener el contenido de la carpeta: ' . esc_html($e->getMessage()));
    }
}


// Agrega la acción AJAX para usuarios registrados y no registrados
add_action('wp_ajax_get_folder_content', 'get_folder_content');
add_action('wp_ajax_nopriv_get_folder_content', 'get_folder_content');