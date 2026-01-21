<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - EasyRúbrica</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f0f2f5; height: 100vh; display: flex; align-items: center; }
        .card { border-radius: 15px; border: none; }
        .btn-primary { background-color: #0d6efd; border: none; padding: 10px; }
    </style>
</head>
<body>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-4">
            <div class="card shadow-lg">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <i class="fa-solid fa-square-poll-vertical fa-3x text-primary mb-2"></i>
                        <h3 class="fw-bold">EasyRúbrica</h3>
                        <p class="text-muted small">Inicia sesión para continuar</p>
                    </div>

                    <?php if (isset($error) && $error): ?>
                        <div class="alert alert-danger small py-2"><?= $error ?></div>
                    <?php endif; ?>

                    <form action="?action=login" method="POST">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Usuario</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fa-solid fa-user text-muted"></i></span>
                                <input type="text" name="usuario" class="form-control" required>
                            </div>
                        </div>
                        <div class="mb-4">
                            <label class="form-label small fw-bold">Contraseña</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fa-solid fa-lock text-muted"></i></span>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 fw-bold">ENTRAR</button>
                    </form>

                    <div class="text-center mt-4">
                        <a href="?action=recover" class="text-decoration-none text-muted small">
                            ¿Olvidaste tu contraseña? Recupérala aquí
                        </a>
                    </div>
                </div>
            </div>
            <p class="text-center text-muted mt-4 small">© <?= date('Y') ?> EasyRúbrica</p>
        </div>
    </div>
</div>

</body>
</html>
