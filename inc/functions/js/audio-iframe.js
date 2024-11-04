// El siguiente codigo se enfoca en la generacion del Iframe y la modificacion del load icon de la tarjeta de audio

jQuery(document).ready(function($) {
    $('#folder-content').on('click', '.load-audio', function (e) {
        e.preventDefault();
        
        var button = $(this);
        
        // Desactivar el botón para evitar múltiples clics
        button.prop('disabled', true);

        // Cambiar el ícono y el texto al iniciar la carga
        button.html('<i class="fas fa-spinner fa-spin"></i><p>Cargando...</p>'); 

        setTimeout(function() {
            // Cambiar el ícono y el texto al finalizar la carga
            button.html('<i class="fas fa-play"></i><p>Audio activado</p>'); 
            // Si quieres habilitar el botón de nuevo, descomenta la siguiente línea
            // button.prop('disabled', false);
        }, 5000);

        var container = button.closest('.file-item'); 
        var audioUrl = container.find('.audio-container').data('audio-url'); 
        var audioContent = container.find('.img-preview-audio-google-drive .audio-content'); 

        if (audioContent.is(':empty')) {
            audioContent.html('<iframe src="' + audioUrl + '" display="flex" width="100%" height="100%" frameborder="0" allow="autoplay"></iframe>');
        }
    });
});
