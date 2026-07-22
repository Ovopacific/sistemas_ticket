<div class="mb-4">
    <a href="<?php echo $_SESSION['user']['role'] === 'user' ? '/my-tickets' : '/tickets'; ?>" class="btn btn-outline-secondary btn-sm mb-3"><i class="bi bi-arrow-left"></i> Volver</a>
    <h2 class="fw-bold mb-0">Crear Solicitud de Soporte</h2>
    <p class="text-muted">Describa detalladamente su problema técnico para que nuestro equipo lo asigne y solucione.</p>
</div>

<div class="card border-0">
    <div class="card-body p-4 p-md-5">
        <form method="POST" action="/tickets/create" enctype="multipart/form-data" class="needs-validation">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">

            <!-- Configuración para rol de Técnico o Administrador -->
            <?php if ($_SESSION['user']['role'] !== 'user' && !empty($users)): ?>
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label for="requester_id" class="form-label fw-semibold">Solicitante (Afectado) *</label>
                        <select class="form-select" id="requester_id" name="requester_id" required>
                            <option value="">Seleccione el usuario...</option>
                            <?php foreach ($users as $u): ?>
                                <option value="<?php echo $u['id']; ?>"><?php echo htmlspecialchars($u['first_name'] . ' ' . $u['last_name'] . ' (' . $u['email'] . ')'); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="department_id" class="form-label fw-semibold">Departamento *</label>
                        <select class="form-select" id="department_id" name="department_id" required>
                            <option value="">Seleccione...</option>
                            <?php foreach ($departments as $dept): ?>
                                <option value="<?php echo $dept['id']; ?>"><?php echo htmlspecialchars($dept['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label for="category_id" class="form-label fw-semibold">Categoría *</label>
                        <select class="form-select" id="category_id" name="category_id" required>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="priority_id" class="form-label fw-semibold">Prioridad *</label>
                        <select class="form-select" id="priority_id" name="priority_id" required>
                            <?php foreach ($priorities as $pr): ?>
                                <option value="<?php echo $pr['id']; ?>"><?php echo htmlspecialchars($pr['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            <?php else: ?>
                <!-- Hidden fields to support database and controller defaults para usuarios comunes -->
                <input type="hidden" name="priority_id" value="1">
                <input type="hidden" name="category_id" value="<?php echo $categories[0]['id'] ?? '1'; ?>">
                <input type="hidden" name="department_id" value="<?php echo $_SESSION['user']['department_id'] ?? ''; ?>">

                <div class="mb-3">
                    <label class="form-label fw-semibold">Nombre del Solicitante</label>
                    <input type="text" class="form-control bg-light" value="<?php echo htmlspecialchars($_SESSION['user']['first_name'] . ' ' . $_SESSION['user']['last_name']); ?>" readonly>
                </div>
            <?php endif; ?>

            <div class="mb-3">
                <label for="title" class="form-label fw-semibold">Asunto *</label>
                <input type="text" class="form-control" id="title" name="title" placeholder="ej. Falla en el sistema de red local" required>
            </div>

            <div class="mb-3">
                <label for="description" class="form-label fw-semibold">Descripción *</label>
                <textarea class="form-control" id="description" name="description" rows="5" placeholder="Describa de manera detallada el problema técnico..." required></textarea>
            </div>

            <div class="mb-4">
                <label for="attachment" class="form-label fw-semibold">Subir Imagen o Captura de Pantalla</label>
                <input type="file" class="form-control" id="attachment" name="attachment" accept="image/*">
                <div class="form-text text-xs text-muted mt-2">
                    Formatos de imagen permitidos: PNG, JPG, JPEG, GIF.
                </div>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-primary px-4 py-2 fw-semibold">Enviar Ticket</button>
                <a href="<?php echo $_SESSION['user']['role'] === 'user' ? '/my-tickets' : '/tickets'; ?>" class="btn btn-outline-secondary px-4 py-2 ms-2">Cancelar</a>
            </div>
        </form>
    </div>
</div>
