<?php
function render_font_template($file) {
    ob_start();
    ?>
    <div class="file-item file-item-font">
        <p class="font-name"><?php echo esc_html($file->name); ?></p>
        <a href="<?php echo esc_url($file->thumbnailLink); ?>" download="<?php echo esc_attr($file->name); ?>" class="download-link">Descargar</a>
    </div>
    <?php
    return ob_get_clean();
}
