jQuery(document).ready(function ($) {
    console.log("jQuery está listo");

    // Asignar el evento de clic al botón de búsqueda
    $(document).on('click', '#search-button', function () {
        var searchText = normalizeString($('#search-input').val().toLowerCase()); // Normalizar y obtener el texto del input
        console.log("Texto ingresado:", searchText); // Mostrar el texto en la consola

        // Filtrar y mostrar elementos
        filterItems(searchText);
    });

    // Asignar el evento de tecla al input de búsqueda
    $(document).on('keyup', '#search-input', function (e) {
        if (e.key === 'Enter') {
            var searchText = normalizeString($('#search-input').val().toLowerCase()); // Normalizar y obtener el texto del input
            console.log("Texto ingresado:", searchText); // Mostrar el texto en la consola

            // Filtrar y mostrar elementos
            filterItems(searchText);
        }
    });

    // Asignar el evento de clic al botón "X" para limpiar el filtro
    $(document).on('click', '#clear-button', function () {
        $('#search-input').val(''); // Limpia el campo de búsqueda
        $('.file-item').show(); // Muestra todos los elementos
        $(this).hide(); // Oculta el botón "X"
        console.log("Filtro eliminado"); // Mensaje en la consola
    });

    // Función para normalizar el texto (eliminar acentos)
    function normalizeString(text) {
        return text.normalize('NFD').replace(/[\u0300-\u036f]/g, ""); // Normaliza y elimina los acentos
    }

    // Función para filtrar los elementos según el texto de búsqueda
    function filterItems(searchText) {
        // Bandera para verificar si se encontraron coincidencias
        var encontrado = false;

        // Iterar sobre cada elemento con la clase .filter-prop-element
        $('.filter-prop-element').each(function () {
            var altText = $(this).attr('alt'); // Obtén el texto de alt directamente del elemento img
            var itemText = normalizeString($(this).closest('.file-item').text().toLowerCase()); // Normaliza el texto del contenedor

            // Compara el altText (en minúsculas) o el texto del contenedor (en minúsculas) con el texto de búsqueda (en minúsculas)
            if ((altText && normalizeString(altText).toLowerCase().includes(searchText)) || itemText.includes(searchText)) {
                $(this).closest('.file-item').show(); // Muestra el contenedor del elemento si coincide
                encontrado = true; // Cambia la bandera a verdadero
                console.log("Archivo encontrado:", altText); // Muestra los elementos que coinciden
            } else {
                $(this).closest('.file-item').hide(); // Oculta el contenedor del elemento si no coincide
            }
        });

        // Mostrar u ocultar el botón "X" según si se encontraron coincidencias
        if (encontrado) {
            $('#clear-button').show(); // Muestra el botón "X" si se encontró al menos un archivo
        } else {
            $('#clear-button').hide(); // Oculta el botón "X" si no se encontró nada
            alert("No hay ningún archivo con el nombre: " + searchText); // Mensaje si no se encuentran coincidencias
            // No ocultar elementos, ya que no hay coincidencias
            $('.file-item').show(); // Muestra todos los elementos
        }
    }
});
