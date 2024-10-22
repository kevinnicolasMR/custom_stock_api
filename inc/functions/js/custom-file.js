jQuery(document).ready(function($) {
    // Evento para cargar el audio al hacer clic en el botón
    $('.load-audio').on('click', function (e) {
        e.preventDefault();
        
        // Obtener el contenedor de audio correspondiente
        var audioContainer = $(this).closest('.audio-container');
        var audioUrl = audioContainer.data('audio-url'); // Obtener la URL del audio
        
        // Mostrar el elemento de audio y reproducir
        var audioPlayer = audioContainer.find('.audio-player');
        
        // Si el audio ya está cargado, reproducirlo
        if (audioPlayer.length) {
            // Cambiar el src del elemento de audio y forzar la carga
            audioPlayer.attr('src', audioUrl);
            audioPlayer[0].load(); // Cargar el audio
            audioPlayer[0].play(); // Intentar reproducir el audio
            audioPlayer.show(); // Mostrar el elemento de audio
        } else {
            // Si no hay audio, mostrar un mensaje o realizar otra acción
            console.log('No se encontró el elemento de audio.');
        }

        // Ocultar el botón de carga después de hacer clic
        $(this).hide();

        // Manejo de errores para verificar si el audio está cargado
        var checkAudioInterval = setInterval(function() {
            // Verificar si el audio está listo para reproducir
            if (audioPlayer[0].readyState >= 2) { // readyState 2 significa que está listo para reproducir
                clearInterval(checkAudioInterval); // Detener la verificación
                console.log("Audio cargado y listo para reproducir");
            } else {
                console.log('Esperando a que el audio se cargue...');
            }
        }, 1000); // Verificar cada 1 segundo
    });
    
    console.log("Rau");
});
