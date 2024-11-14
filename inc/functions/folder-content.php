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

        // Buscar la subcarpeta de miniaturas dentro de la carpeta actual
        $thumbnailFolderId = null;
        $query = sprintf("'%s' in parents and mimeType = 'application/vnd.google-apps.folder' and name = 'miniaturas'", $folderId);
        $thumbnailFolders = $driveService->files->listFiles(['q' => $query, 'fields' => 'files(id)']);
        
        // Añadir console.log para verificar si la carpeta "miniaturas" fue encontrada
        $output = '';
        if (count($thumbnailFolders->files) > 0) {
            $thumbnailFolderId = $thumbnailFolders->files[0]->id;
        } else {
            $output .= "<script>console.log('Carpeta miniaturas NO encontrada en la carpeta actual.');</script>";
        }

        // Obtener archivos de la carpeta actual
        $query = sprintf("'%s' in parents", $folderId);
        $files = $driveService->files->listFiles(['q' => $query, 'fields' => 'files(id, name, mimeType, thumbnailLink)']);

        // Clasificar archivos por tipo
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
            } elseif (strpos($mimeType, 'font/') === 0) {
                $fonts[] = $file;
            }
        }

        $offset = isset($_POST['offset']) ? intval($_POST['offset']) : 0;
        $limit = 10; 
        $fileContent = ''; 

        if ($offset === 0) {
            $output .= '<div class="search-container">';
            $output .= '<input type="text" id="search-input" placeholder="¿Qué archivo buscas?">';
            $output .= '<button id="search-button">Buscar</button>';
            $output .= '<button id="clear-button" style="display: none;">X</button>';
            $output .= '</div>';

             $output .= '<div class="current-category-name">';
            $output .= $breadcrumb; 
            $output .= '</div>';
        }

        $totalFilesQueried = $driveService->files->listFiles(array('q' => $query, 'pageSize' => ($offset + $limit + 1), 'fields' => 'files(id)'));
        $moreContentAvailable = count($totalFilesQueried->files) > ($offset + $limit);

        $fileCount = 0;

        $fileContent = ''; 

        // Procesar carpetas
$fileCount = 0;
foreach ($folders as $folder) {
    // Verificar si el nombre de la carpeta es "miniaturas" y omitirla si es así
    if (strtolower($folder->name) === 'miniaturas') {
        continue;
    }
    
    if ($fileCount >= $offset && $fileCount < $offset + $limit) {
        $fileContent .= render_folder_template($folder);
    }
    $fileCount++;
}


        // Procesar videos con miniaturas personalizadas
        foreach ($videos as $video) {
            if ($fileCount >= $offset && $fileCount < $offset + $limit) {
$customThumbnail = null;
if ($thumbnailFolderId) {
    $thumbnailQuery = sprintf("'%s' in parents and name = '%s.jpg'", $thumbnailFolderId, pathinfo($video->name, PATHINFO_FILENAME));
    $customThumbnails = $driveService->files->listFiles(['q' => $thumbnailQuery, 'fields' => 'files(id, name, thumbnailLink)']);

    if (count($customThumbnails->files) > 0) {
        $customThumbnail = $customThumbnails->files[0]->thumbnailLink;
    } else {
        $output .= "<script>console.log('No se encontró miniatura personalizada para el video: " . esc_js($video->name) . "');</script>";
    }
}
                $fileContent .= render_video_template($video, $customThumbnail);
            }
            $fileCount++;
        }

        // Procesar imágenes
        foreach ($images as $image) {
            if ($fileCount >= $offset && $fileCount < $offset + $limit) {
                $fileContent .= render_image_template($image);
            }
            $fileCount++;
        }

        // Procesar audios
        foreach ($audios as $audio) {
            if ($fileCount >= $offset && $fileCount < $offset + $limit) {
                $fileContent .= render_audio_template($audio, $folderName); 
            }
            $fileCount++;
        }

        // Procesar PDFs
        foreach ($pdfs as $pdf) {
            if ($fileCount >= $offset && $fileCount < $offset + $limit) {
                $fileContent .= render_pdf_template($pdf);
            }
            $fileCount++;
        }

        // Procesar fuentes
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

        $totalFilesQueried = $driveService->files->listFiles(['q' => $query, 'pageSize' => ($offset + $limit + 1), 'fields' => 'files(id)']);
        $moreContentAvailable = count($totalFilesQueried->files) > ($offset + $limit);

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

    $rootFolder = $driveService->files->get($parentFolderId, ['fields' => 'name']);
    array_unshift($breadcrumb, '<p class="breadcrumb-text">' . esc_html($rootFolder->name) . '</p>');

    return implode(' >> ', $breadcrumb); // Concatenar con ' >> '
}


add_action('wp_ajax_get_folder_content', 'get_folder_content');
add_action('wp_ajax_nopriv_get_folder_content', 'get_folder_content');