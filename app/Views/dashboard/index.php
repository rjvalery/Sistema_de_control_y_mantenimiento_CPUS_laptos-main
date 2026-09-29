<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Dashboard<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h4 mb-1">Panel de control</h1>
        <p class="text-muted mb-0">Bienvenido, <?= esc(session('usuario_nombre')) ?>.</p>
    </div>
</div>

<?php if (session()->getFlashdata('msg')): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fa-solid fa-circle-check me-2"></i><?= session()->getFlashdata('msg') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fa-solid fa-circle-exclamation me-2"></i><?= session()->getFlashdata('error') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- PANEL DE CONTROL DE INVENTARIO Y STOCK -->
<div class="card border-0 shadow-sm mb-4" style="border-radius: 12px; overflow: hidden;">
    <div class="card-header bg-white py-3 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div>
            <h5 class="card-title fw-bold mb-0 text-dark">
                <i class="fa-solid fa-boxes-stacked text-primary me-2"></i>Control de Inventario General y Descuento de Stock
            </h5>
            <small class="text-muted">Equipos cargados masivamente vs equipos intervenidos en los módulos de taller.</small>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 rounded-pill fw-semibold">
                <i class="fa-solid fa-chart-pie me-1"></i> <?= $statsInventario['porcentaje'] ?>% Intervenido
            </span>
            <a href="<?= base_url('inventario') ?>" class="btn btn-primary btn-sm fw-semibold shadow-sm">
                <i class="fa-solid fa-boxes-stacked me-1"></i> Módulo Inventario
            </a>
        </div>
    </div>
    <div class="card-body p-4">
        <div class="row g-3 mb-3">
            <!-- 1. Total en Sistema -->
            <div class="col-md-6 col-xl-3">
                <div class="p-3 rounded border bg-light h-100">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted small fw-semibold">Total en Sistema (Cargue)</span>
                        <div class="p-2 bg-primary-subtle text-primary rounded-circle">
                            <i class="fa-solid fa-laptop-file fs-5"></i>
                        </div>
                    </div>
                    <div class="fs-2 fw-bold text-dark"><?= esc((string)$statsInventario['totalCargados']) ?></div>
                    <div class="text-muted small mt-1">Cargados en inventario masivo</div>
                </div>
            </div>

            <!-- 2. Intervenidos (Descontados) -->
            <div class="col-md-6 col-xl-3">
                <div class="p-3 rounded border border-success-subtle bg-success-subtle bg-opacity-25 h-100">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-success small fw-semibold">Equipos Intervenidos</span>
                        <div class="p-2 bg-success text-white rounded-circle">
                            <i class="fa-solid fa-check-double fs-5"></i>
                        </div>
                    </div>
                    <div class="fs-2 fw-bold text-success"><?= esc((string)$statsInventario['intervenidos']) ?></div>
                    <div class="text-muted small mt-1">Marcados y descontados de stock</div>
                </div>
            </div>

            <!-- 3. Pendientes por Intervenir -->
            <div class="col-md-6 col-xl-3">
                <div class="p-3 rounded border border-warning-subtle bg-warning-subtle bg-opacity-25 h-100">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-warning-emphasis small fw-semibold">Pendientes por Intervenir</span>
                        <div class="p-2 bg-warning text-dark rounded-circle">
                            <i class="fa-solid fa-clock-rotate-left fs-5"></i>
                        </div>
                    </div>
                    <div class="fs-2 fw-bold text-warning-emphasis"><?= esc((string)$statsInventario['pendientes']) ?></div>
                    <div class="text-muted small mt-1">Equipos restantes por gestionar</div>
                </div>
            </div>

            <!-- 4. Total Intervenciones Globales -->
            <div class="col-md-6 col-xl-3">
                <div class="p-3 rounded border bg-light h-100">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted small fw-semibold">Intervenciones Globales</span>
                        <div class="p-2 bg-info-subtle text-info rounded-circle">
                            <i class="fa-solid fa-screwdriver-wrench fs-5"></i>
                        </div>
                    </div>
                    <div class="fs-2 fw-bold text-info"><?= esc((string)$totalIntervenciones) ?></div>
                    <div class="text-muted small mt-1">Diagnósticos + Soplado + Laptops</div>
                </div>
            </div>
        </div>

        <!-- Barra de Progreso de Stock -->
        <div>
            <div class="d-flex justify-content-between align-items-center small text-muted mb-1">
                <span>Avance de Intervenciones sobre Inventario Masivo:</span>
                <span class="fw-semibold text-dark"><?= esc((string)$statsInventario['intervenidos']) ?> de <?= esc((string)$statsInventario['totalCargados']) ?> equipos (<?= esc((string)$statsInventario['porcentaje']) ?>%)</span>
            </div>
            <div class="progress" style="height: 12px; border-radius: 6px;">
                <div class="progress-bar bg-success progress-bar-striped progress-bar-animated" role="progressbar" style="width: <?= (float)$statsInventario['porcentaje'] ?>%;" aria-valuenow="<?= (float)$statsInventario['porcentaje'] ?>" aria-valuemin="0" aria-valuemax="100"></div>
            </div>
        </div>
    </div>
