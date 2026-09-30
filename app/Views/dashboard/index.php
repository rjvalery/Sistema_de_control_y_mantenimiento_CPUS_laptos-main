<?php declare(strict_types=1); ?>
<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Dashboard Ejecutivo<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
    .hero-banner {
        background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
        border-radius: 16px;
        color: #ffffff;
    }
    .kpi-card {
        border-radius: 14px;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .kpi-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 20px -5px rgba(0, 0, 0, 0.08) !important;
    }
    .kpi-icon {
        width: 52px;
        height: 52px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.4rem;
    }
    .chart-box {
        position: relative;
        min-height: 290px;
    }
    .chart-center-label {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        text-align: center;
        pointer-events: none;
    }
    .module-item {
        transition: background 0.15s ease;
        border-radius: 10px;
    }
    .module-item:hover {
        background-color: #f8fafc;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- 1. HERO BANNER PRINCIPAL (ENCABEZADO EJECUTIVO) -->
<div class="hero-banner p-4 p-md-4 mb-4 shadow-sm">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1 fw-semibold">
                    <i class="fa-solid fa-circle-dot me-1"></i> Taller Operativo
                </span>
                <span class="badge bg-white bg-opacity-10 text-white rounded-pill px-3 py-1">
                    <i class="fa-regular fa-calendar me-1"></i> <?= date('d M, Y') ?>
                </span>
            </div>
            <h1 class="h3 fw-bold text-white mb-1">
                Hola, <?= esc(session('usuario_nombre') ?? 'Administrador') ?> 👋
            </h1>
            <p class="text-white-50 mb-0 small">
                Panel de control en tiempo real: flujo de entrada de máquinas, avance de intervenciones y rendimiento del taller.
            </p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="<?= base_url('inventario') ?>" class="btn btn-primary fw-semibold px-3 py-2 shadow-sm">
                <i class="fa-solid fa-boxes-stacked me-1"></i> Módulo Inventario
            </a>
            <a href="<?= base_url('equipos/formulario') ?>" class="btn btn-outline-light fw-semibold px-3 py-2">
                <i class="fa-solid fa-plus me-1"></i> Nuevo Diagnóstico
            </a>
        </div>
    </div>
</div>

<!-- 2. SELECTOR DE FILTRO TEMPORAL PARA MÉTRICAS (DÍA, SEMANA, MES, AÑO, HISTÓRICO) -->
<div class="card border-0 shadow-sm mb-4 bg-white" style="border-radius: 14px;">
    <div class="card-body p-3 d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div class="d-flex align-items-center gap-2">
            <span class="p-2 rounded bg-primary-subtle text-primary">
                <i class="fa-solid fa-filter"></i>
            </span>
            <div>
                <span class="fw-bold text-dark d-block">Filtrar Métricas y Flujo de Máquinas:</span>
                <small class="text-muted" id="labelPeriodoActivo">
                    <i class="fa-regular fa-calendar-check text-primary me-1"></i><?= esc($labelPeriodo) ?>
                </small>
            </div>
        </div>
        <div class="btn-group p-1 bg-light rounded-pill border" role="group" id="grupoFiltroPeriodo">
            <button type="button" class="btn btn-sm rounded-pill fw-semibold btn-filtro-periodo <?= ($periodo === 'dia') ? 'btn-primary shadow-sm active' : 'btn-light text-secondary' ?>" data-periodo="dia" onclick="filtrarPeriodo('dia')">
                <i class="fa-solid fa-calendar-day me-1"></i> Hoy (Día)
            </button>
            <button type="button" class="btn btn-sm rounded-pill fw-semibold btn-filtro-periodo <?= ($periodo === 'semana') ? 'btn-primary shadow-sm active' : 'btn-light text-secondary' ?>" data-periodo="semana" onclick="filtrarPeriodo('semana')">
                <i class="fa-solid fa-calendar-week me-1"></i> Esta Semana
            </button>
            <button type="button" class="btn btn-sm rounded-pill fw-semibold btn-filtro-periodo <?= ($periodo === 'mes') ? 'btn-primary shadow-sm active' : 'btn-light text-secondary' ?>" data-periodo="mes" onclick="filtrarPeriodo('mes')">
                <i class="fa-solid fa-calendar me-1"></i> Este Mes
            </button>
            <button type="button" class="btn btn-sm rounded-pill fw-semibold btn-filtro-periodo <?= ($periodo === 'anio') ? 'btn-primary shadow-sm active' : 'btn-light text-secondary' ?>" data-periodo="anio" onclick="filtrarPeriodo('anio')">
                <i class="fa-solid fa-calendar-days me-1"></i> Este Año
            </button>
            <button type="button" class="btn btn-sm rounded-pill fw-semibold btn-filtro-periodo <?= ($periodo === 'todos') ? 'btn-primary shadow-sm active' : 'btn-light text-secondary' ?>" data-periodo="todos" onclick="filtrarPeriodo('todos')">
                <i class="fa-solid fa-clock-rotate-left me-1"></i> Histórico
            </button>
        </div>
    </div>
</div>

<!-- 3. TARJETAS KPI DE ENTRADA, INTERVENCIONES Y STOCK -->
<div class="row g-3 mb-4">
    <!-- Card 1: Total Máquinas Ingresadas -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100 bg-white kpi-card border-start border-primary border-4 p-3">
            <div class="card-body p-0 d-flex flex-column justify-content-between">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small text-uppercase fw-bold">Máquinas que Entraron</span>
                    <div class="kpi-icon bg-primary-subtle text-primary">
                        <i class="fa-solid fa-truck-ramp-box"></i>
                    </div>
                </div>
                <div>
                    <h2 class="fw-bold mb-0 text-dark display-6" id="kpiTotalCargados"><?= number_format((int)$statsInventario['totalCargados']) ?></h2>
                    <div class="d-flex align-items-center justify-content-between mt-2 pt-2 border-top">
                        <small class="text-muted" id="kpiLabelCargados">Cargadas en inventario</small>
                        <span class="badge bg-primary-subtle text-primary fw-bold" id="kpiBadgeTraslados"><?= $totalTraslados ?> Traslados</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Card 2: Máquinas Intervenidas -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100 bg-white kpi-card border-start border-success border-4 p-3">
            <div class="card-body p-0 d-flex flex-column justify-content-between">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small text-uppercase fw-bold">Máquinas Intervenidas</span>
                    <div class="kpi-icon bg-success-subtle text-success">
                        <i class="fa-solid fa-circle-check"></i>
                    </div>
                </div>
                <div>
                    <h2 class="fw-bold mb-0 text-success display-6" id="kpiIntervenidos">
                        <?= number_format((int)$statsInventario['intervenidos']) ?>
                    </h2>
                    <div class="d-flex align-items-center justify-content-between mt-2 pt-2 border-top">
                        <small class="text-muted">Atendidas en el taller</small>
                        <span class="badge bg-success text-white fw-bold" id="kpiBadgePorcentaje"><?= $statsInventario['porcentaje'] ?>% Listo</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Card 3: Pendientes por Intervenir -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100 bg-white kpi-card border-start border-warning border-4 p-3">
            <div class="card-body p-0 d-flex flex-column justify-content-between">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small text-uppercase fw-bold">Pendientes por Intervenir</span>
                    <div class="kpi-icon bg-warning-subtle text-warning">
                        <i class="fa-solid fa-hourglass-half"></i>
                    </div>
                </div>
                <div>
                    <h2 class="fw-bold mb-0 text-dark display-6" id="kpiPendientes"><?= number_format((int)$statsInventario['pendientes']) ?></h2>
                    <div class="d-flex align-items-center justify-content-between mt-2 pt-2 border-top">
                        <small class="text-muted">En cola de trabajo</small>
                        <span class="badge bg-warning-subtle text-dark border border-warning-subtle fw-semibold">Por Atender</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Card 4: Total Intervenciones Globales en Taller -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100 bg-white kpi-card border-start border-info border-4 p-3">
            <div class="card-body p-0 d-flex flex-column justify-content-between">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small text-uppercase fw-bold">Intervenciones Taller</span>
                    <div class="kpi-icon bg-info-subtle text-info">
                        <i class="fa-solid fa-screwdriver-wrench"></i>
                    </div>
                </div>
                <div>
                    <h2 class="fw-bold mb-0 text-dark display-6" id="kpiTotalIntervenciones"><?= number_format((int)$totalIntervenciones) ?></h2>
                    <div class="d-flex align-items-center justify-content-between mt-2 pt-2 border-top">
                        <small class="text-muted">Total registros en bitácoras</small>
                        <span class="badge bg-info-subtle text-info fw-bold">Activo</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 3. SECCIÓN VISUAL: TORTA COLORIDA Y RENDIMIENTO POR MÓDULOS -->
<div class="row g-4 mb-4">
    <!-- COLUMNA IZQUIERDA: GRÁFICO DE TORTA COLORIDO -->
    <div class="col-12 col-lg-7">
        <div class="card border-0 shadow-sm h-100 bg-white" style="border-radius: 14px;">
            <div class="card-header bg-white border-bottom py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div>
                    <h5 class="card-title fw-bold mb-0 text-dark">
                        <i class="fa-solid fa-chart-pie text-primary me-2"></i>Métricas Visuales de Taller
                    </h5>
                    <small class="text-muted">Proporción gráfica de máquinas ingresadas, intervenidas y áreas técnicas.</small>
                </div>
                <!-- BOTONES SWITCH PARA CAMBIAR VISTA DE LA TORTA -->
                <div class="btn-group btn-group-sm" role="group">
                    <button type="button" class="btn btn-primary fw-semibold" id="btnChartFlujo" onclick="cambiarVistaGrafico('flujo')">
                        <i class="fa-solid fa-sliders me-1"></i> Flujo Entrada
                    </button>
                    <button type="button" class="btn btn-outline-primary fw-semibold" id="btnChartModulos" onclick="cambiarVistaGrafico('modulos')">
                        <i class="fa-solid fa-layer-group me-1"></i> Por Módulo
                    </button>
                </div>
            </div>
            
            <div class="card-body p-4 d-flex flex-column justify-content-between">
                <!-- CONTENEDOR DEL CANVAS CON ETIQUETA CENTRAL -->
                <div class="chart-box d-flex align-items-center justify-content-center my-2">
                    <canvas id="chartTortaColorida" style="max-height: 270px;"></canvas>
                    <div class="chart-center-label" id="centerLabelBox">
                        <div class="display-6 fw-bold text-dark mb-0" id="centerLabelValor"><?= $statsInventario['porcentaje'] ?>%</div>
                        <small class="text-muted fw-semibold" id="centerLabelTexto">Intervenido</small>
                    </div>
                </div>

                <!-- LEYENDA COLORIDA DINÁMICA DEBAJO DEL GRÁFICO -->
                <div class="d-flex flex-wrap justify-content-center align-items-center gap-3 mt-3 pt-3 border-top" id="contenedorLeyenda">
                    <!-- Generado dinámicamente por JavaScript -->
                </div>
            </div>
        </div>
    </div>

    <!-- COLUMNA DERECHA: DESGLOSE OPERATIVO POR MÓDULOS -->
    <div class="col-12 col-lg-5">
        <div class="card border-0 shadow-sm h-100 bg-white" style="border-radius: 14px;">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="card-title fw-bold mb-0 text-dark">
                        <i class="fa-solid fa-bars-progress text-info me-2"></i>Distribución de Trabajo
                    </h5>
                    <small class="text-muted">Rendimiento por línea técnica y stock.</small>
                </div>
                <span class="badge bg-light text-muted border px-2 py-1 small">
                    <?= number_format((int)$totalAnalistas) ?> Analistas
                </span>
            </div>

            <div class="card-body p-3 p-md-4 d-flex flex-column justify-content-around">
                <!-- 1. Diagnóstico CPU -->
                <div class="module-item p-2 mb-2">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <div class="d-flex align-items-center gap-2">
                            <span class="p-2 rounded bg-primary-subtle text-primary"><i class="fa-solid fa-desktop"></i></span>
                            <div>
                                <span class="fw-bold text-dark d-block">Diagnóstico CPU</span>
                                <small class="text-muted">Equipos de escritorio</small>
                            </div>
                        </div>
                        <div class="text-end">
                            <span class="fs-5 fw-bold text-primary" id="modTotalEquipos"><?= number_format((int)$totalEquipos) ?></span>
                            <small class="text-muted d-block">equipos</small>
                        </div>
                    </div>
                    <div class="progress" style="height: 6px;">
                        <?php $porcEq = $totalIntervenciones > 0 ? round(($totalEquipos / $totalIntervenciones) * 100, 1) : 0; ?>
                        <div class="progress-bar bg-primary" id="modBarEquipos" style="width: <?= $porcEq ?>%;"></div>
                    </div>
                </div>

                <!-- 2. Soplado de CPUs -->
                <div class="module-item p-2 mb-2">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <div class="d-flex align-items-center gap-2">
                            <span class="p-2 rounded bg-info-subtle text-info"><i class="fa-solid fa-wind"></i></span>
                            <div>
                                <span class="fw-bold text-dark d-block">Mantenimiento Soplado</span>
                                <small class="text-muted">Limpieza y pasta térmica</small>
                            </div>
                        </div>
                        <div class="text-end">
                            <span class="fs-5 fw-bold text-info" id="modTotalSoplado"><?= number_format((int)$totalSoplado) ?></span>
                            <small class="text-muted d-block">servicios</small>
                        </div>
                    </div>
                    <div class="progress" style="height: 6px;">
                        <?php $porcSp = $totalIntervenciones > 0 ? round(($totalSoplado / $totalIntervenciones) * 100, 1) : 0; ?>
                        <div class="progress-bar bg-info" id="modBarSoplado" style="width: <?= $porcSp ?>%;"></div>
                    </div>
                </div>

                <!-- 3. Portátiles -->
                <div class="module-item p-2 mb-2">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <div class="d-flex align-items-center gap-2">
                            <span class="p-2 rounded bg-purple-subtle text-purple" style="background-color: #f3e8ff; color: #7e22ce;"><i class="fa-solid fa-laptop"></i></span>
                            <div>
                                <span class="fw-bold text-dark d-block">Garantías Portátiles</span>
                                <small class="text-muted">Laptops Lenovo / FRU</small>
                            </div>
                        </div>
                        <div class="text-end">
                            <span class="fs-5 fw-bold" style="color: #7e22ce;" id="modTotalPortatiles"><?= number_format((int)$totalPortatiles) ?></span>
                            <small class="text-muted d-block">laptops</small>
                        </div>
                    </div>
                    <div class="progress" style="height: 6px;">
                        <?php $porcPt = $totalIntervenciones > 0 ? round(($totalPortatiles / $totalIntervenciones) * 100, 1) : 0; ?>
                        <div class="progress-bar" id="modBarPortatiles" style="background-color: #8b5cf6; width: <?= $porcPt ?>%;"></div>
                    </div>
                </div>

                <!-- 4. Barra consolidada de stock de inventario -->
                <div class="p-3 bg-light rounded-3 mt-1 border">
                    <div class="d-flex justify-content-between align-items-center small mb-1">
                        <span class="fw-bold text-dark">Efectividad de Intervención:</span>
                        <span class="fw-bold text-success" id="modEfectividadPct"><?= $statsInventario['porcentaje'] ?>% Atendido</span>
                    </div>
                    <div class="progress" style="height: 10px; border-radius: 6px;">
                        <div class="progress-bar bg-success progress-bar-striped progress-bar-animated" id="modEfectividadBar" style="width: <?= (float)$statsInventario['porcentaje'] ?>%;"></div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mt-2 small text-muted">
                        <span id="modEfectividadIntervenidos"><i class="fa-solid fa-circle text-success me-1"></i><?= number_format((int)$statsInventario['intervenidos']) ?> Intervenidas</span>
                        <span id="modEfectividadPendientes"><i class="fa-solid fa-circle text-warning me-1"></i><?= number_format((int)$statsInventario['pendientes']) ?> Restantes</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 4. TABLA: MÁQUINAS QUE HAN ENTRADO E INTERVENIDO EN EL SISTEMA -->
<div class="card border-0 shadow-sm mb-4" style="border-radius: 14px;">
    <div class="card-header bg-white border-bottom py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div>
            <h5 class="card-title fw-bold mb-0 text-dark">
                <i class="fa-solid fa-microchip text-success me-2"></i>Máquinas que Entraron e Intervenidas en Taller
            </h5>
            <small class="text-muted">Flujo más reciente de equipos que ingresaron al inventario y ya recibieron atención técnica.</small>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="<?= base_url('inventario?filtro=agregados') ?>" class="btn btn-outline-primary btn-sm fw-semibold">
                <i class="fa-solid fa-boxes-stacked me-1"></i> Ver Todas en Inventario
            </a>
            <a href="<?= base_url('inventario/sincronizar') ?>" class="btn btn-primary btn-sm fw-semibold shadow-sm" title="Sincronizar y actualizar estado de máquinas">
                <i class="fa-solid fa-arrows-rotate me-1"></i> Sincronizar Mapeo
            </a>
        </div>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 text-nowrap">
                <thead class="table-dark">
                    <tr>
                        <th class="ps-3">ID</th>
                        <th style="min-width: 140px;">Equipo / Serial</th>
                        <th>Traslado</th>
                        <th>Modelo / Descripción</th>
                        <th>Módulo de Intervención</th>
                        <th>Analista Técnico</th>
                        <th>Fecha Intervención</th>
                        <th class="text-end pe-3">Estado</th>
                    </tr>
                </thead>
                <tbody id="tbodyMaquinasIntervenidas">
                    <?php if (!empty($maquinasIntervenidas)): ?>
                        <?php foreach ($maquinasIntervenidas as $m): ?>
                            <tr>
                                <td class="ps-3 text-muted"><?= esc((string)$m['id']) ?></td>
                                <td>
                                    <div class="fw-bold text-dark font-monospace">
                                        <?= esc((string)($m['identificador_1'] ?: ($m['placa_id'] ?? '—'))) ?>
                                    </div>
                                    <small class="text-muted font-monospace d-block">
                                        <i class="fa-solid fa-barcode me-1 text-secondary opacity-75"></i><?= esc((string)($m['identificador_2'] ?: ($m['serial'] ?? '—'))) ?>
                                    </small>
                                </td>
                                <td>
                                    <?php if (!empty($m['num_traslado'])): ?>
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle fw-semibold">
                                            <i class="fa-solid fa-truck-ramp-box me-1"></i><?= esc((string)$m['num_traslado']) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted small">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="text-truncate" style="max-width: 180px;" title="<?= esc((string)($m['ref_principal'] ?: ($m['modelo'] ?? ''))) ?>">
                                        <?= esc((string)($m['ref_principal'] ?: ($m['modelo'] ?? ($m['descripcion'] ?? 'Equipo Taller')))) ?>
                                    </div>
                                </td>
                                <td>
                                    <?php 
                                        $mod = strtolower((string)($m['modulo_intervencion'] ?? ''));
                                        if (str_contains($mod, 'diagn') || str_contains($mod, 'cpu')) {
                                            $badgeClass = 'bg-primary-subtle text-primary border border-primary-subtle';
                                            $icono = 'fa-desktop';
                                        } elseif (str_contains($mod, 'sopla') || str_contains($mod, 'limpie')) {
                                            $badgeClass = 'bg-info-subtle text-info border border-info-subtle';
                                            $icono = 'fa-wind';
                                        } else {
                                            $badgeClass = 'bg-success-subtle text-success border border-success-subtle';
                                            $icono = 'fa-laptop';
                                        }
                                    ?>
                                    <span class="badge <?= $badgeClass ?> fw-semibold px-2 py-1">
                                        <i class="fa-solid <?= $icono ?> me-1"></i><?= esc((string)($m['modulo_intervencion'] ?: 'Agregado al Sistema')) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if (!empty($m['analista_intervencion'])): ?>
                                        <div class="text-truncate text-dark small" style="max-width: 130px;" title="<?= esc((string)$m['analista_intervencion']) ?>">
                                            <i class="fa-solid fa-user-check text-muted me-1"></i><?= esc((string)$m['analista_intervencion']) ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted small">—</span>
                                    <?php endif; ?>
                                </td>
                                <td class="small text-muted">
                                    <?php if (!empty($m['fecha_intervencion'])): ?>
                                        <i class="fa-regular fa-clock me-1"></i><?= esc(date('d/m/Y H:i', strtotime((string)$m['fecha_intervencion']))) ?>
                                    <?php else: ?>
                                        —
                                    <?php endif; ?>
                                </td>
                                <td class="text-end pe-3">
                                    <span class="badge bg-success text-white px-2 py-1 fw-semibold">
                                        <i class="fa-solid fa-check me-1"></i>Intervenido
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted py-5">
                                <i class="fa-solid fa-filter-circle-xmark display-6 text-muted mb-3 d-block"></i>
                                <strong>No hay máquinas intervenidas en este periodo.</strong><br>
                                <span class="small text-muted">Selecciona otro rango de fechas o consulta el histórico completo.</span>
                                <div class="mt-3">
                                    <a href="<?= base_url('inventario') ?>" class="btn btn-sm btn-primary">
                                        <i class="fa-solid fa-boxes-stacked me-1"></i> Ver Inventario Completo
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card-footer bg-light py-2 px-3 d-flex flex-wrap justify-content-between align-items-center small text-muted">
        <span>Mostrando <strong id="footerCountMaquinas"><?= count($maquinasIntervenidas) ?></strong> máquina(s) intervenida(s)</span>
        <a href="<?= base_url('inventario') ?>" class="text-decoration-none fw-semibold">
            Gestionar inventario completo &rarr;
        </a>
    </div>
</div>



<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<!-- Script de Chart.js para el gráfico de torta colorido y dinámico -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
let chartInstance = null;
let vistaActual = 'flujo';
let periodoActual = '<?= esc($periodo) ?>';

// Datos consolidados desde el backend
const metricasData = {
    flujo: {
        titulo: 'Flujo de Entrada vs Intervenidas',
        centerValor: '<?= $statsInventario['porcentaje'] ?>%',
        centerTexto: 'Intervenido',
        labels: ['Máquinas Intervenidas', 'Pendientes en Espera'],
        valores: [<?= (int)$statsInventario['intervenidos'] ?>, <?= (int)$statsInventario['pendientes'] ?>],
        colores: ['#10b981', '#f59e0b'], // Verde esmeralda y Ámbar cálido
        iconos: ['fa-circle-check', 'fa-clock-rotate-left']
    },
    modulos: {
        titulo: 'Intervenciones por Módulo',
        centerValor: '<?= number_format((int)$totalIntervenciones) ?>',
        centerTexto: 'Intervenciones',
        labels: ['Diagnóstico CPU', 'Mantenimiento Soplado', 'Garantías Portátiles'],
        valores: [<?= (int)$totalEquipos ?>, <?= (int)$totalSoplado ?>, <?= (int)$totalPortatiles ?>],
        colores: ['#3b82f6', '#06b6d4', '#8b5cf6'], // Azul eléctrico, Cian y Violeta
        iconos: ['fa-desktop', 'fa-wind', 'fa-laptop']
    }
};

function renderizarGrafico(tipo) {
    const config = metricasData[tipo];
    const ctx = document.getElementById('chartTortaColorida');
    if (!ctx) return;

    // Actualizar texto central
    document.getElementById('centerLabelValor').textContent = config.centerValor;
    document.getElementById('centerLabelTexto').textContent = config.centerTexto;

    // Si todos los valores son 0, mostrar una porción visual neutral
    const sumaValores = config.valores.reduce((a, b) => a + b, 0);
    const datasetValores = sumaValores > 0 ? config.valores : [1];
    const datasetColores = sumaValores > 0 ? config.colores : ['#e2e8f0'];

    if (chartInstance) {
        chartInstance.destroy();
    }

    chartInstance = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: config.labels,
            datasets: [{
                data: datasetValores,
                backgroundColor: datasetColores,
                hoverBackgroundColor: datasetColores,
                borderWidth: 3,
                borderColor: '#ffffff',
                borderRadius: 6,
                spacing: 3
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '72%',
            plugins: {
                legend: {
                    display: false // Usamos nuestra leyenda HTML interactiva
                },
                tooltip: {
                    enabled: sumaValores > 0,
                    backgroundColor: '#1e293b',
                    titleFont: { size: 13, weight: 'bold' },
                    bodyFont: { size: 12 },
                    padding: 12,
                    cornerRadius: 8,
                    callbacks: {
                        label: function(context) {
                            const val = context.raw || 0;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const porc = total > 0 ? Math.round((val / total) * 100) : 0;
                            return ` ${context.label}: ${val} (${porc}%)`;
                        }
                    }
                }
            },
            animation: {
                animateScale: true,
                animateRotate: true,
                duration: 600
            }
        }
    });

    // Renderizar leyenda interactiva
    actualizarLeyenda(config, sumaValores);
}

