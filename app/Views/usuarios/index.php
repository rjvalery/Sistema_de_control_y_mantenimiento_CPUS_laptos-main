<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Gestión de Usuarios y Roles<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="row justify-content-center">
    <div class="col-12 col-xl-11">

        <!-- ENCABEZADO DE MÓDULO -->
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 pb-2 border-bottom">
            <div>
                <h1 class="h3 fw-bold text-dark mb-1">
                    <i class="fa-solid fa-users-gear text-primary me-2"></i>Gestión de Usuarios y Asignación de Roles
                </h1>
                <p class="text-muted mb-0">
                    Administra las cuentas de acceso del personal técnico, asigna perfiles de <strong>Administrador</strong> o <strong>Analista</strong> y gestiona contraseñas.
                </p>
            </div>
            <div class="mt-2 mt-md-0 d-flex flex-wrap gap-2">
                <button type="button" class="btn btn-primary btn-sm fw-semibold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalNuevoUsuario">
                    <i class="fa-solid fa-user-plus me-1"></i> Nuevo Usuario
                </button>
                <a href="<?= base_url('dashboard') ?>" class="btn btn-outline-secondary btn-sm">
                    <i class="fa-solid fa-arrow-left me-1"></i> Volver al Dashboard
                </a>
            </div>
        </div>

        <!-- TARJETAS DE MÉTRICAS RÁPIDAS -->
        <div class="row g-3 mb-4">
            <div class="col-12 col-sm-6 col-lg-3">
                <div class="card border-0 shadow-sm h-100 bg-white border-start border-primary border-4">
                    <div class="card-body p-3 d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small text-uppercase fw-bold">Total Usuarios</span>
                            <h3 class="fw-bold mb-0 text-dark"><?= number_format((int)$totalUsuarios) ?></h3>
                            <small class="text-muted">Cuentas en el sistema</small>
                        </div>
                        <div class="bg-primary-subtle text-primary p-3 rounded-circle">
                            <i class="fa-solid fa-users fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-lg-3">
                <div class="card border-0 shadow-sm h-100 bg-white border-start border-dark border-4">
                    <div class="card-body p-3 d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small text-uppercase fw-bold">Administradores</span>
                            <h3 class="fw-bold mb-0 text-dark"><?= number_format((int)$totalAdmins) ?></h3>
                            <small class="text-muted">Acceso total y configuración</small>
                        </div>
                        <div class="bg-dark-subtle text-dark p-3 rounded-circle">
                            <i class="fa-solid fa-shield-halved fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-lg-3">
                <div class="card border-0 shadow-sm h-100 bg-white border-start border-info border-4">
                    <div class="card-body p-3 d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small text-uppercase fw-bold">Analistas Técnicos</span>
                            <h3 class="fw-bold mb-0 text-info"><?= number_format((int)$totalAnalistas) ?></h3>
                            <small class="text-muted">Operación de formularios</small>
                        </div>
                        <div class="bg-info-subtle text-info p-3 rounded-circle">
                            <i class="fa-solid fa-clipboard-user fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-lg-3">
                <div class="card border-0 shadow-sm h-100 bg-white border-start border-success border-4">
                    <div class="card-body p-3 d-flex align-items-center justify-content-between">
                        <div class="overflow-hidden pe-2">
                            <span class="text-muted small text-uppercase fw-bold">Tu Sesión</span>
                            <h6 class="fw-bold mb-0 text-dark text-truncate"><?= esc(session('usuario_nombre') ?? 'Usuario') ?></h6>
                            <span class="badge bg-success-subtle text-success border border-success-subtle mt-1">
                                <i class="fa-solid fa-circle-check me-1"></i><?= strtoupper(session('usuario_rol') ?? 'ADMIN') ?>
                            </span>
                        </div>
                        <div class="bg-success-subtle text-success p-3 rounded-circle">
                            <i class="fa-solid fa-user-check fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- TABLA PRINCIPAL DE USUARIOS Y ROLES -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom py-3">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                    <div>
                        <h5 class="card-title fw-bold mb-0 text-dark">
                            <i class="fa-solid fa-list-check text-primary me-2"></i>Cuentas Registradas y Roles de Acceso
                            <span class="badge bg-primary ms-1"><?= count($usuarios) ?> listados</span>
                        </h5>
                        <small class="text-muted">Asigna roles para definir qué dashboard y accesos operativos verá cada usuario.</small>
                    </div>
                    
                    <button type="button" class="btn btn-primary btn-sm fw-semibold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalNuevoUsuario">
                        <i class="fa-solid fa-user-plus me-1"></i> Crear Usuario
                    </button>
                </div>

                <!-- FILTRO Y BÚSQUEDA -->
                <form method="GET" action="<?= base_url('usuarios') ?>" class="row g-2 align-items-center border-top pt-3">
                    <!-- Filtro por Rol -->
                    <div class="col-12 col-sm-4 col-md-3">
                        <select name="rol" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">Todos los Roles (<?= $totalUsuarios ?>)</option>
                            <option value="admin" <?= ($filtroRol === 'admin') ? 'selected' : '' ?>>Solo Administradores (<?= $totalAdmins ?>)</option>
                            <option value="analista" <?= ($filtroRol === 'analista') ? 'selected' : '' ?>>Solo Analistas (<?= $totalAnalistas ?>)</option>
                        </select>
                    </div>

                    <!-- Buscador -->
                    <div class="col-12 col-sm-5 col-md-6">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light text-muted"><i class="fa-solid fa-magnifying-glass"></i></span>
                            <input type="text" name="buscar" class="form-control form-control-sm" placeholder="Buscar por nombre o login..." value="<?= esc($busqueda) ?>">
                        </div>
                    </div>

                    <!-- Botones -->
                    <div class="col-12 col-sm-3 col-md-3 d-flex gap-2">
                        <button type="submit" class="btn btn-secondary btn-sm flex-fill fw-semibold">
                            <i class="fa-solid fa-filter me-1"></i> Filtrar
                        </button>
                        <?php if (!empty($busqueda) || !empty($filtroRol)): ?>
                            <a href="<?= base_url('usuarios') ?>" class="btn btn-outline-danger btn-sm" title="Restablecer filtros">
                                <i class="fa-solid fa-xmark"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 text-nowrap">
                        <thead class="table-dark">
                            <tr>
                                <th class="ps-3">ID</th>
                                <th>Nombre Completo</th>
                                <th>Usuario (Login)</th>
                                <th>Rol Actual</th>
                                <th>Asignar Rol</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                                <th class="text-end pe-3">Fecha Registro</th>
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
                                            <span class="badge bg-success-subtle text-success border border-success-subtle">
                                                <i class="fa-solid fa-circle-check me-1"></i>Activo
                                            </span>
                                        </td>
                                        <td>
                                            <div class="d-flex gap-1">
                                                <button type="button" 
                                                        class="btn btn-outline-info btn-sm fw-semibold" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#modalPermisos"
                                                        data-id="<?= $u['id'] ?>"
                                                        data-nombre="<?= esc($u['nombre']) ?>"
                                                        data-permisos='<?= esc($u['permisos'] ?? "[]") ?>'
                                                        title="Editar permisos">
                                                    <i class="fa-solid fa-user-lock"></i> Permisos
                                                </button>
                                                <button type="button" 
                                                        class="btn btn-outline-warning btn-sm fw-semibold" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#modalResetPass"
                                                        data-id="<?= $u['id'] ?>"
                                                        data-nombre="<?= esc($u['nombre']) ?>"
                                                        data-usuario="<?= esc($u['usuario']) ?>"
                                                        title="Restablecer clave">
                                                    <i class="fa-solid fa-key"></i> Reset
                                                </button>
                                            </div>
                                        </td>
                                        <td class="text-end pe-3 text-muted small">
                                            <?= !empty($u['created_at']) ? date('d/m/Y H:i', strtotime($u['created_at'])) : '—' ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-5">
                                        <i class="fa-solid fa-user-xmark display-6 text-muted mb-3 d-block"></i>
                                        No se encontraron usuarios con los criterios de búsqueda seleccionados.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div class="card-footer bg-light py-2 px-3 d-flex justify-content-between align-items-center">
                <small class="text-muted">
                    Mostrando <strong><?= count($usuarios) ?></strong> de <strong><?= $totalUsuarios ?></strong> usuarios en total
                </small>
                <small class="text-muted">
                    <i class="fa-solid fa-shield me-1"></i> Módulo exclusivo de Administrador
                </small>
            </div>
        </div>

    </div>
