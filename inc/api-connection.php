<?php
if (file_exists(plugin_dir_path(__FILE__) . '../vendor/autoload.php')) {
    require_once plugin_dir_path(__FILE__) . '../vendor/autoload.php';
} else {
    wp_die('El archivo autoload.php de Composer no fue encontrado. Asegúrate de instalar las dependencias correctamente.');
}

function connect_to_google_drive() {
    $client = new Google_Client();
    $client->setAuthConfig(plugin_dir_path(__FILE__) . '../config/segundo-test-438820-79896991963d.json'); 
    $client->addScope(Google_Service_Drive::DRIVE_READONLY);

    $driveService = new Google_Service_Drive($client);

    return $driveService;
}
