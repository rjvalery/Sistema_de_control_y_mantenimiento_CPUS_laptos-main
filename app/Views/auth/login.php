<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar sesión - Sistema de Control CPUS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        body {
            min-height: 100vh;
            background: linear-gradient(160deg, #0f172a 0%, #1e3a5f 55%, #2563eb 100%);
        }
        .login-card {
            max-width: 420px;
            border: 0;
            border-radius: 1rem;
        }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center p-3">
    <div class="card login-card shadow-lg w-100">
        <div class="card-body p-4 p-md-5">
            <div class="text-center mb-4">
                <i class="fa-solid fa-microchip text-primary fa-2x mb-2"></i>
                <h1 class="h4 mb-1">Control CPUs</h1>
                <p class="text-muted small mb-0">Ingrese con su usuario de administración</p>
            </div>

            <?php if (session()->getFlashdata('msg')): ?>
                <div class="alert alert-success"><?= esc(session()->getFlashdata('msg')) ?></div>
            <?php endif; ?>
            <?php if (session()->getFlashdata('error')): ?>
                <div class="alert alert-danger"><?= esc(session()->getFlashdata('error')) ?></div>
            <?php endif; ?>

            <form action="<?= site_url('login') ?>" method="post" autocomplete="off">
                <?= csrf_field() ?>
                <div class="mb-3">
                    <label for="usuario" class="form-label fw-semibold">Usuario</label>
                    <input type="text" class="form-control" id="usuario" name="usuario"
                           value="<?= esc(old('usuario')) ?>" required autofocus>
                </div>
                <div class="mb-4">
                    <label for="password" class="form-label fw-semibold">Contraseña</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fa-solid fa-right-to-bracket me-1"></i> Ingresar
                </button>
                <div class="text-center mt-3">
                    <a href="#" class="text-decoration-none small text-muted" data-bs-toggle="modal" data-bs-target="#modalOlvidoPassword">
                        <i class="fa-solid fa-circle-question me-1"></i> ¿Olvidaste tu contraseña?
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL RECUPERACIÓN DE CONTRASEÑA -->
    <div class="modal fade" id="modalOlvidoPassword" tabindex="-1" aria-labelledby="modalOlvidoPasswordLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-primary text-white py-3 px-4">
                    <h5 class="modal-title fs-6 fw-bold mb-0" id="modalOlvidoPasswordLabel">
                        <i class="fa-solid fa-key me-2"></i>Recuperación de Contraseña
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 text-center">
                    <div class="mb-3">
                        <i class="fa-solid fa-user-shield text-primary fa-3x"></i>
                    </div>
                    <h6 class="fw-bold text-dark mb-2">¿Olvidaste tu contraseña de acceso?</h6>
                    <p class="small text-muted mb-3">
                        Por seguridad interna, las contraseñas de los analistas son gestionadas y restablecidas por el <strong>Administrador del Sistema</strong>.
                    </p>
                    <div class="alert alert-light border small text-start">
                        <i class="fa-solid fa-circle-info text-primary me-2"></i>
                        Comunícate con tu administrador para que use el botón <strong>"Reset Clave"</strong> en el panel de usuarios y te asigne una nueva clave temporal.
                    </div>
                </div>
                <div class="modal-footer bg-light px-4 py-3">
                    <button type="button" class="btn btn-primary btn-sm fw-bold w-100" data-bs-dismiss="modal">Entendido</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
