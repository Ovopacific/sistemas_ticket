<?php
$db = \App\Core\Database::getConnection();
$currentUser = $_SESSION['user'] ?? null;
$basePath = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/\\');

// Query the list of tickets for the left column list
$leftTickets = [];
if ($currentUser) {
    $filters = [];
    if ($currentUser['role'] === 'user') {
        $filters['requester_id'] = $currentUser['id'];
    } elseif ($currentUser['role'] === 'technician') {
        $filters['assigned_technician_id'] = $currentUser['id'];
    }
    $leftTickets = \App\Models\TicketModel::getAll($filters);
}
?>

<!-- Container class to reset default page padding for this three-column layout -->
<div class="ticket-view-page-wrapper">
    <div class="ticket-view-layout">
        
        <!-- ================= COLUMNA IZQUIERDA: Listado de Tickets ================= -->
        <aside class="ticket-view-left">
            <div class="column-header-container px-3 py-3 border-bottom d-flex align-items-center justify-content-between">
                <span class="fw-bold text-sm text-uppercase tracking-wider" style="color: var(--text-muted);">Tickets</span>
                <span class="badge bg-light-primary rounded-pill"><?php echo count($leftTickets); ?></span>
            </div>
            <div class="ticket-list-scroll">
                <?php if (empty($leftTickets)): ?>
                    <div class="p-4 text-center text-muted text-xs">No hay tickets disponibles.</div>
                <?php else: ?>
                    <?php foreach ($leftTickets as $lt): ?>
                        <?php 
                        $isSelected = $lt['id'] == $ticket['id'];
                        $priorityColor = htmlspecialchars($lt['priority_color'] ?? '#cccccc');
                        ?>
                        <a href="<?php echo $basePath; ?>/tickets/view/<?php echo $lt['id']; ?>" class="ticket-list-card <?php echo $isSelected ? 'selected' : ''; ?>" style="border-left: 4px solid <?php echo $priorityColor; ?>;">
                            <div class="d-flex justify-content-between align-items-start mb-1">
                                <span class="ticket-number font-monospace text-xs fw-semibold">#<?php echo htmlspecialchars($lt['ticket_number']); ?></span>
                                <span class="badge rounded-pill text-xs" style="background-color: <?php echo htmlspecialchars($lt['status_color']); ?>12; color: <?php echo htmlspecialchars($lt['status_color']); ?>; border: 1px solid <?php echo htmlspecialchars($lt['status_color']); ?>25; padding: 2px 8px; font-size: 10px;">
                                    <?php echo htmlspecialchars($lt['status_id'] == 4 ? 'Pendiente' : ($lt['status_id'] == 1 ? 'Abierto' : $lt['status_name'])); ?>
                                </span>
                            </div>
                            <div class="ticket-title-text text-truncate fw-semibold mb-2" style="font-size: 0.85rem; color: var(--text-main);"><?php echo htmlspecialchars($lt['title']); ?></div>
                            <div class="d-flex justify-content-between align-items-center text-xs text-muted">
                                <span><i class="bi bi-person me-1"></i><?php echo htmlspecialchars($lt['req_first'] . ' ' . $lt['req_last']); ?></span>
                                <span><?php echo date('d M', strtotime($lt['created_at'])); ?></span>
                            </div>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </aside>

        <!-- ================= COLUMNA CENTRAL: Zona Principal / Conversación ================= -->
        <section class="ticket-view-center">
            <!-- Header de la Columna Central -->
            <div class="ticket-center-header px-4 py-3 border-bottom bg-white d-flex align-items-center justify-content-between" style="background-color: var(--card-bg) !important;">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <span class="h5 fw-bold mb-0" style="color: var(--text-main);">#<?php echo htmlspecialchars($ticket['ticket_number']); ?></span>
                        <span class="badge" style="background-color: <?php echo htmlspecialchars($ticket['priority_color']); ?>; font-size: 11px; padding: 3px 8px;">
                            <?php echo htmlspecialchars($ticket['priority_name']); ?>
                        </span>
                        <span class="badge rounded-pill" style="background-color: <?php echo htmlspecialchars($ticket['status_color']); ?>18; color: <?php echo htmlspecialchars($ticket['status_color']); ?>; border: 1px solid <?php echo htmlspecialchars($ticket['status_color']); ?>30; font-size: 11px; padding: 3px 8px;">
                            <?php echo htmlspecialchars($ticket['status_name']); ?>
                        </span>
                        <?php if (!empty($ticket['closed_at'])): ?>
                            <span class="badge rounded-pill bg-danger bg-opacity-10 text-danger border border-danger border-opacity-20" style="font-size: 11px; padding: 3px 8px;">
                                Cerrado: <?php echo date('d/m/Y H:i', strtotime($ticket['closed_at'])); ?>
                            </span>
                        <?php endif; ?>
                    </div>
                    <h5 class="mb-0 text-muted fw-semibold" style="font-size: 0.95rem;"><?php echo htmlspecialchars($ticket['title']); ?></h5>
                </div>
                
                <?php if ($_SESSION['user']['role'] !== 'user'): ?>
                    <div class="d-flex align-items-center gap-2">
                        <button type="button" class="btn btn-outline-primary btn-sm d-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#timeModal" style="border-radius: var(--radius-sm);">
                            <i class="bi bi-clock-history"></i> <span>Registrar Tiempo (<?php echo $ticket['time_spent']; ?> min)</span>
                        </button>
                        <form action="/tickets/delete/<?php echo $ticket['id']; ?>" method="POST" class="m-0" onsubmit="return confirm('¿Está seguro de que desea eliminar este ticket de forma permanente? Esta acción no se puede deshacer.');">
                            <button type="submit" class="btn btn-outline-danger btn-sm d-flex align-items-center gap-1" style="border-radius: var(--radius-sm);">
                                <i class="bi bi-trash"></i> <span>Eliminar Ticket</span>
                            </button>
                        </form>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Scroll de la conversación -->
            <div class="ticket-center-scroll px-4 py-4">
                
                <!-- Ticket inicial (Descripción del Solicitante) -->
                <div class="card border-0 mb-4 shadow-sm" style="background-color: var(--card-bg);">
                    <div class="card-header border-0 bg-transparent pt-3 px-4 pb-0">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center">
                                <?php if (!empty($ticket['req_avatar'])): ?>
                                    <img src="<?php echo (strpos($ticket['req_avatar'], 'http') === 0 ? '' : '/') . htmlspecialchars($ticket['req_avatar']); ?>" class="rounded-circle me-2" style="width: 36px; height: 36px; object-fit: cover;">
                                <?php else: ?>
                                    <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 36px; height: 36px; font-weight: bold;">
                                        <?php echo strtoupper(substr($ticket['req_first'], 0, 1)); ?>
                                    </div>
                                <?php endif; ?>
                                <div>
                                    <div class="fw-bold text-sm" style="color: var(--text-main);"><?php echo htmlspecialchars($ticket['req_first'] . ' ' . $ticket['req_last']); ?></div>
                                    <div class="text-xs text-muted"><?php echo date('d/m/Y H:i', strtotime($ticket['created_at'])); ?></div>
                                </div>
                            </div>
                            <span class="badge bg-light-primary text-xs" style="text-transform: capitalize; padding: 4px 10px;">Solicitante</span>
                        </div>
                    </div>
                    <div class="card-body px-4 py-3">
                        <p class="mb-0 text-break text-sm" style="white-space: pre-wrap; color: var(--text-main);"><?php echo htmlspecialchars($ticket['description']); ?></p>
                    </div>
                </div>

                <!-- Historial de Conversación / Mensajes -->
                <div class="d-flex align-items-center gap-2 mb-3">
                    <span class="text-xs fw-bold text-uppercase tracking-wider" style="color: var(--text-muted);">Mensajes de Soporte</span>
                    <hr class="flex-grow-1 opacity-10">
                </div>

                <div class="comments-stream-container">
                    <?php if (empty($comments)): ?>
                        <div class="text-center py-5 text-muted text-xs bg-white rounded border border-dashed" style="background-color: var(--card-bg); border-color: var(--border-color) !important;">
                            <i class="bi bi-chat-left-dots fs-3 mb-2 d-block text-muted"></i>
                            No hay mensajes en este ticket. Utiliza la caja inferior para responder.
                        </div>
                    <?php else: ?>
                        <?php foreach ($comments as $comment): ?>
                            <?php $isSelf = $comment['user_id'] === $_SESSION['user']['id']; ?>
                            <div class="chat-bubble-wrapper <?php echo $isSelf ? 'self' : 'other'; ?>">
                                <div class="chat-bubble border">
                                    <div class="d-flex justify-content-between align-items-center mb-2 gap-3 pb-1 border-bottom" style="border-color: rgba(0,0,0,0.03) !important;">
                                        <div class="d-flex align-items-center gap-2">
                                            <?php if (!empty($comment['avatar_path'])): ?>
                                                <img src="<?php echo (strpos($comment['avatar_path'], 'http') === 0 ? '' : '/') . htmlspecialchars($comment['avatar_path']); ?>" class="rounded-circle" style="width: 20px; height: 20px; object-fit: cover;">
                                            <?php endif; ?>
                                            <span class="chat-meta-name text-xs" style="color: var(--text-main);"><?php echo htmlspecialchars($comment['first_name'] . ' ' . $comment['last_name']); ?></span>
                                            <span class="badge rounded-pill text-uppercase" style="font-size: 8px; padding: 2px 6px; background-color: <?php echo $comment['role'] === 'admin' ? 'rgba(239, 68, 68, 0.1)' : ($comment['role'] === 'technician' ? 'rgba(79, 70, 229, 0.1)' : 'rgba(16, 185, 129, 0.1)'); ?>; color: <?php echo $comment['role'] === 'admin' ? '#ef4444' : ($comment['role'] === 'technician' ? '#4f46e5' : '#10b981'); ?>;">
                                                <?php echo $comment['role'] === 'admin' ? 'Admin' : ($comment['role'] === 'technician' ? 'Técnico' : 'Cliente'); ?>
                                            </span>
                                        </div>
                                        <span class="text-xs text-muted font-monospace" style="font-size: 9px;"><?php echo date('d/m/Y H:i', strtotime($comment['created_at'])); ?></span>
                                    </div>
                                    
                                    <!-- Texto Comentario -->
                                    <div id="commentText<?php echo $comment['id']; ?>" class="text-break text-sm text-main" style="white-space: pre-wrap; font-size: 0.875rem;"><?php echo htmlspecialchars($comment['comment_text']); ?></div>
                                    
                                    <!-- Adjuntos del comentario -->
                                    <?php if (!empty($comment['attachment_path'])): ?>
                                        <div class="mt-2 p-2 rounded bg-white bg-opacity-50 border border-light d-flex align-items-center justify-content-between" style="border-radius: var(--radius-sm);">
                                            <div class="d-flex align-items-center overflow-hidden me-2">
                                                <i class="bi bi-paperclip me-2 text-primary fs-5"></i>
                                                <span class="text-xs text-truncate" title="<?php echo htmlspecialchars($comment['attachment_filename']); ?>" style="color: var(--text-main); font-weight: 500;">
                                                    <?php echo htmlspecialchars($comment['attachment_filename']); ?>
                                                </span>
                                            </div>
                                            <a href="/<?php echo htmlspecialchars($comment['attachment_path']); ?>" download class="btn btn-xs btn-primary py-1 px-2 text-xs d-flex align-items-center gap-1" style="font-size: 10px; border-radius: var(--radius-sm);">
                                                <i class="bi bi-download"></i>Descargar
                                            </a>
                                        </div>
                                    <?php endif; ?>

                                    <!-- Barra de Acciones del Comentario -->
                                    <?php if ($isSelf || $_SESSION['user']['role'] === 'admin'): ?>
                                        <div class="d-flex justify-content-end align-items-center mt-2 border-top pt-2" style="border-color: rgba(0,0,0,0.03) !important;">
                                            <div class="d-flex gap-3">
                                                <button onclick="enableCommentEdit(<?php echo $comment['id']; ?>)" class="btn btn-link p-0 text-decoration-none text-xs text-muted" style="font-size: 11px;"><i class="bi bi-pencil me-1"></i>Editar</button>
                                                <form action="/comments/delete/<?php echo $comment['id']; ?>" method="POST" class="m-0" onsubmit="return confirm('¿Eliminar este mensaje?');">
                                                    <button type="submit" class="btn btn-link p-0 text-decoration-none text-xs text-danger" style="font-size: 11px;"><i class="bi bi-trash me-1"></i>Eliminar</button>
                                                </form>
                                            </div>
                                        </div>
                                    <?php endif; ?>

                                    <!-- Editor invisible para editar comentario -->
                                    <div id="commentEditor<?php echo $comment['id']; ?>" class="d-none mt-2">
                                        <textarea id="editInput<?php echo $comment['id']; ?>" class="form-control text-sm mb-2" rows="3"><?php echo htmlspecialchars($comment['comment_text']); ?></textarea>
                                        <div class="d-flex justify-content-end gap-1">
                                            <button onclick="cancelCommentEdit(<?php echo $comment['id']; ?>)" class="btn btn-xs btn-outline-secondary py-1 px-2 text-xs">Cancelar</button>
                                            <button onclick="submitCommentEdit(<?php echo $comment['id']; ?>)" class="btn btn-xs btn-success py-1 px-2 text-xs">Guardar</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Caja inferior para Responder (Formulario) -->
            <div class="ticket-center-footer border-top bg-white p-3" style="background-color: var(--card-bg) !important; border-color: var(--border-color) !important;">
                <form action="/tickets/comment/<?php echo $ticket['id']; ?>" method="POST" enctype="multipart/form-data" class="m-0">
                    <div class="chat-input-wrapper">
                        <textarea class="form-control" name="comment_text" rows="3" placeholder="Escriba una respuesta o comentario..." required style="font-size: 0.9rem;"></textarea>
                        <div class="chat-input-toolbar">
                            <div class="d-flex align-items-center gap-2">
                                <!-- Botón de Adjunto -->
                                <button type="button" class="btn btn-sm btn-outline-secondary border-0 file-input-btn p-2" title="Adjuntar archivo">
                                    <i class="bi bi-paperclip fs-5" style="color: var(--text-muted);"></i>
                                    <input type="file" name="attachment" onchange="document.getElementById('fileNameIndicator').innerText = this.files[0] ? this.files[0].name : '';">
                                </button>
                                <span id="fileNameIndicator" class="text-xs text-muted text-truncate" style="max-width: 200px;"></span>
                                
                                <!-- Emojis Mock Tool -->
                                <button type="button" class="btn btn-sm btn-outline-secondary border-0 p-2" title="Insertar Emojis" onclick="alert('Emojis cargados en portapapeles');">
                                    <i class="bi bi-emoji-smile fs-5" style="color: var(--text-muted);"></i>
                                </button>
                            </div>
                            
                            <div class="d-flex align-items-center gap-2">
                                <a href="<?php echo $_SESSION['user']['role'] === 'user' ? '/my-tickets' : '/tickets'; ?>" class="btn btn-outline-secondary btn-sm" style="border-radius: var(--radius-sm);">Volver</a>
                                <button type="submit" class="btn btn-primary btn-sm px-3 d-flex align-items-center gap-1" style="border-radius: var(--radius-sm);">
                                    <i class="bi bi-send-fill text-white fs-6"></i> <span>Responder</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </section>

        <!-- ================= COLUMNA DERECHA: Barra Lateral Fija de Propiedades ================= -->
        <aside class="ticket-view-right">
            
            <!-- Estado General Badge Card -->
            <div class="card border-0 shadow-sm mb-4" style="background-color: var(--card-bg);">
                <div class="card-body p-3 text-center">
                    <span class="text-xs text-muted text-uppercase fw-bold d-block mb-1">Estado del Ticket</span>
                    <span class="badge rounded-pill fs-6 px-4 py-2" style="background-color: <?php echo htmlspecialchars($ticket['status_color']); ?>18; color: <?php echo htmlspecialchars($ticket['status_color']); ?>; border: 1px solid <?php echo htmlspecialchars($ticket['status_color']); ?>30;">
                        <?php echo htmlspecialchars($ticket['status_id'] == 4 ? 'Pendiente' : ($ticket['status_id'] == 1 ? 'Abierto' : $ticket['status_name'])); ?>
                    </span>
                </div>
            </div>

            <!-- Panel de Propiedades y Metadatos -->
            <?php if ($_SESSION['user']['role'] !== 'user'): ?>
                <div class="card border-0 shadow-sm mb-4" style="background-color: var(--card-bg);">
                    <div class="card-header border-0 bg-transparent py-3 border-bottom" style="border-color: var(--border-color) !important;">
                        <h6 class="fw-bold mb-0 text-muted" style="font-size: 0.85rem;"><i class="bi bi-gear me-2"></i> Propiedades</h6>
                    </div>
                    <div class="card-body p-3">
                        <form action="/tickets/update-properties/<?php echo $ticket['id']; ?>" method="POST" class="d-flex flex-column gap-3">
                            <div>
                                <label class="form-label text-xs fw-semibold text-muted mb-1">Agente Asignado</label>
                                <select class="form-select form-select-sm text-xs" name="assigned_technician_id" style="font-size: 0.8rem;">
                                    <option value="">Sin Asignar</option>
                                    <?php foreach ($technicians as $tech): ?>
                                        <option value="<?php echo $tech['id']; ?>" <?php echo $ticket['assigned_technician_id'] == $tech['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($tech['first_name'] . ' ' . $tech['last_name']); ?> (<?php echo $tech['active_tickets_count']; ?> tks)</option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div>
                                <label class="form-label text-xs fw-semibold text-muted mb-1">Estado</label>
                                <select class="form-select form-select-sm text-xs" name="status_id" style="font-size: 0.8rem;">
                                    <?php foreach ($statuses as $st): ?>
                                        <?php if (in_array((int)$st['id'], [2, 4, 7])): ?>
                                            <option value="<?php echo $st['id']; ?>" <?php echo $ticket['status_id'] == $st['id'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($st['id'] == 4 ? 'Pendiente' : $st['name']); ?>
                                            </option>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div>
                                <label class="form-label text-xs fw-semibold text-muted mb-1">Prioridad</label>
                                <select class="form-select form-select-sm text-xs" name="priority_id" style="font-size: 0.8rem;">
                                    <?php foreach ($priorities as $pr): ?>
                                        <option value="<?php echo $pr['id']; ?>" <?php echo $ticket['priority_id'] == $pr['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($pr['name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div>
                                <label class="form-label text-xs fw-semibold text-muted mb-1">Categoría</label>
                                <select class="form-select form-select-sm text-xs" name="category_id" style="font-size: 0.8rem;">
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?php echo $cat['id']; ?>" <?php echo $ticket['category_id'] == $cat['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($cat['name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <button type="submit" class="btn btn-primary btn-sm w-100 mt-2 fw-semibold" style="border-radius: var(--radius-sm);"><i class="bi bi-save me-1"></i> Guardar Propiedades</button>
                        </form>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Información del Solicitante -->
            <div class="card border-0 shadow-sm mb-4" style="background-color: var(--card-bg);">
                <div class="card-header border-0 bg-transparent py-3 border-bottom" style="border-color: var(--border-color) !important;">
                    <h6 class="fw-bold mb-0 text-muted" style="font-size: 0.85rem;"><i class="bi bi-person-badge me-2"></i> Solicitante</h6>
                </div>
                <div class="card-body p-3 text-center">
                    <?php if (!empty($ticket['req_avatar'])): ?>
                        <img src="<?php echo (strpos($ticket['req_avatar'], 'http') === 0 ? '' : '/') . htmlspecialchars($ticket['req_avatar']); ?>" alt="Avatar" class="rounded-circle mb-2" style="width: 50px; height: 50px; object-fit: cover; border: 2px solid var(--border-color);">
                    <?php else: ?>
                        <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-2" style="width: 50px; height: 50px; font-size: 1.25rem; font-weight: bold;">
                            <?php echo strtoupper(substr($ticket['req_first'], 0, 1)); ?>
                        </div>
                    <?php endif; ?>
                    
                    <h6 class="fw-bold mb-1 text-sm" style="color: var(--text-main);"><?php echo htmlspecialchars($ticket['req_first'] . ' ' . $ticket['req_last']); ?></h6>
                    <p class="text-muted text-xs mb-3"><?php echo htmlspecialchars($ticket['department_name'] ?? 'Sin Departamento'); ?></p>
                    
                    <div class="text-start border-top pt-2 text-xs" style="border-color: var(--border-color) !important;">
                        <div class="mb-1 text-truncate" style="color: var(--text-muted);"><i class="bi bi-envelope me-1"></i><?php echo htmlspecialchars($ticket['req_email']); ?></div>
                        <?php if (!empty($ticket['req_phone'])): ?>
                            <div class="mb-1" style="color: var(--text-muted);"><i class="bi bi-telephone me-1"></i>Ext: <?php echo htmlspecialchars($ticket['req_phone']); ?></div>
                        <?php endif; ?>
                    </div>
                    <button class="btn btn-outline-secondary btn-sm w-100 mt-2 text-xs py-1" onclick="alert('Información del usuario: Email: <?php echo $ticket['req_email']; ?>');">Ver información completa</button>
                </div>
            </div>

            <!-- Historial / Timeline (Zendesk Style) -->
            <?php if ($_SESSION['user']['role'] !== 'user' && !empty($auditLogs)): ?>
                <div class="card border-0 shadow-sm" style="background-color: var(--card-bg);">
                    <div class="card-header border-0 bg-transparent py-3 border-bottom" style="border-color: var(--border-color) !important;">
                        <h6 class="fw-bold mb-0 text-muted" style="font-size: 0.85rem;"><i class="bi bi-clock-history me-2"></i> Historial (Línea de tiempo)</h6>
                    </div>
                    <div class="card-body p-3">
                        <div class="timeline-zendesk">
                            <?php foreach ($auditLogs as $log): ?>
                                <div class="timeline-item mb-3">
                                    <div class="timeline-icon">
                                        <i class="bi bi-check-circle-fill text-success" style="font-size: 11px;"></i>
                                    </div>
                                    <div class="timeline-content">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <span class="fw-bold text-xs" style="color: var(--text-main);"><?php echo htmlspecialchars($log['username'] ?? 'Sistema'); ?></span>
                                            <span class="text-muted" style="font-size: 9px; font-family: monospace;"><?php echo date('d/m H:i', strtotime($log['created_at'])); ?></span>
                                        </div>
                                        <p class="mb-0 text-muted text-xs" style="line-height: 1.4;"><?php echo htmlspecialchars($log['action']); ?>: <?php echo htmlspecialchars($log['details']); ?></p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

        </aside>
        
    </div>
</div>

<!-- Time Logger Modal (Admin / Techs) -->
<?php if ($_SESSION['user']['role'] !== 'user'): ?>
    <div class="modal fade" id="timeModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: var(--radius-md); background-color: var(--card-bg);">
                <div class="modal-header border-bottom" style="border-color: var(--border-color) !important;">
                    <h5 class="modal-title fw-bold" style="color: var(--text-main);"><i class="bi bi-clock me-2 text-primary"></i>Registrar Tiempo de Trabajo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="/tickets/time/<?php echo $ticket['id']; ?>" method="POST" class="m-0">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="time_spent" class="form-label fw-semibold text-xs">Tiempo Adicional a Sumar (en minutos) *</label>
                            <input type="number" class="form-control" id="time_spent" name="time_spent" placeholder="ej. 30" min="1" required style="font-size: 0.9rem;">
                            <div class="form-text text-xs mt-2" style="color: var(--text-muted);">
                                Ingrese el tiempo acumulado de solución de soporte para este ticket en minutos.
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-top" style="border-color: var(--border-color) !important;">
                        <button type="button" class="btn btn-outline-secondary text-xs" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary text-xs px-3">Registrar Tiempo</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Comment Edit Ajax Logic -->
<script>
function enableCommentEdit(id) {
    document.getElementById('commentText' + id).classList.add('d-none');
    document.getElementById('commentEditor' + id).classList.remove('d-none');
}

function cancelCommentEdit(id) {
    document.getElementById('commentText' + id).classList.remove('d-none');
    document.getElementById('commentEditor' + id).classList.add('d-none');
}

function submitCommentEdit(id) {
    const text = document.getElementById('editInput' + id).value;
    if (!text.trim()) {
        alert('El comentario no puede estar vacío.');
        return;
    }

    const formData = new FormData();
    formData.append('comment_text', text);

    fetch('<?php echo $basePath; ?>/comments/edit/' + id, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status) {
            document.getElementById('commentText' + id).innerText = text;
            cancelCommentEdit(id);
        } else {
            alert('Error: ' + data.error);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Ocurrió un error en el servidor al intentar editar.');
    });
}
</script>
