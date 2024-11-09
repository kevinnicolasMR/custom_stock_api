jQuery(document).ready(function($) {
    let currentParentFolderId = "1VEnaLmB6_EYRKYj5552rXB7shcjesrgM"; // ID de la carpeta madre
    let currentFolderId = currentParentFolderId; // ID de la carpeta actual
    const loadedFolders = {}; // Objeto para rastrear las carpetas cargadas

    if (!currentParentFolderId) {
        console.error('El ID de la carpeta madre no está definido.');
        return; // Si no hay ID de la carpeta madre, no se ejecuta más código
    }
    

    function isFolderLoaded(folderId) {
        // Si el ID de la carpeta es el de la carpeta madre, no se verifica si está cargada
        if (folderId === currentParentFolderId) {
            return false; // Permite cargar siempre la carpeta madre
        }
        return loadedFolders[folderId] !== undefined;
    }
    
    

    // Función para marcar una carpeta como cargada
    function markFolderAsLoaded(folderId) {
        loadedFolders[folderId] = true;
    }

    function loadDriveFolders(folderId, level) {
        console.log(`Cargando carpetas para el folder ID: ${folderId}, nivel: ${level}`);
    
        if (isFolderLoaded(folderId)) {
            console.log(`La carpeta ${folderId} ya está cargada. No se realiza la solicitud.`);
            return;
        }
    
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
                    } else {
                        $(`[data-folder-id="${folderId}"]`).append(response.data);
                        $(`[data-folder-id="${folderId}"]`).addClass('loaded');
                    }
    
                    markFolderAsLoaded(folderId);
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
    
    
    

    function loadFolderContent(folderId) {
        console.log(`Cargando contenido para el folder ID: ${folderId}`);
    
        // Si es la carpeta madre, siempre cargamos su contenido
        if (folderId === currentParentFolderId) {
            console.log(`Cargando el contenido de la carpeta madre (ID: ${folderId}) nuevamente.`);
        } else if (folderId === currentFolderId) {
            console.log(`El contenido de la carpeta ${folderId} ya está cargado. No se recarga.`);
            return; // No recargar si ya es la misma carpeta
        }
    
        // Actualizar el ID actual solo si no es la carpeta madre
        if (folderId !== currentParentFolderId) {
            currentFolderId = folderId;
        }
    
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
                    console.log(`Contenido cargado para el folder ID: ${folderId}`);
                    markFolderAsLoaded(folderId); // Marca la carpeta como cargada
                } else {
                    $("#folder-content").html("<p>Error al cargar el contenido.</p>");
                }
            },
            error: function() {
                $("#folder-content").html("<p>Error de conexión. Inténtalo de nuevo.</p>");
            }
        });
    }
    

    // Manejo de clic en las carpetas de nivel-0
    $(document).on("click", ".clickable-folder.level-0", function() {
        const folderId = $(this).data("folder-id");

        console.log(`Clic en la carpeta de nivel-0, folder ID: ${folderId}`); // Log del ID de la carpeta clicada

        // Evitar duplicación: Si ya está cargando o ya tiene subcarpetas cargadas, salir
        if ($(this).hasClass("loaded") || $(this).data("isLoading")) {
            return;
        }

        // Marcar como en proceso de carga para evitar duplicación de llamadas AJAX
        $(this).data("isLoading", true);

        // Cargar carpetas de nivel-1 solo si no están cargadas
        loadDriveFolders(folderId, 1); // Cargar subcarpetas nivel-1
    });

    // Manejo de clic en cualquier carpeta para cargar el contenido
    $(document).on("click", ".clickable-folder", function() {
        const folderId = $(this).data("folder-id");

        console.log(`Clic en cualquier carpeta para cargar contenido, folder ID: ${folderId}`); // Log del ID de la carpeta clicada

        // Cargar contenido de la carpeta, verificando si el ID es el mismo
        loadFolderContent(folderId); // Cargar contenido de la carpeta
    });

    // Cargar el menú y el contenido inicial de la carpeta madre
    console.log(`Cargando el menú y contenido para la carpeta madre con ID: ${currentParentFolderId}`);
    loadDriveFolders(currentParentFolderId, 0); // Cargar el menú de la carpeta madre (nivel-0)
    loadFolderContent(currentParentFolderId); // Cargar el contenido de la carpeta madre desde el inicio

 


});
