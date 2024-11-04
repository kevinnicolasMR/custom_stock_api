<?php
function render_font_template($file) {
    ob_start();
    $downloadLink = 'https://drive.google.com/uc?export=download&id=' . urlencode($file->id);
    ?>
    <div class="file-item file-item-font">
        <p class="font-name"><?php echo esc_html($file->name); ?></p>
        <a href="<?php echo esc_url($downloadLink); ?>" download="<?php echo esc_attr($file->name); ?>" class="download-link">Descargar</a>
    </div>
    <?php
    return ob_get_clean();
}