function actualizarLeyenda(config, sumaValores) {
    const contenedor = document.getElementById('contenedorLeyenda');
    if (!contenedor) return;

    let html = '';
    config.labels.forEach((label, idx) => {
        const val = config.valores[idx];
        const color = config.colores[idx];
        const icono = config.iconos[idx];
        const porc = sumaValores > 0 ? Math.round((val / sumaValores) * 100) : 0;

        html += `
            <div class="d-flex align-items-center gap-2 p-1 px-2 rounded" style="background-color: #f8fafc; border: 1px solid #e2e8f0;">
                <span class="d-inline-block rounded-circle" style="width: 12px; height: 12px; background-color: ${color};"></span>
                <span class="small fw-semibold text-dark"><i class="fa-solid ${icono} me-1" style="color: ${color};"></i> ${label}:</span>
                <span class="badge rounded-pill fw-bold" style="background-color: ${color}20; color: ${color}; border: 1px solid ${color}40;">
                    ${val.toLocaleString()} (${porc}%)
                </span>
            </div>
        `;
    });

    contenedor.innerHTML = html;
}

function cambiarVistaGrafico(tipo) {
    vistaActual = tipo;
    const btnFlujo = document.getElementById('btnChartFlujo');
    const btnModulos = document.getElementById('btnChartModulos');

    if (tipo === 'flujo') {
        btnFlujo.className = 'btn btn-primary fw-semibold';
        btnModulos.className = 'btn btn-outline-primary fw-semibold';
    } else {
        btnFlujo.className = 'btn btn-outline-primary fw-semibold';
        btnModulos.className = 'btn btn-primary fw-semibold';
    }

    renderizarGrafico(tipo);
}

