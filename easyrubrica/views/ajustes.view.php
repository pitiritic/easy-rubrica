<?php
// easyrubrica/views/ajustes.view.php
?>
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
        <div class="col-md-4">
            <div class="card shadow-sm border-0 h-100 hover-card d-flex flex-column">
                <div class="card-header bg-primary text-white py-3">
                    <h5 class="mb-0 small fw-bold text-uppercase"><i class="fa-solid fa-download"></i> Copia de Seguridad</h5>
                </div>
                <div class="card-body d-flex flex-column">
                    <p class="text-muted small flex-grow-1">Descarga un archivo <code>.sql</code> con toda la base de datos (usuarios, clases, rúbricas y notas).</p>
                    <a href="?action=ajustes&do=backup" class="btn btn-primary w-100 fw-bold mt-auto py-2 small">
                        <i class="fa-solid fa-file-export me-2"></i> DESCARGAR RESPALDO
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm border-0 h-100 hover-card d-flex flex-column">
                <div class="card-header bg-success text-white py-3">
                    <h5 class="mb-0 small fw-bold text-uppercase"><i class="fa-solid fa-upload"></i> Restaurar Sistema</h5>
                </div>
                <div class="card-body d-flex flex-column">
                    <p class="text-muted small">Sube un archivo de respaldo previo para restaurar toda la información.</p>
                    <form method="POST" enctype="multipart/form-data" class="mt-auto">
                        <input type="file" name="backup_file" class="form-control form-control-sm mb-3" accept=".sql" required>
                        <button type="submit" name="restaurar_backup" class="btn btn-success w-100 fw-bold py-2 small">
                            <i class="fa-solid fa-clock-rotate-left me-2"></i> RESTAURAR
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- TARJETA DE AUDITORÍA CON COLOR #1b355c -->
        <div class="col-md-4">
            <div class="card shadow-sm border-0 h-100 hover-card d-flex flex-column" style="border-top: 4px solid #1b355c !important;">
                <div class="card-header text-white py-3" style="background-color: #1b355c;">
                    <h5 class="mb-0 small fw-bold text-uppercase"><i class="fa-solid fa-clipboard-check"></i> Auditoría</h5>
                </div>
                <div class="card-body d-flex flex-column">
                    <p class="text-muted small flex-grow-1">Consulta el historial de movimientos, eventos del sistema y exporta informes de actividad.</p>
                    <a href="?action=auditoria" class="btn text-white w-100 fw-bold mt-auto py-2 small" style="background-color: #1b355c;">
                        <i class="fa-solid fa-eye me-2"></i> VER INFORMES
                    </a>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card shadow-sm border-0 hover-card border-top border-4 border-info">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 text-info fw-bold"><i class="fa-solid fa-link"></i> Enlaces de la Plataforma (Menú Usuario)</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted small">Define las direcciones web a las que apuntarán los enlaces del menú desplegable de usuario.</p>
                    <form method="POST">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-muted text-uppercase">URL Ayuda y Recursos</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fa-solid fa-circle-question text-info"></i></span>
                                    <input type="url" name="url_ayuda" class="form-control" value="<?= htmlspecialchars($sistema['url_ayuda']) ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-muted text-uppercase">URL Acerca de</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fa-solid fa-circle-info text-secondary"></i></span>
                                    <input type="url" name="url_acerca" class="form-control" value="<?= htmlspecialchars($sistema['url_acerca']) ?>">
                                </div>
                            </div>
                        </div>
                        <div class="mt-3">
                            <button type="submit" name="guardar_sistema" class="btn btn-info px-4 fw-bold shadow-sm text-white">
                                <i class="fa-solid fa-floppy-disk me-2"></i> GUARDAR ENLACES
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card shadow-sm border-0 hover-card border-top border-4 border-secondary">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 text-secondary fw-bold"><i class="fa-solid fa-envelope"></i> Configuración del Servidor SMTP</h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-muted text-uppercase">SERVIDOR SMTP</label>
                                <input type="text" name="smtp_host" class="form-control" value="<?= htmlspecialchars($smtp['smtp_host'] ?? '') ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-muted text-uppercase">USUARIO / EMAIL</label>
                                <input type="text" name="smtp_user" class="form-control" value="<?= htmlspecialchars($smtp['smtp_user'] ?? '') ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-muted text-uppercase">CONTRASEÑA</label>
                                <input type="password" name="smtp_pass" class="form-control" value="<?= htmlspecialchars($smtp['smtp_pass'] ?? '') ?>" required>
                            </div>
                        </div>
                        <div class="mt-3">
                            <button type="submit" name="guardar_smtp" class="btn btn-secondary px-4 fw-bold shadow-sm">
                                <i class="fa-solid fa-floppy-disk me-2"></i> GUARDAR CONFIGURACIÓN
                            </button>
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
                        <p class="text-muted mb-0 small">Esta acción borrará <strong>TODOS</strong> los datos del sistema de forma irreversible.</p>
                    </div>
                    <form method="POST">
                        <input type="hidden" name="reset_total" value="1">
                        <button type="submit" class="btn btn-danger btn-lg px-4 fw-bold shadow-sm" onclick="return confirm('¿Estás seguro de que deseas borrar TODO?');">
                            <i class="fa-solid fa-trash-can me-2"></i> FORMATEAR SISTEMA
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>