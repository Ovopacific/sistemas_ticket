<?php
$rememberedUser = $_COOKIE['helpdesk_user'] ?? '';
$csrfToken = $this->session->getCsrfToken();
$basePath = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/\\');
?>

<!-- Carga de Fuentes Premium de Google -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

<!-- Contenedor Principal del Login en Dos Columnas -->
<div class="login-container d-flex min-vh-100 w-100 overflow-hidden" style="background-color: #06080d; font-family: 'Plus Jakarta Sans', sans-serif;">
    
    <!-- ================= COLUMNA IZQUIERDA (45%): Ilustración 3D del Huevo Corporativo ================= -->
    <div class="login-left-panel d-none d-lg-block position-relative" style="width: 45%; background: radial-gradient(circle at center, #0f172a 0%, #06080d 100%); border-right: 1px solid rgba(255, 255, 255, 0.05); overflow: hidden;">
        
        <!-- Imagen de Mascota Huevo 3D (Pixar Style) -->
        <div class="mascot-wrapper w-100 h-100 d-flex align-items-center justify-content-center" style="position: absolute; top: 0; left: 0; z-index: 2;">
            <img src="<?php echo $basePath; ?>/assets/img/egg_mascot.png" alt="Mascota Ovopacific" style="width: 100%; height: 100%; object-fit: cover; filter: drop-shadow(0 15px 30px rgba(0,0,0,0.5)); transform: scale(1.02); animation: subtleFloat 8s ease-in-out infinite;">
        </div>

        <!-- Canvas para partículas y circuitos sobre el panel izquierdo -->
        <canvas id="leftPanelCanvas" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; z-index: 3; pointer-events: none; mix-blend-mode: screen;"></canvas>

        <!-- Sombra degradada para fusionar los bordes -->
        <div style="position: absolute; bottom: 0; left: 0; width: 100%; height: 40%; background: linear-gradient(to top, #06080d, transparent); z-index: 4; pointer-events: none;"></div>
        <div style="position: absolute; top: 0; right: 0; width: 30%; height: 100%; background: linear-gradient(to left, rgba(6, 8, 13, 0.6), transparent); z-index: 4; pointer-events: none;"></div>
    </div>

    <!-- ================= COLUMNA DERECHA (55%): Formulario Glassmorphism ================= -->
    <div class="login-right-panel position-relative d-flex align-items-center justify-content-center" style="flex-grow: 1; background: radial-gradient(circle at 70% 30%, #0d1527 0%, #050811 100%); overflow: hidden;">
        
        <!-- Blobs / Nubes de Neón Ambientales Detrás de la Tarjeta para dar Profundidad -->
        <div class="glow-blob" style="position: absolute; width: 420px; height: 420px; background: radial-gradient(circle, rgba(99, 102, 241, 0.22) 0%, transparent 70%); top: 10%; right: 10%; filter: blur(40px); pointer-events: none; z-index: 1; animation: floatBlob1 15s infinite alternate;"></div>
        <div class="glow-blob" style="position: absolute; width: 360px; height: 360px; background: radial-gradient(circle, rgba(249, 115, 22, 0.15) 0%, transparent 70%); bottom: 10%; left: 10%; filter: blur(40px); pointer-events: none; z-index: 1; animation: floatBlob2 15s infinite alternate;"></div>

        <!-- Tarjeta de Login Glassmorphism Ultra Premium -->
        <div class="glass-card card border-0 p-4 p-md-5 shadow-lg position-relative" style="z-index: 3; width: 100%; max-width: 450px; margin: 20px; background: rgba(10, 18, 36, 0.75) !important; backdrop-filter: blur(28px); -webkit-backdrop-filter: blur(28px); border-radius: 28px; border: 1px solid rgba(255, 255, 255, 0.1); box-shadow: 0 25px 60px rgba(0, 0, 0, 0.65), inset 0 0 25px rgba(255, 255, 255, 0.05); transform-style: preserve-3d; transition: transform 0.15s ease-out; overflow: hidden;">
            
            <div class="text-center mb-4" style="transform: translateZ(30px);">
                <!-- Logo de Ovopacific -->
                <?php if (!empty($company_logo)): ?>
                    <?php 
                    $logoSrc = (strpos($company_logo, 'http') === 0) ? $company_logo : $basePath . '/' . $company_logo;
                    ?>
                    <img src="<?php echo htmlspecialchars($logoSrc); ?>" alt="Logo" class="img-fluid mb-2" style="max-height: 52px; border-radius: 4px;">
                <?php else: ?>
                    <div class="bg-white bg-opacity-10 text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-2" style="width: 50px; height: 50px; border: 1px solid rgba(255,255,255,0.15);">
                        <i class="bi bi-headset fs-3" style="color: #67e8f9;"></i>
                    </div>
                <?php endif; ?>
                
                <h3 class="fw-bold mb-1 text-white" style="letter-spacing: -0.025em; text-shadow: 0 2px 8px rgba(0,0,0,0.5); font-weight: 800;">Sistemas Ovopacific</h3>
                
                <!-- Separador de Líneas con Escudo -->
                <div class="d-flex align-items-center justify-content-center my-3 gap-2">
                    <span style="height: 1px; width: 45px; background: linear-gradient(to right, transparent, rgba(99, 102, 241, 0.6));"></span>
                    <i class="bi bi-shield-check" style="color: #6366f1; font-size: 0.95rem; filter: drop-shadow(0 0 4px rgba(99,102,241,0.5));"></i>
                    <span style="height: 1px; width: 45px; background: linear-gradient(to left, transparent, rgba(249, 115, 22, 0.6));"></span>
                </div>
                
            </div>

            <?php if (isset($_SESSION['flash_messages']['error'])): ?>
                <div class="alert alert-danger text-sm border-0 d-flex align-items-center gap-2 mb-4" role="alert" style="border-radius: var(--radius-sm); background-color: rgba(239, 68, 68, 0.2); color: #fff; transform: translateZ(20px);">
                    <i class="bi bi-exclamation-circle-fill fs-5"></i>
                    <span style="font-weight: 500;"><?php echo htmlspecialchars($_SESSION['flash_messages']['error']['value']); ?></span>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['flash_messages']['success'])): ?>
                <div class="alert alert-success text-sm border-0 d-flex align-items-center gap-2 mb-4" role="alert" style="border-radius: var(--radius-sm); background-color: rgba(16, 185, 129, 0.2); color: #fff; transform: translateZ(20px);">
                    <i class="bi bi-check-circle-fill fs-5"></i>
                    <span style="font-weight: 500;"><?php echo htmlspecialchars($_SESSION['flash_messages']['success']['value']); ?></span>
                </div>
            <?php endif; ?>

            <form method="POST" action="<?php echo $basePath; ?>/login" class="d-flex flex-column gap-3" style="transform: translateZ(20px);">
                <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">

                <div>
                    <label for="username" class="form-label text-xs fw-bold text-white text-opacity-90 mb-2">Nombre de usuario</label>
                    <div class="input-group login-input-group">
                        <span class="input-group-text"><i class="bi bi-person text-white text-opacity-50"></i></span>
                        <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($rememberedUser); ?>" placeholder="ej. analistati" required>
                    </div>
                </div>

                <div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label for="password" class="form-label text-xs fw-bold text-white text-opacity-90 mb-0">Contraseña</label>
                        <a href="<?php echo $basePath; ?>/recover" class="text-xs text-decoration-none fw-bold hover-underline" style="color: #6366f1;">¿Olvidó su contraseña?</a>
                    </div>
                    <div class="input-group login-input-group position-relative">
                        <span class="input-group-text"><i class="bi bi-lock text-white text-opacity-50"></i></span>
                        <input type="password" class="form-control pe-5" id="password" name="password" placeholder="••••••••" required>
                        <!-- Botón para ver/ocultar contraseña -->
                        <button type="button" onclick="togglePasswordVisibility()" class="btn border-0 position-absolute end-0 top-50 translate-middle-y px-3 text-white text-opacity-50" style="z-index: 10; background: transparent;" title="Mostrar Contraseña">
                            <i id="togglePasswordIcon" class="bi bi-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="form-check my-1">
                    <input type="checkbox" class="form-check-input" id="remember" name="remember" <?php echo !empty($rememberedUser) ? 'checked' : ''; ?> style="background-color: rgba(255,255,255,0.05); border-color: rgba(255,255,255,0.15);">
                    <label class="form-check-label text-sm fw-semibold text-white text-opacity-80" for="remember">Recordar mi usuario</label>
                </div>

                <button type="submit" class="btn btn-login-submit w-100 py-2.5 fw-bold mt-2 shadow-sm d-flex align-items-center justify-content-center gap-2">
                    <span>INICIAR SESIÓN</span>
                    <i class="bi bi-arrow-right-short fs-5 text-white"></i>
                </button>
            </form>

            <!-- Bloque de Pie de Página: Seguridad Cifrada -->
            <div class="d-flex align-items-center justify-content-center mt-4 gap-2" style="font-size: 0.725rem; letter-spacing: 0.08em; font-weight: 700; color: rgba(255, 255, 255, 0.4); transform: translateZ(10px);">
                <span style="height: 1px; flex-grow: 1; background: rgba(255, 255, 255, 0.06);"></span>
                <i class="bi bi-lock-fill text-xs" style="color: rgba(255, 255, 255, 0.5);"></i>
                <span>SEGURIDAD</span>
                <span style="height: 1px; flex-grow: 1; background: rgba(255, 255, 255, 0.06);"></span>
            </div>
            <div class="text-center mt-1.5" style="font-size: 0.675rem; color: rgba(255, 255, 255, 0.5); font-weight: 500; transform: translateZ(10px);"><i class="bi bi-shield-fill-check me-1" style="color: #6366f1;"></i> Conexión protegida con cifrado SSL</div>
        </div>
    </div>
