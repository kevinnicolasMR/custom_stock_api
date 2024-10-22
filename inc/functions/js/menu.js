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
});


jQuery(document).ready(function($) {
    // Escucha el clic en el botón "Cargar audio" usando delegación de eventos
    $('#folder-content').on('click', '.load-audio', function() {
        var container = $(this).closest('.audio-container'); // Encuentra el contenedor del audio
        var audioUrl = container.data('audio-url'); // Obtén la URL del audio del atributo data
        var audioContent = container.find('.audio-content'); // Encuentra el div donde se insertará el iframe
        
        // Verifica si la URL es correcta
        console.log('URL de audio:', audioUrl);

        // Inserta el iframe solo si aún no se ha cargado
        if (audioContent.is(':empty')) {
            audioContent.html('<iframe src="' + audioUrl + '" width="100%" height="85" frameborder="0" allow="autoplay"></iframe>');
        }
        
        // Cambia el botón a "Reproduciendo..." y lo desactiva
        $(this).text('Reproduciendo...');
        $(this).prop('disabled', true); // Desactiva el botón para evitar múltiples clics
    });
});
