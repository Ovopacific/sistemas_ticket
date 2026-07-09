<div class="container text-center py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <h1 class="display-1 fw-bold text-warning">500</h1>
            <h3 class="mb-4">Error del Servidor</h3>
            <p class="text-muted mb-4"><?php echo htmlspecialchars($message ?? 'Ha ocurrido un problema interno en el servidor.'); ?></p>
            <a href="/" class="btn btn-primary px-4 py-2">Volver al Inicio</a>
        </div>
    </div>
</div>
