<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?= $this->renderSection('title') ?> - Sistema de Control CPUS</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

    <style>
        body {
            background-color: #f8fafc;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .content-wrapper {
            flex: 1;
        }
        .navbar-brand {
            font-weight: 600;
            letter-spacing: -0.5px;
        }
    </style>

    <?= $this->renderSection('styles') ?>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
        <div class="container-fluid px-4">
            <a class="navbar-brand" href="<?= base_url('dashboard') ?>">
                <i class="fa-solid fa-microchip text-primary me-2"></i>Control CPUs
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="<?= base_url('dashboard') ?>">
                            <i class="fa-solid fa-gauge-high me-1"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fa-solid fa-desktop me-1"></i> Diagnóstico
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="<?= base_url('equipos/formulario') ?>"><i class="fa-solid fa-plus me-2 text-primary"></i>Nuevo Registro</a></li>
                            <li><a class="dropdown-item" href="<?= base_url('equipos/bitacora') ?>"><i class="fa-solid fa-list me-2 text-secondary"></i>Bitácora</a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fa-solid fa-wind me-1"></i> Soplado
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="<?= base_url('soplado/formulario') ?>"><i class="fa-solid fa-plus me-2 text-info"></i>Nuevo Registro</a></li>
                            <li><a class="dropdown-item" href="<?= base_url('soplado/bitacora') ?>"><i class="fa-solid fa-list me-2 text-secondary"></i>Bitácora</a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fa-solid fa-laptop me-1"></i> Portátiles
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="<?= base_url('portatiles/formulario') ?>"><i class="fa-solid fa-plus me-2 text-success"></i>Nuevo Diagnóstico</a></li>
                            <li><a class="dropdown-item" href="<?= base_url('portatiles/evidencia') ?>"><i class="fa-solid fa-camera me-2 text-warning"></i>Subir Evidencia Foto</a></li>
                            <li><a class="dropdown-item" href="<?= base_url('portatiles/bitacora') ?>"><i class="fa-solid fa-list me-2 text-secondary"></i>Bitácora</a></li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= base_url('inventario') ?>">
                            <i class="fa-solid fa-boxes-stacked me-1"></i> Inventario
                        </a>
                    </li>
                    <?php if (session('usuario_rol') === 'admin'): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= base_url('usuarios') ?>">
                                <i class="fa-solid fa-users-gear me-1"></i> Usuarios
                            </a>
                        </li>
                    <?php endif; ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fa-solid fa-user-shield me-1"></i>
                            <?= esc(session('usuario_nombre') ?? 'Usuario') ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><span class="dropdown-item-text small text-muted"><?= esc(session('usuario_user') ?? '') ?> (<?= esc(session('usuario_rol') ?? 'analista') ?>)</span></li>
                            <li><hr class="dropdown-divider"></li>
                            <?php if (session('usuario_rol') === 'admin'): ?>
                                <li>
                                    <a class="dropdown-item" href="<?= base_url('usuarios') ?>">
                                        <i class="fa-solid fa-users-gear me-2 text-primary"></i> Gestión de Usuarios y Roles
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                            <?php endif; ?>
                            <li>
                                <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#modalCambiarPasswordPropia">
                                    <i class="fa-solid fa-key me-2 text-warning"></i> Cambiar mi contraseña
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item text-danger" href="<?= base_url('logout') ?>">
                                    <i class="fa-solid fa-right-from-bracket me-1"></i> Cerrar sesión
                                </a>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <main class="content-wrapper py-4">
        <div class="<?= $this->renderSection('container_class') ?: 'container' ?>">
            <?php if (session()->getFlashdata('msg')): ?>
                <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                    <i class="fa-solid fa-circle-check me-2"></i><?= session()->getFlashdata('msg') ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            <?php if (session()->getFlashdata('error')): ?>
                <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                    <i class="fa-solid fa-triangle-exclamation me-2"></i><?= session()->getFlashdata('error') ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?= $this->renderSection('content') ?>
        </div>
    </main>

    <!-- MODAL CAMBIAR CONTRASEÑA PROPIA (ANALISTA / ADMIN) -->
    <div class="modal fade" id="modalCambiarPasswordPropia" tabindex="-1" aria-labelledby="modalCambiarPasswordPropiaLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-dark text-white py-3 px-4">
                    <h5 class="modal-title fs-6 fw-bold mb-0" id="modalCambiarPasswordPropiaLabel">
                        <i class="fa-solid fa-key me-2 text-warning"></i>Cambiar Mi Contraseña
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="<?= base_url('usuarios/cambiar-password') ?>" method="POST">
                    <?= csrf_field() ?>
                    <div class="modal-body p-4">
                        <p class="small text-muted mb-3">Actualiza tu clave de acceso al sistema con seguridad.</p>
                        <div class="mb-3">
                            <label class="form-label fw-bold small">Contraseña Actual *</label>
                            <input type="password" name="password_actual" class="form-control" required placeholder="Ingresa tu clave actual">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small">Nueva Contraseña *</label>
                            <input type="password" name="password_nueva" class="form-control" required minlength="6" placeholder="Mínimo 6 caracteres">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small">Confirmar Nueva Contraseña *</label>
                            <input type="password" name="password_confirmar" class="form-control" required minlength="6" placeholder="Repite la nueva contraseña">
                        </div>
                    </div>
                    <div class="modal-footer bg-light px-4 py-3">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary btn-sm fw-bold">
                            <i class="fa-solid fa-check me-1"></i> Actualizar Contraseña
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <footer class="bg-white border-top py-3 text-center text-muted small">
        <div class="container">
            &copy; <?= date('Y') ?> Sistema de Diagnóstico y Garantías.
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <?= $this->renderSection('scripts') ?>
</body>
</html>
