<?php
// Asegúrate de incluir la conexión con Google API (api-connection.php)
require_once plugin_dir_path(__FILE__) . 'api-connection.php';

// Función para mostrar las carpetas seleccionadas en el front-end
function display_drive_folders($atts) {
    // Asegúrate de tener el ID de las carpetas en los atributos
    $atts = shortcode_atts(array(
        'ids' => ''
    ), $atts, 'drive_folders');

    // Comprobar que se ha proporcionado algún ID
    if (empty($atts['ids'])) {
        return '<p>No se han proporcionado carpetas para mostrar.</p>';
    }

    // Conectar a Google Drive
    $driveService = connect_to_google_drive();
    $folderNumbers = explode(',', $atts['ids']); // IDs serán los números de 3 cifras
    $output = '<h2>Contenido de Google Drive (Carpetas Seleccionadas)</h2>';

    // Obtener el mapeo de la base de datos
    $folder_map = get_option('drive_folder_map', []);

    try {
        // Invertir el mapeo para obtener los IDs a partir de los números
        $folderIds = array_map(function($num) use ($folder_map) {
            return isset($folder_map[$num]) ? $folder_map[$num] : null;
        }, $folderNumbers);

        foreach ($folderIds as $folderId) {
            if (!$folderId) {
                continue; // Si no hay un ID válido, saltamos esta iteración
            }

            $query = sprintf("'%s' in parents", esc_attr($folderId));
            $optParams = array(
                'pageSize' => 20,
                'fields' => "nextPageToken, files(id, name, mimeType, webContentLink)"
            );

            $results = $driveService->files->listFiles(array_merge($optParams, ['q' => $query]));

            if (count($results->files) == 0) {
                $output .= '<p>No se encontraron archivos en la carpeta con ID: ' . esc_html($folderId) . '</p>';
            } else {
                $output .= '<h3>Carpeta ID: ' . esc_html($folderId) . '</h3><ul>';
                foreach ($results->files as $file) {
                    if (strpos($file->mimeType, 'image/') === 0) {
                        $output .= '<li>' . esc_html($file->name) . ' - <a href="' . esc_url($file->webContentLink) . '" target="_blank">Ver imagen</a></li>';
                    } else {
                        $output .= '<li>' . esc_html($file->name) . '</li>';
                    }
                }
                $output .= '</ul>';
            }
        }
        return $output;
    } catch (Exception $e) {
        return 'Error al obtener archivos: ' . esc_html($e->getMessage());
    }
}

// Función para mapear IDs a números de 3 cifras
function map_folders_to_numbers($folderIds) {
    $folder_map = []; // Mapeo nuevo

    foreach ($folderIds as $index => $folderId) {
        // Genera un número de 3 cifras basado en el índice
        $number = str_pad($index + 1, 3, '0', STR_PAD_LEFT); // Ej: 001, 002, ...
        $folder_map[$number] = $folderId; // Mapeamos el número al ID de la carpeta
    }

    // Guarda el mapeo en la base de datos
    update_option('drive_folder_map', $folder_map);
}

// Función que se llama al generar el shortcode
function generate_shortcode($selectedFolders) {
    map_folders_to_numbers($selectedFolders); // Mapeamos e insertamos en la base de datos
    $folderNumbers = array_keys(get_option('drive_folder_map')); // Obtenemos los números de 3 cifras
    $shortcode = '[drive_folders ids="' . implode(',', $folderNumbers) . '"]'; // Generamos el shortcode

    return $shortcode; // Devolver el shortcode generado
}

// Registra el shortcode
add_shortcode('drive_folders', 'display_drive_folders');

