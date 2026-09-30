<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Módulo de Inventario y Cargue Masivo<?= $this->endSection() ?>

<?= $this->section('container_class') ?>container-fluid px-3 px-xl-4 py-2<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
    .upload-box {
        border: 2px dashed #0d6efd;
        border-radius: 12px;
        background-color: #f8faff;
        transition: all 0.2s ease-in-out;
        padding: 2.2rem 1.5rem;
        text-align: center;
        cursor: pointer;
    }
    .upload-box:hover {
        background-color: #eef4ff;
        border-color: #0b5ed7;
        transform: translateY(-2px);
    }
    .table-inventario-sticky thead th {
        position: sticky;
        top: 0;
        z-index: 2;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="row">
    <div class="col-12">

        <!-- ENCABEZADO DE MÓDULO UNIFICADO -->
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 pb-2 border-bottom">
            <div>
                <h1 class="h3 fw-bold text-dark mb-1">
                    <i class="fa-solid fa-boxes-stacked text-primary me-2"></i>Módulo de Inventario y Cargue Masivo
                </h1>
                <p class="text-muted mb-0">
                    Control general de inventario de equipos (Cubic), trazabilidad de traslados e importación masiva de datos a <code>inventario_general</code>.
                </p>
            </div>
            <div class="mt-2 mt-md-0 d-flex flex-wrap gap-2">
                <button class="btn btn-outline-primary btn-sm fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#panelCargue" aria-expanded="true" aria-controls="panelCargue">
                    <i class="fa-solid fa-cloud-arrow-up me-1"></i> Panel de Cargue Masivo
                </button>
                <a href="<?= base_url('inventario/plantilla') ?>" class="btn btn-success btn-sm fw-semibold">
                    <i class="fa-solid fa-file-excel me-1"></i> Plantilla Excel/CSV
                </a>
                <a href="<?= base_url('dashboard') ?>" class="btn btn-outline-secondary btn-sm">
                    <i class="fa-solid fa-arrow-left me-1"></i> Volver al Dashboard
                </a>
            </div>
        </div>


        <!-- PANEL DE MÉTRICAS Y ESTADO DEL INVENTARIO -->
        <div class="row g-3 mb-4">
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100 bg-white border-start border-primary border-4">
                    <div class="card-body p-3 d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small text-uppercase fw-bold">Total Cargados (Cubic)</span>
                            <h3 class="fw-bold mb-0 text-dark"><?= number_format((int)($statsInventario['totalCargados'] ?? 0)) ?></h3>
                            <small class="text-muted">Equipos en <code>inventario_general</code></small>
                        </div>
                        <div class="bg-primary-subtle text-primary p-3 rounded-circle">
                            <i class="fa-solid fa-boxes-stacked fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100 bg-white border-start border-success border-4">
                    <div class="card-body p-3 d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small text-uppercase fw-bold">Intervenidos en Sistema</span>
                            <h3 class="fw-bold mb-0 text-success">
                                <?= number_format((int)($statsInventario['intervenidos'] ?? 0)) ?>
                                <span class="badge bg-success-subtle text-success fs-6 border border-success-subtle ms-1">
                                    <?= ($statsInventario['porcentaje'] ?? 0) ?>%
                                </span>
                            </h3>
                            <small class="text-muted">En diagnóstico o soplado</small>
                        </div>
                        <div class="bg-success-subtle text-success p-3 rounded-circle">
                            <i class="fa-solid fa-circle-check fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100 bg-white border-start border-warning border-4">
                    <div class="card-body p-3 d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small text-uppercase fw-bold">Pendientes por Ingresar</span>
                            <h3 class="fw-bold mb-0 text-warning"><?= number_format((int)($statsInventario['pendientes'] ?? 0)) ?></h3>
                            <small class="text-muted">Aún sin registro en bitácoras</small>
                        </div>
                        <div class="bg-warning-subtle text-warning p-3 rounded-circle">
                            <i class="fa-solid fa-clock-rotate-left fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100 bg-white border-start border-info border-4">
                    <div class="card-body p-3 d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small text-uppercase fw-bold">Traslados Registrados</span>
                            <h3 class="fw-bold mb-0 text-info"><?= count($trasladosDisponibles) ?></h3>
                            <small class="text-muted">Lotes / Remisiones en BD</small>
                        </div>
                        <div class="bg-info-subtle text-info p-3 rounded-circle">
                            <i class="fa-solid fa-truck-ramp-box fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ANCLA Y SECCIÓN DE CARGUE MASIVO (COLAPSIBLE) -->
        <div id="seccion-cargue"></div>
        <div class="collapse show mb-4" id="panelCargue">
            <div class="row g-4">
                
                <!-- FORMULARIO DE CARGUE DIRECTO -->
                <div class="col-lg-7">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-white border-bottom py-3">
                            <h5 class="card-title fw-bold mb-0 text-dark">
                                <i class="fa-solid fa-cloud-arrow-up text-primary me-2"></i>Subir Archivo para Inserción en Base de Datos
                            </h5>
                        </div>
                        <div class="card-body p-4">
                            
                            <form action="<?= base_url('inventario/procesar') ?>" method="POST" enctype="multipart/form-data" id="formCargueMasivo">
                                <?= csrf_field() ?>
                                <input type="hidden" name="num_traslado" id="num_traslado">

                                <div class="mb-3">
                                    <label class="form-label fw-bold">Archivo Excel (.xlsx, .xls) o CSV (.csv) *</label>
                                    <div class="upload-box" id="upload-box-container" onclick="document.getElementById('archivo_csv').click();">
                                        <i class="fa-solid fa-file-excel display-4 text-success mb-3" id="upload-icon"></i>
                                        <h6 class="fw-bold text-dark mb-1" id="file-label">Haz clic para seleccionar tu archivo Excel o CSV</h6>
                                        <p class="small text-muted mb-0" id="file-subtext">Admite libros de Excel (.xlsx, .xls) y archivos delimitados (.csv, .txt).</p>
                                        <input type="file" name="archivo_csv" id="archivo_csv" class="d-none" accept=".xlsx, .xls, .csv, .txt, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel, text/csv, text/plain" required onchange="mostrarNombreArchivo(this)">
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between align-items-center mt-4">
                                    <small class="text-muted">
                                        <i class="fa-solid fa-info-circle me-1"></i>Tabla de destino: <code>inventario_general</code>
                                    </small>
                                    <button type="button" class="btn btn-primary px-4 py-2 fw-bold" id="btnProcesar" onclick="abrirModalTraslado()">
                                        <i class="fa-solid fa-upload me-2"></i>Importar a Base de Datos
                                    </button>
                                </div>
                            </form>

                        </div>
                    </div>
                </div>

                <!-- INSTRUCCIONES Y GUÍA -->
                <div class="col-lg-5">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                            <h5 class="card-title fw-bold mb-0 text-dark">
                                <i class="fa-solid fa-list-check text-success me-2"></i>Estructura de Columnas
                            </h5>
                            <a href="<?= base_url('inventario/plantilla') ?>" class="btn btn-sm btn-outline-success">
                                <i class="fa-solid fa-download me-1"></i>Plantilla
                            </a>
                        </div>
                        <div class="card-body p-4">
                            <p class="small text-muted mb-3">
                                Estructura de 8 columnas adaptada a la plantilla oficial <strong>Formato en Cubic</strong>:
                            </p>

                            <div class="table-responsive">
                                <table class="table table-sm table-bordered small mb-3">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Columna (Excel/CSV)</th>
                                            <th>Tipo</th>
                                            <th>Ejemplo</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><code>Identificador 1</code></td>
                                            <td><span class="badge bg-danger-subtle text-danger">Requerido*</span></td>
                                            <td>ACT-10021</td>
                                        </tr>
                                        <tr>
                                            <td><code>Identificador 2</code></td>
                                            <td><span class="badge bg-danger-subtle text-danger">Requerido*</span></td>
                                            <td>SN-MBP99201</td>
                                        </tr>
                                        <tr>
                                            <td><code>Ref. Principal</code></td>
                                            <td><span class="badge bg-secondary-subtle text-secondary">Opcional</span></td>
                                            <td>MacBook Pro 16 M1</td>
                                        </tr>
                                        <tr>
                                            <td><code>Descripción</code></td>
                                            <td><span class="badge bg-secondary-subtle text-secondary">Opcional</span></td>
                                            <td>Laptop Apple Corporativo</td>
                                        </tr>
                                        <tr>
                                            <td><code>Zona Origen</code></td>
                                            <td><span class="badge bg-secondary-subtle text-secondary">Opcional</span></td>
                                            <td>Sede Central</td>
                                        </tr>
                                        <tr>
                                            <td><code>Ubicación Origen</code></td>
                                            <td><span class="badge bg-secondary-subtle text-secondary">Opcional</span></td>
                                            <td>Piso 3 - Puesto 302</td>
                                        </tr>
                                        <tr>
                                            <td><code>Verificado</code></td>
                                            <td><span class="badge bg-secondary-subtle text-secondary">Opcional</span></td>
                                            <td>Verificado / Pendiente</td>
                                        </tr>
                                        <tr>
                                            <td><code>Observaciones</code></td>
                                            <td><span class="badge bg-secondary-subtle text-secondary">Opcional</span></td>
                                            <td>Equipo en buen estado</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <small class="text-muted d-block"><em>* Se requiere al menos Identificador 1 o Identificador 2 para procesar la fila.</em></small>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- TABLA DE REGISTROS ALMACENADOS EN inventario_general -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom py-3">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                    <div>
                        <h5 class="card-title fw-bold mb-0 text-dark">
                            <i class="fa-solid fa-table-list text-primary me-2"></i>Inventario General de Equipos (Estructura Cubic)
                            <span class="badge bg-primary ms-1">
                                <?= (!empty($filtro) || !empty($traslado) || !empty($busqueda)) ? "{$totalFiltrados} de {$totalRegistros}" : "{$totalRegistros} total" ?>
                            </span>
                        </h5>
                        <small class="text-muted">Cruzando registros de <code>inventario_general</code> con <code>equipos</code>, <code>soplado_registros</code> y <code>garantias_portatiles</code>.</small>
                    </div>
                    
                    <div class="d-flex flex-wrap align-items-center gap-2">
                        <!-- BOTÓN DE MAPEO / SINCRONIZACIÓN AUTOMÁTICA -->
                        <a href="<?= base_url('inventario/sincronizar') ?><?= !empty($traslado) ? '?traslado=' . urlencode($traslado) : '' ?>" class="btn btn-primary btn-sm fw-bold shadow-sm" title="Cruza y localiza automáticamente los equipos cargados que ya están en las bitácoras">
                            <i class="fa-solid fa-arrows-rotate me-1"></i> Sincronizar Mapeo BD<?= !empty($traslado) ? ' (' . esc($traslado) . ')' : '' ?>
                        </a>

                        <!-- BOTÓN PARA RECUPERAR TRASLADOS DESDE BITÁCORAS -->
                        <a href="<?= base_url('inventario/recuperar-traslados') ?>" class="btn btn-outline-info btn-sm fw-bold shadow-sm" title="Mapea y asigna el número de traslado a las máquinas del respaldo que aún no lo tienen, usando las bitácoras técnicas">
                            <i class="fa-solid fa-wand-magic-sparkles me-1"></i> Recuperar Traslados
                        </a>

                        <?php if ($totalRegistros > 0 && $esAdmin): ?>
                            <form action="<?= base_url('inventario/vaciar') ?>" method="POST" onsubmit="return confirm('¿Seguro que deseas vaciar todos los registros del inventario masivo? Esta acción no se puede deshacer.');">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-outline-danger btn-sm" title="Vaciar tabla">
                                    <i class="fa-solid fa-trash-can me-1"></i> Vaciar
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- FILTRO UNIFICADO: ESTADO, TRASLADO, LÍMITE Y BÚSQUEDA -->
                <form method="GET" action="<?= base_url('inventario') ?>" class="row g-2 align-items-center border-top pt-3">
                    <!-- Filtro por Estado de Intervención -->
                    <div class="col-12 col-md-3 col-lg-3">
                        <select name="filtro" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">Todos los Estados</option>
                            <option value="agregados" <?= ($filtro === 'agregados' || $filtro === 'intervenidos') ? 'selected' : '' ?>>
                                Ya agregados al sistema (<?= number_format((int)($statsInventario['intervenidos'] ?? 0)) ?>)
                            </option>
                            <option value="pendientes" <?= ($filtro === 'pendientes') ? 'selected' : '' ?>>
                                Pendientes por ingresar (<?= number_format((int)($statsInventario['pendientes'] ?? 0)) ?>)
                            </option>
                        </select>
                    </div>

                    <!-- Filtro por Número de Traslado -->
                    <div class="col-12 col-md-3 col-lg-2">
                        <select name="traslado" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">Todos los traslados</option>
                            <option value="sin_traslado" <?= ($traslado === 'sin_traslado') ? 'selected' : '' ?>>
                                ⚠️ Sin Traslado (Respaldo BD)
                            </option>
                            <?php foreach ($trasladosDisponibles as $t): ?>
                                <option value="<?= esc($t) ?>" <?= ($traslado === $t) ? 'selected' : '' ?>>
                                    Traslado <?= esc($t) ?>
                                </option>
                            <?php endforeach; ?>
                            <?php if (!empty($traslado) && $traslado !== 'sin_traslado' && !in_array($traslado, $trasladosDisponibles, true)): ?>
                                <option value="<?= esc($traslado) ?>" selected>Traslado <?= esc($traslado) ?></option>
                            <?php endif; ?>
                        </select>
                    </div>

                    <!-- Filtro por Límite de Registros -->
                    <div class="col-6 col-md-2 col-lg-2">
                        <select name="limite" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="50" <?= ($limite === 50) ? 'selected' : '' ?>>Ver 50 filas</option>
                            <option value="100" <?= ($limite === 100) ? 'selected' : '' ?>>Ver 100 filas</option>
                            <option value="250" <?= ($limite === 250) ? 'selected' : '' ?>>Ver 250 filas</option>
                            <option value="500" <?= ($limite === 500) ? 'selected' : '' ?>>Ver 500 filas</option>
                            <option value="1000" <?= ($limite === 1000) ? 'selected' : '' ?>>Ver 1000 filas</option>
                        </select>
                    </div>

                    <!-- Input de Búsqueda de texto -->
                    <div class="col-12 col-md-4 col-lg-3">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light text-muted"><i class="fa-solid fa-magnifying-glass"></i></span>
                            <input type="text" name="buscar" class="form-control form-control-sm" placeholder="Buscar por Placa, Serial, Ref..." value="<?= esc($busqueda) ?>">
                        </div>
                    </div>

                    <!-- Botones de Acción -->
                    <div class="col-6 col-md-2 col-lg-2 d-flex gap-2">
                        <button type="submit" class="btn btn-secondary btn-sm flex-fill fw-semibold">
                            <i class="fa-solid fa-filter me-1"></i> Filtrar
                        </button>
                        <?php if (!empty($busqueda) || !empty($filtro) || !empty($traslado) || $limite !== 250): ?>
                            <a href="<?= base_url('inventario') ?>" class="btn btn-outline-danger btn-sm" title="Restablecer todos los filtros">
                                <i class="fa-solid fa-xmark"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped align-middle mb-0 text-nowrap table-inventario-sticky">
                        <thead class="table-dark">
                            <tr>
                                <th class="ps-3">ID</th>
                                <th>Núm. Traslado</th>
                                <th>Identificador 1 (Placa)</th>
                                <th>Identificador 2 (Serial)</th>
                                <th>Ref. Principal</th>
                                <th>Descripción</th>
                                <th>Zona / Sede</th>
                                <th>Ubicación</th>
                                <th>Estatus</th>
                                <th>Intervenido en Sistema</th>
                                <th>Observaciones</th>
                                <th class="text-end pe-3">Fecha Cargue</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($registros)): ?>
                                <?php foreach ($registros as $r): ?>
                                    <tr>
                                        <td class="ps-3 text-muted"><?= $r['id'] ?></td>
                                        <td>
                                            <?php if (!empty($r['num_traslado'])): ?>
                                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle fw-bold">
                                                    <i class="fa-solid fa-truck-ramp-box me-1"></i><?= esc($r['num_traslado']) ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted small">—</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><strong><?= esc($r['identificador_1'] ?: ($r['placa_id'] ?: '—')) ?></strong></td>
                                        <td><code><?= esc($r['identificador_2'] ?: ($r['serial'] ?: '—')) ?></code></td>
                                        <td><?= esc($r['ref_principal'] ?: ($r['modelo'] ?: '—')) ?></td>
                                        <td><span class="badge bg-secondary"><?= esc($r['descripcion'] ?: ($r['tipo_equipo'] ?: '—')) ?></span></td>
                                        <td><?= esc($r['zona_origen'] ?: '—') ?></td>
                                        <td><?= esc($r['ubicacion_origen'] ?: ($r['ubicacion'] ?: '—')) ?></td>
                                        <td>
                                            <?php 
                                                $estatusVal = $r['estado'] ?: ($r['verificado'] ?: 'Cargado');
                                                $esCargado = (strtolower(trim((string)$estatusVal)) === 'cargado');
                                            ?>
                                            <?php if ($esCargado): ?>
                                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle fw-bold">
                                                    <i class="fa-solid fa-cloud-arrow-up me-1"></i>Cargado
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-info-subtle text-dark border"><?= esc($estatusVal) ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ((int)($r['intervenido'] ?? 0) === 1): ?>
                                                <div>
                                                    <span class="badge bg-success-subtle text-success border border-success-subtle fw-bold">
                                                        <i class="fa-solid fa-circle-check me-1"></i><?= esc($r['modulo_intervencion'] ?: 'Agregado al Sistema') ?>
                                                    </span>
                                                    <?php if (!empty($r['analista_intervencion'])): ?>
                                                        <div class="small text-muted mt-1">
                                                            <i class="fa-solid fa-user-check me-1 text-primary"></i><?= esc($r['analista_intervencion']) ?>
                                                        </div>
                                                    <?php endif; ?>
                                                    <?php if (!empty($r['fecha_intervencion'])): ?>
                                                        <div class="small text-muted">
                                                            <i class="fa-solid fa-calendar-day me-1"></i><?= date('d/m/Y H:i', strtotime($r['fecha_intervencion'])) ?>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            <?php else: ?>
                                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle">
                                                    <i class="fa-solid fa-hourglass-start me-1"></i>Pendiente
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="small text-muted" style="max-width: 180px; overflow: hidden; text-overflow: ellipsis;">
                                            <?= esc($r['observaciones'] ?: '—') ?>
                                        </td>
                                        <td class="text-end pe-3 text-muted small">
                                            <?= !empty($r['created_at']) ? date('d/m/Y H:i', strtotime($r['created_at'])) : '—' ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="12" class="text-center text-muted py-5">
                                        <i class="fa-solid fa-magnifying-glass display-6 text-muted mb-3 d-block"></i>
                                        No se encontraron registros de inventario con los criterios seleccionados.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php if (!empty($registros)): ?>
                <div class="card-footer bg-light py-2 px-3 d-flex flex-wrap justify-content-between align-items-center">
                    <small class="text-muted">
                        Mostrando <strong><?= number_format(count($registros)) ?></strong> de <strong><?= number_format($totalFiltrados) ?></strong> registros coincidentes (Total en BD: <strong><?= number_format($totalRegistros) ?></strong>)
                    </small>
                    <small class="text-muted">
                        <i class="fa-solid fa-desktop me-1"></i> Vista de Inventario en Ventana Completa
                    </small>
                </div>
            <?php endif; ?>
        </div>

    </div>
