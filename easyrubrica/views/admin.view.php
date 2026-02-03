<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4 bg-primary text-white p-3 rounded shadow-sm">
        <h3 class="mb-0"><i class="fa-solid fa-list-check"></i> Gestión de Tareas</h3>
        <a href="index.php" class="btn btn-light btn-sm fw-bold"><i class="fa-solid fa-arrow-left"></i> Volver</a>
    </div>

    <?php if($mensaje): ?><div class="alert alert-success alert-dismissible fade show shadow-sm"><?= $mensaje ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>
    <?php if($error): ?><div class="alert alert-danger alert-dismissible fade show shadow-sm"><?= $error ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>

    <!-- Formulario Nueva Tarea -->
    <div class="card shadow-sm border-0 mb-5">
        <div class="card-header bg-white pt-3"><h5 class="fw-bold text-dark">Nueva Tarea</h5></div>
        <div class="card-body">
            <form method="POST">
                <input type="hidden" name="crear_tarea" value="1">
                <div class="mb-3">
                    <label class="fw-bold small text-muted">TÍTULO DE LA TAREA</label>
                    <input type="text" name="titulo" class="form-control" placeholder="Ej: Exposición Oral" required>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="fw-bold small text-muted">CLASE</label>
                        <select name="clase_id" class="form-select" required>
                            <option value="">-- Seleccionar --</option>
                            <?php foreach($clases as $c): ?><option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nombre']) ?></option><?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="fw-bold small text-muted">RÚBRICA BASE</label>
                        <select name="rubrica_id" class="form-select" required>
                            <option value="">-- Seleccionar --</option>
                            <?php foreach($rubricas as $r): ?><option value="<?= $r['id'] ?>"><?= htmlspecialchars($r['nombre']) ?></option><?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="mb-3 p-3 bg-light rounded border">
                    <div class="row g-2">
                        <div class="col-md-4"><div class="input-group"><span class="input-group-text bg-primary text-white fw-bold w-50 small">Hetero</span><input type="number" name="peso_hetero" class="form-control" value="60"></div></div>
                        <div class="col-md-4"><div class="input-group"><span class="input-group-text bg-info text-dark fw-bold w-50 small">Coeval</span><input type="number" name="peso_coeval" class="form-control" value="30"></div></div>
                        <div class="col-md-4"><div class="input-group"><span class="input-group-text bg-success text-white fw-bold w-50 small">Auto</span><input type="number" name="peso_auto" class="form-control" value="10"></div></div>
                    </div>
                </div>
                <button class="btn btn-primary w-100 fw-bold">CREAR TAREA</button>
            </form>
        </div>
    </div>

    <!-- Tareas Activas -->
    <div class="card shadow-sm border-0 mb-5">
        <div class="card-header bg-light"><h6 class="mb-0 fw-bold text-secondary">Tareas Activas</h6></div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light small text-muted"><tr><th class="ps-4">Clase</th><th>Tarea / Rúbrica</th><th class="text-center">Estado</th><th class="text-end px-4">Acción</th></tr></thead>
                    <tbody>
                        <?php foreach($lista_tareas as $t): ?>
                        <tr>
                            <td class="ps-4"><span class="badge bg-primary"><?= htmlspecialchars($t['nombre_clase']) ?></span></td>
                            <td><div class="fw-bold"><?= htmlspecialchars($t['titulo']) ?></div><div class="small text-muted"><?= htmlspecialchars($t['nombre_rubrica']) ?></div></td>
                            <td class="text-center" style="width: 150px;">
                                <form method="POST" style="display:inline;"><input type="hidden" name="toggle_estado_id" value="<?= $t['id'] ?>"><input type="hidden" name="nuevo_estado" value="<?= $t['estado']==='activa'?'cerrada':'activa' ?>"><button class="btn btn-sm <?= $t['estado']==='activa'?'btn-success':'btn-secondary' ?> w-100"><i class="fa-solid <?= $t['estado']==='activa'?'fa-lock-open':'fa-lock' ?>"></i> <?= $t['estado']==='activa'?'Abierta':'Cerrada' ?></button></form>
                            </td>
                            <td class="text-end px-3">
                                <div class="d-flex justify-content-end gap-1">
                                    <a href="?action=evaluar&tarea_id=<?= $t['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-pen-nib"></i></a>
                                    <a href="?action=notas&tarea_id=<?= $t['id'] ?>" class="btn btn-sm btn-primary"><i class="fa-solid fa-chart-line"></i></a>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="prepareEdit(<?= $t['id'] ?>, '<?= addslashes($t['titulo']) ?>', <?= $t['peso_hetero'] ?>, <?= $t['peso_coeval'] ?>, <?= $t['peso_auto'] ?>)"><i class="fa-solid fa-pen"></i></button>
                                    <button type="button" class="btn btn-sm btn-outline-warning" onclick="prepareArchive(<?= $t['id'] ?>)"><i class="fa-solid fa-box-archive"></i></button>
                                    <form method="POST" onsubmit="return confirm('¿Borrar?');" style="display:inline;"><input type="hidden" name="borrar_tarea_id" value="<?= $t['id'] ?>"><button class="btn btn-sm btn-outline-danger"><i class="fa-solid fa-trash"></i></button></form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Depósito -->
    <div class="card shadow-sm border-0 mt-5 border-top border-4" style="border-top-color: #1b355c !important;">
        <div class="card-header bg-white py-3"><h5 class="mb-0 fw-bold" style="color: #1b355c;"><i class="fa-solid fa-boxes-stacked"></i> Depósito de Tareas</h5></div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light small text-muted"><tr><th class="ps-4">Curso</th><th>Clase</th><th>Tarea</th><th class="text-end px-4">Acción</th></tr></thead>
                    <tbody>
                        <?php foreach($deposito_tareas as $dt): ?>
                        <tr>
                            <td class="ps-4 fw-bold" style="color: #1b355c;"><?= htmlspecialchars($dt['curso_academico']) ?></td>
                            <td><span class="badge bg-secondary"><?= htmlspecialchars($dt['nombre_clase']) ?></span></td>
                            <td><div class="fw-bold"><?= htmlspecialchars($dt['titulo']) ?></div></td>
                            <td class="text-end px-4">
                                <form method="POST" style="display:inline;"><input type="hidden" name="recuperar_tarea_id" value="<?= $dt['id'] ?>"><button class="btn btn-sm btn-outline-primary" title="Recuperar"><i class="fa-solid fa-rotate-left"></i></button></form>
                                <a href="?action=notas&tarea_id=<?= $dt['id'] ?>" class="btn btn-sm btn-primary"><i class="fa-solid fa-chart-line"></i></a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- MODAL EDITAR (ÚNICO) -->
