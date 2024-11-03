<?php
function render_audio_template($file) {
    $audioUrl = 'https://drive.google.com/file/d/' . esc_attr($file->id) . '/preview';
    $downloadUrl = 'https://drive.google.com/uc?export=download&id=' . esc_attr($file->id);
    ob_start();
    ?>
    <div class="file-item file-item-audio filter-prop-element" alt="<?php echo esc_attr($file->name); ?>">
        <div class="audio-info-container">
            <div class="audio-container" data-audio-url="<?php echo esc_url($audioUrl); ?>">
                <button class="load-audio"><i class="fas fa-download"></i></button>
                <div class="audio-title-container">
                    <p class="audio-title"><?php echo esc_html($file->name); ?></p>
                </div>
            </div>        
        </div>

        <!-- Contenedor de la imagen de vista previa y audio-content juntos -->
        <div class="img-preview-audio-google-drive"> 
            <img src="<?php echo plugins_url('img/preview-audio.png', __FILE__); ?>" alt="Audio Preview" class="img-preview-audio">
            <div class="audio-content"></div> <!-- Ahora este div está dentro de img-preview-audio-google-drive -->
        </div>

        <div class="audio-download">
            <a href="<?php echo esc_url($downloadUrl); ?>" class="download-audio-button" target="_blank" download>Descargar</a>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
