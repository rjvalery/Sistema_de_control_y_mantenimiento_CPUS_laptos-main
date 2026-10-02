<?php declare(strict_types=1); ?>
<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Dashboard Operativo y Control de Equipos<?= $this->endSection() ?>

<?= $this->section('container_class') ?>container-fluid px-3 px-xl-4<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
    :root {
        --pbi-canvas-bg: #f4f6f9;
        --pbi-card-bg: #ffffff;
        --pbi-border: #e2e8f0;
        --pbi-text-main: #1e293b;
        --pbi-text-muted: #64748b;
        --pbi-blue: #0078d4;
        --pbi-cyan: #00bcf2;
        --pbi-teal: #10b981;
        --pbi-amber: #f59e0b;
        --pbi-purple: #7c3aed;
    }

    body {
        background-color: var(--pbi-canvas-bg);
        font-family: 'Segoe UI', -apple-system, BlinkMacSystemFont, Roboto, Helvetica, Arial, sans-serif;
    }

    /* 1. Cabecera Ejecutiva Canvas */
    .pbi-canvas-header {
        background-color: var(--pbi-card-bg);
        border: 1px solid var(--pbi-border);
        border-radius: 8px;
        padding: 0.85rem 1.25rem;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03);
    }
    .pbi-canvas-title {
        font-size: 0.95rem;
        font-weight: 700;
        letter-spacing: 0.5px;
        color: var(--pbi-text-main);
        text-transform: uppercase;
        margin: 0;
    }
    .pbi-select-filter {
        background-color: #f8fafc;
        border: 1px solid #cbd5e1;
        font-size: 0.825rem;
        font-weight: 600;
        color: #334155;
        border-radius: 6px;
        min-width: 170px;
        cursor: pointer;
    }
    .pbi-select-filter:focus {
        border-color: var(--pbi-blue);
        box-shadow: 0 0 0 2px rgba(0, 120, 212, 0.15);
    }

    /* 2. Tarjetas KPI Estilo Power BI Card */
    .pbi-kpi-card {
        background-color: var(--pbi-card-bg);
        border: 1px solid var(--pbi-border);
        border-radius: 8px;
        padding: 1rem 1.25rem;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
        transition: transform 0.15s ease, box-shadow 0.15s ease;
        position: relative;
        overflow: hidden;
    }
    .pbi-kpi-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    }
    .pbi-kpi-val {
        font-size: 1.85rem;
        font-weight: 700;
        line-height: 1.1;
        margin: 0;
        color: var(--pbi-text-main);
        letter-spacing: -0.5px;
    }
    .pbi-kpi-label {
        font-size: 0.72rem;
        font-weight: 600;
        letter-spacing: 0.5px;
        text-transform: uppercase;
        color: var(--pbi-text-muted);
        margin-top: 0.35rem;
    }
    .pbi-kpi-icon {
        width: 44px;
        height: 44px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        flex-shrink: 0;
    }
    .pbi-icon-blue { background-color: #eff6ff; color: #0078d4; }
    .pbi-icon-teal { background-color: #ecfdf5; color: #10b981; }
    .pbi-icon-amber { background-color: #fffbeb; color: #f59e0b; }
    .pbi-icon-purple { background-color: #f5f3ff; color: #7c3aed; }

    /* 3. Widgets Modulares Analíticos */
    .pbi-widget {
        background-color: var(--pbi-card-bg);
        border: 1px solid var(--pbi-border);
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
    }
    .pbi-widget-header {
        padding: 0.85rem 1.25rem;
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .pbi-widget-title {
        font-size: 0.78rem;
        font-weight: 700;
        letter-spacing: 0.5px;
        text-transform: uppercase;
        color: #475569;
        margin: 0;
    }
    .pbi-chart-container {
        position: relative;
        min-height: 250px;
        max-height: 270px;
    }
    .pbi-donut-center {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        text-align: center;
        pointer-events: none;
    }
    .pbi-donut-val {
        font-size: 1.65rem;
        font-weight: 700;
        line-height: 1;
        color: var(--pbi-text-main);
    }
    .pbi-donut-lbl {
        font-size: 0.72rem;
        font-weight: 600;
        text-transform: uppercase;
        color: var(--pbi-text-muted);
        letter-spacing: 0.4px;
    }

    /* 4. Tabla Matriz Analítica */
    .pbi-table {
        font-size: 0.825rem;
        margin-bottom: 0;
    }
    .pbi-table thead th {
        background-color: #f8fafc;
        color: #475569;
        font-size: 0.74rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-bottom: 1px solid var(--pbi-border);
        padding: 0.65rem 0.85rem;
        white-space: nowrap;
    }
    .pbi-table tbody td {
        padding: 0.6rem 0.85rem;
        border-bottom: 1px solid #f1f5f9;
        color: #334155;
    }
    .pbi-table tbody tr:hover td {
        background-color: #f8fafc;
    }
    .pbi-badge {
        font-size: 0.72rem;
        font-weight: 600;
        padding: 0.25rem 0.55rem;
        border-radius: 4px;
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- 1. CABECERA EJECUTIVA (ESTILO CANVAS POWER BI) -->
<div class="pbi-canvas-header mb-3 d-flex flex-wrap justify-content-between align-items-center gap-3">
    <div class="d-flex align-items-center gap-2">
        <i class="fa-solid fa-chart-column text-primary fs-5"></i>
        <div>
            <h1 class="pbi-canvas-title">DASHBOARD OPERATIVO Y CONTROL DE EQUIPOS</h1>
            <div class="text-muted d-flex align-items-center gap-2 flex-wrap" style="font-size: 0.74rem;">
                <span id="labelPeriodoActivo"><i class="fa-regular fa-calendar-check text-primary me-1"></i><?= esc($labelPeriodo) ?></span>
                <span id="labelAnalistaActivo" class="badge <?= !empty($nombreAnalista) ? 'bg-primary-subtle text-primary border border-primary-subtle' : 'd-none' ?>">
                    <i class="fa-solid fa-user-check me-1"></i><?= esc((string)($nombreAnalista ?? '')) ?>
                </span>
            </div>
        </div>
    </div>
    <div class="d-flex flex-wrap align-items-center gap-2">
        <!-- Slicer 1: Periodo -->
        <div class="d-flex align-items-center gap-1">
            <label for="filtroPeriodoSelect" class="small fw-semibold text-secondary mb-0 d-none d-md-inline-flex align-items-center gap-1">
                <i class="fa-solid fa-calendar-days text-muted"></i>
            </label>
            <select id="filtroPeriodoSelect" class="form-select form-select-sm pbi-select-filter" onchange="aplicarFiltros(this.value, null, 1)">
                <option value="dia" <?= ($periodo === 'dia') ? 'selected' : '' ?>>Hoy</option>
                <option value="semana" <?= ($periodo === 'semana') ? 'selected' : '' ?>>Esta Semana</option>
                <option value="mes" <?= ($periodo === 'mes') ? 'selected' : '' ?>>Este Mes</option>
                <option value="anio" <?= ($periodo === 'anio') ? 'selected' : '' ?>>Este Año</option>
                <option value="todos" <?= ($periodo === 'todos') ? 'selected' : '' ?>>Histórico Completo</option>
            </select>
        </div>

        <!-- Slicer 2: Analista Técnico -->
        <div class="d-flex align-items-center gap-1">
            <label for="filtroAnalistaSelect" class="small fw-semibold text-secondary mb-0 d-none d-md-inline-flex align-items-center gap-1">
                <i class="fa-solid fa-user-gear text-muted"></i>
            </label>
            <select id="filtroAnalistaSelect" class="form-select form-select-sm pbi-select-filter" onchange="aplicarFiltros(null, this.value, 1)" style="min-width: 180px;">
                <option value="todos">Todos los Analistas</option>
                <?php if (!empty($analistas)): ?>
                    <?php foreach ($analistas as $a): ?>
                        <option value="<?= esc((string)$a['id']) ?>" <?= ($analista_id === (int)$a['id']) ? 'selected' : '' ?>>
                            <?= esc((string)$a['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>
    </div>
</div>

<!-- 2. FILA SUPERIOR DE KPIS (TARJETAS ESTILO POWER BI CARD) -->
<div class="row g-3 mb-3">
    <!-- KPI 1: Total Máquinas Ingresadas -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="pbi-kpi-card h-100">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <h2 class="pbi-kpi-val" id="kpiTotalCargados"><?= number_format((int)$statsInventario['totalCargados']) ?></h2>
                    <div class="pbi-kpi-label">Total Máquinas Ingresadas</div>
                </div>
                <div class="pbi-kpi-icon pbi-icon-blue">
                    <i class="fa-solid fa-boxes-stacked"></i>
                </div>
            </div>
            <div class="mt-2 pt-2 border-top d-flex justify-content-between align-items-center" style="font-size: 0.72rem;">
                <span class="text-muted">Cargadas en inventario</span>
                <span class="text-primary fw-semibold" id="kpiBadgeTraslados"><?= esc((string)$totalTraslados) ?> Traslados</span>
            </div>
        </div>
    </div>

    <!-- KPI 2: Máquinas Intervenidas -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="pbi-kpi-card h-100">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <h2 class="pbi-kpi-val text-success" id="kpiIntervenidos"><?= number_format((int)$statsInventario['intervenidos']) ?></h2>
                    <div class="pbi-kpi-label">Máquinas Intervenidas</div>
                </div>
                <div class="pbi-kpi-icon pbi-icon-teal">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
            </div>
            <div class="mt-2 pt-2 border-top d-flex justify-content-between align-items-center" style="font-size: 0.72rem;">
                <span class="text-muted">Atendidas en taller</span>
                <span class="text-success fw-semibold" id="kpiPorcentajeIntervenidas"><?= esc((string)$statsInventario['porcentaje']) ?>% Atendido</span>
            </div>
        </div>
    </div>

    <!-- KPI 3: Pendientes por Intervenir -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="pbi-kpi-card h-100">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <h2 class="pbi-kpi-val text-warning-emphasis" id="kpiPendientes"><?= number_format((int)$statsInventario['pendientes']) ?></h2>
                    <div class="pbi-kpi-label">Pendientes por Intervenir</div>
                </div>
                <div class="pbi-kpi-icon pbi-icon-amber">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                </div>
            </div>
            <div class="mt-2 pt-2 border-top d-flex justify-content-between align-items-center" style="font-size: 0.72rem;">
                <span class="text-muted">En cola de taller</span>
                <span class="text-warning-emphasis fw-semibold" id="kpiBadgePendientes">Por Atender</span>
            </div>
        </div>
    </div>

    <!-- KPI 4: Total Intervenciones Taller -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="pbi-kpi-card h-100">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <h2 class="pbi-kpi-val" style="color: var(--pbi-purple);" id="kpiTotalIntervenciones"><?= number_format((int)$totalIntervenciones) ?></h2>
                    <div class="pbi-kpi-label">Total Intervenciones Taller</div>
                </div>
                <div class="pbi-kpi-icon pbi-icon-purple">
                    <i class="fa-solid fa-screwdriver-wrench"></i>
                </div>
            </div>
            <div class="mt-2 pt-2 border-top d-flex justify-content-between align-items-center" style="font-size: 0.72rem;">
                <span class="text-muted">Total registros en bitácoras</span>
                <span class="text-muted fw-semibold" id="kpiBadgeAnalistas"><?= number_format((int)$totalAnalistas) ?> Analistas</span>
            </div>
        </div>
    </div>
</div>

<!-- 3. DISTRIBUCIÓN GRÁFICA Y ANALÍTICA CENTRAL -->
<div class="row g-3 mb-3">
    <!-- GRÁFICO 1: DISTRIBUCIÓN DE TRABAJO POR LÍNEA TÉCNICA -->
    <div class="col-12 col-lg-7">
        <div class="pbi-widget h-100 d-flex flex-column justify-content-between">
            <div class="pbi-widget-header">
                <div>
                    <h2 class="pbi-widget-title">Distribución de Trabajo por Línea Técnica</h2>
                    <div class="text-muted" style="font-size: 0.72rem;">Diagnóstico CPU, Mantenimiento Soplado y Diagnóstico Portátiles</div>
                </div>
                <span class="badge bg-light text-secondary border fw-semibold" style="font-size: 0.72rem;" id="badgeTotalIntervencionesGrafico">
                    <?= number_format((int)$totalIntervenciones) ?> Registros
                </span>
            </div>
            
            <div class="p-3 flex-grow-1 d-flex flex-column justify-content-between">
                <div class="pbi-chart-container" style="min-height: 220px; max-height: 240px;">
                    <canvas id="chartLineasTecnicas"></canvas>
                </div>
                
                <!-- Micro-métricas de soporte estilo Power BI -->
                <div class="row g-2 pt-2 mt-2 border-top text-center" style="font-size: 0.75rem;">
                    <div class="col-4">
                        <span class="text-muted d-block" style="font-size: 0.7rem;">DIAGNÓSTICO CPU</span>
                        <strong class="text-primary" id="metricEqVal"><?= number_format((int)$totalEquipos) ?></strong>
                        <span class="text-muted ms-1" style="font-size: 0.68rem;" id="metricEqPct">(<?= $porcEq ?>%)</span>
                    </div>
                    <div class="col-4 border-start border-end">
                        <span class="text-muted d-block" style="font-size: 0.7rem;">SOPLADO</span>
                        <strong style="color: var(--pbi-cyan);" id="metricSpVal"><?= number_format((int)$totalSoplado) ?></strong>
                        <span class="text-muted ms-1" style="font-size: 0.68rem;" id="metricSpPct">(<?= $porcSp ?>%)</span>
                    </div>
                    <div class="col-4">
                        <span class="text-muted d-block" style="font-size: 0.7rem;">DIAGNÓSTICO PORTÁTILES</span>
                        <strong style="color: var(--pbi-purple);" id="metricPtVal"><?= number_format((int)$totalPortatiles) ?></strong>
                        <span class="text-muted ms-1" style="font-size: 0.68rem;" id="metricPtPct">(<?= $porcPt ?>%)</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- GRÁFICO 2: TASA DE CUMPLIMIENTO / EFECTIVIDAD DE INTERVENCIÓN -->
    <div class="col-12 col-lg-5">
        <div class="pbi-widget h-100 d-flex flex-column justify-content-between">
            <div class="pbi-widget-header">
                <div>
                    <h2 class="pbi-widget-title">Tasa de Cumplimiento / Efectividad</h2>
                    <div class="text-muted" style="font-size: 0.72rem;">Porcentaje atendido vs máquinas restantes en taller</div>
                </div>
                <span class="badge bg-success-subtle text-success border border-success-subtle fw-semibold" style="font-size: 0.72rem;" id="badgeTasaCumplimiento">
                    <?= esc((string)$statsInventario['porcentaje']) ?>% Cumplimiento
                </span>
            </div>

            <div class="p-3 flex-grow-1 d-flex flex-column justify-content-between">
                <!-- Donut con etiqueta central estilo Power BI -->
                <div class="pbi-chart-container d-flex align-items-center justify-content-center" style="min-height: 200px; max-height: 220px;">
                    <canvas id="chartCumplimiento" style="max-height: 200px;"></canvas>
                    <div class="pbi-donut-center">
                        <div class="pbi-donut-val text-success" id="donutCenterPct"><?= esc((string)$statsInventario['porcentaje']) ?>%</div>
                        <div class="pbi-donut-lbl">Atendido</div>
                    </div>
                </div>

                <!-- Resumen analítico inferior -->
                <div class="pt-2 mt-2 border-top">
                    <div class="progress mb-2" style="height: 6px; border-radius: 4px; background-color: #f1f5f9;">
                        <div class="progress-bar bg-success" id="progresoCumplimientoBar" style="width: <?= (float)$statsInventario['porcentaje'] ?>%;"></div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center" style="font-size: 0.75rem;">
                        <span class="text-muted">
                            <i class="fa-solid fa-circle text-success me-1" style="font-size: 0.6rem;"></i>
                            Intervenidas: <strong class="text-dark" id="txtIntervenidasMini"><?= number_format((int)$statsInventario['intervenidos']) ?></strong>
                        </span>
                        <span class="text-muted">
                            <i class="fa-solid fa-circle text-warning me-1" style="font-size: 0.6rem;"></i>
                            Pendientes: <strong class="text-dark" id="txtPendientesMini"><?= number_format((int)$statsInventario['pendientes']) ?></strong>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 4. TABLA ANALÍTICA INFERIOR (ESTILO MATRIZ DE POWER BI) -->
<div class="pbi-widget mb-4">
    <div class="pbi-widget-header">
        <div>
            <h2 class="pbi-widget-title">Matriz Analítica de Máquinas Intervenidas</h2>
            <div class="text-muted" style="font-size: 0.72rem;">Registro cronológico y auditoría técnica de equipos atendidos</div>
        </div>
        <div class="text-muted" style="font-size: 0.75rem;">
            Total: <strong id="tablaCountBadge" class="text-dark"><?= (int)($total_registros ?? count($maquinasIntervenidas)) ?></strong> máquinas intervenidas
        </div>
    </div>

    <div class="table-responsive">
        <table class="table pbi-table align-middle">
            <thead>
                <tr>
                    <th style="width: 70px;">ID</th>
                    <th style="width: 280px;">Equipo / Serial</th>
                    <th style="width: 220px;">Módulo</th>
                    <th style="width: 220px;">Analista</th>
                    <th style="width: 170px;">Fecha Intervención</th>
                </tr>
            </thead>
            <tbody id="tbodyMatrizOperativa">
                <?php if (!empty($maquinasIntervenidas)): ?>
                    <?php foreach ($maquinasIntervenidas as $m): ?>
                        <tr>
                            <td class="text-muted font-monospace"><?= esc((string)$m['id']) ?></td>
                            <td>
                                <div class="fw-semibold text-dark font-monospace" style="font-size: 0.84rem;">
                                    <?= esc((string)($m['identificador_1'] ?: ($m['placa_id'] ?? '—'))) ?>
                                </div>
                                <div class="text-muted font-monospace" style="font-size: 0.74rem;">
                                    <i class="fa-solid fa-barcode text-secondary opacity-75 me-1"></i><?= esc((string)($m['identificador_2'] ?: ($m['serial'] ?? '—'))) ?>
                                </div>
                            </td>
                            <td>
                                <?php 
                                    $mod = strtolower((string)($m['modulo_intervencion'] ?? ''));
                                    if (str_contains($mod, 'portat') || str_contains($mod, 'laptop')) {
                                        $badgeClass = 'bg-purple-subtle text-purple border';
                                        $icono = 'fa-laptop';
                                        $labelMod = 'Diagnóstico Portátiles';
                                    } elseif (str_contains($mod, 'sopla') || str_contains($mod, 'limpie')) {
                                        $badgeClass = 'bg-info-subtle text-info border border-info-subtle';
                                        $icono = 'fa-wind';
                                        $labelMod = 'Soplado';
                                    } else {
                                        $badgeClass = 'bg-primary-subtle text-primary border border-primary-subtle';
                                        $icono = 'fa-desktop';
                                        $labelMod = 'Diagnóstico CPU';
                                    }
                                ?>
                                <span class="pbi-badge <?= $badgeClass ?>" style="<?= str_contains($badgeClass, 'text-purple') ? 'background-color: #f5f3ff; color: #7c3aed; border-color: #ddd6fe !important;' : '' ?>">
                                    <i class="fa-solid <?= $icono ?>"></i>
                                    <span><?= esc($labelMod) ?></span>
                                </span>
                            </td>
                            <td>
                                <?php if (!empty($m['analista_intervencion'])): ?>
                                    <span class="text-dark">
                                        <i class="fa-regular fa-user text-muted me-1"></i><?= esc((string)$m['analista_intervencion']) ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-muted">
                                <?php if (!empty($m['fecha_intervencion'])): ?>
                                    <i class="fa-regular fa-clock me-1 text-secondary opacity-75"></i><?= esc(date('d/m/Y H:i', strtotime((string)$m['fecha_intervencion']))) ?>
                                <?php else: ?>
                                    —
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">
                            <i class="fa-solid fa-filter-circle-xmark fs-4 text-muted mb-2 d-block"></i>
                            <span class="fw-semibold">No se encontraron máquinas intervenidas para los filtros seleccionados.</span>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Paginación compacta estilo Power BI -->
    <div class="pbi-widget-footer p-2 px-3 bg-light border-top d-flex flex-wrap justify-content-between align-items-center gap-2" style="border-radius: 0 0 8px 8px;">
        <div class="text-muted small" style="font-size: 0.75rem;">
            <span id="pagiInfoText">
                Mostrando página <strong id="pagiCurrentPage"><?= (int)($pagina_actual ?? 1) ?></strong> de <strong id="pagiTotalPages"><?= (int)($total_paginas ?? 1) ?></strong> (<span id="pagiTotalReg"><?= (int)($total_registros ?? count($maquinasIntervenidas)) ?></span> registros)
            </span>
        </div>
        <div class="btn-group btn-group-sm" role="group">
            <button type="button" class="btn btn-outline-secondary btn-sm px-3" id="btnPagiPrev" onclick="cambiarPagina(paginaActual - 1)" <?= (($pagina_actual ?? 1) <= 1) ? 'disabled' : '' ?>>
                <i class="fa-solid fa-chevron-left me-1"></i> Anterior
            </button>
            <button type="button" class="btn btn-outline-secondary btn-sm px-3" id="btnPagiNext" onclick="cambiarPagina(paginaActual + 1)" <?= (($pagina_actual ?? 1) >= ($total_paginas ?? 1)) ? 'disabled' : '' ?>>
                Siguiente <i class="fa-solid fa-chevron-right ms-1"></i>
            </button>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<!-- Chart.js 4.4.1 -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
let chartLineas = null;
let chartCumplimientoInst = null;
let periodoActual = '<?= esc($periodo) ?>';
let analistaActual = '<?= esc((string)($analista_id ?? 'todos')) ?>';
let paginaActual = <?= (int)($pagina_actual ?? 1) ?>;
let totalPaginas = <?= (int)($total_paginas ?? 1) ?>;
let totalRegistros = <?= (int)($total_registros ?? count($maquinasIntervenidas)) ?>;

// Configuración inicial de datos analíticos
const dataAnalytics = {
    lineas: {
        labels: ['Diagnóstico CPU', 'Mantenimiento Soplado', 'Diagnóstico Portátiles'],
        valores: [<?= (int)$totalEquipos ?>, <?= (int)$totalSoplado ?>, <?= (int)$totalPortatiles ?>]
    },
    cumplimiento: {
        intervenidos: <?= (int)$statsInventario['intervenidos'] ?>,
        pendientes: <?= (int)$statsInventario['pendientes'] ?>,
        porcentaje: <?= (float)$statsInventario['porcentaje'] ?>
    }
};

/**
 * Renderiza el gráfico de barras horizontales de distribución técnica
 */
function renderizarGraficoLineas() {
    const ctx = document.getElementById('chartLineasTecnicas');
    if (!ctx) return;

    if (chartLineas) {
        chartLineas.destroy();
    }

    chartLineas = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: dataAnalytics.lineas.labels,
            datasets: [{
                label: 'Intervenciones',
                data: dataAnalytics.lineas.valores,
                backgroundColor: ['#0078d4', '#00bcf2', '#7c3aed'],
                borderRadius: 4,
                borderSkipped: false,
                barThickness: 22
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#1e293b',
                    titleFont: { size: 12, weight: 'bold' },
                    bodyFont: { size: 11 },
                    padding: 10,
                    cornerRadius: 6,
                    callbacks: {
                        label: function(ctx) {
                            const val = ctx.raw || 0;
                            const total = dataAnalytics.lineas.valores.reduce((a, b) => a + b, 0);
                            const pct = total > 0 ? Math.round((val / total) * 100) : 0;
                            return ` Total: ${val.toLocaleString()} (${pct}%)`;
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: {
                        color: '#f1f5f9',
                        drawBorder: false
                    },
                    ticks: {
                        font: { size: 11 },
                        color: '#64748b'
                    }
                },
                y: {
                    grid: { display: false },
                    ticks: {
                        font: { size: 11, weight: '600' },
                        color: '#334155'
                    }
                }
            },
            animation: { duration: 500 }
        }
    });
}

/**
 * Renderiza el Donut minimalista de cumplimiento estilo Power BI
 */
function renderizarGraficoCumplimiento() {
    const ctx = document.getElementById('chartCumplimiento');
    if (!ctx) return;

    if (chartCumplimientoInst) {
        chartCumplimientoInst.destroy();
    }

    const intervenidos = dataAnalytics.cumplimiento.intervenidos;
    const pendientes = dataAnalytics.cumplimiento.pendientes;
    const total = intervenidos + pendientes;

    const datasetValues = total > 0 ? [intervenidos, pendientes] : [0, 1];
    const datasetColors = total > 0 ? ['#10b981', '#f1f5f9'] : ['#e2e8f0', '#f8fafc'];

    chartCumplimientoInst = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Intervenidas', 'Pendientes'],
            datasets: [{
                data: datasetValues,
                backgroundColor: datasetColors,
                borderWidth: 2,
                borderColor: '#ffffff',
                hoverOffset: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '78%',
            plugins: {
                legend: { display: false },
                tooltip: {
                    enabled: total > 0,
                    backgroundColor: '#1e293b',
                    titleFont: { size: 12, weight: 'bold' },
                    bodyFont: { size: 11 },
                    padding: 10,
                    cornerRadius: 6,
                    callbacks: {
                        label: function(ctx) {
                            const val = ctx.raw || 0;
                            const pct = total > 0 ? Math.round((val / total) * 100) : 0;
                            return ` ${ctx.label}: ${val.toLocaleString()} (${pct}%)`;
                        }
                    }
                }
            },
            animation: {
                animateScale: true,
                duration: 500
            }
        }
    });
}

/**
 * Filtro Asíncrono de Periodo, Analista y Paginación
 */
function aplicarFiltros(nuevoPeriodo = null, nuevoAnalista = null, nuevaPagina = null) {
    if (nuevoPeriodo !== null) {
        periodoActual = nuevoPeriodo;
        paginaActual = 1;
    }
    if (nuevoAnalista !== null) {
        analistaActual = nuevoAnalista;
        paginaActual = 1;
    }
    if (nuevaPagina !== null) {
        paginaActual = nuevaPagina;
    }

    const labelPeriodo = document.getElementById('labelPeriodoActivo');
    if (labelPeriodo) {
        labelPeriodo.innerHTML = `<i class="fa-solid fa-spinner fa-spin text-primary me-1"></i> Actualizando métricas...`;
    }

    let url = '<?= base_url('dashboard/metricas') ?>?periodo=' + encodeURIComponent(periodoActual);
    if (analistaActual && analistaActual !== 'todos') {
        url += '&analista_id=' + encodeURIComponent(analistaActual);
    }
    url += '&page=' + encodeURIComponent(paginaActual);

    fetch(url, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.status !== 'success') return;

        periodoActual = data.periodo;
        analistaActual = data.analista_id ? String(data.analista_id) : 'todos';
        paginaActual = data.pagina_actual;
        totalPaginas = data.total_paginas;
        totalRegistros = data.total_registros;

        // 1. Etiqueta de periodo activa y analista activo
        if (labelPeriodo) {
            labelPeriodo.innerHTML = `<i class="fa-regular fa-calendar-check text-primary me-1"></i> ${escapeHtml(data.labelPeriodo)}`;
        }

        const labelAnalista = document.getElementById('labelAnalistaActivo');
        if (labelAnalista) {
            if (data.nombreAnalista) {
                labelAnalista.className = 'badge bg-primary-subtle text-primary border border-primary-subtle';
                labelAnalista.innerHTML = `<i class="fa-solid fa-user-check me-1"></i>${escapeHtml(data.nombreAnalista)}`;
            } else {
                labelAnalista.className = 'd-none';
                labelAnalista.innerHTML = '';
            }
        }

        // Sincronizar selects en UI
        const selectPeriodo = document.getElementById('filtroPeriodoSelect');
        if (selectPeriodo && selectPeriodo.value !== periodoActual) {
            selectPeriodo.value = periodoActual;
        }
        const selectAnalista = document.getElementById('filtroAnalistaSelect');
        if (selectAnalista && selectAnalista.value !== analistaActual) {
            selectAnalista.value = analistaActual;
        }

        // 2. Actualizar tarjetas KPI
        const kpiCargados = document.getElementById('kpiTotalCargados');
        if (kpiCargados) kpiCargados.textContent = Number(data.statsInventario.totalCargados).toLocaleString();

        const kpiInterv = document.getElementById('kpiIntervenidos');
        if (kpiInterv) kpiInterv.textContent = Number(data.statsInventario.intervenidos).toLocaleString();

        const kpiPct = document.getElementById('kpiPorcentajeIntervenidas');
        if (kpiPct) kpiPct.textContent = `${data.statsInventario.porcentaje}% Atendido`;

        const kpiPend = document.getElementById('kpiPendientes');
        if (kpiPend) kpiPend.textContent = Number(data.statsInventario.pendientes).toLocaleString();

        const kpiTot = document.getElementById('kpiTotalIntervenciones');
        if (kpiTot) kpiTot.textContent = Number(data.totalIntervenciones).toLocaleString();

        const badgeGraf = document.getElementById('badgeTotalIntervencionesGrafico');
        if (badgeGraf) badgeGraf.textContent = `${Number(data.totalIntervenciones).toLocaleString()} Registros`;

        // 3. Actualizar micro-métricas de líneas técnicas
        const mEq = document.getElementById('metricEqVal');
        if (mEq) mEq.textContent = Number(data.totalEquipos).toLocaleString();
        const mEqP = document.getElementById('metricEqPct');
        if (mEqP) mEqP.textContent = `(${data.porcEq}%)`;

        const mSp = document.getElementById('metricSpVal');
        if (mSp) mSp.textContent = Number(data.totalSoplado).toLocaleString();
        const mSpP = document.getElementById('metricSpPct');
        if (mSpP) mSpP.textContent = `(${data.porcSp}%)`;

        const mPt = document.getElementById('metricPtVal');
        if (mPt) mPt.textContent = Number(data.totalPortatiles).toLocaleString();
        const mPtP = document.getElementById('metricPtPct');
        if (mPtP) mPtP.textContent = `(${data.porcPt}%)`;

        // 4. Actualizar gráfico de líneas
        dataAnalytics.lineas.valores = [data.totalEquipos, data.totalSoplado, data.totalPortatiles];
        renderizarGraficoLineas();

        // 5. Actualizar gráfico y datos de cumplimiento
        dataAnalytics.cumplimiento.intervenidos = data.statsInventario.intervenidos;
        dataAnalytics.cumplimiento.pendientes = data.statsInventario.pendientes;
        dataAnalytics.cumplimiento.porcentaje = data.statsInventario.porcentaje;

        const donutVal = document.getElementById('donutCenterPct');
        if (donutVal) donutVal.textContent = `${data.statsInventario.porcentaje}%`;

        const badgeCumpl = document.getElementById('badgeTasaCumplimiento');
        if (badgeCumpl) badgeCumpl.textContent = `${data.statsInventario.porcentaje}% Cumplimiento`;

        const progBar = document.getElementById('progresoCumplimientoBar');
        if (progBar) progBar.style.width = `${data.statsInventario.porcentaje}%`;

        const txtIntMini = document.getElementById('txtIntervenidasMini');
        if (txtIntMini) txtIntMini.textContent = Number(data.statsInventario.intervenidos).toLocaleString();

        const txtPendMini = document.getElementById('txtPendientesMini');
        if (txtPendMini) txtPendMini.textContent = Number(data.statsInventario.pendientes).toLocaleString();

        renderizarGraficoCumplimiento();

        // 6. Actualizar tabla matriz de máquinas
        actualizarMatrizMaquinas(data.maquinasIntervenidas);

        // 7. Actualizar controles de paginación
        actualizarControlesPaginacion(data.pagina_actual, data.total_paginas, data.total_registros);

        // 8. Sincronizar URL en el navegador
        if (window.history && window.history.pushState) {
            let nuevaUrl = window.location.pathname + '?periodo=' + encodeURIComponent(periodoActual);
            if (analistaActual && analistaActual !== 'todos') {
                nuevaUrl += '&analista_id=' + encodeURIComponent(analistaActual);
            }
            if (paginaActual > 1) {
                nuevaUrl += '&page=' + encodeURIComponent(paginaActual);
            }
            window.history.pushState(null, '', nuevaUrl);
        }
    })
    .catch(err => {
        console.error('Error al actualizar métricas:', err);
        if (labelPeriodo) {
            labelPeriodo.innerHTML = `<span class="text-danger"><i class="fa-solid fa-triangle-exclamation me-1"></i> Error al cargar datos</span>`;
        }
    });
}

function cambiarPagina(nuevaPagina) {
    if (nuevaPagina < 1 || nuevaPagina > totalPaginas || nuevaPagina === paginaActual) return;
    aplicarFiltros(null, null, nuevaPagina);
}

function actualizarControlesPaginacion(pActual, tPaginas, tRegistros) {
    const elCurrent = document.getElementById('pagiCurrentPage');
    if (elCurrent) elCurrent.textContent = pActual;

    const elTotalP = document.getElementById('pagiTotalPages');
    if (elTotalP) elTotalP.textContent = tPaginas;

    const elTotalR = document.getElementById('pagiTotalReg');
    if (elTotalR) elTotalR.textContent = Number(tRegistros).toLocaleString();

    const elBadge = document.getElementById('tablaCountBadge');
    if (elBadge) elBadge.textContent = Number(tRegistros).toLocaleString();

    const btnPrev = document.getElementById('btnPagiPrev');
    if (btnPrev) {
        btnPrev.disabled = (pActual <= 1);
    }

    const btnNext = document.getElementById('btnPagiNext');
    if (btnNext) {
        btnNext.disabled = (pActual >= tPaginas);
    }
}

/**
 * Re-renderiza el cuerpo de la matriz analítica
 */
function actualizarMatrizMaquinas(maquinas) {
    const tbody = document.getElementById('tbodyMatrizOperativa');
    if (!tbody) return;

    if (!maquinas || maquinas.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="5" class="text-center text-muted py-4">
                    <i class="fa-solid fa-filter-circle-xmark fs-4 text-muted mb-2 d-block"></i>
                    <span class="fw-semibold">No se encontraron máquinas intervenidas para los filtros seleccionados.</span>
                </td>
            </tr>
        `;
        return;
    }

    let html = '';
    maquinas.forEach(m => {
        const id1 = escapeHtml(m.identificador_1 || m.placa_id || '—');
        const id2 = escapeHtml(m.identificador_2 || m.serial || '—');

        let mod = (m.modulo_intervencion || '').toLowerCase();
        let badgeClass = 'bg-primary-subtle text-primary border border-primary-subtle';
        let icono = 'fa-desktop';
        let customStyle = '';
        let labelMod = 'Diagnóstico CPU';

        if (mod.includes('porta') || mod.includes('laptop')) {
            badgeClass = 'bg-purple-subtle text-purple border';
            icono = 'fa-laptop';
            customStyle = 'style="background-color: #f5f3ff; color: #7c3aed; border-color: #ddd6fe !important;"';
            labelMod = 'Diagnóstico Portátiles';
        } else if (mod.includes('sopla') || mod.includes('limpie')) {
            badgeClass = 'bg-info-subtle text-info border border-info-subtle';
            icono = 'fa-wind';
            labelMod = 'Soplado';
        } else {
            badgeClass = 'bg-primary-subtle text-primary border border-primary-subtle';
            icono = 'fa-desktop';
            labelMod = 'Diagnóstico CPU';
        }

        const moduloHtml = `<span class="pbi-badge ${badgeClass}" ${customStyle}><i class="fa-solid ${icono}"></i> <span>${escapeHtml(labelMod)}</span></span>`;
        const analista = m.analista_intervencion 
            ? `<span class="text-dark"><i class="fa-regular fa-user text-muted me-1"></i>${escapeHtml(m.analista_intervencion)}</span>` 
            : '<span class="text-muted">—</span>';
        const fecha = m.fecha_intervencion 
            ? `<i class="fa-regular fa-clock me-1 text-secondary opacity-75"></i>${formatearFecha(m.fecha_intervencion)}` 
            : '—';

        html += `
            <tr>
                <td class="text-muted font-monospace">${m.id}</td>
                <td>
                    <div class="fw-semibold text-dark font-monospace" style="font-size: 0.84rem;">${id1}</div>
                    <div class="text-muted font-monospace" style="font-size: 0.74rem;">
                        <i class="fa-solid fa-barcode text-secondary opacity-75 me-1"></i>${id2}
                    </div>
                </td>
                <td>${moduloHtml}</td>
                <td>${analista}</td>
                <td class="text-muted">${fecha}</td>
            </tr>
        `;
    });

    tbody.innerHTML = html;
}

function formatearFecha(fechaStr) {
    if (!fechaStr) return '—';
    const d = new Date(fechaStr.replace(' ', 'T'));
    if (isNaN(d.getTime())) return fechaStr;
    const dia = String(d.getDate()).padStart(2, '0');
    const mes = String(d.getMonth() + 1).padStart(2, '0');
    const anio = d.getFullYear();
    const hora = String(d.getHours()).padStart(2, '0');
    const min = String(d.getMinutes()).padStart(2, '0');
    return `${dia}/${mes}/${anio} ${hora}:${min}`;
}

function escapeHtml(text) {
    if (!text) return '';
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return String(text).replace(/[&<>"']/g, m => map[m]);
}

document.addEventListener('DOMContentLoaded', function() {
    renderizarGraficoLineas();
    renderizarGraficoCumplimiento();
});
</script>
<?= $this->endSection() ?>
