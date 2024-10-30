<?php
function render_pdf_template($file) {
    ob_start();
    ?>
    <div class="file-item file-item-pdf filter-prop-element" alt="<?php echo esc_attr($file->name); ?>">
        <p><?php echo esc_html($file->name); ?> <a href="https://drive.google.com/file/d/<?php echo esc_attr($file->id); ?>/view" target="_blank">Ver PDF</a></p>
    </div>
    <?php
    return ob_get_clean();
}
