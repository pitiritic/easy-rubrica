<div class="container py-4">
    <div class="d-flex align-items-center mb-4 bg-white p-3 rounded shadow-sm border-start border-5 border-primary">
        <h2 class="text-primary mb-0 fw-bold"><i class="fa-solid fa-gears"></i> Ajustes del Sistema</h2>
    </div>

    <?php if($mensaje): ?><div class="alert alert-success shadow-sm border-0 border-start border-4 border-success alert-dismissible fade show"><i class="fa-solid fa-circle-check me-2"></i> <?= $mensaje ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>
    <?php if($error): ?><div class="alert alert-danger shadow-sm border-0 border-start border-4 border-danger alert-dismissible fade show"><i class="fa-solid fa-triangle-exclamation me-2"></i> <?= $error ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>
    
    <div class="row g-4">
        <div class="col-md-4">
            <div class="card shadow-sm border-0 h-100 d-flex flex-column">
                <div class="card-header bg-primary text-white py-3"><h5 class="mb-0 small fw-bold text-uppercase"><i class="fa-solid fa-download"></i> Copia de Seguridad</h5></div>
                <div class="card-body d-flex flex-column">
                    <p class="text-muted small flex-grow-1">Descarga un respaldo completo de la base de datos.</p>
                    <a href="?action=ajustes&do=backup" class="btn btn-primary w-100 fw-bold mt-auto py-2 small">DESCARGAR RESPALDO</a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm border-0 h-100 d-flex flex-column">
                <div class="card-header bg-success text-white py-3"><h5 class="mb-0 small fw-bold text-uppercase"><i class="fa-solid fa-upload"></i> Restaurar Sistema</h5></div>
                <div class="card-body d-flex flex-column">
                    <form method="POST" enctype="multipart/form-data" class="mt-auto">
                        <input type="file" name="backup_file" class="form-control form-control-sm mb-3" accept=".sql" required>
                        <button type="submit" name="restaurar_backup" class="btn btn-success w-100 fw-bold py-2 small">RESTAURAR</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm border-0 h-100 d-flex flex-column" style="border-top: 4px solid #1b355c !important;">
                <div class="card-header text-white py-3" style="background-color: #1b355c;"><h5 class="mb-0 small fw-bold text-uppercase"><i class="fa-solid fa-clipboard-check"></i> Auditoría</h5></div>
                <div class="card-body d-flex flex-column">
                    <p class="text-muted small flex-grow-1">Consulta el historial de movimientos y eventos del sistema.</p>
                    <a href="?action=auditoria" class="btn text-white w-100 fw-bold mt-auto py-2 small" style="background-color: #1b355c;">VER INFORMES</a>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card shadow-sm border-0 border-top border-4 border-info">
                <div class="card-header bg-white py-3"><h5 class="mb-0 text-info fw-bold"><i class="fa-solid fa-link"></i> Enlaces de la Plataforma</h5></div>
                <div class="card-body">
                    <form method="POST">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-muted">URL AYUDA</label>
                                <input type="url" name="url_ayuda" class="form-control" value="<?= htmlspecialchars($sistema['url_ayuda']) ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-muted">URL ACERCA DE</label>
                                <input type="url" name="url_acerca" class="form-control" value="<?= htmlspecialchars($sistema['url_acerca']) ?>">
                            </div>
                        </div>
                        <button type="submit" name="guardar_sistema" class="btn btn-info mt-3 fw-bold text-white">GUARDAR ENLACES</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card shadow-sm border-0 border-top border-4 border-secondary">
                <div class="card-header bg-white py-3"><h5 class="mb-0 text-secondary fw-bold"><i class="fa-solid fa-envelope"></i> Servidor SMTP</h5></div>
                <div class="card-body">
                    <form method="POST">
                        <div class="row g-3">
                            <div class="col-md-4"><label class="small fw-bold">HOST</label><input type="text" name="smtp_host" class="form-control" value="<?= htmlspecialchars($smtp['smtp_host'] ?? '') ?>" required></div>
                            <div class="col-md-4"><label class="small fw-bold">USER</label><input type="text" name="smtp_user" class="form-control" value="<?= htmlspecialchars($smtp['smtp_user'] ?? '') ?>" required></div>
                            <div class="col-md-4"><label class="small fw-bold">PASS</label><input type="password" name="smtp_pass" class="form-control" value="<?= htmlspecialchars($smtp['smtp_pass'] ?? '') ?>" required></div>
                            
                            <!-- NUEVOS CAMPOS RESTAURADOS -->
                            <div class="col-md-2"><label class="small fw-bold">PUERTO</label><input type="number" name="smtp_port" class="form-control" value="<?= htmlspecialchars($smtp['smtp_port'] ?? 587) ?>" required></div>
                            <div class="col-md-2">
                                <label class="small fw-bold">SEGURIDAD</label>
                                <select name="smtp_secure" class="form-select">
                                    <option value="tls" <?= ($smtp['smtp_secure']??'') == 'tls' ? 'selected' : '' ?>>TLS</option>
                                    <option value="ssl" <?= ($smtp['smtp_secure']??'') == 'ssl' ? 'selected' : '' ?>>SSL</option>
                                </select>
                            </div>
                            <div class="col-md-4"><label class="small fw-bold">ENVIAR COMO (EMAIL)</label><input type="email" name="from_email" class="form-control" value="<?= htmlspecialchars($smtp['from_email'] ?? '') ?>" required></div>
                            <div class="col-md-4"><label class="small fw-bold">NOMBRE REMITENTE</label><input type="text" name="from_name" class="form-control" value="<?= htmlspecialchars($smtp['from_name'] ?? 'EasyRúbrica') ?>" required></div>
                        </div>
                        <button type="submit" name="guardar_smtp" class="btn btn-secondary mt-3 fw-bold">GUARDAR CONFIGURACIÓN</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-12 mt-4">
            <div class="card border-danger border-2 shadow-sm">
                <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div>
                        <h4 class="text-danger fw-bold mb-1"><i class="fa-solid fa-biohazard"></i> Zona de Peligro</h4>
                        <p class="text-muted mb-0 small">Esta acción borrará <strong>TODOS</strong> los datos del sistema de forma irreversible.</p>
                    </div>
                    <form method="POST">
                        <input type="hidden" name="reset_total" value="1">
                        <button type="submit" class="btn btn-danger btn-lg px-4 fw-bold shadow-sm" onclick="return confirm('¿Estás seguro de que deseas borrar TODO? Esta acción no se puede deshacer.');">
                            <i class="fa-solid fa-trash-can me-2"></i> FORMATEAR SISTEMA
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>