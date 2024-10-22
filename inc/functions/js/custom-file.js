jQuery(document).ready(function($) {
    // Escucha el clic en el botón "Cargar audio"
    $('#folder-content').on('click', '.load-audio', function (e) {
        e.preventDefault();
        
        // Referencia al botón
        var button = $(this);
        
        // Cambiar el ícono/texto del botón a "goku"
        button.html('<i class="fas fa-spinner fa-spin"></i>'); 

        // Después de 5 segundos, cambiar el texto a "Holi"
        setTimeout(function() {
            button.html('<i class="fas fa-play"></i>'); 
        }, 5000);

        // Lógica del iframe para cargar el audio
        var container = button.closest('.audio-container'); // Encuentra el contenedor del audio
        var audioUrl = container.data('audio-url'); // Obtén la URL del audio del atributo data
        var audioContent = container.find('.audio-content'); // Encuentra el div donde se insertará el iframe

        // Verifica si la URL es correcta
        console.log('URL de audio:', audioUrl);

        // Inserta el iframe solo si aún no se ha cargado
        if (audioContent.is(':empty')) {
            audioContent.html('<iframe src="' + audioUrl + '" width="100%" height="85" frameborder="0" allow="autoplay"></iframe>');
        }
    });

    console.log("Rauluighiug");
});
