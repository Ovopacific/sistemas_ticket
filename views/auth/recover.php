<div class="container d-flex flex-column align-items-center justify-content-center min-vh-100 py-5">
    <div class="card p-4 p-md-5 shadow-lg border-0 bg-opacity-75 fade-in" style="width: 100%; max-width: 450px; background-color: var(--card-bg);">
        
        <div class="text-center mb-4">
            <div class="bg-warning bg-opacity-10 text-warning rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 70px; height: 70px;">
                <i class="bi bi-shield-slash fs-1"></i>
            </div>
            <h3 class="fw-bold mb-1">Recuperar Contraseña</h3>
            <p class="text-muted text-sm">Ingrese su correo para restablecer sus credenciales</p>
        </div>

        <form method="POST" action="/recover">
            <div class="mb-4">
                <label for="email" class="form-label text-sm fw-semibold">Correo Electrónico Corporativo</label>
                <div class="input-group">
                    <span class="input-group-text bg-transparent border-end-0 text-muted"><i class="bi bi-envelope"></i></span>
                    <input type="email" class="form-control border-start-0" id="email" name="email" placeholder="usuario@empresa.lan" required>
                </div>
                <div class="form-text text-xs text-muted mt-2">
                    <i class="bi bi-info-circle me-1"></i> El sistema opera únicamente en red interna LAN. Al enviar, se registrará una traza de restablecimiento de contraseña para el Administrador de Sistemas.
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold mb-3">Solicitar Restablecimiento</button>
            
            <div class="text-center">
                <a href="/login" class="text-sm text-decoration-none"><i class="bi bi-arrow-left me-1"></i> Volver al Login</a>
            </div>
        </form>
    </div>
</div>
