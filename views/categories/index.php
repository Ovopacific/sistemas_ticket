<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="fw-bold mb-0">Categorías de Soporte</h2>
        <p class="text-muted">Gestione los tipos o clases de problemas (Hardware, Software, Red, etc.) para catalogar tickets.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="/departments" class="btn btn-outline-secondary"><i class="bi bi-building"></i> Gestionar Departamentos</a>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createCatModal">
            <i class="bi bi-plus-lg"></i> Nueva Categoría
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
                        <th>Nombre de la Categoría</th>
                        <th>Descripción</th>
                        <th class="text-end" style="width: 150px;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categories as $cat): ?>
                        <tr>
                            <td><?php echo $cat['id']; ?></td>
                            <td class="fw-bold"><?php echo htmlspecialchars($cat['name']); ?></td>
                            <td><?php echo htmlspecialchars($cat['description'] ?? 'Sin descripción'); ?></td>
                            <td class="text-end">
                                <button type="button" class="btn btn-sm btn-outline-secondary me-1" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#editCatModal<?php echo $cat['id']; ?>">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <form action="/categories/delete/<?php echo $cat['id']; ?>" method="POST" class="d-inline" onsubmit="return confirm('¿Está seguro de eliminar esta categoría?');">
                                    <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>

                        <!-- Edit Category Modal -->
                        <div class="modal fade" id="editCatModal<?php echo $cat['id']; ?>" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content border-0 shadow">
                                    <div class="modal-header">
                                        <h5 class="modal-title fw-bold">Editar Categoría</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <form action="/categories/edit/<?php echo $cat['id']; ?>" method="POST">
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label for="name<?php echo $cat['id']; ?>" class="form-label fw-semibold">Nombre de la Categoría</label>
                                                <input type="text" class="form-control" id="name<?php echo $cat['id']; ?>" name="name" value="<?php echo htmlspecialchars($cat['name']); ?>" required>
                                            </div>
                                            <div class="mb-3">
                                                <label for="desc<?php echo $cat['id']; ?>" class="form-label fw-semibold">Descripción</label>
                                                <textarea class="form-control" id="desc<?php echo $cat['id']; ?>" name="description" rows="3"><?php echo htmlspecialchars($cat['description'] ?? ''); ?></textarea>
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

<!-- Create Category Modal -->
<div class="modal fade" id="createCatModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Nueva Categoría</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="/categories/create" method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="create_name" class="form-label fw-semibold">Nombre de la Categoría *</label>
                        <input type="text" class="form-control" id="create_name" name="name" placeholder="ej. VPN" required>
                    </div>
                    <div class="mb-3">
                        <label for="create_desc" class="form-label fw-semibold">Descripción</label>
                        <textarea class="form-control" id="create_desc" name="description" rows="3" placeholder="ej. Problemas relacionados a la conexión remota VPN..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Crear Categoría</button>
                </div>
            </form>
        </div>
    </div>
</div>
