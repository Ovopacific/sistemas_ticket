<?php
/**
 * Help Desk LAN - Installation Wizard
 * Diagnoses PHP/Apache, sets up MariaDB schema, configurations, and admin account.
 */
session_start();

$configFile = __DIR__ . '/../config/config.php';
if (file_exists($configFile)) {
    // Already installed, redirect to home
    header('Location: index.php');
    exit;
}

// Diagnostics
$requirements = [
    'php_version' => PHP_VERSION_ID >= 80300,
    'pdo' => class_exists('PDO'),
    'pdo_mysql' => in_array('mysql', PDO::getAvailableDrivers()),
    'config_writable' => is_writable(__DIR__ . '/../config') || is_writable($configFile),
    'uploads_writable' => is_writable(__DIR__ . '/uploads'),
    'logs_writable' => is_writable(__DIR__ . '/../logs')
];

$allOk = !in_array(false, $requirements, true);

$step = isset($_GET['step']) ? (int)$_GET['step'] : 1;
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($step === 2) {
        // Test Database & Save
        $db_host = trim($_POST['db_host'] ?? 'localhost');
        $db_port = trim($_POST['db_port'] ?? '3306');
        $db_name = trim($_POST['db_name'] ?? 'helpdesk');
        $db_user = trim($_POST['db_user'] ?? '');
        $db_pass = $_POST['db_pass'] ?? '';

        try {
            $dsn = "mysql:host=$db_host;port=$db_port;charset=utf8mb4";
            $pdo = new PDO($dsn, $db_user, $db_pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_TIMEOUT => 5
            ]);

            // Create database if it doesn't exist
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $pdo->exec("USE `$db_name`");

            // Store credentials in session for next step
            $_SESSION['install_db'] = [
                'host' => $db_host,
                'port' => $db_port,
                'name' => $db_name,
                'user' => $db_user,
                'pass' => $db_pass
            ];

            header('Location: install.php?step=3');
            exit;
        } catch (PDOException $e) {
            $error = 'Error de conexión: ' . $e->getMessage();
        }
    } elseif ($step === 3) {
        // Create Admin Account & Finish Installation
        if (!isset($_SESSION['install_db'])) {
            header('Location: install.php?step=2');
            exit;
        }

        $admin_user = trim($_POST['admin_user'] ?? '');
        $admin_email = trim($_POST['admin_email'] ?? '');
        $admin_pass = $_POST['admin_pass'] ?? '';
        $admin_first = trim($_POST['admin_first'] ?? '');
        $admin_last = trim($_POST['admin_last'] ?? '');

        if (empty($admin_user) || empty($admin_email) || empty($admin_pass) || empty($admin_first) || empty($admin_last)) {
            $error = 'Todos los campos del administrador son requeridos.';
        } else {
            $db = $_SESSION['install_db'];
            try {
                $dsn = "mysql:host={$db['host']};port={$db['port']};dbname={$db['name']};charset=utf8mb4";
                $pdo = new PDO($dsn, $db['user'], $db['pass'], [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
                ]);

                // Run SQL Import
                $sqlFile = __DIR__ . '/../config/database.sql';
                if (!file_exists($sqlFile)) {
                    throw new Exception("No se encontró el archivo de base de datos 'config/database.sql'.");
                }

                $sql = file_get_contents($sqlFile);
                
                // Execute schema statements using PDO query execution
                // We split by standard semicolon, but to handle multi-line seeds reliably:
                $queries = preg_split("/;+(?=(?:[^'\"`]*['\"`][^'\"`]*['\"`])*[^'\"`]*$)/", $sql);
                foreach ($queries as $query) {
                    $query = trim($query);
                    if (!empty($query)) {
                        $pdo->exec($query);
                    }
                }

                // Add Admin User
                $hashedPass = password_hash($admin_pass, PASSWORD_BCRYPT);
                $stmt = $pdo->prepare("INSERT INTO `users` (username, password, email, first_name, last_name, role, status) VALUES (?, ?, ?, ?, ?, 'admin', 'active')");
                $stmt->execute([$admin_user, $hashedPass, $admin_email, $admin_first, $admin_last]);

                // Write Config File
                $appKey = bin2hex(random_bytes(32));
                $configContent = "<?php\n"
                    . "/**\n * Configuration File Generated Automatically by Help Desk Installer\n */\n\n"
                    . "define('DB_HOST', " . var_export($db['host'], true) . ");\n"
                    . "define('DB_PORT', " . var_export($db['port'], true) . ");\n"
                    . "define('DB_NAME', " . var_export($db['name'], true) . ");\n"
                    . "define('DB_USER', " . var_export($db['user'], true) . ");\n"
                    . "define('DB_PASS', " . var_export($db['pass'], true) . ");\n\n"
                    . "define('APP_KEY', " . var_export($appKey, true) . ");\n"
                    . "define('UPLOAD_DIR', __DIR__ . '/../public/uploads');\n"
                    . "define('LOG_DIR', __DIR__ . '/../logs');\n"
                    . "define('DEBUG_MODE', false);\n";

                if (file_put_contents($configFile, $configContent) === false) {
                    throw new Exception("No se pudo escribir el archivo de configuración. Por favor, verifique los permisos de escritura en la carpeta /config.");
                }

                // Clean session
                unset($_SESSION['install_db']);
                $success = '¡Instalación completada con éxito!';
                $step = 4;
            } catch (Exception $e) {
                $error = 'Error durante la instalación: ' . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instalador Mesa de Ayuda LAN</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <style>
        :root {
            --bg-color: #f4f6f9;
            --card-bg: #ffffff;
            --text-color: #333;
            --accent-color: #0d6efd;
            --border-color: #dee2e6;
        }
        @media (prefers-color-scheme: dark) {
            :root {
                --bg-color: #121212;
                --card-bg: #1e1e1e;
                --text-color: #e0e0e0;
                --accent-color: #3f8cff;
                --border-color: #333333;
            }
            .form-control, .form-select {
                background-color: #2b2b2b;
                border-color: #444;
                color: #e0e0e0;
            }
            .form-control:focus, .form-select:focus {
                background-color: #333;
                color: #fff;
                border-color: #3f8cff;
            }
        }
        body {
            background-color: var(--bg-color);
            color: var(--text-color);
            font-family: system-ui, -apple-system, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            transition: background-color 0.3s ease;
        }
        .install-container {
            width: 100%;
            max-width: 650px;
        }
        .card {
            background-color: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.15);
            overflow: hidden;
        }
        .step-progress {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2rem;
            position: relative;
        }
        .step-progress::before {
            content: '';
            position: absolute;
            top: 15px;
            left: 10%;
            width: 80%;
            height: 2px;
            background-color: var(--border-color);
            z-index: 1;
        }
        .step-item {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background-color: var(--bg-color);
            border: 2px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            z-index: 2;
            transition: all 0.3s ease;
        }
        .step-item.active {
            background-color: var(--accent-color);
            color: #fff;
            border-color: var(--accent-color);
        }
        .step-item.completed {
            background-color: #198754;
            color: #fff;
            border-color: #198754;
        }
    </style>
</head>
<body>

<div class="install-container">
    <div class="card p-4 p-md-5">
        <div class="text-center mb-4">
            <h2 class="fw-bold">Mesa de Ayuda LAN</h2>
            <p class="text-muted">Asistente de Instalación del Sistema</p>
        </div>

        <!-- Step Indicator -->
        <div class="step-progress">
            <div class="step-item <?php echo $step === 1 ? 'active' : ($step > 1 ? 'completed' : ''); ?>">1</div>
            <div class="step-item <?php echo $step === 2 ? 'active' : ($step > 2 ? 'completed' : ''); ?>">2</div>
            <div class="step-item <?php echo $step === 3 ? 'active' : ($step > 3 ? 'completed' : ''); ?>">3</div>
            <div class="step-item <?php echo $step === 4 ? 'completed' : ''; ?>">✓</div>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success" role="alert">
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <!-- Step 1: Requisitos -->
        <?php if ($step === 1): ?>
            <h4 class="mb-3">Verificación de Requisitos</h4>
            <div class="list-group mb-4">
                <div class="list-group-item d-flex justify-content-between align-items-center bg-transparent border-0 px-0">
                    <span>PHP v8.3 o superior (Detectado: <?php echo PHP_VERSION; ?>)</span>
                    <span class="badge bg-<?php echo $requirements['php_version'] ? 'success' : 'danger'; ?>">
                        <?php echo $requirements['php_version'] ? 'Correcto' : 'Requerido'; ?>
                    </span>
                </div>
                <div class="list-group-item d-flex justify-content-between align-items-center bg-transparent border-0 px-0">
                    <span>Extensión PDO (PHP)</span>
                    <span class="badge bg-<?php echo $requirements['pdo'] ? 'success' : 'danger'; ?>">
                        <?php echo $requirements['pdo'] ? 'Correcto' : 'Falta'; ?>
                    </span>
                </div>
                <div class="list-group-item d-flex justify-content-between align-items-center bg-transparent border-0 px-0">
                    <span>Controlador PDO MySQL (MariaDB)</span>
                    <span class="badge bg-<?php echo $requirements['pdo_mysql'] ? 'success' : 'danger'; ?>">
                        <?php echo $requirements['pdo_mysql'] ? 'Correcto' : 'Falta'; ?>
                    </span>
                </div>
                <div class="list-group-item d-flex justify-content-between align-items-center bg-transparent border-0 px-0">
                    <span>Permisos de Escritura en carpeta /config</span>
                    <span class="badge bg-<?php echo $requirements['config_writable'] ? 'success' : 'danger'; ?>">
                        <?php echo $requirements['config_writable'] ? 'Escritura OK' : 'Sin Permiso'; ?>
                    </span>
                </div>
                <div class="list-group-item d-flex justify-content-between align-items-center bg-transparent border-0 px-0">
                    <span>Permisos de Escritura en /public/uploads</span>
                    <span class="badge bg-<?php echo $requirements['uploads_writable'] ? 'success' : 'danger'; ?>">
                        <?php echo $requirements['uploads_writable'] ? 'Escritura OK' : 'Sin Permiso'; ?>
                    </span>
                </div>
                <div class="list-group-item d-flex justify-content-between align-items-center bg-transparent border-0 px-0">
                    <span>Permisos de Escritura en carpeta /logs</span>
                    <span class="badge bg-<?php echo $requirements['logs_writable'] ? 'success' : 'danger'; ?>">
                        <?php echo $requirements['logs_writable'] ? 'Escritura OK' : 'Sin Permiso'; ?>
                    </span>
                </div>
            </div>

            <div class="d-grid">
                <?php if ($allOk): ?>
                    <a href="install.php?step=2" class="btn btn-primary btn-lg">Continuar a Base de Datos</a>
                <?php else: ?>
                    <button class="btn btn-secondary btn-lg" disabled>Solucione los requisitos para continuar</button>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- Step 2: Base de Datos -->
        <?php if ($step === 2): ?>
            <h4 class="mb-3">Configuración de Base de Datos (MariaDB / MySQL)</h4>
            <form method="POST" action="install.php?step=2">
                <div class="mb-3">
                    <label for="db_host" class="form-label">Servidor / Host</label>
                    <input type="text" class="form-control" id="db_host" name="db_host" value="localhost" required>
                </div>
                <div class="mb-3">
                    <label for="db_port" class="form-label">Puerto</label>
                    <input type="text" class="form-control" id="db_port" name="db_port" value="3306" required>
                </div>
                <div class="mb-3">
                    <label for="db_name" class="form-label">Nombre de Base de Datos</label>
                    <input type="text" class="form-control" id="db_name" name="db_name" value="helpdesk" required>
                </div>
                <div class="mb-3">
                    <label for="db_user" class="form-label">Usuario</label>
                    <input type="text" class="form-control" id="db_user" name="db_user" required>
                </div>
                <div class="mb-3">
                    <label for="db_pass" class="form-label">Contraseña</label>
                    <input type="password" class="form-control" id="db_pass" name="db_pass">
                </div>
                <div class="d-flex justify-content-between">
                    <a href="install.php?step=1" class="btn btn-outline-secondary">Atrás</a>
                    <button type="submit" class="btn btn-primary">Probar y Crear BD</button>
                </div>
            </form>
        <?php endif; ?>

        <!-- Step 3: Administrador -->
        <?php if ($step === 3): ?>
            <h4 class="mb-3">Configuración del Administrador Principal</h4>
            <form method="POST" action="install.php?step=3">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="admin_first" class="form-label">Nombre</label>
                        <input type="text" class="form-control" id="admin_first" name="admin_first" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="admin_last" class="form-label">Apellido</label>
                        <input type="text" class="form-control" id="admin_last" name="admin_last" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="admin_user" class="form-label">Usuario (Username)</label>
                    <input type="text" class="form-control" id="admin_user" name="admin_user" required>
                </div>
                <div class="mb-3">
                    <label for="admin_email" class="form-label">Correo Electrónico</label>
                    <input type="email" class="form-control" id="admin_email" name="admin_email" placeholder="admin@empresa.lan" required>
                </div>
                <div class="mb-3">
                    <label for="admin_pass" class="form-label">Contraseña</label>
                    <input type="password" class="form-control" id="admin_pass" name="admin_pass" minlength="6" required>
                </div>
                <div class="d-flex justify-content-between">
                    <a href="install.php?step=2" class="btn btn-outline-secondary">Atrás</a>
                    <button type="submit" class="btn btn-success">Finalizar Instalación</button>
                </div>
            </form>
        <?php endif; ?>

        <!-- Step 4: Éxito -->
        <?php if ($step === 4): ?>
            <h4 class="mb-3 text-success">¡Instalación Completa!</h4>
            <p>El sistema se ha configurado correctamente. El archivo de configuración `/config/config.php` ha sido generado y la base de datos importada con los valores semilla por defecto.</p>
            
            <div class="alert alert-warning">
                <strong>Recomendación de Seguridad:</strong> Elimine el archivo <code>public/install.php</code> después de completar la instalación.
            </div>

            <div class="d-grid mt-4">
                <a href="index.php" class="btn btn-primary btn-lg">Acceder al Sistema de Soporte</a>
            </div>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
