<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Subir Evidencia de Portátiles<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="row justify-content-center">
    <div class="col-lg-8 col-xl-7">

        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h4 class="fw-bold mb-1 text-dark">
                    <i class="fa-solid fa-camera text-primary me-2"></i>Subir Evidencia Fotográfica
                </h4>
                <p class="text-muted small mb-0">Módulo exclusivo para capturar y adjuntar la evidencia de laptops intervenidas.</p>
            </div>
            <div>
                <a href="<?= base_url('portatiles/bitacora') ?>" class="btn btn-outline-secondary btn-sm">
                    <i class="fa-solid fa-list me-1"></i> Bitácora
                </a>
                <a href="<?= base_url('portatiles/formulario') ?>" class="btn btn-outline-primary btn-sm ms-1">
                    <i class="fa-solid fa-laptop me-1"></i> Diagnóstico
                </a>
            </div>
        </div>

        <div class="card shadow-sm border-0" style="border-radius: 14px;">
            <div class="card-header bg-primary text-white py-3" style="border-top-left-radius: 14px; border-top-right-radius: 14px;">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="fw-semibold">
                        <i class="fa-solid fa-laptop me-2"></i> Registro Rápido de Evidencia
                    </div>
                    <span class="badge bg-white text-primary fw-bold">Laptops</span>
                </div>
            </div>

            <div class="card-body p-4">

                <form id="formEvidenciaPortatil" enctype="multipart/form-data">
                    <?= csrf_field() ?>

                    <div class="row g-3">

                        <!-- Placa ID o Serial -->
                        <div class="col-12">
                            <label class="form-label fw-bold">Placa ID o Serial del portátil *</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fa-solid fa-barcode text-muted"></i></span>
                                <input type="text" name="placa_id_equipo" id="placa_id_equipo" class="form-control form-control-lg fw-bold" 
                                       placeholder="Ej: L123456 o Serial de la laptop" 
                                       value="<?= esc($placaInicial) ?>" required autocomplete="off">
                                <span class="input-group-text d-none" id="spinner_placa">
                                    <i class="fa-solid fa-spinner fa-spin text-primary"></i>
                                </span>
                            </div>
                            <div id="laptop_feedback" class="mt-2"></div>
                        </div>

                        <!-- Analista Responsable -->
                        <div class="col-12">
                            <label class="form-label fw-bold">Analista que captura la evidencia *</label>
                            <?php if (session('usuario_rol') === 'analista'): ?>
                                <input type="text" class="form-control bg-light fw-semibold" value="<?= esc(session('usuario_nombre')) ?>" readonly>
                                <input type="hidden" name="nombre_analista" value="<?= esc(session('usuario_nombre')) ?>">
                            <?php else: ?>
                                <select name="nombre_analista" id="nombre_analista" class="form-select" required>
                                    <option value="" disabled selected>-- Seleccione Analista --</option>
                                    <?php if (!empty($analistas)): ?>
                                        <?php foreach ($analistas as $a): ?>
                                            <option value="<?= esc($a['nombre']) ?>" <?= (session('usuario_nombre') === $a['nombre']) ? 'selected' : '' ?>>
                                                <?= esc($a['nombre']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            <?php endif; ?>
                        </div>

                        <!-- Área de Captura de Fotografía -->
                        <div class="col-12 mt-3">
                            <label class="form-label fw-bold">
                                <i class="fa-solid fa-camera me-1 text-primary"></i> Fotografía del Portátil *
                            </label>

                            <div class="p-3 border rounded text-center bg-light" style="border-style: dashed !important; border-width: 2px !important; border-color: #0d6efd !important;">
                                <!-- Input principal que valida el formulario y asegura la carga -->
                                <input type="file" name="foto_equipo" id="foto_equipo" 
                                       style="position: absolute; opacity: 0; width: 0.1px; height: 0.1px; overflow: hidden;" 
                                       accept="image/*" required>

                                <div class="d-flex flex-wrap justify-content-center gap-3 mb-2">
                                    <!-- Botón Cámara con input nativo superpuesto -->
                                    <div class="position-relative d-inline-block">
                                        <button type="button" class="btn btn-primary btn-lg px-4 py-2 fw-bold shadow-sm" style="pointer-events: none;">
                                            <i class="fa-solid fa-camera me-2"></i> Abrir Cámara
                                        </button>
                                        <input type="file" id="foto_camara" accept="image/*" capture="environment" 
                                               style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; opacity: 0; cursor: pointer; z-index: 10;" 
                                               onchange="manejarSeleccionFoto(this)">
                                    </div>

                                    <!-- Botón Galería con input nativo superpuesto -->
                                    <div class="position-relative d-inline-block">
                                        <button type="button" class="btn btn-outline-secondary btn-lg px-4 py-2 fw-bold shadow-sm" style="pointer-events: none;">
                                            <i class="fa-solid fa-images me-2"></i> Galería / Archivos
                                        </button>
                                        <input type="file" id="foto_galeria" accept="image/*" 
                                               style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; opacity: 0; cursor: pointer; z-index: 10;" 
                                               onchange="manejarSeleccionFoto(this)">
                                    </div>
                                </div>
                                
                                <div id="upload-label" class="form-text mt-2 text-muted fw-semibold">
                                    Toca un botón para activar la cámara o seleccionar de la galería de tu dispositivo.
                                </div>

                                <div class="mt-2 text-center">
                                    <a href="javascript:void(0)" class="text-decoration-none small text-muted" onclick="document.getElementById('selector_respaldo_portatil').classList.toggle('d-none')">
                                        <i class="fa-solid fa-sliders me-1"></i> ¿Problemas en la tablet? Probar selector directo alternativo
                                    </a>
                                    <div id="selector_respaldo_portatil" class="mt-2 d-none">
                                        <input type="file" id="foto_respaldo_portatil" accept="image/*" class="form-control form-control-sm" onchange="manejarSeleccionFoto(this)">
                                    </div>
                                </div>

                                <!-- Vista previa instantánea (0 ms) -->
                                <div id="preview-container" class="mt-3 d-none">
                                    <img id="preview" src="#" alt="Vista previa del portátil" class="img-thumbnail shadow-sm rounded" style="max-height: 260px; max-width: 100%;">
                                    <div class="mt-2">
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1">
                                            <i class="fa-solid fa-circle-check me-1"></i> Foto lista para enviar
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Botón Enviar -->
                        <div class="col-12 mt-4">
                            <button type="button" class="btn btn-primary w-100 py-3 fs-5 fw-bold shadow-sm" id="btnGuardar" onclick="enviarEvidencia()">
                                <i class="fa-solid fa-cloud-arrow-up me-2"></i> Guardar Evidencia Fotográfica
                            </button>
                        </div>

                    </div>
                </form>

            </div>
        </div>

    </div>
</div>

<!-- MODAL DE CONFIRMACIÓN -->
<div class="modal fade" id="modalConfirmacion" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content text-center p-3">
            <div class="modal-body">
                <div id="modalIcono" class="display-3 mb-2"></div>
                <h4 class="modal-title fw-bold mb-2" id="modalTitulo"></h4>
                <p class="text-muted mb-4" id="modalMensaje"></p>

                <div class="d-grid gap-2">
                    <button type="button" class="btn btn-primary fw-bold py-2" data-bs-dismiss="modal">
                        <i class="fa-solid fa-plus me-1"></i> Subir Otra Evidencia
                    </button>
                    <a href="<?= base_url('portatiles/bitacora') ?>" class="btn btn-outline-secondary py-2">
                        <i class="fa-solid fa-list me-1"></i> Ver en Bitácora
                    </a>
                </div>
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

function manejarSeleccionFoto(input) {
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

    // 1. VISTA PREVIA INSTANTÁNEA (0 ms): el analista ve su foto al instante
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

            const blob = await new Promise((resolve) => {
                canvas.toBlob((b) => resolve(b), 'image/jpeg', 0.65);
            });

            if (blob) {
                fotoOptimBlob = blob;
                uploadLabel.innerHTML = `<i class="fa-solid fa-circle-check text-success me-1"></i> Foto lista y optimizada (${(blob.size / 1024).toFixed(0)} KB)`;
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
    new bootstrap.Modal(document.getElementById('modalConfirmacion')).show();
}

async function enviarEvidencia() {
    const form = document.getElementById('formEvidenciaPortatil');
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }

    const btnSubmit = document.getElementById('btnGuardar');
    btnSubmit.disabled = true;
    btnSubmit.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i> Guardando evidencia...';

    if (optimizacionPromesa) {
        try {
            await optimizacionPromesa;
        } catch (e) {}
    }

    const formData = new FormData(form);

    if (fotoOptimBlob) {
        formData.set('foto_equipo', fotoOptimBlob, 'evidencia_laptop.jpg');
    }

    fetch('<?= base_url('portatiles/guardar-evidencia') ?>', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(async r => {
        const text = await r.text();
        try {
            return JSON.parse(text);
        } catch (e) {
            throw new Error('Respuesta inesperada del servidor.');
        }
    })
    .then(data => {
        if (data.status === 'success') {
            mostrarModal(
                '<i class="fa-solid fa-circle-check text-success"></i>', 
                '¡Evidencia Guardada!', 
                `La fotografía del equipo <strong>${escapeHtml(data.placa_id || '')}</strong> se subió correctamente.`
            );
            form.reset();
            fotoOptimBlob = null;
            optimizacionPromesa = null;
            const principal = document.getElementById('foto_equipo');
            if (principal) principal.required = true;
            document.getElementById('preview-container').classList.add('d-none');
            document.getElementById('upload-label').innerText = 'Toma la foto directamente con la cámara o selecciónala de la galería.';
            const feedbackDiv = document.getElementById('laptop_feedback');
            if (feedbackDiv) feedbackDiv.innerHTML = '';
        } else {
            mostrarModal('<i class="fa-solid fa-circle-xmark text-danger"></i>', 'Error al guardar', data.message || 'Ocurrió un error al guardar la evidencia.');
        }
    })
    .catch(err => {
        console.error('Error al subir evidencia:', err);
        mostrarModal('<i class="fa-solid fa-triangle-exclamation text-warning"></i>', 'Error al procesar', err.message || 'Ocurrió un error al procesar el envío de la foto.');
    })
    .finally(() => {
        btnSubmit.disabled = false;
        btnSubmit.innerHTML = '<i class="fa-solid fa-cloud-arrow-up me-2"></i> Guardar Evidencia Fotográfica';
    });
}

// Búsqueda en tiempo real de la laptop por Placa o Serial
document.addEventListener('DOMContentLoaded', function() {
    const placaInput = document.getElementById('placa_id_equipo');
    const spinnerPlaca = document.getElementById('spinner_placa');
    const feedbackDiv = document.getElementById('laptop_feedback');
    let debounceTimer = null;

    function buscarInfoLaptop(val) {
        if (!val || val.length < 2) {
            if (feedbackDiv) feedbackDiv.innerHTML = '';
            if (spinnerPlaca) spinnerPlaca.classList.add('d-none');
            return;
        }

        if (spinnerPlaca) spinnerPlaca.classList.remove('d-none');

        fetch('<?= base_url('portatiles/buscar-laptop') ?>?query=' + encodeURIComponent(val))
            .then(r => r.json())
            .then(res => {
                if (spinnerPlaca) spinnerPlaca.classList.add('d-none');
                if (!feedbackDiv) return;

                if (res.diagnostico) {
                    const diag = res.diagnostico;
                    const tieneFoto = res.tiene_foto;
                    feedbackDiv.innerHTML = `
                        <div class="alert alert-success py-2 px-3 mb-0 small border-success shadow-sm">
                            <div class="d-flex align-items-center mb-1">
                                <i class="fa-solid fa-circle-check text-success me-2 fs-5"></i>
                                <strong>Portátil Registrado en Diagnóstico</strong>
                            </div>
                            <div class="text-dark">
                                <strong>Placa:</strong> <code>${escapeHtml(diag.placa_id_equipo || '')}</code> | 
                                <strong>Gestión:</strong> ${escapeHtml(diag.tipo_gestion || '')} | 
                                <strong>Analista:</strong> ${escapeHtml(diag.nombre_analista || '')}
                            </div>
                            <div class="mt-1 small">
                                ${tieneFoto ? '<span class="badge bg-warning text-dark"><i class="fa-solid fa-triangle-exclamation me-1"></i>Ya tiene foto registrada — se actualizará por la nueva</span>' : '<span class="badge bg-primary"><i class="fa-solid fa-clock me-1"></i>Pendiente por evidencia fotográfica</span>'}
                            </div>
                        </div>
                    `;
                } else if (res.inventario) {
                    const inv = res.inventario;
                    feedbackDiv.innerHTML = `
                        <div class="alert alert-info py-2 px-3 mb-0 small border-info shadow-sm">
                            <div class="d-flex align-items-center mb-1">
                                <i class="fa-solid fa-circle-info text-info me-2 fs-5"></i>
                                <strong>Equipo encontrado en Inventario General</strong>
                            </div>
                            <div class="text-dark">
                                <strong>Serial:</strong> <code>${escapeHtml(inv.serial || '')}</code> | 
                                <strong>Equipo:</strong> ${escapeHtml(inv.marca || '')} ${escapeHtml(inv.modelo || '')}
                            </div>
                            <div class="text-muted small mt-1">Aún no tiene diagnóstico completo, pero puedes guardar su evidencia fotográfica ahora.</div>
                        </div>
                    `;
                } else {
                    feedbackDiv.innerHTML = `
                        <div class="alert alert-light border py-1 px-2 mb-0 small text-muted">
                            <i class="fa-solid fa-circle-question me-1 text-secondary"></i> Portátil no registrado previamente. Se guardará la evidencia vinculada a esta placa.
                        </div>
                    `;
                }
            })
            .catch(() => {
                if (spinnerPlaca) spinnerPlaca.classList.add('d-none');
            });
    }

    if (placaInput) {
        placaInput.addEventListener('input', function() {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => buscarInfoLaptop(this.value.trim()), 300);
        });

        // Si ya vino con una placa en URL, buscar automáticamente
        if (placaInput.value.trim().length >= 2) {
            buscarInfoLaptop(placaInput.value.trim());
        }
    }
});

function escapeHtml(str) {
    if (!str) return '';
    return String(str).replace(/[&<>"']/g, function(m) {
        return {'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'}[m];
    });
}
</script>
<?= $this->endSection() ?>
