jQuery(document).ready(function($) {
    // Delegar el evento de clic para las imágenes con la clase 'image-item'
    $('#folder-content').on('click', '.image-item', function(event) {
        event.preventDefault(); // Evitar la acción predeterminada (si existe)

        // Obtener la URL de la imagen y el ID del archivo de Google Drive
        var imageUrl = $(this).data('image-url');
        var fileId = $(this).data('file-id'); // Asegúrate de que este valor exista

        if (!fileId) {
            console.error("ID del archivo no encontrado");
            return;
        }

        // Crear la URL de descarga directa de Google Drive
        var downloadUrl = 'https://drive.google.com/uc?export=download&id=' + fileId;

        // Crear el contenedor del popup (overlay)
        var overlay = $('<div></div>').css({
            position: 'fixed',
            top: 0,
            left: 0,
            width: '100%',
            height: '100%',
            backgroundColor: 'rgba(0, 0, 0, 0.8)',
            display: 'flex',
            justifyContent: 'center',
            alignItems: 'center',
            zIndex: 10000 // Asegurarse de que esté encima de todo
        });

        // Crear el elemento de imagen dentro del popup
        var img = $('<img>').attr('src', imageUrl).css({
            maxWidth: '90%',
            maxHeight: '90%',
            border: '5px solid white',
            borderRadius: '10px'
        });

        // Botón de cierre
        var closeButton = $('<span>&times;</span>').css({
            position: 'absolute',
            top: '10px',
            right: '20px',
            fontSize: '30px',
            color: 'white',
            cursor: 'pointer'
        });

        // Botón de descarga
        var downloadButton = $('<a>Descargar</a>').attr('href', downloadUrl).attr('target', '_blank').css({
            position: 'absolute',
            top: '10px',
            right: '60px', // A la izquierda del botón de cerrar
            fontSize: '18px',
            color: 'white',
            textDecoration: 'none',
            padding: '5px 10px',
            backgroundColor: '#28a745',
            borderRadius: '5px',
            cursor: 'pointer'
        });

        // Al hacer clic en el botón de cierre, eliminar el popup
        closeButton.on('click', function() {
            overlay.remove();
        });

        // Al hacer clic en cualquier parte del overlay, también cerrar el popup
        overlay.on('click', function() {
            overlay.remove();
        });

        // Evitar que el clic en la imagen cierre el popup
        img.on('click', function(event) {
            event.stopPropagation();
        });

        // Agregar la imagen, el botón de descarga, y el botón de cierre al overlay
        overlay.append(img).append(downloadButton).append(closeButton);

        // Agregar el overlay al cuerpo del documento
        $('body').append(overlay);
    });
});