// Función para filtrar asíncronamente por periodo (dia, semana, mes, anio, todos)
function filtrarPeriodo(periodo) {
    if (periodo === periodoActual && chartInstance) return;

    // 1. Actualizar apariencia de los botones del grupo
    document.querySelectorAll('.btn-filtro-periodo').forEach(btn => {
        if (btn.getAttribute('data-periodo') === periodo) {
            btn.className = 'btn btn-sm rounded-pill fw-semibold btn-filtro-periodo btn-primary shadow-sm active';
        } else {
            btn.className = 'btn btn-sm rounded-pill fw-semibold btn-filtro-periodo btn-light text-secondary';
        }
    });

    // 2. Mostrar indicador de carga en la etiqueta de periodo
    const labelPeriodo = document.getElementById('labelPeriodoActivo');
    if (labelPeriodo) {
        labelPeriodo.innerHTML = `<i class="fa-solid fa-spinner fa-spin text-primary me-1"></i> Cargando métricas...`;
    }

    // 3. Petición AJAX al endpoint de métricas
    const url = '<?= base_url('dashboard/metricas') ?>?periodo=' + encodeURIComponent(periodo);

    fetch(url, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.status !== 'success') return;

        periodoActual = data.periodo;

        // Actualizar etiqueta del periodo
        if (labelPeriodo) {
            labelPeriodo.innerHTML = `<i class="fa-regular fa-calendar-check text-primary me-1"></i> ${escapeHtml(data.labelPeriodo)}`;
        }

        // Actualizar KPIs superiores
        const kpiCargados = document.getElementById('kpiTotalCargados');
        if (kpiCargados) kpiCargados.textContent = Number(data.statsInventario.totalCargados).toLocaleString();

        const kpiInterv = document.getElementById('kpiIntervenidos');
        if (kpiInterv) kpiInterv.textContent = Number(data.statsInventario.intervenidos).toLocaleString();

        const kpiBadgePct = document.getElementById('kpiBadgePorcentaje');
        if (kpiBadgePct) kpiBadgePct.textContent = `${data.statsInventario.porcentaje}% Listo`;

        const kpiPend = document.getElementById('kpiPendientes');
        if (kpiPend) kpiPend.textContent = Number(data.statsInventario.pendientes).toLocaleString();

        const kpiTotInterv = document.getElementById('kpiTotalIntervenciones');
        if (kpiTotInterv) kpiTotInterv.textContent = Number(data.totalIntervenciones).toLocaleString();

        // Actualizar módulo de Diagnóstico CPU
        const elTotalEq = document.getElementById('modTotalEquipos');
        if (elTotalEq) elTotalEq.textContent = Number(data.totalEquipos).toLocaleString();
        const elBarEq = document.getElementById('modBarEquipos');
        if (elBarEq) elBarEq.style.width = `${data.porcEq}%`;

        // Actualizar módulo de Soplado
        const elTotalSp = document.getElementById('modTotalSoplado');
        if (elTotalSp) elTotalSp.textContent = Number(data.totalSoplado).toLocaleString();
        const elBarSp = document.getElementById('modBarSoplado');
        if (elBarSp) elBarSp.style.width = `${data.porcSp}%`;

        // Actualizar módulo de Portátiles
        const elTotalPt = document.getElementById('modTotalPortatiles');
        if (elTotalPt) elTotalPt.textContent = Number(data.totalPortatiles).toLocaleString();
        const elBarPt = document.getElementById('modBarPortatiles');
        if (elBarPt) elBarPt.style.width = `${data.porcPt}%`;

        // Actualizar barra consolidada de efectividad
        const elEfPct = document.getElementById('modEfectividadPct');
        if (elEfPct) elEfPct.textContent = `${data.statsInventario.porcentaje}% Atendido`;
        const elEfBar = document.getElementById('modEfectividadBar');
        if (elEfBar) elEfBar.style.width = `${data.statsInventario.porcentaje}%`;

        const elEfInterv = document.getElementById('modEfectividadIntervenidos');
        if (elEfInterv) elEfInterv.innerHTML = `<i class="fa-solid fa-circle text-success me-1"></i>${Number(data.statsInventario.intervenidos).toLocaleString()} Intervenidas`;

        const elEfPend = document.getElementById('modEfectividadPendientes');
        if (elEfPend) elEfPend.innerHTML = `<i class="fa-solid fa-circle text-warning me-1"></i>${Number(data.statsInventario.pendientes).toLocaleString()} Restantes`;

        // Actualizar datos del Gráfico Donut
        metricasData.flujo.centerValor = `${data.statsInventario.porcentaje}%`;
        metricasData.flujo.valores = [data.statsInventario.intervenidos, data.statsInventario.pendientes];

        metricasData.modulos.centerValor = Number(data.totalIntervenciones).toLocaleString();
        metricasData.modulos.valores = [data.totalEquipos, data.totalSoplado, data.totalPortatiles];

        renderizarGrafico(vistaActual);

        // Actualizar listado de máquinas intervenidas recientes
        actualizarTablaMaquinas(data.maquinasIntervenidas);

        // Sincronizar URL en el navegador sin recargar la página
        if (window.history && window.history.pushState) {
            const nuevaUrl = window.location.pathname + '?periodo=' + encodeURIComponent(periodo);
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

function actualizarTablaMaquinas(maquinas) {
    const tbody = document.getElementById('tbodyMaquinasIntervenidas');
    const badgeCount = document.getElementById('footerCountMaquinas');
    if (!tbody) return;

    if (!maquinas || maquinas.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="8" class="text-center text-muted py-5">
                    <i class="fa-solid fa-filter-circle-xmark display-6 text-muted mb-3 d-block"></i>
                    <strong>No hay máquinas intervenidas en este periodo.</strong><br>
                    <span class="small text-muted">Selecciona otro rango de fechas o consulta el histórico completo.</span>
                    <div class="mt-3">
                        <a href="<?= base_url('inventario') ?>" class="btn btn-sm btn-primary">
                            <i class="fa-solid fa-boxes-stacked me-1"></i> Ver Inventario Completo
                        </a>
                    </div>
                </td>
            </tr>
        `;
        if (badgeCount) badgeCount.textContent = '0';
        return;
    }

    if (badgeCount) badgeCount.textContent = maquinas.length;

    let html = '';
    maquinas.forEach(m => {
        const id1 = escapeHtml(m.identificador_1 || m.placa_id || '—');
        const id2 = escapeHtml(m.identificador_2 || m.serial || '—');
        const traslado = m.num_traslado 
            ? `<span class="badge bg-primary-subtle text-primary border border-primary-subtle fw-semibold"><i class="fa-solid fa-truck-ramp-box me-1"></i>${escapeHtml(m.num_traslado)}</span>` 
            : '<span class="text-muted small">—</span>';
        const modelo = escapeHtml(m.ref_principal || m.modelo || m.descripcion || 'Equipo Taller');

        let mod = (m.modulo_intervencion || '').toLowerCase();
        let badgeClass = 'bg-success-subtle text-success border border-success-subtle';
        let icono = 'fa-laptop';
        if (mod.includes('diagn') || mod.includes('cpu')) {
            badgeClass = 'bg-primary-subtle text-primary border border-primary-subtle';
            icono = 'fa-desktop';
        } else if (mod.includes('sopla') || mod.includes('limpie')) {
            badgeClass = 'bg-info-subtle text-info border border-info-subtle';
            icono = 'fa-wind';
        }

        const moduloHtml = `<span class="badge ${badgeClass} fw-semibold px-2 py-1"><i class="fa-solid ${icono} me-1"></i>${escapeHtml(m.modulo_intervencion || 'Agregado al Sistema')}</span>`;
        const analista = m.analista_intervencion 
            ? `<div class="text-truncate text-dark small" style="max-width: 130px;" title="${escapeHtml(m.analista_intervencion)}"><i class="fa-solid fa-user-check text-muted me-1"></i>${escapeHtml(m.analista_intervencion)}</div>` 
            : '<span class="text-muted small">—</span>';
        const fecha = m.fecha_intervencion 
            ? `<i class="fa-regular fa-clock me-1"></i>${formatearFecha(m.fecha_intervencion)}` 
            : '—';

        html += `
            <tr>
                <td class="ps-3 text-muted">${m.id}</td>
                <td>
                    <div class="fw-bold text-dark font-monospace">${id1}</div>
                    <small class="text-muted font-monospace d-block"><i class="fa-solid fa-barcode me-1 text-secondary opacity-75"></i>${id2}</small>
                </td>
                <td>${traslado}</td>
                <td><div class="text-truncate" style="max-width: 180px;" title="${modelo}">${modelo}</div></td>
                <td>${moduloHtml}</td>
                <td>${analista}</td>
                <td class="small text-muted">${fecha}</td>
                <td class="text-end pe-3">
                    <span class="badge bg-success text-white px-2 py-1 fw-semibold">
                        <i class="fa-solid fa-check me-1"></i>Intervenido
                    </span>
                </td>
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
    renderizarGrafico('flujo');
});
</script>
<?= $this->endSection() ?>

