jQuery(document).ready(function ($) {
    // Filtrar elementos según el texto de búsqueda
    $('#search-input').on('keyup', function () {
        var searchText = $(this).val().toLowerCase();

        $('.file-item').each(function () {
            var itemName = $(this).text().toLowerCase();
            if (itemName.includes(searchText)) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });
});
