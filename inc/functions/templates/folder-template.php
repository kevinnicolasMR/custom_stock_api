<?php
function render_folder_template($file) {
    ob_start();
    ?>
    <div class="file-item filter-prop-element file-item-folder clickable-folder" data-folder-id="<?php echo esc_attr($file->id); ?>" style="width: 300px; height: 200px;">
        <p class="folder-name"><?php echo esc_html($file->name); ?></p>
    </div>
    <?php
    return ob_get_clean();
}
