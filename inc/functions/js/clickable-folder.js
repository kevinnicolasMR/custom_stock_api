jQuery(document).ready(function($) {
    // Función para manejar el clic en las carpetas del contenido
    $('#folder-content').on('click', '.clickable-folder', function() {
        var folderId = $(this).data('folder-id');

        // Muestra un mensaje de carga
        $('#loading-message').show();

        $.ajax({
            url: ajax_object.ajax_url,  // usa ajax_object en lugar de ajaxurl
            type: 'POST',
            data: {
                action: 'get_folder_content',
                folder_id: folderId
            },
            success: function(response) {
                if (response.success) {
                    $('#folder-content').html(response.data);
                } else {
                    $('#folder-content').html('<p>Error al cargar el contenido de la carpeta.</p>');
                }
            },
            complete: function() {
                $('#loading-message').hide();
            }
        });
        
    });
});


console.log("El  papa")