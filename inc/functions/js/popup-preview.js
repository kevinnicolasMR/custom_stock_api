jQuery(document).ready(function ($) {

    // Evento para capturar el clic en el área de hover para imágenes y videos
    $('#folder-content').on('click', '.hover-general-information-img, .hover-general-information-video', function (event) {
        // Verifica si el clic fue en el botón de descarga
        var isDownloadButton = $(event.target).closest('.download-button').length > 0;

        // Si el clic fue en el botón de descarga, permite que el evento continúe y no se muestra el popup
        if (isDownloadButton) {
            return;
        }

        // Si no es un botón de descarga, se previene el comportamiento por defecto y se muestra el popup
        event.preventDefault();

        // Verifica si se trata de un archivo de imagen
        var imageItem = $(this).closest('.file-item-img').find('.image-item');
        if (imageItem.length > 0) {
            var imageUrl = imageItem.data('image-url');
            var fileId = imageItem.data('file-id');

            var highResImageUrl = imageUrl.replace(/=s\d+/, '=s800');
            var overlay = createOverlay();

            var img = $('<img>').attr('src', highResImageUrl).css({
                maxWidth: '90%',
                maxHeight: '90%',
                borderRadius: '25px'
            });

            overlay.append(img);

            var downloadUrl = createDownloadUrl(fileId);
            var downloadButton = createDownloadButton(downloadUrl);

            var closeButton = createCloseButton(overlay);

            overlay.append(downloadButton).append(closeButton);
            $('body').append(overlay);

            img.on('click', function (event) {
                event.stopPropagation();
            });
        }

        // Verifica si se trata de un archivo de video
        var videoItem = $(this).closest('.file-item-video').find('.video-item');
        if (videoItem.length > 0) {
            var videoUrl = videoItem.data('video-url');
            var videoFileId = extractFileIdFromUrl(videoUrl);
            var videoDownloadUrl = createDownloadUrl(videoFileId);

            var overlay = createOverlay();

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

            var closeButton = createCloseButton(overlay);
            var videoDownloadButton = createDownloadButton(videoDownloadUrl);

            overlay.append(videoDownloadButton).append(closeButton);
            $('body').append(overlay);
        }
    });

    function createDownloadUrl(fileId) {
        return 'https://drive.google.com/uc?export=download&id=' + fileId;
    }

    function extractFileIdFromUrl(url) {
        var match = url.match(/\/d\/([a-zA-Z0-9_-]+)\//);
        return match ? match[1] : null;
    }

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

        overlay.on('click', function () {
            overlay.remove();
        });

        return overlay;
    }

    function createDownloadButton(downloadUrl) {
        return $('<a>Descargar</a>').attr('href', downloadUrl).attr('target', '_blank').css({
            position: 'absolute',
            top: '20px',
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
