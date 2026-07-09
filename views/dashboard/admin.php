<div class="row mb-4">
    <div class="col-12">
        <h2 class="fw-bold mb-0">Panel de Control General</h2>
        <p class="text-muted mb-0">Consolidado general de operaciones de soporte técnico y rendimiento del departamento
            de TI.</p>
    </div>
</div>

<!-- Metrics Row -->
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="card border-0 stat-card stat-primary shadow-sm">
            <div class="card-body p-4 d-flex align-items-center justify-content-between">
                <div>
                    <div class="text-xs text-muted text-uppercase fw-bold mb-1">Total de Tickets</div>
                    <div class="h2 fw-bold mb-0" style="color: var(--text-main);"><?php echo $metrics['total']; ?></div>
                </div>
                <div class="bg-primary bg-opacity-10 text-primary rounded-circle p-2 d-flex align-items-center justify-content-center"
                    style="width: 48px; height: 48px;">
                    <i class="bi bi-ticket-detailed fs-4"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card border-0 stat-card stat-warning shadow-sm">
            <div class="card-body p-4 d-flex align-items-center justify-content-between">
                <div>
                    <div class="text-xs text-muted text-uppercase fw-bold mb-1">Abiertos / Nuevos</div>
                    <div class="h2 fw-bold mb-0" style="color: var(--text-main);">
                        <?php echo $metrics['open'] + $metrics['assigned']; ?></div>
                </div>
                <div class="bg-warning bg-opacity-10 text-warning rounded-circle p-2 d-flex align-items-center justify-content-center"
                    style="width: 48px; height: 48px;">
                    <i class="bi bi-envelope-open fs-4"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card border-0 stat-card stat-danger shadow-sm">
            <div class="card-body p-4 d-flex align-items-center justify-content-between">
                <div>
                    <div class="text-xs text-muted text-uppercase fw-bold mb-1">Pendientes / Urgentes</div>
                    <div class="h2 fw-bold mb-0" style="color: var(--text-main);"><?php echo $metrics['pending']; ?>
                        <span class="fs-6 text-muted">(<?php echo $metrics['urgent']; ?> urg)</span></div>
                </div>
                <div class="bg-danger bg-opacity-10 text-danger rounded-circle p-2 d-flex align-items-center justify-content-center"
                    style="width: 48px; height: 48px;">
                    <i class="bi bi-exclamation-triangle fs-4"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card border-0 stat-card stat-success shadow-sm">
            <div class="card-body p-4 d-flex align-items-center justify-content-between">
                <div>
                    <div class="text-xs text-muted text-uppercase fw-bold mb-1">Resueltos / Cerrados</div>
                    <div class="h2 fw-bold mb-0" style="color: var(--text-main);">
                        <?php echo $metrics['resolved'] + $metrics['closed']; ?></div>
                </div>
                <div class="bg-success bg-opacity-10 text-success rounded-circle p-2 d-flex align-items-center justify-content-center"
                    style="width: 48px; height: 48px;">
                    <i class="bi bi-patch-check fs-4"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Secondary KPIs -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center p-4">
                <div class="bg-info bg-opacity-10 text-info rounded p-3 me-3"><i class="bi bi-clock-history fs-3"></i>
                </div>
                <div>
                    <div class="text-xs text-muted text-uppercase fw-bold">Promedio de Respuesta</div>
                    <div class="h4 fw-bold mb-0"><?php echo $metrics['avgResponse']; ?> horas</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center p-4">
                <div class="bg-success bg-opacity-10 text-success rounded p-3 me-3"><i
                        class="bi bi-check2-circle fs-3"></i></div>
                <div>
                    <div class="text-xs text-muted text-uppercase fw-bold">Promedio de Solución</div>
                    <div class="h4 fw-bold mb-0"><?php echo $metrics['avgResolution']; ?> horas</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center p-4">
                <div class="bg-danger bg-opacity-10 text-danger rounded p-3 me-3"><i
                        class="bi bi-exclamation-triangle fs-3"></i></div>
                <div>
                    <div class="text-xs text-muted text-uppercase fw-bold">Tickets Sin Asignar</div>
                    <div class="h4 fw-bold mb-0"><?php echo $metrics['unassigned']; ?> tickets</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Graphs Rows -->
<div class="row mb-4">
    <div class="col-lg-6 mb-4 mb-lg-0">
        <div class="card border-0 h-100 shadow-sm">
            <div class="card-header border-0 bg-transparent py-3">
                <h6 class="fw-bold mb-0 text-muted">Tickets por Departamento</h6>
            </div>
            <div class="card-body d-flex align-items-center justify-content-center p-4" style="height: 320px;">
                <canvas id="deptChart"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card border-0 h-100 shadow-sm">
            <div class="card-header border-0 bg-transparent py-3">
                <h6 class="fw-bold mb-0 text-muted">Carga por Técnico</h6>
            </div>
            <div class="card-body d-flex align-items-center justify-content-center p-4" style="height: 320px;">
                <canvas id="techChart"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-lg-4 mb-4 mb-lg-0">
        <div class="card border-0 h-100 shadow-sm">
            <div class="card-header border-0 bg-transparent py-3">
                <h6 class="fw-bold mb-0 text-muted">Tickets por Categoría</h6>
            </div>
            <div class="card-body d-flex align-items-center justify-content-center p-4" style="height: 300px;">
                <canvas id="catChart"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-4 mb-4 mb-lg-0">
        <div class="card border-0 h-100 shadow-sm">
            <div class="card-header border-0 bg-transparent py-3">
                <h6 class="fw-bold mb-0 text-muted">Tickets por Prioridad</h6>
            </div>
            <div class="card-body d-flex align-items-center justify-content-center p-4" style="height: 300px;">
                <canvas id="prioChart"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card border-0 h-100 shadow-sm">
            <div class="card-header border-0 bg-transparent py-3">
                <h6 class="fw-bold mb-0 text-muted">Tickets Recibidos por Mes</h6>
            </div>
            <div class="card-body d-flex align-items-center justify-content-center p-4" style="height: 300px;">
                <canvas id="monthChart"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Details row: Recent tickets & Recent Activity -->
