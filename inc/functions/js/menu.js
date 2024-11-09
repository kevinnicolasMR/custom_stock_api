jQuery(document).ready(function($) {
    function handleFolderClick() {
        $(document).on('click', '.subfolder.level-0', function(event) {
            event.stopPropagation(); // Prevenir la propagación del evento

            const folderElement = $(this);
            const folderId = folderElement.data('folder-id');

            // Verificar si ya hay otros submenús visibles, y ocultarlos
            $('.subfolder.level-0').each(function() {
                const otherFolderElement = $(this);
                if (otherFolderElement[0] !== folderElement[0]) {
                    // Ocultar submenú de otras carpetas level-0
                    otherFolderElement.children('.level-1-wrapper').addClass('hideContentMenu');
                }
            });

            // Verificar si el submenú ya está cargado, si no lo está, cargarlo
            if (folderElement.hasClass('loaded') || folderElement.data('isLoading')) {
                toggleSubfolders(folderElement);  // Alternar la visibilidad si ya está cargado
                return;
            }

            // Marcar como en carga para evitar solicitudes adicionales
            folderElement.data('isLoading', true);
            $('#loading-message').show();

            // Cargar el contenido de la carpeta seleccionada
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

            // Realizar solicitud AJAX para obtener las subcarpetas de nivel-1
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
                        folderElement.append(newSubfolders).addClass('loaded'); // Añadir la clase "loaded"
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

        // Manejar clics en level-1, impidiendo que la visibilidad cambie
        $(document).on('click', '.subfolder.level-1', function(event) {
            event.stopPropagation(); // Impedir que el clic en level-1 altere la visibilidad del level-0
        });
    }

    // Alternar la visibilidad de los submenús de level-1
    function toggleSubfolders(folderElement) {
        const subfoldersWrapper = folderElement.children('.level-1-wrapper');
        subfoldersWrapper.toggleClass('hideContentMenu'); // Alterna la clase hideContentMenu para ocultar o mostrar
    }

    // Inicializar el manejo de clics en las carpetas
    handleFolderClick();
});
