<div class="container py-4">
    <div class="d-flex align-items-center mb-4 bg-white p-3 rounded shadow-sm border-start border-5 border-primary">
        <h2 class="text-primary mb-0 fw-bold"><i class="fa-solid fa-gears"></i> Ajustes del Sistema</h2>
    </div>

    <?php if($mensaje): ?>
        <div class="alert alert-success shadow-sm border-0 border-start border-4 border-success alert-dismissible fade show">
            <i class="fa-solid fa-circle-check me-2"></i> <?= $mensaje ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if($error): ?>
        <div class="alert alert-danger shadow-sm border-0 border-start border-4 border-danger alert-dismissible fade show">
            <i class="fa-solid fa-triangle-exclamation me-2"></i> <?= $error ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <div class="row g-4">
        <div class="col-md-6">
            <div class="card shadow-sm border-0 h-100 hover-card d-flex flex-column">
                <div class="card-header bg-primary text-white py-3">
                    <h5 class="mb-0"><i class="fa-solid fa-download"></i> Copia de Seguridad</h5>
                </div>
                <div class="card-body d-flex flex-column">
                    <p class="text-muted flex-grow-1">Descarga un archivo <code>.sql</code> con toda la base de datos (usuarios, clases, rúbricas y notas).</p>
                    <a href="?action=ajustes&do=backup" class="btn btn-primary w-100 fw-bold mt-auto py-2">
                        <i class="fa-solid fa-file-export me-2"></i> DESCARGAR RESPALDO
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card shadow-sm border-0 h-100 hover-card d-flex flex-column">
                <div class="card-header bg-success text-white py-3">
                    <h5 class="mb-0"><i class="fa-solid fa-upload"></i> Restaurar Sistema</h5>
                </div>
                <div class="card-body d-flex flex-column">
                    <p class="text-muted">Sube un archivo de respaldo previo para restaurar toda la información.</p>
                    <form method="POST" enctype="multipart/form-data" class="mt-auto">
                        <input type="file" name="backup_file" class="form-control mb-3" accept=".sql" required>
                        <button type="submit" name="restaurar_backup" class="btn btn-success w-100 fw-bold py-2">
                            <i class="fa-solid fa-clock-rotate-left me-2"></i> EJECUTAR RESTAURACIÓN
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card shadow-sm border-0 hover-card">
                <div class="card-header bg-secondary text-white py-3">
                    <h5 class="mb-0"><i class="fa-solid fa-envelope"></i> Configuración del Servidor SMTP</h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-bold small">Servidor SMTP</label>
                                <input type="text" name="smtp_host" class="form-control" value="<?= htmlspecialchars($smtp['smtp_host'] ?? '') ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small">Usuario / Email</label>
                                <input type="text" name="smtp_user" class="form-control" value="<?= htmlspecialchars($smtp['smtp_user'] ?? '') ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small">Contraseña</label>
                                <input type="password" name="smtp_pass" class="form-control" value="<?= htmlspecialchars($smtp['smtp_pass'] ?? '') ?>" required>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-bold small">Puerto</label>
                                <input type="number" name="smtp_port" class="form-control" value="<?= htmlspecialchars($smtp['smtp_port'] ?? 587) ?>" required>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-bold small">Seguridad</label>
                                <select name="smtp_secure" class="form-select">
                                    <option value="tls" <?= ($smtp['smtp_secure']??'') == 'tls' ? 'selected' : '' ?>>TLS</option>
                                    <option value="ssl" <?= ($smtp['smtp_secure']??'') == 'ssl' ? 'selected' : '' ?>>SSL</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small">Email Remitente</label>
                                <input type="email" name="from_email" class="form-control" value="<?= htmlspecialchars($smtp['from_email'] ?? '') ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small">Nombre Remitente</label>
                                <input type="text" name="from_name" class="form-control" value="<?= htmlspecialchars($smtp['from_name'] ?? 'EasyRúbrica') ?>" required>
                            </div>
                        </div>
                        <div class="mt-3 d-flex gap-2">
                            <button type="submit" name="guardar_smtp" class="btn btn-secondary px-4 fw-bold">GUARDAR CONFIGURACIÓN</button>
                            <button type="submit" name="test_smtp" class="btn btn-outline-secondary px-4 fw-bold">PROBAR TEST</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-12 mt-4">
            <div class="card border-danger border-2 shadow-sm">
                <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div>
                        <h4 class="text-danger fw-bold mb-1"><i class="fa-solid fa-biohazard"></i> Zona de Peligro</h4>
                        <p class="text-muted mb-0 small">Esta acción borrará <strong>TODOS</strong> los datos del sistema.</p>
                    </div>
                    <form method="POST">
                        <input type="hidden" name="reset_total" value="1">
                        <button type="submit" class="btn btn-danger btn-lg px-4 fw-bold shadow-sm" onclick="return confirm('¿Borrar todo?');">
                            <i class="fa-solid fa-trash-can me-2"></i> FORMATEAR SISTEMA
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
