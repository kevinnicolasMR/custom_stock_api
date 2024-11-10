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
        $folder = $driveService->files->get($folderId, ['fields' => 'name, parents']);
        $folderName = $folder->name;

        // Obtener la ruta completa de la carpeta
        $breadcrumb = get_breadcrumb_path($driveService, $folderId, $parentFolderId);

        $query = sprintf("'%s' in parents", $folderId);
        $files = $driveService->files->listFiles(array('q' => $query, 'fields' => 'files(id, name, mimeType, thumbnailLink)'));

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

        $offset = isset($_POST['offset']) ? intval($_POST['offset']) : 0;
        $limit = 10; 
        $output = '';

        if ($offset === 0) {
            $output .= '<div class="search-container">';
            $output .= '<input type="text" id="search-input" placeholder="Escribe el nombre del archivo que estás buscando">';
            $output .= '<button id="search-button">Buscar</button>';
            $output .= '<button id="clear-button" style="display: none;">X</button>';
            $output .= '</div>';

            // Div para mostrar la ruta completa de la categoría
             $output .= '<div class="current-category-name">';
            $output .= $breadcrumb; // No uses `esc_html` aquí para permitir HTML
            $output .= '</div>';
        }

        $totalFilesQueried = $driveService->files->listFiles(array('q' => $query, 'pageSize' => ($offset + $limit + 1), 'fields' => 'files(id)'));
        $moreContentAvailable = count($totalFilesQueried->files) > ($offset + $limit);

        $fileCount = 0;

        $fileContent = ''; 

        foreach ($folders as $folder) {
            if ($fileCount >= $offset && $fileCount < $offset + $limit) {
                $fileContent .= render_folder_template($folder);
            }
            $fileCount++;
        }

        foreach ($videos as $video) {
            if ($fileCount >= $offset && $fileCount < $offset + $limit) {
                $fileContent .= render_video_template($video);
            }
            $fileCount++;
        }

        foreach ($images as $image) {
            if ($fileCount >= $offset && $fileCount < $offset + $limit) {
                $fileContent .= render_image_template($image);
            }
            $fileCount++;
        }

        foreach ($audios as $audio) {
            if ($fileCount >= $offset && $fileCount < $offset + $limit) {
                $fileContent .= render_audio_template($audio, $folderName); 
            }
            $fileCount++;
        }

        foreach ($pdfs as $pdf) {
            if ($fileCount >= $offset && $fileCount < $offset + $limit) {
                $fileContent .= render_pdf_template($pdf);
            }
            $fileCount++;
        }

        foreach ($fonts as $font) {
            if ($fileCount >= $offset && $fileCount < $offset + $limit) {
                $fileContent .= render_font_template($font);
            }
            $fileCount++;
        }

        if ($offset === 0) {
            $output .= '<div class="file-container">' . $fileContent . '</div>';
        } else {
            $output = $fileContent;
        }

        if ($moreContentAvailable) {
            $output .= '<div class="button-load-more-container">';
            $output .= '<button id="load-more" data-folder-id="' . esc_attr($folderId) . '" data-offset="' . ($offset + $limit) . '">Ver más contenido</button>';
            $output .= '</div>';
        }
        

        wp_send_json_success($output);
    } catch (Exception $e) {
        wp_send_json_error('Error al obtener el contenido de la carpeta: ' . esc_html($e->getMessage()));
    }
}

function get_breadcrumb_path($driveService, $folderId, $parentFolderId) {
    $breadcrumb = [];
    while ($folderId !== $parentFolderId) {
        $folder = $driveService->files->get($folderId, ['fields' => 'name, parents']);
        array_unshift($breadcrumb, '<p class="breadcrumb-text">' . esc_html($folder->name) . '</p>');
        if (!isset($folder->parents) || empty($folder->parents)) {
            break;
        }
        $folderId = $folder->parents[0];
    }

    // Añadir la carpeta madre
    $rootFolder = $driveService->files->get($parentFolderId, ['fields' => 'name']);
    array_unshift($breadcrumb, '<p class="breadcrumb-text">' . esc_html($rootFolder->name) . '</p>');

    return implode(' >> ', $breadcrumb); // Concatenar con ' >> '
}


add_action('wp_ajax_get_folder_content', 'get_folder_content');
add_action('wp_ajax_nopriv_get_folder_content', 'get_folder_content');