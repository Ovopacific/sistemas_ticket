<div class="mb-4">
    <h2 class="fw-bold mb-0">Configuración del Sistema</h2>
    <p class="text-muted">Ajustes generales, marca de la empresa y parámetros de conexión LDAP corporativo.</p>
</div>

<div class="row">
    <!-- Branding Logo Column -->
    <div class="col-lg-4 mb-4 mb-lg-0">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-transparent py-3 border-bottom">
                <h6 class="fw-bold mb-0 text-muted"><i class="bi bi-image me-2"></i> Logotipo de la Empresa</h6>
            </div>
            <div class="card-body p-4 text-center">
                <div class="mb-4 bg-light p-3 rounded d-inline-flex align-items-center justify-content-center" style="min-height: 120px; width: 100%;">
                    <?php if (!empty($settings['company_logo'])): ?>
                        <?php 
                        $logoSrc = (strpos($settings['company_logo'], 'http') === 0) ? $settings['company_logo'] : '/' . $settings['company_logo'];
                        ?>
                        <img src="<?php echo htmlspecialchars($logoSrc); ?>" alt="Logo actual" class="img-fluid" style="max-height: 100px;">
                    <?php else: ?>
                        <div class="text-muted text-sm"><i class="bi bi-headset fs-2 d-block mb-2"></i> Sin logotipo configurado</div>
                    <?php endif; ?>
                </div>

                <form action="/settings/logo" method="POST" enctype="multipart/form-data">
                    <div class="mb-3 text-start">
                        <label for="logo_url" class="form-label fw-semibold text-xs mb-1">Pegar URL de logotipo externo:</label>
                        <input type="url" class="form-control form-control-sm" id="logo_url" name="logo_url" placeholder="https://ejemplo.com/logo.png" value="<?php echo (strpos($settings['company_logo'] ?? '', 'http') === 0) ? htmlspecialchars($settings['company_logo']) : ''; ?>">
                    </div>
                    <div class="mb-3 text-start">
                        <label for="logo" class="form-label fw-semibold text-xs mb-1">O seleccionar archivo local:</label>
                        <input type="file" class="form-control form-control-sm" id="logo" name="logo" accept="image/*">
                    </div>
                    <button type="submit" class="btn btn-sm btn-primary w-100"><i class="bi bi-check-circle"></i> Actualizar Logotipo</button>
                </form>
            </div>
        </div>

        <!-- System variables reference card -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent py-3 border-bottom">
                <h6 class="fw-bold mb-0 text-muted"><i class="bi bi-info-circle me-2"></i> Variables del Sistema</h6>
            </div>
            <div class="card-body p-4 text-sm">
                <p><strong>Ruta de Subidas:</strong><br><code class="text-break"><?php echo htmlspecialchars(UPLOAD_DIR); ?></code></p>
                <p><strong>Límite de PHP (upload_max_filesize):</strong><br><code><?php echo ini_get('upload_max_filesize'); ?></code></p>
                <p class="mb-0"><strong>Límite de PHP (post_max_size):</strong><br><code><?php echo ini_get('post_max_size'); ?></code></p>
            </div>
        </div>
    </div>

    <!-- Configuration Options Column -->
    <div class="col-lg-8">
        <form action="/settings/save" method="POST">
            <!-- General configurations card -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent py-3 border-bottom">
                    <h6 class="fw-bold mb-0 text-muted"><i class="bi bi-sliders me-2"></i> Ajustes Generales</h6>
                </div>
                <div class="card-body p-4">
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label for="company_name" class="form-label fw-semibold text-sm">Nombre de la Empresa *</label>
                            <input type="text" class="form-control" id="company_name" name="company_name" value="<?php echo htmlspecialchars($settings['company_name'] ?? ''); ?>" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="theme_color" class="form-label fw-semibold text-sm">Color del Tema (Hex)</label>
                            <div class="d-flex gap-2">
                                <input type="color" class="form-control form-control-color" id="theme_color_picker" value="<?php echo htmlspecialchars($settings['theme_color'] ?? '#0d6efd'); ?>" oninput="document.getElementById('theme_color').value = this.value">
                                <input type="text" class="form-control text-sm" id="theme_color" name="theme_color" value="<?php echo htmlspecialchars($settings['theme_color'] ?? '#0d6efd'); ?>" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="timezone" class="form-label fw-semibold text-sm">Zona Horaria</label>
                            <input type="text" class="form-control text-sm" id="timezone" name="timezone" value="<?php echo htmlspecialchars($settings['timezone'] ?? 'America/Bogota'); ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="language" class="form-label fw-semibold text-sm">Idioma</label>
                            <select class="form-select text-sm" id="language" name="language">
                                <option value="es" <?php echo ($settings['language'] ?? 'es') === 'es' ? 'selected' : ''; ?>>Español</option>
                                <option value="en" <?php echo ($settings['language'] ?? 'es') === 'en' ? 'selected' : ''; ?>>English</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="max_upload_size" class="form-label fw-semibold text-sm">Tamaño Máximo Archivos (Bytes)</label>
                            <input type="number" class="form-control text-sm" id="max_upload_size" name="max_upload_size" value="<?php echo htmlspecialchars($settings['max_upload_size'] ?? '10485760'); ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="allowed_extensions" class="form-label fw-semibold text-sm">Extensiones Permitidas (Separadas por comas)</label>
                            <input type="text" class="form-control text-sm" id="allowed_extensions" name="allowed_extensions" value="<?php echo htmlspecialchars($settings['allowed_extensions'] ?? ''); ?>" required>
                        </div>
                    </div>
                </div>
            </div>

            <!-- LDAP configurations card -->
            <div class="card border-0 shadow-sm border-start border-info border-4 mb-4">
                <div class="card-header bg-transparent py-3 border-bottom">
                    <h6 class="fw-bold mb-0 text-info"><i class="bi bi-shield-lock me-2"></i> Integración con Active Directory (LDAP)</h6>
                </div>
                <div class="card-body p-4">
                    <div class="mb-3 form-check form-switch">
                        <input class="form-check-input" type="checkbox" role="switch" id="ldap_enabled" name="ldap_enabled" value="1" <?php echo ($settings['ldap_enabled'] ?? '0') === '1' ? 'checked' : ''; ?> onchange="toggleLdapFields(this.checked)">
                        <label class="form-check-label fw-semibold text-sm" for="ldap_enabled">Habilitar Autenticación LDAP</label>
                    </div>

                    <div id="ldapFields" class="<?php echo ($settings['ldap_enabled'] ?? '0') === '1' ? '' : 'd-none'; ?>">
                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label for="ldap_host" class="form-label fw-semibold text-sm">Servidor LDAP (IP o Hostname) *</label>
                                <input type="text" class="form-control text-sm" id="ldap_host" name="ldap_host" value="<?php echo htmlspecialchars($settings['ldap_host'] ?? ''); ?>" placeholder="ej. 192.168.1.10">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="ldap_port" class="form-label fw-semibold text-sm">Puerto *</label>
                                <input type="text" class="form-control text-sm" id="ldap_port" name="ldap_port" value="<?php echo htmlspecialchars($settings['ldap_port'] ?? '389'); ?>">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="ldap_dn" class="form-label fw-semibold text-sm">Patrón DN del Usuario (User DN Pattern) *</label>
                            <input type="text" class="form-control text-sm" id="ldap_dn" name="ldap_dn" value="<?php echo htmlspecialchars($settings['ldap_dn'] ?? ''); ?>" placeholder="ej. DOMAIN\\{username} o uid={username},ou=users,dc=empresa,dc=lan">
                            <div class="form-text text-xs text-muted mt-1"><i class="bi bi-info-circle me-1"></i> Use la clave <code>{username}</code> que será sustituida por el usuario en tiempo de inicio de sesión.</div>
                        </div>

                        <div class="mb-3">
                            <label for="ldap_search_base" class="form-label fw-semibold text-sm">Base de Búsqueda (Search Base) - Opcional</label>
                            <input type="text" class="form-control text-sm" id="ldap_search_base" name="ldap_search_base" value="<?php echo htmlspecialchars($settings['ldap_search_base'] ?? ''); ?>" placeholder="ej. ou=users,dc=empresa,dc=lan">
                        </div>
                    </div>
                </div>
            </div>

            <div class="text-end mb-5">
                <button type="submit" class="btn btn-primary px-5 py-2 fw-semibold"><i class="bi bi-check-circle"></i> Guardar Todo</button>
            </div>
        </form>
    </div>
</div>

<script>
function toggleLdapFields(enabled) {
    const fields = document.getElementById('ldapFields');
    if (enabled) {
        fields.classList.remove('d-none');
    } else {
        fields.classList.add('d-none');
    }
}
</script>