</div>

<!-- MODAL NUEVO USUARIO -->
<div class="modal fade" id="modalNuevoUsuario" tabindex="-1" aria-labelledby="modalNuevoUsuarioLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
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
                        <input type="text" name="nombre" id="nuevo_nombre" class="form-control" placeholder="Ej: Carlos Gómez" required autocomplete="name">
                    </div>
                    <div class="mb-3">
                        <label for="nuevo_usuario" class="form-label fw-bold small">Nombre de Usuario (Login) *</label>
                        <input type="text" name="usuario" id="nuevo_usuario" class="form-control" placeholder="Ej: cgomez" required autocomplete="username">
                    </div>
                    <div class="mb-3">
                        <label for="nuevo_password" class="form-label fw-bold small">Contraseña *</label>
                        <input type="password" name="password" id="nuevo_password" class="form-control" placeholder="Mínimo 6 caracteres" required minlength="6" autocomplete="new-password">
                    </div>
                    <div class="mb-3">
                        <label for="nuevo_rol" class="form-label fw-bold small">Rol Asignado *</label>
                        <select name="rol" id="nuevo_rol" class="form-select" required>
                            <option value="analista" selected>Analista (Acceso operativo a registros y bitácoras)</option>
                            <option value="admin">Administrador (Acceso total, usuarios e inventario)</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer bg-light px-4 py-3">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary btn-sm fw-bold px-3">
                        <i class="fa-solid fa-floppy-disk me-1"></i> Guardar Usuario
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL RESET PASSWORD (ADMIN) -->
<div class="modal fade" id="modalResetPass" tabindex="-1" aria-labelledby="modalResetPassLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
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
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning btn-sm fw-bold px-3">
                        <i class="fa-solid fa-rotate-right me-1"></i> Restablecer Clave
                    </button>
                </div>
            </form>
