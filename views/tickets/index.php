<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="fw-bold mb-0"><?php echo $isUserPanel ? 'Mis Tickets' : 'Gestión de Tickets'; ?></h2>
        <p class="text-muted mb-0">Consulte, filtre y administre las solicitudes de soporte técnico.</p>
    </div>
    <div>
        <a href="/tickets/create" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Crear Ticket</a>
    </div>
</div>

<!-- Filters Panel -->
<div class="card border-0 mb-4 shadow-sm">
    <div class="card-header border-0 bg-transparent py-3">
        <h6 class="fw-bold mb-0 text-muted"><i class="bi bi-funnel me-2"></i> Filtros de Búsqueda</h6>
    </div>
    <div class="card-body px-4 pb-4 pt-0">
        <form method="GET" action="<?php echo $isUserPanel ? '/my-tickets' : '/tickets'; ?>">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label text-sm fw-semibold">Estado</label>
                    <select class="form-select text-sm" name="status_id">
                        <option value="">Todos los Estados</option>
                        <?php foreach ($statuses as $st): ?>
                            <?php if (in_array((int)$st['id'], [2, 4, 7])): ?>
                                <option value="<?php echo $st['id']; ?>" <?php echo isset($_GET['status_id']) && $_GET['status_id'] == $st['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($st['id'] == 4 ? 'Pendiente' : $st['name']); ?>
                                </option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label text-sm fw-semibold">Prioridad</label>
                    <select class="form-select text-sm" name="priority_id">
                        <option value="">Todas las Prioridades</option>
                        <?php foreach ($priorities as $pr): ?>
                            <option value="<?php echo $pr['id']; ?>" <?php echo isset($_GET['priority_id']) && $_GET['priority_id'] == $pr['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($pr['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label text-sm fw-semibold">Categoría</label>
                    <select class="form-select text-sm" name="category_id">
                        <option value="">Todas las Categorías</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>" <?php echo isset($_GET['category_id']) && $_GET['category_id'] == $cat['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($cat['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-outline-primary text-sm w-100"><i class="bi bi-search"></i> Filtrar</button>
                    <a href="<?php echo $isUserPanel ? '/my-tickets' : '/tickets'; ?>" class="btn btn-outline-secondary text-sm"><i class="bi bi-arrow-counterclockwise"></i> Limpiar</a>
                </div>
            </div>

            <!-- Quick filters tags -->
            <div class="d-flex flex-wrap gap-2 mt-3 pt-3 border-top">
                <a href="?today=1" class="btn btn-xs btn-outline-secondary text-xs <?php echo isset($_GET['today']) ? 'active' : ''; ?>">Hoy</a>
                <a href="?week=1" class="btn btn-xs btn-outline-secondary text-xs <?php echo isset($_GET['week']) ? 'active' : ''; ?>">Esta Semana</a>
                <a href="?month=1" class="btn btn-xs btn-outline-secondary text-xs <?php echo isset($_GET['month']) ? 'active' : ''; ?>">Este Mes</a>
                <a href="?urgent=1" class="btn btn-xs btn-outline-danger text-xs <?php echo isset($_GET['urgent']) ? 'active' : ''; ?>">Urgentes / Críticos</a>
                <a href="?unassigned=1" class="btn btn-xs btn-outline-info text-xs <?php echo isset($_GET['unassigned']) ? 'active' : ''; ?>">Sin Asignar</a>
                <a href="?in_process=1" class="btn btn-xs btn-outline-warning text-xs <?php echo isset($_GET['in_process']) ? 'active' : ''; ?>">En Proceso</a>
            </div>
        </form>
    </div>
</div>

<!-- Tickets Table -->
<div class="card border-0">
    <div class="card-body p-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle datatable">
                <thead>
                    <tr>
                        <th>Número</th>
                        <th>Título</th>
                        <th>Categoría</th>
                        <th>Solicitante</th>
                        <th>Prioridad</th>
                        <th>Estado</th>
                        <th>Fecha Creación</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tickets as $t): ?>
                        <tr>
                            <td>
                                <a href="/tickets/view/<?php echo $t['id']; ?>" class="fw-bold text-decoration-none">
                                    <?php echo htmlspecialchars($t['ticket_number']); ?>
                                </a>
                            </td>
                            <td>
                                <div class="fw-semibold text-truncate" style="max-width: 250px;" title="<?php echo htmlspecialchars($t['title']); ?>">
                                    <?php echo htmlspecialchars($t['title']); ?>
                                </div>
                                <div class="text-xs text-muted">
                                    Asignado: <?php echo $t['tech_first'] ? htmlspecialchars($t['tech_first'] . ' ' . $t['tech_last']) : '<span class="text-danger fw-semibold">Sin Técnico</span>'; ?>
                                </div>
                            </td>
                            <td><?php echo htmlspecialchars($t['category_name'] ?? 'General'); ?></td>
                            <td>
                                <div class="fw-semibold text-sm"><?php echo htmlspecialchars($t['req_first'] . ' ' . $t['req_last']); ?></div>
                                <div class="text-xs text-muted"><?php echo htmlspecialchars($t['department_name'] ?? 'General'); ?></div>
                            </td>
                            <td>
                                <span class="badge" style="background-color: <?php echo htmlspecialchars($t['priority_color']); ?>;">
                                    <?php echo htmlspecialchars($t['priority_name']); ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge rounded-pill" style="background-color: <?php echo htmlspecialchars($t['status_color']); ?>18; color: <?php echo htmlspecialchars($t['status_color']); ?>; border: 1px solid <?php echo htmlspecialchars($t['status_color']); ?>40;">
                                    <?php echo htmlspecialchars($t['status_id'] == 4 ? 'Pendiente' : ($t['status_id'] == 1 ? 'Abierto' : $t['status_name'])); ?>
                                </span>
                                <?php if (!empty($t['closed_at'])): ?>
                                    <div class="text-danger fw-semibold mt-1" style="font-size: 11px;">
                                        Cerrado: <?php echo date('d/m/Y H:i', strtotime($t['closed_at'])); ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td class="text-muted text-sm">
                                <?php echo date('d/m/Y H:i', strtotime($t['created_at'])); ?>
                            </td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-1">
                                    <a href="/tickets/view/<?php echo $t['id']; ?>" class="btn btn-sm btn-primary" title="Ver Detalles"><i class="bi bi-eye"></i> Ver</a>
                                    <?php if ($_SESSION['user']['role'] !== 'user'): ?>
                                        <form action="/tickets/delete/<?php echo $t['id']; ?>" method="POST" class="m-0" onsubmit="return confirm('¿Eliminar este ticket permanentemente?');">
                                            <button type="submit" class="btn btn-sm btn-danger" title="Eliminar"><i class="bi bi-trash"></i></button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
