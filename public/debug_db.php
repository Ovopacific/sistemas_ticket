<?php
/**
 * Diagnostic script to check DB users and test session persistence
 */
require_once __DIR__ . '/../config/config.php';

echo "<h2>Herramienta de Diagnóstico</h2>";

// 1. Check Session State
if (session_status() === PHP_SESSION_NONE) {
    // Configure same sessions path
    $sessionPath = __DIR__ . '/../sessions';
    if (!is_dir($sessionPath)) {
        mkdir($sessionPath, 0755, true);
    }
    session_save_path($sessionPath);
    session_start();
}

$_SESSION['test_var'] = 'Hola mundo, la sesion funciona!';
echo "<p><strong>Prueba de Sesión:</strong> ID de sesión actual: <code>" . session_id() . "</code></p>";
echo "<p>Si recargas esta página y ves la siguiente línea, la sesión está guardando datos: <br>";
echo "<code>" . ($_SESSION['test_var'] ?? 'No persistido') . "</code></p>";

// 2. Check Database connection & users
try {
    $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    
    echo "<p style='color:green;'><strong>Conexión a Base de Datos:</strong> exitosa</p>";
    
    $stmt = $pdo->query("SELECT id, username, email, first_name, last_name, role, status FROM users");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>Usuarios Registrados en la Base de Datos:</h3>";
    if (empty($users)) {
        echo "<p style='color:red;'>¡Alerta! No hay ningún usuario en la tabla 'users'.</p>";
    } else {
        echo "<table border='1' cellpadding='8' style='border-collapse:collapse;'>";
        echo "<tr style='background-color:#eee;'><th>ID</th><th>Usuario (Username)</th><th>Correo</th><th>Nombre</th><th>Rol</th><th>Estado</th></tr>";
        foreach ($users as $u) {
            echo "<tr>";
            echo "<td>" . $u['id'] . "</td>";
            echo "<td><strong>" . htmlspecialchars($u['username']) . "</strong></td>";
            echo "<td>" . htmlspecialchars($u['email']) . "</td>";
            echo "<td>" . htmlspecialchars($u['first_name'] . ' ' . $u['last_name']) . "</td>";
            echo "<td>" . htmlspecialchars($u['role']) . "</td>";
            echo "<td>" . htmlspecialchars($u['status']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
} catch (Exception $e) {
    echo "<p style='color:red;'><strong>Error de Base de Datos:</strong> " . $e->getMessage() . "</p>";
}
