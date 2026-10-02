<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Intervención de Portátiles<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="row justify-content-center">
    <div class="col-md-10 col-lg-8">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-primary text-white py-3">
                <h5 class="mb-0 fs-6"><i class="fa-solid fa-laptop me-2"></i>Diagnóstico e Intervención de Portátiles</h5>
            </div>
            <div class="card-body p-4">

                <form id="formPortatiles" onsubmit="event.preventDefault(); return false;">
                    <?= csrf_field() ?>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Nombre del analista *</label>
                            <?php if (session('usuario_rol') === 'analista'): ?>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-primary"><i class="fa-solid fa-user-check"></i></span>
                                    <input type="text" name="nombre_analista" id="nombre_analista" class="form-control bg-light fw-semibold" value="<?= esc(session('usuario_nombre')) ?>" readonly required>
                                </div>
                                <div class="form-text text-muted small"><i class="fa-solid fa-lock me-1 text-success"></i>Sincronizado automáticamente con tu sesión activa.</div>
                            <?php else: ?>
                                <select name="nombre_analista" id="nombre_analista" class="form-select" required>
                                    <option value="" disabled>-- Seleccione Analista --</option>
                                    <?php 
                                        $encontrado = false;
                                        foreach ($analistas as $a): 
                                            $esActual = (trim($a['nombre']) === trim(session('usuario_nombre') ?? ''));
                                            if ($esActual) $encontrado = true;
                                    ?>
                                        <option value="<?= esc($a['nombre']) ?>" <?= $esActual ? 'selected' : '' ?>>
                                            <?= esc($a['nombre']) ?> <?= $esActual ? '(Tu sesión)' : '' ?>
                                        </option>
                                    <?php endforeach; ?>
                                    <?php if (!$encontrado && !empty(session('usuario_nombre'))): ?>
                                        <option value="<?= esc(session('usuario_nombre')) ?>" selected><?= esc(session('usuario_nombre')) ?> (Tu sesión)</option>
                                    <?php endif; ?>
                                </select>
                                <div class="form-text text-muted small">Selecciona el analista o usa tu sesión actual.</div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Número de Traslado *</label>
                            <input type="text" name="numero_traslado" id="numero_traslado" class="form-control" placeholder="Ej: 123456" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Placa ID o Serial del equipo *</label>
                            <div class="input-group">
                                <input type="text" name="placa_id_equipo" id="placa_id_equipo" class="form-control" placeholder="Ej: B123456 o Serial" required autocomplete="off">
                                <span class="input-group-text d-none" id="spinner_placa">
                                    <i class="fa-solid fa-spinner fa-spin text-primary"></i>
                                </span>
                            </div>
                            <div id="inventario_feedback" class="mt-2"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Tipo de gestión *</label>
                            <select name="tipo_gestion" id="tipo_gestion" class="form-select" required>
                                <option value="Diagnostico" selected>Diagnóstico</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">¿Energiza? *</label>
                            <select name="energiza" id="energiza" class="form-select" required>
                                <option value="" disabled selected>-- Seleccione --</option>
                                <option value="Si">Si</option>
                                <option value="No">No</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">¿Da video? *</label>
                            <select name="da_video" id="da_video" class="form-select" required>
                                <option value="" disabled selected>-- Seleccione --</option>
                                <option value="Si">Si</option>
                                <option value="No">No</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">¿Realizó test Lenovo? *</label>
                            <select name="realizo_test_lenovo" id="realizo_test_lenovo" class="form-select" required>
                                <option value="" disabled selected>-- Seleccione --</option>
                                <option value="Si">Si</option>
                                <option value="No">No</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Estado actual del equipo *</label>
                            <select name="estado_actual_equipo" id="estado_actual_equipo" class="form-select" required onchange="evaluarEstadoEquipo(this.value)">
                                <option value="" disabled selected>-- Seleccione --</option>
                                <option value="Funcional">Funcional</option>
                                <option value="Garantia">Garantía</option>
                                <option value="Novedad">Novedad</option>
                                <option value="Pendiente repuesto">Pendiente repuesto</option>
                                <option value="Reparado">Reparado</option>
                                <option value="Baja">Baja</option>
                                <option value="Donacion">Donación</option>
                            </select>
                        </div>

                        <!-- SECCIÓN CONDICIONAL A: GARANTÍA -->
                        <div id="seccion_garantia" class="col-12 d-none">
                            <div class="p-3 bg-light rounded border border-warning-subtle">
                                <div class="fw-bold text-dark mb-2">
                                    <i class="fa-solid fa-shield-halved text-warning me-2"></i>Información de Garantía
                                </div>
                                <div class="row g-3">
                                    <div class="col-12">
                                        <label class="form-label fw-bold">Número de ticket *</label>
                                        <input type="text" name="numero_ticket" id="numero_ticket" class="form-control" placeholder="Ej: TCK-9988">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-bold">¿Por qué solicita garantía?</label>
                                        <textarea name="porque_solicita_garantia" id="porque_solicita_garantia" class="form-control" rows="2" placeholder="Motivo de la solicitud..."></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- SECCIÓN CONDICIONAL B: DIAGNÓSTICO / NOVEDAD / COMENTARIO -->
                        <div id="seccion_diagnostico" class="col-12 d-none">
                            <div class="p-3 bg-light rounded border border-primary-subtle" id="card_diagnostico">
                                <label class="form-label fw-bold text-dark" id="lbl_diagnostico">
                                    <i class="fa-solid fa-stethoscope text-primary me-2"></i>Diagnóstico del laptop intervenido
                                </label>
                                <textarea name="diagnostico_laptop_intervenido" id="diagnostico_laptop_intervenido" class="form-control" rows="2" placeholder="Describa el diagnóstico o detalle de la novedad..."></textarea>
                            </div>
                        </div>

                        <!-- SECCIÓN CONDICIONAL C: PENDIENTE REPUESTO (PIEZAS Y COMPONENTES) -->
                        <div id="seccion_repuesto" class="col-12 d-none">
                            <div class="p-3 bg-light rounded border border-info-subtle">
                                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                                    <div class="fw-bold text-dark">
                                        <i class="fa-solid fa-microchip text-info me-2"></i>Detalle de Repuestos y Piezas Requeridas
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-primary fw-semibold" onclick="agregarFilaPieza()">
                                        <i class="fa-solid fa-plus me-1"></i> Agregar otra pieza
                                    </button>
                                </div>
                                <div class="text-muted small mb-3">Puedes agregar múltiples repuestos con sus respectivos códigos FRU.</div>

                                <div id="contenedor_piezas" class="d-flex flex-column gap-2">
                                    <div class="row g-2 align-items-center fila-pieza">
                                        <div class="col-md-6">
                                            <div class="input-group">
                                                <span class="input-group-text bg-white text-muted"><i class="fa-solid fa-puzzle-piece"></i></span>
                                                <input type="text" name="indique_pieza[]" class="form-control" placeholder="Ej: Pantalla / Teclado / Batería">
                                            </div>
                                        </div>
                                        <div class="col-md-5">
                                            <div class="input-group">
                                                <span class="input-group-text bg-white text-muted"><i class="fa-solid fa-barcode"></i></span>
                                                <input type="text" name="indique_fru[]" class="form-control" placeholder="Ej: 5B20V12345">
                                            </div>
                                        </div>
                                        <div class="col-md-1 text-end text-md-center">
                                            <button type="button" class="btn btn-outline-danger btn-sm w-100 btn-eliminar-pieza" onclick="eliminarFilaPieza(this)" title="Eliminar fila" disabled>
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- SECCIÓN CONDICIONAL D: BAJA -->
                        <div id="seccion_baja" class="col-12 d-none">
                            <div class="p-3 bg-light rounded border border-danger-subtle">
                                <div class="fw-bold text-danger mb-2">
                                    <i class="fa-solid fa-trash-can me-2"></i>Información de Baja del Portátil
                                </div>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Motivo de baja *</label>
                                        <select name="motivo_baja" id="motivo_baja" class="form-select">
                                            <option value="" disabled selected>-- Seleccione motivo --</option>
                                            <option value="Obsoleto">Obsoleto</option>
                                            <option value="Baja total">Baja total</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Serial del disco</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-white text-muted"><i class="fa-solid fa-hard-drive"></i></span>
                                            <input type="text" name="serial_disco" id="serial_disco" class="form-control" placeholder="Ej: S3Z1NX0T123456">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- SECCIÓN CONDICIONAL E: REPARADO -->
                        <div id="seccion_reparado" class="col-12 d-none">
                            <div class="p-3 bg-light rounded border border-success-subtle">
                                <div class="fw-bold text-success mb-2">
                                    <i class="fa-solid fa-screwdriver-wrench me-2"></i>Información de Reparación del Equipo
                                </div>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">¿Quién realizó la reparación? *</label>
                                        <select name="reparado_por" id="reparado_por" class="form-select">
                                            <option value="" disabled selected>-- Seleccione quién reparó --</option>
                                            <option value="Analista">Analista</option>
                                            <option value="Lenovo">Lenovo</option>
                                        </select>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-bold">Comentario u Observaciones de la reparación</label>
                                        <textarea name="comentario_reparado" id="comentario_reparado" class="form-control" rows="2" placeholder="Detalle los trabajos realizados o comentarios de la reparación..."></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Separación de Evidencia Fotográfica -->
                        <div class="col-12">
                            <div class="alert alert-light border border-primary-subtle d-flex flex-wrap align-items-center justify-content-between p-3 rounded shadow-sm gap-2">
                                <div>
                                    <div class="fw-bold text-primary">
                                        <i class="fa-solid fa-camera me-2"></i> Módulo de Evidencia Fotográfica Independiente
                                    </div>
                                    <div class="small text-muted">
                                        La captura de fotos ahora se gestiona en su propio enlace para mayor velocidad y comodidad.
                                    </div>
                                </div>
                                <a href="<?= base_url('portatiles/evidencia') ?>" class="btn btn-outline-primary btn-sm text-nowrap fw-semibold">
                                    <i class="fa-solid fa-camera me-1"></i> Ir a Subir Evidencia
                                </a>
                            </div>
                        </div>

                        <div class="col-12 mt-4">
                            <button type="button" class="btn btn-primary w-100 py-2 fs-6 fw-bold" id="btnGuardar" onclick="enviarFormulario()">
                                <i class="fa-solid fa-floppy-disk me-1"></i> Guardar Registro de Portátil
                            </button>
                        </div>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>

