function connect_to_google_drive() {
    // Cargar archivo JSON de credenciales
    $client = new Google_Client();
    $client->setAuthConfig(plugin_dir_path(__FILE__) . '../config/client_secret.json'); 
    $client->addScope(Google_Service_Drive::DRIVE_READONLY);
    $client->setAccessType('offline');

    // Si no hay token, redirigir para obtener el consentimiento
    if (isset($_GET['code'])) {
        $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
        $_SESSION['access_token'] = $token;
    }

    if (isset($_SESSION['access_token']) && $_SESSION['access_token']) {
        $client->setAccessToken($_SESSION['access_token']);
    } else {
        // Redirigir a Google OAuth para autenticación
        $authUrl = $client->createAuthUrl();
        echo "Autorización requerida. <a href='$authUrl'>Haz clic aquí para autenticarte.</a>";
        exit;
    }

    // Crear el servicio de Google Drive
    $driveService = new Google_Service_Drive($client);

    return $driveService;
}