</div>

<style>
/* CSS Keyframes & Animations */
@keyframes subtleFloat {
    0% { transform: translateY(0) scale(1.02); }
    50% { transform: translateY(-8px) scale(1.03); }
    100% { transform: translateY(0) scale(1.02); }
}

@keyframes fadeScale {
    from { opacity: 0; transform: scale(0.96); }
    to { opacity: 1; transform: scale(1); }
}

.glass-card {
    animation: fadeScale 0.75s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

/* Reflective Sheen effect */
.glass-card::before {
    content: '';
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: linear-gradient(
        45deg,
        transparent 45%,
        rgba(255, 255, 255, 0.08) 50%,
        transparent 55%
    );
    transform: rotate(45deg);
    animation: shine 7s infinite ease-in-out;
    pointer-events: none;
    z-index: 1;
}

@keyframes shine {
    0% { transform: translate(-30%, -30%) rotate(45deg); }
    100% { transform: translate(30%, 30%) rotate(45deg); }
}

@keyframes floatBlob1 {
    0% { transform: translate(0, 0) scale(1); }
    100% { transform: translate(30px, -20px) scale(1.1); }
}

@keyframes floatBlob2 {
    0% { transform: translate(0, 0) scale(1); }
    100% { transform: translate(-25px, 20px) scale(1.08); }
}

/* Custom inputs design (Fluent/Dark-SaaS Mode) */
.login-input-group {
    background-color: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 12px;
    overflow: hidden;
    transition: all 0.25s ease;
}
.login-input-group:focus-within {
    border-color: #6366f1;
    background-color: rgba(255, 255, 255, 0.08);
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.25);
}
.login-input-group .input-group-text {
    background-color: transparent !important;
    border: none !important;
    padding-right: 8px;
}
.login-input-group .form-control {
    background-color: transparent !important;
    border: none !important;
    color: #fff !important;
    font-size: 0.925rem;
    font-weight: 500;
    padding: 10px 12px;
}
.login-input-group .form-control::placeholder {
    color: rgba(255, 255, 255, 0.35) !important;
}
.login-input-group .form-control:focus {
    box-shadow: none !important;
}

