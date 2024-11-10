// Filtrador de file-items mediante un input search, aca controlamos los tildes, mayusculas, etc.
jQuery(document).ready(function ($) {

    // Acción cuando se hace clic en el botón de búsqueda
    $(document).on('click', '#search-button', function () {
        var searchText = normalizeString($('#search-input').val().toLowerCase()); 
        filterItems(searchText);
    });

    // Acción cuando se presiona una tecla en el input de búsqueda
    $(document).on('keyup', '#search-input', function (e) {
        if (e.key === 'Enter') {
            var searchText = normalizeString($('#search-input').val().toLowerCase()); 
            console.log("Texto ingresado:", searchText); 
            filterItems(searchText);
        }
    });

    // Acción cuando se hace clic en el botón de limpiar el filtro
    $(document).on('click', '#clear-button', function () {
        $('#search-input').val(''); 
        $('.file-item').show(); 
        $(this).hide(); 
        $('#load-more').show(); // Mostrar el botón de "Cargar más"
        console.log("Filtro eliminado"); 
    });

    // Normalizar texto, eliminando tildes y caracteres especiales
    function normalizeString(text) {
        return text.normalize('NFD').replace(/[\u0300-\u036f]/g, ""); 
    }

    // Función para filtrar los elementos
    function filterItems(searchText) {
        var encontrado = false;

        $('.filter-prop-element').each(function () {
            var altText = $(this).attr('alt'); 
            var itemText = normalizeString($(this).closest('.file-item').text().toLowerCase()); 

            if ((altText && normalizeString(altText).toLowerCase().includes(searchText)) || itemText.includes(searchText)) {
                $(this).closest('.file-item').show(); 
                encontrado = true; 
                console.log("Archivo encontrado:", altText); 
            } else {
                $(this).closest('.file-item').hide(); 
            }
        });

        // Si se encontró algún archivo, mostrar el botón de limpiar y ocultar el de cargar más
        if (encontrado) {
            $('#clear-button').show(); 
            $('#load-more').hide(); // Ocultar el botón "Cargar más"
        } else {
            $('#clear-button').hide(); 
            alert("No hay ningún archivo con el nombre: " + searchText); 
            $('.file-item').show(); 
            $('#load-more').show(); // Asegurarse de que el botón "Cargar más" esté visible
        }
    }
});
