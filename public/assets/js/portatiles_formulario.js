function escapeHtml(str) {
    if (!str) return '';
    return String(str).replace(/[&<>"']/g, function(m) {
        return {'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'}[m];
    });
}

function mostrarModal(icono, titulo, mensaje, esError = false) {
    document.getElementById('modalIcono').innerHTML = icono;
    document.getElementById('modalTitulo').innerText = titulo;
    document.getElementById('modalMensaje').innerHTML = mensaje;

    const boxExito = document.getElementById('modalBotonesExito');
    const boxError = document.getElementById('modalBotonesError');

    if (esError) {
        if (boxExito) boxExito.classList.add('d-none');
        if (boxError) boxError.classList.remove('d-none');
    } else {
        if (boxExito) boxExito.classList.remove('d-none');
        if (boxError) boxError.classList.add('d-none');
    }

    new bootstrap.Modal(document.getElementById('modalEmergente')).show();
}

function evaluarEstadoEquipo(estado) {
    const secGarantia = document.getElementById('seccion_garantia');
    const secDiagnostico = document.getElementById('seccion_diagnostico');
    const secRepuesto = document.getElementById('seccion_repuesto');
    const secBaja = document.getElementById('seccion_baja');
    const secReparado = document.getElementById('seccion_reparado');
    const ticketInput = document.getElementById('numero_ticket');
    const motivoBajaSelect = document.getElementById('motivo_baja');
    const reparadoPorSelect = document.getElementById('reparado_por');
    const lblDiagnostico = document.getElementById('lbl_diagnostico');
    const txtDiagnostico = document.getElementById('diagnostico_laptop_intervenido');
    const cardDiagnostico = document.getElementById('card_diagnostico');

    if (!secGarantia || !secDiagnostico || !secRepuesto || !secBaja) return;

    secGarantia.classList.add('d-none');
    secDiagnostico.classList.add('d-none');
    secRepuesto.classList.add('d-none');
    secBaja.classList.add('d-none');
    if (secReparado) secReparado.classList.add('d-none');

    if (ticketInput) {
        ticketInput.required = false;
    }
    if (motivoBajaSelect) {
        motivoBajaSelect.required = false;
    }
    if (reparadoPorSelect) {
        reparadoPorSelect.required = false;
    }

    if (estado === 'Funcional') {
        secDiagnostico.classList.remove('d-none');
        if (lblDiagnostico) {
            lblDiagnostico.innerHTML = '<i class="fa-solid fa-comment-dots text-success me-2"></i>Comentario u Observaciones del equipo funcional';
        }
        if (txtDiagnostico) {
            txtDiagnostico.placeholder = 'Escriba un comentario u observación sobre el equipo funcional...';
        }
        if (cardDiagnostico) {
            cardDiagnostico.className = 'p-3 bg-light rounded border border-success-subtle';
        }
        return;
    }

    if (estado === 'Donacion' || estado === 'Donación') {
        secDiagnostico.classList.remove('d-none');
        if (lblDiagnostico) {
            lblDiagnostico.innerHTML = '<i class="fa-solid fa-hand-holding-heart text-info me-2"></i>Comentario u Observaciones de Donación';
        }
        if (txtDiagnostico) {
            txtDiagnostico.placeholder = 'Escriba detalles, destinatario u observaciones sobre la donación del equipo...';
        }
        if (cardDiagnostico) {
            cardDiagnostico.className = 'p-3 bg-light rounded border border-info-subtle';
        }
        return;
    }

    if (estado === 'Garantia') {
        secGarantia.classList.remove('d-none');
        if (ticketInput) {
            ticketInput.required = true;
        }
    } else if (estado === 'Novedad') {
        secDiagnostico.classList.remove('d-none');
        if (lblDiagnostico) {
            lblDiagnostico.innerHTML = '<i class="fa-solid fa-stethoscope text-primary me-2"></i>Diagnóstico del laptop intervenido';
        }
        if (txtDiagnostico) {
            txtDiagnostico.placeholder = 'Describa el diagnóstico o detalle de la novedad...';
        }
        if (cardDiagnostico) {
            cardDiagnostico.className = 'p-3 bg-light rounded border border-primary-subtle';
        }
    } else if (estado === 'Pendiente repuesto') {
        secDiagnostico.classList.remove('d-none');
        if (lblDiagnostico) {
            lblDiagnostico.innerHTML = '<i class="fa-solid fa-stethoscope text-info me-2"></i>Diagnóstico / Motivo del repuesto';
        }
        if (txtDiagnostico) {
            txtDiagnostico.placeholder = 'Describa la falla técnica o motivo por el que requiere el repuesto...';
        }
        if (cardDiagnostico) {
            cardDiagnostico.className = 'p-3 bg-light rounded border border-info-subtle';
        }
        secRepuesto.classList.remove('d-none');
    } else if (estado === 'Reparado') {
        if (secReparado) secReparado.classList.remove('d-none');
        if (reparadoPorSelect) {
            reparadoPorSelect.required = true;
        }
    } else if (estado === 'Baja') {
        secBaja.classList.remove('d-none');
        if (motivoBajaSelect) {
            motivoBajaSelect.required = true;
        }
    }
}