</div>

<!-- MODAL DE CONFIRMACIÓN CON NÚMERO DE TRASLADO -->
<div class="modal fade" id="modalConfirmarCargue" tabindex="-1" aria-labelledby="modalCargueLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white py-3">
                <h5 class="modal-title fw-bold fs-6" id="modalCargueLabel">
                    <i class="fa-solid fa-truck-ramp-box me-2"></i>Confirmación de Cargue Masivo
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body p-4">
                <div class="alert alert-primary-subtle border border-primary-subtle d-flex align-items-center mb-4 p-3 rounded">
                    <i class="fa-solid fa-file-excel fs-2 text-success me-3" id="modal-archivo-icono"></i>
                    <div class="overflow-hidden">
                        <div class="fw-bold text-dark text-truncate" id="modal-archivo-nombre">archivo.xlsx</div>
                        <small class="text-muted" id="modal-archivo-tamano">0 KB</small>
                    </div>
                </div>

                <div class="mb-2">
                    <label for="input_num_traslado" class="form-label fw-bold text-dark">
                        Número de Traslado *
                    </label>
                    <div class="input-group">
                        <span class="input-group-text bg-light text-primary"><i class="fa-solid fa-hashtag"></i></span>
                        <input type="text" id="input_num_traslado" class="form-control form-control-lg fw-bold" placeholder="Ej: TRAS-2026-001 o N° de Guía" required autocomplete="off">
                    </div>
                    <div class="invalid-feedback text-danger small mt-1" id="error_num_traslado" style="display: none;">
                        <i class="fa-solid fa-circle-exclamation me-1"></i>El número de traslado es obligatorio para confirmar la subida.
                    </div>
                    <div class="form-text mt-2 text-muted small">
                        <i class="fa-solid fa-circle-info me-1 text-primary"></i>Ingresa el número de traslado o remisión con el que llegaron estos equipos para asociarlos al lote.
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light py-3">
                <button type="button" class="btn btn-outline-secondary px-3" data-bs-dismiss="modal">
                    <i class="fa-solid fa-xmark me-1"></i> Cancelar
                </button>
                <button type="button" class="btn btn-primary fw-bold px-4 shadow-sm" id="btnConfirmarSubida" onclick="confirmarYSubir()">
                    <i class="fa-solid fa-cloud-arrow-up me-2"></i> Confirmar y Subir al Sistema
                </button>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
