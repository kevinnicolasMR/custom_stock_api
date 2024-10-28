jQuery(document).ready(function ($) {
    let currentFolderId = ""; // Almacena el ID de la carpeta actual

    // Función para cargar el contenido de la carpeta
    function loadFolderContent(folderId) {
        currentFolderId = folderId; // Actualiza el ID de la carpeta actual
        console.log("Cargando contenido para la carpeta:", currentFolderId);

        // Aquí va tu código AJAX para cargar el contenido de la carpeta
        $.ajax({
            url: ajax_object.ajax_url,
            method: "POST",
            data: {
                action: "get_folder_content",
                folder_id: folderId
            },
            beforeSend: function() {
                $("#loading-message").show();
                $("#folder-content").html(""); // Limpiar contenido anterior
            },
            success: function(response) {
                $("#loading-message").hide();
                if (response.success) {
                    $("#folder-content").html(response.data.output);
                } else {
                    $("#folder-content").html("<p>Error al cargar el contenido.</p>");
                }
            },
            error: function() {
                $("#loading-message").hide();
                $("#folder-content").html("<p>Error de conexión. Inténtalo de nuevo.</p>");
            }
        });
    }

    // Manejo del clic en las carpetas
    $(document).on("click", ".clickable-folder", function () {
        const folderId = $(this).data("folder-id");
        loadFolderContent(folderId);
    });

    // Manejo del clic en el botón de retroceso
    $(document).on("click", "#back-button", function () {
        console.log("ID de la carpeta actual:", currentFolderId);
        // Aquí puedes implementar la lógica para volver a la carpeta anterior
    });

    console.log("Funcionando");
});
