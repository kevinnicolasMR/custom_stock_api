<?php
// Asegúrate de incluir la conexión con Google API (api-connection.php)
require_once plugin_dir_path(__FILE__) . 'api-connection.php';

// Incluir el archivo que maneja los clics
require_once plugin_dir_path(__FILE__) . 'functions/click-handlers.php';

// Función para renderizar subcarpetas de manera recursiva
function render_subfolders($driveService, $folderId, $level = 1) {
    $output = '<div class="subfolders">'; // Contenedor de subcarpetas

    // Obtener las subcarpetas dentro de la carpeta actual
    try {
        $query = sprintf("'%s' in parents and mimeType = 'application/vnd.google-apps.folder'", $folderId);
        $subFolders = $driveService->files->listFiles(array('q' => $query, 'fields' => 'files(id, name)'));

        // Recorrer las subcarpetas y mostrarlas
        foreach ($subFolders->files as $subFolder) {
            $output .= '<div class="subfolder">';
            // Mostrar nombre y nivel
            $output .= '<p class="folder-name level-' . esc_attr($level) . '" data-folder-id="' . esc_attr($subFolder->id) . '">';
            $output .= esc_html($subFolder->name); // Mostrar el nombre de la subcarpeta
            if ($level == 3) { // Solo mostrar el ID en el nivel 3
                $output .= '<span class="folder-id">' . esc_html($subFolder->id) . '</span>'; // Mostrar el ID de la subcarpeta
            }
            $output .= '</p>'; // Cierra el párrafo de la subcarpeta

            // Llamar a la función de manera recursiva si no se ha alcanzado el nivel 3
            if ($level < 3) {
                $output .= render_subfolders($driveService, $subFolder->id, $level + 1);
            }
            $output .= '</div>'; // Cierra el div de la subcarpeta
        }
    } catch (Exception $e) {
        $output .= '<p>Error al obtener subcarpetas: ' . esc_html($e->getMessage()) . '</p>';
    }

    $output .= '</div>'; // Cierra el contenedor de subcarpetas
    return $output;
}

// Función para mostrar las carpetas de Google Drive
function display_drive_folders_menu($atts) {
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
    $folderIds = explode(',', $atts['ids']); // IDs de las carpetas

    // Creamos un único div contenedor que envolverá el menú
    $output = '<div id="drive-folders-container">';
    $output .= '<div id="folder-menu">';

    foreach ($folderIds as $folderId) {
        $folderId = trim($folderId); // Limpia cualquier espacio en blanco
        try {
            // Obtener la información de la carpeta por su ID
            $folder = $driveService->files->get($folderId, array('fields' => 'id, name'));

            // Crear el título de la carpeta principal como H2
            $output .= '<p class="folder-name level-0" data-folder-id="' . esc_attr($folderId) . '">';
            $output .= esc_html($folder->name);  // Muestra el nombre de la carpeta principal
            $output .= '</p>';  // Cierra el párrafo de la carpeta principal
            
            // Llamar a la función para renderizar subcarpetas
            $output .= render_subfolders($driveService, $folderId, 1);
        } catch (Exception $e) {
            $output .= '<p>Error al obtener carpeta: ' . esc_html($e->getMessage()) . '</p>';
        }
    }

    $output .= '</div>'; // Cierra el div del menú
    $output .= '<div id="folder-content"></div>'; // Div donde se mostrará el contenido de la carpeta seleccionada
    $output .= '</div>'; // Cierra el contenedor principal

    // Devuelve el contenido
    return $output;
}

// Encolar el archivo CSS para el estilo del menú
function enqueue_custom_styles() {
    wp_enqueue_style('custom-style', plugin_dir_url(__FILE__) . 'custom_css/menu.css');
}
add_action('wp_enqueue_scripts', 'enqueue_custom_styles');

// Registrar la función para manejar solicitudes AJAX autenticadas y no autenticadas
add_action('wp_ajax_get_folder_content', 'get_folder_content');
add_action('wp_ajax_nopriv_get_folder_content', 'get_folder_content');

// Registra el shortcode
add_shortcode('drive_folders', 'display_drive_folders_menu');
