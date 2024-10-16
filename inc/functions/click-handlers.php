<?php
// Definir la función display_click_handler() para manejar clics en carpetas y archivos multimedia
function display_click_handler() {
    echo '
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Manejar clic en las carpetas para navegar
            document.querySelectorAll(".folder-item").forEach(function(folder) {
                folder.addEventListener("click", function() {
                    var folderId = this.getAttribute("data-folder-id");
                    var contentDiv = document.getElementById("folder-content");

                    // Limpiar el contenido previo
                    contentDiv.innerHTML = "Cargando...";

                    // Hacer la solicitud AJAX para obtener el contenido de la carpeta
                    fetch("' . esc_url(admin_url('admin-ajax.php')) . '?action=get_folder_content&folder_id=" + folderId)
                    .then(response => response.text())
                    .then(data => contentDiv.innerHTML = data)
                    .catch(error => contentDiv.innerHTML = "Error al cargar el contenido.");
                });
            });

            // Manejar clic en las imágenes para mostrar una previsualización
            document.addEventListener("click", function(event) {
                if (event.target.classList.contains("image-item")) {
                    // Obtener la URL de la imagen clicada
                    var imageUrl = event.target.getAttribute("data-image-url");

                    // Crear un div superpuesto
                    var overlay = document.createElement("div");
                    overlay.style.position = "fixed";
                    overlay.style.top = "0";
                    overlay.style.left = "0";
                    overlay.style.width = "100%";
                    overlay.style.height = "100%";
                    overlay.style.backgroundColor = "rgba(0, 0, 0, 0.8)";
                    overlay.style.display = "flex";
                    overlay.style.justifyContent = "center";
                    overlay.style.alignItems = "center";
                    overlay.style.zIndex = "1000";

                    // Crear la imagen en el div
                    var img = document.createElement("img");
                    img.src = imageUrl;
                    img.style.maxWidth = "100%";
                    img.style.maxHeight = "100%";

                    // Agregar un evento para cerrar el overlay al hacer clic
                    overlay.addEventListener("click", function() {
                        document.body.removeChild(overlay);
                    });

                    overlay.appendChild(img);
                    document.body.appendChild(overlay);
                }
            });
        });
    </script>';
}
