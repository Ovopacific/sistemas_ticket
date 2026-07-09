<?php
/**
 * Test script to verify if session cookie persists across clicks/redirects
 */
if (session_status() === PHP_SESSION_NONE) {
    $sessionPath = __DIR__ . '/../sessions';
    if (!is_dir($sessionPath)) {
        mkdir($sessionPath, 0755, true);
    }
    session_save_path($sessionPath);
    
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => false,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
}

if (!isset($_SESSION['counter'])) {
    $_SESSION['counter'] = 1;
} else {
    $_SESSION['counter']++;
}

echo "<div style='font-family:sans-serif;padding:30px;max-width:500px;margin:auto;border:1px solid #ccc;border-radius:8px;'>";
echo "<h2>Prueba de Persistencia de Sesión</h2>";
echo "<p>ID de Sesión: <code>" . session_id() . "</code></p>";
echo "<p>Contador de Visitas: <strong style='font-size:24px;color:#0d6efd;'>" . $_SESSION['counter'] . "</strong></p>";
echo "<p><a href='session_test.php' style='background:#198754;color:#fff;padding:10px 15px;text-decoration:none;border-radius:4px;display:inline-block;'>Hacer clic aquí para recargar</a></p>";
echo "<p>Si al recargar el contador aumenta a 2, 3, etc., las sesiones funcionan perfectamente.</p>";
echo "</div>";