let modalCargueInstance = null;

function mostrarNombreArchivo(input) {
    const label = document.getElementById('file-label');
    const icon = document.getElementById('upload-icon');
    const subtext = document.getElementById('file-subtext');
    if (input.files && input.files[0]) {
        const file = input.files[0];
        const ext = file.name.split('.').pop().toLowerCase();
        if (icon) {
            if (ext === 'xlsx' || ext === 'xls') {
                icon.className = 'fa-solid fa-file-excel display-4 text-success mb-3';
            } else {
                icon.className = 'fa-solid fa-file-csv display-4 text-primary mb-3';
            }
        }
        label.innerHTML = `<i class="fa-solid fa-circle-check text-success me-1"></i> Archivo seleccionado: <strong>${file.name}</strong> (${(file.size / 1024).toFixed(1)} KB)`;
        if (subtext) subtext.innerText = 'Archivo cargado y listo para ser procesado.';
    } else {
        label.innerText = 'Haz clic para seleccionar tu archivo Excel o CSV';
        if (icon) icon.className = 'fa-solid fa-file-excel display-4 text-success mb-3';
        if (subtext) subtext.innerText = 'Admite libros de Excel (.xlsx, .xls) y archivos delimitados (.csv, .txt).';
    }
}

function abrirModalTraslado() {
    const fileInput = document.getElementById('archivo_csv');
    if (!fileInput.files || !fileInput.files[0]) {
        alert('Por favor selecciona primero un archivo Excel (.xlsx, .xls) o CSV antes de continuar.');
        fileInput.click();
        return;
    }

    const file = fileInput.files[0];
    const ext = file.name.split('.').pop().toLowerCase();
    const modalIcon = document.getElementById('modal-archivo-icono');
    if (modalIcon) {
        if (ext === 'xlsx' || ext === 'xls') {
            modalIcon.className = 'fa-solid fa-file-excel fs-2 text-success me-3';
        } else {
            modalIcon.className = 'fa-solid fa-file-csv fs-2 text-primary me-3';
        }
    }
    document.getElementById('modal-archivo-nombre').innerText = file.name;
    document.getElementById('modal-archivo-tamano').innerText = (file.size / 1024).toFixed(1) + ' KB';

    const inputTraslado = document.getElementById('input_num_traslado');
    inputTraslado.classList.remove('is-invalid');
    document.getElementById('error_num_traslado').style.display = 'none';

    if (!modalCargueInstance) {
        modalCargueInstance = new bootstrap.Modal(document.getElementById('modalConfirmarCargue'));
    }
    modalCargueInstance.show();

    setTimeout(() => {
        inputTraslado.focus();
        inputTraslado.select();
    }, 450);
}

