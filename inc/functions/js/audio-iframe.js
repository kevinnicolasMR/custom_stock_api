// El siguiente código se enfoca en la generación del iframe y la modificación del ícono de carga de la tarjeta de audio

jQuery(document).ready(function($) {
    $('#folder-content').on('click', '.load-audio', function (e) {
        e.preventDefault();
        
        var button = $(this);
        
        button.prop('disabled', true);

        button.html('<i class="fas fa-spinner fa-spin"></i>'); 

        var container = button.closest('.file-item'); 
        var audioUrl = container.find('.audio-container').data('audio-url'); 
        var audioContent = container.find('.img-preview-audio-google-drive .audio-content'); 
        var audioContainer = container.find('.audio-container');

        audioContainer.append('<div class="loading-message">Cargando audio...</div>');

        if (audioContent.is(':empty')) {
            audioContent.html('<iframe src="' + audioUrl + '" display="flex" width="100%" height="100%" frameborder="0" allow="autoplay"></iframe>');
        }

        setTimeout(function() {
            button.html('<i class="fas fa-volume-up"></i>');
            container.find('.img-example-audio-google-drive').addClass('img-example-audio-google-drive-opacity');
            
            if (audioContent.length > 0) {
                audioContent.addClass('img-example-audio-google-drive-opacity');
            } else {
                console.log('El elemento audioContent no existe.');
            }

            audioContainer.find('.loading-message').remove();
            audioContainer.append('<div class="finished-message">Audio descargado.</div>');

            // Reactivar el botón (opcional)
            // button.prop('disabled', false);
        }, 5000);
    });
});
