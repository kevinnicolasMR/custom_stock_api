jQuery(document).ready(function($) {
    // Función para eliminar la clase `hideContentMenu` de los elementos `level-2-wrapper`
    function removeHideClassFromLevel2() {
        $('.level-2-wrapper').each(function() {
            if ($(this).hasClass('hideContentMenu')) {
                $(this).removeClass('hideContentMenu');
            }
        });
    }

    // Configurar un MutationObserver para observar cambios en el contenedor principal
    const observer = new MutationObserver((mutationsList) => {
        for (let mutation of mutationsList) {
            if (mutation.type === 'childList') {
                removeHideClassFromLevel2();
            }
        }
    });

    // Observar cambios en el contenedor donde se añaden las carpetas
    const targetNode = document.getElementById('drive-folders-container');
    if (targetNode) {
        observer.observe(targetNode, { childList: true, subtree: true });
    }

    // La función de clic para cargar carpetas y manejar la interacción sigue igual
    function handleFolderClick() {
        $(document).on('click', '.subfolder.level-0, .subfolder.level-1', function(event) {
            event.stopPropagation(); // Evitar propagación

            const folderElement = $(this);
            const folderId = folderElement.data('folder-id');
            const level = folderElement.hasClass('level-0') ? 1 : 2; // Determinar el nivel a cargar

            // Ocultar submenús de otras carpetas del mismo nivel
            const folderClassToHide = level === 1 ? '.subfolder.level-0' : '.subfolder.level-1';
            $(folderClassToHide).each(function() {
                const otherFolderElement = $(this);
                if (otherFolderElement[0] !== folderElement[0]) {
                    otherFolderElement.children('.level-' + (level + 1) + '-wrapper').addClass('hideContentMenu');
                }
            });

            if (folderElement.hasClass('loaded') || folderElement.data('isLoading')) {
                toggleSubfolders(folderElement);
                return;
            }

            folderElement.data('isLoading', true);
            $('#loading-message').show();

            // Solicitud AJAX para cargar subcarpetas del siguiente nivel
            $.ajax({
                url: ajax_object.ajax_url,
                type: 'POST',
                data: {
                    action: 'get_folder_menu',
                    folder_id: folderId,
                    level: level
                },
                success: function(response) {
                    if (response.success) {
                        const newSubfolders = $(response.data);
                        folderElement.append(newSubfolders).addClass('loaded');
                        toggleSubfolders(folderElement);
                    } else {
                        console.error('Error al cargar las subcarpetas:', response.data);
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.error('Error en la solicitud AJAX para subcarpetas:', textStatus, errorThrown);
                },
                complete: function() {
                    folderElement.data('isLoading', false);
                    $('#loading-message').hide();
                }
            });
        });

        // Evitar que los clics en `level-2` alteren la visibilidad de `level-1`
        $(document).on('click', '.subfolder.level-2', function(event) {
            event.stopPropagation(); // Impedir propagación
        });
    }

    function toggleSubfolders(folderElement) {
        const subfoldersWrapper = folderElement.children('.level-' + (parseInt(folderElement.attr('class').match(/level-(\d+)/)[1]) + 1) + '-wrapper');
        subfoldersWrapper.toggleClass('hideContentMenu');
    }

    handleFolderClick();
});
