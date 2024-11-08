// Funcionalidad del menú lateral izquierdo

jQuery(document).ready(function($) {
    function handleFolderClick() {
        // Usamos delegación de eventos para manejar clics en carpetas de level-0 generadas dinámicamente
        $(document).on('click', '.subfolder.level-0', function(event) {
            event.stopPropagation();

            const folderId = $(this).data('folder-id');
            console.log('ID de carpeta seleccionada:', folderId);

            $('#loading-message').show();

            // Cargar contenido de la carpeta seleccionada
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

            // Cargar subcarpetas de level-1 si aún no están cargadas
            if ($(this).children('.level-1-wrapper').length === 0) {
                $.ajax({
                    url: ajax_object.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'get_folder_menu',
                        folder_id: folderId,
                        level: 1
                    },
                    success: function(response) {
                        if (response.success) {
                            // Agregar las subcarpetas dentro del elemento clicado, ocultas inicialmente
                            const newSubfolders = $(response.data).addClass('hideContentMenu');
                            $(event.currentTarget).append(newSubfolders);
                            toggleSubfolders(event.currentTarget); // Mostrar las subcarpetas recién cargadas
                        } else {
                            console.error('Error al cargar las subcarpetas:', response.data);
                        }
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        console.error('Error en la solicitud AJAX para subcarpetas:', textStatus, errorThrown);
                    }
                });
            } else {
                // Alternar visibilidad de las subcarpetas si ya están cargadas
                toggleSubfolders(this);
            }
        });
    }

    function toggleSubfolders(folderElement) {
        const subfolders = $(folderElement).children('.level-1-wrapper').children('.subfolder');

        // Alternar visibilidad de las subcarpetas específicas de la carpeta actual
        subfolders.each(function() {
            toggleVisibility(this);
        });
    }

    function toggleVisibility(element) {
        if ($(element).hasClass('hideContentMenu')) {
            $(element).removeClass('hideContentMenu').addClass('visibleContentMenu');
        } else if ($(element).hasClass('visibleContentMenu')) {
            $(element).removeClass('visibleContentMenu').addClass('hideContentMenu');
        }
    }

    // Inicializar manejo de clics en las carpetas
    handleFolderClick();
});
