<div class="mb-4">
    <h2 class="fw-bold mb-0">Bitácora de Auditoría del Sistema</h2>
    <p class="text-muted">Registro secuencial e inmutable de las operaciones realizadas por los usuarios en el sistema.</p>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle datatable">
                <thead>
                    <tr>
                        <th style="width: 160px;">Fecha y Hora</th>
                        <th>Usuario</th>
                        <th>Operación / Acción</th>
                        <th>Dirección IP</th>
                        <th>Detalles del Registro</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($logs as $log): ?>
                        <tr>
                            <td class="text-muted text-sm fw-medium"><?php echo date('d/m/Y H:i:s', strtotime($log['created_at'])); ?></td>
                            <td>
                                <?php if ($log['user_id']): ?>
                                    <div class="fw-bold"><?php echo htmlspecialchars($log['username']); ?></div>
                                    <div class="text-muted text-xs"><?php echo htmlspecialchars($log['first_name'] . ' ' . $log['last_name']); ?></div>
                                <?php else: ?>
                                    <span class="text-danger fw-semibold">Sistema / Invitado</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 px-2 py-1 text-xs">
                                    <?php echo htmlspecialchars($log['action']); ?>
                                </span>
                            </td>
                            <td class="text-sm code text-muted font-monospace"><?php echo htmlspecialchars($log['ip_address']); ?></td>
                            <td class="text-break text-sm"><?php echo htmlspecialchars($log['details'] ?? 'Sin detalles adicionales'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
