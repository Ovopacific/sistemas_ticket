<?php
/**
 * Help Desk LAN - SMTP Settings View
 */
$basePath = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/\\');
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="fw-bold mb-0">Configuración de Correo SMTP</h2>
        <p class="text-muted mb-0">Establezca los parámetros de su servidor de correo saliente para las notificaciones automáticas.</p>
    </div>
</div>

<div class="row">
    <!-- Columna Principal: Formulario de Configuración -->
    <div class="col-lg-8 mb-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4 p-md-5">
                <form id="smtpForm" method="POST" action="<?php echo $basePath; ?>/settings/smtp/save">
                    
                    <h5 class="fw-bold text-primary mb-4"><i class="bi bi-envelope-paper me-2"></i>Remitente y Encabezados</h5>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="mail_from_name" class="form-label fw-semibold">Nombre del remitente *</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-person text-muted"></i></span>
                                <input type="text" class="form-control border-start-0" id="mail_from_name" name="mail_from_name" value="<?php echo htmlspecialchars($smtp['mail_from_name'] ?? ''); ?>" placeholder="ej. Soporte Ovopacific" required>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="mail_from" class="form-label fw-semibold">Correo del remitente *</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-envelope text-muted"></i></span>
                                <input type="email" class="form-control border-start-0" id="mail_from" name="mail_from" value="<?php echo htmlspecialchars($smtp['mail_from'] ?? ''); ?>" placeholder="ej. no-reply@ovopacific.com" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="reply_to" class="form-label fw-semibold">Responder a (Reply-To) <span class="text-muted text-xs">(Opcional)</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-reply text-muted"></i></span>
                                <input type="email" class="form-control border-start-0" id="reply_to" name="reply_to" value="<?php echo htmlspecialchars($smtp['reply_to'] ?? ''); ?>" placeholder="ej. info@ovopacific.com">
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="bcc" class="form-label fw-semibold">Correo de copia oculta (BCC) <span class="text-muted text-xs">(Opcional)</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-eye-slash text-muted"></i></span>
                                <input type="email" class="form-control border-start-0" id="bcc" name="bcc" value="<?php echo htmlspecialchars($smtp['bcc'] ?? ''); ?>" placeholder="ej. auditoria@ovopacific.com">
                            </div>
                        </div>
                    </div>

                    <hr class="my-4 text-muted opacity-25">
                    
                    <h5 class="fw-bold text-primary mb-4"><i class="bi bi-server me-2"></i>Servidor de Correo (SMTP)</h5>

                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label for="mail_host" class="form-label fw-semibold">Servidor SMTP *</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-globe text-muted"></i></span>
                                <input type="text" class="form-control border-start-0" id="mail_host" name="mail_host" value="<?php echo htmlspecialchars($smtp['mail_host'] ?? ''); ?>" placeholder="ej. smtp.office365.com o smtp.gmail.com" required>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="mail_port" class="form-label fw-semibold">Puerto SMTP *</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-hash text-muted"></i></span>
                                <input type="number" class="form-control border-start-0" id="mail_port" name="mail_port" value="<?php echo htmlspecialchars($smtp['mail_port'] ?? '587'); ?>" placeholder="587" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="mail_encryption" class="form-label fw-semibold">Tipo de seguridad *</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-shield-lock text-muted"></i></span>
                                <select class="form-select border-start-0" id="mail_encryption" name="mail_encryption">
                                    <option value="none" <?php echo ($smtp['mail_encryption'] ?? '') === 'none' ? 'selected' : ''; ?>>Ninguna</option>
                                    <option value="ssl" <?php echo ($smtp['mail_encryption'] ?? '') === 'ssl' ? 'selected' : ''; ?>>SSL</option>
                                    <option value="tls" <?php echo ($smtp['mail_encryption'] ?? '') === 'tls' ? 'selected' : ''; ?>>TLS (Recomendado)</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="timeout" class="form-label fw-semibold">Tiempo de espera (Timeout en seg) *</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-hourglass-split text-muted"></i></span>
                                <input type="number" class="form-control border-start-0" id="timeout" name="timeout" value="<?php echo htmlspecialchars($smtp['timeout'] ?? '30'); ?>" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="mail_username" class="form-label fw-semibold">Usuario SMTP *</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-person-workspace text-muted"></i></span>
                                <input type="text" class="form-control border-start-0" id="mail_username" name="mail_username" value="<?php echo htmlspecialchars($smtp['mail_username'] ?? ''); ?>" placeholder="ej. usuario@dominio.com" required>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="mail_password" class="form-label fw-semibold">Contraseña SMTP *</label>
                            <div class="input-group position-relative">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-key text-muted"></i></span>
                                <input type="password" class="form-control border-start-0 pe-5" id="mail_password" name="mail_password" value="<?php echo htmlspecialchars($smtp['mail_password_decrypted'] ?? ''); ?>" placeholder="••••••••">
                                <button type="button" onclick="toggleSmtpPasswordVisibility()" class="btn border-0 position-absolute end-0 top-50 translate-middle-y px-3 text-muted" style="z-index: 10; background: transparent;" title="Mostrar Contraseña">
                                    <i id="toggleSmtpPasswordIcon" class="bi bi-eye"></i>
                                </button>
                            </div>
                            <small class="form-text text-muted">Deje en blanco si no desea modificar la contraseña guardada.</small>
                        </div>
                    </div>

                    <hr class="my-4 text-muted opacity-25">
                    
                    <h5 class="fw-bold text-primary mb-3"><i class="bi bi-toggle-on me-2"></i>Estado del Servicio</h5>
                    <div class="mb-4">
                        <div class="form-check form-switch form-switch-lg">
                            <input class="form-check-input" type="checkbox" id="statusSwitch" <?php echo ($smtp['status'] ?? '') === 'active' ? 'checked' : ''; ?>>
                            <label class="form-check-label fw-semibold ms-2" for="statusSwitch" id="statusLabel">
                                <?php echo ($smtp['status'] ?? '') === 'active' ? 'Activo (El sistema enviará correos automáticamente)' : 'Inactivo'; ?>
                            </label>
                            <input type="hidden" name="status" id="statusHidden" value="<?php echo htmlspecialchars($smtp['status'] ?? 'inactive'); ?>">
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-3 mt-4">
                        <button type="submit" class="btn btn-primary px-4 py-2 fw-semibold">
                            <i class="bi bi-save me-2"></i> Guardar configuración
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Columna Lateral: Herramienta de Pruebas de Envío -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-light border-0 py-3">
                <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-send-dash me-2"></i>Correo de Prueba</h5>
            </div>
            <div class="card-body p-4">
                <p class="text-muted text-sm">Pruebe la conexión con el servidor SMTP y valide si las notificaciones de correo se envían correctamente sin salir de esta interfaz.</p>
                
                <form id="testMailForm" method="POST" action="<?php echo $basePath; ?>/settings/smtp/test">
                    <!-- Clones ocultos para probar la configuración actual antes de guardarla -->
                    <input type="hidden" name="mail_host" id="test_mail_host">
                    <input type="hidden" name="mail_port" id="test_mail_port">
                    <input type="hidden" name="mail_username" id="test_mail_username">
                    <input type="hidden" name="mail_password" id="test_mail_password">
                    <input type="hidden" name="mail_encryption" id="test_mail_encryption">
                    <input type="hidden" name="mail_from" id="test_mail_from">
                    <input type="hidden" name="mail_from_name" id="test_mail_from_name">
                    <input type="hidden" name="reply_to" id="test_reply_to">
                    <input type="hidden" name="bcc" id="test_bcc">
                    <input type="hidden" name="timeout" id="test_timeout">

                    <div class="mb-3">
                        <label for="test_email" class="form-label fw-semibold">Correo destinatario *</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-envelope-at text-muted"></i></span>
                            <input type="email" class="form-control border-start-0" id="test_email" name="test_email" placeholder="ej. prueba@correo.com" required>
                        </div>
                    </div>
                    
                    <div class="d-grid">
                        <button type="button" onclick="runSmtpDiagnosticTest()" class="btn btn-outline-primary fw-semibold py-2">
                            <i class="bi bi-lightning-charge me-2"></i> Enviar correo de prueba
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Panel de Diagnóstico Técnico de PHPMailer / SMTP Debug Log -->
        <?php if (isset($_SESSION['smtp_test_log'])): ?>
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-dark text-white border-0 py-3 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0"><i class="bi bi-terminal me-2"></i>Log de Diagnóstico SMTP</h6>
                    <span class="badge bg-secondary">Debug Log</span>
                </div>
                <div class="card-body p-0">
                    <pre class="bg-black text-light p-3 m-0 rounded-bottom" style="font-size: 0.775rem; max-height: 350px; overflow-y: auto; font-family: monospace; white-space: pre-wrap; word-break: break-all;"><?php 
                        echo htmlspecialchars($_SESSION['smtp_test_log']); 
                        unset($_SESSION['smtp_test_log']);
                    ?></pre>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
