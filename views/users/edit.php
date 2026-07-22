<div class="mb-4">
    <?php if ($_SESSION['user']['role'] === 'admin'): ?>
        <a href="/users" class="btn btn-outline-secondary btn-sm mb-3"><i class="bi bi-arrow-left"></i> Volver a Usuarios</a>
    <?php endif; ?>
    <h2 class="fw-bold mb-0">Editar Perfil</h2>
    <p class="text-muted">Actualice la información de perfil para el usuario: <strong><?php echo htmlspecialchars($user['username']); ?></strong></p>
</div>

<div class="card border-0">
    <div class="card-body p-4 p-md-5">
        <form method="POST" action="/users/edit/<?php echo $user['id']; ?>" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">

            <div class="text-center mb-4">
                <?php if (!empty($user['avatar_path'])): ?>
                    <img src="<?php echo (strpos($user['avatar_path'], 'http') === 0 ? '' : '/') . htmlspecialchars($user['avatar_path']); ?>" alt="Avatar" class="rounded-circle border p-1" style="width: 120px; height: 120px; object-fit: cover;">
                <?php else: ?>
                    <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-inline-flex align-items-center justify-content-center border" style="width: 120px; height: 120px; font-size: 3rem; font-weight: bold;">
                        <?php echo strtoupper(substr($user['first_name'], 0, 1)); ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="first_name" class="form-label fw-semibold">Nombre *</label>
                    <input type="text" class="form-control" id="first_name" name="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="last_name" class="form-label fw-semibold">Apellido *</label>
                    <input type="text" class="form-control" id="last_name" name="last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-semibold">Nombre de Usuario (No modificable)</label>
                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['username']); ?>" disabled>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="email" class="form-label fw-semibold">Correo Electrónico *</label>
                    <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="password" class="form-label fw-semibold">Nueva Contraseña (Dejar en blanco para mantener actual)</label>
                    <input type="password" class="form-control" id="password" name="password" minlength="6" placeholder="Opcional">
                </div>
                <div class="col-md-6 mb-3">
                    <label for="role" class="form-label fw-semibold">Rol del Sistema</label>
                    <?php if ($_SESSION['user']['role'] === 'admin' && !$isSelfEdit): ?>
                        <select class="form-select" id="role" name="role" onchange="toggleTechSpecialty(this.value)">
                            <option value="user" <?php echo $user['role'] === 'user' ? 'selected' : ''; ?>>Usuario Final (Empleado)</option>
                            <option value="technician" <?php echo $user['role'] === 'technician' ? 'selected' : ''; ?>>Técnico de Sistemas</option>
                            <option value="admin" <?php echo $user['role'] === 'admin' ? 'selected' : ''; ?>>Administrador del Sistema</option>
                        </select>
                    <?php else: ?>
                        <input type="hidden" name="role" value="<?php echo $user['role']; ?>">
                        <input type="text" class="form-control" value="<?php echo $user['role'] === 'admin' ? 'Administrador' : ($user['role'] === 'technician' ? 'Técnico' : 'Usuario Final'); ?>" disabled>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Dynamic Specialty for Technicians -->
            <div class="mb-3 <?php echo $user['role'] === 'technician' ? '' : 'd-none'; ?>" id="techSpecialtyDiv">
                <label for="specialty" class="form-label fw-semibold">Especialidad del Técnico</label>
                <input type="text" class="form-control" id="specialty" name="specialty" placeholder="ej. Redes, Servidores" value="<?php echo htmlspecialchars($specialty); ?>">
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="department_id" class="form-label fw-semibold">Departamento</label>
                    <?php if ($_SESSION['user']['role'] === 'admin'): ?>
                        <select class="form-select" id="department_id" name="department_id">
                            <option value="">Seleccione Departamento</option>
                            <?php foreach ($departments as $dept): ?>
                                <option value="<?php echo $dept['id']; ?>" <?php echo $user['department_id'] == $dept['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($dept['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    <?php else: ?>
                        <input type="hidden" name="department_id" value="<?php echo $user['department_id']; ?>">
                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['department_name'] ?? 'Sin asignar'); ?>" disabled>
                    <?php endif; ?>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="position" class="form-label fw-semibold">Cargo / Puesto</label>
                    <input type="text" class="form-control" id="position" name="position" value="<?php echo htmlspecialchars($user['position'] ?? ''); ?>" placeholder="ej. Analista de Nómina">
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="phone" class="form-label fw-semibold">Teléfono / Extensión</label>
                    <input type="text" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" placeholder="ej. Ext 450">
                </div>
                <div class="col-md-6 mb-3">
                    <label for="avatar_url" class="form-label fw-semibold">O pegar URL de imagen de perfil:</label>
                    <input type="url" class="form-control" id="avatar_url" name="avatar_url" placeholder="https://ejemplo.com/foto.png" value="<?php echo (strpos($user['avatar_path'] ?? '', 'http') === 0) ? htmlspecialchars($user['avatar_path']) : ''; ?>">
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                </div>
                <div class="col-md-6 mb-3">
                    <label for="avatar" class="form-label fw-semibold">O subir archivo de fotografía:</label>
                    <input type="file" class="form-control" id="avatar" name="avatar" accept="image/*">
                </div>
            </div>

            <?php if ($user['role'] === 'technician'): ?>
                <div class="mb-3">
                    <label for="specialty" class="form-label fw-semibold">Especialidad / Tipo de Soporte</label>
                    <input type="text" class="form-control" id="specialty" name="specialty" value="<?php echo htmlspecialchars($specialty ?? 'Soporte General'); ?>" placeholder="ej. Redes, Telefonía, Impresoras, Hardware">
                </div>
            <?php endif; ?>

            <?php if ($_SESSION['user']['role'] === 'admin' && !$isSelfEdit): ?>
                <div class="mb-3">
                    <label for="status" class="form-label fw-semibold">Estado de Cuenta</label>
                    <select class="form-select" id="status" name="status">
                        <option value="active" <?php echo $user['status'] === 'active' ? 'selected' : ''; ?>>Activo</option>
                        <option value="inactive" <?php echo $user['status'] === 'inactive' ? 'selected' : ''; ?>>Inactivo (Acceso Bloqueado)</option>
                    </select>
                </div>
            <?php else: ?>
                <input type="hidden" name="status" value="<?php echo $user['status']; ?>">
            <?php endif; ?>

            <div class="mt-4">
                <button type="submit" class="btn btn-primary px-4 py-2 fw-semibold">Guardar Cambios</button>
                <?php if ($_SESSION['user']['role'] === 'admin'): ?>
                    <a href="/users" class="btn btn-outline-secondary px-4 py-2 ms-2">Cancelar</a>
                <?php else: ?>
                    <a href="<?php echo $_SESSION['user']['role'] === 'user' ? '/my-tickets' : '/dashboard'; ?>" class="btn btn-outline-secondary px-4 py-2 ms-2">Cancelar</a>
                <?php endif; ?>
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
