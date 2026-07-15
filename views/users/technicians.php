<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="fw-bold mb-0">Técnicos de Sistemas</h2>
        <p class="text-muted mb-0">Consulte la disponibilidad, especialidades y carga de tickets del equipo de soporte.</p>
    </div>
    <div>
        <a href="/users" class="btn btn-outline-secondary"><i class="bi bi-people"></i> Volver a Usuarios</a>
    </div>
</div>

<div class="row">
    <?php foreach ($techs as $tech): ?>
        <div class="col-md-4 mb-4">
            <div class="card border-0 h-100 shadow-sm">
                <div class="card-body p-4 text-center">
                    <div class="position-relative d-inline-block mb-3">
                        <?php if (!empty($tech['avatar_path'])): ?>
                            <img src="<?php echo (strpos($tech['avatar_path'], 'http') === 0 ? '' : '/') . htmlspecialchars($tech['avatar_path']); ?>" alt="Avatar" class="rounded-circle" style="width: 80px; height: 80px; object-fit: cover;">
                        <?php else: ?>
                            <div class="bg-info bg-opacity-10 text-info rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px; font-size: 2rem; font-weight: bold;">
                                <?php echo strtoupper(substr($tech['first_name'], 0, 1)); ?>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Status indicator dot -->
                        <?php 
                        $statusClass = 'bg-success';
                        if ($tech['tech_status'] === 'busy') $statusClass = 'bg-danger';
                        if ($tech['tech_status'] === 'away') $statusClass = 'bg-warning';
                        ?>
                        <span class="position-absolute bottom-0 end-0 border border-white border-2 rounded-circle p-2 <?php echo $statusClass; ?>" title="<?php echo ucfirst($tech['tech_status']); ?>"></span>
                    </div>

                    <h5 class="fw-bold mb-1"><?php echo htmlspecialchars($tech['first_name'] . ' ' . $tech['last_name']); ?></h5>
                    <p class="text-muted text-sm mb-2"><?php echo htmlspecialchars($tech['position'] ?? 'Técnico de Soporte'); ?></p>
                    
                    <div class="bg-light rounded p-2 mb-3 text-start">
                        <div class="text-xs text-muted text-uppercase fw-semibold mb-1">Especialidad</div>
                        <div class="text-sm fw-bold text-truncate" title="<?php echo htmlspecialchars($tech['specialty'] ?? 'Soporte General'); ?>">
                            <?php echo htmlspecialchars($tech['specialty'] ?? 'Soporte General'); ?>
                        </div>
                    </div>

                    <div class="row g-2 border-top pt-3">
                        <div class="col-6 border-end">
                            <div class="text-xs text-muted text-uppercase">Tickets Activos</div>
                            <div class="h4 fw-bold mb-0 text-primary"><?php echo (int)$tech['active_tickets_count']; ?></div>
                        </div>
                        <div class="col-6">
                            <div class="text-xs text-muted text-uppercase">Departamento</div>
                            <div class="text-sm fw-semibold text-truncate"><?php echo htmlspecialchars($tech['department_name'] ?? 'General'); ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>
