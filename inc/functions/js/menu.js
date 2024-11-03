// Verifica si la URL es correcta
jQuery(document).ready(function($) {
    // Función para manejar el clic en la carpeta y el renderizado del contenido
    function handleFolderClick() {
        $('.subfolder').on('click', function(event) {
            event.stopPropagation(); // Evita que el evento se propague a otros elementos

            // Obtener el ID de la carpeta que se hizo clic
            const folderId = $(this).data('folder-id');
            console.log('ID de carpeta seleccionada:', folderId); // Mostrar el ID en la consola

            // Mostrar el mensaje de carga
            $('#loading-message').show();

            // Realizar una solicitud AJAX para obtener el contenido de la carpeta
            $.ajax({
                url: ajax_object.ajax_url, // Usar ajax_object.ajax_url
                type: 'POST',
                data: {
                    action: 'get_folder_content', // Acción definida en PHP
                    folder_id: folderId // Enviar el ID de la carpeta seleccionada
                },
                success: function(response) {
                    if (response.success) {
                        $('#folder-content').html(response.data); // Renderizar el contenido en el div correspondiente
                    } else {
                        console.error(response.data);
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.error('Error en la solicitud AJAX:', textStatus, errorThrown);
                },
                complete: function() {
                    // Ocultar el mensaje de carga
                    $('#loading-message').hide();
                }
            });

            // Llamada a la función de manejo de clases para subcarpetas
            toggleSubfolders(this);
        });
    }

    // Función para alternar la visibilidad de las subcarpetas
    function toggleSubfolders(folderElement) {
        const currentLevel = $(folderElement).attr('class').match(/level-\d+/)[0]; // Obtener el nivel actual (ej. 'level-0', 'level-1')
        
        // Ocultar las subcarpetas visibles de todos los demás elementos del mismo nivel
        $(folderElement).siblings('.subfolder').each(function() {
            $(this).find('.subfolder').each(function() {
                // Si algún elemento del mismo nivel está visible, lo ocultamos
                $(this).removeClass('visibleContentMenu').addClass('hideContentMenu');
            });
        });

        // Alternar la visibilidad de las subcarpetas del elemento actual
        const subfolders = $(folderElement).children('.subfolder');
        if (subfolders.length) {
            subfolders.each(function() {
                toggleVisibility(this); // Alternar visibilidad solo para las subcarpetas directas
            });
        }
    }

    // Función para alternar clases de visibilidad
    function toggleVisibility(element) {
        if ($(element).hasClass('hideContentMenu')) {
            $(element).removeClass('hideContentMenu').addClass('visibleContentMenu');
        } else if ($(element).hasClass('visibleContentMenu')) {
            $(element).removeClass('visibleContentMenu').addClass('hideContentMenu');
        }
    }

    // Iniciar el manejo de eventos
    handleFolderClick();

    
    // Función para manejar el clic en el botón "Ver más contenido"
$(document).on('click', '#load-more', function() {
    const folderId = $(this).data('folder-id'); // Obtener el ID de la carpeta actual
    const currentItems = $('.file-container > div').length; // Contar los elementos actuales mostrados
    const limit = 5; // Límite de elementos a cargar cada vez

    // Realizar una solicitud AJAX para obtener el contenido de la carpeta
    $.ajax({
        url: ajax_object.ajax_url,
        type: 'POST',
        data: {
            action: 'get_folder_content',
            folder_id: folderId,
            offset: currentItems, // Enviar el número de elementos ya mostrados
            limit: limit // Enviar el límite para la próxima carga
        },
        success: function(response) {
            if (response.success) {
                // Agregar nuevos elementos al contenedor
                $('#folder-content').append(response.data);
                
                // Verificar si aún hay más elementos para mostrar
                if (response.more_content_available) {
                    $('#load-more').show(); // Mostrar el botón "Ver más contenido"
                } else {
                    $('#load-more').hide(); // Ocultar el botón si no hay más elementos
                }
            } else {
                console.error(response.data);
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.error('Error en la solicitud AJAX:', textStatus, errorThrown);
        }
    });
});

















});


