jQuery(document).ready(function($) {
    let currentParentFolderId = "1VEnaLmB6_EYRKYj5552rXB7shcjesrgM"; // ID de la carpeta madre
    let currentFolderId = currentParentFolderId; // ID de la carpeta actual
    const loadedFolders = {}; // Objeto para rastrear las carpetas cargadas

    // Obtener los IDs del atributo "all_IDs_ShortCode"
    const allIDsShortcode = $('#drive-folders-container').attr('all_IDs_ShortCode');
    const allIDsArray = allIDsShortcode ? allIDsShortcode.split(',') : [];
    console.log("IDs en all_IDs_ShortCode:", allIDsArray);

    // Función para capturar y mostrar los IDs de level-0
    function logLevel0Ids() {
        const level0Ids = [];
        $('.clickable-folder.level-0').each(function() {
            const folderId = $(this).data('folder-id');
            if (folderId) {
                level0Ids.push(folderId);
                // Eliminar el HTML si no hay match
                if (!allIDsArray.includes(folderId)) {
                    $(this).remove(); // Eliminar directamente el elemento
                }
            }
        });
        console.log("IDs de level-0 detectados en el menú:", level0Ids);

        // Comparar con IDs de all_IDs_ShortCode
        const matchingIds = level0Ids.filter(id => allIDsArray.includes(id));
        console.log("IDs que hacen match entre level-0 y all_IDs_ShortCode:", matchingIds);
    }

    // Función para filtrar las carpetas del contenido
    function filterContentFolders() {
        // Filtra las carpetas cargadas en el contenido
        $('#folder-content .clickable-folder').each(function() {
            const folderId = $(this).data('folder-id');
            if (folderId && !allIDsArray.includes(folderId)) {
                $(this).remove(); // Elimina las carpetas que no coinciden con los IDs en all_IDs_ShortCode
            }
        });
        console.log("Contenido filtrado según all_IDs_ShortCode.");
    }

    // Función para verificar si una carpeta ya está cargada
    function isFolderLoaded(folderId) {
        if (folderId === currentParentFolderId) {
            return false; // Permite cargar siempre la carpeta madre
        }
        return loadedFolders[folderId] !== undefined;
    }

    // Función para marcar una carpeta como cargada
    function markFolderAsLoaded(folderId) {
        loadedFolders[folderId] = true;
    }

    // Función para limpiar las carpetas cargadas cuando se navega
    function clearLoadedFolders() {
        for (let key in loadedFolders) {
            if (loadedFolders.hasOwnProperty(key)) {
                delete loadedFolders[key]; // Eliminar el folder ID
            }
        }
    }

    // Función para cargar las carpetas del Drive
    function loadDriveFolders(folderId, level) {
        $.ajax({
            url: ajax_object.ajax_url,
            method: 'POST',
            data: {
                action: 'get_folder_menu',
                folder_id: folderId,
                level: level
            },
            success: function(response) {
                if (response.success) {
                    if (level === 0) {
                        $('#folder-menu').html(response.data);
                        logLevel0Ids(); // Llamar a la función después de cargar level-0
                    } else {
                        $(`[data-folder-id="${folderId}"]`).append(response.data);
                        $(`[data-folder-id="${folderId}"]`).addClass('loaded');
                    }

                    markFolderAsLoaded(folderId);
                    highlightCurrentFolder(); // Resaltar la carpeta actual en el menú
                } else {
                    $('#folder-menu').html('<p>Error al cargar el menú de carpetas.</p>');
                }
            },
            error: function() {
                $('#folder-menu').html('<p>Error de conexión. Inténtalo de nuevo.</p>');
            },
            complete: function() {
                $(`[data-folder-id="${folderId}"]`).data('isLoading', false);
            }
        });
    }

    // Función para cargar el contenido de una carpeta
    function loadFolderContent(folderId) {
        currentFolderId = folderId; // Actualiza el ID actual solo si no es la carpeta madre

        // Limpiar las carpetas cargadas antes de cargar el contenido
        clearLoadedFolders();

        $("#loading-message").show();
        $("#folder-content").css("display", "none");

        $.ajax({
            url: ajax_object.ajax_url,
            method: "POST",
            data: {
                action: "get_folder_content",
                folder_id: folderId
            },
            beforeSend: function() {
                $("#folder-content").html(""); // Limpiar el contenido actual
            },
            success: function(response) {
                if (response.success) {
                    $("#folder-content").html(response.data);
                    markFolderAsLoaded(folderId); // Marca la carpeta como cargada
                    highlightCurrentFolder(); // Llama la función para resaltar la carpeta actual en el menú
                    filterContentFolders(); // Filtra las carpetas del contenido
                } else {
                    $("#folder-content").html("<p>Error al cargar el contenido.</p>");
                }
            },
            error: function() {
                $("#folder-content").html("<p>Error de conexión. Inténtalo de nuevo.</p>");
            },
            complete: function() {
                $("#loading-message").hide();
                $("#folder-content").css("display", "block");
            }
        });
    }

    // Función para resaltar la carpeta actual en el menú
    function highlightCurrentFolder() {
        // Remueve el estilo de cualquier carpeta previamente resaltada
        $('.clickable-folder p').css({
            'font-weight': ''
        });

        // Aplica un borde y grosor de fuente al primer <p> de la carpeta actual en el menú
        $(`.clickable-folder[data-folder-id="${currentFolderId}"] > p:first-child`).css({
            'font-weight': '800'
        });
    }

    // Eventos de clic
    $(document).on("click", ".clickable-folder.level-0", function() {
        const folderId = $(this).data("folder-id");
        if ($(this).hasClass("loaded") || $(this).data("isLoading")) {
            return;
        }

        $(this).data("isLoading", true);
        loadDriveFolders(folderId, 1); // Cargar subcarpetas nivel-1
    });

    // Cuando se hace clic en cualquier carpeta para cargar su contenido
    $(document).on("click", ".clickable-folder", function() {
        const folderId = $(this).data("folder-id");

        loadFolderContent(folderId); // Cargar contenido de la carpeta
    });

    loadDriveFolders(currentParentFolderId, 0); // Cargar el menú de la carpeta madre (nivel-0)
    loadFolderContent(currentParentFolderId); // Cargar el contenido de la carpeta madre desde el inicio
});
