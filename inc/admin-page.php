<?php
// Asegúrate de incluir la conexión con Google API (api-connection.php)
require_once plugin_dir_path(__FILE__) . 'api-connection.php';

// ID de la carpeta madre (cámbialo por el ID real)
define('MOTHER_FOLDER_ID', '1VEnaLmB6_EYRKYj5552rXB7shcjesrgM'); // Reemplaza con tu ID

// Función para mostrar la interfaz en el panel de administración
function wp_drive_folders_admin_page() {
    $driveService = connect_to_google_drive(); // Usar la función desde api-connection.php

    // Obtener las carpetas de Google Drive
    $query = sprintf("'%s' in parents", MOTHER_FOLDER_ID); // Buscar carpetas dentro de la carpeta madre
    $optParams = array(
        'pageSize' => 20, // Puedes ajustar este número según tus necesidades
        'fields' => "nextPageToken, files(id, name, mimeType)"
    );

    try {
        // Listar las carpetas desde Google Drive
        $results = $driveService->files->listFiles(array_merge($optParams, ['q' => $query]));
        
        // Crear la interfaz en el admin de WordPress
        echo '<div>';
        echo '<h1>Carpetas disponibles en Google Drive</h1>';

        // Formulario para seleccionar carpetas
        echo '<form method="post" action="">'; // Acción vacía para manejar en la misma página
        if (count($results->files) == 0) {
            echo '<p>No se encontraron carpetas en Google Drive.</p>';
        } else {
            foreach ($results->files as $file) {
                // Solo mostrar carpetas
                if ($file->mimeType === 'application/vnd.google-apps.folder') {
                    echo '<label>';
                    echo '<input type="checkbox" name="selected_folders[]" value="' . esc_attr($file->id) . '"> ';
                    echo esc_html($file->name) . ' (ID: ' . esc_html($file->id) . ')';
                    echo '</label><br>';
                }
            }
            echo '<input type="submit" name="generate_shortcode" value="Generar Shortcode" class="button button-primary">';
        }
        echo '</form>';

        // Manejar el formulario al enviar
        if (isset($_POST['generate_shortcode'])) {
            if (isset($_POST['selected_folders'])) {
                $selected_folders = $_POST['selected_folders'];
                
                // Generar el shortcode con los IDs de las carpetas seleccionadas
                $shortcode = generate_shortcode($selected_folders); // Llamamos a la función para generar el shortcode
                echo '<h2>Shortcode Generado:</h2>';
                echo '<p>' . esc_html($shortcode) . '</p>';
                echo '<p>Copia este shortcode y pégalo en cualquier página o entrada para mostrar las carpetas seleccionadas.</p>';
            } else {
                echo '<p>No se seleccionó ninguna carpeta.</p>';
            }
        }

        echo '</div>';
    } catch (Exception $e) {
        echo '<p>Error al obtener las carpetas: ' . esc_html($e->getMessage()) . '</p>';
        // También podrías añadir más detalles aquí si es necesario
        echo '<p>Por favor verifica los permisos y la configuración de la API.</p>';
    }
}


// Función para agregar la página al menú de administración
function wp_drive_folders_menu() {
    add_menu_page(
        'Carpetas de Google Drive', // Título de la página
        'Drive Folders',            // Texto del menú
        'manage_options',           // Capability
        'drive-folders',            // Slug de la página
        'wp_drive_folders_admin_page' // Callback para el contenido de la página
    );

    // Encolar el CSS para la página de administración
    add_action('admin_enqueue_scripts', 'wp_drive_folders_admin_styles');
}

// Función para encolar los estilos de la página de administración
function wp_drive_folders_admin_styles() {
    wp_enqueue_style('drive-folder-admin-style', plugin_dir_url(__FILE__) . '../assets/style/admin-page.css');
}

add_action('admin_menu', 'wp_drive_folders_menu');

