// Boton que usamos para pedirle a Google Drive que genere mas file-items

jQuery(document).ready(function($) {

$(document).on('click', '#load-more', function() {
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

                $('.file-container').append(newContent);

                if ($('#load-more', response.data).length > 0) {
                    loadMoreButton.show().prop('disabled', false); 
                } else {
                    loadMoreButton.remove(); 
                }
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
    
