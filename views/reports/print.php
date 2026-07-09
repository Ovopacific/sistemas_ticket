<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Impresión</title>
    <link rel="stylesheet" href="/assets/css/bootstrap.min.css">
    <style>
        body {
            background-color: #ffffff;
            color: #000000;
            font-family: system-ui, -apple-system, sans-serif;
            padding: 30px;
        }

        @media print {
            .no-print {
                display: none !important;
            }

            body {
                padding: 0;
            }
        }
    </style>
</head>

<body>

    <div class="d-flex justify-content-between align-items-center mb-4 no-print border-bottom pb-3">
        <div>
            <h4 class="fw-bold mb-0">Vista de Impresión</h4>
            <p class="text-muted text-sm mb-0">Use las opciones del navegador para imprimir o guardar como PDF.</p>
        </div>
        <div class="d-flex gap-2">
            <button onclick="window.print()" class="btn btn-primary"><i class="bi bi-printer"></i> Imprimir</button>
            <button onclick="window.close()" class="btn btn-outline-secondary">Cerrar Ventana</button>
        </div>
    </div>

    <div class="text-center mb-5">
        <h2 class="fw-bold mb-1">Reporte de Tickets</h2>
        <p class="text-muted mb-0">Generado el: <?php echo date('d/m/Y H:i:s'); ?></p>
    </div>

    <table class="table table-bordered align-middle table-striped text-sm">
        <thead class="table-dark">
            <tr>
                <th>Número</th>
                <th>Título</th>
                <th>Solicitante</th>
                <th>Departamento</th>
                <th>Categoría</th>
                <th>Prioridad</th>
                <th>Estado</th>
                <th>Técnico Asignado</th>
                <th>Tiempo (Min)</th>
                <th>Fecha Creación</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($tickets as $t): ?>
                <tr>
                    <td class="fw-bold"><?php echo htmlspecialchars($t['ticket_number']); ?></td>
                    <td><?php echo htmlspecialchars($t['title']); ?></td>
                    <td><?php echo htmlspecialchars($t['req_first'] . ' ' . $t['req_last']); ?></td>
                    <td><?php echo htmlspecialchars($t['department_name'] ?? 'General'); ?></td>
                    <td><?php echo htmlspecialchars($t['category_name'] ?? 'General'); ?></td>
                    <td><?php echo htmlspecialchars($t['priority_name']); ?></td>
                    <td><?php echo htmlspecialchars($t['status_name']); ?></td>
                    <td><?php echo htmlspecialchars($t['tech_first'] ? ($t['tech_first'] . ' ' . $t['tech_last']) : 'Sin Asignar'); ?>
                    </td>
                    <td><?php echo $t['time_spent']; ?></td>
                    <td class="text-muted"><?php echo date('d/m/Y H:i', strtotime($t['created_at'])); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <script>
        // Auto trigger print prompt on load if not already processed
        window.addEventListener('DOMContentLoaded', () => {
            setTimeout(() => {
                window.print();
            }, 800);
        });
    </script>

</body>

</html>