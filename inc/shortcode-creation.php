<?php 
// Asegúrate de incluir la conexión con Google API (api-connection.php)
require_once plugin_dir_path(__FILE__) . 'api-connection.php';

// Función para mostrar los títulos de las carpetas seleccionadas en el front-end
function display_drive_folders_titles($atts) {
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
    $output = '<div id="folder-list"><h2>Carpetas disponibles:</h2>';

    // Generar títulos de las carpetas
    foreach ($folderIds as $folderId) {
        $folderId = trim($folderId); // Limpia cualquier espacio en blanco
        try {
            // Obtener la información de la carpeta por su ID
            $folder = $driveService->files->get($folderId, array('fields' => 'id, name'));

            // Crear el enlace que al hacer clic mostrará el contenido de la carpeta
            $output .= '<div class="folder-title" data-folder-id="' . esc_attr($folderId) . '">';
            $output .= esc_html($folder->name);  // Muestra el nombre de la carpeta
            $output .= '</div>';
        } catch (Exception $e) {
            $output .= '<p>Error al obtener carpeta: ' . esc_html($e->getMessage()) . '</p>';
        }
    }

    $output .= '</div>';
    // Div donde se mostrará el contenido de la carpeta seleccionada
    $output .= '<div id="folder-content"></div>';

    // Agregar script para manejar el clic y mostrar el contenido
    $output .= '
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            document.querySelectorAll(".folder-title").forEach(function(folder) {
                folder.addEventListener("click", function() {
                    var folderId = this.getAttribute("data-folder-id");
                    var contentDiv = document.getElementById("folder-content");

                    // Limpiar el contenido previo
                    contentDiv.innerHTML = "Cargando...";

                    // Hacer la solicitud AJAX para obtener el contenido de la carpeta
                    fetch("' . admin_url('admin-ajax.php') . '?action=get_folder_content&folder_id=" + folderId)
                    .then(response => response.text())
                    .then(data => contentDiv.innerHTML = data)
                    .catch(error => contentDiv.innerHTML = "Error al cargar el contenido.");
                });
            });
        });
    </script>';

    return $output;
}

// Función para manejar la solicitud AJAX y devolver el contenido de la carpeta
function get_folder_content() {
    // Verificar que se proporciona un ID de carpeta
    if (!isset($_GET['folder_id'])) {
        echo 'No se proporcionó un ID de carpeta.';
        wp_die();
    }

    // Sanitize el ID de carpeta que viene de la solicitud
    $folderId = sanitize_text_field($_GET['folder_id']);
    
    // Conectar a Google Drive
    $driveService = connect_to_google_drive();

    try {
        // Hacer la consulta para obtener los archivos dentro de la carpeta usando el ID de la carpeta
        $query = sprintf("'%s' in parents", $folderId);  // Usar el ID de la carpeta

        $optParams = array(
            'pageSize' => 20,
            'fields' => "nextPageToken, files(id, name, mimeType, webContentLink)" // Obtener archivos
        );

        // Listar los archivos que están en la carpeta con el ID proporcionado
        $results = $driveService->files->listFiles(array('q' => $query, 'pageSize' => 20, 'fields' => 'files(id, name)'));

        if (count($results->files) == 0) {
            echo '<p>No se encontraron archivos en esta carpeta.</p>';
        } else {
            echo '<ul>';
            foreach ($results->files as $file) {
                echo '<li>' . esc_html($file->name) . '</li>';  // Muestra el nombre de cada archivo
            }
            echo '</ul>';
        }
    } catch (Exception $e) {
        // Capturar y mostrar cualquier error que ocurra al intentar obtener los archivos
        echo 'Error al obtener archivos: ' . esc_html($e->getMessage());
    }

    wp_die(); // Termina la ejecución correctamente después de la respuesta
}

// Registrar la función para manejar solicitudes AJAX autenticadas y no autenticadas
add_action('wp_ajax_get_folder_content', 'get_folder_content');
add_action('wp_ajax_nopriv_get_folder_content', 'get_folder_content');

// Función que se llama al generar el shortcode
function generate_shortcode($selectedFolders) {
    // Usar los IDs de las carpetas seleccionadas para crear el shortcode
    $shortcode = '[drive_folders ids="' . implode(',', $selectedFolders) . '"]';
    return $shortcode;
}

// Registra el shortcode
add_shortcode('drive_folders', 'display_drive_folders_titles');
