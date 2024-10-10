<?php
function display_drive_folders() {
    $driveService = connect_to_google_drive(); 

    // Cambia 'YOUR_FOLDER_ID' por el ID de la carpeta que deseas consultar
    $folderId = '1c-vP86pS3uy0JeKjO-RT5J0Fq5wHWXou';
    $query = "'$folderId' in parents"; // Filtrar solo archivos en la carpeta especificada
    $optParams = array(
        'pageSize' => 10,
        'fields' => "nextPageToken, files(id, name, mimeType, webContentLink)"
    );

    try {
        $results = $driveService->files->listFiles(array_merge($optParams, ['q' => $query]));
        $output = '<h2>Contenido de Google Drive (Carpeta)</h2>';
        if (count($results->files) == 0) {
            $output .= '<p>No se encontraron archivos en la carpeta.</p>';
        } else {
            $output .= '<ul>';
            foreach ($results->files as $file) {
                if (strpos($file->mimeType, 'image/') === 0) {
                    $output .= '<li>' . esc_html($file->name) . ' (ID: ' . esc_html($file->id) . ') - <a href="' . esc_url($file->webContentLink) . '" target="_blank">Ver imagen</a></li>';
                } else {
                    $output .= '<li>' . esc_html($file->name) . ' (ID: ' . esc_html($file->id) . ')</li>';
                }
            }
            $output .= '</ul>';
        }
        return $output;
    } catch (Exception $e) {
        return 'Error: ' . $e->getMessage();
    }
}
add_shortcode('drive_folders', 'display_drive_folders');


