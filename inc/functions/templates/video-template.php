<?php
function render_video_template($file, $customThumbnail = null, $thumbnailFound = false) {
    ob_start();

    // Título acortado
    $shortTitle = mb_strimwidth(esc_html($file->name), 0, 25, '...');

    // Determina la URL de la miniatura
    $thumbnailUrl = $customThumbnail ? esc_url($customThumbnail) : esc_url($file->thumbnailLink);

    ?>
    <div class="file-item file-item-video">
        <!-- Imagen de miniatura -->
        <img src="<?php echo $thumbnailUrl; ?>" alt="<?php echo esc_attr($file->name); ?>" class="video-item filter-prop-element" 
             data-video-url="https://drive.google.com/file/d/<?php echo esc_attr($file->id); ?>/preview" 
             data-file-id="<?php echo esc_attr($file->id); ?>" style="max-width: 100%; height: auto;" 
             onload="console.log('Miniatura personalizada <?php echo $thumbnailFound ? 'encontrada' : 'no encontrada'; ?> para el video <?php echo esc_js($file->name); ?>')">

        <!-- Información en hover -->
        <div class="hover-general-information-video">
            <div class="video-title-preview-video">
                <p><?php echo $shortTitle; ?></p>
            </div>
            <div class="video-download-box">
                <a href="https://drive.google.com/uc?export=download&id=<?php echo esc_attr($file->id); ?>" 
                   download="<?php echo esc_attr($file->name); ?>" class="download-button" title="Descargar video">
                    <i class="fas fa-download"></i> 
                </a>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
