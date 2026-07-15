<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="fw-bold mb-0">Gestión de Usuarios</h2>
        <p class="text-muted mb-0">Administre el personal de la empresa, técnicos y roles del sistema.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="/technicians" class="btn btn-outline-secondary"><i class="bi bi-person-gear"></i> Ver Carga Técnicos</a>
        <a href="/users/create" class="btn btn-primary"><i class="bi bi-person-plus"></i> Crear Usuario</a>
    </div>
</div>

<div class="card border-0">
    <div class="card-body p-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle datatable">
                <thead>
                    <tr>
                        <th>Usuario</th>
                        <th>Nombre Completo</th>
                        <th>Correo</th>
                        <th>Departamento / Cargo</th>
                        <th>Rol</th>
                        <th>Estado</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <?php if (!empty($user['avatar_path'])): ?>
                                        <img src="<?php echo (strpos($user['avatar_path'], 'http') === 0 ? '' : '/') . htmlspecialchars($user['avatar_path']); ?>" class="rounded-circle me-2" style="width: 32px; height: 32px; object-fit: cover;">
                                    <?php else: ?>
                                        <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px; font-weight: bold;">
                                            <?php echo strtoupper(substr($user['first_name'], 0, 1)); ?>
                                        </div>
                                    <?php endif; ?>
                                    <span class="fw-semibold"><?php echo htmlspecialchars($user['username']); ?></span>
                                </div>
                            </td>
                            <td><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td>
                                <div class="fw-semibold text-sm"><?php echo htmlspecialchars($user['department_name'] ?? 'Sin asignar'); ?></div>
                                <div class="text-muted text-xs"><?php echo htmlspecialchars($user['position'] ?? 'Sin cargo'); ?></div>
                            </td>
                            <td>
                                <?php if ($user['role'] === 'admin'): ?>
                                    <span class="badge bg-danger bg-opacity-10 text-danger">Administrador</span>
                                <?php elseif ($user['role'] === 'technician'): ?>
                                    <span class="badge bg-info bg-opacity-10 text-info">Técnico</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary">Usuario Final</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($user['status'] === 'active'): ?>
                                    <span class="badge bg-success">Activo</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Inactivo</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <a href="/users/edit/<?php echo $user['id']; ?>" class="btn btn-sm btn-outline-secondary me-1" title="Editar"><i class="bi bi-pencil"></i></a>
                                <?php if ($user['id'] !== $_SESSION['user']['id']): ?>
                                    <form action="/users/toggle/<?php echo $user['id']; ?>" method="POST" class="d-inline">
                                        <button type="submit" class="btn btn-sm <?php echo $user['status'] === 'active' ? 'btn-outline-danger' : 'btn-outline-success'; ?>" title="<?php echo $user['status'] === 'active' ? 'Desactivar' : 'Activar'; ?>">
                                            <i class="bi <?php echo $user['status'] === 'active' ? 'bi-person-x' : 'bi-person-check'; ?>"></i>
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
