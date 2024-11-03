<?php
require_once plugin_dir_path(__FILE__) . '../api-connection.php';

define('PARENT_FOLDER_ID', '1VEnaLmB6_EYRKYj5552rXB7shcjesrgM');

function render_subfolders($driveService, $folderId, $level = 0) {
    $output = ''; 
    try {
        $query = sprintf("'%s' in parents and mimeType = 'application/vnd.google-apps.folder'", $folderId);
        $subFolders = $driveService->files->listFiles(array('q' => $query, 'fields' => 'files(id, name)'));
        foreach ($subFolders->files as $subFolder) {
            $output .= '<div class="subfolder level-' . esc_attr($level) . ' hideContentMenu" data-folder-id="' . esc_attr($subFolder->id) . '">';
            $output .= '<p>' . esc_html($subFolder->name) . '</p>';
            if ($level < 2) {
                $output .= render_subfolders($driveService, $subFolder->id, $level + 1); 
            }
            $output .= '</div>'; // Cierra el div de la subcarpeta
        }
    } catch (Exception $e) {
        $output .= '<p>Error al obtener subcarpetas: ' . esc_html($e->getMessage()) . '</p>';
    }
    return $output; 
}

function display_drive_folders_menu($atts) {
    $atts = shortcode_atts(array('ids' => ''), $atts, 'drive_folders');
    if (empty($atts['ids'])) {
        return '<p>No se han proporcionado carpetas para mostrar.</p>';
    }
    $driveService = connect_to_google_drive();
    $folderIds = explode(',', $atts['ids']);
    
    $output = '<div id="drive-folders-container">';
    
    $output .= '<div id="folder-menu">';
    
    $output .= '<div class="level-0-wrapper">'; 

    foreach ($folderIds as $folderId) {
        $folderId = trim($folderId);
        try {
            $folder = $driveService->files->get($folderId, array('fields' => 'id, name'));
            
            $output .= '<div class="subfolder level-0 clickable-folder" data-folder-id="' . esc_attr($folderId) . '">';
            $output .= '<p>' . esc_html($folder->name) . '</p>';  
            $output .= render_subfolders($driveService, $folderId, 1);
            $output .= '</div>';  
        } catch (Exception $e) {
            $output .= '<p>Error al obtener carpeta: ' . esc_html($e->getMessage()) . '</p>';
        }
    }
 
    $output .= '</div>'; 

    $output .= '</div><div id="folder-content"><div id="loading-message" style="display:none;">Cargando...</div></div></div>';

$output .= '
<script>
    jQuery(document).ready(function ($) {
        const parentFolderId = "' . PARENT_FOLDER_ID . '";
        
        function loadFolderContent(folderId) {
            $.ajax({
                url: ajax_object.ajax_url,
                method: "POST",
                data: {
                    action: "get_folder_content",
                    folder_id: folderId
                },
                beforeSend: function() {
                    $("#loading-message").show();
                    $("#folder-content").html("");
                },
                success: function (response) {
                    $("#loading-message").hide();
                    if (response.success) {
                        $("#folder-content").html(response.data);
                    } else {
                        $("#folder-content").html("<p>Error al cargar el contenido.</p>");
                    }
                },
                error: function () {
                    $("#folder-content").html("<p>Error de conexión. Inténtalo de nuevo.</p>");
                }
            });
        }

        // Cargar automáticamente la carpeta madre al inicio, pero en segundo plano
        window.onload = function() {
            loadFolderContent(parentFolderId);
        };

        // Cargar carpetas al hacer clic en el menú de la izquierda
        $(document).on("click", ".clickable-folder", function () {
            const folderId = $(this).data("folder-id");
            loadFolderContent(folderId);
        });
    });
</script>
';

    return $output;
}
