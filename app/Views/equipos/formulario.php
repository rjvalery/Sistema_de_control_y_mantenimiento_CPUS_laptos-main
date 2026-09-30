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
                            <label class="form-label fw-bold" for="foto_equipo" id="lbl_evidencia">
                                <i class="fa-solid fa-camera me-1 text-primary"></i> Evidencia Fotográfica <span id="foto_asterisco" class="text-danger">*</span>
                            </label>
                            
                            <input type="file" id="foto_equipo" name="foto_equipo" class="form-control" accept="image/*" capture="environment">
                            
                            <div id="upload-label" class="form-text text-muted mt-1">
                                Toma una foto con la cámara del dispositivo o selecciona una imagen (JPG, PNG, WEBP, máx 4MB).
                            </div>

                            <div id="preview-container" class="mt-3 text-center d-none">
                                <div class="position-relative d-inline-block">
                                    <img id="preview" src="#" alt="Previsualización de la foto" class="img-thumbnail shadow-sm rounded" style="max-height: 240px; max-width: 100%;">
                                    <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 m-1 rounded-circle shadow" style="width: 28px; height: 28px; padding: 0;" title="Quitar foto" onclick="limpiarPrevisualizacion()">
                                        <i class="fa-solid fa-xmark"></i>
                                    </button>
                                </div>
                                <div class="mt-1 small text-success fw-semibold">
                                    <i class="fa-solid fa-circle-check me-1"></i> Foto seleccionada lista para enviar
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
function ocultarTodos() {
    ['seccion_diagnostico', 'seccion_intervencion', 'seccion_novedad', 'seccion_baja'].forEach(id => {
        const sec = document.getElementById(id);
        sec.classList.add('d-none');
        sec.querySelectorAll('select, input, textarea').forEach(inp => {
            inp.required = false;
            inp.value = '';
        });
    });
}

function evaluarGestion(valor) {
    ocultarTodos();
    const fotoPrincipal = document.getElementById('foto_equipo');
    const fotoAsterisco = document.getElementById('foto_asterisco');
    const uploadLabel   = document.getElementById('upload-label');

    if (valor === 'Diagnostico') {
        document.getElementById('seccion_diagnostico').classList.remove('d-none');
        document.getElementById('energiza').required = true;
        document.getElementById('da_video').required = true;
        document.getElementById('estado_actual').required = true;
    } else if (valor === 'Intervencion') {
        document.getElementById('seccion_intervencion').classList.remove('d-none');
        document.getElementById('que_va_intervenir').required = true;
        document.getElementById('origen_pieza').required = true;
    } else if (valor === 'Novedad') {
        document.getElementById('seccion_novedad').classList.remove('d-none');
        document.getElementById('descripcion_novedad').required = true;
    } else if (valor === 'Baja') {
        document.getElementById('seccion_baja').classList.remove('d-none');
        document.getElementById('motivo_baja').required = true;

        const selectDestino = document.getElementById('ubicacion_destino');
        if (selectDestino && !selectDestino.value) {
            selectDestino.value = 'Sala Bajas';
        }
    }

    // Si la gestión es Baja, la foto es opcional; en otros tipos sigue siendo obligatoria
    if (valor === 'Baja') {
        if (fotoPrincipal) {
            fotoPrincipal.required = false;
        }
        if (fotoAsterisco) {
            fotoAsterisco.innerHTML = '<span class="badge bg-secondary-subtle text-secondary fw-normal ms-1">(Opcional para Baja)</span>';
        }
        if (uploadLabel) {
            uploadLabel.textContent = 'Para equipos en baja la foto es opcional. Puedes guardar sin anexar imagen.';
        }
    } else {
        if (fotoPrincipal) {
            fotoPrincipal.required = true;
        }
        if (fotoAsterisco) {
            fotoAsterisco.innerHTML = '<span class="text-danger">*</span>';
        }
        if (uploadLabel) {
            uploadLabel.textContent = 'Toma una foto con la cámara del dispositivo o selecciona una imagen (JPG, PNG, WEBP, máx 4MB).';
        }
    }
}

// Previsualización ligera nativa en JavaScript (URL.createObjectURL)
function inicializarPrevisualizacion() {
    const inputFoto = document.getElementById('foto_equipo');
    const previewContainer = document.getElementById('preview-container');
    const preview = document.getElementById('preview');

    if (inputFoto) {
        inputFoto.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                const file = this.files[0];
                if (preview) {
                    preview.src = URL.createObjectURL(file);
                }
                if (previewContainer) {
                    previewContainer.classList.remove('d-none');
                }
            } else {
                limpiarPrevisualizacion();
            }
        });
    }
}

