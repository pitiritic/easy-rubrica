<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Instalación - EasyRúbrica</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .install-card { max-width: 500px; margin: 50px auto; }
        .form-label { font-weight: bold; }
    </style>
</head>
<body>
<div class="container">
    <div class="card install-card shadow">
        <div class="card-header bg-primary text-white text-center p-3">
            <h4 class="mb-0">Configuración Inicial EasyRúbrica</h4>
        </div>
        <div class="card-body p-4">
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>

            <?php if ($mensaje): ?>
                <div class="alert alert-success"><?= $mensaje ?></div>
                <p class="text-center text-muted">Redirigiendo al login...</p>
            <?php else: ?>
                <form method="POST">
                    <p class="text-muted mb-4 small">Por favor, rellena los datos para crear la cuenta del administrador principal.</p>
                    
                    <div class="mb-3">
                        <label class="form-label">Nombre completo</label>
                        <input type="text" name="admin_nombre" class="form-control" required 
                               value="<?= htmlspecialchars($_POST['admin_nombre'] ?? '') ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Usuario de acceso</label>
                        <input type="text" name="admin_user" class="form-control" required
                               value="<?= htmlspecialchars($_POST['admin_user'] ?? '') ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Correo electrónico</label>
                        <input type="email" name="admin_email" class="form-control" required
                               value="<?= htmlspecialchars($_POST['admin_email'] ?? '') ?>">
                    </div>

                    <hr class="my-4">

                    <div class="mb-3">
                        <label class="form-label">Contraseña</label>
                        <input type="password" name="admin_pass" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Repetir contraseña</label>
                        <input type="password" name="admin_pass_confirm" class="form-control" required>
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary w-100 py-2">Finalizar Instalación</button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>
