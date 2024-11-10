jQuery(document).ready(function($) {
    // Manejar el clic en el breadcrumb
    $(document).on('click', '.breadcrumb-link', function(event) {
        event.preventDefault();
        
        // Obtener el ID de la carpeta desde el atributo data
        const folderId = $(this).data('folder-id');
        
        // Llamar a la función AJAX para obtener el contenido de la nueva carpeta
        loadFolderContent(folderId);
    });

    // Función para cargar el contenido de la carpeta por su ID
    function loadFolderContent(folderId, offset = 0) {
        $.ajax({
            url: ajaxurl,
            method: 'POST',
            data: {
                action: 'get_folder_content',
                folder_id: folderId,
                offset: offset
            },
            success: function(response) {
                if (response.success) {
                    $('#content-container').html(response.data); // Actualiza el contenedor con el nuevo contenido
                } else {
                    alert('Error al cargar el contenido de la carpeta.');
                }
            },
            error: function() {
                alert('Error de conexión con el servidor.');
            }
        });
    }
});
