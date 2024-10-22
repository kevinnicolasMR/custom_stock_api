jQuery(document).ready(function ($) {

    // Evento para imágenes
    $('#folder-content').on('click', '.image-item', function (event) {
        event.preventDefault();

        var imageUrl = $(this).data('image-url'); // Obtén la URL de la imagen
        var fileId = $(this).data('file-id'); // Obtén el ID del archivo

        // Cambiar el tamaño de la imagen para alta resolución
        var highResImageUrl = imageUrl.replace(/=s\d+/, '=s800'); // Cambia '800' por el tamaño que desees

        console.log("URL de la imagen alta resolución:", highResImageUrl);

        // Crear el contenedor del popup (overlay)
        var overlay = createOverlay();

        // Crear la imagen
        var img = $('<img>').attr('src', highResImageUrl).css({
            maxWidth: '90%',
            maxHeight: '90%',
            borderRadius: '25px'
        });

        overlay.append(img);

        // Botón de descarga
        var downloadUrl = createDownloadUrl(fileId); // Llama a la función para obtener la URL de descarga
        var downloadButton = createDownloadButton(downloadUrl);

        // Botón de cierre
        var closeButton = createCloseButton(overlay);

        // Agregar los botones y la imagen al overlay
        overlay.append(downloadButton).append(closeButton);
        $('body').append(overlay);

        // Evitar que el clic en la imagen cierre el popup
        img.on('click', function (event) {
            event.stopPropagation();
        });
    });

    // Evento para videos
    $('#folder-content').on('click', '.video-item', function (event) {
        event.preventDefault();

        var videoUrl = $(this).data('video-url'); // Obtén la URL del video

        console.log("URL del video:", videoUrl);

        // Crear el contenedor del popup (overlay)
        var overlay = createOverlay();

        // Crear el iframe para el video
        var iframe = $('<iframe>', {
            src: videoUrl,
            width: '60%',
            height: '80%',
            frameborder: '0',
            allow: 'autoplay; encrypted-media',
            allowfullscreen: true
        }).css({
            borderRadius: '25px'
        });

        overlay.append(iframe);

        // Botón de cierre
        var closeButton = createCloseButton(overlay);

        // Botón de descarga
        var videoFileId = $(this).data('file-id'); // Obtén el ID del archivo para el video
        var videoDownloadUrl = createDownloadUrl(videoFileId); // Llama a la función para obtener la URL de descarga
        var videoDownloadButton = createDownloadButton(videoDownloadUrl); // Crear botón de descarga

        // Agregar el botón de descarga y el de cierre al overlay
        overlay.append(videoDownloadButton).append(closeButton);
        $('body').append(overlay);
    });

    // Función para crear la URL de descarga
    function createDownloadUrl(fileId) {
        return 'https://drive.google.com/uc?export=download&id=' + fileId; // URL de descarga directa
    }

    // Función para crear el overlay
    function createOverlay() {
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

        // Cerrar el popup al hacer clic en el overlay
        overlay.on('click', function () {
            overlay.remove();
        });

        return overlay;
    }

    // Función para crear el botón de descarga
    function createDownloadButton(downloadUrl) {
        return $('<a>Descargar</a>').attr('href', downloadUrl).attr('target', '_blank').css({
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
    }

    // Función para crear el botón de cierre
    function createCloseButton(overlay) {
        var closeButton = $('<span>&times;</span>').css({
            position: 'absolute',
            top: '28px',
            right: '30px',
            fontSize: '50px',
            color: 'white',
            cursor: 'pointer'
        });

        closeButton.on('click', function () {
            overlay.remove();
        });

        return closeButton;
    }
});
