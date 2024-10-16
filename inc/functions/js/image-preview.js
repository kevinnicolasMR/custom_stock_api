document.addEventListener("DOMContentLoaded", function() {
    // Función para manejar el clic en las imágenes
    function handleImageClick(imageElement) {
        var fullImage = document.getElementById("full-image");
        var previewDiv = document.getElementById("image-preview");
        
        fullImage.src = imageElement.getAttribute("data-full"); // Obtener la URL de la imagen completa
        previewDiv.style.display = "flex"; // Mostrar el div de previsualización
    }

    // Manejar clics en las imágenes
    document.querySelectorAll(".preview-image").forEach(function(image) {
        image.addEventListener("click", function() {
            handleImageClick(this);
        });
    });

    // Manejar clic para cerrar la previsualización
    document.getElementById("close-preview").addEventListener("click", function() {
        document.getElementById("image-preview").style.display = "none"; // Ocultar el div
    });
});
