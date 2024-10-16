<?php
/*
Plugin Name: WP Stock Multimedia
Description: Sistema de stock multimedia privado con integración a Google Drive.
Version: 1.0
Author: Kevin Nicolas Medina Robles
*/

if (!defined('ABSPATH')) exit; // Evitar acceso directo

// Definir constantes del plugin (como rutas y URL base)
define('WP_STOCK_MULTIMEDIA_PATH', plugin_dir_path(__FILE__));
define('WP_STOCK_MULTIMEDIA_URL', plugin_dir_url(__FILE__));

// Incluir archivos de funcionalidades del plugin
require_once WP_STOCK_MULTIMEDIA_PATH . 'inc/api-connection.php';  // Conexión con la API de Google Drive
require_once WP_STOCK_MULTIMEDIA_PATH . 'inc/shortcode-creation.php';  // Lógica de creación de shortcodes
require_once WP_STOCK_MULTIMEDIA_PATH . 'inc/admin-page.php';  // Página interna en el admin para gestionar shortcodes

// Activación del plugin
function wp_stock_multimedia_activate() {
    // Código que quieras ejecutar en la activación del plugin (como crear opciones, tablas, etc.)
}
register_activation_hook(__FILE__, 'wp_stock_multimedia_activate');

// Desactivación del plugin
function wp_stock_multimedia_deactivate() {
    // Código que quieras ejecutar en la desactivación del plugin (como limpiar opciones, tablas, etc.)
}
register_deactivation_hook(__FILE__, 'wp_stock_multimedia_deactivate');

