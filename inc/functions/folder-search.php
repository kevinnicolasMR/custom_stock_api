<?php
require_once plugin_dir_path(__FILE__) . '../api-connection.php';

define('PARENT_FOLDER_ID', '1VEnaLmB6_EYRKYj5552rXB7shcjesrgM');

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

    // Agregar script JS para el manejo de AJAX
    $output .= '
    <script>
        window.addEventListener("load", function() {
            setTimeout(function() {
                loadDriveFoldersMenu(); // Cargar nombres del menú en segundo plano
                loadDriveFolders();     // Cargar el contenido de la carpeta padre
            }, 500); // Espera medio segundo después de cargar la página
        });

        function loadDriveFoldersMenu() {
            jQuery(document).ready(function ($) {
                $(".clickable-folder").each(function() {
                    var folderId = $(this).data("folder-id");
                    if (!folderId) return;

                    var folderElement = $(this);

                    $.ajax({
                        url: ajax_object.ajax_url,
                        method: "POST",
                        data: {
                            action: "get_folder_name",
                            folder_id: folderId
                        },
                        success: function(response) {
                            console.log(response); // Agrega esto
                            if (response.success) {
                                folderElement.html("<p>" + response.data.name + "</p>");
                            } else {
                                folderElement.html("<p>Error al cargar carpeta</p>");
                                console.error("Error en la respuesta del servidor:", response.data);
                            }
                        },
                        error: function(jqXHR, textStatus, errorThrown) {
                            folderElement.html("<p>Error de conexión</p>");
                            console.error("Error en AJAX:", textStatus, errorThrown, jqXHR.responseText);
                        }
                    });
                });
            });
        }

        function loadDriveFolders() {
            jQuery(document).ready(function ($) {
                $("#loading-message").show();
                $.ajax({
                    url: ajax_object.ajax_url,
                    method: "POST",
                    data: {
                        action: "get_folder_content",
                        folder_id: "' . esc_js(PARENT_FOLDER_ID) . '"
                    },
                    success: function(response) {
                        $("#loading-message").hide();
                        if (response.success) {
                            $("#folder-content").html(response.data);
                        } else {
                            $("#folder-content").html("<p>Error al cargar el contenido.</p>");
                        }
                    },
                    error: function() {
                        $("#folder-content").html("<p>Error de conexión. Inténtalo de nuevo.</p>");
                    }
                });
            });
        }

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
                    $("#loading-message").show();
                    $("#folder-content").html("");
                },
                success: function(response) {
                    $("#loading-message").hide();
                    if (response.success) {
                        $("#folder-content").html(response.data);
                    } else {
                        $("#folder-content").html("<p>Error al cargar el contenido.</p>");
                    }
                },
                error: function() {
                    $("#folder-content").html("<p>Error de conexión. Inténtalo de nuevo.</p>");
                }
            });
        }
    </script>';

    return $output;
}