</div>

<!-- DESGLOSE POR MÓDULOS DE TALLER -->
<div class="row g-3 mb-4">
    <div class="col-md-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small">Diagnósticos CPU</div>
                <div class="fs-3 fw-semibold"><?= $totalEquipos === null ? '—' : esc((string)$totalEquipos) ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small">Soplado de CPUs</div>
                <div class="fs-3 fw-semibold"><?= $totalSoplado === null ? '—' : esc((string)$totalSoplado) ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small">Garantías Portátiles</div>
                <div class="fs-3 fw-semibold"><?= $totalPortatiles === null ? '—' : esc((string)$totalPortatiles) ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small">Analistas de Sistema</div>
                <div class="fs-3 fw-semibold"><?= $totalAnalistas === null ? '—' : esc((string)$totalAnalistas) ?></div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex flex-column">
                <div class="d-flex align-items-center mb-2">
                    <i class="fa-solid fa-desktop text-primary fs-5 me-2"></i>
                    <h2 class="h6 mb-0 text-dark fw-bold">Diagnóstico CPU</h2>
                </div>
                <p class="small text-muted mb-3 flex-grow-1">Registro y control técnico de CPUs.</p>
                <div class="d-flex gap-2">
                    <a href="<?= base_url('equipos/formulario') ?>" class="btn btn-primary btn-sm flex-fill fw-semibold">
                        <i class="fa-solid fa-plus me-1"></i> Nuevo
                    </a>
                    <a href="<?= base_url('equipos/bitacora') ?>" class="btn btn-outline-secondary btn-sm flex-fill fw-semibold">
                        <i class="fa-solid fa-list me-1"></i> Bitácora
                    </a>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex flex-column">
                <div class="d-flex align-items-center mb-2">
                    <i class="fa-solid fa-wind text-info fs-5 me-2"></i>
                    <h2 class="h6 mb-0 text-dark fw-bold">Soplado</h2>
                </div>
                <p class="small text-muted mb-3 flex-grow-1">Mantenimiento preventivo y limpieza.</p>
                <div class="d-flex gap-2">
                    <a href="<?= base_url('soplado/formulario') ?>" class="btn btn-info text-white btn-sm flex-fill fw-semibold">
                        <i class="fa-solid fa-plus me-1"></i> Nuevo
                    </a>
                    <a href="<?= base_url('soplado/bitacora') ?>" class="btn btn-outline-secondary btn-sm flex-fill fw-semibold">
                        <i class="fa-solid fa-list me-1"></i> Bitácora
                    </a>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex flex-column">
                <div class="d-flex align-items-center mb-2">
                    <i class="fa-solid fa-laptop text-success fs-5 me-2"></i>
                    <h2 class="h6 mb-0 text-dark fw-bold">Portátiles</h2>
                </div>
                <p class="small text-muted mb-3 flex-grow-1">Garantías Lenovo e intervención.</p>
                <div class="d-flex gap-2">
                    <a href="<?= base_url('portatiles/formulario') ?>" class="btn btn-success btn-sm flex-fill fw-semibold">
                        <i class="fa-solid fa-plus me-1"></i> Nuevo
                    </a>
                    <a href="<?= base_url('portatiles/bitacora') ?>" class="btn btn-outline-secondary btn-sm flex-fill fw-semibold">
                        <i class="fa-solid fa-list me-1"></i> Bitácora
                    </a>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100 border-start border-primary border-3">
            <div class="card-body d-flex flex-column">
                <div class="d-flex align-items-center mb-2">
                    <i class="fa-solid fa-boxes-stacked text-primary fs-5 me-2"></i>
                    <h2 class="h6 mb-0 text-dark fw-bold">Inventario</h2>
                </div>
                <p class="small text-muted mb-3 flex-grow-1">Gestión general, cargue masivo y stock.</p>
                <div class="d-flex gap-2">
                    <a href="<?= base_url('inventario') ?>" class="btn btn-primary btn-sm flex-fill fw-semibold">
                        <i class="fa-solid fa-boxes-stacked me-1"></i> Abrir
                    </a>
                    <a href="<?= base_url('inventario#seccion-cargue') ?>" class="btn btn-outline-primary btn-sm flex-fill fw-semibold">
                        <i class="fa-solid fa-file-arrow-up me-1"></i> Cargue
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- SECCIÓN: GESTIÓN DE USUARIOS Y ASIGNACIÓN DE ROLES -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div>
            <h5 class="card-title fw-bold mb-0 text-dark">
                <i class="fa-solid fa-users-gear text-primary me-2"></i>Gestión de Usuarios y Asignación de Roles
            </h5>
            <small class="text-muted">Asigna roles para definir qué dashboard y accesos verá cada usuario al iniciar sesión.</small>
        </div>
        <button type="button" class="btn btn-primary btn-sm fw-semibold" data-bs-toggle="modal" data-bs-target="#modalNuevoUsuario">
            <i class="fa-solid fa-user-plus me-1"></i> Nuevo Usuario
        </button>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">ID</th>
                        <th>Nombre</th>
                        <th>Usuario (Login)</th>
                        <th>Rol Actual</th>
                        <th>Asignar Rol</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                        <th class="text-end pe-3">Registrado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($usuarios)): ?>
                        <?php foreach ($usuarios as $u): ?>
                            <tr>
                                <td class="ps-3 text-muted"><?= $u['id'] ?></td>
                                <td class="fw-semibold">
                                    <?= esc($u['nombre']) ?>
                                    <?php if ((int)$u['id'] === (int)session('usuario_id')): ?>
                                        <span class="badge bg-secondary-subtle text-secondary border ms-1 small">Tú</span>
                                    <?php endif; ?>
                                </td>
                                <td><code><?= esc($u['usuario']) ?></code></td>
                                <td>
                                    <?php if ($u['rol'] === 'admin'): ?>
                                        <span class="badge bg-dark text-white px-2 py-1">
                                            <i class="fa-solid fa-shield-halved me-1"></i>Administrador
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-primary text-white px-2 py-1">
                                            <i class="fa-solid fa-clipboard-user me-1"></i>Analista
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <form action="<?= base_url('usuarios/cambiar-rol') ?>" method="POST" class="d-inline-block">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                        <select name="rol" class="form-select form-select-sm" style="width: 145px;" onchange="this.form.submit()">
                                            <option value="admin" <?= $u['rol'] === 'admin' ? 'selected' : '' ?>>Administrador</option>
                                            <option value="analista" <?= $u['rol'] === 'analista' ? 'selected' : '' ?>>Analista</option>
                                        </select>
                                    </form>
                                </td>
                                <td>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle">Activo</span>
                                </td>
                                <td>
                                    <button type="button" 
                                            class="btn btn-outline-warning btn-sm fw-semibold" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#modalResetPass"
                                            data-id="<?= $u['id'] ?>"
                                            data-nombre="<?= esc($u['nombre']) ?>"
                                            data-usuario="<?= esc($u['usuario']) ?>"
                                            title="Restablecer clave">
                                        <i class="fa-solid fa-key me-1"></i> Reset
                                    </button>
                                </td>
                                <td class="text-end pe-3 text-muted small">
                                    <?= !empty($u['created_at']) ? date('d/m/Y', strtotime($u['created_at'])) : '—' ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="8" class="text-center text-muted py-3">No hay usuarios registrados.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- MODAL NUEVO USUARIO -->
