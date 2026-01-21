<div class="container py-4">
    <div class="bg-dark text-white p-3 rounded shadow-sm mb-4 d-flex justify-content-between align-items-center">
        <h3 class="mb-0"><i class="fa-solid fa-users-gear"></i> Gestión de Usuarios</h3>
        <span class="badge bg-primary">Importación Masiva</span>
    </div>

    <?php if($mensaje): ?><div class="alert alert-success"><?= $mensaje ?></div><?php endif; ?>
    <?php if($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>

    <div class="row">
        <div class="col-md-6">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white py-3">
                    <h5 class="mb-0"><i class="fa-solid fa-file-csv"></i> Subir Archivo CSV</h5>
                </div>
                <div class="card-body p-4">
                    <form method="POST" enctype="multipart/form-data">
                        <div class="mb-4">
                            <label class="form-label fw-bold">1. Seleccionar Clase destino</label>
                            <select name="clase_id" class="form-select form-select-lg" required>
                                <option value="">-- Seleccionar Clase --</option>
                                <?php foreach($clases as $c): ?>
                                    <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nombre']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">2. Seleccionar archivo .csv</label>
                            <input type="file" name="csv_usuarios" class="form-control" accept=".csv" required>
                            <div class="form-text mt-2">
                                Formato: nombre, email, contraseña
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold">
                            <i class="fa-solid fa-upload me-2"></i> IMPORTAR ALUMNOS
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card border-0 bg-light shadow-sm h-100">
                <div class="card-body">
                    <h5 class="fw-bold text-primary"><i class="fa-solid fa-circle-info"></i> Instrucciones</h5>
                    <hr>
                    <p>Para realizar una importación masiva exitosa, sigue estos pasos:</p>
                    <ol>
                        <li>Crea un archivo en Excel o Bloc de notas.</li>
                        <li>Escribe una fila por alumno con este orden: <strong>Nombre,Email,Contraseña</strong>.</li>
                        <li>Guarda el archivo con extensión <strong>.csv</strong>.</li>
                        <li>Súbelo usando el formulario de la izquierda.</li>
                    </ol>
                    <div class="p-3 bg-white border rounded">
                        <code class="text-muted">
                            Juan Perez,juan@escuela.com,123456<br>
                            Maria Garcia,maria@escuela.com,pass789
                        </code>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