<!-- MODAL EMERGENTE DE CONFIRMACIÓN -->
<div class="modal fade" id="modalEmergente" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content text-center p-3 shadow-lg border-0">
            <div class="modal-body">
                <div id="modalIcono" class="display-4 mb-2"></div>
                <h5 class="modal-title fw-bold mb-2" id="modalTitulo"></h5>
                <div class="text-muted small mb-3" id="modalMensaje"></div>
                
                <!-- Botones para estado de éxito -->
                <div id="modalBotonesExito" class="d-grid gap-2">
                    <a id="btnIrEvidencia" href="<?= base_url('portatiles/evidencia') ?>" class="btn btn-warning fw-bold py-2 shadow-sm">
                        <i class="fa-solid fa-camera me-1"></i> Subir Evidencia Fotográfica Ahora
                    </a>
                    <button type="button" class="btn btn-outline-secondary py-2" data-bs-dismiss="modal">
                        Registrar Otra Laptop
                    </button>
                </div>

                <!-- Botones para estado de error -->
                <div id="modalBotonesError" class="d-none">
                    <button type="button" class="btn btn-danger w-100 py-2 fw-semibold shadow-sm" data-bs-dismiss="modal">
                        <i class="fa-solid fa-xmark me-1"></i> Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    window.AppUrls = {
        buscarEquipo: '<?= base_url('inventario/buscar-equipo') ?>',
        guardarPortatiles: '<?= base_url('portatiles/guardar') ?>',
        evidenciaPortatiles: '<?= base_url('portatiles/evidencia') ?>'
    };
</script>
<script src="<?= base_url('assets/js/portatiles_formulario.js') ?>"></script>
<?= $this->endSection() ?>