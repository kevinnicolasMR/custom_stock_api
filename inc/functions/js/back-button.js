jQuery(document).ready(function ($) {
    let currentFolderId = ""; 
    function loadFolderContent(folderId) {
        currentFolderId = folderId; // Actualiza el ID de la carpeta actual
        console.log("Cargando contenido para la carpeta:", currentFolderId);

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

    $(document).on("click", ".clickable-folder", function () {
        const folderId = $(this).data("folder-id");
        loadFolderContent(folderId);
    });

    $(document).on("click", "#back-button", function () {
        console.log("ID de la carpeta actual:", currentFolderId);
       
    });

});
