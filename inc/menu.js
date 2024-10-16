console.log("El Script está activo");
console.log(ajax_object.ajax_url); // Verifica si la URL es correcta

document.addEventListener('DOMContentLoaded', function() {
    // Selecciona todos los elementos con la clase 'level-0'
    const level0Elements = document.querySelectorAll('.level-0');

    // Itera sobre cada elemento 'level-0' y añade un evento de click
    level0Elements.forEach(function(level0Element) {
        level0Element.addEventListener('click', function() {
            // Busca todos los hijos con la clase 'level-1'
            const level1Elements = this.querySelectorAll('.level-1');

            // Itera sobre cada hijo con la clase 'level-1'
            level1Elements.forEach(function(level1Element) {
                // Alterna las clases 'hideContentMenu' y 'visibleContentMenu' para level-1
                toggleVisibility(level1Element);
            });
        });
    });

    // Añadir evento de clic para cada nivel-1 para manejar level-2
    document.querySelectorAll('.level-1').forEach(function(level1Element) {
        level1Element.addEventListener('click', function(event) {
            // Evita que el clic en level-1 active el clic en level-0
            event.stopPropagation();

            // Busca todos los hijos con la clase 'level-2' dentro de este level-1
            const level2Elements = this.querySelectorAll('.level-2');

            // Itera sobre cada hijo con la clase 'level-2'
            level2Elements.forEach(function(level2Element) {
                // Alterna las clases 'hideContentMenu' y 'visibleContentMenu' para level-2
                toggleVisibility(level2Element);
            });
        });
    });

    // Función para alternar clases
    function toggleVisibility(element) {
        if (element.classList.contains('hideContentMenu')) {
            element.classList.remove('hideContentMenu');
            element.classList.add('visibleContentMenu');
        } else if (element.classList.contains('visibleContentMenu')) {
            element.classList.remove('visibleContentMenu');
            element.classList.add('hideContentMenu');
        }
    }
})

jQuery(document).ready(function($) {
    $(document).on('click', '.subfolder', function() {
        var folderId = $(this).data('folder-id');

        $.ajax({
            url: ajax_object.ajax_url, // Usar ajax_object.ajax_url
            type: 'POST',
            data: {
                action: 'get_folder_content',
                folder_id: folderId
            },
            success: function(response) {
                if (response.success) {
                    $('#folder-content').html(response.data);
                } else {
                    console.error(response.data);
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error('Error en la solicitud AJAX:', textStatus, errorThrown);
            }
        });
    });
});