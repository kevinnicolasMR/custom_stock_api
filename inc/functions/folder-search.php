<?php
require_once plugin_dir_path(__FILE__) . '../api-connection.php';

define('PARENT_FOLDER_ID', '1VEnaLmB6_EYRKYj5552rXB7shcjesrgM');

function display_drive_folders_menu($atts) {
    // Shortcode attributes
    $atts = shortcode_atts(array('ids' => ''), $atts, 'drive_folders');
    if (empty($atts['ids'])) {
        return '<p>No se han proporcionado carpetas para mostrar.</p>';
    }

    // Output container for the Drive folders
    $output = '<div id="drive-folders-container">';

    // Placeholder for the folder menu
    $output .= '<div id="folder-menu"><p>Cargando menú de carpetas...</p></div>';

    // Placeholder for the folder content (will load the default folder content here)
    $output .= '<div id="folder-content"><div id="loading-message" style="text-align: center; display: none;">Cargando contenido de Google Drive...</div></div>';
    $output .= '</div>';

    // JavaScript for AJAX loading of menu and folder contents
    $output .= '
    <script>
        jQuery(document).ready(function ($) {
            const parentFolderId = "' . PARENT_FOLDER_ID . '";

            // Load folder menu for level-0 on page load
            function loadDriveFolders(folderId, level) {
                $.ajax({
                    url: ajax_object.ajax_url,
                    method: "POST",
                    data: {
                        action: "get_folder_menu",
                        folder_id: folderId,
                        level: level
                    },
                    success: function (response) {
                        if (response.success) {
                            if (level === 0) {
                                $("#folder-menu").html(response.data); // Load level-0 folders into the menu
                            } else {
                                $(`[data-folder-id="${folderId}"]`).append(response.data); // Append level-1 subfolders to the clicked folder
                            }
                        } else {
                            $("#folder-menu").html("<p>Error al cargar el menú de carpetas.</p>");
                        }
                    },
                    error: function () {
                        $("#folder-menu").html("<p>Error de conexión. Inténtalo de nuevo.</p>");
                    }
                });
            }

            // Load specific folder content on click
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

            // Load the folder menu for level-0 on page load
            loadDriveFolders(parentFolderId, 0);

            // Load the content of the parent folder by default on page load
            loadFolderContent(parentFolderId);

            // Handle click events on level-0 folder items to dynamically load level-1 folders
            $(document).on("click", ".clickable-folder.level-0", function () {
                const folderId = $(this).data("folder-id");
                // Check if level-1 folders are already loaded to avoid reloading
                if ($(this).children(".level-1-wrapper").length === 0) {
                    loadDriveFolders(folderId, 1); // Load level-1 folders for the clicked level-0 folder
                }
            });

            // Handle click events on all folder items to load folder content
            $(document).on("click", ".clickable-folder", function () {
                const folderId = $(this).data("folder-id");
                loadFolderContent(folderId);
            });
        });
    </script>';

    return $output;
}

function get_folder_menu() {
    $folderId = isset($_POST['folder_id']) ? sanitize_text_field($_POST['folder_id']) : PARENT_FOLDER_ID;
    $level = isset($_POST['level']) ? intval($_POST['level']) : 0;
    $driveService = connect_to_google_drive();

    try {
        $output = ($level === 0) ? '<div class="level-0-wrapper">' : '<div class="level-1-wrapper">';
        
        // Query to fetch folders based on level and folder_id
        $query = sprintf("'%s' in parents and mimeType = 'application/vnd.google-apps.folder'", $folderId);
        $folders = $driveService->files->listFiles(array('q' => $query, 'fields' => 'files(id, name)'));

        // Generate HTML for each folder
        foreach ($folders->files as $folder) {
            $folderLevelClass = ($level === 0) ? 'level-0' : 'level-1';
            $output .= '<div class="subfolder ' . $folderLevelClass . ' clickable-folder" data-folder-id="' . esc_attr($folder->id) . '">';
            $output .= '<p>' . esc_html($folder->name) . '</p>';
            $output .= '</div>';
        }

        $output .= '</div>';
        wp_send_json_success($output);

    } catch (Exception $e) {
        wp_send_json_error('Error al obtener el menú de carpetas: ' . esc_html($e->getMessage()));
    }
}
add_action('wp_ajax_get_folder_menu', 'get_folder_menu');
add_action('wp_ajax_nopriv_get_folder_menu', 'get_folder_menu');
?>