function limpiarPrevisualizacion() {
    const inputFoto = document.getElementById('foto_equipo');
    const previewContainer = document.getElementById('preview-container');
    const preview = document.getElementById('preview');
    if (inputFoto) inputFoto.value = '';
    if (preview) preview.src = '#';
    if (previewContainer) previewContainer.classList.add('d-none');
}

function mostrarModal(icono, titulo, mensaje) {
    document.getElementById('modalIcono').innerHTML = icono;
    document.getElementById('modalTitulo').innerText = titulo;
    document.getElementById('modalMensaje').innerText = mensaje;
    new bootstrap.Modal(document.getElementById('modalEmergente')).show();
}

async function enviarFormulario() {
    const form = document.getElementById('formGarantias');
    const tipoGestion = document.getElementById('tipo_gestion')?.value;
    const principal = document.getElementById('foto_equipo');

    // Si es Baja, la foto no es obligatoria; si no es Baja y no se ha seleccionado foto, se exige
    if (tipoGestion === 'Baja') {
        if (principal) principal.required = false;
    } else {
        if (principal && (!principal.files || !principal.files[0])) {
            principal.required = true;
        }
    }

    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }

    const btnSubmit = document.getElementById('btnGuardar');
    btnSubmit.disabled = true;
    btnSubmit.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i> Guardando...';

    const formData = new FormData(form);

    if (tipoGestion === 'Baja') {
        const sdBaja = document.getElementById('serial_disco_baja')?.value?.trim();
        if (sdBaja) {
            formData.set('serial_disco', sdBaja);
        }
    }

    fetch('<?= base_url('equipos/guardar') ?>', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(r => r.json())
    .then(data => {
        if (data.status === 'success') {
            mostrarModal('<i class="fa-solid fa-circle-check text-success"></i>', '¡Guardado con éxito!', data.message || 'El registro se ha guardado correctamente.');
            form.reset();
            limpiarPrevisualizacion();
            if (principal) principal.required = true;
            const asterisco = document.getElementById('foto_asterisco');
            if (asterisco) asterisco.innerHTML = '<span class="text-danger">*</span>';
            ocultarTodos();
            const feedbackDiv = document.getElementById('inventario_feedback');
            if (feedbackDiv) feedbackDiv.innerHTML = '';
        } else {
            mostrarModal('<i class="fa-solid fa-circle-xmark text-danger"></i>', 'Error al guardar', data.message || 'No se pudo guardar el registro.');
        }
    })
    .catch(() => {
        mostrarModal('<i class="fa-solid fa-triangle-exclamation text-warning"></i>', 'Error de red', 'Ocurrió un error procesando el registro.');
    })
    .finally(() => {
        btnSubmit.disabled = false;
        btnSubmit.innerHTML = '<i class="fa-solid fa-floppy-disk me-1"></i> Guardar Registro';
    });
}

