<div class="container py-4">

    <div class="d-flex justify-content-between align-items-center mb-4 bg-primary text-white p-3 rounded shadow-sm">
        <h3 class="mb-0"><i class="fa-solid fa-list-check"></i> Gestión de Tareas</h3>
        <a href="index.php" class="btn btn-light btn-sm fw-bold"><i class="fa-solid fa-arrow-left"></i> Volver</a>
    </div>

    <?php if($mensaje): ?><div class="alert alert-success alert-dismissible fade show"><?= $mensaje ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>
    <?php if($error): ?><div class="alert alert-danger alert-dismissible fade show"><?= $error ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>

    <div class="card shadow-sm border-0 mb-5">
        <div class="card-header bg-white pt-3">
            <h5 class="fw-bold text-dark">Nueva Tarea</h5>
            <p class="text-muted small mb-0">Crea nuevas tareas de evaluación y controla su estado.</p>
        </div>
        <div class="card-body">
            <form method="POST">
                <input type="hidden" name="crear_tarea" value="1">
                <div class="mb-3">
                    <label class="fw-bold small text-muted">TÍTULO DE LA TAREA</label>
                    <input type="text" name="titulo" class="form-control" placeholder="Ej: Exposición Oral - Trimestre 1" required>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="fw-bold small text-muted">CLASE</label>
                        <select name="clase_id" class="form-select" required>
                            <option value="">-- Seleccionar Clase --</option>
                            <?php foreach($clases as $c): ?>
                                <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="fw-bold small text-muted">RÚBRICA BASE</label>
                        <select name="rubrica_id" class="form-select" required>
                            <option value="">-- Seleccionar Rúbrica --</option>
                            <?php foreach($rubricas as $r): ?>
                                <option value="<?= $r['id'] ?>"><?= htmlspecialchars($r['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="mb-3 p-3 bg-light rounded border">
                    <label class="fw-bold small text-dark mb-2"><i class="fa-solid fa-scale-balanced"></i> Ponderaciones (%)</label>
                    <div class="row g-2">
                        <div class="col-md-4">
                            <div class="input-group">
                                <span class="input-group-text bg-primary text-white fw-bold w-50">Hetero</span>
                                <input type="number" name="peso_hetero" class="form-control" value="60">
                                <span class="input-group-text">%</span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="input-group">
                                <span class="input-group-text bg-info text-dark fw-bold w-50">Coeval</span>
                                <input type="number" name="peso_coeval" class="form-control" value="30">
                                <span class="input-group-text">%</span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="input-group">
                                <span class="input-group-text bg-success text-white fw-bold w-50">Auto</span>
                                <input type="number" name="peso_auto" class="form-control" value="10">
                                <span class="input-group-text">%</span>
                            </div>
                        </div>
                    </div>
                </div>
                <button class="btn btn-primary w-100 fw-bold"><i class="fa-solid fa-plus-circle"></i> CREAR TAREA</button>
            </form>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-light">
            <h6 class="mb-0 fw-bold text-secondary">Mis Tareas Asignadas</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light small text-muted">
                        <tr>
                            <th>Clase</th>
                            <th>Tarea / Rúbrica</th>
                            <th class="text-center">Estado</th>
                            <th class="text-end px-4">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($lista_tareas as $t): ?>
                        <tr>
                            <td><span class="badge bg-primary"><?= htmlspecialchars($t['nombre_clase']) ?></span></td>
                            <td>
                                <div class="fw-bold"><?= htmlspecialchars($t['titulo']) ?></div>
                                <div class="small text-muted"><i class="fa-solid fa-table"></i> <?= htmlspecialchars($t['nombre_rubrica']) ?></div>
                            </td>
                            <td class="text-center" style="width: 150px;">
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="toggle_estado_id" value="<?= $t['id'] ?>">
                                    <?php if($t['estado'] === 'activa'): ?>
                                        <input type="hidden" name="nuevo_estado" value="cerrada">
                                        <button class="btn btn-sm btn-success w-100">
                                            <i class="fa-solid fa-lock-open"></i> Abierta
                                        </button>
                                    <?php else: ?>
                                        <input type="hidden" name="nuevo_estado" value="activa">
                                        <button class="btn btn-sm btn-secondary w-100">
                                            <i class="fa-solid fa-lock"></i> Cerrada
                                        </button>
                                    <?php endif; ?>
                                </form>
                            </td>
                            <td class="text-end">
                                <div class="d-flex justify-content-end gap-2 px-3">
                                    <a href="index.php?action=evaluar&tarea_id=<?= $t['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-pen-nib"></i> Evaluar</a>
                                    <a href="index.php?action=ver_resultados&tarea_id=<?= $t['id'] ?>" class="btn btn-sm btn-primary"><i class="fa-solid fa-chart-line"></i> Notas</a>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#modalEditarTarea<?= $t['id'] ?>"><i class="fa-solid fa-pen"></i></button>
                                    <form method="POST" onsubmit="return confirm('¿Borrar?');" style="display:inline;">
                                        <input type="hidden" name="borrar_tarea_id" value="<?= $t['id'] ?>">
                                        <button class="btn btn-sm btn-outline-danger"><i class="fa-solid fa-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>

                        <!-- Modal Editar -->
                        <div class="modal fade" id="modalEditarTarea<?= $t['id'] ?>" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header bg-light">
                                        <h5 class="modal-title fw-bold">Editar Tarea</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <form method="POST">
                                        <div class="modal-body text-start">
                                            <input type="hidden" name="editar_tarea" value="1">
                                            <input type="hidden" name="id_tarea" value="<?= $t['id'] ?>">
                                            <div class="mb-3">
                                                <label class="form-label small fw-bold text-muted">Título</label>
                                                <input type="text" name="titulo" class="form-control" value="<?= htmlspecialchars($t['titulo']) ?>" required>
                                            </div>
                                            <div class="p-3 bg-light rounded border">
                                                <div class="input-group mb-2">
                                                    <span class="input-group-text bg-primary text-white fw-bold" style="width: 100px;">Hetero</span>
                                                    <input type="number" name="peso_hetero" class="form-control" value="<?= $t['peso_hetero'] ?>">
                                                </div>
                                                <div class="input-group mb-2">
                                                    <span class="input-group-text bg-info text-dark fw-bold" style="width: 100px;">Coeval</span>
                                                    <input type="number" name="peso_coeval" class="form-control" value="<?= $t['peso_coeval'] ?>">
                                                </div>
                                                <div class="input-group">
                                                    <span class="input-group-text bg-success text-white fw-bold" style="width: 100px;">Auto</span>
                                                    <input type="number" name="peso_auto" class="form-control" value="<?= $t['peso_auto'] ?>">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                                            <button type="submit" class="btn btn-primary fw-bold">Guardar</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        <?php if(empty($lista_tareas)): ?><tr><td colspan="4" class="text-center py-4 text-muted">No hay tareas asignadas todavía.</td></tr><?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>