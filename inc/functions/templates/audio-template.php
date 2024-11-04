<?php
function render_audio_template($file, $categoryName) { 
    $audioUrl = 'https://drive.google.com/file/d/' . esc_attr($file->id) . '/preview';
    $downloadUrl = 'https://drive.google.com/uc?export=download&id=' . esc_attr($file->id);
    ob_start();

    $audioTitle = esc_html($file->name);
    if (mb_strlen($audioTitle) > 26) {
        $audioTitle = mb_substr($audioTitle, 0, 26) . '...';
    }

    $creationDate = new DateTime($file->created_time);
    $formattedDate = $creationDate->format('d-m-Y'); 

    ?>
    <div class="file-item file-item-audio filter-prop-element" alt="<?php echo esc_attr($file->name); ?>">
        <div class="audio-info-container">
            <div class="audio-container" data-audio-url="<?php echo esc_url($audioUrl); ?>">
                <div class="button-load-start">
                    <button class="load-audio"><i class="fas fa-play"></i></button>
                </div>
                <div class="audio-title-container">
                    <p class="audio-category"><?php echo esc_html($categoryName); ?></p>                     
                    <p class="audio-title"><?php echo $audioTitle; ?></p> 
                </div>
            </div>        
        </div>
        <div class="img-example-audio-google-drive"> 
            <img src="<?php echo plugins_url('img/preview-img.png', __FILE__); ?>" alt="Audio Example" class="img-example-audio">
        </div>
        <div class="img-preview-audio-google-drive"> 
            <img src="<?php echo plugins_url('img/preview-audio.png', __FILE__); ?>" alt="Audio Preview" class="img-preview-audio">
            <div class="audio-content"></div> 
        </div>

        <div class="audio-download">
        <p class="audio-creation-date"><?php echo esc_html($formattedDate); ?></p> 
            <a href="<?php echo esc_url($downloadUrl); ?>" class="download-audio-button" target="_blank" download>
                <i class="fas fa-download"></i> 
            </a>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
