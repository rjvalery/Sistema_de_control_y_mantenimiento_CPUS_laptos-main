<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Bitácora de Equipos<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="card shadow-sm border-0">
    <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center py-3">
        <h5 class="mb-0 fs-6"><i class="fa-solid fa-list me-2"></i>Bitácora de Equipos Registrados</h5>
        <div>
            <a href="<?= base_url('equipos/formulario') ?>" class="btn btn-primary btn-sm me-2"><i class="fa-solid fa-plus me-1"></i> Nuevo</a>
            <?php 
                $paramsExcel = array_filter([
                    'buscar'      => $busqueda,
                    'fecha_desde' => $fechaDesde ?? '',
                    'fecha_hasta' => $fechaHasta ?? ''
                ], fn($val) => $val !== '');
                $urlExcel = base_url('equipos/exportar') . (!empty($paramsExcel) ? '?' . http_build_query($paramsExcel) : '');
            ?>
            <a href="<?= $urlExcel ?>" class="btn btn-success btn-sm"><i class="fa-solid fa-file-excel me-1"></i> Excel</a>
        </div>
    </div>
    <div class="card-body p-4">

        <form method="GET" action="<?= base_url('equipos/bitacora') ?>" class="row g-2 mb-4 align-items-end">
            <div class="col-md-4">
                <label class="form-label small fw-bold text-muted mb-1"><i class="fa-solid fa-magnifying-glass me-1"></i> Búsqueda general</label>
                <input type="text" name="buscar" class="form-control" placeholder="Placa, Traslado o Analista..." value="<?= esc($busqueda) ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-bold text-muted mb-1"><i class="fa-regular fa-calendar me-1"></i> Fecha Desde</label>
                <input type="date" name="fecha_desde" class="form-control" value="<?= esc($fechaDesde ?? '') ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-bold text-muted mb-1"><i class="fa-regular fa-calendar-check me-1"></i> Fecha Hasta</label>
                <input type="date" name="fecha_hasta" class="form-control" value="<?= esc($fechaHasta ?? '') ?>">
            </div>
            <div class="col-md-2 d-flex gap-1">
                <button type="submit" class="btn btn-primary flex-grow-1" title="Filtrar registros">
                    <i class="fa-solid fa-filter me-1"></i> Filtrar
                </button>
                <?php if (!empty($busqueda) || !empty($fechaDesde) || !empty($fechaHasta)): ?>
                    <a href="<?= base_url('equipos/bitacora') ?>" class="btn btn-outline-danger" title="Limpiar filtros">
                        <i class="fa-solid fa-rotate-left"></i>
                    </a>
                <?php endif; ?>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-striped table-hover table-bordered align-middle text-nowrap">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Fecha/Hora</th>
                        <th>Analista</th>
                        <th>Traslado</th>
                        <th>Placa ID</th>
                        <th>Gestión</th>
                        <th>Energiza</th>
                        <th>Video</th>
                        <th>Estado</th>
                        <th>Intervención</th>
                        <th>Origen</th>
                        <th>Novedad</th>
                        <th>Motivo Baja</th>
                        <th>Serial Disco</th>
                        <th>Destino</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($registros)): ?>
                        <?php foreach ($registros as $row): ?>
                            <tr>
                                <td><?= $row['id'] ?></td>
                                <td><?= $row['fecha_creacion'] ?></td>
                                <td><?= esc($row['nombre_analista']) ?></td>
                                <td><?= esc($row['num_traslado']) ?></td>
                                <td><strong><?= esc($row['placa_id']) ?></strong></td>
                                <td><span class="badge bg-primary"><?= esc($row['tipo_gestion']) ?></span></td>
                                <td><?= esc($row['energiza'] ?? '-') ?></td>
                                <td><?= esc($row['da_video'] ?? '-') ?></td>
                                <td><?= esc($row['estado_actual'] ?? '-') ?></td>
                                <td><?= esc($row['que_va_intervenir'] ?? '-') ?></td>
                                <td><?= esc($row['origen_pieza'] ?? '-') ?></td>
                                <td><?= esc($row['descripcion_novedad'] ?? '-') ?></td>
                                <td><?= esc($row['motivo_baja'] ?? '-') ?></td>
                                <td><?= esc($row['serial_disco'] ?? '-') ?></td>
                                <td><?= esc($row['ubicacion_destino'] ?? '-') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="15" class="text-center text-muted py-3">No hay registros aún.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>
</div>
<?= $this->endSection() ?>