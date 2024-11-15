<?php
require_once plugin_dir_path(__FILE__) . '../api-connection.php';

define('PARENT_FOLDER_ID', '1VEnaLmB6_EYRKYj5552rXB7shcjesrgM');

function display_drive_folders_menu($atts) {
    // Shortcode attributes
    $atts = shortcode_atts(array('ids' => ''), $atts, 'drive_folders');
    if (empty($atts['ids'])) {
        return '<p>No se han proporcionado carpetas para mostrar.</p>';
    }

    $folder_ids = explode(',', $atts['ids']);  // Convertir los IDs en un array

    // Crear un atributo 'data-folder-ids' con los IDs de las carpetas seleccionadas
    $folder_ids_attribute = implode(',', $folder_ids);  // Crear una cadena con los IDs

    // Output container for the Drive folders
    $output = '<div id="drive-folders-container" data-folder-ids="' . esc_attr($folder_ids_attribute) . '">';
    
    // Añadir un botón arriba del menú
    $output .= '<div id="top-menu-button">';
    $output .= '<p>Menu de recursos disponibles</p>';
    $output .= '<button id="menu-toggle"><i class="fas fa-bars"></i></button>';  // Este botón tiene un id de "menu-toggle"
    $output .= '</div>';

    // Menú de carpetas
    $output .= '<div id="folder-menu"><p>Cargando menú de carpetas...</p></div>';
    
    // Mueve el div de carga fuera de #folder-content
    $output .= '<div id="loading-message" style="text-align: center; display: none;">
    <div id="loading-spinner"></div>
    <p>Cargando contenido de Google Drive...</p></div>';
    
    $output .= '<div id="folder-content"></div>';
    $output .= '</div>';

    return $output;
}





function enqueue_drive_folder_scripts() {
    // Encola el archivo JavaScript externo
    wp_enqueue_script('drive-folders', plugin_dir_url(__FILE__) . 'js/drive-folders.js', array('jquery'), null, true);

    wp_localize_script('drive-folders', 'ajax_object', array(
        'ajax_url' => admin_url('admin-ajax.php')
    ));
}

add_action('wp_enqueue_scripts', 'enqueue_drive_folder_scripts');

function get_folder_menu() {
    // Obtener los parámetros pasados por POST
    $folderId = isset($_POST['folder_id']) ? sanitize_text_field($_POST['folder_id']) : PARENT_FOLDER_ID;
    $level = isset($_POST['level']) ? intval($_POST['level']) : 0;
    
    // Obtener los IDs de las carpetas desde el atributo 'data-folder-ids'
    $folder_ids = isset($_POST['folder_ids']) ? explode(',', sanitize_text_field($_POST['folder_ids'])) : [];

    // Conexión a Google Drive
    $driveService = connect_to_google_drive();

    try {
        // Definir el contenedor según el nivel
        if ($level === 0) {
            $output = '<div class="level-0-wrapper">';
        } elseif ($level === 1) {
            $output = '<div class="level-1-wrapper">';
        } else {
            $output = '<div class="level-2-wrapper">'; // Nuevo contenedor para level-2
        }

        // Crear la consulta de Google Drive para filtrar las carpetas por los IDs proporcionados
        $query = sprintf("'%s' in parents and mimeType = 'application/vnd.google-apps.folder'", $folderId);

        if (!empty($folder_ids)) {
            // Si hay IDs de carpetas, añadirlos a la consulta
            $folder_ids_str = implode("','", $folder_ids);
            $query .= " and id in ('" . $folder_ids_str . "')";
        }

        // Obtener las carpetas desde Google Drive
        $folders = $driveService->files->listFiles(array('q' => $query, 'fields' => 'files(id, name)'));

        // Variables para contar coincidencias
        $matchingFoldersCount = 0;
        $nonMatchingFoldersCount = 0;

        // Recorrer las carpetas obtenidas
        foreach ($folders->files as $folder) {
            // OMITIR carpetas llamadas "miniaturas"
            if (strtolower($folder->name) === 'miniaturas') {
                continue;
            }

            // Si la carpeta está en la lista de IDs permitidos, contarla como match
            if (!empty($folder_ids) && in_array($folder->id, $folder_ids)) {
                $matchingFoldersCount++;
            } else {
                $nonMatchingFoldersCount++;
            }

            // Añadir la carpeta al output
            $folderLevelClass = 'level-' . $level;
            $output .= '<div class="subfolder ' . $folderLevelClass . ' clickable-folder" data-folder-id="' . esc_attr($folder->id) . '">';
            $output .= '<p>' . esc_html($folder->name) . '</p>';
            $output .= '</div>';
        }

        // Mostrar los resultados en consola
        $output .= '<script type="text/javascript">
            console.log("Carpetas que hacen match: ' . $matchingFoldersCount . '");
            console.log("Carpetas que NO hacen match: ' . $nonMatchingFoldersCount . '");
        </script>';

        $output .= '</div>'; // Cerrar el contenedor del nivel actual
        wp_send_json_success($output);

    } catch (Exception $e) {
        wp_send_json_error('Error al obtener el menú de carpetas: ' . esc_html($e->getMessage()));
    }
}



add_action('wp_ajax_get_folder_menu', 'get_folder_menu');
add_action('wp_ajax_nopriv_get_folder_menu', 'get_folder_menu');