/* Gradient purple-blue button */
.btn-login-submit {
    background: linear-gradient(135deg, #6366f1, #a855f7);
    background-size: 150% auto;
    border: none;
    height: 48px;
    border-radius: 12px;
    transition: all 0.4s ease;
    color: #fff;
    font-size: 0.95rem;
    letter-spacing: 0.05em;
    font-weight: 700;
    box-shadow: 0 4px 15px rgba(99, 102, 241, 0.3);
}
.btn-login-submit:hover {
    background-position: right center;
    box-shadow: 0 6px 20px rgba(168, 85, 247, 0.4);
    transform: translateY(-2px);
}
.hover-underline:hover {
    text-decoration: underline !important;
}
</style>

<!-- Script de Parallax del Mouse e Iluminación del Canvas -->
<script>
// Toggle Password Visibility
function togglePasswordVisibility() {
    const pwdInput = document.getElementById('password');
    const eyeIcon = document.getElementById('togglePasswordIcon');
    if (pwdInput.type === 'password') {
        pwdInput.type = 'text';
        eyeIcon.classList.remove('bi-eye');
        eyeIcon.classList.add('bi-eye-slash');
    } else {
        pwdInput.type = 'password';
        eyeIcon.classList.remove('bi-eye-slash');
        eyeIcon.classList.add('bi-eye');
    }
}

document.addEventListener('DOMContentLoaded', () => {
    // 3D Card Tilt effect
    const card = document.querySelector('.glass-card');
    document.addEventListener('mousemove', (e) => {
        const rect = card.getBoundingClientRect();
        const x = e.clientX - rect.left - rect.width/2;
        const y = e.clientY - rect.top - rect.height/2;
        const tiltX = -(y / (rect.height/2)) * 6;
        const tiltY = (x / (rect.width/2)) * 6;
        card.style.transform = `perspective(1000px) rotateX(${tiltX}deg) rotateY(${tiltY}deg) scale(1.01)`;
    });
    
    card.addEventListener('mouseleave', () => {
        card.style.transform = 'perspective(1000px) rotateX(0deg) rotateY(0deg) scale(1)';
    });

    // 1. Mouse position feedback for Parallax Left Mascot
    const bgContainer = document.getElementById('loginBgContainer');
    if (bgContainer) {
        document.addEventListener('mousemove', (e) => {
            const x = (window.innerWidth / 2 - e.clientX) / 45;
            const y = (window.innerHeight / 2 - e.clientY) / 45;
            bgContainer.style.transform = `translate3d(${x}px, ${y}px, 0)`;
        });
    }

    // 2. Left Panel Canvas - Holographic neon connection network
    const canvas = document.getElementById('leftPanelCanvas');
    const ctx = canvas.getContext('2d');
    
    let width = canvas.width = canvas.offsetWidth;
    let height = canvas.height = canvas.offsetHeight;

    window.addEventListener('resize', () => {
        width = canvas.width = canvas.offsetWidth;
        height = canvas.height = canvas.offsetHeight;
    });

    class Particle {
        constructor() {
            this.x = Math.random() * width;
            this.y = Math.random() * height;
            this.vx = (Math.random() - 0.5) * 0.4;
            this.vy = (Math.random() - 0.5) * 0.4;
            this.radius = 1 + Math.random() * 2;
            this.alpha = 0.15 + Math.random() * 0.5;
        }
        update() {
            this.x += this.vx;
            this.y += this.vy;
            if (this.x < 0 || this.x > width) this.vx *= -1;
            if (this.y < 0 || this.y > height) this.vy *= -1;
        }
        draw() {
            ctx.beginPath();
            ctx.arc(this.x, this.y, this.radius, 0, Math.PI * 2);
            ctx.fillStyle = `rgba(99, 102, 241, ${this.alpha})`;
            ctx.fill();
        }
    }

    const particles = [];
    for (let i = 0; i < 30; i++) {
        particles.push(new Particle());
    }

    function draw() {
        ctx.clearRect(0, 0, width, height);
        
        // Draw connection lines
        ctx.strokeStyle = 'rgba(99, 102, 241, 0.08)';
        ctx.lineWidth = 1;
        for (let i = 0; i < particles.length; i++) {
            for (let j = i + 1; j < particles.length; j++) {
                const dist = Math.hypot(particles[i].x - particles[j].x, particles[i].y - particles[j].y);
                if (dist < 120) {
                    ctx.beginPath();
                    ctx.moveTo(particles[i].x, particles[i].y);
                    ctx.lineTo(particles[j].x, particles[j].y);
                    ctx.stroke();
                }
            }
        }

        particles.forEach(p => {
            p.update();
            p.draw();
        });

        requestAnimationFrame(draw);
    }
    draw();
});
</script>