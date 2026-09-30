<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Soplado de CPUs<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="row justify-content-center">
    <div class="col-md-10 col-lg-8">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-primary text-white py-3">
                <h5 class="mb-0 fs-6"><i class="fa-solid fa-wind me-2"></i>Soplado de CPUs - Registro de Mantenimiento</h5>
            </div>
            <div class="card-body p-4">

                <form id="formSoplado" onsubmit="event.preventDefault(); return false;">
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
                            <input type="text" name="num_traslado" id="num_traslado" class="form-control" placeholder="Ej: 123456" required>
                        </div>

                        <div class="col-12">
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
                            <label class="form-label fw-bold">¿Detecta disco? *</label>
                            <select name="detecta_disco" id="detecta_disco" class="form-select" required>
                                <option value="" disabled selected>-- Seleccione --</option>
                                <option value="Si">Si</option>
                                <option value="No">No</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">¿Ingresó a la BIOS? *</label>
                            <select name="ingreso_bios" id="ingreso_bios" class="form-select" required>
                                <option value="" disabled selected>-- Seleccione --</option>
                                <option value="Si">Si</option>
                                <option value="No">No</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">¿Se aplicó pasta térmica? *</label>
                            <select name="pasta_termica" id="pasta_termica" class="form-select" required>
                                <option value="" disabled selected>-- Seleccione --</option>
                                <option value="Si">Si</option>
                                <option value="No">No</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">La máquina contenía: *</label>
                            <select name="maquina_contenia" id="maquina_contenia" class="form-select" required>
                                <option value="" disabled selected>-- Seleccione --</option>
                                <option value="Cucaracha">Cucaracha</option>
                                <option value="Polvo">Polvo</option>
                                <option value="Papeles de comida">Papeles de comida</option>
                            </select>
                        </div>

                        <div class="col-12 d-none" id="contenedor_gel_cucarachas">
                            <label class="form-label fw-bold"><i class="fa-solid fa-shield-virus text-warning me-1"></i> ¿Se aplicó gel para cucarachas? *</label>
                            <select name="gel_cucarachas" id="gel_cucarachas" class="form-select">
                                <option value="" disabled selected>-- Seleccione --</option>
                                <option value="Si">Si</option>
                                <option value="No">No</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-bold"><i class="fa-solid fa-camera me-1 text-primary"></i> Evidencia Fotográfica *</label>
                            
                            <div class="p-3 border rounded text-center bg-light" style="border-style: dashed !important; border-width: 2px !important; border-color: #0d6efd !important;">
                                <!-- Input principal para validación del formulario -->
                                <input type="file" name="foto_equipo" id="foto_equipo" 
                                       style="position: absolute; opacity: 0; width: 0.1px; height: 0.1px; overflow: hidden;" 
                                       accept="image/*" required>

                                <div class="d-flex flex-wrap justify-content-center gap-3 mb-2">
                                    <!-- Botón Cámara con input nativo superpuesto -->
                                    <div class="position-relative d-inline-block">
                                        <button type="button" class="btn btn-primary fw-bold py-2 px-3 shadow-sm" style="pointer-events: none;">
                                            <i class="fa-solid fa-camera me-2"></i> Abrir Cámara
                                        </button>
                                        <input type="file" id="foto_camara_soplado" accept="image/*" capture="environment" 
                                               style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; opacity: 0; cursor: pointer; z-index: 10;" 
                                               onchange="manejarSeleccionFotoSoplado(this)">
                                    </div>

                                    <!-- Botón Galería con input nativo superpuesto -->
                                    <div class="position-relative d-inline-block">
                                        <button type="button" class="btn btn-outline-secondary fw-bold py-2 px-3 shadow-sm" style="pointer-events: none;">
                                            <i class="fa-solid fa-images me-2"></i> Galería / Archivos
                                        </button>
                                        <input type="file" id="foto_galeria_soplado" accept="image/*" 
                                               style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; opacity: 0; cursor: pointer; z-index: 10;" 
                                               onchange="manejarSeleccionFotoSoplado(this)">
                                    </div>
                                </div>

                                <div id="upload-label" class="form-text mt-1 text-muted fw-semibold">
                                    Toca un botón para activar la cámara o seleccionar de la galería.
                                </div>

                                <div class="mt-2 text-center">
                                    <a href="javascript:void(0)" class="text-decoration-none small text-muted" onclick="document.getElementById('selector_respaldo_soplado').classList.toggle('d-none')">
                                        <i class="fa-solid fa-sliders me-1"></i> ¿Problemas en la tablet? Probar selector directo alternativo
                                    </a>
                                    <div id="selector_respaldo_soplado" class="mt-2 d-none">
                                        <input type="file" id="foto_respaldo_soplado" accept="image/*" class="form-control form-control-sm" onchange="manejarSeleccionFotoSoplado(this)">
                                    </div>
                                </div>

                                <div id="preview-container" class="mt-3 text-center d-none">
                                    <img id="preview" src="#" alt="Vista previa" class="img-thumbnail shadow-sm rounded" style="max-height: 220px; max-width: 100%;">
                                    <div class="mt-2">
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1">
                                            <i class="fa-solid fa-circle-check me-1"></i> Foto lista para enviar
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 mt-4">
                            <button type="button" class="btn btn-primary w-100 py-2 fs-6 fw-bold" id="btnGuardar" onclick="enviarFormulario()">
                                <i class="fa-solid fa-floppy-disk me-1"></i> Guardar Registro de Soplado
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
// Manejo ultrarrápido de compresión y preparación de imagen
let fotoOptimBlob = null;
let optimizacionPromesa = null;