function confirmarYSubir() {
    const inputTraslado = document.getElementById('input_num_traslado');
    const valor = inputTraslado.value.trim();

    if (valor === '') {
        inputTraslado.classList.add('is-invalid');
        document.getElementById('error_num_traslado').style.display = 'block';
        inputTraslado.focus();
        return;
    }

    inputTraslado.classList.remove('is-invalid');
    document.getElementById('error_num_traslado').style.display = 'none';

    // Asignar al formulario oculto
    document.getElementById('num_traslado').value = valor;

    // Cambiar estado del botón modal y botón principal
    const btnModal = document.getElementById('btnConfirmarSubida');
    btnModal.disabled = true;
    btnModal.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Subiendo al sistema...';

    const btnPrincipal = document.getElementById('btnProcesar');
    btnPrincipal.disabled = true;
    btnPrincipal.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i>Procesando...';

    // Enviar el formulario
    document.getElementById('formCargueMasivo').submit();
}

document.addEventListener('DOMContentLoaded', function() {
    const inputTraslado = document.getElementById('input_num_traslado');
    if (inputTraslado) {
        inputTraslado.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                confirmarYSubir();
            }
        });
    }

    const dropZone = document.getElementById('upload-box-container');
    const fileInput = document.getElementById('archivo_csv');
    if (dropZone && fileInput) {
        ['dragenter', 'dragover'].forEach(eventName => {
            dropZone.addEventListener(eventName, (e) => {
                e.preventDefault();
                e.stopPropagation();
                dropZone.classList.add('border-success', 'bg-light');
            }, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, (e) => {
                e.preventDefault();
                e.stopPropagation();
                dropZone.classList.remove('border-success', 'bg-light');
            }, false);
        });

        dropZone.addEventListener('drop', (e) => {
            const dt = e.dataTransfer;
            if (dt && dt.files && dt.files.length > 0) {
                fileInput.files = dt.files;
                mostrarNombreArchivo(fileInput);
            }
        }, false);
    }
});
</script>
<?= $this->endSection() ?>
