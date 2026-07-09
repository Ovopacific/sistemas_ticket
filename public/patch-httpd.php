<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$httpdConf = 'C:\laragon\bin\apache\httpd-2.4.66-260223-Win64-VS18\conf\httpd.conf';

if (!file_exists($httpdConf)) {
    echo "<p style='color:red;'>No se encontró el archivo httpd.conf en la ruta especificada.</p>";
    exit;
}

echo "<h2>Parche de httpd.conf</h2>";
echo "<p>Archivo: " . htmlspecialchars($httpdConf) . "</p>";

$content = file_get_contents($httpdConf);

// Reemplazar DocumentRoot
$content = str_replace(
    'DocumentRoot "C:/laragon/www"',
    'DocumentRoot "C:/laragon/www/sistemas-ticket/public"',
    $content
);

// Reemplazar Directory
$content = str_replace(
    '<Directory "C:/laragon/www">',
    '<Directory "C:/laragon/www/sistemas-ticket/public">',
    $content
);

if (file_put_contents($httpdConf, $content) !== false) {
    echo "<p style='color:green;font-weight:bold;'>¡httpd.conf actualizado correctamente!</p>";
    echo "<p><strong>Por favor:</strong> Ve a Laragon, haz clic en <strong>Stop</strong> y luego en <strong>Start All</strong> para aplicar los cambios.</p>";
} else {
    echo "<p style='color:red;font-weight:bold;'>Error al escribir el archivo.</p>";
}
