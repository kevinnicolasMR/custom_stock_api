<?php
function render_image_template($file) {
    ob_start();
    ?>
    <div class="file-item file-item-img">
        <img src="<?php echo esc_url($file->thumbnailLink); ?>" alt="<?php echo esc_attr($file->name); ?>" class="image-item filter-prop-element" data-image-url="<?php echo esc_url($file->thumbnailLink); ?>" data-file-id="<?php echo esc_attr($file->id); ?>">
        
        <!-- Div de descarga debajo de la imagen -->
        <div class="download-icon button-downloead-hidde">
            <a href="https://drive.google.com/uc?export=download&id=<?php echo esc_attr($file->id); ?>" download="<?php echo esc_attr($file->name); ?>" class="download-button" title="Descargar imagen">
                <i class="fas fa-download"></i> 
            </a>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
?>
