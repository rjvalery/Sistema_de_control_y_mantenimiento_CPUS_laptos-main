<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Intervención de Portátiles<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="row justify-content-center">
    <div class="col-md-10 col-lg-8">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-primary text-white py-3">
                <h5 class="mb-0 fs-6"><i class="fa-solid fa-laptop me-2"></i>Garantías e Intervención de Portátiles</h5>
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

    // 1. Ocultar todas las secciones condicionales por defecto
    secGarantia.classList.add('d-none');
    secDiagnostico.classList.add('d-none');
    secRepuesto.classList.add('d-none');
    secBaja.classList.add('d-none');
    if (secReparado) secReparado.classList.add('d-none');

    // 2. Validación de campos obligatorios condicionales
    if (ticketInput) {
        ticketInput.required = false;
    }
    if (motivoBajaSelect) {
        motivoBajaSelect.required = false;
    }
    if (reparadoPorSelect) {
        reparadoPorSelect.required = false;
    }

    // 3. Evaluar el estado seleccionado
    if (estado === 'Funcional') {
        // En Funcional sale únicamente el campo de comentario u observaciones
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
        // En Donación sale únicamente el campo de comentario u observaciones de la donación
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
        // Salen solo: Número de ticket y ¿Por qué solicita garantía?
        secGarantia.classList.remove('d-none');
        if (ticketInput) {
            ticketInput.required = true;
        }
    } else if (estado === 'Novedad') {
        // Solo sale: Diagnóstico del laptop intervenido
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
        // Diagnóstico y detalle de piezas/repuestos
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
        // Sección de equipo reparado (quién reparó y observaciones)
        if (secReparado) secReparado.classList.remove('d-none');
        if (reparadoPorSelect) {
            reparadoPorSelect.required = true;
        }
    } else if (estado === 'Baja') {
        // Se muestra la información de baja (motivo y serial de disco)
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

    fetch('<?= base_url('portatiles/guardar') ?>', {
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
                btnEvidencia.href = '<?= base_url('portatiles/evidencia') ?>?placa=' + encodeURIComponent(placa);
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

// Sincronización en tiempo real con inventario al tipear ID o Serial
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
</script>
<?= $this->endSection() ?>