<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña - EasyRúbrica</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f0f2f5; height: 100vh; display: flex; align-items: center; }
        .card { border-radius: 15px; border: none; }
        .btn-primary { padding: 10px; font-weight: bold; }
        .input-group-text { background-color: #f8f9fa; }
    </style>
</head>
<body>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-5 col-lg-4">
            <div class="card shadow-lg">
                <div class="card-body p-4 p-md-5">
                    
                    <div class="text-center mb-4">
                        <i class="fa-solid fa-key fa-3x text-primary mb-3"></i>
                        <h3 class="fw-bold">Recuperar</h3>
                        <p class="text-muted small">Acceso a tu cuenta</p>
                    </div>

                    <?php if($error): ?>
                        <div class="alert alert-danger small py-2">
                            <i class="fa-solid fa-triangle-exclamation me-2"></i><?= $error ?>
                        </div>
                    <?php endif; ?>

                    <?php if($mensaje): ?>
                        <div class="alert alert-success small py-2">
                            <i class="fa-solid fa-circle-check me-2"></i><?= $mensaje ?>
                        </div>
                    <?php endif; ?>

                    <?php if($step == 1): ?>
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-secondary">Correo Electrónico</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fa-solid fa-envelope text-muted"></i></span>
                                    <input type="email" name="email" class="form-control" placeholder="tu@email.com" required autofocus>
                                </div>
                            </div>
                            <button type="submit" name="enviar_email" class="btn btn-primary w-100 shadow-sm">
                                ENVIAR ENLACE <i class="fa-solid fa-paper-plane ms-2"></i>
                            </button>
                        </form>

                    <?php elseif($step == 2): ?>
                        <div class="text-center">
                            <p class="text-muted">Hemos enviado las instrucciones a tu correo.</p>
                            <a href="?action=login" class="btn btn-outline-primary w-100">VOLVER AL LOGIN</a>
                        </div>

                    <?php elseif($step == 3): ?>
                        <form method="POST">
                            <input type="hidden" name="token_hidden" value="<?= htmlspecialchars($_GET['token']) ?>">
                            
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-secondary">NUEVA CONTRASEÑA</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fa-solid fa-lock text-muted"></i></span>
                                    <input type="password" name="password" class="form-control" placeholder="••••••••" required autofocus>
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <label class="form-label small fw-bold text-secondary">REPETIR CONTRASEÑA</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fa-solid fa-shield-check text-muted"></i></span>
                                    <input type="password" name="password_confirm" class="form-control" placeholder="••••••••" required>
                                </div>
                            </div>
                            
                            <button type="submit" name="cambiar_password" class="btn btn-success w-100 shadow-sm fw-bold py-2">
                                ACTUALIZAR CONTRASEÑA
                            </button>
                        </form>
                    <?php endif; ?>

                    <div class="text-center mt-4">
                        <a href="?action=login" class="text-decoration-none text-muted small">
                            <i class="fa-solid fa-arrow-left me-1"></i> Volver al inicio
                        </a>
                    </div>
                </div>
            </div>
            <p class="text-center text-muted mt-4 small">© EasyRúbrica</p>
        </div>
    </div>
</div>

</body>
</html>