</div>
    </div>
</div>

<!-- MODAL PERMISOS -->
<div class="modal fade" id="modalPermisos" tabindex="-1" aria-labelledby="modalPermisosLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-info text-white py-3 px-4">
                <h5 class="modal-title fs-6 fw-bold mb-0" id="modalPermisosLabel">
                    <i class="fa-solid fa-user-lock me-2"></i>Permisos Específicos
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= base_url('usuarios/guardar-permisos') ?>" method="POST">
                <?= csrf_field() ?>
                <input type="hidden" name="id" id="permisos_user_id" value="">
                <div class="modal-body p-4">
                    <p class="small text-muted mb-3">Editando permisos para: <strong id="permisos_user_nombre" class="text-dark"></strong></p>
                    <div class="alert alert-info py-2 small">Selecciona los módulos a los que este usuario tendrá acceso en el sistema.</div>
                    
                    <div class="row g-2">
                        <?php 
                        $listaPermisos = [
                            'ver_inventario' => 'Ver Inventario General',
                            'gestionar_inventario' => 'Gestionar Inventario',
                            'ver_equipos' => 'Ver Bitácora Diagnóstico',
                            'editar_equipos' => 'Editar Diagnóstico CPU',
                            'ver_soplado' => 'Ver Bitácora Soplado',
                            'editar_soplado' => 'Editar Soplado',
                            'ver_portatiles' => 'Ver Bitácora Portátiles',
                            'editar_portatiles' => 'Editar Portátiles',
                            'exportar_excel' => 'Exportar Datos a Excel',
                        ];
                        foreach($listaPermisos as $key => $label): ?>
                        <div class="col-6">
                            <div class="form-check form-switch">
                                <input class="form-check-input permiso-checkbox" type="checkbox" name="permisos[]" value="<?= $key ?>" id="perm_<?= $key ?>">
                                <label class="form-check-label small" for="perm_<?= $key ?>"><?= $label ?></label>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="modal-footer bg-light px-4 py-3">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-info text-white btn-sm fw-bold px-3">
                        <i class="fa-solid fa-floppy-disk me-1"></i> Guardar Permisos
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

    const modalPermisos = document.getElementById('modalPermisos');
    if (modalPermisos) {
        modalPermisos.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const id = button.getAttribute('data-id');
            const nombre = button.getAttribute('data-nombre');
            let permisos = [];
            try {
                permisos = JSON.parse(button.getAttribute('data-permisos') || '[]');
            } catch (e) {
                permisos = [];
            }
            
            modalPermisos.querySelector('#permisos_user_id').value = id;
            modalPermisos.querySelector('#permisos_user_nombre').textContent = nombre;
            
            modalPermisos.querySelectorAll('.permiso-checkbox').forEach(chk => {
                chk.checked = permisos.includes(chk.value);
            });
        });
    }
});
</script>
<?= $this->endSection() ?>