function manejarSeleccionFotoSoplado(input) {
    if (!input.files || !input.files[0]) return;
    const principal = document.getElementById('foto_equipo');
    if (principal) {
        principal.required = false;
        try {
            if (window.DataTransfer) {
                const dt = new DataTransfer();
                dt.items.add(input.files[0]);
                principal.files = dt.files;
            }
        } catch (e) {}
    }
    optimizarImagen(input);
}

function optimizarImagen(input) {
    const previewContainer = document.getElementById('preview-container');
    const preview = document.getElementById('preview');
    const uploadLabel = document.getElementById('upload-label');

    if (!input.files || !input.files[0]) {
        fotoOptimBlob = null;
        optimizacionPromesa = null;
        return;
    }

    const file = input.files[0];

    // 1. VISTA PREVIA INSTANTÁNEA (0 ms): el analista ve su foto de inmediato sin congelamientos
    const instantUrl = URL.createObjectURL(file);
    preview.src = instantUrl;
    previewContainer.classList.remove('d-none');
    uploadLabel.innerHTML = '<span class="spinner-border spinner-border-sm text-primary me-1"></span> Optimizando peso en segundo plano...';

    // 2. Compresión en segundo plano ultra-optimizada (Hardware / createImageBitmap ~50-100ms)
    optimizacionPromesa = (async () => {
        try {
            const maxDim = 1000;
            let canvas = document.createElement('canvas');
            let ctx = canvas.getContext('2d');
            let procesado = false;

            // Intentar decodificación y reescalado nativo por hardware (evita cargar megabytes en RAM)
            if ('createImageBitmap' in window) {
                try {
                    const bitmap = await createImageBitmap(file, { resizeWidth: maxDim, resizeQuality: 'medium' });
                    canvas.width = bitmap.width;
                    canvas.height = bitmap.height;
                    ctx.drawImage(bitmap, 0, 0);
                    bitmap.close();
                    procesado = true;
                } catch (e1) {
                    try {
                        const bitmap = await createImageBitmap(file);
                        let w = bitmap.width, h = bitmap.height;
                        if (w > h && w > maxDim) {
                            h = Math.round((h * maxDim) / w);
                            w = maxDim;
                        } else if (h > maxDim) {
                            w = Math.round((w * maxDim) / h);
                            h = maxDim;
                        }
                        canvas.width = w;
                        canvas.height = h;
                        ctx.drawImage(bitmap, 0, 0, w, h);
                        bitmap.close();
                        procesado = true;
                    } catch (e2) {
                        procesado = false;
                    }
                }
            }

            // Fallback a Image() si createImageBitmap no está disponible
            if (!procesado) {
                await new Promise((resolve) => {
                    const img = new Image();
                    img.onload = () => {
                        let w = img.width, h = img.height;
                        if (w > h && w > maxDim) {
                            h = Math.round((h * maxDim) / w);
                            w = maxDim;
                        } else if (h > maxDim) {
                            w = Math.round((w * maxDim) / h);
                            h = maxDim;
                        }
                        canvas.width = w;
                        canvas.height = h;
                        ctx.drawImage(img, 0, 0, w, h);
                        resolve();
                    };
                    img.onerror = () => resolve();
                    img.src = instantUrl;
                });
            }

            // Obtener blob ligero (~60-120 KB)
            const blob = await new Promise((resolve) => {
                canvas.toBlob((b) => resolve(b), 'image/jpeg', 0.65);
            });

            if (blob) {
                fotoOptimBlob = blob;
                uploadLabel.innerHTML = `<i class="fa-solid fa-circle-check text-success me-1"></i> Foto lista (${(blob.size / 1024).toFixed(0)} KB)`;
            } else {
                fotoOptimBlob = file;
                uploadLabel.innerHTML = '<i class="fa-solid fa-circle-check text-success me-1"></i> Foto cargada.';
            }
        } catch (err) {
            fotoOptimBlob = file;
            uploadLabel.innerHTML = '<i class="fa-solid fa-circle-check text-success me-1"></i> Foto lista.';
        }
        return fotoOptimBlob;
    })();
}

