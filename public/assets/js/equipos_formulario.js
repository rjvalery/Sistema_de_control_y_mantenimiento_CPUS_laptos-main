function cambiarTipoGestion() {
    const selector = document.getElementById('tipo_gestion');
    if (!selector) return;

    const valor = selector.value;
    
    const secciones = [
        'seccion_diagnostico', 
        'seccion_intervencion', 
        'seccion_novedad', 
        'seccion_baja',
        'seccion_it'
    ];
    
    secciones.forEach(id => {
        const el = document.getElementById(id);
        if (el) el.classList.add('d-none');
    });

    const selects = document.querySelectorAll('#seccion_diagnostico select, #seccion_intervencion select, #seccion_novedad textarea, #seccion_baja textarea, #seccion_it textarea, #seccion_intervencion input, #seccion_baja input');
    selects.forEach(s => {
        s.required = false;
        s.value = '';
    });

    if (!valor) return;

    const fotoPrincipal = document.getElementById('foto_equipo');
    const fotoAsterisco = document.getElementById('foto-asterisco');
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
    } else if (valor === 'IT') {
        document.getElementById('seccion_it').classList.remove('d-none');
        document.getElementById('descripcion_it').required = true;
    }

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
            fotoPrincipal.required = !fotoOptimBlob;
        }
        if (fotoAsterisco) {
            fotoAsterisco.innerHTML = '<span class="text-danger">*</span>';
        }
        if (uploadLabel && !fotoOptimBlob) {
            uploadLabel.textContent = 'Toma la foto directamente con la cámara o selecciónala de la galería o archivos.';
        }
    }
}

let fotoOptimBlob = null;
let optimizacionPromesa = null;

function manejarSeleccionFoto(input) {
    if (!input.files || !input.files[0]) return;
    
    const file = input.files[0];
    const principal = document.getElementById('foto_equipo');
    
    if (principal) {
        principal.required = false;
        try {
            if (window.DataTransfer) {
                const dt = new DataTransfer();
                dt.items.add(file);
                principal.files = dt.files;
            }
        } catch (e) {}
    }
    
    optimizarImagen(file);
    
    input.value = '';
}

function optimizarImagen(file) {
    const previewContainer = document.getElementById('preview-container');
    const preview = document.getElementById('preview');
    const uploadLabel = document.getElementById('upload-label');

    if (!file) {
        fotoOptimBlob = null;
        optimizacionPromesa = null;
        return;
    }

    const instantUrl = URL.createObjectURL(file);
    if (preview) preview.src = instantUrl;
    if (previewContainer) previewContainer.classList.remove('d-none');
    if (uploadLabel) uploadLabel.innerHTML = '<span class="spinner-border spinner-border-sm text-primary me-1"></span> Optimizando peso en segundo plano...';

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
                if (uploadLabel) uploadLabel.innerHTML = `<i class="fa-solid fa-circle-check text-success me-1"></i> Foto lista (${(blob.size / 1024).toFixed(0)} KB)`;
            } else {
                fotoOptimBlob = file;
                if (uploadLabel) uploadLabel.innerHTML = '<i class="fa-solid fa-circle-check text-success me-1"></i> Foto cargada.';
            }
        } catch (err) {
            fotoOptimBlob = file;
            if (uploadLabel) uploadLabel.innerHTML = '<i class="fa-solid fa-circle-check text-success me-1"></i> Foto lista.';
        }
        return fotoOptimBlob;
    })();
}

function limpiarPrevisualizacion() {
    fotoOptimBlob = null;
    optimizacionPromesa = null;
    const inputCam = document.getElementById('foto_camara_diagnostico');
    const inputGal = document.getElementById('foto_galeria_diagnostico');
    const inputResp = document.getElementById('foto_respaldo');
    const inputPrincipal = document.getElementById('foto_equipo');
    const previewContainer = document.getElementById('preview-container');
    const preview = document.getElementById('preview');
    const uploadLabel = document.getElementById('upload-label');

    if (inputCam) inputCam.value = '';
    if (inputGal) inputGal.value = '';
    if (inputResp) inputResp.value = '';
    if (inputPrincipal) inputPrincipal.value = '';
    if (preview) preview.src = '#';
    if (previewContainer) previewContainer.classList.add('d-none');
    if (uploadLabel) uploadLabel.textContent = 'Toma la foto directamente con la cámara o selecciónala de la galería o archivos.';

    const tipoGestion = document.getElementById('tipo_gestion')?.value;
    if (inputPrincipal && tipoGestion !== 'Baja') {
        inputPrincipal.required = true;
    }
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

    if (tipoGestion === 'Baja') {
        if (principal) principal.required = false;
    } else {
        if (principal && !fotoOptimBlob && (!principal.files || !principal.files[0])) {
            principal.required = true;
        } else if (principal) {
            principal.required = false;
        }
    }

    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }

    const btnSubmit = document.getElementById('btnGuardar');
    btnSubmit.disabled = true;
    btnSubmit.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i> Guardando...';

    if (optimizacionPromesa) {
        try {
            await optimizacionPromesa;
        } catch (e) {}
    }

    const formData = new FormData(form);

    if (fotoOptimBlob) {
        formData.set('foto_equipo', fotoOptimBlob, 'foto_diagnostico.jpg');
    }

    if (tipoGestion === 'Baja') {
        const sdBaja = document.getElementById('serial_disco_baja')?.value?.trim();
        if (sdBaja) {
            formData.set('serial_disco', sdBaja);
        }
    }

    fetch(window.AppUrls.guardarEquipo, {
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
            // Assuming ocultarTodos is available (either defined globally or in this file)
            const event = new Event('change');
            document.getElementById('tipo_gestion').dispatchEvent(event);
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

document.addEventListener('DOMContentLoaded', function() {
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
                fetch(window.AppUrls.buscarEquipo + '?query=' + encodeURIComponent(val))
                    .then(r => r.json())
                    .then(res => {
                        if (spinnerPlaca) spinnerPlaca.classList.add('d-none');
                        if (!feedbackDiv) return;

                        if (res.encontrado && res.equipo) {
                            const eq = res.equipo;
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