// Sincronización en tiempo real con inventario al tipear ID o Serial
document.addEventListener('DOMContentLoaded', function() {
    inicializarPrevisualizacion();

    const placaInput = document.getElementById('placa_id');
    const spinnerPlaca = document.getElementById('spinner_placa');
    const feedbackDiv = document.getElementById('inventario_feedback');
    let debounceTimer = null;

    const inputTraslado = document.getElementById('num_traslado');
    if (inputTraslado) {
        inputTraslado.addEventListener('input', function() {
            this.dataset.manual = this.value.trim() !== '' ? 'true' : 'false';
        });
    }

    if (placaInput) {
        placaInput.addEventListener('input', function() {
            const val = this.value.trim();
            clearTimeout(debounceTimer);

            if (val.length < 3) {
                if (feedbackDiv) feedbackDiv.innerHTML = '';
                if (spinnerPlaca) spinnerPlaca.classList.add('d-none');
                if (inputTraslado && inputTraslado.dataset.autofilled === 'true' && inputTraslado.dataset.manual !== 'true') {
                    inputTraslado.value = '';
                    inputTraslado.dataset.autofilled = 'false';
                }
                return;
            }

            if (spinnerPlaca) spinnerPlaca.classList.remove('d-none');

            debounceTimer = setTimeout(() => {
                fetch('<?= base_url('inventario/buscar-equipo') ?>?query=' + encodeURIComponent(val))
                    .then(r => r.json())
                    .then(res => {
                        if (spinnerPlaca) spinnerPlaca.classList.add('d-none');
                        if (!feedbackDiv) return;

                        if (res.encontrado && res.equipo) {
                            const eq = res.equipo;

                            // Autocompletar número de traslado si no fue digitado manualmente por el analista
                            if (inputTraslado && inputTraslado.dataset.manual !== 'true' && eq.num_traslado) {
                                inputTraslado.value = eq.num_traslado;
                                inputTraslado.dataset.autofilled = 'true';
                                inputTraslado.classList.add('is-valid');
                                setTimeout(() => inputTraslado.classList.remove('is-valid'), 2500);
                            }

                            const trasladoBadge = eq.num_traslado ? `
                                <div class="mt-1 small text-primary fw-bold">
                                    <i class="fa-solid fa-truck-ramp-box me-1"></i>Traslado de Inventario: <span class="badge bg-primary fs-7 px-2 py-1">${escapeHtml(eq.num_traslado)}</span>
                                </div>
                            ` : '';

                            if (eq.intervenido) {
                                feedbackDiv.innerHTML = `
                                    <div class="alert alert-warning py-2 px-3 mb-0 small border-warning shadow-sm">
                                        <div class="d-flex align-items-center mb-1">
                                            <i class="fa-solid fa-triangle-exclamation text-warning me-2 fs-5"></i>
                                            <strong>Equipo en Sistema — ¡YA INTERVENIDO PREVIAMENTE!</strong>
                                        </div>
                                        <div class="text-dark">
                                            <strong>Serial:</strong> <code>${escapeHtml(eq.serial || 'N/A')}</code> | 
                                            <strong>Placa:</strong> <code>${escapeHtml(eq.placa_id || 'N/A')}</code> | 
                                            <strong>Equipo:</strong> ${escapeHtml(eq.marca || '')} ${escapeHtml(eq.modelo || '')}
                                        </div>
                                        ${trasladoBadge}
                                        <div class="text-muted mt-1 small">
                                            <i class="fa-regular fa-clock me-1"></i>Intervenido el <strong>${escapeHtml(eq.fecha_intervencion || '')}</strong> en módulo <strong>${escapeHtml(eq.modulo_intervencion || '')}</strong> por <strong>${escapeHtml(eq.analista_intervencion || 'N/A')}</strong>.
                                        </div>
                                    </div>
                                `;
                            } else {
                                feedbackDiv.innerHTML = `
                                    <div class="alert alert-success py-2 px-3 mb-0 small border-success shadow-sm">
                                        <div class="d-flex align-items-center mb-1">
                                            <i class="fa-solid fa-circle-check text-success me-2 fs-5"></i>
                                            <strong>Equipo sincronizado con Inventario General</strong>
                                        </div>
                                        <div class="text-dark">
                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle me-1">${escapeHtml(eq.tipo_equipo || 'Equipo')}</span>
                                            <strong>Serial:</strong> <code class="text-dark fw-bold">${escapeHtml(eq.serial || 'N/A')}</code> | 
                                            <strong>Placa:</strong> <code class="text-dark fw-bold">${escapeHtml(eq.placa_id || 'N/A')}</code>
                                        </div>
                                        <div class="text-muted mt-1">
                                            <strong>Marca / Modelo:</strong> ${escapeHtml(eq.marca || '')} ${escapeHtml(eq.modelo || '')} | 
                                            <strong>Ubicación:</strong> ${escapeHtml(eq.ubicacion || 'Sede')}
                                        </div>
                                        ${trasladoBadge}
                                        <div class="text-success fw-semibold mt-1">
                                            <i class="fa-solid fa-arrow-down-long me-1"></i> Se marcará como intervenido y se descontará del inventario pendiente al guardar.
                                        </div>
                                    </div>
                                `;
                            }
                        } else {
                            feedbackDiv.innerHTML = `
                                <div class="alert alert-light border py-1 px-2 mb-0 small text-muted">
                                    <i class="fa-solid fa-info-circle me-1 text-secondary"></i> No registrado en cargue masivo previo. Se registrará como equipo nuevo.
                                </div>
                            `;
                        }
                    })
                    .catch(() => {
                        if (spinnerPlaca) spinnerPlaca.classList.add('d-none');
                    });
            }, 350);
        });
    }

    function escapeHtml(str) {
        if (!str) return '';
        return String(str).replace(/[&<>"']/g, function(m) {
            return {'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'}[m];
        });
    }
});
</script>
<?= $this->endSection() ?>