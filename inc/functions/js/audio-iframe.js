jQuery(document).ready(function($) {
    // Escucha el clic en el botón "Cargar audio"
    $('#folder-content').on('click', '.load-audio', function (e) {
        e.preventDefault();
        
        // Referencia al botón
        var button = $(this);
        
        // Cambiar el ícono/texto del botón a "goku" (ícono de carga)
        button.html('<i class="fas fa-spinner fa-spin"></i>'); 

        // Después de 5 segundos, cambiar el texto a "Holi" y agregar el ícono de Play con texto "Pedro"
        setTimeout(function() {
            button.html('<i class="fas fa-play"></i>'); 
        }, 5000);

        // Lógica del iframe para cargar el audio
        var container = button.closest('.file-item'); // Encuentra el contenedor principal
        var audioUrl = container.find('.audio-container').data('audio-url'); // Obtén la URL del audio del atributo data
        var audioContent = container.find('.img-preview-audio-google-drive .audio-content'); // Encuentra el div audio-content dentro de img-preview-audio-google-drive

        // Verifica si la URL es correcta
        console.log('URL de audio:', audioUrl);

        // Inserta el iframe solo si aún no se ha cargado
        if (audioContent.is(':empty')) {
            audioContent.html('<iframe src="' + audioUrl + '" display="flex" width="100%" height="100%" frameborder="0" allow="autoplay"></iframe>');
        }
    });

    console.log("Rauluighiug");
});
