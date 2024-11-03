// Navegabilidad al momento de hacer click en un file-item-folder que nos permite movernos entre carpetas.

jQuery(document).ready(function($) {
    $('#folder-content').on('click', '.clickable-folder', function() {
        var folderId = $(this).data('folder-id');

        $('#loading-message').show();

        $.ajax({
            url: ajax_object.ajax_url,  
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


