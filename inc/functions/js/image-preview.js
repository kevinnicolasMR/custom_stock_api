jQuery(document).ready(function ($) {

    console.log("Goku god, causa");

    $('#folder-content').on('click', '.image-item', function (event) {
        event.preventDefault();
        
        var imageUrl = $(this).data('image-url'); // Obtén la URL de la imagen
        var fileId = $(this).data('file-id'); // Obtén el ID del archivo

        // Cambiar el tamaño de la imagen para alta resolución
        var highResImageUrl = imageUrl.replace(/=s\d+/, '=s800'); // Cambia '800' por el tamaño que desees

        console.log("URL de la imagen alta resolución:", highResImageUrl);

        // Crear el contenedor del popup (overlay)
        var overlay = $('<div></div>').css({
            position: 'fixed',
            top: 0,
            left: 0,
            width: '100%',
            height: '100%',
            backgroundColor: 'rgba(0, 0, 0, 0.9)',
            display: 'flex',
            justifyContent: 'center',
            alignItems: 'center',
            zIndex: 10000
        });

        // Crear la imagen
        var img = $('<img>').attr('src', highResImageUrl).css({
            maxWidth: '90%',
            maxHeight: '90%',
            borderRadius: '25px'
        });

        overlay.append(img);

        // Botón de cierre
        var closeButton = $('<span>&times;</span>').css({
            position: 'absolute',
            top: '28px',
            right: '30px',
            fontSize: '50px',
            color: 'white',
            cursor: 'pointer'
        });

        // Botón de descarga
        var downloadUrl = 'https://drive.google.com/uc?export=download&id=' + fileId; // URL de descarga directa
        var downloadButton = $('<a>Descargar</a>').attr('href', downloadUrl).attr('target', '_blank').css({
            position: 'absolute',
            top: '50px',
            right: '80px',
            fontSize: '18px',
            color: 'white',
            textDecoration: 'none',
            padding: '5px 10px',
            backgroundColor: '#28a745',
            borderRadius: '5px',
            cursor: 'pointer'
        });

        closeButton.on('click', function () {
            overlay.remove();
        });

        // Agregar los botones y la imagen al overlay
        overlay.append(downloadButton).append(closeButton);
        $('body').append(overlay);

        // Evitar que el clic en la imagen cierre el popup
        img.on('click', function (event) {
            event.stopPropagation();
        });

        // Cerrar el popup al hacer clic en el overlay
        overlay.on('click', function () {
            overlay.remove();
        });
    });
});
