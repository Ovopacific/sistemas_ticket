<?php
$currentUser = $_SESSION['user'] ?? null;
if ($currentUser):
    $currentPath = $_SERVER['REQUEST_URI'];
    // helper to mark active tabs
    $isActive = function($paths) use ($currentPath) {
        if (is_array($paths)) {
            foreach ($paths as $path) {
                if (str_contains($currentPath, $path)) return 'active';
            }
        } else {
            if ($currentPath === $paths || str_contains($currentPath, $paths)) return 'active';
        }
        return '';
    };
?>

<!-- Barra Superior (Top Bar) -->
<header class="top-bar">
    <div class="d-flex align-items-center gap-3">
        <!-- Toggle Sidebar Button -->
        <button type="button" id="sidebarCollapse" class="btn btn-outline-secondary border-0 p-2" title="Menu">
            <i class="bi bi-list fs-4" style="color: var(--text-main);"></i>
        </button>
        
        <!-- Logo & Branding -->
        <a href="/dashboard" class="d-flex align-items-center text-decoration-none">
            <?php if (!empty($company_logo)): ?>
                <img src="/<?php echo htmlspecialchars($company_logo); ?>" alt="Logo" style="max-height: 28px; border-radius: 4px;">
            <?php else: ?>
                <i class="bi bi-headset fs-4 text-primary"></i>
            <?php endif; ?>
        </a>
    </div>



    <!-- Right Top Bar Actions -->
    <div class="d-flex align-items-center gap-3">
        <!-- Botón principal "Nuevo" -->
        <a href="/tickets/create" class="btn btn-primary d-flex align-items-center gap-1 py-1 px-3" style="border-radius: var(--radius-sm);">
            <i class="bi bi-plus-lg"></i>
            <span class="fw-semibold">Nuevo</span>
        </a>



        <!-- Notifications Dropdown -->
        <div class="dropdown">
            <button class="btn btn-outline-secondary border-0 p-2 position-relative" type="button" id="notificationDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-bell fs-5" style="color: var(--text-muted);"></i>
                <?php if (count($notifications) > 0): ?>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.65rem; padding: 4px 6px;">
                        <?php echo count($notifications); ?>
                    </span>
                    <script>
                        document.addEventListener('DOMContentLoaded', () => {
                            function playNotificationSound() {
                                try {
                                    const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                                    const osc = audioCtx.createOscillator();
                                    const gain = audioCtx.createGain();
                                    osc.connect(gain);
                                    gain.connect(audioCtx.destination);
                                    osc.type = 'sine';
                                    osc.frequency.setValueAtTime(587.33, audioCtx.currentTime); // D5 tone
                                    osc.frequency.exponentialRampToValueAtTime(880.00, audioCtx.currentTime + 0.12); // A5 chime
                                    gain.gain.setValueAtTime(0.12, audioCtx.currentTime);
                                    gain.gain.exponentialRampToValueAtTime(0.01, audioCtx.currentTime + 0.35);
                                    osc.start(audioCtx.currentTime);
                                    osc.stop(audioCtx.currentTime + 0.4);
                                } catch (e) {
                                    console.log("Audio feedback blocked until user interaction.");
                                }
                            }
                            // Play on click anywhere if blocked initially
                            const playOnce = () => { playNotificationSound(); document.removeEventListener('click', playOnce); };
                            document.addEventListener('click', playOnce);
                            // Try immediate play
                            playNotificationSound();
                        });
                    </script>
                <?php endif; ?>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 py-0" aria-labelledby="notificationDropdown" style="width: 300px; max-height: 400px; overflow-y: auto; background-color: var(--card-bg); border: 1px solid var(--border-color) !important;">
                <li class="p-3 border-bottom d-flex justify-content-between align-items-center" style="background-color: var(--body-bg); border-color: var(--border-color) !important;">
                    <span class="fw-bold" style="color: var(--text-main);">Notificaciones</span>
                    <span class="badge bg-primary rounded-pill"><?php echo count($notifications); ?> nuevas</span>
                </li>
                <?php if (empty($notifications)): ?>
                    <li class="p-3 text-center text-muted" style="color: var(--text-muted) !important;">No tienes notificaciones pendientes</li>
                <?php else: ?>
                    <?php foreach ($notifications as $notif): ?>
                        <li class="border-bottom" style="border-color: var(--border-color) !important;">
                            <form action="/notifications/read/<?php echo $notif['id']; ?>" method="POST" class="m-0">
                                <button type="submit" class="dropdown-item p-3 text-wrap text-start" style="color: var(--text-main); transition: background-color var(--transition-fast);">
                                    <div class="text-xs text-muted mb-1"><?php echo date('d/m H:i', strtotime($notif['created_at'])); ?></div>
                                    <div class="text-sm fw-semibold"><?php echo htmlspecialchars($notif['message']); ?></div>
                                </button>
                            </form>
                        </li>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>
        </div>

        <!-- User profile image / fallback -->
        <div class="dropdown">
            <button class="btn btn-link text-decoration-none dropdown-toggle text-body d-flex align-items-center p-0 border-0" type="button" id="userMenu" data-bs-toggle="dropdown" aria-expanded="false">
                <?php if (!empty($currentUser['avatar_path'])): ?>
                    <img src="/<?php echo htmlspecialchars($currentUser['avatar_path']); ?>" class="rounded-circle" style="width: 32px; height: 32px; object-fit: cover;">
                <?php else: ?>
                    <div class="bg-primary rounded-circle text-white d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; font-weight: 600; font-size: 0.85rem;">
                        <?php echo strtoupper(substr($currentUser['first_name'], 0, 1)); ?>
                    </div>
                <?php endif; ?>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0" aria-labelledby="userMenu" style="background-color: var(--card-bg); border: 1px solid var(--border-color) !important;">
                <li class="dropdown-header">
                    <h6 class="mb-0" style="color: var(--text-main);"><?php echo htmlspecialchars($currentUser['first_name'] . ' ' . $currentUser['last_name']); ?></h6>
                    <small class="text-muted"><?php echo htmlspecialchars($currentUser['email']); ?></small>
                </li>
                <li><hr class="dropdown-divider" style="border-color: var(--border-color);"></li>
                <li><a class="dropdown-item" href="/users/edit/<?php echo $currentUser['id']; ?>" style="color: var(--text-main);"><i class="bi bi-person me-2 text-muted"></i> Mi Perfil</a></li>
                <li><hr class="dropdown-divider" style="border-color: var(--border-color);"></li>
                <li><a class="dropdown-item text-danger" href="/logout"><i class="bi bi-box-arrow-left me-2"></i> Salir</a></li>
            </ul>
        </div>
    </div>
