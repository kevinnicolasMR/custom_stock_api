// Filtrador de file-items mediante un input search, aca controlamos los tildes, mayusculas, etc.

jQuery(document).ready(function ($) {

    $(document).on('click', '#search-button', function () {
        var searchText = normalizeString($('#search-input').val().toLowerCase()); 

        filterItems(searchText);
    });

    $(document).on('keyup', '#search-input', function (e) {
        if (e.key === 'Enter') {
            var searchText = normalizeString($('#search-input').val().toLowerCase()); 
            console.log("Texto ingresado:", searchText); 

            filterItems(searchText);
        }
    });

    $(document).on('click', '#clear-button', function () {
        $('#search-input').val(''); 
        $('.file-item').show(); 
        $(this).hide(); 
        console.log("Filtro eliminado"); 
    });

    function normalizeString(text) {
        return text.normalize('NFD').replace(/[\u0300-\u036f]/g, ""); 
    }

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

        if (encontrado) {
            $('#clear-button').show(); 
        } else {
            $('#clear-button').hide(); 
            alert("No hay ningún archivo con el nombre: " + searchText); 
            $('.file-item').show(); 
        }
    }
});