<div class="modal fade" id="modalNuevoUsuario" tabindex="-1" aria-labelledby="modalNuevoUsuarioLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white py-3 px-4">
                <h5 class="modal-title fs-6 fw-bold mb-0" id="modalNuevoUsuarioLabel">
                    <i class="fa-solid fa-user-plus me-2"></i>Crear Nuevo Usuario
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= base_url('usuarios/crear') ?>" method="POST">
                <?= csrf_field() ?>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label for="nuevo_nombre" class="form-label fw-bold small">Nombre Completo *</label>
                        <input type="text" name="nombre" id="nuevo_nombre" class="form-control" placeholder="Ej: Carlos Gómez" required>
                    </div>
                    <div class="mb-3">
                        <label for="nuevo_usuario" class="form-label fw-bold small">Nombre de Usuario (Login) *</label>
                        <input type="text" name="usuario" id="nuevo_usuario" class="form-control" placeholder="Ej: cgomez" required>
                    </div>
                    <div class="mb-3">
                        <label for="nuevo_password" class="form-label fw-bold small">Contraseña *</label>
                        <input type="password" name="password" id="nuevo_password" class="form-control" placeholder="Mínimo 6 caracteres" required minlength="6">
                    </div>
                    <div class="mb-3">
                        <label for="nuevo_rol" class="form-label fw-bold small">Rol Asignado *</label>
                        <select name="rol" id="nuevo_rol" class="form-select" required>
                            <option value="analista" selected>Analista (Solo acceso a nuevo registro)</option>
                            <option value="admin">Administrador (Acceso total al sistema)</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer bg-light px-4 py-3">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary btn-sm fw-bold">
                        <i class="fa-solid fa-floppy-disk me-1"></i> Guardar Usuario
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL RESET PASSWORD (ADMIN) -->
<div class="modal fade" id="modalResetPass" tabindex="-1" aria-labelledby="modalResetPassLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-warning text-dark py-3 px-4">
                <h5 class="modal-title fs-6 fw-bold mb-0" id="modalResetPassLabel">
                    <i class="fa-solid fa-key me-2"></i>Restablecer Contraseña de Usuario
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= base_url('usuarios/reset-password') ?>" method="POST">
                <?= csrf_field() ?>
                <input type="hidden" name="id" id="reset_user_id" value="">
                <div class="modal-body p-4">
                    <div class="alert alert-warning-subtle border border-warning-subtle p-3 mb-3 small">
                        Restablecerás la contraseña de acceso para: <br>
                        <strong id="reset_user_nombre" class="fs-6 text-dark"></strong> 
                        (<code id="reset_user_usuario"></code>)
                    </div>
                    <div class="mb-3">
                        <label for="reset_nueva_pass" class="form-label fw-bold small">Nueva Contraseña Temporal</label>
                        <input type="text" name="nueva_password" id="reset_nueva_pass" class="form-control" placeholder="Dejar en blanco para usar: Password123*">
                        <small class="text-muted">Si lo dejas vacío, se asignará automáticamente <code>Password123*</code>.</small>
                    </div>
                </div>
                <div class="modal-footer bg-light px-4 py-3">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning btn-sm fw-bold">
                        <i class="fa-solid fa-rotate-right me-1"></i> Restablecer Clave
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const modalResetPass = document.getElementById('modalResetPass');
    if (modalResetPass) {
        modalResetPass.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const id = button.getAttribute('data-id');
            const nombre = button.getAttribute('data-nombre');
            const usuario = button.getAttribute('data-usuario');
            
            modalResetPass.querySelector('#reset_user_id').value = id;
            modalResetPass.querySelector('#reset_user_nombre').textContent = nombre;
            modalResetPass.querySelector('#reset_user_usuario').textContent = usuario;
            modalResetPass.querySelector('#reset_nueva_pass').value = '';
        });
    }
});
</script>
<?= $this->endSection() ?>