function agregarFilaPieza() {
    const contenedor = document.getElementById('contenedor_piezas');
    if (!contenedor) return;

    const div = document.createElement('div');
    div.className = 'row g-2 align-items-center fila-pieza';
    div.innerHTML = `
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
            <button type="button" class="btn btn-outline-danger btn-sm w-100 btn-eliminar-pieza" onclick="eliminarFilaPieza(this)" title="Eliminar fila">
                <i class="fa-solid fa-trash"></i>
            </button>
        </div>
    `;
    contenedor.appendChild(div);
    actualizarBotonesEliminarPieza();
}

function eliminarFilaPieza(btn) {
    const fila = btn.closest('.fila-pieza');
    if (fila) {
        fila.remove();
        actualizarBotonesEliminarPieza();
    }
}

function actualizarBotonesEliminarPieza() {
    const filas = document.querySelectorAll('.fila-pieza');
    const btns = document.querySelectorAll('.btn-eliminar-pieza');
    if (filas.length <= 1) {
        btns.forEach(b => b.disabled = true);
    } else {
        btns.forEach(b => b.disabled = false);
    }
}

function resetContenedorPiezas() {
    const contenedor = document.getElementById('contenedor_piezas');
    if (!contenedor) return;
    const filas = contenedor.querySelectorAll('.fila-pieza');
    filas.forEach((f, idx) => {
        if (idx === 0) {
            f.querySelectorAll('input').forEach(i => i.value = '');
        } else {
            f.remove();
        }
    });
    actualizarBotonesEliminarPieza();
}

function enviarFormulario() {
    const form = document.getElementById('formPortatiles');
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }

    const btnSubmit = document.getElementById('btnGuardar');
    btnSubmit.disabled = true;
    btnSubmit.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i> Guardando diagnóstico...';

    const formData = new FormData(form);

    fetch(window.AppUrls.guardarPortatiles, {
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
            const placa = data.placa_id || form.querySelector('#placa_id_equipo')?.value || '';
            const btnEvidencia = document.getElementById('btnIrEvidencia');
            if (btnEvidencia && placa) {
                btnEvidencia.href = window.AppUrls.evidenciaPortatiles + '?placa=' + encodeURIComponent(placa);
            }

            mostrarModal(
                '<i class="fa-solid fa-circle-check text-success"></i>', 
                '¡Diagnóstico Guardado!', 
                `El diagnóstico del portátil <strong>${escapeHtml(placa)}</strong> se guardó exitosamente.`,
                false
            );
            form.reset();
            evaluarEstadoEquipo('');
            resetContenedorPiezas();
            const feedbackDiv = document.getElementById('inventario_feedback');
            if (feedbackDiv) feedbackDiv.innerHTML = '';
        } else {
            mostrarModal(
                '<i class="fa-solid fa-circle-xmark text-danger"></i>', 
                'Error al guardar', 
                escapeHtml(data.message || 'Ocurrió un error al guardar.'),
                true
            );
        }
    })
    .catch(err => {
        console.error('Error al guardar portátil:', err);
        mostrarModal(
            '<i class="fa-solid fa-triangle-exclamation text-danger"></i>', 
            'Error de comunicación', 
            escapeHtml(err.message || 'Ocurrió un error procesando el registro.'),
            true
        );
    })
    .finally(() => {
        btnSubmit.disabled = false;
        btnSubmit.innerHTML = '<i class="fa-solid fa-floppy-disk me-1"></i> Guardar Registro de Portátil';
    });
}

document.addEventListener('DOMContentLoaded', function() {
    const estadoSelect = document.getElementById('estado_actual_equipo');
    if (estadoSelect) {
        evaluarEstadoEquipo(estadoSelect.value);
    }

    const placaInput = document.getElementById('placa_id_equipo');
    const spinnerPlaca = document.getElementById('spinner_placa');
    const feedbackDiv = document.getElementById('inventario_feedback');
    let debounceTimer = null;

    const inputTraslado = document.getElementById('numero_traslado');
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
                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle me-1">${escapeHtml(eq.tipo_equipo || 'Portátil')}</span>
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
});
