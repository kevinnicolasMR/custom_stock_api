function display_drive_folders() {
    $driveService = connect_to_google_drive(); // Asegúrate de que esta función esté definida en tu api-connection.php

    // Obtener la lista de carpetas
    $query = "mimeType='application/vnd.google-apps.folder'"; // Filtrar solo carpetas
    $optParams = array(
        'pageSize' => 10, // Ajusta según tus necesidades
        'fields' => "nextPageToken, files(id, name)"
    );

    try {
        $results = $driveService->files->listFiles($optParams);
        $output = '<h2>Carpetas de Google Drive</h2>';
        if (count($results->files) == 0) {
            $output .= '<p>No se encontraron carpetas.</p>';
        } else {
            $output .= '<ul>';
            foreach ($results->files as $file) {
                $output .= '<li>' . esc_html($file->name) . ' (ID: ' . esc_html($file->id) . ')</li>';
            }
            $output .= '</ul>';
        }
        return $output;
    } catch (Exception $e) {
        return 'Error: ' . $e->getMessage();
    }
}
add_shortcode('drive_folders', 'display_drive_folders');
