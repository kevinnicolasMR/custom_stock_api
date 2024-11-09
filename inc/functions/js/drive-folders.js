jQuery(document).ready(function($) {
    const parentFolderId = ajax_object.parent_folder_id;

    // Cargar el menú de carpetas para el nivel-0 en la carga de la página
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
                        $("#folder-menu").html(response.data); // Cargar carpetas de nivel-0 en el menú
                    } else {
                        $(`[data-folder-id="${folderId}"]`).append(response.data); // Agregar subcarpetas nivel-1 a la carpeta clicada
                        $(`[data-folder-id="${folderId}"]`).addClass("loaded"); // Marcar como cargado
                    }
                } else {
                    $("#folder-menu").html("<p>Error al cargar el menú de carpetas.</p>");
                }
            },
            error: function() {
                $("#folder-menu").html("<p>Error de conexión. Inténtalo de nuevo.</p>");
            },
            complete: function() {
                $(`[data-folder-id="${folderId}"]`).data("isLoading", false); // Permitir clics nuevamente
            }
        });
    }

    // Cargar contenido de la carpeta específica al hacer clic
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

    // Cargar el menú de carpetas nivel-0 al cargar la página
    loadDriveFolders(parentFolderId, 0);

    // Cargar el contenido de la carpeta principal al cargar la página
    loadFolderContent(parentFolderId);

    // Manejar clics en carpetas de nivel-0 para cargar dinámicamente las subcarpetas de nivel-1
    $(document).on("click", ".clickable-folder.level-0", function() {
        const folderId = $(this).data("folder-id");

        // Evitar duplicación: Si ya está cargando o ya tiene subcarpetas cargadas, salir
        if ($(this).hasClass("loaded") || $(this).data("isLoading")) {
            return;
        }

        // Marcar como en proceso de carga para evitar duplicación de llamadas AJAX
        $(this).data("isLoading", true);

        // Cargar carpetas de nivel-1 solo si no están cargadas
        loadDriveFolders(folderId, 1); // Cargar subcarpetas nivel-1
    });

    // Manejar clics en todos los elementos de carpeta para cargar el contenido de la carpeta
    $(document).on("click", ".clickable-folder", function() {
        const folderId = $(this).data("folder-id");
        loadFolderContent(folderId);
    });
});
