<?php
require_once __DIR__ . '/../config/config.php';
try {
    $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    
    // Obtenemos el primer usuario administrador registrado
    $stmt = $pdo->query("SELECT id, username FROM users WHERE role = 'admin' LIMIT 1");
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($admin) {
        $newPass = password_hash('admin123', PASSWORD_BCRYPT);
        $update = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        $update->execute([$newPass, $admin['id']]);
        
        echo "<div style='font-family:sans-serif;padding:20px;text-align:center;'>";
        echo "<h2 style='color:#198754;'>¡Contraseña restablecida!</h2>";
        echo "<p>Usuario Administrador: <strong>" . htmlspecialchars($admin['username']) . "</strong></p>";
        echo "<p>Nueva contraseña: <strong>admin123</strong></p>";
        echo "<a href='/login' style='background:#0d6efd;color:#fff;padding:10px 20px;text-decoration:none;border-radius:5px;display:inline-block;margin-top:10px;'>Ir al Login</a>";
        echo "</div>";
    } else {
        echo "No se encontró ningún administrador en la base de datos.";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