</header>

<!-- Main SaaS Container Layout -->
<div class="app-container">
    
    <!-- Sidebar Menú Lateral -->
    <nav id="sidebar">
        <ul class="list-unstyled components">
            <!-- Dashboard / Mis Tickets Links -->
            <?php if (in_array($currentUser['role'], ['admin', 'technician'])): ?>
                <li class="<?php echo ($currentPath === '/' || str_contains($currentPath, '/dashboard')) ? 'active' : ''; ?>">
                    <a href="/dashboard"><i class="bi bi-speedometer2"></i> <span>Dashboard</span></a>
                </li>
            <?php else: ?>
                <li class="<?php echo ($currentPath === '/my-tickets') ? 'active' : ''; ?>">
                    <a href="/my-tickets"><i class="bi bi-ticket-perforated"></i> <span>Mis Tickets</span></a>
                </li>
            <?php endif; ?>

            <!-- Tickets Navigation -->
            <li class="<?php echo $isActive($currentUser['role'] === 'user' ? '/tickets/create' : '/tickets'); ?>">
                <a href="<?php echo $currentUser['role'] === 'user' ? '/tickets/create' : '/tickets'; ?>"><i class="bi bi-chat-left-text"></i> <span><?php echo $currentUser['role'] === 'user' ? 'Enviar Ticket' : 'Tickets'; ?></span></a>
            </li>

            <?php if ($currentUser['role'] === 'admin'): ?>
                <!-- User Administration -->
                <li class="<?php echo $isActive(['/users', '/technicians']); ?>">
                    <a href="/users"><i class="bi bi-people"></i> <span>Usuarios y Técnicos</span></a>
                </li>

                <!-- Config Entities -->
                <li class="<?php echo $isActive(['/departments', '/categories']); ?>">
                    <a href="/departments"><i class="bi bi-tags"></i> <span>Deptos y Categorías</span></a>
                </li>

                <!-- Settings Settings -->
                <li class="<?php echo $isActive('/settings'); ?>">
                    <a href="/settings"><i class="bi bi-sliders"></i> <span>Configuración</span></a>
                </li>

                <!-- Audit Trail -->
                <li class="<?php echo $isActive('/audit'); ?>">
                    <a href="/audit"><i class="bi bi-shield-lock"></i> <span>Auditoría</span></a>
                </li>
            <?php endif; ?>

            <?php if (in_array($currentUser['role'], ['admin', 'technician'])): ?>
                <!-- Reports Section -->
                <li class="<?php echo $isActive('/reports'); ?>">
                    <a href="/reports"><i class="bi bi-bar-chart-line"></i> <span>Reportes</span></a>
                </li>
            <?php endif; ?>

            <li class="mt-4 pt-3 border-top" style="border-color: rgba(255,255,255,0.05) !important;">
                <a href="/logout" class="text-danger"><i class="bi bi-box-arrow-left text-danger"></i> <span>Cerrar Sesión</span></a>
            </li>
        </ul>
    </nav>
    
    <!-- Main Content Area -->
    <main id="content" class="fade-in">
        <!-- Flash Alert system -->
        <?php if (isset($_SESSION['flash_messages']['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert" style="border-radius: var(--radius-sm);">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <?php echo htmlspecialchars($_SESSION['flash_messages']['error']['value']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        <?php if (isset($_SESSION['flash_messages']['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert" style="border-radius: var(--radius-sm);">
                <i class="bi bi-check-circle-fill me-2"></i>
                <?php echo htmlspecialchars($_SESSION['flash_messages']['success']['value']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
<?php endif; ?>
