<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Bitácora de Portátiles<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="card shadow-sm border-0">
    <div class="card-header bg-dark text-white d-flex flex-wrap justify-content-between align-items-center gap-2 py-3">
        <div class="d-flex align-items-center gap-2">
            <h5 class="mb-0 fs-6"><i class="fa-solid fa-list me-2"></i>Bitácora de Portátiles</h5>
            <span class="badge text-white fs-6 px-3 py-1 rounded-pill" style="background-color: #8b5cf6;">
                <i class="fa-solid fa-laptop me-1"></i> <?= number_format($totalFiltrados ?? count($registros)) ?> Diagnosticadas
            </span>
        </div>
        <div>
            <a href="<?= base_url('portatiles/formulario') ?>" class="btn btn-primary btn-sm me-2"><i class="fa-solid fa-plus me-1"></i> Nuevo</a>
            <a href="<?= base_url('portatiles/evidencia') ?>" class="btn btn-warning btn-sm me-2 fw-semibold"><i class="fa-solid fa-camera me-1"></i> Subir Foto</a>
            <?php 
                $paramsExcel = array_filter([
                    'buscar'      => $busqueda,
                    'fecha_desde' => $fechaDesde ?? '',
                    'fecha_hasta' => $fechaHasta ?? ''
                ], fn($val) => $val !== '');
                $urlExcel = base_url('portatiles/exportar') . (!empty($paramsExcel) ? '?' . http_build_query($paramsExcel) : '');
            ?>
            <?php if (has_permission('exportar_excel')): ?>
            <a href="<?= $urlExcel ?>" class="btn btn-success btn-sm"><i class="fa-solid fa-file-excel me-1"></i> Excel (<?= number_format($totalFiltrados ?? count($registros)) ?>)</a>
            <?php endif; ?>
        </div>
    </div>
    <div class="card-body p-4">

        <form method="GET" action="<?= base_url('portatiles/bitacora') ?>" class="row g-2 mb-4 align-items-end">
            <div class="col-md-4">
                <label class="form-label small fw-bold text-muted mb-1"><i class="fa-solid fa-magnifying-glass me-1"></i> Búsqueda general</label>
                <input type="text" name="buscar" class="form-control" placeholder="Placa, Ticket o Analista..." value="<?= esc($busqueda) ?>">
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
                    <a href="<?= base_url('portatiles/bitacora') ?>" class="btn btn-outline-danger" title="Limpiar filtros">
                        <i class="fa-solid fa-rotate-left"></i>
                    </a>
                <?php endif; ?>
            </div>
        </form>

        <!-- INDICADOR / MÉTRICA DE PORTÁTILES DIAGNOSTICADAS -->
        <div class="card bg-light border-0 shadow-sm mb-4">
            <div class="card-body p-3 d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="p-3 rounded-circle fs-4 d-flex align-items-center justify-content-center" style="width: 52px; height: 52px; background-color: #f3e8ff; color: #7e22ce;">
                        <i class="fa-solid fa-laptop"></i>
                    </div>
                    <div>
                        <span class="text-muted small text-uppercase fw-bold d-block">Cantidad de Portátiles Diagnosticadas</span>
                        <h3 class="fw-bold mb-0 text-dark">
                            <?= number_format($totalFiltrados ?? count($registros)) ?>
                            <span class="fs-6 fw-normal text-muted">laptop(s) <?= (!empty($busqueda) || !empty($fechaDesde) || !empty($fechaHasta)) ? 'filtrada(s)' : 'registrada(s)' ?></span>
                        </h3>
                    </div>
                </div>
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <?php if (!empty($busqueda) || !empty($fechaDesde) || !empty($fechaHasta)): ?>
                        <span class="badge bg-warning-subtle text-dark border border-warning-subtle px-3 py-2 fw-semibold">
                            <i class="fa-solid fa-filter me-1 text-warning"></i> Filtro activo: <?= number_format($totalFiltrados ?? count($registros)) ?> de <?= number_format($totalGeneral ?? count($registros)) ?> registros
                        </span>
                        <a href="<?= base_url('portatiles/bitacora') ?>" class="btn btn-outline-secondary btn-sm">
                            <i class="fa-solid fa-xmark me-1"></i> Restablecer
                        </a>
                    <?php else: ?>
                        <span class="badge bg-white text-muted border px-3 py-2">
                            <i class="fa-solid fa-database me-1" style="color: #7e22ce;"></i> Total histórico en base de datos: <strong><?= number_format($totalGeneral ?? count($registros)) ?></strong>
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
                        <th>Fecha</th>
                        <th>Analista</th>
                        <th>Placa ID</th>
                        <th>Evidencia</th>
                        <th>Gestión</th>
                        <th>Ticket</th>
                        <th>Garantía</th>
                        <th>Estado Actual</th>
                        <th>Estado Final</th>
                        <th>Pieza(s)</th>
                        <th>FRU(s)</th>
                        <th>Serial Disco</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($registros)): ?>
                        <?php foreach ($registros as $row): ?>
                            <tr>
                                <td><?= $row['id'] ?></td>
                                <td><?= esc($row['created_at']) ?></td>
                                <td><?= esc($row['nombre_analista']) ?></td>
                                <td><strong><?= esc($row['placa_id_equipo']) ?></strong></td>
                                <td>
                                    <?php if (!empty($row['foto_ruta'])): ?>
                                        <span class="badge bg-success"><i class="fa-solid fa-check me-1"></i> Con Foto</span>
                                    <?php else: ?>
                                        <a href="<?= base_url('portatiles/evidencia?placa=' . urlencode($row['placa_id_equipo'])) ?>" class="btn btn-outline-warning btn-sm py-0 px-2 fw-semibold">
                                            <i class="fa-solid fa-camera me-1"></i> Subir Foto
                                        </a>
                                    <?php endif; ?>
                                </td>
                                <td><span class="badge bg-info text-dark"><?= esc($row['tipo_gestion']) ?></span></td>
                                <td><?= esc($row['numero_ticket'] ?: '-') ?></td>
                                <td>
                                    <?php 
                                        $g = $row['garantia'] ?? '';
                                        $bg = ($g === 'Aplica') ? 'success' : (($g === 'No Aplica') ? 'danger' : 'warning text-dark');
                                    ?>
                                    <span class="badge bg-<?= $bg ?>"><?= esc($g ?: 'N/A') ?></span>
                                </td>
                                <td><?= esc($row['estado_actual_equipo'] ?: '-') ?></td>
                                <td><?= esc($row['estado_final_equipo'] ?: '-') ?></td>
                                <td><?= esc($row['indique_pieza'] ?: ($row['pieza_intervenida'] ?: '-')) ?></td>
                                <td><?= esc($row['indique_fru'] ?: '-') ?></td>
                                <td><?= esc($row['serial_disco'] ?: '-') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="13" class="text-center text-muted py-3">No hay registros encontrados.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>
    <div class="card-footer bg-light py-2 px-4 d-flex flex-wrap justify-content-between align-items-center small text-muted">
        <span>Mostrando <strong><?= number_format($totalFiltrados ?? count($registros)) ?></strong> portátil(es) diagnosticada(s) <?= (!empty($busqueda) || !empty($fechaDesde) || !empty($fechaHasta)) ? '(filtradas de un total de ' . number_format($totalGeneral ?? count($registros)) . ')' : '' ?></span>
        <span class="font-monospace">Bitácora Diagnóstico Portátiles</span>
    </div>
</div>
<?= $this->endSection() ?>