// Switch status switcher handler
const statusSwitch = document.getElementById('statusSwitch');
const statusLabel = document.getElementById('statusLabel');
const statusHidden = document.getElementById('statusHidden');

if (statusSwitch) {
    statusSwitch.addEventListener('change', function() {
        if (this.checked) {
            statusLabel.textContent = 'Activo (El sistema enviará correos automáticamente)';
            statusHidden.value = 'active';
        } else {
            statusLabel.textContent = 'Inactivo';
            statusHidden.value = 'inactive';
        }
    });
}

// Toggle password visibility
function toggleSmtpPasswordVisibility() {
    const pwdInput = document.getElementById('mail_password');
    const eyeIcon = document.getElementById('toggleSmtpPasswordIcon');
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

// Sync form values to test form and submit
function runSmtpDiagnosticTest() {
    const testEmail = document.getElementById('test_email').value;
    if (!testEmail || !testEmail.includes('@')) {
        alert('Por favor ingrese un correo destinatario válido para realizar la prueba.');
        return;
    }

    // Sync elements
    document.getElementById('test_mail_host').value = document.getElementById('mail_host').value;
    document.getElementById('test_mail_port').value = document.getElementById('mail_port').value;
    document.getElementById('test_mail_username').value = document.getElementById('mail_username').value;
    document.getElementById('test_mail_password').value = document.getElementById('mail_password').value;
    document.getElementById('test_mail_encryption').value = document.getElementById('mail_encryption').value;
    document.getElementById('test_mail_from').value = document.getElementById('mail_from').value;
    document.getElementById('test_mail_from_name').value = document.getElementById('mail_from_name').value;
    document.getElementById('test_reply_to').value = document.getElementById('reply_to').value;
    document.getElementById('test_bcc').value = document.getElementById('bcc').value;
    document.getElementById('test_timeout').value = document.getElementById('timeout').value;

    // Submit test form
    document.getElementById('testMailForm').submit();
}
</script>
