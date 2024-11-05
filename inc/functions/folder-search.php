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
    // Atributos del shortcode
    $atts = shortcode_atts(array('ids' => ''), $atts, 'drive_folders');
    if (empty($atts['ids'])) {
        return '<p>No se han proporcionado carpetas para mostrar.</p>';
    }

    // Conexión con el servicio de Google Drive
    $folderIds = explode(',', $atts['ids']);
    $output = '<div id="drive-folders-container">';

    // Contenedor para el menú de carpetas
    $output .= '<div id="folder-menu">';
    $output .= '<div class="level-0-wrapper">';

    // Recorre cada carpeta ID proporcionado en los atributos
    foreach ($folderIds as $folderId) {
        $folderId = trim($folderId);
        $output .= '<div class="subfolder level-0 clickable-folder" data-folder-id="' . esc_attr($folderId) . '">';
        $output .= '<p>Cargando carpeta...</p>'; // Mensaje temporal antes de la carga
        $output .= '</div>';  
    }
    $output .= '</div>'; // Cierra level-0-wrapper
    $output .= '</div>'; // Cierra folder-menu

    // Contenedor para el contenido de la carpeta
    $output .= '<div id="folder-content"><div id="loading-message" style="text-align: center; display: none;">Cargando contenido de Google Drive...</div></div>';
    $output .= '</div>'; // Cierra drive-folders-container

    // Script para cargar el contenido de la carpeta en segundo plano después de la carga completa de la página
    $output .= '
    <script>
        window.addEventListener("load", function() {
            setTimeout(function() {
                loadDriveFolders();
            }, 500); // Espera medio segundo después de cargar la página
        });

        function loadDriveFolders() {
            jQuery(document).ready(function ($) {
                $("#loading-message").show(); // Muestra el mensaje de carga
                $.ajax({
                    url: ajax_object.ajax_url,
                    method: "POST",
                    data: {
                        action: "get_folder_content", // Llama a la acción AJAX que ya tienes
                        folder_id: "' . esc_js(PARENT_FOLDER_ID) . '"
                    },
                    success: function (response) {
                        $("#loading-message").hide(); // Oculta el mensaje de carga
                        if (response.success) {
                            $("#folder-content").html(response.data); // Rellena el contenedor con el contenido
                        } else {
                            $("#folder-content").html("<p>Error al cargar el contenido.</p>");
                        }
                    },
                    error: function () {
                        $("#folder-content").html("<p>Error de conexión. Inténtalo de nuevo.</p>");
                    }
                });
            });
        }

        // Manejador de clics en carpetas
        jQuery(document).on("click", ".clickable-folder", function () {
            var folderId = jQuery(this).data("folder-id");
            loadFolderContent(folderId);
        });

        function loadFolderContent(folderId) {
            jQuery.ajax({
                url: ajax_object.ajax_url,
                method: "POST",
                data: {
                    action: "get_folder_content",
                    folder_id: folderId
                },
                beforeSend: function() {
                    $("#loading-message").show(); // Mostrar mensaje de carga
                    $("#folder-content").html(""); // Limpiar contenido anterior
                },
                success: function (response) {
                    $("#loading-message").hide(); // Ocultar mensaje de carga
                    if (response.success) {
                        $("#folder-content").html(response.data); // Rellenar con el contenido recibido
                    } else {
                        $("#folder-content").html("<p>Error al cargar el contenido.</p>");
                    }
                },
                error: function () {
                    $("#folder-content").html("<p>Error de conexión. Inténtalo de nuevo.</p>");
                }
            });
        }
    </script>';

    return $output;
}

