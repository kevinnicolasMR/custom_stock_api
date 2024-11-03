// Funcionalidad del menu lateral izquierdo

jQuery(document).ready(function($) {
    function handleFolderClick() {
        $('.subfolder').on('click', function(event) {
            event.stopPropagation(); 

            const folderId = $(this).data('folder-id');
            console.log('ID de carpeta seleccionada:', folderId); 

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
                        console.error(response.data);
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.error('Error en la solicitud AJAX:', textStatus, errorThrown);
                },
                complete: function() {
                    $('#loading-message').hide();
                }
            });
            toggleSubfolders(this);
        });
    }

    function toggleSubfolders(folderElement) {
        const currentLevel = $(folderElement).attr('class').match(/level-\d+/)[0]; 
        
        $(folderElement).siblings('.subfolder').each(function() {
            $(this).find('.subfolder').each(function() {
                $(this).removeClass('visibleContentMenu').addClass('hideContentMenu');
            });
        });

        const subfolders = $(folderElement).children('.subfolder');
        if (subfolders.length) {
            subfolders.each(function() {
                toggleVisibility(this); 
            });
        }
    }

    function toggleVisibility(element) {
        if ($(element).hasClass('hideContentMenu')) {
            $(element).removeClass('hideContentMenu').addClass('visibleContentMenu');
        } else if ($(element).hasClass('visibleContentMenu')) {
            $(element).removeClass('visibleContentMenu').addClass('hideContentMenu');
        }
    }

    handleFolderClick();
});


