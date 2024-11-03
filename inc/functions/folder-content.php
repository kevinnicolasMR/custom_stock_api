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
        $limit = 10; // Límite de elementos a mostrar
        $output = '';

        // Solo en la primera carga, generamos el contenedor de búsqueda
        if ($offset === 0) {
            $output .= '<div class="search-container">';
            $output .= '<input type="text" id="search-input" placeholder="Escribe el nombre del archivo que estás buscando">';
            $output .= '<button id="search-button">Buscar</button>';
            $output .= '<button id="clear-button" style="display: none;">X</button>';
            $output .= '</div>';
        }

        // Actualizar el código PHP para verificar si hay más archivos disponibles
$totalFilesQueried = $driveService->files->listFiles(array('q' => $query, 'pageSize' => ($offset + $limit + 1), 'fields' => 'files(id)'));
$moreContentAvailable = count($totalFilesQueried->files) > ($offset + $limit);

        // Contador para los elementos mostrados
        $fileCount = 0;
 
        // Generar el contenido a cargar
        $fileContent = ''; 

        // Renderizar carpetas
        foreach ($folders as $folder) {
            if ($fileCount >= $offset && $fileCount < $offset + $limit) {
                $fileContent .= render_folder_template($folder);
            }
            $fileCount++;
        }

        // Renderizar videos
        foreach ($videos as $video) {
            if ($fileCount >= $offset && $fileCount < $offset + $limit) {
                $fileContent .= render_video_template($video);
            }
            $fileCount++;
        }

        // Renderizar imágenes
        foreach ($images as $image) {
            if ($fileCount >= $offset && $fileCount < $offset + $limit) {
                $fileContent .= render_image_template($image);
            }
            $fileCount++;
        }

        // Renderizar audios
        foreach ($audios as $audio) {
            if ($fileCount >= $offset && $fileCount < $offset + $limit) {
                $fileContent .= render_audio_template($audio);
            }
            $fileCount++;
        }

        // Renderizar PDFs
        foreach ($pdfs as $pdf) {
            if ($fileCount >= $offset && $fileCount < $offset + $limit) {
                $fileContent .= render_pdf_template($pdf);
            }
            $fileCount++;
        }

        // Renderizar fuentes
        foreach ($fonts as $font) {
            if ($fileCount >= $offset && $fileCount < $offset + $limit) {
                $fileContent .= render_font_template($font);
            }
            $fileCount++;
        }

        // Agregar el contenido generado al contenedor principal si es la primera carga
        if ($offset === 0) {
            $output .= '<div class="file-container">' . $fileContent . '</div>';
        } else {
            // En cargas posteriores, solo se envía el contenido sin el contenedor principal
            $output = $fileContent;
        }

        // Agregar botón "Ver más contenido" si hay más archivos
        if ($moreContentAvailable) {
            $output .= '<button id="load-more" data-folder-id="' . esc_attr($folderId) . '">Ver más contenido</button>';
        }

        wp_send_json_success($output);
    } catch (Exception $e) {
        wp_send_json_error('Error al obtener el contenido de la carpeta: ' . esc_html($e->getMessage()));
    }
}



// Agrega la acción AJAX para usuarios registrados y no registrados
add_action('wp_ajax_get_folder_content', 'get_folder_content');
add_action('wp_ajax_nopriv_get_folder_content', 'get_folder_content');