<div class="row">
    <!-- Recent Tickets -->
    <div class="col-lg-7 mb-4 mb-lg-0">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header border-0 bg-transparent py-3 d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0 text-muted">Últimos Tickets Creados</h6>
                <a href="/tickets" class="btn btn-sm btn-link text-decoration-none">Ver Todos</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr class="table-light">
                                <th class="ps-4">Número</th>
                                <th>Título</th>
                                <th>Estado</th>
                                <th class="pe-4">Fecha</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentTickets as $ticket): ?>
                                <tr>
                                    <td class="ps-4"><a href="/tickets/view/<?php echo $ticket['id']; ?>"
                                            class="fw-bold text-decoration-none"><?php echo htmlspecialchars($ticket['ticket_number']); ?></a>
                                    </td>
                                    <td class="fw-semibold text-truncate" style="max-width: 250px;">
                                        <?php echo htmlspecialchars($ticket['title']); ?></td>
                                    <td>
                                        <span class="badge rounded-pill"
                                            style="background-color: <?php echo htmlspecialchars($ticket['status_color']); ?>18; color: <?php echo htmlspecialchars($ticket['status_color']); ?>; border: 1px solid <?php echo htmlspecialchars($ticket['status_color']); ?>40;">
                                            <?php echo htmlspecialchars($ticket['status_name']); ?>
                                        </span>
                                    </td>
                                    <td class="text-muted text-sm pe-4">
                                        <?php echo date('d/m H:i', strtotime($ticket['created_at'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header border-0 bg-transparent py-3">
                <h6 class="fw-bold mb-0 text-muted">Actividad Reciente</h6>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush mb-0">
                    <?php foreach ($recentActivity as $act): ?>
                        <div class="list-group-item bg-transparent px-4 py-3 text-sm">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="fw-bold"
                                    style="color: var(--text-main);"><?php echo htmlspecialchars($act['username'] ?? 'Sistema'); ?></span>
                                <span
                                    class="text-xs text-muted"><?php echo date('H:i', strtotime($act['created_at'])); ?></span>
                            </div>
                            <div class="text-muted text-xs"><?php echo htmlspecialchars($act['action']); ?>:
                                <?php echo htmlspecialchars($act['details']); ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart JS Setup Script -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // 1. Department Chart (Doughnut)
        new Chart(document.getElementById('deptChart'), {
            type: 'doughnut',
            data: {
                labels: [<?php echo implode(',', array_map(fn($r) => '"' . addslashes($r['name']) . '"', $charts['department'])); ?>],
                datasets: [{
                    data: [<?php echo implode(',', array_column($charts['department'], 'count')); ?>],
                    backgroundColor: ['#0d6efd', '#28a745', '#fd7e14', '#198754', '#ffc107', '#6f42c1', '#212529']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom' } }
            }
        });

        // 2. Technician Load Chart (Bar)
        new Chart(document.getElementById('techChart'), {
            type: 'bar',
            data: {
                labels: [<?php echo implode(',', array_map(fn($r) => '"' . addslashes($r['name']) . '"', $charts['technician'])); ?>],
                datasets: [{
                    label: 'Trabajo Asignado',
                    data: [<?php echo implode(',', array_column($charts['technician'], 'count')); ?>],
                    backgroundColor: '#3f8cff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
            }
        });

        // 3. Categories Pie Chart
        new Chart(document.getElementById('catChart'), {
            type: 'pie',
            data: {
                labels: [<?php echo implode(',', array_map(fn($r) => '"' . addslashes($r['name']) . '"', $charts['category'])); ?>],
                datasets: [{
                    data: [<?php echo implode(',', array_column($charts['category'], 'count')); ?>],
                    backgroundColor: ['#fd7e14', '#20c997', '#0dcaf0', '#0d6efd', '#6610f2', '#6f42c1', '#e83e8c', '#dc3545', '#adb5bd']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } }
            }
        });

        // 4. Priorities Radar/Doughnut Chart
        new Chart(document.getElementById('prioChart'), {
            type: 'doughnut',
            data: {
                labels: [<?php echo implode(',', array_map(fn($r) => '"' . addslashes($r['name']) . '"', $charts['priority'])); ?>],
                datasets: [{
                    data: [<?php echo implode(',', array_column($charts['priority'], 'count')); ?>],
                    backgroundColor: ['#28a745', '#fd7e14', '#dc3545', '#6f42c1', '#e83e8c']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom' } }
            }
        });

        // 5. Month Chart (Line)
        new Chart(document.getElementById('monthChart'), {
            type: 'line',
            data: {
                labels: [<?php echo implode(',', array_map(fn($r) => '"' . addslashes($r['month']) . '"', $charts['month'])); ?>],
                datasets: [{
                    label: 'Tickets',
                    data: [<?php echo implode(',', array_column($charts['month'], 'count')); ?>],
                    borderColor: '#198754',
                    backgroundColor: 'rgba(25, 135, 84, 0.1)',
                    fill: true,
                    tension: 0.3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
            }
        });
    });
</script>