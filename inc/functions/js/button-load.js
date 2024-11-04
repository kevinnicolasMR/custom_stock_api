// Boton que usamos para pedirle a Google Drive que genere mas file-items
jQuery(document).ready(function($) {
    // Función para verificar si el filtro está activado y ocultar el botón de cargar más
    function checkFilterStatus() {
        if ($('#clear-button').is(':visible')) {
            $('.button-load-more-container').hide(); // Ocultar el contenedor del botón de cargar más
        } else {
            $('.button-load-more-container').show(); // Mostrar el contenedor del botón de cargar más
        }
    }

    // Llamada inicial para verificar el estado del filtro al cargar la página
    checkFilterStatus();

    // Evento para verificar el estado cuando se hace clic en el botón de búsqueda
    $(document).on('click', '#search-button', function() {
        checkFilterStatus();
    });

    // Evento para verificar el estado cuando se hace clic en el botón de limpiar
    $(document).on('click', '#clear-button', function() {
        checkFilterStatus();
    });

    $(document).on('click', '.button-load-more-container #load-more', function() {
        const folderId = $(this).data('folder-id'); 
        const currentItems = $('.file-container > div').length; 
        const limit = 10; 
        const loadMoreButton = $(this);

        loadMoreButton.prop('disabled', true);

        $.ajax({
            url: ajax_object.ajax_url,
            type: 'POST',
            data: {
                action: 'get_folder_content',
                folder_id: folderId,
                offset: currentItems, 
                limit: limit 
            },
            success: function(response) {
                if (response.success) {
                    const newContent = $(response.data).find('.file-container').length > 0
                        ? $(response.data).find('.file-container').html()
                        : response.data;

                    // Append new content to the file container
                    $('.file-container').append(newContent);

                    // Remove old load-more button if it exists
                    $('.button-load-more-container').remove();

                    // Append the new load-more button if there's more content
                    if ($('#load-more', response.data).length > 0) {
                        $('.file-container').after('<div class="button-load-more-container">' + $('#load-more', response.data).parent().html() + '</div>');
                    }

                    // Verificar el estado del filtro después de cargar más contenido
                    checkFilterStatus();
                } else {
                    console.error(response.data);
                    loadMoreButton.show().prop('disabled', false); 
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error('Error en la solicitud AJAX:', textStatus, errorThrown);
                loadMoreButton.show().prop('disabled', false); 
            }
        });
    });
});

    
