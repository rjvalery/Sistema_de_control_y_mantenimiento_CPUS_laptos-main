<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Bitácora de Soplado<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="card shadow-sm border-0">
    <div class="card-header bg-dark text-white d-flex flex-wrap justify-content-between align-items-center gap-2 py-3">
        <div class="d-flex align-items-center gap-2">
            <h5 class="mb-0 fs-6"><i class="fa-solid fa-list me-2"></i>Bitácora de Soplado</h5>
            <span class="badge bg-info text-white fs-6 px-3 py-1 rounded-pill">
                <i class="fa-solid fa-wind me-1"></i> <?= number_format($totalFiltrados ?? count($registros)) ?> Soplados
            </span>
        </div>
        <div>
            <a href="<?= base_url('soplado/formulario') ?>" class="btn btn-primary btn-sm me-2"><i class="fa-solid fa-plus me-1"></i> Nuevo</a>
            <?php 
                $paramsExcel = array_filter([
                    'buscar'      => $busqueda,
                    'fecha_desde' => $fechaDesde ?? '',
                    'fecha_hasta' => $fechaHasta ?? ''
                ], fn($val) => $val !== '');
                $urlExcel = base_url('soplado/exportar') . (!empty($paramsExcel) ? '?' . http_build_query($paramsExcel) : '');
            ?>
            <?php if (has_permission('exportar_excel')): ?>
            <a href="<?= $urlExcel ?>" class="btn btn-success btn-sm"><i class="fa-solid fa-file-excel me-1"></i> Excel (<?= number_format($totalFiltrados ?? count($registros)) ?>)</a>
            <?php endif; ?>
        </div>
    </div>
    <div class="card-body p-4">
        
        <form method="GET" action="<?= base_url('soplado/bitacora') ?>" class="row g-2 mb-4 align-items-end">
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
                    <a href="<?= base_url('soplado/bitacora') ?>" class="btn btn-outline-danger" title="Limpiar filtros">
                        <i class="fa-solid fa-rotate-left"></i>
                    </a>
                <?php endif; ?>
            </div>
        </form>

        <!-- INDICADOR / MÉTRICA DE EQUIPOS SOPLADOS -->
        <div class="card bg-light border-0 shadow-sm mb-4">
            <div class="card-body p-3 d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="p-3 rounded-circle bg-info-subtle text-info fs-4 d-flex align-items-center justify-content-center" style="width: 52px; height: 52px;">
                        <i class="fa-solid fa-wind"></i>
                    </div>
                    <div>
                        <span class="text-muted small text-uppercase fw-bold d-block">Cantidad de Equipos Atendidos / Soplados</span>
                        <h3 class="fw-bold mb-0 text-dark">
                            <?= number_format($totalFiltrados ?? count($registros)) ?>
                            <span class="fs-6 fw-normal text-muted">equipo(s) <?= (!empty($busqueda) || !empty($fechaDesde) || !empty($fechaHasta)) ? 'filtrado(s)' : 'registrado(s)' ?></span>
                        </h3>
                    </div>
                </div>
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <?php if (!empty($busqueda) || !empty($fechaDesde) || !empty($fechaHasta)): ?>
                        <span class="badge bg-warning-subtle text-dark border border-warning-subtle px-3 py-2 fw-semibold">
                            <i class="fa-solid fa-filter me-1 text-warning"></i> Filtro activo: <?= number_format($totalFiltrados ?? count($registros)) ?> de <?= number_format($totalGeneral ?? count($registros)) ?> registros
                        </span>
                        <a href="<?= base_url('soplado/bitacora') ?>" class="btn btn-outline-secondary btn-sm">
                            <i class="fa-solid fa-xmark me-1"></i> Restablecer
                        </a>
                    <?php else: ?>
                        <span class="badge bg-white text-muted border px-3 py-2">
                            <i class="fa-solid fa-database me-1 text-info"></i> Total histórico en base de datos: <strong><?= number_format($totalGeneral ?? count($registros)) ?></strong>
                        </span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-striped table-hover table-bordered align-middle text-nowrap">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Fecha/Hora</th>
                        <th>Analista</th>
                        <th>Traslado</th>
                        <th>Placa ID</th>
                        <th>Energiza</th>
                        <th>Video</th>
                        <th>Disco</th>
                        <th>BIOS</th>
                        <th>Pasta</th>
                        <th>Gel</th>
                        <th>Contenido</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($registros)): ?>
                        <?php foreach ($registros as $r): ?>
                            <tr>
                                <td><?= $r['id'] ?></td>
                                <td><?= esc($r['created_at'] ?? ($r['fecha_creacion'] ?? '-')) ?></td>
                                <td><?= esc($r['nombre_analista']) ?></td>
                                <td><?= esc($r['num_traslado']) ?></td>
                                <td><strong><?= esc($r['placa_id']) ?></strong></td>
                                <td><span class="badge bg-<?= $r['energiza'] === 'Si' ? 'success' : 'danger' ?>"><?= esc($r['energiza']) ?></span></td>
                                <td><span class="badge bg-<?= $r['da_video'] === 'Si' ? 'success' : 'danger' ?>"><?= esc($r['da_video']) ?></span></td>
                                <td><span class="badge bg-<?= $r['detecta_disco'] === 'Si' ? 'success' : 'danger' ?>"><?= esc($r['detecta_disco']) ?></span></td>
                                <td><span class="badge bg-<?= $r['ingreso_bios'] === 'Si' ? 'success' : 'danger' ?>"><?= esc($r['ingreso_bios']) ?></span></td>
                                <td><span class="badge bg-<?= $r['pasta_termica'] === 'Si' ? 'success' : 'danger' ?>"><?= esc($r['pasta_termica']) ?></span></td>
                                <td><span class="badge bg-<?= $r['gel_cucarachas'] === 'Si' ? 'success' : 'danger' ?>"><?= esc($r['gel_cucarachas']) ?></span></td>
                                <td><?= esc($r['maquina_contenia']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="12" class="text-center text-muted py-3">No hay registros encontrados.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>
    <div class="card-footer bg-light py-2 px-4 d-flex flex-wrap justify-content-between align-items-center small text-muted">
        <span>Mostrando <strong><?= number_format($totalFiltrados ?? count($registros)) ?></strong> equipo(s) soplado(s) <?= (!empty($busqueda) || !empty($fechaDesde) || !empty($fechaHasta)) ? '(filtrados de un total de ' . number_format($totalGeneral ?? count($registros)) . ')' : '' ?></span>
        <span class="font-monospace">Bitácora Mantenimiento Soplado</span>
    </div>
</div>
<?= $this->endSection() ?>