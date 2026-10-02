let fotoOptimBlob = null;
let optimizacionPromesa = null;

function manejarSeleccionFotoSoplado(input) {
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
    preview.src = instantUrl;
    previewContainer.classList.remove('d-none');
    uploadLabel.innerHTML = '<span class="spinner-border spinner-border-sm text-primary me-1"></span> Optimizando peso en segundo plano...';

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

    if (optimizacionPromesa) {
        try {
            await optimizacionPromesa;
        } catch (e) {}
    }

    const formData = new FormData(form);

    if (fotoOptimBlob) {
        formData.set('foto_equipo', fotoOptimBlob, 'foto_evidencia.jpg');
    }

    fetch(window.AppUrls.guardarSoplado, {
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

document.addEventListener('DOMContentLoaded', function() {
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