function mostrarModal(icono, titulo, mensaje) {
    document.getElementById('modalIcono').innerHTML = icono;
    document.getElementById('modalTitulo').innerText = titulo;
    document.getElementById('modalMensaje').innerText = mensaje;
    new bootstrap.Modal(document.getElementById('modalEmergente')).show();
}

async function enviarFormulario() {
    const form = document.getElementById('formSoplado');
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }

    const btnSubmit = document.getElementById('btnGuardar');
    btnSubmit.disabled = true;
    btnSubmit.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i> Guardando...';

    // Si aún está terminando la compresión de 50ms, esperar silenciosamente
    if (optimizacionPromesa) {
        try {
            await optimizacionPromesa;
        } catch (e) {}
    }

    const formData = new FormData(form);

    // Garantizar que siempre se envíe el blob comprimido (evita subir fotos crudas de 10-15MB)
    if (fotoOptimBlob) {
        formData.set('foto_equipo', fotoOptimBlob, 'foto_evidencia.jpg');
    }

    fetch('<?= base_url('soplado/guardar') ?>', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(r => r.json())
    .then(data => {
        if (data.status === 'success') {
            mostrarModal('<i class="fa-solid fa-circle-check text-success"></i>', '¡Guardado con éxito!', data.message || 'El registro y la foto se han guardado correctamente.');
            form.reset();
            fotoOptimBlob = null;
            optimizacionPromesa = null;
            const principal = document.getElementById('foto_equipo');
            if (principal) principal.required = true;
            const contenedorGel = document.getElementById('contenedor_gel_cucarachas');
            const selectGel = document.getElementById('gel_cucarachas');
            if (contenedorGel) contenedorGel.classList.add('d-none');
            if (selectGel) { selectGel.required = false; selectGel.value = ''; }
            document.getElementById('preview-container').classList.add('d-none');
            document.getElementById('upload-label').innerText = 'Adjunta o captura la foto del equipo.';
            const feedbackDiv = document.getElementById('inventario_feedback');
            if (feedbackDiv) feedbackDiv.innerHTML = '';
        } else {
            mostrarModal('<i class="fa-solid fa-circle-xmark text-danger"></i>', 'Error al guardar', data.message || 'Ocurrió un error al guardar.');
        }
    })
    .catch(() => {
        mostrarModal('<i class="fa-solid fa-triangle-exclamation text-warning"></i>', 'Error de red', 'Ocurrió un error procesando el registro.');
    })
    .finally(() => {
        btnSubmit.disabled = false;
        btnSubmit.innerHTML = '<i class="fa-solid fa-floppy-disk me-1"></i> Guardar Registro de Soplado';
    });
}

// Sincronización en tiempo real y lógica condicional del formulario
document.addEventListener('DOMContentLoaded', function() {
    // Control condicional: ¿Se aplicó gel para cucarachas?
    const maquinaContenia = document.getElementById('maquina_contenia');
    const contenedorGel   = document.getElementById('contenedor_gel_cucarachas');
    const selectGel       = document.getElementById('gel_cucarachas');
    const formSoplado     = document.getElementById('formSoplado');

    function toggleGelCucarachas() {
        if (!maquinaContenia || !contenedorGel || !selectGel) return;
        if (maquinaContenia.value === 'Cucaracha') {
            contenedorGel.classList.remove('d-none');
            selectGel.required = true;
        } else {
            contenedorGel.classList.add('d-none');
            selectGel.required = false;
            selectGel.value = '';
        }
    }

    if (maquinaContenia) {
        maquinaContenia.addEventListener('change', toggleGelCucarachas);
        toggleGelCucarachas();
    }

    if (formSoplado) {
        formSoplado.addEventListener('reset', function() {
            setTimeout(toggleGelCucarachas, 0);
        });
    }

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