<?php 
// Asegúrate de incluir la conexión con Google API (api-connection.php)
require_once plugin_dir_path(__FILE__) . 'api-connection.php';

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
    $output = '<div id="folder-menu"><h2>Carpetas disponibles:</h2><ul>';

    // Generar títulos de las carpetas principales
    foreach ($folderIds as $folderId) {
        $folderId = trim($folderId); // Limpia cualquier espacio en blanco
        try {
            // Obtener la información de la carpeta por su ID
            $folder = $driveService->files->get($folderId, array('fields' => 'id, name'));

            // Crear la lista de carpetas con hover para mostrar subcarpetas
            $output .= '<li class="menu-folder" data-folder-id="' . esc_attr($folderId) . '">';
            $output .= esc_html($folder->name);  // Muestra el nombre de la carpeta principal

            // Generar subcarpetas para este menú principal
            $output .= '<ul class="submenu">';
            
            // Obtener las subcarpetas
            $query = sprintf("'%s' in parents and mimeType = 'application/vnd.google-apps.folder'", $folderId);
            $subFolders = $driveService->files->listFiles(array('q' => $query, 'fields' => 'files(id, name)'));

            foreach ($subFolders->files as $subFolder) {
                $output .= '<li class="submenu-folder" data-folder-id="' . esc_attr($subFolder->id) . '">';
                $output .= esc_html($subFolder->name);  // Muestra el nombre de la subcarpeta
                $output .= '</li>';
            }

            $output .= '</ul>';  // Cierra el submenú
            $output .= '</li>';  // Cierra la carpeta principal
        } catch (Exception $e) {
            $output .= '<p>Error al obtener carpeta: ' . esc_html($e->getMessage()) . '</p>';
        }
    }

    $output .= '</ul></div>';
    // Div donde se mostrará el contenido de la carpeta seleccionada
    $output .= '<div id="folder-content"></div>';

    // Generar la URL de admin-ajax.php de forma estática dentro del JavaScript
    $ajax_url = admin_url('admin-ajax.php');

    // Agregar script para manejar el clic y mostrar el contenido
    $output .= '
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Manejar clics en las carpetas principales y subcarpetas
            document.querySelectorAll(".menu-folder, .submenu-folder").forEach(function(folder) {
                folder.addEventListener("click", function() {
                    var folderId = this.getAttribute("data-folder-id");
                    var contentDiv = document.getElementById("folder-content");

                    // Limpiar el contenido previo
                    contentDiv.innerHTML = "Cargando...";

                    // Hacer la solicitud AJAX para obtener el contenido de la carpeta
                    fetch("' . esc_url($ajax_url) . '?action=get_folder_content&folder_id=" + folderId)
                    .then(response => response.text())
                    .then(data => contentDiv.innerHTML = data)
                    .catch(error => contentDiv.innerHTML = "Error al cargar el contenido multimedia.");
                });
            });
        });
    </script>';

    // Devuelve el contenido
    return $output;
}

function get_folder_content() {
    // Verificar que se proporciona un ID de carpeta
    if (!isset($_GET['folder_id'])) {
        echo 'No se proporcionó un ID de carpeta.';
        wp_die();
    }

    // Sanitize el ID de la carpeta que viene de la solicitud
    $folderId = sanitize_text_field($_GET['folder_id']);
    
    // Conectar a Google Drive
    $driveService = connect_to_google_drive();

    try {
        // Hacer la consulta para obtener SOLO archivos multimedia dentro de la carpeta (imágenes/videos)
        $query = sprintf("'%s' in parents and (mimeType contains 'image/' or mimeType contains 'video/')", $folderId);  // Usar el ID de la carpeta para obtener solo imágenes y videos

        // Listar los archivos dentro de la carpeta (solo multimedia)
        $results = $driveService->files->listFiles(array('q' => $query, 'fields' => 'files(id, name, mimeType, thumbnailLink, webViewLink)'));

        if (count($results->files) == 0) {
            echo '<p>No se encontraron archivos multimedia en esta carpeta.</p>';
        } else {
            echo '<div class="media-preview">';
            foreach ($results->files as $file) {
                echo '<div class="media-item" style="display:inline-block; margin:10px;">';

                // Si el archivo es una imagen o un video
                if (strpos($file->mimeType, 'image') !== false) {
                    // Mostrar imagen con tamaño máximo de 300x300
                    echo '<img src="' . esc_url($file->thumbnailLink) . '" style="max-width: 300px; max-height: 300px;" alt="' . esc_attr($file->name) . '" class="image-item" data-image-url="' . esc_url($file->thumbnailLink) . '" />';
                } elseif (strpos($file->mimeType, 'video') !== false) {
                    // Para videos, no hay miniatura directa, pero puedes usar un enlace para verlo
                    echo '<video controls style="max-width: 300px; max-height: 300px;">
                            <source src="' . esc_url($file->webViewLink) . '" type="' . esc_attr($file->mimeType) . '">
                            Tu navegador no soporta la previsualización de este video.
                          </video>';
                }

                // Nombre del archivo y enlace para verlo en Drive
                echo '<p>' . esc_html($file->name) . '</p>';
                echo '<a href="' . esc_url($file->webViewLink) . '" target="_blank">Ver en Drive</a>';
                echo '</div>';
            }
            echo '</div>';
        }
    } catch (Exception $e) {
        // Capturar y mostrar cualquier error que ocurra al intentar obtener los archivos
        echo 'Error al obtener archivos multimedia: ' . esc_html($e->getMessage());
    }

    wp_die(); // Termina la ejecución correctamente después de la respuesta
}

// Encolar el archivo JavaScript
function enqueue_custom_scripts() {
    wp_enqueue_script('image-preview-script', plugin_dir_url(__FILE__) . 'image-preview.js', array(), null, true);
    // Agregar el script para manejar el clic en las imágenes
    add_action('wp_footer', 'display_click_handler');
}
add_action('wp_enqueue_scripts', 'enqueue_custom_scripts');

function display_click_handler() {
    // Agregar el script para manejar el clic en las imágenes
    echo '
    <script>
        // Manejar el clic en las imágenes
        document.addEventListener("click", function(event) {
            if (event.target.classList.contains("image-item")) {
                console.log("Imagen clicada"); // Mostrar mensaje en la consola

                // Obtener la URL de la imagen clicada
                var imageUrl = event.target.getAttribute("data-image-url");

                // Crear un div superpuesto
                var overlay = document.createElement("div");
                overlay.style.position = "fixed";
                overlay.style.top = "0";
                overlay.style.left = "0";
                overlay.style.width = "100%";
                overlay.style.height = "100%";
                overlay.style.backgroundColor = "rgba(0, 0, 0, 0.8)"; // Fondo semi-transparente
                overlay.style.display = "flex";
                overlay.style.justifyContent = "center";
                overlay.style.alignItems = "center";
                overlay.style.zIndex = "1000"; // Asegura que esté en la parte superior

                // Crear la imagen en el div
                var img = document.createElement("img");
                img.src = imageUrl;
                img.style.maxWidth = "90%"; // Ajustar el tamaño de la imagen
                img.style.maxHeight = "90%"; // Ajustar el tamaño de la imagen

                // Agregar un evento para cerrar el overlay al hacer clic en cualquier parte
                overlay.addEventListener("click", function() {
                    document.body.removeChild(overlay); // Remover el overlay
                });

                // Agregar la imagen al overlay
                overlay.appendChild(img);
                document.body.appendChild(overlay); // Agregar el overlay al body
            }
        });
    </script>';
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
add_shortcode('drive_folders', 'display_drive_folders_menu');
