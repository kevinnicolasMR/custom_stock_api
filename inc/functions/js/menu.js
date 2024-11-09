jQuery(document).ready(function($) {
    function handleFolderClick() {
        $(document).on('click', '.subfolder.level-0', function(event) {
            event.stopPropagation();
            const folderElement = $(this);
            const folderId = folderElement.data('folder-id');

            // Evitar duplicación: Verificar si ya está cargando o si ya tiene subcarpetas cargadas
            if (folderElement.hasClass('loaded') || folderElement.data('isLoading')) {
                toggleSubfolders(folderElement);  // Alterna la visibilidad si ya está cargado
                return;
            }

            // Marcar como en carga para evitar solicitudes adicionales
            folderElement.data('isLoading', true);

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

            // Realizar solicitud AJAX para obtener subcarpetas
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
                        // Agregar subcarpetas e indicar que ya están cargadas
                        const newSubfolders = $(response.data);
                        folderElement.append(newSubfolders).addClass('loaded'); // Añadimos la clase "loaded"
                        toggleSubfolders(folderElement); // Mostrar las subcarpetas recién cargadas
                    } else {
                        console.error('Error al cargar las subcarpetas:', response.data);
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.error('Error en la solicitud AJAX para subcarpetas:', textStatus, errorThrown);
                },
                complete: function() {
                    folderElement.data('isLoading', false); // Permitir nuevos clics
                }
            });
        });
    }

    function toggleSubfolders(folderElement) {
        const subfolders = folderElement.children('.level-1-wrapper').children('.subfolder');
        subfolders.each(function() {
            toggleVisibility(this);
        });
    }

    function toggleVisibility(element) {
        $(element).toggleClass('hideContentMenu visibleContentMenu');
    }

    // Inicializar manejo de clics en las carpetas
    handleFolderClick();
});
