<div class="mb-4">
    <h2 class="fw-bold mb-0">Generador de Reportes</h2>
    <p class="text-muted">Filtre y exporte información estadística detallada sobre las solicitudes de soporte técnico.</p>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-4 p-md-5">
        <form method="POST" action="/reports/export" target="_blank">
            <div class="row g-3 mb-4">
                <div class="col-md-6 col-lg-3">
                    <label for="start_date" class="form-label fw-semibold text-sm">Fecha Inicial</label>
                    <input type="date" class="form-control" id="start_date" name="start_date">
                </div>
                <div class="col-md-6 col-lg-3">
                    <label for="end_date" class="form-label fw-semibold text-sm">Fecha Final</label>
                    <input type="date" class="form-control" id="end_date" name="end_date">
                </div>
                <div class="col-md-6 col-lg-3">
                    <label for="department_id" class="form-label fw-semibold text-sm">Departamento</label>
                    <select class="form-select" id="department_id" name="department_id">
                        <option value="">Todos los Departamentos</option>
                        <?php foreach ($departments as $dept): ?>
                            <option value="<?php echo $dept['id']; ?>"><?php echo htmlspecialchars($dept['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6 col-lg-3">
                    <label for="category_id" class="form-label fw-semibold text-sm">Categoría</label>
                    <select class="form-select" id="category_id" name="category_id">
                        <option value="">Todas las Categorías</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-6 col-lg-4">
                    <label for="status_id" class="form-label fw-semibold text-sm">Estado</label>
                    <select class="form-select" id="status_id" name="status_id">
                        <option value="">Todos los Estados</option>
                        <?php foreach ($statuses as $st): ?>
                            <option value="<?php echo $st['id']; ?>"><?php echo htmlspecialchars($st['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6 col-lg-4">
                    <label for="priority_id" class="form-label fw-semibold text-sm">Prioridad</label>
                    <select class="form-select" id="priority_id" name="priority_id">
                        <option value="">Todas las Prioridades</option>
                        <?php foreach ($priorities as $prio): ?>
                            <option value="<?php echo $prio['id']; ?>"><?php echo htmlspecialchars($prio['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-12 col-lg-4">
                    <label for="assigned_technician_id" class="form-label fw-semibold text-sm">Técnico Asignado</label>
                    <select class="form-select" id="assigned_technician_id" name="assigned_technician_id">
                        <option value="">Todos los Técnicos</option>
                        <?php foreach ($technicians as $tech): ?>
                            <option value="<?php echo $tech['id']; ?>"><?php echo htmlspecialchars($tech['first_name'] . ' ' . $tech['last_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label fw-semibold text-sm d-block">Formato de Exportación</label>
                <div class="d-flex gap-4 mt-2">
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="format" id="format_csv" value="csv" checked>
                        <label class="form-check-label fw-medium text-sm" for="format_csv">
                            <i class="bi bi-filetype-csv text-success me-1 fs-5"></i> CSV (Para importación)
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="format" id="format_xls" value="excel">
                        <label class="form-check-label fw-medium text-sm" for="format_xls">
                            <i class="bi bi-file-earmark-excel text-primary me-1 fs-5"></i> Excel (Hojas de cálculo)
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="format" id="format_print" value="print">
                        <label class="form-check-label fw-medium text-sm" for="format_print">
                            <i class="bi bi-printer text-danger me-1 fs-5"></i> Vista de Impresión (PDF)
                        </label>
                    </div>
                </div>
            </div>

            <div class="pt-3 border-top">
                <button type="submit" class="btn btn-primary px-5 py-2 fw-semibold"><i class="bi bi-download me-2"></i> Generar y Exportar</button>
            </div>
        </form>
    </div>
</div>
