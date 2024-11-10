// Boton que usamos para pedirle a Google Drive que genere mas file-items
jQuery(document).ready(function($) {
    function checkFilterStatus() {
        if ($('#clear-button').is(':visible')) {
            $('.button-load-more-container').hide(); 
        } else {
            $('.button-load-more-container').show(); 
        }
    }

    checkFilterStatus();

    $(document).on('click', '#search-button', function() {
        checkFilterStatus();
    });

    $(document).on('click', '#clear-button', function() {
        checkFilterStatus();
    });

    $(document).on('click', '.button-load-more-container #load-more', function() {
        const loadMoreButton = $(this);
        const folderId = loadMoreButton.data('folder-id');
        const currentItems = $('.file-container > div').length;
        const limit = 10;

        // Agregar la clase para eliminar el borde y hacer el fondo transparente
        loadMoreButton.addClass('button-loading');

        // Reemplaza el texto del botón con el spinner
        loadMoreButton.html('<div class="spinner"></div>');

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

                    $('.file-container').append(newContent);

                    $('.button-load-more-container').remove();

                    if ($('#load-more', response.data).length > 0) {
                        $('.file-container').after('<div class="button-load-more-container">' + $('#load-more', response.data).parent().html() + '</div>');
                    }

                    checkFilterStatus();
                } else {
                    console.error(response.data);
                    loadMoreButton.html('Ver más contenido').show().prop('disabled', false); // Restaura el texto y habilita el botón
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error('Error en la solicitud AJAX:', textStatus, errorThrown);
                loadMoreButton.html('Ver más contenido').show().prop('disabled', false); // Restaura el texto y habilita el botón
            },
            complete: function() {
                // Quitar la clase después de que la carga haya terminado
                loadMoreButton.removeClass('button-loading');
            }
        });
    });
});
