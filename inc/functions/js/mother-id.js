jQuery(document).ready(function ($) {

    console.log ("Mother is her")
    // ID de la carpeta madre predeterminada
    const parentFolderId = "1VEnaLmB6_EYRKYj5552rXB7shcjesrgM";

    // Función para cargar el contenido de la carpeta
    function loadFolderContent(folderId) {
        // Hacer una solicitud AJAX para cargar el contenido de la carpeta
        $.ajax({
            url: ajax_object.ajax_url, // URL de la solicitud AJAX en WordPress
            method: "POST",
            data: {
                action: "get_folder_content",
                folder_id: folderId
            },
            beforeSend: function() {
                // Opcional: muestra un mensaje de carga mientras se obtienen los datos
                $(".file-container").html("<p>Cargando contenido...</p>");
            },
            success: function (response) {
                if (response.success) {
                    // Inserta el contenido en el contenedor de archivos
                    $(".file-container").html(response.data);
                } else {
                    $(".file-container").html("<p>Error al cargar el contenido.</p>");
                }
            },
            error: function () {
                $(".file-container").html("<p>Error de conexión. Inténtalo de nuevo.</p>");
            }
        });
    }

    // Cargar automáticamente el contenido de la carpeta madre en la carga inicial
    loadFolderContent(parentFolderId);

    // Manejar clics en las carpetas para cargar contenido al hacer clic
    $(document).on("click", ".clickable-folder", function () {
        const folderId = $(this).data("folder-id");
        loadFolderContent(folderId);
    });
});
