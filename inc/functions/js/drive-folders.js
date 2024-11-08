// drive-folders.js

jQuery(document).ready(function($) {
    const parentFolderId = ajax_object.parent_folder_id;

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
            success: function(response) {
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
            error: function() {
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

    // Load the folder menu for level-0 on page load
    loadDriveFolders(parentFolderId, 0);

    // Load the content of the parent folder by default on page load
    loadFolderContent(parentFolderId);

    // Handle click events on level-0 folder items to dynamically load level-1 folders
    $(document).on("click", ".clickable-folder.level-0", function() {
        const folderId = $(this).data("folder-id");
        // Check if level-1 folders are already loaded to avoid reloading
        if ($(this).children(".level-1-wrapper").length === 0) {
            loadDriveFolders(folderId, 1); // Load level-1 folders for the clicked level-0 folder
        }
    });

    // Handle click events on all folder items to load folder content
    $(document).on("click", ".clickable-folder", function() {
        const folderId = $(this).data("folder-id");
        loadFolderContent(folderId);
    });
});