<div class="modal fade" id="modalEditarTareaGlobal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" class="modal-content">
            <div class="modal-header"><h5>Editar Tarea</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <input type="hidden" name="editar_tarea" value="1">
                <input type="hidden" name="id_tarea" id="edit_id_tarea">
                <div class="mb-3"><label class="small fw-bold">Título</label><input type="text" name="titulo" id="edit_titulo" class="form-control" required></div>
                <div class="row g-2">
                    <div class="col-4"><label class="small">Hetero</label><input type="number" name="peso_hetero" id="edit_h" class="form-control"></div>
                    <div class="col-4"><label class="small">Coeval</label><input type="number" name="peso_coeval" id="edit_c" class="form-control"></div>
                    <div class="col-4"><label class="small">Auto</label><input type="number" name="peso_auto" id="edit_a" class="form-control"></div>
                </div>
            </div>
            <div class="modal-footer"><button type="submit" class="btn btn-primary">Guardar Cambios</button></div>
        </form>
    </div>
</div>

<!-- MODAL ARCHIVAR (ÚNICO) -->
<div class="modal fade" id="modalArchivarGlobal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" class="modal-content">
            <div class="modal-header bg-warning"><h5>Archivar Tarea</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <input type="hidden" name="archivar_tarea_id_hidden" id="archive_id_tarea">
                <label class="small fw-bold text-uppercase">Curso Académico</label>
                <input type="text" name="curso_academico" class="form-control" value="<?= date('Y').'-'.(date('Y')+1) ?>" required>
            </div>
            <div class="modal-footer"><button type="submit" class="btn btn-warning w-100 fw-bold">MOVER AL DEPÓSITO</button></div>
        </form>
    </div>
</div>

<script>
function prepareEdit(id, titulo, h, c, a) {
    document.getElementById('edit_id_tarea').value = id;
    document.getElementById('edit_titulo').value = titulo;
    document.getElementById('edit_h').value = h;
    document.getElementById('edit_c').value = c;
    document.getElementById('edit_a').value = a;
    new bootstrap.Modal(document.getElementById('modalEditarTareaGlobal')).show();
}
function prepareArchive(id) {
    document.getElementById('archive_id_tarea').value = id;
    new bootstrap.Modal(document.getElementById('modalArchivarGlobal')).show();
}
</script>