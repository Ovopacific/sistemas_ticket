<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../config/config.php';

echo "<h2>Diagnóstico de Base de Datos - Sistemas Ovopacific</h2>";
echo "<p><strong>Host:</strong> " . DB_HOST . "</p>";
echo "<p><strong>Puerto:</strong> " . DB_PORT . "</p>";
echo "<p><strong>Base de Datos:</strong> " . DB_NAME . "</p>";
echo "<p><strong>Usuario:</strong> " . DB_USER . "</p>";

try {
    $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    echo "<p style='color:green;'><strong>Conexión Exitosa con la Base de Datos</strong></p>";
    
    // Consultar usuarios
    $stmt = $pdo->query("SELECT id, username, email, role, status, password FROM users");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>Usuarios en la Base de Datos (" . count($users) . "):</h3>";
    echo "<table border='1' cellpadding='5' style='border-collapse:collapse;'>";
    echo "<tr><th>ID</th><th>Usuario</th><th>Email</th><th>Rol</th><th>Estado</th><th>Prueba Contraseña 'admin123'</th></tr>";
    
    foreach ($users as $user) {
        $verify = password_verify('admin123', $user['password']);
        $verify_text = $verify ? "<span style='color:green;'>Correcta</span>" : "<span style='color:red;'>Incorrecta / Hash no coincide</span>";
        
        echo "<tr>";
        echo "<td>{$user['id']}</td>";
        echo "<td>{$user['username']}</td>";
        echo "<td>{$user['email']}</td>";
        echo "<td>{$user['role']}</td>";
        echo "<td>{$user['status']}</td>";
        echo "<td>{$verify_text}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Botón/Opción para forzar reset de admin en esta BD exacta
    if (isset($_GET['reset'])) {
        $newPass = password_hash('admin123', PASSWORD_BCRYPT);
        
        // Si no hay usuarios, creamos uno
        if (count($users) === 0) {
            $insert = $pdo->prepare("INSERT INTO users (id, username, password, email, first_name, last_name, role, status) VALUES (1, 'admin', ?, 'admin@ovopacific.com', 'Admin', 'Sistemas', 'admin', 'active')");
            $insert->execute([$newPass]);
            echo "<p style='color:blue;'><strong>Se creó el usuario 'admin' con contraseña 'admin123'</strong></p>";
        } else {
            // Si hay usuarios, actualizamos todos los admin y tecnicos
            $update = $pdo->prepare("UPDATE users SET password = ? WHERE role IN ('admin', 'technician')");
            $update->execute([$newPass]);
            echo "<p style='color:blue;'><strong>Se actualizaron las contraseñas de todos los Admins y Técnicos a 'admin123'</strong></p>";
        }
        echo "<script>setTimeout(() => { window.location.href = 'test-db.php'; }, 1500);</script>";
    } else {
        echo "<p><a href='test-db.php?reset=1' style='background:blue;color:white;padding:5px 10px;text-decoration:none;border-radius:4px;'>Forzar Restablecimiento de Contraseñas a 'admin123' en esta base de datos</a></p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color:red;'><strong>Error de conexión:</strong> " . $e->getMessage() . "</p>";
}
