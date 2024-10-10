<?php
// Incluir el autoload de Composer para cargar Google_Client y otras clases
if (file_exists(plugin_dir_path(__FILE__) . '../vendor/autoload.php')) {
    require_once plugin_dir_path(__FILE__) . '../vendor/autoload.php';
} else {
    wp_die('El archivo autoload.php de Composer no fue encontrado. Asegúrate de instalar las dependencias correctamente.');
}

function connect_to_google_drive() {
    // Cargar archivo JSON de credenciales de la cuenta de servicio
    $client = new Google_Client();
    $client->setAuthConfig(plugin_dir_path(__FILE__) . '../config/handy-parity-438113-m8-866c6484b495.json'); // Cambia el nombre del archivo JSON de credenciales aquí
    $client->addScope(Google_Service_Drive::DRIVE_READONLY);

    // Crear el servicio de Google Drive
    $driveService = new Google_Service_Drive($client);

    return $driveService;
}
