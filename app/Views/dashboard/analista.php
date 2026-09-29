<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Panel del Analista<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
    .module-card {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        border-radius: 12px;
        overflow: hidden;
    }
    .module-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.1) !important;
    }
    .icon-wrapper {
        width: 72px;
        height: 72px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        margin-bottom: 1rem;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="row justify-content-center">
    <div class="col-12 col-xl-10">
        
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 pb-2 border-bottom">
            <div>
                <h1 class="h3 fw-bold text-dark mb-1">
                    <i class="fa-solid fa-clipboard-check text-primary me-2"></i>Panel del Analista
                </h1>
                <p class="text-muted mb-0">
                    Bienvenido, <strong><?= esc(session('usuario_nombre')) ?></strong>. Selecciona la tarea que deseas registrar:
                </p>
            </div>
            <div class="mt-2 mt-md-0">
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2 rounded-pill">
                    <i class="fa-solid fa-user-gear me-1"></i> Rol: Analista
                </span>
            </div>
        </div>

        <?php if (!empty($statsInventario)): ?>
        <!-- WIDGET CONTROL DE INVENTARIO Y STOCK -->
        <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
            <div class="card-body p-3 p-md-4">
                <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
                    <div>
                        <h5 class="fw-bold mb-1 text-dark fs-6">
                            <i class="fa-solid fa-boxes-stacked text-primary me-2"></i>Estado del Inventario General
                        </h5>
                        <span class="text-muted small">Los equipos registrados en tus formularios se sincronizan y descuentan del stock pendiente.</span>
                    </div>
                    <div class="d-flex align-items-center gap-2 mt-2 mt-md-0">
                        <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 rounded-pill fw-semibold">
                            <i class="fa-solid fa-chart-pie me-1"></i> <?= esc((string)$statsInventario['porcentaje']) ?>% Procesado
                        </span>
                        <a href="<?= base_url('inventario') ?>" class="btn btn-outline-primary btn-sm fw-semibold">
                            <i class="fa-solid fa-boxes-stacked me-1"></i> Ver Inventario
                        </a>
                    </div>
                </div>
                
                <div class="row g-2 g-md-3 text-center mb-3">
                    <div class="col-4">
                        <div class="p-2 rounded bg-light border">
                            <small class="text-muted d-block small">Total en Sistema</small>
                            <span class="fs-5 fw-bold text-dark"><?= esc((string)$statsInventario['totalCargados']) ?></span>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="p-2 rounded bg-success-subtle border border-success-subtle">
                            <small class="text-success d-block fw-semibold small">Intervenidos</small>
                            <span class="fs-5 fw-bold text-success"><?= esc((string)$statsInventario['intervenidos']) ?></span>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="p-2 rounded bg-warning-subtle border border-warning-subtle">
                            <small class="text-warning-emphasis d-block fw-semibold small">Pendientes (Stock)</small>
                            <span class="fs-5 fw-bold text-warning-emphasis"><?= esc((string)$statsInventario['pendientes']) ?></span>
                        </div>
                    </div>
                </div>

                <div class="progress" style="height: 10px; border-radius: 5px;">
                    <div class="progress-bar bg-success progress-bar-striped progress-bar-animated" role="progressbar" style="width: <?= (float)$statsInventario['porcentaje'] ?>%;" aria-valuenow="<?= (float)$statsInventario['porcentaje'] ?>" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <div class="row g-4 justify-content-center">
            
            <!-- 1. NUEVO REGISTRO DE DIAGNÓSTICO -->
            <div class="col-md-6 col-xl-3">
                <div class="card module-card border-0 shadow-sm h-100 text-center p-3">
                    <div class="card-body d-flex flex-column align-items-center">
                        <div class="icon-wrapper bg-primary-subtle text-primary">
                            <i class="fa-solid fa-desktop fs-2"></i>
                        </div>
                        <h2 class="h5 fw-bold text-dark mb-2">Diagnóstico CPU</h2>
                        <p class="small text-muted mb-4 flex-grow-1">
                            Registro de diagnósticos, garantías, intervenciones de piezas y novedades de CPUs.
                        </p>
                        <div class="d-flex gap-2 w-100">
                            <a href="<?= base_url('equipos/formulario') ?>" class="btn btn-primary flex-fill py-2 fw-semibold">
                                <i class="fa-solid fa-plus-circle me-1"></i> Nuevo
                            </a>
                            <a href="<?= base_url('equipos/bitacora') ?>" class="btn btn-outline-secondary flex-fill py-2 fw-semibold">
                                <i class="fa-solid fa-list me-1"></i> Bitácora
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. NUEVO REGISTRO DE SOPLADO -->
            <div class="col-md-6 col-xl-3">
                <div class="card module-card border-0 shadow-sm h-100 text-center p-3">
                    <div class="card-body d-flex flex-column align-items-center">
                        <div class="icon-wrapper bg-info-subtle text-info">
                            <i class="fa-solid fa-wind fs-2"></i>
                        </div>
                        <h2 class="h5 fw-bold text-dark mb-2">Soplado de CPUs</h2>
                        <p class="small text-muted mb-4 flex-grow-1">
                            Mantenimiento preventivo, limpieza interna, cambio de pasta térmica y gel de plagas.
                        </p>
                        <div class="d-flex gap-2 w-100">
                            <a href="<?= base_url('soplado/formulario') ?>" class="btn btn-info text-white flex-fill py-2 fw-semibold">
                                <i class="fa-solid fa-plus-circle me-1"></i> Nuevo
                            </a>
                            <a href="<?= base_url('soplado/bitacora') ?>" class="btn btn-outline-secondary flex-fill py-2 fw-semibold">
                                <i class="fa-solid fa-list me-1"></i> Bitácora
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 3. NUEVO REGISTRO DE PORTÁTIL -->
            <div class="col-md-6 col-xl-3">
                <div class="card module-card border-0 shadow-sm h-100 text-center p-3">
                    <div class="card-body d-flex flex-column align-items-center">
                        <div class="icon-wrapper bg-success-subtle text-success">
                            <i class="fa-solid fa-laptop fs-2"></i>
                        </div>
                        <h2 class="h5 fw-bold text-dark mb-2">Portátiles</h2>
                        <p class="small text-muted mb-4 flex-grow-1">
                            Garantías e intervención de portátiles Lenovo, reporte de FRU y cambio de piezas.
                        </p>
                        <div class="d-flex gap-2 w-100">
                            <a href="<?= base_url('portatiles/formulario') ?>" class="btn btn-success flex-fill py-2 fw-semibold">
                                <i class="fa-solid fa-plus-circle me-1"></i> Nuevo
                            </a>
                            <a href="<?= base_url('portatiles/bitacora') ?>" class="btn btn-outline-secondary flex-fill py-2 fw-semibold">
                                <i class="fa-solid fa-list me-1"></i> Bitácora
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 4. INVENTARIO Y STOCK -->
            <div class="col-md-6 col-xl-3">
                <div class="card module-card border-0 shadow-sm h-100 text-center p-3 border-start border-primary border-3">
                    <div class="card-body d-flex flex-column align-items-center">
                        <div class="icon-wrapper bg-primary-subtle text-primary">
                            <i class="fa-solid fa-boxes-stacked fs-2"></i>
                        </div>
                        <h2 class="h5 fw-bold text-dark mb-2">Inventario</h2>
                        <p class="small text-muted mb-4 flex-grow-1">
                            Consulta general de máquinas cargadas (Cubic), traslados y seguimiento de stock.
                        </p>
                        <div class="d-flex gap-2 w-100">
                            <a href="<?= base_url('inventario') ?>" class="btn btn-primary flex-fill py-2 fw-semibold">
                                <i class="fa-solid fa-boxes-stacked me-1"></i> Abrir
                            </a>
                            <a href="<?= base_url('inventario#seccion-cargue') ?>" class="btn btn-outline-primary flex-fill py-2 fw-semibold">
                                <i class="fa-solid fa-file-arrow-up me-1"></i> Cargue
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 4. SEGURIDAD Y CAMBIO DE CONTRASEÑA DEL ANALISTA -->
            <div class="col-12 mt-4">
                <div class="card border-0 shadow-sm p-3" style="border-radius: 12px; background: #ffffff;">
                    <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="icon-wrapper bg-warning-subtle text-warning mb-0" style="width: 50px; height: 50px;">
                                <i class="fa-solid fa-key fs-4"></i>
                            </div>
                            <div>
                                <h3 class="h6 fw-bold text-dark mb-1">Seguridad de tu Cuenta</h3>
                                <p class="small text-muted mb-0">¿Deseas actualizar tu clave de acceso o asignarle una nueva contraseña?</p>
                            </div>
                        </div>
                        <button type="button" class="btn btn-outline-warning text-dark fw-bold btn-sm px-3 py-2" data-bs-toggle="modal" data-bs-target="#modalCambiarPasswordPropia">
                            <i class="fa-solid fa-lock-open me-1"></i> Cambiar mi Contraseña
                        </button>
                    </div>
                </div>
            </div>

        </div>

    </div>
</div>
<?= $this->endSection() ?>
