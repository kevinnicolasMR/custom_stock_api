// Verifica si la URL es correcta
console.log("Menu JS activo");

jQuery(document).ready(function($) {
    // Agregar un evento de clic a los elementos del menú de carpetas
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
                action: 'get_folder_content', // Esta acción debe coincidir con la que vamos a definir en PHP
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

        // Cambiar la clase de visibilidad de las subcarpetas
        $(this).siblings().each(function() {
            toggleVisibility(this); // Ocultar otras subcarpetas
        });

        // Alternar la visibilidad de la carpeta seleccionada
        toggleVisibility(this);

        // Alternar subcarpetas
        const subfolders = $(this).find('.subfolder');
        if (subfolders.length) {
            subfolders.each(function() {
                toggleVisibility(this); // Alternar la visibilidad de las subcarpetas
            });
        }
    });

    // Función para alternar clases
    function toggleVisibility(element) {
        if ($(element).hasClass('hideContentMenu')) {
            $(element).removeClass('hideContentMenu').addClass('visibleContentMenu');
        } else if ($(element).hasClass('visibleContentMenu')) {
            $(element).removeClass('visibleContentMenu').addClass('hideContentMenu');
        }
    }
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
