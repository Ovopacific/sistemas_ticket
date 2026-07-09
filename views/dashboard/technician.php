<?php
$db = \App\Core\Database::getConnection();
$techStmt = $db->prepare("SELECT specialty FROM technicians WHERE user_id = ?");
$techStmt->execute([$_SESSION['user']['id']]);
$techInfo = $techStmt->fetch();
$specialtyName = $techInfo['specialty'] ?? 'Soporte General';
?>
<div class="row mb-4">
    <div class="col-12">
        <h2 class="fw-bold mb-0">Panel del Técnico</h2>
        <p class="text-muted mb-0">Resumen de solicitudes de soporte técnico asignadas a su perfil. <strong>Especialidad:</strong> <?php echo htmlspecialchars($specialtyName); ?></p>
    </div>
</div>

<!-- Metrics Row -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card border-0 stat-card stat-primary shadow-sm">
            <div class="card-body p-4 d-flex align-items-center justify-content-between">
                <div>
                    <div class="text-xs text-muted text-uppercase fw-bold mb-1">Mis Tickets Asignados</div>
                    <div class="h2 fw-bold mb-0" style="color: var(--text-main);"><?php echo $metrics['total']; ?></div>
                </div>
                <div class="bg-primary bg-opacity-10 text-primary rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                    <i class="bi bi-ticket-detailed fs-4"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 stat-card stat-warning shadow-sm">
            <div class="card-body p-4 d-flex align-items-center justify-content-between">
                <div>
                    <div class="text-xs text-muted text-uppercase fw-bold mb-1">Pendientes por Resolver</div>
                    <div class="h2 fw-bold mb-0" style="color: var(--text-main);"><?php echo $metrics['pending']; ?></div>
                </div>
                <div class="bg-warning bg-opacity-10 text-warning rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                    <i class="bi bi-clock-history fs-4"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 stat-card stat-danger shadow-sm">
            <div class="card-body p-4 d-flex align-items-center justify-content-between">
                <div>
                    <div class="text-xs text-muted text-uppercase fw-bold mb-1">Tickets Urgentes</div>
                    <div class="h2 fw-bold mb-0" style="color: var(--text-main);"><?php echo $metrics['urgent']; ?></div>
                </div>
                <div class="bg-danger bg-opacity-10 text-danger rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                    <i class="bi bi-exclamation-triangle fs-4"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 stat-card stat-primary shadow-sm">
            <div class="card-body p-4 d-flex align-items-center justify-content-between">
                <div>
                    <div class="text-xs text-muted text-uppercase fw-bold mb-1">En Proceso</div>
                    <div class="h2 fw-bold mb-0" style="color: var(--text-main);"><?php echo $metrics['inProcess']; ?></div>
                </div>
                <div class="bg-info bg-opacity-10 text-info rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                    <i class="bi bi-gear fs-4"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Assigned tickets table -->
    <div class="col-lg-8 mb-4 mb-lg-0">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header border-0 bg-transparent py-3 d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0 text-muted">Mis Asignaciones Activas</h6>
                <a href="/my-tickets" class="btn btn-sm btn-link text-decoration-none">Ver Todos</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr class="table-light">
                                <th class="ps-4">Número</th>
                                <th>Título / Solicitante</th>
                                <th>Prioridad</th>
                                <th>Estado</th>
                                <th class="pe-4 text-end">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($myTicketsList)): ?>
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">No tienes tickets activos asignados. ¡Buen trabajo!</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($myTicketsList as $ticket): ?>
                                    <tr>
                                        <td class="ps-4"><a href="/tickets/view/<?php echo $ticket['id']; ?>" class="fw-bold text-decoration-none"><?php echo htmlspecialchars($ticket['ticket_number']); ?></a></td>
                                        <td>
                                            <div class="fw-semibold text-truncate" style="max-width: 250px;"><?php echo htmlspecialchars($ticket['title']); ?></div>
                                            <div class="text-xs text-muted">Por: <?php echo htmlspecialchars($ticket['req_first'] . ' ' . $ticket['req_last']); ?></div>
                                        </td>
                                        <td>
                                            <span class="badge" style="background-color: <?php echo htmlspecialchars($ticket['priority_color']); ?>;">
                                                <?php echo htmlspecialchars($ticket['priority_name']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge rounded-pill" style="background-color: <?php echo htmlspecialchars($ticket['status_color']); ?>18; color: <?php echo htmlspecialchars($ticket['status_color']); ?>; border: 1px solid <?php echo htmlspecialchars($ticket['status_color']); ?>40;">
                                                <?php echo htmlspecialchars($ticket['status_name']); ?>
                                            </span>
                                        </td>
                                        <td class="pe-4 text-end">
                                            <a href="/tickets/view/<?php echo $ticket['id']; ?>" class="btn btn-xs btn-primary py-1 text-xs">Atender</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Alerts / Notifications panel -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header border-0 bg-transparent py-3">
                <h6 class="fw-bold mb-0 text-muted">Notificaciones Recientes</h6>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush mb-0">
                    <?php if (empty($notifications)): ?>
                        <div class="text-center py-4 text-muted">No tienes alertas sin leer.</div>
                    <?php else: ?>
                        <?php foreach ($notifications as $notif): ?>
                            <form action="/notifications/read/<?php echo $notif['id']; ?>" method="POST" class="m-0">
                                <button type="submit" class="list-group-item list-group-item-action bg-transparent border-bottom px-4 py-3 text-start">
                                    <div class="d-flex justify-content-between mb-1">
                                        <span class="text-xs text-primary fw-semibold">Alerta de Ticket</span>
                                        <span class="text-xs text-muted"><?php echo date('d/m H:i', strtotime($notif['created_at'])); ?></span>
                                    </div>
                                    <div class="text-sm fw-medium" style="color: var(--text-main);"><?php echo htmlspecialchars($notif['message']); ?></div>
                                </button>
                            </form>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
