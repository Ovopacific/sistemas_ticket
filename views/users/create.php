<div class="mb-4">
    <a href="/users" class="btn btn-outline-secondary btn-sm mb-3"><i class="bi bi-arrow-left"></i> Volver a Usuarios</a>
    <h2 class="fw-bold mb-0">Crear Nuevo Usuario</h2>
    <p class="text-muted">Complete los datos de registro corporativo para habilitar la cuenta.</p>
</div>

<div class="card border-0">
    <div class="card-body p-4 p-md-5">
        <form method="POST" action="/users/create" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="first_name" class="form-label fw-semibold">Nombre *</label>
                    <input type="text" class="form-control" id="first_name" name="first_name" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="last_name" class="form-label fw-semibold">Apellido *</label>
                    <input type="text" class="form-control" id="last_name" name="last_name" required>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="username" class="form-label fw-semibold">Nombre de Usuario *</label>
                    <input type="text" class="form-control" id="username" name="username" placeholder="ej. jgomez" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="email" class="form-label fw-semibold">Correo Electrónico *</label>
                    <input type="email" class="form-control" id="email" name="email" placeholder="jgomez@empresa.lan" required>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="password" class="form-label fw-semibold">Contraseña *</label>
                    <input type="password" class="form-control" id="password" name="password" minlength="6" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="role" class="form-label fw-semibold">Rol del Sistema</label>
                    <select class="form-select" id="role" name="role" onchange="toggleTechSpecialty(this.value)">
                        <option value="user" selected>Usuario Final (Empleado)</option>
                        <option value="technician">Técnico de Sistemas</option>
                        <option value="admin">Administrador del Sistema</option>
                    </select>
                </div>
            </div>

            <!-- Dynamic Specialty for Technicians -->
            <div class="mb-3 d-none" id="techSpecialtyDiv">
                <label for="specialty" class="form-label fw-semibold">Especialidad del Técnico</label>
                <input type="text" class="form-control" id="specialty" name="specialty" placeholder="ej. Redes, Servidores, Hardware, Software" value="Soporte General">
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="department_id" class="form-label fw-semibold">Departamento</label>
                    <select class="form-select" id="department_id" name="department_id">
                        <option value="">Seleccione Departamento</option>
                        <?php foreach ($departments as $dept): ?>
                            <option value="<?php echo $dept['id']; ?>"><?php echo htmlspecialchars($dept['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="position" class="form-label fw-semibold">Cargo / Puesto</label>
                    <input type="text" class="form-control" id="position" name="position" placeholder="ej. Analista de Nómina">
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="phone" class="form-label fw-semibold">Teléfono / Extensión</label>
                    <input type="text" class="form-control" id="phone" name="phone" placeholder="ej. Ext 450">
                </div>
                <div class="col-md-6 mb-3">
                    <label for="avatar" class="form-label fw-semibold">Fotografía de Perfil</label>
                    <input type="file" class="form-control" id="avatar" name="avatar" accept="image/*">
                </div>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-primary px-4 py-2 fw-semibold">Crear Usuario</button>
                <a href="/users" class="btn btn-outline-secondary px-4 py-2 ms-2">Cancelar</a>
            </div>
        </form>
    </div>
</div>

<script>
function toggleTechSpecialty(role) {
    const div = document.getElementById('techSpecialtyDiv');
    if (role === 'technician') {
        div.classList.remove('d-none');
    } else {
        div.classList.add('d-none');
    }
}
</script>
