<?php
require_once plugin_dir_path(__FILE__) . '../api-connection.php';
require_once plugin_dir_path(__FILE__) . 'templates/folder-template.php';
require_once plugin_dir_path(__FILE__) . 'templates/video-template.php';
require_once plugin_dir_path(__FILE__) . 'templates/image-template.php';
require_once plugin_dir_path(__FILE__) . 'templates/audio-template.php';
require_once plugin_dir_path(__FILE__) . 'templates/pdf-template.php';
require_once plugin_dir_path(__FILE__) . 'templates/fonts-template.php';


function get_folder_content() {
    $parentFolderId = '1VEnaLmB6_EYRKYj5552rXB7shcjesrgM';
    $folderId = isset($_POST['folder_id']) ? sanitize_text_field($_POST['folder_id']) : $parentFolderId;

    $driveService = connect_to_google_drive();

    try {
        $query = sprintf("'%s' in parents", $folderId);
        $files = $driveService->files->listFiles(array('q' => $query, 'fields' => 'files(id, name, mimeType, thumbnailLink)'));

        // Inicializa los arrays para clasificar los archivos
        $folders = [];
        $videos = [];
        $images = [];
        $audios = [];
        $pdfs = [];
        $fonts = [];
        
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

        // Obtener los parámetros de paginación
        $offset = isset($_POST['offset']) ? intval($_POST['offset']) : 0;
        $limit = 5; // Límite de elementos a mostrar
        $output = '';

        // Generar el search-container solo en la primera carga
        if ($offset === 0) {
            $output .= '<div class="search-container">';
            $output .= '<input type="text" id="search-input" placeholder="Escribe el nombre del archivo que estás buscando">';
            $output .= '<button id="search-button">Buscar</button>';
            $output .= '<button id="clear-button" style="display: none;">X</button>';
            $output .= '</div>';
        }

        $output .= '<div class="file-container">';

        // Contador total de archivos
        $totalFiles = count($folders) + count($videos) + count($images) + count($audios) + count($pdfs) + count($fonts);
        $moreContentAvailable = $totalFiles > ($offset + $limit); // Verifica si hay más archivos que mostrar

        // Contador para los elementos mostrados
        $fileCount = 0; 

        // Renderizar carpetas
        foreach ($folders as $folder) {
            if ($fileCount >= $offset && $fileCount < $offset + $limit) {
                $output .= render_folder_template($folder);
            }
            $fileCount++;
        }

        // Renderizar videos
        foreach ($videos as $video) {
            if ($fileCount >= $offset && $fileCount < $offset + $limit) {
                $output .= render_video_template($video);
            }
            $fileCount++;
        }

        // Renderizar imágenes
        foreach ($images as $image) {
            if ($fileCount >= $offset && $fileCount < $offset + $limit) {
                $output .= render_image_template($image);
            }
            $fileCount++;
        }

        // Renderizar audios
        foreach ($audios as $audio) {
            if ($fileCount >= $offset && $fileCount < $offset + $limit) {
                $output .= render_audio_template($audio);
            }
            $fileCount++;
        }

        // Renderizar PDFs
        foreach ($pdfs as $pdf) {
            if ($fileCount >= $offset && $fileCount < $offset + $limit) {
                $output .= render_pdf_template($pdf);
            }
            $fileCount++;
        }

        // Renderizar fuentes
        foreach ($fonts as $font) {
            if ($fileCount >= $offset && $fileCount < $offset + $limit) {
                $output .= render_font_template($font);
            }
            $fileCount++;
        }

        $output .= '</div>';

        // Agregar botón "Ver más contenido" si hay más archivos
        if ($moreContentAvailable) {
            $output .= '<button id="load-more" data-folder-id="' . esc_attr($folderId) . '">Ver más contenido</button>';
        } else {
            $output .= '<p>No se encontraron más archivos en esta carpeta.</p>';
        }

        wp_send_json_success($output);
    } catch (Exception $e) {
        wp_send_json_error('Error al obtener el contenido de la carpeta: ' . esc_html($e->getMessage()));
    }
}



 

// Agrega la acción AJAX para usuarios registrados y no registrados
add_action('wp_ajax_get_folder_content', 'get_folder_content');
add_action('wp_ajax_nopriv_get_folder_content', 'get_folder_content');