<?php
require_once plugin_dir_path(__FILE__) . 'api-connection.php';

define('MOTHER_FOLDER_ID', '1VEnaLmB6_EYRKYj5552rXB7shcjesrgM'); // Reemplaza con tu ID

function wp_drive_folders_admin_page() {
    $driveService = connect_to_google_drive(); 

    $query = sprintf("'%s' in parents", MOTHER_FOLDER_ID); 
    $optParams = array(
        'pageSize' => 20, 
        'fields' => "nextPageToken, files(id, name, mimeType)"
    );

    try {
        $results = $driveService->files->listFiles(array_merge($optParams, ['q' => $query]));
        
        echo '<div>';
        echo '<h1>Carpetas disponibles en Google Drive</h1>';

        echo '<form method="post" action="">'; 
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

        if (isset($_POST['generate_shortcode'])) {
            if (isset($_POST['selected_folders'])) {
                $selected_folders = $_POST['selected_folders'];
                
                $shortcode = generate_shortcode($selected_folders);
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
        echo '<p>Por favor verifica los permisos y la configuración de la API.</p>';
    }
}


function wp_drive_folders_menu() {
    add_menu_page(
        'Carpetas de Google Drive', 
        'Drive Folders',            
        'manage_options',           
        'drive-folders',            
        'wp_drive_folders_admin_page' 
    );

    add_action('admin_enqueue_scripts', 'wp_drive_folders_admin_styles');
}

function wp_drive_folders_admin_styles() {
    wp_enqueue_style('drive-folder-admin-style', plugin_dir_url(__FILE__) . '../assets/style/admin-page.css');
}

add_action('admin_menu', 'wp_drive_folders_menu');

