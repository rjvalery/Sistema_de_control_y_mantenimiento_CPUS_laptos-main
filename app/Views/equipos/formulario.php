<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Diagnóstico y Garantías CPUs<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="row justify-content-center">
    <div class="col-md-10 col-lg-8">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-primary text-white py-3">
                <h5 class="mb-0 fs-6"><i class="fa-solid fa-microchip me-2"></i>Formulario CPU Garantías</h5>
            </div>
            <div class="card-body p-4">

                <form id="formGarantias" enctype="multipart/form-data" onsubmit="event.preventDefault(); return false;">
                    <?= csrf_field() ?>
                    <div class="row g-3">
                        
                        <!-- 1. Analista y Traslado -->
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
                            <input type="text" name="num_traslado" id="num_traslado" class="form-control" placeholder="Ej: 123456" required>
                        </div>

                        <!-- 2. Placa e ID -->
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Placa ID o Serial del equipo *</label>
                            <div class="input-group">
                                <input type="text" name="placa_id" id="placa_id" class="form-control" placeholder="Ej: B123456 o Serial" required autocomplete="off">
                                <span class="input-group-text d-none" id="spinner_placa">
                                    <i class="fa-solid fa-spinner fa-spin text-primary"></i>
                                </span>
                            </div>
                            <div id="inventario_feedback" class="mt-2"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Tipo de gestión *</label>
                            <select name="tipo_gestion" id="tipo_gestion" class="form-select" required onchange="evaluarGestion(this.value)">
                                <option value="" disabled selected>-- Seleccione --</option>
                                <option value="Diagnostico">Diagnóstico</option>
                                <option value="Intervencion">Intervención</option>
                                <option value="Novedad">Novedad</option>
                                <option value="Baja">Baja</option>
                                <option value="IT">IT</option>
                            </select>
                        </div>

                        <!-- CAMPOS CONDICIONALES -->
                        
                        <!-- A. DIAGNÓSTICO -->
                        <div id="seccion_diagnostico" class="col-12 d-none p-3 bg-light rounded border">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">¿Energiza? *</label>
                                    <select name="energiza" id="energiza" class="form-select">
                                        <option value="">-- Seleccione --</option>
                                        <option value="Si">Si</option>
                                        <option value="No">No</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">¿Da video? *</label>
                                    <select name="da_video" id="da_video" class="form-select">
                                        <option value="">-- Seleccione --</option>
                                        <option value="Si">Si</option>
                                        <option value="No">No</option>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-bold">Estado actual del equipo *</label>
                                    <select name="estado_actual" id="estado_actual" class="form-select">
                                        <option value="">-- Seleccione --</option>
                                        <option value="Funcional">Funcional</option>
                                        <option value="Garantia">Garantía</option>
                                        <option value="Pendiente Repuesto">Pendiente Repuesto</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- B. INTERVENCIÓN -->
                        <div id="seccion_intervencion" class="col-12 d-none p-3 bg-light rounded border">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">¿Qué va a intervenir? *</label>
                                    <select name="que_va_intervenir" id="que_va_intervenir" class="form-select">
                                        <option value="">-- Seleccione --</option>
                                        <option value="Disco">Disco</option>
                                        <option value="RAM">RAM</option>
                                        <option value="Pila de BIOS">Pila de BIOS</option>
                                        <option value="Disco;RAM">Disco;RAM</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Origen de pieza *</label>
                                    <select name="origen_pieza" id="origen_pieza" class="form-select">
                                        <option value="">-- Seleccione --</option>
                                        <option value="Nuevo">Nuevo</option>
                                        <option value="Reacondicionado">Reacondicionado</option>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-bold">Serial del disco</label>
                                    <input type="text" name="serial_disco" id="serial_disco" class="form-control" placeholder="Escriba el serial...">
                                </div>
                            </div>
                        </div>

                        <!-- C. NOVEDAD -->
                        <div id="seccion_novedad" class="col-12 d-none p-3 bg-light rounded border">
                            <label class="form-label fw-bold">Escriba la novedad del equipo *</label>
                            <textarea name="descripcion_novedad" id="descripcion_novedad" class="form-control" rows="3" placeholder="Detalle la novedad..."></textarea>
                        </div>

                        <!-- D. BAJA -->
                        <div id="seccion_baja" class="col-12 d-none p-3 bg-light rounded border border-danger-subtle">
                            <div class="fw-bold text-danger mb-2">
                                <i class="fa-solid fa-trash-can me-2"></i>Información de Baja del Equipo
                            </div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Motivo Baja *</label>
                                    <select name="motivo_baja" id="motivo_baja" class="form-select">
                                        <option value="">-- Seleccione --</option>
                                        <option value="Obsoleto">Obsoleto</option>
                                        <option value="No energiza">No energiza</option>
                                        <option value="Baja total">Baja total</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Serial del disco duro</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white text-muted"><i class="fa-solid fa-hard-drive"></i></span>
                                        <input type="text" name="serial_disco_baja" id="serial_disco_baja" class="form-control" placeholder="Escriba el serial del disco duro...">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- E. GESTIÓN IT -->
                        <div id="seccion_it" class="col-12 d-none p-3 bg-light rounded border border-primary-subtle">
                            <div class="fw-bold text-primary mb-2">
                                <i class="fa-solid fa-laptop-code me-2"></i>Información de Gestión IT
                            </div>
                            <label class="form-label fw-bold">Escriba la información de la gestión IT *</label>
                            <textarea name="descripcion_it" id="descripcion_it" class="form-control" rows="3" placeholder="Detalle la información o procedimiento realizado en IT..."></textarea>
                        </div>

                        <!-- 3. Ubicación Destino -->
                        <div class="col-12">
                            <label class="form-label fw-bold">Ubicación destino *</label>
                            <select name="ubicacion_destino" id="ubicacion_destino" class="form-select" required>
                                <option value="" disabled selected>-- Seleccione Ubicación --</option>
                                <option value="Sala Dban">Sala Dban</option>
                                <option value="Sala Garantias">Sala Garantías</option>
                                <option value="Almacen">Almacén</option>
                                <option value="Sala Bajas">Sala Bajas</option>
                            </select>
                        </div>

                        <!-- 4. Adjuntar / Tomar Foto -->
                        <div class="col-12" id="contenedor_foto">
                            <label class="form-label fw-bold" id="lbl_evidencia">
                                <i class="fa-solid fa-camera me-1 text-primary"></i> Evidencia Fotográfica <span id="foto_asterisco" class="text-danger">*</span>
                            </label>

                            <div class="p-3 border rounded text-center bg-light" style="border-style: dashed !important; border-width: 2px !important; border-color: #0d6efd !important;">
                                <!-- Input principal para validación del formulario -->
                                <input type="file" name="foto_equipo" id="foto_equipo" 
                                       style="position: absolute; opacity: 0; width: 0.1px; height: 0.1px; overflow: hidden;" 
                                       accept="image/*" required>

                                <div class="d-flex flex-wrap justify-content-center gap-3 mb-2">
                                    <!-- Botón Cámara con input nativo transparente superpuesto (100% área táctil directa) -->
                                    <div class="position-relative d-inline-block">
                                        <button type="button" class="btn btn-primary fw-bold py-2 px-3 shadow-sm" style="pointer-events: none;">
                                            <i class="fa-solid fa-camera me-2"></i> Abrir Cámara
                                        </button>
                                        <input type="file" id="foto_camara_diagnostico" accept="image/*" capture="environment" 
                                               style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; opacity: 0; cursor: pointer; z-index: 10;" 
                                               onchange="manejarSeleccionFoto(this)">
                                    </div>

                                    <!-- Botón Galería con input nativo transparente superpuesto (100% área táctil directa) -->
                                    <div class="position-relative d-inline-block">
                                        <button type="button" class="btn btn-outline-secondary fw-bold py-2 px-3 shadow-sm" style="pointer-events: none;">
                                            <i class="fa-solid fa-images me-2"></i> Galería / Archivos
                                        </button>
                                        <input type="file" id="foto_galeria_diagnostico" accept="image/*" 
                                               style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; opacity: 0; cursor: pointer; z-index: 10;" 
                                               onchange="manejarSeleccionFoto(this)">
                                    </div>
                                </div>

                                <div id="upload-label" class="form-text mt-1 text-muted fw-semibold">
                                    Toca un botón para activar la cámara o seleccionar de la galería.
                                </div>

                                <!-- Selector directo de respaldo para máxima compatibilidad -->
                                <div class="mt-2 text-center">
                                    <a href="javascript:void(0)" class="text-decoration-none small text-muted" onclick="document.getElementById('selector_respaldo').classList.toggle('d-none')">
                                        <i class="fa-solid fa-sliders me-1"></i> ¿Problemas en la tablet? Probar selector directo alternativo
                                    </a>
                                    <div id="selector_respaldo" class="mt-2 d-none">
                                        <input type="file" id="foto_respaldo" accept="image/*" class="form-control form-control-sm" onchange="manejarSeleccionFoto(this)">
                                    </div>
                                </div>

                                <div id="preview-container" class="mt-3 text-center d-none">
                                    <div class="position-relative d-inline-block">
                                        <img id="preview" src="#" alt="Previsualización de la foto" class="img-thumbnail shadow-sm rounded" style="max-height: 240px; max-width: 100%;">
                                        <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 m-1 rounded-circle shadow" style="width: 28px; height: 28px; padding: 0;" title="Quitar foto" onclick="limpiarPrevisualizacion()">
                                            <i class="fa-solid fa-xmark"></i>
                                        </button>
                                    </div>
                                    <div class="mt-2">
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1">
                                            <i class="fa-solid fa-circle-check me-1"></i> Foto lista para enviar
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 mt-4">
                            <button type="button" class="btn btn-primary w-100 py-2 fs-6 fw-bold shadow-sm" id="btnGuardar" onclick="enviarFormulario()">
                                <i class="fa-solid fa-floppy-disk me-1"></i> Guardar Registro
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
        <div class="modal-content text-center p-3">
            <div class="modal-body">
                <div id="modalIcono" class="display-4 mb-2"></div>
                <h5 class="modal-title fw-bold mb-2" id="modalTitulo"></h5>
                <p class="text-muted small mb-3" id="modalMensaje"></p>
                <button type="button" class="btn btn-primary w-100" data-bs-dismiss="modal">Aceptar</button>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    window.AppUrls = {
        buscarEquipo: '<?= base_url('inventario/buscar-equipo') ?>',
        guardarEquipo: '<?= base_url('equipos/guardar') ?>'
    };
    function evaluarGestion(valor) {
        if (typeof cambiarTipoGestion === 'function') {
            cambiarTipoGestion();
        }
    }
</script>
<script src="<?= base_url('assets/js/equipos_formulario.js') ?>"></script>
<?= $this->endSection() ?>