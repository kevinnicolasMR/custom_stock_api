<?php
function render_video_template($file) {
    ob_start();
    ?>
    <div class="file-item file-item-video">
        <img src="<?php echo esc_url($file->thumbnailLink); ?>" alt="<?php echo esc_attr($file->name); ?>" class="video-item filter-prop-element" data-video-url="https://drive.google.com/file/d/<?php echo esc_attr($file->id); ?>/preview" style="max-width: 100%; height: auto;">
    </div>
    <?php
    return ob_get_clean();
}
