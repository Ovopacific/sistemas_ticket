<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="fw-bold mb-0">Departamentos</h2>
        <p class="text-muted">Administre los departamentos de la organización vinculados a las solicitudes de soporte.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="/categories" class="btn btn-outline-secondary"><i class="bi bi-tag"></i> Gestionar Categorías</a>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createDeptModal">
            <i class="bi bi-plus-lg"></i> Nuevo Departamento
        </button>
    </div>
</div>

<div class="card border-0">
    <div class="card-body p-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle datatable">
                <thead>
                    <tr>
                        <th style="width: 80px;">ID</th>
                        <th>Nombre del Departamento</th>
                        <th>Descripción</th>
                        <th class="text-end" style="width: 150px;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($departments as $dept): ?>
                        <tr>
                            <td><?php echo $dept['id']; ?></td>
                            <td class="fw-bold"><?php echo htmlspecialchars($dept['name']); ?></td>
                            <td><?php echo htmlspecialchars($dept['description'] ?? 'Sin descripción'); ?></td>
                            <td class="text-end">
                                <button type="button" class="btn btn-sm btn-outline-secondary me-1" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#editDeptModal<?php echo $dept['id']; ?>">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <form action="/departments/delete/<?php echo $dept['id']; ?>" method="POST" class="d-inline" onsubmit="return confirm('¿Está seguro de eliminar este departamento?');">
                                    <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>

                        <!-- Edit Department Modal -->
                        <div class="modal fade" id="editDeptModal<?php echo $dept['id']; ?>" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content border-0 shadow">
                                    <div class="modal-header">
                                        <h5 class="modal-title fw-bold">Editar Departamento</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <form action="/departments/edit/<?php echo $dept['id']; ?>" method="POST">
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label for="name<?php echo $dept['id']; ?>" class="form-label fw-semibold">Nombre del Departamento</label>
                                                <input type="text" class="form-control" id="name<?php echo $dept['id']; ?>" name="name" value="<?php echo htmlspecialchars($dept['name']); ?>" required>
                                            </div>
                                            <div class="mb-3">
                                                <label for="desc<?php echo $dept['id']; ?>" class="form-label fw-semibold">Descripción</label>
                                                <textarea class="form-control" id="desc<?php echo $dept['id']; ?>" name="description" rows="3"><?php echo htmlspecialchars($dept['description'] ?? ''); ?></textarea>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                                            <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Create Department Modal -->
<div class="modal fade" id="createDeptModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Nuevo Departamento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="/departments/create" method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="create_name" class="form-label fw-semibold">Nombre del Departamento *</label>
                        <input type="text" class="form-control" id="create_name" name="name" placeholder="ej. Contabilidad" required>
                    </div>
                    <div class="mb-3">
                        <label for="create_desc" class="form-label fw-semibold">Descripción</label>
                        <textarea class="form-control" id="create_desc" name="description" rows="3" placeholder="Descripción breve del departamento..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Crear Departamento</button>
                </div>
            </form>
        </div>
    </div>
</div>
