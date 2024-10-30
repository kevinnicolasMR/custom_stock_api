<?php
function render_image_template($file) {
    ob_start();
    ?>
    <div class="file-item file-item-img">
        <img src="<?php echo esc_url($file->thumbnailLink); ?>" alt="<?php echo esc_attr($file->name); ?>" class="image-item filter-prop-element" data-image-url="<?php echo esc_url($file->thumbnailLink); ?>" data-file-id="<?php echo esc_attr($file->id); ?>">
    </div>
    <?php
    return ob_get_clean();
}
