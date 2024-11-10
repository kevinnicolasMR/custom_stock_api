<?php
function render_video_template($file) {
    ob_start();

    $shortTitle = mb_strimwidth(esc_html($file->name), 0, 25, '...');

    ?>
    <div class="file-item file-item-video">
        <img src="<?php echo esc_url($file->thumbnailLink); ?>" alt="<?php echo esc_attr($file->name); ?>" class="video-item filter-prop-element" data-video-url="https://drive.google.com/file/d/<?php echo esc_attr($file->id); ?>/preview" data-file-id="<?php echo esc_attr($file->id); ?>" style="max-width: 100%; height: auto;">

        <div class="hover-general-information-video">
            <div class="video-title-preview-video">
                <p><?php echo $shortTitle; ?></p>
            </div>
            <div class="video-download-box">
                <a href="https://drive.google.com/uc?export=download&id=<?php echo esc_attr($file->id); ?>" download="<?php echo esc_attr($file->name); ?>" class="download-button" title="Descargar video">
                    <i class="fas fa-download"></i> 
                </a>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}